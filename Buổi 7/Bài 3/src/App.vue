<template>
  <!-- Khung bao ngoài giúp căn giữa và giới hạn chiều rộng của toàn bộ trang. -->
  <div class="khung-trang">
    <!-- Phần tiêu đề giới thiệu bài học và mục tiêu của giỏ hàng. -->
    <header class="tieu-de-trang">
      <!-- Nhãn nhỏ cho biết công nghệ và nội dung đang thực hiện. -->
      <p class="nhan-nho">Vue 3 · Bài 3 · Giỏ hàng</p>
      <!-- Tiêu đề chính của trang. -->
      <h1>Giỏ hàng của bạn</h1>
      <!-- Mô tả ngắn về kiến trúc cha - con và luồng dữ liệu một chiều. -->
      <p class="mo-ta-trang">
        Component cha giữ toàn bộ dữ liệu giỏ hàng. Mỗi dòng sản phẩm là một component con
        <code>&lt;dong-gio-hang&gt;</code> nhận dữ liệu bằng props và gửi yêu cầu thay đổi lên cha bằng sự kiện.
      </p>
    </header>

    <!-- Khu vực tổng quan hiển thị các chỉ số được tính tự động bằng computed của component cha. -->
    <section class="luoi-tong-quan" aria-label="Tổng quan giỏ hàng">
      <!-- Thẻ hiển thị tổng số lượng sản phẩm trong giỏ. -->
      <article class="the-tong-quan">
        <!-- Nhãn mô tả chỉ số. -->
        <span class="nhan-tong-quan">Tổng số lượng</span>
        <!-- Giá trị lấy từ computed tongSoLuong nên tự cập nhật khi mảng dữ liệu thay đổi. -->
        <strong class="gia-tri-tong-quan" data-testid="tong-so-luong" aria-live="polite">
          {{ tongSoLuong }}
        </strong>
      </article>

      <!-- Thẻ hiển thị tổng tiền của toàn bộ giỏ hàng. -->
      <article class="the-tong-quan">
        <!-- Nhãn mô tả chỉ số. -->
        <span class="nhan-tong-quan">Tổng tiền</span>
        <!-- Giá trị lấy từ computed tongTien và được định dạng theo kiểu tiền Việt Nam. -->
        <strong class="gia-tri-tong-quan" data-testid="tong-tien" aria-live="polite">
          {{ dinhDangTien(tongTien) }}
        </strong>
      </article>

      <!-- Thẻ hiển thị số dòng sản phẩm khác nhau đang có trong giỏ. -->
      <article class="the-tong-quan">
        <!-- Nhãn mô tả chỉ số. -->
        <span class="nhan-tong-quan">Số dòng sản phẩm</span>
        <!-- Độ dài mảng chính là số dòng hàng hiện có trong giỏ. -->
        <strong class="gia-tri-tong-quan" data-testid="so-dong-san-pham">
          {{ danhSachGioHang.length }}
        </strong>
      </article>
    </section>

    <!-- Thông báo phản hồi sau khi cha xử lý sự kiện từ con. -->
    <!-- aria-live giúp trình đọc màn hình đọc thông báo ngay khi nội dung thay đổi. -->
    <p
      v-if="thongBao"
      class="thong-bao"
      role="status"
      aria-live="polite"
      data-testid="thong-bao"
    >
      <!-- Nội dung thông báo được cập nhật trong các phương thức xử lý sự kiện của cha. -->
      {{ thongBao }}
    </p>

    <!-- Khu vực hiển thị danh sách sản phẩm trong giỏ hàng. -->
    <section class="khu-vuc-gio-hang" aria-labelledby="tieu-de-gio-hang">
      <!-- Tiêu đề của khu vực giỏ hàng, liên kết với section bằng aria-labelledby. -->
      <h2 id="tieu-de-gio-hang" class="tieu-de-khu-vuc">Sản phẩm trong giỏ</h2>

      <!-- Chỉ hiển thị danh sách khi giỏ hàng còn ít nhất một sản phẩm. -->
      <div v-if="!gioHangTrong" class="danh-sach-gio-hang" data-testid="danh-sach-gio-hang">
        <!-- Duyệt mảng dữ liệu của cha và tạo một component con cho mỗi dòng sản phẩm. -->
        <!-- :key giúp Vue nhận diện đúng từng dòng khi mảng thay đổi. -->
        <!-- :san-pham truyền nguyên đối tượng sản phẩm xuống con thông qua props. -->
        <!-- Lắng nghe hai sự kiện do con phát lên và chuyển cho phương thức xử lý của cha. -->
        <dong-gio-hang
          v-for="sanPham in danhSachGioHang"
          :key="sanPham.id"
          :san-pham="sanPham"
          @doi-so-luong="xuLyDoiSoLuong"
          @yeu-cau-xoa="xuLyYeuCauXoa"
        />
      </div>

      <!-- Khi mảng rỗng, hiển thị đúng thông báo "Giỏ hàng trống" theo yêu cầu của bài. -->
      <p
        v-else
        class="gio-hang-trong"
        data-testid="gio-hang-trong"
        role="status"
        aria-live="polite"
      >
        <!-- Nội dung thông báo bắt buộc khi không còn sản phẩm nào trong giỏ. -->
        Giỏ hàng trống
      </p>
    </section>

    <!-- Ghi chú hướng dẫn kiểm tra các yêu cầu của bài. -->
    <footer class="chan-trang">
      <!-- Nội dung ghi chú ngắn gọn, không ảnh hưởng đến logic ứng dụng. -->
      <p>
        Số lượng mỗi sản phẩm chỉ nhận giá trị nguyên từ 1 đến 99.
        Nút tăng và nút giảm sẽ tự khóa khi chạm giới hạn tương ứng.
      </p>
    </footer>
  </div>
