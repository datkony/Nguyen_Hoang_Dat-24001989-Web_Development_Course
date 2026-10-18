<?php
/**
 * ============================================================================
 * FILE: common/dbConnect.php   (YÊU CẦU III.1 – Kết nối Database)
 * ----------------------------------------------------------------------------
 * MỤC ĐÍCH:
 *   Tạo MỘT KẾT NỐI DUY NHẤT tới MySQL qua PDO rồi gán vào biến $conn.
 *   Các file khác chỉ cần `require_once` file này là dùng chung được kết nối.
 *
 * CÁCH SỬ DỤNG (trong model hoặc trang PHP):
 *   require_once __DIR__ . '/../common/dbConnect.php';   // từ thư mục con
 *   require_once __DIR__ . '/common/dbConnect.php';      // từ thư mục app/
 *   ... dùng $conn ...
 *
 * LƯU Ý VỀ ĐƯỜNG DẪN:
 *   PHẢI dùng __DIR__ (đường dẫn tuyệt đối của file đang chạy) thay vì đường
 *   dẫn tương đối như `require 'common/dbConnect.php'`. Đường dẫn tương đối
 *   được PHP giải nghĩa theo THƯ MỤC LÀM VIỆC CỦA SCRIPT GỐC (ví dụ trang
 *   product_list.php nằm ở app/), nên nó rất dễ sai khi file được nhúng từ
 *   thư mục khác -> Fatal error: Failed opening required ...
 * ============================================================================
 */

// ----- Thông tin kết nối -----------------------------------------------------
$host     = 'localhost';   // MySQL của XAMPP chạy trên localhost
$dbname   = 'shopping_cart'; // Tên database (YÊU CẦU II) – phải import file database/shopping_cart.sql trước
$username = 'root';        // Tài khoản mặc định của XAMPP
$password = '';            // Mật khẩu mặc định của XAMPP là chuỗi rỗng

try {
    // DSN (Data Source Name): khai báo loại driver, host, database và bộ mã hóa.
    // `charset=utf8mb4` để tiếng Việt có dấu không bị lỗi khi đọc/ghi.
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

    // new PDO() sẽ NÉM PDOException nếu không kết nối được (sai host, sai
    // tài khoản, MySQL chưa chạy, database chưa được tạo...).
    $conn = new PDO($dsn, $username, $password);

    // ERRMODE_EXCEPTION: để các câu lệnh SQL sai (vd: bảng không tồn tại)
    // ném exception thay vì in cảnh báo rồi trả về false âm thầm.
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // FETCH_ASSOC: fetch()/fetchAll() trả về mảng khóa-tên cột (vd: $row['name'])
    // thay vì mảng chỉ số số thứ tự, giúp code dễ đọc hơn.
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // ATTR_EMULATE_PREPARES = false: dùng prepared statement THẬT của MySQL,
    // cơ chế chống SQL Injection an toàn và chính xác kiểu dữ liệu hơn.
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    // ---------------------------------------------------------------------------
    // XỬ LÝ KHI KẾT NỐI THẤT BẠI  (sửa lỗi cũ: "echo rồi chạy tiếp")
    // ---------------------------------------------------------------------------
    // Lỗi cũ chỉ echo thông báo rồi vẫn để $conn = null chạy tiếp => các file
    // model gọi $conn->prepare() => Fatal error: Call to a member function
    // prepare() on null. Vì vậy ở đây PHẢI dừng chương trình lại.
    //
    // 1) Ghi log chi tiết (chứa user/host) vào server để mình khi sửa lỗi,
    //    KHÔNG hiển thị cho người dùng vì lộ thông tin nhạy cảm.
    error_log('[DB CONNECT FAIL] ' . $e->getMessage());

    // 2) Đặt mã trạng thái HTTP 500 (Internal Server Error) cho trang trả về.
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <title>Lỗi kết nối cơ sở dữ liệu</title>
        <!-- Không dùng CSS (yêu cầu bài nộp chỉ có HTML cơ bản) -->
    </head>
    <body>
        <div>
            <h2>Không thể kết nối đến cơ sở dữ liệu</h2>
            <p>Hệ thống đang gặp sự cố khi kết nối MySQL. Vui lòng kiểm tra:</p>
            <ol>
                <li>MySQL đã được khởi động trong XAMPP Control Panel chưa?</li>
                <li>Đã import file <code>database/shopping_cart.sql</code> để tạo database
                    <code>shopping_cart</code> chưa?</li>
                <li>Thông tin tài khoản trong <code>common/dbConnect.php</code> có đúng không?</li>
            </ol>
            <p><em>Chi tiết kỹ thuật đã được ghi vào log của server (PHP error log).</em></p>
        </div>
    </body>
    </html>
    <?php
    // 3) DỪNG CHƯƠNG TRÌNH NGAY TẠI ĐÂY.
    //    Đây là câu lệnh quan trọng nhất của khối catch: nó đảm bảo $conn
    //    KHÔNG BAO GIỜ bị null rồi tiếp tục chạy xuống các file phía sau.
    exit();
}
?>