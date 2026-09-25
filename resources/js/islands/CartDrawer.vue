<!-- CartDrawer.vue — Vue 3 island for the cart drawer.
  Progressive enhancement of the jQuery-powered mini-cart in front-theme.js.
  Kill-switch: window.__SPA_VUE_ISLANDS__ === false disables this component entirely.

  Endpoints (all session-auth, CSRF-protected):
    POST /{slug}/cart-list-sidebar -> CartController::cart_list_sidebar  returns full cart JSON
    POST /{slug}/change-cart      -> CartController::change_cart        qty inc/dec/remove
    POST /{slug}/cart-remove      -> CartController::cart_remove         remove item
-->
<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  slug: { type: String, default: '' },
  initialCount: { type: Number, default: 0 },
})

const isOpen = ref(false)
const cart = ref(null)
const cartCount = ref(props.initialCount)
const isLoadingCart = ref(false)
const error = ref(null)

const storeSlug = props.slug || (window.location.pathname.split('/').filter(Boolean)[0] ?? '')
const endpoints = {
  list: `/${storeSlug}/cart-list-sidebar`,
  change: `/${storeSlug}/change-cart`,
  remove: `/${storeSlug}/cart-remove`,
}

function csrfToken() {
  const t = document.querySelector('meta[name="csrf-token"]')
  return t ? t.content : ''
}

function buildBody(data) {
  const b = new URLSearchParams()
  b.append('_token', csrfToken())
  for (const [k, v] of Object.entries(data)) {
    if (v !== undefined && v !== null) b.append(k, v)
  }
  return b
}

// Parse the rendered HTML from cart-list-sidebar to extract per-item data.
// Blade already embeds data-* attrs on the remove button, so we read them directly.
function parseCartHtml(html) {
  const items = []
  if (!html) return items
  const c = document.createElement('div')
  c.innerHTML = html
  c.querySelectorAll('[data-id][data-product-id]').forEach((btn) => {
    const cartId = btn.getAttribute('data-id')
    const productId = btn.getAttribute('data-product-id')
    const variantId = btn.getAttribute('data-variant-id') || '0'
    // Find the item container by walking up to the closest div with a quantity input
    const root = btn.closest('div')
    let qtyRoot = root
    while (qtyRoot && !qtyRoot.querySelector('input[name="quantity"]')) {
      qtyRoot = qtyRoot.parentElement
    }
    const qtyInput = (qtyRoot || root).querySelector('input[name="quantity"]')
    const qty = qtyInput ? parseInt(qtyInput.value) || 1 : 1
    const nameEl = root ? root.querySelector('h4') : null
    const imgEl = root ? root.querySelector('img') : null
    // Variant name is in a <p class="text-sm text-gray-600"> sibling
    const variantPEl = root ? root.querySelector('p.text-sm.text-gray-600') : null
    items.push({
      cart_id: cartId,
      product_id: productId,
      variant_id: parseInt(variantId) || 0,
      name: nameEl ? nameEl.textContent.trim() : '',
      image: imgEl ? imgEl.src : '',
      qty,
      variant_name: variantPEl ? variantPEl.textContent.trim() : '',
    })
  })
  return items
}

async function fetchCart() {
  if (isLoadingCart.value) return
  isLoadingCart.value = true
  error.value = null
  try {
    const r = await fetch(endpoints.list, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: new URLSearchParams({
        _token: csrfToken(), method_id: '', shipping_price: '',
        coupon_code: '', stateId: '', countryId: '', cityId: '',
        tax_id_value: '', shipping_final_price: '0', order_type: '',
      }),
      credentials: 'same-origin',
    })
    const d = await r.json()
    console.log('[CartDrawer.fetchCart] response:', { status: d.status, cart_total: d.cart_total_product, htmlLength: d.html?.length || 0, sub_total: d.sub_total, final_price: d.final_price });
    if (d.status === 0 || d.status === 1) {
      cartCount.value = d.cart_total_product ?? 0
      if (d.status === 0) {
        cart.value = { empty: true, cart_total_product: 0 }
      } else {
        const items = parseCartHtml(d.html || '')
        console.log('[CartDrawer.fetchCart] parsed items:', JSON.stringify(items.map(i => ({ cart_id: i.cart_id, product_id: i.product_id, name: i.name, qty: i.qty, variant_id: i.variant_id }))));
        cart.value = {
          empty: false,
          cart_total_product: d.cart_total_product,
          product_list: items,
          sub_total: d.sub_total || '',
          tax_price: d.tax_price || '',
          total: d.final_price || '',
        }
      }
    }
  } catch (e) {
    error.value = e.message || 'Failed to load cart'
  } finally {
    isLoadingCart.value = false
  }
}

