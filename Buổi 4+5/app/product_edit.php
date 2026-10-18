<?php
/**
 * ============================================================================
 * FILE: product_edit.php   (YÊU CẦU III.5 – Sửa sản phẩm)
 * ----------------------------------------------------------------------------
 * MỤC ĐÍCH:
 *   - Nhận id từ URL, KIỂM TRA id hợp lệ + kiểm tra sản phẩm CÓ TỒN TẠI không.
 *   - Hiển thị form với THÔNG TIN HIỆN TẠI của sản phẩm (đề bài yêu cầu).
 *   - Kiểm tra dữ liệu mới (tên/không rỗng, giá > 0, số lượng >= 0) rồi cập nhật.
 *
 * THỨ TỰ XỬ LÝ:  1) đăng nhập  2) validate id + đọc DB  3) xử lý POST &
 *                 header('Location')  4) in HTML (nhúng view/header.php)
 *
 * LỖI CŨ ĐÃ SỬA (nguyên nhân Fatal error TypeError):
 *   - $id = $_GET['id'] ?? null rồi truyền thẳng vào getProductById(int $id):
 *       + Thiếu id  -> null      -> TypeError: null must be of type int
 *       + id='abc'  -> chuỗi 'abc' -> TypeError: must be of type int, string given
 *     => nay: lọc qua filter_var(FILTER_VALIDATE_INT) và từ chối mọi id không
 *        phải số nguyên dương TRƯỚC khi chạm vào model.
 *   - Điều kiện cũ `if ($id != null || !is_int($id))` dùng || sai nghĩa
 *     (id='abc' vẫn đi vào nhánh gọi model) -> đã bỏ hẳn, thay bằng kiểm tra tường minh.
 * ============================================================================
 */

// ----- BƯỚC 1: KIỂM TRA ĐĂNG NHẬP -----------------------------------------
require_once __DIR__ . '/common/auth.php';
requireLogin(); // Chưa đăng nhập -> login.php + exit (chưa có HTML nào)

// ----- BƯỚC 2: NẠP MODEL ---------------------------------------------------
require_once __DIR__ . '/model/product.php';
$productModel = new ProductModel($conn);
$productModel->initTable(); // đảm bảo bảng products tồn tại (dự phòng nếu chưa import SQL)

// ----- BƯỚC 3: ĐỌC VÀ KIỂM TRA ID TỪ URL ----------------------------------
// Lấy id từ query string; `?? ''` tránh cảnh báo "Undefined array key".
// Khi submit form (POST) thì id nằm trong ô ẩn name="id" -> đọc $_POST['id'].
// Ưu tiên ô ẩn, thiếu thì lấy trên URL; cả hai không có -> chuỗi rỗng.
// ?? (coalesce) đọc từ TRÁI sang PHẢI nên không bao giờ báo warning.
$idRaw = $_POST['id'] ?? $_GET['id'] ?? '';

// filter_var + FILTER_VALIDATE_INT:
//   - 'abc', '', '1.5', '12abc', '0x10' -> false   (KHÔNG phải số nguyên)
//   - '5' -> 5 ; '0' -> 0 ; '-3' -> -3             (đúng kiểu nhưng cần kiểm thêm)
// Đây là LÁ CHẮN CHỐNG TypeError: mọi giá trị vô lý đều bị loại từ rất sớm.
$id = filter_var($idRaw, FILTER_VALIDATE_INT);

// Chấp nhận duy nhất số nguyên DƯƠNG; còn lại (false, 0, số âm) -> coi như thiếu id.
if ($id === false || $id <= 0) {
    $id = null; // chuẩn hóa về null để các bước sau chỉ cần kiểm một kiểu
}

// Chỉ gọi model khi id hợp lệ; nếu id null thì KHÔNG chạm vào getProductById.
// $currentProduct === null nghĩa là "không tìm thấy / id sai".
$currentProduct = ($id === null) ? null : $productModel->getProductById($id);

// ----- BƯỚC 4: KHỞI TẠO BIẾN CHO FORM -------------------------------------
$name     = '';
$price    = '';
$quantity = '';
$errors   = [];

