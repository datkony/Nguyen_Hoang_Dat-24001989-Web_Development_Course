<?php
// ============================================================================
// GIAI ĐOẠN 4: KỊCH BẢN KIỂM TRA TỰ ĐỘNG API
// ----------------------------------------------------------------------------
// Tệp này kiểm tra bốn tình huống bắt buộc của đề bài và một số trường hợp
// biên bổ sung. Kịch bản gọi trực tiếp API PHP bằng cURL, nên cần chạy máy chủ
// web (Apache hoặc PHP built-in server) trước khi thực thi.
//
// Cách chạy:
//   php tests/kiem-tra-api.php http://localhost:8080/api/products.php
//
// Nếu không truyền tham số, kịch bản dùng địa chỉ mặc định bên dưới.
// Kịch bản sẽ tự xóa các bản ghi do nó tạo ra để không làm bẩn cơ sở dữ liệu.
// ============================================================================

// Bật chế độ kiểm tra kiểu nghiêm ngặt cho các hàm trong tệp này.
declare(strict_types=1);

// Đọc địa chỉ API từ tham số dòng lệnh, hoặc dùng giá trị mặc định nếu không có.
$diaChiApi = $argv[1] ?? 'http://localhost:8080/api/products.php';

// Khai báo biến đếm số bài kiểm tra đã chạy.
$soBaiDaChay = 0;

// Khai báo biến đếm số bài kiểm tra thất bại.
$soBaiThatBai = 0;

/**
 * Gửi một yêu cầu HTTP tới API và trả về mã trạng thái cùng dữ liệu JSON.
 *
 * @param string               $diaChi  Địa chỉ API cần gọi.
 * @param string               $phuongThuc Phương thức HTTP (GET, POST, PUT, DELETE).
 * @param array<string, mixed>|null $duLieu Dữ liệu gửi kèm dưới dạng JSON.
 * @return array{status: int, json: array<string, mixed>|null, raw: string}
 */
function goiApi(string $diaChi, string $phuongThuc = 'GET', ?array $duLieu = null): array
{
    // Khởi tạo phiên cURL mới cho yêu cầu hiện tại.
    $curl = curl_init($diaChi);

    // Khai báo mảng header gửi kèm yêu cầu.
    $tieuDe = [
        // Yêu cầu máy chủ trả về JSON.
        'Accept: application/json',
    ];

    // Thiết lập phương thức HTTP cần sử dụng.
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $phuongThuc);

    // Yêu cầu cURL trả nội dung phản hồi về dưới dạng chuỗi thay vì in trực tiếp.
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // Đặt thời gian chờ tối đa 10 giây cho mỗi yêu cầu để tránh treo vô hạn.
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);

    // Nếu có dữ liệu gửi kèm thì chuyển thành JSON và thêm header Content-Type.
    if ($duLieu !== null) {
        // Mã hóa mảng dữ liệu thành chuỗi JSON, giữ nguyên tiếng Việt.
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($duLieu, JSON_UNESCAPED_UNICODE));

        // Khai báo nội dung gửi lên là JSON.
        $tieuDe[] = 'Content-Type: application/json';
    }

    // Gắn danh sách header đã chuẩn bị vào yêu cầu.
    curl_setopt($curl, CURLOPT_HTTPHEADER, $tieuDe);

    // Thực thi yêu cầu và lấy nội dung phản hồi thô.
    $noiDungTho = curl_exec($curl);

    // Lấy mã trạng thái HTTP của phản hồi.
    $maHttp = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

    // Đóng phiên cURL để giải phóng tài nguyên.
    curl_close($curl);

    // Nếu cURL không trả về chuỗi (lỗi kết nối), dùng chuỗi rỗng để tránh lỗi kiểu.
    if (!is_string($noiDungTho)) {
        // Gán chuỗi rỗng làm nội dung dự phòng.
        $noiDungTho = '';
    }

    // Giải mã nội dung JSON; nếu không hợp lệ thì trả về null.
    $duLieuJson = json_decode($noiDungTho, true);

    // Trả về đầy đủ mã trạng thái, JSON đã giải mã và nội dung thô để chẩn đoán.
    return [
        // Mã trạng thái HTTP nhận được.
        'status' => $maHttp,
        // Mảng JSON nếu giải mã thành công, ngược lại là null.
        'json' => is_array($duLieuJson) ? $duLieuJson : null,
        // Nội dung gốc phục vụ việc in lỗi khi cần.
        'raw' => $noiDungTho,
    ];
}

