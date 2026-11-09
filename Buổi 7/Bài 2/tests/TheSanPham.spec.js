// Nhập hàm mount từ thư viện chính thức của Vue để gắn component vào DOM mô phỏng.
import { mount } from '@vue/test-utils';
// Nhập các hàm của Vitest để khai báo nhóm kiểm thử, từng bài kiểm thử và phép so sánh.
import { describe, it, expect } from 'vitest';
// Nhập component gốc App để kiểm tra việc quản lý danh sách và truyền props.
import App from '../src/App.vue';
// Nhập component thẻ sản phẩm để kiểm tra trực tiếp props, trạng thái và sự kiện.
import TheSanPham from '../src/components/TheSanPham.js';

// Tạo cấu hình dùng chung để đăng ký component thẻ sản phẩm ở cấp toàn cục trong môi trường kiểm thử.
// Việc này mô phỏng chính xác bước app.component('the-san-pham', TheSanPham) trong tệp main.js.
const cauHinhToanCuc = {
  // Khai báo các component toàn cục mà template của App có thể sử dụng.
  global: {
    // Đăng ký tên thẻ kebab-case đúng như trong template của App.
    components: {
      // Gán tên thẻ "the-san-pham" cho component TheSanPham đã import.
      'the-san-pham': TheSanPham,
    },
  },
};

