// Nhập hàm defineConfig từ gói vitest/config để dùng chung một tệp cấu hình cho cả Vite và Vitest.
import { defineConfig } from 'vitest/config';
// Nhập plugin chính thức của Vue để Vite biên dịch được các tệp Single File Component (.vue).
import vue from '@vitejs/plugin-vue';

// Xuất cấu hình dùng chung cho môi trường phát triển, build và kiểm thử của dự án.
export default defineConfig({
  // Dùng đường dẫn tài nguyên tương đối để bản build có thể chạy trong thư mục con của XAMPP.
  base: './',
  // Đăng ký plugin Vue để hỗ trợ cú pháp SFC trong toàn bộ dự án.
  plugins: [vue()],
  // Cấu hình riêng cho Vitest, công cụ chạy kiểm thử tự động.
  test: {
    // Dùng jsdom để mô phỏng môi trường DOM trình duyệt khi kiểm thử component Vue.
    environment: 'jsdom',
    // Chỉ thu thập các tệp kiểm thử có đuôi .spec.js nằm trong thư mục tests.
    include: ['tests/**/*.spec.js'],
    // Khôi phục các spy/mock sau mỗi bài kiểm thử để tránh rò rỉ trạng thái giữa các bài.
    restoreMocks: true,
    // Xóa lịch sử gọi hàm của mock trước mỗi bài kiểm thử để kết quả không bị ảnh hưởng lẫn nhau.
    clearMocks: true,
  },
});
