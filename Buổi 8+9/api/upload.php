<?php
// ============================================================================
// API TẢI ẢNH SẢN PHẨM: api/upload.php
// ----------------------------------------------------------------------------
// Endpoint này chỉ nhận một yêu cầu POST dạng multipart/form-data gồm:
//   - product_id: mã sản phẩm đã được api/products.php lưu thành công.
//   - image: đúng một tệp ảnh JPG/JPEG/PNG có dung lượng tối đa 2 MB.
//
// Quy trình xử lý:
//   1. Kiểm tra phương thức HTTP và các tham số đầu vào.
//   2. Kiểm tra lại dung lượng cùng loại ảnh THỰC TẾ ở phía PHP (không tin vào phần mở rộng do client gửi).
//   3. Kiểm tra sản phẩm tồn tại trong cơ sở dữ liệu.
//   4. Tạo tên file duy nhất, lưu ảnh vào thư mục uploads và cập nhật cột image.
//   5. Trả về JSON cho Vue biết kết quả để hiển thị thông báo hoặc cho phép thử lại.
//
// Mọi phản hồi đều có trường success theo đúng quy ước của các API khác trong dự án.
// ============================================================================

// Bật chế độ kiểm tra kiểu nghiêm ngặt để tránh ép kiểu ngầm gây sai dữ liệu.
declare(strict_types=1);

// ----------------------------------------------------------------------------
// PHẦN 1: HEADER, CẤU HÌNH VÀ HẰNG SỐ
// ----------------------------------------------------------------------------

// Thông báo cho trình duyệt biết phần thân phản hồi là JSON, mã hóa UTF-8 để giữ tiếng Việt.
header('Content-Type: application/json; charset=utf-8');

// Cấm cache để mỗi lần upload đều trả kết quả mới nhất, không phải kết quả cũ của trình duyệt.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

// Cho phép giao diện ở nguồn khác gọi API này (phục vụ chế độ chạy Vite/dev server).
header('Access-Control-Allow-Origin: *');

// Khai báo các phương thức được phép dùng với endpoint này.
header('Access-Control-Allow-Methods: POST, OPTIONS');

// Khai báo các header được phép gửi kèm, trong đó Content-Type là bắt buộc của multipart.
header('Access-Control-Allow-Headers: Content-Type, Accept');

// Đọc phương thức HTTP hiện tại; nếu không có thì mặc định là GET để đi vào nhánh lỗi 405.
$phuongThuc = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

// Xử lý yêu cầu preflight CORS mà trình duyệt gửi trước khi gửi multipart từ nguồn khác.
if ($phuongThuc === 'OPTIONS') {
    // Trả JSON thành công theo đúng cấu trúc chung của dự án.
    traLoiJson([
        // Đánh dấu preflight được chấp nhận.
        'success' => true,
        // Thông báo ngắn gọn mô tả kết quả.
        'message' => 'Yêu cầu CORS preflight thành công.',
    ], 200);
}

// Nạp cấu hình kết nối MySQL dùng chung với api/products.php.
require_once __DIR__ . '/config.php';

// Khai báo dung lượng tối đa của ảnh: 2 MB = 2 * 1024 * 1024 byte.
const KICH_THUOC_ANH_TOI_DA = 2 * 1024 * 1024;

// Khai báo giới hạn trên của cột INT MySQL để kiểm tra product_id.
const GIA_TRI_INT_TOI_DA = 2147483647;

// Khai báo bảng ánh xạ từ MIME type thật sang phần mở rộng dùng khi lưu file.
// Chỉ JPG/JPEG/PNG được phép; mọi loại khác sẽ bị từ chối ở bước kiểm tra MIME.
const LOAI_ANH_HOP_LE = [
    // Ảnh JPEG thường do trình duyệt báo là image/jpeg.
    'image/jpeg' => 'jpg',
    // Ảnh PNG do trình duyệt báo là image/png.
    'image/png' => 'png',
];

// ----------------------------------------------------------------------------
// PHẦN 2: CÁC HÀM HỖ TRỢ
// ----------------------------------------------------------------------------

/**
 * Gửi phản hồi JSON rồi kết thúc chương trình.
 *
 * @param array<string, mixed> $duLieu  Nội dung phản hồi.
 * @param int                  $maHttp  Mã trạng thái HTTP.
 * @return never                        Hàm luôn gọi exit nên không trả về.
 */
