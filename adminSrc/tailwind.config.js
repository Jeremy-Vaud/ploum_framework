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
            add: '#22c55e',
            add_light: '#4ade80',
            cancel: "#d4d4d8",
            cancel_light: "#9ca3af",
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