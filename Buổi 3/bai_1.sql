CREATE DATABASE shopping_cart;
USE shopping_cart;

# Tạo bảng.
CREATE TABLE cart_items(
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL
);

#1.
INSERT INTO cart_items(name, price, quantity)
VALUES
	("Bo", 138000, 4),
    ("Ca",72000,5),
    ("Tom",109000,7),
    ("Cua",80000,9),
    ("Ga",146000,3);
    
#2.
SELECT * 
FROM cart_items;

#3.
SELECT *
FROM cart_items
WHERE price > 100000;

#4.
SELECT *
FROM cart_items
WHERE quantity > 5;

#5.
SELECT *
FROM cart_items
ORDER BY price DESC;

#6.
UPDATE cart_items
SET price = 99000
WHERE name = "Tom";

#7.
UPDATE cart_items
SET quantity = 8
WHERE name = "Cua";

#8.
DELETE
FROM cart_items
WHERE name = "Ca";

#9.
SELECT name, price, quantity, (price * quantity) AS total
FROM cart_items;

#10.
SELECT SUM(price * quantity) AS cart_payment
FROM cart_items;




    
   



