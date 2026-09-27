# BULK মডেল — কাজের প্রম্পট

> **এই slot-এ বর্তমানে ব্যবহৃত মডেল: DeepSeek V4 Flash**
> মডেল বদলাতে চাইলে শুধু এই এক লাইনটি বদলাবেন — নিচের নিয়মগুলো Flash-শ্রেণির
> যেকোনো মডেলের (DeepSeek V4 Flash / GLM 5.3 Flash / Ling 3.0 Flash /
> MiniMax 2.5) জন্য একইভাবে প্রযোজ্য। প্রতিটি টাস্ক self-contained, তাই
> মডেল মাঝপথে বদলালেও কাজ হারায় না।

**ব্যবহারবিধি:** নিচের প্রম্পটগুলো **একটি একটি করে** দেবেন। এক নম্বর শেষ হলে দুই নম্বর।

**BULK মডেলের ভূমিকা:** যান্ত্রিক, পুনরাবৃত্তিমূলক bulk edit — নির্দিষ্ট recipe অনুযায়ী।
নতুন কিছু ডিজাইন করা নয়, সিদ্ধান্ত নেওয়া নয় — শুধু দেওয়া নিয়ম হুবহু প্রয়োগ করা।

---

## 🔴 Flash-শ্রেণির মডেলের জন্য বিশেষ নিয়ম

Flash-শ্রেণির মডেল দ্রুত ও সস্তা, কিন্তু তিনটি পরিচিত প্রবণতা আছে যা এই
প্রজেক্টে ক্ষতি করতে পারে। প্রতিটির জন্য একটি নিয়ম:

### ১. "আরও ভালো করে দিই" প্রবণতা — ⛔ নিষিদ্ধ

Flash মডেল প্রায়ই কোড দেখে নিজে থেকে "উন্নতি" করে ফেলে — indentation বদলায়,
variable-এর নাম বদলায়, `let`-কে `const` করে, অপ্রয়োজনীয় কোড মুছে দেয়।

**এই প্রজেক্টে সেটি বিপর্যয়।** কারণ এটি একটি লাইসেন্সকৃত স্ক্রিপ্ট (WorkDo),
ব্যবহারকারীকে পরে upstream আপডেট merge করতে হবে। তুমি যত বেশি লাইন বদলাবে,
তত বেশি merge conflict।

```
তোমার কাজ = wrapper যোগ করা। ভেতরের কোডের একটি অক্ষরও নয়।

আগে:                          পরে:
$(document).ready(function(){  window.onTurboLoad(function(){
    $('.select2').select2();       $('.select2').select2();   ← হুবহু একই
});                            });
```

কোনো লাইন "খারাপ" মনে হলে সেটি **রিপোর্টে লিখবে**, ঠিক করবে না।

### ২. চুপচাপ ভুল কাজ করে "হয়ে গেছে" বলা — ⛔ নিষিদ্ধ

Flash মডেল কখনো ফাইল খুঁজে না পেলে বা বুঝতে না পারলে অনুমান করে এগিয়ে যায়
এবং শেষে "সব ঠিক আছে" রিপোর্ট দেয়।

**নিয়ম:** নিশ্চিত না হলে স্পর্শ করবে না। রিপোর্টে লিখবে `Claude দেখবে`।
একটি ফাইল বাদ দেওয়া নিরাপদ; ভুলভাবে বদলানো নয়।

সন্দেহের উদাহরণ — এগুলো পেলে থামবে, নিজে সিদ্ধান্ত নেবে না:
- একই `<script>`-এ `$(document).ready` আর `$(document).on(...)` দুইটাই আছে
- Blade কোড (`@if`, `{{ }}`) script-এর মধ্যে জড়ানো
- script-এর ভেতরে আরেকটি nested function যার ভূমিকা অস্পষ্ট
- ফাইলটি inventory তালিকায় নেই

### ৩. পাইলট নিয়ম — প্রথম ২টি ফাইল করে **থামবে**

প্রতিটি টাস্কের শুরুতে শুধু **২টি ফাইল** করো, তারপর:

```
১. git add -A
   git commit -m "wip(<scope>): pilot 2 files  [BULK-<N>]"
২. ব্যবহারকারীকে দেখাও: এই ২ ফাইলে হুবহু কী বদলেছ (before/after)
৩. থামো। অনুমতি ছাড়া ৩ নম্বর ফাইলে যাবে না।
```

**কেন:** ভুল recipe-এ ২০ ফাইল নষ্ট করার চেয়ে ২ ফাইলে ধরা পড়া ভালো।
Claude ওই ২টি দেখে বলবে "চালিয়ে যাও" বা "এভাবে নয়"।

### ৪. একবারে ৩টি ফাইল, তারপর commit

পাইলট পাস করার পর প্রতি **৩টি ফাইলে** checkpoint commit (বিস্তারিত নিচে)।
৫ নয়, ৩ — কারণ Flash মডেলের টোকেন লিমিট দ্রুত ফুরায়।
### ৫. যা কখনো করবে না

```
❌ app/  routes/  database/  vendor/  packages/  — এই ফোল্ডারগুলো নিষিদ্ধ
❌ git reset --hard, git checkout <file>, git clean — কখনো নয়
❌ npm install / composer install — কখনো নয়
❌ .env ফাইল পড়বে না, লিখবে না
❌ একই ফাইল দুইবার wrap করবে না
❌ টাস্ক নম্বরের বাইরে কোনো কাজ নয় — "পাশে একটা bug দেখলাম" = রিপোর্টে লিখো
```

---

## 🔴 প্রতিটি টাস্কে যা মানতে হবে (টোকেন লিমিট ফুরিয়ে গেলেও কাজ হারাবে না)

DeepSeek V4 Flash-এর দৈনিক টোকেন লিমিট মাঝপথে ফুরিয়ে যেতে পারে। তখন মডেল রিসেট হয়ে
সব ভুলে যায়। তাই দুইটি নিয়ম **বাধ্যতামূলক**:

### নিয়ম ১ — শুরুতে নিজের অবস্থান খুঁজে নাও

```
১. পড়ো:  .ai/PROJECT_CONTEXT.md
২. পড়ো:  .ai/PROGRESS.md
৩. চালাও: git log --oneline -20
৪. চালাও: git status --short
```

PROGRESS.md-এ তোমার টাস্ক যদি **🟨 IN PROGRESS** থাকে → এর মানে আগের সেশনে কাজ
অসম্পূর্ণ রেখে থেমে গিয়েছিলে। তখন সেই টাস্কের `.ai/reports/` ফাইলটি পড়ো, কোন
ফাইল পর্যন্ত শেষ হয়েছে দেখো, **তার পরের ফাইল থেকে শুরু করো**।

⛔ আগে শেষ হওয়া ফাইল আবার করবে না। একই script দুইবার wrap করলে
`window.onTurboLoad(window.onTurboLoad(...))` হয়ে কোড ভেঙে যাবে।

**যাচাই করার সহজ উপায়:** ফাইল খুলে দেখো `onTurboLoad` বা `loading="lazy"`
ইতিমধ্যে আছে কি না। থাকলে সেই ফাইল শেষ — এগিয়ে যাও।

### নিয়ম ২ — প্রতি ৩টি ফাইলে checkpoint commit

টাস্ক শেষ হওয়ার জন্য অপেক্ষা করবে না। **প্রতি ৩টি ফাইল শেষ করার সাথে সাথে:**

```
১. .ai/reports/<এই টাস্কের রিপোর্ট>.md এ ওই ৩টি ফাইলের সারি যোগ করো
২. .ai/PROGRESS.md এ অবস্থা 🟨 IN PROGRESS রাখো,
   নোট কলামে লিখো: "N/মোট ফাইল শেষ"
৩. git add -A
   git commit -m "wip(<scope>): <কাজ> files X-Y  [BULK-<N>]"
```

উদাহরণ: `git commit -m "wip(turbo): batch 1 files 3-5  [BULK-2]"`

সব ফাইল শেষ হলে তখন আসল commit করবে এবং অবস্থা ✅ DONE করবে।

**কেন:** এতে সবচেয়ে খারাপ ক্ষেত্রেও সর্বোচ্চ ২ ফাইলের কাজ হারাবে, ২০টার নয়।

### নিয়ম ৩ — রিপোর্টে সৎ থাকবে

রিপোর্টে তিনটি সংখ্যা আলাদা করে লিখতে হবে:

```
পরিবর্তন করেছি      : N ফাইল
স্পর্শ করিনি (কারণ সহ) : N ফাইল
সন্দেহ — Claude দেখবে  : N ফাইল
```

"সব ঠিক আছে" লিখলে Claude যাচাই করবে এবং না মিললে কাজটি বাতিল হবে।
বাদ দেওয়া ফাইল স্বীকার করা কোনো ব্যর্থতা নয় — সেটাই সঠিক আচরণ।

---

## প্রম্পট ১ — Image lazy loading + CLS fix (Day 1)

```
তুমি `C:\laragon\www\ecommercego` প্রজেক্টে কাজ করছ। Laravel 11 multi-store SaaS eCommerce।

প্রথমে এই দুইটি ফাইল পড়ো (বাধ্যতামূলক):
1. .ai/PROJECT_CONTEXT.md
2. .ai/PROGRESS.md

তোমার কাজ (BULK-1): storefront theme-এর সব <img> ট্যাগে lazy loading যোগ করা।

⚠️ কাজ শুরুর আগে branch তৈরি করো:
cd C:\laragon\www\ecommercego
git checkout -b day1/asset-pipeline
(যদি branch আগেই থাকে: git checkout day1/asset-pipeline)

শুধু এই ফাইলগুলোতে কাজ করবে:
- themes/stylique/views/**/*.blade.php
- resources/views/front_end/**/*.blade.php

⛔ অন্য কোনো ফোল্ডারে হাত দেবে না। বিশেষ করে app/, routes/, database/, vendor/।

### নিয়ম (হুবহু মানবে)

প্রতিটি <img> ট্যাগে:

১. `loading="lazy"` যোগ করো — কিন্তু নিচের ব্যতিক্রম ছাড়া:
   ❌ যে img `header`, `navbar`, `logo`, `banner`, `slider`, `hero`, `carousel`,
      `swiper` শব্দযুক্ত কোনো element-এর ভেতরে বা যার class/id-তে এই শব্দ আছে
   → এগুলোতে `loading="eager"` এবং `fetchpriority="high"` দাও (এগুলো LCP element)

২. `decoding="async"` যোগ করো (সব img-এ, ব্যতিক্রম নেই)

৩. যদি img-এ `width` বা `height` attribute না থাকে:
   → কাছাকাছি CSS class দেখে বোঝার চেষ্টা করবে না।
   → পরিবর্তে wrapper div-এ `style="aspect-ratio: 1/1;"` যোগ করবে **না**।
   → শুধু `.ai/reports/img-missing-dimensions.md` ফাইলে তালিকা করো:
     | ফাইল | লাইন | img src (সংক্ষেপে) |
   (Claude পরে সিদ্ধান্ত নেবে — ভুল dimension দিলে ডিজাইন ভাঙবে)

৪. যদি img আগে থেকেই `loading=` রাখে → স্পর্শ করবে না, তালিকায় রাখো।

৫. `<img>` যদি Blade variable দিয়ে তৈরি হয় (যেমন `{!! $html !!}`) → স্পর্শ করবে না।

### 🔁 পাইলট ও checkpoint (বাধ্যতামূলক)

**প্রথমে ২টি blade ফাইল** করো → commit → ব্যবহারকারীকে before/after দেখাও →
**থামো**। অনুমতি পেলে বাকিগুলো করবে।

তারপর প্রতি ৩টি ফাইল শেষ করার পর:
- `.ai/reports/img-lazy-report.md` এ ওই ৩টি ফাইলের সারি যোগ করো
- `.ai/PROGRESS.md` এ BULK-1 → 🟨 IN PROGRESS, নোটে "N/মোট ফাইল শেষ"
- `git add -A && git commit -m "wip(images): lazy loading files X-Y  [BULK-1]"`

⚠️ কোনো ফাইলে `loading=` ইতিমধ্যে থাকলে সেটি শেষ হয়ে গেছে — এগিয়ে যাও।

### উদাহরণ

আগে:
<img src="{{ asset('img/product.png') }}" class="product-thumb" alt="Product">
পরে:
<img src="{{ asset('img/product.png') }}" class="product-thumb" alt="Product" loading="lazy" decoding="async">

আগে (banner — ব্যতিক্রম):
<div class="hero-banner"><img src="{{ $banner }}" alt="Banner"></div>
পরে:
<div class="hero-banner"><img src="{{ $banner }}" alt="Banner" loading="eager" fetchpriority="high" decoding="async"></div>

### সম্পন্ন করার পর

১. রিপোর্ট লেখো: `.ai/reports/img-lazy-report.md`
   - মোট img পাওয়া গেছে: কত
   - lazy করা হয়েছে: কত
   - eager রাখা হয়েছে: কত (এবং কেন — ফাইল তালিকা সহ)
   - স্পর্শ করা হয়নি: কত (কারণ সহ)
   - dimension অনুপস্থিত: কত

২. `.ai/PROGRESS.md`-এ BULK-1 → ✅ DONE করো, কমিট hash লেখো।

৩. commit করো:
cd C:\laragon\www\ecommercego
git add -A
git commit -m "perf(images): add lazy loading and async decoding to storefront images  [BULK-1]"

৪. আমাকে বাংলায় ৫ লাইনে জানাও কী করেছ।

⛔ আটকে গেলে: `.ai/PROGRESS.md`-এর `## Blockers` সেকশনে লিখে থামবে। নিজে থেকে
সমাধান আবিষ্কার করার চেষ্টা করবে না — ভুল সমাধান ডিজাইন ভাঙতে পারে।
```

---

## প্রম্পট ২ — Turbo script migration, Batch 1 (Day 2)

```
তুমি `C:\laragon\www\ecommercego` প্রজেক্টে কাজ করছ।

