// ============================================================================
// GIAI ĐOẠN 3: MÃ JAVASCRIPT CỦA GIAO DIỆN VUE.JS
// ----------------------------------------------------------------------------
// Tệp này khởi tạo ứng dụng Vue 3, tải danh sách sản phẩm từ API PHP và xử lý
// ba thao tác thêm, sửa, xóa. Sau mỗi thao tác thành công, ứng dụng gọi lại API
// để cập nhật bảng mà không tải lại trang.
//
// Lưu ý: thư viện Vue được nạp trước đó bằng thẻ <script> trong index.html,
// nên biến toàn cục `Vue` đã sẵn sàng khi tệp này được thực thi.
// ============================================================================

// Bọc toàn bộ mã trong một hàm tự gọi (IIFE) để tránh tạo biến rác toàn cục.
(() => {
  // Bật chế độ JavaScript nghiêm ngặt, giúp phát hiện sớm các lỗi tiềm ẩn.
  'use strict';

  // Đường dẫn tương đối tới API PHP, tính từ tệp index.html.
  // Dùng đường dẫn tương đối để dự án chạy đúng dù đặt trong thư mục con nào của htdocs.
  const API_URL = 'api/products.php';

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
      // Chỉ thêm Content-Type khi yêu cầu có phần thân (POST/PUT gửi JSON).
      ...(tuyChon.body ? { 'Content-Type': 'application/json' } : {}),
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
        },

        // Mã sản phẩm đang được sửa; null nghĩa là không ở chế độ sửa.
        editingId: null,

        // Cờ cho biết ứng dụng đang tải danh sách sản phẩm hay không.
        loading: false,

        // Cờ cho biết biểu mẫu đang gửi yêu cầu thêm/sửa/xóa hay không.
        submitting: false,

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
       * Gọi GET api/products.php để tải lại danh sách sản phẩm.
       * Hàm được dùng khi mở trang, khi bấm "Làm mới" và sau mỗi thao tác thành công.
       *
       * @returns {Promise<void>} Promise hoàn tất khi dữ liệu đã được cập nhật.
       */
      async fetchProducts() {
        // Bật cờ loading để giao diện hiển thị trạng thái đang tải.
        this.loading = true;

        // Xóa thông báo lỗi cũ trước khi thực hiện yêu cầu mới.
        this.errorMessage = '';

        // Bọc lời gọi API trong try/catch để hiển thị lỗi thay vì làm hỏng ứng dụng.
        try {
          // Gọi API danh sách sản phẩm.
          const ketQua = await goiApi(API_URL);

          // Kiểm tra data có phải mảng không; nếu không, dùng mảng rỗng để bảo đảm an toàn.
          this.products = Array.isArray(ketQua.data) ? ketQua.data : [];
        } catch (loi) {
          // Hiển thị thông báo lỗi do API hoặc kết nối trả về.
          this.errorMessage = loi.message || 'Không thể tải danh sách sản phẩm.';
        } finally {
          // Dù thành công hay thất bại, luôn tắt cờ loading.
          this.loading = false;
        }
      },

      /**
       * Xử lý sự kiện gửi biểu mẫu.
       * Nếu đang sửa thì gửi PUT kèm id; nếu đang thêm thì gửi POST.
       *
       * @returns {Promise<void>} Promise hoàn tất khi yêu cầu xử lý xong.
       */
      async saveProduct() {
        // Nếu đang có yêu cầu khác chạy dở thì bỏ qua để tránh gửi trùng.
        if (this.submitting) {
          // Kết thúc hàm ngay lập tức.
          return;
        }

        // Xóa các thông báo cũ trước khi bắt đầu thao tác mới.
        this.clearMessages();

        // Gom ba trường của biểu mẫu thành đối tượng JSON gửi lên API.
        const duLieuGui = {
          // Tên sản phẩm; PHP sẽ tự trim và kiểm tra độ dài.
          name: this.form.name,
          // Đơn giá; PHP kiểm tra là số nguyên lớn hơn 0.
          price: this.form.price,
          // Số lượng tồn; PHP kiểm tra là số nguyên không âm.
          stock: this.form.stock,
        };

        // Khai báo biến lưu địa chỉ API tương ứng với chế độ thêm hoặc sửa.
        let diaChiApi = API_URL;

        // Khai báo biến lưu phương thức HTTP cần dùng.
        let phuongThuc = 'POST';

        // Nếu đang ở chế độ sửa thì chuyển sang PUT và gắn id trên URL.
        if (this.isEditing && this.form.id !== null) {
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

          // Hiển thị thông báo thành công do API trả về, hoặc thông báo dự phòng.
          this.successMessage = ketQua.message
            || (this.isEditing ? 'Cập nhật sản phẩm thành công.' : 'Thêm sản phẩm thành công.');

          // Đưa biểu mẫu về trạng thái thêm mới sau khi lưu thành công.
          this.resetForm();

          // Gọi lại API để bảng hiển thị dữ liệu mới nhất mà không tải lại trang.
          await this.fetchProducts();
        } catch (loi) {
          // Hiển thị thông báo lỗi do API trả về, ví dụ dữ liệu không hợp lệ.
          this.errorMessage = loi.message || 'Không thể lưu sản phẩm.';
        } finally {
          // Dù thành công hay thất bại, luôn mở khóa biểu mẫu.
          this.submitting = false;
        }
      },

      /**
       * Đưa dữ liệu của một dòng sản phẩm lên biểu mẫu để chỉnh sửa.
       *
       * @param {{id: number, name: string, price: number, stock: number}} sanPham Sản phẩm được chọn.
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
        };

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
        };
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
