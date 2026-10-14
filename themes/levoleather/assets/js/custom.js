// Guard: initialize tailwind object if it doesn't exist (when legacy CDN is disabled)
window.tailwind = window.tailwind || {};

tailwind.config = {
  theme: {
    extend: {
      fontFamily: {
        sans: ['Roboto', 'sans-serif'],
      },
      colors: {
        orange: {
          500: '#f8796c',
        },
        primary: '#f8796c',
      },
    },
  },
};


// ── Turbo Drive: everything below runs once per page ────────────────────────
//
// CLAUDE-6. This file used to run its 29 top-level `const` declarations in the
// global scope. Turbo re-executes every <script> in the body after a page swap,
// and a second `const searchToggle` in the same scope is a SyntaxError — which
// aborted the rest of the file. Measured after one navigation: 0 of 8 sliders
// initialised, cart drawer, search popup, language dropdown and mobile menu all
// dead. `data-turbo-eval="false"` does not help; the attribute is not preserved
// across the head merge (tested).
//
// The fix is scope, not sequence: one IIFE means the declarations are function
// local, so re-execution is harmless. `boot()` then re-runs the page setup
// against the new DOM.
//
// Kill-switch: none needed. With TURBO_ENABLED=false the else-branch below is
// taken and this behaves exactly as the original DOMContentLoaded version.
//
// ⚠️ greentic and techzonix have their own copy of this file, still unwrapped.
// They are safe today because their layouts do not load Turbo. Whoever enables
// Turbo for those themes has to bring this wrapper along.
(function () {
  function boot() {

  // Was a DOMContentLoaded listener, which fires only on the first page under
  // Turbo. Inlined into boot() so it runs per page. The null guard is new and
  // required: this element only exists on pages with the category bar, and
  // without it every other page threw a TypeError here.
  const categoriesWrap = document.querySelector(".categories-wrap .btn");
  const categoryDropdown = document.querySelector(".category-dropdown");

  if (categoriesWrap && categoryDropdown) {
    categoriesWrap.addEventListener("click", function (e) {
      e.preventDefault();
      categoryDropdown.classList.toggle("open");
    });
  }



// Toggle search popup
const searchToggle = document.getElementById('search-toggle');
const searchPopup = document.getElementById('search-popup-container');
const closeSearch = document.getElementById('close-search');

// Guards added with the IIFE wrapper. boot() is a single function, so an
// unguarded null here would abort every initialiser below it — the same failure
// the wrapper exists to prevent, just with a TypeError instead of a SyntaxError.
if (searchToggle && searchPopup) {
  searchToggle.addEventListener('click', () => {
    searchPopup.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    const input = document.getElementById('search-input');
    if (input) input.focus();
  });
}

if (closeSearch && searchPopup) {
  closeSearch.addEventListener('click', () => {
    searchPopup.classList.add('hidden');
    document.body.style.overflow = '';
  });
}


// Cart Toggle
const cartPanel = document.getElementById('cart-panel');
const cartOverlay = document.getElementById('cart-overlay');
const cartClose = document.getElementById('cart-close');
const continueShopping = document.getElementById('continue-shopping');
const cartToggle = document.getElementById('cart-toggle');

const toggleCart = (show) => {
  if (cartPanel) cartPanel.classList.toggle('open', show);
  if (cartOverlay) cartOverlay.classList.toggle('open', show);
  document.body.style.overflow = show ? 'hidden' : '';
};

// Was `if (cartPanel || cartOverlay || cartToggle)` — an OR guard in front of
// code that dereferences two of the three. Narrowed to what it actually uses.
if (cartToggle) {
  cartToggle.addEventListener('click', () => toggleCart(true));
}

if (cartOverlay) {
  cartOverlay.addEventListener('click', (e) => {
    if (e.target === cartOverlay) toggleCart(false);
  });
}

if (cartClose) {
  cartClose.addEventListener('click', () => toggleCart(false));
}

if (continueShopping) {
  continueShopping.addEventListener('click', () => toggleCart(false));
}
// if (cartPanel && cartOverlay && cartToggle && cartClose && continueShopping) {
//     cartToggle.addEventListener('click', () => toggleCart(true));
//     cartClose.addEventListener('click', () => toggleCart(false));
//     continueShopping.addEventListener('click', () => toggleCart(false));
//     cartOverlay.addEventListener('click', (e) => {
//         if (e.target === cartOverlay) toggleCart(false);
//     });
// }

// Quantity Controls
// document.querySelectorAll('.quantity-increment').forEach(function (button) {
//     button.addEventListener('click', function () {
//         const input = this.parentElement.querySelector('.quantity');
//         let quantity = parseInt(input.value) || 0;
//         input.value = quantity + 1;
//     });
// });

// document.querySelectorAll('.quantity-decrement').forEach(function (button) {
//     button.addEventListener('click', function () {
//         const input = this.parentElement.querySelector('.quantity');
//         let quantity = parseInt(input.value) || 0;
//         if (quantity > 1) {
//             input.value = quantity - 1;
//         }
//     });
// });

const dropdownButtons = document.querySelectorAll("[data-dropdown-toggle]");
dropdownButtons.forEach((button) => {
  const menuId = button.getAttribute("data-dropdown-toggle");
  const menu = document.querySelector(`[data-dropdown-menu='${menuId}']`);

  if (menu) {
    button.addEventListener("click", function (event) {
      event.stopPropagation();

      // Hide all other dropdowns
      document.querySelectorAll("[data-dropdown-menu]").forEach((otherMenu) => {
        if (otherMenu !== menu) {
          otherMenu.classList.add("hidden");
        }
      });

      // Toggle current dropdown
      menu.classList.toggle("hidden");
    });
  }
});

// Close-on-outside-click, lifted out of the forEach above.
//
// It used to add one `document` click listener per dropdown button, each closing
// over its own menu. Those are bound to document, not to the swapped body, so
// Turbo could not clean them up: 4 buttons × 10 navigations = 40 listeners, each
// holding a detached menu node alive. Now it is one listener that reads the DOM
// when it fires, registered once per session — same behaviour, no accumulation.
closeDropdownsOnOutsideClick();

// Mobile Menu
const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
const mobileMenu = document.getElementById('mobile-menu');
const closeMobileMenu = document.getElementById('close-mobile-menu');
const overlay = document.querySelector('.overlay');

// closeMenu was a top-level function declaration; it is now local to boot(),
// which is fine because nothing outside this file calls it (grepped across
// resources/views and themes: zero references).
function closeMenu() {
  if (mobileMenu) {
    mobileMenu.classList.remove('show');
    setTimeout(() => mobileMenu.classList.add('hidden'), 300);
  }
  if (overlay) overlay.classList.remove('open');
  document.body.classList.remove('no-scroll');
}

if (mobileMenuToggle && mobileMenu) {
  mobileMenuToggle.addEventListener('click', () => {
    mobileMenu.classList.remove('hidden');
    if (overlay) overlay.classList.add('open');
    document.body.classList.add('no-scroll');
    setTimeout(() => mobileMenu.classList.add('show'), 10);
  });
}

closeMobileMenu?.addEventListener('click', closeMenu);
overlay?.addEventListener('click', closeMenu);

document.querySelectorAll('.mobile-dropdown-toggle').forEach(toggle => {
  toggle.addEventListener('click', function () {
    const dropdown = this.nextElementSibling;
    const icon = this.querySelector('.fa-chevron-down');
    if (dropdown) dropdown.classList.toggle('show');
    if (icon) icon.classList.toggle('rotate-180');
  });
});

// Product Category Tabs
const tabBtn = document.querySelectorAll(".tab-button");
const tabButtons = document.querySelectorAll(".tab-btn");
const tabContents = document.querySelectorAll(".tab-content");

// Declared here rather than inside the rtl/ltr branches below.
//
// It was `const featuredProductsSlider = new Swiper(...)` inside an if-block,
// so it was block-scoped to that branch and the reference at "tabName === all"
// (~40 lines down) always threw ReferenceError. A pre-existing bug, not one the
// Turbo work introduced, but the IIFE made it easy to fix properly: hoisting the
// binding here is what the tab handler was written to expect.
let featuredProductsSlider = null;
tabButtons.forEach((button) => {
  button.addEventListener("click", () => {
    // Remove active class from all buttons and tab contents
    tabButtons.forEach((btn) =>
      btn.classList.remove("active", "border-[var(--primary-color)]")
    );
    tabButtons.forEach((btn) =>
      btn.classList.add("border-transparent")
    );
    tabContents.forEach((content) => content.classList.add("hidden"));
    tabContents.forEach((content) =>
      content.classList.remove("active")
    );

    // Add active class to clicked button
    button.classList.add("active", "border-[var(--primary-color)]");
    button.classList.remove("border-transparent");

    // Show the corresponding tab content
    const tabName = button.getAttribute("data-tab");
    const tabContent = document.getElementById(`${tabName}-tab`);
    if (tabContent) {
      tabContent.classList.remove("hidden");
      tabContent.classList.add("active");

      // Reinitialize Swiper if this is the "all" tab (which has a Swiper instance)
      if (tabName === "all" && featuredProductsSlider) {
        featuredProductsSlider.update();
      }
    }
  });
});

tabBtn.forEach(button => {
  button.addEventListener('click', () => {
    // Remove 'active' class from all buttons
    tabBtn.forEach(btn => btn.classList.remove('active'));

    // Add 'active' class to the clicked button
    button.classList.add('active');

    // Hide all tab contents
    tabContents.forEach(content => content.classList.add('hidden'));

    // Show the selected tab content
    const tabId = button.getAttribute('data-tab');
    document.getElementById(tabId)?.classList.remove('hidden');
  });
});

// FAQ Toggle
const faqButtons = document.querySelectorAll('.faq-button button');
faqButtons.forEach(button => {
  button.addEventListener('click', () => {
    const content = button.nextElementSibling;
    const icon = button.querySelector('svg');
    content.classList.toggle('hidden');
    icon.innerHTML = content.classList.contains('hidden') ? '<path d="M5 12h14"/><path d="M12 5v14"/>' : '<path d="M5 12h14"/>';
  });
});




// Hero Slider
if (document.documentElement.dir === 'rtl') {
  const heroSlider = new Swiper(".hero-slider", {
    loop: true,

    rtl: true,
    spaceBetween: 10,
    // autoplay: {
    //   delay: 5000,
    //   disableOnInteraction: false,
    // },
    pagination: {
      el: ".hero-slider .swiper-pagination",
      clickable: true,
    },
  });
}
else {
  const heroSlider = new Swiper(".hero-slider", {
    loop: true,
    spaceBetween: 10,
    // autoplay: {
    //   delay: 5000,
    //   disableOnInteraction: false,
    // },
    pagination: {
      el: ".hero-slider .swiper-pagination",
      clickable: true,
    },
  });
}


// Featured Products Slider
if (document.documentElement.dir === 'rtl') {
  featuredProductsSlider = new Swiper(".featured-products", {
    slidesPerView: 1,
    spaceBetween: 15,
    loop: false,
    rtl: true,
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    breakpoints: {
      576: {
        slidesPerView: 2,
      },
      1024: {
        slidesPerView: 3,
      },
      1280: {
        slidesPerView: 4,
      },
    },
  });
} else {
  featuredProductsSlider = new Swiper(".featured-products", {
    slidesPerView: 1,
    spaceBetween: 15,
    loop: false,
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    breakpoints: {
      576: {
        slidesPerView: 2,
      },
      1024: {
        slidesPerView: 3,
      },
      1280: {
        slidesPerView: 4,
      },
    },
  });
}

// Testimonials Slider
if (document.documentElement.dir === 'rtl') {
  const testimonialsSlider = new Swiper(".testimonials-slider", {
    slidesPerView: 1,
    spaceBetween: 10,
    loop: false,
    rtl: true,
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    breakpoints: {
      576: {
        slidesPerView: 2,
      },
      1024: {
        slidesPerView: 3,
      },
    },
  });
} else {
  const testimonialsSlider = new Swiper(".testimonials-slider", {
    slidesPerView: 1,
    spaceBetween: 10,
    loop: false,
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    breakpoints: {
      576: {
        slidesPerView: 2,
      },
      1024: {
        slidesPerView: 3,
      },
    },
  });
}

// blog Slider
if (document.documentElement.dir === 'rtl') {
  const blogslider = new Swiper(".blog-slider", {
    slidesPerView: 1,
    spaceBetween: 10,
    loop: false,
    rtl: true,
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    breakpoints: {
      600: {
        slidesPerView: 2,
      },
      1024: {
        slidesPerView: 3,
      },
      1280: {
        slidesPerView: 4,
      },
    },
  });
} else {
  const blogslider = new Swiper(".blog-slider", {
    slidesPerView: 1,
    spaceBetween: 10,
    loop: false,
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    breakpoints: {
      600: {
        slidesPerView: 2,
      },
      1024: {
        slidesPerView: 3,
      },
      1280: {
        slidesPerView: 4,
      },
    },
  });
}

// partnerSwiper Swiper
if (document.documentElement.dir === 'rtl') {
  const partnerSwiper = new Swiper(".partnerSwiper", {
    slidesPerView: 1,
    spaceBetween: 20,
    loop: false,
    rtl: true,
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    breakpoints: {
      576: {
        slidesPerView: 2,
      },
      768: {
        slidesPerView: 3,
      },
      1024: {
        slidesPerView: 6,
      },
    },
  });
} else {
  const partnerSwiper = new Swiper(".partnerSwiper", {
    slidesPerView: 1,
    spaceBetween: 20,
    loop: false,
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    breakpoints: {
      576: {
        slidesPerView: 2,
      },
      768: {
        slidesPerView: 3,
      },
      1024: {
        slidesPerView: 6,
      },
    },
  });
}

// Product Image Slider + Thumbnails
let sliderThumbnail = null;
let sliderMain = null;

if (document.documentElement.dir === 'rtl') {
  sliderThumbnail = new Swiper(".thumbnail-slider", {
    slidesPerView: 3,
    spaceBetween: 15,
    speed: 500,
    centeredSlides: false,
    centeredSlidesBounds: true,
    watchOverflow: true,
    watchSlidesVisibility: false,
    watchSlidesProgress: false,
    rtl: true,
    breakpoints: {
      640: {
        slidesPerView: 4,
      },
    },
  });
} else {
  sliderThumbnail = new Swiper(".thumbnail-slider", {
    slidesPerView: 3,
    spaceBetween: 15,
    speed: 500,
    centeredSlides: false,
    centeredSlidesBounds: true,
    watchOverflow: true,
    watchSlidesVisibility: false,
    watchSlidesProgress: false,
    breakpoints: {
      640: {
        slidesPerView: 4,
      },
    },
  });
}

if (document.documentElement.dir === 'rtl') {
  sliderMain = new Swiper(".main-image-slider", {
    spaceBetween: 15,
    watchOverflow: true,
    watchSlidesVisibility: true,
    watchSlidesProgress: true,
    rtl: true,
    speed: 500,
    preventInteractionOnTransition: true,
    navigation: {
      nextEl: '.swiper-button-next.pdp-arrow',
      prevEl: '.swiper-button-prev.pdp-arrow',
    },
    thumbs: {
      swiper: sliderThumbnail
    }
  });
} else {
  sliderMain = new Swiper(".main-image-slider", {
    spaceBetween: 15,
    watchOverflow: true,
    watchSlidesVisibility: true,
    watchSlidesProgress: true,
    speed: 500,
    preventInteractionOnTransition: true,
    navigation: {
      nextEl: '.swiper-button-next.pdp-arrow',
      prevEl: '.swiper-button-prev.pdp-arrow',
    },
    thumbs: {
      swiper: sliderThumbnail
    }
  });
}

// team Swiper
if (document.documentElement.dir === 'rtl') {
  const teamSwiper = new Swiper('.team-swiper', {
    slidesPerView: 1,
    spaceBetween: 20,
    loop: false,
    rtl: true,
    speed: 500,
    navigation: {
      nextEl: '.swiper-button-next.team-arrow',
      prevEl: '.swiper-button-prev.team-arrow',
    },
    breakpoints: {
      576: {
        slidesPerView: 2,
      },
      768: {
        slidesPerView: 2,
      },
      1024: {
        slidesPerView: 3,
      },
      1280: {
        slidesPerView: 4,
      },
    }
  });
} else {
  const teamSwiper = new Swiper('.team-swiper', {
    slidesPerView: 1,
    spaceBetween: 20,
    loop: false,
    speed: 500,
    navigation: {
      nextEl: '.swiper-button-next.team-arrow',
      prevEl: '.swiper-button-prev.team-arrow',
    },
    breakpoints: {
      576: {
        slidesPerView: 2,
      },
      768: {
        slidesPerView: 2,
      },
      1024: {
        slidesPerView: 3,
      },
      1280: {
        slidesPerView: 4,
      },
    }
  });
}

// counter js
const counters = document.querySelectorAll('.counter');
counters.forEach(counter => {
  const updateCount = () => {
    const target = +counter.getAttribute('data-target');
    const speed = 500;
    const increment = target / speed;
    let count = +counter.innerText;

    if (count < target) {
      counter.innerText = Math.ceil(count + increment);
      setTimeout(updateCount, 10);
    } else {
      // Final adjustment
      counter.innerText = target >= 1000 ? target.toLocaleString() : target;
      if (counter.dataset.target.endsWith('000')) {
        counter.innerText += '+';
      } else if (counter.dataset.target == '100') {
        counter.innerText += '%';
      } else if (counter.dataset.target == '45') {
        counter.innerText += '+';
      }
    }
  };

  updateCount();
});



// Only add event listeners if both sliders are initialized
if (sliderMain && sliderThumbnail) {
  sliderMain.on('slideChangeTransitionStart', function () {
    sliderThumbnail.slideTo(sliderMain.activeIndex);
  });

  sliderThumbnail.on('transitionStart', function () {
    sliderMain.slideTo(sliderThumbnail.activeIndex);
  });
}


// filter popup
const filterBtn = document.getElementById('filter-icon');
const filterPanel = document.getElementById('mobile-filter');
const filterOverlay = document.getElementById('filter-overlay');
const filterClose = document.getElementById('filter-close');
function openFilter() {
  if (filterPanel && filterOverlay) {
    filterPanel.classList.remove('-translate-x-full');
    filterOverlay.classList.add('open');
    document.body.classList.add('no-scroll');
  }
}
function closeFilter() {
  if (filterPanel && filterOverlay) {
    filterPanel.classList.add('-translate-x-full');
    filterOverlay.classList.remove('open');
    document.body.classList.remove('no-scroll');
  }
}
// Attach event listeners only if elements exist
if (filterBtn) {
  filterBtn.addEventListener('click', openFilter);
}
if (filterClose) {
  filterClose.addEventListener('click', closeFilter);
}
if (filterOverlay) {
  filterOverlay.addEventListener('click', closeFilter);
}
function handleResize() {
  if (window.innerWidth >= 1024) {
    closeFilter(); // Close if resizing to desktop
  }
}

// Was `window.addEventListener('resize', handleResize)` at the top level. Under
// Turbo that binds a new closure per page onto window, which Turbo never clears,
// so ten navigations meant ten handlers each holding a stale filterPanel.
// Registered once per session instead; it re-reads the DOM when it fires.
if (window.onTurboOnce) {
  window.onTurboOnce(function () {
    window.addEventListener('resize', function () {
      // Late lookup on purpose: the closed-over filterPanel belongs to whichever
      // page registered this, and that page is gone.
      const panel = document.getElementById('mobile-filter');
      const filterOverlayNow = document.getElementById('filter-overlay');
      if (window.innerWidth >= 1024 && panel && filterOverlayNow) {
        panel.classList.add('-translate-x-full');
        filterOverlayNow.classList.remove('open');
        document.body.classList.remove('no-scroll');
      }
    });
  }, 'stylique-filter-resize');
} else {
  // Turbo off (TURBO_ENABLED=false), or this file loaded outside the storefront
  // layout: no navigation, so there is nothing to accumulate.
  window.addEventListener('resize', handleResize);
}

  } // ── end boot() ──────────────────────────────────────────────────────────

// Close-on-outside-click for [data-dropdown-toggle] menus. Session-scoped, so
// it survives body swaps without being re-registered — see the call in boot().
function closeDropdownsOnOutsideClick() {
  if (window.__STYLIQUE_DROPDOWN_BOUND__) return;
  window.__STYLIQUE_DROPDOWN_BOUND__ = true;

  document.addEventListener('click', function (event) {
    document.querySelectorAll('[data-dropdown-menu]').forEach((menu) => {
      const id = menu.getAttribute('data-dropdown-menu');
      const button = document.querySelector(`[data-dropdown-toggle='${id}']`);
      const insideMenu = menu.contains(event.target);
      const onButton = button && button.contains(event.target);
      if (!insideMenu && !onButton) menu.classList.add('hidden');
    });
  });
}

// ── Entry point ─────────────────────────────────────────────────────────────
//
// onTurboLoad runs boot() for this page and again after every Turbo navigation,
// never twice for the same page. The stub in layouts/includes/turbo.blade.php
// defines it before any body script runs, so it is available even though the
// Vite module that implements it is deferred.
//
// The fallback keeps this file working anywhere Turbo is not present — the
// greentic and techzonix layouts, and TURBO_ENABLED=false.
if (window.onTurboLoad) {
  window.onTurboLoad(boot);
} else if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
  boot();
}
})();