// ── Turbo Drive bootstrap ────────────────────────────────────────────────────
//
// Kill-switch: `window.__TURBO_DISABLED__ = true` stops Turbo from ever
// starting. The flag is set from .env (TURBO_ENABLED) by the layout, in
// resources/views/layouts/includes/turbo.blade.php. Every helper below keeps
// working with the switch on — a kill-switch that breaks 240 migrated scripts
// is not a kill-switch.
//
// Public API, all on `window` so Blade inline scripts can call it:
//
//   onTurboLoad(fn)       run fn for this page, and after every Turbo
//                         navigation. Never twice for the same page.
//   onTurboOnce(fn, key)  run fn once per browser session, however many times
//                         the surrounding <script> is re-executed.
//   onTurboUnload(fn)     run fn just before this page is cached or replaced.
//   onTurboMount(fn)      alias of onTurboLoad, persistent — island registry.
//   onTurboUnmount(fn)    alias of onTurboUnload, persistent — island registry.
//
// ── Why inline registrations are page-scoped ────────────────────────────────
//
// Turbo re-executes every <script> in the new body after a page swap
// (PageRenderer#activateNewBodyScriptElements). So a blade block saying
//
//     window.onTurboLoad(function () { $('.select2').select2() })
//
// registers itself again on every single page. If those callbacks were kept
// forever, a home → product → home round trip would run the same initialiser
// twice against the same DOM: two select2 widgets, "Cannot reinitialise
// DataTable", doubled add-to-cart clicks. That is the most common way a Turbo
// migration breaks a jQuery app, and the reason this file exists.
//
// Therefore: a registration made from a <script> element lives only for the
// page it was registered on. Turbo re-running the script is what re-registers
// it for the next page. Code from the Vite bundle (a module, so
// `document.currentScript` is null) is persistent and runs on every page. Add
// `data-turbo-persistent` to an inline <script>, or pass
// `{ persistent: true }`, to opt a tag into that behaviour.
//
// ── Page lifecycle ──────────────────────────────────────────────────────────
//
// `pageId` counts rendered pages. It is bumped on turbo:before-render, which
// fires before the incoming body is attached, so that page's inline scripts
// register against the right number. Callbacks are flushed once per pageId by
// whichever fires first — DOMContentLoaded (first load, or Turbo off) or
// turbo:load. Anything registered after that flush runs immediately.

const disabled = window.__TURBO_DISABLED__ === true;

// ── State ───────────────────────────────────────────────────────────────────

let pageId = 0;
let flushedPage = -1; // last pageId whose callbacks were flushed
let cleanedPage = -1; // last pageId whose teardowns ran
let flushing = false;

// One ordered list for load and once callbacks, so registration order survives
// the mix of bundle code and inline page scripts — the same guarantee the
// $(document).ready queue gave the blocks being migrated.
//   scope 'page'  → { fn, pageId }    runs once, only for that page
//   scope 'every' → { fn, lastPage }  runs once per page, forever
//   scope 'once'  → { fn }            runs once, ever
const registry = [];
const teardowns = [];
const onceKeys = new Set();

function trace(...args) {
    if (window.__TURBO_DEBUG__ === true) console.log('[turbo]', ...args);
}

// A throwing callback must not take the rest of the page down with it. Same
// contract jQuery's ready queue gave these scripts before the migration.
function run(fn, label) {
    try {
        fn(document.body);
    } catch (error) {
        console.error(`[turbo] ${label} callback failed:`, error);
    }
}

// ── Scope detection ─────────────────────────────────────────────────────────

function scopeFor(options, scriptElement) {
    if (options && typeof options === 'object') {
        if (options.persistent === true) return 'every';
        if (options.page === true) return 'page';
    }

    // `document.currentScript` is the <script> being executed, and is null
    // inside modules, event handlers and timers.
    const element = scriptElement || document.currentScript;

    if (element && element.tagName === 'SCRIPT') {
        if (element.hasAttribute('data-turbo-persistent')) return 'every';

        // Turbo only re-executes scripts it re-activates. Two kinds are never
        // re-activated, so a page-scoped registration from them would run on
        // the first page and never again:
        //   - <head> scripts whose outerHTML is unchanged (they stay in the
        //     head snapshot, so mergeHead leaves them alone)
        //   - anything marked data-turbo-eval="false"
        if (element.getAttribute('data-turbo-eval') === 'false') return 'every';
        if (document.head && document.head.contains(element)) return 'every';

        return 'page';
    }

    return 'every';
}

