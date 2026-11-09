/**
 * Kiểm tra một giá trị có được coi là rỗng hay không.
 * - null và undefined được coi là rỗng.
 * - Chuỗi chỉ chứa khoảng trắng cũng được coi là rỗng.
 * - Số 0 không phải là rỗng vì đây là giá trị hợp lệ với số lượng tồn.
 * @param {unknown} value Giá trị cần kiểm tra.
 * @returns {boolean} true nếu giá trị rỗng, ngược lại false.
 */
function isBlank(value) {
  // null hoặc undefined đều không có dữ liệu để kiểm tra.
  if (value === null || value === undefined) {
    // Trả về true vì giá trị được coi là rỗng.
    return true;
  }
  // Nếu là chuỗi thì kiểm tra sau khi cắt khoảng trắng hai đầu.
  if (typeof value === 'string') {
    // Chuỗi rỗng hoặc chỉ gồm khoảng trắng được coi là rỗng.
    return value.trim() === '';
  }
  // Các kiểu dữ liệu khác (bao gồm số 0) không được coi là rỗng.
  return false;
}

/**
 * Chuyển một giá trị đầu vào thành số một cách an toàn.
 * - Số được giữ nguyên.
 * - Chuỗi không rỗng được chuyển bằng Number().
 * - Các trường hợp còn lại trả về NaN để phần kiểm tra phía sau đánh dấu là không hợp lệ.
 * @param {unknown} value Giá trị cần chuyển đổi.
 * @returns {number} Số sau khi chuyển đổi hoặc NaN nếu không hợp lệ.
 */
function toNumber(value) {
  // Nếu đã là kiểu số thì trả về nguyên giá trị để tránh chuyển đổi thừa.
  if (typeof value === 'number') {
    // Trả về chính giá trị số ban đầu.
    return value;
  }
  // Nếu là chuỗi có nội dung thì mới thử chuyển thành số.
  if (typeof value === 'string' && value.trim() !== '') {
    // Number() trả về NaN nếu chuỗi không phải biểu diễn số hợp lệ.
    return Number(value);
  }
  // Các trường hợp còn lại không thể chuyển thành số hợp lệ.
  return Number.NaN;
}

/**
 * Kiểm tra dữ liệu của biểu mẫu thêm sản phẩm mới.
 * Quy tắc theo yêu cầu:
 * - Tên sản phẩm không được rỗng (kể cả toàn khoảng trắng).
 * - Giá phải là số lớn hơn 0.
 * - Số lượng tồn phải là số nguyên không âm.
 * - Danh mục phải nằm trong danh sách danh mục hợp lệ.
 * @param {{ name?: unknown, price?: unknown, category?: unknown, stock?: unknown }} form Dữ liệu biểu mẫu.
 * @param {Array<string>} categories Danh sách danh mục hợp lệ.
 * @returns {Record<string, string>} Object chứa thông báo lỗi theo từng trường; rỗng nếu dữ liệu hợp lệ.
 */
export function validateNewProduct(form, categories) {
  // Khởi tạo object lỗi rỗng; mỗi khóa tương ứng với một trường không hợp lệ.
  const errors = {};
  // Đảm bảo form luôn là object để truy cập thuộc tính an toàn.
  const safeForm = form && typeof form === 'object' ? form : {};
  // Đảm bảo danh sách danh mục luôn là mảng để dùng phương thức includes().
  const safeCategories = Array.isArray(categories) ? categories : [];
  // Lấy tên đã cắt khoảng trắng nếu là chuỗi; nếu không phải chuỗi thì coi như rỗng.
  const normalizedName = typeof safeForm.name === 'string' ? safeForm.name.trim() : '';
  // Nếu tên sau khi cắt khoảng trắng rỗng thì ghi nhận lỗi cho trường tên.
  if (normalizedName === '') {
    // Gán thông báo lỗi rõ ràng để hiển thị ngay dưới ô nhập tên.
    errors.name = 'Tên sản phẩm không được để trống.';
  }
  // Chuyển giá trị giá sang số để kiểm tra tính hợp lệ.
  const price = toNumber(safeForm.price);
  // Giá không được rỗng, phải là số hữu hạn và phải lớn hơn 0.
  if (isBlank(safeForm.price) || !Number.isFinite(price) || price <= 0) {
    // Gán thông báo lỗi cho trường giá.
    errors.price = 'Giá phải là số lớn hơn 0.';
  }
  // Chuyển giá trị số lượng tồn sang số để kiểm tra tính hợp lệ.
  const stock = toNumber(safeForm.stock);
  // Số lượng tồn không được rỗng, phải là số nguyên và không được âm (0 là hợp lệ).
  if (isBlank(safeForm.stock) || !Number.isInteger(stock) || stock < 0) {
    // Gán thông báo lỗi cho trường số lượng tồn.
    errors.stock = 'Số lượng tồn phải là số nguyên không âm.';
  }
  // Danh mục phải là chuỗi và phải nằm trong danh sách danh mục hợp lệ.
  if (typeof safeForm.category !== 'string' || !safeCategories.includes(safeForm.category)) {
    // Gán thông báo lỗi cho trường danh mục.
    errors.category = 'Vui lòng chọn một danh mục hợp lệ.';
  }
  // Trả về object lỗi; nếu không có lỗi thì object rỗng.
  return errors;
}
