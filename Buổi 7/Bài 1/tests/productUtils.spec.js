// Nhập các hàm kiểm thử của Vitest để mô tả và chạy các bài kiểm tra.
import { describe, it, expect } from 'vitest';
// Nhập các hàm tiện ích cần kiểm tra từ mã nguồn chính.
import {
  // Hằng số quy định các kiểu sắp xếp.
  SORT_ORDER,
  // Hàm lấy danh sách sản phẩm sau khi tìm kiếm, lọc và sắp xếp.
  getVisibleProducts,
  // Hàm tính giá trị tồn kho của một sản phẩm.
  getInventoryValue,
  // Hàm tính tổng giá trị tồn kho của nhiều sản phẩm.
  getTotalInventoryValue,
  // Hàm sinh id sản phẩm mới.
  getNextProductId,
} from '../src/utils/productUtils.js';

// Tạo dữ liệu mẫu cố định cho các bài kiểm tra tiện ích.
const sampleProducts = [
  // Sản phẩm 1 thuộc Điện tử, giá 890.000, còn 15 sản phẩm.
  { id: 1, name: 'Tai nghe Bluetooth', price: 890000, category: 'Điện tử', stock: 15 },
  // Sản phẩm 2 thuộc Điện tử, giá 1.250.000, đã hết hàng.
  { id: 2, name: 'Bàn phím cơ', price: 1250000, category: 'Điện tử', stock: 0 },
  // Sản phẩm 3 thuộc Gia dụng, giá 1.790.000, còn 4 sản phẩm.
  { id: 3, name: 'Nồi chiên không dầu', price: 1790000, category: 'Gia dụng', stock: 4 },
  // Sản phẩm 4 thuộc Thời trang, giá 250.000, còn 30 sản phẩm.
  { id: 4, name: 'Áo thun cotton', price: 250000, category: 'Thời trang', stock: 30 },
];

