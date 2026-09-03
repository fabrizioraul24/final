/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        fitonist: {
          bg: {
            dark: '#0e0f14',
            light: '#f3f4f8',
          },
          card: {
            dark: '#161820',
            light: '#ffffff',
          },
          cardSub: {
            dark: '#1e212b',
            light: '#f8fafc',
          },
          border: {
            dark: 'rgba(255, 255, 255, 0.08)',
            light: '#e2e8f0',
          },
          purple: '#0b4fc1',
          purpleLight: '#7ea6ff',
          green: '#22c55e',
          yellow: '#f25a59',
          cyan: '#06b6d4',
          pink: '#ec4899',
        }
      },
      fontFamily: {
        sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
      },
      boxShadow: {
        'glow-purple': '0 0 25px -5px rgba(11, 79, 193, 0.3)',
        'glow-green': '0 0 20px -5px rgba(34, 197, 94, 0.3)',
      }
    },
  },
  plugins: [],
}
