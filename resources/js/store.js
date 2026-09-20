// ── Storefront entry (Vite) ─────────────────────────────────────────────────
//
// Loaded from the front-end layout head:
//   @vite(['resources/js/store.js'])
//
// Separate from admin.js on purpose: the storefront must not pay for DataTables,
// summernote or ApexCharts, and the admin must not pay for Swiper.
//
// Deliberately thin for now. The theme's own <script src> tags (jquery,
// swiper-bundle, custom.js) are still loaded by the layout and are not bundled
// here — folding them in would change load order for 50 blade views at once.
// That migration belongs to a later task; this entry only has to exist and to
// start Turbo.

// Loaded first: turbo-bootstrap's turbo:load flush asks this module whether the
// incoming page still has body scripts in flight, so the gate has to exist before
// the flush consults it. It also restores inline-after-external ordering for
// front-theme.js, flipdown.js and swiper-bundle.min.js, which all sit in the body
// ahead of inline blocks that use them.
import './turbo-script-order';

import './turbo-bootstrap';

// Order matters: the bootstrap's turbo:before-cache listener runs first, so a
// page's own onTurboUnload teardowns happen before this global sweep (CLAUDE-6).
// The storefront needs it for Swiper, the cart drawer overlay and the body
// scroll lock; select2 is also initialised in jQueryRoute.blade.php.
import './turbo-cleanup';

// Skeleton loader & selective prefetch (CLAUDE-13)
import './skeleton';
