<template>
  <!-- Khung bao ngoài giúp căn giữa và giới hạn chiều rộng của toàn bộ trang. -->
  <div class="khung-trang">
    <!-- Phần tiêu đề giới thiệu danh mục sản phẩm. -->
    <header class="tieu-de-trang">
      <!-- Nhãn nhỏ cho biết công nghệ và bài học đang thực hiện. -->
      <p class="nhan-nho">Vue 3 · Bài 2 · Component dùng lại</p>
      <!-- Tiêu đề chính của trang. -->
      <h1>Danh mục sản phẩm</h1>
      <!-- Mô tả ngắn về mục tiêu của bài: truyền props và quản lý trạng thái độc lập. -->
      <p class="mo-ta-trang">
        Mỗi sản phẩm được hiển thị bằng một thẻ <code>&lt;the-san-pham&gt;</code> dùng lại được.
        Dữ liệu đi từ cha xuống con bằng props, còn trạng thái mở mô tả được lưu riêng trong từng thẻ.
        Mỗi lần mua thành công, số lượng tồn của đúng sản phẩm đó sẽ giảm đi 1.
      </p>
    </header>

    <!-- Khu vực thống kê nhanh về danh mục sản phẩm. -->
    <section class="luoi-thong-ke" aria-label="Thống kê danh mục sản phẩm">
      <!-- Thẻ thống kê tổng số sản phẩm trong danh mục. -->
      <article class="the-thong-ke">
        <!-- Nhãn mô tả chỉ số. -->
        <span class="nhan-thong-ke">Tổng sản phẩm</span>
        <!-- Giá trị được tính tự động từ mảng dữ liệu của component cha. -->
        <strong class="gia-tri-thong-ke" data-testid="tong-san-pham">{{ tongSanPham }}</strong>
      </article>

      <!-- Thẻ thống kê số sản phẩm còn hàng. -->
      <article class="the-thong-ke">
        <!-- Nhãn mô tả chỉ số. -->
        <span class="nhan-thong-ke">Còn hàng</span>
        <!-- Giá trị được tính tự động từ danh sách sản phẩm. -->
        <strong class="gia-tri-thong-ke" data-testid="so-san-pham-con-hang">{{ soSanPhamConHang }}</strong>
      </article>

      <!-- Thẻ thống kê số sản phẩm đã hết hàng. -->
      <article class="the-thong-ke">
        <!-- Nhãn mô tả chỉ số. -->
        <span class="nhan-thong-ke">Hết hàng</span>
        <!-- Giá trị được tính tự động từ danh sách sản phẩm. -->
        <strong class="gia-tri-thong-ke" data-testid="so-san-pham-het-hang">{{ soSanPhamHetHang }}</strong>
      </article>
    </section>

    <!-- Thông báo phản hồi khi người dùng bấm nút "Mua" ở một thẻ sản phẩm. -->
    <!-- aria-live giúp trình đọc màn hình đọc thông báo ngay khi nội dung thay đổi. -->
    <p
      v-if="thongBaoMua"
      class="thong-bao-mua"
      role="status"
      aria-live="polite"
      data-testid="thong-bao-mua"
    >
      <!-- Nội dung thông báo được cập nhật bởi phương thức xuLyMua. -->
      {{ thongBaoMua }}
    </p>

    <!-- Khu vực hiển thị danh mục sản phẩm. -->
    <section class="khu-vuc-danh-muc" aria-labelledby="tieu-de-danh-muc">
      <!-- Tiêu đề của khu vực danh mục, được liên kết với section bằng aria-labelledby. -->
      <h2 id="tieu-de-danh-muc" class="tieu-de-khu-vuc">Sản phẩm nổi bật</h2>

      <!-- Lưới hiển thị các thẻ sản phẩm; số cột tự co giãn theo kích thước màn hình. -->
      <div class="luoi-san-pham">
        <!-- Duyệt mảng sản phẩm của cha và tạo một thẻ cho mỗi sản phẩm. -->
        <!-- :key giúp Vue nhận diện đúng từng thẻ khi danh sách thay đổi. -->
        <!-- Sự kiện "mua" gửi kèm object sanPham của vòng lặp để cha trừ đúng tồn kho của sản phẩm vừa mua. -->
        <the-san-pham
          v-for="sanPham in danhSachSanPham"
          :key="sanPham.id"
          :ten="sanPham.ten"
          :gia="sanPham.gia"
          :anh="sanPham.anh"
          :mo-ta="sanPham.moTa"
          :so-luong-ton="sanPham.soLuongTon"
          @mua="xuLyMua(sanPham, $event)"
        />
      </div>
    </section>

    <!-- Ghi chú hướng dẫn kiểm tra các yêu cầu của bài. -->
    <footer class="chan-trang">
      <!-- Nội dung ghi chú ngắn gọn, không ảnh hưởng đến logic ứng dụng. -->
      <p>
        Thử mở mô tả ở nhiều thẻ để thấy trạng thái của mỗi thẻ hoàn toàn độc lập.
        Sau mỗi lần mua thành công, số lượng tồn giảm 1; khi tồn kho về 0, sản phẩm sẽ có nhãn riêng
        và nút <strong>Mua</strong> bị khóa.
      </p>
    </footer>
  </div>
