// Nhập hàm createApp từ Vue để tạo một ứng dụng Vue 3 mới.
import { createApp } from 'vue';
// Nhập component gốc App từ tệp Single File Component chứa danh mục sản phẩm.
import App from './App.vue';
// Nhập component thẻ sản phẩm từ tệp JavaScript riêng theo đúng yêu cầu của đề bài.
import TheSanPham from './components/TheSanPham.js';
// Nhập tệp CSS toàn cục để áp dụng giao diện cho toàn bộ ứng dụng.
import './style.css';

// Tạo thực thể ứng dụng Vue từ component gốc App.
const ungDung = createApp(App);

// Đăng ký thẻ sản phẩm ở cấp toàn cục với tên thẻ viết dạng kebab-case là "the-san-pham".
// Bước này bắt buộc phải chạy TRƯỚC khi gọi mount() để Vue phân giải được thẻ trong template của App.
ungDung.component('the-san-pham', TheSanPham);

// Gắn ứng dụng đã cấu hình vào phần tử có id="app" trong tệp index.html.
ungDung.mount('#app');
