<?php
/**
 * ============================================================================
 * FILE: model/user.php   (YÊU CẦU V – Bảng users: đăng ký / đăng nhập)
 * ----------------------------------------------------------------------------
 * MỤC ĐÍCH: Đóng gói mọi thao tác CSDL với bảng `users`:
 *   - usernameExists($username) : kiểm tra tên đăng nhập đã tồn tại chưa (đăng ký)
 *   - create($username, $pass)  : tạo tài khoản, MÃ HÓA mật khẩu bằng password_hash()
 *   - verify($username, $pass)  : kiểm tra đăng nhập bằng password_verify()
 *   (+ initTable(): lớp dự phòng tự tạo bảng & nạp tài khoản mẫu – đồng nhất
 *    với model/product.php)
 *
 * ĐƯỜNG DẪN NHÚNG: cùng nguyên tắc với model/product.php – PHẢI dùng __DIR__
 * rồi đi ngược một cấp tới thư mục common/.
 * ============================================================================
 */

// Dùng chung file kết nối PDO (require_once -> không kết nối DB hai lần).
require_once __DIR__ . '/../common/dbConnect.php';

/**
 * Lớp UserModel – thao tác bảng users.
 */
class UserModel
{
    /** @var PDO Kết nối CSDL dùng chung */
    private PDO $conn;

    /**
     * Constructor: nhận kết nối PDO từ bên ngoài.
     * @param PDO $conn
     */
    public function __construct(PDO $conn)
    {
        // Lưu kết nối vào thuộc tính để các hàm sau dùng.
        $this->conn = $conn;
    }

