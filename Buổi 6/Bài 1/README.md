# Bài 1 - Trang hiển thị thông tin một sinh viên (Vue 3)

Dự án được tạo từ file `bai_1.vue`, sử dụng **Vue 3 + Vite**.

## 1. Cấu trúc dự án

```
Bài 1/
├─ index.html                 # Trang HTML gốc, chứa <div id="app">
├─ package.json               # Khai báo thông tin dự án và các lệnh chạy
├─ vite.config.js             # Cấu hình Vite (base './' để chạy được trong thư mục con)
├─ public/
│  └─ avatar.svg              # Ảnh đại diện mẫu (có thể thay bằng ảnh thật)
└─ src/
   ├─ main.js                 # Khởi tạo ứng dụng Vue và mount vào #app
   ├─ App.vue                 # Component gốc, lắp ghép component Bai1
   ├─ style.css               # CSS dùng chung cho toàn ứng dụng
   └─ components/
      └─ bai_1.vue            # ⭐ Toàn bộ nội dung bài làm (template + script + style)
```

Toàn bộ mã nguồn của bài tập (dữ liệu, computed, chỉ thị Vue, CSS) vẫn nằm trong
duy nhất file `src/components/bai_1.vue`. Các file còn lại chỉ là "khung" của dự án.

## 2. Cài đặt và chạy

Mở terminal tại thư mục `Bài 1`:

```bash
npm install     # Cài thư viện (chỉ cần chạy lần đầu)
npm run dev     # Chạy chế độ phát triển
```

Sau đó mở trình duyệt tại địa chỉ mà Vite in ra (mặc định `http://localhost:5173`).

## 3. Build để chạy trên XAMPP

```bash
npm run build
```

Lệnh này tạo thư mục `dist/`. Truy cập qua XAMPP:

```
http://localhost/Môn phát triển ứng dụng web/Buổi 6/Bài 1/dist/
```

Cấu hình `base: './'` trong `vite.config.js` đảm bảo các file JS/CSS được nạp đúng
khi đặt dự án trong thư mục con có dấu tiếng Việt và khoảng trắng.

## 4. Các yêu cầu của đề đã thực hiện

| Yêu cầu | Vị trí trong `bai_1.vue` |
|---|---|
| Khai báo dữ liệu (mã SV, họ tên, lớp, ảnh, điểm TB, trạng thái) | `data()` |
| Họ tên bằng `{{ }}`, lớp bằng `v-text` | phần `<template>` |
| Ảnh và nội dung thay thế bằng `v-bind` | `v-bind:src`, `v-bind:alt` |
| Xếp loại theo điểm trung bình | computed `xepLoai` |
| Đổi màu xếp loại | `:class="mauXepLoai"` + CSS `.loai-*` |
| Hiển thị "Đang học" / "Đã nghỉ" | computed `trangThai` |
| Bật/tắt phần giới thiệu | `v-show="hienGioiThieu"` |

## 5. Kiểm tra dữ liệu đầu vào (phần bổ sung)

Phần "Kiểm tra thay đổi dữ liệu" cho phép sửa **họ tên, lớp, điểm trung bình**
và đổi trạng thái ngay trên trang. Mọi giá trị đều được kiểm tra **ngay khi gõ**:

| Ô nhập | Quy tắc kiểm tra | Khi sai |
|---|---|---|
| Điểm TB | Phải là số, `0 ≤ điểm ≤ 10`, tối đa 2 chữ số thập phân. Chấp nhận cả dấu phẩy `8,5` | Từ chối giá trị, giữ nguyên điểm cũ, hiện lỗi đỏ, viền ô đỏ |
| Họ tên | Không rỗng, tối đa 50 ký tự, chỉ gồm chữ cái (kể cả tiếng Việt), khoảng trắng và `. ' -` | Từ chối, hiện lỗi; rời ô sẽ khôi phục giá trị hợp lệ gần nhất |
| Lớp | Không rỗng, tối đa 30 ký tự, chỉ gồm chữ, số, khoảng trắng và `. _ -` | Từ chối, hiện lỗi; rời ô sẽ khôi phục giá trị hợp lệ gần nhất |

Ví dụ: nhập điểm `11` → ô viền đỏ, hiện "Điểm không được lớn hơn 10…",
điểm/xếp loại/màu sắc trên thẻ sinh viên **không đổi**. Bấm ra ngoài ô,
giá trị tự trở về điểm hợp lệ gần nhất.

Nút "Khôi phục dữ liệu ban đầu" đưa toàn bộ dữ liệu và các ô nhập về trạng thái gốc.
