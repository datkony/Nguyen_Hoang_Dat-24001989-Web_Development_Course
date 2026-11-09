// Nhập các hàm kiểm thử của Vitest, bao gồm vi để tạo mock cho window.confirm.
import { describe, it, expect, vi } from 'vitest';
// Nhập hàm mount của Vue Test Utils để gắn component vào DOM ảo.
import { mount } from '@vue/test-utils';
// Nhập nextTick để chờ Vue cập nhật DOM sau khi trạng thái thay đổi.
import { nextTick } from 'vue';
// Nhập component gốc cần kiểm thử.
import App from '../src/App.vue';
// Nhập dữ liệu sản phẩm mẫu để tính toán kỳ vọng.
import { INITIAL_PRODUCTS } from '../src/data/products.js';
// Nhập các hàm tiện ích để tính giá trị kỳ vọng trong bài kiểm thử.
import { formatCurrency, getTotalInventoryValue } from '../src/utils/productUtils.js';

// Tạo component mới cho mỗi bài kiểm thử để trạng thái không bị ảnh hưởng lẫn nhau.
function mountApp() {
  // Gắn component App vào một DOM ảo và trả về wrapper để truy vấn phần tử.
  return mount(App);
}

// Nhóm các bài kiểm tra cho yêu cầu hiển thị ban đầu và hook mounted.
describe('hiển thị ban đầu', () => {
  // Kiểm tra trạng thái trang được đặt thành "Sẵn sàng" sau khi mounted chạy.
  it('đặt trạng thái trang thành "Sẵn sàng" trong mounted()', async () => {
    // Gắn component để hook mounted được thực thi.
    const wrapper = mountApp();
    // Chờ Vue hoàn tất việc cập nhật DOM sau khi mounted() thay đổi trạng thái.
    await nextTick();
    // Đọc nội dung trạng thái trang từ DOM.
    expect(wrapper.get('[data-testid="page-status"]').text()).toBe('Sẵn sàng');
  });

  // Kiểm tra bảng hiển thị đủ sản phẩm mẫu và thống kê ban đầu chính xác.
  it('hiển thị đủ sản phẩm và thống kê ban đầu', () => {
    // Gắn component.
    const wrapper = mountApp();
    // Số hàng sản phẩm phải bằng số sản phẩm mẫu.
    expect(wrapper.findAll('[data-testid="product-row"]')).toHaveLength(INITIAL_PRODUCTS.length);
    // Số đang hiển thị trên tổng số phải là 9 / 9 với dữ liệu mẫu.
    expect(wrapper.get('[data-testid="visible-count"]').text())
      .toBe(`${INITIAL_PRODUCTS.length} / ${INITIAL_PRODUCTS.length}`);
    // Tổng giá trị tồn kho phải khớp với tổng tính từ dữ liệu mẫu.
    expect(wrapper.get('[data-testid="total-inventory-value"]').text())
      .toBe(formatCurrency(getTotalInventoryValue(INITIAL_PRODUCTS)));
  });

  // Kiểm tra nhãn trạng thái còn hàng và hết hàng có màu khác nhau.
  it('hiển thị nhãn "Còn hàng" và "Hết hàng" với lớp màu khác nhau', () => {
    // Gắn component.
    const wrapper = mountApp();
    // Lấy toàn bộ nhãn trạng thái trong bảng.
    const badges = wrapper.findAll('[data-testid="stock-badge"]');
    // Gom nội dung chữ của các nhãn để kiểm tra sự tồn tại của cả hai trạng thái.
    const badgeTexts = badges.map((badge) => badge.text());
    // Dữ liệu mẫu có sản phẩm tồn kho dương nên phải có nhãn "Còn hàng".
    expect(badgeTexts).toContain('Còn hàng');
    // Dữ liệu mẫu có sản phẩm tồn kho bằng 0 nên phải có nhãn "Hết hàng".
    expect(badgeTexts).toContain('Hết hàng');
    // Tìm nhãn "Còn hàng" đầu tiên để kiểm tra lớp CSS màu xanh.
    const inStockBadge = badges.find((badge) => badge.text() === 'Còn hàng');
    // Nhãn còn hàng phải dùng lớp in-stock.
    expect(inStockBadge.classes()).toContain('in-stock');
    // Tìm nhãn "Hết hàng" đầu tiên để kiểm tra lớp CSS màu đỏ.
    const outStockBadge = badges.find((badge) => badge.text() === 'Hết hàng');
    // Nhãn hết hàng phải dùng lớp out-stock.
    expect(outStockBadge.classes()).toContain('out-stock');
  });
});

