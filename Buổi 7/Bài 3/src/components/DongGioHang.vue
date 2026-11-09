<template>
  <!-- Thẻ bao ngoài đại diện cho một dòng sản phẩm trong giỏ hàng. -->
  <article class="dong-gio-hang" data-testid="dong-gio-hang">
    <!-- Khối bên trái hiển thị thông tin đọc của sản phẩm. -->
    <div class="dong-gio-hang__thong-tin">
      <!-- Tên sản phẩm được lấy trực tiếp từ prop sanPham, không qua biến trung gian. -->
      <h3 class="dong-gio-hang__ten" data-testid="ten-san-pham">{{ sanPham.ten }}</h3>
      <!-- Hiển thị đơn giá của một sản phẩm, đã được định dạng theo kiểu tiền Việt Nam. -->
      <p class="dong-gio-hang__dong">
        Đơn giá:
        <!-- Gọi phương thức dinhDangTien để biến số tiền thành chuỗi dễ đọc. -->
        <span class="dong-gio-hang__don-gia" data-testid="don-gia">{{ dinhDangTien(sanPham.donGia) }}</span>
      </p>
      <!-- Hiển thị thành tiền của riêng dòng này, tức đơn giá nhân với số lượng. -->
      <p class="dong-gio-hang__dong">
        Thành tiền:
        <!-- Giá trị thanhTien là computed nên tự cập nhật khi prop sanPham thay đổi từ cha. -->
        <strong class="dong-gio-hang__thanh-tien" data-testid="thanh-tien">{{ dinhDangTien(thanhTien) }}</strong>
      </p>
    </div>

    <!-- Khối bên phải chứa các nút điều chỉnh số lượng, ô nhập số lượng và nút xóa. -->
    <!-- role="group" giúp trình đọc màn hình hiểu các nút này thuộc cùng một nhóm chức năng. -->
    <div
      class="dong-gio-hang__dieu-khien"
      role="group"
      :aria-label="`Điều chỉnh số lượng cho ${sanPham.ten}`"
    >
      <!-- Nút giảm số lượng; bị khóa khi số lượng đã chạm mức tối thiểu là 1. -->
      <!-- type="button" ngăn nút tự gửi biểu mẫu nếu component được đặt trong thẻ form. -->
      <button
        type="button"
        class="nut nut--giam"
        data-testid="nut-giam"
        :disabled="!coTheGiam"
        :aria-label="`Giảm số lượng ${sanPham.ten}`"
        @click="giamSoLuong"
      >
        <!-- Ký tự trừ hiển thị trên nút giảm. -->
        −
      </button>

      <!-- Bọc ô nhập trong label để nhãn ẩn vẫn được liên kết với input cho trình đọc màn hình. -->
      <label class="dong-gio-hang__nhap-so-luong">
        <!-- Nhãn chỉ dành cho trình đọc màn hình, không chiếm chỗ trên giao diện. -->
        <span class="chi-doc">Số lượng sản phẩm</span>
        <!-- Ô nhập số lượng cho phép người dùng thay đổi bằng bàn phím. -->
        <!-- :value gắn với bản nháp cục bộ, không ghi trực tiếp vào prop sanPham. -->
        <!-- min, max và step hướng dẫn trình duyệt chỉ nhận số nguyên từ 1 đến 99. -->
        <!-- @input tự động phát sự kiện ngay khi dữ liệu đang nhập hợp lệ. -->
        <!-- @change và phím Enter dùng để báo lỗi hoặc chuẩn hóa khi người dùng kết thúc nhập. -->
        <input
          type="number"
          inputmode="numeric"
          min="1"
          max="99"
          step="1"
          class="dong-gio-hang__o-nhap"
          data-testid="o-nhap-so-luong"
          :value="soLuongNhap"
          :aria-label="`Số lượng của ${sanPham.ten}`"
          :aria-invalid="loiSoLuong ? 'true' : 'false'"
          title="Nhập số nguyên từ 1 đến 99 rồi nhấn Enter"
          @input="capNhatSoLuongNhap"
          @change="xacNhanSoLuongNhap"
          @keydown.enter.prevent="xacNhanSoLuongNhap"
        />
      </label>

      <!-- Nút tăng số lượng; bị khóa khi số lượng đã chạm mức tối đa là 99. -->
      <button
        type="button"
        class="nut nut--tang"
        data-testid="nut-tang"
        :disabled="!coTheTang"
        :aria-label="`Tăng số lượng ${sanPham.ten}`"
        @click="tangSoLuong"
      >
        <!-- Ký tự cộng hiển thị trên nút tăng. -->
        +
      </button>

      <!-- Nút mở bước xác nhận xóa; chưa phát sự kiện xóa ngay để tránh xóa nhầm. -->
      <button
        v-if="!dangXacNhanXoa"
        type="button"
        class="nut nut--xoa"
        data-testid="nut-xoa"
        :aria-label="`Xóa ${sanPham.ten} khỏi giỏ hàng`"
        @click="moXacNhanXoa"
      >
        <!-- Nhãn chữ giúp người dùng hiểu ngay chức năng của nút. -->
        Xóa
      </button>
    </div>

    <!-- Hộp xác nhận chỉ xuất hiện sau khi người dùng bấm nút Xóa. -->
    <!-- Chỉ khi bấm "Xác nhận xóa" component con mới phát sự kiện yeu-cau-xoa lên cha. -->
    <div
      v-if="dangXacNhanXoa"
      class="dong-gio-hang__xac-nhan"
      data-testid="hop-xac-nhan-xoa"
      role="group"
      :aria-label="`Xác nhận xóa ${sanPham.ten} khỏi giỏ hàng`"
    >
      <!-- Câu hỏi xác nhận nêu rõ tên sản phẩm sắp bị xóa để tránh nhầm lẫn. -->
      <p class="dong-gio-hang__xac-nhan-noi-dung">
        Xóa "<strong>{{ sanPham.ten }}</strong>" khỏi giỏ hàng?
      </p>
      <!-- Nhóm hai nút để người dùng chọn xác nhận hoặc hủy thao tác xóa. -->
      <div class="dong-gio-hang__xac-nhan-nut">
        <!-- Nút xác nhận mới thực sự phát sự kiện yêu cầu xóa lên component cha. -->
        <button
          type="button"
          class="nut nut--xac-nhan"
          data-testid="nut-xac-nhan-xoa"
          :aria-label="`Xác nhận xóa ${sanPham.ten}`"
          @click="xacNhanXoa"
        >
          Xác nhận xóa
        </button>
        <!-- Nút hủy đóng hộp xác nhận và giữ nguyên sản phẩm trong giỏ. -->
        <button
          type="button"
          class="nut nut--huy"
          data-testid="nut-huy-xoa"
          :aria-label="`Hủy xóa ${sanPham.ten}`"
          @click="huyXacNhanXoa"
        >
          Hủy
        </button>
      </div>
    </div>

    <!-- Thông báo lỗi khi người dùng nhập số lượng không phải số nguyên từ 1 đến 99. -->
    <!-- role="alert" giúp trình đọc màn hình đọc ngay thông báo lỗi. -->
    <p v-if="loiSoLuong" class="dong-gio-hang__loi" data-testid="loi-so-luong" role="alert">
      <!-- Nội dung lỗi được cập nhật trong phương thức xacNhanSoLuongNhap. -->
      {{ loiSoLuong }}
    </p>

    <!-- Ghi chú hiển thị khi số lượng đang ở mức nhỏ nhất để giải thích vì sao nút giảm bị khóa. -->
    <p v-if="!coTheGiam" class="dong-gio-hang__ghi-chu" data-testid="ghi-chu-toi-thieu">
      Số lượng tối thiểu là 1.
    </p>
    <!-- Ghi chú hiển thị khi số lượng đang ở mức lớn nhất để giải thích vì sao nút tăng bị khóa. -->
    <p v-else-if="!coTheTang" class="dong-gio-hang__ghi-chu" data-testid="ghi-chu-toi-da">
      Số lượng tối đa là 99.
    </p>
  </article>
