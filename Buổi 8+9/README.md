# Website quản lý sản phẩm - PHP, MySQL & Vue.js

Ứng dụng quản lý sản phẩm hoàn chỉnh theo 4 giai đoạn của đề bài:

1. Tạo cơ sở dữ liệu MySQL.
2. Xây dựng API CRUD bằng PHP + PDO.
3. Xây dựng giao diện bằng Vue.js.
4. Kiểm tra các tình huống bắt buộc.

Toàn bộ mã nguồn đều được chú thích chi tiết đến cấp độ từng câu lệnh.

---

## 1. Công nghệ sử dụng

| Thành phần | Công nghệ |
|---|---|
| Máy chủ web | Apache (XAMPP) hoặc PHP built-in server |
| Ngôn ngữ backend | PHP 8.2 |
| Cơ sở dữ liệu | MySQL / MariaDB, kết nối bằng PDO |
| Giao diện | Vue.js 3 (Options API), tải từ thư viện nội bộ |
| Giao tiếp | Fetch API, dữ liệu JSON |
| Kiểm thử | PHP cURL + Playwright (Chrome headless) |

Thư viện Vue được lưu tại `vendor/vue.global.prod.js`, vì vậy giao diện vẫn chạy được khi máy không có Internet.

---

## 2. Cấu trúc thư mục

```text
Buổi 8/
├─ database.sql                # Giai đoạn 1: tạo CSDL, bảng products và 3 dòng mẫu
├─ index.html                  # Giai đoạn 3: giao diện Vue.js (template + v-for + v-model)
├─ favicon.svg                 # Biểu tượng hiển thị trên tab trình duyệt
├─ api/
│  ├─ config.php               # Cấu hình kết nối MySQL và hàm tạo PDO
│  └─ products.php             # Giai đoạn 2: API GET / POST / PUT / DELETE
├─ css/
│  └─ style.css                # CSS giao diện, responsive, chú thích từng quy tắc
├─ js/
│  └─ app.js                   # Logic Vue.js: tải, thêm, sửa, xóa sản phẩm
├─ vendor/
│  └─ vue.global.prod.js       # Thư viện Vue 3 bản production
├─ tests/
│  └─ kiem-tra-api.php         # Giai đoạn 4: kịch bản kiểm tra tự động API
└─ README.md                   # Tài liệu hướng dẫn và kết quả kiểm tra
```

---

## 3. Cài đặt và chạy ứng dụng

### Bước 1: Khởi động XAMPP

Mở **XAMPP Control Panel** và bấm **Start** cho hai dịch vụ:

- **Apache**
- **MySQL**

### Bước 2: Tạo cơ sở dữ liệu

Có hai cách:

**Cách A - Dùng phpMyAdmin**

1. Mở `http://localhost/phpmyadmin`.
2. Chọn thẻ **Import**.
3. Chọn tệp `database.sql` trong thư mục dự án.
4. Bấm **Import**.

**Cách B - Dùng dòng lệnh**

```bash
mysql -u root -p < database.sql
```

Tệp `database.sql` sẽ:

- Tạo cơ sở dữ liệu `quanly_sanpham` với bảng mã `utf8mb4`.
- Tạo bảng `products`.
- Chèn ba sản phẩm mẫu: Bút bi, Vở ô ly, Thước kẻ.

### Bước 3: Kiểm tra cấu hình kết nối

Mở `api/config.php` và kiểm tra các giá trị mặc định:

```php
DB_HOST = 127.0.0.1
DB_PORT = 3306
DB_NAME = quanly_sanpham
DB_USER = root
DB_PASS = (để trống)
```

Đây là cấu hình mặc định của XAMPP. Nếu MySQL dùng cổng, tài khoản hoặc mật khẩu khác, sửa trực tiếp trong tệp hoặc đặt biến môi trường `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.

### Bước 4: Mở ứng dụng

Nếu dự án nằm trong `htdocs`, mở:

```text
http://localhost/Môn phát triển ứng dụng web/Buổi 8/
```

Hoặc chạy bằng PHP built-in server:

```powershell
cd "F:\xampp\htdocs\Môn phát triển ứng dụng web\Buổi 8"
php -S 127.0.0.1:8080
```

Sau đó mở `http://127.0.0.1:8080`.

> Nếu MySQL đang chạy ở cổng khác 3306, đặt biến môi trường trước khi chạy:
>
> ```powershell
> $env:DB_PORT = "3307"
> php -S 127.0.0.1:8080
> ```

---

## 4. Giai đoạn 1 - Cơ sở dữ liệu

Bảng `products` trong `database.sql`:

| Cột | Kiểu dữ liệu | Ràng buộc |
|---|---|---|
| `id` | INT | Khóa chính, tự tăng |
| `name` | VARCHAR(100) | NOT NULL, bắt buộc nhập |
| `price` | INT | NOT NULL, CHECK `price > 0` |
| `stock` | INT | NOT NULL, mặc định 0, CHECK `stock >= 0` |

