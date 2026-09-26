# Day 1 Benchmark — Tailwind Play CDN → Vite build (CLAUDE-1)

**তারিখ:** Day 1 · **পরিমাপকারী:** Claude (Playwright + Chrome DevTools MCP)
**পেজ:** `http://ecommercego.dev/stylique/home` · **theme:** stylique
**Viewport:** 412 × 915 (mobile) · **throttling:** none (CPU 1x, network unthrottled)

---

## পদ্ধতি (কেন এই সংখ্যাগুলো বিশ্বাসযোগ্য)

দুইটি মোড **একই পেজে, একই theme-এ, একই সেশনে** মাপা হয়েছে — শুধু
`.env`-এর `LEGACY_TAILWIND_CDN` ফ্ল্যাগ বদলে:

| মোড | ফ্ল্যাগ | কী লোড হয় |
|---|---|---|
| **BEFORE** | `LEGACY_TAILWIND_CDN=true` | `themes/stylique/assets/js/tailwind.js` (Play CDN) + swiper.css + main-style.css |
| **AFTER** | `LEGACY_TAILWIND_CDN=false` | `public/build/assets/store-*.css` (Vite) |

প্রতিটি মোডে **৫টি রান**, প্রতিবার **নতুন browser context** (খালি HTTP cache)
এবং CDP `Network.setCacheDisabled`। নিচের টেবিলে **median** দেওয়া হলো।
Debugbar দুই মোডেই বন্ধ (`DEBUGBAR_ENABLED=false`) — না হলে ~১০০ KB এবং
২টি বাড়তি request একদিকে যোগ হতো।

TBT = সব long task-এর (≥50ms) ৫০ms-এর উপরের অংশের যোগফল, `PerformanceObserver`
দিয়ে মাপা। এটি Lighthouse-এর TBT-র সমতুল্য, তবে হুবহু একই সংখ্যা নয়।

---

## ফলাফল

| মেট্রিক | BEFORE (Play CDN) | AFTER (Vite) | পার্থক্য |
|---|---|---|---|
| **FCP** | 1072 ms | **924 ms** | −148 ms (−13.8%) |
| **LCP** | 1212 ms | **1016 ms** | −196 ms (−16.2%) |
| **TBT** | 108 ms | **37 ms** | −71 ms (−65.7%) |
| **CLS** | 0.00 | 0.00 | অপরিবর্তিত |
| TTFB | 587 ms | 574 ms | −13 ms (noise) |
| **JS** | **1053.6 KB** | **586.9 KB** | **−466.7 KB (−44.3%)** |
| CSS | 156.2 KB | 238.0 KB | +81.8 KB |
| Request সংখ্যা | 41 | 38 | −3 |
| **মোট payload** | 4435.6 KB | 4050.7 KB | **−384.9 KB (−8.7%)** |
| ছবি | 2879.4 KB | 2879.4 KB | অপরিবর্তিত (BULK-1 এর কাজ) |
| ফন্ট | 271 KB | 271 KB | অপরিবর্তিত |

রান-টু-রান বিস্তার (FCP): BEFORE 1064–1184 ms · AFTER 864–980 ms — দুইটি
সেট মোটেই ওভারল্যাপ করে না, তাই উন্নতিটি noise নয়।

### CSS বাড়ল কেন, এবং তাতেও লাভ কীভাবে

Play CDN মোডে ব্রাউজার ৪৬৬ KB **JavaScript** নামিয়ে সেটি parse করে, DOM scan
করে, তারপর runtime-এ CSS বানাত। এখন সেই CSS **আগেই কম্পাইল করা** — ১১৮ KB
স্ট্যাটিক ফাইল (gzip ২২ KB)।

৮২ KB CSS বেশি, ৪৬৭ KB JS কম। কিন্তু আসল লাভ শুধু বাইটে নয়:
CSS parse করা main thread-এর জন্য সস্তা, JS execute করা ব্যয়বহুল। তাই
TBT ১০৮ → ৩৭ ms।

