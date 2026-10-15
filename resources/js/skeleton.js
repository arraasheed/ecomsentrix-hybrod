// ── Skeleton Loader & Selective Prefetch Manager (CLAUDE-13) ────────────────
//
// 1. Skeleton Screen Overlay:
//    - Shows only when navigation takes longer than 150ms (debounce).
//    - Skips cached/preview visits (document.documentElement has data-turbo-preview).
//    - Cleans up before caching (turbo:before-cache) to prevent ghost states.
//
// 2. Selective Prefetch Policy:
//    - Enables prefetch on bottom nav, main navigation, and pagination.
//    - Excludes product listings, cart/checkout mutations, and admin links.

import '../css/skeleton.css';

// ── Kill-switch ─────────────────────────────────────────────────────────────
// Set to false to disable the skeleton overlay and selective prefetch entirely.
// To disable: change `true` to `false` below.
const SKELETON_ENABLED = true;

// ── Prefetch kill-switch (ADMIN-NAV-FIX) ────────────────────────────────────
// Set to false to disable ALL speculative prefetch (storefront nav/bottom-nav
// AND the admin sidebar added by this fix) while keeping the skeleton overlay.
const PREFETCH_ENABLED = true;

let skeletonTimer = null;
let skeletonEl = null;

function createSkeletonOverlay() {
    if (document.getElementById('turbo-skeleton-overlay')) {
        return document.getElementById('turbo-skeleton-overlay');
    }

    const overlay = document.createElement('div');
    overlay.id = 'turbo-skeleton-overlay';
    overlay.className = 'turbo-skeleton-overlay';
    overlay.setAttribute('aria-hidden', 'true');
    overlay.innerHTML = `
        <div class="turbo-skeleton-top"></div>
        <div class="turbo-skeleton-content">
            <div class="turbo-skeleton-banner"></div>
            <div class="turbo-skeleton-grid">
                <div class="turbo-skeleton-card"></div>
                <div class="turbo-skeleton-card"></div>
                <div class="turbo-skeleton-card"></div>
                <div class="turbo-skeleton-card"></div>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);
    return overlay;
}

function showSkeletonDebounced() {
    // Kill-switch: skip entirely if disabled
    if (!SKELETON_ENABLED) return;

    // If Turbo preview (cached snapshot) is active, do not display skeleton
    if (document.documentElement.hasAttribute('data-turbo-preview')) {
        return;
    }

    clearTimeout(skeletonTimer);
    skeletonTimer = setTimeout(() => {
        if (!document.documentElement.hasAttribute('data-turbo-preview')) {
            skeletonEl = createSkeletonOverlay();
            // Force reflow for smooth opacity transition
            void skeletonEl.offsetWidth;
            skeletonEl.classList.add('is-visible');
        }
    }, 150);
}

function hideSkeleton() {
    clearTimeout(skeletonTimer);
    if (skeletonEl) {
        skeletonEl.classList.remove('is-visible');
        setTimeout(() => {
            if (skeletonEl && skeletonEl.parentNode) {
                skeletonEl.parentNode.removeChild(skeletonEl);
                skeletonEl = null;
            }
        }, 160);
    }
}

function cleanupBeforeCache() {
    clearTimeout(skeletonTimer);
    const existing = document.getElementById('turbo-skeleton-overlay');
    if (existing && existing.parentNode) {
        existing.parentNode.removeChild(existing);
    }
    skeletonEl = null;
}

// ── Turbo Event Listeners ───────────────────────────────────────────────────

document.addEventListener('turbo:visit', (event) => {
    // Only show for main application frame visits
    if (!event.detail || event.detail.action !== 'restore') {
        showSkeletonDebounced();
    }
});

document.addEventListener('turbo:render', () => {
    hideSkeleton();
});

document.addEventListener('turbo:load', () => {
    hideSkeleton();
    initSelectivePrefetch();
});

// If a navigation fails (network offline, server error, etc.) the skeleton
// overlay would otherwise be stuck on screen forever — hide it so the user
// can still interact with the page or see the error.
document.addEventListener('turbo:visit-failed', () => {
    hideSkeleton();
});

document.addEventListener('turbo:fetch-request-error', () => {
    hideSkeleton();
});

document.addEventListener('turbo:before-cache', () => {
    cleanupBeforeCache();
});

// ── Selective Prefetch Policy ───────────────────────────────────────────────

function initSelectivePrefetch() {
    // Kill-switch: skip entirely if disabled
    if (!SKELETON_ENABLED || !PREFETCH_ENABLED) return;

    // Excluded href patterns — cart/wishlist/account/checkout/auth routes
    // must never be speculatively prefetched (stateful or dynamic routes).
    const skipPatterns = ['cart', 'wishlist', 'my-account', 'login', 'logout', 'checkout', 'register'];
    const shouldSkipHref = (href) => href && skipPatterns.some((p) => href.includes(p));

    // Enable prefetch on mobile bottom navigation
    const bottomNavLinks = document.querySelectorAll('#mobile-bottom-nav a[href]');
    bottomNavLinks.forEach((link) => {
        if (!link.hasAttribute('data-turbo-prefetch') && !link.hasAttribute('data-turbo-eval')) {
            const href = link.getAttribute('href');
            // Skip stateful/auth routes in the bottom nav
            if (!shouldSkipHref(href)) {
                link.setAttribute('data-turbo-prefetch', 'true');
            }
        }
    });

    // Enable prefetch on primary header navigation
    const mainNavLinks = document.querySelectorAll('.main-nav a[href], .site-header a.nav-link[href]');
    mainNavLinks.forEach((link) => {
        const href = link.getAttribute('href');
        if (href && !href.startsWith('#')) {
            // Skip external, auth, and stateful routes (cart/wishlist/checkout/account)
            if (!shouldSkipHref(href)) {
                link.setAttribute('data-turbo-prefetch', 'true');
            }
        }
    });

    // Enable prefetch on pagination links
    const paginationLinks = document.querySelectorAll('.pagination a[href], [data-pagination] a[href]');
    paginationLinks.forEach((link) => {
        link.setAttribute('data-turbo-prefetch', 'true');
    });

    // ── Admin sidebar prefetch (ADMIN-NAV-FIX) ──────────────────────────────
    // The storefront already prefetched on hover; the admin sidebar did not,
    // so every first click on a menu item paid the full server round-trip.
    // Same trick here: safe GET links get data-turbo-prefetch so the HTML is
    // fetched on hover and the click renders from Turbo's cache instantly.
    //
    // Safety (prefetch is a GET, so anything state-changing must never be
    // prefetched): skip logout/delete-style routes, modal forms
    // (data-ajax-popup / data-bs-toggle), links with inline onclick handlers,
    // external links, and parameterised URLs (can serve stale variants).
    // Parent menu items (dash-hasmenu) carry href="#!" and only toggle the
    // submenu, so the href check drops them.
    const adminSkip = ['logout', 'login', 'register', 'delete', 'destroy', 'remove', 'trash', 'status', 'pos'];
    const isSafeAdminHref = (href) => {
        if (!href) return false;
        if (/^(javascript:|#|mailto:|tel:)/.test(href)) return false;
        try {
            const url = new URL(href, window.location.href);
            if (url.origin !== window.location.origin) return false;
            if (url.search) return false;
            return !adminSkip.some((p) => url.pathname.toLowerCase().includes(p));
        } catch {
            return false;
        }
    };

    const sidebarLinks = document.querySelectorAll('.dash-navbar a.dash-link[href]');
    sidebarLinks.forEach((link) => {
        if (link.hasAttribute('data-turbo-prefetch')) return;
        if (link.hasAttribute('data-ajax-popup') || link.hasAttribute('data-bs-toggle') || link.hasAttribute('data-toggle')) return;
        if (link.getAttribute('onclick')) return;
        if (!isSafeAdminHref(link.getAttribute('href'))) return;
        link.setAttribute('data-turbo-prefetch', 'true');
    });
}

export { showSkeletonDebounced, hideSkeleton };
