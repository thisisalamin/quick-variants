/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.php", // Main plugin directory
    "./templates/**/*.php", // Template folder
    "./assets/js/**/*.js", // JavaScript files inside assets
    "./assets/css/**/*.css", // CSS files inside assets
  ],
  theme: {
    extend: {},
  },
  plugins: [],
};
