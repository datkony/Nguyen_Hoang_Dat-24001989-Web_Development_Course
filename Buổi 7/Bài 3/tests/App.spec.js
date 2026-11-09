// Nhập các hàm mô tả và kiểm tra của Vitest.
import { describe, expect, it } from 'vitest';
// Nhập hàm mount của Vue Test Utils để mount toàn bộ ứng dụng.
import { mount } from '@vue/test-utils';
// Nhập component cha cần kiểm thử.
import App from '../src/App.vue';
// Nhập component con để truy vấn các dòng hàng và kiểm tra props.
import DongGioHang from '../src/components/DongGioHang.vue';

/**
 * Mount component cha App và trả về wrapper để tương tác.
 *
 * @returns {object} Wrapper của toàn bộ ứng dụng.
 */
function taoWrapper() {
  // Mount App; các component con được đăng ký cục bộ nên sẽ render bình thường.
  return mount(App);
}

/**
 * Lấy component con tại một vị trí trong danh sách.
 *
 * @param {object} wrapper Wrapper của toàn bộ ứng dụng.
 * @param {number} viTri Vị trí của dòng hàng cần lấy.
 * @returns {object} Wrapper của component con tại vị trí đó.
 */
function layDong(wrapper, viTri) {
  // Tìm tất cả component DongGioHang rồi chọn phần tử theo chỉ số.
  return wrapper.findAllComponents(DongGioHang)[viTri];
}

