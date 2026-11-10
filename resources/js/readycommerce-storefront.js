const onTurboLoad = (callback) => {
  if (typeof window.onTurboLoad === 'function') {
    window.onTurboLoad(callback)
    return
  }

  const initialize = () => callback(document.body)
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize, { once: true })
  } else {
    initialize()
  }
  document.addEventListener('turbo:load', initialize)
}

const onTurboUnload = (callback) => {
  if (typeof window.onTurboUnload === 'function') {
    window.onTurboUnload(callback)
    return
  }

  document.addEventListener('turbo:before-cache', () => callback(document.body))
}

const pendingCommerceActions = []

function getPayloadSources(payload) {
  return [payload, payload?.data, payload?.cart, payload?.data?.cart, payload?.result].filter((value) => value && typeof value === 'object')
}

function getPayloadValue(payload, keys) {
  for (const source of getPayloadSources(payload)) {
    for (const key of keys) {
      if (source[key] !== undefined && source[key] !== null) return source[key]
    }
  }
  return undefined
}

function parseAjaxPayload(xhr, response) {
  if (response && typeof response === 'object') return response
  if (xhr?.responseJSON && typeof xhr.responseJSON === 'object') return xhr.responseJSON
  const raw = typeof response === 'string' ? response : xhr?.responseText
  if (typeof raw !== 'string' || raw.length === 0) return {}
  try {
    return JSON.parse(raw)
  } catch {
    return { html: raw }
  }
}

function normalizePath(url) {
  try {
    return new URL(url, window.location.origin).pathname
  } catch {
    return String(url || '')
  }
}

function updateCartFragment(markup) {
  const current = document.querySelector('#cart-body')
  if (!current || typeof markup !== 'string' || !markup.trim()) return

  const parsed = new DOMParser().parseFromString(markup, 'text/html')
  const fragment = parsed.querySelector('#cart-body, [data-rc-cart-fragment]')
  if (!fragment) return

  fragment.querySelectorAll('script, iframe, object, embed').forEach((element) => element.remove())
  fragment.querySelectorAll('*').forEach((element) => {
    Array.from(element.attributes).forEach((attribute) => {
      const name = attribute.name.toLowerCase()
      const unsafeUrl = ['href', 'src', 'action', 'formaction'].includes(name)
        && (/^\s*(?:javascript|vbscript):/i.test(attribute.value) || (name !== 'src' && /^\s*data:/i.test(attribute.value)))
      if (/^on/i.test(attribute.name) || name === 'srcdoc' || unsafeUrl) element.removeAttribute(attribute.name)
    })
  })
  Array.from(fragment.attributes).forEach((attribute) => {
    if (/^on/i.test(attribute.name) || attribute.name === 'srcdoc') fragment.removeAttribute(attribute.name)
  })
  current.replaceWith(document.importNode(fragment, true))
}

function formatAmountLikeCurrent(value, element) {
  const amount = Number(value)
  if (!Number.isFinite(amount)) return String(value)

  const currentText = element.textContent || ''
  const match = currentText.match(/[-+]?\d[\d.,\s\u00a0\u202f]*/u)
  if (!match) return amount.toFixed(2)

  const numericText = match[0].trim()
  const separators = Array.from(numericText).filter((character) => character === '.' || character === ',')
  const lastSeparator = separators.at(-1)
  const digitsAfterLastSeparator = lastSeparator ? numericText.length - numericText.lastIndexOf(lastSeparator) - 1 : 0
  const decimalSeparator = separators.length > 1 || (lastSeparator && digitsAfterLastSeparator !== 3) ? lastSeparator : ''
  const decimalDigits = decimalSeparator ? Math.min(3, numericText.length - numericText.lastIndexOf(decimalSeparator) - 1) : 0
  const groupingSeparator = decimalSeparator
    ? Array.from(numericText.slice(0, numericText.lastIndexOf(decimalSeparator))).reverse().find((character) => character === '.' || character === ',' || /\s/u.test(character)) || ''
    : separators[0] || ''
  const fixedAmount = Math.abs(amount).toFixed(decimalDigits)
  const [integerPart, fractionPart] = fixedAmount.split('.')
  const groupedInteger = groupingSeparator ? integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, groupingSeparator) : integerPart
  const formattedAmount = `${amount < 0 ? '-' : ''}${groupedInteger}${decimalSeparator ? `${decimalSeparator}${fractionPart}` : ''}`
  const leadingWhitespace = match[0].match(/^\s*/u)?.[0] || ''
  const trailingWhitespace = match[0].match(/\s*$/u)?.[0] || ''
  return currentText.replace(match[0], `${leadingWhitespace}${formattedAmount}${trailingWhitespace}`)
}

