// Nhập hàm describe, expect, it của Vitest để mô tả và kiểm tra kết quả.
import { describe, expect, it } from 'vitest';
// Nhập hàm định dạng tiền cần kiểm thử.
import { dinhDangTien } from '../src/utils/dinhDangTien.js';

// Nhóm các bài kiểm thử cho hàm định dạng tiền dùng chung.
describe('dinhDangTien', () => {
  // Kiểm tra giá trị 0 vẫn hiển thị rõ ràng, không bị rỗng.
  it('định dạng số 0 thành "0 đồng"', () => {
    // Gọi hàm với giá trị 0 và so sánh kết quả mong đợi.
    expect(dinhDangTien(0)).toBe('0 đồng');
  });

  // Kiểm tra số có ba chữ số không bị thêm dấu chấm thừa.
  it('giữ nguyên số có ba chữ số', () => {
    // Giá trị 999 không cần dấu phân cách hàng nghìn.
    expect(dinhDangTien(999)).toBe('999 đồng');
  });

  // Kiểm tra đơn giá 5.000 đồng theo dữ liệu mẫu của đề bài.
  it('định dạng 5000 thành "5.000 đồng"', () => {
    // Dấu chấm phải xuất hiện sau chữ số hàng nghìn.
    expect(dinhDangTien(5000)).toBe('5.000 đồng');
  });

  // Kiểm tra tổng tiền mẫu 46.000 đồng theo kết quả kiểm tra của đề bài.
  it('định dạng 46000 thành "46.000 đồng"', () => {
    // Chuỗi kết quả phải khớp chính xác để giao diện hiển thị đúng yêu cầu.
    expect(dinhDangTien(46000)).toBe('46.000 đồng');
  });

  // Kiểm tra số tiền lớn có nhiều nhóm hàng nghìn.
  it('định dạng số tiền lớn với nhiều dấu chấm phân cách', () => {
    // Giá trị 1.234.567 phải có dấu chấm sau mỗi nhóm ba chữ số.
    expect(dinhDangTien(1234567)).toBe('1.234.567 đồng');
  });

  // Kiểm tra số âm vẫn được định dạng đúng dấu.
  it('định dạng số âm với dấu trừ ở đầu', () => {
    // Dấu trừ phải đứng trước phần chữ số đã phân cách.
    expect(dinhDangTien(-5000)).toBe('-5.000 đồng');
  });

  // Kiểm tra giá trị không phải số hữu hạn được thay bằng 0 an toàn.
  it('trả về "0 đồng" cho giá trị không hữu hạn', () => {
    // NaN không phải số hữu hạn nên phải trả về mức 0.
    expect(dinhDangTien(NaN)).toBe('0 đồng');
    // Infinity không phải số hữu hạn nên cũng phải trả về mức 0.
    expect(dinhDangTien(Infinity)).toBe('0 đồng');
    // Chuỗi không chuyển được thành số sẽ tạo NaN và cũng trả về mức 0.
    expect(dinhDangTien('không phải số')).toBe('0 đồng');
  });
});
