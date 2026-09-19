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

$totalScore = 0;

echo "DANH SÁCH SINH VIÊN <br>";

// Sử dụng foreach và thay \n bằng <br>
foreach ($students as $student) {
    echo "Họ tên: " . $student["name"] . " | Tuổi: " . $student["age"] . " | Điểm: " . $student["score"] . "<br>";
    $totalScore += $student["score"];
}

$averageScore = $totalScore / count($students);

echo "<br>";
echo "Điểm trung bình của tất cả sinh viên: " . number_format($averageScore, 2) . "<br>";
?>