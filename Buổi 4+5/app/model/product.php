<?php
/**
 * ============================================================================
 * FILE: model/product.php   (YÊU CẦU III.2 – Model Product)
 * ----------------------------------------------------------------------------
 * MỤC ĐÍCH: Đóng gói MỌI thao tác CSDL liên quan đến bảng `products`
 * (CRUD) thành các hàm dùng lại. Trang PHP chỉ GỌI hàm, không tự viết SQL.
 *
 * Các hàm theo đúng yêu cầu đề bài:
 *   - getAllProducts()                          -> lấy toàn bộ sản phẩm
 *   - getProductById($id)                       -> lấy 1 sản phẩm theo id
 *   - addProduct($name, $price, $quantity)      -> thêm sản phẩm
 *   - updateProduct($id, $name, $price, $qty)   -> cập nhật sản phẩm
 *   - deleteProduct($id)                        -> xóa sản phẩm
 *   (+ initTable(): lớp dự phòng tự tạo bảng & nạp 5 bản ghi mẫu)
 *
 * ĐƯỜNG DẪN NHÚNG (sửa lỗi cũ: require_once 'common/dbConnect.php'):
 *   __DIR__ = thư mục chứa file model/product.php (app/model).
 *   Vì vậy PHẢI đi ngược lên một cấp: __DIR__ . '/../common/dbConnect.php'.
 *   Nếu viết 'common/dbConnect.php' (tương đối) thì PHP tìm trong thư mục
 *   làm việc của script gốc -> không thấy -> Fatal error: Failed opening required.
 * ============================================================================
 */

// require_once: nếu file đã được nhúng ở chỗ khác rồi thì PHP sẽ BỎ QUA,
// tránh "Cannot redeclare ..." và tránh kết nối MySQL 2 lần không cần thiết.
require_once __DIR__ . '/../common/dbConnect.php';

/**
 * Lớp ProductModel: nhận đối tượng PDO qua constructor (kỹ thuật Dependency
 * Injection) để mọi hàm đều dùng CHUNG một kết nối đã được khởi tạo sẵn.
 */
class ProductModel
{
    /** @var PDO Kết nối CSDL – khai báo kiểu tường minh (typed property) */
    private PDO $conn;

    /**
     * Constructor: nhận $conn (biến toàn cục do dbConnect.php tạo ra).
     * @param PDO $conn
     */
    public function __construct(PDO $conn)
    {
        // Gán kết nối vào biến của lớp để các hàm phía sau dùng chung.
        $this->conn = $conn;
    }

