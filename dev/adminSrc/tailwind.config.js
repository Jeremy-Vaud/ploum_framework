/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./adminSrc/**/*.{html,js,ts,jsx,tsx}",
    ],
    theme: {
        extend: {
            spacing: {
                header_height: '5rem',
                header_padding: '1.5rem'
            },
            screens: {
                xs: '420px'
            }
        },
        colors: {
            transparent: 'transparent',
            current: 'currentColor',
            dark: '#082f49',
            light: 'white',
            main: '#60a5fa',
            main_light: '#93c5fd',
            add: '#16a34a',
            add_light: '#22c55e',
            cancel: "#d4d4d8",
            cancel_light: "#e5e5e5",
            delete: "#ef4444",
            delete_light: "#f87171",
            update: "#f59e0b",
            update_light: "#fbbf24",
        },
    },
    plugins: [
        require('tailwind-scrollbar-hide')
    ],
}