function applyCartPayload(payload) {
  if (!payload) return

  const markup = getPayloadValue(payload, ['cart_html', 'sidebar_html', 'cart_sidebar_html', 'cartHtml', 'sidebarHtml'])
    ?? getPayloadValue(payload, ['html'])
    ?? (typeof payload?.data === 'string' ? payload.data : undefined)
  const html = typeof markup === 'string' ? markup : typeof payload === 'string' ? payload : ''
  if (html.includes('cart-body') || html.includes('data-rc-cart-fragment')) updateCartFragment(html)

  const countValue = getPayloadValue(payload, ['cart_total_product', 'cart_count', 'item_count', 'total_items', 'count'])
  const count = Number(countValue)
  if (Number.isFinite(count)) {
    document.querySelectorAll('[data-rc-cart-count]').forEach((element) => {
      element.textContent = String(count)
    })
  }

  const subtotal = getPayloadValue(payload, ['subtotal_formatted', 'sub_total_formatted', 'formatted_subtotal', 'subtotal', 'sub_total'])
  const tax = getPayloadValue(payload, ['tax_formatted', 'tax_price_formatted', 'formatted_tax', 'tax_price'])
  let total = getPayloadValue(payload, ['total_formatted', 'final_price_formatted', 'formatted_total', 'total', 'final_price'])
  if (total === undefined && Number.isFinite(Number(subtotal)) && Number.isFinite(Number(tax))) total = Number(subtotal) + Number(tax)

  const updateAmount = (selector, value) => {
    if (value === undefined || value === null) return
    document.querySelectorAll(selector).forEach((element) => {
      const numericValue = typeof value === 'number' || (typeof value === 'string' && Number.isFinite(Number(value)))
      element.textContent = numericValue ? formatAmountLikeCurrent(value, element) : String(value)
    })
  }
  updateAmount('[data-rc-cart-subtotal]', subtotal)
  updateAmount('[data-rc-cart-tax]', tax)
  updateAmount('[data-rc-cart-total]', total)
}

function updateWishlistButtons(productId, action) {
  if (!productId) return
  const saved = action === 'add'
  document.querySelectorAll('.wishbtn-globaly, .wishlist-btn').forEach((button) => {
    if (button.dataset.productId !== String(productId)) return
    button.classList.toggle('active', saved)
    button.setAttribute('aria-pressed', saved ? 'true' : 'false')
    button.setAttribute('in_wishlist', saved ? 'remove' : 'add')
    button.dataset.wishlistAction = saved ? 'remove' : 'add'
  })
}

function applyWishlistPayload(payload) {
  const productId = getPayloadValue(payload, ['product_id', 'productId'])
  const state = getPayloadValue(payload, ['in_wishlist', 'wishlist_action', 'action', 'saved'])
  if (!productId || state === undefined) return
  const saved = state === true || state === 1 || state === '1' || state === 'true' || state === 'add'
  updateWishlistButtons(productId, saved ? 'add' : 'remove')
}

