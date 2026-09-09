// ── Turbo Drive plugin teardown (CLAUDE-6) ──────────────────────────────────
//
// Turbo keeps a snapshot of every page it leaves and re-executes the body
// scripts of every page it renders. Both halves of that deal break jQuery
// plugins:
//
//   1. The snapshot is a clone of the live DOM, so it captures whatever the
//      plugin built — a DataTables wrapper, a select2 container, an ApexCharts
//      <svg>. Come back to that page and the markup is already "initialised",
//      but the JavaScript instance that owned it is gone.
//   2. The page's inline scripts then run again against that markup. jQuery
//      plugins are not idempotent: DataTables throws "Cannot reinitialise
//      DataTable", ApexCharts appends a second chart, select2 leaves a dead
//      dropdown behind.
//
// So the page has to be handed back to the browser in the state the scripts
// expect to find it in: plain HTML. That is what this file does.
//
// ── Why two events ──────────────────────────────────────────────────────────
//
// turbo:before-cache — fires before Turbo clones the outgoing page. Cleaning
//   here is what makes the *cached snapshot* clean, which is the whole point.
//
// turbo:before-render — fires before each render, while the outgoing DOM is
//   still attached. Needed because a warm cache renders twice per navigation
//   (cached preview first, real response second) and before-cache only fires
//   once, on the first of the two. Verified in CLAUDE-5's testing: pageId
//   advances 0→1→3→4→6, two renders per visit.
//
// Every step is idempotent, so running on both events costs nothing. Steps run
// in dependency order, and each is wrapped so that a missing library or a
// plugin that throws mid-teardown cannot stop the rest.
//
// ── Kill-switch ─────────────────────────────────────────────────────────────
//
// TURBO_ENABLED=false in .env disables this file implicitly: neither event ever
// fires. To keep Turbo but stop the teardown (to reproduce a bug, say), set
// window.__TURBO_CLEANUP_DISABLED__ = true — from the console, or from the
// layout before the bundle loads.

const steps = [];
let ran = 0;
const failures = [];

function step(name, fn) {
    steps.push({ name, fn });
}

// jQuery is loaded by the layouts as a plain <script src>, not bundled here, so
// it is read at call time rather than captured at module scope.
function jq() {
    return window.jQuery;
}

// ── 1. DataTables ───────────────────────────────────────────────────────────
//
// yajra generates this, and we cannot edit it (vendor/):
//
//   $(function(){ window.LaravelDataTables["x"] = $("#x").DataTable({...}) });
//
// destroy() also strips the `dataTable` class DataTables adds to the <table>.
// That matters more than it looks: public/js/custom.js runs
// `new simpleDatatables.DataTable(el)` over every `.dataTable`, and it runs
// *before* @stack('scripts') in the admin layout. Leave the class in the cached
// snapshot and returning to the page wraps a yajra table in a second, unrelated
// table plugin.
step('datatable', () => {
    const $ = jq();
    if (!$ || !$.fn || !$.fn.dataTable) return 0;

    let destroyed = 0;

    // tables() sees every instance DataTables knows about, including ones whose
    // element has already been detached by an earlier render.
    const tables = $.fn.dataTable.tables ? $.fn.dataTable.tables() : [];

    for (const table of tables) {
        try {
            if ($.fn.dataTable.isDataTable(table)) {
                $(table).DataTable().destroy();
                destroyed++;
            }
        } catch (error) {
            failures.push(['datatable', error.message]);
        }
    }

    // Buttons and Responsive append to body, outside the table. destroy()
    // removes its own, but a detached instance leaves these behind.
    $('body > .dt-buttons, body > .dtr-bs-modal, div.dt-button-collection').remove();

    // Stale API objects pointing at detached nodes. The re-executed script
    // reassigns the keys it owns; anything left here is from a page that is gone.
    if (window.LaravelDataTables) window.LaravelDataTables = {};

    return destroyed;
});