---

## ডিজাইন যাচাই — pixel diff

৫টি পেজের full-page screenshot দুই মোডে নেওয়া হয়েছে (animation ও transition
বন্ধ করে, swiper প্রথম slide-এ আটকে), তারপর canvas দিয়ে pixel-by-pixel তুলনা:

| পেজ | উচ্চতা (vite / legacy) | ভিন্ন pixel | ভিন্ন % |
|---|---|---|---|
| home | 2952 / 2952 | **0** | 0% |
| collections | 1013 / 1013 | **0** | 0% |
| product-list | 1070 / 1070 | **0** | 0% |
| cart | 1281 / 1281 | **0** | 0% |
| login | 1317 / 1317 | **0** | 0% |

**পাঁচটি পেজ pixel-perfect অভিন্ন।** স্ক্রিনশট: `.ai/screenshots/day1/`
(`*-vite.png` ও `*-legacy.png`)।

---

## পথে যে তিনটি বাগ ধরা পড়ল ও সারানো হলো

প্রথম বিল্ডটি কাজ করছিল বলে ধরে নেওয়া ভুল ছিল। যা যা ভাঙা ছিল:

### ১. Vite-এর আউটপুট 404 দিচ্ছিল — পেজ সম্পূর্ণ unstyled ছিল

`asset()` `/build/assets/store-*.css` URL বানায়, কিন্তু প্রজেক্টের
**root `.htaccess`**-এ একটি whitelist আছে যা শুধু নির্দিষ্ট ফোল্ডারকে
`public/`-এ rewrite করে — `build` সেই তালিকায় ছিল না।

ফলাফল: `store.css` → 404, `.flex` → `display:block`, body font
Times New Roman, `.btn-primary` ধূসর। **এই অবস্থার উপরেই প্রথম "after"
বেঞ্চমার্ক নেওয়া হয়েছিল** — সেজন্য তখন মিথ্যা "LCP 643 ms, CLS 0.00"
দেখা গিয়েছিল। CSS না থাকলে পেজ দ্রুত হয়, কিন্তু সেটা অর্জন নয়।

সমাধান — `.htaccess:36`-এ `build` যোগ:
```apache
RewriteRule ^(build|css|assets|market_assets|...)/(.*)$ public/$1/$2 [L,NC]
```

### ২. `@config` ও `@import`-এর ক্রম উল্টো ছিল — theme CSS বান্ডেলেই ঢোকেনি

CSS স্পেক অনুযায়ী `@import` অবশ্যই `@charset`/`@layer` ছাড়া অন্য সব
at-rule-এর **আগে** থাকতে হয়। `@config` উপরে থাকায় postcss-import
নীরবে দুইটি `@import` বাদ দিয়েছিল — bundle-এ `.btn-primary`,
`.section-spacing`, `.swiper-slide` কিছুই ছিল না (৯০ KB, হওয়া উচিত ১১৮ KB)।
এখন `@import` আগে, `@config` পরে।

### ৩. opacity modifier ও forms plugin — দুইটি নীরব design regression

| সমস্যা | কেন | সমাধান |
|---|---|---|
| `bg-primary/10`, `hover:bg-primary/90`, `bg-primary/80` কম্পাইল হচ্ছিল না | `primary: 'var(--primary-color)'` দেওয়া হয়েছিল; Tailwind CSS variable থেকে opacity বের করতে পারে না | hex `#f8796c` — Play CDN-এর config-এর হুবহু মান |
| সব input-এর চেহারা বদলে যাচ্ছিল (উচ্চতা 39→42px, radius 6px→0, background transparent→সাদা) | root config-এ `@tailwindcss/forms` আছে, কিন্তু Play CDN কোনো plugin লোড করত না | storefront-এর জন্য আলাদা `tailwind.store.config.js`, plugin ছাড়া |

---

## Admin CSS Bundling (P3 — CLAUDE-2)