প্রথমে পড়ো (বাধ্যতামূলক):
1. .ai/PROJECT_CONTEXT.md
2. .ai/PROGRESS.md
3. .ai/reports/script-inventory.md   ← Gemini তৈরি করেছে, এখানে শ্রেণিবিন্যাস আছে
4. resources/js/turbo-bootstrap.js   ← Claude তৈরি করেছে, helper function আছে

⚠️ যদি উপরের ৩ বা ৪ নম্বর ফাইল না থাকে → থামো। `.ai/PROGRESS.md`-এর Blockers-এ
লিখো "GEMINI-3 বা CLAUDE-5 এখনো শেষ হয়নি" এবং কাজ শুরু করবে না।

branch:
cd C:\laragon\www\ecommercego
git checkout day2/turbo-drive

### প্রেক্ষাপট

Hotwired Turbo Drive বসানো হয়েছে। Turbo পেজ reload না করে body swap করে।
ফলে `$(document).ready()` শুধু প্রথম লোডে চলে — পরের পেজে চলে না।
সব pageful script `turbo:load` ইভেন্টে সরাতে হবে।

Claude ইতিমধ্যে `resources/js/turbo-bootstrap.js`-এ দুইটি global helper দিয়েছে:

```js
// প্রতি Turbo navigation-এ চলবে (DataTable, select2 ইত্যাদি init)
window.onTurboLoad(function () { ... });

// শুধু একবার চলবে (event delegation, global setup)
window.onTurboOnce(function () { ... });
```

### তোমার কাজ (BULK-2)

`.ai/reports/script-inventory.md`-এর **শ্রেণি A (REINIT-NEEDED)** তালিকার
**প্রথম ২০টি ফাইল** নিয়ে কাজ করবে। বেশি নয়, কম নয়।

প্রতিটি ফাইলে:

**নিয়ম A — `$(document).ready(function () { ... });` পেলে:**

আগে:
```
@push('custom-script')
<script>
    $(document).ready(function () {
        $('.select2').select2();
    });
</script>
@endpush
```
পরে:
```
@push('custom-script')
<script>
    window.onTurboLoad(function () {
        $('.select2').select2();
    });
</script>
@endpush
```

**নিয়ম B — `$(function () { ... });` পেলে:** একইভাবে `window.onTurboLoad(function () { ... });`

**নিয়ম C — `$(document).on('click', 'selector', ...)` (event delegation) পেলে:**
এটি ONCE-ONLY। মোড়াবে:
```
window.onTurboOnce(function () {
    $(document).on('click', '.code', function () { ... });
});
```

**নিয়ম D — খোলা script (কোনো ready wrapper নেই, সরাসরি কোড):**
যদি কোডটি DOM element খুঁজে init করে (`$('.x').something()`), তাহলে
`window.onTurboLoad(function () { ... });` দিয়ে মোড়াও।
যদি শুধু variable declaration হয় (`var site_url = "...";`) → **স্পর্শ করবে না**।

**নিয়ম E — একই script-এ A এবং C দুইটাই মিশে আছে:**
দুইটা আলাদা block-এ ভাগ করো — `onTurboLoad` এবং `onTurboOnce`।

### 🔴 কঠোর নিষেধাজ্ঞা