// ── Flushing ────────────────────────────────────────────────────────────────

function flush(reason) {
    if (flushedPage === pageId || flushing) return;

    flushing = true;
    flushedPage = pageId;
    trace(`flush page ${pageId} (${reason}) — ${registry.length} registered`);

    // Index loop on purpose: a callback may register another one, and that one
    // has to run in this same flush, like a nested $(document).ready.
    for (let i = 0; i < registry.length; i++) {
        const entry = registry[i];
        if (entry.done) continue;

        if (entry.scope === 'page') {
            entry.done = true;
            // Left over from a snapshot preview that the real response has
            // since replaced. Its DOM is gone; dropping it is the point of
            // page scoping.
            if (entry.pageId !== pageId) continue;
            run(entry.fn, 'onTurboLoad');
        } else if (entry.scope === 'once') {
            entry.done = true;
            run(entry.fn, 'onTurboOnce');
        } else if (entry.lastPage !== pageId) {
            entry.lastPage = pageId;
            run(entry.fn, 'onTurboLoad');
        }
    }

    compact();
    flushing = false;
}

function compact() {
    for (let i = registry.length - 1; i >= 0; i--) {
        if (registry[i].done) registry.splice(i, 1);
    }
}

// Teardown runs newest first: a widget created late is destroyed first.
//
// A page that was never flushed is never torn down. That happens on Turbo's
// *preview* renders: Turbo swaps in a cached snapshot, re-executes its scripts,
// but does not dispatch turbo:load until the real response arrives. Those
// registrations belong to a page that never initialised, so running their
// teardown would call destroy() on a widget that was never created.
function cleanup(reason) {
    if (cleanedPage === pageId) return;

    const initialised = flushedPage === pageId;
    if (initialised) cleanedPage = pageId;
    trace(`cleanup page ${pageId} (${reason}) initialised=${initialised}`);

    for (let i = teardowns.length - 1; i >= 0; i--) {
        const entry = teardowns[i];

        if (entry.scope === 'page') {
            if (entry.pageId !== pageId) continue;
            teardowns.splice(i, 1);
            if (initialised) run(entry.fn, 'onTurboUnload');
        } else if (initialised && entry.lastPage !== pageId) {
            entry.lastPage = pageId;
            run(entry.fn, 'onTurboUnload');
        }
    }
}

// ── Registration ────────────────────────────────────────────────────────────

function register(fn, options, scriptElement) {
    if (typeof fn !== 'function') {
        console.error('[turbo] onTurboLoad expects a function, got', typeof fn);
        return;
    }

    const element = scriptElement || document.currentScript;
    const scope = scopeFor(options, element);

    // A persistent registration made from a <script> tag must be deduped, or it
    // accumulates: Turbo re-executes body scripts on every render (twice per
    // navigation once the snapshot cache is warm — preview, then real response),
    // and each execution would add another copy of the same callback. Ten
    // navigations later the same initialiser runs twenty times per page.
    // `data-turbo-persistent` therefore means "register once, run on every page".
    if (scope === 'every' && element && element.tagName === 'SCRIPT') {
        const identity = 'p:' + keyFor(fn);
        if (onceKeys.has(identity)) {
            trace('duplicate persistent registration skipped', identity);
            return;
        }
        onceKeys.add(identity);
    }

    const entry = scope === 'page' ? { fn, scope, pageId } : { fn, scope, lastPage: -1 };

    registry.push(entry);

    // Registered after this page was already flushed (async chunk, timer,
    // another callback): run now rather than waiting for the next page.
    if (flushedPage === pageId && !flushing && document.readyState !== 'loading') {
        if (entry.scope === 'every') entry.lastPage = pageId;
        else entry.done = true;
        run(fn, 'onTurboLoad');
        compact();
    }
}

