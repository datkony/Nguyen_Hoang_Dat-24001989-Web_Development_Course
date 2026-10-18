<?php
/**
 * ============================================================================
 * FILE: register.php   (YÊU CẦU V – Đăng ký tài khoản)
 * ----------------------------------------------------------------------------
 * MỤC ĐÍCH:
 *   + Form HTML gồm tên đăng nhập và mật khẩu.
 *   + Kiểm tra 2 trường KHÔNG ĐƯỢC ĐỂ TRỐNG.
 *   + Kiểm tra tên đăng nhập CHƯA TỒN TẠI trong database (UNIQUE).
 *   + Mã hóa mật khẩu bằng password_hash() TRƯỚC khi lưu.
 *   + Thông báo kết quả; thành công -> chuyển sang trang đăng nhập (login.php).
 * ============================================================================
 */

// ----- BƯỚC 1: PHIÊN ĐĂNG NHẬP --------------------------------------------
require_once __DIR__ . '/common/auth.php';

// Đã đăng nhập thì không cần đăng ký nữa -> vào thẳng danh sách sản phẩm.
if (isLoggedIn()) {
    header('Location: product_list.php');
    exit(); // dừng trước khi in HTML
}

// ----- BƯỚC 2: MODEL NGƯỜI DÙNG -------------------------------------------
require_once __DIR__ . '/model/user.php';
$userModel = new UserModel($conn);
$userModel->initTable(); // dự phòng: tạo bảng users + tài khoản mẫu nếu chưa import SQL

// ----- BƯỚC 3: BIẾN CHO FORM ----------------------------------------------
$username      = ''; // giữ lại khi có lỗi để người dùng khỏi gõ lại
$password      = ''; // KHÔNG hiển thị lại mật khẩu ở đâu cả
$errors        = [];

// ----- BƯỚC 4: XỬ LÝ KHI GỬI FORM (POST) ----------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 4.1 Đọc dữ liệu (?? '' tránh warning Undefined array key).
    $usernameRaw = $_POST['username'] ?? '';
    $passwordRaw = $_POST['password'] ?? '';

    // 4.2 KIỂM TRA TRƯỜNG KHÔNG ĐƯỢC TRỐNG (yêu cầu bắt buộc).
    //     Tên đăng nhập: trim TRƯỚC -> '   ' cũng bị coi là trống.
    $username = trim($usernameRaw);
    if ($username === '') {
        $errors[] = 'Tên đăng nhập không được để trống!';
    } elseif (mb_strlen($username) > 50) {
        // Vượt VARCHAR(50) -> MySQL sẽ báo lỗi hoặc cắt ngầm -> chặn trước.
        $errors[] = 'Tên đăng nhập không được vượt quá 50 ký tự!';
    }

    // Mật khẩu: so sánh === '' thay vì empty() (empty('0') là true -> sai lệch).
    if ($passwordRaw === '') {
        $errors[] = 'Mật khẩu không được để trống!';
    } elseif (strlen($passwordRaw) < 6) {
        // YÊU CẦU BỔ SUNG (ngoài đề bài): mật khẩu tối thiểu 6 ký tự để
        // tránh tạo tài khoản quá dễ đoán. Nếu không muốn, xóa nhánh này.
        $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự!';
    }

    // 4.3 KIỂM TRA TÊN ĐĂNG NHẬP CHƯA TỒN TẠI (yêu cầu bắt buộc).
    //     Chỉ hỏi database khi form đã hợp lệ để tránh truy vấn thừa.
    if (empty($errors) && $userModel->usernameExists($username)) {
        $errors[] = 'Tên đăng nhập đã tồn tại, vui lòng chọn tên khác!';
    }

    // 4.4 TẠO TÀI KHOẢN khi mọi kiểm tra đều đạt.
    if (empty($errors)) {
        try {
            // create() bên trong model sẽ gọi password_hash($password, PASSWORD_DEFAULT)
            // rồi mới lưu – MẬT KHẨU THUẦN KHÔNG BAO GIỜ chạm vào database.
            $userModel->create($username, $passwordRaw);

            // THÔNG BÁO + CHUYỂN TỚI TRANG ĐĂNG NHẬP (theo yêu cầu).
            // Thông báo được truyền qua tham số status -> login.php hiển thị.
            header('Location: login.php?status=registered');
            exit(); // bắt buộc dừng, nếu không HTML dưới sẽ in tiếp

        } catch (Throwable $e) {
            // Bắt cả lỗi logic (ví dụ: 2 người đăng ký trùng nhau cùng lúc ->
            // INSERT vi phạm UNIQUE) lẫn lỗi CSDL.
            // strpos(): kiểm tra thông báo lỗi có chứa chữ 'Duplicate' không
            // (MySQL báo lỗi trùng username bằng 'Duplicate entry').
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $errors[] = 'Tên đăng nhập đã tồn tại, vui lòng chọn tên khác!';
            } else {
                $errors[] = 'Không thể tạo tài khoản: ' . $e->getMessage();
            }
        }
    }
}

// ----- BƯỚC 5: HIỂN THỊ FORM ----------------------------------------------
require_once __DIR__ . '/view/header.php';
?>

<div>
    <h1>Đăng ký tài khoản</h1>

    <form action="" method="POST" novalidate>

        <div>
            <label for="username">Tên đăng nhập *</label>
            <!-- in lại tên đăng nhập đã gõ -->
            <input type="text"
                   id="username"
                   name="username"
                   maxlength="50"
                   autocomplete="username"
                   value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="VD: nguyenvana">
        </div>

        <div>
            <label for="password">Mật khẩu *</label>
            <!-- Không hiển thị lại mật khẩu (KHÔNG có attribute value) -->
            <input type="password"
                   id="password"
                   name="password"
                   autocomplete="new-password"
                   placeholder="Tối thiểu 6 ký tự">
        </div>

        <div>
            <button type="submit">Đăng ký</button>
        </div>
    </form>

    <br>

    <?php if (!empty($errors)): ?>
        <!-- Thông báo lỗi đặt trên form, mỗi dòng qua htmlspecialchars (chống XSS) -->
        <div>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <p>
        <!-- Nút điều hướng sang trang đăng nhập – dùng <button> thay cho thẻ <a> -->
        Đã có tài khoản?
        <button type="button" onclick="window.location.href='login.php'"><strong>Đăng nhập</strong></button>
    </p>
</div>

<?php
// Đóng trang (KHÔNG gọi header('Location') sau dòng này).
require_once __DIR__ . '/view/footer.php';
