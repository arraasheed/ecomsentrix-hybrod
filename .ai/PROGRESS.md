# PROGRESS — কাজের অবস্থা ট্র্যাকার

> **👤 ব্যবহারকারী: আপনার এই ফাইলটি পড়ার দরকার নেই।**
> আপনার জন্য একটি আলাদা সহজ গাইড আছে: **`.ai/HOW_TO_RUN.md`**
> ওখানে ২৭টি ধাপ ক্রমে সাজানো আছে — কোন ফাইল কাকে দেবেন, কীভাবে দেবেন।
> প্রম্পটগুলো রেডি অবস্থায় আছে `.ai/prompts/` ফোল্ডারে (পুরো ফাইল কপি করে
> পেস্ট করবেন, অংশ বেছে নেওয়ার দরকার নেই)।

> **🔴 প্রতিটি মডেল কাজ শুরুর আগে এই ফাইল পড়বে এবং কাজ শেষে আপডেট করবে।**
>
> ব্যবহারকারী যদি শুধু বলেন **"কাজ শুরু করো"** বা **"আগের কাজ কন্টিনিউ করো"** —
> মডেল এই ফাইল পড়ে নিজেই বুঝে নেবে কোথায় থেমেছিল। ব্যবহারকারীকে কিছু বলতে হবে না।

---

## 🚀 যেকোনো মডেলের জন্য বুট-আপ নির্দেশনা (প্রতিবার এটাই করবে)

> **স্মৃতি মডেলের মাথায় নয় — ডিস্কে।** টোকেন লিমিট ফুরিয়ে গেলে, পিসি রিস্টার্ট
> হলে, বা কনভার্সেশন হারিয়ে গেলে মডেল সব ভুলে যায়। কিন্তু কাজের অবস্থা তিন
> জায়গায় লেখা থাকে, তাই কিছুই হারায় না:
>
> | কোথায় | কী বলে |
> |---|---|
> | `git log` | আসলে কী কমিট হয়েছে — **এটাই চূড়ান্ত সত্য** |
> | `.ai/PROGRESS.md` | কোন টাস্ক ✅ DONE, কোনটা ⬜ PENDING |
> | `.ai/reports/*.md` | কোন ফাইলে কী করা হয়েছে, সারি ধরে |
>
> Antigravity-তে `.agents/hooks.json` এই কাজটি স্বয়ংক্রিয়ভাবে করে।
> **অন্য টুলে (OpenCode, Cursor, ওয়েব চ্যাট) hook চলবে না** — তখন ব্যবহারকারী
> নিচের বুট প্রম্পটটি পেস্ট করবেন। এটি সব মডেলে, সব টুলে কাজ করে।

### 📋 ব্যবহারকারীর জন্য: নতুন সেশনে প্রথমে এই প্রম্পটটি পেস্ট করুন

> **⚡ সহজ পথ:** নিচের প্রম্পটটি ইতিমধ্যে `.ai/prompts/` ফোল্ডারের প্রতিটি
> ফাইলের ভেতরে বসানো আছে। তাই আলাদা করে কপি করার দরকার নেই — শুধু
> `.ai/prompts/` থেকে সংশ্লিষ্ট `.txt` ফাইলটির পুরোটা কপি করে পেস্ট করুন।
> বিস্তারিত: `.ai/HOW_TO_RUN.md`
>
> নিচের অংশটি রেফারেন্স হিসেবে রাখা হলো।

নিচের বক্সের ভেতরের অংশ হুবহু কপি করে **যেকোনো** মডেলকে দিন (টাস্ক প্রম্পটের
আগে)। এটি দিলে মডেল নিজেই খুঁজে নেবে কোথায় থেমেছিল।

```
তুমি C:\laragon\www\ecommercego প্রজেক্টে কাজ করছ।
ধরে নাও তোমার কোনো পূর্ব স্মৃতি নেই। কাজ শুরুর আগে এই ৪টি ধাপ করো:

১. পড়ো:  .ai/PROJECT_CONTEXT.md
২. পড়ো:  .ai/PROGRESS.md
৩. চালাও: git log --oneline -20
৪. চালাও: git status --short

তারপর PROGRESS.md-এর টেবিলে তোমার নামের (Claude / Gemini / BULK) প্রথম
⬜ PENDING বা 🟨 IN PROGRESS টাস্ক খুঁজে বের করো।

- 🟨 IN PROGRESS পেলে → সেই টাস্কের `.ai/reports/` ফাইলটি পড়ো, কোন ফাইল
  পর্যন্ত শেষ হয়েছে দেখো, তার পরের ফাইল থেকে শুরু করো।
  ⛔ আগে শেষ হওয়া ফাইল আবার করবে না — দুইবার wrap করলে কোড ভাঙবে।
- ⬜ PENDING পেলে → তোমার টাস্ক ফাইল থেকে সেই নম্বরের প্রম্পট পড়ে শুরু করো।

git log আর PROGRESS.md না মিললে git-কে সত্য ধরবে।

শুরু করার আগে আমাকে এক লাইনে জানাও: কোন টাস্কে আছ, কোন ফাইল থেকে শুরু করছ।
```

মডেল উত্তরে বলবে যেমন — *"আমি BULK-2 এ আছি, ৩৭ নম্বর ফাইল থেকে শুরু করছি"*।
তখন আপনি নিশ্চিত হবেন এটি সঠিক জায়গায় আছে। ভুল বললে শুধু সংশোধন করে দিন।

### 🔁 মাঝপথে লিমিট ফুরিয়ে গেলে কাজ না হারানোর নিয়ম (checkpoint commit)

