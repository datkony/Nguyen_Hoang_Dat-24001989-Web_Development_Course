// Import hàm defineConfig để có gợi ý (IntelliSense) và kiểm tra kiểu khi viết cấu hình.
import { defineConfig } from 'vite'
// Import plugin chính thức của Vue: cho phép Vite hiểu và biên dịch file .vue.
import vue from '@vitejs/plugin-vue'

// Xuất cấu hình mặc định cho Vite.
export default defineConfig({
  // Kích hoạt plugin Vue để xử lý các file Single File Component (.vue).
  plugins: [vue()],

  // base: './' giúp mọi đường dẫn tài nguyên (JS, CSS, ảnh) trở thành đường dẫn TƯƠNG ĐỐI.
  // Nhờ đó thư mục dist/ có thể chạy trực tiếp hoặc đặt trong thư mục con của XAMPP
  // (đường dẫn có dấu tiếng Việt và khoảng trắng) mà không bị lỗi 404.
  base: './',

  // Cấu hình máy chủ phát triển (chỉ dùng khi chạy "npm run dev").
  server: {
    // Dùng cổng 5174 để không trùng với dự án "Bài 1" (đang dùng 5173).
    port: 5174,
    // Tự động mở trình duyệt khi khởi động máy chủ.
    open: true
  }
})
