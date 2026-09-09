# BULK-3 এর ৬টি "Claude দেখবে" ফাইল — সিদ্ধান্ত

**তারিখ:** Day 2
**প্রসঙ্গ:** BULK-3 (`.ai/reports/turbo-migration-batch2.md`) ২০টির ১৫টি
migrate করেছে, ৫টি ফাইল ও ১টি অতিরিক্ত block স্পর্শ করেনি — সঠিকভাবে।
প্রতিটি নিজে পড়ে দেখলাম। **কোনোটিই BULK-4-কে দেওয়া হবে না।**

---

## সারসংক্ষেপ

| # | ফাইল | সিদ্ধান্ত | কে করবে |
|---|---|---|---|
| 1 | `payment/authorizenet.blade.php` | ⏭️ **স্কিপ, স্থায়ীভাবে** | কেউ নয় |
| 2 | `payment/khalti.blade.php` | ⏭️ **স্কিপ, স্থায়ীভাবে** | কেউ নয় |
| 3 | `layouts/app.blade.php` @310 | ✋ **CLAUDE-6** | Claude |
| 4 | `menu/script.blade.php` | ✋ **CLAUDE-6** | Claude |
| 5 | `product-list.blade.php` | ✋ **CLAUDE-6** | Claude |
| 6 | `checkout.blade.php` block@365 | ✋ **CLAUDE-6** | Claude |

BULK মডেলের সিদ্ধান্ত ছয়টিতেই সঠিক ছিল। এগুলো recipe দিয়ে করা যায় না।

---

## ১ ও ২ — payment gateway পেজ: স্থায়ীভাবে স্কিপ

```
resources/views/payment/authorizenet.blade.php   ১১৬ লাইন, নিজের <!DOCTYPE html>…</html>
resources/views/payment/khalti.blade.php          ৭৮ লাইন, নিজের <head> দিয়ে শুরু
```

দুইটিই **সম্পূর্ণ standalone HTML পেজ** — কোনো layout extend করে না, তাই
`layouts/includes/turbo.blade.php` এর helper stub সেখানে নেই। `onTurboLoad`
দিয়ে মোড়ালে সাথে সাথে `onTurboLoad is not a function` — পেমেন্ট পেজ মরে যাবে।

আরও গুরুত্বপূর্ণ: এগুলোতে Turbo navigation পৌঁছায়ই না। gateway-এ যাওয়ার form-এ
`data-turbo="false"` বসানো আছে (CLAUDE-5), তাই ব্রাউজার সরাসরি ওই পেজে যায়,
পূর্ণ page load হয়, `$(document).ready` স্বাভাবিকভাবেই চলে।

**সিদ্ধান্ত: কখনো মোড়ানো হবে না।** এটি অসম্পূর্ণ কাজ নয়, ইচ্ছাকৃত।
একই যুক্তি `payment/` ফোল্ডারের অন্য standalone পেজগুলোতেও প্রযোজ্য।

---

## ৩ — `layouts/app.blade.php` @310: CLAUDE-6

```blade
<script type="text/javascript">
    function setActiveTheme(themeColor) { ... }
    setActiveTheme("{{$themeColor}}");     ← Blade মান, প্রতি পেজে বদলায়
    $(document).ready(function(){
        if ($('.select2').length > 0) { $('.select2').select2({ tags: true, ... }); }
    })
</script>
```

এটি **admin-এর সব পেজে চলে** — একটি ভুল হলে পুরো admin panel-এর select2 মরে।

কিন্তু আসল কারণ ভিন্ন: **CLAUDE-6 এই একই ফাইলে Turbo চালু করবে** এবং
`turbo-cleanup.js` বসাবে যা `turbo:before-cache` এ select2 destroy করবে।
init আর destroy একসাথে ডিজাইন করা দরকার — আলাদাভাবে দুইজন করলে হয় duplicate
select2, নয়তো destroy-করা element-এ init।

`$('.select2').length > 0` guard-টিও লক্ষণীয় — এটি ইতিমধ্যে idempotent-এর
কাছাকাছি, কিন্তু select2 দুইবার init হলে `select2-hidden-accessible` class
থেকে যায় এবং dropdown ডুপ্লিকেট হয়। CLAUDE-6 এ `$.fn.select2.defaults` বা
`.select2('destroy')` দিয়ে সমাধান করতে হবে।

---

## ৪ — `menu/script.blade.php`: CLAUDE-6

৬১৩ লাইনের একটি ফাইল, একটিমাত্র `<script>` block, ভেতরে:

```
line   5  $(document).ready(function () {
line   6      initializeSortable();
line   8      $(document).on("click", ".show-item-details", ...)   ← delegation
line  39      $(document).on('change', selectAllId, ...)
line  43      $(document).on('change', itemClass, ...)
line 205      $(document).on('click', '#add-custom-link', ...)
line 234      $(document).on("click", ".update-custom-link", ...)
line 266-411  আরও ৭টি delegation (#add-categories … .delete-menu-item)
line 435      $(document).on("click", "#saveMenu", ...)
line 463  });                                    ← ready শেষ
line 466  document.addEventListener('DOMContentLoaded', function() {
line 531      $(document).on('change', '.icon-select', ...)
line 549      $(document).on('click', '.preview-icon', ...)
line 611  });
```

