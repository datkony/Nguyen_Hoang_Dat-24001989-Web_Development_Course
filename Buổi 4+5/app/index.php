<?php
/**
 * ============================================================================
 * FILE: index.php   (Trang chủ – lối vào của hệ thống)
 * ----------------------------------------------------------------------------
 * MỤC ĐÍCH:
 *   + Trang điều hướng: chọn "Xem danh sách sản phẩm" hoặc "Thêm sản phẩm".
 *   + Hiển thị menu đăng nhập/đăng xuất (thông qua view/header.php).
 *
 * LƯU Ý VỀ THỨ TỰ (sửa lỗi tiềm ẩn "header() sau khi đã xuất HTML"):
 *   Mọi lệnh header('Location: ...') nằm ở PHẦN ĐẦU file, TRƯỚC lần nhúng
 *   view/header.php (dòng in HTML đầu tiên). Nếu trật tự này thì chuyển hướng
 *   sẽ hỏng với lỗi "Cannot modify header information - headers already sent".
 * ============================================================================
 */

// Nhúng các hàm phiên để header.php hiển thị đúng trạng thái đăng nhập.
require_once __DIR__ . '/common/auth.php';

// ----- XỬ LÝ ĐIỀU HƯỚNG QUA THAM SỐ ?action= --------------------------------
// Đây là kiểu chuyển hướng "nhẹ" tại trang chủ: index.php?action=view|add
if (isset($_GET['action'])) {

    if ($_GET['action'] === 'view') {
        // Chuyển tới trang danh sách sản phẩm.
        header('Location: product_list.php');
        exit(); // DỪNG NGAY: nếu không, HTML phía dưới vẫn in ra và trình duyệt
                // sẽ bỏ qua phần thân phản hồi của lệnh chuyển hướng.
    }

    if ($_GET['action'] === 'add') {
        // SỬA LỖI BẢN CŨ: nút "Thêm mặt hàng vào giỏ" trước đây trỏ nhầm sang
        // index.php?action=view (copy/paste), dẫn tới cả 2 nút đều sang một chỗ.
        header('Location: product_add.php');
        exit();
    }
    // action không hợp lệ -> rơi xuống, vẫn hiển thị trang chủ bình thường.
}

// ----- HIỂN THỊ TRANG CHỦ ---------------------------------------------------
require_once __DIR__ . '/view/header.php';
?>

<h1>Hệ thống quản lý giỏ hàng</h1>

<div>
    <!--
        Hai nút điều hướng ở trang chủ: dùng thẻ <button> (KHÔNG dùng thẻ <a>).
        - type="button" để không gửi form nào (trang này không có form).
        - onclick="window.location.href=..." thay cho href -> bấm là chuyển trang.
        Vẫn đi qua ?action= để giữ nguyên kiểu điều hướng của trang chủ
        (index.php xử lý ?action= rồi header('Location: ...') ở đầu file).
    -->
    <button type="button" onclick="window.location.href='index.php?action=view'">Xem giỏ hàng</button>
    <button type="button" onclick="window.location.href='index.php?action=add'">Thêm mặt hàng vào giỏ</button>
</div>

<hr>

<?php if (isLoggedIn()): ?>
    <!--
        Trạng thái ĐÃ ĐĂNG NHẬP: hiện tên tài khoản + 2 lối vào chức năng quản lý.
        currentUsername() lấy từ $_SESSION; htmlspecialchars chống XSS.
    -->
    <p>
        Bạn đang đăng nhập với tài khoản
        <strong><?php echo htmlspecialchars((string) currentUsername(), ENT_QUOTES, 'UTF-8'); ?></strong>.
    </p>

    <div>
        <!-- Nút điều hướng: thẻ <button> thay cho thẻ <a> -->
        <button type="button" onclick="window.location.href='logout.php'">Đăng xuất</button>
    </div>

<?php else: ?>
    <!--
        Trạng thái CHƯA ĐĂNG NHẬP: các trang product_*.php sẽ tự chuyển về
        login.php (requireLogin()), nên trang chủ gợi ý người dùng đăng nhập trước.
    -->
    <p>Bạn chưa đăng nhập. Hãy đăng nhập để vào khu vực quản lý sản phẩm.</p>

    <div>
        <button type="button" onclick="window.location.href='login.php'">Đăng nhập</button>
        <button type="button" onclick="window.location.href='register.php'">Đăng ký</button>
    </div>
<?php endif; ?>

<?php
// Đóng trang (KHÔNG gọi header('Location') sau dòng này).
require_once __DIR__ . '/view/footer.php';
?>