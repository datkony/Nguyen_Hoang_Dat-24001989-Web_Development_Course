<?php
/**
 * ============================================================================
 * FILE: product_list.php   (YÊU CẦU III.3 – Danh sách sản phẩm)
 * ----------------------------------------------------------------------------
 * MỤC ĐÍCH: Hiển thị bảng danh sách toàn bộ sản phẩm với các cột
 *   ID | Tên sản phẩm | Giá | Số lượng | Chức năng ([Sửa] [Xóa])
 *
 * THỨ TỰ CÁC BƯỚC TRONG FILE NÀY RẤT QUAN TRỌNG:
 *   1) Kiểm tra đăng nhập  (chưa đăng nhập -> login.php, exit – chưa có HTML nào)
 *   2) Lấy dữ liệu từ model
 *   3) Xử lý mọi header('Location: ...')   (phải TRƯỚC lần xuất HTML đầu tiên)
 *   4) Mới được nhúng view/header.php và in HTML
 *
 * LỖI CŨ ĐÃ SỬA (xem chú thích tại chỗ bên dưới):
 *   - require product_delete.php ngay đầu file -> file này thấy $_GET['id']
 *     rỗng nên header('Location: product_list.php'); exit(); -> TRANG DANH SÁCH
 *     tự redirect CHÍNH NÓ rồi thoát => danh sách không bao giờ hiển thị.
 *   - $id = $_GET['id'] thiếu kiểm tra (undefined key / chuỗi 'abc').
 *   - Chức năng Xóa xử lý qua GET => xóa không qua bước xác nhận.
 *   - Thiếu htmlspecialchars ở một số chỗ -> XSS.
 *   - require __DIR__ .'view/footer.php' (thiếu dấu /) -> Fatal error.
 * ============================================================================
 */

// ----- BƯỚC 1: KIỂM TRA ĐĂNG NHẬP -----------------------------------------
// Nhúng file hàm phiên; nếu chưa đăng nhập thì requireLogin() chuyển hướng
// về login.php và exit() NGAY TẠI ĐÂY (chưa in ra byte HTML nào nên header hoạt động).
require_once __DIR__ . '/common/auth.php';
requireLogin(); // YÊU CẦU: chỉ người đã đăng nhập mới được vào trang quản lý

// ----- BƯỚC 2: LẤY DỮ LIỆU TỪ MODEL ---------------------------------------
// require_once nhúng file class ProductModel; bên trong nó tự nhúng dbConnect.php
// (đường dẫn tuyệt đối __DIR__ -> không bị sai thư mục làm việc).
require_once __DIR__ . '/model/product.php';

// Tạo đối tượng model, truyền kết nối $conn do dbConnect.php tạo ra.
$productModel = new ProductModel($conn);

// Lớp dự phòng: đảm bảo bảng `products` tồn tại và có 5 bản ghi mẫu
// (bỏ qua nếu file database/shopping_cart.sql đã tạo sẵn).
$productModel->initTable();

// Lấy toàn bộ sản phẩm: trả về mảng 2 chiều.
$products = $productModel->getAllProducts();

// ----- BƯỚC 3: XỬ LÝ THÔNG BÁO SAO KHI THAO TÁC THÀNH CÁNG ----------------
// Các trang thêm/sửa/xóa sẽ redirect về đây kèm ?status=xxx.
// Dùng WHITE LIST (mảng cho phép trước) thay vì echo thẳng $_GET['status']:
// tránh bị lợi dụng chèn <script> vào URL (XSS) và tránh hiện thông báo rác.
$allowedStatusMessages = [
    'added'   => 'Thêm sản phẩm thành công.',
    'updated' => 'Cập nhật sản phẩm thành công.',
    'deleted' => 'Xóa sản phẩm thành công.',
];

// Lấy tham số status từ URL; chuỗi rỗng nếu không có (?? tránh warning).
$status = $_GET['status'] ?? '';

// Chỉ hiển thị khi status nằm trong white list; ngược lại là chuỗi rỗng.
$statusMessage = $allowedStatusMessages[$status] ?? '';