**সমস্যা:** commit হয় টাস্ক শেষে। ২০ ফাইলের কাজে ১৩ ফাইল করার পর লিমিট
ফুরালে ওই ১৩ ফাইল uncommitted, রিপোর্ট লেখা হয়নি — নতুন সেশন কিছুই জানবে না।

**নিয়ম (বড় batch টাস্কে বাধ্যতামূলক — BULK-1 থেকে BULK-4):**

```
প্রতি ৫টি ফাইল শেষ করার পর সাথে সাথে:

১. .ai/reports/<task-report>.md এ ওই ৫টি ফাইলের সারি যোগ করো
২. .ai/PROGRESS.md এ টাস্কের অবস্থা 🟨 IN PROGRESS রাখো,
   নোট কলামে লিখো "N/মোট ফাইল শেষ"
৩. git add -A
   git commit -m "wip(<scope>): <task> files X-Y  [<MODEL>-<TASK#>]"
```

উদাহরণ: `git commit -m "wip(turbo): batch 1 files 1-5  [BULK-2]"`

সব ফাইল শেষ হলে তখন আসল commit (`refactor(turbo): ... [BULK-2]`) করবে এবং
অবস্থা ✅ DONE করবে। তাতে সবচেয়ে খারাপ ক্ষেত্রেও ৪ ফাইলের কাজ হারাবে, ২০টার নয়।

**⚠️ git log এবং এই ফাইল যদি না মেলে — git log-কে সত্য ধরবে।** কমিট মিথ্যা বলে না।

---

## Environment

| বিষয় | মান |
|---|---|
| প্রজেক্ট পাথ | `C:\laragon\www\ecommercego` |
| APP_URL (`.env`) | `http://ecommercego.dev` |
| Storefront URL | `http://ecommercego.dev/{store-slug}/home` ⚠️ `/home` লাগে — শুধু slug দিলে 404 |
| Admin URL | `http://ecommercego.dev/login` |
| ✅ URL যাচাই | Day 1-এ Laragon চালু, `http://ecommercego.dev` কাজ করছে। টেস্টে ব্যবহৃত: `http://ecommercego.dev/stylique/home` |
| টেস্ট store | `id=2`, slug `stylique`. ⚠️ `theme_id` ছিল `greentic` — CLAUDE-1-এ `stylique` করা হয়েছে (DB আপডেট, কোড নয়) |
| ✅ DB-তে ডেটা | CLAUDE-4-এ টেস্ট ফিক্সচার বসানো হয়েছে (শুধু DB, কোনো কোড নয়): **৩ product · ২ category · ১ brand · ১ label · ৩ product image · ১ customer (`buyer@example.com` / `1234`) · ১ order (COD)**। এতে cart → checkout → order পুরো flow টেস্ট করা যায়। rollback SQL: `%TEMP%\opencode\seed-day1-test.sql`-এর নিচে |
| DB | MySQL — `ecommercego` · CLI: `C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe -uroot ecommercego` |
| CACHE_DRIVER / CACHE_STORE | `file` ⚠️ (Redis-এ বদলানো হবে — CLAUDE-9) |
| SESSION_DRIVER | `file` ⚠️ |
| ⚠️ gzip / brotli | **বন্ধ** — কোনো response-এ `Content-Encoding` নেই। DevTools: storefront 66 KB, admin 72.5 KB সঞ্চয় সম্ভব, FCP/LCP ~640 ms। CLAUDE-9-এ Apache কনফিগে ঠিক করতে হবে |
| Server | Laragon (Apache) — **docroot প্রজেক্ট root, `public/` নয়**। root `.htaccess` whitelist দিয়ে rewrite করে |
| Node / npm | `v26.4.0` / `11.17.0` ✅ |
| Playwright Chromium | `chromium-1234`, `chromium-1243` ইনস্টল ✅ |
| git branch | `day2/turbo-drive` (master-এ Day 1 merged, tag `day1-complete`) |
| git baseline tag | `baseline-v0` → commit `17a47e9` |
| **git remote** | `origin` → `https://github.com/arraasheed/ecommercego.git` (public) |

### git remote নিয়ম

| কাজ | অনুমতি |
|---|---|
| `git commit` (লোকাল) | ✅ সব মডেল, প্রতি টাস্কে বাধ্যতামূলক |
| `git push origin <branch>` | ⚠️ শুধু ব্যবহারকারী স্পষ্ট বললে |
| `git push origin master` | ⛔ কখনো নিজে থেকে নয় |
| `git push --force` | ⛔ কখনো নয় |

**কেন push স্বয়ংক্রিয় নয়:** এটি একটি **public** repo। কোনো টাস্কে ভুলে কোনো
গোপন তথ্য commit হয়ে গেলে, push করার আগে ধরা পড়লে ঠিক করা যায় — push হয়ে গেলে
GitHub-এর ইতিহাসে থেকে যায়। তাই প্রতিটি push ব্যবহারকারীর সিদ্ধান্ত।

**push করার সময় যা যাচাই করা আছে (Day 0):**
- `.env` ট্র্যাক করা **নেই** — শুধু `.env.example`, তার সব মান খালি বা `null`
- `vendor/` (177 MB), `node_modules/` (39 MB), uploads, debugbar — সব ignored
- ট্র্যাক করা: 3082 ফাইল / ~156 MB

⚠️ remote-এ `main` ডিফল্ট ব্রাঞ্চ হিসেবে সেট করা আছে কিন্তু repo **খালি**।
আমাদের লোকাল ব্রাঞ্চ `master`। প্রথম push-এ `master` যাবে; ব্যবহারকারী চাইলে
GitHub সেটিংসে ডিফল্ট `master` করতে পারেন, অথবা আমরা `main`-এ push করতে পারি।

