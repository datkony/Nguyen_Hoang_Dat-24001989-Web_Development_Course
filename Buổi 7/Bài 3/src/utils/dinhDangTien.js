/**
 * Chuyển một giá trị số thành chuỗi tiền tệ Việt Nam dễ đọc.
 * Ví dụ: 46000 sẽ được định dạng thành "46.000 đồng".
 * Hàm được tách riêng để cả component cha và component con dùng chung,
 * tránh việc mỗi nơi định dạng một kiểu khác nhau.
 *
 * @param {number} giaTri Giá trị tiền cần định dạng.
 * @returns {string} Chuỗi tiền tệ đã được thêm dấu phân cách hàng nghìn.
 */
export function dinhDangTien(giaTri) {
  // Chuyển giá trị đầu vào về kiểu số để hàm xử lý được cả trường hợp nhận chuỗi số.
  const soDaChuyenDoi = Number(giaTri);
  // Nếu giá trị không phải là số hữu hạn (NaN, Infinity, -Infinity) thì trả về mức 0 an toàn.
  if (!Number.isFinite(soDaChuyenDoi)) {
    // Trả về chuỗi 0 đồng thay vì để giao diện hiển thị "NaN đồng" hoặc giá trị gây hiểu nhầm.
    return '0 đồng';
  }
  // Làm tròn về số nguyên vì đơn vị tiền tệ đồng không cần phần thập phân trong bài này.
  const soNguyen = Math.round(soDaChuyenDoi);
  // Xác định dấu âm để xử lý riêng, tránh dấu trừ chen vào giữa các nhóm chữ số.
  const laSoAm = soNguyen < 0;
  // Lấy giá trị tuyệt đối rồi chuyển thành chuỗi chữ số để chuẩn bị thêm dấu phân cách.
  const chuSo = Math.abs(soNguyen).toString();
  // Thêm dấu chấm sau mỗi nhóm ba chữ số bằng biểu thức chính quy.
  const chuSoCoDauCham = chuSo.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  // Ghép dấu âm (nếu có), phần chữ số đã định dạng và đơn vị tiền tệ thành kết quả cuối cùng.
  return `${laSoAm ? '-' : ''}${chuSoCoDauCham} đồng`;
}
