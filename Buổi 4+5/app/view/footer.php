<?php
/**
 * ============================================================================
 * FILE: view/footer.php   (YÊU CẦU IV – dùng view/footer.php cho các trang)
 * ----------------------------------------------------------------------------
 * MỤC ĐÍCH: Phần KẾT THÚC chung – đóng các thẻ HTML mà header.php đã mở:
 *   </main> (vùng nội dung) -> </body> -> </html>.
 *
 * CÁCH DÙNG (luôn là dòng CUỐI CÙNG của trang):
 *   require_once __DIR__ . '/view/footer.php';
 *
 * LƯU Ý:
 *   Khi nhúng file này nghĩa là HTML đã được in ra rồi, nên KHÔNG được gọi
 *   header('Location: ...') sau câu lệnh này (sẽ lỗi headers already sent).
 * ============================================================================
 */
?>
</main><!-- Đóng thẻ <main> mở trong view/header.php -->

</body><!-- Đóng thẻ <body> mở trong view/header.php -->
</html><!-- Đóng thẻ <html> – kết thúc trang -->
