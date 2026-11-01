<!--
  ============================================================
  APP.VUE - COMPONENT GỐC
  Nhiệm vụ: "lắp ghép" component Bai1 vào ứng dụng.
  Nhờ tách riêng như vậy, sau này có thể thêm nhiều bài/trang khác
  mà không phải sửa nội dung của bai_1.vue.
  ============================================================
-->
<template>
  <!-- Khu vực hiển thị nội dung chính của ứng dụng. -->
  <main class="ung-dung">
    <!--
      Sử dụng component Bai1 vừa import bên dưới.
      Tên thẻ trong template là "Bai1" (PascalCase) tương ứng biến Bai1 trong script.
    -->
    <Bai1 />
  </main>
</template>

<script>
// Import component bài 1 từ thư mục components.
// Đường dẫn tương đối: src/App.vue -> src/components/bai_1.vue
import Bai1 from './components/bai_1.vue'

export default {
  // Khai báo tên component gốc.
  name: 'App',

  // Đăng ký component Bai1 để có thể dùng thẻ <Bai1 /> ở phần template.
  components: {
    Bai1
  }
}
</script>

<style scoped>
/* Khung chứa nội dung, căn giữa và tạo khoảng trống trên/dưới. */
.ung-dung {
  /* Giới hạn chiều rộng tối đa cho dễ đọc trên màn hình lớn. */
  max-width: 720px;
  /* Căn giữa khối theo chiều ngang. */
  margin: 0 auto;
  /* Khoảng đệm trên và dưới. */
  padding: 24px 16px 40px;
}
</style>
