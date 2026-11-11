const CART_KEY = 'rc_cart_items_v1';
const WISH_KEY = 'rc_wish_items_v1';
let heroTimer = null;
let toastTimer = null;

function readJson(key, fallback) {
  try {
    const raw = window.localStorage.getItem(key);
    return raw ? JSON.parse(raw) : fallback;
  } catch {
    return fallback;
  }
}

function writeJson(key, value) {
  try {
    window.localStorage.setItem(key, JSON.stringify(value));
  } catch {
    // ignore storage errors
  }
}

function formatMoney(amount) {
  return '$' + Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

export function showToast(message) {
  const el = document.querySelector('[data-rc-toast]');
  if (!el) return;
  el.textContent = message;
  el.classList.remove('-translate-y-3', 'opacity-0');
  el.classList.add('translate-y-0', 'opacity-100');
  if (toastTimer) window.clearTimeout(toastTimer);
  toastTimer = window.setTimeout(() => {
    el.classList.add('-translate-y-3', 'opacity-0');
    el.classList.remove('translate-y-0', 'opacity-100');
  }, 2200);
}

window.rcShowToast = showToast;

function getProductMeta(id) {
  const numId = Number(id);
  const card = document.querySelector(`[data-rc-card][data-product-id="${numId}"], [data-rc-detail-root][data-product-id="${numId}"]`);
  if (card) {
    return {
      id: numId,
      name: card.getAttribute('data-product-name') || 'Product',
      brand: card.getAttribute('data-product-brand') || 'ReadyCart',
      price: parseFloat(card.getAttribute('data-product-price') || '0') || 0,
      image: card.getAttribute('data-product-image') || '/readycart/p-heater.png',
    };
  }
  return null;
}

function renderCartUI() {
  const cart = readJson(CART_KEY, []);
  const totalCount = cart.reduce((sum, item) => sum + (Number(item.qty) || 1), 0);
  const totalPrice = cart.reduce((sum, item) => sum + (Number(item.price) || 0) * (Number(item.qty) || 1), 0);

  document.querySelectorAll('[data-rc-cart-count]').forEach((el) => {
    el.textContent = String(totalCount);
  });
  document.querySelectorAll('[data-rc-cart-total], [data-rc-cart-subtotal], [data-rc-checkout-subtotal], [data-rc-checkout-total]').forEach((el) => {
    el.textContent = formatMoney(totalPrice);
  });
  document.querySelectorAll('[data-rc-checkout-count]').forEach((el) => {
    el.textContent = String(cart.length);
  });

  const body = document.querySelector('[data-rc-cart-body]');
  if (body) {
    if (cart.length === 0) {
      body.innerHTML = '<p class="py-16 text-center text-sm text-ink/55">Your cart is empty.</p>';
    } else {
      body.innerHTML = cart
        .map(
          (line) => `
        <div class="flex gap-3 rounded-md border border-line p-3">
          <div class="size-20 shrink-0 overflow-hidden rounded-lg bg-surface">
            <img src="${line.image}" alt="" class="size-full object-contain p-1.5 mix-blend-multiply" />
          </div>
          <div class="flex min-w-0 flex-1 flex-col">
            <p class="line-clamp-1 text-sm font-semibold">${line.name}</p>
            <p class="text-sm font-bold text-brand">${formatMoney(line.price)}</p>
            <div class="mt-auto flex items-center justify-between">
              <div class="flex items-center rounded-lg border border-line">
                <button type="button" data-rc-line-qty="${line.id}" data-delta="-1" class="flex size-8 items-center justify-center hover:text-brand">-</button>
                <span class="w-6 text-center text-sm font-semibold">${line.qty}</span>
                <button type="button" data-rc-line-qty="${line.id}" data-delta="1" class="flex size-8 items-center justify-center hover:text-brand">+</button>
              </div>
              <button type="button" data-rc-line-remove="${line.id}" class="text-ink/40 transition hover:text-brand" aria-label="Remove item">
                <i class="fas fa-trash-alt text-xs"></i>
              </button>
            </div>
          </div>
        </div>`
        )
        .join('');
    }
  }

  const checkoutList = document.querySelector('[data-rc-checkout-items]');
  if (checkoutList && cart.length > 0) {
    checkoutList.innerHTML = cart
      .map(
        (line) => `
      <li class="flex items-center gap-4 py-4">
        <div class="size-16 shrink-0 overflow-hidden rounded-md bg-surface">
          <img src="${line.image}" alt="" class="size-full object-contain p-1.5 mix-blend-multiply" />
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-xs font-bold uppercase tracking-wider text-brand">${line.brand || 'ReadyCart'}</p>
          <p class="truncate text-sm font-semibold">${line.name}</p>
        </div>
        <div class="flex items-center rounded-lg border border-line">
          <button type="button" data-rc-line-qty="${line.id}" data-delta="-1" class="flex size-8 items-center justify-center hover:text-brand">-</button>
          <span class="w-7 text-center text-sm font-bold">${line.qty}</span>
          <button type="button" data-rc-line-qty="${line.id}" data-delta="1" class="flex size-8 items-center justify-center hover:text-brand">+</button>
        </div>
        <span class="w-20 text-right text-sm font-extrabold">${formatMoney(line.price * line.qty)}</span>
      </li>`
      )
      .join('');
  }
}

function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function getStoreSlug() {
  const bodySlug = document.querySelector('[data-slug]')?.getAttribute('data-slug');
  if (bodySlug) return bodySlug;
  const pathParts = window.location.pathname.split('/').filter(Boolean);
  return pathParts.length > 0 ? pathParts[0] : 'xyz';
}

function addItemToCart(id, qty = 1, callback = null) {
  const numId = Number(id);
  const cart = readJson(CART_KEY, []);
  const existing = cart.find((item) => Number(item.id) === numId);
  if (existing) {
    existing.qty = Math.min(10, (Number(existing.qty) || 1) + qty);
  } else {
    const meta = getProductMeta(numId) || {
      id: numId,
      name: 'Product',
      brand: 'Store',
      price: 0,
      image: '/readycart/p-heater.png',
    };
    cart.push({ ...meta, qty });
  }
  writeJson(CART_KEY, cart);
  renderCartUI();

  // Sync to Laravel server-side cart
  const token = getCsrfToken();
  const slug = getStoreSlug();
  if (numId > 0 && token) {
    fetch(`/${slug}/product_cart`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        product_id: numId,
        qty: qty,
        variant_id: 0
      })
    })
    .then(r => r.json())
    .then(res => {
      if (typeof callback === 'function') callback(res);
    })
    .catch(err => {
      console.warn('Server cart sync:', err);
      if (typeof callback === 'function') callback(null);
    });
  } else if (typeof callback === 'function') {
    callback(null);
  }
}

