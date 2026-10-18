<?php
/**
 * ============================================================================
 * FILE: product_add.php   (YÊU CẦU III.4 – Thêm sản phẩm)
 * ----------------------------------------------------------------------------
 * MỤC ĐÍCH: Form nhập Tên / Giá / Số lượng và kiểm tra dữ liệu trước khi ghi:
 *   - Tên không được rỗng            (bắt buộc theo đề bài)
 *   - Giá > 0                        (bắt buộc theo đề bài)
 *   - Số lượng >= 0                   (bắt buộc theo đề bài)
 *   + Bổ sung: tên tối đa 100 ký tự (khớp VARCHAR(100) của bảng)
 *
 * THỨ TỰ XỬ LÝ (quan trọng – tránh "headers already sent"):
 *   1) đăng nhập  2) validate + ghi CSDL + header('Location')  3) mới in HTML
 *
 * LỖI CŨ ĐÃ SỬA:
 *   - `empty($_POST['name'])` kiểm tra TRƯỚC khi cắt khoảng trắng => tên toàn
 *     khoảng trắng được coi là hợp lệ và lưu thành '' (vi phạm NOT NULL/ý nghĩa).
 *     => nay: trim() TRƯỚC rồi mới so sánh với ''.
 *   - In lỗi SAU form (ít thấy).
 * ============================================================================
 */

// ----- BƯỚC 1: KIỂM TRA ĐĂNG NHẬP -----------------------------------------
require_once __DIR__ . '/common/auth.php';
requireLogin(); // Chưa đăng nhập -> chuyển về login.php rồi exit

// ----- BƯỚC 2: NẠP MODEL ---------------------------------------------------
require_once __DIR__ . '/model/product.php';

// Tạo model dùng chung $conn; initTable() đảm bảo bảng products đã tồn tại.
$productModel = new ProductModel($conn);
$productModel->initTable();

// ----- BƯỚC 3: KHỞI TẠO BIẾN CHO FORM -------------------------------------
// Các biến này được in vào value="..." của ô nhập, nên khởi tạo rỗng để
// tránh cảnh báo "Undefined variable" khi trang được tải lần đầu (GET).
$name     = '';
$price    = '';
$quantity = '';

// Mảng gom TẤT CẢ thông báo lỗi để hiển thị 1 lần (trải nghiệm tốt hơn
// việc chỉ báo lỗi đầu tiên rồi dừng lại).
$errors = [];

// ----- BƯỚC 4: XỬ LÝ KHI NGƯỜI DÙNG ẤN NÚT SUBMIT (POST) ------------------
// $_SERVER['REQUEST_METHOD'] cho biết kiểu request; so sánh === để không bị
// ép kiểu ngầm (so sánh chuỗi 'POST').
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Lấy dữ liệu người dùng nhập; `?? ''` phòng khi ô không được gửi lên.
    $nameRaw     = $_POST['name'] ?? '';
    $priceRaw    = $_POST['price'] ?? '';
    $quantityRaw = $_POST['quantity'] ?? '';

    // ----- 4.1 KIỂM TRA TÊN SẢN PHẨM --------------------------------------
    // trim(): bỏ khoảng trắng đầu/cuối NGAY TẠI ĐÂY (sửa lỗi cũ: kiểm tra rỗng
    // trước khi trim nên "   " lọt qua, còn chop() chỉ cắt phía SAU nên
    // " abc " thành " abc" – lệch dữ liệu).
    $name = trim($nameRaw);

    if ($name === '') {
        // Chuỗi rỗng hoặc toàn khoảng trắng sau khi trim -> bắt buộc nhập lại.
        $errors[] = 'Tên sản phẩm không được để trống!';
    } elseif (mb_strlen($name) > 100) {
        // Không cho vượt VARCHAR(100): nếu để MySQL cắt ngầm thì dữ liệu
        // bị mất chữ mà người dùng không hề biết.
        $errors[] = 'Tên sản phẩm không được vượt quá 100 ký tự!';
    }

    // ----- 4.2 KIỂM TRA GIÁ ------------------------------------------------
    // filter_var + FILTER_VALIDATE_FLOAT:
    //   - Trả về (float) nếu là số hợp lệ, trả về false nếu không (vd: 'abc', '').
    //   - "1.5" -> 1.5 (hợp lệ), "0" -> 0.0 (đúng kiểu nhưng SAI điều kiện giá > 0).
    $priceValidated = filter_var($priceRaw, FILTER_VALIDATE_FLOAT);

    if ($priceValidated === false) {
        // Không phải số -> báo lỗi và GIỮ NGUYÊN chuỗi người dùng gõ
        // để hiển thị lại trong form (không mất dữ liệu khi lỗi).
        $errors[] = 'Giá sản phẩm không hợp lệ (phải là số)!';
        $price = $priceRaw;
    } elseif ($priceValidated <= 0) {
        // Là số nhưng <= 0 -> vi phạm yêu cầu "Giá > 0".
        $errors[] = 'Giá sản phẩm phải lớn hơn 0!';
        $price = $priceRaw;
    } else {
        // Hợp lệ -> dùng giá trị đã ép kiểu (float) cho các bước sau.
        $price = $priceValidated;
    }

    // ----- 4.3 KIỂM TRA SỐ LƯỢNG ------------------------------------------
    // FILTER_VALIDATE_INT: 'abc' -> false; '1.5' -> false (không phải số nguyên);
    // '5' -> 5; '-1' -> -1 (đúng kiểu nhưng sai điều kiện số lượng >= 0).
    $quantityValidated = filter_var($quantityRaw, FILTER_VALIDATE_INT);

    if ($quantityValidated === false) {
        $errors[] = 'Số lượng không hợp lệ (phải là số nguyên)!';
        $quantity = $quantityRaw; // giữ lại để hiển thị lại trên form
    } elseif ($quantityValidated < 0) {
        $errors[] = 'Số lượng phải lớn hơn hoặc bằng 0!';
        $quantity = $quantityRaw;
    } else {
        $quantity = $quantityValidated;
    }

    // ----- 4.4 GHI VÀO CSDL (chỉ khi KHÔNG CÓ LỖI) ------------------------
    // empty($errors): tất cả các kiểm tra phía trên đều đạt mới đi tiếp.
    if (empty($errors)) {
        try {
            // Gọi hàm model (III.2) – nội dung SQL nằm trong model, trang này
            // không tự nối chuỗi SQL => chống SQL Injection tập trung.
            $productModel->addProduct($name, (float) $price, (int) $quantity);

            // CHUYỂN HƯỚNG sau khi ghi thành công.
            // header() phải được gọi TRƯỚC lần xuất HTML đầu tiên (file này
            // chưa nhúng view/header.php nên vẫn an toàn).
            // exit(): ngăn code bên dưới tiếp tục in HTML làm "dính" trang.
            header('Location: product_list.php?status=added');
            exit();

        } catch (Throwable $e) {
            // catch (Throwable) để bắt CẢ Exception lẫn Error (vd: TypeError)
            // thay vì để chương trình chết trắng với Fatal error.
            $errors[] = 'Không thể thêm sản phẩm: ' . $e->getMessage();
        }
    }
}

