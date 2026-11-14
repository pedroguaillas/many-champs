import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    // hover: solo en dispositivos con mouse; en celular el hover queda pegado tras tocar
    future: {
        hoverOnlyWhenSupported: true,
    },
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Roboto', ...defaultTheme.fontFamily.sans],
            },
            // Curvas con decision: entrada rapida (ease-out fuerte) y movimiento en pantalla (ease-in-out).
            // Las curvas por defecto de Tailwind son demasiado suaves; ease-in no se usa en UI.
            transitionTimingFunction: {
                DEFAULT: 'cubic-bezier(0.23, 1, 0.32, 1)',
                out: 'cubic-bezier(0.23, 1, 0.32, 1)',
                'in-out': 'cubic-bezier(0.77, 0, 0.175, 1)',
            },
        },
    },

    plugins: [forms, typography],
};