- ভেতরের কোডের একটি অক্ষরও বদলাবে না। শুধু wrapper বদলাবে।
- ইন্ডেন্টেশন ঠিক রাখবে।
- Blade syntax (`{{ }}`, `@if`, `{!! !!}`) স্পর্শ করবে না।
- `@push` / `@endpush` স্পর্শ করবে না।
- `<script src="...">` (external) স্পর্শ করবে না।
- third-party script (analytics, pixel, payment SDK) স্পর্শ করবে না।
- inventory-তে নেই এমন ফাইল খুলবে না।

### 🔁 checkpoint (বাধ্যতামূলক — এই টাস্কে সবচেয়ে জরুরি)

২০টি ফাইল একটানা করার চেষ্টা করবে না। **প্রতি ৫টি ফাইল শেষ করার পর:**
- `.ai/reports/turbo-migration-batch1.md` এ ওই ৫টি ফাইলের সারি যোগ করো
- `.ai/PROGRESS.md` এ BULK-2 → 🟨 IN PROGRESS, নোটে "N/২০ ফাইল শেষ"
- `git add -A && git commit -m "wip(turbo): batch 1 files X-Y  [BULK-2]"`

⛔ নতুন সেশনে শুরু করলে আগে রিপোর্ট দেখো কোন ফাইল শেষ — সেগুলো **আবার
wrap করবে না**। দুইবার wrap হলে nested `onTurboLoad` হয়ে কোড ভাঙবে।

### সম্পন্ন করার পর

১. রিপোর্ট: `.ai/reports/turbo-migration-batch1.md`
   | # | ফাইল | কোন নিয়ম প্রয়োগ | নোট |
   এবং শেষে: স্পর্শ না করা ফাইল ও কারণ।

২. `.ai/PROGRESS.md`-এ BULK-2 → ✅ DONE, কমিট hash সহ।

৩. commit:
git add -A
git commit -m "refactor(turbo): migrate document.ready to turbo:load, batch 1 of 3  [BULK-2]"

৪. বাংলায় জানাও: কত ফাইল, কোন নিয়ম কতবার, কোনটা স্পর্শ করিনি।

⛔ কোনো ফাইল নিয়ে সন্দেহ হলে সেটি স্পর্শ করবে না — রিপোর্টে "Claude দেখবে" লিখে রাখো।
```

---

## প্রম্পট ৩ — Turbo script migration, Batch 2 (Day 2)

```
তুমি `C:\laragon\www\ecommercego` প্রজেক্টে কাজ করছ।

প্রথমে পড়ো:
1. .ai/PROJECT_CONTEXT.md
2. .ai/PROGRESS.md
3. .ai/reports/script-inventory.md
4. .ai/reports/turbo-migration-batch1.md   ← আগের batch-এ কী করেছ
5. resources/js/turbo-bootstrap.js

branch: git checkout day2/turbo-drive

তোমার কাজ (BULK-3): BULK-2 এর হুবহু একই নিয়ম, কিন্তু এবার
`.ai/reports/script-inventory.md`-এর শ্রেণি A তালিকার **২১ থেকে ৪০ নম্বর ফাইল**।

নিয়ম A, B, C, D, E — সব BULK-2 এর প্রম্পটে যেমন ছিল, হুবহু তেমন।
যদি নিয়মগুলো মনে না থাকে, `.ai/reports/turbo-migration-batch1.md` দেখো —
সেখানে কোন ফাইলে কোন নিয়ম প্রয়োগ করেছ লেখা আছে।

একই কঠোর নিষেধাজ্ঞা প্রযোজ্য:
- ভেতরের কোড অপরিবর্তিত, শুধু wrapper
- Blade syntax স্পর্শ নয়
- external script স্পর্শ নয়
- inventory-র বাইরের ফাইল খোলা নয়

🔁 checkpoint: প্রতি ৫ ফাইলে রিপোর্ট আপডেট + 🟨 IN PROGRESS +
`git commit -m "wip(turbo): batch 2 files X-Y  [BULK-3]"`
নতুন সেশনে শুরু করলে রিপোর্ট দেখে বাদ পড়া ফাইল থেকে শুরু করো — শেষ হওয়া
ফাইল আবার wrap করবে না।

সম্পন্ন করার পর:
১. রিপোর্ট: `.ai/reports/turbo-migration-batch2.md` (batch1 এর মতো ফরম্যাট)
২. `.ai/PROGRESS.md`-এ BULK-3 → ✅ DONE
৩. commit:
git add -A
git commit -m "refactor(turbo): migrate document.ready to turbo:load, batch 2 of 3  [BULK-3]"
৪. বাংলায় সংক্ষিপ্ত রিপোর্ট
```

---

## প্রম্পট ৪ — Turbo script migration, Batch 3 (Day 2)

```
তুমি `C:\laragon\www\ecommercego` প্রজেক্টে কাজ করছ।

প্রথমে পড়ো:
1. .ai/PROJECT_CONTEXT.md
2. .ai/PROGRESS.md
3. .ai/reports/script-inventory.md
4. .ai/reports/turbo-migration-batch1.md
5. .ai/reports/turbo-migration-batch2.md
6. resources/js/turbo-bootstrap.js

branch: git checkout day2/turbo-drive

তোমার কাজ (BULK-4): শ্রেণি A তালিকার **৪১ নম্বর থেকে বাকি সব ফাইল**।
এছাড়া শ্রেণি B (ONCE-ONLY) তালিকার সব ফাইল — এগুলোতে `window.onTurboOnce()` ব্যবহার করবে।

নিয়ম BULK-2 এর মতোই।

শ্রেণি B-এর জন্য বিশেষ নির্দেশ:
- event delegation (`$(document).on(...)`) → `onTurboOnce`
- global variable / `window.X = ...` → স্পর্শ করবে না
- service worker registration → স্পর্শ করবে না (Claude আলাদাভাবে করবে)

শ্রেণি C (INLINE-DATA), D (THIRD-PARTY), E (UNCLEAR) → **স্পর্শ করবে না।**