function renderWishUI() {
  const wish = readJson(WISH_KEY, []);
  document.querySelectorAll('[data-rc-wishlist-count]').forEach((el) => {
    el.textContent = String(wish.length);
  });
  document.querySelectorAll('[data-rc-wish-toggle]').forEach((btn) => {
    const id = Number(btn.getAttribute('data-rc-wish-toggle'));
    const active = wish.includes(id);
    btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    const svg = btn.querySelector('svg');
    if (svg) {
      svg.classList.toggle('fill-brand', active);
      svg.classList.toggle('text-brand', active);
    }
  });
}

function toggleCartDrawer(open) {
  const drawer = document.querySelector('[data-rc-cart-drawer]');
  const backdrop = document.querySelector('[data-rc-cart-backdrop]');
  if (!drawer || !backdrop) return;
  drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
  drawer.classList.toggle('translate-x-full', !open);
  drawer.classList.toggle('translate-x-0', open);
  backdrop.classList.toggle('invisible', !open);
  backdrop.classList.toggle('opacity-0', !open);
  backdrop.classList.toggle('visible', open);
  backdrop.classList.toggle('opacity-100', open);
}

function toggleCatsDrawer(open) {
  const drawer = document.querySelector('[data-rc-cats-drawer]');
  const backdrop = document.querySelector('[data-rc-cats-backdrop]');
  if (!drawer || !backdrop) return;
  drawer.classList.toggle('-translate-x-full', !open);
  drawer.classList.toggle('translate-x-0', open);
  backdrop.classList.toggle('pointer-events-none', !open);
  backdrop.classList.toggle('opacity-0', !open);
  backdrop.classList.toggle('opacity-100', open);
}