// Identity by source text, because re-executing a <script> builds a brand new
// function object every time. Function.prototype.toString returns the literal
// source, so a delegation block living in a shared partial is registered once
// however many pages include it.
//
// Caveat for CLAUDE-6: if a block interpolates page-specific Blade values into
// its source, the text differs per page and each variant registers separately.
// Handlers bound to `document` then stack up. Pass an explicit key —
// onTurboOnce(fn, 'cart-delegation') — for any block where that matters.
function keyFor(fn) {
    const source = String(fn);
    let hash = 5381;
    for (let i = 0; i < source.length; i++) {
        hash = ((hash << 5) + hash + source.charCodeAt(i)) | 0;
    }
    return `${source.length}:${hash}`;
}

function registerOnce(fn, key) {
    if (typeof fn !== 'function') {
        console.error('[turbo] onTurboOnce expects a function, got', typeof fn);
        return;
    }

    const identity = typeof key === 'string' && key ? `k:${key}` : keyFor(fn);
    if (onceKeys.has(identity)) {
        trace('duplicate onTurboOnce skipped', identity);
        return;
    }
    onceKeys.add(identity);

    const entry = { fn, scope: 'once' };
    registry.push(entry);

    if (flushedPage === pageId && !flushing && document.readyState !== 'loading') {
        entry.done = true;
        run(fn, 'onTurboOnce');
        compact();
    }
}
function registerTeardown(fn, options, scriptElement) {
    if (typeof fn !== 'function') {
        console.error('[turbo] onTurboUnload expects a function, got', typeof fn);
        return;
    }

    const element = scriptElement || document.currentScript;
    const scope = scopeFor(options, element);

    // Same dedupe as register(): a persistent teardown coming from a <script>
    // tag would otherwise be added again on every render.
    if (scope === 'every' && element && element.tagName === 'SCRIPT') {
        const identity = 'u:' + keyFor(fn);
        if (onceKeys.has(identity)) return;
        onceKeys.add(identity);
    }

    teardowns.push(scope === 'page' ? { fn, scope, pageId } : { fn, scope, lastPage: -1 });
}

// ── Public API ──────────────────────────────────────────────────────────────

window.onTurboLoad = (fn, options) => register(fn, options, document.currentScript);
window.onTurboOnce = (fn, key) => registerOnce(fn, key);
window.onTurboUnload = (fn, options) => registerTeardown(fn, options, document.currentScript);

// Island registry hooks (CLAUDE-15). Mount handlers receive document.body.
// Unmount runs on turbo:before-cache, so no component instance is ever
// serialised into a cached snapshot or left holding a detached DOM node.
window.onTurboMount = (fn) => register(fn, { persistent: true });
window.onTurboUnmount = (fn) => registerTeardown(fn, { persistent: true });

// Drain the inline stub from the layout: those calls happened while the page was
// still parsing, before this module existed.
function drainStub() {
    const queue = window.__turboQueue;
    if (!Array.isArray(queue)) return;

    const forward = (item) => {
        const [kind, fn, arg, scriptElement] = item;
        if (kind === 'once') registerOnce(fn, arg);
        else if (kind === 'unload') registerTeardown(fn, arg, scriptElement);
        else register(fn, arg, scriptElement);
    };

    queue.splice(0, queue.length).forEach(forward);

    // Any later stub call (there should be none) goes straight through.
    queue.push = (item) => {
        forward(item);
        return 0;
    };
}

// ── Wiring ──────────────────────────────────────────────────────────────────

// Registered even when Turbo is off: they just never fire, and registering them
// unconditionally keeps both code paths identical.
document.addEventListener('turbo:before-render', () => {
    cleanup('before-render'); // still the outgoing page at this point
    pageId += 1;
    trace(`page ${pageId} rendering`);
});

document.addEventListener('turbo:before-cache', () => cleanup('before-cache'));

