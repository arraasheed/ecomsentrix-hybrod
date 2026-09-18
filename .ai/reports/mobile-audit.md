# Mobile UX Audit

**তারিখ:** ১৮ সেপ্টেম্বর ২০২৬  
**মডেল / অডিটর:** Gemini (Antigravity)  
**টাস্ক:** GEMINI-4 (Day 4 — Mobile App-Feel Audit)  
**ভিউপোর্ট অডিট:** 412 × 915 (Android / Chrome) ও 390 × 844 (iOS / Safari)  

---

## ১. সারসংক্ষেপ

| স্তর | সংখ্যা | বিবরণ |
|---|---|---|
| 🔴 **গুরুতর (Critical)** | **৫টি** | Native app-feel সম্পূর্ণ নষ্ট করে, iOS auto-zoom, ১১ কলামের টেবিল ব্রেকিং, safe-area ওভারল্যাপ, অ্যাক্সেসিবিলিটি জুম ব্লক |
| 🟠 **মধ্যম (Medium)** | **৬টি** | টাচ টার্গেট ছোট (< ৪৪px), ভুল কীবোর্ড টাইপ (tel/numeric), মোবাইল বটম বার অনুপস্থিত, মডাল সেন্টারিং ইস্যু |
| 🟡 **সামান্য (Minor)** | **৪টি** | অ্যানিমেশন স্মুথনেস, সোয়াইপ জেসচার অভাব, ফর্ম অটোফিল ট্যাগিং, সাবমেনু অ্যাকর্ডিয়ন |
| **মোট চিহ্নিত সমস্যা** | **১৫টি** | |

---

## ২. 🔴 গুরুতর সমস্যা (Critical App-Feel Blockers)

| # | সমস্যা | ফাইল:লাইন | মোবাইলে ব্যবহারকারী কী দেখবে | প্রস্তাবিত সমাধান |
|---|---|---|---|---|
| **C1** | **iOS Safari Input Auto-Zoom (< 16px)** | `themes/stylique/assets/css/main-style.css:48`<br>`themes/greentic/assets/css/main-style.css:48`<br>`themes/stylique/assets/css/responsive.css:12`<br>`public/build/assets/store-*.css` | মোবাইলে যেকোনো input বা select-এ ট্যাপ করলেই স্ক্রিন আকস্মিকভাবে বড় (zoom-in) হয়ে যায়। ব্যবহারকারীকে প্রতিবার টাইপ শেষে দুই আঙুল দিয়ে pinch-zoom-out করতে হয়। | `input, select, textarea, .form-control, .form-input` এ `font-size: 16px !important` নির্ধারণ করা (বা `transform: scale()` কৌশল)। |
| **C2** | **১১ কলাম পর্যন্ত বিশাল DataTable (Horizontally Broken)** | `app/DataTables/ProductDataTable.php:168` (১১ cols)<br>`app/DataTables/PlanOrderDataTable.php:168` (১০ cols)<br>`app/DataTables/CustomerDataTable.php:168` (৯ cols)<br>`app/DataTables/OrderDataTable.php:168` (৭ cols) | মোবাইলের ৩৬০-৪১২px স্ক্রিনে টেবিল কেটে যায়। অ্যাকশন বোতাম বা দাম দেখতে ডানে-বামে লম্বা স্ক্রোল করতে হয়, ডেটার সারি বোঝা যায় না। | BULK-7 টাস্কে `@media (max-width: 768px)` দিয়ে টেবিলকে আধুনিক Card View (flex/grid) লেআউটে রূপান্তর করা। |
| **C3** | **Safe-Area Inset অনুপস্থিত (iPhone Home Bar Overlay)** | সকল CSS ফাইল (`safe-area-inset` গণনা: ০)<br>`themes/stylique/views/front_end/layouts/app.blade.php` | iPhone-এর নিচের Home Indicator বার ফিক্সড বাটন (যেমন Add to Cart, Sticky Checkout) এবং ফুটারের ওপর চেপে থাকে। ব্যবহারকারী বাটনে ক্লিক করতে গিয়ে অ্যাপ মিনিমাইজ করে ফেলে। | `padding-bottom: env(safe-area-inset-bottom, 16px)` যোগ করা এবং ভিউপোর্টে `viewport-fit=cover` নিশ্চিত করা। |
| **C4** | **`100vh` Viewport Height বাগ (Dynamic URL Bar Clashing)** | `resources/css/store.css`<br>`resources/views/layouts/guest.blade.php`<br>Auth ও Modal স্ক্রিন | মোবাইল ব্রাউজারের অ্যাড্রেস বার উপরে-নিচে উঠলে `100vh` পেজের নিচের অংশ কেটে ফেলে, লগইন বাটন বা মডালের সেভ বাটন স্ক্রিনের নিচে ঢাকা পড়ে যায়। | `100vh`-এর বদলে আধুনিক `100dvh` (Dynamic Viewport Height) এবং fallback `min-height: -webkit-fill-available` ব্যবহার করা। |
| **C5** | **অ্যাক্সেসিবিলিটি জুম ব্লক (`user-scalable=0` / `maximum-scale=1`)** | `resources/views/layouts/app.blade.php:73`<br>`resources/views/partision/head.blade.php:30`<br>`themes/*/views/front_end/layouts/app.blade.php:6` | দৃষ্টি প্রতিবন্ধী বা সাধারণ ব্যবহারকারী কোনো ছোট টেক্সট বা ছবি জুম করে বড় করতে পারে না। WCAG 1.4.4 লঙ্ঘন। | `user-scalable=0` ও `maximum-scale=1` সরিয়ে স্ট্যান্ডার্ড `<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">` ব্যবহার করা। |

