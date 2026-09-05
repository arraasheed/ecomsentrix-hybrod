import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/store.css',

                // Admin bundle (P3): replaces 28 separate <link> tags in
                // resources/views/layouts/app.blade.php. Four variants because
                // the layout picks its theme CSS from two independent settings,
                // SITE_RTL and cust_darklayout. Which one is served is decided
                // by $adminCssVariant in that layout's opening @php block.
                'resources/css/admin-ltr-light.css',
                'resources/css/admin-ltr-dark.css',
                'resources/css/admin-rtl-light.css',
                'resources/css/admin-rtl-dark.css',

                // Turbo Drive entries (CLAUDE-5). Two separate bundles so the
                // storefront never downloads admin-only plugins and vice versa.
                // store.js is loaded by the front-end layout; admin.js is built
                // but not yet referenced by any view — see the note in that file.
                'resources/js/store.js',
                'resources/js/admin.js',
            ],
            refresh: true,
        }),
    ],

    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    // Turbo is imported dynamically by turbo-bootstrap.js so
                    // that TURBO_ENABLED=false skips the download entirely.
                    // Naming the chunk keeps that request identifiable in
                    // DevTools and in the Workbox precache list Day 3 builds.
                    if (id.includes('@hotwired/turbo')) return 'turbo';

                    // store.js and admin.js both import the bootstrap, so
                    // Rollup hoists it into a shared chunk either way. Left
                    // unnamed that chunk inherits the name of whichever entry
                    // it processed first, and the admin ends up downloading
                    // "store-a1b2c3.js" — which reads like a bug. Naming it
                    // costs nothing: @vite() emits a modulepreload link, so it
                    // is fetched in parallel with the entry, not after it.
                    if (id.includes('turbo-bootstrap')) return 'turbo-bootstrap';
                },
            },
        },
    },
});
