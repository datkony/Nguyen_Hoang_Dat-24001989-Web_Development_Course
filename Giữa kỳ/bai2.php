<?php
$prices = [12 , 51, 36, 49, 45];

function calculateTotal($prices) {
    if (!is_array($prices)) {
        throw new InvalidArgumentException("Đầu vào phải là mảng chứa giá!");
    }

    $totalPrice = 0;

    foreach ($prices as $price) {
        if (!(is_int($price) | is_float($price)) or $price < 0) {
            throw new InvalidArgumentException("Giá nhập vào không hợp lệ!");
        }
        
        $totalPrice += $price;
    }

    return $totalPrice;
}

function findMaxPrice($prices) {
    if (!is_array($prices)) {
        throw new InvalidArgumentException("Đầu vào phải là mảng chứa giá!");
    }

    $maxPrice = 0;

    foreach ($prices as $price) {
        if (!(is_int($price) | is_float($price)) or $price < 0) {
            throw new InvalidArgumentException("Giá nhập vào không hợp lệ!");
        }
        
        if ($price > $maxPrice) {
            $maxPrice = $price;
        }
    }

    return $maxPrice;
}

try {
    echo "Tổng giá trị của tất cả sản phẩm: ". calculateTotal($prices);
    echo "<br>";
    echo "Giá sản phẩm cao nhất: ". findMaxPrice($prices);
} catch(Exception $e) {
    echo "Lỗi! ". $e->getMessage();
}