Dữ liệu mẫu:

| Tên sản phẩm | Đơn giá | Số lượng tồn |
|---|---:|---:|
| Bút bi | 5.000 | 20 |
| Vở ô ly | 12.000 | 15 |
| Thước kẻ | 8.000 | 0 |

Ngoài kiểm tra ở PHP, các ràng buộc `CHECK` còn bảo vệ dữ liệu ở tầng cơ sở dữ liệu.

---

## 5. Giai đoạn 2 - API PHP

### 5.1. Danh sách endpoint

| Phương thức | Đường dẫn | Chức năng | Thành công |
|---|---|---|---|
| GET | `api/products.php` | Lấy danh sách sản phẩm | 200 |
| POST | `api/products.php` | Thêm sản phẩm mới | 201 |
| PUT | `api/products.php?id=1` | Cập nhật sản phẩm theo id | 200 |
| DELETE | `api/products.php?id=1` | Xóa sản phẩm theo id | 200 |

### 5.2. Định dạng phản hồi

Thành công:

```json
{
  "success": true,
  "data": [
    { "id": 1, "name": "Bút bi", "price": 5000, "stock": 20 }
  ]
}
```

Thêm / sửa thành công có thêm `message` và `data` của sản phẩm:

```json
{
  "success": true,
  "message": "Thêm sản phẩm thành công.",
  "data": { "id": 4, "name": "Bút chì", "price": 3000, "stock": 10 }
}
```

Thất bại:

```json
{
  "success": false,
  "message": "Số lượng tồn phải là số nguyên không âm và không vượt quá 2147483647."
}
```

### 5.3. Dữ liệu gửi lên khi thêm / sửa

```json
{
  "name": "Bút chì",
  "price": 3000,
  "stock": 10
}
```

### 5.4. Quy tắc kiểm tra dữ liệu

| Trường | Quy tắc | Mã lỗi |
|---|---|---|
| `name` | Sau khi `trim` phải có từ 1 đến 100 ký tự Unicode | 400 |
| `price` | Phải là số nguyên lớn hơn 0, không vượt quá giới hạn INT | 400 |
| `stock` | Phải là số nguyên không âm, không vượt quá giới hạn INT | 400 |
| `id` | Phải là số nguyên dương, bắt buộc với PUT / DELETE | 400 |
| Sản phẩm không tồn tại | PUT / DELETE trả thông báo không tìm thấy | 404 |
| Phương thức không hỗ trợ | Trả danh sách phương thức hợp lệ trong header `Allow` | 405 |

Các điểm kỹ thuật quan trọng:

- Dùng **PDO prepared statement** cho mọi truy vấn có tham số.
- Bật `PDO::ERRMODE_EXCEPTION` để bắt lỗi SQL tập trung.
- Tắt `PDO::ATTR_EMULATE_PREPARES` để MySQL xử lý prepared statement thật.
- Không trả chi tiết lỗi SQL cho người dùng; chi tiết được ghi vào `error_log`.
- Hỗ trợ CORS và xử lý `OPTIONS` để có thể gọi API từ giao diện chạy cổng khác.

---

## 6. Giai đoạn 3 - Giao diện Vue.js

### 6.1. Chức năng

1. Khi mở trang, `mounted()` gọi `fetchProducts()` để lấy dữ liệu từ API.
2. Bảng dùng `v-for` hiển thị tên sản phẩm, đơn giá, số lượng tồn và hai nút **Sửa**, **Xóa**.
3. Biểu mẫu có ba ô **Tên sản phẩm**, **Đơn giá**, **Số lượng tồn**, liên kết bằng `v-model`.
4. Nút **Lưu sản phẩm** ở chế độ thêm gửi `POST`.
5. Nút **Sửa** đưa dữ liệu dòng được chọn lên biểu mẫu; nút **Cập nhật** gửi `PUT` kèm id.
6. Nút **Xóa** hiển thị `window.confirm`; chỉ gửi `DELETE` khi người dùng đồng ý.
7. Sau khi thêm, sửa hoặc xóa thành công, ứng dụng gọi lại API và cập nhật bảng, không tải lại trang.
8. Nếu API trả lỗi, thông báo lỗi hiển thị trong hộp cảnh báo phía trên bảng.
9. Đơn giá được định dạng theo tiền Việt Nam, ví dụ `5.000 đ`.
10. Giao diện responsive, có trạng thái đang tải, danh sách rỗng và thông báo thành công.

### 6.2. Luồng gọi API

