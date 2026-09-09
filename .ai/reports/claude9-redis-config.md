# CLAUDE-9: Redis + OPcache Backend Tuning

**Date:** 2026-09-09  
**Task:** Day 3 Phase 1 — Redis cache driver, configuration hardening  
**Branch:** `day3/pwa-workbox`  
**Commit:** `868433c`

---

## ✅ Completed

### 1. Redis Configuration
- ✅ Added `predis/predis: ^2.2` to `composer.json` (pure PHP Redis client, no C extension needed)
- ✅ Updated `.env`:
  - `CACHE_DRIVER=redis`
  - `CACHE_STORE=redis`
  - `SESSION_DRIVER=redis`
  - `QUEUE_CONNECTION=redis`
  - `REDIS_CLIENT=phpredis` (fallback to Predis if not available)
- ✅ Updated `.env.example` with same config
- ✅ Committed all changes

### 2. Next: Configuration for `config:cache`

**⚠️ Important:** Before running `php artisan config:cache` in production, we need to move environment-dependent kill-switches from `env()` calls to `config()` array, because `config:cache` freezes all `env()` reads.

**Current kill-switches using `env()` in blade:**
- `env('TURBO_ENABLED', true)` — multiple views
- `env('LEGACY_TAILWIND_CDN', false)` — layout
- `env('LEGACY_ADMIN_CSS', false)` — layout
- `env('LEGACY_ASSET_TIME_BUSTER', false)` — helper

**Recommendation for next session:**
1. Create `config/app.php` section:
   ```php
   'spa' => [
       'turbo_enabled'       => env('TURBO_ENABLED', true),
       'legacy_tailwind_cdn' => env('LEGACY_TAILWIND_CDN', false),
       'legacy_admin_css'    => env('LEGACY_ADMIN_CSS', false),
       'legacy_asset_time_buster' => env('LEGACY_ASSET_TIME_BUSTER', false),
       'pwa_enabled'         => env('PWA_ENABLED', true),
   ]
   ```
2. Replace all blade `env()` calls with `config('app.spa.*')`
3. Then `config:cache` is safe

### 3. OPcache Status

PHP OPcache is **system-level configuration**, not per-project. Current status:
- Located: `C:\laragon\bin\php\php-8.3.32-nts-Win32-msvc17-x64\php.ini`
- Status: **Unknown** (cannot modify without admin access)

**User action needed:** If you want OPcache enabled, ask Laragon to enable it in php.ini:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0
opcache.jit=tracing
opcache.jit_buffer_size=256M
```

---

## 📊 Performance Impact (Expected)

| Metric | Before | After | Reason |
|---|---|---|---|
| TTFB | ~765 ms | ~400-500 ms | Redis vs file I/O |
| Cache read | ~5-15 ms | <1 ms | Memory vs disk |
| Session lookup | 10+ ms | <1 ms | Memory-backed |
| store settings (per request) | multiple file reads | 1 Redis call | Model caching layer |

*Actual gains depend on Redis availability and OPcache status.*

---

## 🚀 Ready for Production?

✅ **Predis installed** — fallback PHP client, no extension required  
⚠️ **env() → config()** refactor — pending (blocking `config:cache`)  
⚠️ **OPcache** — user must configure php.ini  
⚠️ **Laravel cache commands** — safe to run after env→config refactor

---

## Next Task: CLAUDE-10

**Workbox service worker** — replace broken `serviceWorker.js` with proper caching strategy:
- HTML: NetworkFirst (always fetch fresh, fallback offline page)
- Assets: CacheFirst (use cached, hash in URL ensures updates)
- Per-store manifest (3+ stores, different names/icons)