/**
 * Ghi nhận và in kết quả của một bài kiểm tra.
 *
 * @param string $tenBai    Mô tả ngắn gọn bài kiểm tra.
 * @param bool   $dieuKien  true nếu bài kiểm tra đạt yêu cầu.
 * @param string $chiTiet   Thông tin bổ sung hiển thị khi cần.
 * @return void
 */
function kiemTra(string $tenBai, bool $dieuKien, string $chiTiet = ''): void
{
    // Cho phép hàm truy cập hai biến đếm toàn cục.
    global $soBaiDaChay, $soBaiThatBai;

    // Tăng số bài đã chạy sau mỗi lần gọi.
    $soBaiDaChay++;

    // Nếu bài kiểm tra đạt, in nhãn ĐẠT màu xanh (nếu terminal hỗ trợ).
    if ($dieuKien) {
        // In kết quả đạt cùng mô tả bài kiểm tra.
        echo "  [ĐẠT]       {$tenBai}\n";

        // Kết thúc hàm sớm vì không cần xử lý phần thất bại.
        return;
    }

    // Tăng số bài thất bại khi điều kiện không đúng.
    $soBaiThatBai++;

    // In kết quả không đạt kèm chi tiết để dễ điều tra.
    echo "  [KHÔNG ĐẠT] {$tenBai}" . ($chiTiet !== '' ? " -> {$chiTiet}" : '') . "\n";
}

/**
 * Tìm một sản phẩm trong mảng danh sách theo id.
 *
 * @param array<int, array<string, mixed>> $danhSach Mảng sản phẩm từ API.
 * @param int                              $id       Mã sản phẩm cần tìm.
 * @return array<string, mixed>|null Sản phẩm tìm thấy hoặc null.
 */
function timTheoId(array $danhSach, int $id): ?array
{
    // Duyệt lần lượt từng sản phẩm trong danh sách.
    foreach ($danhSach as $sanPham) {
        // So sánh id sau khi ép kiểu số để tránh khác biệt chuỗi/số.
        if ((int) ($sanPham['id'] ?? 0) === $id) {
            // Trả về sản phẩm ngay khi tìm thấy.
            return $sanPham;
        }
    }

    // Trả về null nếu không có sản phẩm nào khớp id.
    return null;
}

// In tiêu đề khu vực kiểm tra và địa chỉ API đang dùng.
echo "=== KIỂM TRA API QUẢN LÝ SẢN PHẨM ===\n";
echo "Địa chỉ API: {$diaChiApi}\n\n";

// Tạo tên duy nhất cho sản phẩm hợp lệ dùng trong bài kiểm tra thêm/sửa/xóa.
$tenSanPhamThu = 'Sản phẩm kiểm tra ' . date('His') . ' ' . substr(uniqid(), -5);

// Tạo tên duy nhất cho sản phẩm không hợp lệ dùng trong bài kiểm tra tồn kho âm.
$tenSanPhamKhongHopLe = 'Sản phẩm tồn âm ' . date('His') . ' ' . substr(uniqid(), -5);

// Khai báo biến lưu id sản phẩm hợp lệ sau khi thêm thành công.
$idSanPhamThu = null;

// ----------------------------------------------------------------------------
// BÀI 1: LẤY DANH SÁCH SẢN PHẨM BAN ĐẦU
// ----------------------------------------------------------------------------
echo "1. Kiểm tra lấy danh sách sản phẩm\n";

// Gọi GET để lấy danh sách sản phẩm hiện có.
$phanHoiDanhSach = goiApi($diaChiApi);

// Kiểm tra HTTP trả về 200 OK.
kiemTra('GET trả về mã 200', $phanHoiDanhSach['status'] === 200, 'Mã nhận được: ' . $phanHoiDanhSach['status']);

