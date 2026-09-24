<?php

class Movie {
    public int $id;
    public string $title;
    public float $price;
    public int $totalSeats;
    public int $availableSeats;

    // Hàm dựng:
    public function __construct(int $id, string $title, float $price, int $totalSeats) {
        $this->id = $id;
        $this->title = $title;
        $this->price = $price;
        $this->totalSeats = $totalSeats;
        $this->availableSeats = $totalSeats; // Ban đầu số ghế còn lại bằng tổng số ghế
    }

    // Hàm đặt vé:
    public function bookTicket(int $quantity): void {
        // Check số lượng không hợp lệ (<= 0)
        if ($quantity <= 0) {
            echo "Số lượng vé đặt cho phim <b>{$this->title}</b> không hợp lệ (phải > 0)! <br>";
            return;
        }

        // Check số lượng vượt quá số ghế còn lại
        if ($quantity > $this->availableSeats) {
            echo "Không thể đặt <b>{$quantity}</b> vé cho phim <b>{$this->title}</b> (Chỉ còn <b>{$this->availableSeats}</b> ghế trống)! <br>";
            return;
        }

        // Đặt vé thành công
        $this->availableSeats -= $quantity;
        echo "Đã đặt thành công <b>{$quantity}</b> vé xem phim <b>{$this->title}</b>! <br>";
    }

    // Hàm hủy vé:
    public function cancelTicket(int $quantity): void {
        // Check số lượng không hợp lệ (<= 0)
        if ($quantity <= 0) {
            echo "Số lượng vé hủy cho phim <b>{$this->title}</b> không hợp lệ (phải > 0)! <br>";
            return;
        }

        $soldSeats = $this->getSoldSeats();

        // Check số lượng hủy vượt quá số vé đã bán
        if ($quantity > $soldSeats) {
            echo "Không thể hủy <b>{$quantity}</b> vé cho phim <b>{$this->title}</b> (Số vé đã bán chỉ có <b>{$soldSeats}</b> vé)! <br>";
            return;
        }

        // Hủy vé thành công
        $this->availableSeats += $quantity;
        echo "Đã hủy thành công <b>{$quantity}</b> vé xem phim <b>{$this->title}</b>! <br>";
    }

    // Hàm lấy số vé đã bán:
    public function getSoldSeats(): int {
        return $this->totalSeats - $this->availableSeats;
    }

    // Hàm lấy doanh thu:
    public function getRevenue(): float {
        return $this->getSoldSeats() * $this->price;
    }

    // Hàm hiển thị thông tin phim:
    public function displayInfo(): void {
        echo "<b>Mã phim:</b> " . $this->id . 
             " | <b>Tên phim:</b> " . $this->title . 
             " | <b>Giá vé:</b> " . number_format($this->price) . " VNĐ" .
             " | <b>Tổng số ghế:</b> " . $this->totalSeats . 
             " | <b>Ghế còn lại:</b> " . $this->availableSeats . 
             " | <b>Đã bán:</b> " . $this->getSoldSeats() . 
             " | <b>Doanh thu:</b> " . number_format($this->getRevenue()) . " VNĐ<br>";
    }
}

// Tìm phim theo ID
function findMovieById(array $movies, int $id): ?Movie {
    if (count($movies) == 0) {
        echo "Danh sách phim rỗng! <br>";
        return null;
    }

    foreach ($movies as $movie) {
        if ($movie->id == $id) {
            return $movie;
        }
    }

    return null;
}

// Tính tổng doanh thu của tất cả phim
function getTotalRevenue(array $movies): float {
    if (count($movies) == 0) {
        return 0;
    }

    $totalRevenue = 0;
    foreach ($movies as $movie) {
        $totalRevenue += $movie->getRevenue();
    }
    return $totalRevenue;
}

