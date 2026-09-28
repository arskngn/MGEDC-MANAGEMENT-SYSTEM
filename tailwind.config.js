import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                ink: {
                    DEFAULT: 'var(--ink-body)',
                    body:    'var(--ink-body)',
                    heading: 'var(--ink-heading)',
                    muted:   'var(--ink-muted)',
                    subtle:  'var(--ink-subtle)',
                    inverse: 'var(--ink-inverse)',
                    placeholder: 'var(--ink-placeholder)',
                },
                surface: {
                    DEFAULT: 'var(--surface-body)',
                    body:   'var(--surface-body)',
                    panel:  'var(--surface-panel)',
                    inset:  'var(--surface-inset)',
                    elevated: 'var(--surface-elevated)',
                    hover:  'var(--surface-hover)',
                },
                stroke: {
                    DEFAULT: 'var(--stroke-body)',
                    body:   'var(--stroke-body)',
                    strong: 'var(--stroke-strong)',
                    subtle: 'var(--stroke-subtle)',
                },
            },
        },
    },

    plugins: [forms],
};
