# BULK-4 Report — Turbo script migration (Batch 3 / Day 2)

**Task:** Class A files 41+ (remaining) + Class B (delegation-only), editable scope (`resources/views/**` + `themes/**`), `packages/workdo/**` excluded.
**Helper:** `window.onTurboLoad` / `window.onTurboOnce` from `resources/js/turbo-bootstrap.js` (CLAUDE-5).
**Pilot passed 2026-09-05 (user, task resumption). Checkpoint = every 3 files (user decision).**
**Status:** 🟨 IN PROGRESS — শ্রেণি A সম্পন্ন (1-35); শ্রেণি B (36-73) বাকি।

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
| 9 | `resources/views/product/edit.blade.php` | E — একাধিক split | একটাই বড় script (662-1164): ready@663 → `onTurboLoad` (attribute_option_data/type init + select2 + preview)। `#enable_product_stock`/`.deleteRecord`/২x `#enable_product_variant`/`#attribute_id`/`.attribute_option_data` delegation → ৫x `onTurboOnce`। variation-init `if` → `onTurboLoad`। repeater init → `onTurboLoad`। Dropzones IIFE → `onTurboLoad`। `attribute_option_data()`/`update_attribute()`/`type()` function top-level (global) — delegation-গুলো ও init-গুলো ডাকে, কভার logic অক্ষত। `$('#submit-all')` direct bind top-level। |
| 10 | `resources/views/product/show.blade.php` | E — split | block1: ready@634 → `onTurboLoad` (readonly/select2/preview init); `type()` function ভেতরেই (hoisted)। block1 ২য় ready → split: custom_value init → `onTurboLoad`, `#enable_custom_field` delegation → `onTurboOnce`। block2 open code: variation `if` init → `onTurboLoad`; `attribute_option_data`/`update_attribute` function top-level (block-1-থেকে ডাকা হয়, wrap করলে scope ভাঙত)। block3: `.product_attribute` delegation → `onTurboOnce`; `if(#enable_product_variant)` init → `onTurboLoad`। |
| 11 | `resources/views/reports/bar_chart_order_report.blade.php` | A — ২x `onTurboLoad` | script@62: ready(init + `fetchData` fn + `.chart-data` direct bind) → `onTurboLoad` (ApexCharts init, destroy CLAUDE-6 করবে)। script@256: ready(export + datepicker direct binds) → `onTurboLoad`। |
| 12 | `resources/views/reports/index.blade.php` | A — ২x `onTurboLoad` | ২টি ready (bar chart init + `.chart-data` direct bind + export direct bind) → `onTurboLoad`। |
| 13 | `resources/views/reports/order_report.blade.php` | A — ২x `onTurboLoad` | ২টি ready (line/bar chart init + `.chart-data`/export/daterangepicker direct binds) → `onTurboLoad`। |
| 14 | `resources/views/reports/order_country_Report.blade.php` | D — ৪x `onTurboLoad` (open code) | pie/bar/area ApexCharts init (open script) + svgMap init → ৪x `onTurboLoad`। `saveAsPDF()` (html2pdf export) function declaration global — HTML onclick-এ ব্যবহৃত, স্পর্শ করিনি। |
| 15 | `resources/views/reports/orderStatusReport.blade.php` | D — ৩x `onTurboLoad` (open code) | pie/bar/area ApexCharts init (open script, `@json` data) → ৩x `onTurboLoad`। `saveAsPDF()` global fn স্পর্শ করিনি। |
| 16 | `resources/views/reports/sale_by_brand.blade.php` | A — ২x `onTurboLoad` + D — Choices init | `if ($(".multi-select"))` Choices init (open code, fresh per page) → `onTurboLoad`; ready@78 (fetchData + `.chart-data`/`.filter-apply-btn`/`#brand_id` direct binds) → `onTurboLoad`; ready@181 (export + daterangepicker) → `onTurboLoad`। |
| 17 | `resources/views/reports/sale_by_category.blade.php` | A + D | sale_by_brand-এর হুবহু প্যাটার্ন (Choices init + ২x ready)। `#category_id`। |
| 18 | `resources/views/reports/sale_by_product.blade.php` | A + D | Choices init (open code) → `onTurboLoad`; ২x ready (chart init + export/datepicker) → `onTurboLoad`। `#product_id`। |
| 19 | `resources/views/reports/stock_report.blade.php` | B — `$(function)` → `onTurboLoad` | ২টি DataTable init (`#low-stock-table`, `#out-of-stock-table`) এক wrapper-এ। `removeSortingOrderFromHeader()` = global fn (datatable-js-এ), শুধু call। |
| 20 | `resources/views/reports/top_5_reports.blade.php` | D — `onTurboLoad` (open code) | চারটি ApexCharts init (`commonOptions` + ৪টি chart), open script → এক `onTurboLoad`। `saveAsPDF()` global fn স্পর্শ করিনি। |
| 21 | `resources/views/roles/index.blade.php` | C — delegation → `onTurboOnce` | ready-র delegation (`input[type=checkbox]` permission check) → `onTurboOnce`; `.show-more`/`.show-less` delegation → ২য় `onTurboOnce`। `CheckPermission` function top-level। |
| 22 | `resources/views/setting/email_setting.blade.php` | A — ready → `onTurboLoad` | `getEmailSettingFields(emailSetting)` init। |
| 23 | `resources/views/setting/index.blade.php` | E — split (বড়) | block1 (195-492): ready → `onTurboLoad` (PWA init + add/remove direct binds)। `.nav-link`/`#email_setting`/`.send_email`/`.test-whatsapp-massage`/`[name=storage_setting]`/`#test_email`/`.whatsapp-notification`/`.currency_note` — ৮টি delegation → ৮x `onTurboOnce`; `getSettingForm`/`sendData`/`getEmailSettingFields`/`check_theme` function top-level। block2 (494-703): `.colorPicker`/`.themes-color-change` delegation → `onTurboOnce`; `DOMContentLoaded` → `onTurboLoad`; `#pwa_store`/`#payment-search` delegation → `onTurboOnce`; `reinitializeEventListeners`/`initializePaynowListeners`/`initializePwaListeners`/`$.fn.removeClassRegex` top-level; `#tab-search` direct bind top-level (fresh per page); `.add_button`/`.remove_button` delegation `reinitializeEventListeners`-এর ভেতরে (idempotent off+on) — অক্ষত। |
| 24 | `resources/views/shipping_method/free_shipping.blade.php` | A + C — split | ready (#FreeShipping change trigger init) → `onTurboLoad`; `#FreeShipping` delegation → `onTurboOnce`। |
| 25 | `resources/views/shopify/product.blade.php` | A — ready → `onTurboLoad` | DataTable info text init। |
| 26 | `resources/views/superadmin/dashboard.blade.php` | D — open code → `onTurboLoad` | ApexCharts area chart IIFE init (`{{ }}`/`{!! !!}` data)। |
| 27 | `resources/views/theme_analytic.blade.php` | D — ২x `onTurboLoad` (open code) | block1: ৩টি ApexCharts init (area + 2 donut, `{!! !!}` data) → `onTurboLoad`। block2: `DOMContentLoaded` → `onTurboLoad`; `window.PurposeStyle` + `WorkedHoursChart` init ভেতরেই (page-scoped, source অক্ষত)। |
| 28 | `resources/views/theme_customize/customize-script.blade.php` | E — split | outer `$(function)` → `onTurboLoad` (summernote destroy + `initIconPreviews` + repeater inits); `.icon-picker-button` delegation (`$(document).on`) → nested `onTurboOnce` (bootstrap flush index-loop neted registry সমর্থন করে; source-hash dedup → সেশনে একবার)। `$(document).one` handler-এর ভেতরে প্রতি invocation-এ fresh outside-click closer। |
| 29 | `resources/views/theme_customize/order.blade.php` | B — `$(function)` → `onTurboLoad` | `$(".sortable").sortable()` init। |
| 30 | `resources/views/users/profile.blade.php` | C + A — split | `.list-group-item` delegation → `onTurboOnce`; hash/scrollSpy init → `onTurboLoad`; `DOMContentLoaded` scroll listener → `onTurboLoad`। (plans/stripe block@3049 প্যাটার্ন) |
| 31 | `resources/views/webhook/create.blade.php` | D — open code → `onTurboLoad` | Choices init (multi-select + text-remove)। |
| 32 | `resources/views/webhook/edit.blade.php` | D — open code → `onTurboLoad` | একই Choices init প্যাটার্ন। |
| 33 | `resources/views/wishlist/index.blade.php` | C — delegation → `onTurboOnce` | block1: `#category_id` delegation → `onTurboOnce`। block2: `$(document).ready`-এর ভেতরে শুধু `.bandonwish` delegation → ready-লাইন ১:১ `onTurboOnce`-এ প্রতিস্থাপন (pure delegation; `onTurboLoad` দিলে প্রতি nav-এ re-register হতো); টপ-লেভেল `.bandonmessmess` delegation → নিজস্ব `onTurboOnce`। ⚠️ source-এ `var wish_id` ডুপ্লিকেট লাইন আছে — নিয়ম ১ অনুযায়ী অক্ষত। |
| 34 | `resources/views/woocommerce/category.blade.php` | B — `$(function)` → `onTurboLoad` | DataTable init (`#woocom-category-table`)। |
| 35 | `resources/views/woocommerce/coupon.blade.php` | B + C — split | `$(function)` DataTable init (`#woocom-coupon-table`) → `onTurboLoad`; `.code` ও `#code-generate` delegation → ২x `onTurboOnce`। |
| 36 | `resources/views/addon/apps.blade.php` | C — delegation → `onTurboOnce` | `.module_change` delegation। |
| 37 | `resources/views/addon/index.blade.php` | C — delegation → `onTurboOnce` | একই `.module_change` delegation (addon/themes index)। |
| 38 | `resources/views/coupon/index.blade.php` | C — ২x delegation → এক `onTurboOnce` | `.code` + `#code-generate` — দুটো এক wrapper-এ (পাইলট প্যাটার্ন)। |

## স্পর্শ করিনি — কারণ

| ফাইল / block | কারণ |
|---|---|
| `plans/stripe.blade.php` block@3105+ | payment gateway flow (Stripe/Paystack/Razorpay/Khalti) — Blade `@if` script-এর ভেতরে interspersed; নিয়ম ২ → Claude দেখবে |
| `pos/index.blade.php` block@295 | 380-লাইন mixed block, ready-র ভেতরে function declarations + delegation একই scope — mechanical split scope ভাঙবে; BULK-2 `country/index` precedent → Claude দেখবে |

## টally (নিয়ম ৫ — সৎ হিসাব) — 1-38 পর্যন্ত

- পরিবর্তন করেছি: **37 ফাইল** (1-3, 5-38; pos/index বাদে)
- স্পর্শ করিনি: **1 ফাইল** (pos/index) + stripe-এর payment block + saveAsPDF fn (order_country/orderStatusReport/top_5) + idempotent function-ভেতরের delegation
- সন্দেহ — Claude দেখবে: **2** (pos/index, stripe block@3105+)

## শ্রেণি B (delegation-only, 36-73) — বাকি

- 48 `datatable-js.blade.php` স্পর্শ করা হবে না — শুধু global function declaration (`removeSortingOrderFromHeader`), global থাকা বাধ্যতামূলক।
- ⚠️ 50-51 `myfatoorah/includes/sectionCards|sectionForm` — standalone `myfatoorah/checkout.blade.php`-এ include হয়; সেখানে turbo helper stub পৌঁছায় না কিনা যাচাই করে দেখা হবে, সন্দেহ হলে রিপোর্টে লিখে এগোনো হবে (নিয়ম ২)।

## Commits

- `3dbcb02` — `wip(turbo): pilot 2 files  [BULK-4]` (1-2 planes: create, edit)
- `8d23826` — `wip(turbo): batch 3 files 3-5  [BULK-4]` (3 stripe block@3049, 5 attribute_combinations; 4 pos/index skipped)
- `07980ed` — `wip(turbo): batch 3 files 6-8  [BULK-4]` (attribute_combinations_data, attribute_combinations_edit, product/create)
- `7ed38d9` — `wip(turbo): batch 3 files 9-11  [BULK-4]` (product/edit, product/show, reports/bar_chart_order_report)
- `7332c9b` — `wip(turbo): batch 3 files 12-14  [BULK-4]` (reports/index, reports/order_report, reports/order_country_Report)
- `92ff00e` — `wip(turbo): batch 3 files 15-17  [BULK-4]` (orderStatusReport, sale_by_brand, sale_by_category)
- `3dfce20` — `wip(turbo): batch 3 files 18-20  [BULK-4]` (sale_by_product, stock_report, top_5_reports)
- `228dd37` — `wip(turbo): batch 3 files 21-23  [BULK-4]` (roles/index, setting/email_setting, setting/index)
- `27f6688` — `wip(turbo): batch 3 files 24-26  [BULK-4]` (free_shipping, shopify/product, superadmin/dashboard)
- `92217cb` — `wip(turbo): batch 3 files 27-29  [BULK-4]` (theme_analytic, theme_customize/customize-script, theme_customize/order)
- `a56fda0` — `wip(turbo): batch 3 files 30-32  [BULK-4]` (users/profile, webhook/create, webhook/edit)
- `3a6d834` — `wip(turbo): batch 3 files 33-35  [BULK-4]` (wishlist/index, woocommerce/category, woocommerce/coupon) — **শ্রেণি A সম্পন্ন**
- (pending) — `wip(turbo): batch 3 files 36-38  [BULK-4]` (addon/apps, addon/index, coupon/index) — শ্রেণি B শুরু