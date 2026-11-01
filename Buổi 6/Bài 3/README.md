# Bài 3 - Quản lý công việc (Vue 3)

Dự án được tạo từ file `bai_3.vue`, sử dụng **Vue 3 + Vite**.

## 1. Cấu trúc dự án

```
Bài 3/
├─ index.html                 # Trang HTML gốc, chứa <div id="app">
├─ package.json               # Khai báo thông tin dự án và các lệnh chạy
├─ vite.config.js             # Cấu hình Vite (base './' + cổng 5175)
├─ README.md                  # Tài liệu mô tả và kiểm thử
└─ src/
   ├─ main.js                 # Khởi tạo ứng dụng Vue và mount vào #app
   ├─ App.vue                 # Component gốc, lắp ghép component Bai3
   ├─ style.css               # CSS dùng chung cho toàn ứng dụng
   └─ components/
      └─ bai_3.vue            # ⭐ Toàn bộ nội dung bài làm (template + script + style)
```

Toàn bộ logic và giao diện của bài tập nằm trong `src/components/bai_3.vue`.
Các file còn lại chỉ là "khung" của dự án.

## 2. Cài đặt và chạy

Mở terminal tại thư mục `Bài 3`:

```bash
npm install     # Cài thư viện (chỉ cần chạy lần đầu)
npm run dev     # Chạy chế độ phát triển
```

Sau đó mở trình duyệt tại `http://localhost:5175` (cổng 5175 để không trùng Bài 1 và Bài 2).

## 3. Build để chạy trên XAMPP

```bash
npm run build
```

Lệnh này tạo thư mục `dist/`. Truy cập qua XAMPP:

```
http://localhost/Môn phát triển ứng dụng web/Buổi 6/Bài 3/dist/
```

Cấu hình `base: './'` trong `vite.config.js` đảm bảo các file JS/CSS được nạp đúng
khi đặt dự án trong thư mục con có dấu tiếng Việt và khoảng trắng.

## 4. Cấu trúc dữ liệu

Mỗi công việc là một object có đúng 3 thuộc tính theo yêu cầu:

```js
{
  id: 1,                                  // số nguyên duy nhất, dùng làm :key
  ten: 'Đọc lại kiến thức Vue 3 cơ bản',  // tên công việc (đã trim khi thêm)
  hoanThanh: true                         // true = đã xong, false = chưa xong
}
```

Dữ liệu mẫu ban đầu gồm 3 công việc (1 việc đã xong, 2 việc chưa xong) để thấy
ngay cách hoạt động. Xóa hết các công việc để kiểm tra thông báo danh sách rỗng.

## 5. Các yêu cầu của đề đã thực hiện

| # | Yêu cầu | Vị trí trong `bai_3.vue` |
|---|---|---|
| 1 | Mỗi công việc gồm `id`, tên và trạng thái hoàn thành | mảng `danhSachCongViec`, hằng số `CONG_VIEC_MAU` |
| 2 | Thêm bằng nút "Thêm" hoặc nhấn Enter | `<form @submit.prevent="themCongViec">` + nút `type="submit"` |
| 3 | Không cho thêm tên rỗng / chỉ chứa khoảng trắng | `themCongViec` kiểm tra `trim()`, computed `loiNhap` |
| 4 | Hiển thị bằng `v-for`, dùng `id` làm `:key` | `<li v-for="congViec in danhSachDaLoc" :key="congViec.id">` |
| 5 | Checkbox đánh dấu hoàn thành, việc đã xong gạch ngang | `v-model="congViec.hoanThanh"` + class `gach-ngang` |
| 6 | Xóa từng công việc | method `xoaCongViec(congViec.id)` |
| 7 | Lọc 3 trạng thái: Tất cả / Chưa hoàn thành / Đã hoàn thành | biến `boLoc` + computed `danhSachDaLoc` |
| 8 | Computed tổng số việc và số việc chưa hoàn thành | `tongSoCongViec`, `soViecChuaHoanThanh` |
| 9 | Nút "Xóa các việc đã hoàn thành" | method `xoaCacViecDaHoanThanh` |
| 10 | Thông báo khi danh sách rỗng hoặc kết quả lọc rỗng | hai khối `v-if` / `v-else-if` với class `trang-thai-rong` |

Mọi thao tác đều diễn ra trên cùng một trang:

- Form dùng `@submit.prevent` nên **không tải lại trang**.
- Các nút lọc và nút xóa đều có `type="button"` (trừ nút "Thêm" là `type="submit"`).
- Thêm/xóa/đổi trạng thái đều thay đổi mảng phản ứng, kéo theo computed tự tính lại.