// Nhóm kiểm thử cho component TheSanPham.
describe('TheSanPham', () => {
  // Bài kiểm thử xác nhận component khai báo đủ props, có prop bắt buộc và prop mặc định.
  it('khai báo đủ 5 props với kiểu dữ liệu, prop bắt buộc và prop mặc định', () => {
    // Lấy danh sách tên props đã khai báo trong component.
    const danhSachProp = Object.keys(TheSanPham.props);
    // Kiểm tra component có đúng 5 prop theo yêu cầu: tên, giá, ảnh, mô tả và số lượng tồn.
    expect(danhSachProp).toEqual(['ten', 'gia', 'anh', 'moTa', 'soLuongTon']);
    // Kiểm tra prop "ten" có khai báo kiểu String.
    expect(TheSanPham.props.ten.type).toBe(String);
    // Kiểm tra prop "ten" là prop bắt buộc.
    expect(TheSanPham.props.ten.required).toBe(true);
    // Kiểm tra prop "gia" có khai báo kiểu Number.
    expect(TheSanPham.props.gia.type).toBe(Number);
    // Kiểm tra prop "gia" cũng là prop bắt buộc.
    expect(TheSanPham.props.gia.required).toBe(true);
    // Kiểm tra prop "anh" có kiểu String.
    expect(TheSanPham.props.anh.type).toBe(String);
    // Kiểm tra prop "anh" có giá trị mặc định.
    expect(TheSanPham.props.anh.default).toBe('./images/placeholder.svg');
    // Kiểm tra prop "moTa" có kiểu String.
    expect(TheSanPham.props.moTa.type).toBe(String);
    // Kiểm tra prop "moTa" có giá trị mặc định.
    expect(TheSanPham.props.moTa.default).toBe('Sản phẩm này chưa có mô tả chi tiết.');
    // Kiểm tra prop "soLuongTon" có kiểu Number.
    expect(TheSanPham.props.soLuongTon.type).toBe(Number);
    // Kiểm tra prop "soLuongTon" có giá trị mặc định là 0.
    expect(TheSanPham.props.soLuongTon.default).toBe(0);
  });

  // Bài kiểm thử xác nhận dữ liệu nhận từ props được hiển thị đúng trên thẻ.
  it('hiển thị đúng tên, giá, ảnh và mô tả lấy từ props', async () => {
    // Gắn component với đầy đủ props do cha truyền xuống.
    const the = mount(TheSanPham, {
      // Truyền dữ liệu mẫu tương tự cách App truyền dữ liệu thật.
      props: {
        // Tên sản phẩm dùng để kiểm tra prop "ten".
        ten: 'Tai nghe kiểm thử',
        // Giá sản phẩm dùng để kiểm tra định dạng tiền tệ.
        gia: 890000,
        // Đường dẫn ảnh dùng để kiểm tra prop "anh".
        anh: './images/tai-nghe.svg',
        // Mô tả dùng để kiểm tra prop "moTa".
        moTa: 'Mô tả kiểm thử cho sản phẩm.',
        // Số lượng tồn dùng để kiểm tra nhãn còn hàng.
        soLuongTon: 5,
      },
    });

    // Kiểm tra tên sản phẩm xuất hiện trong phần tử hiển thị tên.
    expect(the.get('[data-testid="ten-san-pham"]').text()).toBe('Tai nghe kiểm thử');
    // Kiểm tra giá được định dạng có dấu phân cách hàng nghìn theo chuẩn Việt Nam.
    expect(the.get('[data-testid="gia-san-pham"]').text()).toContain('890.000');
    // Kiểm tra ảnh sử dụng đúng đường dẫn từ prop.
    expect(the.get('img').attributes('src')).toBe('./images/tai-nghe.svg');
    // Kiểm tra nhãn tồn kho hiển thị đúng số lượng còn lại.
    expect(the.get('[data-testid="nhan-ton-kho"]').text()).toBe('Còn 5 sản phẩm');

    // Bấm nút "Xem mô tả" để mở phần mô tả.
    await the.get('[data-testid="nut-mo-ta"]').trigger('click');
    // Kiểm tra mô tả lấy từ prop xuất hiện sau khi mở.
    expect(the.get('[data-testid="mo-ta-san-pham"]').text()).toBe('Mô tả kiểm thử cho sản phẩm.');
  });

  // Bài kiểm thử xác nhận prop mặc định hoạt động khi cha không truyền ảnh và mô tả.
  it('dùng giá trị mặc định của prop ảnh và mô tả khi cha không truyền', async () => {
    // Chỉ truyền hai prop bắt buộc là tên và giá, bỏ qua ảnh, mô tả và số lượng tồn.
    const the = mount(TheSanPham, {
      // Truyền dữ liệu tối thiểu để component hợp lệ.
      props: {
        // Tên sản phẩm bắt buộc.
        ten: 'Sản phẩm tối giản',
        // Giá sản phẩm bắt buộc.
        gia: 100000,
      },
    });

    // Kiểm tra ảnh dùng giá trị mặc định đã khai báo trong props.
    expect(the.get('img').attributes('src')).toBe('./images/placeholder.svg');
    // Mở phần mô tả để kiểm tra nội dung mặc định.
    await the.get('[data-testid="nut-mo-ta"]').trigger('click');
    // Kiểm tra mô tả mặc định xuất hiện khi cha không truyền prop "moTa".
    expect(the.get('[data-testid="mo-ta-san-pham"]').text()).toBe(
      'Sản phẩm này chưa có mô tả chi tiết.',
    );
    // Kiểm tra số lượng tồn mặc định là 0 nên sản phẩm được coi là hết hàng.
    expect(the.get('[data-testid="nhan-het-hang"]').exists()).toBe(true);
  });

  // Bài kiểm thử xác nhận thẻ hết hàng có nhãn riêng và nút Mua bị khóa.
  it('hiển thị nhãn hết hàng và khóa nút Mua khi số lượng tồn bằng 0', async () => {
    // Gắn component với số lượng tồn bằng 0.
    const the = mount(TheSanPham, {
      // Truyền dữ liệu tối thiểu và đặt số lượng tồn bằng 0.
      props: {
        // Tên sản phẩm hết hàng.
        ten: 'Sản phẩm hết hàng',
        // Giá sản phẩm hợp lệ.
        gia: 500000,
        // Số lượng tồn bằng 0 kích hoạt trạng thái hết hàng.
        soLuongTon: 0,
      },
    });

    // Kiểm tra nhãn riêng "Hết hàng" xuất hiện.
    expect(the.get('[data-testid="nhan-het-hang"]').text()).toBe('Hết hàng');
    // Kiểm tra nhãn số lượng còn hàng không xuất hiện.
    expect(the.find('[data-testid="nhan-ton-kho"]').exists()).toBe(false);
    // Kiểm tra nút Mua có thuộc tính disabled.
    expect(the.get('[data-testid="nut-mua"]').attributes('disabled')).toBeDefined();
    // Kiểm tra trạng thái aria-disabled cũng phản ánh việc nút bị khóa.
    expect(the.get('[data-testid="nut-mua"]').attributes('aria-disabled')).toBe('true');
    // Bấm thử nút Mua đang bị khóa để chắc chắn không phát sự kiện.
    await the.get('[data-testid="nut-mua"]').trigger('click');
    // Kiểm tra component không phát sự kiện "mua" khi sản phẩm hết hàng.
    expect(the.emitted('mua')).toBeUndefined();
  });

  // Bài kiểm thử xác nhận nút Mua hoạt động và phát sự kiện khi sản phẩm còn hàng.
  it('phát sự kiện mua kèm dữ liệu sản phẩm khi còn hàng', async () => {
    // Gắn component với số lượng tồn lớn hơn 0.
    const the = mount(TheSanPham, {
      // Truyền dữ liệu mẫu cho sản phẩm còn hàng.
      props: {
        // Tên sản phẩm còn hàng.
        ten: 'Sản phẩm còn hàng',
        // Giá sản phẩm hợp lệ.
        gia: 750000,
        // Số lượng tồn lớn hơn 0 để bật nút Mua.
        soLuongTon: 3,
      },
    });

    // Kiểm tra nút Mua không bị khóa.
    expect(the.get('[data-testid="nut-mua"]').attributes('disabled')).toBeUndefined();
    // Bấm nút Mua để phát sự kiện lên cha.
    await the.get('[data-testid="nut-mua"]').trigger('click');
    // Lấy danh sách các lần sự kiện "mua" đã được phát.
    const cacLanMua = the.emitted('mua');
    // Kiểm tra sự kiện đã được phát đúng một lần.
    expect(cacLanMua).toHaveLength(1);
    // Kiểm tra dữ liệu kèm sự kiện chứa đúng tên, giá và số lượng tồn.
    expect(cacLanMua[0][0]).toEqual({
      // Tên sản phẩm gửi lên cha.
      ten: 'Sản phẩm còn hàng',
      // Giá sản phẩm gửi lên cha.
      gia: 750000,
      // Số lượng tồn gửi lên cha.
      soLuongTon: 3,
    });
  });

  // Bài kiểm thử xác nhận ảnh lỗi được thay bằng ảnh dự phòng an toàn.
  it('chuyển sang ảnh dự phòng khi ảnh gốc không tải được', async () => {
    // Gắn component với đường dẫn ảnh không tồn tại để mô phỏng lỗi tải ảnh.
    const the = mount(TheSanPham, {
      // Truyền dữ liệu tối thiểu với ảnh sai đường dẫn.
      props: {
        // Tên sản phẩm hợp lệ.
        ten: 'Sản phẩm lỗi ảnh',
        // Giá sản phẩm hợp lệ.
        gia: 100000,
        // Đường dẫn ảnh cố ý không tồn tại.
        anh: './images/khong-ton-tai.svg',
      },
    });

    // Kích hoạt sự kiện lỗi ảnh trên thẻ img.
    await the.get('img').trigger('error');
    // Kiểm tra ảnh đã được thay bằng data URI dự phòng.
    expect(the.get('img').attributes('src')).toMatch(/^data:image\/svg\+xml/);
  });

  // Bài kiểm thử xác nhận component vẫn nhận ảnh mới sau khi đã dùng ảnh dự phòng.
  it('cập nhật ảnh mới khi cha thay đổi prop anh', async () => {
    // Gắn component với đường dẫn ảnh sai để kích hoạt ảnh dự phòng trước.
    const the = mount(TheSanPham, {
      // Truyền dữ liệu tối thiểu với ảnh sai đường dẫn.
      props: {
        // Tên sản phẩm hợp lệ.
        ten: 'Sản phẩm đổi ảnh',
        // Giá sản phẩm hợp lệ.
        gia: 100000,
        // Đường dẫn ảnh cố ý không tồn tại.
        anh: './images/khong-ton-tai.svg',
      },
    });

    // Kích hoạt lỗi ảnh để component chuyển sang ảnh dự phòng.
    await the.get('img').trigger('error');
    // Kiểm tra ảnh dự phòng đã được áp dụng.
    expect(the.get('img').attributes('src')).toMatch(/^data:image\/svg\+xml/);
    // Cập nhật prop "anh" sang một đường dẫn hợp lệ mới.
    await the.setProps({ anh: './images/tai-nghe.svg' });
    // Kiểm tra ảnh mới được hiển thị thay vì giữ ảnh dự phòng cũ.
    expect(the.get('img').attributes('src')).toBe('./images/tai-nghe.svg');
  });
});

