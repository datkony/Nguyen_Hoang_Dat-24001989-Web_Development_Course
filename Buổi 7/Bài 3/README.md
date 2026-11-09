# Bài 3 - Giỏ hàng với component cha và component con `<dong-gio-hang>`

Dự án Vue 3 hoàn chỉnh minh họa kiến trúc cha - con trong quản lý giỏ hàng:

- Component cha `App.vue` giữ toàn bộ dữ liệu giỏ hàng.
- Component con `src/components/DongGioHang.vue` nhận từng sản phẩm bằng props và hiển thị thành tiền cùng các nút tăng, giảm, xóa.
- Component con không sửa props; mọi thay đổi được gửi lên cha bằng sự kiện.

## Chạy dự án

```bash
npm install
npm run dev
```

Mở địa chỉ mà Vite in ra trong terminal (thường là `http://localhost:5173`).

## Chạy kiểm thử và build

```bash
npm test
npm run build
```

## Cấu trúc chính

```text
Bài 3/
├─ index.html                         # Trang HTML gốc, chứa phần tử #app
├─ package.json                       # Khai báo phụ thuộc và các lệnh npm
├─ vite.config.js                     # Cấu hình Vite và Vitest
├─ src/
│  ├─ main.js                         # Tạo ứng dụng Vue và mount vào #app
│  ├─ App.vue                         # Component cha giữ dữ liệu và tính tổng
│  ├─ components/
│  │  └─ DongGioHang.vue              # Component con hiển thị một dòng sản phẩm
│  ├─ utils/
│  │  └─ dinhDangTien.js              # Hàm định dạng tiền dùng chung
│  └─ style.css                       # Giao diện responsive toàn ứng dụng
└─ tests/
   ├─ App.spec.js                     # Kiểm thử dữ liệu cha, sự kiện, tổng tiền, giỏ trống
   ├─ DongGioHang.spec.js             # Kiểm thử props, hiển thị, sự kiện, giới hạn 1-99
   └─ dinhDangTien.spec.js            # Kiểm thử hàm định dạng tiền
```

## Đối chiếu yêu cầu

1. **Mỗi mục trong giỏ gồm `id`, tên, đơn giá và số lượng**  
   `danhSachGioHang` trong `App.vue` chứa các object có đủ bốn trường `id`, `ten`, `donGia`, `soLuong`.

2. **Component con nhận dữ liệu bằng props, hiển thị thành tiền và các nút tăng, giảm, xóa**  
   `DongGioHang.vue` khai báo prop `sanPham`, tính `thanhTien = donGia × soLuong` bằng computed và hiển thị hai nút tăng/giảm, một ô nhập số lượng và nút xóa.

3. **Component con khai báo và phát sự kiện `doi-so-luong`, `yeu-cau-xoa`**  
   - `emits` khai báo tường minh hai sự kiện.
   - Nút tăng/giảm và ô nhập số lượng phát `doi-so-luong` kèm `id` và số lượng mới.
   - Nút xóa chỉ mở hộp xác nhận; sự kiện `yeu-cau-xoa` kèm `id` chỉ được phát sau khi người dùng bấm **Xác nhận xóa**.

4. **Component cha nhận sự kiện, kiểm tra dữ liệu rồi cập nhật mảng**  
   Cha kiểm tra:
   - `id` phải là số hữu hạn hoặc chuỗi không rỗng.
   - `soLuong` phải là số nguyên từ 1 đến 99.
   - Sản phẩm phải tồn tại trong mảng.
   
   Chỉ khi tất cả điều kiện đúng, cha mới cập nhật hoặc xóa phần tử.

5. **Số lượng chỉ nhận giá trị nguyên từ 1 đến 99**  
   Quy tắc được kiểm tra ở cả bốn lớp:
   - Prop validator của component con.
   - Phương thức tăng/giảm của component con.
   - Phương thức xác nhận ô nhập số lượng của component con.
   - Phương thức xử lý sự kiện của component cha.
   
   Nút tăng bị khóa khi số lượng bằng 99, nút giảm bị khóa khi số lượng bằng 1. Khi nhập tay, giỏ tự động cập nhật ngay lúc dữ liệu đang nhập là số nguyên từ 1 đến 99; nếu giá trị sai khi kết thúc nhập, ô nhập hiển thị thông báo lỗi, tự trở về số lượng hiện tại và không phát sự kiện.