### টেস্ট ক্রেডেনশিয়াল
```
Super Admin : superadmin@example.com / 1234
Store Admin : admin@example.com / 1234
Customer    : buyer@example.com / 1234        (CLAUDE-4-এ তৈরি, store 2)
Store slug  : stylique   (URL: http://ecommercego.dev/stylique/home)
টেস্ট product: test-cotton-shirt · test-organic-rice · test-olive-oil
```

---

## 🤖 কোন মডেল কোন ফাইল ব্যবহার করবে

| মডেল | টাস্ক ফাইল | ভূমিকা |
|---|---|---|
| Claude | `.ai/CLAUDE_TASKS.md` | আর্কিটেকচার, integration, ব্রাউজার টেস্ট, সব merge |
| Gemini | `.ai/GEMINI_TASKS.md` | বিশ্লেষণ ও রিপোর্ট (কোনো কোড পরিবর্তন নয়) |
| **BULK মডেল** | `.ai/BULK_TASKS.md` | যান্ত্রিক bulk edit — recipe অনুযায়ী |

**BULK slot = যেকোনো একটি উপলব্ধ সস্তা/দ্রুত মডেল।** মূল প্ল্যানে DeepSeek ছিল,
কিন্তু সেটি অনুপলব্ধ। তাই ফাইলের নাম মডেল-নিরপেক্ষ করা হয়েছে (`BULK_TASKS.md`)
এবং টাস্ক আইডি `BULK-1` … `BULK-7`।

| প্রার্থী | মন্তব্য |
|---|---|
| **DeepSeek V4 Flash** | 🟢 **বর্তমানে ব্যবহৃত** — মূল প্ল্যান DeepSeek-এর recipe ধরেই লেখা, তাই আচরণ সবচেয়ে কাছাকাছি |
| GLM 5.3 Flash | সমমানের বিকল্প |
| MiniMax 2.5 | বড় context |
| Nemotron 3.5 Light | instruction-following ভালো |

⚠️ **BULK মডেল বদলালে কোনো সমস্যা নেই** — এমনকি মাঝপথে বদলালেও। টাস্কগুলো
self-contained, আর অবস্থা git + PROGRESS.md + reports-এ লেখা থাকে। নতুন মডেল
শুধু উপরের বুট প্রম্পট পেলেই কাজ ধরতে পারবে। মডেল বদলালে শুধু
`.ai/BULK_TASKS.md`-এর একদম উপরের এক লাইনটি আপডেট করবেন।

---

## 📊 সামগ্রিক অবস্থা

**বর্তমান পর্যায়:** 🟨 **Day 2 চলছে — CLAUDE-5 শেষ, BULK-2 শুরুর জন্য প্রস্তুত**
**সর্বশেষ আপডেট:** CLAUDE-5 (Turbo Drive বসানো — full reload বন্ধ, cached back ১০-১৭ ms, kill-switch পরীক্ষিত; ২টি প্রত্যাশিত breakage নথিভুক্ত)
**সক্রিয় branch:** `day2/turbo-drive`

**Day 1 ক্রম:** ~~GEMINI-1~~ → ~~GEMINI-2~~ → ~~CLAUDE-1~~ → ~~CLAUDE-2~~ → ~~BULK-1~~ → ~~CLAUDE-3~~ → ~~CLAUDE-4~~ ✅ **শেষ**

**Day 2 ক্রম:** ~~CLAUDE-5~~ → **BULK-2 (এখন চালানো যায়)** → BULK-3 → BULK-4 → CLAUDE-6 → CLAUDE-7 → CLAUDE-8

### Day 1-এর সংক্ষিপ্ত ফলাফল (mobile, cold, median of 3)

| মেট্রিক | Before | After | পরিবর্তন |
|---|---|---|---|
| storefront মোট payload | 4480.4 KB | 4096.1 KB | −384.3 KB (−8.6%) |
| storefront JS | 1049.8 KB | 583.4 KB | −466.4 KB (−44%) |
| storefront request | 41 | 39 | −2 |
| storefront LCP | 1568 ms | 1296 ms | −272 ms (−17%) |
| storefront TBT | 175 ms | 35 ms | −140 ms (−80%) |
| admin request | 64 | 46 | −18 (−28%) |
| admin CSS request | 20 | 2 | −18 (−90%) |
| admin CSS bytes | 994.0 KB | 755.7 KB | −238.3 KB (−24%) |
| CLS (দুইটিতেই) | 0.00 | 0.00 | অপরিবর্তিত |

বিস্তারিত → `.ai/reports/benchmark.md` ("Day 1 Final") ও `.ai/reports/day1-final-test.md`

---

## ⚠️ BULK-2/3/4 শুরুর আগে যা জানা দরকার (CLAUDE-5 থেকে)

**Turbo বসেছে এবং storefront-এ চালু আছে।** পূর্ণ রিপোর্ট →
`.ai/reports/turbo-initial-test.md`। যা পড়ে নেওয়া দরকার:

- **helper তিনটি রেডি ও পরীক্ষিত:**
  ```js
  window.onTurboLoad(fn)     // প্রতি পেজে একবার — widget init
  window.onTurboOnce(fn, k)  // পুরো সেশনে একবার — event delegation
  window.onTurboUnload(fn)   // পেজ cache হওয়ার আগে — destroy
  ```
  `onTurboMount` / `onTurboUnmount` — Vue island-এর জন্য (CLAUDE-15)।
  layout-এ একটি stub আছে, তাই bundle লোড হওয়ার আগেই inline script এগুলো
  ডাকতে পারে — কোনো "not a function" error হবে না।