// ----- BƯỚC 5: XỬ LÝ SUBMIT (POST) ----------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
     * Nếu id sai hoặc sản phẩm KHÔNG TỒN TẠI thì form cũng không được render,
     * nên việc cập nhật là vô nghĩa -> bỏ qua toàn bộ xử lý.
     * (Trong nhánh HTML bên dưới sẽ hiển thị thông báo "không tồn tại".)
     */
    if ($currentProduct !== null) {

        // Đọc dữ liệu form (?? '' phòng thiếu khóa).
        $nameRaw     = $_POST['name'] ?? '';
        $priceRaw    = $_POST['price'] ?? '';
        $quantityRaw = $_POST['quantity'] ?? '';

        // --- 5.1 TÊN: trim TRƯỚC rồi mới kiểm rỗng (sửa lỗi cũ) ---
        $name = trim($nameRaw);
        if ($name === '') {
            $errors[] = 'Tên sản phẩm không được để trống!';
        } elseif (mb_strlen($name) > 100) {
            $errors[] = 'Tên sản phẩm không được vượt quá 100 ký tự!';
        }

        // --- 5.2 GIÁ: phải là số và > 0 ---
        $priceValidated = filter_var($priceRaw, FILTER_VALIDATE_FLOAT);
        if ($priceValidated === false) {
            $errors[] = 'Giá sản phẩm không hợp lệ (phải là số)!';
            $price = $priceRaw;       // giữ chuỗi người dùng vừa gõ để hiển thị lại
        } elseif ($priceValidated <= 0) {
            $errors[] = 'Giá sản phẩm phải lớn hơn 0!';
            $price = $priceRaw;
        } else {
            $price = $priceValidated;
        }

        // --- 5.3 SỐ LƯỢNG: phải là số nguyên và >= 0 ---
        $quantityValidated = filter_var($quantityRaw, FILTER_VALIDATE_INT);
        if ($quantityValidated === false) {
            $errors[] = 'Số lượng không hợp lệ (phải là số nguyên)!';
            $quantity = $quantityRaw;
        } elseif ($quantityValidated < 0) {
            $errors[] = 'Số lượng phải lớn hơn hoặc bằng 0!';
            $quantity = $quantityRaw;
        } else {
            $quantity = $quantityValidated;
        }

        // --- 5.4 CẬP NHẬT (chỉ khi không có lỗi) ---
        if (empty($errors)) {
            try {
                // $id tại đây chắc chắn là int > 0 -> không còn nguy cơ TypeError.
                $productModel->updateProduct($id, $name, (float) $price, (int) $quantity);

                // Chuyển hướng về danh sách kèm cờ "đã cập nhật".
                // header() chạy TRƯỚC mọi HTML -> không bị "headers already sent".
                header('Location: product_list.php?status=updated');
                exit();

            } catch (Throwable $e) {
                // Bắt cả Exception lẫn Error (vd: id bị xóa ngay lúc cập nhật
                // -> InvalidArgumentException cũng hiện ở đây).
                $errors[] = 'Không thể cập nhật sản phẩm: ' . $e->getMessage();
            }
        }

    } // end if ($currentProduct !== null)

} else {
    /*
     * TRẠNG THÁI GET (mở form lần đầu, CHƯA submit):
     * Điền THÔNG TIN HIỆN TẠI của sản phẩm vào form – đây là yêu cầu
     * "Hiển thị thông tin hiện tại của sản phẩm" trong đề bài.
     */
    if ($currentProduct !== null) {
        $name     = $currentProduct['name'];     // tên đang lưu trong DB
        $price    = $currentProduct['price'];    // ví dụ: "45000.00"
        $quantity = $currentProduct['quantity']; // ví dụ: 30
    }
    // Nếu là POST không hợp lệ thì KHÔNG vào nhánh này -> giá trị người dùng
    // vừa gõ vẫn nằm trong $name/$price/$quantity => form GIỮ NGUYÊN dữ liệu.
}

