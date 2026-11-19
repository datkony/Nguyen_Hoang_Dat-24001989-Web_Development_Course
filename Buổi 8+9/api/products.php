<?php
// ============================================================================
// GIAI ĐOẠN 2: API QUẢN LÝ SẢN PHẨM BẰNG PHP + PDO
// ----------------------------------------------------------------------------
// Đường dẫn: api/products.php
// Chức năng:
//   GET    api/products.php?page=1&limit=5 -> Lấy danh sách sản phẩm có phân trang.
//   POST   api/products.php        -> Thêm sản phẩm mới (trả kèm id để gọi api/upload.php).
//   PUT    api/products.php?id=1   -> Cập nhật sản phẩm theo id.
//   DELETE api/products.php?id=1   -> Xóa sản phẩm theo id.
//
// Mọi phản hồi đều là JSON và luôn có trường `success`:
//   { "success": true,  "data": ..., "message": "..." }
//   { "success": false, "message": "..." }
//
// API sử dụng PDO prepared statement cho mọi truy vấn có tham số để chống
// tấn công SQL Injection và bảo đảm dữ liệu được xử lý an toàn.
// ============================================================================

// Bật chế độ kiểm tra kiểu nghiêm ngặt cho các hàm trong tệp này.
declare(strict_types=1);

// ----------------------------------------------------------------------------
// PHẦN 1: KHAI BÁO HEADER VÀ CẤU HÌNH CHUNG
// ----------------------------------------------------------------------------

// Thông báo cho trình duyệt biết nội dung phản hồi là JSON và dùng bảng mã UTF-8.
// Nhờ header này, tiếng Việt trong thông báo lỗi và tên sản phẩm hiển thị đúng.
header('Content-Type: application/json; charset=utf-8');

// Ngăn trình duyệt và proxy lưu cache phản hồi API.
// Điều này bảo đảm mỗi lần gọi GET đều nhận dữ liệu mới nhất sau khi thêm/sửa/xóa.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

// Cho phép giao diện ở nguồn khác (ví dụ chạy Vite ở cổng 5173) gọi API này.
// Dấu * nghĩa là chấp nhận mọi nguồn; khi triển khai thật nên thay bằng tên miền cụ thể.
header('Access-Control-Allow-Origin: *');

// Khai báo các phương thức HTTP mà API hỗ trợ cho yêu cầu CORS.
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

// Khai báo các header mà phía giao diện được phép gửi lên API.
header('Access-Control-Allow-Headers: Content-Type, Accept');

// Đọc phương thức HTTP của yêu cầu hiện tại; nếu không có thì mặc định là GET.
$phuongThuc = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

// Xử lý yêu cầu preflight (OPTIONS) do trình duyệt gửi trước các yêu cầu CORS.
if ($phuongThuc === 'OPTIONS') {
    // Trả về JSON thành công để mọi phản hồi của API đều có trường success theo quy ước.
    traLoiJson([
        // Đánh dấu yêu cầu preflight được chấp nhận.
        'success' => true,
        // Thông báo mô tả kết quả.
        'message' => 'Yêu cầu CORS preflight thành công.',
    ], 200);
}

// Nạp tệp cấu hình kết nối; dùng require_once để không nạp lặp lại nếu đã có.
require_once __DIR__ . '/config.php';

// Khai báo giới hạn lớn nhất của cột INT có dấu trong MySQL.
// Giá và tồn kho vượt quá giới hạn này sẽ không thể ghi vào cơ sở dữ liệu.
const GIA_TRI_INT_TOI_DA = 2147483647;

// ----------------------------------------------------------------------------
// PHẦN 2: CÁC HÀM HỖ TRỢ DÙNG CHUNG
// ----------------------------------------------------------------------------

/**
 * Gửi phản hồi JSON về cho trình duyệt và kết thúc chương trình.
 *
 * @param array<string, mixed> $duLieu  Nội dung cần chuyển thành JSON.
 * @param int                  $maHttp  Mã trạng thái HTTP (mặc định 200).
 * @return never                        Hàm không trả về vì luôn gọi exit.
 */