- 🔴 **`onTurboLoad` এ registration পেজ-নির্দিষ্ট।** Turbo প্রতি navigation-এ
  body-র সব `<script>` আবার চালায়, তাই BULK-এর wrap করা block প্রতি পেজে
  নিজেই আবার register হবে — এটিই কাঙ্ক্ষিত। যাচাই করা হয়েছে: ৭ পেজ পরেও
  ডুপ্লিকেট চলেনি।
- 🔴 **event delegation অবশ্যই `onTurboOnce`-এ দিতে হবে।** না দিলে যা হয় তা
  মাপা হয়েছে: `document`-এ click handler **২৯ → ৪৬৪** (৮ navigation), এবং
  একটি add-to-cart ক্লিকে **৮টি `POST /product_cart`** — DB-তে qty ১ → ৮।
  একই source-এর handler দুইবার register হলে `onTurboOnce` নিজেই skip করে।
  ⚠️ Blade মান interpolate করা block-এ source text প্রতি পেজে আলাদা হয়, তাই
  সেখানে দ্বিতীয় আর্গুমেন্টে একটি নাম দিতে হবে: `onTurboOnce(fn, 'cart-delegation')`।
- 🔴 **`custom.js` এখন প্রতি navigation-এ SyntaxError দেয়** (৫৩টি top-level
  `const`, `searchToggle` প্রথমটি)। ফলে দ্বিতীয় পেজে Swiper, cart drawer,
  search popup, dropdown, mobile menu — সব মরা। **এটি BULK-এর কাজ নয়**
  (`custom.js` blade নয়, theme asset) — CLAUDE-6 ঠিক করবে। BULK মডেল এই
  error দেখে ভয় পাবে না, এটি জানা সমস্যা।
- **kill-switch:** `.env`-এ `TURBO_ENABLED=false` + `php artisan view:clear` →
  Turbo সম্পূর্ণ বন্ধ (chunk ডাউনলোডও হয় না), helper তবুও `$(document).ready`-র
  মতো কাজ করে। কিছু ভাঙলে এটিই ফেরার পথ।
- **`data-turbo="false"` যোগ করা হয়েছে ৩ জায়গায়** — checkout form (gateway-তে
  `redirect()->away()` করে, Turbo-র fetch cross-origin redirect follow করতে
  পারে না → checkout মরে যেত), এবং downloadable-product anchor। BULK এগুলো
  ছোঁবে না।
- **`target="_blank"` ও `download` attribute যুক্ত link-এ কিছু লাগে না** —
  Turbo নিজেই বাদ দেয় (পরীক্ষিত)। `.pdf`/`.zip` extension-ও তাই।

---

## ⚠️ Day 2-এর জন্য গুরুত্বপূর্ণ (CLAUDE-4 থেকে)

- **`script-inventory.md` পুনর্নির্মাণ করা হয়েছে** (GEMINI-3-এর ভাঙা রিপোর্টের
  বদলে)। স্ক্যানার: `.agents/scripts/scan-inline-scripts.cjs`, পুনরায় চালানো
  নিরাপদ। ফলাফল: **199 ফাইলে 300 inline script block** —
  A 129 / B 91 / C 19 / D 12 / E 49। মেশিন-পাঠযোগ্য: `script-inventory.json`
  · ✅ Claude ৩০০টি লাইন রেফারেন্স যাচাই করেছে — **৩০০/৩০০ সঠিক**
- 🔴 **BULK কতটুকু ছুঁতে পারবে — ৩০০ নয়, ২৪০:**

  | কোথায় | block | BULK ছুঁতে পারবে? |
  |---|---|---|
  | `resources/views/**` | 230 | ✅ |
  | `themes/**` | 10 | ✅ |
  | `packages/workdo/LandingPage/**` | **60** | ⛔ **নিষিদ্ধ ফোল্ডার** |

  BULK-2/3/4 শুধু `resources/` + `themes/` এর **২৪০ block / ১৫৭ ফাইলে** কাজ করবে
  (A 108 · B 75 · C 17 · D 11 · E 29)। `packages/workdo/` লাইসেন্সকৃত upstream
  module — PROJECT_CONTEXT নিয়ম ১ অনুযায়ী নিষিদ্ধ। LandingPage marketing সাইট
  render করে, storefront/admin নয়, তাই Day 2-এর স্কোপেও নেই।
- 🔴 **mixed A+B — ৫০ নয়, আসলে ৪৫:** একই `<script>`-এ widget init *এবং*
  `$(document).on()` delegation। পুরোটা `turbo:load`-এ wrap করলে প্রতি
  navigation-এ handler ডুপ্লিকেট হবে (double add-to-cart), না করলে widget
  হারাবে। **BULK-2/3/4-এর recipe-এ এগুলো আলাদা করে দিতে হবে।**
  বাকি ৫টি false-positive — স্ক্যানার `document.addEventListener('DOMContentLoaded')`
  কে global listener গুনেছে, কিন্তু সেটি page-init wrapper, delegation নয়।
  Claude প্রতিটি block body পড়ে যাচাই করেছে।
- re-init লাগবে যেসব widget: DataTable, select2, summernote, flatpickr, Swiper,
  ApexCharts, Dropzone, bootstrap modal, switchButton, jquery.steps,
  jquery.validate, choices.js, emojionearea, flipdown, leaflet, sortable