---

## ৩. 🟠 মধ্যম সমস্যা (Medium UX Friction)

| # | সমস্যা | ফাইল:লাইন | মোবাইলে ব্যবহারকারী কী দেখবে | প্রস্তাবিত সমাধান |
|---|---|---|---|---|
| **M1** | **ক্ষুদ্র Touch Targets (< 44×44 px)** | `resources/views/**/*.blade.php` (২০০০+ `btn-sm`, `btn-xs`, `btn-icon`)<br>`resources/views/components/datatable.blade.php` | টেবিলের Action Icons (এডিট/ডিলিট), পেজিনেশন নম্বর এবং কোয়ান্টিটি (+/-) বাটনে সহজে আঙুল দিয়ে ক্লিক করা যায় না; ভুল লিংকে ট্যাপ লেগে যায়। | মোবাইল ভিউপোর্টে অ্যাকশন বাটনগুলোর জন্য ন্যূনতম `min-height: 44px; min-width: 44px; display: inline-flex; align-items: center; justify-content: center;` নিশ্চিত করা। |
| **M2** | **মোবাইল বটম নেভিগেশন বার অনুপস্থিত** | `themes/*/views/front_end/layouts/app.blade.php` | ক্রেতাকে সবসময় উপরে গিয়ে ৩-লাইন হ্যামবার্গার মেনু খুলতে হয়। হোম, কার্ট, উইশলিস্ট বা অ্যাকাউন্টে এক ট্যাপে যাওয়ার কোনো সহজ বটম বার নেই। | BULK-6 টাস্কে ফিক্সড Mobile Bottom Bar (Home, Category, Search, Cart Badge, Account) যোগ করা। |
| **M3** | **মোবাইল কীবোর্ড অপ্টিমাইজেশন নেই (`type="tel"` / `inputmode="numeric"`)** | `themes/stylique/views/front_end/pages/checkout.blade.php`<br>`resources/views/customer/create.blade.php`<br>`resources/views/auth/login.blade.php` | ফোন নম্বর, ওটিপি, জিপ কোড বা পরিমাণের ঘরে ক্লিক করলে ইংরেজি টেক্সট কীবোর্ড খোলে। ব্যবহারকারীকে প্রতিবার ম্যানুয়ালি নম্বর প্যাডে সুইচ করতে হয়। | ফোন ইনপুটে `type="tel"` এবং ওটিপি/সংখ্যা ফিল্ডে `inputmode="numeric"` ও `autocomplete` যোগ করা। |
| **M4** | **ডেস্কটপ-স্টাইল Floating Modal (১০১টি Ajax Popup)** | `resources/views/layouts/app.blade.php:240`<br>`resources/views/front_end/jQueryRoute.blade.php` | মোবাইলে মডাল পপআপ স্ক্রিনের মাঝে ছোট বক্স হয়ে ভেসে থাকে, সফট কীবোর্ড উঠলে ফর্মের অর্ধেক ফিল্ড অদৃশ্য হয়ে যায়। | মোবাইলে মডালকে Bottom Sheet Drawer (নিচ থেকে উপরে ওঠা ফুল-উইডথ স্লাইডার) হিসেবে রেন্ডার করা। |
| **M5** | **Admin Layout-এ মোবাইল Meta Tags অনুপস্থিত** | `resources/views/layouts/app.blade.php`<br>`resources/views/partision/head.blade.php` | অ্যাডমিন প্যানেলে স্ট্যাটাস বার কালার নেই, standalone মোডে ব্রাউজার হেডার কালো বা বেমানান দেখায়। | `<meta name="theme-color">`, `<meta name="apple-mobile-web-app-capable" content="yes">`, `<link rel="apple-touch-icon">` যোগ করা। |
| **M6** | **মোবাইল টেবিল স্ক্রোল কন্টেইনার মিসিং** | `resources/views/front_end/sections/order_return_list.blade.php`<br>`resources/views/pos/create.blade.php`<br>`resources/views/front_end/pages/order-detail.blade.php` | কিছু কাস্টম ব্লেড টেবিলে `.table-responsive` ডিভ না থাকায় পুরো পেজের লেআউট ডানে প্রসারিত হয়ে অনুভূমিক স্ক্রোলবার তৈরি করে। | টেবিলগুলোকে `<div class="table-responsive">` বা `overflow-x-auto` দিয়ে আবদ্ধ করা। |