// Nhóm các bài kiểm tra cho hàm getVisibleProducts.
describe('getVisibleProducts', () => {
  // Kiểm tra tìm kiếm theo tên không phân biệt chữ hoa và chữ thường.
  it('tìm theo tên không phân biệt chữ hoa chữ thường', () => {
    // Gọi hàm với từ khóa viết hoa hoàn toàn.
    const result = getVisibleProducts(sampleProducts, {
      // Từ khóa viết hoa để kiểm tra việc chuẩn hóa chữ thường.
      keyword: 'TAI NGHE',
      // Không lọc theo danh mục.
      category: '',
      // Không sắp xếp.
      sortOrder: SORT_ORDER.NONE,
    });
    // Kết quả phải chỉ chứa sản phẩm "Tai nghe Bluetooth" có id 1.
    expect(result.map((product) => product.id)).toEqual([1]);
  });

  // Kiểm tra lọc theo danh mục.
  it('lọc đúng theo danh mục', () => {
    // Gọi hàm với danh mục Điện tử.
    const result = getVisibleProducts(sampleProducts, {
      // Không nhập từ khóa.
      keyword: '',
      // Chỉ lấy sản phẩm thuộc Điện tử.
      category: 'Điện tử',
      // Không sắp xếp.
      sortOrder: SORT_ORDER.NONE,
    });
    // Hai sản phẩm Điện tử phải được giữ lại theo đúng thứ tự ban đầu.
    expect(result.map((product) => product.id)).toEqual([1, 2]);
  });

  // Kiểm tra sắp xếp giá tăng dần.
  it('sắp xếp giá tăng dần', () => {
    // Gọi hàm với kiểu sắp xếp tăng dần.
    const result = getVisibleProducts(sampleProducts, {
      // Không nhập từ khóa.
      keyword: '',
      // Không lọc danh mục.
      category: '',
      // Sắp xếp giá tăng dần.
      sortOrder: SORT_ORDER.ASC,
    });
    // Thứ tự giá tăng dần phải là 250.000, 890.000, 1.250.000, 1.790.000.
    expect(result.map((product) => product.id)).toEqual([4, 1, 2, 3]);
  });

  // Kiểm tra sắp xếp giá giảm dần.
  it('sắp xếp giá giảm dần', () => {
    // Gọi hàm với kiểu sắp xếp giảm dần.
    const result = getVisibleProducts(sampleProducts, {
      // Không nhập từ khóa.
      keyword: '',
      // Không lọc danh mục.
      category: '',
      // Sắp xếp giá giảm dần.
      sortOrder: SORT_ORDER.DESC,
    });
    // Thứ tự giá giảm dần phải là 1.790.000, 1.250.000, 890.000, 250.000.
    expect(result.map((product) => product.id)).toEqual([3, 2, 1, 4]);
  });

  // Kiểm tra tìm kiếm, lọc và sắp xếp hoạt động kết hợp.
  it('kết hợp tìm kiếm, lọc danh mục và sắp xếp', () => {
    // Gọi hàm với từ khóa "b", danh mục Điện tử và sắp xếp tăng dần.
    const result = getVisibleProducts(sampleProducts, {
      // Chữ "b" xuất hiện trong "Bluetooth" và "Bàn phím".
      keyword: 'b',
      // Chỉ xét các sản phẩm thuộc Điện tử.
      category: 'Điện tử',
      // Sắp xếp theo giá tăng dần.
      sortOrder: SORT_ORDER.ASC,
    });
    // Sau khi lọc còn hai sản phẩm; sắp xếp tăng dần cho id 1 rồi id 2.
    expect(result.map((product) => product.id)).toEqual([1, 2]);
  });

  // Kiểm tra hàm không làm thay đổi thứ tự mảng dữ liệu gốc.
  it('không làm thay đổi thứ tự mảng dữ liệu gốc khi sắp xếp', () => {
    // Ghi lại thứ tự id ban đầu của mảng gốc.
    const originalIds = sampleProducts.map((product) => product.id);
    // Gọi hàm sắp xếp giảm dần để tạo ra thứ tự khác với dữ liệu gốc.
    const result = getVisibleProducts(sampleProducts, {
      // Không nhập từ khóa.
      keyword: '',
      // Không lọc danh mục.
      category: '',
      // Sắp xếp giá giảm dần.
      sortOrder: SORT_ORDER.DESC,
    });
    // Kết quả phải được sắp xếp giảm dần đúng như mong đợi.
    expect(result.map((product) => product.id)).toEqual([3, 2, 1, 4]);
    // Mảng gốc phải giữ nguyên thứ tự ban đầu.
    expect(sampleProducts.map((product) => product.id)).toEqual(originalIds);
    // Kết quả phải là một mảng mới, không phải cùng tham chiếu với mảng gốc.
    expect(result).not.toBe(sampleProducts);
  });

  // Kiểm tra trường hợp không có sản phẩm nào khớp bộ lọc.
  it('trả về mảng rỗng khi không có sản phẩm phù hợp', () => {
    // Gọi hàm với từ khóa chắc chắn không xuất hiện trong dữ liệu mẫu.
    const result = getVisibleProducts(sampleProducts, {
      // Từ khóa không tồn tại trong bất kỳ tên sản phẩm nào.
      keyword: 'không-có-sản-phẩm-này',
      // Không lọc danh mục.
      category: '',
      // Không sắp xếp.
      sortOrder: SORT_ORDER.NONE,
    });
    // Kết quả phải là mảng rỗng để giao diện hiển thị trạng thái không có dữ liệu.
    expect(result).toEqual([]);
  });

  // Kiểm tra hàm vẫn trả về mảng mới ngay cả khi không sắp xếp.
  it('trả về bản sao mảng ngay cả khi không sắp xếp', () => {
    // Gọi hàm không sắp xếp và không lọc.
    const result = getVisibleProducts(sampleProducts, {
      // Không nhập từ khóa.
      keyword: '',
      // Không lọc danh mục.
      category: '',
      // Không sắp xếp.
      sortOrder: SORT_ORDER.NONE,
    });
    // Thứ tự phần tử phải giống mảng gốc.
    expect(result.map((product) => product.id)).toEqual([1, 2, 3, 4]);
    // Nhưng tham chiếu mảng phải khác để tránh vô tình sửa dữ liệu gốc.
    expect(result).not.toBe(sampleProducts);
  });

  // Kiểm tra hàm xử lý an toàn khi không truyền hoặc truyền null cho tùy chọn.
  it('xử lý an toàn khi không truyền tùy chọn hoặc truyền null', () => {
    // Không truyền tham số tùy chọn phải trả về toàn bộ sản phẩm theo thứ tự gốc.
    expect(getVisibleProducts(sampleProducts).map((product) => product.id)).toEqual([1, 2, 3, 4]);
    // Truyền null cũng phải được xử lý như không có tùy chọn thay vì gây lỗi.
    expect(getVisibleProducts(sampleProducts, null).map((product) => product.id)).toEqual([1, 2, 3, 4]);
  });
});