// ── 2. Modals, backdrops and the body scroll lock ───────────────────────────
//
// Two unrelated modal systems live in this app:
//
//   admin       #commanModel / #commanModelOver / #commonModal, Bootstrap 5.1.3
//   storefront  #commonModal, a Tailwind panel toggled by the `hidden` class
//
// hide() is not usable here: it is animated, and Turbo clones the DOM on the
// next event loop tick, long before the 300 ms transition ends. The class and
// style changes have to be made directly.
//
// The body scroll lock is included because it outlives the modal. Navigate away
// with a modal or the cart drawer open and `overflow: hidden` stays on <body>;
// the next page cannot be scrolled and nothing on it explains why.
step('modal', () => {
    let closed = 0;

    for (const element of document.querySelectorAll('.modal.show, .modal[style*="display: block"]')) {
        try {
            if (window.bootstrap && window.bootstrap.Modal) {
                const instance = window.bootstrap.Modal.getInstance(element);
                if (instance) instance.dispose();
            }
        } catch (error) {
            failures.push(['modal', error.message]);
        }

        element.classList.remove('show');
        element.style.removeProperty('display');
        element.style.removeProperty('padding-right');
        element.setAttribute('aria-hidden', 'true');
        element.removeAttribute('aria-modal');
        element.removeAttribute('role');
        closed++;
    }

    // Storefront modal: a plain div, so `.modal.show` above never matched it.
    const tailwindModal = document.getElementById('commonModal');
    if (tailwindModal && !tailwindModal.classList.contains('modal') && !tailwindModal.classList.contains('hidden')) {
        tailwindModal.classList.add('hidden');
        closed++;
    }

    // Backdrops are appended to <body>, so they are not inside the modal that
    // created them and survive it.
    for (const backdrop of document.querySelectorAll('.modal-backdrop, .offcanvas-backdrop')) {
        backdrop.remove();
    }

    // Storefront overlays: cart drawer, mobile menu, wishlist drawer, filters.
    // Same failure mode as a backdrop — a fixed, opaque layer over the new page.
    for (const overlay of document.querySelectorAll('.cart-overlay.open, .overlay.open, .overlay.wish-overlay')) {
        overlay.classList.remove('open', 'wish-overlay');
    }
    const cartPanel = document.getElementById('cart-panel');
    if (cartPanel) cartPanel.classList.remove('open');

    const body = document.body;
    body.classList.remove('modal-open', 'no-scroll', 'wishOpen', 'note-link-unlink-sample');
    body.style.removeProperty('overflow');
    body.style.removeProperty('padding-right');

    return closed;
});

// ── 3. select2 ──────────────────────────────────────────────────────────────
//
// Initialised in at least three shared places (admin layout, jQueryRoute,
// comman_function) with `if ($('.select2').length > 0)` guards. That guard does
// not help: the class stays on the element after init, so a second run finds it
// again and select2 quietly builds a second container next to the first.
step('select2', () => {
    const $ = jq();
    if (!$ || !$.fn || !$.fn.select2) return 0;

    // select2 v4 marks every element it owns with this class, whatever selector
    // was used to initialise it.
    const $widgets = $('.select2-hidden-accessible');
    let destroyed = 0;

    $widgets.each(function () {
        // The class alone is not proof of a live instance. A restored snapshot
        // carries the markup select2 wrote — class included — while the instance
        // that owned it died with the previous page. Calling destroy() there logs
        // "The select2('destroy') method was called on an element that is not using
        // Select2" (observed 3×, on warm-cache renders). $(el).data('select2') is
        // the instance itself, so it is the honest test.
        if (!$(this).data('select2')) {
            // Strip the markers anyway, so the page's initialiser does not skip a
            // widget that no longer exists.
            $(this).removeClass('select2-hidden-accessible').removeAttr('aria-hidden tabindex');
            $(this).next('.select2-container').remove();
            return;
        }

        try {
            $(this).select2('destroy');
            destroyed++;
        } catch (error) {
            failures.push(['select2', error.message]);
        }
    });

    // An open dropdown lives in body > .select2-container--open.
    $('body > .select2-container').remove();

    return destroyed;
});