| মেট্রিক | BEFORE (28 links) | AFTER (4 bundles) | উন্নতি |
|---|---|---|---|
| **CSS requests** | 20 | 2 | −18 (−90%) |
| **CSS bytes** | 994 KB | 755.7 KB | −238.3 KB (−24%) |
| **FCP** | 1288 ms | 1312 ms | +24 ms (noise) |
| **LCP** | 1444 ms | 1364 ms | −80 ms (−5.5%) |
| **Total requests** | 60 | 42 | −18 |
| **Variants** | N/A | 4 (LTR/RTL × light/dark) | — |

**Method:** 3 runs per mode, fresh context + cache disabled, mobile viewport 412×915, median values reported.

### CSS Bundling Details

- `admin-ltr-light.css` (737.4 KB minified) → `/build/assets/admin-ltr-light-*.css`
- `admin-ltr-dark.css` (749.1 KB)
- `admin-rtl-light.css` (749.9 KB)
- `admin-rtl-dark.css` (751.5 KB)

Includes: select2, notifier, bootstrap-switch, datatable, custom-color, tabler-icons, feather, fontawesome, customizer, dropzone, flatpickr, summernote, emojionearea, calendar, plus theme + custom + loader CSS.

**Removed:** material.css (unused), leaflet.css (Leaflet JS never loaded).

### Kill-Switch

```env
LEGACY_ADMIN_CSS=false   # Default — use bundled variants
LEGACY_ADMIN_CSS=true    # Fallback — restore 28 separate links
```

### Dark/RTL Toggle

The brand settings page has live preview toggles for dark mode and RTL. The footer script swaps `#main-style-link` and `#main-style-custom-link` hrefs to create a preview. With bundling:
- Bundle loads with correct variant (determined by `$adminCssVariant` in layout)
- Two placeholder `<link>` elements with those IDs exist but have no href (no request)
- Toggle sets their href to legacy CSS files, which cascade on top of bundle for preview
- Form submission reloads page and correct bundle loads

This preserves all functionality without modifying `footerlink.blade.php`.

## Cache-buster: `time()` → mtime (P5 — CLAUDE-3)

`?v=time()` মানে প্রতি সেকেন্ডে নতুন URL, তাই ব্রাউজার কোনো ফাইলই cache করতে
পারত না। `asset_v()` / `get_file_v()` helper এখন `filemtime()` ব্যবহার করে —
ফাইল বদলালেই URL বদলায়, নাহলে নয়।

| মেট্রিক | BEFORE (`time()`) | AFTER (mtime) |
|---|---|---|
| দ্বিতীয় লোডে disk-cache থেকে আসা resource | 0 / 32 | **31 / 32** (বাকি ১টি 304) |
| versioned URL ২ সেকেন্ড পর একই থাকে? | ❌ প্রতিবার নতুন | ✅ স্থির |
| `custom.css` (84 KB) প্রতি পেজে | পুনরায় ডাউনলোড | disk cache |
| `html2pdf.bundle.min.js` (469 KB) | পুনরায় ডাউনলোড | disk cache |
| `choices.min.js` (173 KB) | পুনরায় ডাউনলোড | disk cache |
| Workbox precache সম্ভব? | ❌ | ✅ |

`Cache-Control` header (`.htaccess`, `mod_headers`): `/build/**` →
`31536000, immutable` · font → `31536000` · image → `2592000` ·
অন্য css/js → `604800` · HTML → Laravel-এর `no-cache, private` অপরিবর্তিত।

pixel-diff (kill-switch on vs off, ৪ পেজ): dashboard **0**, login **0**,
settings ও storefront ১ পিক্সেল = anti-aliasing noise (পরপর দুইবার তোলা
বেসলাইন স্ক্রিনশটেও একই ১ পিক্সেল)। ApexCharts animation অঞ্চল mask করা।

kill-switch: `LEGACY_ASSET_TIME_BUSTER=true` — তিনটি helper একসাথে পুরোনো
`time()` আচরণে ফিরে যায়, কোনো blade ছোঁয়া লাগে না। বিস্তারিত:
`.ai/reports/cache-buster-map.md`