🔁 checkpoint: প্রতি ৫ ফাইলে রিপোর্ট আপডেট + 🟨 IN PROGRESS +
`git commit -m "wip(turbo): batch 3 files X-Y  [BULK-4]"`
এই batch-এ ফাইল সংখ্যা বেশি হতে পারে, তাই checkpoint বাদ দিলে কাজ হারানোর
ঝুঁকি সবচেয়ে বেশি।

সম্পন্ন করার পর:
১. রিপোর্ট: `.ai/reports/turbo-migration-batch3.md`
   এবং একটি চূড়ান্ত সারসংক্ষেপ যোগ করো:
   - তিন batch মিলে মোট কত ফাইল migrate হয়েছে
   - কত ফাইল বাকি আছে এবং কেন
   - কোন ফাইলগুলো Claude-কে দেখতে হবে
২. `.ai/PROGRESS.md`-এ BULK-4 → ✅ DONE
৩. commit:
git add -A
git commit -m "refactor(turbo): migrate document.ready to turbo:load, batch 3 of 3  [BULK-4]"
৪. বাংলায় রিপোর্ট
```

---

## প্রম্পট ৫ — Offline page + Install prompt UI (Day 3)

```
তুমি `C:\laragon\www\ecommercego` প্রজেক্টে কাজ করছ।

প্রথমে পড়ো:
1. .ai/PROJECT_CONTEXT.md
2. .ai/PROGRESS.md

branch:
cd C:\laragon\www\ecommercego
git checkout day3/pwa-workbox
(না থাকলে: git checkout -b day3/pwa-workbox)

### প্রেক্ষাপট

Claude ইতিমধ্যে Workbox দিয়ে service worker বসিয়েছে। এখন দুইটি UI দরকার:
(ক) ইন্টারনেট না থাকলে যে পেজ দেখাবে
(খ) "অ্যাপ ইনস্টল করুন" এর সুন্দর banner

বর্তমানে `resources/views/front_end/jQueryRoute.blade.php:199-260` এ একটি
install prompt আছে, কিন্তু তা কাঁচা। তুমি এটি প্রতিস্থাপন করবে না — নতুন
component বানাবে, Claude পরে পুরনোটি সরাবে।

### তোমার কাজ (BULK-5)

**অংশ ১: Offline পেজ**

নতুন ফাইল তৈরি করো: `resources/views/front_end/offline.blade.php`

শর্ত:
- সম্পূর্ণ standalone HTML (কোনো layout extend করবে না, কারণ offline-এ layout
  লোড হবে না)
- সব CSS **inline `<style>` ট্যাগে**, কোনো external file নয়
- সব image inline SVG, কোনো external image নয়
- কোনো JS library নয়, শুধু vanilla JS
- মোবাইল-first, viewport meta সহ
- Dark mode support (`prefers-color-scheme`)
- Bangla + English দুইভাষী টেক্সট
- একটি "আবার চেষ্টা করুন / Retry" বোতাম যা `location.reload()` করবে
- `navigator.onLine` দেখে অটো-রিলোড: online হলে নিজে থেকে reload হবে
  (`window.addEventListener('online', () => location.reload())`)
- দেখতে পরিষ্কার ও প্রফেশনাল লাগবে — কোনো emoji নয়, একটি সাধারণ SVG icon

**অংশ ২: PWA Install banner**

নতুন ফাইল তৈরি করো: `resources/views/front_end/partison/pwa-install.blade.php`

শর্ত:
- মোবাইলে নিচে থেকে উঠে আসা bottom-sheet (CSS transform + transition, JS animation নয়)
- ডেস্কটপে ডান-নিচের কোণে ছোট card
- CSS সব `<style>` ট্যাগে scoped রাখো (class prefix `pwa-`)
- vanilla JS, jQuery নয়
- `beforeinstallprompt` ধরে রাখবে, তারপর banner দেখাবে
- ব্যবহারকারী "পরে" চাপলে `localStorage`-এ ৭ দিনের জন্য মনে রাখবে
  (key: `pwa-dismissed-until`, মান = timestamp)
- ইতিমধ্যে ইনস্টল থাকলে দেখাবে না:
  `window.matchMedia('(display-mode: standalone)').matches` চেক করবে
- iOS Safari-তে `beforeinstallprompt` কাজ করে না — সেখানে আলাদা নির্দেশনা দেখাবে
  ("Share বোতাম → Add to Home Screen"), iOS detect করবে
  `/iphone|ipad|ipod/i.test(navigator.userAgent)` দিয়ে
- `safe-area-inset-bottom` সম্মান করবে (iPhone notch)
- Bangla + English টেক্সট
- store-এর নাম ও logo Blade variable থেকে নেবে, কিন্তু **নিরাপদভাবে**:
  `{{ $store->name ?? config('app.name') }}` — variable না থাকলেও ভাঙবে না

### 🔴 কঠোর নিষেধাজ্ঞা

- এই দুইটি নতুন ফাইল ছাড়া **আর কোনো ফাইল স্পর্শ করবে না**
- কোনো layout ফাইলে include যোগ করবে না — Claude করবে
- `serviceWorker.js` বা `public/sw.js` স্পর্শ করবে না
- routes/ এ কিছু যোগ করবে না
- CSS framework (Tailwind/Bootstrap) class ব্যবহার করবে না — কারণ offline পেজে
  CSS লোড হবে না। সব নিজের `<style>`-এ লিখবে।

### সম্পন্ন করার পর

১. রিপোর্ট: `.ai/reports/pwa-ui.md`
   - দুইটি ফাইলের পাথ
   - Claude-কে কী করতে হবে (কোন layout-এ কোথায় include করতে হবে, কোন route লাগবে)
   - কোন ব্রাউজারে কী behavior আশা করা যায়

২. `.ai/PROGRESS.md`-এ BULK-5 → ✅ DONE

