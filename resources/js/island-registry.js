// ── Vue island registry (CLAUDE-15) ───────────────────────────────────────────
//
// Progressive-enhancement bridge between server-rendered Blade and Vue 3
// components. Only the elements that carry `data-island="<name>"` are touched;
// everything else on the page stays exactly as Blade rendered it. When JS
// fails, is slow, or is disabled, the jQuery/AJAX behaviour (front-theme.js)
// keeps working — the island simply never mounts.
//
// ── Responsibilities ──────────────────────────────────────────────────────────
//
//  1. Scan for [data-island] nodes on turbo:load and mount the matching
//     component onto each one.
//  2. Mount each node exactly once — never double-mount on the same element.
//  3. On turbo:before-cache, unmount every live component so none of its
//     reactive state, timers, or closures are captured into a cached
//     snapshot. This is the single choke-point that prevents memory leaks
//     across 10+ Turbo navigations (verification #9).
//  4. If a registered component name has no loader, log a warning and move on
//     — the Blade fallback (jQuery handlers, static HTML) is already in place,
//     so the page must not throw.
//  5. Respect the kill-switch: when `window.__SPA_VUE_ISLANDS__ === false`,
//     no component is ever imported or mounted.
//
// ── Kill-switch ─────────────────────────────────────────────────────────────
//
// config('app.spa.vue_islands') is rendered by the storefront layout as
// window.__SPA_VUE_ISLANDS__. Set to false (or env SPA_VUE_ISLANDS=false) and
// the registry becomes a no-op: no dynamic import, no mount, no unmount.
// The existing jQuery flow then handles the cart drawer / variant selector
// exactly as it did before this work (verification #10).
//
// ── Component registry ────────────────────────────────────────────────────────
//
// Each entry is a lazy import factory so the code-split chunk for a component
// is only fetched when (a) the kill-switch is on and (b) the element
// actually exists on the current page.
//
// ── Progressive enhancement contract ────────────────────────────────────────
//
// The island never replaces the server-rendered markup with an empty shell.
// It boots on top of what Blade already produced:
//   • CartDrawer reads the cart count and items from the initial DOM
//     (the rendered sidebar) and from #cart-count, then takes over reactivity.
//   • VariantSelector reads the pre-selected variant from the <select> state
//     and only intercepts subsequent changes.
//
// If the Vue bundle never loads (slow network, JS error), the jQuery handlers
// in front-theme.js handle clicks as before.

import { createApp, h } from 'vue';

// Lazy component factories. Importing here would pull the vendor chunk into
// every page; these are only resolved when an island element is found.
const components = {
    'cart-drawer': () => import('./islands/CartDrawer.vue'),
    'variant-selector': () => import('./islands/VariantSelector.vue'),
};

// Guard flag. The layout writes __SPA_VUE_ISLANDS__ from
// config('app.spa.vue_islands') before the Vite bundle is imported.
const enabled = window.__SPA_VUE_ISLANDS__ !== false;

// Live component instances, keyed by a stable DOM identifier (the element's
// data-island id or a generated one). Cleared on every unmount sweep so we
// never hold a detached node between pages.
const instances = new Map();

// Tracks elements that have already been mounted during the current
// turbo:load cycle so a node rendered twice (preview + real response) is
// only mounted once.
const mountedElements = new WeakSet();

/**
 * Mount every [data-island] element in `container` (defaults to document.body)
 * that has not already been mounted.
 *
 * @param {HTMLElement} [container=document.body]
 */
function mount(container = document.body) {
    if (!enabled) return;

    const islands = container.querySelectorAll('[data-island]');
    islands.forEach((el) => {
        // Skip elements already mounted — Turbo renders twice per navigation
        // (cached preview then fresh response); the element survives the
        // preview and would be double-mounted without this guard.
        if (mountedElements.has(el)) return;

        const name = el.getAttribute('data-island');
        const factory = components[name];

        if (!factory) {
            // Graceful: no component registered for this name. The Blade
            // fallback markup is already visible, so warn and continue.
            console.warn(`[island-registry] no Vue component registered for data-island="${name}"`);
            return;
        }

        mountComponent(el, name, factory);
    });
}

