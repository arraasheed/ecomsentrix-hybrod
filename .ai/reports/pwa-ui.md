# BULK-5: Offline Page + PWA Install Prompt UI

**Date:** 2026-09-10
**Task:** Day 3 Phase 4 — Offline page + install banner UI
**Branch:** `day3/pwa-workbox`
**Commits:** pilot `19674c9` (`wip(pwa): pilot 2 files [BULK-5]`) → final (`feat(pwa): add offline page and install prompt UI [BULK-5]`)

---

## ✅ Created Files (2)

| # | Path | What it is |
|---|---|---|
| 1 | `resources/views/front_end/offline.blade.php` | Standalone offline page (no layout extend, all CSS inline, all images inline SVG, vanilla JS only) |
| 2 | `resources/views/front_end/partison/pwa-install.blade.php` | PWA install prompt — bottom-sheet on mobile, corner card on desktop (`partison` folder created new) |

No existing file was modified. The old raw install prompt in `jQueryRoute.blade.php:199-260` was **not touched** — Claude will remove it.

---

## Honest Numbers (Rule 5)

- **পরিবর্তন করেছি:** 2 ফাইল (দুইটিই নতুন তৈরি)
- **স্পর্শ করিনি:** সব বাকি ফাইল — টাস্ক-নির্দেশনায় শুধু এই ২টি নতুন ফাইলের অনুমতি ছিল; `jQueryRoute.blade.php`, কোনো layout, `serviceWorker.js`/`public/sw.js`, routes — কিছুই ছোঁয়া হয়নি
- **সন্দেহ — Claude দেখবে:** 0

---

## File 1 — `offline.blade.php`

**Requirements met:**
- ✅ Standalone HTML (no `@extends`), usable even when the layout cannot load
- ✅ All CSS inside one inline `<style>`; no external file / framework classes
- ✅ All images inline SVG (wifi-off icon, retry-clock icon) — no emoji, no external image
- ✅ Vanilla JS only (no libraries)
- ✅ Mobile-first with `viewport` meta (`width=device-width, initial-scale=1, viewport-fit=cover`)
- ✅ Dark mode via `prefers-color-scheme` (CSS custom properties)
- ✅ Bangla + English bilingual text (`.bn` / `.en` spans)
- ✅ "আবার চেষ্টা করুন / Retry" button → `location.reload()`
- ✅ Auto-reload on reconnect: `window.addEventListener('online', () => location.reload())`, plus an "Offline / অফলাইন" status pill
- ✅ Contains **no Blade variables** → renderable to static `public/offline.html` at build time with no `$store` dependency

**Kill-switch:** none needed — passive static page; the SW decides when to serve it (offline fallback in `public/sw.js`).

---

## File 2 — `partison/pwa-install.blade.php`

**Requirements met:**
- ✅ Bottom-sheet on mobile (`position:fixed; bottom:0; transform:translateY(110%)` → `translateY(0)` on `.pwa-install--show`; CSS transition only, no JS animation)
- ✅ Desktop (≥768px): small card fixed bottom-right
- ✅ All CSS inside `<style>`, scoped with `pwa-` prefix
- ✅ Vanilla JS only (no jQuery) — `var`/`function` style
- ✅ Holds `beforeinstallprompt` (`event.preventDefault()` + stored `deferredPrompt`), Install button calls `prompt()` and handles `userChoice` (accepted → hide + remember; declined → hide only)
- ✅ "পরে / Later" → `localStorage['pwa-dismissed-until']` = `Date.now() + 7*24*60*60*1000`; `isDismissed()` blocks until timestamp passes
- ✅ Hidden when installed: `window.matchMedia('(display-mode: standalone)').matches` (+ `fullscreen` + `navigator.standalone` for iOS)
- ✅ iOS Safari (no `beforeinstallprompt`): `/iphone|ipad|ipod/i.test(navigator.userAgent)` → hides Install button after 3s, shows numbered manual steps (Share বোতাম → Add to Home Screen)
- ✅ `safe-area-inset-bottom` respected: `--pwa-safe-bottom: env(safe-area-inset-bottom, 0px)` in sheet padding
- ✅ Bangla + English bilingual
- ✅ Store name: exactly `{{ $store->name ?? config('app.name') }}` (recipe expression; safe if `$store` missing)
- ✅ Store logo — **note:** the `stores` table has **no `logo` column** (verified by search + seed data). Fallback chain: `$store->logo ?? $themeSettings['header_logo']` → `get_file()` (guarded with `function_exists`), else inline SVG phone-icon fallback. All accesses inside `isset()` / `??`
- ✅ Kill-switch: whole partial wrapped in `@if (isset($store->enable_pwa_store) && $store->enable_pwa_store == 'on') @endif` — preserves the per-store PWA feature required by CLAUDE-10
- ✅ `appinstalled` → hide + remember (never re-offers after install)

