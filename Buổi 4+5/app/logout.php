<?php
/**
 * ============================================================================
 * FILE: logout.php   (YÊU CẦU V – Chức năng đăng xuất)
 * ----------------------------------------------------------------------------
 * MỤC ĐÍCH:
 *   + Xóa thông tin phiên đăng nhập ($_SESSION + cookie + session trên server).
 *   + Chuyển người dùng về trang login.php SAU khi đăng xuất.
 *
 * LƯU Ý: toàn bộ file này chỉ gồm PHP thuần, KHÔNG có một byte HTML nào,
 * nên lệnh header('Location: ...') luôn hoạt động (không lỗi headers sent).
 * ============================================================================
 */

// Nhúng file chứa các hàm phiên (auth.php tự session_start() nếu chưa chạy).
require_once __DIR__ . '/common/auth.php';

// Xóa $_SESSION (user_id, username...), xóa cookie phiên và hủy session phía server.
performLogout();

// Chuyển về trang đăng nhập kèm cờ 'logged_out' để login.php hiện
// thông báo "Bạn đã đăng xuất" (chống XSS nhờ white list ở login.php).
header('Location: login.php?status=logged_out');

// Dừng chương trình: nếu thiếu dòng này, PHP vẫn tiếp tục chạy các file được
// nhúng phía dưới (nếu có) và in HTML ra sau header chuyển hướng.
exit();
