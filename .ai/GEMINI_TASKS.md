# GEMINI — কাজের প্রম্পট

**ব্যবহারবিধি (ব্যবহারকারীর জন্য):**
নিচের প্রম্পটগুলো **একটি একটি করে** Gemini-কে দেবেন। এক নম্বর শেষ হলে দুই নম্বর।
প্রতিটি প্রম্পটের ভেতরের অংশ (```` ``` ```` এর মধ্যে) হুবহু copy-paste করবেন।
Gemini কাজ শেষে যা রিপোর্ট দেবে, সেটা Claude-কে দেখাবেন।

**Gemini-র ভূমিকা:** বিশ্লেষণ ও রিপোর্ট তৈরি। **সে কোনো কোড পরিবর্তন করবে না** (টাস্ক ৪ ছাড়া, যেখানে শুধু রিপোর্ট)। এতে ঝুঁকি শূন্য।

---

## প্রম্পট ১ — Asset Inventory (Day 1)

```
তুমি `C:\laragon\www\ecommercego` প্রজেক্টে কাজ করছ। এটি Laravel 11-এর একটি multi-store SaaS eCommerce (WorkDo / eCommerce Go)।

প্রথমে এই দুইটি ফাইল পড়ো — এগুলোতে সব নিয়ম আছে:
1. .ai/PROJECT_CONTEXT.md
2. .ai/PROGRESS.md

⛔ গুরুত্বপূর্ণ: এই টাস্কে তুমি একটি ফাইলও পরিবর্তন করবে না। শুধু পড়বে এবং একটি রিপোর্ট ফাইল তৈরি করবে।

তোমার কাজ (GEMINI-1): `public/` ফোল্ডারের 31 MB asset অডিট করা।

যা করতে হবে:
1. এই ফোল্ডারগুলোর সব CSS ও JS ফাইলের তালিকা করো, সাইজ সহ:
   - public/assets/css/  ও  public/assets/js/
   - public/css/  ও  public/js/
   - public/market_assets/
   - themes/stylique/assets/  (css ও js)

2. প্রতিটি ফাইলের জন্য বের করো এটি আসলে কোথায় ব্যবহৃত হচ্ছে। এই জায়গাগুলোতে খোঁজো:
   - resources/views/**/*.blade.php
   - themes/**/views/**/*.blade.php

3. প্রতিটি ফাইলকে এই তিন ভাগে ভাগ করো:
   - USED      = কোনো blade ফাইলে reference আছে (কোন কোন ফাইলে, লিখবে)
   - UNUSED    = কোথাও reference নেই
   - UNCERTAIN = dynamic path বা variable দিয়ে load হতে পারে

4. আলাদাভাবে চিহ্নিত করো:
   - ১০০ KB-এর বড় ফাইলগুলো
   - ডুপ্লিকেট ফাইল (যেমন jquery.min.js এবং jquery-3.6.0.min.js দুইটাই আছে কি? কোনটা কোথায় ব্যবহৃত?)
   - vendor library বনাম custom কোড

ফলাফল লিখবে এই ফাইলে: `.ai/reports/asset-inventory.md`

ফরম্যাট:
# Asset Inventory Report

## ১. সারসংক্ষেপ
- মোট ফাইল / মোট সাইজ
- USED কত (সাইজ) / UNUSED কত (সাইজ) / UNCERTAIN কত (সাইজ)
- সম্ভাব্য কতটুকু বাঁচানো যাবে

## ২. UNUSED ফাইল (নিরাপদে বাদ দেওয়া যায়)
| ফাইল | সাইজ | মন্তব্য |

## ৩. USED ফাইল
| ফাইল | সাইজ | কোন blade-এ |

## ৪. UNCERTAIN ফাইল
| ফাইল | সাইজ | কেন অনিশ্চিত |

## ৫. ডুপ্লিকেট
| ফাইল ১ | ফাইল ২ | কোনটা রাখা উচিত | কারণ |

## ৬. সবচেয়ে বড় ১৫টি ফাইল
| ফাইল | সাইজ | USED? |

শেষ হলে `.ai/PROGRESS.md` ফাইলে GEMINI-1 এর অবস্থা ⬜ PENDING থেকে ✅ DONE করো এবং নোট কলামে রিপোর্টের পাথ লেখো।

তারপর git commit করো:
cd C:\laragon\www\ecommercego
git add .ai/
git commit -m "docs(audit): asset inventory report  [GEMINI-1]"

সবশেষে আমাকে বাংলায় ৫-১০ লাইনে জানাও: সবচেয়ে বড় অপচয় কোথায়, কতটুকু বাঁচানো যাবে।
```

---

## প্রম্পট ২ — Tailwind Class Scan (Day 1)

```
তুমি `C:\laragon\www\ecommercego` প্রজেক্টে কাজ করছ।

প্রথমে পড়ো: .ai/PROJECT_CONTEXT.md এবং .ai/PROGRESS.md

⛔ এই টাস্কেও তুমি কোনো ফাইল পরিবর্তন করবে না। শুধু রিপোর্ট তৈরি করবে।

প্রেক্ষাপট: এই প্রজেক্টের storefront theme-গুলো ব্রাউজারে **Tailwind Play CDN** চালাচ্ছে —
`themes/stylique/assets/js/tailwind.js` (466 KB)। এটি runtime-এ CSS তৈরি করে, যা পারফরম্যান্সের
সবচেয়ে বড় সমস্যা। আমরা এটিকে সরিয়ে Vite দিয়ে প্রকৃত Tailwind build করব।

কিন্তু Play CDN সরালে যদি কোনো class বাদ পড়ে, ডিজাইন ভেঙে যাবে। তাই আগে জানতে হবে
কোন কোন Tailwind class আসলে ব্যবহৃত হচ্ছে — বিশেষ করে যেগুলো Tailwind-এর স্বাভাবিক
scanner ধরতে পারবে না।

তোমার কাজ (GEMINI-2):

1. এই ফাইলগুলোতে Tailwind class খোঁজো:
   - themes/stylique/views/**/*.blade.php   (~50 ফাইল)
   - themes/greentic/views/**/*.blade.php
   - themes/techzonix/views/**/*.blade.php
   - themes/*/assets/js/custom.js
   - resources/views/front_end/**/*.blade.php

2. বিশেষভাবে খুঁজে বের করো **dynamic / ঝুঁকিপূর্ণ** class ব্যবহার, কারণ Tailwind
   build-time scanner এগুলো ধরতে পারে না:
   - PHP দিয়ে তৈরি class: `class="bg-{{ $color }}"` বা `class="{{ $cls }}"`
   - JS দিয়ে যোগ করা class: `classList.add('...')`, `addClass('...')`, `className = '...'`
   - শর্তসাপেক্ষ: `@if` / ternary-এর ভেতরে class
   - Alpine.js: `:class="..."`, `x-bind:class`
   - কোনো config বা database থেকে আসা class

3. `themes/stylique/views/**` এ ব্যবহৃত সব unique Tailwind class-এর একটি তালিকা তৈরি করো
   (যেটা safelist-এ লাগতে পারে)।

4. Tailwind-এর কোন কোন প্লাগইন-নির্ভর ফিচার ব্যবহৃত হয়েছে দেখো:
   forms, typography, aspect-ratio, line-clamp, container queries।

5. `tailwind.config.js` ফাইলটি পড়ো এবং বলো এর `content` path বর্তমানে theme ফোল্ডার
   cover করে কি না।

ফলাফল লিখবে: `.ai/reports/tailwind-classes.md`

ফরম্যাট:
# Tailwind Class Scan Report

## ১. সারসংক্ষেপ
- মোট unique class পাওয়া গেছে
- ঝুঁকিপূর্ণ dynamic class কতটি
- Play CDN সরানো কি নিরাপদ? (হ্যাঁ / সতর্কতার সাথে / না) — কারণ সহ

## ২. 🔴 Dynamic Class (safelist-এ যোগ করতে হবে)
| ফাইল:লাইন | কোড | কেন ঝুঁকি |

## ৩. JS থেকে যোগ হওয়া class
| ফাইল:লাইন | class |

## ৪. প্রস্তাবিত tailwind.config.js content path
(কোড ব্লকে লিখবে — শুধু প্রস্তাব, ফাইল বদলাবে না)

## ৫. প্রস্তাবিত safelist
(কোড ব্লকে লিখবে)

## ৬. ব্যবহৃত Tailwind প্লাগইন
| প্লাগইন | প্রমাণ (কোন class) |

## ৭. ব্যবহৃত সব unique class (stylique theme)
(কোড ব্লকে, বর্ণানুক্রমে)

শেষ হলে `.ai/PROGRESS.md`-এ GEMINI-2 → ✅ DONE করো।

git commit করো:
cd C:\laragon\www\ecommercego
git add .ai/
git commit -m "docs(audit): tailwind class scan for Play CDN removal  [GEMINI-2]"

আমাকে বাংলায় সংক্ষেপে জানাও: Play CDN সরানো নিরাপদ কি না, এবং কতটি dynamic class safelist করতে হবে।
```

---

## প্রম্পট ৩ — Inline Script Inventory (Day 2)

```
তুমি `C:\laragon\www\ecommercego` প্রজেক্টে কাজ করছ।

প্রথমে পড়ো: .ai/PROJECT_CONTEXT.md এবং .ai/PROGRESS.md

⛔ এই টাস্কেও তুমি কোনো blade ফাইল পরিবর্তন করবে না। শুধু রিপোর্ট।

প্রেক্ষাপট: আমরা Hotwired Turbo Drive বসাচ্ছি। Turbo পেজ reload না করে body swap করে,
তাই `$(document).ready()` শুধু প্রথম লোডে চলে — পরের পেজে আর চলে না। প্রজেক্টে ১৭০টি
blade ফাইলে inline `<script>` আছে, ৭২টিতে `$(document).ready`। এগুলো `turbo:load`
ইভেন্টে সরাতে হবে।

কিন্তু সব script একইভাবে সরানো যায় না। কিছু script একবারই চলা উচিত (যেমন global config,
event delegation), কিছু প্রতি পেজে চলা উচিত (যেমন DataTable init, select2 init)।
ভুল ভাগে ফেললে হয় duplicate binding হবে, নয়তো ফিচার কাজ করবে না।

তোমার কাজ (GEMINI-3): সব inline script শ্রেণিবিন্যাস করা।

স্ক্যান করবে:
- resources/views/**/*.blade.php
- themes/*/views/**/*.blade.php

প্রতিটি inline `<script>` block-কে এই ৫ শ্রেণিতে ফেলো:

A. REINIT-NEEDED → প্রতি Turbo navigation-এ আবার চালাতে হবে
   লক্ষণ: DataTable(), select2(), summernote(), flatpickr(), ApexCharts,
   Swiper(), dropzone, নতুন DOM element খুঁজে init করা

B. ONCE-ONLY → শুধু একবার চলবে, বারবার চললে সমস্যা
   লক্ষণ: `$(document).on('click', 'selector', ...)` (event delegation),
   global variable declaration, `window.X = ...`, service worker register,
   একবারের setup

C. INLINE-DATA → শুধু PHP থেকে JS variable পাস করছে, কোনো লজিক নেই
   লক্ষণ: `var x = "{{ $y }}";` ধরনের লাইন মাত্র

D. THIRD-PARTY → বাইরের স্ক্রিপ্ট (analytics, chat widget, pixel, payment SDK)
   লক্ষণ: googletagmanager, facebook pixel, tawk, stripe.js, razorpay ইত্যাদি

E. UNCLEAR → নিশ্চিত নও, Claude-কে দেখাতে হবে

ফলাফল লিখবে: `.ai/reports/script-inventory.md`

ফরম্যাট:
# Inline Script Inventory

## ১. সারসংক্ষেপ
| শ্রেণি | ফাইল সংখ্যা | script block সংখ্যা |
| A REINIT-NEEDED | | |
| B ONCE-ONLY | | |
| C INLINE-DATA | | |
| D THIRD-PARTY | | |
| E UNCLEAR | | |

## ২. A — REINIT-NEEDED
| ফাইল | লাইন | কী init করছে | নির্ভরশীল library |

## ৩. B — ONCE-ONLY
| ফাইল | লাইন | কী করছে | বারবার চললে কী সমস্যা |

## ৪. C — INLINE-DATA
| ফাইল | লাইন |

## ৫. D — THIRD-PARTY
| ফাইল | লাইন | কোন সেবা |

## ৬. E — UNCLEAR (Claude সিদ্ধান্ত নেবে)
| ফাইল | লাইন | কোড (সংক্ষেপে) | কেন অনিশ্চিত |

## ৭. 🔴 বিশেষ ঝুঁকি
এমন script যেগুলোতে A এবং B **একই block-এ মিশে আছে** —
এগুলো ভাগ করতে হবে, তাই আলাদা তালিকা দাও:
| ফাইল | লাইন | কোন অংশ A, কোন অংশ B |

শেষ হলে `.ai/PROGRESS.md`-এ GEMINI-3 → ✅ DONE করো।

git commit:
cd C:\laragon\www\ecommercego
git add .ai/
git commit -m "docs(audit): inline script inventory for Turbo migration  [GEMINI-3]"

আমাকে বাংলায় জানাও: কতটি script প্রতিটি শ্রেণিতে পড়ল, এবং সবচেয়ে ঝুঁকিপূর্ণ কোনগুলো।
```

---

## প্রম্পট ৪ — মোবাইল UX অডিট (Day 4)

```
তুমি `C:\laragon\www\ecommercego` প্রজেক্টে কাজ করছ।

প্রথমে পড়ো: .ai/PROJECT_CONTEXT.md এবং .ai/PROGRESS.md

⛔ কোনো ফাইল পরিবর্তন করবে না। শুধু রিপোর্ট।

প্রেক্ষাপট: এই প্রজেক্টের সবচেয়ে বড় প্রাধান্য মোবাইল। কারণ (ক) দোকানদাররা
মোবাইল থেকেই admin panel চালাবেন, (খ) ক্রেতারা প্রায় সবাই মোবাইল ব্যবহারকারী।
লক্ষ্য: মোবাইল ব্রাউজারে যেন native app-এর মতো অনুভূতি হয়।

তোমার কাজ (GEMINI-4): মোবাইলে কী কী ভাঙা আছে বা খারাপ লাগে, তার তালিকা।

Claude ইতিমধ্যে Playwright দিয়ে মোবাইল ভিউপোর্টে (412×915) স্ক্রিনশট নিয়ে
`.ai/screenshots/` ফোল্ডারে রেখেছে। সেগুলো দেখো। যদি ফোল্ডার খালি থাকে,
শুধু কোড বিশ্লেষণ করো এবং রিপোর্টে সেটা উল্লেখ করো।

কোড বিশ্লেষণে এই বিষয়গুলো দেখো:

1. **Viewport ও meta**
   - resources/views/layouts/app.blade.php এবং themes/*/views/front_end/layouts/app.blade.php
   - `user-scalable=0` আছে কি? (accessibility সমস্যা)
   - `theme-color`, `apple-mobile-web-app-*` meta আছে কি?

2. **টেবিল** — ৭০টি blade-এ DataTable আছে। মোবাইলে এগুলো কীভাবে দেখায়?
   `table-responsive` wrapper আছে কি? horizontal scroll হবে কি?
   কোন কোন টেবিলে ৬+ কলাম আছে (মোবাইলে সবচেয়ে খারাপ)?

3. **Modal** — ১০১টি `data-ajax-popup` modal। মোবাইলে full-screen বা
   bottom-sheet হয় কি, নাকি ছোট box-এ আটকে থাকে?

4. **Touch target** — button/link-এর সাইজ ৪৪×৪৪ px-এর কম কোথায়?
   CSS-এ ছোট padding বা `btn-sm`/`btn-xs` কোথায় বেশি?

5. **Sidebar** — admin sidebar মোবাইলে off-canvas হয় কি? কোন class দিয়ে?

6. **Form** — input-এ `type="tel"` / `type="email"` / `inputmode` ঠিকভাবে
   ব্যবহৃত কি? (ভুল হলে মোবাইলে ভুল keyboard আসে)

7. **Fixed element** — `position: fixed` header/footer আছে কি?
   iOS-এ `100vh` সমস্যা হবে কি? safe-area inset ব্যবহৃত কি?

8. **Font size** — ১৬px-এর ছোট input font কোথায়?
   (iOS-এ ১৬px-এর ছোট input-এ tap করলে auto-zoom হয়)

ফলাফল লিখবে: `.ai/reports/mobile-audit.md`

ফরম্যাট:
# Mobile UX Audit

## ১. সারসংক্ষেপ
- মোট সমস্যা: কত
- 🔴 গুরুতর (app-feel নষ্ট করে): কত
- 🟠 মধ্যম: কত
- 🟡 সামান্য: কত

## ২. 🔴 গুরুতর সমস্যা
| # | সমস্যা | ফাইল:লাইন | মোবাইলে ব্যবহারকারী কী দেখবে | প্রস্তাবিত সমাধান |

## ৩. 🟠 মধ্যম সমস্যা
(একই ফরম্যাট)

## ৪. 🟡 সামান্য সমস্যা
(একই ফরম্যাট)

## ৫. ৬+ কলামের টেবিল (card view দরকার)
| blade ফাইল | কলাম সংখ্যা |

## ৬. অনুপস্থিত মোবাইল meta tag
| ফাইল | কী নেই |

## ৭. প্রাধান্যক্রম
১ নম্বর থেকে শুরু করে কোনটা আগে ঠিক করলে সবচেয়ে বেশি লাভ — সেই ক্রমে সাজাও।

শেষ হলে `.ai/PROGRESS.md`-এ GEMINI-4 → ✅ DONE করো।

git commit:
cd C:\laragon\www\ecommercego
git add .ai/
git commit -m "docs(audit): mobile UX audit  [GEMINI-4]"

আমাকে বাংলায় জানাও: মোবাইলে সবচেয়ে বড় ৫টি সমস্যা কী।
```

---

## প্রম্পট ৫ - Pixel Block CAPI spec (Phase-2, SPEC ONLY — কোড নয়)

```
তুমি `C:\laragon\www\ecommercego` প্রজেক্টে কাজ করছ।

⛔⛔⛔ সবচেয়ে গুরুত্বপূর্ণ নিয়ম (তিনবার পড়ো) — এই টাস্কে তুমি কোনো কোড
লিখবে না, কোনো ফাইল এডিট করবে না, কোনো migration/controller/route বানাবে
না। তোমার পুরো output = **একটি রিপোর্ট ফাইল**:
```
.ai/reports/pixel-block-spec.md
```
এর বাইরে তুমি শুধু `.ai/PROGRESS.md` আপডেট করবে। এই দুইটির বাইরে একটি
অক্ষরও লিখলে তোমার কাজ reject হবে। `git commit`-ও তুমি করবে না —
ব্যবহারকারী করবেন।

### তোমার কাজ (GEMINI-5): Pixel Block-এর SPEC লেখা

রেফারেন্স (শুধু পড়বে):
```
E:\SENTRIX_ALL_WORK\AI-RELATED-TOOLS-BACKUP\SaaS-CAPI-Courier-Isolated\app\Jobs\SendMetaCapiPurchaseJob.php
E:\SENTRIX_ALL_WORK\AI-RELATED-TOOLS-BACKUP\SaaS-CAPI-Courier-Isolated\DEEPSEEK_ACTION_PLAN.md
C:\laragon\www\ecommercego\resources\views\setting\pixel_field_setting.blade.php
```
(রেফারেন্সের bug-গুলো — random-UUID idempotency, queue-তে request()->ip() —
SPEC-এ "Known defects to avoid" হিসেবে নথিভুক্ত করবে, copy করবে না।)

রিপোর্টে **ঠিক এই ৬টি heading** থাকবে, এই ক্রমে, অন্য heading নয়:

1. `## Endpoints` — Meta CAPI `Purchase` event-এর exact payload JSON
   (event_name, event_time, event_id=`order_{id}`, hashed em/ph/fn,
   currency BDT, value, action_source=website) + test_event_code ব্যবহারবিধি।
2. `## Idempotency` — deterministic key ফরম্যাট (`order_{id}_capi_purchase`),
   ledger টেবিল schema (key unique, status pending/dispatched/failed),
   double-confirm ও retry-তে কী হবে।
3. `## Status flow` — order status-এ `pixel_blocked` থেকে `pixel_sent`-এ
   যাওয়ার শর্ত (admin confirm + double-confirmation UI), কে confirm করতে
   পারবে (permission), confirm ছাড়া কী হবে (blocked-ই থাকবে)।
4. `## Credentials` — প্রতি store-এর pixel_id + access_token কোথায় থাকবে
   (settings spec: field নাম, encrypt কি না), না থাকলে কী হবে (dispatch skip + log)।
5. `## Attribution guard` — 7-day rule + test-event mode-এ যাচাই পদ্ধতি।
6. `## Test steps` — coder যাচাই করবে এমন 8টি step (test_event_code দিয়ে
   Events Manager-এ দেখা সহ)।

প্রতিটি section-এ "UNCERTAIN:" tag দেবে যেখানে নিশ্চিত নও — অনুমান করে
"সম্ভবত" লিখবে না।

### শেষে:
1. `.ai/PROGRESS.md`-এ GEMINI-5 → ✅ DONE বা Blockers (PROGRESS-এর টেবিলে
   GEMINI-5 row না থাকলে Blockers সেকশনে লিখবে, টেবিল বানাবে না)।
2. আমাকে বাংলায় জানাও: spec-এর 6টি section-এর এক-লাইন সারসংক্ষেপ +
   UNCERTAIN কয়টি।
```

---

## ⚠️ Gemini-র জন্য সাধারণ নিয়ম

---

## ⚠️ Gemini-র জন্য সাধারণ নিয়ম

1. **কোনো টাস্কে কোড পরিবর্তন করবে না।** শুধু `.ai/reports/` ফোল্ডারে ফাইল তৈরি করবে এবং `.ai/PROGRESS.md` আপডেট করবে।
2. **নিজের টাস্ক নম্বরের বাইরে কিছু করবে না।** পরের টাস্ক ব্যবহারকারী আলাদা করে দেবেন।
3. **আটকে গেলে** `.ai/PROGRESS.md`-এর `## Blockers` সেকশনে লিখবে, তারপর থামবে।
4. **অনুমান করবে না।** নিশ্চিত না হলে UNCERTAIN / UNCLEAR শ্রেণিতে রাখবে।
5. **কাজ হারিয়ে গেলে** — শুধু `.ai/PROGRESS.md` পড়ো আর `git log --oneline -15` চালাও। কোথায় থেমেছিলে বুঝে যাবে।
