<template>
  <!-- Khung bao ngoài giúp căn giữa và giới hạn chiều rộng toàn bộ giao diện. -->
  <div class="app-shell">
    <!-- Phần tiêu đề trang, hiển thị tên ứng dụng và trạng thái khởi tạo. -->
    <header class="page-header">
      <!-- Nhóm nội dung giới thiệu ngắn gọn về ứng dụng. -->
      <div class="header-content">
        <!-- Dòng nhãn nhỏ phía trên tiêu đề chính. -->
        <p class="eyebrow">Vue 3 · Quản lý sản phẩm</p>
        <!-- Tiêu đề chính của trang. -->
        <h1>Quản lý sản phẩm</h1>
        <!-- Mô tả ngắn về các chức năng chính của trang. -->
        <p class="subtitle">Tìm kiếm, lọc, sắp xếp và theo dõi giá trị tồn kho trong một màn hình.</p>
      </div>
      <!-- Thẻ hiển thị trạng thái trang; giá trị được cập nhật trong hook mounted(). -->
      <div class="status-card">
        <!-- Nhãn mô tả ý nghĩa của trạng thái. -->
        <span class="status-label">Trạng thái trang</span>
        <!-- Giá trị trạng thái; thêm lớp is-ready khi trang đã sẵn sàng để đổi màu. -->
        <span
          class="status-value"
          :class="{ 'is-ready': pageStatus === 'Sẵn sàng' }"
          data-testid="page-status"
        >
          <!-- Hiển thị trạng thái hiện tại của trang. -->
          {{ pageStatus }}
        </span>
      </div>
    </header>

    <!-- Khu vực thống kê, luôn được tính lại từ danh sách sản phẩm đang hiển thị. -->
    <section class="stats-grid" aria-label="Thống kê sản phẩm">
      <!-- Thẻ thống kê số sản phẩm đang hiển thị trên tổng số sản phẩm. -->
      <article class="stat-card">
        <!-- Nhãn của chỉ số thứ nhất. -->
        <span class="stat-label">Sản phẩm đang hiển thị</span>
        <!-- Giá trị dạng "đang hiển thị / tổng số", được cung cấp bởi computed. -->
        <strong class="stat-value" data-testid="visible-count">
          {{ visibleProductCount }} / {{ totalProductCount }}
        </strong>
        <!-- Ghi chú giải thích chỉ số luôn khớp với bộ lọc hiện tại. -->
        <span class="stat-note">Khớp với bộ lọc hiện tại</span>
      </article>
      <!-- Thẻ thống kê tổng giá trị tồn kho của các sản phẩm đang hiển thị. -->
      <article class="stat-card">
        <!-- Nhãn của chỉ số thứ hai. -->
        <span class="stat-label">Tổng giá trị tồn kho</span>
        <!-- Tổng giá trị tồn kho đã được định dạng theo tiền tệ Việt Nam. -->
        <strong class="stat-value" data-testid="total-inventory-value">
          {{ formatCurrency(totalInventoryValue) }}
        </strong>
        <!-- Ghi chú giải thích công thức tính giá trị tồn kho. -->
        <span class="stat-note">Giá × số lượng tồn của sản phẩm đang hiển thị</span>
      </article>
    </section>

    <!-- Khu vực chứa các bộ điều khiển tìm kiếm, lọc và sắp xếp. -->
    <section class="panel">
      <!-- Tiêu đề của khu vực bộ lọc. -->
      <h2 class="panel-title">Bộ lọc &amp; tìm kiếm</h2>
      <!-- Lưới ba cột chứa các trường điều khiển; tự co lại trên màn hình nhỏ. -->
      <div class="filter-grid">
        <!-- Trường tìm kiếm theo tên sản phẩm. -->
        <div class="field">
          <!-- Nhãn liên kết với ô tìm kiếm thông qua thuộc tính for/id. -->
          <label for="search-keyword">Tìm theo tên</label>
          <!-- Ô nhập từ khóa, ràng buộc hai chiều với biến searchKeyword. -->
          <input
            id="search-keyword"
            v-model="searchKeyword"
            type="search"
            placeholder="Nhập tên sản phẩm..."
            autocomplete="off"
            data-testid="search-input"
          />
          <!-- Thông báo từ watch chỉ xuất hiện khi từ khóa có đúng một ký tự. -->
          <p
            v-if="searchHint"
            class="field-hint"
            role="status"
            aria-live="polite"
            data-testid="search-hint"
          >
            <!-- Hiển thị nội dung cảnh báo do watch cập nhật. -->
            {{ searchHint }}
          </p>
        </div>
        <!-- Trường lọc theo danh mục sản phẩm. -->
        <div class="field">
          <!-- Nhãn liên kết với ô chọn danh mục. -->
          <label for="category-filter">Danh mục</label>
          <!-- Ô chọn danh mục; giá trị rỗng đại diện cho "tất cả danh mục". -->
          <select id="category-filter" v-model="selectedCategory" data-testid="category-filter">
            <!-- Tùy chọn mặc định không lọc theo danh mục. -->
            <option value="">Tất cả danh mục</option>
            <!-- Sinh các tùy chọn danh mục từ danh sách CATEGORIES cố định. -->
            <option v-for="category in categories" :key="category" :value="category">
              <!-- Hiển thị tên danh mục. -->
              {{ category }}
            </option>
          </select>
        </div>
        <!-- Trường chọn kiểu sắp xếp theo giá. -->
        <div class="field">
          <!-- Nhãn liên kết với ô chọn kiểu sắp xếp. -->
          <label for="sort-order">Sắp xếp theo giá</label>
          <!-- Ô chọn kiểu sắp xếp, ràng buộc với biến sortOrder. -->
          <select id="sort-order" v-model="sortOrder" data-testid="sort-select">
            <!-- Tùy chọn giữ nguyên thứ tự dữ liệu sau khi lọc. -->
            <option :value="sortOrders.NONE">Không sắp xếp</option>
            <!-- Tùy chọn sắp xếp giá tăng dần. -->
            <option :value="sortOrders.ASC">Giá tăng dần</option>
            <!-- Tùy chọn sắp xếp giá giảm dần. -->
            <option :value="sortOrders.DESC">Giá giảm dần</option>
          </select>
        </div>
      </div>
    </section>

    <!-- Khu vực hiển thị bảng danh sách sản phẩm. -->
    <section class="panel">
      <!-- Thanh tiêu đề của bảng kèm số lượng kết quả hiện tại. -->
      <div class="panel-heading">
        <!-- Tiêu đề của bảng sản phẩm. -->
        <h2 class="panel-title">Danh sách sản phẩm</h2>
        <!-- Số kết quả đang hiển thị, được tính lại tự động khi bộ lọc thay đổi. -->
        <span class="result-count" aria-live="polite">{{ visibleProductCount }} kết quả</span>
      </div>
      <!-- Lớp bao giúp bảng có thể cuộn ngang trên màn hình nhỏ. -->
      <div class="table-wrapper">
        <!-- Bảng hiển thị đầy đủ thông tin sản phẩm. -->
        <table>
          <!-- Chú thích ẩn dành cho trình đọc màn hình. -->
          <caption class="sr-only">Danh sách sản phẩm với giá, tồn kho và trạng thái</caption>
          <!-- Phần tiêu đề của các cột trong bảng. -->
          <thead>
            <!-- Một hàng chứa toàn bộ tiêu đề cột. -->
            <tr>
              <!-- Cột mã sản phẩm. -->
              <th scope="col">ID</th>
              <!-- Cột tên sản phẩm. -->
              <th scope="col">Tên sản phẩm</th>
              <!-- Cột danh mục sản phẩm. -->
              <th scope="col">Danh mục</th>
              <!-- Cột giá bán. -->
              <th scope="col" class="align-right">Giá</th>
              <!-- Cột số lượng tồn kho. -->
              <th scope="col" class="align-right">Tồn kho</th>
              <!-- Cột trạng thái còn hàng hoặc hết hàng. -->
              <th scope="col">Trạng thái</th>
              <!-- Cột giá trị tồn kho của từng sản phẩm. -->
              <th scope="col" class="align-right">Giá trị tồn kho</th>
              <!-- Cột chứa nút thao tác xóa. -->
              <th scope="col" class="align-right">Thao tác</th>
            </tr>
          </thead>
          <!-- Phần thân bảng, nơi mỗi sản phẩm được render thành một hàng. -->
          <tbody>
            <!-- Duyệt danh sách đã tìm kiếm, lọc và sắp xếp để tạo các hàng sản phẩm. -->
            <tr
              v-for="product in visibleProducts"
              :key="product.id"
              data-testid="product-row"
            >
              <!-- Hiển thị id của sản phẩm. -->
              <td class="muted">{{ product.id }}</td>
              <!-- Hiển thị tên sản phẩm với kiểu chữ nổi bật. -->
              <td class="product-name">{{ product.name }}</td>
              <!-- Hiển thị danh mục dưới dạng nhãn nhỏ. -->
              <td>
                <!-- Nhãn danh mục có nền màu nhẹ để dễ phân biệt. -->
                <span class="category-pill">{{ product.category }}</span>
              </td>
              <!-- Hiển thị giá đã định dạng tiền tệ. -->
              <td class="align-right">{{ formatCurrency(product.price) }}</td>
              <!-- Hiển thị số lượng tồn kho hiện tại. -->
              <td class="align-right">{{ product.stock }}</td>
              <!-- Hiển thị trạng thái dựa trên số lượng tồn kho. -->
              <td>
                <!-- Nhãn "Còn hàng" có màu xanh, "Hết hàng" có màu đỏ. -->
                <span
                  class="stock-badge"
                  :class="product.stock > 0 ? 'in-stock' : 'out-stock'"
                  data-testid="stock-badge"
                >
                  <!-- Toán tử ba ngôi quyết định nội dung nhãn theo số lượng tồn. -->
                  {{ product.stock > 0 ? 'Còn hàng' : 'Hết hàng' }}
                </span>
              </td>
              <!-- Hiển thị giá trị tồn kho của riêng sản phẩm: giá nhân số lượng tồn. -->
              <td class="align-right">{{ formatCurrency(getInventoryValue(product)) }}</td>
              <!-- Cột chứa nút xóa sản phẩm. -->
              <td class="align-right">
                <!-- Nút xóa gọi phương thức xác nhận trước khi thực hiện xóa. -->
                <button
                  type="button"
                  class="danger-button"
                  :aria-label="`Xóa sản phẩm ${product.name}`"
                  data-testid="delete-button"
                  @click="handleDelete(product)"
                >
                  <!-- Nhãn hiển thị trên nút xóa. -->
                  Xóa
                </button>
              </td>
            </tr>
            <!-- Hàng thông báo khi không có sản phẩm nào khớp với bộ lọc hiện tại. -->
            <tr v-if="visibleProducts.length === 0">
              <!-- Ô trải rộng toàn bộ tám cột để hiển thị trạng thái rỗng. -->
              <td colspan="8" class="empty-state" data-testid="empty-state">
                <!-- Nội dung thông báo khi danh sách kết quả rỗng. -->
                Không có sản phẩm nào phù hợp với bộ lọc hiện tại.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Khu vực biểu mẫu thêm sản phẩm mới. -->
    <section class="panel">
      <!-- Tiêu đề của biểu mẫu. -->
      <h2 class="panel-title">Thêm sản phẩm mới</h2>
      <!-- Biểu mẫu chặn hành vi submit mặc định và gọi phương thức kiểm tra/thêm sản phẩm. -->
      <form novalidate data-testid="add-form" @submit.prevent="handleAddProduct">
        <!-- Lưới bốn cột cho các trường nhập liệu; tự co lại trên màn hình nhỏ. -->
        <div class="form-grid">
          <!-- Trường nhập tên sản phẩm. -->
          <div class="field">
            <!-- Nhãn liên kết với ô nhập tên. -->
            <label for="new-name">Tên sản phẩm</label>
            <!-- Ô nhập tên, ràng buộc hai chiều với newProduct.name. -->
            <input
              id="new-name"
              v-model="newProduct.name"
              type="text"
              placeholder="VD: Tai nghe Bluetooth"
              autocomplete="off"
              data-testid="name-input"
              :aria-invalid="formErrors.name ? 'true' : 'false'"
              :aria-describedby="formErrors.name ? 'new-name-error' : undefined"
              @input="clearFieldError('name')"
            />
            <!-- Hiển thị lỗi tên nếu có. -->
            <p v-if="formErrors.name" id="new-name-error" class="field-error" data-testid="name-error">
              <!-- Nội dung lỗi do validateNewProduct trả về. -->
              {{ formErrors.name }}
            </p>
          </div>
          <!-- Trường nhập giá sản phẩm. -->
          <div class="field">
            <!-- Nhãn liên kết với ô nhập giá. -->
            <label for="new-price">Giá (₫)</label>
            <!-- Ô nhập giá kiểu số; v-model.number tự chuyển chuỗi hợp lệ thành số. -->
            <input
              id="new-price"
              v-model.number="newProduct.price"
              type="number"
              min="0"
              step="any"
              placeholder="VD: 890000"
              data-testid="price-input"
              :aria-invalid="formErrors.price ? 'true' : 'false'"
              :aria-describedby="formErrors.price ? 'new-price-error' : undefined"
              @input="clearFieldError('price')"
            />
            <!-- Hiển thị lỗi giá nếu có. -->
            <p v-if="formErrors.price" id="new-price-error" class="field-error" data-testid="price-error">
              <!-- Nội dung lỗi giá do validateNewProduct trả về. -->
              {{ formErrors.price }}
            </p>
          </div>
          <!-- Trường chọn danh mục cho sản phẩm mới. -->
          <div class="field">
            <!-- Nhãn liên kết với ô chọn danh mục. -->
            <label for="new-category">Danh mục</label>
            <!-- Ô chọn danh mục; mặc định rỗng để buộc người dùng chọn. -->
            <select
              id="new-category"
              v-model="newProduct.category"
              data-testid="category-select"
              :aria-invalid="formErrors.category ? 'true' : 'false'"
              :aria-describedby="formErrors.category ? 'new-category-error' : undefined"
              @change="clearFieldError('category')"
            >
              <!-- Tùy chọn hướng dẫn người dùng chọn danh mục. -->
              <option value="" disabled>Chọn danh mục</option>
              <!-- Sinh danh sách danh mục hợp lệ từ CATEGORIES. -->
              <option v-for="category in categories" :key="category" :value="category">
                <!-- Hiển thị tên danh mục. -->
                {{ category }}
              </option>
            </select>
            <!-- Hiển thị lỗi danh mục nếu có. -->
            <p
              v-if="formErrors.category"
              id="new-category-error"
              class="field-error"
              data-testid="category-error"
            >
              <!-- Nội dung lỗi danh mục do validateNewProduct trả về. -->
              {{ formErrors.category }}
            </p>
          </div>
          <!-- Trường nhập số lượng tồn kho. -->
          <div class="field">
            <!-- Nhãn liên kết với ô nhập số lượng tồn. -->
            <label for="new-stock">Số lượng tồn</label>
            <!-- Ô nhập số nguyên không âm; v-model.number chuyển giá trị hợp lệ thành số. -->
            <input
              id="new-stock"
              v-model.number="newProduct.stock"
              type="number"
              min="0"
              step="1"
              placeholder="VD: 10"
              data-testid="stock-input"
              :aria-invalid="formErrors.stock ? 'true' : 'false'"
              :aria-describedby="formErrors.stock ? 'new-stock-error' : undefined"
              @input="clearFieldError('stock')"
            />
            <!-- Hiển thị lỗi số lượng tồn nếu có. -->
            <p v-if="formErrors.stock" id="new-stock-error" class="field-error" data-testid="stock-error">
              <!-- Nội dung lỗi số lượng tồn do validateNewProduct trả về. -->
              {{ formErrors.stock }}
            </p>
          </div>
        </div>
        <!-- Khu vực chứa nút thêm và thông báo phản hồi sau khi thao tác. -->
        <div class="form-actions">
          <!-- Nút gửi biểu mẫu để thêm sản phẩm mới. -->
          <button type="submit" class="primary-button" data-testid="submit-button">
            <!-- Nhãn hiển thị trên nút thêm. -->
            Thêm sản phẩm
          </button>
          <!-- Thông báo thành công hoặc lỗi chung sau khi submit. -->
          <p
            v-if="feedback.message"
            class="feedback"
            :class="feedback.type"
            role="status"
            aria-live="polite"
            data-testid="feedback"
          >
            <!-- Nội dung phản hồi do phương thức xử lý cập nhật. -->
            {{ feedback.message }}
          </p>
        </div>
      </form>
    </section>
  </div>
