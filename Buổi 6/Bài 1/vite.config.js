import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// Cấu hình Vite cho dự án Vue 3
export default defineConfig({
  plugins: [vue()],
  // base: './' giúp các đường dẫn tài nguyên (js/css) là đường dẫn tương đối.
  // Nhờ vậy sau khi "npm run build", thư mục dist có thể mở trực tiếp
  // hoặc chạy trong thư mục con của XAMPP mà không bị lỗi 404.
  base: './',
  server: {
    port: 5173,
    open: true
  }
})
