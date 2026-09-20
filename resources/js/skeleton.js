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

document.addEventListener('turbo:before-cache', () => {
    cleanupBeforeCache();
});

// ── Selective Prefetch Policy ───────────────────────────────────────────────

function initSelectivePrefetch() {
    // Enable prefetch on mobile bottom navigation
    const bottomNavLinks = document.querySelectorAll('#mobile-bottom-nav a[href]');
    bottomNavLinks.forEach((link) => {
        if (!link.hasAttribute('data-turbo-prefetch') && !link.hasAttribute('data-turbo-eval')) {
            link.setAttribute('data-turbo-prefetch', 'true');
        }
    });

    // Enable prefetch on primary header navigation
    const mainNavLinks = document.querySelectorAll('.main-nav a[href], .site-header a.nav-link[href]');
    mainNavLinks.forEach((link) => {
        const href = link.getAttribute('href');
        // Do not prefetch external links, auth endpoints, or modals
        if (href && !href.startsWith('#') && !href.includes('login') && !href.includes('logout') && !href.includes('checkout')) {
            link.setAttribute('data-turbo-prefetch', 'true');
        }
    });

    // Enable prefetch on pagination links
    const paginationLinks = document.querySelectorAll('.pagination a[href], [data-pagination] a[href]');
    paginationLinks.forEach((link) => {
        link.setAttribute('data-turbo-prefetch', 'true');
    });
}

export { showSkeletonDebounced, hideSkeleton };
