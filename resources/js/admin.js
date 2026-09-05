// ── Admin entry (Vite) ──────────────────────────────────────────────────────
//
// Not wired into resources/views/layouts/app.blade.php yet. CLAUDE-5 enables
// Turbo on the storefront only; the admin follows in CLAUDE-6, once
// turbo-cleanup.js can destroy DataTables, select2, summernote, Dropzone and
// ApexCharts on turbo:before-cache. Turning Turbo on in the admin before that
// produces "Cannot reinitialise DataTable" on the first back-navigation.
//
// The entry exists now so that the Vite input list, the manifest and the
// build output are settled before CLAUDE-6 starts.

import './turbo-bootstrap';
