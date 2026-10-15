# Admin Nav Fix — ADMIN-NAV-FIX report

> **Executed by:** step-5-preview (this session, live-verified in Chrome via chrome-devtools MCP)
> **Date:** 2026-10-15 · **Task:** ADMIN-NAV-FIX · **Kill-switch round-trip verified:** yes

## Why

Admin panel menu-to-menu navigation still showed the skeleton loader on every first
visit, even after the Day 1–4 SPA work. Diagnosis (step-5-preview) + corrections from
Muse Spark's review, then executed in the agreed order:
**(0) .env guard → (1) redis drivers → (2) gzip → (3) OPcache → (4) sidebar prefetch.**

## What was done

| # | Fix | File(s) | Verification |
|---|---|---|---|
| 0 | `.env` guard — new script + warning header in `.env` | `.agents/scripts/env-guard.cjs` (new), `.env` | `env-guard.cjs` reports GUARD OK; `--fix`/`--file` round-trip both keep site 200 |
| 1 | Redis drivers: `CACHE_DRIVER`/`CACHE_STORE`/`SESSION_DRIVER` → `redis` (QUEUE stays `sync` — no worker runs; a redis queue with no worker would strand jobs) | `.env` (git-ignored, local) | redis reachable (PONG); drivers verified by guard script; site 200 |
| 2 | gzip — `mod_deflate` block | `.htaccess` | curl shows `Content-Encoding: gzip` on HTML and CSS (the earlier "no encoding" reading was a .NET Invoke-WebRequest auto-decompress artifact) |
| 3 | OPcache enabled (was fully commented out — every request recompiled ~1000 PHP files) | `C:\laragon\bin\php\php-8.3.32-Win32-vs16-x64\php.ini` (Laragon; outside repo) | web check: `opcache_enabled=true`, 1084 scripts cached; Apache restarted |
| 4 | Admin sidebar prefetch + submenu-leaf click fix | `resources/js/skeleton.js`, `public/assets/js/dash.js` | see below |

### 4a — Sidebar prefetch (skeleton.js)

`initSelectivePrefetch()` now also marks safe admin sidebar links
(`.dash-navbar a.dash-link[href]`) with `data-turbo-prefetch="true"`.
Safety: skips logout/delete/status/pos-style routes, modal-form links
(`data-ajax-popup`/`data-bs-toggle`), `onclick` links, external links, and
parameterised URLs. New kill-switch constant `PREFETCH_ENABLED` (set false =
no prefetch anywhere, skeleton overlay stays).

### 4b — Root-cause fix: submenu leaf clicks did a FULL page reload

Verification exposed a pre-existing bug: every sidebar leaf link click did a
full document reload — Turbo SPA navigation only worked for top-level links.
Root cause (traced live with per-level click listeners): the click on a leaf
inside a submenu bubbles through the **parent** `li.dash-hasmenu`, whose
`dash.js` handler calls `event.stopPropagation()` for accordion parents —
killing the event before Turbo's document-level click listener ever sees it.
The SIDEBAR-FIX commit (e3f284e) guarded the *leaf's own* handler but not the
*parent's* handler the event passes through.

Fix (`public/assets/js/dash.js`, 4 handlers): parents now only
`stopPropagation()` when the click's nearest `li` **is** the parent itself
(`event.target.closest('li') === this`) — i.e. the accordion toggle was
clicked — never when the click bubbled up from a leaf inside its submenu.

Worst case of this change = the previous behaviour (full reload), so no
app-level kill-switch is needed; `git revert` of the commit restores it.

## Measurements (local, warm, median of runs)

| Page | Before | After | Δ |
|---|---|---|---|
| `/login` TTFB | ~580 ms | **261 ms** | −55% |
| `/stylique/home` TTFB | ~835 ms | **458 ms** | −45% |

Driver attribution (measured step-by-step): Redis drivers alone ≈ no visible
TTFB change (cache hit ≈ file read); **OPcache is the dominant win**; gzip
shrinks transfer (login raw 30 KB → 3.7 KB CSS etc.), not TTFB.

## Browser verification (chrome-devtools MCP, admin@example.com)

- Login → dashboard → Products submenu → Brand leaf: **Turbo SPA nav**
  (`__turboBootstrap.stats.pageId` 1→2, no new navigation entry, title swapped)
- Hover on sidebar link fires prefetch (`x-sec-purpose:prefetch` header seen
  on the fetch)
- Prefetch cache behaviour (read from `node_modules/@hotwired/turbo` source):
  `size = 1`, **TTL 10 s** (`cacheTtl`), cleared after every visit. A human
  hover→click (0.3–2 s) hits the window and renders from cache; tool-driven
  tests with ~15 s round-trips miss it and refetch (~380 ms, still far better
  than the old full reload + 500–800 ms TTFB).
- 0 console errors on dashboard/product/order/customer/deliveryboy navigations
- Storefront regression: home + product-list 200, Turbo nav works, 0 errors
- Kill-switch round-trip: `env-guard.cjs --file` → 200, `--fix` → 200

## Known caveats

1. **Redis is not a Windows service.** Started manually
   (`redis-x64-5.0.14.1`); it will NOT survive a PC restart. If it is down,
   `env-guard.cjs` reports DANGER and the kill-switch is
   `node .agents/scripts/env-guard.cjs --file`. Recommend registering it in
   Laragon's auto-start (one-time system task, needs the user).
2. **OPcache restart required** after any php.ini change (Apache was restarted
   manually: `taskkill httpd` + relaunch; `laragon reload apache` hung).
   `validate_timestamps` left at default 1 (dev-friendly).
3. Prefetch is GET-only speculation; stateful admin routes are excluded by
   design (see 4a).
4. `side-dash.js` (only loaded when the SidebarCustomization module is active —
   it is not) has the same parent-stopPropagation pattern; the 4b fix was NOT
   applied there. If that module is ever enabled, port the fix.

## Analysis-loop note (Muse Spark review)

Muse Spark's review was mostly correct and improved the plan: the .env
recurrence/guard gap (accepted — hence fix 0), the file-driver-makes-caching-
worthless point (accepted). Two claims were not supported by evidence:
"someone reverted my .env redis change" (git shows `.env` was never committed
and CLAUDE-9's commit 868433c only edited `.env.example` — the live `.env` was
never set to redis), and "getAdminAllSetting is cached" (the `Cache::remember`
calls only wrap user-row lookups that the layout's no-arg call path never
triggers; the settings query itself runs per render — though with OPcache+redis
this is now a minor cost, not a 500 ms one).
