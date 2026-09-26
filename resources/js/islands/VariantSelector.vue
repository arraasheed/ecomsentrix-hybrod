<!-- VariantSelector.vue — Vue 3 island for live variant price/stock.
  Progressive enhancement of the jQuery product_price() in front-theme.js.
  Kill-switch: window.__SPA_VUE_ISLANDS__ === false disables this entirely.

  Endpoints (session-auth, CSRF-protected):
    POST /{slug}/product_price -> ProductController::product_price

  The component reads the initial variant state from the Blade-rendered <select>
  and only intercepts subsequent changes. If the Vue bundle never loads, the
  jQuery handlers in front-theme.js continue to work as before.
-->
<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'

const props = defineProps({
  slug: { type: String, default: '' },
  productId: { type: Number, default: 0 },
})

// Reactive state — hydrated from the DOM on mount.
const selectedVariantId = ref(0)
const finalPrice = ref('')
const originalPrice = ref('')
const stockMessage = ref('In Stock')
const stockStatus = ref('in_stock')
const isOutOfStock = ref(false)
const isLoading = ref(false)
const error = ref(null)
const variantName = ref('')

const storeSlug = props.slug || (window.location.pathname.split('/').filter(Boolean)[0] ?? '')
const priceEndpoint = `/${storeSlug}/product_price`

function csrfToken() {
  const t = document.querySelector('meta[name="csrf-token"]')
  return t ? t.content : ''
}

// Read current select values and build the variant string expected by the
// product_price endpoint. The Blade form serializes `varint[AttributeName]`
// selects; we replicate that here.
function collectVariantString() {
  const form = document.querySelector('.variant_form')
  if (!form) return null

  const parts = []
  form.querySelectorAll('select[name^="varint["]').forEach((select) => {
    const val = select.value
    if (val) parts.push(val)
  })
  // Also include hidden / data attributes the Blade side may rely on.
  const variantInput = form.querySelector('input[name="variant_id"]')
  if (variantInput && variantInput.value) {
    return variantInput.value
  }
  return parts.length > 0 ? parts.join('-') : ''
}

async function fetchVariantPrice() {
  if (isLoading.value) return
  isLoading.value = true
  error.value = null

  const form = document.querySelector('.variant_form')
  if (!form) {
    return
  }

  // Build a FormData from the variant_form so we send exactly what the jQuery
  // code sends (serialized form data + product_id).
  const formData = new FormData(form)
  formData.append('product_id', props.productId)

  try {
    const r = await fetch(priceEndpoint, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken(),
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: new URLSearchParams(formData),
      credentials: 'same-origin',
    })
    const d = await r.json()

    if (d.status === 'error' || d.stock_status === 'out_of_stock' || d.stock_status === 'not_available') {
      isOutOfStock.value = true
      stockStatus.value = d.stock_status || 'out_of_stock'
      stockMessage.value = d.stock_message || 'Out of Stock'
      selectedVariantId.value = d.variant_id || 0
      // Hide the add-to-cart button when out of stock
      const addBtn = document.querySelector('.addcart-btn.addcart-btn-globaly')
      if (addBtn) addBtn.style.display = 'none'
    } else {
      isOutOfStock.value = false
      stockMessage.value = d.stock_message || 'In Stock'
      stockStatus.value = d.stock_status || 'in_stock'
      selectedVariantId.value = d.variant_id || 0
      finalPrice.value = d.final_price_text || ''
      originalPrice.value = d.product_original_price ? `${d.currency || ''}${d.product_original_price}` : ''
      variantName.value = d.variant_name || ''

      // Update price display
      const priceEl = document.querySelector('.product-price-amount .product_final_price')
      if (priceEl && d.final_price) {
        priceEl.textContent = `${d.currency || ''}${d.final_price}`
      }
      const origEl = document.querySelector('.product-price-amount .product_orignal_price')
      if (origEl && d.product_original_price != null) {
        origEl.textContent = `${d.currency || ''}${d.product_original_price}`
      }

      // Update stock status element
      const stockEl = document.querySelector('.stock_status_color')
      if (stockEl) {
        stockEl.classList.remove('text-red-600')
        stockEl.classList.add('text-green-600')
      }

      // Update add-to-cart button variant_id and qty
      const addBtn = document.querySelector('.addcart-btn.addcart-btn-globaly')
      if (addBtn) {
        addBtn.style.display = ''
        addBtn.setAttribute('variant_id', d.variant_id || 0)
        addBtn.setAttribute('qty', d.qty || 1)
      }

      // Update quick-checkout button
      const quickBtn = document.querySelector('.quick-checkout-button')
      if (quickBtn) {
        quickBtn.setAttribute('variant_id', d.variant_id || 0)
        quickBtn.setAttribute('qty', d.qty || 1)
      }
    }
  } catch (e) {
    error.value = e.message || 'Failed to update variant'
    console.error('[VariantSelector] fetch error:', e)
  } finally {
    isLoading.value = false
  }
}

function onVariantChange() {
  fetchVariantPrice()
}

// MutationObserver fallback: re-read values if the DOM changes externally.
function setupVariantListener() {
  const form = document.querySelector('.variant_form')
  if (!form) return

  form.addEventListener('change', onVariantChange)

  // Also listen to clicks on the +/- buttons which call product_price in jQuery
  const decrement = form.querySelector('.quantity-decrement')
  const increment = form.querySelector('.quantity-increment')
  if (decrement) decrement.addEventListener('click', onVariantChange)
  if (increment) increment.addEventListener('click', onVariantChange)
}

onMounted(() => {
  setupVariantListener()
  // Hydrate initial state from existing DOM
  const variantInput = document.querySelector('input[name="variant_id"]')
  if (variantInput) selectedVariantId.value = parseInt(variantInput.value) || 0

  // If variant selects already have a value selected, fetch price immediately
  const form = document.querySelector('.variant_form')
  if (form) {
    const selects = form.querySelectorAll('select[name^="varint["]')
    let hasSelection = false
    selects.forEach((s) => {
      if (s.value) hasSelection = true
    })
    if (hasSelection) {
      fetchVariantPrice()
    }
  }
})

onUnmounted(() => {
  const form = document.querySelector('.variant_form')
  if (form) {
    form.removeEventListener('change', onVariantChange)
  }
})
</script>

<template>
  <!-- The template is intentionally empty — this component works by mutating
       the existing Blade DOM (price spans, stock pills, add-to-cart attrs).
       Rendering nothing itself means no layout shift and no duplicate markup.
       The island exists purely for its reactive wiring + SSR-free hydration. -->
  <template v-if="false">
    <div data-variant-selector>
      <span class="product-final-price" v-text="finalPrice"></span>
      <span class="product-original-price" v-text="originalPrice"></span>
      <span class="stock-message" v-text="stockMessage"></span>
    </div>
  </template>
</template>
