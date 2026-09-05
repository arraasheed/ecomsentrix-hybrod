# BULK-4 Report — Turbo script migration (Batch 3 / Day 2)

**Task:** Class A files 41+ (remaining) + Class B (delegation-only), editable scope (`resources/views/**` + `themes/**`), `packages/workdo/**` excluded.
**Helper:** `window.onTurboLoad` / `window.onTurboOnce` from `resources/js/turbo-bootstrap.js` (CLAUDE-5).
**Pilot passed 2026-09-05 (user, task resumption). Checkpoint = every 3 files (user decision).**
**Status:** 🟨 IN PROGRESS — pilot (1-2) + batch 3-5.

## Log

| # | ফাইল | কোন নিয়ম প্রয়োগ | নোট |
|---|---|---|---|
| 1 | `resources/views/plans/create.blade.php` | C — delegation → `onTurboOnce`; A — DOMContentLoaded → `onTurboLoad` | ৩x `$(document).on` delegation (#trial, #theme-search, #addon-search) → ২x `onTurboOnce` (theme+addon এক wrapper)। `document.addEventListener('DOMContentLoaded')` block (img-checkbox) → `onTurboLoad` — শুধু opening লাইন বদল, body অক্ষত। |
| 2 | `resources/views/plans/edit.blade.php` | C + A | plans/create-এর হুবহু একই প্যাটার্ন। |
| 3 | `resources/views/plans/stripe.blade.php` | C + A — block@3049 | tab UI: `.list-group-item` delegation → `onTurboOnce`; hash/scrollSpy init → `onTurboLoad`; DOMContentLoaded scroll listener → `onTurboLoad`। **বাকি সব block (3105+) = payment gateway flow (Stripe/Paystack/Razorpay/Khalti, Blade `@if` interspersed) → স্পর্শ করিনি, Claude দেখবে।** |
| 4 | `resources/views/pos/index.blade.php` | **none — Claude দেখবে** | block@295-674 (380 লাইন): ready-র ভেতরে ৩টি function declaration (`searchProducts`, `searchProductsSku`, `getProductCategories`) + ৯টি delegation একই scope-এ; ৩টি top-level delegation বাইরে। Delegation-কে onTurboOnce-এ সরালে scope ভাঙবে (functions আর visible নয়); onTurboLoad-এ রাখলে প্রতি nav-এ re-register হয়ে handler জমবে (measured: ২৯→৪৬৪)। |
| 5 | `resources/views/product/attribute_combinations.blade.php` | C — delegation → `onTurboOnce`; A — ready → `onTurboLoad` | `.delete_option` delegation → `onTurboOnce`; `.downloadable`+`.virtual_product`+`.manage_stock` delegation → এক `onTurboOnce`; `.enable_view`/`#enable_product_stock` direct binds top-level-এ (fresh per page)। `$(document).ready` → `onTurboLoad`। |
| 6 | `resources/views/product/attribute_combinations_data.blade.php` | C + A | একই attribute_combinations প্যাটার্ন: `.delete_option` → `onTurboOnce`; `.downloadable`+`.virtual_product`+`.manage_stock` delegation → এক `onTurboOnce`; `$(function)` body-এর `$('body').on('.delete-option-comment')` delegation → `onTurboOnce`; ৩x `$(document).ready` → `onTurboLoad`। direct binds top-level। |
| 7 | `resources/views/product/attribute_combinations_edit.blade.php` | C + A | `.delete_option` → `onTurboOnce`; `$(function)`-এর `$('body').on('.delete-comment')` delegation → `onTurboOnce`; `.downloadable`+`.virtual_product`+`.manage_stock` delegation → এক `onTurboOnce`; ২x ready → `onTurboLoad`। |
| 8 | `resources/views/product/create.blade.php` | E — একাধিক split | block@479: ready → `onTurboLoad` (select2/slug init), `#enable_product_stock` delegation → `onTurboOnce`, preview init → `onTurboLoad`, `#enable_custom_field` delegation → `onTurboOnce`, repeater init → `onTurboLoad`, `.custom_field_repeater`/`.deleteRecord` direct binds top-level। block@668: ২x delegation (variant/attribute_id) → `onTurboOnce`; `update_attribute()` function top-level (global); `.attribute_option_data`+variant+attribute_id delegation → ২য় `onTurboOnce`। block@811: Dropzones IIFE (init) → `onTurboLoad`; `#submit-all` direct bind top-level। |

## স্পর্শ করিনি — কারণ

| ফাইল / block | কারণ |
|---|---|
| `plans/stripe.blade.php` block@3105+ | payment gateway flow (Stripe/Paystack/Razorpay/Khalti) — Blade `@if` script-এর ভেতরে interspersed; নিয়ম ২ → Claude দেখবে |
| `pos/index.blade.php` block@295 | 380-লাইন mixed block, ready-র ভেতরে function declarations + delegation একই scope — mechanical split scope ভাঙবে; BULK-2 `country/index` precedent → Claude দেখবে |

## টally (নিয়ম ৫ — সৎ হিসাব) — ফাইল ৩-৮ পর্যন্ত

- পরিবর্তন করেছি: **7 ফাইল** (1, 2, 3, 5, 6, 7, 8)
- স্পর্শ করিনি: **1 ফাইল** (pos/index) + stripe-এর payment block
- সন্দেহ — Claude দেখবে: **2** (pos/index, stripe block@3105+)

## Commits

- `3dbcb02` — `wip(turbo): pilot 2 files  [BULK-4]` (1-2 planes: create, edit)
- `8d23826` — `wip(turbo): batch 3 files 3-5  [BULK-4]` (3 stripe block@3049, 5 attribute_combinations; 4 pos/index skipped)
- (pending) — `wip(turbo): batch 3 files 6-8  [BULK-4]` (attribute_combinations_data, attribute_combinations_edit, product/create)