```text
Mở trang
   └─ GET api/products.php
        └─ Hiển thị bảng bằng v-for

Lưu ở chế độ thêm
   └─ POST api/products.php  { name, price, stock }
        └─ Gọi lại GET để làm mới bảng

Lưu ở chế độ sửa
   └─ PUT api/products.php?id=<id>  { name, price, stock }
        └─ Gọi lại GET để làm mới bảng

Bấm Xóa
   └─ window.confirm()
        ├─ Hủy  -> không gửi yêu cầu
        └─ Đồng ý -> DELETE api/products.php?id=<id>
                       └─ Gọi lại GET để làm mới bảng
```

---

## 7. Giai đoạn 4 - Kết quả kiểm tra

### 7.1. Kiểm tra API tự động

> Nên import lại `database.sql` trước khi chạy để bảo đảm có đủ ba sản phẩm mẫu.

Chạy kịch bản:

```powershell
php tests/kiem-tra-api.php http://127.0.0.1:8080/api/products.php
```

Kết quả thực tế:

```text
=== TỔNG KẾT: 31/31 bài kiểm tra đạt ===
Tất cả bài kiểm tra đều đạt.
```

Kịch bản kiểm tra gồm:

- GET trả về 200, `success = true`, `data` là mảng.
- Ba sản phẩm mẫu tồn tại trong cơ sở dữ liệu.
- `id`, `price`, `stock` là số nguyên trong JSON.
- POST thêm sản phẩm hợp lệ trả 201.
- Gọi lại GET sau khi thêm: sản phẩm mới vẫn xuất hiện.
- PUT sửa đơn giá: danh sách trả về đơn giá mới.
- POST với `stock = -1`: trả 400, `success = false`, có `message`.
- Sau yêu cầu `stock = -1`: MySQL không có bản ghi mới.
- Từ chối tên rỗng, tên quá 100 ký tự, giá bằng 0, giá là số thực.
- PUT / DELETE sản phẩm không tồn tại trả 404.
- PUT thiếu id trả 400.
- DELETE sản phẩm: gọi lại GET không còn sản phẩm đã xóa.

Ngoài ra, ràng buộc `CHECK` của MySQL cũng được kiểm tra trực tiếp: câu lệnh
`INSERT ... stock = -1` bị từ chối với lỗi `CONSTRAINT chk_products_stock_non_negative failed`
và số bản ghi trong bảng không thay đổi.

### 7.2. Kiểm tra giao diện bằng Playwright + Chrome

Kịch bản tự động mở trang thật, thao tác như người dùng và kiểm tra DOM. Kết quả thực tế:

```text
PASS 1: Trang tải danh sách sản phẩm bằng v-for
PASS 2: Thêm sản phẩm và tải lại trang vẫn còn dữ liệu
PASS 3: Sửa đơn giá và tải lại trang vẫn hiển thị giá mới
PASS 4: Tồn kho -1 bị PHP từ chối, MySQL không có bản ghi mới
PASS 5: Giao diện hiển thị thông báo lỗi từ API
PASS 6: Hủy xác nhận xóa thì không gửi DELETE
PASS 7: Xóa sản phẩm và tải lại trang không còn sản phẩm

UI_E2E_PASS: 7/7 kịch bản giao diện đạt
```

### 7.3. Đối chiếu bảng kiểm tra của đề bài

| Thao tác | Kết quả mong đợi | Kết quả thực tế |
|---|---|---|
| Thêm sản phẩm hợp lệ rồi tải lại trang | Sản phẩm mới vẫn xuất hiện | **Đạt** - sản phẩm còn sau `page.reload()` và trong GET |
| Sửa đơn giá của một sản phẩm | Bảng hiển thị đơn giá mới | **Đạt** - bảng hiển thị `21.000 đ` sau khi sửa |
| Gửi trực tiếp yêu cầu thêm với `stock = -1` | PHP trả lỗi, MySQL không có bản ghi mới | **Đạt** - HTTP 400, `success = false`, số bản ghi không đổi |
| Xóa một sản phẩm rồi tải lại trang | Sản phẩm đã xóa không còn trong danh sách | **Đạt** - dòng biến mất và không quay lại sau khi tải lại |

---

## 8. Ghi chú

- Dữ liệu kiểm thử được tạo với tên duy nhất và được xóa tự động sau khi chạy.
- Tệp `tests/kiem-tra-api.php` chỉ dùng cho kiểm thử, không được gọi từ giao diện.
- Nếu gặp lỗi **Không thể kết nối đến cơ sở dữ liệu**, kiểm tra:
  1. MySQL đã chạy chưa.
  2. Tên cơ sở dữ liệu trong `api/config.php` có đúng `quanly_sanpham` không.
  3. Cổng MySQL có đúng 3306 không.
  4. Tài khoản `root` có mật khẩu không.
- Nếu giao diện hiển thị **Không thể kết nối đến API**, kiểm tra Apache hoặc PHP built-in server đã chạy và đường dẫn `api/products.php` tồn tại.
