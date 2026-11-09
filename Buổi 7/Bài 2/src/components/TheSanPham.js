/**
 * Component thẻ sản phẩm dùng lại được trong ứng dụng Vue 3.
 *
 * Các đặc điểm chính:
 * - Nhận toàn bộ dữ liệu hiển thị từ component cha thông qua props.
 * - Mỗi thẻ tự quản lý trạng thái đóng/mở mô tả trong data() riêng.
 * - Sản phẩm hết hàng có nhãn riêng và nút "Mua" bị khóa.
 * - Không sửa trực tiếp props để tránh cảnh báo và bảo đảm luồng dữ liệu một chiều.
 */

// Biến đếm nằm ngoài component, dùng để sinh id duy nhất cho vùng mô tả của từng thẻ.
// Nhờ vậy nhiều thẻ cùng tên vẫn có id khác nhau, tránh trùng id trong DOM.
let boDemTheSanPham = 0;

// Ảnh SVG dự phòng được nhúng trực tiếp dưới dạng data URI.
// Ảnh này luôn sẵn sàng, không phụ thuộc mạng nên tránh được vòng lặp lỗi khi ảnh gốc không tải được.
const ANH_DU_PHONG = `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(
  // Bắt đầu nội dung SVG với kích thước 800x600 giống tỉ lệ khung ảnh của thẻ.
  '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600">'
    // Vẽ nền màu xám nhạt cho toàn bộ ảnh dự phòng.
    + '<rect width="800" height="600" fill="#e2e8f0"/>'
    // Vẽ một khung ảnh đơn giản để người dùng nhận biết đây là ảnh thay thế.
    + '<rect x="250" y="170" width="300" height="220" rx="24" fill="none" stroke="#94a3b8" stroke-width="12"/>'
    // Vẽ hình tròn tượng trưng cho mặt trời trong khung ảnh.
    + '<circle cx="430" cy="240" r="30" fill="#94a3b8"/>'
    // Vẽ hai đường chéo tượng trưng cho hình ngọn núi trong khung ảnh.
    + '<path d="M270 370 L360 280 L430 350 L480 310 L530 370 Z" fill="#94a3b8"/>'
    // Hiển thị dòng chữ thông báo chưa có ảnh ở phía dưới khung hình.
    + '<text x="400" y="470" text-anchor="middle" font-family="Arial, sans-serif" font-size="34" fill="#64748b">Chưa có ảnh</text>'
    // Kết thúc thẻ SVG.
    + '</svg>',
)}`;