function traLoiJson(array $duLieu, int $maHttp = 200): never
{
    // Gán mã trạng thái HTTP trước khi gửi nội dung.
    http_response_code($maHttp);

    // Mã hóa mảng PHP thành chuỗi JSON.
    // JSON_UNESCAPED_UNICODE giữ nguyên tiếng Việt thay vì chuyển thành \uXXXX.
    // JSON_UNESCAPED_SLASHES giúp URL và đường dẫn hiển thị dễ đọc hơn.
    $chuoiJson = json_encode($duLieu, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // Nếu quá trình mã hóa JSON thất bại, gửi một thông báo lỗi tối giản thay thế.
    if ($chuoiJson === false) {
        // Đặt mã lỗi máy chủ vì đây là sự cố ngoài ý muốn của dữ liệu.
        http_response_code(500);

        // In một JSON hợp lệ để phía giao diện vẫn đọc được trường success.
        echo '{"success":false,"message":"Không thể mã hóa dữ liệu phản hồi."}';
    } else {
        // In chuỗi JSON vừa tạo ra làm nội dung phản hồi.
        echo $chuoiJson;
    }

    // Dừng toàn bộ đoạn mã phía sau để không vô tình gửi thêm dữ liệu.
    exit;
}

/**
 * Gửi phản hồi lỗi theo đúng cấu trúc JSON thống nhất.
 *
 * @param string $thongBao Thông báo lỗi thân thiện với người dùng.
 * @param int    $maHttp   Mã trạng thái HTTP tương ứng với loại lỗi.
 * @return never           Hàm không trả về vì luôn gọi exit.
 */
function traLoiLoi(string $thongBao, int $maHttp = 400): never
{
    // Đóng gói lỗi thành đối tượng có success = false và trường message.
    traLoiJson([
        // Trường bắt buộc cho biết thao tác không thành công.
        'success' => false,
        // Thông báo mô tả nguyên nhân lỗi để giao diện hiển thị cho người dùng.
        'message' => $thongBao,
    ], $maHttp);
}

/**
 * Đọc và giải mã nội dung JSON từ phần thân (body) của yêu cầu HTTP.
 *
 * @return array<string, mixed> Mảng dữ liệu nhận được từ phía giao diện.
 *                              Nếu dữ liệu không hợp lệ, hàm dừng chương trình bằng lỗi 400.
 */
function docDuLieuJson(): array
{
    // Đọc toàn bộ nội dung thô từ luồng đầu vào của PHP.
    $noiDungTho = file_get_contents('php://input');

    // Nếu không đọc được nội dung hoặc nội dung chỉ gồm khoảng trắng thì báo lỗi.
    if ($noiDungTho === false || trim($noiDungTho) === '') {
        // Yêu cầu thêm/sửa bắt buộc phải có phần thân JSON.
        traLoiLoi('Dữ liệu JSON không được để trống.', 400);
    }

    // Giải mã chuỗi JSON thành mảng kết hợp để dễ truy cập theo tên trường.
    $duLieu = json_decode($noiDungTho, true);

    // Kiểm tra lỗi cú pháp JSON do hàm json_decode ghi nhận.
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Trả về thông báo lỗi kèm mô tả ngắn gọn của PHP để dễ kiểm tra.
        traLoiLoi('Dữ liệu JSON không hợp lệ: ' . json_last_error_msg(), 400);
    }

    // Nếu JSON hợp lệ nhưng không phải là một đối tượng/mảng thì cũng không chấp nhận.
    if (!is_array($duLieu)) {
        // Ví dụ: gửi "abc", 123 hoặc null thay vì { "name": ... }.
        traLoiLoi('Dữ liệu gửi lên phải là một đối tượng JSON.', 400);
    }

    // Trả về mảng dữ liệu đã giải mã thành công.
    return $duLieu;
}

/**
 * Đọc và kiểm tra tham số `id` trên URL của yêu cầu PUT hoặc DELETE.
 *
 * @return int Mã sản phẩm hợp lệ (số nguyên dương nằm trong giới hạn INT).
 *             Nếu thiếu hoặc sai định dạng, hàm dừng chương trình bằng lỗi 400.
 */
function layIdTuQuery(): int
{
    // Lấy giá trị id từ chuỗi truy vấn; nếu không có thì trả về null.
    $giaTriId = $_GET['id'] ?? null;

    // Nếu tham số id không tồn tại hoặc không phải chuỗi (ví dụ mảng id[]) thì báo lỗi.
    if (!is_string($giaTriId) || trim($giaTriId) === '') {
        // Hướng dẫn người gọi bổ sung đúng tham số trên URL.
        traLoiLoi('Thiếu tham số id hợp lệ trên URL. Ví dụ: api/products.php?id=1', 400);
    }

    // Cắt khoảng trắng hai đầu để chấp nhận các giá trị như " 1 ".
    $giaTriId = trim($giaTriId);

    // Chỉ chấp nhận chuỗi gồm các chữ số, không có dấu trừ, dấu chấm hay ký tự lạ.
    if (preg_match('/^\d+$/', $giaTriId) !== 1) {
        // Báo lỗi vì id sản phẩm phải là số nguyên dương.
        traLoiLoi('id phải là một số nguyên dương.', 400);
    }

    // Ép chuỗi chữ số thành số nguyên để dùng trong câu lệnh SQL.
    $id = (int) $giaTriId;

    // Kiểm tra id lớn hơn 0 và không vượt quá giới hạn của cột INT trong MySQL.
    if ($id < 1 || $id > GIA_TRI_INT_TOI_DA) {
        // Báo lỗi nếu id bằng 0, âm hoặc quá lớn.
        traLoiLoi('id phải nằm trong khoảng từ 1 đến ' . GIA_TRI_INT_TOI_DA . '.', 400);
    }

    // Trả về id đã được kiểm tra an toàn.
    return $id;
}

