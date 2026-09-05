# BULK-3 Report — Turbo script migration (Batch 2 / Day 2)

**Task:** files 21-40 of Class A (REINIT-NEEDED), editable scope (`resources/views/**` + `themes/**`), `packages/workdo/**` excluded.
**Helper:** `window.onTurboLoad` / `window.onTurboOnce` from `resources/js/turbo-bootstrap.js` (CLAUDE-5).
**Pilot passed 2026-09-05 (user). Checkpoint = every 3 files (user decision; BULK-2 prompt text contradicts itself between ধাপ ২ নিয়ম ৪ and checkpoint section).**
**Status:** 🟨 IN PROGRESS.

## Log

| # | ফাইল | কোন নিয়ম প্রয়োগ | নোট |
|---|---|---|---|
| 21 | `resources/views/front_end/pages/address-form.blade.php` | A — `onTurboLoad` | select2 init + direct binds on `#country`/`#state` (fresh elements per page). Blade `@if(isset($address))` inline inside ready stays untouched inside wrapper. |
| 22 | `resources/views/front_end/pages/address.blade.php` | C — `onTurboOnce` | Whole ready body = 3× `$(document).on(...)` delegation (submit/delete/set-default). |
| 23 | `resources/views/front_end/pages/checkout.blade.php` | E — block@288 split; **block@363 skipped — Claude দেখবে** | block 1: `ready`(loadAddressForm/handleShippingAddress/loadAdditionalNote)→`onTurboLoad`, `$(document).on("#same-address")`→`onTurboOnce`, function decls stay top-level. block 2: payfast — Blade `@if/@else/@endif` INSIDE script (auth-vs-guest `get_payfast_status`), same pattern as checkout_js (BULK-2 #16) → rule 2. |
| 24 | `resources/views/front_end/pages/order.blade.php` | E — split | `ready`(init + order_page_filter)→`onTurboLoad`; `.pagination a` delegation→`onTurboOnce`; direct bind `#filter_order`.change + `order_page_filter` fn stay top-level (re-binds per page, no accumulation — mirrors filter.blade.php decision). |
| 25 | `resources/views/front_end/pages/product-list.blade.php` | **none — Claude দেখবে** | Large Blade `@if($enable_skip_cart...)`..`@endif` block INSIDE the script (lines 349-419) wrapping `initSkipCartHandlers` (unclear nested fn, invocation site unknown) + bootstrap modal init + `@if($button_link)` interpolation inside fn. Rule 2 → skip. |
| 26 | `resources/views/front_end/pages/refund_order.blade.php` | A + E | block@275 direct `#form_refund_data` submit bind → `onTurboLoad`. block@284: ready `#RefundReasonss` trigger → `onTurboLoad`; delegations `#RefundReasonss` + `#carthtml quantity` → `onTurboOnce` (one wrapper for both). |
| 27 | `resources/views/front_end/pages/reply_support_ticket.blade.php` | A — `onTurboLoad` | summernote init block @171. Preceding tinymce/editor script (unlisted) untouched. |
| 28 | `resources/views/front_end/pages/support_ticket_edit.blade.php` | A — `onTurboLoad` | summernote init block @128. Preceding editor script (unlisted) untouched. |
| 29 | `resources/views/generate.blade.php` | C — 3 delegations → `onTurboOnce` | No ready wrapper. `copyText`/`copySelectedText` are global fns used by HTML onclick — stay top-level. `$('body').on('shown.bs.modal')`, `.template_name`, `#generate` wrapped in ONE `onTurboOnce`. |
| 30 | `resources/views/layouts/app.blade.php` | **none — Claude দেখবে** | Admin layout (most sensitive). select2 init ready@310 + `setActiveTheme`. User instructed: if doubt, don't touch; CLAUDE-6 works on this file (admin DataTable/select2 re-init). Leave for CLAUDE-6 — avoids double-edit. |
| 31 | `resources/views/marketplace/pricing.blade.php` | E — split | ready@694 (price calc init)→`onTurboLoad`; `.user_module_check` delegation→`onTurboOnce`. Script @722 (`#user_counter` keyup) unlisted → untouched. |

## স্পর্শ করিনি — কারণ

| ফাইল / block | কারণ |
|---|---|
| `checkout.blade.php` block@363 (payfast) | Blade `@if/@else/@endif` inside script; payment flow — rule 2 → Claude দেখবে। |
| `product-list.blade.php` (block@146) | Blade `@if` block wrapping `initSkipCartHandlers` inside script; unclear nested function — rule 2 → Claude দেখবে। |
| `layouts/app.blade.php` @304 | admin layout, most sensitive; CLAUDE-6 will handle this file (user decision) → Claude দেখবে। |

## টally (নিয়ম ৫ — সৎ হিসাব) — current

- পরিবর্তন করেছি: **9 ফাইল** (21-24, 26-29, 31)
- স্পর্শ করিনি: **2 ফাইল** (product-list, layouts/app) + checkout block 2 (payfast)
- সন্দেহ — Claude দেখবে: **3** (product-list, checkout block-2/payfast, layouts/app)

## Commits

- `42e2543` — `wip(turbo): pilot 2 files  [BULK-3]` (files 21-22)
- `68cf97e` — `wip(turbo): batch 2 files 23-25  [BULK-3]` (23-24 edited, 25 skipped → Claude)
- `fae5ed1` — `wip(turbo): batch 2 files 26-28  [BULK-3]`
- (checkpoint) `wip(turbo): batch 2 files 29-31  [BULK-3]` (29,31 edited; 30 layouts/app skipped → Claude)