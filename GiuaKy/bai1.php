<?php

$students = [
    [
        "name" => "Nguyen Van A",
        "score" => 8.25
    ],
    [
        "name" => "Tran Thi B",
        "score" => 6.5
    ],
    [
        "name" => "Vu Van C",
        "score" => 4.85
    ],
    [
        "name" => "Nguyen Thi D",
        "score" => 7.75
    ]
];


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
         " | Điểm: " . $student["score"] . 
         " | Xếp loại: " . $rank . "<br>";
}

// main

echo " DANH SÁCH SINH VIÊN <br>";

// Hiển thị từng sinh viên
foreach ($students as $student) {
    displayStudent($student);
}
echo "<br>";

?>