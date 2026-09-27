<?php
function lineDown(int $num_of_line) {
    for ($i = 0; $i < $num_of_line; $i++) {
        echo "<br>";
    }
}

echo "Bài 2";
lineDown(2);

Class Movie {
    private string $id;
    private string $title;
    private float $price;
    private int $totalSeats;
    private int $availableSeats;

    public function __construct(string $id, string $title, float $price, int $totalSeats) {
        if ($price < 0) {
            throw new InvalidArgumentException("Giá vé phải lớn hơn hoặc bằng 0.");
        }

        if ($totalSeats <= 0) {
            throw new InvalidArgumentException("Tổng số ghế phải lớn hơn 0.");
        }

        $this->id = $id;
        $this->title = $title;
        $this->price = $price;
        $this->totalSeats = $totalSeats;
        $this->availableSeats = $this->totalSeats;
    }

    public function getId(): string {
        return $this->id;
    }

    public function bookTicket(int $quantity) {
        if ($quantity <= 0) {
            throw new InvalidArgumentException("Đặt vé thất bại! Số vé đặt phải lớn hơn 0.");
        }

        if ($quantity > $this->availableSeats) {
            throw new UnexpectedValueException("Đặt vé thất bại! Không còn đủ số vé để đặt.");
        }

        echo "Đặt ". $quantity ." vé thành công!";
        $this->availableSeats -= $quantity;
    }

    public function getSoldSeats() {
        return $this->totalSeats - $this->availableSeats;
    }

    public function cancelTicket(int $quantity) {
        if ($quantity <= 0) {
            throw new InvalidArgumentException("Hủy vé thất bại! Số vé hủy phải lớn hơn 0.");
        }

        if ($quantity > $this->getSoldSeats()) {
            throw new UnexpectedValueException("Hủy vé thất bại! Số vé hủy không được vượt quá số vé đã bán.");
        }

        echo "Hủy ". $quantity ." vé thành công!";
        $this->availableSeats += $quantity;
    }

    public function getRevenue() : float {
        return $this->getSoldSeats() * $this->price;
    }

    public function displayInfo() : string {
        return "[Mã phim: ". $this->id .
            ", Tên phim: ". $this->title .
            ", Giá vé: ". $this->price .
            ", Tổng số ghế: " . $this->totalSeats .
            ", Số ghế còn lại: ". $this->availableSeats .
            ", Số ghế đã bán: ". $this->getSoldSeats() .
            ", Doanh thu: ". number_format($this->getRevenue(), 2) ." (USD)".
            "]";
    }
}

function findMovieById(array $movies, string $id) : ?Movie {
    for ($i = 0; $i < count($movies); $i++) {
        if (!($movies[$i] instanceof Movie)) {
            throw new InvalidArgumentException("Danh sách phim không hợp lệ!");
        }

        if ($movies[$i]->getId() == $id) {
            return $movies[$i];
        }
    }

    return null;
}