// ----- BƯỚC 5: XUẤT GIAO DIỆN (sau mọi header('Location')) ----------------
require_once __DIR__ . '/view/header.php';
?>

<h1>Thêm sản phẩm mới</h1>



<div>
    <!--
        action="": form vẫn POST về CHÍNH trang này (giữ nguyên URL hiện tại).
        method="POST": dữ liệu nằm trong thân request, KHÔNG hiện trên thanh URL
        (phù hợp khi thay đổi dữ liệu; GET chỉ dùng để ĐỌC).
        novalidate: tắt cảnh báo mặc định của trình duyệt để MỌI lỗi đều do
        PHP hiển thị (phù hợp bài tập – nếu bỏ, trình duyệt chặn trước).
    -->
    <form action="" method="POST" novalidate>

        <!-- Ô NHẬP TÊN: value in lại giá trị đã nhập (GIỮ GIÁ trị KHI LỖI) -->
        <div>
            <label for="name">Tên sản phẩm *</label>
            <input type="text"
                   id="name"
                   name="name"
                   maxlength="100"
                   value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="VD: Bánh quy socola">
        </div>

        <!-- Ô NHẬP GIÁ: step="any" cho phép nhập số thập phân (vd: 12500.50) -->
        <div>
            <label for="price">Giá (VNĐ) *</label>
            <input type="number"
                   id="price"
                   name="price"
                   step="any"
                   min="0.01"
                   value="<?php echo htmlspecialchars((string) $price, ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="VD: 45000">
        </div>

        <!-- Ô NHẬP SỐ LƯỢNG: step="1" để trình duyệt chỉ cho số nguyên -->
        <div>
            <label for="quantity">Số lượng *</label>
            <input type="number"
                   id="quantity"
                   name="quantity"
                   step="1"
                   min="0"
                   value="<?php echo htmlspecialchars((string) $quantity, ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="VD: 30">
        </div>

        <div>
            <!-- Nút submit: bấm vào sẽ gửi POST về chính trang này -->
            <button type="submit">Lưu sản phẩm</button>

            <!--
                Hủy: quay về danh sách – dùng thẻ <button> (KHÔNG dùng thẻ <a>).
                type="button" là BẮT BUỘC: nếu để mặc định (submit) thì bấm Hủy
                sẽ GỬI FORM (POST dữ liệu) thay vì chỉ quay về danh sách.
            -->
            <button type="button" onclick="window.location.href='product_list.php'">Hủy</button>
        </div>
    </form>
</div>

<br><br><br>

<?php if (!empty($errors)): ?>
    <!--
        Mỗi dòng qua htmlspecialchars -> nội dung lỗi (có thể chứa thông điệp
        exception từ CSDL) không thể chèn script được (chống XSS).
    -->
    <div>
        <strong>Vui lòng kiểm tra lại các thông tin sau:</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php
// Đóng trang (KHÔNG gọi header('Location') sau dòng này).
require_once __DIR__ . '/view/footer.php';
?>