function bindNativeCommerceBridge(root) {
  if (root.dataset.rcCommerceBound !== '1') {
    root.dataset.rcCommerceBound = '1'
    root.addEventListener('click', (event) => {
      if (!(event.target instanceof Element)) return
      const button = event.target.closest('.addcart-btn-globaly[data-product-id], .addtocart-btn[data-product-id], .wishbtn-globaly[data-product-id], .wishlist-btn[data-product-id], .remove_item_from_cart[data-product-id], .change-cart-globaly[data-product-id], .empty_cart')
      if (!button) return

      const kind = button.matches('.wishbtn-globaly, .wishlist-btn') ? 'wishlist' : 'cart'
      const now = Date.now()
      for (let index = pendingCommerceActions.length - 1; index >= 0; index--) {
        if (pendingCommerceActions[index].expiresAt <= now) pendingCommerceActions.splice(index, 1)
      }
      pendingCommerceActions.push({
        kind,
        url: button.dataset.url || '',
        productId: button.dataset.productId,
        variantId: button.dataset.variantId || button.getAttribute('variant_id') || '0',
        quantity: button.dataset.quantity || button.getAttribute('qty') || '1',
        wishlistAction: button.dataset.wishlistAction || button.getAttribute('in_wishlist') || 'add',
        expiresAt: now + 10000,
      })
    }, true)
  }

  if (document.documentElement.dataset.rcCommerceEventsBound === '1') return
  document.documentElement.dataset.rcCommerceEventsBound = '1'
  document.addEventListener('cart:updated', (event) => applyCartPayload(event.detail))
  document.addEventListener('wishlist:updated', (event) => applyWishlistPayload(event.detail))

  const $ = window.jQuery
  if (!$) return
  $(document).on('ajaxSuccess.rcReadyCart', (event, xhr, settings, response) => {
    const requestPath = normalizePath(settings?.url)
    const now = Date.now()
    const actionIndex = pendingCommerceActions.findIndex((action) => {
      if (action.expiresAt <= now) return false
      const matchesPath = action.url && normalizePath(action.url) === requestPath
      const matchesKind = action.kind === 'cart'
        ? /add[._-]?to[._-]?cart|cart[-_]?(?:add|update|change|remove)/i.test(requestPath) || requestPath.toLowerCase().includes('cart')
        : /wish|favorite/i.test(requestPath)
      return matchesPath || matchesKind
    })
    if (actionIndex < 0) return

    const [action] = pendingCommerceActions.splice(actionIndex, 1)
    const payload = parseAjaxPayload(xhr, response)
    if (action.kind === 'wishlist') {
      updateWishlistButtons(action.productId, action.wishlistAction)
      document.dispatchEvent(new CustomEvent('wishlist:updated', { detail: payload }))
      return
    }

    document.dispatchEvent(new CustomEvent('cart:updated', { detail: payload }))
  })
}

function bindCarousels(root) {
  root.querySelectorAll('[data-rc-carousel]').forEach((carousel) => {
    const slides = Array.from(carousel.querySelectorAll('[data-rc-slide]'))
    const dots = Array.from(carousel.querySelectorAll('[data-rc-carousel-dot]'))
    if (slides.length < 2) return

    let activeIndex = Math.max(0, slides.findIndex((slide) => !slide.hidden))
    const show = (nextIndex) => {
      activeIndex = (nextIndex + slides.length) % slides.length
      slides.forEach((slide, index) => {
        slide.hidden = index !== activeIndex
        slide.classList.toggle('is-active', index === activeIndex)
      })
      dots.forEach((dot, index) => dot.setAttribute('aria-pressed', index === activeIndex ? 'true' : 'false'))
    }

    if (carousel.dataset.bound !== '1') {
      carousel.dataset.bound = '1'
      carousel.addEventListener('click', (event) => {
        const next = event.target.closest('[data-rc-carousel-next]')
        const previous = event.target.closest('[data-rc-carousel-prev]')
        const dot = event.target.closest('[data-rc-carousel-dot]')
        if (next) show(activeIndex + 1)
        if (previous) show(activeIndex - 1)
        if (dot) show(Number(dot.dataset.rcCarouselDot) || 0)
      })
    }

    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches && !carousel.dataset.timer) {
      const timer = window.setInterval(() => show(activeIndex + 1), 6000)
      carousel.dataset.timer = String(timer)
    }
  })
}

