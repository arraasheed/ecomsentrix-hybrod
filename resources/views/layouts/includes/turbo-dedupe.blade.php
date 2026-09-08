{{--
    Duplicate-handler guard for jQuery delegation (CLAUDE-6)

    Included by layouts/includes/turbo.blade.php, immediately after the helper
    stub and before @vite. Must be in <head>, before any body script binds.

    ── The problem this solves ───────────────────────────────────────────────

    Turbo re-executes every <script> in the body after a page swap — inline and
    external alike. Any `$(document).on('click', '.x', fn)` at the top level of
    such a script therefore binds again on every navigation, and delegated
    handlers on `document` are never cleaned up, because `document` is not the
    node Turbo replaces.

    Measured on this app before the fix (CLAUDE-5, reconfirmed here):

        cold load          29 click handlers on document
        8 navigations     464 click handlers
        one add-to-cart click → 8 POST /product_cart → cart qty 1 becomes 8

    BULK-2/3/4 fixed this for the 240 blade blocks by moving them to
    window.onTurboOnce(). What remained is three external assets, which are not
    blade files and cannot be wrapped per block:

        public/assets/js/front-theme.js   29 delegations   storefront
        public/js/custom.js               10 delegations   admin
        public/js/loader.js                2 delegations   both

    Verified in the browser that the alternatives do not work:
      · data-turbo-eval="false" on the <script src> — Turbo drops the attribute
        during the head merge and re-executes the file regardless (tested: a
        marker handler still went 2 → 4 across one navigation)
      · a session guard around the whole file — front-theme.js interleaves
        delegation with genuine per-page init (cart count, price slider,
        flipdown, variant pricing), and jQuery runs a freshly registered
        $(document).ready callback immediately once the document is ready, so
        that init is *supposed* to run again. Skipping the file would break it.

    ── How it works ──────────────────────────────────────────────────────────

    jQuery funnels every binding through jQuery.event.add. This wraps it: when
    the target is document, window or body, a binding whose (type, namespace,
    selector, handler source) already exists on that element is dropped.

    Identity is the handler's source text, the same rule turbo-bootstrap.js uses
    for onTurboOnce — re-executing a script produces a new function object every
    time, so references cannot be compared.

    Two properties worth stating explicitly:
      · it reads the live handler table rather than keeping its own registry, so
        `$(document).off(...).on(...)` still works: after the off() the scan
        finds nothing and the bind goes through
      · namespaced events are compared including the namespace, so per-instance
        bindings from select2 and DataTables (click.select2.xyz) are never
        treated as duplicates of each other

    ── KILL-SWITCH ───────────────────────────────────────────────────────────

    window.__TURBO_DEDUPE_DISABLED__ = true    (console, or set before this file)
    TURBO_ENABLED=false                        skips it entirely — see the guard
                                               below, there is nothing to
                                               accumulate without navigation

    Either way jQuery is left untouched and the original behaviour returns.
--}}
<script>
    (function () {
        // Without Turbo there is no body swap, so no script is ever re-executed
        // and there is nothing to deduplicate. Leaving jQuery unpatched keeps the
        // kill-switch honest: TURBO_ENABLED=false means the app runs exactly as it
        // did before Day 2.
        if (window.__TURBO_DISABLED__ === true) return;

        var installed = false;
        var skipped = 0;

        function srcKey(fn) {
            // Cached on the function object: re-execution creates new objects, so
            // each is hashed once and every later comparison is an integer one.
            if (fn.__turboKey) return fn.__turboKey;
            var source = String(fn);
            var hash = 5381;
            for (var i = 0; i < source.length; i++) {
                hash = ((hash << 5) + hash + source.charCodeAt(i)) | 0;
            }
            var key = source.length + ':' + hash;
            try {
                fn.__turboKey = key;
            } catch (error) {
                // Frozen function: fall back to recomputing.
            }
            return key;
        }

        function install($) {
            if (installed || !$ || !$.event || typeof $.event.add !== 'function') return;
            if (typeof $._data !== 'function') return; // not jQuery 1.8-3.x
            installed = true;

            var originalAdd = $.event.add;

            function isGlobalTarget(elem) {
                return elem === document || elem === window || elem === document.body;
            }

            function alreadyBound(elem, type, namespace, selector, key) {
                var events = $._data(elem, 'events');
                if (!events) return false;

                var handlers = events[type];
                if (!handlers) return false;

                for (var i = 0; i < handlers.length; i++) {
                    var bound = handlers[i];
                    if ((bound.selector || '') !== (selector || '')) continue;
                    if ((bound.namespace || '') !== namespace) continue;
                    if (typeof bound.handler !== 'function') continue;
                    if (srcKey(bound.handler) !== key) continue;
                    return true;
                }

                return false;
            }

            $.event.add = function (elem, types, handler, data, selector) {
                if (
                    window.__TURBO_DEDUPE_DISABLED__ === true ||
                    !isGlobalTarget(elem) ||
                    typeof handler !== 'function' ||
                    typeof types !== 'string'
                ) {
                    return originalAdd.apply(this, arguments);
                }

                var key = srcKey(handler);
                var keep = [];

                // "click keyup.foo" — jQuery accepts several types per call, so
                // each is decided on its own. Namespaces are sorted to match how
                // jQuery stores them.
                var tokens = types.match(/\S+/g) || [];

                for (var i = 0; i < tokens.length; i++) {
                    var parts = tokens[i].split('.');
                    var type = parts.shift();
                    var namespace = parts.sort().join('.');

                    if (alreadyBound(elem, type, namespace, selector, key)) {
                        skipped++;
                        continue;
                    }
                    keep.push(tokens[i]);
                }

                if (!keep.length) return undefined;

                return originalAdd.call(this, elem, keep.join(' '), handler, data, selector);
            };

            // Introspection for the Playwright checks and the browser console.
            window.__turboDedupe = {
                get stats() {
                    var events = $._data(document, 'events') || {};
                    var total = 0;
                    for (var type in events) total += events[type].length;
                    return { installed: true, skipped: skipped, documentHandlers: total };
                },
            };
        }

        if (window.jQuery) {
            install(window.jQuery);
            return;
        }

        // The admin layout loads jQuery at the end of <body>, long after this
        // runs. Trap the assignment so the wrapper is in place before the first
        // delegation binds — a plain poll could lose the race.
        var pending;
        try {
            Object.defineProperty(window, 'jQuery', {
                configurable: true,
                enumerable: true,
                get: function () {
                    return pending;
                },
                set: function (value) {
                    pending = value;
                    try {
                        install(value);
                    } catch (error) {
                        console.error('[turbo-dedupe] could not install:', error);
                    }
                },
            });
        } catch (error) {
            // Property not configurable: the app keeps working, handlers just
            // accumulate as they did before.
            console.error('[turbo-dedupe] window.jQuery is not interceptable:', error);
        }
    })();
</script>
