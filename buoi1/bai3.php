<?php
// Dữ liệu danh sách sinh viên từ Bài 1
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

// 1. Tìm sinh viên có điểm cao nhất
function findBestStudent($students) {
    if (empty($students)) return null;
    
    $bestStudent = $students[0];
    foreach ($students as $student) {
        if ($student["score"] > $bestStudent["score"]) {
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
        if ($student["score"] < $worstStudent["score"]) {
            $worstStudent = $student;
        }
    }
    return $worstStudent;
}

// 3. Đếm số sinh viên đạt (điểm >= 5)
function countPassedStudents($students) {
    $count = 0;
    foreach ($students as $student) {
        if ($student["score"] >= 5) {
            $count++;
        }
    }
    return $count;
}

// 4. Tìm sinh viên theo tên (tìm chính xác hoặc tương đối)
function findStudentByName($students, $name) {
    foreach ($students as $student) {
        // So sánh không phân biệt hoa thường
        if (mb_stripos($student["name"], $name) !== false) {
            return $student;
        }
    }
    return null;
}

// Hàm hỗ trợ in thông tin sinh viên ngắn gọn
function printStudentInfo($student) {
    if ($student) {
        echo "Họ tên: " . $student["name"] . " | Tuổi: " . $student["age"] . " | Điểm: " . $student["score"] . "<br>";
    } else {
        echo "Không tìm thấy sinh viên.<br>";
    }
}

//main

// 1. Sinh viên có điểm cao nhất
$best = findBestStudent($students);
echo "<b>1. Sinh viên có điểm cao nhất:</b><br>";
printStudentInfo($best);
echo "<br>";

// 2. Sinh viên có điểm thấp nhất
$worst = findWorstStudent($students);
echo "<b>2. Sinh viên có điểm thấp nhất:</b><br>";
printStudentInfo($worst);
echo "<br>";

// 3. Số sinh viên đạt (điểm >= 5)
$passedCount = countPassedStudents($students);
echo "<b>3. Số sinh viên đạt (điểm >= 5):</b> " . $passedCount . " sinh viên<br><br>";

// 4. Tìm sinh viên theo tên
$searchName = "Cuong";
$foundStudent = findStudentByName($students, $searchName);
echo "<b>4. Kết quả tìm kiếm sinh viên tên '$searchName':</b><br>";
printStudentInfo($foundStudent);

?>