// Kiểm tra phản hồi có trường success = true.
kiemTra(
    'Phản hồi có success = true',
    ($phanHoiDanhSach['json']['success'] ?? null) === true,
    $phanHoiDanhSach['raw']
);

// Kiểm tra trường data là một mảng.
kiemTra(
    'Phản hồi có data là mảng',
    is_array($phanHoiDanhSach['json']['data'] ?? null),
    $phanHoiDanhSach['raw']
);

// Lấy mảng sản phẩm ban đầu để dùng cho các bài kiểm tra tiếp theo.
$danhSachBanDau = is_array($phanHoiDanhSach['json']['data'] ?? null)
    ? $phanHoiDanhSach['json']['data']
    : [];

// Ghi lại số lượng sản phẩm ban đầu để so sánh sau khi thêm/xóa.
$soLuongBanDau = count($danhSachBanDau);

// Kiểm tra ba sản phẩm mẫu của đề bài có trong cơ sở dữ liệu hay không.
$coButBi = false;
$coVoOLy = false;
$coThuocKe = false;

// Duyệt danh sách để tìm từng sản phẩm mẫu theo tên.
foreach ($danhSachBanDau as $sanPhamMau) {
    // Lấy tên sản phẩm, mặc định chuỗi rỗng nếu thiếu trường.
    $ten = (string) ($sanPhamMau['name'] ?? '');

    // Đánh dấu nếu tìm thấy Bút bi.
    if ($ten === 'Bút bi') {
        // Ghi nhận sản phẩm mẫu thứ nhất.
        $coButBi = true;
    }

    // Đánh dấu nếu tìm thấy Vở ô ly.
    if ($ten === 'Vở ô ly') {
        // Ghi nhận sản phẩm mẫu thứ hai.
        $coVoOLy = true;
    }

    // Đánh dấu nếu tìm thấy Thước kẻ.
    if ($ten === 'Thước kẻ') {
        // Ghi nhận sản phẩm mẫu thứ ba.
        $coThuocKe = true;
    }
}

// Kiểm tra cả ba sản phẩm mẫu đều tồn tại trong danh sách.
kiemTra('Có đủ ba sản phẩm mẫu trong cơ sở dữ liệu', $coButBi && $coVoOLy && $coThuocKe);

// Kiểm tra id, price, stock được trả về dưới dạng số nguyên đúng kiểu JSON.
$kieuDuLieuDung = true;

// Duyệt từng sản phẩm để kiểm tra kiểu dữ liệu của ba trường số.
foreach ($danhSachBanDau as $sanPhamKiemTraKieu) {
    // Nếu có bất kỳ trường nào không phải số nguyên thì đánh dấu sai.
    if (
        !is_int($sanPhamKiemTraKieu['id'] ?? null)
        || !is_int($sanPhamKiemTraKieu['price'] ?? null)
        || !is_int($sanPhamKiemTraKieu['stock'] ?? null)
    ) {
        // Đánh dấu dữ liệu trả về chưa đúng kiểu.
        $kieuDuLieuDung = false;
    }
}

// Ghi nhận kết quả kiểm tra kiểu dữ liệu.
kiemTra('id, price, stock là số nguyên trong JSON', $kieuDuLieuDung);

// In dòng trống để tách các nhóm bài kiểm tra.
echo "\n";

// ----------------------------------------------------------------------------
// BÀI 2: THÊM SẢN PHẨM HỢP LỆ RỒI TẢI LẠI DANH SÁCH
// ----------------------------------------------------------------------------
echo "2. Kiểm tra thêm sản phẩm hợp lệ\n";

// Gửi POST thêm một sản phẩm có dữ liệu hoàn toàn hợp lệ.
$phanHoiThem = goiApi($diaChiApi, 'POST', [
    // Tên sản phẩm duy nhất để tránh trùng với dữ liệu cũ.
    'name' => $tenSanPhamThu,
    // Đơn giá là số nguyên dương.
    'price' => 15000,
    // Tồn kho là số nguyên không âm.
    'stock' => 7,
]);