</template>

<script>
/**
 * Component gốc của ứng dụng Bài 2.
 * - Quản lý danh sách sản phẩm (dữ liệu nguồn duy nhất).
 * - Truyền dữ liệu xuống component con <the-san-pham> bằng props.
 * - Nhận sự kiện "mua" từ con, giảm 1 đơn vị tồn kho và hiển thị thông báo phản hồi.
 */
export default {
  // Đặt tên component gốc để dễ nhận diện khi gỡ lỗi bằng Vue Devtools.
  name: 'App',

  // Hàm data trả về trạng thái phản ứng của component cha.
  data() {
    // Trả về object chứa danh sách sản phẩm và thông báo mua hàng.
    return {
      // Danh sách sản phẩm do cha quản lý, gồm 6 sản phẩm (vượt yêu cầu tối thiểu 4 sản phẩm).
      // Mỗi object chứa đúng các trường tương ứng với props của component con.
      danhSachSanPham: [
        {
          // Mã duy nhất dùng cho thuộc tính :key của vòng lặp.
          id: 1,
          // Tên sản phẩm, sẽ truyền vào prop "ten" của thẻ.
          ten: 'Tai nghe Bluetooth',
          // Giá bán kiểu số, sẽ truyền vào prop "gia" của thẻ.
          gia: 890000,
          // Đường dẫn ảnh tương đối, sẽ truyền vào prop "anh" của thẻ.
          anh: './images/tai-nghe.svg',
          // Mô tả chi tiết, sẽ truyền vào prop "moTa" của thẻ.
          moTa:
            'Tai nghe không dây chống ồn chủ động, thời lượng pin lên đến 30 giờ và đệm tai êm ái cho cả ngày dài.',
          // Số lượng tồn kho kiểu số, sẽ truyền vào prop "soLuongTon" của thẻ.
          soLuongTon: 15,
        },
        {
          // Mã duy nhất của sản phẩm thứ hai.
          id: 2,
          // Tên sản phẩm thứ hai.
          ten: 'Bàn phím cơ',
          // Giá bán của sản phẩm thứ hai.
          gia: 1250000,
          // Ảnh minh họa của sản phẩm thứ hai.
          anh: './images/ban-phim.svg',
          // Mô tả chi tiết của sản phẩm thứ hai.
          moTa:
            'Bàn phím cơ khung nhôm, switch êm và đèn nền RGB nhiều chế độ, phù hợp cho cả làm việc lẫn giải trí.',
          // Sản phẩm này cố ý đặt số lượng tồn bằng 0 để kiểm tra nhãn "Hết hàng" và nút Mua bị khóa.
          soLuongTon: 0,
        },
        {
          // Mã duy nhất của sản phẩm thứ ba.
          id: 3,
          // Tên sản phẩm thứ ba.
          ten: 'Màn hình 24 inch',
          // Giá bán của sản phẩm thứ ba.
          gia: 3200000,
          // Ảnh minh họa của sản phẩm thứ ba.
          anh: './images/man-hinh.svg',
          // Mô tả chi tiết của sản phẩm thứ ba.
          moTa:
            'Màn hình viền mỏng độ phân giải Full HD, tần số quét 100 Hz và chế độ bảo vệ mắt trong thời gian dài.',
          // Sản phẩm còn hàng với số lượng vừa phải.
          soLuongTon: 7,
        },
        {
          // Mã duy nhất của sản phẩm thứ tư.
          id: 4,
          // Tên sản phẩm thứ tư.
          ten: 'Nồi chiên không dầu',
          // Giá bán của sản phẩm thứ tư.
          gia: 1790000,
          // Ảnh minh họa của sản phẩm thứ tư.
          anh: './images/noi-chien.svg',
          // Mô tả chi tiết của sản phẩm thứ tư.
          moTa:
            'Dung tích 5 lít, công suất 1500 W, có 8 chế độ nấu cài sẵn và lòng nồi chống dính dễ vệ sinh.',
          // Sản phẩm còn hàng với số lượng nhỏ.
          soLuongTon: 4,
        },
        {
          // Mã duy nhất của sản phẩm thứ năm.
          id: 5,
          // Tên sản phẩm thứ năm.
          ten: 'Máy xay sinh tố',
          // Giá bán của sản phẩm thứ năm.
          gia: 950000,
          // Ảnh minh họa của sản phẩm thứ năm.
          anh: './images/may-xay.svg',
          // Mô tả chi tiết của sản phẩm thứ năm.
          moTa:
            'Cối thủy tinh 1,5 lít, lưỡi dao thép không gỉ và 3 tốc độ xay, phù hợp cho sinh tố, sữa hạt và nước ép.',
          // Sản phẩm thứ hai cố ý hết hàng để kiểm tra trạng thái độc lập giữa nhiều thẻ.
          soLuongTon: 0,
        },
        {
          // Mã duy nhất của sản phẩm thứ sáu.
          id: 6,
          // Tên sản phẩm thứ sáu.
          ten: 'Giày sneaker',
          // Giá bán của sản phẩm thứ sáu.
          gia: 1200000,
          // Ảnh minh họa của sản phẩm thứ sáu.
          anh: './images/giay.svg',
          // Mô tả chi tiết của sản phẩm thứ sáu.
          moTa:
            'Giày sneaker nhẹ, đế cao su bám tốt và thiết kế tối giản dễ phối với nhiều phong cách hằng ngày.',
          // Sản phẩm còn hàng với số lượng lớn.
          soLuongTon: 30,
        },
      ],
      // Chuỗi thông báo phản hồi sau khi nhận sự kiện mua từ component con; rỗng nghĩa là chưa có thông báo.
      thongBaoMua: '',
    };
  },

  // Khu vực các thuộc tính tính toán tự động cập nhật khi danh sách sản phẩm thay đổi.
  computed: {
    /**
     * Tổng số sản phẩm mà component cha đang quản lý.
     * @returns {number} Độ dài của mảng danhSachSanPham.
     */
    tongSanPham() {
      // Đếm toàn bộ phần tử trong mảng sản phẩm.
      return this.danhSachSanPham.length;
    },

    /**
     * Số sản phẩm còn hàng (số lượng tồn lớn hơn 0).
     * @returns {number} Số sản phẩm thỏa điều kiện còn hàng.
     */
    soSanPhamConHang() {
      // Lọc danh sách theo điều kiện số lượng tồn lớn hơn 0 rồi lấy độ dài mảng kết quả.
      return this.danhSachSanPham.filter((sanPham) => sanPham.soLuongTon > 0).length;
    },

    /**
     * Số sản phẩm đã hết hàng (số lượng tồn bằng 0).
     * @returns {number} Số sản phẩm thỏa điều kiện hết hàng.
     */
    soSanPhamHetHang() {
      // Lọc danh sách theo điều kiện số lượng tồn không lớn hơn 0 rồi lấy độ dài mảng kết quả.
      return this.danhSachSanPham.filter((sanPham) => sanPham.soLuongTon <= 0).length;
    },
  },

  // Khu vực các phương thức xử lý sự kiện do component con phát lên.
  methods: {
    /**
     * Xử lý sự kiện "mua" do component <the-san-pham> phát lên.
     * - Chỉ trừ tồn kho khi sản phẩm thực sự còn hàng.
     * - Mỗi lần mua thành công giảm đúng 1 đơn vị tồn kho.
     * - Không bao giờ để số lượng tồn xuống dưới 0.
     * @param {object} sanPham Object sản phẩm hiện tại trong mảng danhSachSanPham.
     * @param {{ ten?: string, gia?: number, soLuongTon?: number }} thongTin Dữ liệu do con gửi lên.
     */
    xuLyMua(sanPham, thongTin) {
      // Kiểm tra object sản phẩm do cha quản lý để tránh truy cập thuộc tính của giá trị không hợp lệ.
      if (!sanPham || typeof sanPham.ten !== 'string' || sanPham.ten.trim().length === 0) {
        // Hiển thị thông báo lỗi an toàn nếu không xác định được sản phẩm cần mua.
        this.thongBaoMua = 'Không nhận được thông tin sản phẩm cần mua.';
        // Dừng xử lý vì không có sản phẩm hợp lệ để cập nhật tồn kho.
        return;
      }

      // Kiểm tra dữ liệu sự kiện do con gửi lên có khớp với sản phẩm đang được xử lý hay không.
      // Bước này giúp phát hiện sự kiện bị gắn nhầm thẻ và tránh trừ tồn kho sai sản phẩm.
      if (!thongTin || thongTin.ten !== sanPham.ten) {
        // Hiển thị thông báo lỗi khi dữ liệu sự kiện không khớp với sản phẩm của thẻ.
        this.thongBaoMua = 'Thông tin mua hàng không khớp với sản phẩm. Vui lòng thử lại.';
        // Dừng xử lý để không thay đổi tồn kho của bất kỳ sản phẩm nào.
        return;
      }

      // Chuyển số lượng tồn hiện tại sang kiểu số để kiểm tra an toàn trước khi trừ.
      const soLuongTonHienTai = Number(sanPham.soLuongTon);

      // Chỉ cho phép mua khi số lượng tồn là số nguyên dương.
      // Điều kiện này cũng chặn trường hợp nút Mua bị kích hoạt bằng mã lệnh dù sản phẩm đã hết hàng.
      if (!Number.isInteger(soLuongTonHienTai) || soLuongTonHienTai <= 0) {
        // Hiển thị thông báo cho biết sản phẩm đã hết hàng và không thể mua thêm.
        this.thongBaoMua = `Sản phẩm "${sanPham.ten}" đã hết hàng nên không thể mua thêm.`;
        // Dừng xử lý để bảo đảm tồn kho không bao giờ bị âm.
        return;
      }

      // Giảm đúng 1 đơn vị tồn kho của sản phẩm vừa mua thành công.
      // Vì sanPham là object phản ứng trong mảng của component cha, Vue sẽ tự cập nhật prop
      // "soLuongTon" ở component con và làm mới nhãn tồn kho trên giao diện.
      sanPham.soLuongTon = soLuongTonHienTai - 1;

      // Kiểm tra sản phẩm vừa mua có phải là sản phẩm cuối cùng trong kho hay không.
      if (sanPham.soLuongTon === 0) {
        // Thông báo trường hợp mua thành công sản phẩm cuối cùng và cho biết sản phẩm đã hết hàng.
        this.thongBaoMua = `Bạn đã mua thành công "${sanPham.ten}". Đây là sản phẩm cuối cùng trong kho.`;
      } else {
        // Thông báo trường hợp mua thành công bình thường kèm số lượng tồn còn lại sau khi trừ.
        this.thongBaoMua = `Bạn đã mua thành công "${sanPham.ten}". Số lượng còn lại: ${sanPham.soLuongTon} sản phẩm.`;
      }
    },
  },
};
</script>
