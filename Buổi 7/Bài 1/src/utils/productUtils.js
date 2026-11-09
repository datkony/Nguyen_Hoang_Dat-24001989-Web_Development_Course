/**
 * Các giá trị hợp lệ cho kiểu sắp xếp theo giá.
 * - NONE: giữ nguyên thứ tự của dữ liệu sau khi tìm kiếm và lọc.
 * - ASC: sắp xếp giá tăng dần.
 * - DESC: sắp xếp giá giảm dần.
 * - Dùng Object.freeze để tránh việc mã nguồn vô tình thay đổi các hằng số này.
 */
export const SORT_ORDER = Object.freeze({
  // Giá trị đại diện cho việc không sắp xếp.
  NONE: 'none',
  // Giá trị đại diện cho việc sắp xếp tăng dần.
  ASC: 'asc',
  // Giá trị đại diện cho việc sắp xếp giảm dần.
  DESC: 'desc',
});

/**
 * Bộ định dạng tiền tệ dùng chung.
 * - Ngôn ngữ "vi-VN" giúp phân tách hàng nghìn bằng dấu chấm theo cách hiển thị quen thuộc.
 * - maximumFractionDigits: 2 cho phép hiển thị tối đa hai chữ số thập phân nếu giá có phần lẻ.
 * - Tạo một lần ở cấp module để không phải khởi tạo lại nhiều lần khi render bảng.
 */
const CURRENCY_FORMATTER = new Intl.NumberFormat('vi-VN', {
  // Cho phép tối đa hai chữ số sau dấu phẩy thập phân.
  maximumFractionDigits: 2,
});

/**
 * Chuẩn hóa một giá trị thành chuỗi chữ thường và đã cắt khoảng trắng hai đầu.
 * - Hàm này phục vụ tìm kiếm không phân biệt chữ hoa/chữ thường.
 * - Nếu giá trị không phải chuỗi (ví dụ null hoặc undefined) thì trả về chuỗi rỗng.
 * @param {unknown} value Giá trị cần chuẩn hóa.
 * @returns {string} Chuỗi đã được trim và chuyển thành chữ thường.
 */
export function normalizeText(value) {
  // Chỉ chấp nhận kiểu chuỗi; mọi kiểu khác được quy về chuỗi rỗng để tránh lỗi.
  const text = typeof value === 'string' ? value : '';
  // Cắt khoảng trắng hai đầu trước, sau đó chuyển toàn bộ về chữ thường theo ngôn ngữ tiếng Việt.
  return text.trim().toLocaleLowerCase('vi-VN');
}

/**
 * Tính giá trị tồn kho của một sản phẩm theo công thức: giá nhân số lượng tồn.
 * - Hàm có kiểm tra dữ liệu đầu vào để tránh trả về NaN nếu dữ liệu bị lỗi.
 * @param {{ price?: number, stock?: number } | null | undefined} product Sản phẩm cần tính.
 * @returns {number} Giá trị tồn kho; trả về 0 nếu dữ liệu không hợp lệ.
 */
export function getInventoryValue(product) {
  // Chuyển giá về kiểu số; dùng optional chaining để không lỗi khi product là null/undefined.
  const price = Number(product?.price);
  // Chuyển số lượng tồn về kiểu số; dùng optional chaining để an toàn với dữ liệu thiếu.
  const stock = Number(product?.stock);
  // Nếu giá hoặc số lượng tồn không phải số hữu hạn thì không thể tính toán chính xác.
  if (!Number.isFinite(price) || !Number.isFinite(stock)) {
    // Trả về 0 để tổng giá trị tồn kho không bị NaN.
    return 0;
  }
  // Trả về tích của giá và số lượng tồn theo đúng yêu cầu đề bài.
  return price * stock;
}

/**
 * Tính tổng giá trị tồn kho của một danh sách sản phẩm.
 * @param {Array<object>} products Danh sách sản phẩm cần tính tổng.
 * @returns {number} Tổng giá trị tồn kho; trả về 0 nếu đầu vào không phải mảng.
 */
export function getTotalInventoryValue(products) {
  // Nếu đầu vào không phải mảng thì không có gì để cộng, trả về 0.
  if (!Array.isArray(products)) {
    // Trả về 0 để hàm luôn trả về kiểu số an toàn.
    return 0;
  }
  // Duyệt qua từng sản phẩm, cộng dồn giá trị tồn kho vào biến tích lũy total.
  return products.reduce((total, product) => total + getInventoryValue(product), 0);
}

/**
 * Định dạng một giá trị số thành chuỗi tiền tệ Việt Nam kèm ký hiệu đồng.
 * @param {unknown} value Giá trị cần định dạng.
 * @returns {string} Chuỗi đã định dạng, ví dụ "1.250.000 ₫".
 */
export function formatCurrency(value) {
  // Chuyển giá trị đầu vào về kiểu số để xử lý thống nhất.
  const numericValue = Number(value);
  // Nếu giá trị không hữu hạn (NaN, Infinity) thì dùng 0 để tránh hiển thị lỗi.
  const safeValue = Number.isFinite(numericValue) ? numericValue : 0;
  // Định dạng phần số rồi nối thêm khoảng trắng và ký hiệu đồng Việt Nam.
  return `${CURRENCY_FORMATTER.format(safeValue)} ₫`;
}