---

# Day 1 Final — সব পরিবর্তন একসাথে (CLAUDE-4)

**তারিখ:** Day 1 শেষ · **পরিমাপকারী:** Claude (Playwright + chrome-devtools MCP)
**Viewport:** 412 × 915 (mobile, DPR 2, touch) · **throttling:** none · **runs:** ৩ (median)

## পদ্ধতি — "BEFORE" মানে কী

আগের তিনটি সেকশন প্রতিটি টাস্ক আলাদাভাবে মেপেছিল। এখানে **তিনটি kill-switch
একসাথে** ঘুরিয়ে master-এর আসল অবস্থা বনাম day1 branch-এর আসল অবস্থা মাপা হলো:

| মোড | `.env` |
|---|---|
| **BEFORE** | `LEGACY_TAILWIND_CDN=true` · `LEGACY_ADMIN_CSS=true` · `LEGACY_ASSET_TIME_BUSTER=true` |
| **AFTER** | তিনটিই `false` (branch ডিফল্ট) |

দুইটি অবস্থাই মাপা হয়েছে — **cold** (`Network.setCacheDisabled`, প্রথম ভিজিট)
এবং **warm** (একবার লোড করে, তারপর দ্বিতীয় লোড মাপা — P5-এর লাভ এখানেই দেখা যায়)।

⚠️ পরিমাপের সময় DB-তে ৩টি product, ২টি category, ১টি order ছিল (CLAUDE-4-এর
টেস্ট ফিক্সচার)। আগের সেকশনগুলো খালি DB-তে মাপা, তাই সরাসরি তুলনীয় নয় —
এই সেকশনের BEFORE/AFTER দুইটিই একই ডেটার উপরে।

## Storefront — `/stylique/home` (mobile)

| মেট্রিক | BEFORE | AFTER | পরিবর্তন |
|---|---|---|---|
| **FCP** (cold) | 1408 ms | **1108 ms** | −300 ms (−21%) |
| **LCP** (cold) | 1568 ms | **1296 ms** | −272 ms (−17%) |
| **TBT** (cold) | 175 ms | **35 ms** | −140 ms (−80%) |
| CLS | 0.00 | 0.00 | অপরিবর্তিত |
| **FCP** (warm) | 1028 ms | **932 ms** | −96 ms |
| **LCP** (warm) | 1028 ms | **932 ms** | −96 ms |
| **TBT** (warm) | 74 ms | **5 ms** | −69 ms (−93%) |
| **মোট payload** | 4480.4 KB | **4096.1 KB** | −384.3 KB (−8.6%) |
| **JS** | 1049.8 KB | **583.4 KB** | −466.4 KB (−44%) |
| CSS | 154.1 KB | 236.2 KB | +82.1 KB (Play CDN-এর runtime CSS এখন স্ট্যাটিক ফাইল) |
| CSS request | 7 | 6 | −1 |
| ছবি | 2875.6 KB | 2875.6 KB | অপরিবর্তিত |
| **request সংখ্যা** | 41 | **39** | −2 |
| warm লোডে **নতুন ডাউনলোড** | 6 / 41 | **4 / 39** | ২টি কম |
| TTFB | 944 ms | 778 ms | server noise (একই কোড পথ) |

## Admin — `/dashboard` (mobile)

| মেট্রিক | BEFORE | AFTER | পরিবর্তন |
|---|---|---|---|
| **request সংখ্যা** | 64 | **46** | −18 (−28%) |
| **CSS request** | 20 | **2** | −18 (−90%) |
| **CSS bytes** | 994.0 KB | **755.7 KB** | −238.3 KB (−24%) |
| **মোট payload** | 8964.1 KB | **8724.3 KB** | −239.8 KB |
| JS | 3085.6 KB | 3085.6 KB | অপরিবর্তিত (Day 2-এর কাজ) |
| FCP (cold) | 1152 ms | 1092 ms | −60 ms |
| LCP (cold) | 1220 ms | 1172 ms | −48 ms |
| CLS | 0.00 | 0.00 | অপরিবর্তিত |
| FCP / LCP (warm) | 972 / 1024 ms | 980 / 1020 ms | noise |
| warm লোডে **নতুন ডাউনলোড** | 10 / 64 | **3 / 46** | ৭টি কম |

