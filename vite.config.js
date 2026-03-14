import { fileURLToPath, URL } from 'node:url'
import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'
// import laravel from 'laravel-vite-plugin'
import { defineConfig } from 'vite'

export default defineConfig({
  plugins: [
    // laravel({
    //   input: [],
    //   refresh: true,
    // }),
    vue(),
    tailwindcss(),
  ],
  resolve: {
    alias: {
      'vue': 'vue/dist/vue.esm-bundler.js',
      '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
    },
  },
})
