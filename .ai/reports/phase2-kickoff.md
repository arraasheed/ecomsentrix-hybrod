# Phase-2 Kickoff — Analytic Report (Shareable)

> উদ্দেশ্য: অন্য মডেল যাচাই করবে — কী শেষ, কী বাকি, কে কী করবে।
> তারিখ: 2026-09-27 · লেখক: Muse Spark · ভিত্তি: git log + PROGRESS.md + browser-verified.

---

## 1. সম্পন্ন (Phase-1: CLAUDE-1 … CLAUDE-16, সব ✅ DONE)

| এলাকা | কী হয়েছে | প্রমাণ |
|---|---|---|
| Asset pipeline | Tailwind build (Play CDN বাদ), admin CSS bundle, cache-buster fix, image lazy | `benchmark.md` Day 1 Final: JS −44%, admin request −28% |
| Turbo Drive | Storefront SPA navigation, 240 script wrap, 303 middleware | back-nav ~107 ms, full reload বন্ধ |
| PWA | Workbox SW, per-store manifest (`PwaManifest` মডিউল), offline page, install banner | installable ✅ |
| Mobile feel | bottom nav, skeleton, touch polish, mobile table CSS | browser-verified |
| Vue islands | cart drawer + variant selector, island registry, kill-switch | `claude15-final-test.md`: 8/12 PASS, 0 console error |
| Final | regression + merge to `master` | tags `day4-complete` + `v1.0-spa-pwa` |

**Branch:** `master` (`6b54e34`) · **Scope rule:** `app/ routes/ database/ vendor/ packages/workdo/` নিষিদ্ধ (নতুন মডিউল ছাড়া) · প্রতিটি ফিচারে `.env` kill-switch বাধ্যতামূলক · `git push` শুধু ব্যবহারকারী বললে।

## 2. বাকি (Phase-2, অগ্রাধিকার ক্রমে)

| # | কাজ | কেন বাকি | মাপ |
|---|---|---|---|
| P1 | **Admin SPA** — 70 পেজে Turbo (DataTable/select2 re-init সহ) | Day 2-তে ইচ্ছা করে বাদ (`TURBO_ADMIN_ENABLED` flag রেডি) | ~1 সপ্তাহ |
| P2 | **Production config** — `APP_DEBUG=false`, OPcache, Redis verify | Sandbox-এ debug on ছিল; TTFB ~775 ms-এর মূল কারণ | 1–2 ঘণ্টা, কোডে হাত নয় |
| P3 | **FraudPixelShield মডিউল** — BD Courier rotation (legal, নিজেদের টোকেন), threshold, WhatsApp redirect, admin view + notification | নতুন কাজ | 3–4 দিন |
| P4 | **Pixel Block** — status dropdown অপশন, CAPI dispatch on confirm | নতুন কাজ, store credential settings লাগবে | 2–3 দিন |
| P5 | **Admin reskin + storefront preset theme** | ডিজাইন বাইরে চলছে (v0), port করা বাকি | Phase-2b |

## 3. Parallel Lane পরিকল্পনা (একসাথে চলবে)

| Lane | কাজ | মডেল | Allowed files (এর বাইরে হাত নয়) | Merge ক্রম |
|---|---|---|---|---|
| A | P2 production config + TTFB পুনঃমাপ | Muse Spark | `.env`, server config, `.ai/reports/` | 1st |
| B | P1 pilot (dashboard, product, order) + recipe + browser যাচাই | Muse Spark | `resources/js/admin.js`, 3 blade, `turbo-cleanup.js` | 2nd |
| C | P3 engine (rotation PHP port + threshold + contract), আলাদা branch `feat/fraud-shield` | DeepSeek V4.1 / GLM 5.3 | `packages/workdo/FraudPixelShield/` শুধু | 3rd |
| D | P4 design (CAPI payload + hook spec), একই branch, আলাদা subfolder | Gemini (spec) → Flash (code) | `.ai/reports/` + মডিউলের `Pixel/` subfolder | 4th |

**নিয়ম:** প্রতি lane-এর প্রম্পটে allowed-files সিল; লঙ্ঘন = reject। B-এর recipe ছাড়া admin bulk শুরু নয়। Merge + final regression: Muse Spark।

## 4. যাচাইকারী মডেলের জন্য প্রশ্ন

1. Lane-বিভাজনে কোনো file-overlap আছে কি?
2. P1-এর ঝুঁকি (DataTable double-init) recipe-তে ঢাকা আছে কি?
3. P3-তে checkout latency (live API call) async+cache ছাড়া নিরাপদ কি?
4. P4-তে ভুল confirm-এর আর্থিক ঝুঁকির guard আছে কি?
5. Merge ক্রম A→B→C→D সঠিক কি?