৩. commit:
git add -A
git commit -m "feat(pwa): add offline page and install prompt UI  [BULK-5]"

৪. বাংলায় জানাও কী বানিয়েছ।
```

---

## প্রম্পট ৬ — Mobile bottom navigation (Day 4)

```
তুমি `C:\laragon\www\ecommercego` প্রজেক্টে কাজ করছ।

প্রথমে পড়ো:
1. .ai/PROJECT_CONTEXT.md
2. .ai/PROGRESS.md
3. .ai/reports/mobile-audit.md   ← Gemini তৈরি করেছে (থাকলে)

branch:
cd C:\laragon\www\ecommercego
git checkout day4/mobile-polish
(না থাকলে: git checkout -b day4/mobile-polish)

### প্রেক্ষাপট

এই প্রজেক্টে মোবাইল সর্বোচ্চ প্রাধান্য। native app-এর মতো অনুভূতি দিতে
storefront-এ একটি bottom navigation bar দরকার — যেমন Daraz, Foodpanda বা
যেকোনো e-commerce app-এ থাকে।

Turbo Drive ইতিমধ্যে বসানো আছে, তাই এই bar-টি navigation-এর সময় অদৃশ্য হয়ে
আবার আসবে না — এর জন্য `data-turbo-permanent` ব্যবহার করতে হবে।

### তোমার কাজ (BULK-6)

নতুন ফাইল: `themes/stylique/views/front_end/partison/mobile-bottom-nav.blade.php`

শর্ত:

**গঠন**
- ৫টি আইটেম: Home, Category, Cart, Wishlist, Account
- প্রতিটিতে inline SVG icon (24×24, `stroke-width="1.5"`, currentColor) + নিচে label
- Cart ও Wishlist-এ badge (সংখ্যা দেখাবে)
- root element-এ অবশ্যই: `id="mobile-bottom-nav" data-turbo-permanent`
  (Turbo যাতে এটি পুনরায় তৈরি না করে)

**Route**
প্রজেক্টের route নাম ব্যবহার করবে। কোন route কী নামে আছে জানতে
`themes/stylique/views/front_end/partison/header.blade.php` এবং `footer.blade.php`
পড়ো — সেখানে কীভাবে link তৈরি হয়েছে দেখে হুবহু সেই প্যাটার্ন অনুসরণ করো।
⛔ route নাম অনুমান করবে না। খুঁজে বের করবে। না পেলে রিপোর্টে লিখে `#` রাখবে।

**CSS** (একই ফাইলের `@push('page-style')` ব্লকে)
- `position: fixed; bottom: 0; left: 0; right: 0;`
- `padding-bottom: env(safe-area-inset-bottom);` (iPhone notch)
- `z-index` — header-এর চেয়ে কম, modal-এর চেয়ে কম রাখবে (header/footer CSS দেখে
  উপযুক্ত মান বাছবে, অনুমান নয়)
- শুধু মোবাইলে দেখাবে: `@media (min-width: 768px) { display: none; }`
- সক্রিয় আইটেম হাইলাইট হবে
- `body { padding-bottom: 60px; }` মোবাইলে — যাতে ফুটার কনটেন্ট ঢাকা না পড়ে
- touch target সর্বনিম্ন ৪৪×৪৪ px
- `-webkit-tap-highlight-color: transparent;`
- মসৃণ transition, কিন্তু `transition: all` নয় — নির্দিষ্ট property

**সক্রিয় অবস্থা (active state)**
- বর্তমান route অনুযায়ী হাইলাইট হবে
- Turbo navigation-এর পরেও ঠিক থাকতে হবে, তাই vanilla JS-এ:
  ```js
  document.addEventListener('turbo:load', function () { /* active class সেট করো */ });
  ```
  (`window.onTurboLoad` ব্যবহার করতে পারো যদি `turbo-bootstrap.js` থাকে)

### 🔴 কঠোর নিষেধাজ্ঞা

- শুধু এই একটি নতুন ফাইল তৈরি করবে
- কোনো layout ফাইলে include যোগ করবে **না** — Claude করবে
- বিদ্যমান header/footer/CSS ফাইল স্পর্শ করবে না
- route নাম অনুমান করবে না
- jQuery ব্যবহার করবে না, vanilla JS

### সম্পন্ন করার পর

১. রিপোর্ট: `.ai/reports/mobile-nav.md`
   - ফাইল পাথ
   - কোন route নাম ব্যবহার করেছ, কোথা থেকে পেয়েছ
   - কোন route খুঁজে পাওনি (Claude ঠিক করবে)
   - Claude-কে কোথায় include করতে হবে
   - cart/wishlist badge-এর সংখ্যা কোন variable থেকে আসছে

২. `.ai/PROGRESS.md`-এ BULK-6 → ✅ DONE

৩. commit:
git add -A
git commit -m "feat(mobile): add bottom navigation bar for storefront  [BULK-6]"

৪. বাংলায় জানাও।
```

---

## প্রম্পট ৭ — DataTable → Mobile card view (Day 4)

```
তুমি `C:\laragon\www\ecommercego` প্রজেক্টে কাজ করছ।

প্রথমে পড়ো:
1. .ai/PROJECT_CONTEXT.md
2. .ai/PROGRESS.md
3. .ai/reports/mobile-audit.md   (থাকলে — ৬+ কলামের টেবিলের তালিকা আছে)

branch: git checkout day4/mobile-polish

### প্রেক্ষাপট

Admin panel-এ ৭০টি পেজে DataTable আছে। এর মধ্যে ৩৮টি
`<x-datatable :dataTable="$dataTable" />` component ব্যবহার করে
(ফাইল: `resources/views/components/datatable.blade.php`)।

মোবাইলে এই টেবিলগুলো horizontal scroll হয় — ব্যবহারকারীকে ডানে-বাঁয়ে টানতে হয়।
এটি app-এর মতো লাগে না। দোকানদাররা মোবাইল থেকেই admin চালাবেন, তাই এটি বড় সমস্যা।