function bindProductFilters(root) {
  const page = root.querySelector('[data-rc-products]')
  if (!page || page.dataset.bound === '1') return
  page.dataset.bound = '1'

  const results = page.querySelector('.product_filter')
  const range = page.querySelector('#priceRangeSlider')
  const maxInput = page.querySelector('#maxPriceInput')
  const valueLabel = page.querySelector('#sliderValue')
  let requestController
  const filterPanel = page.querySelector('#mobile-filter')
  const filterOpen = window.matchMedia('(max-width: 820px)').matches
  if (filterPanel) {
    filterPanel.setAttribute('aria-hidden', filterOpen ? 'true' : 'false')
    filterPanel.inert = filterOpen
  }
  const currentParams = new URLSearchParams(window.location.search)
  const selectedTags = new Set((currentParams.get('product_tag') || '').split(',').filter(Boolean))
  const selectedBrands = new Set((currentParams.get('product_brand') || '').split(',').filter(Boolean))
  page.querySelectorAll('.product_tag').forEach((input) => { input.checked = selectedTags.has(input.value) })
  page.querySelectorAll('.product_brand').forEach((input) => { input.checked = selectedBrands.has(input.value) })
  const initialFilter = currentParams.get('filter_product')
  if (initialFilter && page.querySelector('#filter_product')) page.querySelector('#filter_product').value = initialFilter
  const initialMaxPrice = currentParams.get('max_price')
  if (initialMaxPrice && range) range.value = initialMaxPrice
  if (initialMaxPrice && maxInput) maxInput.value = initialMaxPrice

  const updateResults = async (requestedPage = 1) => {
    if (!results) return
    requestController?.abort()
    requestController = new AbortController()
    page._rcFilterController = requestController

    const params = new URLSearchParams({
      page: String(requestedPage),
      search_product: currentParams.get('search_product') || page.querySelector('#desktop-search-input')?.value || '',
      product_tag: Array.from(page.querySelectorAll('.product_tag:checked'), (input) => input.value).join(','),
      product_brand: Array.from(page.querySelectorAll('.product_brand:checked'), (input) => input.value).join(','),
      min_price: range?.min || '',
      max_price: range?.value || maxInput?.value || '',
      filter_product: page.querySelector('#filter_product')?.value || 'all',
    })
    if (maxInput && range) maxInput.value = range.value

    const url = new URL(page.dataset.filterUrl, window.location.origin)
    url.search = params.toString()
    window.history.replaceState(window.history.state, '', `${url.pathname}${url.search}`)
    page.setAttribute('aria-busy', 'true')

    try {
      const response = await fetch(url, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        signal: requestController.signal,
      })
      if (!response.ok) throw new Error(`Product filter failed with ${response.status}`)
      const payload = await response.json()
      if (typeof payload.html === 'string') results.innerHTML = payload.html
      const count = page.querySelector('.product_count')
      if (count && payload.product_count !== undefined) count.textContent = payload.product_count
    } catch (error) {
      if (error.name !== 'AbortError') {
        results.innerHTML = '<p class="rc-empty-state" role="status">Products could not be loaded. Please try again.</p>'
      }
    } finally {
      page.removeAttribute('aria-busy')
    }
  }

  page.addEventListener('click', (event) => {
    if (event.target.closest('#product_filter_btn')) {
      event.preventDefault()
      updateResults(1)
      page.classList.remove('filters-open')
      page.querySelector('[data-rc-filter-open]')?.setAttribute('aria-expanded', 'false')
      const panel = page.querySelector('#mobile-filter')
      if (panel) {
        const isMobile = window.matchMedia('(max-width: 820px)').matches
        panel.setAttribute('aria-hidden', isMobile ? 'true' : 'false')
        panel.inert = isMobile
      }
    }

    const paginationLink = event.target.closest('.page-link, [data-page]')
    if (paginationLink) {
      const pageNumber = paginationLink.dataset.page || new URL(paginationLink.href, window.location.origin).searchParams.get('page')
      if (pageNumber) {
        event.preventDefault()
        updateResults(pageNumber)
      }
    }

    if (event.target.closest('[data-rc-filter-open]')) {
      page.classList.add('filters-open')
      page.querySelector('[data-rc-filter-open]')?.setAttribute('aria-expanded', 'true')
      const panel = page.querySelector('#mobile-filter')
      if (panel) {
        panel.setAttribute('aria-hidden', 'false')
        panel.inert = false
      }
    }

    if (event.target.closest('[data-rc-filter-close]')) {
      page.classList.remove('filters-open')
      page.querySelector('[data-rc-filter-open]')?.setAttribute('aria-expanded', 'false')
      const panel = page.querySelector('#mobile-filter')
      if (panel) {
        panel.setAttribute('aria-hidden', 'true')
        panel.inert = true
      }
    }

    const viewButton = event.target.closest('#grid-view-btn, #list-view-btn')
    const productGrid = page.querySelector('#products-container')
    if (viewButton && productGrid) {
      const isList = viewButton.id === 'list-view-btn'
      productGrid.classList.toggle('rc-product-grid--list', isList)
      page.querySelectorAll('.view-toggle').forEach((button) => {
        const active = button === viewButton
        button.classList.toggle('is-active', active)
        button.setAttribute('aria-pressed', active ? 'true' : 'false')
      })
    }
  })

  page.addEventListener('change', (event) => {
    if (event.target.matches('#filter_product, .product_tag, .product_brand')) updateResults(1)
  })

  range?.addEventListener('input', () => {
    if (maxInput) maxInput.value = range.value
    if (valueLabel) valueLabel.textContent = range.value
  })
  range?.addEventListener('change', () => updateResults(1))
  const initialPage = new URLSearchParams(window.location.search).get('page') || 1
  updateResults(initialPage)
}

