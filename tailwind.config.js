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
        "primary": "#E3FFEC",
        "primary-dark": "#D3F1E4",
        "secondary": "#16cdff",
        "tirtiary": "#77DFFF",
        "dark": "#4D4C4C",
        "light-grey": "#777777",
        "light": "#00000029",
        "grey": "#5F5F5F",
      },
    },
    fontFamily: {
      roboto: ['Roboto', 'sans-sefif'],
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