function openDrawer() {
  console.log('[CartDrawer.openDrawer] called');
  isOpen.value = true
  document.body.style.overflow = 'hidden'
  if (!cart.value) fetchCart()
}

function closeDrawer() {
  isOpen.value = false
  document.body.style.overflow = ''
}

async function changeQty(item, type) {
  console.log('[CartDrawer.changeQty] called', { cart_id: item?.cart_id, type, qty: item?.qty });
  if (!item) return
  const prevQty = item.qty
  if (type === 'increase') item.qty += 1
  else if (type === 'decrease') item.qty = Math.max(1, item.qty - 1)
  else if (type === 'remove') {
    if (cart.value && cart.value.product_list) {
      const idx = cart.value.product_list.findIndex((i) => i.cart_id === item.cart_id)
      if (idx >= 0) cart.value.product_list.splice(idx, 1)
      cart.value.cart_total_product = (cart.value.cart_total_product || 0) - 1
      cartCount.value = Math.max(0, cartCount.value - 1)
    }
  }
  try {
    const r = await fetch(endpoints.change, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: buildBody({ cart_id: item.cart_id, quantity_type: type, product_id: item.product_id, variant_id: item.variant_id }),
      credentials: 'same-origin',
    })
    const d = await r.json()
    if (d.status === 1) {
      cartCount.value = d.count ?? d.data?.count ?? cartCount.value
      if (type === 'remove' && cartCount.value === 0) cart.value = { empty: true, cart_total_product: 0 }
      // Re-fetch fresh totals after mutation
      if (cart.value && !cart.value.empty) fetchCart()
    } else {
      if (type === 'increase' || type === 'decrease') item.qty = prevQty
      error.value = d.message || 'Could not update cart'
    }
  } catch (e) {
    if (type !== 'remove') item.qty = prevQty
    error.value = e.message || 'Network error'
  }
}

onMounted(() => {
  // Override jQuery's get_cartlist to prevent it from replacing Vue-rendered
  // content in #cart-panel. When the Vue island is active, the component
  // manages the drawer DOM reactively.
  if (typeof window.get_cartlist === 'function') {
    window.get_cartlist = function () { /* no-op — Vue handles this */ }
  }

  const toggle = document.getElementById('cart-toggle')
  if (toggle) {
    toggle.addEventListener('click', (e) => {
      console.log('[CartDrawer] cart-toggle clicked, stopping propagation');
      e.preventDefault()
      // Stop propagation so jQuery's delegated .cart-header handler doesn't
      // call get_cartlist() and replace our teleported content.
      e.stopPropagation()
      openDrawer()
    })
  }
  const badge = document.getElementById('cart-count')
  if (badge && badge.textContent) {
    const c = parseInt(badge.textContent)
    if (!isNaN(c)) cartCount.value = c
  }
  const onKeydown = (e) => { if (e.key === 'Escape' && isOpen.value) closeDrawer() }
  const onOverlayClick = (e) => { if (e.target && e.target.id === 'cart-overlay') closeDrawer() }
  document.addEventListener('keydown', onKeydown)
  document.addEventListener('click', onOverlayClick)
  onUnmounted(() => {
    document.removeEventListener('keydown', onKeydown)
    document.removeEventListener('click', onOverlayClick)
  })
})

watch(isOpen, (val) => {
  const panel = document.getElementById('cart-panel')
  const overlay = document.getElementById('cart-overlay')
  if (panel) panel.classList.toggle('open', val)
  if (overlay) overlay.classList.toggle('open', val)
})

// Keep the cart-count badge in sync with the reactive store
watch(cartCount, (val) => {
  const badge = document.getElementById('cart-count')
  if (badge) badge.textContent = String(val)
})
</script>

