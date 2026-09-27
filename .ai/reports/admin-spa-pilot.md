# Admin SPA Pilot — findings (Muse Spark, browser-verified)

> Date: 2026-09-27 · Store admin (`admin@example.com`) · Playwright Chromium.
> Branch: `master` (post-v1.0). No code changed in this pass (read-only pilot).

## Result: admin Turbo core already works

| Check | Result |
|---|---|
| Turbo Drive active in admin (`window.Turbo.session.drive`) | ✅ true |
| Sidebar nav dashboard → product (Turbo visit, no document request) | ✅ |
| Server-side DataTable re-init on every visit | ✅ product 3 rows, order 2 rows |
| product ↔ order ×3 navigations, no "Cannot reinitialise" | ✅ 0 pageerrors |
| jQuery document-handler count across nav | ✅ 6 → 6 (no accumulation) |
| ajax modal (`data-ajax-popup` → `#commanModel`, "Create Tax" 671 chars) | ✅ shown, 0 errors |
| select2/summernote/ApexCharts/Dropzone/POS pages | ⬜ NOT YET COVERED |

## Pre-existing quirks (core code, NOT caused by SPA work, out of scope)

1. `public/js/custom.js:35` + `:311` — TWO delegated handlers on the same
   `data-ajax-popup="true"` selector (targets `#commanModel` and
   `#commonModal`). Both fire per click → double AJAX; one always hits a
   missing id. Harmless today, wasteful. Do not touch without a dedicated task.
2. `envato.workdo.io/verify.js` license ping fails in sandbox (no route).
   Environmental, pre-existing.

## Inventory (admin `resources/views`, Blade only)

| Plugin | Files |
|---|---|
| `x-datatable` component | 39 |
| direct `DataTable(` | 7 |
| `datatable-js` include | 10 |
| select2 | 7 |
| summernote | 20 |
| apexcharts | 21 |
| dropzone | 8 |
| `data-ajax-popup` modal | 102 |

## What remains for P1 (bulk-ready recipe)

1. Cover select2/summernote/ApexCharts/Dropzone + POS-branch (`commonModal`)
   pages with the same nav-twice-and-check pattern.
2. Pilot currently passes WITHOUT new code — the CLAUDE-6 cleanup + BULK
   wraps already handle it. If coverage passes everywhere, P1 reduces to
   "verify + document", not "migrate".
3. Only if a page breaks: per-page fix (destroy/re-init in
   `onTurboUnload`/`onTurboLoad`), then generalize to recipe.