Admin-এ LCP প্রায় একই থাকার কারণ: FCP/LCP এখানে **TTFB-নির্ভর** (≈765 ms,
`APP_DEBUG=true` + file cache)। CSS request ২০ → ২ হলেও ওই ২০টি সমান্তরালে
নামত, তাই ওয়াল-ক্লক সময়ে বড় লাফ দেয় না। আসল লাভ: −18 request, −238 KB,
এবং mobile/3G-তে যেখানে connection সীমিত সেখানে অনেক বেশি।

## chrome-devtools trace (একই viewport, আসল trace)

| পেজ | LCP | CLS | TTFB | render delay |
|---|---|---|---|---|
| storefront home | **950 ms** | 0.00 | 747 ms | 175 ms |
| admin dashboard | **1323 ms** | 0.00 | 743 ms | 579 ms |

দুই পেজেই DevTools-এর সবচেয়ে বড় সুপারিশ **DocumentLatency** — অর্থাৎ
সমস্যা এখন frontend asset নয়, **server response time**:

| Insight | storefront | admin |
|---|---|---|
| DocumentLatency (FCP/LCP savings) | 645 ms | 641 ms |
| text compression দিলে সঞ্চয় | 66 KB | 72.5 KB |
| LegacyJavaScript (অপ্রয়োজনীয় polyfill) | 15.4 KB | 54.7 KB |

**যাচাই করা হয়েছে: কোনো response-এ `Content-Encoding` নেই** — Apache-তে
gzip/brotli বন্ধ। HTML/CSS/JS-এ এটি ~৭০% সঞ্চয় দিত। এটি CLAUDE-9 (Day 3,
Redis + OPcache)-এর সাথে একসাথে করার কাজ, Day 1-এর স্কোপে নয়।

## kill-switch — তিনটিই দুই দিকে পরীক্ষিত

| ফ্ল্যাগ | `true` করলে যা ফিরে আসে | যাচাই |
|---|---|---|
| `LEGACY_TAILWIND_CDN` | `tailwind.js` Play CDN script উপস্থিত, `/build/` CSS অনুপস্থিত | ✅ `.flex` কাজ করে (CDN runtime), পেজ height 5007 (mobile), ৯টি add-to-cart বাটন |
| `LEGACY_ADMIN_CSS` | ২০টি আলাদা CSS link (bundle 0) | ✅ tabler-icons ফন্ট লোড, `.btn-primary` = rgb(16,183,127), ApexChart render |
| `LEGACY_ASSET_TIME_BUSTER` | `?v=1788553020` টাইপ URL ফিরে আসে | ✅ 4টি URL-এ time() buster, কোনো 404 নেই |

তিনটি একসাথে `true` → পুরো master আচরণ, ০ console error। তারপর তিনটিই
`false` → বর্তমান অবস্থা, ০ console error। `php artisan config:clear` ছাড়া
কিছুই লাগে না।

## Day 0 লক্ষ্যমাত্রার বিপরীতে

| মাপকাঠি (PROJECT_CONTEXT §7) | লক্ষ্য | এখন | অবস্থা |
|---|---|---|---|
| প্রথম লোড JS (storefront) | < 400 KB | 583.4 KB | 🟨 আংশিক — ১.৫ MB `all.min.js` Day 2-এ ভাঙা হবে |
| কোনো ফিচার ভেঙেছে? | ০ | **০** | ✅ |
| Navigation < 300 ms | Turbo দরকার | — | Day 2 |
| PWA installable | — | — | Day 3 |
| Lighthouse mobile ≥ 85 / 75 | — | trace নেওয়া হয়েছে, স্কোর নয় | 🟨 Day 3-এ TTFB ঠিক করার পর অর্থবহ |