function sanitizePriceMarkup(markup) {
  const template = document.createElement('template')
  template.innerHTML = markup
  template.content.querySelectorAll('script, style, iframe, object, embed').forEach((element) => element.remove())
  const allowedTags = new Set(['DIV', 'SPAN', 'DEL', 'S', 'INS', 'SMALL', 'STRONG', 'B', 'I', 'EM', 'SUP', 'SUB'])
  template.content.querySelectorAll('*').forEach((element) => {
    if (!allowedTags.has(element.tagName)) {
      element.replaceWith(document.createTextNode(element.textContent || ''))
      return
    }
    Array.from(element.attributes).forEach((attribute) => {
      const name = attribute.name.toLowerCase()
      if (name !== 'class' && name !== 'title' && !name.startsWith('aria-') && !name.startsWith('data-')) {
        element.removeAttribute(attribute.name)
      }
    })
  })
  return template.innerHTML
}

function showVariantGalleryImage(root, source, alt = '') {
  if (!source) return
  let resolvedSource
  try {
    const imageUrl = new URL(source, window.location.origin)
    if (!['http:', 'https:', 'blob:', 'data:'].includes(imageUrl.protocol)) return
    resolvedSource = imageUrl.href
  } catch {
    return
  }
  const gallery = root.querySelector('.rc-product-gallery')
  const galleryMain = gallery?.querySelector('.rc-gallery-main')
  if (!gallery || !galleryMain) return

  let image = Array.from(gallery.querySelectorAll('[data-rc-gallery-image]')).find((item) => item.src === resolvedSource)
  if (!image) {
    image = gallery.querySelector('[data-rc-variant-main]')
    if (!image) {
      image = document.createElement('img')
      image.dataset.rcGalleryImage = ''
      image.dataset.rcVariantMain = ''
      image.loading = 'eager'
      galleryMain.append(image)
    }
    image.src = resolvedSource
  }
  if (alt) image.alt = alt
  Array.from(gallery.querySelectorAll('[data-rc-gallery-image]')).forEach((item) => { item.hidden = item !== image })
  gallery.querySelectorAll('[data-rc-gallery-thumb], [data-rc-variant-thumb], [data-rc-variant-image], [data-variant-image]').forEach((button) => {
    const activeSource = button.dataset.rcGallerySrc || button.dataset.rcVariantImage || button.dataset.variantImage
    const active = activeSource === source || activeSource === resolvedSource
    button.classList.toggle('is-active', active)
    button.setAttribute('aria-pressed', active ? 'true' : 'false')
  })
}

