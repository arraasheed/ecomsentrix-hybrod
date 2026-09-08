{{--
    Turbo Drive boot (CLAUDE-5, extended for the admin by CLAUDE-6)

    Include this inside <head>, after <meta name="csrf-token">:

        storefront:  @include('layouts.includes.turbo')
        admin:       @include('layouts.includes.turbo', ['entry' => 'admin'])

    Currently included by:
        themes/stylique/views/front_end/layouts/app.blade.php   (store)
        resources/views/layouts/app.blade.php                   (admin)

    Not included by the greentic/techzonix front-end layouts, nor by
    layouts/guest.blade.php or marketplace/marketplace.blade.php — see the note at
    the bottom of this file, it matters.

    ── KILL-SWITCHES ─────────────────────────────────────────────────────────
    TURBO_ENABLED=false        both layouts, master switch
    TURBO_ADMIN_ENABLED=false  admin only; the storefront keeps Turbo
    then: php artisan view:clear

    With a switch off, Turbo is never downloaded (the import in
    turbo-bootstrap.js is dynamic) while every onTurboLoad / onTurboOnce /
    onTurboUnload helper keeps working as a $(document).ready equivalent. The
    240+ migrated blade blocks therefore behave exactly as they did before Day 2.

    The admin has its own flag because it is the riskier half: 70 DataTables,
    101 ajax modals, select2, summernote, Dropzone and ApexCharts, none of which
    survive a second initialisation on their own. If something slips through
    testing there, TURBO_ADMIN_ENABLED=false restores the admin without giving up
    the storefront work.

    ⚠️ env() rather than config(): matches the three Day 1 kill-switches
    (LEGACY_TAILWIND_CDN, LEGACY_ADMIN_CSS, LEGACY_ASSET_TIME_BUSTER) and breaks
    the same way under `php artisan config:cache`. CLAUDE-9 moves all of them
    into config/app.php at once.
--}}
@php
    // 'store' (default) or 'admin'. Decides which Vite entry boots Turbo:
    // store.js has Swiper cleanup and no DataTables, admin.js the reverse.
    $turboEntry = ($entry ?? 'store') === 'admin' ? 'resources/js/admin.js' : 'resources/js/store.js';

    $turboEnabled = (bool) env('TURBO_ENABLED', true);
    if (($entry ?? 'store') === 'admin') {
        $turboEnabled = $turboEnabled && (bool) env('TURBO_ADMIN_ENABLED', true);
    }
@endphp
<script>
    window.__TURBO_DISABLED__ = {{ $turboEnabled ? 'false' : 'true' }};

    /*
     * Queue stub. The Vite tag below emits <script type="module">, which is
     * deferred, so it runs after the whole body has been parsed. Every inline
     * script in the body runs first — and each migrated one calls
     * window.onTurboLoad(...). Without this stub each of those is an
     * "onTurboLoad is not a function" TypeError, on the very first page, for all
     * 240 migrated blocks.
     *
     * So the real names are defined here, up front, as collectors.
     * turbo-bootstrap.js drains the queue and replaces them.
     *
     * document.currentScript is captured at call time: by the time the module
     * drains this queue it is null, and the bootstrap needs the calling <script>
     * to decide whether the callback belongs to this page only or to every page.
     */
    window.__turboQueue = window.__turboQueue || [];
    window.onTurboLoad = function (fn, options) {
        window.__turboQueue.push(['load', fn, options, document.currentScript]);
    };
    window.onTurboOnce = function (fn, key) {
        window.__turboQueue.push(['once', fn, key, document.currentScript]);
    };
    window.onTurboUnload = function (fn, options) {
        window.__turboQueue.push(['unload', fn, options, document.currentScript]);
    };
    window.onTurboMount = function (fn) {
        window.__turboQueue.push(['load', fn, { persistent: true }, null]);
    };
    window.onTurboUnmount = function (fn) {
        window.__turboQueue.push(['unload', fn, { persistent: true }, null]);
    };
</script>

{{--
    Duplicate-handler guard. Must load before any script that binds delegation —
    that is, before jQuery in the admin and before front-theme.js in the store.

    BULK-2/3/4 moved the 240 blade blocks onto onTurboOnce, but three external
    assets (front-theme.js 29, public/js/custom.js 10, loader.js 2) bind
    $(document).on(...) at the top level and Turbo re-executes them on every
    navigation. Full reasoning and the measurements are in that file.
--}}
@include('layouts.includes.turbo-dedupe')

{{--
    Stops Turbo navigating on placeholder links — `href="#"` (207 in this codebase)
    and `href="javascript:…"` (135). Turbo resolves "#" to the current URL plus a
    bare hash and treats the click as a visit, which swapped the body out from under
    every ajax modal in the admin. Details and the measurement are in that file.
--}}
@include('layouts.includes.turbo-hash-guard')

@vite([$turboEntry])

{{--
    ── Layouts that do NOT include this file ──────────────────────────────────

    Migrated blade blocks call window.onTurboLoad() unconditionally, so a view
    rendered inside a layout that never defines the stub throws
    "onTurboLoad is not a function" and loses the rest of that block. Two such
    cases exist today and were verified in the browser (CLAUDE-6):

      layouts/guest.blade.php        auth/login.blade.php:114 — reCAPTCHA setup
      marketplace/marketplace.blade.php   marketplace/pricing.blade.php:694

    Both are fixed by a stub-only shim, layouts/includes/turbo-stub.blade.php:
    the helpers behave as $(document).ready, no Turbo is loaded, no navigation
    behaviour changes on those pages. See that file.
--}}