/**
 * Đọc và kiểm tra hai tham số phân trang `page` và `limit` trên URL của yêu cầu GET.
 * Quy tắc của đề bài:
 *   - Thiếu tham số thì mặc định page = 1 và limit = 5.
 *   - page phải là số nguyên dương; limit nếu được gửi thì phải bằng đúng 5.
 *   - Mọi giá trị sai định dạng đều trả lỗi 400 kèm thông báo cụ thể.
 *
 * @return array{page: int, limit: int} Bộ tham số phân trang đã được kiểm tra.
 */
function layThamSoPhanTrang(): array
{
    // Đọc giá trị page từ chuỗi truy vấn; null nghĩa là người dùng không gửi tham số này.
    $pageTho = $_GET['page'] ?? null;

    // Đọc giá trị limit từ chuỗi truy vấn; null nghĩa là người dùng không gửi tham số này.
    $limitTho = $_GET['limit'] ?? null;

    // Gán page mặc định bằng 1 để đáp ứng trường hợp thiếu tham số.
    $page = 1;

    // Gán limit mặc định bằng 5 để mỗi trang luôn có năm sản phẩm.
    $limit = 5;

    // Chỉ kiểm tra page khi người dùng thực sự gửi tham số trên URL.
    if ($pageTho !== null) {
        // Tham số phải là chuỗi gồm toàn chữ số; giá trị dạng mảng page[]=1 bị từ chối.
        if (!is_string($pageTho) || preg_match('/^\d+$/', trim($pageTho)) !== 1) {
            // Báo lỗi rõ ràng để giao diện hoặc người gọi API biết cách sửa.
            traLoiLoi('page phải là một số nguyên dương.', 400);
        }

        // Ép chuỗi chữ số đã kiểm tra thành số nguyên để dùng trong phép tính OFFSET.
        $page = (int) trim($pageTho);

        // Loại bỏ page = 0 và các giá trị vượt quá giới hạn của cột INT MySQL.
        if ($page < 1 || $page > GIA_TRI_INT_TOI_DA) {
            // Thông báo kèm phạm vi hợp lệ để người dùng dễ điều chỉnh.
            traLoiLoi('page phải nằm trong khoảng từ 1 đến ' . GIA_TRI_INT_TOI_DA . '.', 400);
        }
    }

    // Chỉ kiểm tra limit khi người dùng thực sự gửi tham số trên URL.
    if ($limitTho !== null) {
        // Tham số phải là chuỗi gồm toàn chữ số; giá trị dạng mảng limit[]=5 bị từ chối.
        if (!is_string($limitTho) || preg_match('/^\d+$/', trim($limitTho)) !== 1) {
            // Báo lỗi vì limit không phải số nguyên dương hợp lệ.
            traLoiLoi('limit phải là một số nguyên dương.', 400);
        }

        // Ép chuỗi chữ số đã kiểm tra thành số nguyên để so sánh với giá trị quy định.
        $limit = (int) trim($limitTho);

        // Đề bài cố định năm sản phẩm mỗi trang nên mọi giá trị khác 5 đều là sai.
        if ($limit !== 5) {
            // Trả lỗi 400 thay vì âm thầm đổi limit để tránh hiểu nhầm về kết quả.
            traLoiLoi('limit chỉ được phép bằng 5.', 400);
        }
    }

    // Trả về bộ tham số đã hợp lệ cho hàm lấy danh sách sử dụng.
    return [
        // Trang được yêu cầu hoặc trang mặc định.
        'page' => $page,
        // Số bản ghi mỗi trang, luôn bằng 5.
        'limit' => $limit,
    ];
}

/**
 * Chuyển đổi một dòng dữ liệu thô từ MySQL thành mảng có kiểu số rõ ràng.
 * PDO có thể trả các cột INT dưới dạng chuỗi, nên cần ép kiểu để JSON đúng kiểu.
 *
 * @param array<string, mixed> $dong Một dòng sản phẩm lấy từ cơ sở dữ liệu.
 * @return array{id: int, name: string, price: int, stock: int, image: string} Sản phẩm đã chuẩn hóa.
 */
function chuanHoaSanPham(array $dong): array
{
    // Trả về mảng mới với đúng năm trường và đúng kiểu dữ liệu mà giao diện cần.
    return [
        // Mã sản phẩm luôn là số nguyên.
        'id' => (int) $dong['id'],
        // Tên sản phẩm luôn là chuỗi.
        'name' => (string) $dong['name'],
        // Đơn giá luôn là số nguyên (đơn vị đồng).
        'price' => (int) $dong['price'],
        // Số lượng tồn luôn là số nguyên không âm.
        'stock' => (int) $dong['stock'],
        // Đường dẫn ảnh; chuỗi rỗng nghĩa là sản phẩm chưa có ảnh.
        'image' => (string) ($dong['image'] ?? ''),
    ];
}

