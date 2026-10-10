<?php
require_once 'model/product.php';
$id = $_GET['id'] ?? null;

require_once 'view/header.php'
?>

<h1>Sửa mặt hàng id=#<?php echo $id; ?></h1>
<form action="" method="POST">
    <label>Nhập tên mặt hàng:</label>
    <input type="text" name="name" required><br>
    <label>Nhập đơn giá:</label>
    <input type="number" step="any" name="price" required><br>
    <label>Nhập số lượng:</label>
    <input type="number" name="quantity" required><br>
    <input type="submit" value="Submit">
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["name"]) && isset($_POST["price"]) && isset($_POST["quantity"])) {
        
        $name = trim($_POST["name"]);
        $price = (float) $_POST["price"];
        $quantity = (int) $_POST["quantity"];

        if ($price <= 0) {
            $errors[] = "Giá mặt hàng phải lớn hơn 0!";
        } 

        if ($quantity < 0) {
            $errors[] = "Số lượng mặt hàng phải lớn hơn hoặc bằng 0!";
        }

        if (empty($errors)) {
            try {
                updateProduct($id, $name, $price, $quantity);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            
            header("Location: product_list.php?status=success");
            exit();
        } else {
            echo "<p style='color:red;'>". $errors[0] ."</p>";
        }
    } else {
        echo "<p style='color:red;'>Vui lòng điền đầy đủ thông tin!</p>";
    }
}
?>

<?php
require_once 'view/footer.php'
?>
