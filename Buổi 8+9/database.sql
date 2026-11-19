-- ============================================================================
-- GIAI ĐOẠN 1: TẠO CƠ SỞ DỮ LIỆU CHO ỨNG DỤNG QUẢN LÝ SẢN PHẨM
-- ----------------------------------------------------------------------------
-- Tệp này được viết cho MySQL/MariaDB (XAMPP).
-- Cách sử dụng:
--   1. Mở phpMyAdmin, chọn thẻ "Import" và tải tệp này lên; hoặc
--   2. Mở terminal và chạy lệnh:
--        mysql -u root -p < database.sql
-- Tệp sẽ tạo cơ sở dữ liệu `quanly_sanpham`, tạo bảng `products`
-- (có thêm cột `image` lưu đường dẫn ảnh sản phẩm) và chèn sẵn 12 bản ghi mẫu
-- để phục vụ cả tính năng upload ảnh lẫn tính năng phân trang 5 sản phẩm/trang.
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

  -- Cột đường dẫn ảnh: chuỗi tối đa 255 ký tự cho phép NULL vì ảnh là tùy chọn.
  -- Giá trị lưu là đường dẫn tương đối dạng "uploads/ten_file.png" để giao diện dùng trực tiếp trong thẻ img.
  -- Mặc định NULL nghĩa là sản phẩm chưa có ảnh và bảng sẽ hiển thị "Chưa có ảnh".
  `image` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Đường dẫn ảnh sản phẩm trong thư mục uploads, có thể để trống',

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

-- Chèn 12 dòng dữ liệu mẫu: ba sản phẩm đầu giữ nguyên theo đề bài,
-- chín sản phẩm còn lại được bổ sung để bảng có đủ 3 trang (5 + 5 + 2).
-- Không truyền cột `id` để MySQL tự sinh giá trị tăng dần (1 đến 12).
-- Không truyền cột `image` để mặc định NULL, tức là mọi sản phẩm ban đầu đều "Chưa có ảnh".
INSERT INTO `products` (`name`, `price`, `stock`) VALUES
  -- Sản phẩm thứ 1: Bút bi, giá 5.000 đồng, tồn 20 (dữ liệu gốc của đề bài).
  ('Bút bi', 5000, 20),
  -- Sản phẩm thứ 2: Vở ô ly, giá 12.000 đồng, tồn 15 (dữ liệu gốc của đề bài).
  ('Vở ô ly', 12000, 15),
  -- Sản phẩm thứ 3: Thước kẻ, giá 8.000 đồng, tồn 0 - đã hết hàng (dữ liệu gốc).
  ('Thước kẻ', 8000, 0),
  -- Sản phẩm thứ 4: Tẩy, giá 3.000 đồng, tồn 40 (bổ sung cho trang 1).
  ('Tẩy', 3000, 40),
  -- Sản phẩm thứ 5: Bút chì, giá 4.000 đồng, tồn 35 (bổ sung cho trang 1).
  ('Bút chì', 4000, 35),
  -- Sản phẩm thứ 6: Vở ghi, giá 15.000 đồng, tồn 25 (bổ sung cho trang 2).
  ('Vở ghi', 15000, 25),
  -- Sản phẩm thứ 7: Giấy A4, giá 60.000 đồng, tồn 12 (bổ sung cho trang 2).
  ('Giấy A4', 60000, 12),
  -- Sản phẩm thứ 8: Bút dạ, giá 18.000 đồng, tồn 18 (bổ sung cho trang 2).
  ('Bút dạ', 18000, 18),
  -- Sản phẩm thứ 9: Kẹp giấy, giá 9.000 đồng, tồn 50 (bổ sung cho trang 2).
  ('Kẹp giấy', 9000, 50),
  -- Sản phẩm thứ 10: Bút máy, giá 45.000 đồng, tồn 8 (bổ sung cho trang 2).
  ('Bút máy', 45000, 8),
  -- Sản phẩm thứ 11: Dập ghim, giá 35.000 đồng, tồn 6 (bổ sung cho trang 3).
  ('Dập ghim', 35000, 6),
  -- Sản phẩm thứ 12: Băng keo, giá 7.000 đồng, tồn 30 (bổ sung cho trang 3).
  ('Băng keo', 7000, 30);

-- Kiểm tra nhanh dữ liệu vừa chèn để người dùng thấy kết quả ngay sau khi import.
-- Câu lệnh SELECT này chỉ đọc dữ liệu, không làm thay đổi nội dung bảng.
-- Cột image được chọn thêm để dễ đối chiếu với API trả về.
SELECT `id`, `name`, `price`, `stock`, `image` FROM `products` ORDER BY `id` ASC;
