// Nhập các hàm kiểm thử của Vitest để mô tả và chạy các bài kiểm tra.
import { describe, it, expect } from 'vitest';
// Nhập hàm kiểm tra dữ liệu biểu mẫu thêm sản phẩm từ mã nguồn chính.
import { validateNewProduct } from '../src/utils/productValidation.js';

// Khai báo danh sách danh mục hợp lệ dùng cho các bài kiểm tra.
const categories = ['Điện tử', 'Gia dụng', 'Thời trang'];

// Tạo một biểu mẫu hợp lệ mặc định để tái sử dụng và chỉ thay đổi trường cần kiểm tra.
function createValidForm() {
  // Trả về object có đầy đủ bốn trường hợp lệ.
  return {
    // Tên sản phẩm không rỗng.
    name: 'Sản phẩm hợp lệ',
    // Giá là số dương.
    price: 150000,
    // Danh mục nằm trong danh sách hợp lệ.
    category: 'Điện tử',
    // Số lượng tồn là số nguyên không âm.
    stock: 10,
  };
}

// Nhóm các bài kiểm tra cho hàm validateNewProduct.
describe('validateNewProduct', () => {
  // Kiểm tra một biểu mẫu hoàn toàn hợp lệ.
  it('trả về object rỗng khi dữ liệu hợp lệ', () => {
    // Gọi hàm với biểu mẫu hợp lệ mặc định.
    const errors = validateNewProduct(createValidForm(), categories);
    // Không được có bất kỳ lỗi nào.
    expect(errors).toEqual({});
  });

  // Kiểm tra quy tắc tên không được rỗng.
  it('báo lỗi khi tên rỗng', () => {
    // Tạo biểu mẫu hợp lệ rồi thay tên bằng chuỗi rỗng.
    const form = { ...createValidForm(), name: '' };
    // Kiểm tra kết quả trả về.
    const errors = validateNewProduct(form, categories);
    // Phải có lỗi ở trường name.
    expect(errors.name).toBe('Tên sản phẩm không được để trống.');
  });

  // Kiểm tra tên chỉ chứa khoảng trắng cũng bị coi là rỗng.
  it('báo lỗi khi tên chỉ gồm khoảng trắng', () => {
    // Tạo biểu mẫu có tên chỉ chứa khoảng trắng.
    const form = { ...createValidForm(), name: '    ' };
    // Kiểm tra kết quả trả về.
    const errors = validateNewProduct(form, categories);
    // Phải có lỗi ở trường name.
    expect(errors.name).toBe('Tên sản phẩm không được để trống.');
  });

  // Kiểm tra quy tắc giá phải lớn hơn 0.
  it('báo lỗi khi giá bằng 0', () => {
    // Tạo biểu mẫu có giá bằng 0.
    const form = { ...createValidForm(), price: 0 };
    // Kiểm tra kết quả trả về.
    const errors = validateNewProduct(form, categories);
    // Phải có lỗi ở trường price.
    expect(errors.price).toBe('Giá phải là số lớn hơn 0.');
  });

  // Kiểm tra giá âm không hợp lệ.
  it('báo lỗi khi giá âm', () => {
    // Tạo biểu mẫu có giá âm.
    const form = { ...createValidForm(), price: -1000 };
    // Kiểm tra kết quả trả về.
    const errors = validateNewProduct(form, categories);
    // Phải có lỗi ở trường price.
    expect(errors.price).toBe('Giá phải là số lớn hơn 0.');
  });

  // Kiểm tra giá rỗng hoặc không phải số.
  it('báo lỗi khi giá rỗng hoặc không phải số', () => {
    // Kiểm tra giá là chuỗi rỗng.
    expect(validateNewProduct({ ...createValidForm(), price: '' }, categories).price)
      .toBe('Giá phải là số lớn hơn 0.');
    // Kiểm tra giá là chuỗi không biểu diễn số.
    expect(validateNewProduct({ ...createValidForm(), price: 'abc' }, categories).price)
      .toBe('Giá phải là số lớn hơn 0.');
    // Kiểm tra giá là null.
    expect(validateNewProduct({ ...createValidForm(), price: null }, categories).price)
      .toBe('Giá phải là số lớn hơn 0.');
  });

  // Kiểm tra giá dạng chuỗi số vẫn được chấp nhận.
  it('chấp nhận giá dạng chuỗi số hợp lệ', () => {
    // v-model.number có thể trả về số, nhưng hàm vẫn phải xử lý được chuỗi số.
    const form = { ...createValidForm(), price: '150000' };
    // Không được có lỗi giá.
    expect(validateNewProduct(form, categories).price).toBeUndefined();
  });

  // Kiểm tra quy tắc số lượng tồn phải là số nguyên không âm.
  it('báo lỗi khi số lượng tồn âm', () => {
    // Tạo biểu mẫu có số lượng tồn âm.
    const form = { ...createValidForm(), stock: -1 };
    // Kiểm tra kết quả trả về.
    const errors = validateNewProduct(form, categories);
    // Phải có lỗi ở trường stock.
    expect(errors.stock).toBe('Số lượng tồn phải là số nguyên không âm.');
  });

  // Kiểm tra số lượng tồn có phần thập phân không hợp lệ.
  it('báo lỗi khi số lượng tồn không phải số nguyên', () => {
    // Tạo biểu mẫu có số lượng tồn là 1.5.
    const form = { ...createValidForm(), stock: 1.5 };
    // Kiểm tra kết quả trả về.
    const errors = validateNewProduct(form, categories);
    // Phải có lỗi ở trường stock.
    expect(errors.stock).toBe('Số lượng tồn phải là số nguyên không âm.');
  });

  // Kiểm tra số lượng tồn rỗng hoặc không phải số.
  it('báo lỗi khi số lượng tồn rỗng hoặc không phải số', () => {
    // Kiểm tra số lượng tồn là chuỗi rỗng.
    expect(validateNewProduct({ ...createValidForm(), stock: '' }, categories).stock)
      .toBe('Số lượng tồn phải là số nguyên không âm.');
    // Kiểm tra số lượng tồn là chuỗi không biểu diễn số.
    expect(validateNewProduct({ ...createValidForm(), stock: 'abc' }, categories).stock)
      .toBe('Số lượng tồn phải là số nguyên không âm.');
    // Kiểm tra số lượng tồn là null.
    expect(validateNewProduct({ ...createValidForm(), stock: null }, categories).stock)
      .toBe('Số lượng tồn phải là số nguyên không âm.');
  });

  // Kiểm tra số lượng tồn bằng 0 vẫn hợp lệ.
  it('chấp nhận số lượng tồn bằng 0', () => {
    // Tạo biểu mẫu có số lượng tồn bằng 0.
    const form = { ...createValidForm(), stock: 0 };
    // Không được có lỗi ở trường stock.
    expect(validateNewProduct(form, categories).stock).toBeUndefined();
  });

  // Kiểm tra số lượng tồn dạng chuỗi số nguyên vẫn được chấp nhận.
  it('chấp nhận số lượng tồn dạng chuỗi số nguyên', () => {
    // Tạo biểu mẫu có số lượng tồn là chuỗi "12".
    const form = { ...createValidForm(), stock: '12' };
    // Không được có lỗi ở trường stock.
    expect(validateNewProduct(form, categories).stock).toBeUndefined();
  });

  // Kiểm tra danh mục phải nằm trong danh sách hợp lệ.
  it('báo lỗi khi danh mục rỗng hoặc không hợp lệ', () => {
    // Kiểm tra danh mục rỗng.
    expect(validateNewProduct({ ...createValidForm(), category: '' }, categories).category)
      .toBe('Vui lòng chọn một danh mục hợp lệ.');
    // Kiểm tra danh mục không có trong danh sách.
    expect(validateNewProduct({ ...createValidForm(), category: 'Không tồn tại' }, categories).category)
      .toBe('Vui lòng chọn một danh mục hợp lệ.');
  });

  // Kiểm tra trường hợp nhiều trường cùng lỗi.
  it('trả về đầy đủ lỗi khi nhiều trường không hợp lệ', () => {
    // Tạo biểu mẫu sai ở cả bốn trường.
    const form = {
      // Tên rỗng.
      name: '   ',
      // Giá bằng 0.
      price: 0,
      // Danh mục rỗng.
      category: '',
      // Số lượng tồn âm.
      stock: -5,
    };
    // Gọi hàm kiểm tra.
    const errors = validateNewProduct(form, categories);
    // Phải có đủ bốn khóa lỗi tương ứng bốn trường.
    expect(Object.keys(errors).sort()).toEqual(['category', 'name', 'price', 'stock']);
  });
});
