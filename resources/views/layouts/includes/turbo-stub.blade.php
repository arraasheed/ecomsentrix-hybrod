{{--
    Turbo helper stub, without Turbo (CLAUDE-6)

    For layouts that render views containing migrated inline scripts, but where
    Turbo Drive itself is not wanted.

        @include('layouts.includes.turbo-stub')

    Included by:
        resources/views/layouts/guest.blade.php        (login/register — reCAPTCHA)
        resources/views/marketplace/marketplace.blade.php

    ── Why this exists ───────────────────────────────────────────────────────

    BULK-2/3/4 rewrote 240+ blade blocks from

        $(document).ready(function () { ... })
    to
        window.onTurboLoad(function () { ... })

    A handful of those views are rendered by layouts that never include
    layouts.includes.turbo, so the helper was simply undefined there. Verified in
    the browser on http://ecommercego.dev/login:

        TypeError: window.onTurboLoad is not a function   at /login:387
        TypeError: window.onTurboLoad is not a function   at /login:465

    Each of those throws aborts the rest of its <script>. On the login page that
    is the reCAPTCHA callback, which is the difference between being able to log
    in and not.

    Loading full Turbo on these pages would be the wrong fix. Login does a real
    redirect on success, the marketplace pages are outside the app shell, and
    turbo-cleanup.js is not loaded here to tear anything down. So this file
    defines the five helpers with $(document).ready semantics and stops there:
    each callback runs exactly once, on this page, and nothing about navigation
    changes.

    KILL-SWITCH: remove the @include. The console errors come back, nothing else
    changes.
--}}
<script>
    // Turbo is deliberately absent on this layout, not merely switched off. Any
    // code that branches on the flag therefore sees the same value it would with
    // TURBO_ENABLED=false.
    window.__TURBO_DISABLED__ = true;

    (function () {
        var queue = [];
        var flushed = false;

        function runAll() {
            flushed = true;
            while (queue.length) {
                var fn = queue.shift();
                try {
                    fn(document.body);
                } catch (error) {
                    // Same contract as jQuery's ready queue: one broken callback
                    // must not take the remaining ones down with it.
                    console.error('[turbo-stub] callback failed:', error);
                }
            }
        }

        function push(fn) {
            if (typeof fn !== 'function') return;
            if (flushed) {
                // Registered after the flush (async script, timer): run now
                // rather than never.
                try {
                    fn(document.body);
                } catch (error) {
                    console.error('[turbo-stub] callback failed:', error);
                }
                return;
            }
            queue.push(fn);
        }

        // No page ever gets swapped here, so "once per page", "once per session"
        // and "on mount" are all the same thing.
        window.onTurboLoad = push;
        window.onTurboOnce = push;
        window.onTurboMount = push;

        // Nothing is ever cached or unmounted on these pages, so a teardown
        // would never fire. Accepting and dropping the callback keeps shared
        // partials working unchanged.
        window.onTurboUnload = function () {};
        window.onTurboUnmount = function () {};

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', runAll, { once: true });
        } else {
            runAll();
        }
    })();
</script>
