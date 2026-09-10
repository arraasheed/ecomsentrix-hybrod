# CLAUDE-12: PWA Service Worker Registration Debugging

**Date:** 2026-09-10  
**Task:** Day 3 Phase 4 — PWA final test & merge  
**Status:** IN PROGRESS — SW registration code executing, requires verification

---

## Issues Found & Fixed

### Issue 1: SW file not accessible (404)
**Symptom:** `Error while trying to fetch the script ('https://ecommercego.dev/sw.js'): A bad HTTP response code (404)`

**Root Cause:** Apache `.htaccess` was rewriting all requests to `/public/`, but SW file is directly in `public/sw.js`. Rewrite rule didn't account for `/sw.js`.

**Fix:** Added explicit route in `.htaccess:lines 28-30`
```apache
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^sw\.js$ public/sw.js [L,NC]
```

**Commit:** `7145951`

---

### Issue 2: HTML data attributes missing
**Symptom:** `document.documentElement.dataset.pwaEnabled` was `undefined` in layout

**Root Cause:** Storefront layout (`themes/stylique/views/front_end/layouts/app.blade.php`) didn't set `data-pwa-enabled` and `data-store-id` on `<html>` tag.

**Fix:** Added attributes to `<html>` tag:
```blade
<html ... data-pwa-enabled="{{ $store->enable_pwa_store == 'on' ? 'true' : 'false' }}" data-store-id="{{ $store->id ?? '' }}">
```

**Commit:** `2047d29`

---

### Issue 3: Inline head script not executing with Turbo
**Symptom:** Inline `<script>` in `<head>` doesn't execute on Turbo navigations (Turbo only swaps `<body>`)

**Root Cause:** Turbo Drive bypasses `<head>` on subsequent navigations, so head scripts only run on first page load.

**Fix:** Moved SW registration to `turbo:load` event in `turbo-bootstrap.js` so it runs on every page (first load + all Turbo navigations).

**Code added:** Lines 344-361 in `resources/js/turbo-bootstrap.js`

**Commit:** `9cc393e`

---

## Current Status

### ✅ Confirmed Working
- `/sw.js` file is **accessible and valid** (200 response, 3924 bytes)
- `data-pwa-enabled="true"` **present** on HTML tag
- `data-store-id="2"` **present** on HTML tag
- `turbo:load` event **fires** on page loads
- Console logs **"[PWA] Service Worker registered via turbo:load"**
- Manual registration test: `navigator.serviceWorker.register('/sw.js')` returns **`{registered: true, scope: "https://ecommercego.dev/"}`**

### ⚠️ Needs Verification
- Persistent registration (remains after page reload)
- SW controller activation
- Cache functioning
- Offline page serving
- Install prompt display
- Fresh data updates (prices, stock, new products)

### 🔍 Anomaly
- `navigator.serviceWorker.getRegistrations()` returns `[]` even though manual registration succeeds
- Likely timing/DevTools artifact; manual subsequent registrations show success in console

---

## Next Steps

1. **Full browser test:** Open site in real Chrome (not DevTools headless mode) to verify registration persists
2. **Install banner test:** Check if install prompt appears on mobile viewport
3. **Offline test:** Navigate offline, verify offline page serves
4. **Cache test:** Modify admin product price, verify storefront shows cached old price initially, then new price after SW update
5. **Security test:** Login as User A, add to cart, logout, login as User B → verify User B doesn't see User A's cart
6. **Manifest audit:** Verify icon dimensions match manifest (currently getting warning)

---

## Technical Notes

- Kill-switch `PWA_ENABLED=` in `.env` controls SW registration (unregisters if false)
- Manifest served at route `/{store-slug}/manifest.json` (CLAUDE-11 PwaManifest module)
- Workbox strategies implemented in `public/sw.js`:
  - **NetworkFirst:** HTML pages, API calls (3s timeout → fallback to cache)
  - **CacheFirst:** JS/CSS with hash (versioned URLs safe to cache forever)
  - **StaleWhileRevalidate:** Product images (quick load + background refresh)
  - **NetworkOnly:** Login, checkout, account, cart (never cached)

---

## Files Modified

| File | Change | Commit |
|------|--------|--------|
| `.htaccess` | Add `/sw.js` rewrite rule | `7145951` |
| `themes/stylique/views/front_end/layouts/app.blade.php` | Add data attributes to `<html>` | `2047d29` |
| `resources/js/turbo-bootstrap.js` | Add SW registration to `turbo:load` | `9cc393e` |