</template>

<script>
// Nhập hàm định dạng tiền dùng chung để hiển thị đơn giá và thành tiền thống nhất với component cha.
import { dinhDangTien } from '../utils/dinhDangTien.js';

// Khai báo hằng số số lượng nhỏ nhất mà một dòng sản phẩm được phép có.
const SO_LUONG_NHO_NHAT = 1;
// Khai báo hằng số số lượng lớn nhất mà một dòng sản phẩm được phép có.
const SO_LUONG_LON_NHAT = 99;

/**
 * Kiểm tra một mã sản phẩm có hợp lệ để hiển thị hoặc gửi lên cha hay không.
 * Mã hợp lệ là số hữu hạn hoặc chuỗi không rỗng; nhờ đó component dùng được cả id số lẫn id chuỗi.
 *
 * @param {number|string} id Mã sản phẩm cần kiểm tra.
 * @returns {boolean} true nếu mã hợp lệ, ngược lại là false.
 */
function laIdHopLe(id) {
  // Nếu id là kiểu số thì chấp nhận, miễn là không phải NaN hay vô cực.
  if (typeof id === 'number') {
    // Number.isFinite trả về false với NaN và Infinity nên hai giá trị này bị loại.
    return Number.isFinite(id);
  }
  // Nếu id là kiểu chuỗi thì chỉ chấp nhận khi sau khi bỏ khoảng trắng vẫn còn nội dung.
  return typeof id === 'string' && id.trim() !== '';
}

