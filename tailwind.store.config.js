import forms from '@tailwindcss/forms';

/**
 * Storefront-only Tailwind config, used by resources/css/store.css via @config.
 *
 * Why a separate file instead of extending tailwind.config.js:
 *
 * 1. The Play CDN config that this build replaces lives in
 *    themes/stylique/assets/js/custom.js and registers NO plugins. Enabling
 *    @tailwindcss/forms here changes every storefront input (height 39->42px,
 *    radius 6px->0, transparent->white background, native appearance removed),
 *    which is a visible design regression. See .ai/reports/benchmark.md.
 * 2. tailwind.config.js drives resources/css/app.css, which CLAUDE-2 will use
 *    for the admin bundle. Admin does want `forms`. Keeping the two configs
 *    apart means the storefront fix cannot break the admin work later.
 */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './themes/*/views/**/*.blade.php',
        './themes/*/assets/js/**/*.js',
    ],

    theme: {
        extend: {
            // Mirrors `tailwind.config` in themes/stylique/assets/js/custom.js exactly,
            // so compiled output matches what the Play CDN produced at runtime.
            // Hex, not var(--primary-color): Tailwind cannot derive opacity modifiers
            // (bg-primary/10, hover:bg-primary/90) from a CSS variable holding a hex.
            fontFamily: {
                sans: ['Roboto', 'sans-serif'],
            },
            colors: {
                primary: '#f8796c',
                // Play CDN replaced the whole orange scale with a single 500 shade.
                // Kept identical on purpose.
                orange: {
                    500: '#f8796c',
                },
                // Demo Site Colors (Levo Leather reference - CLAUDE-DEMO)
                // Used when DEMO_THEME_ENABLED=true in .env
                pink: {
                    600: '#e8007c',  // Primary accent (demo)
                    700: '#d40069',  // Darker hover state
                },
                green: {
                    500: '#00c153',  // Secondary (demo)
                    600: '#00b84d',  // Darker hover state
                },
                magenta: {
                    500: '#ff00ae',  // Tertiary highlight (demo)
                },
            },
        },
    },

    safelist: [
        // From .ai/reports/tailwind-classes.md (GEMINI-2) - classes built by
        // ternaries in menu.blade.php / product-card.blade.php.
        'relative', 'dropdown', 'has-item', 'pe-4', 'flex', 'items-center',
        'block', 'px-4', 'py-2', 'text-gray-700', 'hover:bg-gray-100',
        'hover:text-orange-500', 'bg-white', 'shadow-lg', 'rounded-md',
        'py-1', 'z-50', 'absolute', 'left-0', 'mt-2', 'w-40',

        // Added after verifying themes/stylique/assets/js/custom.js by hand
        // (see .ai/reports/gemini2-verification.md). Only reachable from JS,
        // so the content scanner cannot see them.
        '-translate-x-full',  // mobile filter panel  custom.js:620,627
        'rotate-180',         // dropdown arrow       custom.js:160
        'border-transparent', // product tab border   custom.js:175,184
        'hidden',             // toggled in 13 places
    ],

    // Intentionally empty. Do not add `forms` here - see comment at top.
    plugins: [],
};

// Keep the import referenced so linters do not flag it, and so the reason this
// file exists stays discoverable. `forms` is deliberately NOT registered.
void forms;
