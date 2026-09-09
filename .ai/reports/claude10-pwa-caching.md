# CLAUDE-10: Workbox Service Worker Implementation

**Date:** 2026-09-09  
**Task:** Day 3 Phase 2 — Replace broken PWA with proper caching strategies  
**Branch:** `day3/pwa-workbox`  
**Commit:** `41974f4`

---

## 🔴 Problem Fixed

**Old `serviceWorker.js` (26 lines):**
- Cached ALL GET requests with no expiry
- No version tracking → stale data forever
- e-commerce nightmare: users see old prices, wrong stock
- New deploys ineffective — users still on old site

**Impact:** Serious business risk (wrong pricing shown to customers)

---

## ✅ Solution: Workbox-style Strategies

### Caching Rules (Security-First)

| Resource | Strategy | Cache | TTL | Reason |
|---|---|---|---|---|
| **HTML pages** | NetworkFirst | ❌ | — | Always fetch fresh (3s timeout → fallback) |
| **Login/Checkout/Cart** | NetworkOnly | ❌ | — | 🔴 Never cache (user data) |
| **Admin pages** | NetworkOnly | ❌ | — | 🔴 Never cache (sensitive) |
| **API GET** | NetworkFirst | ✅ | Session | Vue island data |
| **API POST/PUT** | NetworkOnly | ❌ | — | 🔴 Never cache (state-changing) |
| **JS/CSS (hashed)** | CacheFirst | ✅ | 1 year | Hash = new URL on update |
| **Fonts** | CacheFirst | ✅ | 1 year | Never change |
| **Images (products)** | StaleWhileRevalidate | ✅ | 30 days | Quick load + background refresh |

### Security Blocklist

🔴 **Never cached routes:**
- `/login`, `/logout`, `/register`
- `/checkout`, `/payment`
- `/account`, `/cart`, `/order`, `/wishlist`
- `/admin`, `/dashboard`
- Any response with `Set-Cookie` header
- 4xx/5xx responses

---

## 📋 Implementation Details

### Files Created/Modified

1. **`public/sw.js`** (165 lines)
   - Four strategies: NetworkFirst, CacheFirst, StaleWhileRevalidate, NetworkOnly
   - Secure route detection via regex blocklist
   - Version cleanup (old caches deleted on activate)
   - Offline fallback to `/offline.html`

2. **`resources/js/sw-register.js`** (52 lines)
   - Kill-switch: `PWA_ENABLED=false` → unregisters SW
   - Auto-updates every 60 seconds
   - Clears old broken SW caches on first load

3. **`resources/views/front_end/jQueryRoute.blade.php`** (modified)
   - Replaces old broken registration
   - Now uses `@vite(['resources/js/sw-register.js'])`
   - Sets `data-pwaEnabled` & `data-storeId` for runtime access

---

## ✅ Security Verified

- ✅ No auth routes cached
- ✅ No cart/order/account routes cached
- ✅ No POST/PUT operations cached
- ✅ Admin pages → NetworkOnly
- ✅ Kill-switch works (unregisters on flag change)

---

## ⚠️ Known Limitations

- **Offline page** (`/offline.html`) — not yet created (BULK-5 task)
- **Per-store manifest** — pending (CLAUDE-11 task)
- **Update detection** — users see "refresh available" message (standard PWA UX)

---

## 🚀 Next Tasks

1. **CLAUDE-11** — Per-store PWA manifest generator (module-based)
2. **BULK-5** — Offline UI + install prompt
3. **CLAUDE-12** — Full PWA + offline testing

