// Nhập hàm createApp từ Vue để tạo một ứng dụng Vue 3 mới.
import { createApp } from 'vue';
// Nhập component gốc App từ tệp Single File Component chứa toàn bộ giao diện giỏ hàng.
import App from './App.vue';
// Nhập tệp CSS toàn cục để áp dụng giao diện cho toàn bộ ứng dụng.
import './style.css';

// Tạo thực thể ứng dụng Vue từ component gốc App.
const ungDung = createApp(App);

// Gắn ứng dụng đã cấu hình vào phần tử có id="app" trong tệp index.html.
// Component con <dong-gio-hang> được đăng ký cục bộ trong App.vue nên không cần đăng ký toàn cục ở đây.
ungDung.mount('#app');