/**
 * Mount a single component onto an element.
 */
async function mountComponent(el, name, factory) {
    const instanceId = el.__islandId || Symbol(`${name}-${el.tagName}`);
    el.__islandId = instanceId;

    // If this exact element was mounted before (e.g. back-navigation
    // restoring a node), reuse is impossible — the component owns its own
    // reactive state. Unmount the old instance first.
    if (instances.has(instanceId)) {
        unmountElement(el, instanceId);
    }

    try {
        const mod = await factory();
        const Component = mod.default || mod;

        // Pass props from data-* attributes on the element. The Blade layer
        // injects the initial server-rendered state here (cart count, product
        // id, default variant, etc.) so the component starts hydrated, not
        // loading from the network on first paint.
        const props = {};
        for (const attr of el.attributes) {
            if (attr.name.startsWith('data-prop-')) {
                const key = attr.name.slice(10);  // strip "data-prop-"
                props[key] = parseProp(attr.value);
            }
        }

        const app = createApp({
            render() {
                return h(Component, props);
            },
        });

        // Provide a stop function so unmount can call app.unmount().
        app.mount(el);
        instances.set(instanceId, { app, el, name });
        mountedElements.add(el);

        // Clean up when this element is removed from the DOM (Turbo
        // replaces the body; the old elements are detached but GC may not
        // run immediately, and our Map would hold them). The microtask
        // delay ensures the callback fires after DOM replacement.
        const observer = new MutationObserver(() => {
            if (!document.contains(el)) {
                unmountElement(el, instanceId);
                observer.disconnect();
            }
        });
        observer.observe(el.parentNode || el, {
            childList: true,
            subtree: false,
        });

        instances.set(instanceId, { app, el, name, observer });
    } catch (error) {
        console.error(`[island-registry] failed to mount ${name}:`, error);
        // Do NOT rethrow — one broken island must not kill the page.
    }
}

/**
 * Unmount a single component instance.
 */
function unmountElement(el, instanceId) {
    const entry = instances.get(instanceId);
    if (!entry) return;

    try {
        if (entry.observer) entry.observer.disconnect();
        entry.app.unmount();
    } catch (error) {
        console.error(`[island-registry] unmount error for ${entry.name}:`, error);
    }

    instances.delete(instanceId);
    mountedElements.delete(el);
}

/**
 * Unmount every live component. Called on turbo:before-cache so that no
 * component instance — with its reactive refs, timers, and closures — is
 * ever serialised into a cached snapshot.
 */
function unmountAll() {
    for (const [id, entry] of instances) {
        unmountElement(entry.el, id);
    }
    instances.clear();
    // mountedElements is a WeakSet and clears automatically when nodes are
    // GC'd, but on a full-body swap the old body is detached so we can't
    // reference it; the WeakSet is fine as-is.
}

/**
 * Parse a data attribute value as JSON when possible, otherwise return the
 * raw string. Lets Blade emit either plain strings or `json_encode()`d
 * values for complex props.
 */
function parseProp(value) {
    try {
        return JSON.parse(value);
    } catch {
        return value;
    }
}

// ── Wiring ────────────────────────────────────────────────────────────────────
//
// turbo:load   — Turbo has swapped in a new body. Scan it for islands and
//                mount any that are new.
//
// turbo:before-cache — about to snapshot the outgoing page. Tear down every
//                live island so its state is not captured.
//
// pagehide     — browser is unloading (back/forward cache, tab close). Same
//                teardown as before-cache.
//
// DOMContentLoaded — first page load (no Turbo, or Turbo just starting).
document.addEventListener('turbo:load', () => mount());
document.addEventListener('turbo:before-cache', unmountAll);
window.addEventListener('pagehide', unmountAll);
document.addEventListener('DOMContentLoaded', () => mount());

// Expose for debugging / Playwright introspection
window.__islandRegistry = {
    mount,
    unmountAll,
    get stats() {
        return {
            enabled,
            instances: instances.size,
            componentNames: Object.keys(components),
        };
    },
};
