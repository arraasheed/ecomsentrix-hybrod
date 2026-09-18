# Mobile Bottom Navigation — BULK-6 রিপোর্ট

**তারিখ:** ১৮ সেপ্টেম্বর ২০২৬
**Branch:** `day4/mobile-polish`
**অবস্থা:** ✅ সম্পন্ন (১টি নতুন ফাইল, অন্য কোনো ফাইল স্পর্শ করা হয়নি)

---

## ফাইল পাথ

```
themes/stylique/views/front_end/partison/mobile-bottom-nav.blade.php   (নতুন — এই একটিই)
```

- পরিবর্তন করেছি: **১ ফাইল** (নতুন তৈরি)
- স্পর্শ করিনি: **০ ফাইল** (header/footer/layout/CSS — কিছুই ধরা হয়নি)
- সন্দেহ — Claude দেখবে: **০ ফাইল** (নিচে ৫টি verification note আছে)

`php -l` ✅ (No syntax errors detected)

---

## ব্যবহৃত route নাম — কোথা থেকে পেয়েছি

কোনোটাই অনুমান নয়; প্রতিটি `routes/customer.php` থেকে যাচাই করা এবং
`partison/header.blade.php`-এর হুবহু প্যাটার্ন (`route('name', $slug)`) ধরে লেখা।

| আইটেম | Route নাম | উৎস | header/footer-এ প্যাটার্ন |
|---|---|---|---|
| Home | `landing_page` | `routes/customer.php:66` | header.blade.php:10 (`route('landing_page', $slug)`) |
| Category | `collections` | `routes/customer.php:75` | header-এ per-category link ব্যবহার হয় (`url($slug.'/'.$category->slug)`, header.blade.php:151); পুরো category listing পেজ হিসেবে `collections` নেওয়া হয়েছে |
| Cart | `page.cart` | `routes/customer.php:60` | ⚠️ header-এর Cart icon route নয় — drawer (`#cart-toggle`, header.blade.php:93)। আসল cart পেজের route আছে বলে সেটি ব্যবহার করা হয়েছে |
| Wishlist | `wishlist` | `routes/customer.php:64` | header.blade.php:81 (হুবহু এক) |
| Account (auth) | `my-account.index` | `routes/customer.php:138` (resource) | header.blade.php:62 (হুবহু এক) |
| Account (guest) | `customer.login` | `routes/customer.php:79` | header.blade.php:70 (হুবহু এক) |

`collections` পেজটি সত্যিকারের পেজ কি না যাচাই করা হয়েছে:
`HomeController::showCategory` (app/Http/Controllers/HomeController.php:1675) →
`front_end.pages.collection-list` view রিটার্ন করে (লাইন ১৬৯২)। 404 নয়।

**কোনো route পাওয়া যায়নি এমন কিছু নেই** — ৫টি আইটেমের সবগুলোই পাওয়া গেছে, তাই কোথাও `#` রাখা হয়নি।

---

## Badge-এর সংখ্যা কোথা থেকে আসছে

| Badge | Server-render (প্রথম পেজ লোড) | JS refresh (প্রতি Turbo navigation) |
|---|---|---|
| **Cart** | `\App\Models\Cart::CartCount($slug)` — `app/Models/Cart.php:636`। Auth হলে DB count, guest হলে `cart` cookie count | header-এর `#cart-count` (header.blade.php:101) থেকে mirror; `MutationObserver` দিয়ে add-to-cart-এর সাথে সাথেই লাইভ সিঙ্ক |
| **Wishlist** | `\App\Models\Wishlist::WishCount()` — `app/Models/Wishlist.php:109` (header.blade.php:85-এর হুবহু সেই call) | header-এর `.wishlist-header .cart-badge` (header.blade.php:85) থেকে mirror — header প্রতি navigation-এ তাজা server-render হয় |

কাউন্ট 0 বা খালি হলে badge লুকানো থাকে (`mnav-badge--hidden`)।

⚠️ **`Cart::CartCount()` এতদিন কোথাও ব্যবহৃত ছিল না** (grep: একমাত্র সংজ্ঞাটিই আছে)।
header ব্যবহার করে না কারণ header badge "0" রেন্ডার করে, `front-theme.js` এর
`get_cartlist()` AJAX-এ পরে ভরে। Nav-এ server-side count দেওয়া হয়েছে যাতে প্রথম
paint-এই সঠিক সংখ্যা দেখায়। এতে প্রতি পেজ রেন্ডারে **১টি হালকা count query** যোগ হয়।

---

## Claude-কে কোথায় include করতে হবে

`themes/stylique/views/front_end/layouts/app.blade.php` — body-র **শেষের দিকে**,
`@include('front_end.jQueryRoute')` (লাইন ৮২) ও footer include (লাইন ৮০) এর **পরে**।
সবচেয়ে সুবিধাজনক জায়গা: লাইন ১৩৮-এর `@include('front_end.partison.pwa-install')`
এর পাশে:

```blade
{{-- PWA install banner (BULK-5 / CLAUDE-11) --}}
@include('front_end.partison.pwa-install')

{{-- Mobile bottom navigation (BULK-6) --}}
@include('front_end.partison.mobile-bottom-nav')
```

কারণ: (১) header DOM তখন থাকে, ফলে script-এর badge mirror সাথে সাথে কাজ করে;
(২) nav DOM-এর একদম নিচে থাকে; (৩) `@push('page-style')` ও `@push('page-script')`
stack-এ যায় — layout-এর `@stack('page-style')` (head, লাইন ৫০) ও
`@stack('page-script')` (লাইন ১২০) এ রেন্ডার হয়, আলাদা কিছু করা লাগে না।

**Kill-switch:** include-টি কমেন্ট করলেই সব বন্ধ — markup, CSS, JS সব এই একটি
ফাইলের ভেতরে, বাইরে কোনো অবশিষ্টাংশ থাকে না।

---

## Claude verification notes (৫টি)

1. **`data-turbo-permanent`-এর ফলে server value পুরনো হয়** — nav element
   navigation-এ re-render হয় না, তাই active state ও badge JS দিয়ে প্রতি
   navigation-এ রিফ্রেশ করা হয়: active = URL pathname (`/home`, `/collections`,
   `/cart`, `/wishlist`, `/my-account|login`), badge = header badge mirror।
   Blade-এ `request()->routeIs(...)` দিয়ে initial active-ও দেওয়া আছে (প্রথম
   পেজে JS চালু হওয়ার আগেও সঠিক দেখায়)।
2. **Cart badge navigation-এর পরে** — `#cart-count` header-এ server "0" রেন্ডার
   করে; সঠিক সংখ্যা আসে `front-theme.js`-এর `$(document).ready → get_cartlist()` থেকে
   (front-theme.js:54-58, 222)। Turbo navigation-এ front-theme.js (jQueryRoute এর
   body script, defer) re-execute হয়, ফলে jQuery ready আবার চলে — তবে এটি ব্রাউজারে
   যাচাই করা দরকার (নেভিগেট করে cart badge সঠিক থাকছে কি না দেখা)। যদি re-fire না
   করে, cart badge পুরনো/0 দেখাতে পারে — তখন `onTurboLoad` init-এ `get_cartlist()`
   call করা দরকার হতে পারে (এটি আমার ফাইলের JS থেকে সম্ভব, খালি রাখা হয়েছে কারণ
   আগে থেকেই হওয়ার কথা)।
3. **Wishlist badge লাইভ আপডেট** — একই পেজে (navigation ছাড়া) wishlist add/remove
   করলে header badge কোনো JS দিয়ে আপডেট হয় না (`.wishlist-count` selector কোনো
   element-এ মেলে না — grep এ শূন্য)। ফলে সেই মুহূর্তে nav badge-ও পুরনো থাকবে —
   পরের navigation-এ ঠিক হয়ে যাবে। এটি header-এর বিদ্যমান আচরণের সমান, নতুন
   regression নয়।
4. **`body` padding** — প্রম্পটে লেখা ছিল `body { padding-bottom: 60px; }`।
   হুবহু 60px দিলে iPhone (home indicator ≈ +34px) এ footer এখনো ঢাকা পড়ত —
   রুলের উদ্দেশ্য ("ফুটার কনটেন্ট ঢাকা না পড়ে") রাখতে
   `calc(60px + env(safe-area-inset-bottom))` দেওয়া হয়েছে, শুধু মোবাইলে।
5. **z-index 10** — অনুমান নয়, compiled bundle থেকে মাপা: header `.sticky-header`=11,
   `.overlay`=12, storefront modal (Tailwind `z-50`)=50, cart drawer=999/1000
   (`public/build/assets/store-PN5rhyZR.css`)। 10 রাখলে bar সবার নিচে থাকে। প্রম্পটে
   "header-এর চেয়ে কম" ছিল — তাই 11-এর নিচেই রাখা হয়েছে; দুটি স্ক্রিনে ভিন্ন জায়গায়
   থাকায় দৃশ্যত কোনো overlap নেই।

---

## সারসংক্ষেপ

- ৫টি আইটেম: Home · Category · Cart · Wishlist · Account (auth হলে my-account,
  guest হলে login — header-এর হুবহু প্যাটার্ন)
- inline SVG 24×24, `stroke-width="1.5"`, `currentColor`
- root: `id="mobile-bottom-nav" data-turbo-permanent` ✅
- `@push('page-style')`-এ CSS: fixed bottom, `env(safe-area-inset-bottom)`,
  z-index 10 (মাপা), `min-width:768px`-এ hidden, active highlight,
  touch target ≥ 44×44, `-webkit-tap-highlight-color: transparent`,
  transition নির্দিষ্ট property-তে (`all` নয়)
- `@push('page-script')`-এ vanilla JS (jQuery নেই): `turbo:load`-এ active + badge sync
- include করা হয়নি — শুধু ফাইল তৈরি (include Claude-এর কাজ)