// Kiểm tra API trả về mã 201 Created cho thao tác thêm thành công.
kiemTra('POST trả về mã 201', $phanHoiThem['status'] === 201, 'Mã nhận được: ' . $phanHoiThem['status']);

// Kiểm tra trường success = true.
kiemTra(
    'POST có success = true',
    ($phanHoiThem['json']['success'] ?? null) === true,
    $phanHoiThem['raw']
);

// Lấy id sản phẩm vừa được tạo từ trường data.id.
$idSanPhamThu = isset($phanHoiThem['json']['data']['id'])
    ? (int) $phanHoiThem['json']['data']['id']
    : null;

// Kiểm tra id mới là số nguyên dương.
kiemTra('Sản phẩm mới có id hợp lệ', is_int($idSanPhamThu) && $idSanPhamThu > 0);

// Gọi lại GET để mô phỏng việc tải lại trang sau khi thêm.
$phanHoiSauThem = goiApi($diaChiApi);

// Lấy danh sách mới nhất từ phản hồi.
$danhSachSauThem = is_array($phanHoiSauThem['json']['data'] ?? null)
    ? $phanHoiSauThem['json']['data']
    : [];

// Tìm sản phẩm vừa thêm trong danh sách mới.
$sanPhamVuaThem = $idSanPhamThu !== null ? timTheoId($danhSachSauThem, $idSanPhamThu) : null;

// Kiểm tra sản phẩm mới vẫn xuất hiện sau khi gọi lại API (tương đương tải lại trang).
kiemTra('Sản phẩm mới xuất hiện sau khi tải lại danh sách', $sanPhamVuaThem !== null);

// Kiểm tra dữ liệu của sản phẩm mới được lưu đúng.
kiemTra(
    'Dữ liệu sản phẩm mới được lưu đúng',
    $sanPhamVuaThem !== null
        && $sanPhamVuaThem['name'] === $tenSanPhamThu
        && $sanPhamVuaThem['price'] === 15000
        && $sanPhamVuaThem['stock'] === 7
);

// Kiểm tra số lượng sản phẩm tăng thêm đúng một bản ghi.
kiemTra('Số lượng sản phẩm tăng thêm 1', count($danhSachSauThem) === $soLuongBanDau + 1);

// In dòng trống để tách nhóm.
echo "\n";

// ----------------------------------------------------------------------------
// BÀI 3: SỬA ĐƠN GIÁ CỦA SẢN PHẨM
// ----------------------------------------------------------------------------
echo "3. Kiểm tra sửa đơn giá sản phẩm\n";

// Nếu chưa có id sản phẩm thử thì không thể kiểm tra sửa; dùng id 0 để bài kiểm tra thất bại rõ ràng.
$idDeSua = $idSanPhamThu ?? 0;

// Gửi PUT cập nhật đơn giá thành 9999 và đổi tồn kho thành 9.
$phanHoiSua = goiApi($diaChiApi . '?id=' . $idDeSua, 'PUT', [
    // Giữ nguyên tên sản phẩm thử.
    'name' => $tenSanPhamThu,
    // Đơn giá mới cần kiểm tra sau khi cập nhật.
    'price' => 9999,
    // Số lượng tồn mới.
    'stock' => 9,
]);

// Kiểm tra HTTP trả về 200 OK.
kiemTra('PUT trả về mã 200', $phanHoiSua['status'] === 200, 'Mã nhận được: ' . $phanHoiSua['status']);

// Kiểm tra trường success = true.
kiemTra(
    'PUT có success = true',
    ($phanHoiSua['json']['success'] ?? null) === true,
    $phanHoiSua['raw']
);

// Gọi lại GET để lấy dữ liệu sau khi sửa.
$phanHoiSauSua = goiApi($diaChiApi);

// Lấy danh sách mới nhất.
$danhSachSauSua = is_array($phanHoiSauSua['json']['data'] ?? null)
    ? $phanHoiSauSua['json']['data']
    : [];

// Tìm lại sản phẩm vừa sửa theo id.
$sanPhamSauSua = $idSanPhamThu !== null ? timTheoId($danhSachSauSua, $idSanPhamThu) : null;

