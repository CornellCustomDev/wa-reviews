/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./vendor/livewire/flux-pro/stubs/**/*.blade.php",
        "./vendor/livewire/flux/stubs/**/*.blade.php",
    ],
    theme: {
        fontFamily: {
            sans: ['Inter', 'sans-serif'],
        },
        extend: {
            colors: {
                'cds-blue': {
                    '50': '#f4f7fb',
                    '100': '#e7f0f7',
                    '200': '#cadeed',
                    '300': '#9bc3de',
                    '400': '#65a5cb',
                    '500': '#4289b5',
                    '600': '#2d668e',
                    '700': '#28587c',
                    '800': '#254b67',
                    '900': '#234057',
                    '950': '#17293a',
                },
            },
        },
    },
    darkMode: ['variant', '&:not(.light *)'],
    plugins: [],
}
