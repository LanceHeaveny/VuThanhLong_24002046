<?php

class Student {

// thuộc tính student:

    public $name;
    public $age;
    public $score;

// contructor
public function __construct($name, $age,$score){
    $this->name = $name;
    $this->age = $age;
    $this->score = $score;
}
// xếp loại sinh viên
public function getRank(){
    if ($this->score >= 8) {
        return "Giỏi";
    } elseif ($this->score >= 6.5) {
        return "Khá";
    } elseif ($this->score >= 5) {
        return "Trung bình";
    } else {
        return "Yếu";
    }
}

// kiểm tra sinh viên có đạt hay không
public function isPassed(){
    return $this->score >= 5;
}
// in thông tin sinh viên
public function printStudentInfo(){
    echo "Họ tên: " . $this->name . 
             " | Tuổi: " . $this->age . 
             " | Điểm: " . $this->score . 
             " | Xếp loại: " . $this->getRank() . 
             " | Trạng thái: " . ($this->isPassed() ? "Đạt" : "Không đạt") . "<br>";
    }
}
// 1. Tìm sinh viên có điểm cao nhất
function findBestStudent($students) {
    if (empty($students)) return null;
    
    $bestStudent = $students[0];
    foreach ($students as $student) {
        if ($student->score > $bestStudent->score) {
            $bestStudent = $student;
        }
    }
    return $bestStudent;
}

// 2. Tìm sinh viên có điểm thấp nhất
function findWorstStudent($students) {
    if (empty($students)) return null;
    
    $worstStudent = $students[0];
    foreach ($students as $student) {
        if ($student->score < $worstStudent->score) {
            $worstStudent = $student;
        }
    }
    return $worstStudent;
}

// 3. Đếm số sinh viên đạt (điểm >= 5)
function countPassedStudents($students) {
    $count = 0;
    foreach ($students as $student) {
        if ($student->isPassed()) {
            $count++;
        }
    }
    return $count;
}


// Hàm tính điểm trung bình
function calculateAverageScore($students) {
    if (empty($students)) {
        return 0;
    }
    
    $totalScore = 0;
    foreach ($students as $student) {
        $totalScore += $student->score;
    }
    
    return $totalScore / count($students);
}
// main

// khởi tạo students
$student1 = new Student("Nguyen Van An", 20, 8.5);
$student2 = new Student("Tran Thi Binh", 21, 6.5);
$student3 = new Student("Le Van Cuong", 19, 4.5);
$student4 = new Student("Pham Thi Dung", 20, 7.5);

// tạo danh sách các object Student
$students = [$student1, $student2, $student3, $student4];   

// Duyệt danh sách sinh vien
echo "<b>DANH SÁCH SINH VIÊN:</b><br>";
foreach ($students as $student) {
    $student->printStudentInfo();
}
echo "<br>";

// 1. Sinh viên có điểm cao nhất
$best = findBestStudent($students);
echo "<b>1. Sinh viên có điểm cao nhất:</b><br>";
if($best){
    $best->printStudentInfo();
}
echo "<br>";

// 2. Sinh viên có điểm thấp nhất
$worst = findWorstStudent($students);
echo "<b>2. Sinh viên có điểm thấp nhất:</b><br>";
if ($worst){
    $worst->printStudentInfo();
}
echo "<br>";

// 3. Số sinh viên đạt (điểm >= 5)
$passedCount = countPassedStudents($students);
echo "<b>3. Số sinh viên đạt (điểm >= 5):</b> " . $passedCount . " sinh viên<br><br>";

// 4. Điểm trung bình các sinh viên
$avgScore = calculateAverageScore($students);
echo "<b>4. Điểm trung bình của lớp:</b> " . number_format($avgScore, 2) . "<br>";
?>