// Nhóm các bài kiểm tra cho tìm kiếm và watch.
describe('tìm kiếm và watch từ khóa', () => {
  // Kiểm tra tìm kiếm không phân biệt chữ hoa chữ thường.
  it('tìm kiếm theo tên không phân biệt chữ hoa chữ thường', async () => {
    // Gắn component.
    const wrapper = mountApp();
    // Lấy ô tìm kiếm.
    const searchInput = wrapper.get('[data-testid="search-input"]');
    // Nhập từ khóa viết hoa để kiểm tra việc chuẩn hóa.
    await searchInput.setValue('TAI');
    // Chỉ sản phẩm "Tai nghe Bluetooth" phải còn lại.
    const rows = wrapper.findAll('[data-testid="product-row"]');
    // Kiểm tra số hàng hiển thị.
    expect(rows).toHaveLength(1);
    // Kiểm tra đúng sản phẩm được tìm thấy.
    expect(rows[0].text()).toContain('Tai nghe Bluetooth');
    // Thống kê phải cập nhật thành 1 / 9.
    expect(wrapper.get('[data-testid="visible-count"]').text()).toBe('1 / 9');
  });

  // Kiểm tra watch hiện thông báo khi từ khóa có đúng một ký tự và xóa khi rỗng hoặc từ hai ký tự.
  it('hiện thông báo khi từ khóa có đúng một ký tự và xóa khi rỗng hoặc từ hai ký tự', async () => {
    // Gắn component.
    const wrapper = mountApp();
    // Lấy ô tìm kiếm.
    const searchInput = wrapper.get('[data-testid="search-input"]');
    // Nhập đúng một ký tự.
    await searchInput.setValue('t');
    // Thông báo của watch phải xuất hiện.
    expect(wrapper.find('[data-testid="search-hint"]').exists()).toBe(true);
    // Nhập thêm ký tự thứ hai.
    await searchInput.setValue('ta');
    // Thông báo phải biến mất khi từ khóa có từ hai ký tự.
    expect(wrapper.find('[data-testid="search-hint"]').exists()).toBe(false);
    // Xóa toàn bộ từ khóa.
    await searchInput.setValue('');
    // Thông báo phải tiếp tục không xuất hiện khi từ khóa rỗng.
    expect(wrapper.find('[data-testid="search-hint"]').exists()).toBe(false);
  });
});