Lighthouse-এর সংখ্যাসূচক স্কোর ইচ্ছাকৃতভাবে দেওয়া হলো না: `APP_DEBUG=true` +
`CACHE_DRIVER=file`-এ TTFB ≈765 ms, যা একা Performance স্কোরকে টেনে নামায়।
CLAUDE-9-এর পর সেটি মাপা অর্থবহ হবে। এখন Core Web Vitals সরাসরি মাপা হয়েছে।

---

## পরিবর্তিত ফাইল (সব দিন)

| ফাইল | কী হলো | ডে |
|---|---|---|
| `package.json` | ৮টি dependency exact version-এ pin (`^` সরানো), `terser` যোগ | ১ |
| `tailwind.config.js` | অপরিবর্তিত রাখা হলো admin-এর জন্য (`forms` সহ) — শুধু ব্যাখ্যা কমেন্ট | ১ |
| `tailwind.store.config.js` 🆕 | storefront config — theme content path, hex color, safelist, **plugin ছাড়া** | ১ |
| `resources/css/store.css` 🆕 | `@import` → `@config` → `@tailwind` | ১ |
| `resources/css/_admin-plugins.css` 🆕 | Admin plugins CSS before theme (select2, notifier, icon fonts, etc.) | ১ |
| `resources/css/_admin-plugins-after.css` 🆕 | Admin plugins CSS after theme (flatpickr, summernote, calendar) | ১ |
| `resources/css/admin-ltr-light.css` 🆕 | Admin LTR + light theme bundle entry | ১ |
| `resources/css/admin-ltr-dark.css` 🆕 | Admin LTR + dark theme bundle entry | ১ |
| `resources/css/admin-rtl-light.css` 🆕 | Admin RTL + light theme bundle entry | ১ |
| `resources/css/admin-rtl-dark.css` 🆕 | Admin RTL + dark theme bundle entry | ১ |
| `vite.config.js` | 4 admin entries added (P3 bundling) | ১ |
| `.env.example` | `LEGACY_TAILWIND_CDN=false`, `LEGACY_ADMIN_CSS=false`, `LEGACY_ASSET_TIME_BUSTER=false` | ১ |
| `resources/views/layouts/app.blade.php` | Compute `$adminCssVariant`, add `@if LEGACY_ADMIN_CSS` kill-switch, use `@vite`, add placeholder links | ১ |
| `.htaccess` | rewrite whitelist-এ `build` যোগ (উপরে বাগ ১) | ১ |
| `.htaccess` | `Cache-Control` block যোগ (P5, BEGIN/END কমেন্ট = kill-switch) | ১ |
| `app/Helper/helper.php` | ৩টি নতুন function append: `asset_version()`, `asset_v()`, `get_file_v()` — বিদ্যমান কোড অপরিবর্তিত | ১ |
| ২১টি blade (`layouts/`, `partision/`, `plans/`, `marketplace/`, `setting/`, `order/`, `support/`, `vendor/installer/`) | ৪২টি `time()` cache-buster → helper call | ১ |
| `themes/stylique/views/front_end/layouts/app.blade.php` | kill-switch: `@if (env('LEGACY_TAILWIND_CDN', false))` | ১ |
| `themes/stylique/assets/js/custom.js` | `window.tailwind = window.tailwind \|\| {}` guard | ১ |

### বিল্ড আউটপুট
```
public/build/assets/store-BfJ0BvUh.css   120.99 kB │ gzip: 22.23 kB
public/build/assets/app-B6kyKrMD.css      75.65 kB │ gzip: 12.48 kB
public/build/assets/app-Cs0QkU1O.js       78.50 kB │ gzip: 29.14 kB
```

`store.css`-এ যা **ইচ্ছাকৃতভাবে নেই**:
- `all.min.css` (Font Awesome, ৯৩ KB) — ভেতরে `url(../webfonts/*)` আছে;
  fingerprint করা `/build/assets/`-এ গেলে ফন্ট পাথ ভেঙে যাবে। আলাদা `<link>` থাকল।