- 🟠 **gzip/brotli সম্পূর্ণ বন্ধ** — কোনো response-এ `Content-Encoding` নেই।
  storefront ৬৬ KB + admin ৭২.৫ KB সঞ্চয় এবং FCP/LCP ~৬৪০ ms উন্নতি পাওয়া যেত।
  Apache কনফিগের বিষয়, CLAUDE-9-এ `.htaccess` `mod_deflate` দিয়ে করতে হবে।
- 🟠 **`@foreach` লুপে `fetchpriority="high"`** — `product-detail.blade.php:24,54`
  ও `about.blade.php:97` এ gallery/thumbnail/team ছবি লুপের ভেতরে eager হয়েছে
  (BULK-1)। একটি product-এ ১০ ছবি থাকলে ১০টিই "সর্বোচ্চ প্রাধান্য" পাবে —
  প্রাধান্যের অর্থ হারায়। `$loop->first` দিয়ে শুধু প্রথমটিকে eager রাখা উচিত।
  এখন ৩টি product-এ ধরা পড়েনি; আসল ক্যাটালগে LCP খারাপ হতে পারে।
- **DB-তে এখন টেস্ট ডেটা আছে** (৩ product, ১ order, ১ customer) — CLAUDE-8-এর
  ২০ পেজ টেস্টে cart/checkout/order flow যাচাই করা যাবে। মুছবেন না।
- **stylique ছাড়া অন্য থিম এখনো Play CDN-এ** (greentic, techzonix)। Day 2-এ
  Turbo যোগ করার সময় শুধু stylique-এ যাচাই করাই যথেষ্ট, কিন্তু জানা থাকা দরকার।
- **`partision/head.blade.php:52` → `css/custome.css` 404 — ইচ্ছাকৃতভাবে রাখা হলো।**
  ফাইলটি নেই, master-এও একই 404, আর `custom.css`-এ বদলালে ৬০১টি নতুন rule
  হঠাৎ চালু হয়ে ডিজাইন বদলাতে পারে। উপকার নেই, ঝুঁকি আছে।
- ⚠️ **CLAUDE-2 এর admin স্ক্রিনশট নেওয়া হয়নি** — চারটি variant (LTR/RTL ×
  light/dark) চোখে যাচাই হয়নি। CLAUDE-4-এ dark ও RTL toggle কাজ করা যাচাই
  হয়েছে, কিন্তু ৭৪০ KB bundle-এ cascade order নিয়ে সন্দেহ থাকলে CLAUDE-8-এ
  চারটি variant-এর স্ক্রিনশট নেওয়া উচিত।

---

## ✅ সম্পন্ন হয়েছে (Day 0 — সেটআপ)

| # | কাজ | ফলাফল |
|---|---|---|
| 0.1 | git init + `.gitignore` + baseline commit + tag | ✅ commit `17a47e9`, tag `baseline-v0`, branch `master`, 3051 ফাইল / 156 MB ট্র্যাক করা |
| 0.2 | `.gitignore` — গোপনীয়তা ও আকার | ✅ `.env`, `vendor/` (177 MB), `node_modules/` (39 MB), `storage/debugbar`, uploads বাদ। যাচাই করা: staged ফাইলে কোনো secret নেই |
| 0.3 | Playwright MCP + chrome-devtools MCP | ✅ `~/.config/opencode/opencode.jsonc` |
| 0.4 | Chromium ব্রাউজার | ✅ `chromium-1234` + `chromium-1243` |
| 0.5 | Vision model আপগ্রেড (Qwen3-VL 8B → 235B) | ✅ non-vision মডেলও স্ক্রিনশট পড়তে পারবে |
| 0.6 | কোডবেস স্ক্যান + সমস্যা চিহ্নিতকরণ | ✅ ৮টি সমস্যা (P1-P8) — `PROJECT_CONTEXT.md` |
| 0.7 | `.ai/` ডকুমেন্টেশন সিস্টেম | ✅ ৫টি ডক + `reports/` + `screenshots/` + `state/` — প্রজেক্টের ভেতরে বসানো |
| 0.8 | `.agents/` auto-resume hook (Antigravity) | ✅ PreInvocation + PostToolUse + Stop। ⚠️ শুধু Antigravity-তে চলে — অন্য টুলে উপরের বুট প্রম্পট ব্যবহার করতে হবে |
| 0.9 | DeepSeek → BULK slot রূপান্তর | ✅ `DEEPSEEK_TASKS.md` → `BULK_TASKS.md`, ৩৮টি টাস্ক আইডি `BULK-N`, checkpoint commit নিয়ম যোগ |

> **সংশোধনের নোট:** Day 0-এর আগের সংস্করণে দাবি করা ছিল git repo ও `.ai/`
> তৈরি হয়ে গেছে (commit `b6a4b3e`)। বাস্তবে ফাইলগুলো Desktop-এ পড়ে ছিল, `.git`
> ছিল না। এই সেশনে সত্যিকারভাবে বসানো হলো — তাই commit hash এখন `17a47e9`।

---

## 📋 টাস্ক ট্র্যাকার

### Day 1 — Asset Pipeline (branch: `day1/asset-pipeline`)

