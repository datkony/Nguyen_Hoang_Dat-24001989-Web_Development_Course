<?php
/**
 * ============================================================================
 * FILE: login.php   (YÊU CẦU V – Chức năng đăng nhập)
 * ----------------------------------------------------------------------------
 * MỤC ĐÍCH:
 *   + Form HTML gồm tên đăng nhập và mật khẩu.
 *   + Kiểm tra thông tin với bảng users bằng password_verify().
 *   + Thành công: lưu vào $_SESSION rồi chuyển tới product_list.php.
 *   + Sai: hiển thị thông báo lỗi ngay trên form.
 *
 * THỨ TỰ: xử lý PHP + header('Location') TRƯỚC, mới nhúng view/header.php.
 * ============================================================================
 */

// ----- BƯỚC 1: PHIÊN ĐĂNG NHẬP --------------------------------------------
require_once __DIR__ . '/common/auth.php';

// Nếu ĐÃ đăng nhập mà vào trang này thì chuyển thẳng vào danh sách sản phẩm
// (tránh hiện form đăng nhập thừa).
if (isLoggedIn()) {
    header('Location: product_list.php');
    exit(); // dừng ngay để không in HTML
}

// ----- BƯỚC 2: MODEL NGƯỜI DÙNG -------------------------------------------
require_once __DIR__ . '/model/user.php';

// Đối tượng model dùng chung $conn (do dbConnect.php tạo).
$userModel = new UserModel($conn);
$userModel->initTable(); // dự phòng: tạo bảng users + tài khoản mẫu nếu chưa import SQL

// ----- BƯỚC 3: BIẾN CHO FORM ----------------------------------------------
$username = ''; // GIỮ lại tên đăng nhập đã gõ khi có lỗi (mất công gõ lại)
$password = ''; // KHÔNG bao giờ in mật khẩu trở lại form (bảo mật)
$errors   = [];

// Thông báo chuyển tiếp từ trang đăng ký (register.php redirect về đây).
// Dùng white list thay vì echo thẳng $_GET['status'] -> chống XSS.
$notice = '';
if (($_GET['status'] ?? '') === 'registered') {
    $notice = 'Đăng ký thành công! Vui lòng đăng nhập bằng tài khoản vừa tạo.';
} elseif (($_GET['status'] ?? '') === 'logged_out') {
    $notice = 'Bạn đã đăng xuất. Hãy đăng nhập lại để tiếp tục.';
}

// ----- BƯỚC 4: XỬ LÝ KHI GỬI FORM (POST) ----------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Lấy dữ liệu người dùng gửi lên (?? '' tránh warning khi thiếu khóa).
    $usernameRaw = $_POST['username'] ?? '';
    $passwordRaw = $_POST['password'] ?? '';

    // 4.1 Tên đăng nhập: trim rồi kiểm rỗng (kể cả chuỗi toàn khoảng trắng).
    $username = trim($usernameRaw);
    if ($username === '') {
        $errors[] = 'Vui lòng nhập tên đăng nhập!';
    }

    // 4.2 Mật khẩu: kiểm tra === '' thay vì empty().
    //     Lý do: empty('0') === true -> mật khẩu '0' sẽ bị coi là TRỐNG oan.
    //     Không trim mật khẩu: mật khẩu có thể CỐ Ý chứa khoảng trắng ở đầu/cuối;
    //     nếu trim bên này mà lúc đăng ký password_hash() lại không trim thì sẽ lệch.
    if ($passwordRaw === '') {
        $errors[] = 'Vui lòng nhập mật khẩu!';
    }

    // 4.3 Chỉ kiểm tra đăng nhập khi form đã hợp lệ.
    if (empty($errors)) {
        try {
            // verify() thực hiện: SELECT theo username -> password_verify().
            // Trả về ['id','username'] nếu đúng, null nếu SAI tên hoặc SAI mật khẩu.
            $user = $userModel->verify($username, $passwordRaw);

            if ($user === null) {
                // Thông báo CHUNG, cố ý KHÔNG phân biệt "sai tên" hay "sai mật
                // khẩu": tránh kẻ xấu dò được tài khoản nào tồn tại trong hệ thống.
                $errors[] = 'Tên đăng nhập hoặc mật khẩu không đúng!';
            } else {
                // ---- ĐĂNG NHẬP THÀNH CÔNG ----
                // session_regenerate_id(true): sinh MÃ PHIÊN MỚI và xóa phiên cũ.
                // Chống "session fixation": kẻ xấu cố ghép phiên của nó vào
                // trình duyệt của nạn nhân trước khi nạn nhân đăng nhập.
                session_regenerate_id(true);

                // Lưu THÔNG TIN CẦN THIẾT vào phiên (đề bài: "lưu thông tin cần
                // thiết vào $_SESSION"). Chỉ id + username, KHÔNG lưu mật khẩu.
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];

                // Chuyển tới trang quản lý sản phẩm theo đúng yêu cầu.
                // header() chạy trước mọi HTML -> hoạt động bình thường.
                header('Location: product_list.php');
                exit(); // BẮT BUỘC dừng lại, nếu không HTML phía dưới vẫn in ra
            }

        } catch (Throwable $e) {
            // Lỗi CSDL (MySQL chưa chạy, bảng users chưa tạo...) -> báo thân thiện.
            $errors[] = 'Không thể kiểm tra đăng nhập: ' . $e->getMessage();
        }
    }
}

// ----- BƯỚC 5: HIỂN THỊ FORM ----------------------------------------------
require_once __DIR__ . '/view/header.php';
?>

<div>
    <h1>Đăng nhập</h1>

    <?php if ($notice !== ''): ?>
        <!-- Thông báo thành công (đã nằm trong white list nên an toàn) -->
        <div><?php echo htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <br>

    <!-- method="POST": mật khẩu nằm trong thân request, KHÔNG hiện trên thanh URL -->
    <form action="" method="POST" novalidate>

        <div>
            <label for="username">Tên đăng nhập</label>
            <!-- in lại tên đăng nhập đã gõ (giữ giá trị khi lỗi) -->
            <input type="text"
                   id="username"
                   name="username"
                   maxlength="50"
                   autocomplete="username"
                   value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="VD: admin">
        </div>

        <div>
            <label for="password">Mật khẩu</label>
            <!--
                type="password": chữ bị che bằng chấm/tròn.
                KHÔNG có value: không bao giờ hiển thị lại mật khẩu,
                kể cả khi đăng nhập thất bại.
            -->
            <input type="password"
                   id="password"
                   name="password"
                   autocomplete="current-password"
                   placeholder="Nhập mật khẩu">
        </div>

        <div>
            <button type="submit">Đăng nhập</button>
        </div>
    </form>

    <br>

    <?php if (!empty($errors)): ?>
        <!-- Thông báo lỗi: mỗi dòng qua htmlspecialchars (chống XSS) -->
        <div>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <p>
        <!-- Nút điều hướng sang trang đăng ký – dùng <button> thay cho thẻ <a> -->
        Chưa có tài khoản?
        <button type="button" onclick="window.location.href='register.php'"><strong>Đăng ký ngay</strong></button>
    </p>
</div>

<?php
// Đóng trang (KHÔNG gọi header('Location') sau dòng này).
require_once __DIR__ . '/view/footer.php';