function getSelectedVariantControl(form) {
  return form.querySelector('select[name="variant_id"]')
    || form.querySelector('input[name="variant_id"]:checked')
    || form.querySelector('[data-rc-selected-variant]')
    || form.querySelector('input[name="variant_id"]')
}

function syncVariantControls(form) {
  const selectedVariant = getSelectedVariantControl(form)
  const variantId = selectedVariant?.value || '0'
  const quantity = form.querySelector('[data-rc-quantity], [name="quantity"]')?.value || '1'
  const productId = form.dataset.propProductId
  const detail = form.closest('.rc-product-detail') || document

  detail.querySelectorAll('.addcart-btn-globaly[data-product-id], .addtocart-btn[data-product-id]').forEach((button) => {
    if (button.dataset.productId !== String(productId)) return
    button.dataset.variantId = String(variantId)
    button.setAttribute('variant_id', String(variantId))
    button.dataset.quantity = String(quantity)
    button.setAttribute('qty', String(quantity))
  })
  return { variantId, quantity, productId }
}

function applyVariantResponse(form, payload) {
  const detail = form.closest('.rc-product-detail') || document
  const price = getPayloadValue(payload, ['price_html', 'product_price_html', 'product_price', 'formatted_price', 'price'])
  const priceTarget = detail.querySelector('[data-rc-product-price]')
  if (priceTarget && (typeof price === 'string' || typeof price === 'number')) {
    const renderedPrice = String(price)
    priceTarget.innerHTML = renderedPrice.includes('<') ? sanitizePriceMarkup(renderedPrice) : ''
    if (!renderedPrice.includes('<')) priceTarget.textContent = renderedPrice
  }

  const variantId = getPayloadValue(payload, ['variant_id', 'selected_variant_id'])
  const selectedVariant = getSelectedVariantControl(form)
  if (variantId !== undefined && selectedVariant) selectedVariant.value = String(variantId)

  const stockStatus = getPayloadValue(payload, ['stock_status', 'availability'])
  const stockQuantity = getPayloadValue(payload, ['stock_quantity', 'qty_available'])
  const inStockValue = getPayloadValue(payload, ['in_stock', 'is_available', 'available'])
  let isAvailable
  if (typeof inStockValue === 'boolean') isAvailable = inStockValue
  else if (inStockValue !== undefined) isAvailable = Number(inStockValue) > 0
  else if (stockQuantity !== undefined) isAvailable = Number(stockQuantity) > 0
  else if (typeof stockStatus === 'string') isAvailable = !/out[_ -]?of[_ -]?stock|unavailable/i.test(stockStatus)

  const stockElement = detail.querySelector('[data-rc-stock]')
  const addButton = detail.querySelector('.addcart-btn-globaly[data-product-id]')
  if (isAvailable !== undefined) {
    if (stockElement) {
      stockElement.textContent = isAvailable ? stockElement.dataset.inStockLabel : stockElement.dataset.outOfStockLabel
      stockElement.classList.toggle('is-out', !isAvailable)
    }
    if (addButton) {
      addButton.disabled = !isAvailable
      addButton.setAttribute('aria-disabled', isAvailable ? 'false' : 'true')
    }
  }

  const image = getPayloadValue(payload, ['variant_image', 'image_url', 'image'])
  const imageUrl = typeof image === 'string' ? image : image?.url || image?.image_path
  if (imageUrl) showVariantGalleryImage(detail, imageUrl, detail.querySelector('#rc-product-title')?.textContent || '')
  syncVariantControls(form)
}

