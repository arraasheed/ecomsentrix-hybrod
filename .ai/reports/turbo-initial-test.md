# Turbo Drive — initial test (CLAUDE-5)

> Turbo Drive বসানোর পর storefront-এ প্রথম পরীক্ষা। **উদ্দেশ্য ছিল কী কী ভাঙে
> তার তালিকা তৈরি করা**, ভাঙা জিনিস ঠিক করা নয়। এই তালিকাই BULK-2/3/4-এর কাজ
> যাচাই করার মাপকাঠি — migration-এর পর এই সারিগুলো ✅ হতে হবে।
>
> পরিবেশ: `http://ecommercego.dev/stylique/*` · theme stylique · Turbo 8.0.23
> · `TURBO_ENABLED=true` · Chrome (chrome-devtools MCP), desktop 1280×800 ও
> mobile 390×844 দুইটিতেই · টেস্ট ডেটা: ৩ product, ১ order (CLAUDE-4-এর সিড)

---

## 1. যা কাজ করছে ✅

| বিষয় | প্রমাণ |
|---|---|
| **full reload বন্ধ হয়েছে** | ১০টি navigation-এ DevTools network-এ **document request = 1** (প্রথম লোড)। বাকি সব body swap |
| **CSS/JS পুনরায় ডাউনলোড হয় না** | navigation-এ `stylesheet` ও `script` request = **0** |
| **`<head>` স্থির থাকে** | ৫ পেজ ঘুরে: stylesheet 6, style 4, script 8, meta 20 — অপরিবর্তিত। শুধু `<title>` বদলায় (Home Page → Blog Page → Cart Page) |
| **CSRF token টেকে** | ৫ navigation পরেও `meta[name=csrf-token]` একই (`SBGgm7r8…`) |
| **cached back navigation** | **১০-১৭ ms** (নিচের টেবিল) |
| **progress bar** | Slow 3G-তে 3135 ms navigation-এ bar দেখা গেছে, width বেড়েছে (188→192 px), load শেষে DOM থেকে সরে গেছে। রঙ theme-এর `#f8796c` |
| **lifecycle helper** | `onTurboLoad` / `onTurboOnce` / `onTurboUnload` / `onTurboMount` / `onTurboUnmount` — পাঁচটিই কাজ করে, নিচে আলাদা টেবিলে যাচাই |
| **kill-switch** | `TURBO_ENABLED=false` → `window.Turbo` **undefined**, Turbo chunk ডাউনলোডই হয় না, link click-এ document request ফিরে আসে, helper গুলো তবুও চলে |
| **checkout form Turbo-র বাইরে** | `form#formdata` এ `data-turbo="false"`; Turbo-র নিজের `submissionIsNavigatable()` → **false** |
| **layout ভাঙেনি** | horizontal overflow = 0 (দুই viewport), broken image = 0 |

### গতি (median of 3, warm)

| navigation ধরন | সময় | তুলনা |
|---|---|---|
| Turbo forward → blog | **515 ms** | full reload (server) 558 ms |
| Turbo forward → home | **712 ms** | full reload (server) 730 ms |
| **Turbo cached back** | **10-17 ms** | full reload 700+ ms → **~50× দ্রুত** |
| cold full load (home) | responseEnd 692 ms · DOMContentLoaded 955 ms | — |

⚠️ **forward navigation-এ প্রায় কোনো লাভ নেই (515 বনাম 558 ms)** এবং এটি
প্রত্যাশিত: server-ই ধীর (TTFB ≈ 550-730 ms, `APP_DEBUG=true` + file cache)।
Turbo শুধু asset পুনরায় parse করা বাঁচায়, HTML তৈরির সময় নয়। **CLAUDE-9
(Redis + OPcache + gzip) হওয়ার পর এই সংখ্যা অর্থবহ হবে** — তখন `< 300 ms`
লক্ষ্য নাগালে আসবে। cached back navigation ইতিমধ্যেই লক্ষ্য ছাড়িয়ে গেছে।

### Turbo যোগ করার খরচ

| আইটেম | আকার |
|---|---|
| `turbo-C9IpioHW.js` (gzip 27.3 KB) | 99.6 KB |
| `turbo-bootstrap-*.js` (gzip 2.3 KB) | 5.5 KB |
| network-এ প্রকৃত transfer | **103.2 KB** |
| storefront মোট JS | 690.5 KB (আগে ~583 KB) |

⚠️ gzip/brotli **বন্ধ** থাকায় পুরো 103 KB uncompressed যাচ্ছে। CLAUDE-9-এ
`mod_deflate` চালু হলে এটি ~30 KB হবে।

---

## 2. 🔴 যা ভেঙেছে — BULK-2/3/4-এর কাজের তালিকা

