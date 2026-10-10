<?php
require_once 'view/header.php'
?>

<?php
function goToViewPage() {
    header("Location: product_list.php?status=success");
    exit();
}

function goToAddItemPage() {
    header("Location: product_add.php?status=success");
    exit();
}

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'view') {
        goToViewPage(); // Gọi hàm xử lý giỏ hàng
    } elseif ($_GET['action'] === 'add') {
        goToAddItemPage(); // Gọi hàm thêm mặt hàng
    }
}
?>

<a href="index.php?action=view" class="button">Xem giỏ hàng</a>
<a href="index.php?action=add" class="button">Thêm mặt hàng vào giỏ</a>

<?php
require_once 'view/footer.php'
?>