<?php

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

// 1. Hàm tính điểm trung bình
function calculateAverageScore($students) {
    if (empty($students)) {
        return 0;
    }
    
    $totalScore = 0;
    foreach ($students as $student) {
        $totalScore += $student["score"];
    }
    
    return $totalScore / count($students);
}

// 2. Hàm trả về xếp loại theo điểm
function getRank($score) {
    if ($score >= 8) {
        return "Giỏi";
    } elseif ($score >= 6.5) {
        return "Khá";
    } elseif ($score >= 5) {
        return "Trung bình";
    } else {
        return "Yếu";
    }
}

// 3. Hàm hiển thị thông tin sinh viên 
function displayStudent($student) {
    $rank = getRank($student["score"]);
    echo "Họ tên: " . $student["name"] . 
         " | Tuổi: " . $student["age"] . 
         " | Điểm: " . $student["score"] . 
         " | Xếp loại: " . $rank . "<br>";
}

// main

echo " DANH SÁCH SINH VIÊN <br>";

// Hiển thị từng sinh viên
foreach ($students as $student) {
    displayStudent($student);
}

// Tính và hiển thị điểm trung bình
$avgScore = calculateAverageScore($students);
echo "<br>";
echo "Điểm trung bình của tất cả sinh viên: " . number_format($avgScore, 2) . "<br>";
?>