async function requestVariantPrice(form) {
  const priceUrl = form.dataset.rcPriceUrl
  if (!priceUrl) return

  form._rcPriceController?.abort()
  const controller = new AbortController()
  form._rcPriceController = controller
  const { variantId, productId } = syncVariantControls(form)
  const body = new FormData(form)
  body.set('product_id', productId || '')
  body.set('variant_id', variantId)
  form.setAttribute('aria-busy', 'true')

  try {
    const response = await fetch(priceUrl, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body,
      signal: controller.signal,
    })
    if (!response.ok) throw new Error(`Variant pricing failed with ${response.status}`)
    const contentType = response.headers.get('content-type') || ''
    const payload = contentType.includes('application/json') ? await response.json() : { price_html: await response.text() }
    applyVariantResponse(form, payload)
  } catch (error) {
    if (error.name !== 'AbortError') form.dispatchEvent(new CustomEvent('variant:price-error', { bubbles: true, detail: { error } }))
  } finally {
    if (form._rcPriceController === controller) {
      delete form._rcPriceController
      form.removeAttribute('aria-busy')
    }
  }
}

function bindProductVariants(root) {
  root.querySelectorAll('form[data-rc-price-url]').forEach((form) => {
    if (form.dataset.rcVariantBound === '1') return
    form.dataset.rcVariantBound = '1'

    form.addEventListener('change', (event) => {
      const control = event.target
      if (!(control instanceof HTMLInputElement || control instanceof HTMLSelectElement)) return
      if (control.name === 'quantity' || control.matches('[type="hidden"]')) return
      if (!(control instanceof HTMLSelectElement) && !['radio', 'checkbox'].includes(control.type)) return

      const selectedOption = control instanceof HTMLSelectElement ? control.selectedOptions[0] : control
      const variantId = selectedOption?.dataset.variantId
      const selectedVariant = getSelectedVariantControl(form)
      if (variantId && selectedVariant) selectedVariant.value = variantId
      const image = selectedOption?.dataset.rcVariantImage || selectedOption?.dataset.variantImage
      if (image) showVariantGalleryImage(root, image, root.querySelector('#rc-product-title')?.textContent || '')
      syncVariantControls(form)
      requestVariantPrice(form)
    })

    form.addEventListener('input', (event) => {
      if (event.target.matches('[data-rc-quantity], [name="quantity"]')) syncVariantControls(form)
    })

    form.addEventListener('click', (event) => {
      if (!(event.target instanceof Element)) return
      if (event.target.closest('.quantity-increment, .quantity-decrement')) {
        window.requestAnimationFrame(() => syncVariantControls(form))
        return
      }
      const thumbnail = event.target.closest('[data-rc-variant-image], [data-variant-image], [data-rc-variant-thumb], [data-variant-id][role="option"], [data-variant-value]')
      if (!thumbnail) return
      const image = thumbnail.dataset.rcVariantImage || thumbnail.dataset.variantImage || thumbnail.querySelector('img')?.currentSrc
      const variantId = thumbnail.dataset.variantId
      const selectedVariant = getSelectedVariantControl(form)
      if (variantId && selectedVariant) selectedVariant.value = variantId
      if (image) showVariantGalleryImage(root, image, root.querySelector('#rc-product-title')?.textContent || '')
      syncVariantControls(form)
      requestVariantPrice(form)
    })
  })
}

function bindProductGallery(root) {
  root.querySelectorAll('.rc-product-gallery').forEach((gallery) => {
    if (gallery.dataset.bound === '1') return
    gallery.dataset.bound = '1'

    const thumbnailSelector = '[data-rc-gallery-thumb], [data-rc-variant-thumb], [data-rc-variant-image], [data-variant-image], [data-variant-id][role="option"], [data-variant-value]'
    const thumbnails = Array.from(gallery.querySelectorAll(thumbnailSelector))
    if (!gallery.querySelector('[data-rc-gallery-image]') || thumbnails.length === 0) return

    gallery.addEventListener('click', (event) => {
      if (!(event.target instanceof Element)) return
      const thumbnail = event.target.closest(thumbnailSelector)
      if (!thumbnail) return

      const detail = gallery.closest('.rc-product-detail') || root
      const form = detail.querySelector('form[data-rc-price-url]')
      const variantId = thumbnail.dataset.variantId
      const variantImage = thumbnail.dataset.rcVariantImage || thumbnail.dataset.variantImage || thumbnail.querySelector('img')?.currentSrc
      if (thumbnail.hasAttribute('data-rc-gallery-thumb')) {
        const selectedIndex = Number(thumbnail.dataset.rcGalleryThumb)
        const images = Array.from(gallery.querySelectorAll('[data-rc-gallery-image]'))
        images.forEach((image, index) => { image.hidden = index !== selectedIndex })
      }
      if (variantImage) showVariantGalleryImage(detail, variantImage, detail.querySelector('#rc-product-title')?.textContent || '')
      thumbnails.forEach((button) => {
        const active = button === thumbnail
        button.classList.toggle('is-active', active)
        button.setAttribute('aria-pressed', active ? 'true' : 'false')
      })

      if (form && variantId) {
        const selectedVariant = getSelectedVariantControl(form)
        if (selectedVariant) selectedVariant.value = variantId
        syncVariantControls(form)
        requestVariantPrice(form)
      }
    })
  })
}