// turbo:load fires as soon as the incoming body is attached — before any external
// <script src> inside it has loaded. So a page whose onTurboLoad callback needs a
// page-specific library (repeater.js on product/create, table2excel on
// customer/index) used to run too early and throw "$(...).repeater is not a
// function", once per session per page.
//
// turbo-script-order.js knows which body scripts are still outstanding, so the flush
// waits for them when there are any and runs immediately when there are none. The
// optional-chaining guard keeps this file independent: turbo-cleanup and
// turbo-script-order are separate imports, and with only turbo-bootstrap loaded the
// behaviour is exactly what it was before.
document.addEventListener('turbo:load', () => {
    const gate = window.__turboScriptOrder;

    if (gate && typeof gate.whenScriptsSettled === 'function') {
        gate.whenScriptsSettled(() => flush('turbo:load'));
    } else {
        flush('turbo:load');
    }

    // PWA: Register service worker (CLAUDE-12 fix)
    const pwaEnabled = document.documentElement.dataset.pwaEnabled === 'true';
    if (navigator.serviceWorker) {
        if (!pwaEnabled) {
            navigator.serviceWorker.getRegistrations().then(regs => {
                regs.forEach(reg => reg.unregister());
            });
        } else if (!window.__swRegistered) {
            window.__swRegistered = true;
            navigator.serviceWorker
                .register('/sw.js', { scope: '/' })
                .then(reg => {
                    console.log('[PWA] Service Worker registered:', reg);
                    setInterval(() => reg.update(), 60000);
                    reg.addEventListener('updatefound', () => {
                        const newWorker = reg.installing;
                        if (!newWorker) return;
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                console.log('[PWA] New Service Worker available. Page refresh recommended.');
                            }
                        });
                    });
                })
                .catch(err => {
                    window.__swRegistered = false;
                    console.error('[PWA] SW registration failed:', err);
                });
        }
    }
});

window.addEventListener('pagehide', () => cleanup('pagehide'));

// First page. Every trigger below is registered; the flush guard makes all but
// the first a no-op. With Turbo off this is the only path, which is exactly the
// $(document).ready behaviour these callbacks had before.
function scheduleFirstFlush() {
    // Share jQuery's ready queue when it exists, so migrated blocks still run
    // in the same phase as any legacy $(document).ready left in the page.
    if (window.jQuery) window.jQuery(() => flush('jquery-ready'));

    if (document.readyState === 'complete') {
        queueMicrotask(() => flush('already-complete'));
        return;
    }

    document.addEventListener('DOMContentLoaded', () => flush('DOMContentLoaded'), { once: true });
    window.addEventListener('load', () => flush('window-load'), { once: true });
}

// ── Form submission lifecycle ──────────────────────────────────────────────────
//
// Turbo intercepts form submissions and uses fetch(). We need to disable the submit
// button to prevent double-submission, and show visual feedback to the user.
//
// turbo:submit-start fires before the fetch begins.
// turbo:submit-end fires after the response arrives (success or network error).
//
// Validation errors are handled by redirecting with flash messages, which Turbo
// preserves across navigation. The layout's alerts.blade.php is executed by Turbo's
// page swap and shows the toastr messages automatically.

// The submitted form element. Turbo 8 exposes it as
// formSubmission.formElement (older drafts used .form); read defensively so
// a missing property degrades to "no button feedback" instead of a TypeError
// on every Turbo form submit (seen live on customer login).
function submittedForm(event) {
    const submission = event.detail && event.detail.formSubmission;
    if (!submission) return null;
    const form = submission.formElement || submission.form;
    return form && typeof form.querySelector === 'function' ? form : null;
}

document.addEventListener('turbo:submit-start', (event) => {
    const form = submittedForm(event);
    if (!form) return;
    const submitButton = form.querySelector('button[type="submit"]');
    
    if (submitButton) {
        // Store original content so we can restore it
        submitButton.dataset.originalContent = submitButton.innerHTML;
        
        // Disable button and show loading state
        submitButton.disabled = true;
        submitButton.classList.add('disabled', 'opacity-50');
        
        // If button has a spinner or loading indicator, show it
        if (submitButton.querySelector('[data-turbo-indicator]')) {
            submitButton.querySelector('[data-turbo-indicator]').classList.remove('hidden');
        }
        
        trace('form submit started, button disabled');
    }
});