// ── 4. summernote ───────────────────────────────────────────────────────────
//
// The editor is inserted *after* the target element (ui.createLayout does
// `insertAfter`) and the target is hidden, so the DOM is the only reliable index
// of what is initialised — the init selectors differ per page
// (.summernote-simple, .summernote-simple-product, …).
//
// destroy() puts the edited HTML back into the target and unhides it, so the
// cached snapshot keeps whatever the user had typed.
step('summernote', () => {
    const $ = jq();
    if (!$ || !$.fn || !$.fn.summernote) return 0;

    let destroyed = 0;

    for (const editor of document.querySelectorAll('.note-editor')) {
        const note = editor.previousElementSibling;
        if (!note) continue;

        const $note = $(note);
        if (!$note.data('summernote')) continue;

        try {
            $note.summernote('destroy');
            destroyed++;
        } catch (error) {
            failures.push(['summernote', error.message]);
        }
    }

    // Dialogs are rendered with dialogsInBody: true.
    $('body > .note-modal, body > .modal.note-modal').remove();

    return destroyed;
});

// ── 5. flatpickr ────────────────────────────────────────────────────────────
//
// flatpickr keeps its instance on the input as `_flatpickr` and appends the
// calendar to <body>. Both are used here: daterange() in public/js/custom.js
// goes through HTMLElement.prototype.flatpickr, other pages use the jQuery
// plugin.
step('flatpickr', () => {
    let destroyed = 0;

    for (const input of document.querySelectorAll('.flatpickr-input')) {
        if (!input._flatpickr) continue;
        try {
            input._flatpickr.destroy();
            destroyed++;
        } catch (error) {
            failures.push(['flatpickr', error.message]);
        }
    }

    for (const calendar of document.querySelectorAll('body > .flatpickr-calendar')) {
        calendar.remove();
    }

    return destroyed;
});

// ── 6. Dropzone ─────────────────────────────────────────────────────────────
//
// Dropzone tracks its own instances, and destroy() splices that array, so the
// list has to be copied before iterating.
//
// destroy() removes the instance but leaves the classes it added on the element.
// Two things then go wrong on the way back:
//
//   · the page's initialiser calls .dropzone() again and gets
//     "Dropzone already attached." — the element still looks attached
//   · Dropzone's own discover() scans for `.dropzone` on every execution and
//     constructs an instance for whatever it finds, with no url:
//     "Error: No URL provided."
//
// Both observed on product/create. Stripping the markers is what makes the cached
// snapshot look like server-rendered HTML again.
step('dropzone', () => {
    let destroyed = 0;

    if (window.Dropzone && Array.isArray(window.Dropzone.instances)) {
        for (const instance of window.Dropzone.instances.slice()) {
            try {
                instance.destroy();
                destroyed++;
            } catch (error) {
                failures.push(['dropzone', error.message]);
            }
        }
    }

    // Preview nodes Dropzone cloned from the template, plus its markers. Left in
    // place they are both a broken initialiser and stale thumbnails on return.
    for (const element of document.querySelectorAll('.dropzone, [data-toggle="dropzone1"]')) {
        element.classList.remove('dropzone', 'dz-clickable', 'dz-started');
        if (element.dropzone) {
            try {
                delete element.dropzone;
            } catch (error) {
                element.dropzone = undefined;
            }
        }
    }

    return destroyed;
});

// ── 7. ApexCharts ───────────────────────────────────────────────────────────
//
// Apex._chartInstances only holds charts created with an explicit
// `chart: { id }`, and none of this app's 27 charts set one — so that registry
// is empty and there is no way back from the DOM to the instance.
//
// What is reachable is the rendered output: render() appends a new
// .apexcharts-canvas to the container every time, without checking. Two visits
// to a report page therefore stack two charts. Removing the canvas is what
// actually prevents that; destroying the registered instances is the tidier
// half and only covers charts that opted into an id.
//
// Known gap: an unregistered chart's window resize listener stays bound to a
// detached node until GC. Documented in .ai/reports/turbo-console-errors.md.
step('apexcharts', () => {
    let destroyed = 0;

    if (window.Apex && Array.isArray(window.Apex._chartInstances)) {
        for (const entry of window.Apex._chartInstances.slice()) {
            try {
                if (entry && entry.chart && typeof entry.chart.destroy === 'function') {
                    entry.chart.destroy();
                    destroyed++;
                }
            } catch (error) {
                failures.push(['apexcharts', error.message]);
            }
        }
    }

    for (const canvas of document.querySelectorAll('.apexcharts-canvas')) {
        canvas.remove();
        destroyed++;
    }

    // Tooltips and the xaxis crosshair label are appended outside the canvas.
    for (const stray of document.querySelectorAll('body > .apexcharts-tooltip, body > .apexcharts-xaxistooltip')) {
        stray.remove();
    }

    return destroyed;
});

