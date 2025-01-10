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
            sans: ['Avenir Next', '-apple-system', 'system-ui', 'BlinkMacSystemFont',
                'Segoe UI Semibold', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'sans-serif'],
            verdana: ['Verdana, sans-serif'],
            systemUi: ['-apple-system, system-ui, BlinkMacSystemFont, Avenir Next, "Segoe UI", Roboto, Helvetica Neue, sans-serif']
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
                'cds-gray': {
                    '50': '#f6f6f6',
                    '100': '#e7e7e7',
                    '200': '#d1d1d1',
                    '300': '#b0b0b0',
                    '400': '#888888',
                    '500': '#767676',
                    '600': '#5d5d5d',
                    '700': '#4f4f4f',
                    '800': '#454545',
                    '900': '#3d3d3d',
                    '950': '#262626',
                },
                'wa': {
                    'pass': '#caff37',
                    'warn': '#f6b26b',
                    'fail': '#ea9999',
                    'na':   '#9fc5e8',
                },
            },
            backgroundImage: {
                'radio-checked': "url('/cwd-framework/images/layout/checked-radio.svg')",
                'checked': "url('/cwd-framework/images/layout/checked.svg')",
            },
        },
    },
    darkMode: null,
    plugins: [],
}