- `responsive.css` — এর media query ইতিমধ্যে `main-style.css`-এ আছে।
- `ltr.css` / `rtl.css` — front-end layout এগুলো লোড করেই না।

---

## kill-switch

```env
LEGACY_TAILWIND_CDN=false   # Vite (ডিফল্ট)
LEGACY_TAILWIND_CDN=true    # Play CDN ফিরে আসে

LEGACY_ADMIN_CSS=false      # ৪টি bundle (ডিফল্ট)
LEGACY_ADMIN_CSS=true       # ২৮টি আলাদা link ফিরে আসে

LEGACY_ASSET_TIME_BUSTER=false  # mtime versioning (ডিফল্ট)
LEGACY_ASSET_TIME_BUSTER=true   # ?v=time() ফিরে আসে
```
তিনটিই দুই দিকে পরীক্ষিত — `php artisan config:clear` দিয়ে সাথে সাথে কাজ করে।

---

## সীমাবদ্ধতা

- শুধু **stylique** theme-এ প্রয়োগ। `greentic` ও `techzonix` এখনো Play CDN-এ।
  ওদের `custom.js`-এ আলাদা primary color (greentic `#4caf50`) আছে, তাই
  প্রতিটির জন্য আলাদা config বা CSS-variable ভিত্তিক সমাধান লাগবে।
- ৫টি পেজ যাচাই করা হয়েছে। **checkout যাচাই করা হয়নি** — DB-তে কোনো product
  নেই (`SELECT COUNT(*) FROM products` = 0), তাই cart-এ কিছু যোগ করা যায় না।
- Lighthouse score নেওয়া হয়নি; Core Web Vitals সরাসরি `PerformanceObserver`
  দিয়ে মাপা। Lighthouse চাইলে CLAUDE-4-এ।
- পরিমাপ `APP_DEBUG=true`, `CACHE_DRIVER=file` অবস্থায় — TTFB ~580 ms এর
  কারণেই। CLAUDE-9-এ Redis + OPcache-এর পর এটি আলাদাভাবে মাপতে হবে।

---

## পরের ধাপে যা মনে রাখতে হবে

1. `public/build/` git-ignored, তাই deploy-এ `npm run build` চালাতেই হবে।
2. `.htaccess`-এর whitelist-এ `build` যোগ করাটা এই ফিচারের **অপরিহার্য অংশ** —
   ওই লাইন হারালে পুরো storefront unstyled হয়ে যাবে।
3. `orange-600` (order-detail, refund_order-এ ব্যবহৃত) এখন Tailwind-এর আসল
   কমলা রঙে render হয়। Play CDN-এ পুরো orange scale একটি shade দিয়ে
   প্রতিস্থাপিত ছিল না — শুধু `500` override ছিল, তাই এটি আগেও একই ছিল।

---

# Day 4 Final — Turbo + PWA + Vue islands একসাথে (CLAUDE-16)

**তারিখ:** Day 4 শেষ · **পরিমাপকারী:** Muse Spark (Playwright MCP)
**Viewport:** 412 × 915 (mobile) + 1440 × 900 (desktop spot) · **runs:** 1
(single-run indicative — median-of-3 নয়)

⚠️ **পদ্ধতির সৎ সীমা:** এই সেশনে Lighthouse চালানো যায়নি, আর cold-cache
(`Network.setCacheDisabled`) পরিমাপের উপায় ছিল না। নিচের সংখ্যা **warm**
(একই browser context-এ অনেক পেজ ঘোরার পর) — Day 1 Final-এর cold সংখ্যার
সাথে সরাসরি তুলনীয় নয়। Warm-এর সাথে warm তুলনা করা হয়েছে।

## Storefront — `/stylique/home` (mobile, warm)

