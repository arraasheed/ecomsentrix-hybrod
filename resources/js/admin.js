// ── Admin entry (Vite) ──────────────────────────────────────────────────────
//
// Loaded from resources/views/layouts/app.blade.php through
// resources/views/layouts/includes/turbo-admin.blade.php.
//
// Separate from store.js on purpose: the admin must not pay for Swiper, and the
// storefront must not pay for DataTables, summernote or ApexCharts.
//
// Turbo was deliberately left off in the admin until CLAUDE-6, because the panel
// leans on jQuery plugins that throw on a second initialisation — "Cannot
// reinitialise DataTable" on the first back-navigation. turbo-cleanup.js is what
// makes it safe: it hands each page back as plain HTML before Turbo caches it.

// Loaded first, because turbo-bootstrap's turbo:load flush asks it whether the
// incoming page still has body scripts in flight. Import order decides listener
// order, and the gate has to exist before the flush consults it.
import './turbo-script-order';

import './turbo-bootstrap';

// Must come after the bootstrap: its turbo:before-cache listener registers
// first, so a page's own onTurboUnload teardowns run before this global sweep.
import './turbo-cleanup';

// Skeleton loader & selective prefetch (CLAUDE-13)
import './skeleton';