| টাস্ক | মডেল | অবস্থা | কমিট | নোট |
|---|---|---|---|---|
| GEMINI-1  Asset inventory (`public/` 31MB অডিট) | Gemini | ✅ DONE | — | রিপোর্ট → `.ai/reports/asset-inventory.md` · Claude যাচাই: সংখ্যা নির্ভুল |
| GEMINI-2  Tailwind class scan (Play CDN বাদ দিতে) | Gemini | ✅ DONE | `c945b79` | রিপোর্ট → `.ai/reports/tailwind-classes.md` · ⚠️ safelist অসম্পূর্ণ — যাচাই: `.ai/reports/gemini2-verification.md`, CLAUDE-1 এ ৪টি class যোগ করতে হবে |
| CLAUDE-1  Vite config + Tailwind build (P1 fix) | Claude | ✅ DONE | `a69d27e` → `c69e14a` | Play CDN 466KB সরানো · FCP −148ms, LCP −196ms, TBT −66% · ৫ পেজে pixel-diff = 0 · রিপোর্ট → `.ai/reports/benchmark.md` |
| CLAUDE-2  Admin ২৮ CSS → bundle (P3 fix) | Claude | ✅ DONE | `9d4d831` | CSS requests 20→2 (−90%), CSS bytes 994→755.7 KB (−24%), LCP −80ms · 4 variants (RTL/dark) · kill-switch `LEGACY_ADMIN_CSS` · রিপোর্ট → `.ai/reports/admin-css-map.md` · benchmark updated |
| CLAUDE-3  `?v=time()` সরানো (P5 fix) | Claude | ✅ DONE | `78f421b` | ২১ ফাইল / ৪২ জায়গা · disk-cache 0→31 (৩২টির মধ্যে) · helper: `asset_v()`, `get_file_v()`, `asset_version()` · `.htaccess` Cache-Control · kill-switch `LEGACY_ASSET_TIME_BUSTER` · pixel-diff 0 · রিপোর্ট → `.ai/reports/cache-buster-map.md` |
| BULK-1  Image → lazy + width/height | BULK মডেল | ✅ DONE | `f9e6f11` | ৪৫ img: ২৬ lazy + ৯ eager(LCP) + ৮ আগে থেকে · ব্যতিক্রম ২ (FB pixel, commented) · রিপোর্ট → `.ai/reports/img-lazy-report.md` · missing dims → `.ai/reports/img-missing-dimensions.md` |
| CLAUDE-4  Day 1 ব্রাউজার টেস্ট + merge | Claude | ✅ DONE | `d4f2c70` · merge `cdf7f05` · tag `day1-complete` | ২৩ পেজ × ২ viewport, ০ নতুন console error · cart→checkout→order flow পাস (টেস্ট ডেটা সিড করে) · ৩ kill-switch দুই দিকে পরীক্ষিত · JS −466 KB, admin request −18 · merge-পরবর্তী master-এও যাচাই করা হয়েছে · রিপোর্ট → `.ai/reports/day1-final-test.md` · benchmark "Day 1 Final" · ➕ `script-inventory.md` পুনর্নির্মাণ (স্ক্যানার সহ) |

### Day 2 — Turbo Drive (branch: `day2/turbo-drive`)

| টাস্ক | মডেল | অবস্থা | কমিট | নোট |
|---|---|---|---|---|
| GEMINI-3  ১৭০টি inline script ইনভেন্টরি | Gemini | ✅ REDONE by Claude | `d4f2c70` | Gemini-র সংস্করণ ব্যবহারযোগ্য ছিল না (যাচাই → `.ai/reports/gemini3-verification.md`)। CLAUDE-4-এ স্ক্যানার দিয়ে পুনর্নির্মাণ: `.agents/scripts/scan-inline-scripts.cjs` → **199 ফাইল / 300 block**, A 129 · B 91 · C 19 · D 12 · E 49, 🔴 **mixed A+B = 50** |
| CLAUDE-5  Turbo ইনস্টল + bootstrap + kill-switch | Claude | ✅ DONE | `581b91c` | Turbo 8.0.23 pin · `turbo-bootstrap.js` (৫টি helper, page-scoped registry) · `store.js`+`admin.js` entry · storefront layout-এ যোগ · kill-switch `TURBO_ENABLED` দুই দিকে পরীক্ষিত · **full reload বন্ধ** (১০ nav-এ document request 1) · cached back **১০-১৭ ms** · রিপোর্ট → `.ai/reports/turbo-initial-test.md` · 🔴 প্রত্যাশিতভাবে ভেঙেছে: `custom.js` SyntaxError (৫৩ top-level const) → সব widget মরে; delegated handler ২৯→৪৬৪ জমে → add-to-cart ১ ক্লিকে ৮ POST |
| BULK-2  script → `turbo:load` (batch 1: ৬০টি) | BULK মডেল | 🟨 IN PROGRESS | — | ১১/২০ ফাইল শেষ · ⚠️ `country/index` mixed block → **Claude দেখবে** · রিপোর্ট → `.ai/reports/turbo-migration-batch1.md` |
| BULK-3  script → `turbo:load` (batch 2: ৬০টি) | BULK মডেল | ⬜ PENDING | — | |
| BULK-4  script → `turbo:load` (batch 3: বাকি) | BULK মডেল | ⬜ PENDING | — | |
| CLAUDE-6  DataTable + modal + select2 re-init | Claude | ⬜ PENDING | — | ৭০ table, ১০১ modal |
| CLAUDE-7  Turbo 303 redirect middleware | Claude | ⬜ PENDING | — | form/validation |
| CLAUDE-8  Day 2 ব্রাউজার টেস্ট (২০ পেজ) | Claude | ⬜ PENDING | — | DB-তে টেস্ট ডেটা আছে — cart/checkout/order flow যাচাই করা যাবে |

### Day 3 — PWA + Workbox (branch: `day3/pwa-workbox`)