// ----- BƯỚC 6: XUẤT GIAO DIỆN ---------------------------------------------
require_once __DIR__ . '/view/header.php';
?>

<?php if ($currentProduct === null): ?>
    <!--
        NHÁNH LỖI: id không hợp lệ (thiếu / 'abc' / <= 0) HOẶC sản phẩm
        đã bị xóa. YÊU CẦU III.5 & III.6: phải hiện thông báo PHÙ HỢP thay vì
        redirect làm mất thông báo hay để trang trắng / Fatal error.
    -->
    <div>
        <h1>Không tìm thấy sản phẩm</h1>
        <div>
            <p>
                <?php if ($id === null): ?>
                    ID sản phẩm không hợp lệ. Vui lòng kiểm tra lại địa chỉ trang (tham số <code>id</code>).
                <?php else: ?>
                    Sản phẩm với ID <strong>#<?php echo (int) $id; ?></strong>
                    không tồn tại hoặc đã bị xóa khỏi hệ thống.
                <?php endif; ?>
            </p>
        </div>
        <!--
            Nút quay lại danh sách – dùng thẻ <button type="button"> thay cho
            thẻ <a>: chuyển trang qua onclick, không gửi request nào.
        -->
        <button type="button" onclick="window.location.href='product_list.php'">Quay lại trang danh sách</button>
    </div>

<?php else: ?>
    <!-- NHÁNH CHÍNH: hiển thị form sửa với dữ liệu hiện tại -->
    <h1>Sửa sản phẩm: <?php echo htmlspecialchars($currentProduct['name'], ENT_QUOTES, 'UTF-8'); ?></h1>

    <div>
        <!--
            action="": POST về chính trang này, THAM SỐ ?id=... TRÊN URL được giữ
            nguyên (trình duyệt không đổi query string khi action rỗng) -> PHP vẫn
            biết đang sửa sản phẩm nào. Để chắc chắn, có hidden input id bên dưới.
        -->
        <form action="" method="POST" novalidate>

            <!--
                id gửi kèm dưới dạng ô ẩn: nguồn id chính khi submit (đã đọc
                vào biến $id ở đầu file). Phòng khi trình duyệt không giữ query
                string trên URL khi người dùng sửa địa chỉ trang.
            -->
            <input type="hidden" name="id" value="<?php echo (int) $currentProduct['id']; ?>">

            <!-- Ô 1: TÊN – value = dữ liệu HIỆN TẠI (đã chống XSS) -->
            <div>
                <label for="name">Tên sản phẩm *</label>
                <input type="text"
                       id="name"
                       name="name"
                       maxlength="100"
                       value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <!-- Ô 2: GIÁ – hiện giá hiện tại, step="any" cho phép số thập phân -->
            <div>
                <label for="price">Giá (VNĐ) *</label>
                <input type="number"
                       id="price"
                       name="price"
                       step="any"
                       min="0.01"
                       value="<?php echo htmlspecialchars((string) $price, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <!-- Ô 3: SỐ LƯỢNG – hiện số lượng hiện tại -->
            <div>
                <label for="quantity">Số lượng *</label>
                <input type="number"
                       id="quantity"
                       name="quantity"
                       step="1"
                       min="0"
                       value="<?php echo htmlspecialchars((string) $quantity, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div>
                <!-- Gửi form -> PHP validate -> updateProduct() -->
                <button type="submit">Lưu thay đổi</button>

                <!--
                    Hủy -> về danh sách: thẻ <button type="button"> thay cho <a>.
                    type="button" để KHÔNG gửi form (mặc định của <button> là submit).
                -->
                <button type="button" onclick="window.location.href='product_list.php'">Hủy</button>
            </div>
        </form>
    </div>

    <br><br><br>

    <?php if (!empty($errors)): ?>
        <!-- Danh sách lỗi (đã htmlspecialchars) -->
        <div>
            <strong>Chưa thể cập nhật, vui lòng kiểm tra:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php
// Đóng trang (KHÔNG gọi header('Location') sau dòng này).
require_once __DIR__ . '/view/footer.php';
?>