<template>
  <!--
    Mount the teleport once (v-if="isOpen" on the teleport would mount/unmount
    the component on every open/close, losing reactive state). Instead, keep
    the teleport alive and switch content via a computed class.
  -->
  <teleport to="#cart-panel">
    <div :class="['flex', 'flex-col', 'h-full']" v-show="isOpen" style="display: none;">
      <div class="flex justify-between items-center p-4 border-b">
        <h2 class="font-bold text-xl">Your Cart ({{ cart?.cart_total_product ?? 0 }})</h2>
        <button @click.stop="closeDrawer" class="text-2xl hover:text-primary" aria-label="Close cart">
          &times;
        </button>
      </div>
      <template v-if="isLoadingCart && !cart">
        <div class="p-8 text-center text-gray-500">
          <div class="animate-spin inline-block w-6 h-6 border-2 border-primary border-t-transparent rounded-full mb-2"></div>
          <p>Loading cart...</p>
        </div>
      </template>
      <template v-else-if="error">
        <div class="p-4 text-center text-red-600">
          <p>{{ error }}</p>
          <button @click.stop="fetchCart" class="mt-2 text-sm text-primary hover:underline">Retry</button>
        </div>
      </template>
      <template v-else-if="cart?.empty">
        <div class="p-8 text-center text-gray-500">
          <i class="fa-solid fa-cart-shopping text-3xl mb-3"></i>
          <p>Your cart is empty</p>
        </div>
      </template>
      <template v-else>
        <div class="flex flex-col h-full">
          <div class="overflow-y-auto flex-grow p-4 space-y-4">
            <div v-for="item in cart?.product_list ?? []" :key="item.cart_id" class="flex gap-3 border-b pb-4">
              <a :href="`/${storeSlug}/products`" class="w-20 h-24 rounded-md overflow-hidden bg-gray-100 border p-3">
                <img :src="item.image" alt="cart-image" class="object-contain h-full w-full" loading="lazy" decoding="async" />
              </a>
              <div class="flex-1">
                <div class="flex items-center justify-between gap-3 mb-1">
                  <h4 class="font-semibold">{{ item.name }}</h4>
                  <button @click.stop="changeQty(item, 'remove')" class="remove_item text-red-600" type="button" aria-label="Remove item">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </div>
                <p v-if="item.variant_id && item.variant_id != 0" class="text-sm text-gray-600">{{ item.variant_name ?? '' }}</p>
                <div class="flex justify-between items-center mt-2 gap-2 flex-wrap">
                  <div class="qty-spinner flex items-center border rounded-md max-w-[90px] w-full">
                    <button @click.stop="changeQty(item, 'decrease')" class="quantity-decrement flex-1 px-2 py-1 text-gray-500 hover:text-primary outline-none" aria-label="Decrease quantity">
                      <i class="fas fa-minus"></i>
                    </button>
                    <input type="text" class="quantity text-sm p-2 flex-1 form-input border-none leading-none text-center" :value="item.qty" min="1" max="100" readonly />
                    <button @click.stop="changeQty(item, 'increase')" class="quantity-increment flex-1 px-2 py-1 text-gray-500 hover:text-primary outline-none" aria-label="Increase quantity">
                      <i class="fas fa-plus"></i>
                    </button>
                  </div>
                  <span class="font-bold text-primary-dark">&mdash;</span>
                </div>
              </div>
            </div>
          </div>
          <div class="border-t p-4">
            <div class="flex justify-between pb-2"><span>Subtotal</span><span class="font-bold">{{ cart?.sub_total ?? '&mdash;' }}</span></div>
            <div class="flex justify-between pb-2"><span>Tax</span><span class="font-bold">{{ cart?.tax_price ?? '&mdash;' }}</span></div>
            <div class="flex justify-between pb-3 text-lg font-bold"><span>Total</span><span class="text-primary-dark">{{ cart?.total ?? '&mdash;' }}</span></div>
            <a :href="`/${storeSlug}/cart`" class="btn-primary w-full mb-3">Proceed to Checkout</a>
            <a :href="`/${storeSlug}/products`" class="btn-outline w-full">Continue Shopping</a>
          </div>
        </div>
      </template>
    </div>
  </teleport>
</template>