/*
 * LỖI CŨ (xem lại để biết vì sao phải bỏ):
 *   - function goToEditPage(int $id) nhận $_GET['id'] = 'abc' -> TypeError Fatal
 *     => nay KHÔNG còn chuyển hướng gián tiếp qua ?action=edit/&action=remove.
 *   - $id = $_GET['id'] ?? 1 => ?action=remove không kèm id sẽ XÓA SẢN PHẨM ID 1
 *     => nay không còn đọc $_GET['id'] ở trang này nữa.
 *   - require __DIR__ .'/product_delete.php' => trang xác nhận xóa tự redirect
 *     và exit() ngay giữa trang danh sách => danh sách hiển thị trắng/loop.
 * GIẢI PHÁP: mỗi nút [Sửa]/[Xóa] trỏ thẳng tới trang đích với id đã hợp lệ.
 */

// ----- BƯỚC 4: XUẤT GIAO DIỆN (lần xuất HTML đầu tiên) --------------------
require_once __DIR__ . '/view/header.php';
?>

<h1>Danh sách sản phẩm</h1>

<!--
    Bảng liệt kê sản phẩm – yêu cầu IV: dùng thẻ <table>.
-->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên sản phẩm</th>
            <th>Giá</th>
            <th>Số lượng</th>
            <th>Chức năng</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($products)): ?>
        <!-- Trạng thái rỗng: báo cho người dùng biết (thay vì một bảng trống khó hiểu) -->
        <tr>
            <td colspan="5">Chưa có sản phẩm nào. Hãy thêm sản phẩm mới.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <tr>
                <!--
                    Mỗi ô dữ liệu đều qua htmlspecialchars(..., ENT_QUOTES, 'UTF-8'):
                    chuyển <, >, &, " ' thành thực thể HTML nên dữ liệu chỉ hiển thị
                    dưới dạng CHỮ, không thể thực thi thành script (chống XSS).
                -->
                <td><?php echo htmlspecialchars((string) $product['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo number_format((float) $product['price'], 0, ',', '.') . ' đ'; ?></td>
                <td><?php echo htmlspecialchars((string) $product['quantity'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                    <!--
                        [Sửa] và [Xóa] là các NÚT ĐIỀU HƯỚNG nên viết bằng thẻ
                        <button type="button"> (KHÔNG dùng thẻ <a>):
                        - type="button" rất quan trọng: nếu thiếu thì <button>
                          mặc định là type="submit"; nếu ô này nằm trong form
                          nào đó thì bấm vào sẽ gửi form thay vì chuyển trang.
                        - onclick="window.location.href=..." thay cho href.
                        - id lấy từ CƠ SỞ DỮ LIỆU (đã là số nguyên) và lại ép (int)
                          thêm một lần nữa => không thể chèn chuỗi lạ vào URL.
                    -->
                    <button type="button"
                            onclick="window.location.href='product_edit.php?id=<?php echo (int) $product['id']; ?>'">
                        Sửa
                    </button>

                    <!--
                        [Xóa] KHÔNG xóa ngay: chỉ đưa người dùng đến trang
                        product_delete.php để XÁC NHẬN trước (yêu cầu III.6).
                        Hành động xóa chỉ xảy ra khi trang đó nhận được POST xác nhận.
                    -->
                    <button type="button"
                            onclick="window.location.href='product_delete.php?id=<?php echo (int) $product['id']; ?>'">
                        Xóa
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<p>
    <!--
        Nút thêm mới – dùng thẻ <button> (KHÔNG dùng thẻ <a>).
        type="button": thuần điều hướng, không thuộc form nào nên không gửi request.
        onclick: thay cho href của thẻ <a> cũ.
    -->
    <button type="button" onclick="window.location.href='product_add.php'">+ Thêm sản phẩm mới</button>
</p>

<?php if ($statusMessage !== ''): ?>
    <!--
        Thông báo thành công sau thao tác thêm/sửa/xóa.
        htmlspecialchars(): mọi dữ liệu in ra màn hình đều phải được "chống XSS"
        (dữ liệu này đã nằm trong white list nên càng an toàn).
    -->
    <div>
        <?php echo htmlspecialchars($statusMessage, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<?php
// ----- BƯỚC 5: ĐÓNG TRANG --------------------------------------------------
// Nhúng footer (KHÔNG THỂ gọi header('Location') sau dòng này).
// LỖI CŨ: thiếu ký tự '/' (app + view -> 'appview/footer.php') -> Fatal error.
require_once __DIR__ . '/view/footer.php';
?>