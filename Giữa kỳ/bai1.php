<?php
$name = "Nguyễn Hoàng Đạt";
$score = 8.5;
$ranking = "";

if ((is_int($score) | is_float($score)) and $score >= 0 and $score <= 10) {
    if ($score >= 8) {
        $ranking = "Giỏi";
    } elseif ($score >= 6.5) {
        $ranking = "Khá";
    } elseif ($score >= 5) {
        $ranking = "Trung bình";
    } else {
        $ranking = "Yếu";
    }

    echo "Họ và tên: ". $name . ", điểm: ". $score .", xếp loại: ". $ranking;
} else {
    echo "Lỗi! Điểm số không hợp lệ";
}
?>