// ── 8. Swiper ───────────────────────────────────────────────────────────────
//
// Swiper 11 stores its instance on the element (`el.swiper`) and marks the
// container `swiper-initialized`, which makes it the one plugin here that can be
// torn down exactly.
//
// destroy(true, true) — delete the instance and clean the styles and classes it
// wrote. Without the second flag the cached snapshot keeps transform offsets and
// duplicated loop slides, and a re-initialised slider starts mid-slide.
//
// Order is deliberate: a thumbs-linked pair (product gallery) is destroyed main
// first, then thumbnails, because the main slider reads thumbs.swiper while
// tearing down. Swiper guards this internally too, but relying on that means
// depending on markup order.
step('swiper', () => {
    let destroyed = 0;

    const elements = [...document.querySelectorAll('.swiper-initialized')];
    const mainFirst = elements.sort((a, b) => {
        const aThumb = a.classList.contains('thumbnail-slider') ? 1 : 0;
        const bThumb = b.classList.contains('thumbnail-slider') ? 1 : 0;
        return aThumb - bThumb;
    });

    for (const element of mainFirst) {
        const instance = element.swiper;
        if (!instance || instance.destroyed) continue;
        try {
            instance.destroy(true, true);
            destroyed++;
        } catch (error) {
            failures.push(['swiper', error.message]);
        }
    }

    return destroyed;
});

// ── 9. Tooltips and popovers ────────────────────────────────────────────────
//
// Bootstrap creates these from window 'load' (assets/js/dash.js) and from
// DataTables initComplete callbacks. A tooltip that is visible when the page is
// cached is a floating box on the next page, anchored to nothing.
step('tooltip', () => {
    if (!window.bootstrap) return 0;

    let destroyed = 0;
    const kinds = [
        ['[data-bs-toggle="tooltip"]', window.bootstrap.Tooltip],
        ['[data-bs-toggle="popover"]', window.bootstrap.Popover],
    ];

    for (const [selector, Constructor] of kinds) {
        if (!Constructor || typeof Constructor.getInstance !== 'function') continue;

        for (const element of document.querySelectorAll(selector)) {
            try {
                const instance = Constructor.getInstance(element);
                if (instance) {
                    instance.dispose();
                    destroyed++;
                }
            } catch (error) {
                failures.push(['tooltip', error.message]);
            }
        }
    }

    for (const stray of document.querySelectorAll('body > .tooltip, body > .popover')) {
        stray.remove();
    }

    return destroyed;
});

// ── 10. Choices.js (data-role="tagsinput") ──────────────────────────────────
//
// Not in the original list, added because it fails differently and worse. Choices
// checks `passedElement.isActive` and, if the element is already active, logs
// "Trying to initialise Choices on element already initialised" and returns —
// so on a restored page the widget looks right but has no instance behind it:
// clicks do nothing, and the form submits whatever the cached <select> held.
//
// There is no registry and no back-reference, so the element is restored by
// undoing exactly what Choices' _wrap/reveal did: unhide it, drop the marker
// attribute, put back the original inline style, and lift it out of the wrapper.
step('choices', () => {
    let restored = 0;

    for (const element of document.querySelectorAll('[data-choice]')) {
        try {
            const wrapper = element.closest('.choices');

            element.removeAttribute('data-choice');
            element.hidden = false;
            element.removeAttribute('tabindex');
            element.classList.remove('choices__input');

            const originalStyle = element.getAttribute('data-choice-orig-style');
            if (originalStyle !== null) {
                element.setAttribute('style', originalStyle);
                element.removeAttribute('data-choice-orig-style');
            } else {
                element.removeAttribute('style');
            }

            if (wrapper && wrapper.parentNode) {
                wrapper.parentNode.replaceChild(element, wrapper);
            }
            restored++;
        } catch (error) {
            failures.push(['choices', error.message]);
        }
    }

    return restored;
});

// ── Runner ──────────────────────────────────────────────────────────────────

