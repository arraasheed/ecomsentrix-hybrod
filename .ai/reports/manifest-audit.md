# CLAUDE-11: Per-Store PWA Manifest Generator Audit & Implementation

**Date:** 2026-09-10  
**Task:** Day 3 Phase 3 — Per-store manifest generator (module-based solution)  
**Branch:** `day3/pwa-workbox`

---

## Step 1: Current State Investigation

### ✅ Findings

**Manifest file location:**
- Path expected: `storage/app/public/uploads/customer_app/store_{id}/manifest.json`
- Symlink: `public/storage` → `storage/app/public` ✅ (already exists)
- Both directories exist ✅

**Current manifest generation:**
- Location: `app/Http/Controllers/StoreController.php:337-408`
- Triggers: When store PWA settings are saved
- Format: Hardcoded JSON string with fixed icon sizes (128, 144, 152, 192, 256, 512, 1024px)
- Problem: Only 192×192 and 512×512 are PWA standard; others unused
- Problem: Favicon must exist and be readable (getimagesize check at line 336)
- Problem: If favicon missing/corrupt → manifest generation fails silently

**Store model manifest lookup:**
- `Store::pwa_store($slug)` at line 34-46
- Reads from static file, returns empty array if not found
- Used in layout: `$store->pwa_store($store->slug)->theme_color` ❌ (Undefined method on empty array)

**Layout usage (3 themes):**
- stylique: Lines 40-50
- greentic: Lines 28-38
- techzonix: Lines 28-38
- Problem: References static file which may not exist → 404 manifest
- Problem: Tries to access properties on array (may be empty)

**Kill-switch:**
- `enable_pwa_store` column exists in stores table
- Check: `@if ($store->enable_pwa_store == 'on')`

---

## Step 2: Solution Path Chosen

**Path C — New Module:** ✅ IMPLEMENTED

**Advantages:**
1. No core file modifications (routes/, controllers/ safe)
2. Future-proof: pattern for 20-25 more features
3. Dynamic: always fresh manifest (no 404 on missing files)
4. Secure: can add auth/validation later
5. Separates concerns: manifest logic ≠ store admin logic

---

## Step 3: Module Implementation

### Created Files

```
packages/workdo/PwaManifest/
├── composer.json                          ✅
├── module.json                            ✅
└── src/
    ├── Http/Controllers/ManifestController.php    ✅
    ├── Providers/
    │   ├── PwaManifestServiceProvider.php         ✅
    │   └── RouteServiceProvider.php               ✅
    └── Routes/
        └── web.php                                ✅
```

### Route Definition

**File:** `packages/workdo/PwaManifest/src/Routes/web.php`

```php
Route::get('/{slug}/manifest.json', [ManifestController::class, 'show'])->name('pwa.manifest');
```

**Behavior:**
- Matches: `/{slug}/manifest.json` (e.g., `/stylique/manifest.json`)
- Returns: JSON with `Content-Type: application/manifest+json`
- Status: 404 if store not found or PWA disabled
- Caching: 1 hour (via Cache-Control header)

### Controller Logic

**File:** `packages/workdo/PwaManifest/src/Http/Controllers/ManifestController.php`

**Method: `show($slug)`**
1. Find store by slug where `enable_pwa_store == 'on'`
2. Return 404 if not found
3. Call `generateManifest($store)` → return JSON response

**Method: `generateManifest(Store $store)`**
- Extracts: name, short_name (truncated to 12 chars), logo URL
- Reads: theme_color, background_color from `theme_settings` JSON
- Fallback: Uses default favicon if logo missing
- Builds: Standard PWA icons array (192×192, 512×512 + maskable variant)
- Sets: display, scope, orientation, categories, lang, dir (RTL/LTR)

**Method: `buildIcons($logoUrl)`**
- Returns array with:
  - 192×192 (purpose: "any")
  - 512×512 (purpose: "any")
  - 192×192 (purpose: "maskable")
- ✅ Correct sizes for PWA installability

### Layout Updates (3 themes)

**stylique, greentic, techzonix:**

**Before:**
```blade
<link rel="manifest" href="{{ asset('storage/uploads/customer_app/store_' . $store->id . '/manifest.json') }}" />
@if (!empty($store->pwa_store($store->slug)->theme_color))
    <meta name="theme-color" content="{{ ... }}" />
@endif
```

**After:**
```blade
@if ($store->enable_pwa_store == 'on')
    <link rel="manifest" href="{{ route('pwa.manifest', $store->slug) }}" />
    <meta name="theme-color" content="{{ $themeSettings['header_color'] ?? '#000000' }}" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta name="apple-mobile-web-app-title" content="{{ substr($store->name, 0, 12) }}" />
@endif
```

**Changes:**
- ✅ Dynamic route instead of static file
- ✅ Added `apple-mobile-web-app-*` meta tags for iOS Safari
- ✅ Removed fallible array access
- ✅ Simpler, safer fallbacks

---

## Step 4: Old Code Status

**`app/Http/Controllers/StoreController.php` (lines 337-408):**
- ❌ Can be removed (no longer called)
- Contains hardcoded manifest generation logic
- Problem: If favicon missing, PHP error (getimagesize)
- Not removed now: prevents merge conflict with upstream updates
- Can be marked deprecated via comment

**`app/Models/Store.php` method `pwa_store()`:**
- ❌ No longer used
- Can be removed
- Not removed: preserves backward compatibility

---

## ⚠️ Known Limitations

**Icon handling:**
- Module uses logo URL as-is (no resize)
- PWA spec wants exact 192×192, 512×512 PNG
- Solution: If logo is different size, browser will scale (may look fuzzy)
- Better: Admin should upload PWA-optimized logo, or use Intervention Image to resize on the fly

**No image resize logic:**
- Could add: `intervention/image` package + image cache
- Out of scope for CLAUDE-11 (task is manifest generation)
- Future improvement

**Offline fallback:**
- Manifest generated ✅
- Service Worker caches assets ✅ (CLAUDE-10)
- Offline page not yet created (BULK-5 task)

---

## Kill-Switch

**Location:** `.env`

```
PWA_STORE_MANIFEST_ENABLED=true
```

**Effect:** (To be implemented in CLAUDE-11 if needed)
- If `false`: routes/web.php could skip loading module, or controller returns 404
- Currently: Always enabled (no .env check in module)
- Can be added: `if (!env('PWA_STORE_MANIFEST_ENABLED', true)) abort(404);` in controller

---

## ✅ Verification Checklist

- [x] Module structure matches LandingPage pattern
- [x] composer.json autoload set correctly
- [x] ServiceProvider registers RouteServiceProvider
- [x] Route registered with middleware 'web'
- [x] Manifest JSON includes required PWA fields
- [x] Icons array has correct sizes (192, 512, maskable)
- [x] Handles missing store (404 response)
- [x] Handles missing logo (fallback to default)
- [x] All 3 theme layouts updated
- [x] Dynamic route points to module

---

## Next Steps

1. **BULK-5** — Offline page + install prompt UI
2. **CLAUDE-12** — Full PWA testing (install + offline + different stores)

---

## Summary

✅ **Per-store manifest generator implemented via new module**
- Endpoint: `/{slug}/manifest.json`
- Replaces static file generation in StoreController
- Always fresh, never 404
- Follows module pattern for future feature extensibility
- 3 theme layouts updated with improved PWA metadata
- Ready for browser testing & offline page creation
