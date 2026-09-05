# BULK-2 Report — Turbo script migration (Batch 1 / Day 2)

**Task:** first 20 files of Class A (REINIT-NEEDED), editable scope (`resources/views/**` + `themes/**`), `packages/workdo/**` excluded.
**Helper:** `window.onTurboLoad` / `window.onTurboOnce` from `resources/js/turbo-bootstrap.js` (CLAUDE-5).
**Status:** 🟨 IN PROGRESS — checkpoint commits per 5 files.

## Log

| # | ফাইল | কোন নিয়ম প্রয়োগ | নোট |
|---|---|---|---|
| 1 | `resources/views/addon/create.blade.php` | D — `onTurboLoad` wrap | open `<script>`, Dropzone init bound to `#demo-upload`; no ready wrapper. Inner code untouched. |
| 2 | `resources/views/AppSetting/index.blade.php` | C — ready(delegation only) → `onTurboOnce` | `$(function(){ $('body').on('click','.image_delete',…) })`. `#tab-search` direct listener (line 691) left at top level — binds to fresh element every nav, no accumulation. |
| 3 | `resources/views/AppSetting/mobile_content.blade.php` | C — ready(delegation only) → `onTurboOnce` | same `.image_delete` body delegation, different url than AppSetting/index. ⚠️ Two pages, same selector, different source text → both handlers can coexist in one session on same `.image_delete` element (cross-page collision) — Claude দেখবে. |
| 4 | `resources/views/attribute_option/index.blade.php` | B — `$(function)` → `onTurboLoad` | sortable init only, no delegation. |
| 5 | `resources/views/auth/login.blade.php` | A — `$(document).ready` → `onTurboLoad` | block at line 113 (`#form_data` submit). Line 100 block is Class D reCAPTCHA — untouched. |
| 6 | `resources/views/cart/index.blade.php` | C — 2× ready(delegation only) → 2× `onTurboOnce` | `.bandoncart` (line 20) and `.bandoncartmess` (line 43). |
| 7 | `resources/views/country/index.blade.php` | **none — Claude দেখবে** | 281-line mixed block (line 338): DataTable inits must live in `onTurboLoad`, but delegations at 459-464 close over `stateTable`/`cityTable` vars declared inside that same block; third delegation + function declarations sit at top level. Clean split requires scope restructuring beyond a wrapper change. |
| 8 | `resources/views/coupon/create.blade.php` | A + C split | ready@158 (init + direct binds) → `onTurboLoad`; ready@192 (delegation `#category`) → `onTurboOnce`. |
| 9 | `resources/views/coupon/edit.blade.php` | A — 2× `onTurboLoad` | ready@126 & ready@160 both direct binds (`$('#category').change` / `$('#category').on`), no delegation. Block @120 (top-level format hide) unlisted — untouched. |
| 10 | `resources/views/customer/grid.blade.php` | C + A | ready@141 (delegation `.page-checkbox`) → `onTurboOnce`; ready@180 (direct binds + `applyFilter` fn) → `onTurboLoad`. `.csv` block @167 is Class B — untouched. |
| 11 | `resources/views/customer/index.blade.php` | C + A | ready@80 (delegation `.page-checkbox`) → `onTurboOnce`; ready@120 (direct binds + `applyFilter`) → `onTurboLoad`. `.csv` block @107 is Class B — untouched. |
| 12 | `resources/views/dashboard.blade.php` | A — 3× `onTurboLoad` | ready@465 (copy link), @481 (ApexCharts radialBar), @637 (social sharing + tab fetch + `fetchOrderDetails`) — all init/direct binds, no delegation. `.code` delegation script @603 is Class B — untouched. `@if` Blade wrapper around @464 untouched. |
| 13 | `resources/views/flash-sale/create.blade.php` | E — split 2 blocks | block@107: ready(timeout trigger)→`onTurboLoad`, delegation `.condition_option`→`onTurboOnce`, top-level Choices init→`onTurboLoad` (2nd). block@234: ready init + `#add-field-btn` (direct)→`onTurboLoad`, `.delete-icon` delegation→`onTurboOnce`. |
| 14 | `resources/views/flash-sale/edit.blade.php` | E — split 3 blocks | block@213: empty ready→`onTurboLoad` (kept), `.condition_option` delegation→`onTurboOnce`. block@319 Choices init (no wrapper)→`onTurboLoad`. block@331: init + `#add-field-btn`→`onTurboLoad`, `.delete-icon` delegation→`onTurboOnce`. |
| 15 | `resources/views/flash-sale/index.blade.php` | C — ready(delegation only) → `onTurboOnce` | `.flashsale-toggle` toggle. |
| 16 | `resources/views/front_end/common/checkout/checkout_js.blade.php` | **none — Claude দেখবে** | Blade `@if/@else/@endif` interleaved INSIDE the script (lines 24-66, payfast auth-vs-guest branch); delegation `.payfast_form` calls `get_payfast_status` defined inside Blade branches; direct bind follows. Rule 2 (Blade inside script) → skip. Payment/checkout flow - high risk. |
| 17 | `resources/views/front_end/common/product/filter.blade.php` | E — ready→`onTurboLoad`, 2 delegations→`onTurboOnce` | `#product_filter_btn` + `.page-item .page-link` wrapped once; `product_page_filter` stays top-level (hoisted, visible to all); `.filter_product` direct bind stays top-level (re-binds per page). `{{ module_is_active() }}` ternary interpolation untouched. |

## স্পর্শ করিনি — কারণ

| ফাইল / block | কারণ |
|---|---|
| `resources/views/country/index.blade.php` (block @338) | mixed A+B, delegation closures reference DataTable vars inside the same ready block; mechanical split would break scope — Claude দেখবে। |
| `resources/views/front_end/common/checkout/checkout_js.blade.php` (block @9) | Blade `@if/@else/@endif` inside the script; payfast payment flow — rule 2 → Claude দেখবে। |
| `resources/views/AppSetting/mobile_content.blade.php` (block @1118) | not in Class A list (AppFunction/ScrollSpy) — outside scope. |
| `resources/views/auth/login.blade.php` (block @100) | Class D third-party reCAPTCHA — forbidden. |
| `customer/grid` @167, `customer/index` @107, `dashboard` @603 (`.csv`/`.code` top-level delegations) | Class B (ONCE-ONLY) — recipe: leave as-is / out of BULK-2 scope. |
| `coupon/edit` @120 (top-level format check) | unlisted block — outside scope. |
| `.agents/scripts/split-prompts.cjs`, `.ai/prompts/10-BULK-2.txt` | pre-existing unrelated working-tree changes — left untouched, not included in BULK-2 commits. |

## টally (নিয়ম ৫ — সৎ হিসাব)

- পরিবর্তন করেছি: **15 ফাইল** (1-6, 8-15, 17)
- স্পর্শ করিনি: **2 ফাইল** (country/index, checkout_js — কারণ উপরে) + 5টি unlisted/Class B block
- সন্দেহ — Claude দেখবে: **2** (country/index.split, checkout_js Blade/@if) + `.image_delete` cross-page collision note + `.condition_option` same-selector cross-page collision note (create/edit)

## Commits

- `d9caf54` — `wip(turbo): pilot 2 files  [BULK-2]` (files 1-2)
- `9f5edf6` — `wip(turbo): batch 1 files 3-7  [BULK-2]` (3-6 edited, 7 skipped → Claude)
- `3763039` — `wip(turbo): batch 1 files 8-12  [BULK-2]`
- (checkpoint 3) `wip(turbo): batch 1 files 13-17  [BULK-2]` (13-15, 17 edited, 16 skipped → Claude)