function run(reason) {
    if (window.__TURBO_CLEANUP_DISABLED__ === true) return null;

    const counts = {};
    failures.length = 0;

    for (const { name, fn } of steps) {
        try {
            const count = fn();
            if (count) counts[name] = count;
        } catch (error) {
            // A step that throws outside its own guards must not stop the rest:
            // a page cached with a live DataTable is a broken page on return.
            failures.push([name, error.message]);
            console.error(`[turbo-cleanup] ${name} failed:`, error);
        }
    }

    ran++;

    if (window.__TURBO_DEBUG__ === true) {
        console.log(`[turbo-cleanup] ${reason}`, counts, failures.length ? failures : '');
    }

    return counts;
}

// ── Guard: notifier.js container ────────────────────────────────────────────
//
// The one problem here that is not a teardown. Both layouts render flash
// messages as an inline script in the body:
//
//     @if ($message = Session::get('success'))
//         <script>notifier.show('Success', '...', 'success', ...)</script>
//     @endif
//
// public/assets/js/plugins/notifier.js calls createContainer() at load time,
// which appends <div class="notifier-container"> to document.body — and show()
// then does container.appendChild(ntf) with no null check.
//
// Turbo replaces the body, so that container is destroyed with it. The order on
// the next render is:
//
//   1. body attached → inline scripts execute synchronously, in document order
//   2. notifier.show() runs — container gone, notifier.js has not re-run yet
//        → TypeError: Cannot read properties of null (reading 'appendChild')
//   3. notifier.js re-executes and creates a fresh container, too late
//
// Reproduced in the browser, and it is not cosmetic: the throw aborts the block,
// so the user sees no confirmation at all after an action that redirects.
//
// notifier.js itself is upstream WorkDo code in public/, left untouched. Instead
// show() is wrapped so it creates the container if it is missing.
//
// The wrapper is installed through a property accessor on window rather than on a
// turbo:load listener. First attempt used the listener and it did not hold:
// turbo:load fired *before* the re-activated <script src="notifier.js"> had
// finished executing (Turbo sets async = false, which orders the scripts but does
// not make them synchronous), so notifier.js then overwrote window.notifier with
// a fresh, unguarded object. Verified: __turboGuarded read false after six
// navigations. Intercepting the assignment removes the race entirely.
function ensureNotifierContainer() {
    if (document.querySelector('.notifier-container')) return;

    const container = document.createElement('div');
    container.className = 'notifier-container';
    container.id = 'notifier-container';
    document.body.appendChild(container);
}

function guard(notifier) {
    if (!notifier || typeof notifier.show !== 'function' || notifier.__turboGuarded) return notifier;

    const originalShow = notifier.show;
    notifier.show = function (...args) {
        ensureNotifierContainer();

        // notifier.js runs createContainer() on every re-execution, and
        // ensureNotifierContainer may have added one too, so a navigation can
        // leave two. show() takes the first via querySelector, leaving the second
        // as an empty fixed-position div.
        const containers = document.querySelectorAll('.notifier-container');
        for (let i = 1; i < containers.length; i++) containers[i].remove();

        return originalShow.apply(this, args);
    };
    notifier.__turboGuarded = true;

    return notifier;
}

function installNotifierGuard() {
    let current = guard(window.notifier);

    try {
        Object.defineProperty(window, 'notifier', {
            configurable: true,
            enumerable: true,
            get() {
                return current;
            },
            set(value) {
                current = guard(value);
            },
        });
    } catch (error) {
        // Property not configurable: fall back to guarding on turbo:load, which
        // covers the common case even if it can lose the race described above.
        console.error('[turbo-cleanup] window.notifier is not interceptable:', error);
        document.addEventListener('turbo:load', () => guard(window.notifier));
    }
}

// ── Wiring ──────────────────────────────────────────────────────────────────

// Registered after turbo-bootstrap's own listeners (admin.js and store.js import
// the bootstrap first), so page-scoped onTurboUnload teardowns get to run before
// this global sweep. A page that destroys its own widget then finds nothing left
// for us to do — which is the intended relationship, not a conflict.
document.addEventListener('turbo:before-cache', () => run('before-cache'));
document.addEventListener('turbo:before-render', () => run('before-render'));

installNotifierGuard();

// Introspection for the Playwright checks and the browser console.
window.__turboCleanup = {
    run: () => run('manual'),
    get stats() {
        return {
            runs: ran,
            steps: steps.map((s) => s.name),
            notifierGuarded: !!(window.notifier && window.notifier.__turboGuarded),
            lastFailures: failures.slice(),
        };
    },
};

export { run };
