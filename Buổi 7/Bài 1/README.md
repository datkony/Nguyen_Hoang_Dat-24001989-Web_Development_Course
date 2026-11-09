# Bài 1 - Quản lý sản phẩm bằng Vue 3

Dự án Vue 3 hoàn chỉnh dùng Vite, hiển thị bảng quản lý sản phẩm với đầy đủ chức năng tìm kiếm, lọc, sắp xếp, thêm, xóa và thống kê tồn kho.

## Công nghệ sử dụng

- Vue 3 (Options API)
- Vite
- Vitest + Vue Test Utils + jsdom để kiểm thử tự động
- CSS thuần, không phụ thuộc thư viện giao diện bên ngoài

## Cách chạy dự án

```bash
# Cài đặt phụ thuộc
npm install

# Chạy môi trường phát triển
npm run dev

# Chạy kiểm thử tự động
npm test

# Build bản production
npm run build

# Xem trước bản build
npm run preview
```

Sau khi chạy `npm run dev`, mở địa chỉ mà Vite in ra (thường là `http://localhost:5173`).

## Cấu trúc thư mục

```text
Bài 1/
├─ index.html                  # Trang HTML gốc
├─ package.json                # Khai báo phụ thuộc và script
├─ vite.config.js              # Cấu hình Vite và Vitest
├─ src/
│  ├─ main.js                  # Điểm khởi động ứng dụng Vue
│  ├─ App.vue                  # Component gốc chứa toàn bộ giao diện và logic
│  ├─ style.css                # CSS toàn cục
│  ├─ data/
│  │  └─ products.js           # Danh mục và 9 sản phẩm mẫu thuộc 3 danh mục
│  └─ utils/
│     ├─ productUtils.js       # Tìm kiếm, lọc, sắp xếp, tính tồn kho, định dạng tiền
│     └─ productValidation.js  # Kiểm tra dữ liệu biểu mẫu thêm sản phẩm
└─ tests/
   ├─ productUtils.spec.js     # Kiểm thử logic tìm kiếm, lọc, sắp xếp, thống kê
   ├─ productValidation.spec.js# Kiểm thử các quy tắc kiểm tra dữ liệu
   └─ App.spec.js              # Kiểm thử giao diện và hành vi component
```

## Đối chiếu yêu cầu đề bài

1. **Bảng sản phẩm và nhãn trạng thái**
   - Bảng hiển thị ID, tên, danh mục, giá, tồn kho, trạng thái, giá trị tồn kho và thao tác.
   - Sản phẩm có `stock > 0` hiển thị nhãn **Còn hàng** màu xanh.
   - Sản phẩm có `stock = 0` hiển thị nhãn **Hết hàng** màu đỏ.

2. **Tìm kiếm theo tên**
   - Tìm kiếm dùng `toLocaleLowerCase('vi-VN')` nên không phân biệt chữ hoa/chữ thường.
   - Từ khóa được cắt khoảng trắng hai đầu trước khi so khớp.

3. **Lọc theo danh mục**
   - Có tùy chọn **Tất cả danh mục** và ba danh mục: Điện tử, Gia dụng, Thời trang.

4. **Sắp xếp theo giá**
   - Hỗ trợ không sắp xếp, giá tăng dần và giá giảm dần.
   - Hàm `getVisibleProducts` luôn tạo mảng mới trước khi `sort()`, nên thứ tự mảng dữ liệu gốc không bao giờ bị thay đổi.
   - Khi hai sản phẩm có cùng giá, so sánh thêm `id` để thứ tự ổn định.

5. **Kết hợp tìm kiếm, lọc và sắp xếp**
   - Cả ba điều kiện được xử lý trong cùng một computed `visibleProducts`.

6. **Thêm sản phẩm mới**
   - Tên không được rỗng (kể cả toàn khoảng trắng).
   - Giá phải là số lớn hơn 0.
   - Số lượng tồn phải là số nguyên không âm (0 hợp lệ).
   - Danh mục phải nằm trong danh sách hợp lệ.
   - ID mới được sinh tự động lớn hơn ID lớn nhất hiện có.

7. **Xóa sản phẩm**
   - Hiển thị hộp thoại xác nhận trước khi xóa.
   - Nếu người dùng hủy, dữ liệu giữ nguyên.

8. **Thống kê bằng computed**
   - `visibleProductCount` hiển thị số sản phẩm đang hiển thị trên tổng số sản phẩm.
   - `totalInventoryValue` là tổng của `price * stock` cho các sản phẩm đang hiển thị.
   - Cả hai chỉ số đều lấy từ `visibleProducts`, nên luôn khớp với bộ lọc hiện tại.
   - Khi không có sản phẩm phù hợp, giao diện hiển thị `0 / tổng` và `0 ₫`.

9. **Watch từ khóa**
   - Khi từ khóa (sau khi cắt khoảng trắng) có đúng một ký tự, hiển thị thông báo gợi ý.
   - Khi từ khóa rỗng hoặc có từ hai ký tự trở lên, thông báo được xóa.

10. **Mounted**
    - Trạng thái trang ban đầu là "Đang khởi tạo...".
    - Hook `mounted()` đổi trạng thái thành "Sẵn sàng".

## Ghi chú về kiểm thử

Bộ kiểm thử gồm 46 bài, bao phủ:

- Tìm kiếm không phân biệt hoa thường.
- Lọc theo danh mục.
- Sắp xếp tăng/giảm và kiểm tra mảng gốc không bị thay đổi.
- Kết hợp tìm kiếm + lọc + sắp xếp.
- Trạng thái không có sản phẩm phù hợp và thống kê tương ứng.
- Kiểm tra dữ liệu thêm sản phẩm (tên, giá, tồn kho, danh mục).
- Thêm sản phẩm thành công, bao gồm trường hợp tồn kho bằng 0.
- Xóa sản phẩm khi hủy xác nhận, khi đồng ý xác nhận và khi đang lọc.
- Watch thông báo một ký tự và `mounted()` đặt trạng thái "Sẵn sàng".
