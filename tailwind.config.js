import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
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
                yellow: {
                    50:  '#fffbea',
                    100: '#fff3c4',
                    200: '#fce588',
                    300: '#fadb5f',
                    400: '#f7c948',
                    500: '#f0b429',
                    600: '#fcd45b',
                    700: '#c69400',
                    800: '#997100',
                    900: '#7c5e00'
                }
            }
        },
    },

    plugins: [forms],
};
