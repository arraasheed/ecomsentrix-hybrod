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

import './turbo-bootstrap';
