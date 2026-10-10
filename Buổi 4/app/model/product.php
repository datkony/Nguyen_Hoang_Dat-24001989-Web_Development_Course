<?php
require_once 'common/dbConnect.php';

function initTable() {
    global $conn;
    $sql = "CREATE TABLE IF NOT EXISTS cart_items(
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        quantity INT NOT NULL
    )";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
}

function getAllProduct() {
    initTable();

    global $conn;
    $sql = "SELECT * FROM cart_items";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductById(int $id) {
    initTable();

    global $conn;
    $sql = "SELECT * FROM cart_items WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addProduct(string $name, float $price, int $quantity) {
    initTable();

    global $conn;
    $sql = "INSERT INTO cart_items(name, price, quantity) VALUES (:name, :price, :quantity)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":price", $price);
    $stmt->bindParam(":quantity", $quantity);
    
    return $stmt->execute();
}

function updateProduct(int $id, string $name, float $price, int $quantity) {
    $tuple = getProductById($id);

    if (!$tuple) {
        throw new InvalidArgumentException("Mặt hàng không tồn tại");
    }

    global $conn;
    $sql = "UPDATE cart_items 
            SET name = :name, price = :price, quantity = :quantity
            WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":price", $price);
    $stmt->bindParam(":quantity", $quantity);
    
    return $stmt->execute();
}

function deleteProduct(int $id) {
    $tuple = getProductById($id);

    // Kiểm tra bằng PDO
    if (!$tuple) {
        throw new InvalidArgumentException("Mặt hàng không tồn tại");
    }

    global $conn;
    $sql = "DELETE FROM cart_items WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    
    return $stmt->execute();
}
?>