// Nhóm các bài kiểm tra cho lọc, sắp xếp kết hợp và thống kê.
describe('lọc, sắp xếp và thống kê', () => {
  // Kiểm tra lọc theo danh mục và thống kê khớp với bộ lọc.
  it('lọc theo danh mục và cập nhật thống kê tương ứng', async () => {
    // Gắn component.
    const wrapper = mountApp();
    // Chọn danh mục Điện tử.
    await wrapper.get('[data-testid="category-filter"]').setValue('Điện tử');
    // Tính danh sách kỳ vọng từ dữ liệu mẫu.
    const expectedProducts = INITIAL_PRODUCTS.filter((product) => product.category === 'Điện tử');
    // Số hàng phải khớp số sản phẩm thuộc danh mục đã chọn.
    expect(wrapper.findAll('[data-testid="product-row"]')).toHaveLength(expectedProducts.length);
    // Số đang hiển thị trên tổng số phải là 3 / 9.
    expect(wrapper.get('[data-testid="visible-count"]').text()).toBe('3 / 9');
    // Tổng giá trị tồn kho phải chỉ tính các sản phẩm đang hiển thị.
    expect(wrapper.get('[data-testid="total-inventory-value"]').text())
      .toBe(formatCurrency(getTotalInventoryValue(expectedProducts)));
  });

  // Kiểm tra trạng thái không có sản phẩm phù hợp vẫn hiển thị thống kê đúng.
  it('hiển thị 0 / tổng và 0 ₫ khi không có sản phẩm phù hợp', async () => {
    // Gắn component.
    const wrapper = mountApp();
    // Nhập từ khóa chắc chắn không khớp sản phẩm nào.
    await wrapper.get('[data-testid="search-input"]').setValue('không-có-sản-phẩm-này');
    // Không được còn hàng sản phẩm nào.
    expect(wrapper.findAll('[data-testid="product-row"]')).toHaveLength(0);
    // Phải hiển thị thông báo danh sách rỗng.
    expect(wrapper.find('[data-testid="empty-state"]').exists()).toBe(true);
    // Số đang hiển thị phải là 0 / 9.
    expect(wrapper.get('[data-testid="visible-count"]').text()).toBe('0 / 9');
    // Tổng giá trị tồn kho phải là 0 ₫.
    expect(wrapper.get('[data-testid="total-inventory-value"]').text()).toBe(formatCurrency(0));
  });

  // Kiểm tra tìm kiếm, lọc và sắp xếp hoạt động kết hợp mà không đổi thứ tự mảng gốc.
  it('kết hợp tìm kiếm, lọc, sắp xếp và không làm thay đổi mảng gốc', async () => {
    // Gắn component.
    const wrapper = mountApp();
    // Lấy các ô điều khiển cần sử dụng.
    const searchInput = wrapper.get('[data-testid="search-input"]');
    const categoryFilter = wrapper.get('[data-testid="category-filter"]');
    const sortSelect = wrapper.get('[data-testid="sort-select"]');
    // Ghi lại thứ tự id ban đầu của mảng sản phẩm trong component.
    const originalIds = wrapper.vm.products.map((product) => product.id);
    // Nhập từ khóa "b" để khớp "Bluetooth" và "Bàn phím".
    await searchInput.setValue('b');
    // Chỉ lọc các sản phẩm thuộc Điện tử.
    await categoryFilter.setValue('Điện tử');
    // Sắp xếp giá tăng dần.
    await sortSelect.setValue('asc');
    // Hai sản phẩm khớp phải được sắp xếp theo giá tăng dần: id 1 rồi id 2.
    expect(wrapper.vm.visibleProducts.map((product) => product.id)).toEqual([1, 2]);
    // Mảng dữ liệu gốc trong component phải giữ nguyên thứ tự.
    expect(wrapper.vm.products.map((product) => product.id)).toEqual(originalIds);
    // Chuyển sang sắp xếp giá giảm dần.
    await sortSelect.setValue('desc');
    // Thứ tự phải đảo thành id 2 rồi id 1.
    expect(wrapper.vm.visibleProducts.map((product) => product.id)).toEqual([2, 1]);
    // Mảng gốc vẫn phải giữ nguyên thứ tự sau khi đổi kiểu sắp xếp.
    expect(wrapper.vm.products.map((product) => product.id)).toEqual(originalIds);
    // Chuyển về không sắp xếp.
    await sortSelect.setValue('none');
    // Thứ tự phải trở về thứ tự gốc của các sản phẩm sau khi lọc: id 1 rồi id 2.
    expect(wrapper.vm.visibleProducts.map((product) => product.id)).toEqual([1, 2]);
  });
});