সমাধান: **শুধু CSS দিয়ে** টেবিলকে মোবাইলে card-এর মতো দেখানো। প্রতিটি row
একটি card হবে, প্রতিটি cell-এর আগে column-এর নাম দেখাবে।

এই কৌশলে `<th>` থেকে label নিতে হয়, যা pure CSS-এ সম্ভব নয় — তাই একটি ছোট
JS দিয়ে প্রতিটি `<td>`-তে `data-label` attribute বসাতে হবে।

### তোমার কাজ (BULK-7)

**ফাইল ১: নতুন CSS** — `public/css/mobile-table.css`

```
@media (max-width: 767px) {
  /* .ecom-data-table এর ভেতরের table-কে card বানাও */
  /* thead লুকাও */
  /* প্রতিটি tr = একটি card: border, radius, margin, padding, background */
  /* প্রতিটি td = display:flex, justify-content:space-between */
  /* td::before { content: attr(data-label); font-weight:600; } */
  /* checkbox ও action কলামকে বিশেষভাবে handle করো */
  /* DataTable-এর pagination/length/filter মোবাইলে stack করো */
}
```

শর্ত:
- শুধু `.ecom-data-table` এর ভেতরে প্রভাব ফেলবে (এই class
  `resources/views/components/datatable.blade.php:7` এ আছে)
- ৭৬৮px-এর উপরে কোনো প্রভাব থাকবে না — ডেস্কটপ ডিজাইন অপরিবর্তিত
- dark mode থাকলে সেটাও সম্মান করবে (প্রজেক্টে `style-dark.css` আছে,
  সেটি পড়ে কোন selector দিয়ে dark mode হয় দেখে নাও)
- `!important` যত কম সম্ভব, কিন্তু DataTables-এর inline width override করতে
  প্রয়োজন হলে ব্যবহার করো

**ফাইল ২: নতুন JS** — `public/js/mobile-table.js`

vanilla JS (jQuery নয়), যা করবে:
- `.ecom-data-table table` খুঁজবে
- `thead th`-এর টেক্সট তুলে নেবে
- প্রতিটি `tbody td`-তে সংশ্লিষ্ট `data-label` বসাবে
- DataTable যেহেতু ajax দিয়ে row বদলায়, তাই এটি বারবার চালাতে হবে:
  - `document.addEventListener('turbo:load', apply)` (বা `window.onTurboLoad`)
  - DataTables-এর `draw.dt` ইভেন্টেও চালাতে হবে — jQuery ছাড়াই করতে
    `MutationObserver` ব্যবহার করো `tbody`-র উপর
- শুধু `window.innerWidth < 768` হলে কাজ করবে (ডেস্কটপে অপ্রয়োজনীয় কাজ নয়)
- একই td-তে দুইবার label না বসে — চেক করবে

### 🔴 কঠোর নিষেধাজ্ঞা

- শুধু এই দুইটি নতুন ফাইল তৈরি করবে
- `resources/views/components/datatable.blade.php` স্পর্শ করবে **না**
- কোনো layout-এ CSS/JS link যোগ করবে না — Claude করবে
- বিদ্যমান কোনো CSS ফাইল সম্পাদনা করবে না
- jQuery ব্যবহার করবে না

### সম্পন্ন করার পর

১. রিপোর্ট: `.ai/reports/mobile-table.md`
   - দুইটি ফাইলের পাথ
   - Claude-কে কোথায় link যোগ করতে হবে
   - কোন কোন কলাম বিশেষভাবে handle করা হয়েছে (checkbox, action ইত্যাদি)
   - কোন সীমাবদ্ধতা আছে (কোন ধরনের টেবিলে কাজ করবে না)

২. `.ai/PROGRESS.md`-এ BULK-7 → ✅ DONE

৩. commit:
git add -A
git commit -m "feat(mobile): CSS card view for admin datatables on mobile  [BULK-7]"

৪. বাংলায় জানাও।
```

---

## প্রম্পট ৮ - FraudPixelShield মডিউল scaffold + BD Courier rotation engine (Phase-2)

```
তুমি `C:\laragon\www\ecommercego` প্রজেক্টে কাজ করছ।

⛔⛔⛔ সবচেয়ে গুরুত্বপূর্ণ নিয়ম (তিনবার পড়ো) — এই প্রম্পটে যেই ফাইল/ফোল্ডারের
নাম স্পষ্টভাবে লেখা আছে, শুধু সেগুলোতেই হাত দেবে। অন্য কোনো ফাইল,
অন্য কোনো ফোল্ডার — পড়া যাবে, ছোঁয়া যাবে না। ESPECIALLY:
`app/`, `routes/`, `database/`, `vendor/`, `packages/workdo/*`
(শুধু নিচে অনুমোদিত নতুন ফোল্ডার ছাড়া), `resources/`, `themes/` —
এগুলোতে নতুন ফাইল বানানোও নিষেধ, এডিট তো দূরের কথা। এই নিয়ম ভাঙলে তোমার
পুরো কাজ reject হবে।

### ALLOWED (শুধু এগুলো):

```
packages/workdo/FraudPixelShield/     ← নতুন মডিউল, সবকিছু এর ভেতরে
.ai/reports/fraud-shield-build.md     ← তোমার রিপোর্ট
```

নতুন branch-এ কাজ করবে:
```
git checkout -b feat/fraud-shield
```
(main/master branch-এ সরাসরি commit নয়।)

### তোমার কাজ (BULK-8): দুই ধাপ