// Xuất object cấu hình component để tệp khác có thể import và đăng ký với Vue.
export default {
  // Đặt tên component để Vue Devtools và thông báo lỗi hiển thị tên dễ đọc.
  name: 'TheSanPham',

  // Khai báo toàn bộ props mà component cha truyền xuống, kèm kiểu dữ liệu và quy tắc kiểm tra.
  props: {
    // Prop "ten" là tên sản phẩm, bắt buộc phải có và phải là chuỗi không rỗng.
    ten: {
      // Kiểu dữ liệu mong đợi là String.
      type: String,
      // Đánh dấu đây là prop bắt buộc, thỏa yêu cầu có ít nhất một prop bắt buộc.
      required: true,
      // Hàm kiểm tra bổ sung: tên phải là chuỗi và không được chỉ chứa khoảng trắng.
      validator(value) {
        // Trả về true khi giá trị là chuỗi và sau khi cắt khoảng trắng vẫn còn nội dung.
        return typeof value === 'string' && value.trim().length > 0;
      },
    },

    // Prop "gia" là giá bán của sản phẩm, bắt buộc phải có và phải là số không âm.
    gia: {
      // Kiểu dữ liệu mong đợi là Number.
      type: Number,
      // Giá là thông tin cốt lõi nên cũng được đánh dấu bắt buộc.
      required: true,
      // Hàm kiểm tra bổ sung: giá phải là số hữu hạn và không được nhỏ hơn 0.
      validator(value) {
        // Trả về true khi giá là số hữu hạn và lớn hơn hoặc bằng 0.
        return Number.isFinite(value) && value >= 0;
      },
    },

    // Prop "anh" là đường dẫn ảnh sản phẩm; đây là prop có giá trị mặc định.
    anh: {
      // Kiểu dữ liệu mong đợi là String.
      type: String,
      // Nếu cha không truyền ảnh, component dùng ảnh giữ chỗ trong thư mục public.
      default: './images/placeholder.svg',
      // Hàm kiểm tra bổ sung: đường dẫn ảnh phải là chuỗi không rỗng.
      validator(value) {
        // Trả về true khi giá trị là chuỗi và sau khi cắt khoảng trắng vẫn còn nội dung.
        return typeof value === 'string' && value.trim().length > 0;
      },
    },

    // Prop "moTa" là mô tả chi tiết của sản phẩm; đây cũng là prop có giá trị mặc định.
    moTa: {
      // Kiểu dữ liệu mong đợi là String.
      type: String,
      // Nếu cha không truyền mô tả, component hiển thị câu thông báo thân thiện.
      default: 'Sản phẩm này chưa có mô tả chi tiết.',
    },

    // Prop "soLuongTon" là số lượng sản phẩm còn trong kho; có giá trị mặc định là 0.
    soLuongTon: {
      // Kiểu dữ liệu mong đợi là Number.
      type: Number,
      // Khi cha không truyền số lượng tồn, mặc định coi như đã hết hàng.
      default: 0,
      // Hàm kiểm tra bổ sung: số lượng tồn phải là số nguyên không âm.
      validator(value) {
        // Trả về true khi giá trị là số nguyên và lớn hơn hoặc bằng 0.
        return Number.isInteger(value) && value >= 0;
      },
    },
  },

  // Khai báo sự kiện component con có thể phát lên cha để tài liệu hóa luồng dữ liệu.
  emits: ['mua'],

  // Hàm data trả về trạng thái nội bộ riêng biệt cho từng thẻ sản phẩm.
  data() {
    // Tăng biến đếm mỗi khi một thẻ mới được tạo để bảo đảm id mô tả là duy nhất.
    boDemTheSanPham += 1;
    // Trả về object trạng thái ban đầu của riêng thẻ này.
    return {
      // Trạng thái đóng/mở mô tả được lưu riêng trong data() của từng component.
      // Ban đầu mô tả đóng để giao diện gọn gàng.
      dangMoMoTa: false,
      // Tạo id duy nhất cho vùng mô tả, dùng cho thuộc tính aria-controls.
      idMoTa: `mo-ta-san-pham-${boDemTheSanPham}`,
      // Cờ cho biết thẻ đã chuyển sang ảnh dự phòng hay chưa, giúp chặn vòng lặp lỗi ảnh.
      daDungAnhDuPhong: false,
    };
  },

  // Khu vực các thuộc tính tính toán, tự động cập nhật khi props thay đổi.
  computed: {
    /**
     * Cho biết sản phẩm còn hàng hay không.
     * @returns {boolean} True khi số lượng tồn lớn hơn 0.
     */
    conHang() {
      // So sánh số lượng tồn với 0; kết quả tự cập nhật nếu prop soLuongTon thay đổi.
      return this.soLuongTon > 0;
    },

    /**
     * Chuỗi giá đã được định dạng theo tiền tệ Việt Nam.
     * @returns {string} Giá đã định dạng, ví dụ "890.000 ₫".
     */
    giaDaDinhDang() {
      // Chuyển giá trị prop sang kiểu số để xử lý an toàn cả khi nhận dữ liệu bất thường.
      const soGia = Number(this.gia);
      // Nếu giá không phải số hữu hạn thì trả về chuỗi 0 đồng thay vì làm vỡ giao diện.
      if (!Number.isFinite(soGia)) {
        // Trả về chuỗi dự phòng an toàn.
        return '0 ₫';
      }
      // Dùng Intl.NumberFormat với locale vi-VN để hiển thị dấu phân cách đúng chuẩn Việt Nam.
      return new Intl.NumberFormat('vi-VN', {
        // Định dạng theo kiểu tiền tệ.
        style: 'currency',
        // Dùng đơn vị tiền tệ Việt Nam Đồng.
        currency: 'VND',
        // Không hiển thị phần thập phân vì giá sản phẩm được tính theo đồng.
        maximumFractionDigits: 0,
      }).format(soGia);
    },

    /**
     * Nội dung mô tả thực tế được hiển thị.
     * @returns {string} Mô tả đã cắt khoảng trắng hoặc câu thông báo mặc định.
     */
    moTaHienThi() {
      // Kiểm tra prop có phải chuỗi và có nội dung sau khi cắt khoảng trắng hay không.
      if (typeof this.moTa === 'string' && this.moTa.trim().length > 0) {
        // Trả về mô tả đã cắt khoảng trắng hai đầu để giao diện sạch và nhất quán.
        return this.moTa.trim();
      }
      // Nếu mô tả rỗng hoặc không hợp lệ, trả về câu thông báo thay thế.
      return 'Sản phẩm này chưa có mô tả chi tiết.';
    },

    /**
     * Nhãn hiển thị trên nút đóng/mở mô tả.
     * @returns {string} "Ẩn mô tả" khi đang mở, ngược lại là "Xem mô tả".
     */
    chuoiNutMoTa() {
      // Toán tử ba ngôi chọn nhãn phù hợp với trạng thái hiện tại của từng thẻ.
      return this.dangMoMoTa ? 'Ẩn mô tả' : 'Xem mô tả';
    },

    /**
     * Nhãn trạng thái tồn kho hiển thị trên ảnh sản phẩm.
     * @returns {string} Số lượng còn lại hoặc chữ "Hết hàng".
     */
    nhanTonKho() {
      // Nếu còn hàng thì hiển thị số lượng cụ thể, ngược lại hiển thị nhãn hết hàng.
      return this.conHang ? `Còn ${this.soLuongTon} sản phẩm` : 'Hết hàng';
    },
  },

  // Khu vực theo dõi thay đổi của props để xử lý các trường hợp biên an toàn.
  watch: {
    /**
     * Theo dõi sự thay đổi của prop "anh".
     * Khi cha truyền ảnh mới, cờ dự phòng được đặt lại để ảnh mới vẫn có cơ hội hiển thị.
     */
    anh() {
      // Đặt lại cờ để nếu ảnh mới cũng lỗi thì vẫn có thể chuyển sang ảnh dự phòng một lần nữa.
      this.daDungAnhDuPhong = false;
    },
  },

  // Khu vực các phương thức xử lý sự kiện và tương tác của thẻ.
  methods: {
    /**
     * Đảo trạng thái đóng/mở phần mô tả của riêng thẻ hiện tại.
     * Mỗi thẻ có data() riêng nên thao tác này không ảnh hưởng đến các thẻ khác.
     */
    daoTrangThaiMoTa() {
      // Gán giá trị phủ định của trạng thái hiện tại để bật hoặc tắt mô tả.
      this.dangMoMoTa = !this.dangMoMoTa;
    },

    /**
     * Phát sự kiện "mua" lên component cha khi người dùng bấm nút "Mua".
     * Component con không tự thay đổi dữ liệu của cha, giữ đúng nguyên tắc một chiều.
     */
    xuLyMua() {
      // Nếu sản phẩm đã hết hàng thì dừng xử lý để bảo vệ logic mua hàng.
      if (!this.conHang) {
        // Thoát sớm, không phát sự kiện mua.
        return;
      }
      // Phát sự kiện kèm bản sao dữ liệu cần thiết để cha xử lý mà không phụ thuộc vào DOM.
      this.$emit('mua', {
        // Gửi tên sản phẩm để cha hiển thị thông báo phù hợp.
        ten: this.ten,
        // Gửi giá sản phẩm để cha có thể dùng cho các xử lý tiếp theo.
        gia: this.gia,
        // Gửi số lượng tồn hiện tại để cha biết trạng thái kho của sản phẩm.
        soLuongTon: this.soLuongTon,
      });
    },

    /**
     * Xử lý khi ảnh sản phẩm không tải được.
     * @param {Event} event Sự kiện lỗi của thẻ img.
     */
    xuLyLoiAnh(event) {
      // Lấy phần tử ảnh gây ra lỗi một cách an toàn.
      const phanTuAnh = event && event.target;
      // Nếu không xác định được ảnh hoặc đã dùng ảnh dự phòng rồi thì dừng để tránh lặp vô hạn.
      if (!phanTuAnh || this.daDungAnhDuPhong) {
        // Thoát sớm khỏi phương thức.
        return;
      }
      // Đánh dấu đã chuyển sang ảnh dự phòng cho lần lỗi tiếp theo (nếu có).
      this.daDungAnhDuPhong = true;
      // Gán ảnh dự phòng dạng data URI, bảo đảm ảnh luôn hiển thị mà không cần tải thêm tệp.
      phanTuAnh.src = ANH_DU_PHONG;
    },
  },

  // Template HTML của component, được viết dưới dạng chuỗi vì component nằm trong tệp .js.
  template: `
    <!-- Thẻ article bao ngoài toàn bộ nội dung của một sản phẩm. -->
    <!-- Lớp the-san-pham--het-hang được thêm khi sản phẩm không còn hàng để đổi giao diện. -->
    <article
      class="the-san-pham"
      :class="{ 'the-san-pham--het-hang': !conHang }"
      data-testid="the-san-pham"
    >
      <!-- Khung chứa ảnh và các nhãn trạng thái đè lên ảnh. -->
      <div class="khung-anh">
        <!-- Ảnh sản phẩm lấy trực tiếp từ prop "anh"; alt được tạo từ tên sản phẩm. -->
        <img
          class="anh-san-pham"
          :src="anh"
          :alt="'Ảnh sản phẩm ' + ten"
          loading="lazy"
          @error="xuLyLoiAnh"
        />
        <!-- Nhãn riêng cho sản phẩm hết hàng; chỉ hiển thị khi số lượng tồn bằng 0. -->
        <span
          v-if="!conHang"
          class="nhan-het-hang"
          data-testid="nhan-het-hang"
        >
          Hết hàng
        </span>
        <!-- Nhãn số lượng còn lại; chỉ hiển thị khi sản phẩm vẫn còn hàng. -->
        <span
          v-else
          class="nhan-ton-kho"
          data-testid="nhan-ton-kho"
        >
          {{ nhanTonKho }}
        </span>
      </div>

      <!-- Khu vực nội dung chữ của thẻ sản phẩm. -->
      <div class="noi-dung-the">
        <!-- Tên sản phẩm được lấy từ prop "ten". -->
        <h3 class="ten-san-pham" data-testid="ten-san-pham">{{ ten }}</h3>

        <!-- Giá sản phẩm đã được định dạng theo tiền tệ Việt Nam. -->
        <p class="gia-san-pham" data-testid="gia-san-pham">{{ giaDaDinhDang }}</p>

        <!-- Nút bật/tắt mô tả; trạng thái aria-expanded phản ánh đúng trạng thái của từng thẻ. -->
        <button
          type="button"
          class="nut-mo-ta"
          :aria-expanded="dangMoMoTa ? 'true' : 'false'"
          :aria-controls="dangMoMoTa ? idMoTa : undefined"
          data-testid="nut-mo-ta"
          @click="daoTrangThaiMoTa"
        >
          <!-- Nhãn nút thay đổi giữa "Xem mô tả" và "Ẩn mô tả". -->
          {{ chuoiNutMoTa }}
        </button>

        <!-- Phần mô tả chỉ được render khi trạng thái dangMoMoTa của riêng thẻ này là true. -->
        <p
          v-if="dangMoMoTa"
          :id="idMoTa"
          class="mo-ta-san-pham"
          data-testid="mo-ta-san-pham"
        >
          <!-- Nội dung mô tả lấy từ prop "moTa" đã được chuẩn hóa. -->
          {{ moTaHienThi }}
        </p>

        <!-- Nút mua sản phẩm; bị khóa hoàn toàn khi sản phẩm đã hết hàng. -->
        <button
          type="button"
          class="nut-mua"
          :disabled="!conHang"
          :aria-disabled="conHang ? 'false' : 'true'"
          :title="conHang ? 'Mua sản phẩm' : 'Sản phẩm đã hết hàng'"
          data-testid="nut-mua"
          @click="xuLyMua"
        >
          <!-- Giữ nguyên nhãn "Mua" để người dùng nhận biết nút bị khóa thay vì bị đổi chức năng. -->
          Mua
        </button>
      </div>
    </article>
  `,
};
