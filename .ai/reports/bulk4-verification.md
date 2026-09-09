# BULK-4 যাচাই ও অবশিষ্ট কাজের তালিকা (Claude)

**তারিখ:** Day 2
**যাচাই করা:** BULK-2 + BULK-3 + BULK-4, শেষ commit `771daf7`

---

## রায়: ✅ তিন batch সফল, ০ ডুপ্লিকেট

| যাচাই | ফলাফল |
|---|---|
| `onTurbo*` কল মোট | **213** (শুরুতে ০) |
| nested / double-wrap | **০** ✅ |
| ৯টি শেষ ফাইলে wrapper ও delegation সংখ্যা মিলছে | ✅ (৬৫-৭৩) |
| working tree | clean (শুধু `.ai/Bluk-4_backup.md` untracked) |

BULK মডেল দুইবার বদলেছে (DeepSeek → GLM 5.3 → DeepSeek), দুইবার টোকেন
লিমিটে থেমেছে, একবার ভুল ফোল্ডারে চলেছে — **কোনো ফাইলের কাজ হারায়নি।**
প্রতি ৩ ফাইলে checkpoint commit-এর নিয়মটি কাজে দিয়েছে।

---

## এখনো unwrapped: ২৬ ফাইলে ২৯টি wrapper

BULK-এর তালিকা শেষ, কিন্তু প্রজেক্টে এগুলো বাকি। নিচে শ্রেণিবদ্ধ করা হলো —
সবগুলো Turbo-র জন্য সমস্যা নয়।

### ক. ⛔ ইচ্ছাকৃতভাবে বাদ — standalone পেজ (৭টি)

নিজের `<head>`/`<!DOCTYPE>` দিয়ে শুরু, helper stub নেই। wrap করলে
`onTurboLoad is not a function` → পেজ মরে যাবে। Turbo navigation এখানে
পৌঁছায়ও না (`data-turbo="false"`)।

```
resources/views/payment/khalti.blade.php
resources/views/payment/paystack.blade.php
resources/views/payment/razorpay.blade.php
resources/views/plans/cybersourcerequest.blade.php
```

**কাজ নেই।** BULK-4 এর প্রম্পটে বাদ তালিকায় ছিল।

### খ. ⛔ ইচ্ছাকৃতভাবে বাদ — Turbo boot নিজেই (১টি)

```
resources/views/layouts/includes/turbo.blade.php
```

এটিই `onTurboLoad` stub সংজ্ঞায়িত করে। নিজেকে wrap করতে পারে না।

### গ. ✋ CLAUDE-6 এর কাজ (৬টি) — আগেই সিদ্ধান্ত নেওয়া

`.ai/reports/bulk3-skipped-decisions.md` দেখো।

```
resources/views/layouts/app.blade.php                    admin layout, select2 init
resources/views/menu/script.blade.php                    ৬১৩ লাইন, ~১৬ delegation
resources/views/front_end/pages/product-list.blade.php   Blade @if-এ function
resources/views/front_end/pages/checkout.blade.php       payment flow
resources/views/components/datatable.blade.php           🔴 নিচে দেখো
resources/views/plans/stripe.blade.php                   ৩টি wrapper, ১টি বাকি
```

🔴 **`components/datatable.blade.php` সবচেয়ে গুরুত্বপূর্ণ।** এটি ৭১টি
admin পেজে ব্যবহৃত shared component। ভেতরে:

```js
$(document).ready(function () {
    removeSortingOrderFromHeader();
});
```

CLAUDE-6 এর প্রম্পটেই এই ফাইলটি নাম ধরে বলা আছে ("ন্যূনতম সংশোধন করবে")।
এখানে `onTurboLoad` + `turbo:before-cache` এ DataTable destroy — দুইটি
একসাথে ডিজাইন করতে হবে।

### ঘ. ⚠️ Claude যাচাই করবে — BULK তালিকার বাইরে ছিল (৯টি)

এগুলো `script-inventory.md` এর শ্রেণি A/B-তে ছিল না (স্ক্যানার C বা E
শ্রেণিতে ফেলেছিল), তাই BULK-এর তালিকায় আসেনি। কিন্তু ভেতরে DOM-নির্ভর
init আছে:

| ফাইল | কী আছে | ঝুঁকি |
|---|---|---|
| `country/index.blade.php:339` | `$('#country-table').DataTable({...})` serverSide | 🔴 উচ্চ — দ্বিতীয় পেজে "Cannot reinitialise" |
| `product/index.blade.php:54` | localStorage থেকে toast + সম্ভবত DataTable | 🟠 |
| `product/list.blade.php` | DOM init | 🟠 |
| `pos/index.blade.php` (২টি) | POS পেজ, সম্ভবত জটিল | 🟠 |
| `woocommerce/customer.blade.php` | DataTable | 🟠 |
| `woocommerce/product.blade.php` | DataTable | 🟠 |
| `setting/email_fields.blade.php` | ajax দিয়ে load হয় | 🟡 |
| `marketplace/pricing.blade.php` | ল্যান্ডিং পেজ, admin নয় | 🟡 |
| `front_end/jQueryRoute.blade.php` | storefront shared | 🟠 |
| `front_end/common/checkout/checkout_js.blade.php` | checkout | 🟠 |

### ঙ. ✅ কোনো সমস্যা নয় — auth পেজ (৫টি)

```
resources/views/auth/login.blade.php
resources/views/auth/register.blade.php
resources/views/auth/forgot-password.blade.php
resources/views/auth/reset-password.blade.php
resources/views/auth/verify-email.blade.php
```

ভেতরে শুধু `grecaptcha.ready(...)`। reCAPTCHA প্রতি পেজে নতুন করে
render হয়, আর এই পেজগুলোতে Turbo navigation দিয়ে ঢোকা-বেরোনো হয় না
(login-এর পর পূর্ণ redirect)। **কাজ নেই**, তবে CLAUDE-8 এর টেস্টে
login flow যাচাই করতে হবে।

---

## CLAUDE-6 এর জন্য সংশোধিত কাজের তালিকা

মূল প্রম্পটে যা ছিল (DataTable destroy, modal cleanup, select2/summernote
destroy, admin layout-এ Turbo) — তার সাথে **এই ১০টি ফাইল যোগ হলো**
(উপরের ঘ. অংশ)।

অগ্রাধিকার:

```
১. components/datatable.blade.php   ← ৭১ পেজে প্রভাব, সবার আগে
২. country/index.blade.php          ← serverSide DataTable, নিশ্চিত ভাঙবে
৩. layouts/app.blade.php            ← select2, সব admin পেজে
৪. turbo-cleanup.js                 ← কেন্দ্রীয় destroy
৫. বাকি ৭টি                          ← যাচাই করে সিদ্ধান্ত
```

---

## তিন batch-এর হিসাব

| | ফাইল | block |
|---|---|---|
| BULK-2 | ২০ | ১৮ পরিবর্তিত |
| BULK-3 | ২০ | ১৫ পরিবর্তিত, ৫ স্কিপ |
| BULK-4 | ৭৩ | ৬৮ পরিবর্তিত, ৫ স্কিপ |
| **মোট** | **১১৩** | **১০১ block migrate** |

`onTurbo*` কল ২১৩টি — কারণ একটি ফাইলে একাধিক block থাকতে পারে, এবং
mixed block ভাগ করলে দুইটি কল হয়।
