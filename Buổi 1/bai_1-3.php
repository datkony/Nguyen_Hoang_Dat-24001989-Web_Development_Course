<?php
echo 'Bài 1:<br>';

$students = [
[
    	"name" => "Nguyen Van An",
    	"age" => 20,
    	"score" => 8.5
],
[
    	"name" => "Tran Thi Binh",
    	"age" => 21,
    	"score" => 6.5
],
[
    	"name" => "Le Van Cuong",
    	"age" => 19,
    	"score" => 4.5
],
[
    	"name" => "Pham Thi Dung",
    	"age" => 20,
    	"score" => 7.5
]
];

$a = 0;
$count = 0;
foreach ($students as $student) {
    	echo 'Họ và tên: ' . $student["name"] . ', Tuổi: ' . $student["age"] . ', Điểm: ' . $student["score"] . ' <br>';
	$count++;
	$a += $student["score"];	
}

echo 'Điểm trung bình: ' .  ($a / $count) . '<br>';
?>


<?php
	echo '<br>';
?>

<?php
echo 'Bài 2:<br>';

$students = [
[
    	"name" => "Nguyen Van An",
    	"age" => 20,
    	"score" => 8.5
],
[
    	"name" => "Tran Thi Binh",
    	"age" => 21,
    	"score" => 6.5
],
[
    	"name" => "Le Van Cuong",
    	"age" => 19,
    	"score" => 4.5
],
[
    	"name" => "Pham Thi Dung",
    	"age" => 20,
    	"score" => 7.5
]
];

function calculateAverageScore($students) {
	$a = 0;
	$count = 0;
	
	foreach ($students as $student) {
		$a += $student["score"];
		$count++;
	}

	return $a / $count;
}

echo 'Điểm trung bình: ' . calculateAverageScore($students) . '<br>';

function getRank($score) {
	if ($score >= 8) {
		return 'Giỏi';
	} elseif ($score >= 6.5) {
		return 'Khá';
	} elseif ($score >= 5) {
		return 'Trung bình';
	} else {
		return 'Yếu';
	}
}

function displayStudent($student) {
	echo 'Họ và tên: ' . $student["name"] . ', Tuổi: ' . $student["age"] . ', Điểm: ' . $student["score"] . ', Xếp loại: ' . getRank($student["score"]) . '<br>';
}

foreach ($students as $student) {
	displayStudent($student);
}
?>

<?php
	echo '<br>';
?>

<?php
echo 'Bài 3:<br>';

$students = [
[
    	"name" => "Nguyen Van An",
    	"age" => 20,
    	"score" => 8.5
],
[
    	"name" => "Tran Thi Binh",
    	"age" => 21,
    	"score" => 6.5
],
[
    	"name" => "Le Van Cuong",
    	"age" => 19,
    	"score" => 4.5
],
[
    	"name" => "Pham Thi Dung",
    	"age" => 20,
    	"score" => 7.5
]
];

function displayStudent2($student) {
	if ($student == null) {
		return 'Không tìm thấy sinh viên!';
	} else {
		return 'Họ và tên: ' . $student["name"] . ', Tuổi: ' . $student["age"] . ', Điểm: ' . $student["score"] . ', Xếp loại: ' . getRank($student["score"]);
	}
}


function findBestStudent($students) {
	$curr_student = $students[0];

	foreach ($students as $student) {
		if ($student["score"] > $curr_student["score"]) {
			$curr_student = $student;
		}
	}

	return $curr_student;
}

function findWorstStudent($students) {
	$curr_student = $students[0];

	foreach ($students as $student) {
		if ($student["score"] < $curr_student["score"]) {
			$curr_student = $student;
		}
	}

	return $curr_student;
}

function countPassedStudents($students) {
	$count = 0;

	foreach ($students as $student) {
		if ($student["score"] >= 5) {
			$count++;
		}
	}

	return $count;
}

function findStudentByName($students, $name) {
	foreach ($students as $student) {
		if ($name == $student["name"]) {
			return $student;
		}
	}

	return null;
}

echo 'Sinh viên tốt nhất: ' . displayStudent2(findBestStudent($students)) . '<br>';
echo 'Sinh viên tệ nhất: ' . displayStudent2(findWorstStudent($students)) . '<br>';
echo 'Số sinh viên qua môn: ' . countPassedStudents($students) . '<br>';
echo 'Sinh viên có tên An: ' . displayStudent2(findStudentByName($students, "Nguyen Van An")) . '<br>';
echo 'Sinh viên có tên Minh: ' . displayStudent2(findStudentByName($students, "Nguyen Van Minh")) . '<br>';
?>