// Nhóm các bài kiểm tra cho chức năng thêm sản phẩm.
describe('thêm sản phẩm mới', () => {
  // Kiểm tra biểu mẫu rỗng bị chặn và hiển thị đầy đủ lỗi.
  it('chặn thêm và hiển thị lỗi khi biểu mẫu rỗng', async () => {
    // Gắn component.
    const wrapper = mountApp();
    // Gửi biểu mẫu mà không nhập dữ liệu.
    await wrapper.get('[data-testid="add-form"]').trigger('submit');
    // Phải có lỗi tên.
    expect(wrapper.find('[data-testid="name-error"]').exists()).toBe(true);
    // Phải có lỗi giá.
    expect(wrapper.find('[data-testid="price-error"]').exists()).toBe(true);
    // Phải có lỗi danh mục.
    expect(wrapper.find('[data-testid="category-error"]').exists()).toBe(true);
    // Phải có lỗi số lượng tồn.
    expect(wrapper.find('[data-testid="stock-error"]').exists()).toBe(true);
    // Không được thêm sản phẩm nào vào bảng.
    expect(wrapper.findAll('[data-testid="product-row"]')).toHaveLength(INITIAL_PRODUCTS.length);
    // Thông báo chung phải là lỗi.
    expect(wrapper.get('[data-testid="feedback"]').classes()).toContain('error');
  });

  // Kiểm tra các giá trị không hợp lệ cụ thể bị chặn.
  it('chặn tên toàn khoảng trắng, giá 0 và tồn kho không nguyên', async () => {
    // Gắn component.
    const wrapper = mountApp();
    // Nhập tên chỉ gồm khoảng trắng.
    await wrapper.get('[data-testid="name-input"]').setValue('   ');
    // Nhập giá bằng 0.
    await wrapper.get('[data-testid="price-input"]').setValue('0');
    // Nhập số lượng tồn có phần thập phân.
    await wrapper.get('[data-testid="stock-input"]').setValue('1.5');
    // Chưa chọn danh mục.
    await wrapper.get('[data-testid="category-select"]').setValue('');
    // Gửi biểu mẫu.
    await wrapper.get('[data-testid="add-form"]').trigger('submit');
    // Cả bốn trường đều phải báo lỗi.
    expect(wrapper.find('[data-testid="name-error"]').exists()).toBe(true);
    expect(wrapper.find('[data-testid="price-error"]').exists()).toBe(true);
    expect(wrapper.find('[data-testid="category-error"]').exists()).toBe(true);
    expect(wrapper.find('[data-testid="stock-error"]').exists()).toBe(true);
    // Số sản phẩm phải giữ nguyên.
    expect(wrapper.findAll('[data-testid="product-row"]')).toHaveLength(INITIAL_PRODUCTS.length);
  });

  // Kiểm tra thêm thành công khi dữ liệu hợp lệ.
  it('thêm sản phẩm thành công khi dữ liệu hợp lệ và cập nhật thống kê', async () => {
    // Gắn component.
    const wrapper = mountApp();
    // Nhập tên có khoảng trắng hai đầu để kiểm tra việc trim khi lưu.
    await wrapper.get('[data-testid="name-input"]').setValue('  Sản phẩm kiểm thử  ');
    // Nhập giá hợp lệ.
    await wrapper.get('[data-testid="price-input"]').setValue('150000');
    // Chọn danh mục hợp lệ.
    await wrapper.get('[data-testid="category-select"]').setValue('Điện tử');
    // Nhập số lượng tồn hợp lệ.
    await wrapper.get('[data-testid="stock-input"]').setValue('3');
    // Gửi biểu mẫu để thêm sản phẩm.
    await wrapper.get('[data-testid="add-form"]').trigger('submit');
    // Bảng phải có thêm một hàng.
    expect(wrapper.findAll('[data-testid="product-row"]')).toHaveLength(INITIAL_PRODUCTS.length + 1);
    // Thống kê phải là 10 / 10.
    expect(wrapper.get('[data-testid="visible-count"]').text()).toBe('10 / 10');
    // Lấy sản phẩm vừa được thêm ở cuối mảng.
    const addedProduct = wrapper.vm.products[wrapper.vm.products.length - 1];
    // Kiểm tra đầy đủ dữ liệu và việc cắt khoảng trắng của tên.
    expect(addedProduct).toEqual({
      id: 10,
      name: 'Sản phẩm kiểm thử',
      price: 150000,
      category: 'Điện tử',
      stock: 3,
    });
    // Tổng giá trị tồn kho phải cập nhật theo sản phẩm mới.
    expect(wrapper.get('[data-testid="total-inventory-value"]').text())
      .toBe(formatCurrency(getTotalInventoryValue([...INITIAL_PRODUCTS, addedProduct])));
    // Thông báo phải là thành công.
    expect(wrapper.get('[data-testid="feedback"]').classes()).toContain('success');
    // Biểu mẫu phải được làm mới sau khi thêm thành công.
    expect(wrapper.get('[data-testid="name-input"]').element.value).toBe('');
    expect(wrapper.get('[data-testid="price-input"]').element.value).toBe('');
    expect(wrapper.get('[data-testid="stock-input"]').element.value).toBe('');
    expect(wrapper.get('[data-testid="category-select"]').element.value).toBe('');
  });

  // Kiểm tra sản phẩm mới có tồn kho bằng 0 vẫn được thêm và hiển thị "Hết hàng".
  it('chấp nhận sản phẩm mới có tồn kho bằng 0', async () => {
    // Gắn component.
    const wrapper = mountApp();
    // Nhập tên hợp lệ.
    await wrapper.get('[data-testid="name-input"]').setValue('Sản phẩm hết hàng');
    // Nhập giá hợp lệ.
    await wrapper.get('[data-testid="price-input"]').setValue('99000');
    // Chọn danh mục hợp lệ.
    await wrapper.get('[data-testid="category-select"]').setValue('Gia dụng');
    // Nhập số lượng tồn bằng 0.
    await wrapper.get('[data-testid="stock-input"]').setValue('0');
    // Gửi biểu mẫu.
    await wrapper.get('[data-testid="add-form"]').trigger('submit');
    // Sản phẩm phải được thêm thành công.
    expect(wrapper.findAll('[data-testid="product-row"]')).toHaveLength(INITIAL_PRODUCTS.length + 1);
    // Lấy hàng cuối cùng trong bảng.
    const rows = wrapper.findAll('[data-testid="product-row"]');
    // Hàng cuối phải chứa tên sản phẩm vừa thêm.
    expect(rows[rows.length - 1].text()).toContain('Sản phẩm hết hàng');
    // Sản phẩm tồn kho 0 phải hiển thị nhãn "Hết hàng".
    expect(rows[rows.length - 1].find('[data-testid="stock-badge"]').text()).toBe('Hết hàng');
  });
});

