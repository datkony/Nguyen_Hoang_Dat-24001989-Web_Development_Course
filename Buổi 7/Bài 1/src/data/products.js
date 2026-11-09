/**
 * Danh sách danh mục cố định của cửa hàng.
 * - Dùng Object.freeze để ngăn mã nguồn vô tình thêm, xóa hoặc sửa danh mục.
 * - Danh sách này được dùng chung cho bộ lọc và ô chọn danh mục khi thêm sản phẩm.
 */
export const CATEGORIES = Object.freeze([
  // Danh mục thứ nhất: các thiết bị điện tử.
  'Điện tử',
  // Danh mục thứ hai: các đồ dùng gia đình.
  'Gia dụng',
  // Danh mục thứ ba: quần áo và phụ kiện thời trang.
  'Thời trang',
]);

/**
 * Dữ liệu mẫu ban đầu của ứng dụng.
 * - Có 9 sản phẩm, nhiều hơn yêu cầu tối thiểu 6 sản phẩm.
 * - Có đủ 3 danh mục, mỗi danh mục 3 sản phẩm.
 * - Có cả sản phẩm còn hàng (stock > 0) và hết hàng (stock = 0).
 * - Mỗi sản phẩm gồm đúng các trường: id, name, price, category, stock.
 * - Mảng và từng object đều được đóng băng để bảo vệ dữ liệu gốc khỏi bị thay đổi.
 */
export const INITIAL_PRODUCTS = Object.freeze([
  // Sản phẩm 1: tai nghe không dây, còn hàng.
  Object.freeze({ id: 1, name: 'Tai nghe Bluetooth', price: 890000, category: 'Điện tử', stock: 15 }),
  // Sản phẩm 2: bàn phím cơ, đã hết hàng để kiểm tra nhãn "Hết hàng".
  Object.freeze({ id: 2, name: 'Bàn phím cơ', price: 1250000, category: 'Điện tử', stock: 0 }),
  // Sản phẩm 3: màn hình máy tính, còn hàng.
  Object.freeze({ id: 3, name: 'Màn hình 24 inch', price: 3200000, category: 'Điện tử', stock: 7 }),
  // Sản phẩm 4: nồi chiên không dầu, còn hàng.
  Object.freeze({ id: 4, name: 'Nồi chiên không dầu', price: 1790000, category: 'Gia dụng', stock: 4 }),
  // Sản phẩm 5: máy xay sinh tố, đã hết hàng để kiểm tra nhãn "Hết hàng".
  Object.freeze({ id: 5, name: 'Máy xay sinh tố', price: 950000, category: 'Gia dụng', stock: 0 }),
  // Sản phẩm 6: bộ chén sứ, còn hàng với số lượng lớn.
  Object.freeze({ id: 6, name: 'Bộ chén sứ cao cấp', price: 450000, category: 'Gia dụng', stock: 20 }),
  // Sản phẩm 7: áo thun cotton, còn hàng với số lượng lớn.
  Object.freeze({ id: 7, name: 'Áo thun cotton', price: 250000, category: 'Thời trang', stock: 30 }),
  // Sản phẩm 8: quần jeans, còn hàng.
  Object.freeze({ id: 8, name: 'Quần jeans', price: 550000, category: 'Thời trang', stock: 12 }),
  // Sản phẩm 9: giày sneaker, đã hết hàng để kiểm tra nhãn "Hết hàng".
  Object.freeze({ id: 9, name: 'Giày sneaker', price: 1200000, category: 'Thời trang', stock: 0 }),
]);