// Nhóm các bài kiểm thử cho component cha App.
describe('App - giỏ hàng', () => {
  // Kiểm tra dữ liệu mẫu và kết quả tính toán đúng như đề bài.
  it('hiển thị 2 bút bi và 3 quyển vở với tổng số lượng 5, tổng tiền 46.000 đồng', () => {
    // Mount ứng dụng với dữ liệu mẫu ban đầu.
    const wrapper = taoWrapper();
    // Lấy danh sách component con để kiểm tra số dòng.
    const cacDong = wrapper.findAllComponents(DongGioHang);

    // Giỏ hàng mẫu phải có đúng hai dòng sản phẩm.
    expect(cacDong).toHaveLength(2);
    // Dòng thứ nhất phải hiển thị đúng tên "Bút bi".
    expect(cacDong[0].get('[data-testid="ten-san-pham"]').text()).toBe('Bút bi');
    // Dòng thứ nhất có thành tiền 5.000 × 2 = 10.000 đồng.
    expect(cacDong[0].get('[data-testid="thanh-tien"]').text()).toBe('10.000 đồng');
    // Dòng thứ hai phải hiển thị đúng tên "Quyển vở".
    expect(cacDong[1].get('[data-testid="ten-san-pham"]').text()).toBe('Quyển vở');
    // Dòng thứ hai có thành tiền 12.000 × 3 = 36.000 đồng.
    expect(cacDong[1].get('[data-testid="thanh-tien"]').text()).toBe('36.000 đồng');
    // Tổng số lượng phải là 2 + 3 = 5.
    expect(wrapper.get('[data-testid="tong-so-luong"]').text()).toBe('5');
    // Tổng tiền phải là 10.000 + 36.000 = 46.000 đồng.
    expect(wrapper.get('[data-testid="tong-tien"]').text()).toBe('46.000 đồng');
    // Số dòng sản phẩm phải là 2.
    expect(wrapper.get('[data-testid="so-dong-san-pham"]').text()).toBe('2');
  });

  // Kiểm tra chuỗi sự kiện thực tế khi người dùng bấm nút tăng ở component con.
  it('cập nhật tổng số lượng và tổng tiền khi bấm nút tăng ở component con', async () => {
    // Mount ứng dụng với dữ liệu mẫu.
    const wrapper = taoWrapper();

    // Bấm nút tăng của dòng "Bút bi" để số lượng từ 2 lên 3.
    await layDong(wrapper, 0).get('[data-testid="nut-tang"]').trigger('click');

    // Component con phải phát đúng sự kiện với id 1 và số lượng mới 3.
    expect(layDong(wrapper, 0).emitted('doi-so-luong')).toEqual([[1, 3]]);
    // Component cha phải cập nhật prop xuống con, nên số lượng hiển thị là 3.
    expect(layDong(wrapper, 0).props('sanPham').soLuong).toBe(3);
    // Tổng số lượng mới là 3 + 3 = 6.
    expect(wrapper.get('[data-testid="tong-so-luong"]').text()).toBe('6');
    // Tổng tiền mới là 3 × 5.000 + 3 × 12.000 = 51.000 đồng.
    expect(wrapper.get('[data-testid="tong-tien"]').text()).toBe('51.000 đồng');
    // Thành tiền riêng của dòng bút bi phải cập nhật thành 15.000 đồng.
    expect(layDong(wrapper, 0).get('[data-testid="thanh-tien"]').text()).toBe('15.000 đồng');
  });

  // Kiểm tra chuỗi sự kiện thực tế khi người dùng bấm nút giảm ở component con.
  it('cập nhật tổng số lượng và tổng tiền khi bấm nút giảm ở component con', async () => {
    // Mount ứng dụng với dữ liệu mẫu.
    const wrapper = taoWrapper();

    // Bấm nút giảm của dòng "Bút bi" để số lượng từ 2 xuống 1.
    await layDong(wrapper, 0).get('[data-testid="nut-giam"]').trigger('click');

    // Component con phải phát sự kiện với id 1 và số lượng mới 1.
    expect(layDong(wrapper, 0).emitted('doi-so-luong')).toEqual([[1, 1]]);
    // Tổng số lượng mới là 1 + 3 = 4.
    expect(wrapper.get('[data-testid="tong-so-luong"]').text()).toBe('4');
    // Tổng tiền mới là 1 × 5.000 + 3 × 12.000 = 41.000 đồng.
    expect(wrapper.get('[data-testid="tong-tien"]').text()).toBe('41.000 đồng');
  });

  // Kiểm tra cha chấp nhận các giá trị biên hợp lệ của số lượng.
  it('chấp nhận số lượng biên 1 và 99', async () => {
    // Mount ứng dụng với dữ liệu mẫu.
    const wrapper = taoWrapper();

    // Gửi sự kiện đổi số lượng bút bi xuống mức nhỏ nhất là 1.
    layDong(wrapper, 0).vm.$emit('doi-so-luong', 1, 1);
    // Chờ Vue cập nhật lại DOM và computed sau khi dữ liệu thay đổi.
    await wrapper.vm.$nextTick();
    // Tổng số lượng phải là 1 + 3 = 4.
    expect(wrapper.get('[data-testid="tong-so-luong"]').text()).toBe('4');
    // Tổng tiền phải là 1 × 5.000 + 3 × 12.000 = 41.000 đồng.
    expect(wrapper.get('[data-testid="tong-tien"]').text()).toBe('41.000 đồng');

    // Gửi sự kiện đổi số lượng bút bi lên mức lớn nhất là 99.
    layDong(wrapper, 0).vm.$emit('doi-so-luong', 1, 99);
    // Chờ Vue cập nhật lại DOM và computed lần nữa.
    await wrapper.vm.$nextTick();
    // Tổng số lượng phải là 99 + 3 = 102.
    expect(wrapper.get('[data-testid="tong-so-luong"]').text()).toBe('102');
    // Tổng tiền phải là 99 × 5.000 + 3 × 12.000 = 531.000 đồng.
    expect(wrapper.get('[data-testid="tong-tien"]').text()).toBe('531.000 đồng');
  });

  // Kiểm tra nút tăng tự khóa ngay sau khi cha cập nhật số lượng lên mức tối đa.
  it('khóa nút tăng sau khi số lượng được cha cập nhật lên 99', async () => {
    // Mount ứng dụng với dữ liệu mẫu.
    const wrapper = taoWrapper();
    // Đưa số lượng bút bi lên 98 để chỉ còn một lần tăng là chạm giới hạn.
    wrapper.vm.xuLyDoiSoLuong(1, 98);
    // Chờ Vue cập nhật prop và giao diện của component con.
    await wrapper.vm.$nextTick();

    // Nút tăng vẫn phải bật vì 98 nhỏ hơn 99.
    expect(layDong(wrapper, 0).get('[data-testid="nut-tang"]').element.disabled).toBe(false);
    // Bấm nút tăng để đưa số lượng lên đúng mức tối đa 99.
    await layDong(wrapper, 0).get('[data-testid="nut-tang"]').trigger('click');
    // Chờ Vue cập nhật lại giao diện sau khi cha xử lý sự kiện.
    await wrapper.vm.$nextTick();

    // Số lượng của bút bi phải là 99.
    expect(layDong(wrapper, 0).props('sanPham').soLuong).toBe(99);
    // Nút tăng phải bị khóa ngay khi chạm giới hạn trên.
    expect(layDong(wrapper, 0).get('[data-testid="nut-tang"]').element.disabled).toBe(true);
    // Tổng số lượng phải là 99 + 3 = 102.
    expect(wrapper.get('[data-testid="tong-so-luong"]').text()).toBe('102');
    // Tổng tiền phải là 99 × 5.000 + 3 × 12.000 = 531.000 đồng.
    expect(wrapper.get('[data-testid="tong-tien"]').text()).toBe('531.000 đồng');
  });

  // Kiểm tra cha từ chối mọi số lượng không phải số nguyên từ 1 đến 99.
  it('bỏ qua số lượng không hợp lệ và giữ nguyên dữ liệu', async () => {
    // Mount ứng dụng với dữ liệu mẫu.
    const wrapper = taoWrapper();
    // Khai báo danh sách các giá trị không hợp lệ cần thử.
    const cacGiaTriKhongHopLe = [0, -1, 100, 1.5, '4', NaN, Infinity, -Infinity, null, undefined, {}, []];

    // Gọi trực tiếp phương thức xử lý của cha với từng giá trị không hợp lệ.
    for (const giaTri of cacGiaTriKhongHopLe) {
      // Phương thức phải tự kiểm tra và từ chối cập nhật.
      wrapper.vm.xuLyDoiSoLuong(1, giaTri);
    }
    // Chờ Vue xử lý xong các thay đổi trạng thái có thể có.
    await wrapper.vm.$nextTick();

    // Số lượng bút bi phải giữ nguyên là 2.
    expect(layDong(wrapper, 0).props('sanPham').soLuong).toBe(2);
    // Tổng số lượng phải giữ nguyên là 5.
    expect(wrapper.get('[data-testid="tong-so-luong"]').text()).toBe('5');
    // Tổng tiền phải giữ nguyên là 46.000 đồng.
    expect(wrapper.get('[data-testid="tong-tien"]').text()).toBe('46.000 đồng');
    // Thông báo lỗi phải cho biết số lượng không hợp lệ.
    expect(wrapper.get('[data-testid="thong-bao"]').text()).toContain('không hợp lệ');
  });

  // Kiểm tra cha từ chối mã sản phẩm không tồn tại hoặc không hợp lệ.
  it('bỏ qua mã sản phẩm không tồn tại hoặc không hợp lệ', async () => {
    // Mount ứng dụng với dữ liệu mẫu.
    const wrapper = taoWrapper();

    // Gửi yêu cầu đổi số lượng cho một id không có trong mảng.
    wrapper.vm.xuLyDoiSoLuong(999, 4);
    // Chờ Vue cập nhật thông báo.
    await wrapper.vm.$nextTick();
    // Thông báo phải nói rõ không tìm thấy sản phẩm.
    expect(wrapper.get('[data-testid="thong-bao"]').text()).toContain('Không tìm thấy sản phẩm');
    // Số dòng vẫn là 2 vì không có sản phẩm nào bị thay đổi.
    expect(wrapper.findAllComponents(DongGioHang)).toHaveLength(2);

    // Gửi yêu cầu đổi số lượng với id không đúng kiểu dữ liệu.
    wrapper.vm.xuLyDoiSoLuong(NaN, 4);
    // Chờ Vue cập nhật thông báo.
    await wrapper.vm.$nextTick();
    // Thông báo phải nói rõ mã sản phẩm không hợp lệ.
    expect(wrapper.get('[data-testid="thong-bao"]').text()).toContain('Mã sản phẩm không hợp lệ');

    // Gửi yêu cầu xóa với id không tồn tại.
    wrapper.vm.xuLyYeuCauXoa(999);
    // Chờ Vue cập nhật thông báo.
    await wrapper.vm.$nextTick();
    // Thông báo phải nói rõ không tìm thấy sản phẩm cần xóa.
    expect(wrapper.get('[data-testid="thong-bao"]').text()).toContain('Không tìm thấy sản phẩm');
    // Không được xóa nhầm dòng nào nên số dòng vẫn là 2.
    expect(wrapper.findAllComponents(DongGioHang)).toHaveLength(2);
  });

  // Kiểm tra chuỗi sự kiện xóa khi người dùng bấm nút xóa rồi xác nhận ở component con.
  it('xóa đúng sản phẩm sau khi người dùng xác nhận ở component con', async () => {
    // Mount ứng dụng với dữ liệu mẫu.
    const wrapper = taoWrapper();

    // Bấm nút xóa của dòng đầu tiên là "Bút bi" để mở hộp xác nhận.
    await layDong(wrapper, 0).get('[data-testid="nut-xoa"]').trigger('click');
    // Bấm nút xác nhận xóa để thực sự gửi yêu cầu lên component cha.
    await layDong(wrapper, 0).get('[data-testid="nut-xac-nhan-xoa"]').trigger('click');

    // Sau khi xóa phải còn đúng một dòng sản phẩm.
    expect(wrapper.findAllComponents(DongGioHang)).toHaveLength(1);
    // Dòng còn lại phải là "Quyển vở".
    expect(layDong(wrapper, 0).get('[data-testid="ten-san-pham"]').text()).toBe('Quyển vở');
    // Tổng số lượng còn lại là 3.
    expect(wrapper.get('[data-testid="tong-so-luong"]').text()).toBe('3');
    // Tổng tiền còn lại là 36.000 đồng.
    expect(wrapper.get('[data-testid="tong-tien"]').text()).toBe('36.000 đồng');
    // Thông báo phải xác nhận đã xóa sản phẩm.
    expect(wrapper.get('[data-testid="thong-bao"]').text()).toContain('Đã xóa "Bút bi"');
  });

  // Kiểm tra hủy xác nhận không xóa sản phẩm và giữ nguyên tổng tiền.
  it('không xóa sản phẩm khi người dùng hủy xác nhận', async () => {
    // Mount ứng dụng với dữ liệu mẫu.
    const wrapper = taoWrapper();

    // Bấm nút xóa của dòng đầu tiên để mở hộp xác nhận.
    await layDong(wrapper, 0).get('[data-testid="nut-xoa"]').trigger('click');
    // Bấm nút hủy để dừng thao tác xóa.
    await layDong(wrapper, 0).get('[data-testid="nut-huy-xoa"]').trigger('click');

    // Cả hai dòng sản phẩm vẫn phải còn nguyên trong giỏ.
    expect(wrapper.findAllComponents(DongGioHang)).toHaveLength(2);
    // Component cha không được phát sự kiện xóa nào từ con.
    expect(layDong(wrapper, 0).emitted('yeu-cau-xoa')).toBeUndefined();
    // Tổng số lượng phải giữ nguyên là 5.
    expect(wrapper.get('[data-testid="tong-so-luong"]').text()).toBe('5');
    // Tổng tiền phải giữ nguyên là 46.000 đồng.
    expect(wrapper.get('[data-testid="tong-tien"]').text()).toBe('46.000 đồng');
  });

  // Kiểm tra thông tin tự động cập nhật ngay khi dữ liệu đang nhập hợp lệ.
  it('tự động cập nhật tổng ngay khi dữ liệu nhập vào là hợp lệ', async () => {
    // Mount ứng dụng với dữ liệu mẫu.
    const wrapper = taoWrapper();
    // Lấy ô nhập số lượng của dòng "Bút bi".
    const oNhap = layDong(wrapper, 0).get('[data-testid="o-nhap-so-luong"]');

    // Gõ số lượng mới là 7 vào ô nhập bằng cách gán giá trị vào phần tử DOM.
    oNhap.element.value = '7';
    // Chỉ kích hoạt sự kiện input, không nhấn Enter và chưa rời ô nhập.
    await oNhap.trigger('input');

    // Component cha phải cập nhật ngay số lượng bút bi thành 7.
    expect(layDong(wrapper, 0).props('sanPham').soLuong).toBe(7);
    // Tổng số lượng mới là 7 + 3 = 10.
    expect(wrapper.get('[data-testid="tong-so-luong"]').text()).toBe('10');
    // Tổng tiền mới là 7 × 5.000 + 3 × 12.000 = 71.000 đồng.
    expect(wrapper.get('[data-testid="tong-tien"]').text()).toBe('71.000 đồng');
    // Thành tiền riêng của dòng bút bi là 7 × 5.000 = 35.000 đồng.
    expect(layDong(wrapper, 0).get('[data-testid="thanh-tien"]').text()).toBe('35.000 đồng');
    // Ô nhập phải hiển thị đúng giá trị mới sau khi cha đồng bộ lại prop.
    expect(oNhap.element.value).toBe('7');
  });

  // Kiểm tra dữ liệu chưa hợp lệ thì chưa cập nhật, chỉ cập nhật khi đã hợp lệ.
  it('chưa cập nhật khi dữ liệu đang nhập chưa hợp lệ và cập nhật khi đã hợp lệ', async () => {
    // Mount ứng dụng với dữ liệu mẫu.
    const wrapper = taoWrapper();
    // Lấy ô nhập số lượng của dòng "Bút bi".
    const oNhap = layDong(wrapper, 0).get('[data-testid="o-nhap-so-luong"]');

    // Gõ giá trị 100 vượt quá giới hạn trên, dữ liệu chưa hợp lệ nên chưa được cập nhật.
    oNhap.element.value = '100';
    // Kích hoạt sự kiện input để kiểm tra lớp kiểm tra dữ liệu.
    await oNhap.trigger('input');

    // Số lượng bút bi phải giữ nguyên là 2 vì 100 không hợp lệ.
    expect(layDong(wrapper, 0).props('sanPham').soLuong).toBe(2);
    // Tổng số lượng phải giữ nguyên là 5.
    expect(wrapper.get('[data-testid="tong-so-luong"]').text()).toBe('5');
    // Tổng tiền phải giữ nguyên là 46.000 đồng.
    expect(wrapper.get('[data-testid="tong-tien"]').text()).toBe('46.000 đồng');

    // Sửa lại thành giá trị hợp lệ là 4.
    oNhap.element.value = '4';
    // Kích hoạt sự kiện input để dữ liệu hợp lệ được cập nhật ngay.
    await oNhap.trigger('input');

    // Số lượng bút bi phải được cập nhật thành 4.
    expect(layDong(wrapper, 0).props('sanPham').soLuong).toBe(4);
    // Tổng số lượng mới là 4 + 3 = 7.
    expect(wrapper.get('[data-testid="tong-so-luong"]').text()).toBe('7');
    // Tổng tiền mới là 4 × 5.000 + 3 × 12.000 = 56.000 đồng.
    expect(wrapper.get('[data-testid="tong-tien"]').text()).toBe('56.000 đồng');
  });

  // Kiểm tra trạng thái giỏ hàng trống sau khi xóa hết sản phẩm.
  it('hiển thị "Giỏ hàng trống" và tổng bằng 0 khi xóa hết sản phẩm', async () => {
    // Mount ứng dụng với dữ liệu mẫu.
    const wrapper = taoWrapper();

    // Bấm nút xóa của dòng đầu tiên để mở hộp xác nhận.
    await layDong(wrapper, 0).get('[data-testid="nut-xoa"]').trigger('click');
    // Xác nhận xóa dòng đầu tiên.
    await layDong(wrapper, 0).get('[data-testid="nut-xac-nhan-xoa"]').trigger('click');
    // Chờ Vue cập nhật danh sách sau lần xóa thứ nhất.
    await wrapper.vm.$nextTick();
    // Bấm nút xóa của dòng còn lại để mở hộp xác nhận.
    await layDong(wrapper, 0).get('[data-testid="nut-xoa"]').trigger('click');
    // Xác nhận xóa dòng còn lại.
    await layDong(wrapper, 0).get('[data-testid="nut-xac-nhan-xoa"]').trigger('click');
    // Chờ Vue cập nhật danh sách sau lần xóa thứ hai.
    await wrapper.vm.$nextTick();

    // Không còn component con nào trong giao diện.
    expect(wrapper.findAllComponents(DongGioHang)).toHaveLength(0);
    // Thông báo "Giỏ hàng trống" phải xuất hiện đúng nội dung yêu cầu.
    expect(wrapper.get('[data-testid="gio-hang-trong"]').text()).toBe('Giỏ hàng trống');
    // Tổng số lượng phải bằng 0.
    expect(wrapper.get('[data-testid="tong-so-luong"]').text()).toBe('0');
    // Tổng tiền phải được định dạng là 0 đồng.
    expect(wrapper.get('[data-testid="tong-tien"]').text()).toBe('0 đồng');
    // Số dòng sản phẩm phải bằng 0.
    expect(wrapper.get('[data-testid="so-dong-san-pham"]').text()).toBe('0');
  });
});
