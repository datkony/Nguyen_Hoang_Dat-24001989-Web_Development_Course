-- ============================================================================
-- GIAI ĐOẠN 1: TẠO CƠ SỞ DỮ LIỆU CHO ỨNG DỤNG QUẢN LÝ SẢN PHẨM
-- ----------------------------------------------------------------------------
-- Tệp này được viết cho MySQL/MariaDB (XAMPP).
-- Cách sử dụng:
--   1. Mở phpMyAdmin, chọn thẻ "Import" và tải tệp này lên; hoặc
--   2. Mở terminal và chạy lệnh:
--        mysql -u root -p < database.sql
-- Tệp sẽ tạo cơ sở dữ liệu `quanly_sanpham`, tạo bảng `products`
-- và chèn sẵn ba bản ghi mẫu theo yêu cầu của đề bài.
-- ============================================================================

-- Tạo cơ sở dữ liệu mới với bảng mã UTF-8 đầy đủ để lưu đúng tên tiếng Việt.
-- "IF NOT EXISTS" giúp câu lệnh không báo lỗi nếu cơ sở dữ liệu đã tồn tại.
CREATE DATABASE IF NOT EXISTS `quanly_sanpham`
  -- Bảng mã utf8mb4 cho phép lưu đầy đủ ký tự Unicode, bao gồm tiếng Việt và emoji.
  CHARACTER SET utf8mb4
  -- Bộ đối chiếu unicode_ci giúp so sánh chuỗi không phân biệt chữ hoa/chữ thường.
  COLLATE utf8mb4_unicode_ci;

-- Chọn cơ sở dữ liệu vừa tạo để các câu lệnh tiếp theo thao tác đúng nơi.
USE `quanly_sanpham`;

-- Xóa bảng `products` cũ nếu đã tồn tại để bảo đảm tệp có thể chạy lại nhiều lần
-- mà không bị lỗi "table already exists" và luôn bắt đầu từ dữ liệu mẫu sạch.
DROP TABLE IF EXISTS `products`;

-- Tạo bảng `products` lưu thông tin sản phẩm.
CREATE TABLE `products` (
  -- Cột khóa chính: số nguyên, không được rỗng, tự động tăng sau mỗi lần thêm.
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Mã sản phẩm, khóa chính tự tăng',

  -- Cột tên sản phẩm: chuỗi tối đa 100 ký tự và bắt buộc phải có giá trị.
  `name` VARCHAR(100) NOT NULL COMMENT 'Tên sản phẩm, bắt buộc nhập, tối đa 100 ký tự',

  -- Cột đơn giá: số nguyên, bắt buộc phải có giá trị.
  -- Ràng buộc CHECK bên dưới bảo đảm giá luôn lớn hơn 0 ở tầng cơ sở dữ liệu.
  `price` INT NOT NULL COMMENT 'Đơn giá tính bằng đồng, số nguyên lớn hơn 0',

  -- Cột số lượng tồn: số nguyên, bắt buộc phải có giá trị.
  -- Giá trị mặc định là 0 để thuận tiện khi thêm sản phẩm chưa có hàng.
  `stock` INT NOT NULL DEFAULT 0 COMMENT 'Số lượng tồn kho, số nguyên không âm',

  -- Đặt cột `id` làm khóa chính của bảng, bảo đảm mỗi sản phẩm có mã duy nhất.
  PRIMARY KEY (`id`),

  -- Ràng buộc kiểm tra: đơn giá phải lớn hơn 0 (không cho phép giá 0 hoặc âm).
  CONSTRAINT `chk_products_price_positive` CHECK (`price` > 0),

  -- Ràng buộc kiểm tra: số lượng tồn phải lớn hơn hoặc bằng 0 (không cho phép âm).
  CONSTRAINT `chk_products_stock_non_negative` CHECK (`stock` >= 0)
  -- Sử dụng InnoDB để hỗ trợ khóa ngoại, giao dịch và độ tin cậy cao.
) ENGINE=InnoDB
  -- Đặt bảng mã và bộ đối chiếu UTF-8 cho bảng.
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  -- Ghi chú mô tả mục đích của bảng ngay trong lược đồ cơ sở dữ liệu.
  COMMENT='Danh sách sản phẩm dùng cho ứng dụng quản lý sản phẩm';

-- Chèn ba dòng dữ liệu mẫu đúng theo bảng trong đề bài.
-- Không truyền cột `id` để MySQL tự sinh giá trị tăng dần (1, 2, 3).
INSERT INTO `products` (`name`, `price`, `stock`) VALUES
  -- Sản phẩm thứ nhất: Bút bi, giá 5.000 đồng, tồn 20.
  ('Bút bi', 5000, 20),
  -- Sản phẩm thứ hai: Vở ô ly, giá 12.000 đồng, tồn 15.
  ('Vở ô ly', 12000, 15),
  -- Sản phẩm thứ ba: Thước kẻ, giá 8.000 đồng, tồn 0 (đã hết hàng).
  ('Thước kẻ', 8000, 0);

-- Kiểm tra nhanh dữ liệu vừa chèn để người dùng thấy kết quả ngay sau khi import.
-- Câu lệnh SELECT này chỉ đọc dữ liệu, không làm thay đổi nội dung bảng.
SELECT `id`, `name`, `price`, `stock` FROM `products` ORDER BY `id` ASC;