function toggleFilterDrawer(open) {
  const panel = document.querySelector('[data-rc-filter-panel]');
  const backdrop = document.querySelector('[data-rc-filter-backdrop]');
  if (!panel || !backdrop) return;
  panel.classList.toggle('-translate-x-full', !open);
  panel.classList.toggle('translate-x-0', open);
  backdrop.classList.toggle('pointer-events-none', !open);
  backdrop.classList.toggle('opacity-0', !open);
  backdrop.classList.toggle('opacity-100', open);
}

function initHeroSlider() {
  if (heroTimer) {
    window.clearInterval(heroTimer);
    heroTimer = null;
  }
  const hero = document.querySelector('[data-rc-hero]');
  if (!hero) return;
  const slides = Array.from(hero.querySelectorAll('[data-rc-slide]'));
  const dots = Array.from(hero.querySelectorAll('[data-rc-hero-dot]'));
  if (slides.length <= 1) return;

  let index = 0;
  let dragStartX = null;

  const show = (nextIndex) => {
    index = (nextIndex + slides.length) % slides.length;
    slides.forEach((slide, idx) => {
      const active = idx === index;
      slide.setAttribute('aria-hidden', active ? 'false' : 'true');
      slide.classList.toggle('opacity-100', active);
      slide.classList.toggle('z-[1]', active);
      slide.classList.toggle('opacity-0', !active);
      slide.classList.toggle('pointer-events-none', !active);
      slide.classList.toggle('z-0', !active);
    });
    dots.forEach((dot, idx) => {
      const active = idx === index;
      dot.setAttribute('aria-selected', active ? 'true' : 'false');
      dot.classList.toggle('w-6', active);
      dot.classList.toggle('bg-brand', active);
      dot.classList.toggle('w-1.5', !active);
      dot.classList.toggle('bg-white/70', !active);
    });
  };

  hero.querySelector('[data-rc-hero-prev]')?.addEventListener('click', () => show(index - 1));
  hero.querySelector('[data-rc-hero-next]')?.addEventListener('click', () => show(index + 1));
  dots.forEach((dot, idx) => dot.addEventListener('click', () => show(idx)));

  hero.addEventListener('pointerdown', (e) => {
    dragStartX = e.clientX;
  });
  hero.addEventListener('pointerup', (e) => {
    if (dragStartX === null) return;
    const dist = e.clientX - dragStartX;
    dragStartX = null;
    if (Math.abs(dist) > 48) {
      show(dist > 0 ? index - 1 : index + 1);
    }
  });

  heroTimer = window.setInterval(() => show(index + 1), 5500);
}

function applyProductsPageFilters() {
  const page = document.querySelector('[data-rc-products-page]');
  if (!page) return;
  const grid = page.querySelector('[data-rc-products-grid]');
  if (!grid) return;

  const minVal = parseFloat(page.querySelector('[data-rc-price-min]')?.value || '0') || 0;
  const maxVal = parseFloat(page.querySelector('[data-rc-price-max]')?.value || '99999') || 99999;
  const sort = page.querySelector('[data-rc-sort]')?.value || 'default';
  const checkedCats = Array.from(page.querySelectorAll('[data-rc-filter-cat]:checked')).map((el) => el.value.toLowerCase());
  const checkedBrands = Array.from(page.querySelectorAll('[data-rc-filter-brand]:checked')).map((el) => el.value.toLowerCase());

  const cards = Array.from(grid.querySelectorAll('[data-rc-card]'));
  let visibleCount = 0;

  cards.forEach((card) => {
    const price = parseFloat(card.getAttribute('data-product-price') || '0') || 0;
    const cat = (card.getAttribute('data-product-category') || '').toLowerCase();
    const brand = (card.getAttribute('data-product-brand') || '').toLowerCase();
    const okPrice = price >= minVal && price <= maxVal;
    const okCat = checkedCats.length === 0 || checkedCats.includes(cat);
    const okBrand = checkedBrands.length === 0 || checkedBrands.includes(brand);
    const show = okPrice && okCat && okBrand;
    card.classList.toggle('hidden', !show);
    if (show) visibleCount++;
  });

  if (sort === 'low' || sort === 'high') {
    cards.sort((a, b) => {
      const pa = parseFloat(a.getAttribute('data-product-price') || '0') || 0;
      const pb = parseFloat(b.getAttribute('data-product-price') || '0') || 0;
      return sort === 'low' ? pa - pb : pb - pa;
    });
    cards.forEach((c) => grid.appendChild(c));
  }

  const countEl = page.querySelector('[data-rc-visible-count]');
  if (countEl) countEl.textContent = String(visibleCount);
}

