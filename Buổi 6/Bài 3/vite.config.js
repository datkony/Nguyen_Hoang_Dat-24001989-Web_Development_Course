// Import hàm defineConfig để có gợi ý (IntelliSense) và kiểm tra cấu hình khi viết.
import { defineConfig } from 'vite'
// Import plugin chính thức của Vue để Vite hiểu và biên dịch được các file .vue.
import vue from '@vitejs/plugin-vue'

// Xuất cấu hình mặc định cho Vite.
export default defineConfig({
  // Kích hoạt plugin Vue để xử lý các Single File Component (.vue).
  plugins: [vue()],

  // base: './' biến mọi đường dẫn tài nguyên (JS, CSS, ảnh) thành đường dẫn TƯƠNG ĐỐI.
  // Nhờ vậy thư mục dist/ có thể chạy trực tiếp hoặc đặt trong thư mục con của XAMPP
  // (đường dẫn có dấu tiếng Việt và khoảng trắng) mà không bị lỗi 404.
  base: './',

  // Cấu hình máy chủ phát triển (chỉ dùng khi chạy "npm run dev").
  server: {
    // Dùng cổng 5175 để không trùng với "Bài 1" (5173) và "Bài 2" (5174).
    port: 5175,
    // Tự động mở trình duyệt khi khởi động máy chủ phát triển.
    open: true
  }
})
