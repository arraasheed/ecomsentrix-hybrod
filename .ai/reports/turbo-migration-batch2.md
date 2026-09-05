# BULK-3 Report — Turbo script migration (Batch 2 / Day 2)

**Task:** files 21-40 of Class A (REINIT-NEEDED), editable scope (`resources/views/**` + `themes/**`), `packages/workdo/**` excluded.
**Helper:** `window.onTurboLoad` / `window.onTurboOnce` from `resources/js/turbo-bootstrap.js` (CLAUDE-5).
**Pilot passed 2026-09-05 (user). Checkpoint = every 3 files (user decision; BULK-2 prompt text contradicts itself between ধাপ ২ নিয়ম ৪ and checkpoint section).**
**Status:** ✅ DONE — all 20 slots (21-40) processed; 15 edited, 5 skipped with reasons.

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
| 32 | `resources/views/menu/script.blade.php` | **none — Claude দেখবে** | 612-line mixed block: ready contains 1 delegation + 5× `handleSelectAll()` (each registers 2 delegations); ~14 top-level delegations; nested `document.addEventListener('DOMContentLoaded')`; Blade route interpolation throughout. All delegations MUST go once-only for a correct split — high risk of a missed one = duplicate POST (menu save/delete). Rule 2 → skip. |
| 33 | `resources/views/module/add.blade.php` | D — `onTurboLoad` | open script, Dropzone init + direct binds (`$('#uploadType').change`, `dropzone.on`) on fresh elements. |
| 34 | `resources/views/order/order_show.blade.php` | A — `onTurboLoad` | ready = single direct `.click()` bind on `.delivered-button-status`. Vanilla `querySelectorAll('.download-btn').forEach` bind stays top-level (re-binds per page). |
| 35 | `resources/views/order/refund_details.blade.php` | A + E | ready@214 (setTimeout status init)→`onTurboLoad`; ready@314 split: localStorage init→`onTurboLoad` + `#manage-button` delegation→`onTurboOnce`; ready@349 (`.check-shipp` direct)→`onTurboLoad`; ready@384 split: localStorage init→`onTurboLoad` + `#refund-amount` delegation→`onTurboOnce`. `.accept-button`/`.cancel-button` direct binds stay top-level. |
| 36 | `resources/views/page/create.blade.php` | D — `onTurboLoad` | open script, `$('.modal').on('shown.bs.modal')` = Choices init on modal open. |
| 37 | `resources/views/page/edit.blade.php` | D — `onTurboLoad` | same pattern as page/create. |
| 38 | `resources/views/page/index.blade.php` | C — `onTurboOnce` | ready body = single `.page-toggle` delegation. |
| 39 | `resources/views/payment/authorizenet.blade.php` | **none — Claude দেখবে** | Standalone HTML page (own `<!DOCTYPE html>`…`</html>`, no `layouts.includes.turbo` stub). `window.onTurboLoad` is undefined here → wrapping would throw "not a function" and break card validation. Page is always full-loaded, so `DOMContentLoaded` still fires — leave as-is. |
| 40 | `resources/views/payment/khalti.blade.php` | **none — Claude দেখবে** | Standalone payment page (starts with own `<head>`), no turbo bootstrap. Same reason: `ready` fires on every full load; KhaltiCheckout init works as-is. Wrapping would break. |

## ⚠️ Follow-up fix (post-final verification)

While re-scanning all 20 files after the final commit, a **4th ready block** in `order/refund_details.blade.php` (line 314, manage-stock) was found — missed during the first pass because an earlier read truncated the middle of the file. It was NOT a re-wrap: the block was genuinely un-wrapped. Fixed by splitting init→`onTurboLoad` + `#manage-button` delegation→`onTurboOnce` in a follow-up commit. Tally above unaffected (same file count).

## স্পর্শ করিনি — কারণ

| ফাইল / block | কারণ |
|---|---|
| `checkout.blade.php` block@363 (payfast) | Blade `@if/@else/@endif` inside script; payment flow — rule 2 → Claude দেখবে। |
| `product-list.blade.php` (block@146) | Blade `@if` block wrapping `initSkipCartHandlers` inside script; unclear nested function — rule 2 → Claude দেখবে। |
| `layouts/app.blade.php` @304 | admin layout, most sensitive; CLAUDE-6 will handle this file (user decision) → Claude দেখবে। |
| `menu/script.blade.php` (block@2) | 612-line mixed block, nested DOMContentLoaded, ~16 delegations all needing once-only — high risk → Claude দেখবে। |
| `payment/authorizenet.blade.php` @92 | standalone page, no `onTurboLoad` stub → wrapping breaks; not Turbo-navigated → Claude দেখবে। |
| `payment/khalti.blade.php` @14 | standalone payment page, same reason → Claude দেখবে। |

## টally (নিয়ম ৫ — সৎ হিসাব) — current

- পরিবর্তন করেছি: **15 ফাইল** (21-24, 26-29, 31, 33-38)
- স্পর্শ করিনি: **5 ফাইল** (product-list, layouts/app, menu/script, authorizenet, khalti) + checkout block 2 (payfast)
- সন্দেহ — Claude দেখবে: **6** (product-list, checkout block-2/payfast, layouts/app, menu/script, authorizenet, khalti)

## Commits

- `42e2543` — `wip(turbo): pilot 2 files  [BULK-3]` (files 21-22)
- `68cf97e` — `wip(turbo): batch 2 files 23-25  [BULK-3]` (23-24 edited, 25 skipped → Claude)
- `fae5ed1` — `wip(turbo): batch 2 files 26-28  [BULK-3]`
- `8197ce4` — `wip(turbo): batch 2 files 29-31  [BULK-3]` (29,31 edited; 30 layouts/app skipped → Claude)
- `c936391` — `wip(turbo): batch 2 files 32-34  [BULK-3]` (33,34 edited; 32 menu/script skipped → Claude)
- `02e2153` — `wip(turbo): batch 2 files 35-37  [BULK-3]`
- `47c52ff` — `refactor(turbo): migrate document.ready to turbo:load, batch 2 of 3  [BULK-3]` (ships 38; 39-40 skipped → Claude)
- (fix) `fix(turbo): wrap missed ready block in refund_details  [BULK-3]` — split ready@314 missed in first pass