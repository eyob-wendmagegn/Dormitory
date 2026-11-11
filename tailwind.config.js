import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                obsidian: {
                    DEFAULT: '#0B0F17',
                    dark: '#070A10',
                    light: '#111726',
                },
                surface: {
                    DEFAULT: '#121927',
                    elevated: '#172134',
                    hover: '#1E293B',
                },
                accent: {
                    DEFAULT: '#10B981',
                    secondary: '#34D399',
                    muted: 'rgba(16, 185, 129, 0.15)',
                },
                darkborder: {
                    DEFAULT: 'rgba(255, 255, 255, 0.08)',
                    subtle: 'rgba(255, 255, 255, 0.05)',
                },
                ambient: {
                    green: '#064E3B',
                    glow: 'rgba(16, 185, 129, 0.2)',
                },
            },
        },
    },

    plugins: [forms],
};
