<?php
echo 'Bài 4:<br>';

class Student {
	private $name;
	private $age;
	private $score;

	function __construct($name, $age, $score) {
		$this->name = $name;
		$this->age = $age;
		$this->score = $score;
	}

	function getRank() {
		if ($this->score >= 8) {
			return 'Giỏi' ;
		} elseif ($this->score >= 6.5) {
			return 'Khá' ;
		} elseif ($this->score >= 5) {
			return 'Trung bình' ;
		} else {
			return 'Yếu' ;
		}
	}

	function isPassed() {
		return $this->score >= 5;
	}

	function getScore() {
		return $this->score;
	}

	function display() {
		return 'Họ và tên: ' . $this->name . ', Tuổi: ' . $this->age . ', Điểm: ' . $this->score . ', Xếp loại: ' . $this->getRank();
	}
}

$student1 = new Student("Nguyen Van An", 20, 8.5);
$student2 = new Student("Tran Thi Binh", 21, 6.5);
$student3 = new Student("Le Van Cuong", 19, 4.5);
$student4 = new Student("Pham Thi Dung", 20, 7.5);
$students = [$student1, $student2, $student3, $student4];

foreach ($students as $student) {
	echo $student->display() . '<br>';
}

function getBestStudent($students) {
	$curr_student = $students[0];

	for ($i = 1; $i < count($students); $i++) {
		if ($students[$i]->getScore() > $curr_student->getScore()) {
			$curr_student = $students[$i];
		}
	}

	return $curr_student;
}

function countPassed($students) {
	$countPassed = 0;

	foreach ($students as $student) {
		if($student->isPassed()) {
			$countPassed++;
		}
	}

	return $countPassed;
}

function getAverageScore($students) {
	$sumScore = 0;

	foreach ($students as $student) {
		$sumScore += $student->getScore();
	}

	return $sumScore / count($students);
}

echo 'Sinh viên điểm cao nhất: ' . getBestStudent($students)->display() . '<br>';
echo 'Số sinh viên đạt: ' . countPassed($students) . '<br>';
echo 'Điểm trung bình: ' . getAverageScore($students) . '<br>';
?>

	