document.addEventListener('turbo:submit-end', (event) => {
    const form = submittedForm(event);
    if (!form) return;
    const submitButton = form.querySelector('button[type="submit"]');
    
    if (submitButton) {
        // Re-enable button
        submitButton.disabled = false;
        submitButton.classList.remove('disabled', 'opacity-50');
        
        // Restore original content
        if (submitButton.dataset.originalContent) {
            submitButton.innerHTML = submitButton.dataset.originalContent;
        }
        
        // Hide loading indicator if present
        if (submitButton.querySelector('[data-turbo-indicator]')) {
            submitButton.querySelector('[data-turbo-indicator]').classList.add('hidden');
        }
        
        trace('form submit ended, button re-enabled');
    }
    
    // Check for network errors or fetch failures
    const submission = event.detail && event.detail.formSubmission;
    if (submission && submission.error) {
        console.error('[turbo] form submission error:', submission.error);
        // Toast or alert can be shown here if needed
    }
});

// CSRF for every non-GET request Turbo makes.
//
// Turbo already sets X-CSRF-Token itself, but only in FormSubmission — so plain
// form posts are covered and turbo-frame requests, Turbo Stream requests and
// anything issued through Turbo.fetch() are not. This closes that gap.
//
// Read from <meta> on every request rather than caching at boot: Turbo swaps the
// head on navigation, so a token captured once would go stale the moment Laravel
// regenerated the session token.
//
// GET is skipped deliberately. Turbo prefetches links on hover, and attaching a
// session token to speculative reads is noise Laravel ignores anyway
// (VerifyCsrfToken only inspects non-idempotent methods).
document.addEventListener('turbo:before-fetch-request', (event) => {
    const options = event.detail && event.detail.fetchOptions;
    if (!options || !options.headers) return;
    if (String(options.method || 'GET').toUpperCase() === 'GET') return;

    const token = document.querySelector('meta[name="csrf-token"]');
    if (token && token.content) {
        options.headers['X-CSRF-TOKEN'] = token.content;
    }
});

// Themeable progress bar. Turbo's own bar is a <style> element and so is this
// override; Turbo's head merge only prunes <link data-turbo-track="dynamic">,
// so both survive navigation.
function installProgressBarStyle() {
    if (document.getElementById('turbo-progress-style')) return;

    const style = document.createElement('style');
    style.id = 'turbo-progress-style';
    style.textContent = '.turbo-progress-bar{height:3px !important;background:linear-gradient(90deg,#f8796c 0%,#ff5252 50%,#e02424 100%) !important;box-shadow:0 0 8px rgba(248,121,108,0.6) !important;z-index:999999 !important}';
    document.head.appendChild(style);
}

// ── Start ───────────────────────────────────────────────────────────────────

// Dynamic import, not static: importing @hotwired/turbo calls start() as a side
// effect, so a static import would defeat the kill-switch. This way the Turbo
// chunk is not even downloaded when TURBO_ENABLED=false.
const ready = disabled
    ? Promise.resolve(null)
    : import('@hotwired/turbo')
          .then((Turbo) => {
              // Sooner than the 500 ms default. Verified on this app: forward
              // navigation is 515-712 ms warm because TTFB alone is 550-730 ms
              // (APP_DEBUG=true + file cache; CLAUDE-9 fixes that). At 500 ms the
              // bar appeared right as the page swapped, which reads as a flicker
              // rather than as feedback.
              Turbo.config.drive.progressBarDelay = 150;
              installProgressBarStyle();
              trace('Turbo Drive started, drive =', Turbo.session.drive);
              return Turbo;
          })
          .catch((error) => {
              // Chunk missing, offline, CSP: the helpers above already ran this
              // page's callbacks on DOMContentLoaded, so the site carries on as
              // a plain multi-page app.
              console.error('[turbo] could not load Turbo Drive, staying on full page loads:', error);
              return null;
          });

drainStub();
scheduleFirstFlush();

// Introspection for the Playwright checks and for the browser console.
window.__turboBootstrap = {
    enabled: !disabled,
    ready,
    get stats() {
        return {
            enabled: !disabled,
            pageId,
            flushedPage,
            cleanedPage,
            pending: registry.length,
            teardowns: teardowns.length,
            onceKeys: onceKeys.size,
        };
    },
};

export { ready };