function traLoiJson(array $duLieu, int $maHttp = 200): never
{
    // Gán mã HTTP trước khi ghi nội dung để trình duyệt/công cụ test đọc đúng.
    http_response_code($maHttp);

    // Mã hóa mảng PHP thành JSON; giữ nguyên tiếng Việt và không escape dấu gạch chéo.
    $chuoiJson = json_encode($duLieu, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // Nếu mã hóa thất bại thì vẫn trả một JSON hợp lệ để client không bị lỗi parse.
    if ($chuoiJson === false) {
        // Đánh dấu lỗi máy chủ vì dữ liệu phản hồi không mã hóa được.
        http_response_code(500);

        // In thông báo lỗi tối giản nhưng vẫn đúng cấu trúc success = false.
        echo '{"success":false,"message":"Không thể mã hóa dữ liệu phản hồi."}';
    } else {
        // In chuỗi JSON bình thường khi quá trình mã hóa thành công.
        echo $chuoiJson;
    }

    // Dừng chương trình để không gửi thêm bất kỳ nội dung nào sau JSON.
    exit;
}

/**
 * Gửi phản hồi lỗi thống nhất cho toàn bộ API upload.
 *
 * @param string $thongBao Thông báo thân thiện hiển thị trên giao diện Vue.
 * @param int    $maHttp   Mã lỗi HTTP tương ứng (400, 404, 405, 500...).
 * @return never           Hàm luôn gọi exit.
 */
function traLoiLoi(string $thongBao, int $maHttp = 400): never
{
    // Đóng gói theo đúng quy ước { success: false, message: ... } của dự án.
    traLoiJson([
        // Báo thất bại để Vue hiển thị hộp cảnh báo đỏ và giữ dữ liệu đã lưu.
        'success' => false,
        // Gửi cụ thể nguyên nhân lỗi để người dùng biết cách thử lại.
        'message' => $thongBao,
    ], $maHttp);
}

/**
 * Đọc và kiểm tra product_id gửi qua $_POST của yêu cầu multipart/form-data.
 *
 * @return int Mã sản phẩm hợp lệ; nếu sai thì hàm trả lỗi 400 và dừng chương trình.
 */
function layProductIdTuPost(): int
{
    // Đọc product_id từ mảng $_POST; null nghĩa là client không gửi trường này.
    $giaTriId = $_POST['product_id'] ?? null;

    // Yêu cầu product_id phải là chuỗi; nếu gửi mảng product_id[] thì từ chối ngay.
    if (!is_string($giaTriId) || trim($giaTriId) === '') {
        // Báo thiếu tham số để Vue hoặc người gọi API biết phải gửi kèm id.
        traLoiLoi('Thiếu mã sản phẩm (product_id) để tải ảnh lên.', 400);
    }

    // Cắt khoảng trắng hai đầu để chấp nhận các giá trị như " 12 ".
    $giaTriId = trim($giaTriId);

    // Chỉ chấp nhận chuỗi toàn chữ số, không có dấu trừ, dấu chấm hay ký tự lạ.
    if (preg_match('/^\d+$/', $giaTriId) !== 1) {
        // Báo lỗi định dạng thay vì ép kiểu tùy tiện.
        traLoiLoi('product_id phải là một số nguyên dương.', 400);
    }

    // Ép chuỗi chữ số đã kiểm tra thành số nguyên để dùng trong truy vấn PDO.
    $id = (int) $giaTriId;

    // Kiểm tra id nằm trong phạm vi hợp lệ của kiểu INT trong MySQL.
    if ($id < 1 || $id > GIA_TRI_INT_TOI_DA) {
        // Trả lỗi nếu id bằng 0 hoặc vượt quá giới hạn cơ sở dữ liệu.
        traLoiLoi('product_id phải nằm trong khoảng từ 1 đến ' . GIA_TRI_INT_TOI_DA . '.', 400);
    }

    // Trả về id đã an toàn để dùng trong câu lệnh prepared statement.
    return $id;
}

/**
 * Kiểm tra tệp ảnh nhận được: có upload thật, đúng kích thước và đúng loại ảnh thực tế.
 * Việc kiểm tra MIME bằng Fileinfo và getimagesize ở đây là lớp bảo vệ cuối cùng,
 * không phụ thuộc vào extension hay thuộc tính type do phía Vue gửi lên.
 *
 * @return array{duoi: string, tmp: string} Phần mở rộng và đường dẫn file tạm hợp lệ.
 */
function kiemTraTepAnh(): array
{
    // Kiểm tra trường `image` có tồn tại trong $_FILES hay không.
    if (!isset($_FILES['image'])) {
        // Báo lỗi nếu Vue không gửi kèm file hoặc dùng sai tên trường.
        traLoiLoi('Chưa chọn tệp ảnh để tải lên.', 400);
    }

    // Nếu client gửi nhiều file dưới dạng image[] thì $_FILES['image']['error'] sẽ là mảng.
    if (is_array($_FILES['image']['error'])) {
        // Từ chối vì mỗi sản phẩm chỉ được phép có tối đa một ảnh.
        traLoiLoi('Mỗi sản phẩm chỉ chấp nhận một tệp ảnh.', 400);
    }

    // Đọc mã lỗi upload do PHP ghi nhận (UPLOAD_ERR_OK = 0 nghĩa là upload không lỗi).
    $maLoi = (int) $_FILES['image']['error'];

    // Xử lý riêng hai trường hợp vượt quá giới hạn cấu hình của PHP để thông báo chính xác.
    if ($maLoi === UPLOAD_ERR_INI_SIZE || $maLoi === UPLOAD_ERR_FORM_SIZE) {
        // Báo đúng nguyên nhân để người dùng chọn ảnh nhỏ hơn.
        traLoiLoi('Ảnh vượt quá dung lượng tối đa 2 MB cho phép.', 400);
    }

    // Mọi mã lỗi khác (không tìm thấy file, lỗi ghi tạm, v.v.) cũng là upload thất bại.
    if ($maLoi !== UPLOAD_ERR_OK) {
        // Thông báo chung kèm mã lỗi để tiện điều tra nếu cần.
        traLoiLoi('Tải tệp lên thất bại (mã lỗi ' . $maLoi . '). Vui lòng thử lại.', 400);
    }

    // Lấy đường dẫn tạm do PHP tạo; đây là file trung gian, chưa phải thư mục uploads cuối cùng.
    $tepTam = (string) $_FILES['image']['tmp_name'];

    // is_uploaded_file bảo đảm tệp thực sự đến từ request HTTP, không phải path tự dựng.
    if (!is_uploaded_file($tepTam)) {
        // Từ chối các yêu cầu gửi giả mạo đường dẫn file trên máy chủ.
        traLoiLoi('Tệp tải lên không hợp lệ.', 400);
    }

    // Đọc dung lượng tệp do PHP báo cáo và ép sang số nguyên.
    $kichThuoc = (int) ($_FILES['image']['size'] ?? 0);

    // Kiểm tra ảnh không rỗng và không vượt quá 2 MB theo đúng yêu cầu đề bài.
    if ($kichThuoc <= 0 || $kichThuoc > KICH_THUOC_ANH_TOI_DA) {
        // Báo lỗi ngay trước khi chạm vào cơ sở dữ liệu hoặc thư mục lưu trữ.
        traLoiLoi('Ảnh phải có dung lượng lớn hơn 0 và không vượt quá 2 MB.', 400);
    }

    // Khởi tạo bộ phân tích Fileinfo để đọc MIME type THỰC TẾ từ nội dung tệp.
    $congCuMime = new finfo(FILEINFO_MIME_TYPE);

    // Đọc MIME của tệp tạm; kết quả false được ép thành chuỗi rỗng để dễ so sánh.
    $mimeThucTe = (string) $congCuMime->file($tepTam);

    // Chỉ chấp nhận các MIME nằm trong bảng ánh xạ image/jpeg và image/png.
    if (!array_key_exists($mimeThucTe, LOAI_ANH_HOP_LE)) {
        // Báo lỗi với loại MIME nhận được để người dùng biết tệp của họ bị từ chối.
        traLoiLoi('Chỉ chấp nhận ảnh JPG, JPEG hoặc PNG (loại tệp nhận được: ' . $mimeThucTe . ').', 400);
    }

    // getimagesize đọc-header ảnh thật; @ để không in warning khi tệp giả mạo.
    $thongTinAnh = @getimagesize($tepTam);

    // Kiểm tra getimagesize trả về mảng có chiều rộng và chiều cao lớn hơn 0.
    if (!is_array($thongTinAnh) || ($thongTinAnh[0] ?? 0) <= 0 || ($thongTinAnh[1] ?? 0) <= 0) {
        // Tệp có thể chỉ là văn bản đổi tên thành .png, nên từ chối ở đây.
        traLoiLoi('Tệp không phải là ảnh hợp lệ. Vui lòng chọn lại.', 400);
    }

    // Đối chiếu MIME do getimagesize đọc được với MIME do Fileinfo đọc được.
    if (($thongTinAnh['mime'] ?? '') !== $mimeThucTe) {
        // Hai lớp kiểm tra không khớp nhau thì coi là dữ liệu đáng ngờ.
        traLoiLoi('Loại ảnh không nhất quán. Vui lòng chọn lại tệp JPG hoặc PNG.', 400);
    }

    // Trả về phần mở rộng được SUY RA TỪ MIME THỰC TẾ, không tin vào tên file gốc.
    return [
        // Phần mở rộng dùng để tạo tên file lưu trên máy chủ (jpg hoặc png).
        'duoi' => LOAI_ANH_HOP_LE[$mimeThucTe],
        // Đường dẫn tệp tạm cần chuyển vào thư mục uploads.
        'tmp' => $tepTam,
    ];
}

/**
 * Kiểm tra sản phẩm có tồn tại hay không trước khi lưu ảnh.
 *
 * @param PDO $pdo Kết nối cơ sở dữ liệu đang mở.
 * @param int $id  Mã sản phẩm cần kiểm tra.
 * @return void    Hàm chỉ dừng chương trình khi không tìm thấy sản phẩm.
 */
function kiemTraSanPhamTonTai(PDO $pdo, int $id): void
{
    // Chuẩn bị câu lệnh SELECT có tham số để chống SQL Injection.
    $cauLenh = $pdo->prepare('SELECT id FROM products WHERE id = :id LIMIT 1');

    // Gán mã sản phẩm vào tham số :id với kiểu số nguyên.
    $cauLenh->bindValue(':id', $id, PDO::PARAM_INT);

    // Thực thi truy vấn kiểm tra.
    $cauLenh->execute();

    // fetch trả về false nếu không có dòng nào khớp với id.
    if ($cauLenh->fetch() === false) {
        // Báo 404 để Vue biết sản phẩm đã bị xóa mất trước khi upload.
        traLoiLoi('Không tìm thấy sản phẩm có id = ' . $id . ' để tải ảnh lên.', 404);
    }
}

/**
 * Tạo tên file mới chắc chắn chưa tồn tại trong thư mục uploads.
 *
 * @param string $thuMuc Đường dẫn tuyệt đối tới thư mục lưu ảnh.
 * @param string $duoi   Phần mở rộng suy từ MIME thật (jpg hoặc png).
 * @return string        Tên file duy nhất kèm phần mở rộng.
 */
function taoTenFileDuyNhat(string $thuMuc, string $duoi): string
{
    // Lặp cho đến khi tìm được tên chưa có file trùng; random_bytes rất khó trùng nên thường chỉ chạy một lần.
    do {
        // Tạo 16 byte ngẫu nhiên an toàn rồi chuyển thành 32 ký tự hex.
        $tenCoBan = bin2hex(random_bytes(16));

        // Ghép tên ngẫu nhiên với phần mở rộng đã được kiểm tra.
        $tenFile = $tenCoBan . '.' . $duoi;

        // Kiểm tra xem tên vừa tạo đã tồn tại trên đĩa chưa.
        $daTonTai = file_exists($thuMuc . DIRECTORY_SEPARATOR . $tenFile);
    } while ($daTonTai);

    // Trả về tên file chắc chắn chưa được dùng trong thư mục uploads.
    return $tenFile;
}

// ----------------------------------------------------------------------------
// PHẦN 3: THỰC HIỆN UPLOAD
// ----------------------------------------------------------------------------

// Chỉ phục vụ phương thức POST; mọi phương thức khác (kể cả GET) đều bị từ chối.
if ($phuongThuc !== 'POST') {
    // Gửi header Allow theo chuẩn HTTP để client biết phương thức hợp lệ.
    header('Allow: POST, OPTIONS');

    // Trả lỗi 405 kèm tên phương thức nhận được.
    traLoiLoi('Phương thức ' . $phuongThuc . ' không được hỗ trợ. Hãy dùng POST.', 405);
}

// Đọc product_id từ multipart/form-data và kiểm tra định dạng.
$idSanPham = layProductIdTuPost();

// Kiểm tra toàn bộ quy tắc của tệp ảnh và lấy đường dẫn file tạm cùng phần mở rộng thật.
$tepAnh = kiemTraTepAnh();

// Kết nối MySQL; nếu lỗi thì trả JSON thay vì để PHP hiển thị trang lỗi HTML.
try {
    // Gọi hàm dùng chung trong config.php để tạo đối tượng PDO.
    $pdo = taoKetNoiPDO();
} catch (PDOException $loi) {
    // Ghi chi tiết lỗi kết nối vào log phía máy chủ.
    error_log('[upload.php] Lỗi kết nối cơ sở dữ liệu: ' . $loi->getMessage());

    // Trả thông báo chung, không tiết lộ thông tin cấu hình.
    traLoiLoi('Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.', 500);
}

// Kiểm tra sản phẩm tồn tại trước khi tạo file hay sửa cơ sở dữ liệu.
kiemTraSanPhamTonTai($pdo, $idSanPham);

// Xác định đường dẫn vật lý của thư mục uploads, tính từ thư mục chứa api/upload.php.
$thuMucUploads = __DIR__ . '/../uploads';

// Tự tạo thư mục uploads nếu chưa có để API chạy ngay sau khi deploy, không cần thao tác thủ công.
if (!is_dir($thuMucUploads) && !mkdir($thuMucUploads, 0755, true) && !is_dir($thuMucUploads)) {
    // Báo lỗi nếu không thể tạo thư mục (thường do quyền ghi của máy chủ web).
    traLoiLoi('Không thể tạo thư mục lưu ảnh. Vui lòng kiểm tra quyền ghi của máy chủ.', 500);
}

// Kiểm tra tiếp thư mục có quyền ghi hay không trước khi gọi move_uploaded_file.
if (!is_writable($thuMucUploads)) {
    // Dừng lại với thông báo rõ ràng thay vì để move_uploaded_file thất bại im lặng.
    traLoiLoi('Thư mục uploads không có quyền ghi. Vui lòng liên hệ quản trị viên.', 500);
}

// Tạo tên file duy nhất dựa trên chuỗi ngẫu nhiên và phần mở rộng lấy từ MIME thật.
$tenFileMoi = taoTenFileDuyNhat($thuMucUploads, $tepAnh['duoi']);

// Ghép đường dẫn vật lý để PHP chuyển file tạm vào thư mục uploads.
$duongDanVatLy = $thuMucUploads . DIRECTORY_SEPARATOR . $tenFileMoi;

// Di chuyển file đã upload từ thư mục tạm sang thư mục uploads.
if (!move_uploaded_file($tepAnh['tmp'], $duongDanVatLy)) {
    // Nếu không lưu được file thì dừng ngay, không đụng đến cột image trong CSDL.
    traLoiLoi('Không thể lưu ảnh vào máy chủ. Vui lòng thử lại.', 500);
}

// Chuẩn bị đường dẫn tương đối lưu vào CSDL để giao diện dùng trực tiếp trong thẻ <img src>.
$duongDanLuu = 'uploads/' . $tenFileMoi;

// Bọc câu lệnh UPDATE trong try/catch: chỉ cập nhật cột image SAU KHI file đã lưu thành công.
try {
    // Chuẩn bị câu lệnh cập nhật đường dẫn ảnh theo đúng id sản phẩm.
    $cauLenhCapNhat = $pdo->prepare('UPDATE products SET image = :image WHERE id = :id');

    // Gán đường dẫn tương đối vào tham số :image.
    $cauLenhCapNhat->bindValue(':image', $duongDanLuu, PDO::PARAM_STR);

    // Gán mã sản phẩm vào tham số :id.
    $cauLenhCapNhat->bindValue(':id', $idSanPham, PDO::PARAM_INT);

    // Thực thi câu lệnh UPDATE để ghi đường dẫn ảnh mới vào CSDL.
    $cauLenhCapNhat->execute();
} catch (PDOException $loi) {
    // Ghi chi tiết lỗi SQL vào log để điều tra.
    error_log('[upload.php] Lỗi cập nhật đường dẫn ảnh: ' . $loi->getMessage());

    // Xóa file vừa lưu để tránh rác trên máy chủ khi CSDL không cập nhật được.
    @unlink($duongDanVatLy);

    // Báo lỗi; đường dẫn ảnh cũ trong CSDL vẫn giữ nguyên vì UPDATE không thành công.
    traLoiLoi('Ảnh đã lưu nhưng không thể cập nhật cơ sở dữ liệu. Vui lòng thử lại.', 500);
}

// Trả về JSON thành công kèm đường dẫn ảnh để Vue cập nhật bảng ngay lập tức.
traLoiJson([
    // Đánh dấu thao tác upload thành công.
    'success' => true,
    // Thông báo hiển thị trên giao diện.
    'message' => 'Tải ảnh lên thành công.',
    // Dữ liệu trả về gồm id sản phẩm và đường dẫn ảnh vừa lưu.
    'data' => [
        // Mã sản phẩm đã nhận ảnh.
        'id' => $idSanPham,
        // Đường dẫn tương đối trong thư mục uploads.
        'image' => $duongDanLuu,
    ],
], 200);
