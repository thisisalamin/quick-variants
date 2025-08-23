/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './*.php',
    './templates/**/*.php',
    './includes/**/*.php',
    './assets/js/**/*.js',
    './assets/css/**/*.css'
  ],
  safelist: [
    'bg-[#232323]',
    'w-[400px]',
    'w-[120px]',
    'w-[180px]'
  ],
  theme: { extend: {} },
  plugins: []
};
