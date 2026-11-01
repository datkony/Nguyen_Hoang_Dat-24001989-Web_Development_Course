# Bài 2 - Form đặt vé xem phim (Vue 3)

Dự án được tạo từ file `bai_2.vue`, sử dụng **Vue 3 + Vite**.

## 1. Cấu trúc dự án

```
Bài 2/
├─ index.html                 # Trang HTML gốc, chứa <div id="app">
├─ package.json               # Khai báo thông tin dự án và các lệnh chạy
├─ vite.config.js             # Cấu hình Vite (base './' + cổng 5174)
└─ src/
   ├─ main.js                 # Khởi tạo ứng dụng Vue và mount vào #app
   ├─ App.vue                 # Component gốc, lắp ghép component Bai2
   ├─ style.css               # CSS dùng chung cho toàn ứng dụng
   └─ components/
      └─ bai_2.vue            # ⭐ Toàn bộ nội dung bài làm (template + script + style)
```

Toàn bộ logic và giao diện của bài tập nằm trong `src/components/bai_2.vue`.
Các file còn lại chỉ là "khung" của dự án.

## 2. Cài đặt và chạy

Mở terminal tại thư mục `Bài 2`:

```bash
npm install     # Cài thư viện (chỉ cần chạy lần đầu)
npm run dev     # Chạy chế độ phát triển
```

Sau đó mở trình duyệt tại `http://localhost:5174` (cổng 5174 để không trùng Bài 1).

## 3. Build để chạy trên XAMPP

```bash
npm run build
```

Lệnh này tạo thư mục `dist/`. Truy cập qua XAMPP:

```
http://localhost/Môn phát triển ứng dụng web/Buổi 6/Bài 2/dist/
```

Cấu hình `base: './'` trong `vite.config.js` đảm bảo các file JS/CSS được nạp đúng
khi đặt dự án trong thư mục con có dấu tiếng Việt và khoảng trắng.

## 4. Các yêu cầu của đề đã thực hiện

| # | Yêu cầu | Vị trí trong `bai_2.vue` |
|---|---|---|
| 1 | Nhập họ tên bằng `v-model.trim` | ô `#o-ho-ten` |
| 2 | Chọn suất chiếu bằng `<select>`, ít nhất 3 suất | ô `#o-suat-chieu`, mảng `DANH_SACH_SUAT_CHIEU` (4 suất) |
| 3 | Radio loại vé: Vé thường 70.000, Vé VIP 100.000 | nhóm "Loại vé", hằng số `GIA_VE` |
| 4 | Số lượng `v-model.number`, chỉ nhận số nguyên 1 – 10 | ô `#o-so-luong` + computed `soLuongHopLe`, `loiSoLuong` |
| 5 | Checkbox combo bắp nước 45.000 đồng/đơn | ô combo + hằng số `GIA_COMBO` |
| 6 | Computed tổng tiền = giá vé × số lượng + combo | computed `tongTien` (và `tienVe`, `tienCombo`) |
| 7 | Nút tăng, giảm số lượng và nút đặt lại form | `tangSoLuong`, `giamSoLuong`, `datLaiForm` |
| 8 | Nút "Đặt vé" chỉ bật khi họ tên không rỗng và số lượng hợp lệ | computed `coTheDatVe` + `:disabled` |
| 9 | Gửi form bằng `@submit.prevent`, hiện xác nhận trên trang | `<form @submit.prevent="datVe">` + khối `.hop-xac-nhan` |

## 5. Quy tắc kiểm tra dữ liệu

| Trường | Quy tắc | Khi sai |
|---|---|---|
| Họ tên | Không rỗng sau khi `trim`, tối đa 50 ký tự | Hiện lỗi khi rời ô / bấm đặt vé, nút "Đặt vé" bị khoá |
| Số lượng | Số nguyên từ 1 đến 10 | Hiện lỗi cụ thể, nút "Đặt vé" bị khoá, rời ô tự chuẩn hoá về 1 – 10 |
| Suất chiếu | Phải nằm trong danh sách 4 suất | Chỉ chọn được từ danh sách nên luôn hợp lệ; vẫn kiểm tra phòng vệ |
| Loại vé | Phải là `thuong` hoặc `vip` | Chỉ chọn được từ radio nên luôn hợp lệ; vẫn kiểm tra phòng vệ |

Các trường hợp biên đã xử lý:

- Ô số lượng bị xoá trắng: `v-model.number` trả về `''` → không hợp lệ, nút bị khoá.
- Nhập số lẻ `2.5`: báo "phải là số nguyên"; khi rời ô tự chuẩn hoá thành `2`.
- Nhập `0`, số âm hoặc `11`: báo lỗi; khi rời ô tự kẹp về `1` hoặc `10`.
- Nút tăng/giảm dùng `type="button"` nên **không** vô tình submit form.
- Khi số lượng chưa hợp lệ, tổng tiền hiển thị `—` thay vì một con số sai.
- Thông tin xác nhận là một "ảnh chụp" (snapshot) tại thời điểm đặt vé, nên nếu sửa
  form sau đó thì xác nhận vẫn phản ánh đúng lần đặt gần nhất.
- Nút "Đặt lại form" xoá cả thông tin xác nhận cũ.

## 6. Kết quả kiểm tra theo đề

Chọn **Vé VIP**, số lượng **2**, tick **combo bắp nước**, nhập họ tên bất kỳ:

```
Tổng tiền = 100.000 × 2 + 45.000 = 245.000 đồng
```

Bấm "Đặt vé", khối "Xác nhận đặt vé" hiển thị đúng **245.000 đồng**.
