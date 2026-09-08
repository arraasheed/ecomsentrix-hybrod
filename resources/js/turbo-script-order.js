// ── Script ordering across a Turbo body swap (CLAUDE-6) ─────────────────────
//
// Restores one guarantee the browser gives on a full page load and Turbo does
// not: an inline script never runs before the external <script src> above it.
//
// ── The bug this fixes ──────────────────────────────────────────────────────
//
// The admin pushes both halves of a DataTable into the body, in this order:
//
//   resources/views/layouts/includes/datatable-js.blade.php
//       <script src=".../datatables.min.js">          ← the library
//   vendor/yajra/.../script.blade.php  (not editable)
//       <script>$(function(){ $("#x").DataTable({…}) })</script>   ← the init
//
// On a cold load the library is parser-blocking, so it has executed by the time
// the inline script runs. Under Turbo it does not:
//
//   1. Turbo attaches the new body; every script in it becomes connected at once
//   2. inline scripts execute synchronously, during that attach
//   3. external scripts are network fetches. Turbo sets async = false, which
//      orders them against each other — not against inline scripts
//
// So yajra's `$(function(){…})` runs first. jQuery's ready has long since
// resolved, so the callback fires immediately, and $.fn.DataTable does not exist
// yet:
//
//   TypeError: $(...).DataTable is not a function
//
// Measured: happens on the first navigation into each library-using page, per
// session. The table then renders as plain HTML with no search, sort or paging —
// only a manual reload fixes it. With ~70 DataTable pages in the admin that is
// the first visit to most screens.
//
// Verified experimentally rather than assumed: an inserted <script src> plus a
// following inline script resolved in the order [inline, external].
//
// ── Why patch jQuery.fn.ready ───────────────────────────────────────────────
//
// Because that is the single funnel. `$(fn)`, `$(document).ready(fn)` and
// `jQuery(fn)` all land there (verified: 2 intercepts for 2 different call
// styles), which covers every init block in the app and in vendor-generated code.
//
// jQuery.holdReady() cannot do this job: it only defers the *first* resolution of
// the ready promise, and by the second page jQuery.isReady is already true.
// Verified: a callback registered under holdReady(true) ran anyway.
//
// So while a Turbo render is in flight and body scripts are still loading, ready
// callbacks are queued here and flushed once those scripts settle. Outside a
// render nothing is touched: the wrapper delegates straight through.
//
// ── Kill-switch ─────────────────────────────────────────────────────────────
//
// window.__TURBO_SCRIPT_ORDER_DISABLED__ = true    (console or layout)
// TURBO_ENABLED=false                              never installs — without body
//                                                  swaps the browser's own
//                                                  ordering already applies

const SETTLE_TIMEOUT_MS = 5000;

let installed = false;
let rendering = false;
let queue = [];
const pending = new Set();
const settleCallbacks = [];
let timer = null;
let flushes = 0;
let queuedTotal = 0;
let discarded = 0;

// Marks scripts as soon as they finish. Registered once, in the capture phase,
// because `load` does not bubble — capture still reaches the target (verified).
// Installed before any body swap, so a script cannot slip through unrecorded.
function trackScriptLoads() {
    const mark = (event) => {
        const element = event.target;
        if (element && element.tagName === 'SCRIPT') element.__turboLoaded = true;
    };

    document.addEventListener('load', mark, true);
    document.addEventListener('error', mark, true);
}

function settled() {
    if (timer) {
        clearTimeout(timer);
        timer = null;
    }

    rendering = false;
    pending.clear();

    const callbacks = queue;
    queue = [];
    flushes++;

    for (const fn of callbacks) {
        try {
            fn(window.jQuery);
        } catch (error) {
            // Same contract as jQuery's own ready queue: one broken initialiser
            // must not stop the others.
            console.error('[turbo-script-order] queued ready callback failed:', error);
        }
    }

    // Callbacks that asked to be told when this page's scripts had settled, rather
    // than going through jQuery's ready queue. Used by turbo-bootstrap's flush.
    const waiters = settleCallbacks.splice(0, settleCallbacks.length);
    for (const fn of waiters) {
        try {
            fn();
        } catch (error) {
            console.error('[turbo-script-order] settle waiter failed:', error);
        }
    }
}

// A queue that never flushed belongs to a body that has been replaced. Dropping it
// is required, not just tidy.
//
// With a warm snapshot cache Turbo renders twice per navigation: the cached preview
// first, then the real response. If the preview's scripts had not settled before the
// response arrived, its queued callbacks were still waiting — and the next flush ran
// them together with the real body's, initialising the same table twice:
//
//   DataTables warning: table id=product-table - Cannot reinitialise DataTable
//
// Observed on the second lap through the admin, once the cache was warm. Same
// page-scoping rule turbo-bootstrap.js applies to onTurboLoad registrations.
function discard() {
    if (timer) {
        clearTimeout(timer);
        timer = null;
    }

    discarded += queue.length;
    queue = [];
    settleCallbacks.length = 0;
    pending.clear();
}