/**
 * Chuyển một giá trị bất kỳ thành số nguyên nếu có thể; ngược lại trả về null.
 * Hàm chấp nhận số nguyên thật hoặc chuỗi chỉ gồm chữ số (có thể có dấu trừ).
 * Hàm cố ý từ chối số thực như 1.5 hay 5000.0 vì đề bài yêu cầu số nguyên.
 *
 * @param mixed $giaTri Giá trị cần kiểm tra và chuyển đổi.
 * @return int|null     Số nguyên hợp lệ hoặc null nếu không hợp lệ.
 */
function docSoNguyen(mixed $giaTri): ?int
{
    // Nếu giá trị đã là số nguyên (JSON số nguyên được PHP giải mã thành int) thì dùng luôn.
    if (is_int($giaTri)) {
        // Trả về chính giá trị số nguyên đó.
        return $giaTri;
    }

    // Nếu giá trị là chuỗi thì kiểm tra định dạng số nguyên trước khi ép kiểu.
    if (is_string($giaTri)) {
        // Cắt khoảng trắng hai đầu để chấp nhận chuỗi như " 12000 ".
        $chuoi = trim($giaTri);

        // Chỉ chấp nhận "0" hoặc chuỗi số nguyên không có số 0 thừa ở đầu.
        // Ví dụ hợp lệ: "0", "5", "-3", "12000"; không hợp lệ: "05", "1.5", "abc".
        if (preg_match('/^(0|-?[1-9]\d*)$/', $chuoi) !== 1) {
            // Trả về null để báo rằng giá trị không phải số nguyên hợp lệ.
            return null;
        }

        // Ép chuỗi đã kiểm tra thành số nguyên của PHP.
        $soNguyen = (int) $chuoi;

        // Kiểm tra tràn số: nếu chuỗi quá lớn, ép kiểu sẽ làm sai giá trị.
        if ((string) $soNguyen !== $chuoi) {
            // Trả về null khi số vượt quá khả năng biểu diễn của PHP.
            return null;
        }

        // Trả về số nguyên đã chuyển đổi thành công.
        return $soNguyen;
    }

    // Các kiểu còn lại (float, bool, null, array, object) đều không hợp lệ.
    return null;
}

/**
 * Kiểm tra và chuẩn hóa dữ liệu sản phẩm nhận từ JSON trước khi ghi vào MySQL.
 * Quy tắc theo đề bài:
 *   - name  sau khi trim phải có từ 1 đến 100 ký tự.
 *   - price phải là số nguyên lớn hơn 0.
 *   - stock phải là số nguyên không âm (>= 0).
 *
 * @param array<string, mixed> $duLieu Dữ liệu JSON đã giải mã.
 * @return array{name: string, price: int, stock: int} Dữ liệu hợp lệ để ghi vào CSDL.
 *         Nếu có trường không hợp lệ, hàm dừng chương trình bằng lỗi 400.
 */