/**
 * Kiểm tra số lượng có đúng quy tắc của bài hay không.
 * Quy tắc: phải là số nguyên từ 1 đến 99.
 *
 * @param {unknown} giaTri Giá trị cần kiểm tra.
 * @returns {boolean} true nếu hợp lệ, ngược lại là false.
 */
function laSoLuongHopLe(giaTri) {
  // Bắt buộc phải là kiểu number để loại bỏ chuỗi, null, undefined và các kiểu dữ liệu khác.
  if (typeof giaTri !== 'number') {
    // Trả về false ngay khi kiểu dữ liệu không phải số.
    return false;
  }
  // Number.isInteger loại bỏ số thập phân, NaN và Infinity; sau đó kiểm tra phạm vi 1 đến 99.
  return Number.isInteger(giaTri) && giaTri >= SO_LUONG_NHO_NHAT && giaTri <= SO_LUONG_LON_NHAT;
}

/**
 * Component con hiển thị một dòng sản phẩm trong giỏ hàng.
 * Component chỉ đọc dữ liệu từ prop sanPham, tuyệt đối không gán lại prop
 * và không sửa bất kỳ thuộc tính nào của đối tượng nhận qua props.
 * Mọi thay đổi đều được gửi lên component cha thông qua sự kiện.
 */