/**
 * Lấy danh sách sản phẩm sau khi áp dụng đồng thời tìm kiếm, lọc danh mục và sắp xếp.
 * - Không bao giờ sắp xếp trực tiếp trên mảng gốc.
 * - filter() đã tạo mảng mới, sau đó sao chép thêm một lần nữa trước khi sort() để bảo vệ dữ liệu gốc tuyệt đối.
 * @param {Array<object>} products Mảng sản phẩm gốc.
 * @param {{ keyword?: string, category?: string, sortOrder?: string }} options Tùy chọn tìm kiếm, lọc và sắp xếp.
 * @returns {Array<object>} Mảng mới đã được tìm kiếm, lọc và sắp xếp.
 */
export function getVisibleProducts(products, options = {}) {
  // Đảm bảo đầu vào luôn là mảng để tránh lỗi khi dữ liệu chưa tải xong.
  const safeProducts = Array.isArray(products) ? products : [];
  // Đảm bảo tùy chọn luôn là object; nếu người gọi truyền null thì dùng object rỗng thay thế.
  const safeOptions = options && typeof options === 'object' ? options : {};
  // Lấy các tùy chọn với giá trị mặc định an toàn nếu người gọi không truyền đủ.
  const { keyword = '', category = '', sortOrder = SORT_ORDER.NONE } = safeOptions;
  // Chuẩn hóa từ khóa một lần để dùng cho mọi sản phẩm, giúp tìm kiếm không phân biệt hoa thường.
  const normalizedKeyword = normalizeText(keyword);
  // Tạo mảng mới chỉ chứa các sản phẩm thỏa mãn đồng thời điều kiện tìm kiếm và điều kiện danh mục.
  const filteredProducts = safeProducts.filter((product) => {
    // Chuẩn hóa tên sản phẩm theo cùng quy tắc với từ khóa.
    const productName = normalizeText(product?.name);
    // Từ khóa rỗng nghĩa là không lọc theo tên; ngược lại kiểm tra tên có chứa từ khóa hay không.
    const matchesKeyword = normalizedKeyword === '' || productName.includes(normalizedKeyword);
    // Danh mục rỗng nghĩa là chọn tất cả; ngược lại so khớp chính xác danh mục.
    const matchesCategory = category === '' || product?.category === category;
    // Chỉ giữ sản phẩm khi thỏa mãn cả hai điều kiện cùng lúc.
    return matchesKeyword && matchesCategory;
  });
  // Sao chép mảng đã lọc sang một mảng mới trước khi sắp xếp để không ảnh hưởng dữ liệu gốc.
  const sortedProducts = [...filteredProducts];
  // Nếu người dùng chọn sắp xếp giá tăng dần thì dùng comparator tăng dần.
  if (sortOrder === SORT_ORDER.ASC) {
    // Sắp xếp mảng sao chép theo giá từ nhỏ đến lớn.
    sortedProducts.sort((firstProduct, secondProduct) => {
      // Tính hiệu giá để xác định thứ tự; hiệu âm nghĩa là sản phẩm thứ nhất rẻ hơn.
      const priceDifference = firstProduct.price - secondProduct.price;
      // Nếu giá khác nhau thì dùng hiệu giá làm kết quả so sánh.
      if (priceDifference !== 0) {
        // Trả về hiệu giá để sort() đưa sản phẩm rẻ hơn lên trước.
        return priceDifference;
      }
      // Nếu giá bằng nhau thì so sánh id để thứ tự luôn ổn định và dễ kiểm thử.
      return firstProduct.id - secondProduct.id;
    });
  // Nếu người dùng chọn sắp xếp giá giảm dần thì dùng comparator giảm dần.
  } else if (sortOrder === SORT_ORDER.DESC) {
    // Sắp xếp mảng sao chép theo giá từ lớn đến nhỏ.
    sortedProducts.sort((firstProduct, secondProduct) => {
      // Tính hiệu giá theo chiều ngược lại để sản phẩm đắt hơn đứng trước.
      const priceDifference = secondProduct.price - firstProduct.price;
      // Nếu giá khác nhau thì dùng hiệu giá làm kết quả so sánh.
      if (priceDifference !== 0) {
        // Trả về hiệu giá để sort() đưa sản phẩm đắt hơn lên trước.
        return priceDifference;
      }
      // Nếu giá bằng nhau thì vẫn sắp xếp id tăng dần để kết quả ổn định.
      return firstProduct.id - secondProduct.id;
    });
  }
  // Trả về mảng mới đã qua tìm kiếm, lọc và (nếu có) sắp xếp.
  return sortedProducts;
}

/**
 * Sinh id mới cho sản phẩm dựa trên id lớn nhất hiện có.
 * - Cách này tránh trùng id sau khi người dùng thêm hoặc xóa sản phẩm.
 * @param {Array<object>} products Danh sách sản phẩm hiện tại.
 * @returns {number} Id mới là số nguyên dương lớn hơn mọi id đang có.
 */
export function getNextProductId(products) {
  // Đảm bảo đầu vào là mảng để hàm reduce bên dưới luôn hoạt động an toàn.
  const safeProducts = Array.isArray(products) ? products : [];
  // Tìm id lớn nhất bằng cách duyệt toàn bộ sản phẩm và giữ lại giá trị lớn hơn.
  const highestId = safeProducts.reduce((maxId, product) => {
    // Chuyển id hiện tại về kiểu số để so sánh thống nhất.
    const currentId = Number(product?.id);
    // Nếu id không phải số hữu hạn thì bỏ qua sản phẩm đó.
    if (!Number.isFinite(currentId)) {
      // Giữ nguyên id lớn nhất đã tìm được.
      return maxId;
    }
    // So sánh và trả về id lớn hơn giữa id hiện tại và id lớn nhất đã biết.
    return Math.max(maxId, currentId);
  }, 0);
  // Làm tròn xuống id lớn nhất rồi cộng 1 để tạo id nguyên mới.
  return Math.floor(highestId) + 1;
}