function kiemTraDuLieuSanPham(array $duLieu): array
{
    // Kiểm tra trường name có tồn tại trong dữ liệu gửi lên hay không.
    if (!array_key_exists('name', $duLieu)) {
        // Báo lỗi thiếu trường bắt buộc.
        traLoiLoi('Thiếu trường name.', 400);
    }

    // Tên sản phẩm phải là chuỗi; số hoặc mảng đều không được chấp nhận.
    if (!is_string($duLieu['name'])) {
        // Báo lỗi sai kiểu dữ liệu.
        traLoiLoi('Tên sản phẩm phải là chuỗi ký tự.', 400);
    }

    // Bỏ khoảng trắng ở hai đầu tên theo đúng yêu cầu kiểm tra của đề bài.
    $ten = trim($duLieu['name']);

    // Đếm số ký tự Unicode để đếm đúng tiếng Việt có dấu (mỗi chữ là một ký tự).
    $doDaiTen = mb_strlen($ten, 'UTF-8');

    // Tên sau khi trim phải có ít nhất 1 ký tự và nhiều nhất 100 ký tự.
    if ($doDaiTen < 1 || $doDaiTen > 100) {
        // Thông báo rõ giới hạn để người dùng sửa dữ liệu.
        traLoiLoi('Tên sản phẩm phải có từ 1 đến 100 ký tự sau khi bỏ khoảng trắng hai đầu.', 400);
    }

    // Kiểm tra trường price có tồn tại trong dữ liệu gửi lên hay không.
    if (!array_key_exists('price', $duLieu)) {
        // Báo lỗi thiếu trường bắt buộc.
        traLoiLoi('Thiếu trường price.', 400);
    }

    // Chuyển giá trị price thành số nguyên; kết quả null nghĩa là không hợp lệ.
    $gia = docSoNguyen($duLieu['price']);

    // Đơn giá phải là số nguyên, lớn hơn 0 và không vượt quá giới hạn cột INT.
    if ($gia === null || $gia <= 0 || $gia > GIA_TRI_INT_TOI_DA) {
        // Thông báo lỗi theo đúng quy tắc của đề bài.
        traLoiLoi('Đơn giá phải là số nguyên lớn hơn 0 và không vượt quá ' . GIA_TRI_INT_TOI_DA . '.', 400);
    }

    // Kiểm tra trường stock có tồn tại trong dữ liệu gửi lên hay không.
    if (!array_key_exists('stock', $duLieu)) {
        // Báo lỗi thiếu trường bắt buộc.
        traLoiLoi('Thiếu trường stock.', 400);
    }

    // Chuyển giá trị stock thành số nguyên; kết quả null nghĩa là không hợp lệ.
    $tonKho = docSoNguyen($duLieu['stock']);

    // Tồn kho phải là số nguyên không âm và không vượt quá giới hạn cột INT.
    if ($tonKho === null || $tonKho < 0 || $tonKho > GIA_TRI_INT_TOI_DA) {
        // Thông báo lỗi theo đúng quy tắc của đề bài.
        traLoiLoi('Số lượng tồn phải là số nguyên không âm và không vượt quá ' . GIA_TRI_INT_TOI_DA . '.', 400);
    }

    // Trả về bộ dữ liệu đã được kiểm tra và chuẩn hóa để dùng cho câu lệnh SQL.
    return [
        // Tên đã được trim khoảng trắng hai đầu.
        'name' => $ten,
        // Đơn giá đã là số nguyên dương.
        'price' => $gia,
        // Số lượng tồn đã là số nguyên không âm.
        'stock' => $tonKho,
    ];
}

// ----------------------------------------------------------------------------
// PHẦN 3: CÁC HÀM XỬ LÝ TỪNG PHƯƠNG THỨC HTTP
// ----------------------------------------------------------------------------

/**
 * Xử lý GET: đếm tổng sản phẩm rồi lấy đúng một trang dữ liệu theo page/limit.
 * Kết quả trả về gồm success, data và pagination để giao diện dựng nút chuyển trang.
 *
 * @param PDO $pdo   Kết nối cơ sở dữ liệu đang mở.
 * @param int $page  Trang được yêu cầu (đã kiểm tra ở layThamSoPhanTrang).
 * @param int $limit Số sản phẩm mỗi trang, luôn bằng 5.
 * @return never     Luôn kết thúc bằng một phản hồi JSON.
 */
function xuLyLayDanhSach(PDO $pdo, int $page, int $limit): never
{
    // Bọc truy vấn trong try/catch để chuyển lỗi SQL thành phản hồi JSON an toàn.
    try {
        // Đếm tổng số bản ghi trong bảng để biết tổng số sản phẩm và số trang.
        $cauLenhDem = $pdo->query('SELECT COUNT(*) FROM products');

        // fetchColumn lấy trực tiếp giá trị của cột COUNT(*) dưới dạng chuỗi, rồi ép sang int.
        $tong = (int) $cauLenhDem->fetchColumn();

        // Tính tổng số trang: chia trần tổng cho limit, làm tròn lên bằng ceil.
        // max(1, ...) bảo đảm danh sách rỗng vẫn có ít nhất một trang hợp lệ.
        $tongTrang = max(1, (int) ceil($tong / $limit));

        // Nếu trang yêu cầu vượt trang cuối thì dùng trang cuối thực tế thay vì trả mảng rỗng.
        if ($page > $tongTrang) {
            // Gán lại page bằng tổng số trang để cả dữ liệu lẫn phản hồi đều nhất quán.
            $page = $tongTrang;
        }

        // Tính OFFSET: bỏ qua (page - 1) trang phía trước, mỗi trang có limit bản ghi.
        $offset = ($page - 1) * $limit;

        // Chuẩn bị câu lệnh lấy dữ liệu, sắp xếp id giảm dần và giới hạn bằng LIMIT/OFFSET.
        $cauLenh = $pdo->prepare(
            'SELECT id, name, price, stock, image FROM products ORDER BY id DESC LIMIT :limit OFFSET :offset'
        );

        // Gán số bản ghi mỗi trang vào tham số :limit với kiểu số nguyên.
        $cauLenh->bindValue(':limit', $limit, PDO::PARAM_INT);

        // Gán vị trí bắt đầu vào tham số :offset với kiểu số nguyên.
        $cauLenh->bindValue(':offset', $offset, PDO::PARAM_INT);

        // Thực thi truy vấn để lấy đúng các dòng thuộc trang hiện tại.
        $cauLenh->execute();

        // Lấy toàn bộ dòng kết quả của trang dưới dạng mảng kết hợp.
        $cacDong = $cauLenh->fetchAll();

        // Chuẩn hóa từng dòng để id, price, stock là số nguyên và image là chuỗi đường dẫn.
        $danhSach = array_map('chuanHoaSanPham', $cacDong);

        // Trả về thành công kèm dữ liệu và thông tin phân trang theo đúng cấu trúc đề bài.
        traLoiJson([
            // Đánh dấu thao tác lấy danh sách thành công.
            'success' => true,
            // Trường data chứa mảng tối đa năm sản phẩm của trang hiện tại.
            'data' => $danhSach,
            // Trường pagination mô tả trạng thái phân trang cho giao diện Vue.
            'pagination' => [
                // Trang thực tế sau khi đã xử lý trường hợp vượt trang cuối.
                'page' => $page,
                // Số sản phẩm trên mỗi trang (luôn là 5).
                'limit' => $limit,
                // Tổng số sản phẩm trong toàn bộ bảng, không phải số dòng của riêng trang này.
                'total' => $tong,
                // Tổng số trang, tối thiểu bằng 1 kể cả khi không có sản phẩm.
                'total_pages' => $tongTrang,
            ],
        ], 200);
    } catch (PDOException $loi) {
        // Ghi chi tiết lỗi vào log máy chủ để người phát triển điều tra.
        error_log('[products.php] Lỗi lấy danh sách sản phẩm: ' . $loi->getMessage());

        // Trả thông báo chung cho người dùng, không để lộ chi tiết cơ sở dữ liệu.
        traLoiLoi('Không thể lấy danh sách sản phẩm. Vui lòng thử lại sau.', 500);
    }
}