// Kiểm tra bảng dữ liệu trả về đã có đơn giá mới.
kiemTra(
    'Đơn giá mới 9999 được hiển thị trong danh sách',
    $sanPhamSauSua !== null && $sanPhamSauSua['price'] === 9999,
    $sanPhamSauSua !== null ? 'Giá nhận được: ' . $sanPhamSauSua['price'] : 'Không tìm thấy sản phẩm'
);

// Kiểm tra tồn kho mới cũng được cập nhật đúng.
kiemTra('Số lượng tồn mới 9 được cập nhật đúng', $sanPhamSauSua !== null && $sanPhamSauSua['stock'] === 9);

// In dòng trống để tách nhóm.
echo "\n";

// ----------------------------------------------------------------------------
// BÀI 4: GỬI TRỰC TIẾP SẢN PHẨM CÓ TỒN KHO BẰNG -1
// ----------------------------------------------------------------------------
echo "4. Kiểm tra từ chối tồn kho âm\n";

// Lấy số lượng hiện tại trước khi gửi dữ liệu không hợp lệ.
$phanHoiTruocKhiGuiSai = goiApi($diaChiApi);

// Lấy danh sách trước khi gửi yêu cầu sai.
$danhSachTruocKhiGuiSai = is_array($phanHoiTruocKhiGuiSai['json']['data'] ?? null)
    ? $phanHoiTruocKhiGuiSai['json']['data']
    : [];

// Ghi lại số lượng bản ghi trước khi thử thêm sản phẩm sai.
$soLuongTruocKhiGuiSai = count($danhSachTruocKhiGuiSai);

// Gửi POST trực tiếp với stock = -1, giống hành vi tấn công hoặc gọi API thủ công.
$phanHoiTonAm = goiApi($diaChiApi, 'POST', [
    // Tên sản phẩm duy nhất để kiểm tra không có bản ghi nào được tạo.
    'name' => $tenSanPhamKhongHopLe,
    // Đơn giá hợp lệ để cô lập lỗi ở trường tồn kho.
    'price' => 5000,
    // Tồn kho âm là dữ liệu không hợp lệ theo đề bài.
    'stock' => -1,
]);

// Kiểm tra API trả về mã 400 Bad Request.
kiemTra('POST tồn kho -1 trả về mã 400', $phanHoiTonAm['status'] === 400, 'Mã nhận được: ' . $phanHoiTonAm['status']);

// Kiểm tra trường success = false.
kiemTra(
    'POST tồn kho -1 có success = false',
    ($phanHoiTonAm['json']['success'] ?? null) === false,
    $phanHoiTonAm['raw']
);

// Kiểm tra API trả về thông báo lỗi trong trường message.
kiemTra(
    'POST tồn kho -1 có message giải thích',
    is_string($phanHoiTonAm['json']['message'] ?? null)
        && $phanHoiTonAm['json']['message'] !== '',
    $phanHoiTonAm['raw']
);

// Gọi lại GET để kiểm tra MySQL không có bản ghi mới.
$phanHoiSauTonAm = goiApi($diaChiApi);

// Lấy danh sách sau khi gửi dữ liệu sai.
$danhSachSauTonAm = is_array($phanHoiSauTonAm['json']['data'] ?? null)
    ? $phanHoiSauTonAm['json']['data']
    : [];

// Kiểm tra số lượng bản ghi không thay đổi.
kiemTra('MySQL không có bản ghi mới sau yêu cầu sai', count($danhSachSauTonAm) === $soLuongTruocKhiGuiSai);

// Kiểm tra không tìm thấy bản ghi nào mang tên của sản phẩm không hợp lệ.
$timThaySanPhamSai = false;

// Duyệt danh sách để tìm tên sản phẩm không hợp lệ.
foreach ($danhSachSauTonAm as $sanPhamSauTonAm) {
    // Nếu tên trùng với tên đã gửi thì đánh dấu đã tìm thấy.
    if (($sanPhamSauTonAm['name'] ?? '') === $tenSanPhamKhongHopLe) {
        // Ghi nhận rằng bản ghi sai đã lọt vào cơ sở dữ liệu.
        $timThaySanPhamSai = true;
    }
}