// Public gate for other modules. `fn` runs once this page's body scripts have
// finished loading, or immediately if none are outstanding.
//
// turbo-bootstrap.js uses this for its own flush. Without it, onTurboLoad callbacks
// ran on turbo:load — which Turbo fires as soon as the body is attached, before any
// external body script has loaded. So a page whose init needs a page-specific
// library (repeater.js on product/create, table2excel on customer/index) threw
// "$(...).repeater is not a function" on the first visit, per session.
//
// Waiting is opt-in per page: pages with no outstanding scripts are unaffected, so
// the common case keeps its current timing.
function whenScriptsSettled(fn) {
    if (typeof fn !== 'function') return;

    if (!rendering || window.__TURBO_SCRIPT_ORDER_DISABLED__ === true || collectPending() === 0) {
        fn();
        return;
    }

    settleCallbacks.push(fn);
    if (!timer) timer = setTimeout(settled, SETTLE_TIMEOUT_MS);
}

function watch(element) {
    if (element.__turboLoaded || pending.has(element)) return;

    pending.add(element);

    const done = () => {
        pending.delete(element);
        if (pending.size === 0) settled();
    };

    // Safe to attach now: JavaScript is single-threaded, so no load event can
    // have fired between the check above and this line.
    element.addEventListener('load', done, { once: true });
    element.addEventListener('error', done, { once: true });
}

// Any external script in the body that has not run yet. Scripts below the caller
// count too, which matches the cold-load baseline — there jQuery's ready fires at
// DOMContentLoaded, i.e. after every parser-blocking script in the document.
//
// data-turbo-eval="false" elements are excluded, and that exclusion is load-bearing.
// Turbo does not re-activate them: it hands back the same element parsed from the
// response, which carries the "already started" flag, so attaching it executes
// nothing and fires no load event. Waiting on them meant `pending` never reached
// zero, settled() never ran, and every queued initialiser was dropped at the next
// render — measured as 82 queued, 0 flushed, 78 discarded, and not one DataTable on
// the page.
function collectPending() {
    for (const element of document.body.querySelectorAll('script[src]:not([data-turbo-eval="false"])')) {
        if (!element.__turboLoaded) watch(element);
    }
    return pending.size;
}

function install($) {
    if (installed || !$ || !$.fn || typeof $.fn.ready !== 'function') return;
    installed = true;

    const originalReady = $.fn.ready;

    $.fn.ready = function (fn) {
        if (
            rendering &&
            typeof fn === 'function' &&
            window.__TURBO_SCRIPT_ORDER_DISABLED__ !== true &&
            collectPending() > 0
        ) {
            queue.push(fn);
            queuedTotal++;

            // Never lose a callback: if a script neither loads nor errors (offline,
            // blocked by an extension), run the queue anyway. Late init beats no
            // init.
            if (!timer) timer = setTimeout(settled, SETTLE_TIMEOUT_MS);

            return this;
        }

        return originalReady.call(this, fn);
    };
}

// ── Wiring ──────────────────────────────────────────────────────────────────

if (window.__TURBO_DISABLED__ !== true) {
    trackScriptLoads();

    // The gate opens before the incoming body is attached, and closes when that
    // body's scripts have settled — or at turbo:load, if the page had no external
    // scripts to wait for.
    //
    // Anything still queued at this point came from the body being replaced, so it
    // is dropped: running it against the new DOM is what produced "Cannot
    // reinitialise DataTable" on warm-cache renders.
    document.addEventListener('turbo:before-render', () => {
        discard();
        rendering = true;
    });

    // Close the gate once the incoming body's scripts have settled.
    //
    // collectPending() rather than `pending.size`: this listener runs before the
    // one in turbo-bootstrap.js (import order), so on the first visit to a page
    // nothing had inspected the new body yet — the set was empty, settled() ran
    // immediately, and the flush that followed saw an open gate and did not wait.
    // Measured: "$(...).repeater is not a function" on the first visit to
    // product/create, then clean on every later visit because the library had by
    // then loaded anyway. Populating the set here is what makes the first visit
    // behave like the rest.
    document.addEventListener('turbo:load', () => {
        if (rendering && collectPending() === 0) settled();
    });

    // jQuery lives in the storefront <head> but at the end of the admin <body>, so
    // it may not exist yet. Installing at DOMContentLoaded is early enough either
    // way: nothing is gated on the cold page (the browser orders it correctly), and
    // by the first navigation jQuery is present and now persists across renders —
    // partision/footerlink.blade.php marks it data-turbo-eval="false".
    if (window.jQuery) {
        install(window.jQuery);
    } else if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => install(window.jQuery), { once: true });
    } else {
        install(window.jQuery);
    }
}

// Introspection for the Playwright checks and the browser console.
window.__turboScriptOrder = {
    whenScriptsSettled,
    get stats() {
        return {
            installed,
            rendering,
            pending: pending.size,
            queued: queue.length,
            waiters: settleCallbacks.length,
            queuedTotal,
            flushes,
            discarded,
        };
    },
};

export { install, whenScriptsSettled };
