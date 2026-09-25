<!-- CartDrawer.vue — Vue 3 island for the cart drawer.
  Progressive enhancement of the jQuery-powered mini-cart in front-theme.js.
  Kill-switch: window.__SPA_VUE_ISLANDS__ === false disables this component entirely.
  Endpoints (all session-auth, CSRF-protected):
    POST /{slug}/cart-list-sidebar -> CartController::cart_list_sidebar
    POST /{slug}/change-cart      -> CartController::change_cart
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
// Blade already embeds data-* attrs, so we read them directly.
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
    const nameEl = (root).querySelector('h4')
    const imgEl = (root).querySelector('img')
    items.push({
      cart_id: cartId,
      product_id: productId,
      variant_id: parseInt(variantId) || 0,
      name: nameEl ? nameEl.textContent.trim() : '',
      image: imgEl ? imgEl.src : '',
      qty,
    })
  })
  return items
}

function parseTotals(html) {
  const c = document.createElement('div')
  c.innerHTML = html
  const rows = c.querySelectorAll('.border-t.p-4 .flex.justify-between')
  const labels = ['sub_total', 'tax_price', 'total']
  const t = { sub_total: '', tax_price: '', total: '' }
  let i = 0
  rows.forEach((row) => {
    const spans = row.querySelectorAll('span')
    if (spans.length >= 2 && labels[i]) {
      t[labels[i]] = spans[spans.length - 1].textContent.trim()
      i++
    }
  })
  return t
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
    if (d.status === 0 || d.status === 1) {
      cartCount.value = d.cart_total_product ?? 0
      if (d.status === 0) {
        cart.value = { empty: true, cart_total_product: 0 }
      } else {
        cart.value = {
          empty: false,
          cart_total_product: d.cart_total_product,
          product_list: parseCartHtml(d.html || ''),
          ...parseTotals(d.html || ''),
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
  isOpen.value = true
  document.body.style.overflow = 'hidden'
  if (!cart.value) fetchCart()
}

function closeDrawer() {
  isOpen.value = false
  document.body.style.overflow = ''
}

async function changeQty(item, type) {
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
  const toggle = document.getElementById('cart-toggle')
  if (toggle) {
    toggle.addEventListener('click', (e) => {
      e.preventDefault()
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
</script>

<template>
  <teleport to="#cart-panel">
    <template v-if="isOpen">
      <div class="flex flex-col h-full">
        <div class="flex justify-between items-center p-4 border-b">
          <h2 class="font-bold text-xl">Your Cart ({{ cart?.cart_total_product ?? 0 }})</h2>
          <button @click="closeDrawer" class="text-2xl hover:text-primary">&times;</button>
        </div>
        <div v-if="isLoadingCart && !cart" class="p-8 text-center text-gray-500">
          <div class="animate-spin inline-block w-6 h-6 border-2 border-primary border-t-transparent rounded-full mb-2"></div>
          <p>Loading cart...</p>
        </div>
        <div v-else-if="error" class="p-4 text-center text-red-600">
          <p>{{ error }}</p>
          <button @click="fetchCart" class="mt-2 text-sm text-primary hover:underline">Retry</button>
        </div>
        <div v-else-if="cart?.empty" class="p-8 text-center text-gray-500">
          <i class="fa-solid fa-cart-shopping text-3xl mb-3"></i>
          <p>Your cart is empty</p>
        </div>
        <div v-else class="flex flex-col h-full">
          <div class="overflow-y-auto flex-grow p-4 space-y-4">
            <div v-for="item in cart?.product_list ?? []" :key="item.cart_id" class="flex gap-3 border-b pb-4">
              <a :href="`/${storeSlug}/product/${item.product_id}`" class="w-20 h-24 rounded-md overflow-hidden bg-gray-100 border p-3">
                <img :src="item.image" alt="cart-image" class="object-contain h-full w-full" loading="lazy" decoding="async" />
              </a>
              <div class="flex-1">
                <div class="flex items-center justify-between gap-3 mb-1">
                  <h4 class="font-semibold">{{ item.name }}</h4>
                  <button @click="changeQty(item, 'remove')" class="remove_item text-red-600" type="button">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </div>
                <p v-if="item.variant_id && item.variant_id != 0" class="text-sm text-gray-600">{{ item.variant_name ?? '' }}</p>
                <div class="flex justify-between items-center mt-2 gap-2 flex-wrap">
                  <div class="qty-spinner flex items-center border rounded-md max-w-[90px] w-full">
                    <button @click="changeQty(item, 'decrease')" class="quantity-decrement flex-1 px-2 py-1 text-gray-500 hover:text-primary outline-none">
                      <i class="fas fa-minus"></i>
                    </button>
                    <input type="text" class="quantity text-sm p-2 flex-1 form-input border-none leading-none text-center" :value="item.qty" min="1" max="100" readonly />
                    <button @click="changeQty(item, 'increase')" class="quantity-increment flex-1 px-2 py-1 text-gray-500 hover:text-primary outline-none">
                      <i class="fas fa-plus"></i>
                    </button>
                  </div>
                  <span class="font-bold text-primary-dark">—</span>
                </div>
              </div>
            </div>
          </div>
          <div class="border-t p-4">
            <div class="flex justify-between pb-2"><span>Subtotal</span><span class="font-bold">{{ cart?.sub_total ?? '—' }}</span></div>
            <div class="flex justify-between pb-2"><span>Tax</span><span class="font-bold">{{ cart?.tax_price ?? '—' }}</span></div>
            <div class="flex justify-between pb-3 text-lg font-bold"><span>Total</span><span class="text-primary-dark">{{ cart?.total ?? '—' }}</span></div>
            <a :href="`/${storeSlug}/cart`" class="btn-primary w-full mb-3">Proceed to Checkout</a>
            <a :href="`/${storeSlug}/products`" class="btn-outline w-full">Continue Shopping</a>
          </div>
        </div>
      </div>
    </template>
  </teleport>
</template>