## 6. Quy tắc kiểm tra và các trường hợp biên

| Trường hợp | Kết quả |
|---|---|
| Nhập tên bình thường rồi bấm "Thêm" | Thêm công việc mới vào đầu danh sách, ô nhập được xóa trắng |
| Nhập tên rồi nhấn Enter | Thêm công việc giống hệt bấm nút "Thêm" |
| Bấm "Thêm" khi ô trống | Không thêm, hiện lỗi tiếng Việt, giữ con trỏ ở ô nhập |
| Nhập toàn khoảng trắng rồi bấm "Thêm" | Không thêm (vì `trim()` thành chuỗi rỗng), hiện lỗi |
| Nhập `"  Học Vue  "` | Lưu thành `"Học Vue"` (bỏ khoảng trắng hai đầu) |
| Tên có nhiều từ | Được giữ nguyên khoảng trắng giữa các từ |
| Tên rất dài | Tự xuống dòng, không làm vỡ bố cục |
| Tick checkbox | Công việc gạch ngang, nhãn đổi thành "Đã hoàn thành", số liệu cập nhật |
| Bỏ tick checkbox | Công việc hết gạch ngang, số liệu cập nhật |
| Xóa một công việc | Chỉ công việc đó bị xóa, các công việc khác giữ nguyên |
| Lọc "Chưa hoàn thành" | Chỉ hiện việc chưa xong, số đếm trên nút và bảng thống kê đúng |
| Lọc "Đã hoàn thành" | Chỉ hiện việc đã xong |
| Lọc "Tất cả" | Hiện toàn bộ danh sách |
| Xóa các việc đã hoàn thành | Chỉ các việc đã xong bị xóa; nút tự khóa khi không còn việc đã xong |
| Xóa hết công việc | Hiện thông báo "Danh sách công việc đang trống" |
| Lọc "Chưa hoàn thành" khi mọi việc đã xong | Hiện thông báo "Không còn công việc nào chưa hoàn thành" |
| Lọc "Đã hoàn thành" khi chưa có việc nào xong | Hiện thông báo "Chưa có công việc nào được hoàn thành" |
| Thêm việc khi đang lọc "Đã hoàn thành" | Tự chuyển về "Tất cả" để nhìn thấy việc mới (việc mới chưa hoàn thành) |
| Hai lần thêm liên tiếp | `id` luôn khác nhau (lấy id lớn nhất + 1), không trùng `:key` |
| Xóa rồi thêm lại | `id` mới vẫn không trùng với id còn lại |

## 7. Quy trình kiểm thử thủ công

1. Chạy `npm run dev`, mở `http://localhost:5175`.
2. Nhập `Học Vue 3` → bấm **Thêm**: công việc xuất hiện ở đầu danh sách,
   tổng số tăng lên 4.
3. Nhập `  Viết báo cáo  ` → nhấn **Enter**: công việc được lưu là `Viết báo cáo`
   (không có khoảng trắng thừa), tổng số tăng lên 5.
4. Xóa trắng ô nhập → bấm **Thêm**: không có công việc mới, hiện thông báo lỗi.
5. Nhập vài dấu cách → bấm **Thêm**: vẫn báo lỗi, không thêm.
6. Tick checkbox của một công việc: chữ bị gạch ngang, nhãn "Đã hoàn thành",
   số việc chưa hoàn thành giảm 1.
7. Bấm lần lượt **Tất cả**, **Chưa hoàn thành**, **Đã hoàn thành**:
   danh sách và số đếm thay đổi ngay, không tải lại trang.
8. Bấm **Xóa** ở một dòng: chỉ dòng đó biến mất, các số liệu cập nhật.
9. Bấm **Xóa các việc đã hoàn thành**: các việc đã xong biến mất;
   nếu không còn việc nào đã xong, nút bị khóa.
10. Xóa hết công việc còn lại: xuất hiện thông báo danh sách rỗng.
11. Thêm lại một việc, tick hoàn thành rồi lọc **Chưa hoàn thành**:
    xuất hiện thông báo kết quả lọc rỗng phù hợp.

## 8. Ghi chú

- Dữ liệu chỉ lưu trong bộ nhớ của component, không dùng `localStorage`.
  Tải lại trang sẽ trở về 3 công việc mẫu. Đề bài không yêu cầu lưu trữ lâu dài.
- Toàn bộ mã nguồn (template, script, style) đều có comment tiếng Việt giải thích
  chi tiết đến từng câu lệnh/nhóm thuộc tính.
