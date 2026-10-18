<?php
/**
 * ============================================================================
 * FILE: common/auth.php   (YÊU CẦU V – Đăng nhập / Đăng xuất / Kiểm soát truy cập)
 * ----------------------------------------------------------------------------
 * MỤC ĐÍCH: Tập trung toàn bộ thao tác phiên (session) dùng chung:
 *   - Khởi tạo session MỘT LẦN cho cả chương trình.
 *   - isLoggedIn():     kiểm tra người dùng đã đăng nhập hay chưa.
 *   - requireLogin():   CHẶN truy cập các trang quản lý khi chưa đăng nhập.
 *   - currentUsername():lấy tên tài khoản đang đăng nhập để hiển thị.
 *   - performLogout():  xóa thông tin phiên khi đăng xuất.
 *
 * CÁCH SỬ DỤNG (mọi trang cần bảo vệ phải nhúng file này TRƯỚC khi xuất HTML):
 *   require_once __DIR__ . '/common/auth.php';
 *   requireLogin();   // nếu chưa đăng nhập -> chuyển về login.php rồi DỪNG
 * ============================================================================
 */

// session_start() chỉ được gọi MỘT LẦN trong một request. Nếu mỗi file đều
// gọi trực tiếp thì trang nhúng nhiều file sẽ báo
// "Warning: session_start(): Cannot start session when headers already sent"
// hoặc "A session had already been started".
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Mở/khởi tạo phiên; PHP sẽ tự gắn cookie PHPSESSID vào trình duyệt
}

/**
 * Kiểm tra xem có ai đang đăng nhập hay không.
 * @return bool true nếu phiên có đủ thông tin của người dùng đã đăng nhập
 */
function isLoggedIn(): bool
{
    // isset() kiểm tra cả HAI khóa cùng lúc; thiếu một khóa nào cũng coi như chưa đăng nhập.
    // (Khi đăng xuất ta sẽ xóa hết $_SESSION nên hàm này tự động trả về false.)
    return isset($_SESSION['user_id'], $_SESSION['username']);
}

/**
 * BƯỚC KIỂM SOÁT TRUY CẬP (YÊU CẦU III/ V – "Chỉ người dùng đã đăng nhập
 * mới được truy cập các trang product_*.php").
 * Chưa đăng nhập -> chuyển hướng về trang login.php và DỪNG chương trình.
 */
function requireLogin(): void
{
    // Nếu phiên chưa có thông tin người dùng...
    if (!isLoggedIn()) {
        // Chuyển hướng 302 về trang đăng nhập.
        header('Location: login.php');
        // exit() BẮT BUỘC phải có: nếu thiếu, code phía sau vẫn chạy tiếp
        // (HTML vẫn được in ra) -> mất ý nghĩa của việc chặn truy cập.
        exit();
    }
}

/**
 * Lấy tên tài khoản đang đăng nhập (dùng để hiển thị trên header).
 * @return string|null null nếu chưa đăng nhập
 */
function currentUsername(): ?string
{
    // $_SESSION['username'] ?? null: nếu khóa không tồn tại thì trả về null
    // thay vì báo warning "Undefined array key".
    return $_SESSION['username'] ?? null;
}

/**
 * Đăng xuất: xóa toàn bộ thông tin phiên rồi hủy session.
 * YÊU CẦU V – "Xóa thông tin phiên đăng nhập bằng session".
 */
function performLogout(): void
{
    // gán mảng rỗng: xóa sạch user_id, username... ngay lập tức.
    $_SESSION = [];

    // Xóa cookie phiên phía trình duyệt (khóa = session_name() = 'PHPSESSID').
    // Nếu TỰ XÓA SESSION nhưng không xóa cookie thì cookie mồ côi vẫn còn gửi
    // lên server ở các request sau.
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params(); // lấy thông tin cookie hiện tại
        setcookie(
            session_name(),                    // tên cookie: PHPSESSID
            '',                                // giá trị rỗng = xóa
            time() - 42000,                    // thời gian hết hạn trong quá khứ
            $params['path'],                   // giữ nguyên path
            $params['domain'],                 // giữ nguyên domain
            $params['secure'],                 // giữ nguyên cờ secure
            $params['httponly']                // giữ nguyên cờ httponly
        );
    }

    // Hủy phiên trên SERVER: xóa file lưu trữ phiên của người dùng này.
    session_destroy();
}
?>