</template>

<script>
// Nhập danh sách danh mục và dữ liệu sản phẩm mẫu từ tệp dữ liệu riêng.
import { CATEGORIES, INITIAL_PRODUCTS } from './data/products.js';
// Nhập các hàm tiện ích và hằng số sắp xếp từ tệp xử lý sản phẩm.
import {
  // Hằng số quy định các kiểu sắp xếp hợp lệ.
  SORT_ORDER,
  // Hàm định dạng tiền tệ; đổi tên khi import để tránh trùng với method của component.
  formatCurrency as formatCurrencyValue,
  // Hàm tính giá trị tồn kho; đổi tên khi import để tránh trùng với method của component.
  getInventoryValue as calculateInventoryValue,
  // Hàm sinh id mới dựa trên danh sách sản phẩm hiện tại.
  getNextProductId,
  // Hàm tính tổng giá trị tồn kho của một danh sách sản phẩm.
  getTotalInventoryValue,
  // Hàm áp dụng đồng thời tìm kiếm, lọc và sắp xếp mà không sửa mảng gốc.
  getVisibleProducts,
} from './utils/productUtils.js';
// Nhập hàm kiểm tra dữ liệu biểu mẫu thêm sản phẩm.
import { validateNewProduct } from './utils/productValidation.js';

