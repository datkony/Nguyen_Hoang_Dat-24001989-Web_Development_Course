// ============================================================
// FILE KHỞI ĐỘNG ỨNG DỤNG VUE
// - createApp: hàm của Vue 3 dùng để tạo một ứng dụng mới.
// - App: component gốc (root component) của ứng dụng.
// ============================================================

// Import hàm createApp từ thư viện Vue 3.
import { createApp } from 'vue'

// Import CSS dùng chung (reset margin, màu nền, phông chữ...).
import './style.css'

// Import component gốc App.vue.
import App from './App.vue'

// Tạo ứng dụng từ component gốc và gắn vào phần tử #app trong index.html.
createApp(App).mount('#app')