// Nhóm các bài kiểm tra cho chức năng xóa sản phẩm.
describe('xóa sản phẩm', () => {
  // Kiểm tra phương thức xóa xử lý an toàn khi nhận dữ liệu không hợp lệ.
  it('không gây lỗi khi nhận sản phẩm không hợp lệ', async () => {
    // Gắn component.
    const wrapper = mountApp();
    // Gọi trực tiếp phương thức xóa với giá trị null để kiểm tra lớp bảo vệ.
    expect(() => wrapper.vm.handleDelete(null)).not.toThrow();
    // Chờ Vue cập nhật DOM sau khi trạng thái feedback thay đổi.
    await nextTick();
    // Không được xóa sản phẩm nào.
    expect(wrapper.vm.products).toHaveLength(INITIAL_PRODUCTS.length);
    // Phải hiển thị thông báo lỗi thay vì im lặng.
    expect(wrapper.get('[data-testid="feedback"]').classes()).toContain('error');
  });

  // Kiểm tra không xóa khi người dùng hủy xác nhận.
  it('không xóa sản phẩm khi người dùng hủy xác nhận', async () => {
    // Gắn component.
    const wrapper = mountApp();
    // Giả lập người dùng bấm "Hủy" trong hộp thoại xác nhận.
    const confirmSpy = vi.spyOn(window, 'confirm').mockReturnValue(false);
    // Bấm nút xóa của sản phẩm đầu tiên.
    await wrapper.findAll('[data-testid="delete-button"]')[0].trigger('click');
    // Hộp thoại xác nhận phải được gọi đúng một lần.
    expect(confirmSpy).toHaveBeenCalledTimes(1);
    // Số sản phẩm phải giữ nguyên.
    expect(wrapper.vm.products).toHaveLength(INITIAL_PRODUCTS.length);
    // Số hàng hiển thị cũng phải giữ nguyên.
    expect(wrapper.findAll('[data-testid="product-row"]')).toHaveLength(INITIAL_PRODUCTS.length);
  });

  // Kiểm tra xóa thành công khi người dùng đồng ý.
  it('xóa sản phẩm sau khi người dùng xác nhận và cập nhật thống kê', async () => {
    // Gắn component.
    const wrapper = mountApp();
    // Giả lập người dùng bấm "Đồng ý" trong hộp thoại xác nhận.
    vi.spyOn(window, 'confirm').mockReturnValue(true);
    // Bấm nút xóa của sản phẩm đầu tiên (id 1).
    await wrapper.findAll('[data-testid="delete-button"]')[0].trigger('click');
    // Mảng dữ liệu phải còn lại 8 sản phẩm.
    expect(wrapper.vm.products).toHaveLength(INITIAL_PRODUCTS.length - 1);
    // Bảng phải còn lại 8 hàng.
    expect(wrapper.findAll('[data-testid="product-row"]')).toHaveLength(INITIAL_PRODUCTS.length - 1);
    // Sản phẩm đầu tiên phải bị xóa khỏi dữ liệu.
    expect(wrapper.vm.products.some((product) => product.id === 1)).toBe(false);
    // Thống kê phải cập nhật thành 8 / 8.
    expect(wrapper.get('[data-testid="visible-count"]').text()).toBe('8 / 8');
    // Tổng giá trị tồn kho phải tính lại từ các sản phẩm còn lại.
    expect(wrapper.get('[data-testid="total-inventory-value"]').text())
      .toBe(formatCurrency(getTotalInventoryValue(INITIAL_PRODUCTS.slice(1))));
    // Phải có thông báo xóa thành công.
    expect(wrapper.get('[data-testid="feedback"]').text()).toContain('Đã xóa');
  });

  // Kiểm tra xóa khi đang lọc vẫn cập nhật đúng thống kê của bộ lọc hiện tại.
  it('cập nhật đúng thống kê khi xóa sản phẩm đang được lọc', async () => {
    // Gắn component.
    const wrapper = mountApp();
    // Giả lập người dùng đồng ý xóa.
    vi.spyOn(window, 'confirm').mockReturnValue(true);
    // Tìm kiếm để chỉ còn sản phẩm "Tai nghe Bluetooth".
    await wrapper.get('[data-testid="search-input"]').setValue('tai');
    // Xác nhận chỉ còn đúng một hàng.
    expect(wrapper.findAll('[data-testid="product-row"]')).toHaveLength(1);
    // Bấm nút xóa của hàng đang hiển thị.
    await wrapper.findAll('[data-testid="delete-button"]')[0].trigger('click');
    // Bộ lọc hiện tại không còn sản phẩm nào khớp.
    expect(wrapper.findAll('[data-testid="product-row"]')).toHaveLength(0);
    // Phải hiển thị trạng thái danh sách rỗng.
    expect(wrapper.find('[data-testid="empty-state"]').exists()).toBe(true);
    // Số đang hiển thị phải là 0 trên tổng số còn lại là 8.
    expect(wrapper.get('[data-testid="visible-count"]').text()).toBe('0 / 8');
    // Tổng giá trị tồn kho của danh sách đang hiển thị phải là 0 ₫.
    expect(wrapper.get('[data-testid="total-inventory-value"]').text()).toBe(formatCurrency(0));
  });
});
