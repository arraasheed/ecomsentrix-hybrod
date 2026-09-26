# HANDOVER — eCommerce Go SPA/PWA প্রজেক্ট (v1.0-spa-pwa)

> ব্যবহারকারীর দীর্ঘমেয়াদি রেফারেন্স। সর্বশেষ: Day 4 merge (`3b82fbb`),
> tag `day4-complete` + `v1.0-spa-pwa`, branch `master`.

---

## 1. কী করা হয়েছে (ব্যবসায়িক দৃষ্টিতে)

আপনার WorkDo-ভিত্তিক SaaS এখন **অ্যাপের মতো আচরণ করে**: পেজ বদলে রিলোড হয়
না (Turbo Drive), মোবাইলে নিচে অ্যাপের মতো নেভিগেশন বার, কার্ট সঙ্গে সঙ্গে
খোলে ও সংখ্যা বদলায় (Vue islands), ফোনে "ইনস্টল" করে হোম স্ক্রিনে রাখা যায়
(PWA), নেট না থাকলে অফলাইন পেজ দেখায়। ডিজাইন **এক পিক্সেলও বদলায়নি** —
শুধু গতি ও অনুভূতি বদলেছে।

| দিন | কাজ | ফল |
|---|---|---|
| Day 1 | Asset pipeline (Tailwind build, CSS bundle, cache fix, image lazy) | JS −44%, admin request −28% |
| Day 2 | Turbo Drive SPA navigation | back-navigation ~107 ms, full reload বন্ধ |
| Day 3 | PWA (Workbox SW, per-store manifest, offline page, install banner) | installable ✅ |
| Day 4 | Mobile app-feel (bottom nav, skeleton, touch polish) + Vue islands (cart, variant) | reactive cart, kill-switch সহ |

## 2. আগে vs পরে (সংখ্যা)

| মেট্রিক | Before (baseline-v0) | After (v1.0) |
|---|---|---|
| storefront JS | 1049.8 KB | 583.4 KB (−44%) + islands ~30 KB (gzip) |
| storefront মোট payload | 4480.4 KB | 4096.1 KB (−8.6%) |
| storefront request | 41 | 39 |
| storefront LCP (cold) | 1568 ms | 1296 ms (−17%) |
| storefront TBT (cold) | 175 ms | 35 ms (−80%) |
| admin CSS request | 20 | 2 (−90%) |
| cached back-navigation | full reload (~550 ms) | ~107 ms |
| console error (core flow) | — | 0 |
| CLS | 0.00 | 0.00 |

বিস্তারিত: `.ai/reports/benchmark.md` ("Day 1 Final" + "Day 4 Final")।

## 3. কীভাবে চালাবে

```powershell
cd C:\laragon\www\ecommercego
npm run dev          # development (Vite hot reload)
npm run build        # production — public/build তৈরি হয় (git-ignored!)
php artisan optimize # production cache
php artisan view:clear   # blade বদলের পর
```

**Deploy checklist:** `composer install` → `.env` বসানো → `npm install` →
`npm run build` → `php artisan migrate --force` → `php artisan storage:link`
→ `php artisan optimize`। Queue worker চালু থাকতে হবে
(`queue:work`, Redis queue — CLAUDE-9)।

## 4. সমস্যা হলে কী করবে (ফেরার পথ)

`.env`-এ flag বদলে `php artisan view:clear` — সাথে সাথে কার্যকর:

| Flag = বন্ধ মান | কী বন্ধ হয় | কখন ব্যবহার |
|---|---|---|
| `TURBO_ENABLED=false` | SPA navigation (সাধারণ multi-page সাইট হয়) | পেজ অদ্ভুত আচরণ করলে |
| `TURBO_ADMIN_ENABLED=false` | শুধু admin-এ Turbo বন্ধ | admin টেবিল/মডাল ভাঙলে |
| `SPA_VUE_ISLANDS=false` | Vue cart/variant বন্ধ, পুরনো jQuery চলে | কার্টে সমস্যা হলে |
| `PWA_ENABLED=false` | Service Worker unregister | পুরনো ডেটা দেখালে |
| `LEGACY_TAILWIND_CDN=true` | Play CDN-এ ফেরত (ধীর) | নতুন CSS ভাঙলে |
| `LEGACY_ADMIN_CSS=true` | ২৮টি আলাদা CSS-এ ফেরত | admin লুক ভাঙলে |
| `LEGACY_ASSET_TIME_BUSTER=true` | `?v=time()`-এ ফেরত | cache সমস্যায় |

