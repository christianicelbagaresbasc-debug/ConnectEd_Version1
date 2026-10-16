import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                heading: ['Poppins', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                bg: 'var(--bg)',
                surface: {
                    DEFAULT: 'var(--surface)',
                    2: 'var(--surface-2)',
                },
                border: 'var(--border)',
                text: {
                    DEFAULT: 'var(--text)',
                    muted: 'var(--text-muted)',
                },
                primary: {
                    DEFAULT: 'var(--primary)',
                    contrast: 'var(--primary-contrast)',
                },
                accent: 'var(--accent)',
                success: 'var(--success)',
                warning: 'var(--warning)',
                danger: 'var(--danger)',
                info: 'var(--info)',
            },
            borderRadius: {
                DEFAULT: '8px',
                card: '12px',
            },
        },
    },

    plugins: [forms],
};
