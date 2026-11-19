// ============================================================================
// GIAI ĐOẠN 3: MÃ JAVASCRIPT CỦA GIAO DIỆN VUE.JS
// ----------------------------------------------------------------------------
// Tệp này khởi tạo ứng dụng Vue 3, tải danh sách sản phẩm từ API PHP và xử lý
// ba thao tác thêm, sửa, xóa. Sau mỗi thao tác thành công, ứng dụng gọi lại API
// để cập nhật bảng mà không tải lại trang.
//
// Bổ sung cho bài này:
//   - Chọn ảnh trong biểu mẫu, xem trước và tải ảnh qua api/upload.php bằng FormData.
//   - Phân trang phía Backend: mỗi trang 5 sản phẩm, các nút Trước/Sau gọi lại API.
//
// Lưu ý: thư viện Vue được nạp trước đó bằng thẻ <script> trong index.html,
// nên biến toàn cục `Vue` đã sẵn sàng khi tệp này được thực thi.
// ============================================================================

// Bọc toàn bộ mã trong một hàm tự gọi (IIFE) để tránh tạo biến rác toàn cục.
(() => {
  // Bật chế độ JavaScript nghiêm ngặt, giúp phát hiện sớm các lỗi tiềm ẩn.
  'use strict';

  // Đường dẫn tương đối tới API sản phẩm, tính từ tệp index.html.
  // Dùng đường dẫn tương đối để dự án chạy đúng dù đặt trong thư mục con nào của htdocs.
  const API_URL = 'api/products.php';

  // Đường dẫn tới API chuyên nhận ảnh; đây là endpoint POST multipart/form-data mới.
  const UPLOAD_URL = 'api/upload.php';

  // Dung lượng ảnh tối đa cho phép ở phía Vue: 2 MB = 2 * 1024 * 1024 byte.
  // PHP sẽ kiểm tra lại con số này nên Vue chỉ là lớp chặn sớm để phản hồi nhanh.
  const KICH_THUOC_ANH_TOI_DA = 2 * 1024 * 1024;

  // Danh sách phần mở rộng được chấp nhận, viết thường để so sánh không phân biệt hoa/thường.
  const DUOI_ANH_HOP_LE = ['jpg', 'jpeg', 'png'];

  // Danh sách MIME mà trình duyệt thường gán cho JPG/PNG.
  // Nếu trình duyệt không báo MIME thì Vue vẫn chấp nhận nhưng PHP sẽ kiểm tra nội dung thật.
  const MIME_ANH_HOP_LE = ['image/jpeg', 'image/png'];

  // Tạo bộ định dạng số theo chuẩn Việt Nam: dấu chấm phân cách hàng nghìn.
  // maximumFractionDigits: 0 vì đơn giá và tồn kho đều là số nguyên.
  const boDinhDangSo = new Intl.NumberFormat('vi-VN', {
    // Không hiển thị phần thập phân cho đơn giá tính bằng đồng.
    maximumFractionDigits: 0,
  });

  /**
   * Gọi API và chuẩn hóa cách xử lý phản hồi.
   * Hàm này giúp mọi thao tác trong ứng dụng dùng chung một quy trình:
   *   - Gửi yêu cầu fetch.
   *   - Đọc JSON trả về.
   *   - Ném lỗi nếu HTTP thất bại hoặc trường success không phải true.
   *
   * @param {string} diaChi Đường dẫn API cần gọi.
   * @param {RequestInit} tuyChon Tùy chọn fetch (method, body, headers...).
   * @returns {Promise<object>} Dữ liệu JSON đã xác nhận thành công.
   */
  async function goiApi(diaChi, tuyChon = {}) {
    // Tạo đối tượng header mặc định để yêu cầu phản hồi dạng JSON.
    const tieuDe = {
      // Trình duyệt gửi header Accept để báo rằng client mong nhận JSON.
      Accept: 'application/json',
      // Chỉ tự thêm Content-Type JSON khi phần thân KHÔNG phải FormData.
      // Với FormData, trình duyệt phải tự sinh header multipart kèm boundary;
      // nếu ta gán thủ công thì PHP sẽ không đọc được $_FILES và $_POST.
      ...(tuyChon.body && !(tuyChon.body instanceof FormData)
        ? { 'Content-Type': 'application/json' }
        : {}),
      // Cho phép nơi gọi bổ sung hoặc ghi đè header nếu cần.
      ...(tuyChon.headers || {}),
    };

    // Khai báo biến chứa phản hồi HTTP để dùng ở các bước sau.
    let phanHoi;

    // Bọc lệnh fetch trong try/catch để chuyển lỗi mạng thành thông báo thân thiện.
    try {
      // Gửi yêu cầu tới API với tùy chọn của nơi gọi và header đã chuẩn bị.
      phanHoi = await fetch(diaChi, {
        // Sao chép toàn bộ tùy chọn như method và body.
        ...tuyChon,
        // Gắn bộ header đã xây dựng vào yêu cầu.
        headers: tieuDe,
      });
    } catch (loi) {
      // Ném lỗi mới với hướng dẫn kiểm tra máy chủ thay vì hiển thị "Failed to fetch".
      throw new Error('Không thể kết nối đến API. Hãy kiểm tra Apache và MySQL đã chạy chưa.');
    }

    // Khai báo biến chứa dữ liệu JSON đã giải mã.
    let duLieu;

    // Thử đọc nội dung phản hồi dưới dạng JSON.
    try {
      // Chuyển phần thân phản hồi thành đối tượng JavaScript.
      duLieu = await phanHoi.json();
    } catch (loi) {
      // Nếu phản hồi không phải JSON (ví dụ lỗi PHP in ra HTML), báo lỗi rõ ràng.
      throw new Error('Máy chủ trả về dữ liệu không hợp lệ. Vui lòng kiểm tra log PHP.');
    }

    // Kiểm tra cả mã HTTP và trường success theo quy ước của API.
    if (!phanHoi.ok || !duLieu || duLieu.success !== true) {
      // Ưu tiên hiển thị thông báo lỗi do API cung cấp trong trường message.
      const thongBao = duLieu && duLieu.message
        ? duLieu.message
        : `Yêu cầu thất bại (HTTP ${phanHoi.status}).`;

      // Ném lỗi để hàm gọi hiển thị thông báo trên giao diện.
      throw new Error(thongBao);
    }

    // Trả về dữ liệu JSON hợp lệ cho nơi gọi xử lý tiếp.
    return duLieu;
  }

  // Tạo ứng dụng Vue bằng cú pháp Options API quen thuộc.
  const ungDung = Vue.createApp({
    /**
     * Hàm data trả về trạng thái ban đầu của giao diện.
     * Vue sẽ theo dõi các thuộc tính này và tự cập nhật DOM khi chúng thay đổi.
     *
     * @returns {object} Đối tượng chứa toàn bộ trạng thái của ứng dụng.
     */
    data() {
      // Trả về trạng thái khởi tạo của ứng dụng.
      return {
        // Mảng sản phẩm nhận từ API; ban đầu rỗng và sẽ được tải trong mounted().
        products: [],

        // Đối tượng dữ liệu của biểu mẫu, liên kết với các ô nhập qua v-model.
        form: {
          // Mã sản phẩm đang sửa; null nghĩa là biểu mẫu đang ở chế độ thêm mới.
          id: null,
          // Tên sản phẩm người dùng nhập.
          name: '',
          // Đơn giá người dùng nhập; để chuỗi rỗng để hiển thị placeholder ban đầu.
          price: '',
          // Số lượng tồn người dùng nhập; để chuỗi rỗng để hiển thị placeholder ban đầu.
          stock: '',
          // Đường dẫn ảnh hiện tại của sản phẩm đang sửa; rỗng nghĩa là chưa có ảnh.
          image: '',
        },

        // Mã sản phẩm đang được sửa; null nghĩa là không ở chế độ sửa.
        editingId: null,

        // Cờ cho biết ứng dụng đang tải danh sách sản phẩm hay không.
        loading: false,

        // Cờ cho biết biểu mẫu đang gửi yêu cầu thêm/sửa/xóa hay không.
        submitting: false,

        // Cờ cho biết đang gửi FormData lên api/upload.php hay không.
        uploading: false,

        // Tệp ảnh người dùng vừa chọn; null nghĩa là không tải ảnh mới.
        selectedFile: null,

        // URL tạm do URL.createObjectURL tạo ra để xem trước ảnh mới chọn.
        imagePreview: '',

        // Mã sản phẩm đã lưu thông tin nhưng upload ảnh chưa thành công.
        // Khác null thì giao diện hiện nút "Thử lại upload ảnh".
        pendingUploadId: null,

        // Trạng thái phân trang nhận từ API, dùng để dựng dòng thông tin và nút Trước/Sau.
        pagination: {
          // Trang hiện tại; API có thể trả trang nhỏ hơn nếu yêu cầu vượt trang cuối.
          page: 1,
          // Số sản phẩm mỗi trang, cố định bằng 5 theo đề bài.
          limit: 5,
          // Tổng số sản phẩm trong toàn bộ bảng (không phải số dòng của riêng trang này).
          total: 0,
          // Tổng số trang; tối thiểu là 1 kể cả khi danh sách rỗng.
          total_pages: 1,
        },

        // Thông báo lỗi hiển thị phía trên bảng; chuỗi rỗng nghĩa là không có lỗi.
        errorMessage: '',

        // Thông báo thành công hiển thị phía trên bảng.
        successMessage: '',
      };
    },

    /**
     * Các thuộc tính tính toán (computed) tự cập nhật khi dữ liệu phụ thuộc thay đổi.
     */
    computed: {
      /**
       * Cho biết biểu mẫu đang ở chế độ sửa hay thêm mới.
       * Chỉ cần kiểm tra editingId có khác null hay không.
       *
       * @returns {boolean} true nếu đang sửa một sản phẩm đã tồn tại.
       */
      isEditing() {
        // Trả về true khi đã chọn một sản phẩm để sửa.
        return this.editingId !== null;
      },

      /**
       * Tính tổng giá trị tồn kho của toàn bộ sản phẩm đang hiển thị.
       * Công thức: tổng của (đơn giá × số lượng tồn) trên từng sản phẩm.
       *
       * @returns {number} Tổng giá trị tồn kho tính bằng đồng.
       */
      totalInventoryValue() {
        // Dùng reduce để cộng dồn giá trị tồn kho của từng sản phẩm.
        return this.products.reduce((tong, sanPham) => {
          // Ép kiểu số để bảo đảm phép nhân luôn thực hiện đúng.
          const gia = Number(sanPham.price);
          // Ép kiểu số lượng tồn trước khi nhân.
          const ton = Number(sanPham.stock);
          // Cộng giá trị của sản phẩm hiện tại vào tổng đang tích lũy.
          return tong + gia * ton;
        }, 0);
      },
    },

    /**
     * Hook mounted chạy một lần sau khi Vue gắn giao diện vào DOM.
     * Đây là nơi phù hợp để gọi API tải danh sách sản phẩm ban đầu.
     */
    mounted() {
      // Gọi API để lấy danh sách sản phẩm ngay khi mở trang.
      this.fetchProducts();
    },

    /**
     * Các phương thức xử lý hành vi của giao diện.
     */
    methods: {
      /**
       * Định dạng một số thành chuỗi tiền Việt Nam, ví dụ 5000 -> "5.000 đ".
       *
       * @param {number|string} giaTri Giá trị cần định dạng.
       * @returns {string} Chuỗi đã định dạng kèm đơn vị đồng.
       */
      formatCurrency(giaTri) {
        // Chuyển giá trị đầu vào thành số để tránh lỗi khi API trả về chuỗi.
        const so = Number(giaTri);

        // Nếu giá trị không phải số hữu hạn, trả về "0 đ" để giao diện không hiển thị NaN.
        if (!Number.isFinite(so)) {
          // Giá trị dự phòng an toàn cho dữ liệu bất thường.
          return '0 đ';
        }

        // Định dạng số theo chuẩn Việt Nam và nối thêm đơn vị "đ".
        return `${boDinhDangSo.format(so)} đ`;
      },

      /**
       * Xóa cả thông báo lỗi và thông báo thành công.
       * Được gọi trước mỗi thao tác mới để tránh hiển thị thông báo cũ.
       */
      clearMessages() {
        // Xóa nội dung thông báo lỗi.
        this.errorMessage = '';

        // Xóa nội dung thông báo thành công.
        this.successMessage = '';
      },

      /**
       * Gọi GET api/products.php?page=...&limit=5 để tải một trang sản phẩm.
       * Hàm được dùng khi mở trang, khi bấm "Làm mới", khi chuyển trang và sau mỗi thao tác.
       *
       * @param {{page?: number}} tuyChon Trang cần tải; nếu không truyền thì dùng trang hiện tại.
       * @returns {Promise<void>} Promise hoàn tất khi bảng và thông tin phân trang đã cập nhật.
       */
      async fetchProducts(tuyChon = {}) {
        // Bật cờ loading để giao diện hiển thị trạng thái đang tải.
        this.loading = true;

        // Xóa thông báo lỗi cũ trước khi thực hiện yêu cầu mới.
        this.errorMessage = '';

        // Chọn trang cần lấy: ưu tiên trang truyền vào, nếu không có thì dùng trang đang hiển thị.
        const trangYeuCau = Number.isInteger(tuyChon.page) && tuyChon.page > 0
          ? tuyChon.page
          : this.pagination.page;

        // Ghép URL với đúng tham số page và limit mà Backend yêu cầu.
        const diaChi = `${API_URL}?page=${trangYeuCau}&limit=${this.pagination.limit}`;

        // Bọc lời gọi API trong try/catch để hiển thị lỗi thay vì làm hỏng ứng dụng.
        try {
          // Gọi API danh sách sản phẩm theo trang vừa tính.
          const ketQua = await goiApi(diaChi);

          // Kiểm tra data có phải mảng không; nếu không, dùng mảng rỗng để bảo đảm an toàn.
          this.products = Array.isArray(ketQua.data) ? ketQua.data : [];

          // Đọc đối tượng pagination do API trả về; nếu thiếu thì dùng object mặc định.
          const p = ketQua.pagination && typeof ketQua.pagination === 'object'
            ? ketQua.pagination
            : {};

          // Ép tổng số sản phẩm về số nguyên không âm để hiển thị chính xác.
          const tong = Math.max(0, Number(p.total) || 0);

          // Ép limit về số nguyên dương; phòng trường hợp API trả dữ liệu bất thường.
          const limit = Math.max(1, Number(p.limit) || 5);

          // Tính lại total_pages theo đúng công thức làm tròn lên, tối thiểu là 1.
          const tongTrang = Math.max(1, Math.ceil(tong / limit));

          // Lấy page thực tế do API trả về (có thể đã bị kẹt về trang cuối).
          // Math.min bảo đảm không hiển thị page lớn hơn tổng số trang.
          const trangThucTe = Math.min(
            Math.max(1, Number(p.page) || 1),
            tongTrang
          );

          // Gán toàn bộ thông tin phân trang vào state để template dùng ngay.
          this.pagination = {
            // Trang thực tế sau khi API xử lý.
            page: trangThucTe,
            // Số bản ghi mỗi trang.
            limit,
            // Tổng sản phẩm.
            total: tong,
            // Tổng số trang.
            total_pages: tongTrang,
          };
        } catch (loi) {
          // Hiển thị thông báo lỗi do API hoặc kết nối trả về.
          this.errorMessage = loi.message || 'Không thể tải danh sách sản phẩm.';
        } finally {
          // Dù thành công hay thất bại, luôn tắt cờ loading.
          this.loading = false;
        }
      },

      /**
       * Chuyển tới một trang mới bằng cách gọi lại API, không tải lại toàn bộ trình duyệt.
       * Nút Trước/Sau trong giao diện gọi phương thức này.
       *
       * @param {number} trangMoi Số trang muốn xem.
       * @returns {Promise<void>} Promise hoàn tất khi dữ liệu trang mới đã sẵn sàng.
       */
      async goToPage(trangMoi) {
        // Ép giá trị về số nguyên để tránh lỗi khi nhận chuỗi từ sự kiện click.
        const trang = Number(trangMoi);

        // Không làm gì nếu trang không hợp lệ hoặc trùng trang hiện tại.
        if (
          !Number.isInteger(trang)
          || trang < 1
          || trang > this.pagination.total_pages
          || trang === this.pagination.page
        ) {
          // Dừng hàm để tránh gọi API không cần thiết.
          return;
        }

        // Xóa thông báo cũ trước khi tải trang mới.
        this.clearMessages();

        // Gọi API với trang đích; state pagination sẽ được cập nhật trong fetchProducts.
        await this.fetchProducts({ page: trang });
      },

      /**
       * Kiểm tra tệp ảnh ở phía Vue trước khi gửi yêu cầu.
       * Đây chỉ là lớp chặn sớm; PHP vẫn kiểm tra lại kích thước và loại ảnh thật.
       *
       * @param {File} file Tệp người dùng vừa chọn từ ô input type="file".
       * @returns {string} Chuỗi rỗng nếu hợp lệ, ngược lại là thông báo lỗi.
       */
      kiemTraFileAnh(file) {
        // Lấy tên tệp, ép về chữ thường rồi tách phần mở rộng sau dấu chấm cuối cùng.
        const ten = String(file.name || '').toLowerCase();
        const duoi = ten.includes('.') ? ten.split('.').pop() : '';

        // Kiểm tra phần mở rộng có nằm trong danh sách jpg/jpeg/png hay không.
        if (!DUOI_ANH_HOP_LE.includes(duoi)) {
          // Trả lỗi ngay để người dùng chọn lại trước khi gửi API.
          return 'Chỉ chấp nhận ảnh JPG, JPEG hoặc PNG.';
        }

        // Một số trình duyệt có thể để trống file.type; chỉ kiểm tra khi có giá trị.
        if (file.type && !MIME_ANH_HOP_LE.includes(file.type)) {
          // Báo lỗi nếu MIME do trình duyệt báo không phải JPEG/PNG.
          return 'Tệp chọn không phải ảnh JPG hoặc PNG.';
        }

        // Kiểm tra dung lượng không vượt quá 2 MB theo đúng yêu cầu đề bài.
        if (file.size > KICH_THUOC_ANH_TOI_DA) {
          // Thông báo kèm giới hạn để người dùng biết cần chọn ảnh nhỏ hơn.
          return 'Ảnh không được vượt quá 2 MB.';
        }

        // Kiểm tra tệp không rỗng (trường hợp hiếm do lỗi hệ điều hành).
        if (file.size <= 0) {
          // Trả lỗi để tránh gửi lên server một tệp vô nghĩa.
          return 'Tệp ảnh không được rỗng.';
        }

        // Trả chuỗi rỗng báo rằng tệp đã qua được các kiểm tra sơ bộ ở Vue.
        return '';
      },

      /**
       * Giải phóng URL xem trước cũ để trình duyệt không giữ file trong bộ nhớ.
       */
      huyPreview() {
        // Chỉ gọi revokeObjectURL nếu URL hiện tại là blob do ta tạo ra.
        if (this.imagePreview && this.imagePreview.startsWith('blob:')) {
          // Trả lại tài nguyên cho trình duyệt.
          URL.revokeObjectURL(this.imagePreview);
        }

        // Đặt lại URL xem trước về chuỗi rỗng.
        this.imagePreview = '';
      },

      /**
       * Xử lý sự kiện người dùng chọn hoặc đổi ảnh trong ô input type="file".
       *
       * @param {Event} suKien Sự kiện change của ô chọn ảnh.
       */
      onFileChange(suKien) {
        // Lấy phần tử input đã kích hoạt sự kiện.
        const input = suKien.target;

        // Lấy tệp đầu tiên nếu người dùng chọn được file; ngược lại là null.
        const file = input.files && input.files.length > 0 ? input.files[0] : null;

        // Hủy URL xem trước của lần chọn trước trước khi thiết lập tệp mới.
        this.huyPreview();

        // Đặt lại tệp đã chọn về null; sẽ gán lại nếu tệp mới hợp lệ.
        this.selectedFile = null;

        // Nếu người dùng bỏ chọn ảnh thì dừng, giữ nguyên ảnh hiện tại của sản phẩm.
        if (!file) {
          // Kết thúc hàm; form.image vẫn là đường dẫn ảnh cũ cần giữ lại.
          return;
        }

        // Gọi hàm kiểm tra ở Vue; lỗi trả về chuỗi rỗng nếu tệp hợp lệ.
        const loi = this.kiemTraFileAnh(file);

        // Nếu có lỗi thì báo ngay và xóa giá trị input để có thể chọn lại chính tệp đó.
        if (loi) {
          // Hiển thị thông báo lỗi trong hộp cảnh báo ở bảng.
          this.errorMessage = loi;

          // Xóa value của input để trình duyệt cho phép chọn lại cùng một tệp.
          input.value = '';

          // Dừng hàm, không lưu tệp không hợp lệ vào state.
          return;
        }

        // Lưu tệp hợp lệ để saveProduct hoặc retryUpload dùng khi gửi FormData.
        this.selectedFile = file;

        // Tạo URL tạm và gán vào state để thẻ <img> trong biểu mẫu xem trước ảnh.
        this.imagePreview = URL.createObjectURL(file);

        // Xóa thông báo lỗi cũ vì người dùng đã chọn lại ảnh hợp lệ.
        this.clearMessages();
      },

      /**
       * Bỏ ảnh vừa chọn, quay lại sử dụng ảnh hiện tại của sản phẩm (nếu có).
       */
      xoaAnhDaChon() {
        // Giải phóng URL xem trước.
        this.huyPreview();

        // Xóa tệp đã chọn để khi lưu sẽ không gọi API upload.
        this.selectedFile = null;

        // Xóa value của ô input qua ref để người dùng có thể chọn tệp khác từ đầu.
        if (this.$refs.fileInput) {
          // Đặt lại value về chuỗi rỗng.
          this.$refs.fileInput.value = '';
        }
      },

      /**
       * Xử lý sự kiện gửi biểu mẫu.
       * Nếu đang sửa thì gửi PUT kèm id; nếu đang thêm thì gửi POST.
       * Sau khi thông tin sản phẩm được lưu thành công, nếu có chọn ảnh mới thì gọi tiếp api/upload.php.
       *
       * @returns {Promise<void>} Promise hoàn tất khi yêu cầu xử lý xong.
       */
      async saveProduct() {
        // Nếu đang có yêu cầu khác chạy dở thì bỏ qua để tránh gửi trùng.
        if (this.submitting || this.uploading) {
          // Kết thúc hàm ngay lập tức.
          return;
        }

        // Xóa các thông báo cũ trước khi bắt đầu thao tác mới.
        this.clearMessages();

        // Nếu đã chọn ảnh thì kiểm tra lại ở Vue trước, không gửi file sai lên server.
        if (this.selectedFile) {
          // Lấy thông báo lỗi; chuỗi rỗng nghĩa là tệp hợp lệ.
          const loiAnh = this.kiemTraFileAnh(this.selectedFile);

          // Nếu có lỗi thì dừng, không lưu sản phẩm và không gọi upload.
          if (loiAnh) {
            // Hiện thông báo để người dùng chọn ảnh khác.
            this.errorMessage = loiAnh;

            // Kết thúc hàm ngay lập tức.
            return;
          }
        }

        // Gom ba trường của biểu mẫu thành đối tượng JSON gửi lên API.
        // Ảnh KHÔNG nằm trong JSON mà được gửi riêng qua FormData sau khi có id.
        const duLieuGui = {
          // Tên sản phẩm; PHP sẽ tự trim và kiểm tra độ dài.
          name: this.form.name,
          // Đơn giá; PHP kiểm tra là số nguyên lớn hơn 0.
          price: this.form.price,
          // Số lượng tồn; PHP kiểm tra là số nguyên không âm.
          stock: this.form.stock,
        };

        // Ghi lại chế độ trước khi có thể đổi sang sửa ở bước upload.
        // Biến này quyết định sau khi thêm thì tải lại trang 1, sau khi sửa thì giữ trang hiện tại.
        const dangSua = this.isEditing;

        // Khai báo biến lưu địa chỉ API tương ứng với chế độ thêm hoặc sửa.
        let diaChiApi = API_URL;

        // Khai báo biến lưu phương thức HTTP cần dùng.
        let phuongThuc = 'POST';

        // Nếu đang ở chế độ sửa thì chuyển sang PUT và gắn id trên URL.
        if (dangSua && this.form.id !== null) {
          // Thêm tham số id đã mã hóa vào đường dẫn API.
          diaChiApi = `${API_URL}?id=${encodeURIComponent(this.form.id)}`;

          // Đổi phương thức HTTP sang PUT theo đúng yêu cầu của đề bài.
          phuongThuc = 'PUT';
        }

        // Bật cờ submitting để vô hiệu hóa nút và tránh gửi lặp.
        this.submitting = true;

        // Bọc yêu cầu API trong try/catch/finally để luôn tắt cờ submitting.
        try {
          // Gọi API với phương thức và phần thân JSON vừa chuẩn bị.
          const ketQua = await goiApi(diaChiApi, {
            // Phương thức HTTP là POST hoặc PUT.
            method: phuongThuc,
            // Chuyển đối tượng dữ liệu thành chuỗi JSON.
            body: JSON.stringify(duLieuGui),
          });

          // Lấy mã sản phẩm đã lưu: POST trả id ở cả cấp cao nhất và trong data; PUT dùng form.id.
          const idDaLuu = Number(ketQua.id ?? ketQua.data?.id ?? this.form.id);

          // Tính trang cần tải lại: thêm mới thì về trang 1, sửa thì giữ trang hiện tại.
          const trangCanTai = dangSua ? this.pagination.page : 1;

          // Nếu người dùng không chọn ảnh mới thì lưu xong là kết thúc tại đây.
          if (!this.selectedFile) {
            // Hiển thị thông báo thành công do API trả về, hoặc thông báo dự phòng.
            this.successMessage = ketQua.message
              || (dangSua ? 'Cập nhật sản phẩm thành công.' : 'Thêm sản phẩm thành công.');

            // Đưa biểu mẫu về trạng thái thêm mới sau khi lưu thành công.
            this.resetForm();

            // Tải lại đúng trang đã tính ở trên mà không reload cả trang web.
            await this.fetchProducts({ page: trangCanTai });

            // Kết thúc hàm vì không có ảnh để upload.
            return;
          }

          // Có ảnh: chuyển biểu mẫu sang chế độ sửa id vừa lưu.
          // Nhờ vậy, nếu người dùng bấm "Cập nhật" lần nữa thì vẫn sửa đúng sản phẩm này,
          // chứ không tạo ra bản ghi trùng khi thêm mới.
          this.editingId = idDaLuu;
          this.form.id = idDaLuu;

          // Ghi lại id cần upload để trường hợp thất bại còn có nút "Thử lại".
          this.pendingUploadId = idDaLuu;

          // Báo đã lưu thông tin và đang tải ảnh để người dùng không tưởng rằng upload xong.
          this.successMessage = (ketQua.message || 'Đã lưu thông tin sản phẩm.') + ' Đang tải ảnh lên...';

          // Gọi hàm upload riêng; hàm này tự xử lý thông báo lỗi và cho phép thử lại.
          await this.uploadAnh(idDaLuu, this.selectedFile, trangCanTai);
        } catch (loi) {
          // Hiển thị thông báo lỗi do API trả về, ví dụ dữ liệu không hợp lệ.
          this.errorMessage = loi.message || 'Không thể lưu sản phẩm.';
        } finally {
          // Dù thành công hay thất bại, luôn mở khóa biểu mẫu.
          this.submitting = false;
        }
      },

      /**
       * Gửi ảnh của một sản phẩm đã được lưu tới api/upload.php bằng FormData.
       * Chỉ cập nhật giao diện khi API trả success; nếu lỗi thì giữ nguyên ảnh cũ và báo cho người dùng.
       *
       * @param {number} id          Mã sản phẩm đã lưu ở bước trước.
       * @param {File}   file        Tệp ảnh hợp lệ đã chọn.
       * @param {number} trangCanTai Trang cần tải lại sau khi upload thành công.
       * @returns {Promise<boolean>} true nếu upload thành công, false nếu thất bại.
       */
      async uploadAnh(id, file, trangCanTai) {
        // Bật cờ uploading để khóa nút và tránh gửi nhiều ảnh cùng lúc.
        this.uploading = true;

        // Tạo đối tượng FormData để gửi multipart/form-data theo đúng yêu cầu đề bài.
        const formDuLieu = new FormData();

        // Đính kèm mã sản phẩm; PHP đọc giá trị này từ $_POST.
        formDuLieu.append('product_id', String(id));

        // Đính kèm tệp ảnh dưới tên trường `image`; PHP đọc từ $_FILES['image'].
        formDuLieu.append('image', file, file.name);

        // Bọc yêu cầu upload trong try/catch để biến lỗi mạng thành thông báo thân thiện.
        try {
          // Gọi API upload; goiApi sẽ KHÔNG gán Content-Type JSON cho FormData.
          const ketQua = await goiApi(UPLOAD_URL, {
            // Phương thức POST theo đúng quy định của api/upload.php.
            method: 'POST',
            // Chuyển FormData làm phần thân, trình duyệt tự thêm boundary.
            body: formDuLieu,
          });

          // Hiện thông báo thành công do API trả về.
          this.successMessage = ketQua.message || 'Tải ảnh lên thành công.';

          // Upload thành công nên xóa trạng thái chờ thử lại.
          this.pendingUploadId = null;

          // Reset biểu mẫu, đồng thời giải phóng URL xem trước và ô chọn file.
          this.resetForm();

          // Tải lại đúng trang (thêm mới -> trang 1, sửa -> trang hiện tại).
          await this.fetchProducts({ page: trangCanTai });

          // Báo thành công cho hàm gọi.
          return true;
        } catch (loi) {
          // Hiển thị lỗi do API trả về (sai loại ảnh, quá 2 MB, không tìm thấy sản phẩm...).
          this.errorMessage = loi.message || 'Không thể tải ảnh lên. Vui lòng thử lại.';

          // Giữ pendingUploadId để nút "Thử lại upload ảnh" xuất hiện.
          // Thông tin sản phẩm đã lưu ở bước trước vẫn an toàn trong CSDL.
          return false;
        } finally {
          // Dù thành công hay thất bại, luôn mở khóa giao diện.
          this.uploading = false;
        }
      },

      /**
       * Thử lại upload ảnh cho sản phẩm đã lưu thông tin nhưng upload trước đó thất bại.
       * Không gọi lại POST/PUT sản phẩm nên không tạo bản ghi trùng.
       *
       * @returns {Promise<void>} Promise hoàn tất khi lần thử lại kết thúc.
       */
      async retryUpload() {
        // Nếu đang upload hoặc không có sản phẩm chờ upload thì bỏ qua.
        if (this.uploading || this.pendingUploadId === null) {
          // Kết thúc hàm ngay lập tức.
          return;
        }

        // Nếu người dùng chưa chọn ảnh mới thì yêu cầu chọn lại.
        if (!this.selectedFile) {
          // Hiện hướng dẫn trong hộp lỗi để người dùng biết cần làm gì.
          this.errorMessage = 'Vui lòng chọn lại ảnh JPG/PNG (tối đa 2 MB) để thử lại.';

          // Dừng hàm, không gọi API.
          return;
        }

        // Kiểm tra lại tệp ở Vue dù lỗi trước đó có thể do nội dung ảnh giả mạo.
        const loiAnh = this.kiemTraFileAnh(this.selectedFile);

        // Nếu vẫn sai thì báo lỗi và dừng.
        if (loiAnh) {
          // Hiển thị thông báo cụ thể.
          this.errorMessage = loiAnh;

          // Kết thúc hàm.
          return;
        }

        // Xóa thông báo lỗi cũ trước lần thử lại.
        this.clearMessages();

        // Gọi lại đúng mã sản phẩm đã lưu; trang hiện tại được giữ nguyên.
        await this.uploadAnh(this.pendingUploadId, this.selectedFile, this.pagination.page);
      },

      /**
       * Đưa dữ liệu của một dòng sản phẩm lên biểu mẫu để chỉnh sửa.
       *
       * @param {{id: number, name: string, price: number, stock: number, image: string}} sanPham Sản phẩm được chọn.
       */
      startEdit(sanPham) {
        // Lưu mã sản phẩm đang sửa để giao diện biết đang ở chế độ chỉnh sửa.
        this.editingId = sanPham.id;

        // Sao chép dữ liệu sản phẩm vào biểu mẫu, không gán trực tiếp đối tượng gốc.
        // Việc sao chép giúp thay đổi trên biểu mẫu không làm thay đổi bảng trước khi lưu.
        this.form = {
          // Giữ mã sản phẩm để dùng khi gửi yêu cầu PUT.
          id: sanPham.id,
          // Điền tên hiện tại vào ô nhập.
          name: sanPham.name,
          // Điền đơn giá hiện tại vào ô nhập.
          price: sanPham.price,
          // Điền số lượng tồn hiện tại vào ô nhập.
          stock: sanPham.stock,
          // Điền đường dẫn ảnh hiện tại vào form để biểu mẫu xem trước ảnh cũ.
          image: sanPham.image || '',
        };

        // Khi sửa một sản phẩm khác thì bỏ tệp đã chọn từ lần trước (nếu có).
        this.xoaAnhDaChon();

        // Không còn sản phẩm nào chờ upload ảnh từ thao tác cũ.
        this.pendingUploadId = null;

        // Xóa các thông báo cũ để tránh gây nhầm lẫn khi chuyển sang sửa.
        this.clearMessages();

        // Chờ Vue cập nhật DOM rồi đưa con trỏ vào ô tên sản phẩm cho thuận tiện.
        this.$nextTick(() => {
          // Tìm ô nhập tên theo id và gọi focus nếu phần tử tồn tại.
          document.getElementById('product-name')?.focus();
        });
      },

      /**
       * Hủy chế độ chỉnh sửa và đưa biểu mẫu về trạng thái thêm mới.
       */
      cancelEdit() {
        // Đặt lại toàn bộ biểu mẫu và thoát khỏi chế độ sửa.
        this.resetForm();

        // Xóa các thông báo cũ để giao diện sạch sẽ.
        this.clearMessages();
      },

      /**
       * Đặt biểu mẫu về giá trị ban đầu của chế độ thêm mới.
       */
      resetForm() {
        // Xóa mã sản phẩm đang sửa để isEditing trở thành false.
        this.editingId = null;

        // Tạo đối tượng form mới với các giá trị rỗng.
        this.form = {
          // Không có id vì chưa chọn sản phẩm nào.
          id: null,
          // Ô tên sản phẩm rỗng.
          name: '',
          // Ô đơn giá rỗng để hiển thị placeholder.
          price: '',
          // Ô số lượng tồn rỗng để hiển thị placeholder.
          stock: '',
          // Không có ảnh cũ vì đây là biểu mẫu thêm mới.
          image: '',
        };

        // Bỏ tệp ảnh đã chọn và giải phóng URL xem trước.
        this.xoaAnhDaChon();

        // Xóa luôn trạng thái chờ upload của thao tác trước.
        this.pendingUploadId = null;
      },

      /**
       * Xóa một sản phẩm sau khi người dùng xác nhận.
       * Chỉ gửi yêu cầu DELETE nếu người dùng bấm OK trong hộp thoại xác nhận.
       *
       * @param {{id: number, name: string}} sanPham Sản phẩm cần xóa.
       * @returns {Promise<void>} Promise hoàn tất khi thao tác xóa kết thúc.
       */
      async deleteProduct(sanPham) {
        // Nếu đang có yêu cầu khác chạy dở thì không cho xóa để tránh xung đột.
        if (this.submitting) {
          // Kết thúc hàm ngay lập tức.
          return;
        }

        // Hiển thị hộp thoại xác nhận của trình duyệt và lưu lựa chọn của người dùng.
        const daDongY = window.confirm(
          `Bạn có chắc muốn xóa sản phẩm "${sanPham.name}"? Hành động này không thể hoàn tác.`
        );

        // Nếu người dùng bấm Hủy hoặc đóng hộp thoại thì dừng, không gửi yêu cầu DELETE.
        if (!daDongY) {
          // Kết thúc hàm mà không thay đổi dữ liệu.
          return;
        }

        // Xóa các thông báo cũ trước khi thực hiện xóa.
        this.clearMessages();

        // Bật cờ submitting để khóa các nút trong lúc chờ API phản hồi.
        this.submitting = true;

        // Bọc yêu cầu DELETE trong try/catch/finally.
        try {
          // Gọi API xóa với id được mã hóa an toàn trên URL.
          const ketQua = await goiApi(
            `${API_URL}?id=${encodeURIComponent(sanPham.id)}`,
            {
              // Sử dụng phương thức DELETE theo đúng yêu cầu của đề bài.
              method: 'DELETE',
            }
          );

          // Hiển thị thông báo thành công do API trả về hoặc thông báo dự phòng.
          this.successMessage = ketQua.message || 'Xóa sản phẩm thành công.';

          // Nếu sản phẩm vừa xóa đang được chỉnh sửa thì đưa biểu mẫu về chế độ thêm.
          if (this.editingId === sanPham.id) {
            // Đặt lại biểu mẫu để tránh sửa một sản phẩm không còn tồn tại.
            this.resetForm();
          }

          // Gọi lại API để cập nhật bảng mà không tải lại trang.
          await this.fetchProducts();
        } catch (loi) {
          // Hiển thị thông báo lỗi nếu API không tìm thấy sản phẩm hoặc có lỗi khác.
          this.errorMessage = loi.message || 'Không thể xóa sản phẩm.';
        } finally {
          // Luôn mở khóa giao diện sau khi yêu cầu kết thúc.
          this.submitting = false;
        }
      },
    },
  });

  // Gắn ứng dụng vào phần tử có id="app" trong index.html để bắt đầu hiển thị.
  ungDung.mount('#app');
})();
