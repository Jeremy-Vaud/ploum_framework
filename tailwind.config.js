/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./view/*.php",
        "./view/*/*.php"
    ],
    safelist: [

    ],
    theme: {
        extend: {},
        colors: {
            transparent: 'transparent',
            current: 'currentColor',
            aubergine: '#0C090C',
            beige: '#F5ECE9',
        },
        fontFamily: {
            niramit: ['Niramit', 'sans-serif'],
            langar: ['Langar', 'cursive']
        },
        container: {
            screens: {
                sm: '640px',
                md: '768px',
                lg: '1024px',
                xl: '1280px'
              },
              padding: '10px'
        }
    },
    plugins: [],
}