/**
 * Xử lý POST: thêm một sản phẩm mới sau khi kiểm tra dữ liệu hợp lệ.
 *
 * @param PDO $pdo Kết nối cơ sở dữ liệu đang mở.
 * @return never   Luôn kết thúc bằng một phản hồi JSON.
 */
function xuLyThemSanPham(PDO $pdo): never
{
    // Đọc JSON từ phần thân yêu cầu và kiểm tra toàn bộ quy tắc dữ liệu.
    // Nếu dữ liệu sai, hàm kiemTraDuLieuSanPham sẽ tự trả lỗi 400 và dừng chương trình.
    $duLieu = kiemTraDuLieuSanPham(docDuLieuJson());

    // Bọc thao tác ghi cơ sở dữ liệu trong try/catch để xử lý lỗi SQL.
    try {
        // Chuẩn bị câu lệnh INSERT với ba tham số đặt tên (named placeholders).
        $cauLenh = $pdo->prepare(
            'INSERT INTO products (name, price, stock) VALUES (:name, :price, :stock)'
        );

        // Gán tên sản phẩm vào tham số :name với kiểu chuỗi.
        $cauLenh->bindValue(':name', $duLieu['name'], PDO::PARAM_STR);

        // Gán đơn giá vào tham số :price với kiểu số nguyên.
        $cauLenh->bindValue(':price', $duLieu['price'], PDO::PARAM_INT);

        // Gán số lượng tồn vào tham số :stock với kiểu số nguyên.
        $cauLenh->bindValue(':stock', $duLieu['stock'], PDO::PARAM_INT);

        // Thực thi câu lệnh INSERT để ghi bản ghi mới vào MySQL.
        $cauLenh->execute();

        // Lấy mã id vừa được MySQL tự sinh cho bản ghi mới.
        $idMoi = (int) $pdo->lastInsertId();

        // Tạo đối tượng sản phẩm hoàn chỉnh để trả về cho giao diện.
        $sanPhamMoi = [
            // Mã sản phẩm do cơ sở dữ liệu cấp.
            'id' => $idMoi,
            // Tên sản phẩm đã được kiểm tra và trim.
            'name' => $duLieu['name'],
            // Đơn giá đã được kiểm tra.
            'price' => $duLieu['price'],
            // Số lượng tồn đã được kiểm tra.
            'stock' => $duLieu['stock'],
            // Ảnh chưa được tải lên nên ban đầu để chuỗi rỗng.
            'image' => '',
        ];

        // Trả về mã 201 Created cùng thông báo và dữ liệu sản phẩm vừa tạo.
        traLoiJson([
            // Đánh dấu thao tác thêm thành công.
            'success' => true,
            // Trả mã sản phẩm mới ở cấp cao nhất để Vue dễ dùng khi gọi api/upload.php.
            'id' => $idMoi,
            // Thông báo hiển thị trên giao diện.
            'message' => 'Thêm sản phẩm thành công.',
            // Trả về sản phẩm mới trong data.id theo đúng quy ước cũ của API.
            'data' => $sanPhamMoi,
        ], 201);
    } catch (PDOException $loi) {
        // Ghi chi tiết lỗi SQL vào log của máy chủ.
        error_log('[products.php] Lỗi thêm sản phẩm: ' . $loi->getMessage());

        // Trả thông báo chung, không tiết lộ cấu trúc cơ sở dữ liệu.
        traLoiLoi('Không thể thêm sản phẩm. Vui lòng thử lại sau.', 500);
    }
}

