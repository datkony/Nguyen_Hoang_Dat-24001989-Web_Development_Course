<?php
/**
 * ============================================================================
 * FILE: product_delete.php   (YÊU CẦU III.6 – Xóa sản phẩm)
 * ----------------------------------------------------------------------------
 * MỤC ĐÍCH: Trang XÁC NHẬN xóa sản phẩm.
 *   + Bước 1 (GET):  hiển thị thông tin sản phẩm + nút "Xác nhận xóa".
 *   + Bước 2 (POST): chỉ khi người dùng bấm xác nhận mới thực sự xóa.
 *   + Sản phẩm không tồn tại / id sai -> hiển thị thông báo ngay TRÊN TRANG
 *     (không redirect làm mất thông báo).
 *
 * TẠI SAO PHẢI CÓ BƯỚC XÁC NHẬN?
 *   Phiên bản cũ xóa ngay bằng GET (product_delete.php?id=5) => chỉ cần ai đó
 *   gửi link hoặc trình duyệt/robot tự tải là sản phẩm "bốc hơi", không thể hoàn tác.
 *   Đề bài: "Trước khi xóa phải xác nhận". Kỹ thuật: POST = thao tác thay đổi dữ liệu.
 *
 * LỖI CŨ ĐÃ SỬA:
 *   - File này trước chỉ định nghĩa hàm removeItem() (không có HTML), truy cập
 *     trực tiếp ra trang trắng => nay là MỘT TRANG THẬT có giao diện xác nhận.
 *   - Thông báo "không tồn tại" từng bị header('Location') nuốt mất => nay in ra trang.
 *   - product_list.php từng `require` file này (gây vòng lặp redirect) => đã bỏ.
 * ============================================================================
 */

// ----- BƯỚC 1: KIỂM TRA ĐĂNG NHẬP -----------------------------------------
require_once __DIR__ . '/common/auth.php';
requireLogin(); // Chưa đăng nhập -> chuyển về login.php và DỪNG (chưa có HTML nào)

// ----- BƯỚC 2: NẠP MODEL ---------------------------------------------------
require_once __DIR__ . '/model/product.php';
$productModel = new ProductModel($conn);
$productModel->initTable(); // dự phòng: tạo bảng products nếu chưa import SQL

// ----- BƯỚC 3: ĐỌC VÀ KIỂM TRA ID -----------------------------------------
// Khi mở trang (GET): id nằm trên URL -> product_delete.php?id=5
// Khi bấm "Xác nhận" (POST): id nằm trong ô ẩn name="id" của form.
// ?? đọc từ TRÁI sang PHẢI: ưu tiên ô ẩn, thiếu thì lấy trên URL, không có -> ''.
$idRaw = $_POST['id'] ?? $_GET['id'] ?? '';

// Lọc id: 'abc', '', '1.5', thiếu id... đều trả về false -> KHÔNG truyền chuỗi
// vào getProductById(int $id) nên không còn nguy cơ TypeError (Fatal error).
$id = filter_var($idRaw, FILTER_VALIDATE_INT);

// Chỉ chấp nhận số nguyên dương; nếu không thì coi như "id không hợp lệ".
if ($id === false || $id <= 0) {
    $id = null;
}

// Đọc sản phẩm; id null => không gọi model, $product = null.
$product = ($id === null) ? null : $productModel->getProductById($id);

// Biến chứa thông báo lỗi (nếu thao tác xóa thất bại).
$error = '';

// ----- BƯỚC 4: XỬ LÝ XÁC NHẬN XÓA (chỉ khi POST + bấm nút xác nhận) -------
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['confirm_delete'])      // nút "Xác nhận" có name="confirm_delete"
    && $product !== null                    // CHỈ xóa khi sản phẩm còn tồn tại
) {
    try {
        // Gọi hàm model – bên trong có kiểm tra lại lần nữa + transaction.
        $productModel->deleteProduct($id);

        // Xóa thành công -> chuyển về danh sách kèm cờ trạng thái.
        // header() ở đây còn TRƯỚC mọi đoạn HTML (view/header.php nhúng ở BƯỚC 5)
        // nên không xảy ra lỗi "headers already sent".
        header('Location: product_list.php?status=deleted');
        exit();

    } catch (Throwable $e) {
        // Bắt mọi lỗi (Exception + Error) để hiển thị ngay trên trang xác nhận
        // thay vì để trang chết trắng.
        $error = 'Không thể xóa sản phẩm: ' . $e->getMessage();
    }
}