</template>

<script>
// Nhập component con hiển thị từng dòng sản phẩm trong giỏ hàng.
import DongGioHang from './components/DongGioHang.vue';
// Nhập hàm định dạng tiền dùng chung để hiển thị tổng tiền thống nhất với component con.
import { dinhDangTien } from './utils/dinhDangTien.js';

// Khai báo hằng số số lượng nhỏ nhất mà một sản phẩm được phép có.
const SO_LUONG_NHO_NHAT = 1;
// Khai báo hằng số số lượng lớn nhất mà một sản phẩm được phép có.
const SO_LUONG_LON_NHAT = 99;

/**
 * Component cha của ứng dụng Bài 3.
 * - Giữ toàn bộ dữ liệu giỏ hàng trong data.
 * - Truyền từng sản phẩm xuống component con <dong-gio-hang> bằng props.
 * - Nhận sự kiện doi-so-luong và yeu-cau-xoa từ con, kiểm tra dữ liệu rồi mới cập nhật mảng.
 * - Dùng computed để tính tổng số lượng và tổng tiền.
 */
export default {
  // Đặt tên component gốc để dễ nhận diện khi gỡ lỗi bằng Vue Devtools.
  name: 'App',

  // Đăng ký component con để template của cha sử dụng được thẻ <dong-gio-hang>.
  components: {
    // Tên thuộc tính viết PascalCase sẽ được dùng dưới dạng thẻ kebab-case trong template.
    DongGioHang,
  },

  /**
   * Hàm data trả về trạng thái phản ứng của component cha.
   * Mảng danhSachGioHang là nguồn dữ liệu duy nhất cho toàn bộ giỏ hàng.
   *
   * @returns {object} Trạng thái ban đầu của ứng dụng.
   */
  data() {
    // Trả về object chứa dữ liệu giỏ hàng và thông báo phản hồi.
    return {
      // Mảng sản phẩm mẫu đúng với kết quả kiểm tra của đề bài:
      // 2 bút bi giá 5.000 đồng và 3 quyển vở giá 12.000 đồng.
      danhSachGioHang: [
        {
          // Mã duy nhất dùng cho thuộc tính :key và để cha tìm đúng sản phẩm khi nhận sự kiện.
          id: 1,
          // Tên sản phẩm hiển thị trên giao diện.
          ten: 'Bút bi',
          // Đơn giá của một sản phẩm, đơn vị là đồng.
          donGia: 5000,
          // Số lượng ban đầu là 2, là số nguyên nằm trong khoảng 1 đến 99.
          soLuong: 2,
        },
        {
          // Mã duy nhất của sản phẩm thứ hai.
          id: 2,
          // Tên sản phẩm thứ hai.
          ten: 'Quyển vở',
          // Đơn giá của một quyển vở, đơn vị là đồng.
          donGia: 12000,
          // Số lượng ban đầu là 3, là số nguyên nằm trong khoảng 1 đến 99.
          soLuong: 3,
        },
      ],
      // Chuỗi thông báo phản hồi sau khi xử lý sự kiện; rỗng nghĩa là chưa có thông báo.
      thongBao: '',
    };
  },

  // Khu vực các thuộc tính tính toán tự động cập nhật khi mảng dữ liệu thay đổi.
  computed: {
    /**
     * Tổng số lượng sản phẩm trong giỏ.
     * Mỗi phần tử góp số lượng của nó vào tổng chung.
     *
     * @returns {number} Tổng số lượng của tất cả sản phẩm.
     */
    tongSoLuong() {
      // Duyệt mảng và cộng dồn trường soLuong của từng sản phẩm, bắt đầu từ 0.
      return this.danhSachGioHang.reduce((tong, sanPham) => tong + sanPham.soLuong, 0);
    },

    /**
     * Tổng tiền của toàn bộ giỏ hàng.
     * Mỗi phần tử góp đơn giá nhân số lượng của nó vào tổng chung.
     *
     * @returns {number} Tổng số tiền phải thanh toán.
     */
    tongTien() {
      // Duyệt mảng và cộng dồn thành tiền từng dòng, bắt đầu từ 0.
      return this.danhSachGioHang.reduce(
        (tong, sanPham) => tong + sanPham.donGia * sanPham.soLuong,
        0,
      );
    },

    /**
     * Cho biết giỏ hàng có đang trống hay không.
     * Được dùng để quyết định hiển thị danh sách hay thông báo "Giỏ hàng trống".
     *
     * @returns {boolean} true nếu mảng không còn phần tử nào.
     */
    gioHangTrong() {
      // Mảng rỗng có độ dài bằng 0, tương ứng với trạng thái giỏ hàng trống.
      return this.danhSachGioHang.length === 0;
    },
  },

  // Các phương thức xử lý sự kiện và kiểm tra dữ liệu của component cha.
  methods: {
    // Đưa hàm định dạng tiền vào methods để template có thể gọi trực tiếp.
    dinhDangTien,

    /**
     * Kiểm tra một mã sản phẩm có hợp lệ để tìm trong mảng hay không.
     * Mã hợp lệ là số hữu hạn hoặc chuỗi không rỗng.
     *
     * @param {unknown} id Mã sản phẩm cần kiểm tra.
     * @returns {boolean} true nếu mã hợp lệ.
     */
    laIdHopLe(id) {
      // Nếu id là kiểu số thì chấp nhận, miễn là không phải NaN hay vô cực.
      if (typeof id === 'number') {
        // Number.isFinite loại bỏ NaN, Infinity và -Infinity.
        return Number.isFinite(id);
      }
      // Nếu id là kiểu chuỗi thì chỉ chấp nhận chuỗi có nội dung sau khi bỏ khoảng trắng.
      return typeof id === 'string' && id.trim() !== '';
    },

    /**
     * Kiểm tra số lượng mới do component con gửi lên có hợp lệ hay không.
     * Quy tắc: phải là số nguyên từ 1 đến 99.
     *
     * @param {unknown} giaTri Giá trị cần kiểm tra.
     * @returns {boolean} true nếu hợp lệ.
     */
    laSoLuongHopLe(giaTri) {
      // Bắt buộc phải là kiểu number để loại bỏ chuỗi, null, undefined và các kiểu dữ liệu khác.
      if (typeof giaTri !== 'number') {
        // Trả về false ngay khi kiểu dữ liệu không phải số.
        return false;
      }
      // Number.isInteger loại bỏ số thập phân, NaN và Infinity; sau đó kiểm tra phạm vi 1 đến 99.
      return Number.isInteger(giaTri) && giaTri >= SO_LUONG_NHO_NHAT && giaTri <= SO_LUONG_LON_NHAT;
    },

    /**
     * Tìm vị trí của sản phẩm trong mảng theo mã định danh.
     *
     * @param {number|string} id Mã sản phẩm cần tìm.
     * @returns {number} Vị trí tìm thấy, hoặc -1 nếu không tồn tại.
     */
    timViTriTheoId(id) {
      // Dùng strict equality để tránh so sánh nhầm giữa id số và id chuỗi khác kiểu.
      return this.danhSachGioHang.findIndex((sanPham) => sanPham.id === id);
    },

    /**
     * Xử lý sự kiện doi-so-luong do component con phát lên.
     * Cha kiểm tra mã sản phẩm và số lượng mới trước khi cập nhật mảng dữ liệu.
     *
     * @param {number|string} id Mã sản phẩm cần đổi số lượng.
     * @param {number} soLuongMoi Số lượng mới do con gửi lên.
     */
    xuLyDoiSoLuong(id, soLuongMoi) {
      // Kiểm tra mã sản phẩm trước để tránh tìm kiếm với dữ liệu không hợp lệ.
      if (!this.laIdHopLe(id)) {
        // Ghi thông báo lỗi để người dùng biết yêu cầu không được chấp nhận.
        this.thongBao = 'Mã sản phẩm không hợp lệ nên không thể cập nhật số lượng.';
        // Dừng xử lý, không thay đổi mảng dữ liệu.
        return;
      }
      // Kiểm tra số lượng mới có phải số nguyên từ 1 đến 99 hay không.
      if (!this.laSoLuongHopLe(soLuongMoi)) {
        // Ghi thông báo lỗi rõ ràng về quy tắc số lượng của bài.
        this.thongBao = 'Số lượng không hợp lệ: chỉ nhận số nguyên từ 1 đến 99.';
        // Dừng xử lý, giữ nguyên dữ liệu cũ.
        return;
      }
      // Tìm vị trí của sản phẩm trong mảng theo mã id.
      const viTri = this.timViTriTheoId(id);
      // Nếu không tìm thấy sản phẩm thì không được cập nhật dữ liệu.
      if (viTri === -1) {
        // Thông báo cho người dùng biết mã sản phẩm không tồn tại trong giỏ.
        this.thongBao = 'Không tìm thấy sản phẩm cần cập nhật trong giỏ hàng.';
        // Dừng xử lý để tránh sửa nhầm phần tử khác.
        return;
      }
      // Cập nhật số lượng của đúng sản phẩm; Vue sẽ tự động lan truyền thay đổi xuống component con.
      this.danhSachGioHang[viTri].soLuong = soLuongMoi;
      // Lấy tên sản phẩm vừa cập nhật để tạo thông báo dễ hiểu.
      const tenSanPham = this.danhSachGioHang[viTri].ten;
      // Cập nhật thông báo thành công cho người dùng.
      this.thongBao = `Đã cập nhật "${tenSanPham}" thành ${soLuongMoi} sản phẩm.`;
    },

    /**
     * Xử lý sự kiện yeu-cau-xoa do component con phát lên.
     * Cha kiểm tra mã sản phẩm rồi mới xóa phần tử khỏi mảng.
     *
     * @param {number|string} id Mã sản phẩm cần xóa.
     */
    xuLyYeuCauXoa(id) {
      // Kiểm tra mã sản phẩm trước để tránh thao tác xóa với dữ liệu không hợp lệ.
      if (!this.laIdHopLe(id)) {
        // Ghi thông báo lỗi để người dùng biết yêu cầu không được chấp nhận.
        this.thongBao = 'Mã sản phẩm không hợp lệ nên không thể xóa.';
        // Dừng xử lý, không thay đổi mảng dữ liệu.
        return;
      }
      // Tìm vị trí của sản phẩm cần xóa trong mảng.
      const viTri = this.timViTriTheoId(id);
      // Nếu không tìm thấy sản phẩm thì không được xóa phần tử nào.
      if (viTri === -1) {
        // Thông báo cho người dùng biết mã sản phẩm không tồn tại trong giỏ.
        this.thongBao = 'Không tìm thấy sản phẩm cần xóa trong giỏ hàng.';
        // Dừng xử lý để tránh xóa nhầm phần tử khác.
        return;
      }
      // Lưu tên sản phẩm trước khi xóa để dùng cho thông báo phía sau.
      const tenSanPham = this.danhSachGioHang[viTri].ten;
      // Xóa đúng một phần tử tại vị trí tìm được khỏi mảng dữ liệu.
      this.danhSachGioHang.splice(viTri, 1);
      // Cập nhật thông báo xác nhận sản phẩm đã được xóa.
      this.thongBao = `Đã xóa "${tenSanPham}" khỏi giỏ hàng.`;
    },
  },
};
</script>