// Nhóm các bài kiểm tra cho các hàm tính toán tồn kho.
describe('tính giá trị tồn kho', () => {
  // Kiểm tra công thức giá nhân số lượng tồn của một sản phẩm.
  it('tính đúng giá trị tồn kho của một sản phẩm', () => {
    // Sản phẩm có giá 890.000 và tồn 15 phải có giá trị 13.350.000.
    expect(getInventoryValue({ price: 890000, stock: 15 })).toBe(13350000);
  });

  // Kiểm tra dữ liệu không hợp lệ không tạo ra NaN.
  it('trả về 0 khi dữ liệu sản phẩm không hợp lệ', () => {
    // Giá không phải số nên không thể tính toán.
    expect(getInventoryValue({ price: 'không-phải-số', stock: 5 })).toBe(0);
    // Số lượng tồn không phải số nên không thể tính toán.
    expect(getInventoryValue({ price: 1000, stock: undefined })).toBe(0);
    // Không truyền sản phẩm cũng phải trả về 0 thay vì gây lỗi.
    expect(getInventoryValue(null)).toBe(0);
  });

  // Kiểm tra tổng giá trị tồn kho của cả danh sách.
  it('tính đúng tổng giá trị tồn kho của danh sách', () => {
    // Tổng = 890.000×15 + 1.250.000×0 + 1.790.000×4 + 250.000×30 = 28.010.000.
    expect(getTotalInventoryValue(sampleProducts)).toBe(28010000);
  });

  // Kiểm tra tổng của mảng rỗng.
  it('trả về 0 cho mảng rỗng', () => {
    // Mảng rỗng không có sản phẩm nào nên tổng phải bằng 0.
    expect(getTotalInventoryValue([])).toBe(0);
  });
});

// Nhóm các bài kiểm tra cho hàm sinh id sản phẩm mới.
describe('getNextProductId', () => {
  // Kiểm tra trường hợp chưa có sản phẩm nào.
  it('trả về 1 khi danh sách rỗng', () => {
    // Danh sách rỗng thì id đầu tiên phải là 1.
    expect(getNextProductId([])).toBe(1);
  });

  // Kiểm tra trường hợp đã có sản phẩm.
  it('trả về id lớn hơn id lớn nhất hiện có', () => {
    // Id lớn nhất trong dữ liệu mẫu là 4 nên id mới phải là 5.
    expect(getNextProductId(sampleProducts)).toBe(5);
  });

  // Kiểm tra trường hợp id không liên tục.
  it('xử lý đúng khi id không liên tục', () => {
    // Danh sách có id 1 và 7 thì id mới phải là 8.
    expect(getNextProductId([{ id: 1 }, { id: 7 }])).toBe(8);
  });
});