function bootReadyCommerce() {
  initHeroSlider();
  renderCartUI();
  renderWishUI();
}

document.addEventListener('click', (event) => {
  const loadMore = event.target.closest('[data-rc-load-more]');
  if (loadMore) {
    document.querySelectorAll('[data-rc-extra-product]').forEach((el) => el.classList.remove('hidden'));
    loadMore.closest('[data-rc-load-more-wrap]')?.classList.add('hidden');
    return;
  }

  const addCartBtn = event.target.closest('[data-rc-add-cart]');
  if (addCartBtn) {
    const id = addCartBtn.getAttribute('data-rc-add-cart');
    const detailQtyEl = document.querySelector('[data-rc-detail-qty-val]');
    const qty = addCartBtn.closest('[data-rc-detail-root]') && detailQtyEl ? parseInt(detailQtyEl.textContent || '1', 10) || 1 : 1;
    addItemToCart(id, qty);
    showToast('Added to cart');
    return;
  }

  const buyNowBtn = event.target.closest('[data-rc-buy-now]');
  if (buyNowBtn) {
    const id = buyNowBtn.getAttribute('data-rc-buy-now');
    const detailQtyEl = document.querySelector('[data-rc-detail-qty-val]');
    const qty = buyNowBtn.closest('[data-rc-detail-root]') && detailQtyEl ? parseInt(detailQtyEl.textContent || '1', 10) || 1 : 1;
    const url = buyNowBtn.getAttribute('data-checkout-url');
    addItemToCart(id, qty, () => {
      if (url) window.location.href = url;
    });
    return;
  }

  const wishBtn = event.target.closest('[data-rc-wish-toggle]');
  if (wishBtn) {
    const id = Number(wishBtn.getAttribute('data-rc-wish-toggle'));
    const wish = readJson(WISH_KEY, []);
    const idx = wish.indexOf(id);
    if (idx >= 0) {
      wish.splice(idx, 1);
      showToast('Removed from wishlist');
    } else {
      wish.push(id);
      showToast('Added to wishlist');
    }
    writeJson(WISH_KEY, wish);
    renderWishUI();
    return;
  }

  if (event.target.closest('[data-rc-wishlist-header]')) {
    const wish = readJson(WISH_KEY, []);
    showToast(`${wish.length} item(s) in your wishlist`);
    return;
  }

  if (event.target.closest('[data-rc-cart-open]')) {
    renderCartUI();
    toggleCartDrawer(true);
    return;
  }

  if (event.target.closest('[data-rc-cart-close]') || event.target.closest('[data-rc-cart-backdrop]')) {
    toggleCartDrawer(false);
    return;
  }

  if (event.target.closest('[data-rc-cats-open]')) {
    toggleCatsDrawer(true);
    return;
  }

  if (event.target.closest('[data-rc-cats-close]') || event.target.closest('[data-rc-cats-backdrop]')) {
    toggleCatsDrawer(false);
    return;
  }

  if (event.target.closest('[data-rc-filter-open]')) {
    toggleFilterDrawer(true);
    return;
  }

  if (event.target.closest('[data-rc-filter-close]') || event.target.closest('[data-rc-filter-backdrop]')) {
    toggleFilterDrawer(false);
    return;
  }

  const lineQtyBtn = event.target.closest('[data-rc-line-qty]');
  if (lineQtyBtn) {
    const id = Number(lineQtyBtn.getAttribute('data-rc-line-qty'));
    const delta = Number(lineQtyBtn.getAttribute('data-delta') || '0');
    const cart = readJson(CART_KEY, []);
    const item = cart.find((x) => Number(x.id) === id);
    if (item) {
      item.qty = Math.max(1, Math.min(10, (Number(item.qty) || 1) + delta));
      writeJson(CART_KEY, cart);
      renderCartUI();
    }
    return;
  }

  const lineRemoveBtn = event.target.closest('[data-rc-line-remove]');
  if (lineRemoveBtn) {
    const id = Number(lineRemoveBtn.getAttribute('data-rc-line-remove'));
    const cart = readJson(CART_KEY, []).filter((x) => Number(x.id) !== id);
    writeJson(CART_KEY, cart);
    renderCartUI();
    return;
  }

  const thumbBtn = event.target.closest('[data-rc-detail-thumb]');
  if (thumbBtn) {
    const transform = thumbBtn.getAttribute('data-rc-detail-thumb') || 'scale-100';
    const mainImg = document.querySelector('[data-rc-detail-main-img]');
    if (mainImg) {
      mainImg.classList.remove('scale-100', 'scale-150', 'origin-top-right', 'origin-bottom-left');
      transform.split(' ').forEach((cls) => mainImg.classList.add(cls));
    }
    document.querySelectorAll('[data-rc-detail-thumb]').forEach((btn) => {
      const active = btn === thumbBtn;
      btn.setAttribute('aria-pressed', active ? 'true' : 'false');
      btn.classList.toggle('border-brand', active);
      btn.classList.toggle('border-transparent', !active);
    });
    return;
  }

  const colorBtn = event.target.closest('[data-rc-color-btn]');
  if (colorBtn) {
    const name = colorBtn.getAttribute('data-rc-color-btn') || '';
    const label = document.querySelector('[data-rc-color-label]');
    if (label) label.textContent = name;
    document.querySelectorAll('[data-rc-color-btn]').forEach((btn) => {
      const active = btn === colorBtn;
      btn.setAttribute('aria-checked', active ? 'true' : 'false');
      btn.classList.toggle('ring-2', active);
      btn.classList.toggle('ring-brand', active);
    });
    return;
  }

  const sizeBtn = event.target.closest('[data-rc-size-btn]');
  if (sizeBtn) {
    document.querySelectorAll('[data-rc-size-btn]').forEach((btn) => {
      const active = btn === sizeBtn;
      btn.setAttribute('aria-checked', active ? 'true' : 'false');
      btn.classList.toggle('border-brand', active);
      btn.classList.toggle('bg-brand-soft', active);
      btn.classList.toggle('text-brand', active);
      btn.classList.toggle('border-line', !active);
    });
    return;
  }

  const detailQtyBtn = event.target.closest('[data-rc-detail-qty]');
  if (detailQtyBtn) {
    const delta = parseInt(detailQtyBtn.getAttribute('data-rc-detail-qty') || '0', 10);
    const valEl = document.querySelector('[data-rc-detail-qty-val]');
    if (valEl) {
      const current = parseInt(valEl.textContent || '1', 10) || 1;
      valEl.textContent = String(Math.max(1, Math.min(10, current + delta)));
    }
    return;
  }

  const tabBtn = event.target.closest('[data-rc-tab]');
  if (tabBtn) {
    const key = tabBtn.getAttribute('data-rc-tab');
    document.querySelectorAll('[data-rc-tab]').forEach((btn) => {
      const active = btn === tabBtn;
      btn.setAttribute('aria-selected', active ? 'true' : 'false');
      btn.classList.toggle('border-brand', active);
      btn.classList.toggle('text-brand', active);
      btn.classList.toggle('border-transparent', !active);
      btn.classList.toggle('text-ink/55', !active);
    });
    document.querySelectorAll('[data-rc-tab-panel]').forEach((panel) => {
      panel.classList.toggle('hidden', panel.getAttribute('data-rc-tab-panel') !== key);
    });
    return;
  }

  const viewModeBtn = event.target.closest('[data-rc-view-mode]');
  if (viewModeBtn) {
    const mode = viewModeBtn.getAttribute('data-rc-view-mode');
    const grid = document.querySelector('[data-rc-products-grid]');
    if (grid) {
      grid.classList.toggle('grid-cols-1', mode === 'list');
      grid.classList.toggle('grid-cols-2', mode === 'grid');
      grid.classList.toggle('md:grid-cols-3', mode === 'grid');
      grid.classList.toggle('lg:grid-cols-4', mode === 'grid');
    }
    document.querySelectorAll('[data-rc-view-mode]').forEach((btn) => {
      const active = btn === viewModeBtn;
      btn.setAttribute('aria-pressed', active ? 'true' : 'false');
      btn.classList.toggle('bg-white', active);
      btn.classList.toggle('text-brand', active);
      btn.classList.toggle('shadow-sm', active);
      btn.classList.toggle('text-ink/50', !active);
    });
    return;
  }

  const switchBtn = event.target.closest('[data-rc-switch]');
  if (switchBtn) {
    const checked = switchBtn.getAttribute('aria-checked') === 'true';
    const next = !checked;
    switchBtn.setAttribute('aria-checked', next ? 'true' : 'false');
    switchBtn.classList.toggle('bg-brand', next);
    switchBtn.classList.toggle('bg-ink/15', !next);
    switchBtn.querySelector('span')?.classList.toggle('translate-x-5', next);
    return;
  }

  if (event.target.closest('[data-rc-price-apply]')) {
    applyProductsPageFilters();
    return;
  }

  if (event.target.closest('[data-rc-filter-clear]')) {
    const page = document.querySelector('[data-rc-products-page]');
    if (page) {
      const minIn = page.querySelector('[data-rc-price-min]');
      const maxIn = page.querySelector('[data-rc-price-max]');
      const sortSel = page.querySelector('[data-rc-sort]');
      if (minIn) minIn.value = '0';
      if (maxIn) maxIn.value = '150';
      if (sortSel) sortSel.value = 'default';
      page.querySelectorAll('[data-rc-filter-cat], [data-rc-filter-brand]').forEach((cb) => {
        cb.checked = false;
      });
      applyProductsPageFilters();
    }
  }
});