function bindCheckout(root) {
  const checkout = root.querySelector('[data-rc-checkout]')
  if (!checkout || checkout.dataset.bound === '1') return
  checkout.dataset.bound = '1'

  const form = checkout.querySelector('#formdata')
  const paymentType = form?.querySelector('[name="payment_type"]')
  const selectedPayment = form?.querySelector('[name="payment_setting_id"]:checked')
  if (paymentType && selectedPayment) paymentType.value = selectedPayment.value
  form?.addEventListener('change', (event) => {
    const radio = event.target.closest('[name="payment_setting_id"]')
    if (radio && paymentType) paymentType.value = radio.value
  })

  const token = document.querySelector('meta[name="csrf-token"]')?.content || ''
  const addressUrl = checkout.dataset.addressUrl
  const billingTarget = checkout.querySelector('.selling_address_form')
  const shippingTarget = checkout.querySelector('.shipping_address_form')
  const sameAddress = checkout.querySelector('#same-address')

  const loadAddress = async (type, target) => {
    if (!addressUrl || !target) return
    const body = new URLSearchParams({ type })
    try {
      const response = await fetch(addressUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', Accept: 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
        body,
      })
      if (!response.ok) throw new Error(`Address request failed with ${response.status}`)
      const payload = await response.json()
      target.innerHTML = payload.data?.html || ''
    } catch {
      target.innerHTML = '<p class="rc-form-note" role="status">Address fields could not be loaded. Please refresh and try again.</p>'
    }
  }

  loadAddress('billing', billingTarget)
  sameAddress?.addEventListener('change', () => {
    if (sameAddress.checked) loadAddress('delivery', shippingTarget)
    else if (shippingTarget) shippingTarget.innerHTML = ''
  })

  if (sameAddress?.checked) loadAddress('delivery', shippingTarget)

  const notesTarget = checkout.querySelector('.additional_notes')
  const notesUrl = window.additionalnote
  if (notesTarget && notesUrl) {
    fetch(notesUrl, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
      .then((response) => response.ok ? response.json() : Promise.reject(new Error('Additional notes unavailable')))
      .then((payload) => { notesTarget.innerHTML = payload.html_data || '' })
      .catch(() => { notesTarget.innerHTML = '<p class="rc-form-note" role="status">Additional fields could not be loaded.</p>' })
  }
}

function initializePage(body = document.body) {
  bindCarousels(body)
  bindProductFilters(body)
  bindProductGallery(body)
  bindProductVariants(body)
  bindNativeCommerceBridge(body)
  bindCheckout(body)
}

onTurboLoad(initializePage)
onTurboUnload((body) => {
  body.querySelectorAll('[data-rc-carousel][data-timer]').forEach((carousel) => {
    window.clearInterval(Number(carousel.dataset.timer))
    delete carousel.dataset.timer
  })
  body.querySelectorAll('[data-rc-products]').forEach((page) => page._rcFilterController?.abort())
  body.querySelectorAll('form[data-rc-price-url]').forEach((form) => form._rcPriceController?.abort())
})
