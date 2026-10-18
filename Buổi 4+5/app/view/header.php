<?php
/**
 * ============================================================================
 * FILE: view/header.php   (YÊU CẦU IV – dùng view/header.php cho các trang)
 * ----------------------------------------------------------------------------
 * MỤC ĐÍCH: Phần MỞ ĐẦU chung của mọi trang (DOCTYPE, <head>, menu điều
 * hướng, hiển thị tên tài khoản + nút Đăng xuất theo yêu cầu mục V).
 * Mọi nút bấm chức năng (điều hướng trang, đăng nhập, đăng xuất...) đều viết
 * bằng thẻ <button type="button" onclick="window.location.href='...'">,
 * KHÔNG dùng thẻ <a>.
 *
 * CÁCH DÙNG (trang PHP phải xử lý PHP logic + header('Location') TRƯỚC file này):
 *   require_once __DIR__ . '/view/header.php';   // dòng này in HTML ra màn hình
 *   ...html...
 *   require_once __DIR__ . '/view/footer.php';
 *
 * LƯU Ý QUAN TRỌNG:
 *   header.php IN HTML RA NGAY KHI ĐƯỢC NHÚNG, nên mọi lệnh header('Location:')
 *   PHẢI chạy trước khi nhúng file này (nếu không sẽ bị
 *   "headers already sent" và mất tác dụng chuyển hướng).
 * ============================================================================
 */

// Đảm bảo các hàm phiên (isLoggedIn/currentUsername) luôn có sẵn, kể cả khi
// trang quên nhúng common/auth.php. require_once -> không khởi tạo session lần 2.
require_once __DIR__ . '/../common/auth.php';

// Tên trang hiển thị trên tab trình duyệt; trang con gán $pageTitle TRƯỚC khi
// nhúng header (?? là toán tử coalesce: nếu chưa gán thì dùng giá trị mặc định).
$pageTitle = $pageTitle ?? 'Quản lý giỏ hàng - PHP & MySQL';

// Lấy tên tài khoản đang đăng nhập để hiển thị trên menu (null nếu chưa đăng nhập).
$currentUsername = currentUsername();

// Tạo biến HTML-ánh-xạ cho tên tài khoản: tránh XSS (chèn script qua tên đăng nhập).
$currentUsernameHtml = htmlspecialchars((string) $currentUsername, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Khai báo bộ mã hóa UTF-8 để tiếng Việt có dấu hiển thị đúng -->
    <meta charset="UTF-8">

    <!-- Responsive cơ bản trên điện thoại -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- echo $pageTitle: đã htmlspecialchars ở trên nên an toàn khi in vào <title> -->
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>

    <!--
        YÊU CẦU BÀI NỘP chỉ có HTML cơ bản (table, form, input, button...),
        KHÔNG yêu cầu CSS -> không liên kết file style nào trong <head>.
        MỌI nút bấm chức năng (điều hướng, thêm, sửa, xóa, submit...) đều dùng
        thẻ <button>; KHÔNG dùng thẻ <a> cho các nút bấm nữa.
    -->
</head>
<body>

<!-- ===================== THANH ĐIỀU HƯỚNG CHUNG ===================== -->
<header>
    <nav>

        <!--
            Các nút điều hướng trang chính – dùng thẻ <button> (KHÔNG dùng <a>).
            - type="button": chỉ làm hành động ở onclick, KHÔNG phải nút submit.
            - onclick="window.location.href=...": thay cho href của thẻ <a>.
            - Chuỗi URL không chứa nháy kép/đơn nên nhúng thẳng là an toàn.
        -->
        <button type="button" onclick="window.location.href='index.php'">Trang chủ</button>
        <button type="button" onclick="window.location.href='product_list.php'">Danh sách sản phẩm</button>

        <?php if ($currentUsername !== null): ?>
            <!--
                ĐÃ ĐĂNG NHẬP: hiển thị tên tài khoản + NÚT Đăng xuất
                (YÊU CẦU III/ V: "Hiển thị tên tài khoản và liên kết Đăng xuất" –
                nay thực hiện bằng thẻ <button> thay cho thẻ <a>).
                $currentUsernameHtml đã qua htmlspecialchars -> an toàn XSS.
            -->
            <span>Xin chào, <strong><?php echo $currentUsernameHtml; ?></strong></span>
            <button type="button" onclick="window.location.href='logout.php'">Đăng xuất</button>

        <?php else: ?>
            <!--
                CHƯA ĐĂNG NHẬP: chỉ hiển thị 2 nút này.
                (Các trang quản lý vẫn sẽ tự chặn bằng requireLogin().)
            -->
            <button type="button" onclick="window.location.href='login.php'">Đăng nhập</button>
            <button type="button" onclick="window.location.href='register.php'">Đăng ký</button>

        <?php endif; ?>
    </nav>
</header>

<!--
    <main>: vùng nội dung chính của trang.
    Mỗi trang con chỉ cần viết phần nội dung của riêng nó.
-->
<main>