**দুইটি আলাদা wrapper, ভেতরে ~১৬টি delegation, একটি widget init
(`initializeSortable`)।** নিয়ম E বলে "ভাগ করো", কিন্তু এখানে ভাগ মানে
৬১৩ লাইনের ফাইল পুনর্গঠন — recipe-ভিত্তিক কাজ নয়।

ভুল করলে menu builder সম্পূর্ণ অচল: drag-drop, item যোগ, save — সব।
আর এটি শুধু একটি পেজে ব্যবহৃত (`menu/edit.blade.php:444`), তাই ঝুঁকি সীমিত
কিন্তু ক্ষতি সম্পূর্ণ।

**CLAUDE-6 হাতে করবে**, প্রতিটি delegation যাচাই করে।

---

## ৫ — `product-list.blade.php`: CLAUDE-6

```
line 146  <script>
line 147  var isPreOrderModuleActive = {{ ... }};    ← inline data
line 150  $(document).ready(function() {              ← filter, sort, pagination
line 258      @if(module_is_active('SkipCart'))       ← Blade শর্ত JS-এর ভেতরে
line 259          @php $enable_skip_cart = Utility::GetValueByName(...) @endphp
line 274          initSkipCartHandlers();
line 332  });                                         ← ready শেষ
line 334  $(document).on("click", "#grid-view-btn", ...)  ← delegation, বাইরে
line 344  @if(module_is_active('SkipCart'))
line 350      function initSkipCartHandlers() { ... }  ← Blade শর্তের ভেতরে function
line 419  @endif
line 420  </script>
```

জটিলতা: **`initSkipCartHandlers` function-টি একটি Blade `@if`-এর ভেতরে
সংজ্ঞায়িত**, কিন্তু ডাকা হচ্ছে ready block-এর ভেতর থেকে (line 274) — সেটিও
আরেকটি `@if`-এর ভেতরে। SkipCart module নিষ্ক্রিয় থাকলে দুইটিই উবে যায়, তাই
এখন কাজ করে।

`onTurboLoad` দিয়ে মোড়ালে scope বদলায়: function declaration আর global
থাকবে না, ready-র ভেতর থেকে ডাকা `initSkipCartHandlers()` undefined হবে —
**শুধু যখন SkipCart module সক্রিয়**। এই প্রজেক্টে এখন নিষ্ক্রিয়, তাই টেস্টে
ধরা পড়বে না, কিন্তু গ্রাহক module কিনলে ভাঙবে।

ঠিক এই কারণেই BULK মডেলের "সন্দেহ হলে স্পর্শ করবে না" নিয়ম কাজে দিয়েছে।

---

## ৬ — `checkout.blade.php` block@365: CLAUDE-6

BULK-3 প্রথম block (@288, @296) migrate করেছে, দ্বিতীয়টি (@365) বাদ দিয়েছে:

```
line 366  $(document).ready(function() { setTimeout(() => { $("#formdata").append(...) }, 100); });
line 371  $(document).on("click", ".payfast_form", ...)      ← delegation
line 382  @if (\Auth::guard('customers')->user())
line 383      function get_payfast_status(amount, coupon) { ... }   ← Blade শর্তে function
line 403  @else
line 404      function get_payfast_status(amount, coupon) { ... }   ← ভিন্ন URL
line 424  @endif
line 425  $(".payfast_form").on("click", ...)   ← form action বদলায় gateway URL-এ
```

`product-list`-এর মতোই সমস্যা — **`get_payfast_status` function দুইবার
সংজ্ঞায়িত, `@if/@else` দিয়ে, login অবস্থা অনুযায়ী।** মোড়ালে scope নষ্ট হবে।

তার চেয়েও বড় কথা: এটি **payment flow**। line 429 এ form-এর action বদলে
`https://{pfHost}/eng/process` করা হয় — payfast gateway। এখানে ভুল করলে
গ্রাহকের টাকা যাওয়ার পথ ভাঙে, আর সেটি টেস্টে ধরা কঠিন (sandbox লাগে)।

**CLAUDE-7 (form redirect / Turbo 303) এই ফাইলটি এমনিতেই দেখবে।** সেখানেই
একসাথে সিদ্ধান্ত নেওয়া হবে।

---

## BULK-4 এর জন্য নির্দেশ

এই ৬টির কোনোটিই BULK-4 করবে না। BULK-4 এর প্রম্পটে স্পষ্ট নিষেধাজ্ঞা
যোগ করা হয়েছে।

BULK-4 এর কাজ: শ্রেণি A-র ৪১ নম্বর থেকে বাকি ফাইল + শ্রেণি B, **শুধু
`resources/` ও `themes/` এর ভেতরে**।

---

## একটি প্রশংসনীয় আচরণ

BULK মডেল নিজেই ধরেছে যে `order/refund_details.blade.php:314` এর চতুর্থ
ready block প্রথম পাসে বাদ পড়েছিল (read truncate হওয়ায়), এবং আলাদা
commit `1e93d71` দিয়ে ঠিক করেছে — লুকায়নি, রিপোর্টে স্বীকার করেছে।

যাচাই করলাম: ফাইলটিতে এখন ৪টি wrapper-ই migrated, কোনো unwrapped
`$(document).ready` নেই। double-wrap-ও হয়নি।
