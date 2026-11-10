/**
 * Ready Commerce (readycommerce) Storefront Tailwind Config
 * Mirrors V0 app/globals.css (@theme inline) 1:1 without polluting other themes.
 */
export default {
    content: [
        './themes/readycommerce/views/**/*.blade.php',
        './resources/js/readycommerce-storefront.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            colors: {
                brand: {
                    DEFAULT: '#F05A00',
                    dark: '#D94F00',
                    soft: '#FFF1E8',
                    foreground: '#ffffff',
                },
                ink: '#1c1b22',
                line: '#ececf1',
                surface: '#f7f7fa',
            },
            borderRadius: {
                sm: '0.375rem',
                md: '0.5rem',
                lg: '0.625rem',
                xl: '0.875rem',
                '2xl': '1.125rem',
            },
        },
    },

    plugins: [],
};