### 2.1 সবচেয়ে বড়: `custom.js` দ্বিতীয় পেজে SyntaxError দিয়ে মরে যায়

```
Uncaught SyntaxError: Identifier 'searchToggle' has already been declared
   at themes/stylique/assets/js/custom.js
```

**প্রতিটি** navigation-এ একবার। কারণ: Turbo body swap করার পর body-র সব
`<script>` **আবার execute** করে (`PageRenderer#activateNewBodyScriptElements`)।
`custom.js`-এ **৫৩টি top-level `const`/`let`** আছে (`searchToggle`,
`cartPanel`, `mobileMenuToggle`, `dropdownButtons`, …)। একই global scope-এ
দুইবার `const searchToggle` = SyntaxError, এবং **ফাইলের বাকি পুরো অংশ চলে না**।

ফলাফল (দুই viewport-এ একই):

| widget | cold load | Turbo navigation-এর পর |
|---|---|---|
| hero slider (Swiper) | 8/8 init ✅ | **0/8 init** ❌ |
| cart drawer (`#cart-toggle`) | খোলে ✅ | **মরা** ❌ |
| search popup (`#search-toggle`) | খোলে ✅ | **মরা** ❌ |
| language dropdown | খোলে ✅ | **মরা** ❌ |
| mobile menu (`#mobile-menu-toggle`) | খোলে ✅ | **মরা** ❌ |
| about পেজের team swiper | 1/1 ✅ | **0/1** ❌ |

⚠️ **`data-turbo-eval="false"` দিয়ে সমাধান হয় না** — পরীক্ষা করা হয়েছে।
Turbo `activateScriptElement()` এ attribute copy করে, কিন্তু এখানে সমস্যা
external `<script src>` re-execution, আর head merge-ও attribute টিকিয়ে রাখে না
(নেভিগেশনের পর attribute `null`)। **`custom.js` নিজেই ঠিক করতে হবে** —
IIFE-তে মোড়ানো বা `window.__customJsLoaded` guard।

📌 **এটি BULK-এর ২৪০ block-এর স্কোপে নেই** — `custom.js` blade নয়, theme asset।
👉 **CLAUDE-6-এর কাজ** (নিচে "বাকি কাজ" দেখুন)।

### 2.2 delegated handler প্রতি navigation-এ দ্বিগুণ হয় — **এটিই সবচেয়ে বিপজ্জনক**

`$(document).on('click', ...)` inline block-গুলো প্রতি pageview-এ আবার
register হয়। ফলে handler সংখ্যা জমতে থাকে:

| navigation | `document`-এ click handler |
|---|---|
| cold load | 29 |
| nav 1 | 58 |
| nav 2 | 116 |
| nav 4 | 232 |
| nav 6 | 348 |
| nav 8 | **464** |

**বাস্তব প্রভাব — একটি add-to-cart ক্লিকে কতটি POST যায়:**

| অবস্থা | `POST /product_cart` |
|---|---|
| cold page | **1** ✅ |
| ২ navigation পরে | **4** ❌ |
| ৪ navigation পরে | **8** ❌ |

DB-তে যাচাই: একটি ক্লিকে cart row-এর `qty` **1 → 2 → 4** হয়ে গেছে।
(পরীক্ষার সারি মুছে দেওয়া হয়েছে; `carts` টেবিল আবার খালি, order 1, product 3,
customer 1 — সিড ডেটা অক্ষত।)

**কোন কোন selector জমছে** (প্রতিটি ৯ কপি, ৮ navigation পরে):
`.addcart-btn-globaly` · `.change-cart-globaly` · `.remove_item_from_cart` ·
`.empty_cart` · `.apply_coupon` · `.wishbtn-globaly` · `.delete_wishlist` ·
`.place_order_submit` · `.billing_done` · `.payment_done` · `.quantity-increment`
· `.quantity-decrement` · `.plus, .minus` · `a[data-ajax-popup="true"]` ·
`.close-modal` · `#cart-close` · `.Question` · `.change_price`
এছাড়া `change`: `.country_change` · `.state_chage` (১৮ কপি!) ·
`.shipping_change` · `.payment_change` · `.delivery_and_billing` …

👉 **এগুলোই BULK-4-এর শ্রেণি B (ONCE-ONLY) কাজ** — `window.onTurboOnce()` দিয়ে
মোড়ালে সংখ্যা ১-এ স্থির থাকবে। উৎস মূলত
`resources/views/front_end/jQueryRoute.blade.php` (৪৮৪ লাইন, ৯টি inline block)।

### 2.3 memory ও DOM — এখনই সমস্যা নয়, কিন্তু নজরে রাখতে হবে