    /**
     * ------------------------------------------------------------------------
     * initTable(): TẠO BẢNG (nếu chưa có) + NẠP DỮ LIỆU MẪU (nếu bảng rỗng).
     * ------------------------------------------------------------------------
     * ĐỒNG THỐI với ProductModel::initTable():
     *   + File database/shopping_cart.sql là NGUỒN CHÍNH (nộp bài).
     *   + initTable() chỉ là LỚP DỰ PHÒNG giúp ứng dụng vẫn chạy được trên
     *     máy XAMPP chưa import SQL.
     *   + Cả hai đều là thao tác ÝU (idempotent): CREATE TABLE IF NOT EXISTS
     *     không xóa dữ liệu cũ, seedData() chỉ chèn khi bảng TRỐNG.
     */
    public function initTable(): void
    {
        // Câu lệnh CREATE với IF NOT EXISTS: nếu bảng đã có thì PHP không làm gì cả.
        // UNIQUE ở username: ràng buộc trùng tên đăng nhập (yêu cầu mục V).
        $sql = "CREATE TABLE IF NOT EXISTS users (
                    id INT NOT NULL AUTO_INCREMENT,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    PRIMARY KEY (id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        // exec() (không có biến đầu vào) nhanh hơn prepare() cho DDL.
        $this->conn->exec($sql);

        // Sau khi chắc chắn bảng tồn tại -> mới nạp dữ liệu mẫu.
        $this->seedData();
    }

    /**
     * Nạp tài khoản mẫu khi bảng users hiện CHƯA CÓ bản ghi nào
     * (giống ProductModel::seedData() – không bao giờ chèn đè, không trùng).
     *
     * Dùng create() chứ KHÔNG chèn thẳng hash vào SQL như file .sql:
     *   + password_hash() sinh hash MỚI mỗi lần gọi (salt ngẫu nhiên) ->
     *     không cần hard-code chuỗi hash trong mã nguồn PHP.
     *   + MẬT KHẨU THUẦN không bao giờ chạm vào database.
     */
    private function seedData(): void
    {
        // Đếm số bản ghi hiện có: fetchColumn() trả về ô đầu tiên của dòng
        // đầu tiên kết quả (ở đây là COUNT(*)).
        $stmt = $this->conn->query("SELECT COUNT(*) FROM users");
        $count = (int) $stmt->fetchColumn(); // ép kiểu chuỗi "0" -> int 0

        // Đã có dữ liệu (do file SQL tạo ra hoặc do người dùng đăng ký) -> thoát.
        if ($count > 0) {
            return;
        }

        // Tài khoản mẫu: admin / admin123 (khớp với file database/shopping_cart.sql).
        // create() bên trong sẽ gọi password_hash() rồi mới INSERT.
        $this->create('admin', 'admin123');
    }

    /**
     * Kiểm tra tên đăng nhập đã tồn tại trong bảng users chưa.
     * YÊU CẦU đăng ký: "Kiểm tra tên đăng nhập chưa tồn tại trong database".
     *
     * @param  string $username tên đăng nhập (đã trim ở trang register.php)
     * @return bool             true = ĐÃ TỒN TẠI (không được đăng ký trùng)
     */
    public function usernameExists(string $username): bool
    {
        // Dùng placeholder -> an toàn, không nối chuỗi thẳng vào câu SQL.
        $sql = "SELECT COUNT(*) FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($sql);

        // Gán giá trị cho placeholder.
        $stmt->bindParam(":username", $username);

        // Thực thi truy vấn đếm.
        $stmt->execute();

        // fetchColumn(): trả về giá trị ô đầu tiên của dòng đầu tiên (= số đếm).
        // > 0 nghĩa là đã có ít nhất 1 dòng trùng username.
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Tạo tài khoản mới.
     * YÊU CẦU: "Sử dụng password_hash() để mã hóa mật khẩu trước khi lưu".
     *
     * @param  string $username tên đăng nhập
     * @param  string $password mật khẩu THUẦN (chưa mã hóa)
     * @return int              id vừa được AUTO_INCREMENT cấp phát
     * @throws Exception        nếu username trùng (INSERT vi phạm UNIQUE)
     */
    public function create(string $username, string $password): int
    {
        // ---------------------------------------------------------------------
        // password_hash($password, PASSWORD_DEFAULT):
        //   + Tạo chuỗi hash dạng "$2y$..." (bcrypt) KÈM SALT NGẪU NHIÊN.
        //   + Mỗi lần gọi cho KẾT QUẢ KHÁC NHAU (do salt ngẫu nhiên) -> không
        //     thể đoán ngược lại mật khẩu gốc.
        //   + Chuỗi hash dài hơn mật khẩu gốc -> cột password VARCHAR(255) là đủ.
        //   + KHÔNG BAO GIỜ lưu $password (văn bản thuần) vào database.
        // ---------------------------------------------------------------------
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Chuẩn bị INSERT với placeholder.
        $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
        $stmt = $this->conn->prepare($sql);

        // Gán username (chuỗi) và ĐÚNG CHUỖI HASH đã mã hóa.
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $passwordHash);

        // Thực thi – nếu username đã có (vi phạm UNIQUE) thì PDO ném PDOException.
        $stmt->execute();

        // lastInsertId(): trả về id mà MySQL vừa cấp phát cho dòng vừa chèn.
        return (int) $this->conn->lastInsertId();
    }

    /**
     * Kiểm tra thông tin đăng nhập.
     * YÊU CẦU: "Sử dụng password_verify() để kiểm tra mật khẩu".
     *
     * @param  string     $username tên đăng nhập người dùng gõ
     * @param  string     $password mật khẩu THUẦN người dùng gõ
     * @return array|null           mảng ['id','username'] nếu ĐÚNG,
     *                              null nếu sai tên đăng nhập HOẶC sai mật khẩu
     */
    public function verify(string $username, string $password): ?array
    {
        // BƯỚC 1: Lấy dòng dữ liệu của username này.
        $sql = "SELECT id, username, password FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        // fetch() trả về false nếu không tìm thấy username.
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Không có dòng nào -> sai tên đăng nhập -> trả về null.
        if ($user === false) {
            return null;
        }

        // BƯỚC 2: So sánh mật khẩu người dùng gõ với chuỗi hash LƯU TRONG DB.
        // password_verify($password, $hash):
        //   + Tự tách salt từ $hash ra rồi băm lại $password để so sánh.
        //   + Trả về true nếu khớp, false nếu không.
        //   + So sánh bằng.timing-safe -> không dò được mật khẩu qua thời gian.
        if (!password_verify($password, $user['password'])) {
            return null; // Mật khẩu sai
        }

        // BƯỚC 3: Đăng nhập hợp lệ -> trả về thông tin CẦN THIẾT cho phiên
        // (chỉ id và username – KHÔNG trả về / KHÔNG lưu hash mật khẩu vào session).
        return [
            'id'       => (int) $user['id'],   // ép về int cho chuẩn
            'username' => $user['username'],
        ];
    }
}