export default {
  // Đặt tên component để dễ nhận diện khi gỡ lỗi bằng Vue Devtools.
  name: 'DongGioHang',

  // Khai báo dữ liệu đầu vào mà component con nhận từ component cha.
  props: {
    // Prop sanPham là một object chứa đầy đủ id, tên, đơn giá và số lượng của dòng hàng.
    sanPham: {
      // Bắt buộc kiểu Object vì cả bốn trường dữ liệu được truyền trong cùng một đối tượng.
      type: Object,
      // Bắt buộc component cha phải truyền prop này, nếu thiếu Vue sẽ cảnh báo.
      required: true,
      /**
       * Kiểm tra cấu trúc và giá trị của đối tượng sản phẩm ngay khi nhận prop.
       * Việc kiểm tra sớm giúp phát hiện dữ liệu sai trước khi giao diện hiển thị.
       *
       * @param {object} doiTuong Đối tượng sản phẩm do cha truyền xuống.
       * @returns {boolean} true nếu đối tượng có đủ dữ liệu hợp lệ.
       */
      validator(doiTuong) {
        // Nếu giá trị không phải object hoặc bị null thì dữ liệu không hợp lệ.
        if (!doiTuong || typeof doiTuong !== 'object') {
          // Trả về false để Vue ghi nhận cảnh báo về prop sai cấu trúc.
          return false;
        }
        // Kiểm tra mã sản phẩm bằng hàm dùng chung phía trên.
        const idHopLe = laIdHopLe(doiTuong.id);
        // Tên phải là chuỗi có nội dung sau khi bỏ khoảng trắng ở hai đầu.
        const tenHopLe = typeof doiTuong.ten === 'string' && doiTuong.ten.trim() !== '';
        // Đơn giá phải là số hữu hạn và không được âm.
        const donGiaHopLe =
          typeof doiTuong.donGia === 'number' &&
          Number.isFinite(doiTuong.donGia) &&
          doiTuong.donGia >= 0;
        // Số lượng phải là số nguyên trong khoảng 1 đến 99 theo đúng yêu cầu của bài.
        const soLuongHopLe = laSoLuongHopLe(doiTuong.soLuong);
        // Chỉ hợp lệ khi cả bốn trường đều đạt điều kiện.
        return idHopLe && tenHopLe && donGiaHopLe && soLuongHopLe;
      },
    },
  },

  // Khai báo tường minh các sự kiện mà component con được phép phát lên cha.
  emits: {
    /**
     * Sự kiện yêu cầu đổi số lượng của một dòng hàng.
     *
     * @param {number|string} id Mã sản phẩm cần đổi số lượng.
     * @param {number} soLuongMoi Số lượng mới mong muốn, phải là số nguyên từ 1 đến 99.
     * @returns {boolean} true nếu dữ liệu gửi đi đúng định dạng.
     */
    'doi-so-luong': (id, soLuongMoi) => {
      // Sự kiện chỉ hợp lệ khi mã sản phẩm hợp lệ và số lượng mới nằm trong phạm vi cho phép.
      return laIdHopLe(id) && laSoLuongHopLe(soLuongMoi);
    },

    /**
     * Sự kiện yêu cầu xóa một dòng hàng khỏi giỏ.
     * Sự kiện chỉ được phát sau khi người dùng xác nhận trong hộp xác nhận xóa.
     *
     * @param {number|string} id Mã sản phẩm cần xóa.
     * @returns {boolean} true nếu mã sản phẩm đúng định dạng.
     */
    'yeu-cau-xoa': (id) => {
      // Sự kiện chỉ hợp lệ khi mã sản phẩm không rỗng và đúng kiểu dữ liệu.
      return laIdHopLe(id);
    },
  },

  /**
   * Khởi tạo trạng thái cục bộ của component con.
   * Các trạng thái này chỉ phục vụ giao diện, không sửa đổi prop nhận từ cha.
   *
   * @returns {object} Dữ liệu cục bộ ban đầu.
   */
  data() {
    // Trả về bản nháp số lượng, thông báo lỗi và trạng thái hộp xác nhận xóa.
    return {
      // Bản nháp số lượng được tách khỏi prop để người dùng có thể gõ mà không sửa trực tiếp prop.
      soLuongNhap: String(this.sanPham.soLuong),
      // Chuỗi lỗi hiển thị khi giá trị nhập không hợp lệ; rỗng nghĩa là không có lỗi.
      loiSoLuong: '',
      // Cờ cho biết hộp xác nhận xóa đang mở hay đóng.
      dangXacNhanXoa: false,
    };
  },

  // Theo dõi thay đổi của số lượng trong prop để đồng bộ lại ô nhập.
  watch: {
    /**
     * Khi component cha cập nhật số lượng, ô nhập phải hiển thị giá trị mới.
     * Việc đồng bộ này giúp giao diện luôn khớp với dữ liệu nguồn duy nhất ở cha.
     *
     * @param {number} soLuongMoi Số lượng mới sau khi cha cập nhật.
     */
    'sanPham.soLuong'(soLuongMoi) {
      // Chuyển số lượng mới thành chuỗi để gán vào ô nhập.
      this.soLuongNhap = String(soLuongMoi);
      // Xóa thông báo lỗi cũ vì dữ liệu đã được cha chấp nhận và cập nhật thành công.
      this.loiSoLuong = '';
    },
  },

  // Các giá trị tính toán tự động từ prop sanPham.
  computed: {
    /**
     * Thành tiền của riêng dòng sản phẩm này.
     * Công thức: đơn giá nhân với số lượng.
     *
     * @returns {number} Số tiền của cả dòng hàng.
     */
    thanhTien() {
      // Nhân đơn giá với số lượng; cả hai đều là dữ liệu chỉ đọc lấy từ prop.
      return this.sanPham.donGia * this.sanPham.soLuong;
    },

    /**
     * Cho biết nút giảm có được phép bấm hay không.
     * Chỉ được giảm khi số lượng hiện tại lớn hơn mức nhỏ nhất.
     *
     * @returns {boolean} true nếu còn có thể giảm số lượng.
     */
    coTheGiam() {
      // So sánh số lượng hiện tại với hằng số nhỏ nhất là 1.
      return this.sanPham.soLuong > SO_LUONG_NHO_NHAT;
    },

    /**
     * Cho biết nút tăng có được phép bấm hay không.
     * Chỉ được tăng khi số lượng hiện tại nhỏ hơn mức lớn nhất.
     *
     * @returns {boolean} true nếu còn có thể tăng số lượng.
     */
    coTheTang() {
      // So sánh số lượng hiện tại với hằng số lớn nhất là 99.
      return this.sanPham.soLuong < SO_LUONG_LON_NHAT;
    },
  },

  // Các phương thức xử lý tương tác của người dùng.
  methods: {
    // Đưa hàm định dạng tiền vào methods để template có thể gọi trực tiếp.
    dinhDangTien,

    /**
     * Cập nhật bản nháp số lượng mỗi khi người dùng gõ phím.
     * Nếu chuỗi vừa gõ đã là số nguyên hợp lệ từ 1 đến 99 thì phát sự kiện ngay
     * để thông tin trong giỏ tự động cập nhật theo dữ liệu đang nhập.
     * Nếu chưa hợp lệ, phương thức chỉ lưu bản nháp và chờ người dùng hoàn tất.
     *
     * @param {InputEvent} suKien Sự kiện input do ô nhập phát ra.
     */
    capNhatSoLuongNhap(suKien) {
      // Lấy chuỗi hiện tại trong ô nhập và lưu vào bản nháp cục bộ.
      this.soLuongNhap = suKien.target.value;
      // Xóa thông báo lỗi cũ để người dùng thấy mình đang sửa lại giá trị.
      this.loiSoLuong = '';
      // Chuyển bản nháp thành chuỗi và bỏ khoảng trắng thừa ở hai đầu để kiểm tra.
      const chuoiNhap = String(this.soLuongNhap).trim();
      // Chuỗi rỗng thường xuất hiện khi người dùng đang xóa dở, nên chưa báo lỗi và chưa phát sự kiện.
      if (chuoiNhap === '') {
        // Dừng xử lý để chờ người dùng gõ tiếp một giá trị hoàn chỉnh.
        return;
      }
      // Chuyển chuỗi người dùng nhập thành kiểu số để kiểm tra quy tắc.
      const soLuongMoi = Number(chuoiNhap);
      // Chỉ tự động cập nhật khi giá trị hiện tại đã là số nguyên từ 1 đến 99.
      if (!laSoLuongHopLe(soLuongMoi)) {
        // Dừng xử lý, không phát sự kiện với dữ liệu chưa hợp lệ.
        return;
      }
      // Nếu giá trị nhập trùng với số lượng hiện tại thì không cần phát sự kiện.
      if (soLuongMoi === this.sanPham.soLuong) {
        // Dừng xử lý vì dữ liệu không thay đổi.
        return;
      }
      // Phát sự kiện ngay khi dữ liệu hợp lệ để cha cập nhật mảng và giao diện tức thời.
      this.$emit('doi-so-luong', this.sanPham.id, soLuongMoi);
    },

    /**
     * Xác nhận và chuẩn hóa giá trị người dùng vừa nhập khi kết thúc chỉnh sửa.
     * Được gọi khi ô nhập mất tiêu điểm (change) hoặc khi người dùng nhấn Enter.
     * Sự kiện doi-so-luong đã được phát ngay trong lúc gõ nếu dữ liệu hợp lệ,
     * nên phương thức này chỉ báo lỗi, chuẩn hóa và không phát trùng sự kiện.
     */
    xacNhanSoLuongNhap() {
      // Chuyển bản nháp thành chuỗi và bỏ khoảng trắng thừa ở hai đầu.
      const chuoiNhap = String(this.soLuongNhap).trim();
      // Kiểm tra riêng chuỗi rỗng vì Number('') sẽ trả về 0, gây thông báo khó hiểu.
      if (chuoiNhap === '') {
        // Ghi thông báo lỗi rõ ràng cho trường hợp bỏ trống ô nhập.
        this.loiSoLuong = 'Vui lòng nhập số lượng từ 1 đến 99.';
        // Đưa ô nhập về giá trị thật đang được cha quản lý để tránh hiển thị sai.
        this.soLuongNhap = String(this.sanPham.soLuong);
        // Dừng xử lý vì không có dữ liệu hợp lệ để cập nhật.
        return;
      }
      // Chuyển chuỗi người dùng nhập thành kiểu số để kiểm tra quy tắc.
      const soLuongMoi = Number(chuoiNhap);
      // Kiểm tra số vừa nhập có phải số nguyên từ 1 đến 99 hay không.
      if (!laSoLuongHopLe(soLuongMoi)) {
        // Ghi thông báo lỗi đúng quy tắc của bài.
        this.loiSoLuong = 'Số lượng phải là số nguyên từ 1 đến 99.';
        // Đưa ô nhập về giá trị thật đang được cha quản lý để tránh trạng thái không nhất quán.
        this.soLuongNhap = String(this.sanPham.soLuong);
        // Dừng xử lý vì dữ liệu sai không được cập nhật.
        return;
      }
      // Xóa thông báo lỗi cũ vì giá trị hiện tại đã hợp lệ.
      this.loiSoLuong = '';
      // Chuẩn hóa ô nhập về dạng số gọn nhất, loại bỏ các số 0 vô nghĩa ở đầu.
      this.soLuongNhap = String(soLuongMoi);
    },

    /**
     * Xử lý khi người dùng bấm nút giảm số lượng.
     * Phương thức chỉ tính toán số lượng mới rồi phát sự kiện, không sửa prop.
     */
    giamSoLuong() {
      // Tính số lượng mới bằng cách trừ đi một từ giá trị hiện tại.
      const soLuongMoi = this.sanPham.soLuong - 1;
      // Kiểm tra lại phạm vi để bảo vệ cả khi nút bị kích hoạt bằng cách khác ngoài giao diện.
      if (!laSoLuongHopLe(soLuongMoi)) {
        // Dừng xử lý nếu số lượng mới rơi ra ngoài khoảng 1 đến 99.
        return;
      }
      // Phát sự kiện kèm mã sản phẩm và số lượng mới để component cha cập nhật mảng dữ liệu.
      this.$emit('doi-so-luong', this.sanPham.id, soLuongMoi);
    },

    /**
     * Xử lý khi người dùng bấm nút tăng số lượng.
     * Phương thức chỉ tính toán số lượng mới rồi phát sự kiện, không sửa prop.
     */
    tangSoLuong() {
      // Tính số lượng mới bằng cách cộng thêm một vào giá trị hiện tại.
      const soLuongMoi = this.sanPham.soLuong + 1;
      // Kiểm tra lại phạm vi để bảo vệ cả khi nút bị kích hoạt bằng cách khác ngoài giao diện.
      if (!laSoLuongHopLe(soLuongMoi)) {
        // Dừng xử lý nếu số lượng mới rơi ra ngoài khoảng 1 đến 99.
        return;
      }
      // Phát sự kiện kèm mã sản phẩm và số lượng mới để component cha cập nhật mảng dữ liệu.
      this.$emit('doi-so-luong', this.sanPham.id, soLuongMoi);
    },

    /**
     * Mở hộp xác nhận xóa khi người dùng bấm nút Xóa lần đầu.
     * Phương thức chưa phát sự kiện xóa để đảm bảo luôn có bước xác nhận.
     */
    moXacNhanXoa() {
      // Bật cờ để template hiển thị hộp xác nhận thay cho nút Xóa.
      this.dangXacNhanXoa = true;
    },

    /**
     * Hủy thao tác xóa khi người dùng bấm nút Hủy.
     * Sản phẩm được giữ nguyên và không có sự kiện nào được phát lên cha.
     */
    huyXacNhanXoa() {
      // Tắt cờ để đóng hộp xác nhận và hiện lại nút Xóa.
      this.dangXacNhanXoa = false;
    },

    /**
     * Xác nhận xóa dòng sản phẩm sau khi người dùng đồng ý.
     * Chỉ tại thời điểm này component con mới phát sự kiện yeu-cau-xoa lên cha.
     */
    xacNhanXoa() {
      // Đóng hộp xác nhận trước khi gửi yêu cầu để giao diện trở về trạng thái ban đầu.
      this.dangXacNhanXoa = false;
      // Phát sự kiện kèm mã sản phẩm cần xóa; cha sẽ kiểm tra id rồi quyết định xóa.
      this.$emit('yeu-cau-xoa', this.sanPham.id);
    },
  },
};
</script>