// Tìm phim có số vé đã bán nhiều nhất
function getBestSellingMovie(array $movies): ?Movie {
    if (count($movies) == 0) {
        echo "Danh sách phim rỗng, không thể tìm phim bán chạy nhất! <br>";
        return null;
    }

    $bestMovie = $movies[0];
    foreach ($movies as $movie) {
        if ($movie->getSoldSeats() > $bestMovie->getSoldSeats()) {
            $bestMovie = $movie;
        }
    }

    return $bestMovie;
}

// Hàm hiển thị danh sách tất cả các phim
function displayAllMovies(array $movies): void {
    if (count($movies) == 0) {
        echo "Danh sách phim rỗng! <br>";
        return;
    }

    foreach ($movies as $movie) {
        $movie->displayInfo();
    }
    echo "<br>";
}

// TESTCASE
function main() {
    echo "Bắt đầu quản lý rạp chiếu phim! <br><br>";

    // 1. Tạo danh sách phim
    $movies = [
        new Movie(1, "Avengers", 100000, 100),
        new Movie(2, "Avatar", 120000, 80),
        new Movie(3, "Batman", 90000, 120)
    ];

    echo "Đã tạo danh sách phim ban đầu! <br><br>";

    // Hiển thị danh sách ban đầu
    echo "<b>Danh sách phim hiện tại:</b><br>";
    displayAllMovies($movies);

    // 2. Đặt vé hợp lệ
    echo "<b><u>Thực hiện đặt vé hợp lệ:</u></b><br><br>";
    $avengers = findMovieById($movies, 1);
    if ($avengers) {
        $avengers->bookTicket(50);
    }

    $avatar = findMovieById($movies, 2);
    if ($avatar) {
        $avatar->bookTicket(30);
    }

    echo "<br><b>Thống kê danh sách phim sau khi đặt vé:</b><br>";
    displayAllMovies($movies);

    // 3. Hủy vé hợp lệ
    echo "<b><u>Thực hiện hủy vé hợp lệ:</u></b><br><br>";
    if ($avengers) {
        $avengers->cancelTicket(10);
    }

    echo "<br><b>Thống kê danh sách phim sau khi hủy vé:</b><br>";
    displayAllMovies($movies);

    // 4. Testcase các trường hợp ngoại lệ (Bắt buộc phải xử lý)
    echo "<b><u>Kiểm tra các trường hợp ngoại lệ / lỗi:</u></b><br><br>";

    echo "1. Đặt vé số lượng <= 0:<br>";
    if ($avengers) $avengers->bookTicket(-5);

    echo "<br>2. Đặt vé vượt quá số ghế còn lại:<br>";
    if ($avatar) $avatar->bookTicket(100);

    echo "<br>3. Hủy vé số lượng <= 0:<br>";
    if ($avengers) $avengers->cancelTicket(0);

    echo "<br>4. Hủy vé vượt quá số vé đã bán:<br>";
    if ($avatar) $avatar->cancelTicket(50);

    echo "<br>5. Tìm phim không tồn tại:<br>";
    $notFoundMovie = findMovieById($movies, 999);
    if ($notFoundMovie == null) {
        echo "Không tìm thấy phim với ID = 999! <br>";
    }

    echo "<br>6. Xử lý danh sách phim rỗng:<br>";
    $emptyMoviesList = [];
    findMovieById($emptyMoviesList, 1);
    getBestSellingMovie($emptyMoviesList);

    // 5. Hiển thị tổng doanh thu & phim bán chạy nhất
    echo "<br><b><u>Thống kê chung:</u></b><br><br>";
    
    echo "<b>Danh sách tất cả các phim:</b><br>";
    displayAllMovies($movies);

    echo "<b>Tổng doanh thu tất cả các phim: </b>" . number_format(getTotalRevenue($movies)) . " VNĐ<br><br>";

    $bestMovie = getBestSellingMovie($movies);
    if ($bestMovie) {
        echo "<b>Phim bán chạy nhất: </b>" . $bestMovie->title . " (Đã bán: " . $bestMovie->getSoldSeats() . " vé)<br>";
    }
}

main();
?>