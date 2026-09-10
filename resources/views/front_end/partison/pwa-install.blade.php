{{--
    BULK-5 — PWA install prompt (bottom-sheet on mobile, card on desktop).
    - Vanilla JS only (no jQuery), scoped CSS under `pwa-` prefix
    - Kill-switch: whole partial is skipped unless `enable_pwa_store == 'on'`
    - Included in the storefront layout by Claude (CLAUDE-11 step 5)
--}}
@if (isset($store->enable_pwa_store) && $store->enable_pwa_store == 'on')
    @php
        // Store logo — no `logo` column on the stores table; fall back to the
        // theme header logo (same pattern as the storefront header partial).
        // Every access sits inside ?? / isset() so an undefined variable never breaks.
        $pwaStoreLogo = $store->logo ?? (isset($themeSettings['header_logo']) ? $themeSettings['header_logo'] : '');
        if ($pwaStoreLogo && function_exists('get_file')) {
            $pwaStoreLogo = get_file($pwaStoreLogo);
        }
    @endphp

    <style>
        .pwa-install {
            --pwa-accent: #2563eb;
            --pwa-accent-contrast: #ffffff;
            --pwa-bg: #ffffff;
            --pwa-text: #111827;
            --pwa-muted: #6b7280;
            --pwa-border: #e5e7eb;
            --pwa-radius: 16px;
            --pwa-safe-bottom: env(safe-area-inset-bottom, 0px);
            position: fixed;
            z-index: 99999;
            left: 0;
            right: 0;
            bottom: 0;
            visibility: hidden;
            transform: translateY(110%);
            transition: transform .4s ease, visibility .4s;
        }

        @media (prefers-color-scheme: dark) {
            .pwa-install {
                --pwa-accent: #3b82f6;
                --pwa-accent-contrast: #ffffff;
                --pwa-bg: #1f2937;
                --pwa-text: #f9fafb;
                --pwa-muted: #9ca3af;
                --pwa-border: #374151;
            }
        }

        .pwa-install.pwa-install--show {
            visibility: visible;
            transform: translateY(0);
        }

        .pwa-install__sheet {
            background: var(--pwa-bg);
            color: var(--pwa-text);
            border-top: 1px solid var(--pwa-border);
            border-radius: var(--pwa-radius) var(--pwa-radius) 0 0;
            padding: 18px 20px calc(18px + var(--pwa-safe-bottom));
            box-shadow: 0 -6px 24px rgba(0, 0, 0, .12);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Noto Sans Bengali", "Hind Siliguri", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        @media (min-width: 768px) {
            .pwa-install {
                left: auto;
                right: 20px;
                bottom: 20px;
                width: 340px;
                transform: translateY(12px) scale(.97);
                opacity: 0;
                transition: transform .3s ease, opacity .3s ease, visibility .3s;
            }

            .pwa-install.pwa-install--show {
                transform: translateY(0) scale(1);
                opacity: 1;
            }

            .pwa-install__sheet {
                border: 1px solid var(--pwa-border);
                border-radius: var(--pwa-radius);
                box-shadow: 0 12px 32px rgba(0, 0, 0, .16);
            }
        }
        .pwa-install__brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .pwa-install__logo {
            width: 48px;
            height: 48px;
            flex: none;
            border-radius: 10px;
            object-fit: contain;
            border: 1px solid var(--pwa-border);
            background: var(--pwa-bg);
            padding: 4px;
        }

        .pwa-install__logo--fallback {
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--pwa-accent);
            color: var(--pwa-accent-contrast);
            border: 0;
        }

        .pwa-install__logo--fallback svg { width: 28px; height: 28px; }

        .pwa-install__meta { min-width: 0; }

        .pwa-install__store {
            display: block;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .pwa-install__tagline { margin-top: 2px; font-size: 13px; }
        .pwa-install__tagline .pwa-install__bn { display: inline; }
        .pwa-install__tagline .pwa-install__en { display: inline; color: var(--pwa-muted); font-size: .85em; margin-top: 0; }
        .pwa-install__tagline .pwa-install__bn::after { content: " · "; }

        .pwa-install__text { margin-top: 14px; font-size: 14px; line-height: 1.7; }
        .pwa-install__en { display: block; color: var(--pwa-muted); font-size: .9em; margin-top: 2px; line-height: 1.5; }

        .pwa-install__ios {
            margin-top: 14px;
            padding: 12px 14px;
            background: rgba(37, 99, 235, .08);
            border: 1px solid rgba(37, 99, 235, .25);
            border-radius: 10px;
            font-size: 13px;
            line-height: 1.6;
        }

        .pwa-install__ios-title { font-weight: 700; margin-bottom: 6px; }
        .pwa-install__ios-title .pwa-install__bn { margin-right: 4px; }
        .pwa-install__ios-title .pwa-install__en { display: inline; color: var(--pwa-muted); font-size: .9em; }

        .pwa-install__steps { list-style: none; counter-reset: pwa-step; }

        .pwa-install__steps li {
            counter-increment: pwa-step;
            position: relative;
            padding-left: 28px;
            margin: 8px 0;
        }

        .pwa-install__steps li::before {
            content: counter(pwa-step);
            position: absolute;
            left: 0;
            top: 1px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--pwa-accent);
            color: var(--pwa-accent-contrast);
            font-size: 11px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pwa-install__steps .pwa-install__en { margin-top: 0; }

        .pwa-install__actions { display: flex; gap: 10px; margin-top: 16px; }

        .pwa-install__btn {
            flex: 1;
            appearance: none;
            border: 1px solid transparent;
            border-radius: 10px;
            padding: 11px 12px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            line-height: 1.2;
            transition: background-color .15s ease, border-color .15s ease;
        }

        .pwa-install__btn[hidden] { display: none; }

        .pwa-install__btn .pwa-install__en { font-size: .8em; font-weight: 500; opacity: .8; margin-top: 1px; }

        .pwa-install__btn--primary { background: var(--pwa-accent); color: var(--pwa-accent-contrast); }
        .pwa-install__btn--primary:hover { filter: brightness(1.06); }

        .pwa-install__btn--ghost { background: transparent; color: var(--pwa-text); border-color: var(--pwa-border); }
        .pwa-install__btn--ghost:hover { background: rgba(128, 128, 128, .08); }
    </style>

    <div class="pwa-install" id="pwa-install" role="dialog" aria-live="polite" aria-label="Install app">
        <div class="pwa-install__sheet">
            <div class="pwa-install__brand">
                @if ($pwaStoreLogo)
                    <img class="pwa-install__logo" src="{{ $pwaStoreLogo }}" alt="{{ $store->name ?? config('app.name') }}" width="48" height="48">
                @else
                    <span class="pwa-install__logo pwa-install__logo--fallback" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="6" y="2.5" width="12" height="19" rx="2.5"/>
                            <path d="M12 7v6"/>
                            <path d="M9.5 10.5l2.5 2.5 2.5-2.5"/>
                            <path d="M10 18.5h4"/>
                        </svg>
                    </span>
                @endif
                <div class="pwa-install__meta">
                    <strong class="pwa-install__store">{{ $store->name ?? config('app.name') }}</strong>
                    <span class="pwa-install__tagline"><span class="pwa-install__bn">অ্যাপ ইনস্টল করুন</span><span class="pwa-install__en">Install the app</span></span>
                </div>
            </div>

            <p class="pwa-install__text">
                <span class="pwa-install__bn">হোম স্ক্রিনে যুক্ত করুন — দ্রুত খুলুন, অফলাইনেও চালান।</span>
                <span class="pwa-install__en">Add <strong>{{ $store->name ?? config('app.name') }}</strong> to your home screen for quick access, even offline.</span>
            </p>

            {{-- iOS Safari only: beforeinstallprompt never fires there — show manual steps --}}
            <div class="pwa-install__ios" id="pwa-install-ios" hidden>
                <p class="pwa-install__ios-title"><span class="pwa-install__bn">ম্যানুয়ালি যোগ করুন</span><span class="pwa-install__en">Add manually</span></p>
                <ol class="pwa-install__steps">
                    <li><span class="pwa-install__bn">Safari-র Share বোতামে চাপ দিন।</span><span class="pwa-install__en">Tap the Share button in Safari.</span></li>
                    <li><span class="pwa-install__bn">&ldquo;Add to Home Screen&rdquo; নির্বাচন করুন।</span><span class="pwa-install__en">Choose &ldquo;Add to Home Screen&rdquo;.</span></li>
                    <li><span class="pwa-install__bn">উপরে ডানে &ldquo;Add&rdquo; চাপুন।</span><span class="pwa-install__en">Tap &ldquo;Add&rdquo; at the top right.</span></li>
                </ol>
            </div>

            <div class="pwa-install__actions">
                <button type="button" class="pwa-install__btn pwa-install__btn--primary" id="pwa-install-cta">
                    <span class="pwa-install__bn">ইনস্টল করুন</span>
                    <span class="pwa-install__en">Install</span>
                </button>
                <button type="button" class="pwa-install__btn pwa-install__btn--ghost" id="pwa-install-later">
                    <span class="pwa-install__bn">পরে</span>
                    <span class="pwa-install__en">Later</span>
                </button>
            </div>
        </div>
    </div>

<script>
    (function () {
        'use strict';

        var element = document.getElementById('pwa-install');
        if (!element) { return; }

        // Already running as an installed app → never show
        if (window.matchMedia('(display-mode: standalone)').matches ||
            window.matchMedia('(display-mode: fullscreen)').matches ||
            window.navigator.standalone === true) {
            return;
        }

        var DISMISS_KEY = 'pwa-dismissed-until';
        var DISMISS_MS = 7 * 24 * 60 * 60 * 1000; // 7 days

        // iOS Safari never fires `beforeinstallprompt` → manual instructions instead
        var isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);

        var cta = document.getElementById('pwa-install-cta');
        var later = document.getElementById('pwa-install-later');
        var iosHint = document.getElementById('pwa-install-ios');
        var deferredPrompt = null;

        function isDismissed() {
            try {
                var until = parseInt(localStorage.getItem(DISMISS_KEY), 10);
                return !!(until && until > Date.now());
            } catch (err) {
                return false; // localStorage unavailable → allow showing
            }
        }

        function show() {
            if (isDismissed()) { return; }
            if (isIOS) {
                if (cta) { cta.hidden = true; }
                if (iosHint) { iosHint.hidden = false; }
            }
            element.classList.add('pwa-install--show');
        }

        function hide() {
            element.classList.remove('pwa-install--show');
        }

        function hideAndRemember() {
            hide();
            try {
                localStorage.setItem(DISMISS_KEY, String(Date.now() + DISMISS_MS));
            } catch (err) { /* ignore — banner still hides for this page */ }
        }

        // iOS: no event to wait for — show the manual steps after a short delay
        if (isIOS) {
            window.setTimeout(show, 3000);
        }

        window.addEventListener('beforeinstallprompt', function (event) {
            event.preventDefault();
            deferredPrompt = event;
            show();
        });

        // Installed → remove the banner and remember so it is not offered again
        window.addEventListener('appinstalled', function () {
            hideAndRemember();
        });

        if (cta) {
            cta.addEventListener('click', function () {
                if (!deferredPrompt) { return; }
                var prompt = deferredPrompt;
                deferredPrompt = null;
                prompt.prompt();
                if (prompt.userChoice) {
                    prompt.userChoice.then(function (choice) {
                        if (choice && choice.outcome === 'accepted') {
                            hideAndRemember();
                        } else {
                            hide(); // declined the native prompt → just hide for now
                        }
                    });
                }
            });
        }

        if (later) {
            later.addEventListener('click', hideAndRemember);
        }
    })();
</script>
@endif