    /**
     * ------------------------------------------------------------------------
     * initTable(): TẠO BẢNG (nếu chưa có) + NẠP DỮ LIỆU MẪU (nếu bảng rỗng).
     * ------------------------------------------------------------------------
     * VÌ SAO VẪN GIỮ HÀM NÀY DÙ CÓ FILE SQL?
     *   + File database/shopping_cart.sql là NGUỒN CHÍNH (nộp bài).
     *   + initTable() chỉ là LỚP DỰ PHÒNG giúp ứng dụng vẫn chạy được trên
     *     máy XAMPP chưa import SQL.
     *   + Cả hai đều là thao tác ÝU (idempotent): chạy bao nhiêu lần cũng cho
     *     cùng một kết quả – CREATE TABLE IF NOT EXISTS không xóa dữ liệu cũ,
     *     seedData() chỉ chèn khi bảng TRỐNG.
     */
    public function initTable(): void
    {
        // Câu lệnh CREATE với IF NOT EXISTS: nếu bảng đã có thì PHP không làm gì cả.
        // Dùng exec() (không có biến đầu vào) nhanh hơn prepare().
        $sql = "CREATE TABLE IF NOT EXISTS products (
                    id INT NOT NULL AUTO_INCREMENT,
                    name VARCHAR(100) NOT NULL,
                    price DECIMAL(10,2) NOT NULL,
                    quantity INT NOT NULL,
                    PRIMARY KEY (id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        // exec() trả về số dòng bị ảnh hưởng; với DDL thường là 0.
        $this->conn->exec($sql);

        // Sau khi chắc chắn bảng tồn tại -> mới nạp dữ liệu mẫu.
        $this->seedData();
    }

    /**
     * Nạp 5 sản phẩm mẫu (YÊU CẦU II: "Thêm tối thiểu 5 sản phẩm").
     * Chỉ chèn khi bảng hiện CHƯA CÓ bản ghi nào -> không bao giờ bị trùng.
     */
    private function seedData(): void
    {
        // Đếm số bản ghi hiện có: fetchColumn() trả về giá trị của ô đầu tiên
        // trong dòng đầu tiên của kết quả (ở đây là số đếm COUNT(*)).
        $stmt = $this->conn->query("SELECT COUNT(*) FROM products");
        $count = (int) $stmt->fetchColumn(); // ép kiểu chuỗi "0" -> int 0

        // Nếu đã có dữ liệu (do file SQL tạo ra hoặc do người dùng thêm) -> thoát,
        // KHÔNG chèn đè nữa.
        if ($count > 0) {
            return;
        }

        // 5 sản phẩm mẫu. (LỖI CŨ: chỉ có 1 dòng và còn thừa dấu phẩy cuối
        // "'Nam', 46000, 18)," -> SQL syntax error -> PDO ném exception -> Fatal.)
        $sql = "INSERT INTO products (name, price, quantity) VALUES
                    ('Bánh quy socola',  45000.00,  30),
                    ('Sữa tươi 1L',      32000.00,  50),
                    ('Trà xanh túi lọc', 58000.00,  20),
                    ('Nước ngọt 500ml',  12000.00, 100),
                    ('Mì ăn liền',        4500.00, 200)";

        // exec(): câu INSERT không có phần tử đặt chỗ (placeholder) nên chạy thẳng được.
        $this->conn->exec($sql);
    }

    /**
     * III.2 – getAllProducts(): lấy TOÀN BỘ danh sách sản phẩm.
     * @return array mảng 2 chiều: [ ['id'=>1,'name'=>'...', ...], ... ]
     */
    public function getAllProducts(): array
    {
        // SELECT * không có biến đầu vào -> query() là đủ (không cần prepare).
        $stmt = $this->conn->query("SELECT * FROM products ORDER BY id ASC");

        // fetchAll(PDO::FETCH_ASSOC): trả về TẤT CẢ dòng dưới dạng mảng khóa-tên cột.
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * III.2 – getProductById($id): lấy 1 sản phẩm theo id.
     * @param  int      $id  mã sản phẩm (PHẢI là số nguyên dương – caller đã kiểm)
     * @return array|null    mảng thông tin sản phẩm, null nếu KHÔNG TỒN TẠI
     */
    public function getProductById(int $id)
    {
        // Chuẩn bị câu lệnh có chỗ đặt chỗ (:id) -> THẮNG SQL Injection vì giá trị
        // thật sẽ được MySQL tách biệt với câu lệnh.
        $sql = "SELECT * FROM products WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        // bindParam với PDO::PARAM_INT: báo PDO rằng :id là số nguyên,
        // giúp MySQL so sánh đúng (không xảy ra so kiểu chuỗi vs số).
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        // Thực thi câu lệnh đã gắn giá trị.
        $stmt->execute();

        // fetch(): chỉ lấy 1 dòng. fetch() trả về false nếu KHÔNG có dòng nào.
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Trả về null (chuẩn hóa) thay vì false để caller kiểm tra dễ dàng:
        // if ($product === null) -> "không tồn tại".
        return $result ?: null;
    }

    /**
     * III.2 – addProduct($name, $price, $quantity): thêm sản phẩm mới.
     * @param  string $name     tên (đã được trang gọi trim + kiểm rỗng trước đó)
     * @param  float  $price    giá  (> 0)
     * @param  int    $quantity số lượng (>= 0)
     * @return bool             true nếu chèn thành công
     * @throws Exception        nếu SQL thất bại (đã rollback trước khi ném)
     */
    public function addProduct(string $name, float $price, int $quantity): bool
    {
        try {
            // beginTransaction(): gộp các câu lệnh thành một GIAO DỊCH – nếu có
            // lỗi ở giữa thì toàn bộ bị hoàn tác (không bị nửa vời).
            $this->conn->beginTransaction();

            // Câu INSERT có placeholder -> bắt buộc dùng prepare().
            $sql = "INSERT INTO products (name, price, quantity)
                    VALUES (:name, :price, :quantity)";
            $stmt = $this->conn->prepare($sql);

            // Gán giá trị thật cho từng placeholder (mặc định là kiểu string,
            // PHP sẽ ép theo kiểu đã khai báo trong hàm).
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":price", $price);
            $stmt->bindParam(":quantity", $quantity);

            // Chèn dữ liệu.
            $stmt->execute();

            // commit(): XÁC NHẬN ghi dữ liệu vĩnh viễn vào bảng.
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // inTransaction(): chỉ rollback khi đang có giao dịch mở
            // (tránh warning "There is no active transaction").
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack(); // HOÀN TÁC mọi thay đổi trong try
            }
            throw $e; // Ném lại lỗi để trang gọi (product_add.php) bắt và hiển thị
        }
    }