6. **Component con không gán lại props hoặc sửa thuộc tính của đối tượng nhận qua props**  
   Component con chỉ đọc `this.sanPham.*`, không có phép gán nào vào prop. Ô nhập số lượng dùng một bản nháp cục bộ `soLuongNhap` trong `data`, tách biệt hoàn toàn với prop. Mọi thay đổi đều thông qua `$emit`. Có kiểm thử xác nhận đối tượng prop không đổi sau khi bấm tăng, giảm, nhập số lượng và xác nhận xóa.

7. **Component cha dùng computed tính tổng số lượng và tổng tiền**  
   - `tongSoLuong` dùng `reduce` cộng `soLuong`.
   - `tongTien` dùng `reduce` cộng `donGia × soLuong`.

8. **Khi xóa hết sản phẩm, hiển thị "Giỏ hàng trống"**  
   Computed `gioHangTrong` kiểm tra mảng rỗng; template hiển thị đúng chuỗi `Giỏ hàng trống` và các tổng đều bằng 0.

## Kết quả kiểm tra theo đề bài

Dữ liệu mẫu trong `App.vue`:

- 2 bút bi, đơn giá 5.000 đồng → thành tiền 10.000 đồng.
- 3 quyển vở, đơn giá 12.000 đồng → thành tiền 36.000 đồng.

Kết quả hiển thị:

- **Tổng số lượng:** `5`
- **Tổng tiền:** `46.000 đồng`

Các giá trị này được kiểm thử tự động trong `tests/App.spec.js`.

## Ghi chú kỹ thuật

- Component con được đăng ký cục bộ trong `App.vue` với tên `DongGioHang`, template sử dụng thẻ kebab-case `<dong-gio-hang>`.
- Hàm `dinhDangTien` được tách riêng để cha và con dùng chung, tránh sai lệch định dạng số tiền.
- Không có thao tác gán trực tiếp vào prop trong component con; dữ liệu chỉ thay đổi ở component cha.
- Giao diện responsive, có viền tiêu điểm cho bàn phím và tôn trọng cài đặt giảm chuyển động của người dùng.

## Xác nhận trước khi xóa

Thao tác xóa được bảo vệ bằng hai bước ngay trong `DongGioHang.vue`:

1. Người dùng bấm **Xóa** lần đầu: component con bật cờ `dangXacNhanXoa` và hiển thị hộp xác nhận. Chưa có sự kiện nào được phát lên cha.
2. Người dùng chọn:
   - **Xác nhận xóa**: component con phát `yeu-cau-xoa` kèm `id`; cha kiểm tra rồi mới xóa phần tử khỏi mảng.
   - **Hủy**: hộp xác nhận đóng lại, sản phẩm được giữ nguyên và không có sự kiện nào được phát.

Nhờ vậy, dù người dùng bấm nhầm nút Xóa thì dữ liệu giỏ hàng vẫn không thay đổi cho đến khi xác nhận.

## Nhập số lượng bằng bàn phím

- Giữa hai nút tăng/giảm là ô nhập `type="number"` với `min="1"`, `max="99"`, `step="1"`.
- Thông tin trong giỏ **tự động cập nhật ngay khi dữ liệu đang nhập là hợp lệ**: mỗi lần người dùng gõ phím, nếu giá trị hiện tại là số nguyên từ 1 đến 99 thì component con phát `doi-so-luong` ngay, cha lập tức cập nhật mảng và các tổng số lượng/tổng tiền.
- Khi người dùng nhấn **Enter** hoặc rời khỏi ô, component con kiểm tra lần cuối, chuẩn hóa giá trị và báo lỗi nếu cần; không phát trùng sự kiện đã gửi trong lúc gõ.
- Component con lưu giá trị đang gõ trong bản nháp cục bộ `soLuongNhap`; khi hợp lệ mới phát `doi-so-luong`, không ghi trực tiếp vào prop.
- Giá trị chưa hợp lệ (ví dụ đang gõ dở, hoặc `0`, `100`, `1.5`, chữ) sẽ không được cập nhật. Khi kết thúc nhập, nếu vẫn sai thì:
  - Hiển thị thông báo lỗi ngay dưới dòng sản phẩm.
  - Đưa ô nhập về đúng số lượng hiện tại do cha quản lý.
  - Không phát sự kiện nào.
- Khi cha cập nhật số lượng, `watch` theo dõi `sanPham.soLuong` sẽ đồng bộ lại ô nhập.

## Kiểm thử liên quan

- `tests/DongGioHang.spec.js` kiểm tra hiển thị, tăng/giảm, nhập bàn phím, giá trị biên, xác nhận/hủy xóa và việc không sửa props.
- `tests/App.spec.js` kiểm tra luồng tích hợp: xóa phải xác nhận, hủy không xóa, nhập số lượng bằng bàn phím cập nhật đúng tổng số lượng và tổng tiền của cha.
