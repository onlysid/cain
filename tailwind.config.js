/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*/*.php",
    "./**/*.php",
    "./css/*.css",
    "./js/*.js",
  ],
  theme: {
    container: {
      padding: {
        DEFAULT: "1rem",
        sm: "2rem",
        lg: "3rem",
        xl: "4rem",
      },
    },
    extend: {
      colors: {
        "primary": "#0EA5E9",
        "secondary": "#14B8A6",
        "dark": "#1F2937",
        "light": "#F9FAFB"
      },
    },
    screens: {
      xs: "480px",
      sm: "600px",
      md: "782px",
      lg: "960px",
      xl: "1280px",
      "2xl": "1440px",
      "3xl": "1680px",
      "4xl": "1920px",
    },
  },
};