// ----- BƯỚC 5: XUẤT GIAO DIỆN ---------------------------------------------
require_once __DIR__ . '/view/header.php';
?>

<div>

    <?php if ($product === null): ?>
        <!--
            TRẠNG THÁI LỖI – yêu cầu III.6: "Nếu sản phẩm không tồn tại,
            hiển thị thông báo phù hợp".
            Lưu ý: KHÔNG dùng header('Location') ở đây, vì nếu redirect thì
            người dùng sẽ về danh sách mà KHÔNG BAO GIỜ thấy thông báo này.
        -->
        <h1>Không thể xóa sản phẩm</h1>
        <div>
            <p>
                <?php if ($id === null): ?>
                    ID sản phẩm không hợp lệ (thiếu hoặc không phải số nguyên dương).
                <?php else: ?>
                    Sản phẩm với ID <strong>#<?php echo (int) $id; ?></strong>
                    không tồn tại hoặc đã bị xóa trước đó.
                <?php endif; ?>
            </p>
        </div>
        <!--
            Nút quay lại danh sách – dùng thẻ <button type="button"> thay cho
            thẻ <a> (chỉ điều hướng, không gửi request).
        -->
        <button type="button" onclick="window.location.href='product_list.php'">Quay lại trang danh sách</button>

    <?php else: ?>
        <!-- NHÁNH CHÍNH: xác nhận xóa sản phẩm còn tồn tại -->
        <h2>Xác nhận xóa sản phẩm</h2>

        <?php if ($error !== ''): ?>
            <!-- Thông báo lỗi từ model (đã chống XSS) -->
            <div>
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <!--
            Hiển thị rõ sản phẩm sắp xóa: tên + id + giá + số lượng
            để người dùng KHÔNG lỡ tay xóa nhầm.
            Tất cả dữ liệu đều qua htmlspecialchars (chống XSS).
        -->
        <p>
            Bạn có chắc chắn muốn xóa sản phẩm<br>
            <strong><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
            (ID: #<?php echo (int) $product['id']; ?>,
            giá: <?php echo number_format((float) $product['price'], 0, ',', '.') . ' đ'; ?>,
            số lượng: <?php echo (int) $product['quantity']; ?>)?
        </p>
        <p>Hành động này không thể hoàn tác!</p>

        <!--
            Form XÁC NHẬN:
            + method="POST": việc xóa CHỈ xảy ra sau khi người dùng chủ động bấm nút
              (không thể xóa bằng cách tải link GET như phiên bản cũ).
            + action="": POST về chính trang này -> nhánh BƯỚC 4 xử lý.
            + ô ẩn name="id": mang id của sản phẩm đang hiển thị.
        -->
        <form action="" method="POST">
            <input type="hidden" name="id" value="<?php echo (int) $product['id']; ?>">

            <!-- name="confirm_delete": cờ để BƯỚC 4 nhận ra đây là lần xác nhận -->
            <button type="submit"
                    name="confirm_delete"
                    value="1">
                Có, xóa sản phẩm này
            </button>

            <!--
                Hủy bỏ: quay về danh sách, KHÔNG xóa gì cả.
                Dùng thẻ <button type="button"> thay cho thẻ <a>; type="button"
                là bắt buộc (mặc định của <button> là submit -> nếu thiếu, bấm
                "Hủy bỏ" sẽ gửi form và vô tình ghi dữ liệu).
            -->
            <button type="button" onclick="window.location.href='product_list.php'">Hủy bỏ</button>
        </form>
    <?php endif; ?>

</div>

<?php
// Đóng trang (KHÔNG gọi header('Location') sau dòng này).
require_once __DIR__ . '/view/footer.php';
?>