/**
 * Tạo một object rỗng cho biểu mẫu thêm sản phẩm.
 * - Dùng hàm thay vì dùng chung một object để mỗi lần reset có object mới hoàn toàn.
 * @returns {{ name: string, price: string, category: string, stock: string }} Dữ liệu biểu mẫu mặc định.
 */
function createEmptyProductForm() {
  // Trả về object mới với các trường đều rỗng để buộc người dùng nhập dữ liệu.
  return {
    // Tên sản phẩm mặc định rỗng.
    name: '',
    // Giá sản phẩm mặc định rỗng (khác 0 để phân biệt với giá trị hợp lệ).
    price: '',
    // Danh mục mặc định rỗng để buộc người dùng chọn.
    category: '',
    // Số lượng tồn mặc định rỗng (0 vẫn là giá trị hợp lệ khi người dùng nhập).
    stock: '',
  };
}

// Xuất component gốc của ứng dụng dưới dạng Options API để thể hiện rõ computed, watch và mounted.
export default {
  // Tên component, hữu ích khi gỡ lỗi bằng Vue Devtools.
  name: 'App',
  // Hàm data trả về toàn bộ trạng thái phản ứng (reactive state) của component.
  data() {
    // Trả về object chứa dữ liệu và trạng thái giao diện ban đầu.
    return {
      // Danh sách danh mục cố định dùng cho bộ lọc và biểu mẫu thêm sản phẩm.
      categories: CATEGORIES,
      // Sao chép từng sản phẩm mẫu thành object mới để có thể thêm/xóa mà không sửa dữ liệu gốc đã đóng băng.
      products: INITIAL_PRODUCTS.map((product) => ({ ...product })),
      // Từ khóa tìm kiếm theo tên sản phẩm.
      searchKeyword: '',
      // Thông báo do watch cập nhật khi từ khóa có đúng một ký tự.
      searchHint: '',
      // Danh mục đang được chọn để lọc; chuỗi rỗng nghĩa là tất cả danh mục.
      selectedCategory: '',
      // Kiểu sắp xếp hiện tại; mặc định không sắp xếp để giữ thứ tự ban đầu.
      sortOrder: SORT_ORDER.NONE,
      // Dữ liệu biểu mẫu thêm sản phẩm mới.
      newProduct: createEmptyProductForm(),
      // Object chứa thông báo lỗi theo từng trường của biểu mẫu.
      formErrors: {},
      // Thông báo phản hồi chung sau khi thêm hoặc xóa sản phẩm.
      feedback: { type: '', message: '' },
      // Trạng thái trang ban đầu; sẽ được đổi thành "Sẵn sàng" trong mounted().
      pageStatus: 'Đang khởi tạo...',
      // Cung cấp các hằng số sắp xếp cho template mà không cần import trực tiếp trong template.
      sortOrders: SORT_ORDER,
    };
  },
  // Khu vực khai báo các thuộc tính tính toán (computed) tự động cập nhật khi dữ liệu thay đổi.
  computed: {
    /**
     * Danh sách sản phẩm đang hiển thị sau khi áp dụng đồng thời tìm kiếm, lọc và sắp xếp.
     * - Đây là nguồn dữ liệu duy nhất cho bảng và các chỉ số thống kê.
     * - Hàm tiện ích luôn trả về mảng mới nên không làm thay đổi thứ tự mảng products gốc.
     * @returns {Array<object>} Mảng sản phẩm đã xử lý.
     */
    visibleProducts() {
      // Gọi hàm tiện ích với toàn bộ trạng thái lọc hiện tại để tạo danh sách hiển thị.
      return getVisibleProducts(this.products, {
        // Truyền từ khóa tìm kiếm hiện tại.
        keyword: this.searchKeyword,
        // Truyền danh mục đang lọc hiện tại.
        category: this.selectedCategory,
        // Truyền kiểu sắp xếp hiện tại.
        sortOrder: this.sortOrder,
      });
    },
    /**
     * Số sản phẩm đang hiển thị, luôn khớp với bộ lọc hiện tại.
     * @returns {number} Độ dài của danh sách sản phẩm đang hiển thị.
     */
    visibleProductCount() {
      // Đếm số phần tử của mảng sản phẩm đã qua tìm kiếm, lọc và sắp xếp.
      return this.visibleProducts.length;
    },
    /**
     * Tổng số sản phẩm hiện có trong dữ liệu gốc.
     * @returns {number} Độ dài của mảng sản phẩm gốc.
     */
    totalProductCount() {
      // Đếm toàn bộ sản phẩm hiện có, không phụ thuộc bộ lọc.
      return this.products.length;
    },
    /**
     * Tổng giá trị tồn kho của các sản phẩm đang hiển thị.
     * - Mỗi sản phẩm đóng góp giá trị bằng giá nhân số lượng tồn.
     * @returns {number} Tổng giá trị tồn kho của danh sách đang hiển thị.
     */
    totalInventoryValue() {
      // Tính tổng từ chính danh sách đang hiển thị để thống kê luôn khớp với bảng.
      return getTotalInventoryValue(this.visibleProducts);
    },
  },
  // Khu vực theo dõi thay đổi của dữ liệu để cập nhật thông báo phụ.
  watch: {
    /**
     * Theo dõi từ khóa tìm kiếm.
     * - Khi từ khóa (sau khi cắt khoảng trắng) có đúng 1 ký tự: hiển thị thông báo.
     * - Khi từ khóa rỗng hoặc có từ 2 ký tự trở lên: xóa thông báo.
     * @param {string} newKeyword Giá trị mới của từ khóa.
     */
    searchKeyword(newKeyword) {
      // Chuẩn hóa từ khóa: chỉ xử lý khi là chuỗi, sau đó cắt khoảng trắng hai đầu.
      const normalizedKeyword = typeof newKeyword === 'string' ? newKeyword.trim() : '';
      // Nếu đúng một ký tự thì gán thông báo; mọi trường hợp khác gán chuỗi rỗng để xóa thông báo.
      this.searchHint = normalizedKeyword.length === 1
        ? 'Từ khóa chỉ có 1 ký tự. Hãy nhập thêm để tìm chính xác hơn.'
        : '';
    },
  },
  // Hook vòng đời chạy sau khi component đã được gắn vào DOM.
  mounted() {
    // Đặt trạng thái trang thành "Sẵn sàng" đúng theo yêu cầu đề bài.
    this.pageStatus = 'Sẵn sàng';
  },
  // Khu vực khai báo các phương thức xử lý sự kiện và định dạng dữ liệu.
  methods: {
    /**
     * Định dạng một giá trị thành chuỗi tiền tệ Việt Nam.
     * @param {unknown} value Giá trị cần định dạng.
     * @returns {string} Chuỗi tiền tệ đã định dạng.
     */
    formatCurrency(value) {
      // Gọi hàm tiện ích đã import để giữ logic định dạng tập trung ở một nơi.
      return formatCurrencyValue(value);
    },
    /**
     * Tính giá trị tồn kho của một sản phẩm.
     * @param {object} product Sản phẩm cần tính.
     * @returns {number} Giá trị tồn kho bằng giá nhân số lượng tồn.
     */
    getInventoryValue(product) {
      // Gọi hàm tiện ích đã import để đảm bảo công thức nhất quán với phần thống kê.
      return calculateInventoryValue(product);
    },
    /**
     * Xử lý sự kiện submit biểu mẫu thêm sản phẩm.
     * - Kiểm tra toàn bộ dữ liệu trước khi thêm.
     * - Chỉ thêm sản phẩm khi không còn lỗi.
     * - Sau khi thêm thành công thì làm mới biểu mẫu và hiển thị thông báo.
     */
    handleAddProduct() {
      // Kiểm tra dữ liệu biểu mẫu theo các quy tắc của đề bài và nhận về object lỗi.
      const validationErrors = validateNewProduct(this.newProduct, this.categories);
      // Lưu object lỗi vào trạng thái để giao diện hiển thị thông báo dưới từng trường.
      this.formErrors = validationErrors;
      // Nếu object lỗi có ít nhất một khóa thì dữ liệu chưa hợp lệ.
      if (Object.keys(validationErrors).length > 0) {
        // Hiển thị thông báo lỗi chung để người dùng biết cần sửa các trường được đánh dấu.
        this.feedback = {
          // Loại phản hồi là lỗi để giao diện dùng màu đỏ.
          type: 'error',
          // Nội dung hướng dẫn người dùng kiểm tra lại biểu mẫu.
          message: 'Vui lòng sửa các lỗi được đánh dấu trước khi thêm sản phẩm.',
        };
        // Dừng xử lý, không thêm sản phẩm khi dữ liệu không hợp lệ.
        return;
      }
      // Tạo object sản phẩm mới với id sinh tự động và dữ liệu đã được kiểm tra hợp lệ.
      const productToAdd = {
        // Sinh id mới lớn hơn mọi id hiện có để tránh trùng lặp.
        id: getNextProductId(this.products),
        // Lưu tên đã cắt khoảng trắng hai đầu để dữ liệu sạch và nhất quán.
        name: this.newProduct.name.trim(),
        // Chuyển giá sang kiểu số (đã được kiểm tra là số hữu hạn lớn hơn 0).
        price: Number(this.newProduct.price),
        // Lưu danh mục đã chọn (đã được kiểm tra nằm trong danh sách hợp lệ).
        category: this.newProduct.category,
        // Chuyển số lượng tồn sang kiểu số nguyên không âm (đã được kiểm tra hợp lệ).
        stock: Number(this.newProduct.stock),
      };
      // Thêm sản phẩm mới vào mảng dữ liệu gốc của component; các computed sẽ tự cập nhật.
      this.products.push(productToAdd);
      // Hiển thị thông báo thêm thành công kèm tên sản phẩm vừa tạo.
      this.feedback = {
        // Loại phản hồi là thành công để giao diện dùng màu xanh.
        type: 'success',
        // Nội dung xác nhận sản phẩm đã được thêm.
        message: `Đã thêm sản phẩm "${productToAdd.name}" thành công.`,
      };
      // Làm mới biểu mẫu và xóa các lỗi cũ sau khi thêm thành công.
      this.resetNewProductForm();
    },
    /**
     * Xử lý yêu cầu xóa sản phẩm.
     * - Luôn hỏi xác nhận trước khi xóa.
     * - Chỉ xóa khi người dùng đồng ý và sản phẩm vẫn tồn tại trong dữ liệu.
     * @param {object} product Sản phẩm cần xóa.
     */
    handleDelete(product) {
      // Nếu không nhận được sản phẩm hợp lệ thì báo lỗi và dừng để tránh truy cập thuộc tính của null/undefined.
      if (!product || product.id === undefined || product.id === null) {
        // Hiển thị thông báo lỗi an toàn cho trường hợp dữ liệu không hợp lệ.
        this.feedback = {
          // Loại phản hồi là lỗi.
          type: 'error',
          // Nội dung thông báo lỗi.
          message: 'Không xác định được sản phẩm cần xóa.',
        };
        // Dừng xử lý vì không có sản phẩm hợp lệ để xóa.
        return;
      }
      // Lấy tên sản phẩm an toàn để hiển thị trong hộp thoại xác nhận.
      const productName = product && typeof product.name === 'string' ? product.name : 'sản phẩm này';
      // Hiển thị hộp thoại xác nhận của trình duyệt và nhận kết quả đồng ý/hủy.
      const isConfirmed = window.confirm(
        `Bạn có chắc muốn xóa "${productName}"? Hành động này không thể hoàn tác.`,
      );
      // Nếu người dùng không đồng ý thì dừng xử lý và giữ nguyên dữ liệu.
      if (!isConfirmed) {
        // Thoát khỏi phương thức mà không thay đổi bất kỳ sản phẩm nào.
        return;
      }
      // Tìm vị trí của sản phẩm trong mảng hiện tại theo id để tránh xóa nhầm.
      const productIndex = this.products.findIndex((item) => item.id === product.id);
      // Nếu không tìm thấy sản phẩm (ví dụ dữ liệu đã thay đổi trước đó) thì báo lỗi.
      if (productIndex === -1) {
        // Hiển thị thông báo không tìm thấy sản phẩm cần xóa.
        this.feedback = {
          // Loại phản hồi là lỗi.
          type: 'error',
          // Nội dung thông báo lỗi.
          message: 'Không tìm thấy sản phẩm cần xóa. Danh sách có thể đã thay đổi.',
        };
        // Dừng xử lý vì không có gì để xóa.
        return;
      }
      // Xóa đúng một phần tử tại vị trí tìm được và lấy ra sản phẩm vừa bị xóa.
      const [removedProduct] = this.products.splice(productIndex, 1);
      // Hiển thị thông báo xóa thành công kèm tên sản phẩm đã xóa.
      this.feedback = {
        // Loại phản hồi là thành công để giao diện dùng màu xanh.
        type: 'success',
        // Nội dung xác nhận sản phẩm đã bị xóa khỏi danh sách.
        message: `Đã xóa sản phẩm "${removedProduct.name}".`,
      };
    },
    /**
     * Xóa thông báo lỗi của một trường khi người dùng bắt đầu sửa trường đó.
     * @param {string} field Tên trường cần xóa lỗi (name, price, category, stock).
     */
    clearFieldError(field) {
      // Nếu trường này không có lỗi thì không cần làm gì, tránh tạo object mới không cần thiết.
      if (!Object.prototype.hasOwnProperty.call(this.formErrors, field)) {
        // Thoát sớm để giữ nguyên trạng thái hiện tại.
        return;
      }
      // Sao chép object lỗi sang object mới để thay đổi vẫn đảm bảo tính phản ứng của Vue.
      const nextErrors = { ...this.formErrors };
      // Xóa khóa lỗi tương ứng với trường vừa được người dùng chỉnh sửa.
      delete nextErrors[field];
      // Gán object lỗi mới vào trạng thái để giao diện cập nhật ngay.
      this.formErrors = nextErrors;
    },
    /**
     * Làm mới biểu mẫu thêm sản phẩm sau khi thêm thành công.
     */
    resetNewProductForm() {
      // Tạo object biểu mẫu rỗng mới để không giữ lại dữ liệu của lần nhập trước.
      this.newProduct = createEmptyProductForm();
      // Xóa toàn bộ thông báo lỗi còn sót lại của biểu mẫu.
      this.formErrors = {};
    },
  },
};
</script>