document.addEventListener('change', (event) => {
  if (event.target.matches('[data-rc-filter-cat], [data-rc-filter-brand], [data-rc-sort]')) {
    applyProductsPageFilters();
  }
  if (event.target.matches('input[name="tag"]')) {
    document.querySelectorAll('[data-rc-tag-label]').forEach((lbl) => {
      const checked = lbl.querySelector('input')?.checked;
      lbl.classList.toggle('border-brand', !!checked);
      lbl.classList.toggle('bg-brand-soft', !!checked);
      lbl.classList.toggle('text-brand', !!checked);
      lbl.classList.toggle('border-line', !checked);
      lbl.classList.toggle('text-ink/65', !checked);
    });
  }
  if (event.target.matches('input[name="payment"]')) {
    document.querySelectorAll('[data-rc-pay-label]').forEach((lbl) => {
      const checked = lbl.querySelector('input')?.checked;
      lbl.classList.toggle('border-brand', !!checked);
      lbl.classList.toggle('bg-brand-soft', !!checked);
      lbl.classList.toggle('border-line', !checked);
    });
  }
});

document.addEventListener('submit', (event) => {
  const checkoutForm = event.target.closest('#rc-order-form, [data-rc-checkout-form]');
  if (checkoutForm) {
    // Clear client-side cart mirror so returning back shows clean state
    writeJson(CART_KEY, []);
    renderCartUI();
  }
});

document.addEventListener('DOMContentLoaded', bootReadyCommerce);
document.addEventListener('turbo:load', bootReadyCommerce);
