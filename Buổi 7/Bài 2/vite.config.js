// Nhập hàm defineConfig từ gói vitest/config để dùng chung một tệp cấu hình cho Vite và Vitest.
import { defineConfig } from 'vitest/config';
// Nhập plugin chính thức của Vue để Vite hiểu và biên dịch các tệp Single File Component (.vue).
import vue from '@vitejs/plugin-vue';

// Xuất cấu hình mặc định mà Vite dùng khi chạy dev/build và Vitest dùng khi chạy kiểm thử.
export default defineConfig({
  // Dùng đường dẫn tài nguyên tương đối để bản build có thể chạy trong thư mục con của XAMPP.
  // Nhờ vậy không cần biết trước đường dẫn tuyệt đối khi triển khai ứng dụng.
  base: './',
  // Cấu hình cách Vite phân giải các gói phụ thuộc trong dự án.
  resolve: {
    // Ánh xạ gói "vue" sang bản build có kèm trình biên dịch template.
    // Lý do: component TheSanPham.js định nghĩa giao diện bằng chuỗi template,
    // nên cần bản Vue đầy đủ để biên dịch chuỗi đó ngay trong trình duyệt.
    // Nếu dùng bản runtime-only mặc định, các thẻ sản phẩm sẽ không được render.
    alias: {
      // Trỏ tên gói "vue" tới tệp ESM đầy đủ có compiler.
      vue: 'vue/dist/vue.esm-bundler.js',
    },
  },
  // Đăng ký plugin Vue để hỗ trợ cú pháp SFC trong toàn bộ dự án.
  plugins: [vue()],
  // Cấu hình riêng cho Vitest, công cụ chạy kiểm thử tự động của dự án.
  test: {
    // Dùng jsdom để mô phỏng môi trường DOM trình duyệt khi kiểm thử component Vue.
    environment: 'jsdom',
    // Chỉ thu thập các tệp kiểm thử có đuôi .spec.js nằm trong thư mục tests.
    include: ['tests/**/*.spec.js'],
    // Tự động khôi phục các spy/mock sau mỗi bài kiểm thử để tránh rò rỉ trạng thái giữa các bài.
    restoreMocks: true,
    // Tự động xóa lịch sử gọi hàm của mock trước mỗi bài kiểm thử để kết quả không bị ảnh hưởng lẫn nhau.
    clearMocks: true,
  },
});
