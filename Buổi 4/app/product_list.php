<?php
require_once 'model/product.php';
require 'product_delete.php';

function goToEditPage(int $id) {
    header("Location: product_edit.php?id=$id&status=success");
    exit();
}

function removeItemAndReload(int $id) {
    removeItem($id);
    header("Location: product_list.php?id=$id&status=success");
    exit();
}

$item_list = getAllProduct();

if (isset($_GET['action'])) {
    $id = $_GET['id'] ?? 1;

    if ($_GET['action'] === 'edit') {
        goToEditPage($id); // Gọi hàm xử lý giỏ hàng
    } elseif ($_GET['action'] === 'remove') {
        removeItemAndReload($id); // Gọi hàm thêm mặt hàng
    }
}

require_once 'view/header.php'
?>


<h1>Giỏ hàng</h1>
<table>
    <tr>
        <th>ID</th>
        <th>Tên mặt hàng</th>
        <th>Giá</th>
        <th>Số lượng</th>
        <th>Chức năng</th>
    </tr>
    <?php foreach ($item_list as $item): ?>
        <tr>
            <td><?php echo $item['id']; ?></td>
            <td><?php echo $item['name']; ?></td> 
            <td><?php echo $item['price']; ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td>
                <a href="product_list.php?action=edit&id=<?php echo $item['id']; ?>" class="btn">Sửa</button>
                <a href="product_list.php?action=remove&id=<?php echo $item['id']; ?>" class="btn">Xóa</button>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php
require_once 'view/footer.php'
?>