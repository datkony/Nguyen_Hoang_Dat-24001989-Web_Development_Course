CREATE DATABASE movies_management;
USE movies_management;

# Tạo bảng.
CREATE TABLE movies(
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    total_seats INT NOT NULL,
    available_seats INT NOT NULL DEFAULT 0
);

#1.
INSERT INTO movies(title, price, total_seats, available_seats)
VALUES
	("Avengers", 188000, 190, 60),
    ("Lalaland", 90000, 200, 70),
    ("Barbie", 105000, 150, 40),
    ("Zootopia", 121000, 130, 50),
    ("Avatar", 88000, 100, 75);

#2.
SELECT *
FROM movies;

#3.
SELECT *
FROM movies
WHERE price > 100000;

#4.
SELECT *
FROM movies
WHERE available_seats > 50;

#5.
SELECT *
FROM movies
ORDER BY price DESC;

#6.
UPDATE movies
SET available_seats = 35
WHERE title = "Lalaland";

#7.
DELETE
FROM movies
WHERE title = "Barbie";

#8.
SELECT title, (total_seats - available_seats) AS sold_seats
FROM movies;

#9.
SELECT title, ((total_seats - available_seats) * price) AS revenue
FROM movies;

#10.
SELECT SUM((total_seats - available_seats) * price) AS total_revenue
FROM movies;

#11.
SELECT * 
FROM movies 
WHERE (total_seats - available_seats) = (
    SELECT MAX(total_seats - available_seats) 
    FROM movies
);



    