| টাস্ক | মডেল | অবস্থা | কমিট | নোট |
|---|---|---|---|---|
| CLAUDE-9   Redis + OPcache + optimize | Claude | ⬜ PENDING | — | |
| CLAUDE-10  Workbox SW (P4 fix) | Claude | ⬜ PENDING | — | ভাঙা SW প্রতিস্থাপন |
| CLAUDE-11  Per-store manifest generator | Claude | ⬜ PENDING | — | |
| BULK-5  Offline page + install prompt UI | BULK মডেল | ⬜ PENDING | — | |
| CLAUDE-12  Day 3 PWA টেস্ট (install + offline) | Claude | ⬜ PENDING | — | |

### Day 4 — Mobile App-Feel (branch: `day4/mobile-polish`)

| টাস্ক | মডেল | অবস্থা | কমিট | নোট |
|---|---|---|---|---|
| GEMINI-4   মোবাইল UX অডিট (স্ক্রিনশট বিশ্লেষণ) | Gemini | ⬜ PENDING | — | |
| BULK-6  Mobile bottom nav (storefront) | BULK মডেল | ⬜ PENDING | — | |
| BULK-7  DataTable → mobile card view (CSS) | BULK মডেল | ⬜ PENDING | — | ৭০ table |
| CLAUDE-13  Skeleton loader + Turbo preview | Claude | ⬜ PENDING | — | |
| CLAUDE-14  Touch polish (tap delay, dvh, safe-area) | Claude | ⬜ PENDING | — | |
| CLAUDE-15  Vue island: cart drawer + variant | Claude | ⬜ PENDING | — | `api.php` ব্যবহার |
| CLAUDE-16  চূড়ান্ত টেস্ট + master-এ merge | Claude | ⬜ PENDING | — | |

**অবস্থার প্রতীক:** ⬜ PENDING · 🟨 IN PROGRESS · ✅ DONE · ❌ BLOCKED · ⏭️ SKIPPED

---

## 🚧 Blockers (আটকে যাওয়া কাজ)

*এখনো কিছু নেই।* Day 1-এ কোনো ব্লকার তৈরি হয়নি।

নিচের দুইটি **ব্লকার নয়**, কিন্তু Day 3-এ মনে রাখতে হবে:

| বিষয় | কেন এখন নয় | কার কাজ |
|---|---|---|
| gzip / brotli বন্ধ (`Content-Encoding` নেই) | Apache কনফিগ, Day 1-এর স্কোপে নয়। DevTools: storefront 66 KB, admin 72.5 KB, FCP/LCP ~640 ms সঞ্চয় | CLAUDE-9 |
| `all.min.js` = 1.5 MB একক ফাইল (P2) | code-split-এর জন্য Turbo/Vite entry দরকার | Day 2 |

CLAUDE-5-এর পর যোগ হলো (এগুলোও ব্লকার নয় — জানা ও নির্ধারিত):

| বিষয় | অবস্থা | কার কাজ |
|---|---|---|
| `custom.js`-এ ৫৩টি top-level `const` → প্রতি Turbo navigation-এ SyntaxError, দ্বিতীয় পেজে সব widget মরা | 🔴 নিশ্চিতভাবে পুনরুৎপাদনযোগ্য। `data-turbo-eval="false"` কাজ করে না (পরীক্ষিত) — ফাইলটি IIFE-তে মোড়াতে হবে বা guard দিতে হবে | CLAUDE-6 |
| delegated handler জমে (২৯ → ৪৬৪ in ৮ nav) → এক ক্লিকে ৮টি add-to-cart POST | 🔴 `onTurboOnce` দিয়ে সমাধান হয়, helper রেডি | BULK-2/3/4 |
| Turbo forward navigation ≈ full reload (515 বনাম 558 ms) | server TTFB ৫৫০-৭৩০ ms একাই কারণ। cached back navigation ইতিমধ্যে ১০-১৭ ms | CLAUDE-9 |
| Swiper কখনো destroy হয় না → `turbo:before-cache` cleanup দরকার | `onTurboUnload` helper রেডি, ব্যবহার করা হয়নি | CLAUDE-6 |

> কোনো মডেল আটকে গেলে এখানে এই ফরম্যাটে লিখবে:
> ```
> ### [তারিখ] <MODEL>-<TASK#> — ব্লকড
> কী করতে গিয়েছিলাম:
> কী error এসেছে (হুবহু):
> কী কী চেষ্টা করেছি:
> কী দরকার:
> ```

---

## 📝 সিদ্ধান্তের লগ (কেন এভাবে করা হলো)