**Validation:** both files compile through `Illuminate\View\Compilers\BladeCompiler` and pass `php -l` (`No syntax errors detected`, exit 0). Temporary check script deleted after use.

---

## ⚠️ Working-tree notes — commits around BULK-5 (read before proceeding)

- `be4bab9` (`docs(progress): record BULK-5 commit hash [BULK-5]`) was created with `git add -A`
  while **two pre-existing uncommitted changes from CLAUDE-11's session** were still in the working
  tree. That commit therefore also absorbed:
  1. `app/Providers/PackageServiceProvider.php` — adds `'PwaManifest'` to the static addons list and
     guards it in `loadDynamicAddons()`. **This is the module registration CLAUDE-11 needed for the
     `PwaManifest` module to load**; it was not part of commit `5dc29c2` and must stay committed.
  2. `.ai/scratch/test_loader.php` — a temporary provider-loader test file, accidentally swept in;
     it has since been removed by the concurrent commit `98c65c2`. Nothing to do.
- `9e624cc` (`fix(pwa): include sw-register.js in vite input`) and `98c65c2` were created
  concurrently by another agent while BULK-5 was finishing. They do not touch BULK-5 files.
- BULK-5's own files are byte-identical to commit `e382284` (`git diff e382284 HEAD -- <blade files>`
  is empty); the concurrent commits did not modify them.

---

## 👉 For Claude — what to do next

1. **Include the install prompt** in the storefront layout (CLAUDE-11 step 5): `@include('front_end.partison.pwa-install')` at the end of the body in the live storefront layout (`themes/stylique/views/front_end/layouts/app.blade.php`, and/or `resources/views/front_end/layouts/app.blade.php`). The partial is self-contained (style + markup + script) and guarded by `enable_pwa_store`.
2. **Remove the old raw install prompt** from `resources/views/front_end/jQueryRoute.blade.php:199-260` (with its `@if ($store->enable_pwa_store == 'on')` wrapper). Keep the `onTurboOnce` pattern elsewhere untouched.
3. **Offline page route** (CLAUDE-10 step 6): no route needed. Render `resources/views/front_end/offline.blade.php` → `public/offline.html` at build time (artisan command or vite plugin) and precache it in `public/sw.js` — the SW already falls back to `/offline.html`.
4. **Testing note:** both demo stores currently have `enable_pwa_store = 'off'` (seed data), so the banner will not render and the SW stays unregistered until a store enables PWA.

## Expected Browser Behavior

| Device / Browser | Behavior |
|---|---|
| Chrome / Edge / Android | `beforeinstallprompt` fires → bottom-sheet slides up once installable; Install triggers the native prompt |
| Desktop (≥768px) | Small bottom-right card instead of bottom-sheet |
| iOS Safari (iPhone/iPad) | No `beforeinstallprompt` → after 3s, sheet shows manual Share → Add to Home Screen steps |
| Installed PWA (standalone) | Banner never shows |
| After "পরে" press | Banner hidden for 7 days (`pwa-dismissed-until` in localStorage) |
| Offline (any device) | SW serves `offline.html`; Retry reloads; page auto-reloads when back online |