---

## ৪. 🟡 সামান্য সমস্যা (Minor Polish)

| # | সমস্যা | ফাইল:লাইন | মোবাইলে ব্যবহারকারী কী দেখবে | প্রস্তাবিত সমাধান |
|---|---|---|---|---|
| **L1** | **Touch Tap Delay (300ms)** | Global CSS / Touch Action | বাটনে ট্যাপ করার পর সামান্য বিলম্বে রেসপন্স করে (ডাবল-ট্যাপ জুম চেকের কারণে)। | গ্লোবাল CSS-এ `touch-action: manipulation;` যোগ করা। (CLAUDE-14) |
| **L2** | **অ্যাক্টিভ ও প্রেসড স্টেট অ্যানিমেশন অভাব** | Buttons & Product Cards | বাটনে চাপ দিলে নেটিভ অ্যাপের মতো রিঅ্যাক্টিভ প্রেস/রিপল ইফেক্ট পাওয়া যায় না। | `:active { transform: scale(0.97); }` মাইক্রো-অ্যানিমেশন যোগ করা। |
| **L3** | **সোয়াইপ জেসচার অনুপস্থিত (Gallery / Drawer)** | `themes/*/assets/js/custom.js` | প্রোডাক্ট ইমেজ গ্যালারি বা সাইডবার ড্রয়ার আঙুল দিয়ে সোয়াইপ করে বন্ধ করা যায় না। | টাচ সোয়াইপ ইভেন্ট বা Swiper টাচ সাপোর্ট কার্যকর রাখা। |
| **L4** | **ফর্ম ব্রাউজার অটোফিল হিন্ট নেই** | Checkout & Address Forms | ব্রাউজারে সংরক্ষিত নাম, ইমেইল বা ঠিকানা এক ক্লিকে স্বয়ংক্রিয়ভাবে পূরণ হয় না। | `autocomplete="name"`, `autocomplete="email"`, `autocomplete="address-line1"` যোগ করা। |

---

## ৫. ৬+ কলামের টেবিল তালিকা (Mobile Card View রূপান্তর আবশ্যক)

নিচের টেবিলগুলো মোবাইলের স্ক্রিন প্রস্থে আঁটে না এবং ব্যবহারকারীর জন্য সবচেয়ে বেশি সমস্যার সৃষ্টি করে:

| # | DataTable / Blade ফাইল | কলাম সংখ্যা | মূল কলামসমূহ |
|---|---|---|---|
| ১ | `app/DataTables/ProductDataTable.php` | **১১** | Name, Category, Brand, Label, Cover, Variant, Rating, Price, Stock Status, Stock Qty, Action |
| ২ | `app/DataTables/PlanOrderDataTable.php` | **১০** | Order ID, Date, User Name, Plan Name, Price, Payment Type, Payment Status, Coupon Used, Receipt, Action |
| ৩ | `app/DataTables/CustomerDataTable.php` | **৯** | Name, Email, Last Active, Register Date, Orders, Total Spend, AOV, Status, Action |
| ৪ | `app/DataTables/CategoryDataTable.php` | **৭** | Name, Image, Icon, Parent, Trending, Status, Action |
| ৫ | `app/DataTables/ContactDataTable.php` | **৭** | First Name, Last Name, Email, Contact, Subject, Description, Action |
| ৬ | `app/DataTables/OrderDataTable.php` | **৭** | Order ID, Date, Customer Info, Price, Payment Type, Order Status, Action |
| ৭ | `app/DataTables/PlanRequestDataTable.php` | **৭** | User, Plan, Max Products, Max Stores, Duration, Created At, Action |
| ৮ | `app/DataTables/SupportTicketDataTable.php` | **৭** | Ticket ID, Order ID, Title, Date, Customer, Status, Action |
| ৯ | `app/DataTables/TaxMethodDataTable.php` | **৭** | Name, Tax Rate, Country, State, City, Priority, Action |
| ১০ | `app/DataTables/TestimonialDataTable.php` | **৭** | Avatar, Username, Category, Product, Rating, Description, Action |
| ১১ | `app/DataTables/BlogDataTable.php` | **৬** | Cover Image, Category, Title, Slug, Short Description, Action |
| ১২ | `app/DataTables/CouponDataTable.php` | **৬** | Name, Code, Discount, Limit, Expiry Date, Action |
| ১৩ | `app/DataTables/FlashSaleDataTable.php` | **৬** | Name, Discount Type, Start Date, End Date, Status, Action |
| ১৪ | `app/DataTables/ProductBrandDataTable.php` | **৬** | Name, Slug, Logo, Status, Is Popular, Action |
| ১৫ | `app/DataTables/SalesDownloadableProductDataTable.php` | **৬** | Order ID, Product Name, Customer, Attachment, Date, Action |
| ১৬ | `app/DataTables/ShippingZoneDataTable.php` | **৬** | No, Zone Name, Country, State, Shipping Method, Action |
| ১৭ | `resources/views/front_end/sections/order_return_list.blade.php` | **৬** | Order ID, Date, Product, Return Status, Reason, Action |
| ১৮ | `resources/views/shopify/coupon.blade.php` | **৬** | Name, Code, Discount, Expiry, Type, Action |
| ১৯ | `resources/views/woocommerce/coupon.blade.php` | **৬** | Name, Code, Discount, Expiry, Type, Action |