**সম্পূর্ণ ফেরত:** `git reset --hard baseline-v0` (⚠️ uncommitted কাজ যাবে)।
**দিনে ফেরা:** `git reset --hard day1-complete` / `day2-complete` /
`day3-complete` / `day4-complete`।

## 5. WorkDo আপডেট এলে কী করবে

আমরা core ছুঁইনি — বদলানো ফাইল মাত্র ~১৫টি + নতুন ফাইল:

```
vite.config.js, package.json, tailwind.config.js
resources/views/layouts/app.blade.php (head/foot কয়েক লাইন)
themes/stylique/views/front_end/layouts/app.blade.php
resources/views/front_end/jQueryRoute.blade.php
resources/js/* (turbo-bootstrap, island-registry, islands/*, skeleton…)
resources/css/* (admin bundle source, mobile-app-feel, skeleton)
app/Http/Middleware/TurboRedirect.php (নতুন)
packages/workdo/PwaManifest/ (নতুন মডিউল — উদাহরণ)
```

পদ্ধতি: upstream diff নিন → উপরের তালিকার বাইরে conflict হলে **theirs**
নিন (আমাদের বদল নেই বলে); তালিকার ভেতরে conflict হলে দুই পাশ দেখে মিলিয়ে
নিন — wrapper (onTurboLoad) অক্ষত রাখুন, ভেতরের কোড upstream-এর নিন।

## 6. নতুন ফিচার যোগ করার পদ্ধতি

`packages/workdo/YourModule/` — `PwaManifest` মডিউলকে template ধরুন
(ServiceProvider + Routes + Controller)। `app/`, `routes/`, `database/`
ছোঁবেন না। প্রতিটি ফিচারে `.env` kill-switch বাধ্যতামূলক। কাজ শেষে
`git commit` + `.ai/PROGRESS.md` আপডেট। `git push` শুধু আপনি বললে।

## 7. যা করা হয়নি / ভবিষ্যতের সুযোগ

- `greentic`, `techzonix` theme এখনো Play CDN-এ (শুধু stylique done)।
- Vue island বাকি: search suggest, checkout steps।
- Variant/OOS live-test-এর জন্য variant fixture লাগবে (DB-তে variant নেই)।
- Offline-while-adding-to-cart live-test হয়নি (code-verified)।
- Production TTFB (`APP_DEBUG=false` + OPcache) মাপা হয়নি — sandbox-এ debug on ছিল।
- FraudPixelShield মডিউল (আলাদা পরিকল্পনা, repo-র বাইরে রেফারেন্স কোড)।

## 8. পরিচিত সীমাবদ্ধতা (সৎভাবে)

1. **TTFB ~775 ms** (`APP_DEBUG=true` + debugbar) — সব স্কোরের বোতল-গলা।
   Production-এ নামবে, local-এ এটাই থাকবে।
2. **Lighthouse score মাপা হয়নি** — এই env-এ tool ছিল না। দাবি করা হয়নি।
3. **Benchmark warm, single-run** — Day 4 সংখ্যা indicative, median-of-3 নয়।
4. **`envato.workdo.io/verify.js` লাইসেন্স-ping** sandbox-এ fail করে
   (ইন্টারনেট রুট নেই) — baseline-এও একই; প্রোডাকশনে ঠিক থাকবে।
5. **Admin-এ Turbo বন্ধ রাখা হয়েছে** (ঝুঁকি এড়াতে) — শুধু storefront-এ চলে।
6. Guest cart-এ `cart_id=0` আসে (core Blade-এর আচরণ) — guest পথ
   product+variant দিয়ে চলে, তাই সমস্যা নয়; logged-in পথে আসল id যায়।