| তারিখ | সিদ্ধান্ত | কারণ |
|---|---|---|
| Day 0 | Vue 3 full rewrite বাতিল | 508 blade + 133 controller = ৩-৬ মাস; upstream update path নষ্ট হবে |
| Day 0 | Turbo Drive বাছাই | ৯০% SPA feel, blade কোড প্রায় অপরিবর্তিত, kill-switch সহজ |
| Day 0 | Vue শুধু island হিসেবে | ৪টি জায়গায় reactive দরকার; বাকি সব server HTML যথেষ্ট |
| Day 0 | Tailwind Play CDN অপসারণ = সর্বোচ্চ প্রাধান্য | 466 KB + runtime CSS generation = সবচেয়ে বড় performance killer |
| Day 0 | git init বাধ্যতামূলক করা হলো | প্রতিটি ধাপে ফেরার পথ; মডেল ভুল করলে instant restore |
| Day 0 | vision model 8B → 235B | non-vision মডেলও যাতে স্ক্রিনশট নির্ভুল পড়তে পারে |
| Day 0 | DeepSeek slot → মডেল-নিরপেক্ষ "BULK" slot | DeepSeek অনুপলব্ধ। ফাইল/টাস্ক আইডিতে মডেলের নাম থাকলে নতুন মডেল বিভ্রান্ত হয় ("এটা আমার ফাইল না")। এখন Ling / MiniMax / Nemotron — যেকোনোটি, এমনকি মাঝপথে বদলালেও চলবে |
| Day 0 | checkpoint commit (প্রতি ৫ ফাইল) যোগ করা হলো | সস্তা মডেলের টোকেন লিমিট মাঝপথে ফুরায়; টাস্ক-শেষে-commit নিয়মে ২০ ফাইলের কাজ হারানোর ঝুঁকি ছিল, এখন সর্বোচ্চ ৪ ফাইল |
| Day 0 | বুট প্রম্পট PROGRESS.md-এ লেখা হলো | `.agents/hooks.json` শুধু Antigravity-তে চলে। BULK মডেল অন্য টুলে চলবে — তাই টুল-নিরপেক্ষ, কপি-পেস্ট-যোগ্য একটি প্রম্পট দরকার ছিল |
| Day 0 | `screenshots/` ও `state/` git-ignored | বড় বাইনারি ও machine-local runtime state। ignored থাকায় `git clean -fd`-এ মুছবেও না |
| Day 1 | CLAUDE-4-এ DB-তে টেস্ট ডেটা সিড করা হলো | খালি DB-তে product card, gallery, cart, checkout, order — কিছুই render হয় না, তাই "ফিচার ভাঙেনি" দাবি করা অর্থহীন ছিল। শুধু DB row, কোনো কোড পরিবর্তন নয়; rollback SQL রাখা আছে |
| Day 1 | Lighthouse স্কোরের বদলে সরাসরি Core Web Vitals মাপা হলো | `APP_DEBUG=true` + file cache-এ TTFB ≈765 ms একা Performance স্কোর টেনে নামায় — সংখ্যাটি asset কাজের গুণ বোঝাত না। CLAUDE-9 (Redis + OPcache)-এর পর Lighthouse অর্থবহ হবে |
| Day 1 | `css/custome.css` (404) সংশোধন **না** করার সিদ্ধান্ত | ফাইলটি অস্তিত্বে নেই; `custom.css`-এ বদলালে ৬০১টি নতুন CSS rule হঠাৎ চালু হয়ে ডিজাইন বদলাতে পারে। master-এও একই 404। উপকার নেই, ঝুঁকি আছে |
| Day 1 | inline-script ইনভেন্টরি হাতে না লিখে স্ক্যানার লেখা হলো | Gemini-র হাতে-লেখা রিপোর্টে ১০টি লাইন রেফারেন্সের ৯টি ভুল ছিল। স্ক্রিপ্ট নির্ভুল, পুনরায় চালানো যায়, এবং BULK মডেলের জন্য JSON আউটপুট দেয় |
| Day 2 | Turbo **dynamic import** (`import('@hotwired/turbo')`), static নয় | প্যাকেজটি import হলেই `start()` চালিয়ে দেয় — static import হলে `TURBO_ENABLED=false` দিয়েও Turbo চালু হয়ে যেত, kill-switch অর্থহীন হতো। এখন flag বন্ধ থাকলে ৯৯ KB chunk ডাউনলোডই হয় না |
| Day 2 | `onTurboLoad` registration **পেজ-নির্দিষ্ট**, চিরস্থায়ী নয় | Turbo প্রতি body swap-এ সব inline script আবার চালায়। callback চিরকাল রাখলে home → product → home ঘুরে এলে একই init দুইবার চলত — দুইটি select2, "Cannot reinitialise DataTable", ডাবল add-to-cart। এখন যে পেজে register, শুধু সেই পেজেই চলে; script নিজেই পরের পেজে আবার register করে |
| Day 2 | layout-এ `window.onTurboLoad` **stub** বসানো হলো | `@vite()` দেয় `<script type="module">` = deferred, অর্থাৎ body-র সব inline script তার **আগে** চলে। stub ছাড়া BULK-এর wrap করা ২৪০টি block-ই প্রথম পেজে "onTurboLoad is not a function" দিত |
| Day 2 | `onTurboOnce` এ identity = function-এর **source text** hash | script আবার execute হলে নতুন function object তৈরি হয়, তাই reference দিয়ে মেলানো যায় না। source text এক থাকলে একই handler — shared partial-এর delegation তাই একবারই register হয় |
| Day 2 | checkout form-এ `data-turbo="false"` | `payment.process` gateway-তে `redirect()->away()` করে; Turbo form submit করে `fetch()` দিয়ে (mode `cors`), আর কোনো gateway আমাদের origin-কে CORS দেয় না → fetch reject, console-এ error, ব্যবহারকারী কিছুই দেখে না। checkout সম্পূর্ণ অচল হতো |
| Day 2 | `data-turbo-permanent` **যোগ করা হয়নি** | Turbo এমনিতেই head স্থির রাখছে (stylesheet 6, script 8, meta 20 — ৫ পেজে অপরিবর্তিত), তাই permanent element-এর দরকার পড়েনি। এটি জটিলতা বাড়ায় (id বাধ্যতামূলক, morph-এ ব্যতিক্রম), লাভ নেই |
| Day 2 | admin layout-এ Turbo **এখন নয়** | DataTable/select2/summernote destroy না করে Turbo চালু করলে প্রথম back-navigation-এই "Cannot reinitialise DataTable"। `admin.js` entry তৈরি ও build করা আছে, কিন্তু কোনো view এখনো ডাকে না — CLAUDE-6 যোগ করবে |