১০ navigation পরে: heap 32 → 42 MB, DOM node 676 → 630 (স্থির)।
DOM leak নেই, কিন্তু heap বৃদ্ধি ২.২-এর handler জমার ফল। BULK-4-এর পর
আবার মাপতে হবে (CLAUDE-8)।

---

## 3. lifecycle helper যাচাই (BULK মডেলের ভিত্তি)

`turbo-bootstrap.js`-এর সবচেয়ে জরুরি শর্ত: **একই পেজে একই callback দুইবার
চলবে না**। inline `<script>` সিমুলেট করে ৬টি navigation + back button:

| ধাপ | pageId | `onTurboLoad` | `onTurboOnce` | `onTurboUnload` | persistent |
|---|---|---|---|---|---|
| cold home (inject) | 0 | 1 | 1 | 0 | 1 |
| nav 1 → blog | 1 | 1 | 1 | 1 | 2 |
| nav 2 → home | 3 | 1 | 1 | 1 | 3 |
| nav 3 → cart | 4 | 1 | 1 | 1 | 4 |
| nav 4 → home | 6 | 1 | 1 | 1 | 5 |
| nav 5 → blog | 8 | 1 | 1 | 1 | 6 |
| nav 6 → home | 10 | 1 | 1 | 1 | 7 |

পড়ার নিয়ম:
- **`onTurboLoad` = 1** সবসময় — কারণ ওই `<script>` শুধু প্রথম পেজের DOM-এ ছিল;
  পরের পেজে সেটি নেই, তাই callback-ও চলেনি। **এটিই কাঙ্ক্ষিত** — যে পেজে
  widget নেই সেখানে init চালানোর মানে নেই। আসল blade block প্রতি পেজে
  পুনরায় execute হবে, তাই সেখানে প্রতি পেজে একবার চলবে।
- **`onTurboOnce` = 1, ৭ পেজ পরেও** — event delegation এখানে বাঁধলে আর কখনো
  ডুপ্লিকেট হবে না। ২.২-এর সমস্যার সমাধান এটাই।
- **persistent (`data-turbo-persistent`) প্রতি পেজে ঠিক ১ বার** বাড়ে
  (1,2,3,4,5,6,7) — snapshot preview + real render মিলে দুইবার নয়।
  ⚠️ এটি প্রথম চেষ্টায় ভেঙেছিল (1,2,4,6,8,10,12 — দ্বিগুণ), কারণ warm cache-এ
  Turbo প্রতি navigation-এ দুইবার render করে। `register()`-এ source-hash dedupe
  যোগ করে ঠিক করা হয়েছে।
- `pageId` লাফিয়ে বাড়ে (0→1→3→4→6) — preview render-ও একটি পেজ গোনে। ক্ষতি নেই,
  কারণ প্রতিটি flush ওই নির্দিষ্ট pageId-তে একবারই হয়।

### kill-switch (`TURBO_ENABLED=false`)

| যাচাই | ফলাফল |
|---|---|
| `window.__TURBO_DISABLED__` | `true` |
| `window.Turbo` | **undefined** (chunk ডাউনলোডই হয়নি) |
| ৫টি helper | পাঁচটিই `function` |
| `onTurboLoad` চলল? | ✅ ১ বার (DOMContentLoaded-এ) |
| `onTurboOnce` চলল? | ✅ ১ বার |
| Swiper | 8/8 init ✅ |
| link click | **document request** (full reload) ✅ |
| console error | **0** |

অর্থাৎ kill-switch চালু করলে সাইট Day 1-এর অবস্থায় ফিরে যায়, কিছু ভাঙে না।

---

## 4. Turbo-র বাইরে রাখা হয়েছে (`data-turbo="false"`)

Turbo-র প্রকৃত আচরণ পরীক্ষা করে দেখা হয়েছে (click dispatch করে `turbo:click`
ইভেন্ট ধরা), অনুমান নয়:

| link/form | Turbo দখল করে? | ব্যবস্থা |
|---|---|---|
| সাধারণ internal link | ✅ হ্যাঁ | চাই |
| `form#formdata` → `process-order` | ❌ না | **`data-turbo="false"` যোগ করা হয়েছে** |
| `target="_blank"` (shippinglabel PDF) | ❌ না | কিছু লাগে না |
| `download` attribute যুক্ত link | ❌ না | কিছু লাগে না |
| `.pdf` / `.zip` extension | ❌ না | Turbo-র `unvisitableExtensions`-এ আছে |
| **`.epub`/অজানা extension, `download` নেই** | ✅ **হ্যাঁ** | **`data-turbo="false"` যোগ করা হয়েছে** |
| external (facebook, payfast) | ❌ না | cross-origin, এমনিতেই বাদ |

