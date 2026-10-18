-- =====================================================================
-- FILE: database/shopping_cart.sql
-- MỤC ĐÍCH: Tạo database "shopping_cart", bảng products, bảng users
--           và dữ liệu mẫu (5 sản phẩm + 1 tài khoản).
-- CÁCH DÙNG (XAMPP):
--   Bước 1: Khởi động Apache + MySQL trong Control Panel của XAMPP.
--   Bước 2: Mở http://localhost/phpmyadmin
--   Bước 3: Chọn tab "Import" -> Chọn file này -> "Go"
--   Hoặc chạy từ dòng lệnh:
--     F:\xampp\mysql\bin\mysql.exe -u root < database/shopping_cart.sql
-- =====================================================================

-- Tạo database (IF NOT EXISTS: chạy lại file SQL nhiều lần cũng không báo lỗi)
CREATE DATABASE IF NOT EXISTS shopping_cart
    CHARACTER SET utf8mb4      -- Hỗ trợ đầy đủ tiếng Việt có dấu
    COLLATE utf8mb4_unicode_ci;

-- Chọn database đang thao tác để chạy các câu lệnh phía sau
USE shopping_cart;

-- ---------------------------------------------------------------------
-- DỌN SẠCH BẢNG CŨ (nếu có)
--   cart_items: tên bảng SAI do phiên bản code cũ từng dùng; file SQL nộp bài
--   chỉ cần 2 bảng products và users theo đề bài.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS cart_items;

-- ---------------------------------------------------------------------
-- BẢNG products: lưu thông tin sản phẩm trong giỏ hàng
-- ---------------------------------------------------------------------
-- DROP TABLE IF EXISTS: nếu bảng đã tồn tại (khi chạy lại file SQL) thì xóa đi
-- để dữ liệu mẫu luôn được tạo mới đúng theo file SQL này.
DROP TABLE IF EXISTS products;

CREATE TABLE products (
    id        INT            NOT NULL AUTO_INCREMENT, -- Mã sản phẩm, tự tăng
    name      VARCHAR(100)   NOT NULL,                -- Tên sản phẩm, tối đa 100 ký tự
    price     DECIMAL(10,2)  NOT NULL,                -- Giá: tối đa 10 chữ số, 2 chữ số thập phân
    quantity  INT            NOT NULL,                -- Số lượng tồn kho
    PRIMARY KEY (id)                                  -- Khóa chính cho cột id
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Thêm 5 sản phẩm mẫu (yêu cầu: thêm tối thiểu 5 sản phẩm)
INSERT INTO products (name, price, quantity) VALUES
    ('Bánh quy socola',   45000.00,  30),
    ('Sữa tươi 1L',       32000.00,  50),
    ('Trà xanh túi lọc',  58000.00,  20),
    ('Nước ngọt 500ml',   12000.00, 100),
    ('Mì ăn liền',         4500.00, 200);

-- ---------------------------------------------------------------------
-- BẢNG users: lưu tài khoản đăng nhập (yêu cầu mục V)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id        INT           NOT NULL AUTO_INCREMENT, -- Mã tài khoản
    username  VARCHAR(50)   NOT NULL UNIQUE,         -- Tên đăng nhập, không được trùng
    password  VARCHAR(255)  NOT NULL,                -- MẬT KHẨU ĐÃ MÃ HÓA (password_hash)
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- TÀI KHOẢN MẪU
--   username: admin
--   password: admin123
--
-- LƯU Ý QUAN TRỌNG:
--   Cột password CHỈ lưu chuỗi đã mã hóa bằng password_hash() (bcrypt/argon).
--   KHÔNG BAO GIỜ lưu mật khẩu dạng văn bản thuần (plain text).
--
--   Chuỗi hash dưới đây được sinh bằng PHP 8.2:
--     php -r "echo password_hash('admin123', PASSWORD_DEFAULT);"
--   password_hash() sinh KẾT QUẢ KHÁC NHAU mỗi lần gọi (do có salt ngẫu nhiên),
--   nên hash phải được tạo trước rồi dán vào file SQL.
--   Kiểm tra lại bằng password_verify('admin123', hash) === true.
-- ---------------------------------------------------------------------
INSERT INTO users (id, username, password) VALUES
    (1, 'admin', '$2y$10$2UjBTjPyBszGJv3oG24tjua9SX/9GKn6cxuaXkDHGAozuwVyGlic6');