    /**
     * III.2 – updateProduct($id, $name, $price, $quantity): cập nhật sản phẩm.
     * @param  int    $id       mã sản phẩm cần sửa (phải tồn tại)
     * @param  string $name     tên mới
     * @param  float  $price    giá mới
     * @param  int    $quantity số lượng mới
     * @return bool
     * @throws InvalidArgumentException nếu id không tồn tại
     * @throws Exception               nếu SQL thất bại (đã rollback)
     */
    public function updateProduct(int $id, string $name, float $price, int $quantity): bool
    {
        // KIỂM TRA TỒN TẠI trước khi update: tránh update "im lặng" 0 dòng
        // (yêu cầu: "Nếu sản phẩm không tồn tại, hiển thị thông báo phù hợp").
        if ($this->getProductById($id) === null) {
            // InvalidArgumentException là lớp con của LogicException -> con của
            // Exception, nên trang gọi chỉ cần catch (Exception $e) là bắt được.
            throw new InvalidArgumentException("Sản phẩm với ID = $id không tồn tại.");
        }

        try {
            // Mở giao dịch cho câu UPDATE.
            $this->conn->beginTransaction();

            // UPDATE có WHERE để chỉ sửa đúng 1 dòng theo id.
            $sql = "UPDATE products
                    SET name = :name, price = :price, quantity = :quantity
                    WHERE id = :id";
            $stmt = $this->conn->prepare($sql);

            // Gán tham số: id gán dưới dạng PARAM_INT để so sánh số với số.
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":price", $price);
            $stmt->bindParam(":quantity", $quantity);

            // Thực thi UPDATE.
            $stmt->execute();

            // Ghi nhận thay đổi.
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // Hoàn tác nếu có lỗi giữa chừng.
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            // Ném lại cho trang gọi xử lý (hiển thị thông báo lỗi trên form).
            throw $e;
        }
    }

    /**
     * III.2 – deleteProduct($id): xóa sản phẩm theo id.
     * @param  int    $id mã sản phẩm cần xóa
     * @return bool
     * @throws InvalidArgumentException nếu id không tồn tại
     * @throws Exception               nếu SQL thất bại (đã rollback)
     */
    public function deleteProduct(int $id): bool
    {
        // Kiểm tra tồn tại trước – từ chối xóa sản phẩm không có thật.
        if ($this->getProductById($id) === null) {
            throw new InvalidArgumentException("Sản phẩm với ID = $id không tồn tại.");
        }

        try {
            // Mở giao dịch cho câu DELETE.
            $this->conn->beginTransaction();

            // DELETE theo id – có WHERE nên chỉ xóa đúng 1 dòng.
            $sql = "DELETE FROM products WHERE id = :id";
            $stmt = $this->conn->prepare($sql);

            // Gán id dạng số nguyên.
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            // Thực thi xóa.
            $stmt->execute();

            // Xác nhận xóa.
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // Hoàn tác nếu lỗi.
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e; // Ném lại cho trang gọi xử lý
        }
    }
}
?>