### কেন checkout form সবচেয়ে জরুরি ছিল

`payment.process` → `PaymentService` → paypal/skrill/esewa/ozow/phonepe-তে
`redirect()->away()` করে। Turbo form submit করে `fetch()` দিয়ে, যার default mode
`cors`; কোনো gateway আমাদের origin-এর জন্য `Access-Control-Allow-Origin` দেয় না।
ফলে fetch reject হতো, Turbo console-এ error লিখত, **ব্যবহারকারী কিছুই দেখত না —
checkout সম্পূর্ণ অচল**। এছাড়া payfast handler একই form-এর `action` বদলে
`https://<pfHost>/eng/process` করে দেয়।

পরিবর্তিত ফাইল:
- `resources/views/front_end/pages/checkout.blade.php:25` (আসল checkout)
- `resources/views/front_end/common/checkout/checkout_form.blade.php:118`
  (এখন কোনো view include করে না, কিন্তু আগাম চিহ্নিত)
- `resources/views/front_end/pages/order-detail.blade.php:104` (downloadable
  product, `download` attribute ছাড়া anchor)

---

## 5. 🔴 এখনো বাকি — পরের টাস্কের জন্য

| # | সমস্যা | কার কাজ |
|---|---|---|
| 1 | `custom.js`-এর ৫৩টি top-level `const` → প্রতি navigation-এ SyntaxError, সব storefront widget মরে | **CLAUDE-6** |
| 2 | ২৪০টি inline block-এ `$(document).ready` → `onTurboLoad`, delegation → `onTurboOnce` | **BULK-2/3/4** |
| 3 | Swiper destroy করা হয় না → `turbo:before-cache` এ cleanup দরকার | **CLAUDE-6** |
| 4 | Laravel `redirect()->back()` 302 পাঠায়, Turbo 303 চায় | **CLAUDE-7** |
| 5 | admin layout-এ Turbo যোগ করা হয়নি (DataTable/select2/summernote destroy ছাড়া বিপজ্জনক) | **CLAUDE-6** |
| 6 | greentic / techzonix theme-এ Turbo নেই (তারা এখনো Play CDN-এ) | Day 4 বা পরে |
| 7 | forward navigation প্রায় full reload-এর সমান (server TTFB ৫৫০-৭৩০ ms) | **CLAUDE-9** |

### newsletter form — একটি ইতিবাচক আবিষ্কার

`POST /newsletter` (Laravel `redirect()->back()`, অর্থাৎ **302**) Turbo দিয়ে
সাবমিট করে দেখা গেছে: `turbo:submit-start` → `before-fetch-response status=200`
→ `submit-end` → `render` → `load`, URL অপরিবর্তিত, toastr দেখা গেছে,
**DB-তে row তৈরি হয়েছে**। অর্থাৎ 302-ও কাজ করছে (Turbo redirect follow করে,
তারপর ফলাফল render করে)। CLAUDE-7-এর 303 middleware তবুও দরকার — বিশেষত
validation error-এ — কিন্তু পরিস্থিতি আশঙ্কার চেয়ে ভালো।
(পরীক্ষার row মুছে দেওয়া হয়েছে, `newsletters` টেবিল খালি।)

---

## 6. যেভাবে টেস্ট করা হয়েছে

- ৫টি পেজ: `/home` `/product-list` `/cart` `/blog` `/about` — দুই viewport
- প্রতিটি navigation `Turbo.visit()` দিয়ে, তারপর `turbo:load` ইভেন্টের অপেক্ষা
- widget বাঁচা-মরা **আচরণ দিয়ে** মাপা হয়েছে (ক্লিক করে class বদলাল কি না),
  শুধু element আছে কি না দেখে নয়
- add-to-cart ডুপ্লিকেশন **network request গুনে + DB-তে qty দেখে** প্রমাণিত
- delegated handler `jQuery._data(document, 'events')` দিয়ে গোনা
- gzip নেই বলে সব KB uncompressed
- স্ক্রিনশট: `.ai/screenshots/day2/turbo-01..04*.png`
  (mobile cold vs mobile-after-nav, desktop cold vs desktop-after-6-navs)

### পরীক্ষার পর DB পরিষ্কার করা হয়েছে

| টেবিল | অবস্থা |
|---|---|
| `carts` | ২টি টেস্ট সারি মুছে দেওয়া হয়েছে → **0** |
| `newsletters` | ১টি প্রোব সারি মুছে দেওয়া হয়েছে → **0** |
| `orders` / `products` / `customers` | **1 / 3 / 1** — CLAUDE-4-এর সিড অক্ষত |
| `products.product_stock` | 50 / 40 / 25 — অপরিবর্তিত |
