<?php
require_once 'model/product.php';

function removeItem(int $id) {
    try {
        deleteProduct($id);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
?>