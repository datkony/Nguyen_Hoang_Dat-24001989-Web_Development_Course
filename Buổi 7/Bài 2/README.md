# Bài 2 - Danh mục sản phẩm bằng component `the-san-pham`

Dự án Vue 3 hoàn chỉnh minh họa cách xây dựng danh mục sản phẩm bằng một component thẻ sản phẩm dùng lại được. Mỗi lần mua hàng thành công, số lượng tồn của đúng sản phẩm đó giảm đi 1 và không bao giờ xuống dưới 0.

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
Bài 2/
├─ index.html                  # Trang HTML gốc, chứa phần tử #app
├─ package.json                # Khai báo phụ thuộc và các lệnh npm
├─ vite.config.js              # Cấu hình Vite và Vitest
├─ public/images/              # Ảnh SVG của sản phẩm và ảnh giữ chỗ
├─ src/
│  ├─ main.js                  # Tạo app, ĐĂNG KÝ the-san-pham trước khi mount
│  ├─ App.vue                  # Component cha quản lý danh sách sản phẩm
│  ├─ components/
│  │  └─ TheSanPham.js         # Component thẻ sản phẩm viết bằng file JS
│  └─ style.css                # Giao diện responsive cho toàn ứng dụng
└─ tests/
   └─ TheSanPham.spec.js       # Kiểm thử props, trạng thái, sự kiện, tồn kho và thống kê
```

## Đối chiếu yêu cầu

1. **Component cha quản lý ít nhất bốn sản phẩm**: `App.vue` quản lý 6 sản phẩm trong `data().danhSachSanPham`.
2. **Component `<the-san-pham>` nhận đủ prop**: `ten`, `gia`, `anh`, `moTa`, `soLuongTon`.
3. **Khai báo kiểu dữ liệu, có prop bắt buộc và prop mặc định**:
   - `ten` và `gia` là prop bắt buộc (`required: true`).
   - `anh`, `moTa`, `soLuongTon` có `default`.
   - Các prop đều có `type` và `validator` khi phù hợp.
4. **Truyền dữ liệu bằng binding**: `App.vue` dùng `:ten`, `:gia`, `:anh`, `:mo-ta`, `:so-luong-ton`.
5. **Nút "Xem mô tả" bật/tắt mô tả**: trạng thái `dangMoMoTa` nằm trong `data()` của `TheSanPham.js`.
6. **Mở mô tả độc lập**: mỗi thẻ là một instance component riêng, nên `data()` của mỗi thẻ độc lập. Có kiểm thử tự động cho yêu cầu này.
7. **Sản phẩm hết hàng**:
   - Hiển thị nhãn riêng `Hết hàng`.
   - Nút `Mua` bị khóa bằng thuộc tính `disabled` và `aria-disabled`.
8. **Tách component vào `components/TheSanPham.js`**:
   - `main.js` import component.
   - Gọi `ungDung.component('the-san-pham', TheSanPham)` trước `ungDung.mount('#app')`.
9. **Giảm tồn kho sau khi mua thành công**:
   - Khi con phát sự kiện `mua`, cha xác định đúng object sản phẩm trong `danhSachSanPham`.
   - Cha kiểm tra tồn kho là số nguyên dương rồi trừ đúng 1 đơn vị.
   - Prop `soLuongTon` mới được truyền lại xuống thẻ, giao diện tự cập nhật nhãn tồn kho.
   - Khi tồn kho về 0, sản phẩm lập tức có nhãn `Hết hàng` và nút `Mua` bị khóa.
   - Có kiểm tra chặn tuyệt đối trường hợp tồn kho bị trừ thành số âm.

## Ghi chú kỹ thuật

- Component con không sửa trực tiếp props; khi bấm `Mua`, component phát sự kiện `mua` lên cha.
- Cha nhận sự kiện, kiểm tra dữ liệu và giảm `soLuongTon` của đúng sản phẩm, giữ luồng dữ liệu một chiều đúng chuẩn Vue.
- Mỗi thẻ dùng `@mua="xuLyMua(sanPham, $event)"` để cha biết chính xác object sản phẩm cần cập nhật, tránh nhầm lẫn nếu dữ liệu có tên trùng nhau.
- `TheSanPham.js` định nghĩa giao diện bằng chuỗi `template`, nên `vite.config.js` ánh xạ `vue` sang bản `vue/dist/vue.esm-bundler.js` có sẵn trình biên dịch template. Nếu thiếu alias này, bản build trình duyệt sẽ không render được thẻ sản phẩm.
- Ảnh lỗi được thay tự động bằng ảnh SVG dự phòng dạng data URI, tránh vòng lặp lỗi tải ảnh.
- Giao diện có xử lý responsive và tôn trọng cài đặt giảm chuyển động của người dùng.
