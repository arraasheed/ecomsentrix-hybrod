{{--
    Turbo Drive boot (CLAUDE-5)

    Include this inside <head>, after <meta name="csrf-token">:

        @include('layouts.includes.turbo')

    Currently included by:
        themes/stylique/views/front_end/layouts/app.blade.php

    Not yet included by the admin layout or the greentic/techzonix themes —
    CLAUDE-6 adds the admin one, once turbo-cleanup.js can destroy DataTables,
    select2, summernote and ApexCharts on turbo:before-cache.

    KILL-SWITCH: TURBO_ENABLED=false in .env, then `php artisan view:clear`.
    Turbo is then never downloaded (the import is dynamic), while every
    onTurboLoad / onTurboOnce helper keeps working as a $(document).ready
    equivalent. Removing this single @include is the harder off switch.

    ⚠️ env() rather than config(): matches the three Day 1 kill-switches
    (LEGACY_TAILWIND_CDN, LEGACY_ADMIN_CSS, LEGACY_ASSET_TIME_BUSTER) and breaks
    the same way under `php artisan config:cache`. CLAUDE-9 moves all four into
    config/app.php at once.
--}}
<script>
    window.__TURBO_DISABLED__ = {{ env('TURBO_ENABLED', true) ? 'false' : 'true' }};

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
@vite(['resources/js/store.js'])