// Nhóm kiểm thử cho component cha App.
describe('App', () => {
  // Bài kiểm thử xác nhận component cha quản lý ít nhất bốn sản phẩm.
  it('quản lý danh sách có ít nhất bốn sản phẩm', () => {
    // Gắn App với component thẻ sản phẩm được đăng ký toàn cục.
    const app = mount(App, cauHinhToanCuc);
    // Đếm số thẻ sản phẩm được render từ vòng lặp v-for.
    const soThe = app.findAll('[data-testid="the-san-pham"]').length;
    // Kiểm tra số thẻ không nhỏ hơn 4 theo yêu cầu tối thiểu.
    expect(soThe).toBeGreaterThanOrEqual(4);
    // Kiểm tra chỉ số tổng sản phẩm hiển thị đúng bằng số thẻ.
    expect(app.get('[data-testid="tong-san-pham"]').text()).toBe(String(soThe));
  });

  // Bài kiểm thử xác nhận trạng thái mở mô tả độc lập giữa các thẻ.
  it('mở mô tả ở một thẻ không làm ảnh hưởng đến các thẻ khác', async () => {
    // Gắn App với component thẻ sản phẩm được đăng ký toàn cục.
    const app = mount(App, cauHinhToanCuc);
    // Lấy danh sách tất cả thẻ sản phẩm đang hiển thị.
    const cacThe = app.findAll('[data-testid="the-san-pham"]');
    // Lấy nút mở mô tả của thẻ thứ nhất.
    const nutTheThuNhat = cacThe[0].get('[data-testid="nut-mo-ta"]');
    // Lấy nút mở mô tả của thẻ thứ hai.
    const nutTheThuHai = cacThe[1].get('[data-testid="nut-mo-ta"]');

    // Ban đầu chưa có thẻ nào mở mô tả.
    expect(app.findAll('[data-testid="mo-ta-san-pham"]')).toHaveLength(0);

    // Mở mô tả của thẻ thứ nhất.
    await nutTheThuNhat.trigger('click');
    // Kiểm tra chỉ có đúng một phần mô tả xuất hiện.
    expect(app.findAll('[data-testid="mo-ta-san-pham"]')).toHaveLength(1);
    // Kiểm tra thẻ thứ nhất có phần mô tả.
    expect(cacThe[0].find('[data-testid="mo-ta-san-pham"]').exists()).toBe(true);
    // Kiểm tra thẻ thứ hai vẫn đóng mô tả.
    expect(cacThe[1].find('[data-testid="mo-ta-san-pham"]').exists()).toBe(false);

    // Mở mô tả của thẻ thứ hai.
    await nutTheThuHai.trigger('click');
    // Kiểm tra lúc này có hai phần mô tả cùng mở.
    expect(app.findAll('[data-testid="mo-ta-san-pham"]')).toHaveLength(2);
    // Kiểm tra thẻ thứ hai đã mở mô tả.
    expect(cacThe[1].find('[data-testid="mo-ta-san-pham"]').exists()).toBe(true);

    // Đóng mô tả của thẻ thứ nhất.
    await nutTheThuNhat.trigger('click');
    // Kiểm tra thẻ thứ nhất đã đóng mô tả.
    expect(cacThe[0].find('[data-testid="mo-ta-san-pham"]').exists()).toBe(false);
    // Kiểm tra thẻ thứ hai vẫn giữ trạng thái mở mô tả.
    expect(cacThe[1].find('[data-testid="mo-ta-san-pham"]').exists()).toBe(true);
  });

  // Bài kiểm thử xác nhận App nhận sự kiện mua từ con và hiển thị thông báo.
  it('hiển thị thông báo khi nhận sự kiện mua từ thẻ sản phẩm', async () => {
    // Gắn App với component thẻ sản phẩm được đăng ký toàn cục.
    const app = mount(App, cauHinhToanCuc);
    // Lấy thẻ sản phẩm đầu tiên (sản phẩm này còn hàng).
    const theDauTien = app.findAll('[data-testid="the-san-pham"]')[0];
    // Lấy tên sản phẩm của thẻ đầu tiên để đối chiếu thông báo.
    const tenSanPham = theDauTien.get('[data-testid="ten-san-pham"]').text();
    // Bấm nút Mua của thẻ đầu tiên.
    await theDauTien.get('[data-testid="nut-mua"]').trigger('click');
    // Lấy phần tử thông báo mua hàng.
    const thongBao = app.get('[data-testid="thong-bao-mua"]');
    // Kiểm tra thông báo có chứa tên sản phẩm vừa chọn.
    expect(thongBao.text()).toContain(tenSanPham);
  });

  // Bài kiểm thử xác nhận tồn kho giảm đúng 1 sau mỗi lần mua thành công.
  it('giảm đúng 1 đơn vị tồn kho sau mỗi lần mua thành công', async () => {
    // Gắn App với component thẻ sản phẩm được đăng ký toàn cục.
    const app = mount(App, cauHinhToanCuc);
    // Lấy danh sách tất cả thẻ sản phẩm đang hiển thị.
    const cacThe = app.findAll('[data-testid="the-san-pham"]');
    // Lấy thẻ đầu tiên có số lượng tồn ban đầu là 15.
    const theDauTien = cacThe[0];

    // Kiểm tra nhãn tồn kho ban đầu của thẻ đầu tiên.
    expect(theDauTien.get('[data-testid="nhan-ton-kho"]').text()).toBe('Còn 15 sản phẩm');

    // Mua lần thứ nhất bằng nút Mua của thẻ đầu tiên.
    await theDauTien.get('[data-testid="nut-mua"]').trigger('click');
    // Kiểm tra tồn kho giảm từ 15 xuống 14.
    expect(theDauTien.get('[data-testid="nhan-ton-kho"]').text()).toBe('Còn 14 sản phẩm');
    // Kiểm tra thông báo mua hàng có nêu đúng số lượng còn lại sau khi trừ.
    expect(app.get('[data-testid="thong-bao-mua"]').text()).toContain('Số lượng còn lại: 14 sản phẩm');

    // Mua lần thứ hai bằng nút Mua của cùng thẻ.
    await theDauTien.get('[data-testid="nut-mua"]').trigger('click');
    // Kiểm tra tồn kho tiếp tục giảm từ 14 xuống 13.
    expect(theDauTien.get('[data-testid="nhan-ton-kho"]').text()).toBe('Còn 13 sản phẩm');
    // Kiểm tra thông báo cập nhật đúng số lượng còn lại của lần mua thứ hai.
    expect(app.get('[data-testid="thong-bao-mua"]').text()).toContain('Số lượng còn lại: 13 sản phẩm');

    // Kiểm tra sản phẩm khác không bị trừ tồn kho khi chỉ mua sản phẩm đầu tiên.
    expect(cacThe[2].get('[data-testid="nhan-ton-kho"]').text()).toBe('Còn 7 sản phẩm');
  });

  // Bài kiểm thử xác nhận sản phẩm chuyển sang hết hàng khi tồn kho về 0 và không bị trừ âm.
  it('khóa sản phẩm khi mua hết tồn kho và không trừ xuống số âm', async () => {
    // Gắn App với component thẻ sản phẩm được đăng ký toàn cục.
    const app = mount(App, cauHinhToanCuc);
    // Lấy danh sách tất cả thẻ sản phẩm đang hiển thị.
    const cacThe = app.findAll('[data-testid="the-san-pham"]');
    // Chọn sản phẩm "Nồi chiên không dầu" có số lượng tồn ban đầu là 4.
    const theNoiChien = cacThe[3];

    // Mua liên tiếp 4 lần để đưa số lượng tồn của sản phẩm về 0.
    for (let lanMua = 1; lanMua <= 4; lanMua += 1) {
      // Bấm nút Mua của sản phẩm đang được chọn.
      await theNoiChien.get('[data-testid="nut-mua"]').trigger('click');
    }

    // Kiểm tra nhãn "Hết hàng" xuất hiện sau khi tồn kho về 0.
    expect(theNoiChien.get('[data-testid="nhan-het-hang"]').text()).toBe('Hết hàng');
    // Kiểm tra nhãn số lượng còn hàng không còn hiển thị.
    expect(theNoiChien.find('[data-testid="nhan-ton-kho"]').exists()).toBe(false);
    // Kiểm tra nút Mua đã bị khóa bằng thuộc tính disabled.
    expect(theNoiChien.get('[data-testid="nut-mua"]').attributes('disabled')).toBeDefined();
    // Kiểm tra thông báo của lần mua cuối cùng cho biết đây là sản phẩm cuối trong kho.
    expect(app.get('[data-testid="thong-bao-mua"]').text()).toContain('sản phẩm cuối cùng');
    // Kiểm tra thống kê số sản phẩm hết hàng tăng từ 2 lên 3.
    expect(app.get('[data-testid="so-san-pham-het-hang"]').text()).toBe('3');
    // Kiểm tra thống kê số sản phẩm còn hàng giảm từ 4 xuống 3.
    expect(app.get('[data-testid="so-san-pham-con-hang"]').text()).toBe('3');

    // Gọi trực tiếp phương thức xử lý mua để mô phỏng trường hợp nút bị kích hoạt bằng mã lệnh.
    app.vm.xuLyMua(app.vm.danhSachSanPham[3], {
      // Gửi đúng tên sản phẩm để vượt qua bước kiểm tra dữ liệu sự kiện.
      ten: 'Nồi chiên không dầu',
      // Gửi đúng giá sản phẩm theo dữ liệu hiện tại.
      gia: 1790000,
      // Gửi số lượng tồn hiện tại là 0.
      soLuongTon: 0,
    });

    // Kiểm tra tồn kho vẫn giữ nguyên 0, không bị trừ thành số âm.
    expect(app.vm.danhSachSanPham[3].soLuongTon).toBe(0);
    // Kiểm tra thông báo cho biết sản phẩm đã hết hàng và không thể mua thêm.
    expect(app.vm.thongBaoMua).toContain('đã hết hàng');
  });

  // Bài kiểm thử xác nhận thống kê hết hàng khớp với dữ liệu thực tế.
  it('thống kê đúng số sản phẩm còn hàng và hết hàng', () => {
    // Gắn App với component thẻ sản phẩm được đăng ký toàn cục.
    const app = mount(App, cauHinhToanCuc);
    // Lấy danh sách tất cả thẻ sản phẩm.
    const cacThe = app.findAll('[data-testid="the-san-pham"]');
    // Đếm số thẻ có nhãn hết hàng trong giao diện.
    const soTheHetHang = app.findAll('[data-testid="nhan-het-hang"]').length;
    // Kiểm tra chỉ số hết hàng hiển thị đúng với số nhãn thực tế.
    expect(app.get('[data-testid="so-san-pham-het-hang"]').text()).toBe(String(soTheHetHang));
    // Kiểm tra tổng số sản phẩm còn hàng và hết hàng bằng tổng số thẻ.
    const soConHang = Number(app.get('[data-testid="so-san-pham-con-hang"]').text());
    // Kiểm tra phép cộng hai chỉ số khớp với tổng số thẻ.
    expect(soConHang + soTheHetHang).toBe(cacThe.length);
  });
});