| মেট্রিক | Day 1 Final (warm) | Day 4 Final (warm) | মন্তব্য |
|---|---|---|---|
| TTFB | 778 ms (cold-era) | **775 ms** | server একই, APP_DEBUG=true |
| DOMContentLoaded | — | **901 ms** | |
| load | — | **969 ms** | |
| Turbo back-nav (cached) | 10–17 ms (Day 2) | **~107 ms** (full check সহ) | লক্ষ্য <300 ms ✅ |
| console error (home→list→detail→cart→checkout→login→account) | — | **0** | ৭ পেজ |
| Vue island chunk (gzip) | — | cart 3.2 KB + variant 1.2 KB + vendor 25.8 KB | kill-switch off-এ ডাউনলোডই হয় না |

## Desktop spot (1440 × 900, warm)

| মেট্রিক | মান |
|---|---|
| transfer (cache-hit বাদে) | 293 KB (JS 257 KB, CSS 25 KB) |
| DOMContentLoaded / load | 1206 / 1339 ms |
| console error | 0 |

## Regression সারসংক্ষেপ (CLAUDE-16, browser-verified)

- Storefront: home, product-list, product-detail, cart, checkout, login→account,
  wishlist, my-account — সব 0 console error। customer login (session) পাস।
- Admin: superadmin login→dashboard→stores→plan-coupon (DataTable init ✅),
  store-admin login→dashboard→product (3 rows ✅)→order (2 rows ✅) — 0 error।
- Cart drawer (Vue): add/qty/remove/badge/reload — UI+server দুই দিকেই পাস।
- Kill-switch: `TURBO_ENABLED=false` (Turbo বন্ধ, islands চলে, 0 error) ✅,
  `SPA_VUE_ISLANDS=false` (পুরনো jQuery drawer অক্ষত) ✅ — দুটোই দুই দিকে।
  `PWA_ENABLED=false` unregister পথ code-verified (CLAUDE-12-এ live-tested)।
  `LEGACY_*` Day-1-verified, তারপর থেকে সংশ্লিষ্ট কোড ছোঁয়া হয়নি।
- **Live bug ধরা পড়েছে ও ঠিক করা হয়েছে:** Turbo form-submit handler
  `formSubmission.form` পড়ত (Turbo 8-এ নাম `formElement`) → customer login-এ
  2 TypeError। defensive `submittedForm()` helper বসানো, login পুনঃযাচাই 0 error।
- **জানা environmental noise (আমাদের কাজ নয়):** `envato.workdo.io/verify.js`
  লাইসেন্স-ping sandbox-এ `ERR_FAILED` দেয় (ইন্টারনেট রুট নেই); baseline-এও
  একই হবে — core licensing কোড, ছোঁয়া হয়নি।

## লক্ষ্য-টেবিল (CLAUDE-16 ধাপ ৪, সৎ উত্তর)

| মেট্রিক | লক্ষ্য | ফলাফল | পাস? |
|---|---|---|---|
| Turbo navigation (cached) | <300 ms | ~107 ms | ✅ |
| console error (core flow) | 0 | 0 (license-ping বাদে) | ✅ |
| PWA installable | ✅ | ✅ (manifest + SW + offline.html, Day 3) | ✅ |
| প্রথম লোড JS <400 KB | <400 KB | ~583 KB cold-era (Day 1) + ~30 KB island | ❌ পুরোপুরি নয় — TTFB-ই বড় বাধা |
| Lighthouse mobile ≥85 | ≥85 | মাপা হয়নি (tool নেই) | ⚠️ অজানা |
| 90–95% SPA feel | — | no-reload nav + 107 ms back + reactive islands | ✅ অনুভূতিতে হ্যাঁ |

**সৎ সারকথা:** TTFB (~775 ms, `APP_DEBUG=true` + debugbar) একাই সব স্কোর টেনে
ধরে — production-এ (`APP_DEBUG=false` + OPcache + Redis) এটি নামবে, তখন
Lighthouse অর্থবহ হবে। Performance কাজ blade-স্তরে শেষ; বাকিটা server config।