/**
 * Xử lý PUT: cập nhật sản phẩm theo id trên URL.
 *
 * @param PDO $pdo Kết nối cơ sở dữ liệu đang mở.
 * @return never   Luôn kết thúc bằng một phản hồi JSON.
 */
function xuLyCapNhatSanPham(PDO $pdo): never
{
    // Đọc và kiểm tra id sản phẩm cần cập nhật từ tham số trên URL.
    $id = layIdTuQuery();

    // Đọc JSON và kiểm tra dữ liệu mới trước khi chạm vào cơ sở dữ liệu.
    $duLieu = kiemTraDuLieuSanPham(docDuLieuJson());

    // Bọc các truy vấn trong try/catch để xử lý lỗi SQL.
    try {
        // Chuẩn bị câu lệnh kiểm tra sản phẩm có tồn tại và lấy luôn đường dẫn ảnh hiện tại.
        $cauLenhKiemTra = $pdo->prepare('SELECT id, image FROM products WHERE id = :id LIMIT 1');

        // Gán id vào tham số :id với kiểu số nguyên.
        $cauLenhKiemTra->bindValue(':id', $id, PDO::PARAM_INT);

        // Thực thi câu lệnh kiểm tra.
        $cauLenhKiemTra->execute();

        // Lấy một dòng kết quả; false nghĩa là không tìm thấy sản phẩm.
        $ketQua = $cauLenhKiemTra->fetch();

        // Nếu sản phẩm không tồn tại thì trả lỗi 404 theo yêu cầu của đề bài.
        if ($ketQua === false) {
            // Thông báo rõ id không tồn tại để người dùng biết.
            traLoiLoi('Không tìm thấy sản phẩm có id = ' . $id . '.', 404);
        }

        // Lưu đường dẫn ảnh cũ; câu lệnh UPDATE bên dưới không đụng đến cột image nên ảnh được giữ nguyên.
        $anhHienTai = (string) ($ketQua['image'] ?? '');

        // Chuẩn bị câu lệnh UPDATE ba trường name, price, stock theo id.
        $cauLenhCapNhat = $pdo->prepare(
            'UPDATE products SET name = :name, price = :price, stock = :stock WHERE id = :id'
        );

        // Gán tên mới vào tham số :name.
        $cauLenhCapNhat->bindValue(':name', $duLieu['name'], PDO::PARAM_STR);

        // Gán đơn giá mới vào tham số :price.
        $cauLenhCapNhat->bindValue(':price', $duLieu['price'], PDO::PARAM_INT);

        // Gán số lượng tồn mới vào tham số :stock.
        $cauLenhCapNhat->bindValue(':stock', $duLieu['stock'], PDO::PARAM_INT);

        // Gán id của sản phẩm cần cập nhật vào tham số :id.
        $cauLenhCapNhat->bindValue(':id', $id, PDO::PARAM_INT);

        // Thực thi câu lệnh UPDATE.
        $cauLenhCapNhat->execute();

        // Trả về thành công cùng dữ liệu sản phẩm sau khi cập nhật.
        traLoiJson([
            // Đánh dấu thao tác cập nhật thành công.
            'success' => true,
            // Thông báo hiển thị trên giao diện.
            'message' => 'Cập nhật sản phẩm thành công.',
            // Trả về sản phẩm đã cập nhật để giao diện đối chiếu nếu cần.
            'data' => [
                // Giữ nguyên id của sản phẩm.
                'id' => $id,
                // Tên mới sau khi cập nhật.
                'name' => $duLieu['name'],
                // Đơn giá mới sau khi cập nhật.
                'price' => $duLieu['price'],
                // Số lượng tồn mới sau khi cập nhật.
                'stock' => $duLieu['stock'],
                // Đường dẫn ảnh hiện tại không bị thay đổi vì PUT chỉ cập nhật ba trường thông tin.
                'image' => $anhHienTai,
            ],
        ], 200);
    } catch (PDOException $loi) {
        // Ghi chi tiết lỗi SQL vào log của máy chủ.
        error_log('[products.php] Lỗi cập nhật sản phẩm: ' . $loi->getMessage());

        // Trả thông báo chung cho người dùng.
        traLoiLoi('Không thể cập nhật sản phẩm. Vui lòng thử lại sau.', 500);
    }
}

