# Mobile Table Card View — BULK-7

**তারিখ:** ১৮ সেপ্টেম্বর ২০২৬ · **টাস্ক:** BULK-7 (Day 4) · **Branch:** `day4/mobile-polish`

**পরিবর্তন করেছি:** ২ ফাইল (দুটোই নতুন — কোনো বিদ্যমান ফাইল স্পর্শ করা হয়নি)
**স্পর্শ করিনি:** `resources/views/components/datatable.blade.php`, সব layout, সব বিদ্যমান CSS/JS (নিষেধ — Claude link যোগ করবে)
**সন্দেহ — Claude দেখবে:** নিচের "সীমাবদ্ধতা" সেকশনটি

---

## ১. তৈরি ফাইল

| ফাইল | কাজ |
|---|---|
| `public/css/mobile-table.css` | `@media (max-width: 767.98px)`-এ `.ecom-data-table`-এর ভেতরের DataTable-কে card view বানায়। এর বাইরে কোনো প্রভাব নেই |
| `public/js/mobile-table.js` | Vanilla JS — `thead th`-এর টেক্সট থেকে প্রতিটি `tbody td`-তে `data-label` attribute বসায় |

## ২. 🔴 Claude-কে কোথায় link যোগ করতে হবে

Admin layout: `resources/views/layouts/app.blade.php` (storefront-এ DataTable নেই, তাই শুধু admin-এই যথেষ্ট):

1. **CSS** — line 172 (`@endif`-এর ঠিক পরে, উভয় branch — legacy 28-css এবং bundle — এর *পরে*) এ:
   ```blade
   <link rel="stylesheet" href="{{ asset_v('css/mobile-table.css') }}">
   ```
   এই অবস্থানে দিলে legacy এবং bundled — দুই মোডেই theme CSS-এর পরে লোড হবে (cascade ঠিক থাকবে)।
   `asset_v()` cache-buster helper (CLAUDE-3) ব্যবহারযোগ্য — ফাইলটি `public/css/`-এ আছে।

2. **JS** — line 496, `</body>`-এর ঠিক আগে:
   ```blade
   <script src="{{ asset('js/mobile-table.js') }}" defer></script>
   ```

Kill-switch: যেকোনো একটি link সরালেই ফিচার সম্পূর্ণ বন্ধ (CSS সরালে table আগের horizontal scroll-এ ফেরে; JS সরালে label আর বসে না)।

## ৩. কোন কলাম বিশেষভাবে handle করা হয়েছে

| কলাম | চেনার উপায় | Handle |
|---|---|---|
| **Checkbox (BulkDelete "select all")** | `th`-এর ভেতরে `<input type="checkbox" id="select-all">` (`app/Helper/helper.php:1802`) — textContent খালি | CSS: `thead th:has(input[type="checkbox"])` দিয়ে সেই একটি `th` দৃশ্যমান থাকে (card-এর উপরে ছোট bar, checkbox ডানে)। বাকি `th` সব লুকানো। `:has()` না সাপোর্ট করলে সেটিও লুকায় (progressive)। Row-checkbox `td`-তে কোনো label বসে না, content বাঁয়ে থাকে |
| **Action** | `th` title "Action" (`helper.php:2078`) | সাধারণ label/value flex; `.btn-group` wrap করে end-এ সাজে |
| **Colspan / "No data" row** | `td.colSpan > 1` বা `.dataTables_empty` | label বসে না; `dataTables_empty` center হয় |
| **Hidden columns** (যেমন `id`, ColVis-এ বন্ধ করা) | DataTables inline `display:none` | inline style জয়ী থাকে — লুকানোই থাকে; label দেওয়া হয় (অদৃশ্য), order বদলালে observer ঠিক করে |
| **Header-title HTML** (আইকন ইত্যাদি) | — | `textContent` + whitespace-normalize, তাই HTML ট্যাগ label-এ আসে না |

## ৪. যান্ত্রিক বিবরণ (Claude-এর টেস্টের জন্য)