**ধাপ ১ — scaffold (PwaManifest-কে হুবহু template ধরো):**
`packages/workdo/PwaManifest/` ফোল্ডারের প্রতিটি ফাইল পড়ো (module.json,
composer.json, src/Providers/*, src/Routes/web.php, src/Http/Controllers/*)।
ঠিক একই কাঠামোতে বানাও:
```
packages/workdo/FraudPixelShield/
├── module.json                 (name: FraudPixelShield)
├── composer.json
└── src/
    ├── Providers/
    │   ├── FraudPixelShieldServiceProvider.php
    │   └── RouteServiceProvider.php
    ├── Routes/web.php          (শুধু POST /fraud-check, throttle 10/min — নিচে)
    ├── Http/Controllers/FraudCheckController.php   (stub: validate → JSON)
    ├── Services/RiskScorer.php                     (নিচের algorithm)
    └── Database/Migrations/    (৩টি migration — নিচে)
```
প্রতিটি PHP ফাইলের namespace `Workdo\FraudPixelShield\...` হতে হবে —
`App\...` লিখলে কাজ বাতিল।

**ধাপ ২ — RiskScorer algorithm (E:\ রেফারেন্স থেকে PORT, copy নয়):**
বাইরের রেফারেন্স পাথ (শুধু পড়বে, copy করবে না):
```
E:\SENTRIX_ALL_WORK\AI-RELATED-TOOLS-BACKUP\Ai-Customer-Data-Checker\courierApiManager.js
E:\SENTRIX_ALL_WORK\AI-RELATED-TOOLS-BACKUP\Ai-Customer-Data-Checker\fraudChecker.js (শুধু fetchBdCourierApi অংশ, লাইন ~170-211)
```
PORT করবে (PHP/Laravel HTTP client দিয়ে নতুন করে লিখবে):
1. key rotation: store-এর key pool থেকে random active key (daily limit 50),
   ব্যবহারের পর usage_count++ — `courierApiManager.js`-এর logic।
2. BD Courier `POST /courier-check` (Bearer) → total/delivered/cancelled parse।
3. risk score 0-100: `100 - success_rate`, threshold env থেকে।
⛔ PORT করবে না (নিষেধ): Puppeteer scraper, SQLite, Express, Socket.io,
   whatsapp-web.js, Excel, UI/sound — এগুলোর এক লাইনও আনবে না।

**Migration (৩টি, মডিউলের ভেতরে — core `database/` ছোঁয়া নিষেধ):**
1. `fraud_phone_cache` — phone_hash CHAR(64) PK, risk_score, totals, last_synced_at
2. `fraud_lead_logs` — store_id, phone_hash, phone_last4, name NULL, cart_snapshot JSON NULL, risk_score, decision ENUM(allowed,soft_blocked)
3. `fraud_api_keys` — store_id, courier_name, key_value (encrypted), status, usage_count, last_reset_at
কাঁচা ফোন নম্বর plain-text-এ রাখা নিষেধ — শুধু sha256 hash + last4।

**Kill-switch (বাধ্যতামূলক):** Controller-এর প্রথম লাইনে
`if (!env('FRAUD_PIXEL_SHIELD_ENABLED', true)) abort(404);`
এটা ছাড়া কাজ অসম্পূর্ণ।

### যাচাই (কোড চালিয়ে, অনুমান নয়):
1. `php -l` প্রতিটি PHP ফাইলে — syntax error শূন্য হতে হবে।
2. `php artisan migrate --pretend` — SQL দেখাবে, error দেবে না।
   (আসল migrate চালাবে না — শুধু pretend।)
3. RiskScorer-এর পিওর function (score calculation) — ৩টি sample input দিয়ে
   হাতে হিসাব মিলিয়ে রিপোর্টে লেখো।

### শেষে:
1. `.ai/reports/fraud-shield-build.md` — কোন ফাইল বানালে, rotation logic
   কীভাবে port করলে, কী বাদ দিলে (scraper/UI), pretend output।
2. `.ai/PROGRESS.md`-এ BULK-8 → ✅ DONE বা Blockers (PROGRESS-এর টেবিলে
   BULK-8 row না থাকলে Blockers সেকশনে লিখবে, টেবিল বানাবে না)।
3. commit (feat/fraud-shield branch-এ):
git add packages/workdo/FraudPixelShield .ai/reports/fraud-shield-build.md
git commit -m "feat(fraud): shield scaffold + BD courier rotation engine  [BULK-8]"
⛔ `git add -A` দেবে না — শুধু উপরের দুইটি path। `git push` কখনো নয়।
4. বাংলায় জানাও: কী বানালে, কী বাদ দিলে, pretend পাস কি না।
```

---

## ⚠️ BULK মডেলের জন্য সাধারণ নিয়ম

1. **প্রতিটি প্রম্পটে যে ফাইলগুলোর নাম বলা হয়েছে, শুধু সেগুলোতেই কাজ করবে।** আর কিছুতে নয়।
2. **রেসিপি হুবহু মানবে।** নিজের বুদ্ধিতে "আরও ভালো" করার চেষ্টা করবে না — এখানে সামঞ্জস্য বেশি জরুরি।
3. **সন্দেহ হলে স্পর্শ করবে না।** রিপোর্টে "Claude দেখবে" লিখে রাখবে।
4. **আটকে গেলে** `.ai/PROGRESS.md`-এর Blockers-এ লিখে থামবে।
5. **প্রতি ৫ ফাইলে checkpoint commit** — উপরের "🔴 প্রতিটি টাস্কে যা মানতে হবে" সেকশন দেখো। এটি বাদ দিলে টোকেন লিমিট ফুরালে কাজ হারাবে।
6. **কাজ হারিয়ে গেলে** — `.ai/PROGRESS.md` পড়ো + `git log --oneline -20` চালাও + এই টাস্কের `.ai/reports/` ফাইল পড়ো। শেষ হওয়া ফাইল আবার করবে না।
7. **`app/`, `routes/`, `database/`, `vendor/`, `packages/` — এই ফোল্ডারগুলো তোমার জন্য নিষিদ্ধ।**