// Kiểm tra bản ghi sai không tồn tại trong danh sách.
kiemTra('Không tìm thấy sản phẩm tồn kho -1 trong danh sách', $timThaySanPhamSai === false);

// In dòng trống để tách nhóm.
echo "\n";

// ----------------------------------------------------------------------------
// BÀI 5: CÁC TRƯỜNG HỢP KIỂM TRA DỮ LIỆU BỔ SUNG
// ----------------------------------------------------------------------------
echo "5. Kiểm tra các trường hợp dữ liệu không hợp lệ khác\n";

// Gửi sản phẩm có tên rỗng để kiểm tra quy tắc độ dài tên.
$phanHoiTenRong = goiApi($diaChiApi, 'POST', [
    // Tên chỉ gồm khoảng trắng, sau khi trim sẽ rỗng.
    'name' => '   ',
    // Đơn giá hợp lệ.
    'price' => 1000,
    // Tồn kho hợp lệ.
    'stock' => 1,
]);

// Kiểm tra API từ chối tên rỗng.
kiemTra('Từ chối tên rỗng hoặc toàn khoảng trắng', $phanHoiTenRong['status'] === 400);

// Gửi sản phẩm có tên dài hơn 100 ký tự.
$phanHoiTenDai = goiApi($diaChiApi, 'POST', [
    // Tạo chuỗi 101 ký tự "a" để vượt giới hạn cột VARCHAR(100).
    'name' => str_repeat('a', 101),
    // Đơn giá hợp lệ.
    'price' => 1000,
    // Tồn kho hợp lệ.
    'stock' => 1,
]);

// Kiểm tra API từ chối tên vượt 100 ký tự.
kiemTra('Từ chối tên dài hơn 100 ký tự', $phanHoiTenDai['status'] === 400);

// Gửi sản phẩm có đơn giá bằng 0.
$phanHoiGiaKhong = goiApi($diaChiApi, 'POST', [
    // Tên hợp lệ.
    'name' => 'Sản phẩm giá 0',
    // Giá 0 không hợp lệ vì phải lớn hơn 0.
    'price' => 0,
    // Tồn kho hợp lệ.
    'stock' => 1,
]);

// Kiểm tra API từ chối đơn giá bằng 0.
kiemTra('Từ chối đơn giá bằng 0', $phanHoiGiaKhong['status'] === 400);

// Gửi sản phẩm có đơn giá là số thực.
$phanHoiGiaThuc = goiApi($diaChiApi, 'POST', [
    // Tên hợp lệ.
    'name' => 'Sản phẩm giá thực',
    // Số thực không được chấp nhận vì đề bài yêu cầu số nguyên.
    'price' => 1000.5,
    // Tồn kho hợp lệ.
    'stock' => 1,
]);

// Kiểm tra API từ chối đơn giá không phải số nguyên.
kiemTra('Từ chối đơn giá là số thực', $phanHoiGiaThuc['status'] === 400);

// Gửi yêu cầu sửa một id không tồn tại.
$phanHoiSuaKhongTonTai = goiApi($diaChiApi . '?id=2147483647', 'PUT', [
    // Tên hợp lệ.
    'name' => 'Sản phẩm không tồn tại',
    // Đơn giá hợp lệ.
    'price' => 1000,
    // Tồn kho hợp lệ.
    'stock' => 1,
]);

// Kiểm tra API trả về 404 khi sửa sản phẩm không tồn tại.
kiemTra('Sửa sản phẩm không tồn tại trả về 404', $phanHoiSuaKhongTonTai['status'] === 404);

// Gửi yêu cầu xóa một id không tồn tại.
$phanHoiXoaKhongTonTai = goiApi($diaChiApi . '?id=2147483647', 'DELETE');

// Kiểm tra API trả về 404 khi xóa sản phẩm không tồn tại.
kiemTra('Xóa sản phẩm không tồn tại trả về 404', $phanHoiXoaKhongTonTai['status'] === 404);

