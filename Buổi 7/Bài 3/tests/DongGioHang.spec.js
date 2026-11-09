// Nhập các hàm mô tả và kiểm tra của Vitest để xây dựng bộ kiểm thử.
import { describe, expect, it } from 'vitest';
// Nhập hàm mount của Vue Test Utils để tạo và tương tác với component trong môi trường giả lập DOM.
import { mount } from '@vue/test-utils';
// Nhập component con cần kiểm thử.
import DongGioHang from '../src/components/DongGioHang.vue';

/**
 * Tạo một đối tượng sản phẩm mẫu, cho phép ghi đè từng trường khi cần.
 *
 * @param {object} ghiDe Các trường muốn thay đổi so với giá trị mặc định.
 * @returns {object} Đối tượng sản phẩm hoàn chỉnh để truyền vào prop.
 */
function taoSanPham(ghiDe = {}) {
  // Trả về sản phẩm mặc định có đủ bốn trường bắt buộc, sau đó phủ các giá trị ghi đè lên.
  return {
    // Mã sản phẩm mặc định.
    id: 1,
    // Tên sản phẩm mặc định.
    ten: 'Bút bi',
    // Đơn giá mặc định là 5.000 đồng.
    donGia: 5000,
    // Số lượng mặc định là 2.
    soLuong: 2,
    // Các trường ghi đè được đặt sau nên sẽ thay thế giá trị mặc định tương ứng.
    ...ghiDe,
  };
}

/**
 * Mount component con với đúng một prop sanPham.
 *
 * @param {object} sanPham Đối tượng sản phẩm cần truyền xuống component.
 * @returns {object} Wrapper của Vue Test Utils để truy vấn và tương tác.
 */
function taoWrapper(sanPham) {
  // Mount component và truyền sản phẩm qua props theo đúng thiết kế của bài.
  return mount(DongGioHang, {
    // Khóa props chứa đối tượng sản phẩm cần kiểm thử.
    props: { sanPham },
  });
}

/**
 * Gõ một giá trị mới vào ô nhập số lượng rồi xác nhận bằng sự kiện change.
 * Hàm gom thao tác lặp lại ở nhiều bài kiểm thử để tránh trùng lặp mã.
 *
 * @param {object} wrapper Wrapper của component con.
 * @param {string} giaTri Chuỗi giá trị cần gõ vào ô nhập.
 * @returns {Promise<void>} Promise hoàn thành sau khi Vue cập nhật giao diện.
 */
async function nhapSoLuong(wrapper, giaTri) {
  // Lấy ô nhập số lượng theo data-testid đã khai báo trong template.
  const oNhap = wrapper.get('[data-testid="o-nhap-so-luong"]');
  // Gán trực tiếp giá trị mới vào phần tử DOM để mô phỏng người dùng đang gõ.
  oNhap.element.value = String(giaTri);
  // Kích hoạt sự kiện input để component cập nhật bản nháp cục bộ.
  await oNhap.trigger('input');
  // Kích hoạt sự kiện change để component xác nhận và phát sự kiện nếu hợp lệ.
  await oNhap.trigger('change');
}