function findIndexMovieById(array $movies, string $id) : ?int {
    try {
        for ($i = 0; $i < count($movies); $i++) {
            if (!($movies[$i] instanceof Movie)) {
                throw new InvalidArgumentException("Danh sách phim không hợp lệ!");
            }

            if ($movies[$i]->getId() == $id) {
                return $i;
            }
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }

    return null;
}

function getTotalRevenue(array $movies) {
    $totalRevenue = 0;

    for ($i = 0; $i < count($movies); $i++) {
        if (!($movies[$i] instanceof Movie)) {
            throw new InvalidArgumentException("Danh sách phim không hợp lệ!");
        }
        $totalRevenue += $movies[$i]->getRevenue();
    }

    return $totalRevenue;
}

function getBestSellingMovies(array $movies) {
    if (count($movies) == 0) {
        return null;
    }

    $bestSellingMovie = [$movies[0]];

    for ($i = 1; $i < count($movies); $i++) {
        if (!($movies[$i] instanceof Movie)) {
            throw new InvalidArgumentException("Danh sách phim không hợp lệ!");
        }

        if ($movies[$i]->getRevenue() > $bestSellingMovie[0]->getRevenue()) {
            $bestSellingMovie = [$movies[$i]];
        } else if ($movies[$i]->getRevenue() == $bestSellingMovie[0]->getRevenue()) {
            array_push($bestSellingMovie, $movies[$i]);
        }
    }

    return $bestSellingMovie;
}

/**
 * List of movies
 * @var Movie[] $movies
 */
$movies = [];

function createAndAddMovie(string $id, string $title, float $price, int $totalSeats) {
    try {
        global $movies;

        if (findMovieById($movies, $id) != null) {
            throw new InvalidArgumentException("Mã phim không hợp lệ!");
        }


        $movie = new Movie($id, $title, $price, $totalSeats);
        array_push($movies, $movie);
        echo "Thêm phim thành công!";
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

createAndAddMovie("01b83a6","Avatar", 120.34, 80);
lineDown(1);
createAndAddMovie("02ee893","Batman", 149.6, 120);
lineDown(1);
createAndAddMovie("03x76a8","Avengers", 185.77, 100);
lineDown(1);
createAndAddMovie("03x76a8","Lalaland", 98.89, 105);
lineDown(1);
createAndAddMovie("0416ftf","Lalaland", -98.89, 105);
lineDown(1);
createAndAddMovie("0416ftf","Lalaland", 98.89, 0);
lineDown(1);
createAndAddMovie("0416ftf","Lalaland", 98.89, 105);
lineDown(1);
createAndAddMovie("0a8z15b","Zootopia", 185.77, 130);

function bookMovieTicketById(string $id, int $quantity) {
    try {
        global $movies;
        $indexMovie = findIndexMovieById($movies, $id);

        if ($indexMovie == null) {
            throw new InvalidArgumentException("Mã phim không tồn tại!");
        }

        $movies[$indexMovie]->bookTicket($quantity);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function cancelMovieTicketById(string $id, int $quantity) {
    try {
        global $movies;
        $indexMovie = findIndexMovieById($movies, $id);

        if ($indexMovie == null) {
            throw new InvalidArgumentException("Mã phim không tồn tại!");
        }

        $movies[$indexMovie]->cancelTicket($quantity);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

lineDown(2);
bookMovieTicketById("03x76a8",81);
lineDown(1);
bookMovieTicketById("7har45f",62);
lineDown(1);
bookMovieTicketById("03x76a8",69);
lineDown(1);
bookMovieTicketById("0a8z15b",81);
lineDown(1);
bookMovieTicketById("01b83a6",75);
lineDown(1);
bookMovieTicketById("02ee893",97);
lineDown(1);
bookMovieTicketById("0416ftf",64);


lineDown(2);
cancelMovieTicketById("01b83a6",8);
lineDown(1);
cancelMovieTicketById("02ee893",12);
lineDown(1);
cancelMovieTicketById("0416ftf",89);
lineDown(1);
cancelMovieTicketById("0416ftf",-1);
lineDown(1);
cancelMovieTicketById("7har45f",5);

function displayMovieList(array $movies) {
    try {
        $displayString = "Danh sách phim:<br>";

        foreach ($movies as $movie) {
            if (!($movie instanceof Movie)) {
                throw new InvalidArgumentException("Danh sách phim không hợp lệ!");
            }

            $displayString .= $movie->displayInfo() ."<br>";
        }

        echo $displayString;
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

lineDown(2);
displayMovieList($movies);

function calculateTotalRevenue(array $movies) {
    try {
        $totalRevenue = 0;

        foreach ($movies as $movie) {
            if (!($movie instanceof Movie)) {
                throw new InvalidArgumentException("Danh sách phim không hợp lệ!");
            }

            $totalRevenue += $movie->getRevenue();
        }

        echo "Tổng doanh thu: ". $totalRevenue ." (USD)";
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

lineDown(2);
calculateTotalRevenue($movies);

function displayBestSellingMovies(array $movies) {
    try {
        $bestSellingMovies = getBestSellingMovies($movies);
        echo "Những bộ phim có doanh thu cao nhất: <br>";

        foreach ($bestSellingMovies as $movie) {
            echo $movie->displayInfo() ."<br>";
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

lineDown(2);
displayBestSellingMovies($movies);
?>