// Gửi yêu cầu PUT thiếu tham số id.
$phanHoiThieuId = goiApi($diaChiApi, 'PUT', [
    // Tên hợp lệ.
    'name' => 'Thiếu id',
    // Đơn giá hợp lệ.
    'price' => 1000,
    // Tồn kho hợp lệ.
    'stock' => 1,
]);

// Kiểm tra API trả về 400 khi thiếu id.
kiemTra('Sửa sản phẩm thiếu id trả về 400', $phanHoiThieuId['status'] === 400);

// In dòng trống để tách nhóm.
echo "\n";

// ----------------------------------------------------------------------------
// BÀI 6: XÓA SẢN PHẨM VÀ KIỂM TRA LẠI DANH SÁCH
// ----------------------------------------------------------------------------
echo "6. Kiểm tra xóa sản phẩm\n";

// Nếu có sản phẩm thử thì gửi DELETE theo đúng id.
$phanHoiXoa = $idSanPhamThu !== null
    ? goiApi($diaChiApi . '?id=' . $idSanPhamThu, 'DELETE')
    : ['status' => 0, 'json' => null, 'raw' => 'Không có sản phẩm để xóa'];

// Kiểm tra HTTP trả về 200 OK.
kiemTra('DELETE trả về mã 200', $phanHoiXoa['status'] === 200, 'Mã nhận được: ' . $phanHoiXoa['status']);

// Kiểm tra trường success = true.
kiemTra(
    'DELETE có success = true',
    ($phanHoiXoa['json']['success'] ?? null) === true,
    $phanHoiXoa['raw']
);

// Gọi lại GET để mô phỏng tải lại trang sau khi xóa.
$phanHoiSauXoa = goiApi($diaChiApi);

// Lấy danh sách mới nhất sau khi xóa.
$danhSachSauXoa = is_array($phanHoiSauXoa['json']['data'] ?? null)
    ? $phanHoiSauXoa['json']['data']
    : [];

// Kiểm tra sản phẩm đã xóa không còn trong danh sách.
kiemTra(
    'Sản phẩm đã xóa không còn trong danh sách',
    $idSanPhamThu === null || timTheoId($danhSachSauXoa, $idSanPhamThu) === null
);

// Kiểm tra số lượng trở về bằng số lượng ban đầu.
kiemTra('Số lượng sản phẩm trở về ban đầu', count($danhSachSauXoa) === $soLuongBanDau);

// In dòng trống để tách phần tổng kết.
echo "\n";

// ----------------------------------------------------------------------------
// DỌN DẸP: XÓA CÁC BẢN GHI CÒN SÓT LẠI NẾU BÀI KIỂM TRA THẤT BẠI GIỮA CHỪNG
// ----------------------------------------------------------------------------
if ($idSanPhamThu !== null) {
    // Gọi DELETE lần nữa; nếu sản phẩm đã bị xóa thì API trả 404 và không ảnh hưởng gì.
    goiApi($diaChiApi . '?id=' . $idSanPhamThu, 'DELETE');
}

// ----------------------------------------------------------------------------
// TỔNG KẾT
// ----------------------------------------------------------------------------
// Tính số bài kiểm tra đạt được.
$soBaiDat = $soBaiDaChay - $soBaiThatBai;

// In dòng tổng kết số bài đạt trên tổng số bài đã chạy.
echo "=== TỔNG KẾT: {$soBaiDat}/{$soBaiDaChay} bài kiểm tra đạt ===\n";

// Thông báo khi toàn bộ bài kiểm tra đều đạt.
if ($soBaiThatBai === 0) {
    // In thông báo thành công.
    echo "Tất cả bài kiểm tra đều đạt.\n";
} else {
    // In số bài thất bại để người dùng biết cần kiểm tra lại.
    echo "Có {$soBaiThatBai} bài kiểm tra không đạt.\n";
}

// Thoát với mã 0 nếu tất cả đạt, mã 1 nếu có bài thất bại.
// Mã thoát giúp các công cụ CI/CD nhận biết kết quả kiểm tra.
exit($soBaiThatBai === 0 ? 0 : 1);