// Nhóm các bài kiểm thử liên quan đến component con <dong-gio-hang>.
describe('DongGioHang', () => {
  // Kiểm tra yêu cầu hiển thị đầy đủ thông tin của một dòng hàng.
  it('hiển thị tên, đơn giá, số lượng và thành tiền đúng', () => {
    // Tạo sản phẩm mẫu với số lượng 2 và đơn giá 5.000 đồng.
    const sanPham = taoSanPham();
    // Mount component với sản phẩm vừa tạo.
    const wrapper = taoWrapper(sanPham);
    // Lấy ô nhập số lượng để kiểm tra giá trị đang hiển thị.
    const oNhap = wrapper.get('[data-testid="o-nhap-so-luong"]');

    // Kiểm tra tên sản phẩm được hiển thị chính xác.
    expect(wrapper.get('[data-testid="ten-san-pham"]').text()).toBe('Bút bi');
    // Kiểm tra đơn giá được định dạng đúng theo kiểu tiền Việt Nam.
    expect(wrapper.get('[data-testid="don-gia"]').text()).toBe('5.000 đồng');
    // Ô nhập số lượng phải hiển thị đúng số lượng hiện tại là 2.
    expect(oNhap.element.value).toBe('2');
    // Ô nhập phải có kiểu number để người dùng nhập số bằng bàn phím.
    expect(oNhap.attributes('type')).toBe('number');
    // Kiểm tra thành tiền bằng đơn giá nhân số lượng: 5.000 × 2 = 10.000 đồng.
    expect(wrapper.get('[data-testid="thanh-tien"]').text()).toBe('10.000 đồng');
  });

  // Kiểm tra sự kiện tăng số lượng do component con phát ra.
  it('phát sự kiện doi-so-luong với id và số lượng mới khi bấm tăng', async () => {
    // Tạo sản phẩm mẫu có số lượng 2.
    const sanPham = taoSanPham();
    // Mount component để lấy nút tăng.
    const wrapper = taoWrapper(sanPham);

    // Bấm nút tăng số lượng.
    await wrapper.get('[data-testid="nut-tang"]').trigger('click');

    // Sự kiện phải được phát đúng một lần với hai tham số: id sản phẩm và số lượng mới là 3.
    expect(wrapper.emitted('doi-so-luong')).toEqual([[1, 3]]);
    // Đối tượng gốc truyền vào prop không được bị component con sửa đổi.
    expect(sanPham.soLuong).toBe(2);
    // Prop mà component con nhận vẫn giữ nguyên số lượng cũ.
    expect(wrapper.props('sanPham').soLuong).toBe(2);
  });

  // Kiểm tra sự kiện giảm số lượng do component con phát ra.
  it('phát sự kiện doi-so-luong với số lượng mới khi bấm giảm', async () => {
    // Tạo sản phẩm mẫu có số lượng 2 để vẫn có thể giảm.
    const sanPham = taoSanPham();
    // Mount component để lấy nút giảm.
    const wrapper = taoWrapper(sanPham);

    // Bấm nút giảm số lượng.
    await wrapper.get('[data-testid="nut-giam"]').trigger('click');

    // Sự kiện phải gửi id sản phẩm và số lượng mới là 1.
    expect(wrapper.emitted('doi-so-luong')).toEqual([[1, 1]]);
    // Dữ liệu prop gốc phải được giữ nguyên, chứng minh con không sửa props.
    expect(sanPham.soLuong).toBe(2);
  });

  // Kiểm tra giới hạn nhỏ nhất của số lượng.
  it('khóa nút giảm và không phát sự kiện khi số lượng bằng 1', async () => {
    // Tạo sản phẩm đã ở mức số lượng nhỏ nhất.
    const sanPham = taoSanPham({ soLuong: 1 });
    // Mount component với sản phẩm biên.
    const wrapper = taoWrapper(sanPham);
    // Lấy phần tử nút giảm để kiểm tra trạng thái.
    const nutGiam = wrapper.get('[data-testid="nut-giam"]');

    // Thuộc tính disabled của DOM phải được bật để người dùng không bấm được.
    expect(nutGiam.element.disabled).toBe(true);
    // Ghi chú giải thích giới hạn nhỏ nhất phải xuất hiện.
    expect(wrapper.get('[data-testid="ghi-chu-toi-thieu"]').text()).toContain('tối thiểu là 1');

    // Cố tình kích hoạt sự kiện click để kiểm tra lớp bảo vệ trong phương thức.
    await nutGiam.trigger('click');
    // Không được phát sự kiện đổi số lượng vì số lượng mới sẽ là 0, nằm ngoài phạm vi.
    expect(wrapper.emitted('doi-so-luong')).toBeUndefined();
    // Dữ liệu prop vẫn giữ nguyên giá trị ban đầu.
    expect(sanPham.soLuong).toBe(1);
  });

  // Kiểm tra giới hạn lớn nhất của số lượng.
  it('khóa nút tăng và không phát sự kiện khi số lượng bằng 99', async () => {
    // Tạo sản phẩm đã ở mức số lượng lớn nhất.
    const sanPham = taoSanPham({ soLuong: 99 });
    // Mount component với sản phẩm biên.
    const wrapper = taoWrapper(sanPham);
    // Lấy phần tử nút tăng để kiểm tra trạng thái.
    const nutTang = wrapper.get('[data-testid="nut-tang"]');

    // Thuộc tính disabled của DOM phải được bật để người dùng không bấm được.
    expect(nutTang.element.disabled).toBe(true);
    // Ghi chú giải thích giới hạn lớn nhất phải xuất hiện.
    expect(wrapper.get('[data-testid="ghi-chu-toi-da"]').text()).toContain('tối đa là 99');

    // Cố tình kích hoạt sự kiện click để kiểm tra lớp bảo vệ trong phương thức.
    await nutTang.trigger('click');
    // Không được phát sự kiện đổi số lượng vì số lượng mới sẽ là 100, nằm ngoài phạm vi.
    expect(wrapper.emitted('doi-so-luong')).toBeUndefined();
    // Dữ liệu prop vẫn giữ nguyên giá trị ban đầu.
    expect(sanPham.soLuong).toBe(99);
  });

  // Kiểm tra thông tin tự động cập nhật ngay khi dữ liệu đang nhập đã hợp lệ.
  it('tự động phát sự kiện doi-so-luong ngay khi gõ số lượng hợp lệ', async () => {
    // Tạo sản phẩm mẫu có số lượng 2.
    const sanPham = taoSanPham();
    // Mount component để lấy ô nhập.
    const wrapper = taoWrapper(sanPham);
    // Lấy ô nhập số lượng theo data-testid.
    const oNhap = wrapper.get('[data-testid="o-nhap-so-luong"]');

    // Gõ giá trị 7 vào ô nhập và chỉ kích hoạt sự kiện input, chưa rời ô và chưa nhấn Enter.
    oNhap.element.value = '7';
    // Kích hoạt sự kiện input để kiểm tra việc tự động cập nhật theo từng phím gõ.
    await oNhap.trigger('input');

    // Sự kiện phải được phát ngay lập tức với đúng id và số lượng mới.
    expect(wrapper.emitted('doi-so-luong')).toEqual([[1, 7]]);
    // Component con không được tự sửa prop dù đã phát sự kiện cập nhật.
    expect(sanPham.soLuong).toBe(2);
    // Không có thông báo lỗi vì dữ liệu đang nhập hoàn toàn hợp lệ.
    expect(wrapper.find('[data-testid="loi-so-luong"]').exists()).toBe(false);
  });

  // Kiểm tra rời ô nhập sau khi đã tự động cập nhật thì không phát trùng sự kiện.
  it('không phát trùng sự kiện khi rời ô nhập sau khi đã tự động cập nhật', async () => {
    // Tạo sản phẩm mẫu có số lượng 2.
    const sanPham = taoSanPham();
    // Mount component để lấy ô nhập.
    const wrapper = taoWrapper(sanPham);

    // Gõ giá trị 7 và xác nhận bằng sự kiện change sau khi input đã phát sự kiện.
    await nhapSoLuong(wrapper, '7');

    // Sự kiện chỉ được phát đúng một lần dù có cả input lẫn change.
    expect(wrapper.emitted('doi-so-luong')).toEqual([[1, 7]]);
    // Component con không được tự sửa prop dù người dùng đã nhập giá trị mới.
    expect(sanPham.soLuong).toBe(2);
    // Thông báo lỗi không được xuất hiện vì giá trị nhập hợp lệ.
    expect(wrapper.find('[data-testid="loi-so-luong"]').exists()).toBe(false);
  });

  // Kiểm tra người dùng gõ số lượng mới rồi nhấn Enter.
  it('phát sự kiện doi-so-luong khi nhập số lượng hợp lệ rồi nhấn Enter', async () => {
    // Tạo sản phẩm mẫu có số lượng 2.
    const sanPham = taoSanPham();
    // Mount component để lấy ô nhập.
    const wrapper = taoWrapper(sanPham);
    // Lấy ô nhập số lượng theo data-testid.
    const oNhap = wrapper.get('[data-testid="o-nhap-so-luong"]');

    // Gõ giá trị 12 vào ô nhập, chỉ kích hoạt sự kiện input chứ chưa rời ô.
    oNhap.element.value = '12';
    // Kích hoạt sự kiện input để cập nhật bản nháp cục bộ và tự động phát sự kiện.
    await oNhap.trigger('input');
    // Mô phỏng người dùng nhấn phím Enter để xác nhận giá trị vừa nhập.
    await oNhap.trigger('keydown', { key: 'Enter' });

    // Sự kiện phải gửi đúng id sản phẩm và số lượng mới là 12, không bị phát trùng.
    expect(wrapper.emitted('doi-so-luong')).toEqual([[1, 12]]);
    // Prop gốc vẫn giữ nguyên số lượng cũ vì mọi thay đổi do cha quyết định.
    expect(sanPham.soLuong).toBe(2);
  });

  // Kiểm tra ô nhập tự đồng bộ khi component cha cập nhật số lượng mới.
  it('đồng bộ ô nhập khi prop số lượng thay đổi từ component cha', async () => {
    // Tạo sản phẩm mẫu có số lượng 2.
    const sanPham = taoSanPham();
    // Mount component với sản phẩm ban đầu.
    const wrapper = taoWrapper(sanPham);

    // Giả lập cha cập nhật số lượng xuống 8 thông qua props.
    await wrapper.setProps({ sanPham: taoSanPham({ soLuong: 8 }) });

    // Ô nhập phải hiển thị số lượng mới thay vì giá trị cũ.
    expect(wrapper.get('[data-testid="o-nhap-so-luong"]').element.value).toBe('8');
    // Thành tiền phải tính lại theo số lượng mới: 5.000 × 8 = 40.000 đồng.
    expect(wrapper.get('[data-testid="thanh-tien"]').text()).toBe('40.000 đồng');
  });

  // Kiểm tra từ chối các giá trị không phải số nguyên từ 1 đến 99.
  it('không phát sự kiện và hiện lỗi khi nhập số lượng không hợp lệ', async () => {
    // Khai báo danh sách giá trị không hợp lệ cần thử, gồm cả số 0, vượt trần, số thập phân và chữ.
    const cacGiaTriSai = ['0', '100', '1.5', 'abc', '', '-3', '99.5'];

    // Lần lượt thử từng giá trị sai trên một component mới để kết quả độc lập.
    for (const giaTri of cacGiaTriSai) {
      // Tạo sản phẩm mẫu có số lượng 2 cho mỗi lượt kiểm tra.
      const sanPham = taoSanPham();
      // Mount component mới để trạng thái lỗi không bị ảnh hưởng từ lượt trước.
      const wrapper = taoWrapper(sanPham);

      // Gõ giá trị sai và xác nhận bằng sự kiện change.
      await nhapSoLuong(wrapper, giaTri);

      // Không được phát sự kiện đổi số lượng với dữ liệu sai.
      expect(wrapper.emitted('doi-so-luong')).toBeUndefined();
      // Thông báo lỗi phải xuất hiện để hướng dẫn người dùng.
      expect(wrapper.get('[data-testid="loi-so-luong"]').text()).toContain('1 đến 99');
      // Ô nhập phải được đưa về đúng số lượng hiện tại của prop là 2.
      expect(wrapper.get('[data-testid="o-nhap-so-luong"]').element.value).toBe('2');
      // Prop gốc không được thay đổi trong mọi trường hợp.
      expect(sanPham.soLuong).toBe(2);
    }
  });

  // Kiểm tra nhập lại giá trị trùng với số lượng hiện tại thì không phát sự kiện.
  it('không phát sự kiện khi nhập lại đúng số lượng hiện tại', async () => {
    // Tạo sản phẩm mẫu có số lượng 2.
    const sanPham = taoSanPham();
    // Mount component với sản phẩm vừa tạo.
    const wrapper = taoWrapper(sanPham);

    // Gõ lại đúng giá trị 2 đang có trong prop.
    await nhapSoLuong(wrapper, '2');

    // Không có thay đổi thực sự nên không được phát sự kiện.
    expect(wrapper.emitted('doi-so-luong')).toBeUndefined();
    // Ô nhập vẫn hiển thị đúng giá trị hiện tại.
    expect(wrapper.get('[data-testid="o-nhap-so-luong"]').element.value).toBe('2');
  });

  // Kiểm tra bấm nút Xóa lần đầu chỉ mở hộp xác nhận, chưa xóa sản phẩm.
  it('mở hộp xác nhận và chưa phát sự kiện xóa khi bấm nút Xóa lần đầu', async () => {
    // Tạo sản phẩm có id 7 để chắc chắn sự kiện gửi đúng mã.
    const sanPham = taoSanPham({ id: 7 });
    // Mount component để lấy nút xóa.
    const wrapper = taoWrapper(sanPham);

    // Trước khi bấm, hộp xác nhận chưa được hiển thị.
    expect(wrapper.find('[data-testid="hop-xac-nhan-xoa"]').exists()).toBe(false);

    // Bấm nút Xóa lần đầu.
    await wrapper.get('[data-testid="nut-xoa"]').trigger('click');

    // Hộp xác nhận phải xuất hiện để người dùng quyết định lại.
    expect(wrapper.get('[data-testid="hop-xac-nhan-xoa"]').isVisible()).toBe(true);
    // Câu hỏi xác nhận phải chứa tên sản phẩm sắp xóa.
    expect(wrapper.get('[data-testid="hop-xac-nhan-xoa"]').text()).toContain('Bút bi');
    // Tuyệt đối chưa phát sự kiện xóa ở bước này.
    expect(wrapper.emitted('yeu-cau-xoa')).toBeUndefined();
  });

  // Kiểm tra chỉ khi bấm "Xác nhận xóa" component con mới phát sự kiện xóa.
  it('phát sự kiện yeu-cau-xoa sau khi người dùng xác nhận', async () => {
    // Tạo sản phẩm có id 7 để kiểm tra đúng mã được gửi đi.
    const sanPham = taoSanPham({ id: 7 });
    // Mount component để thao tác với các nút.
    const wrapper = taoWrapper(sanPham);

    // Bấm nút Xóa để mở hộp xác nhận.
    await wrapper.get('[data-testid="nut-xoa"]').trigger('click');
    // Bấm nút Xác nhận xóa để đồng ý thao tác.
    await wrapper.get('[data-testid="nut-xac-nhan-xoa"]').trigger('click');

    // Sự kiện phải được phát đúng một lần với mã sản phẩm cần xóa.
    expect(wrapper.emitted('yeu-cau-xoa')).toEqual([[7]]);
    // Hộp xác nhận phải đóng lại sau khi xác nhận.
    expect(wrapper.find('[data-testid="hop-xac-nhan-xoa"]').exists()).toBe(false);
    // Component con không được tự xóa hay thay đổi dữ liệu trong prop.
    expect(wrapper.props('sanPham')).toEqual(sanPham);
  });

  // Kiểm tra bấm Hủy sẽ đóng hộp xác nhận và giữ nguyên sản phẩm.
  it('không phát sự kiện xóa khi người dùng bấm Hủy', async () => {
    // Tạo sản phẩm mẫu để kiểm tra thao tác hủy.
    const sanPham = taoSanPham();
    // Mount component để thao tác với các nút.
    const wrapper = taoWrapper(sanPham);

    // Bấm nút Xóa để mở hộp xác nhận.
    await wrapper.get('[data-testid="nut-xoa"]').trigger('click');
    // Bấm nút Hủy để từ chối xóa sản phẩm.
    await wrapper.get('[data-testid="nut-huy-xoa"]').trigger('click');

    // Không được phát sự kiện xóa nào lên cha.
    expect(wrapper.emitted('yeu-cau-xoa')).toBeUndefined();
    // Hộp xác nhận phải đóng lại.
    expect(wrapper.find('[data-testid="hop-xac-nhan-xoa"]').exists()).toBe(false);
    // Nút Xóa phải hiện lại để người dùng có thể thao tác tiếp.
    expect(wrapper.get('[data-testid="nut-xoa"]').isVisible()).toBe(true);
  });

  // Kiểm tra toàn bộ đối tượng prop không bị thay đổi sau nhiều thao tác.
  it('không sửa bất kỳ thuộc tính nào của đối tượng nhận qua props', async () => {
    // Tạo sản phẩm mẫu.
    const sanPham = taoSanPham();
    // Sao lưu sâu giá trị ban đầu để so sánh sau khi thao tác.
    const banSao = JSON.parse(JSON.stringify(sanPham));
    // Mount component với sản phẩm cần theo dõi.
    const wrapper = taoWrapper(sanPham);

    // Bấm lần lượt nút tăng để kiểm tra không ghi vào prop.
    await wrapper.get('[data-testid="nut-tang"]').trigger('click');
    // Tiếp tục bấm nút giảm.
    await wrapper.get('[data-testid="nut-giam"]').trigger('click');
    // Gõ số lượng mới hợp lệ để kiểm tra đường nhập bàn phím.
    await nhapSoLuong(wrapper, '9');
    // Mở hộp xác nhận xóa.
    await wrapper.get('[data-testid="nut-xoa"]').trigger('click');
    // Xác nhận xóa để kiểm tra đường phát sự kiện xóa.
    await wrapper.get('[data-testid="nut-xac-nhan-xoa"]').trigger('click');

    // Đối tượng gốc phải hoàn toàn giống bản sao ban đầu.
    expect(sanPham).toEqual(banSao);
  });
});