---

## ৬. অনুপস্থিত মোবাইল Meta Tag ম্যাট্রিক্স

| লেআউট ফাইল | `theme-color` | `apple-mobile-web-app-capable` | `apple-mobile-web-app-status-bar` | `viewport-fit=cover` | `user-scalable` স্ট্যাটাস |
|---|---|---|---|---|---|
| `resources/views/layouts/app.blade.php` (Admin) | ❌ অনুপস্থিত | ❌ অনুপস্থিত | ❌ অনুপস্থিত | ❌ অনুপস্থিত | ⚠️ `user-scalable=0` (Blocked) |
| `resources/views/partision/head.blade.php` | ❌ অনুপস্থিত | ❌ অনুপস্থিত | ❌ অনুপস্থিত | ❌ অনুপস্থিত | ⚠️ `user-scalable=0` (Blocked) |
| `themes/stylique/views/front_end/layouts/app.blade.php` | ✅ উপস্থিত (PWA) | ✅ উপস্থিত | ✅ উপস্থিত | ❌ অনুপস্থিত | ⚠️ `maximum-scale=1` |
| `themes/greentic/views/front_end/layouts/app.blade.php` | ✅ উপস্থিত (PWA) | ✅ উপস্থিত | ✅ উপস্থিত | ❌ অনুপস্থিত | ⚠️ `maximum-scale=1` |
| `themes/techzonix/views/front_end/layouts/app.blade.php` | ✅ উপস্থিত (PWA) | ✅ উপস্থিত | ✅ উপস্থিত | ❌ অনুপস্থিত | ⚠️ `maximum-scale=1` |

---

## ৭. অগ্রাধিকারভিত্তিক কর্মপরিকল্পনা (Day 4 Execution Roadmap)

মোবাইল অ্যাপ-লাইক অনুভূতি তৈরির জন্য গুরুত্বানুসারে কাজের ক্রম:

1. **মোবাইল বটম বার যুক্ত করা (BULK-6):**
   - Storefront-এ ফিক্সড বটম নেভিগেশন (Home, Categories, Search, Cart Badge, Profile)। এটি তৎক্ষণাৎ ই-কমার্স অ্যাপের মতো লুক দেবে।
2. **DataTable → Mobile Card View (BULK-7):**
   - ১৯টি বড় টেবিল সহ সকল DataTable-কে মোবাইলে রেসপনসিভ কার্ড লেআউটে রূপান্তর করা।
3. **Skeleton Loader ও Instant Navigation (CLAUDE-13):**
   - পেজ ট্রানজিশনে ব্ল্যাঙ্ক হোয়াইট স্ক্রিনের বদলে মসৃণ শিমার/স্কেলিটন প্রিভিউ।
4. **Touch & Viewport Polish (CLAUDE-14):**
   - iOS 16px Font Zoom ঠিক করা, Safe-Area ইনসেট যোগ করা, `100dvh` ফিক্স, `touch-action: manipulation` এবং ভিউপোর্ট মেটা ট্যাগ সংশোধন।
5. **Vue Island: Cart Drawer & Variant Selector (CLAUDE-15):**
   - ফুল-পেজ রিলোড বা জাম্পিং ছাড়াই নিচ/পাশ থেকে মসৃণ কার্ট ড্রয়ার ও ভ্যারিয়েন্ট সিলেক্টর খোলা।
