// Nhập hàm defineConfig từ gói vitest/config để cấu hình chung cho cả Vite và Vitest.
import { defineConfig } from 'vitest/config';
// Nhập plugin chính thức của Vue để Vite có thể biên dịch các tệp .vue.
import vue from '@vitejs/plugin-vue';

// Xuất cấu hình mặc định mà Vite và Vitest sẽ đọc khi chạy dự án.
export default defineConfig({
  // Đăng ký plugin Vue để hỗ trợ cú pháp Single File Component (SFC).
  plugins: [vue()],
  // Cấu hình riêng cho Vitest (môi trường chạy kiểm thử tự động).
  test: {
    // Dùng jsdom để mô phỏng DOM trình duyệt khi chạy các bài kiểm thử component.
    environment: 'jsdom',
    // Chỉ thu thập các tệp kiểm thử có đuôi .spec.js nằm trong thư mục tests.
    include: ['tests/**/*.spec.js'],
    // Tự động khôi phục các spy/mock sau mỗi bài kiểm thử để tránh rò rỉ trạng thái.
    restoreMocks: true,
    // Tự động xóa lịch sử gọi hàm của mock trước mỗi bài kiểm thử.
    clearMocks: true,
  },
});
