// Nhập hàm createApp từ Vue để tạo một ứng dụng Vue 3 mới.
import { createApp } from 'vue';
// Nhập component gốc App từ tệp Single File Component.
import App from './App.vue';
// Nhập tệp CSS toàn cục để áp dụng giao diện cho toàn bộ ứng dụng.
import './style.css';

// Tạo ứng dụng Vue từ component gốc App.
createApp(App)
  // Gắn ứng dụng vào phần tử có id="app" trong tệp index.html.
  .mount('#app');