- **স্কোপ:** CSS/JS দুটোই শুধু `.ecom-data-table` (component-এর `datatable.blade.php:7`) এর ভেতরে কাজ করে। অন্য ৩২টি কাস্টম টেবিল (component ব্যবহার করে না) অপরিবর্তিত।
- **Dark mode:** প্রজেক্টে dark = পুরো stylesheet swap (`#main-style-link` → `style-dark.css`), body class নয়। তাই CSS-এ কোনো dark selector নেই — theme-এর নিজের CSS variable ব্যবহার করা হয়েছে যা দুই stylesheet-এ ভিন্ন মান পায়:
  `--bs-card-bg` (#ffffff / #292A33, `.card` scope), `--bs-table-border-color` (#f1f1f1 / #3E3F4A, `.table` scope), `--bs-table-color`, `--bs-secondary`। RTL-ও logical property (`margin-inline`, `justify-content: flex-end`) দিয়ে সম্মানিত।
- **Redraw hooks:** DataTables-এর `draw.dt` jQuery `.trigger()` দিয়ে ছাড়ে — native `document.addEventListener('draw.dt')` ধরতে পারে না (jQuery trigger আসল DOM event dispatch করে না)। তাই একটি `MutationObserver` (`childList`+`subtree`, `attributes:false` — নিজের attribute লেখায় loop হয় না) `document.body`-তে বসানো হয়েছে, rAF-debounced। Turbo: ফাইলটি শুধু head-এ থাকলে একবারই চলে; তবু body-তে re-execute হলেও IIFE + `window.__ecomMobileTableCardView` flag + top-level `const` বর্জন — custom.js-এর (৫৩ top-level const → SyntaxError) মতো সমস্যা হবে না।
- **Breakpoint:** CSS `(max-width: 767.98px)` বনাম JS `window.innerWidth < 768` — সামঞ্জস্যপূর্ণ (767.98 < 768)। Resize-এ breakpoint ক্রস করলে label strip/re-apply হয়।
- **`!important` মাত্র ২টি** — `table width` ও `td width` (DataTables inline width override)।
- JS `node --check` পাস ✅

## ৫. সীমাবদ্ধতা — কোন টেবিলে কাজ করবে না / যা লক্ষ্য রাখবে

1. **শুধু `.ecom-data-table`-এর ভেতরের টেবিল।** `order_return_list`, `pos/create`, `shopify/woocommerce coupon`-এর মতো কাস্টম blade টেবিল (GEMINI-4 রিপোর্টের ১৭-১৯ নং) এই ফিচার পাবে না — ওগুলো আলাদা partial, স্কোপের বাইরে ছিল।
2. **Select-all checkbox `:has()` ছাড়া ব্রাউজারে** (Chrome <105, Safari <15.4, Firefox <121) লুকাবে — row-checkbox দিয়েই bulk select করা যাবে না একবারে; কোনো error নেই, শুধু বাটনটি অদৃশ্য।
3. **Turbo cache + resize edge case:** মোবাইলে দেখা পেজ Turbo cache-এ থাকা অবস্থায় ডেস্কটপে ফিরে এলে cached preview-তে label দেখাতে পারে; এক ক্লিকেই সঠিক হয়ে যায় (resize handler strip করে)। `turbo:before-cache`-এ strip যোগ করা যায় পরে।
4. **জটিল inline cell কনটেন্ট** (খুব চওড়া inline element, fixed-width element) দুর্লভ ক্ষেত্রে ভাঙতে পারে — flex `overflow-wrap: anywhere` দেওয়া, তবু চোখে দেখে CLAUDE-13/16-এর টেস্টে যাচাই প্রয়োজন (বিশেষত ProductDataTable-এর ১১ কলাম — cover image ও variant কলাম)।
5. **Entry-per-page select ও pagination** stack করা হয়েছে কিন্তু touch-target বাড়ানো হয়নি — সেটা audit M1, CLAUDE-14-এর কাজ।
6. `guest` layout-এর (login) কোনো DataTable নেই — সেখানে link দরকার নেই।
