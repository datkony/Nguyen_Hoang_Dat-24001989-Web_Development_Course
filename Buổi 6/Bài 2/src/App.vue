<!--
  ============================================================
  APP.VUE - COMPONENT GỐC
  Nhiệm vụ: "lắp ghép" component Bai2 vào ứng dụng.
  Nhờ tách riêng như vậy, sau này có thể thêm nhiều bài/trang khác
  mà không phải sửa nội dung của bai_2.vue.
  ============================================================
-->
<template>
  <!-- Khu vực hiển thị nội dung chính của ứng dụng. -->
  <main class="ung-dung">
    <!--
      Sử dụng component Bai2 vừa import bên dưới.
      Tên thẻ trong template là "Bai2" (PascalCase) tương ứng biến Bai2 trong script.
    -->
    <Bai2 />
  </main>
</template>

<script>
// Import component bài 2 từ thư mục components.
// Đường dẫn tương đối: src/App.vue -> src/components/bai_2.vue
import Bai2 from './components/bai_2.vue'

export default {
  // Khai báo tên component gốc.
  name: 'App',

  // Đăng ký component Bai2 để có thể dùng thẻ <Bai2 /> ở phần template.
  components: {
    Bai2
  }
}
</script>

<style scoped>
/* Khung chứa nội dung, căn giữa và tạo khoảng trống trên/dưới. */
.ung-dung {
  /* Giới hạn chiều rộng tối đa cho dễ đọc trên màn hình lớn. */
  max-width: 760px;
  /* Căn giữa khối theo chiều ngang. */
  margin: 0 auto;
  /* Khoảng đệm trên và dưới. */
  padding: 24px 16px 48px;
}
</style>
