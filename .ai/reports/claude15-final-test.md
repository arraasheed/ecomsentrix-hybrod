# CLAUDE-15 Final Test — Vue Islands (CartDrawer + VariantSelector)

> Verified by Muse Spark (browser: Playwright Chromium, mobile-ish viewport).
> Store: stylique · URL: http://ecommercego.dev · Guest session.
> Baseline: `e01910d` → fixes on top → final commit (this task).

## Bugs found & fixed (on top of Claude's work)

1. **`CartDrawer.vue` scope bug (ReferenceError on unmount).**
   `onKeydown`/`onOverlayClick` were `const` inside `onMounted` but referenced
   in `onUnmounted` → throw on every unmount. Moved to setup scope.
2. **Remove used the wrong endpoint.** `changeQty(remove)` posted to
   `change-cart`, whose guest path (`Cart::cart_qty_cookie`) ignores
   `quantity_type=remove` → UI emptied, server kept the item. Now posts to
   `cart-remove` (same as the jQuery flow). Added rollback (re-fetch) when a
   remove fails.
3. **Teleported drawer wiped by in-flight jQuery ajax (the big one).**
   jQuery's page-load `get_cartlist()` is already in flight when the island
   mounts; the `get_cartlist` no-op only stops NEW calls. When the response
   lands, `$('#cart-panel').html(response.html)` wipes Vue's teleported div
   and Vue never notices → drawer showed stale server HTML forever. Fix:
   `[data-vue-drawer]` marker + `claimPanel()` (purge foreign children +
   bump teleport `:key` to force re-insert), called in `onMounted` and
   `openDrawer()`. This also covers any future foreign write.
4. **Mount race (hardening).** `mount()` is triggered by DOMContentLoaded,
   turbo:load AND module evaluation; sweeps could interleave inside the
   awaited dynamic import. Sweeps are now serialised via a promise chain +
   post-await re-check. Also added an immediate `mount()` call: the registry
   is itself dynamically imported and could otherwise load after both events
   fired → islands silently never mounted (observed `instances: 0` on a fresh
   load before this fix).
5. **Currency parity.** Totals/item prices were raw numbers (`1500.00`, `—`)
   vs server-formatted (`$1,500,0`). Now scraped from the response `html`
   (`ins`/`del`, totals spans) — pixel parity without re-implementing
   `currency_format_with_sym` client-side.

## 12-point verification matrix

| # | Test | Result |
|---|---|---|
| 1 | add to cart → drawer opens with item | ✅ PASS (after fix 3) |
| 2 | qty + optimistic + server sync | ✅ PASS (UI 2, server qty 2 / sub 3000.00, 0 errors) |
| 3 | remove item | ✅ PASS (UI badge 0 + server total 0, after fix 2) |
| 4 | variant change → price/stock | ⚠️ PARTIAL — no variant fixtures in DB (all 3 test products have 0 variants); island mounts, intercepts form change, updates DOM, 0 errors (synthetic change test) |
| 5 | out-of-stock disables add | ⚠️ CODE-ONLY — endpoint never returns error shape without variant data; handler reviewed, kill-switch covers |
| 6 | badge sync | ✅ PASS |
| 7 | reload persistence | ✅ PASS (badge 2 → 2) |
| 8 | Turbo nav + back, single mount | ✅ PASS (instances 1→1→1, drawer works after back) |
| 9 | memory over 10 navs | ⚠️ LIGHT — 6 full loads, instances stable 1/2/1/2, heap 33 MB, 0 errors; CDP heap snapshot not run |
| 10 | kill-switch `SPA_VUE_ISLANDS=false` | ✅ PASS (flag false, 0 instances, full jQuery HTML drawer, then flag removed + view:clear) |
| 11 | offline error | ⚠️ CODE-ONLY — error template + catch + Retry verified in code; offline not simulated live |
| 12 | cart → checkout intact | ✅ PASS (checkout loads, `data-turbo=false` form present, 0 errors) |

Console errors across all tests: **0**. `.env` clean (flag removed after test 10).

## Residual risks (kill-switch covers all)

- Variant/OOS paths (4/5) need a variant fixture to prove live.
- Offline path (11) needs a live offline run.
- If any island misbehaves in production: `SPA_VUE_ISLANDS=false` + `php artisan view:clear` restores the jQuery flow untouched.
