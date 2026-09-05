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

## স্পর্শ করিনি — কারণ

| ফাইল | কারণ |
|---|---|
| `resources/views/country/index.blade.php` | mixed A+B, delegation closures reference DataTable vars inside the same ready block; mechanical split would break scope — Claude দেখবে। |
| `resources/views/AppSetting/mobile_content.blade.php` (block @1118) | not in Class A list (AppFunction/ScrollSpy) — outside scope. |
| `resources/views/auth/login.blade.php` (block @100) | Class D third-party reCAPTCHA — forbidden. |
| `.agents/scripts/split-prompts.cjs`, `.ai/prompts/10-BULK-2.txt` | pre-existing unrelated working-tree changes (split-prompts.cjs modified; prompt txt missing on disk) — left untouched, not included in BULK-2 commits. |

## টally (নিয়ম ৫ — সৎ হিসাব)

- পরিবর্তন করেছি: **6 ফাইল** (1-6)
- স্পর্শ করিনি: **1 ফাইল** (country/index.blade.php — কারণ উপরে)
- সন্দেহ — Claude দেখবে: **1** (country/index.blade.php split + mobile_content/AppSetting `.image_delete` cross-page collision note)

## Commits

- `d9caf54` — `wip(turbo): pilot 2 files  [BULK-2]` (files 1-2)
- (checkpoint) `wip(turbo): batch 1 files 3-7  [BULK-2]` (3-6 edited, 7 skipped → Claude)