<?php
// ============================================================================
// TỆP CẤU HÌNH KẾT NỐI CƠ SỞ DỮ LIỆU
// ----------------------------------------------------------------------------
// Tệp này chứa thông tin kết nối MySQL và hàm tạo đối tượng PDO dùng chung.
// Tách riêng cấu hình giúp API dễ bảo trì: khi đổi máy chủ hoặc mật khẩu,
// chỉ cần sửa ở một nơi duy nhất thay vì sửa trong nhiều tệp API.
// ============================================================================

// Bật chế độ kiểm tra kiểu nghiêm ngặt cho các hàm trong tệp này.
// Nhờ đó, nếu truyền sai kiểu dữ liệu, PHP sẽ báo lỗi thay vì tự ép kiểu ngầm.
declare(strict_types=1);

/**
 * Đọc một giá trị cấu hình từ biến môi trường.
 * Nếu biến môi trường không tồn tại hoặc rỗng, hàm trả về giá trị mặc định.
 * Cách này cho phép chạy thử với cơ sở dữ liệu ở cổng khác mà không sửa mã nguồn.
 *
 * @param string $tenBien   Tên biến môi trường cần đọc.
 * @param string $macDinh  Giá trị dùng khi không tìm thấy biến môi trường.
 * @return string          Giá trị cấu hình cuối cùng.
 */
function docCauHinh(string $tenBien, string $macDinh): string
{
    // Đọc giá trị của biến môi trường theo tên được truyền vào.
    $giaTri = getenv($tenBien);

    // Nếu biến không tồn tại (false) hoặc là chuỗi rỗng thì dùng giá trị mặc định.
    if ($giaTri === false || $giaTri === '') {
        // Trả về giá trị mặc định đã khai báo.
        return $macDinh;
    }

    // Trả về giá trị thực tế đọc được từ biến môi trường.
    return $giaTri;
}

// Khai báo địa chỉ máy chủ MySQL; mặc định là máy cục bộ 127.0.0.1.
// Có thể ghi đè bằng biến môi trường DB_HOST khi cần kết nối máy chủ khác.
define('DB_HOST', docCauHinh('DB_HOST', '127.0.0.1'));

// Khai báo cổng MySQL; XAMPP mặc định dùng cổng 3306.
// Có thể ghi đè bằng biến môi trường DB_PORT khi cần chạy thử ở cổng khác.
define('DB_PORT', docCauHinh('DB_PORT', '3306'));

// Khai báo tên cơ sở dữ liệu đã được tạo bởi tệp database.sql.
// Có thể ghi đè bằng biến môi trường DB_NAME nếu dùng tên cơ sở dữ liệu khác.
define('DB_NAME', docCauHinh('DB_NAME', 'quanly_sanpham'));

// Khai báo tài khoản đăng nhập MySQL; XAMPP mặc định dùng tài khoản root.
// Có thể ghi đè bằng biến môi trường DB_USER trên máy chủ có tài khoản riêng.
define('DB_USER', docCauHinh('DB_USER', 'root'));

// Khai báo mật khẩu MySQL; XAMPP mặc định để trống cho tài khoản root.
// Có thể ghi đè bằng biến môi trường DB_PASS nếu tài khoản có mật khẩu.
define('DB_PASS', docCauHinh('DB_PASS', ''));

// Khai báo bảng mã kết nối; utf8mb4 bảo đảm lưu và đọc đúng tiếng Việt có dấu.
// Có thể ghi đè bằng biến môi trường DB_CHARSET khi cần dùng bảng mã khác.
define('DB_CHARSET', docCauHinh('DB_CHARSET', 'utf8mb4'));

/**
 * Tạo và trả về một đối tượng PDO đã kết nối tới cơ sở dữ liệu MySQL.
 * Hàm này được các API gọi mỗi khi cần thao tác với cơ sở dữ liệu.
 *
 * @return PDO Đối tượng PDO sẵn sàng thực hiện truy vấn.
 * @throws PDOException Khi không thể kết nối hoặc cấu hình PDO thất bại.
 */
function taoKetNoiPDO(): PDO
{
    // Xây dựng chuỗi DSN (Data Source Name) mô tả loại cơ sở dữ liệu và vị trí.
    // sprintf ghép các hằng số cấu hình vào đúng vị trí của chuỗi mẫu.
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    // Khai báo các tùy chọn hành vi cho kết nối PDO.
    $tuyChon = [
        // Khi có lỗi SQL, PDO ném ngoại lệ để API bắt và trả về JSON thay vì lỗi HTML.
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

        // Mặc định mỗi dòng dữ liệu được trả về dưới dạng mảng kết hợp (tên cột => giá trị).
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

        // Tắt chế độ giả lập prepared statement để MySQL xử lý tham số thật sự.
        // Điều này tăng tính an toàn và giúp kiểu dữ liệu số được xử lý chính xác hơn.
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    // Khởi tạo đối tượng PDO với DSN, tài khoản, mật khẩu và các tùy chọn ở trên.
    // Nếu kết nối thất bại, PDOException sẽ được ném ra cho nơi gọi hàm xử lý.
    return new PDO($dsn, DB_USER, DB_PASS, $tuyChon);
}