/**
 * Xử lý DELETE: xóa sản phẩm theo id trên URL.
 *
 * @param PDO $pdo Kết nối cơ sở dữ liệu đang mở.
 * @return never   Luôn kết thúc bằng một phản hồi JSON.
 */
function xuLyXoaSanPham(PDO $pdo): never
{
    // Đọc và kiểm tra id sản phẩm cần xóa từ tham số trên URL.
    $id = layIdTuQuery();

    // Bọc câu lệnh xóa trong try/catch để xử lý lỗi SQL.
    try {
        // Chuẩn bị câu lệnh DELETE với tham số id an toàn.
        $cauLenh = $pdo->prepare('DELETE FROM products WHERE id = :id');

        // Gán id cần xóa vào tham số :id với kiểu số nguyên.
        $cauLenh->bindValue(':id', $id, PDO::PARAM_INT);

        // Thực thi câu lệnh xóa.
        $cauLenh->execute();

        // rowCount() trả về số dòng bị xóa; bằng 0 nghĩa là id không tồn tại.
        if ($cauLenh->rowCount() === 0) {
            // Trả lỗi 404 khi không tìm thấy sản phẩm để xóa.
            traLoiLoi('Không tìm thấy sản phẩm có id = ' . $id . ' để xóa.', 404);
        }

        // Trả về thành công kèm id vừa xóa.
        traLoiJson([
            // Đánh dấu thao tác xóa thành công.
            'success' => true,
            // Thông báo hiển thị trên giao diện.
            'message' => 'Xóa sản phẩm thành công.',
            // Trả về id đã xóa để giao diện biết bản ghi nào vừa bị loại bỏ.
            'data' => [
                // Mã sản phẩm đã bị xóa.
                'id' => $id,
            ],
        ], 200);
    } catch (PDOException $loi) {
        // Ghi chi tiết lỗi SQL vào log của máy chủ.
        error_log('[products.php] Lỗi xóa sản phẩm: ' . $loi->getMessage());

        // Trả thông báo chung cho người dùng.
        traLoiLoi('Không thể xóa sản phẩm. Vui lòng thử lại sau.', 500);
    }
}

// ----------------------------------------------------------------------------
// PHẦN 4: ĐIỀU HƯỚNG YÊU CẦU THEO PHƯƠNG THỨC HTTP
// ----------------------------------------------------------------------------

// Kết nối tới MySQL một lần duy nhất cho yêu cầu hiện tại.
// Nếu kết nối thất bại, trả JSON lỗi 500 thay vì để PHP hiển thị lỗi HTML.
try {
    // Gọi hàm trong config.php để tạo đối tượng PDO.
    $pdo = taoKetNoiPDO();
} catch (PDOException $loi) {
    // Ghi chi tiết lỗi kết nối vào log máy chủ.
    error_log('[products.php] Lỗi kết nối cơ sở dữ liệu: ' . $loi->getMessage());

    // Trả thông báo chung, không tiết lộ tài khoản hay mật khẩu cơ sở dữ liệu.
    traLoiLoi('Không thể kết nối đến cơ sở dữ liệu. Vui lòng kiểm tra MySQL đã chạy chưa.', 500);
}

// Dùng switch để chọn hàm xử lý tương ứng với phương thức HTTP.
switch ($phuongThuc) {
    // GET: trả danh sách sản phẩm theo trang được yêu cầu.
    case 'GET':
        // Đọc và kiểm tra page/limit trên URL; sai định dạng sẽ trả lỗi 400 tại đây.
        $phanTrang = layThamSoPhanTrang();

        // Gọi hàm lấy danh sách với trang và giới hạn vừa hợp lệ, rồi kết thúc yêu cầu.
        xuLyLayDanhSach($pdo, $phanTrang['page'], $phanTrang['limit']);

    // POST: thêm sản phẩm mới.
    case 'POST':
        // Gọi hàm thêm sản phẩm và kết thúc yêu cầu.
        xuLyThemSanPham($pdo);

    // PUT: cập nhật sản phẩm theo id.
    case 'PUT':
        // Gọi hàm cập nhật sản phẩm và kết thúc yêu cầu.
        xuLyCapNhatSanPham($pdo);

    // DELETE: xóa sản phẩm theo id.
    case 'DELETE':
        // Gọi hàm xóa sản phẩm và kết thúc yêu cầu.
        xuLyXoaSanPham($pdo);

    // Mọi phương thức khác đều không được hỗ trợ.
    default:
        // Gửi header Allow để thông báo các phương thức hợp lệ theo chuẩn HTTP.
        header('Allow: GET, POST, PUT, DELETE, OPTIONS');

        // Trả lỗi 405 Method Not Allowed kèm tên phương thức nhận được.
        traLoiLoi('Phương thức ' . $phuongThuc . ' không được hỗ trợ.', 405);
}
