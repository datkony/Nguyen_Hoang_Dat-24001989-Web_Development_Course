<!--
  ============================================================================
  BÀI 1: TRANG HIỂN THỊ THÔNG TIN MỘT SINH VIÊN (VUE 3 - SINGLE FILE COMPONENT)
  ----------------------------------------------------------------------------
  File này gồm 3 phần chuẩn của một Vue SFC:
    1. <template> : phần giao diện (HTML + các chỉ thị của Vue)
    2. <script>   : phần logic (dữ liệu, computed, methods)
    3. <style>    : phần trình bày (CSS), dùng "scoped" để chỉ áp dụng cho file này

  Ngoài các yêu cầu cơ bản của đề bài, phiên bản này bổ sung phần KIỂM TRA
  DỮ LIỆU ĐẦU VÀO ngay khi người dùng nhập:
    - Điểm trung bình: bắt buộc là số trong khoảng 0 – 10. Giá trị lớn hơn 10
      (hoặc âm, hoặc không phải số) bị TỪ CHỐI NGAY, không ghi vào dữ liệu,
      đồng thời hiện thông báo lỗi màu đỏ.
    - Họ tên: không được để trống, không quá 50 ký tự, chỉ chứa chữ cái
      (kể cả chữ có dấu tiếng Việt), khoảng trắng và các ký tự . ' -
    - Tên lớp: không được để trống, không quá 30 ký tự, chỉ chứa chữ cái,
      chữ số, khoảng trắng và các ký tự . _ -
  Nhờ vậy dữ liệu hiển thị trên thẻ sinh viên luôn hợp lệ, và mọi thay đổi
  hợp lệ đều được cập nhật tức thì (tính reactivity của Vue).
  ============================================================================
-->

<template>
  <!--
    Thẻ <div class="trang-sinh-vien"> là phần tử gốc (root) của component.
    Mỗi component trong Vue chỉ được có DUY NHẤT một phần tử gốc ở cấp ngoài cùng.
    Class "trang-sinh-vien" dùng để định dạng khung nền cho cả trang.
  -->
  <div class="trang-sinh-vien">

    <!--
      Tiêu đề trang.
      Đây là nội dung tĩnh nên không cần dùng chỉ thị nào của Vue.
    -->
    <h1 class="tieu-de">Thông tin sinh viên</h1>

    <!--
      ==================== THẺ HIỂN THỊ THÔNG TIN SINH VIÊN ====================
      <section> là thẻ ngữ nghĩa (semantic) dùng để nhóm nội dung.
    -->
    <section class="the-sinh-vien">

      <!--
        ẢNH ĐẠI DIỆN (Yêu cầu 3: dùng v-bind)
        - v-bind:src="anhDaiDien"  -> gắn giá trị của biến "anhDaiDien" vào thuộc tính src.
          Khi "anhDaiDien" trong data thay đổi, ảnh sẽ tự đổi theo (nhờ cơ chế reactivity).
        - v-bind:alt="hoTen"       -> gắn nội dung thay thế (alt text) bằng họ tên sinh viên.
          Nếu ảnh bị lỗi không tải được, trình duyệt sẽ hiện họ tên thay cho ảnh.
          Khi họ tên được sửa ở phần kiểm tra bên dưới, alt của ảnh cũng tự cập nhật.
        - Có thể viết ngắn gọn là :src và :alt, nhưng ở đây dùng dạng đầy đủ
          "v-bind:" để bám sát yêu cầu của đề bài.
      -->
      <img
        v-bind:src="anhDaiDien"
        v-bind:alt="hoTen"
        class="anh-dai-dien"
      />

      <!--
        Khối chứa các dòng thông tin dạng chữ.
      -->
      <div class="khoi-thong-tin">

        <!--
          HỌ TÊN (Yêu cầu 2: dùng nội suy {{ }})
          Cặp dấu {{ hoTen }} là cú pháp "mustache" (nội suy văn bản) của Vue.
          Vue sẽ thay thế {{ hoTen }} bằng giá trị hiện tại của biến "hoTen".
        -->
        <h2 class="ho-ten">{{ hoTen }}</h2>

        <!--
          MÃ SINH VIÊN
          Vẫn dùng nội suy {{ }} để hiển thị giá trị của biến "maSinhVien".
          Mã sinh viên là dữ liệu cố định, không cho sửa trên giao diện.
        -->
        <p class="dong-thong-tin">
          <span class="nhan">Mã sinh viên:</span>
          {{ maSinhVien }}
        </p>

        <!--
          TÊN LỚP (Yêu cầu 2: dùng v-text)
          Chỉ thị v-text="tenLop" sẽ GHI ĐÈ toàn bộ nội dung bên trong thẻ <span>
          bằng giá trị của biến "tenLop".
          Lưu ý: v-text thay thế toàn bộ nội dung con, nên không dùng {{ }} bên trong nữa.
        -->
        <p class="dong-thong-tin">
          <span class="nhan">Lớp:</span>
          <span v-text="tenLop"></span>
        </p>

        <!--
          ĐIỂM TRUNG BÌNH
          - {{ diemTrungBinhHienThi }} là một computed (thuộc tính tính toán).
            Computed này định dạng điểm cho dễ đọc (ví dụ 8.2 -> "8.2", 8 -> "8").
          - Dữ liệu gốc vẫn là số "diemTrungBinh" dùng để tính xếp loại.
        -->
        <p class="dong-thong-tin">
          <span class="nhan">Điểm trung bình:</span>
          {{ diemTrungBinhHienThi }}
          <span class="don-vi">/ 10</span>
        </p>

        <!--
          XẾP LOẠI (Yêu cầu 4 + Yêu cầu 5)
          - {{ xepLoai }}         : hiển thị chuỗi xếp loại do computed tính ra
                                    (Giỏi / Khá / Trung bình / Chưa đạt).
                                    Nếu dữ liệu điểm không hợp lệ (phòng trường hợp
                                    bị gán sai từ bên ngoài giao diện) thì hiển thị
                                    "Không hợp lệ" thay vì xếp loại sai.
          - :class="mauXepLoai"   : ràng buộc động class CSS theo xếp loại.
                                    Computed "mauXepLoai" trả về một trong các class:
                                    loai-gioi, loai-kha, loai-trung-binh,
                                    loai-chua-dat, loai-khong-hop-le.
                                    Nhờ đó màu sắc tự đổi khi điểm trung bình thay đổi.
          - Dùng dấu hai chấm ":" là cách viết tắt của v-bind:class.
        -->
        <p class="dong-thong-tin">
          <span class="nhan">Xếp loại:</span>
          <span class="nhan-xep-loai" :class="mauXepLoai">{{ xepLoai }}</span>
        </p>

        <!--
          TRẠNG THÁI HỌC TẬP (Yêu cầu 6)
          - {{ trangThai }}     : computed trả về "Đang học" hoặc "Đã nghỉ"
                                  dựa trên giá trị boolean "dangHoc".
          - :class="mauTrangThai" : đổi màu nhãn theo trạng thái
                                  (xanh cho "Đang học", xám cho "Đã nghỉ").
        -->
        <p class="dong-thong-tin">
          <span class="nhan">Trạng thái:</span>
          <span class="nhan-trang-thai" :class="mauTrangThai">{{ trangThai }}</span>
        </p>

      </div>
    </section>

    <!--
      ==================== NÚT BẬT/TẮT PHẦN GIỚI THIỆU (Yêu cầu 7) ====================
      - @click="hienGioiThieu = !hienGioiThieu" : khi bấm nút thì đảo giá trị boolean.
        "@click" là viết tắt của "v-on:click" (lắng nghe sự kiện click chuột).
      - Nội dung nút thay đổi theo trạng thái: nếu đang hiện thì ghi "Ẩn giới thiệu",
        nếu đang ẩn thì ghi "Hiện giới thiệu" -> dùng toán tử ba ngôi trong {{ }}.
    -->
    <button class="nut-bat-tat" @click="hienGioiThieu = !hienGioiThieu">
      {{ hienGioiThieu ? 'Ẩn giới thiệu' : 'Hiện giới thiệu' }}
    </button>

    <!--
      PHẦN GIỚI THIỆU (Yêu cầu 7: dùng v-show)
      - v-show="hienGioiThieu" : khi biến "hienGioiThieu" là true thì khối này hiển thị,
        khi là false thì bị ẩn đi.
      - Khác với v-if: v-show KHÔNG xoá phần tử khỏi DOM, nó chỉ thêm/bớt
        thuộc tính style="display: none". Vì vậy bật/tắt rất nhanh và giữ nguyên trạng thái.
    -->
    <section v-show="hienGioiThieu" class="gioi-thieu">
      <!-- Tiêu đề nhỏ của khối giới thiệu. -->
      <h3>Giới thiệu</h3>

      <!--
        Nội dung giới thiệu, lấy từ biến "gioiThieu" trong data.
        Dùng nội suy {{ }} để hiển thị.
      -->
      <p>{{ gioiThieu }}</p>

      <!--
        Hiển thị thêm sở thích (một mảng chuỗi) bằng v-for để minh hoạ dữ liệu động.
        - v-for="(soThich, viTri) in danhSachSoThich" : lặp qua từng phần tử của mảng.
          "soThich" là giá trị từng phần tử, "viTri" là chỉ số (0, 1, 2, ...).
        - :key="viTri" : gán khoá duy nhất cho mỗi phần tử để Vue tối ưu việc render.
      -->
      <ul class="danh-sach-so-thich">
        <li v-for="(soThich, viTri) in danhSachSoThich" :key="viTri">
          {{ soThich }}
        </li>
      </ul>
    </section>

    <!--
      ==================== PHẦN KIỂM TRA THAY ĐỔI DỮ LIỆU ====================
      Mục đích:
        1. Chứng minh tính reactivity: sửa dữ liệu -> thẻ sinh viên phía trên
           và màu sắc xếp loại tự cập nhật ngay, không cần tải lại trang.
        2. Kiểm tra dữ liệu đầu vào: điểm ngoài khoảng 0 – 10, họ tên/lớp
           không hợp lệ sẽ bị từ chối ngay và hiện thông báo lỗi.
    -->
    <section class="bang-dieu-khien">

      <!-- Tiêu đề của phần kiểm tra. -->
      <h3>Kiểm tra thay đổi dữ liệu</h3>

      <!--
        Dòng gợi ý ngắn, giúp người dùng biết phải làm gì.
      -->
      <p class="goi-y">
        Sửa họ tên, lớp hoặc điểm bên dưới để thấy thông tin phía trên thay đổi ngay.
        Điểm phải nằm trong khoảng 0 – 10.
      </p>

      <!--
        Khung chứa 3 ô nhập, xếp thành cột dọc.
      -->
      <div class="bieu-mau">

        <!-- ==================== Ô NHẬP HỌ TÊN ==================== -->
        <div class="hang-nhap">

          <!--
            <label for="o-ho-ten"> : nhãn của ô nhập.
            Thuộc tính "for" phải trùng với "id" của ô nhập để khi bấm vào nhãn
            thì con trỏ nhảy vào ô nhập (tăng khả năng truy cập - accessibility).
          -->
          <label class="nhan-nhap" for="o-ho-ten">Họ tên</label>

          <!--
            Ô nhập họ tên.
            - :value="hoTenNhap"     : hiển thị giá trị của biến tạm "hoTenNhap"
                                       (bản nháp đang gõ), KHÔNG phải dữ liệu chính thức.
            - @input="kiemTraHoTen"  : mỗi khi gõ 1 ký tự, gọi hàm kiểm tra ngay lập tức.
            - @blur="chotHoTen"      : khi rời khỏi ô, chuẩn hoá lại giá trị
                                       (bỏ khoảng trắng thừa) hoặc khôi phục nếu lỗi.
            - maxlength="50"         : chặn cứng việc gõ quá 50 ký tự.
            - :class="{ 'o-nhap-loi': loiHoTen !== '' }" : thêm viền đỏ khi có lỗi.
            - :aria-invalid           : báo cho trình đọc màn hình biết ô đang sai.
            - aria-describedby        : liên kết ô nhập với dòng thông báo lỗi.
            Lưu ý: KHÔNG dùng v-model ở đây vì v-model sẽ ghi thẳng giá trị vào dữ liệu
            chính thức, trong khi ta cần kiểm tra trước rồi mới quyết định có ghi hay không.
          -->
          <input
            id="o-ho-ten"
            v-bind:value="hoTenNhap"
            @input="kiemTraHoTen"
            @blur="chotHoTen"
            type="text"
            maxlength="50"
            placeholder="Ví dụ: Nguyễn Văn An"
            :class="{ 'o-nhap-loi': loiHoTen !== '' }"
            :aria-invalid="loiHoTen !== ''"
            aria-describedby="loi-ho-ten"
          />

          <!--
            Chỉ hiển thị dòng lỗi KHI biến "loiHoTen" có nội dung (khác chuỗi rỗng).
            - v-if="loiHoTen" : thêm/xoá phần tử khỏi DOM theo điều kiện.
            - role="alert"    : yêu cầu trình đọc màn hình đọc ngay thông báo lỗi.
          -->
          <p
            v-if="loiHoTen"
            id="loi-ho-ten"
            class="thong-bao-loi"
            role="alert"
          >
            {{ loiHoTen }}
          </p>
        </div>

        <!-- ==================== Ô NHẬP TÊN LỚP ==================== -->
        <div class="hang-nhap">

          <!-- Nhãn của ô nhập lớp, liên kết với id "o-ten-lop". -->
          <label class="nhan-nhap" for="o-ten-lop">Lớp</label>

          <!--
            Ô nhập tên lớp, hoạt động giống ô họ tên:
            - :value="tenLopNhap"       : hiển thị bản nháp đang gõ.
            - @input="kiemTraTenLop"    : kiểm tra ngay từng ký tự.
            - @blur="chotTenLop"        : chuẩn hoá khi rời ô.
            - maxlength="30"            : giới hạn độ dài.
          -->
          <input
            id="o-ten-lop"
            v-bind:value="tenLopNhap"
            @input="kiemTraTenLop"
            @blur="chotTenLop"
            type="text"
            maxlength="30"
            placeholder="Ví dụ: CNTT K18A"
            :class="{ 'o-nhap-loi': loiTenLop !== '' }"
            :aria-invalid="loiTenLop !== ''"
            aria-describedby="loi-ten-lop"
          />

          <!-- Dòng thông báo lỗi của ô lớp, chỉ xuất hiện khi có lỗi. -->
          <p
            v-if="loiTenLop"
            id="loi-ten-lop"
            class="thong-bao-loi"
            role="alert"
          >
            {{ loiTenLop }}
          </p>
        </div>

        <!-- ==================== Ô NHẬP ĐIỂM TRUNG BÌNH ==================== -->
        <div class="hang-nhap">

          <!-- Nhãn của ô nhập điểm. -->
          <label class="nhan-nhap" for="o-diem">Điểm TB</label>

          <!--
            Ô nhập điểm, áp dụng kiểm tra nghiêm ngặt nhất:
            - :value="diemNhap"     : bản nháp dạng chuỗi (cho phép gõ dở như "8.").
            - @input="kiemTraDiem"  : kiểm tra NGAY khi gõ. Nếu điểm > 10 hoặc < 0
                                      hoặc không phải số -> không cập nhật dữ liệu,
                                      hiện thông báo lỗi, viền ô chuyển đỏ.
            - @blur="chotDiem"      : khi rời ô, nếu dữ liệu hợp lệ thì hiển thị lại
                                      điểm đã chuẩn hoá; nếu đang lỗi thì khôi phục
                                      về giá trị hợp lệ gần nhất.
            - type="text" + inputmode="decimal" :
                                      dùng ô nhập chữ (không dùng type="number") để
                                      ta tự kiểm soát toàn bộ chuỗi người dùng gõ,
                                      tránh các giá trị lạ mà type="number" sinh ra.
                                      inputmode="decimal" giúp điện thoại hiện bàn phím số.
            - maxlength="5"         : đủ cho "10.00" nhưng chặn các chuỗi quá dài.
            - :class="{ 'o-nhap-loi': loiDiem !== '' }" : viền đỏ khi điểm không hợp lệ.
          -->
          <input
            id="o-diem"
            v-bind:value="diemNhap"
            @input="kiemTraDiem"
            @blur="chotDiem"
            type="text"
            inputmode="decimal"
            maxlength="5"
            placeholder="0 – 10"
            :class="{ 'o-nhap-loi': loiDiem !== '' }"
            :aria-invalid="loiDiem !== ''"
            aria-describedby="loi-diem"
          />

          <!-- Dòng thông báo lỗi của ô điểm, chỉ xuất hiện khi có lỗi. -->
          <p
            v-if="loiDiem"
            id="loi-diem"
            class="thong-bao-loi"
            role="alert"
          >
            {{ loiDiem }}
          </p>
        </div>
      </div>

      <!--
        Hai nút thao tác:
        - Nút đổi trạng thái: gọi method "doiTrangThai" (đảo giá trị dangHoc).
        - Nút khôi phục: gọi method "khoiPhucDuLieu" (trả mọi dữ liệu về ban đầu),
          đồng thời xoá các thông báo lỗi và đồng bộ lại các ô nhập.
      -->
      <div class="hang-nut">
        <button class="nut-phu" @click="doiTrangThai">
          Đổi trạng thái học tập
        </button>
        <button class="nut-phu" @click="khoiPhucDuLieu">
          Khôi phục dữ liệu ban đầu
        </button>
      </div>

    </section>

  </div>
</template>

<script>
/*
  ============================================================================
  DỮ LIỆU BAN ĐẦU DÙNG CHUNG
  ----------------------------------------------------------------------------
  Gom các giá trị mặc định vào MỘT hằng số để tránh "hard code" lặp lại ở nhiều nơi.
  - Object.freeze(...) : "đóng băng" object, không cho phép sửa thuộc tính,
                         tránh vô tình làm thay đổi dữ liệu gốc.
  - Khi cần khôi phục dữ liệu, chỉ cần đọc lại từ hằng số này.
  ============================================================================
*/
const DU_LIEU_BAN_DAU = Object.freeze({
  hoTen: 'Nguyễn Văn An',
  tenLop: 'CNTT K18A',
  diemTrungBinh: 8.2,
});

/*
  ============================================================================
  PHẦN LOGIC CỦA COMPONENT
  Sử dụng Options API (data / computed / methods) vì cách viết này trực quan,
  phù hợp với nội dung môn học và dễ đọc với người mới học Vue.
  ============================================================================
*/
export default {
  /*
    "name" là tên của component.
    Tên này giúp Vue DevTools hiển thị đúng tên khi gỡ lỗi (debug).
  */
  name: 'Bai1ThongTinSinhVien',

  /*
    ------------------------------------------------------------------------
    data(): hàm trả về một OBJECT chứa TOÀN BỘ dữ liệu (state) của component.
    Mọi thuộc tính khai báo ở đây đều có tính PHẢN ỨNG (reactive):
    khi giá trị thay đổi, Vue tự động cập nhật lại giao diện liên quan.
    ------------------------------------------------------------------------
  */
  data() {
    return {
      /*
        Mã sinh viên: kiểu chuỗi (String).
        Dùng chuỗi vì mã sinh viên có thể chứa chữ cái (ví dụ "SV001").
      */
      maSinhVien: 'SV001',

      /*
        Họ và tên sinh viên: kiểu chuỗi (String) - ĐÂY LÀ DỮ LIỆU CHÍNH THỨC.
        Chỉ được cập nhật khi giá trị nhập vào vượt qua kiểm tra hợp lệ.
        Giá trị này được hiển thị bằng {{ hoTen }} và dùng làm alt của ảnh.
      */
      hoTen: DU_LIEU_BAN_DAU.hoTen,

      /*
        Tên lớp: kiểu chuỗi (String) - DỮ LIỆU CHÍNH THỨC.
        Giá trị này được hiển thị bằng chỉ thị v-text.
      */
      tenLop: DU_LIEU_BAN_DAU.tenLop,

      /*
        Đường dẫn ảnh đại diện: kiểu chuỗi (String).
        - 'avatar.svg' là file ảnh mẫu đặt trong thư mục public/ của dự án.
          Vite tự phục vụ các file trong public/ ở đường dẫn gốc, nên chỉ cần ghi tên file.
        - Muốn dùng ảnh thật của sinh viên: chép ảnh vào thư mục public/
          (ví dụ public/anh-sinh-vien.jpg) rồi đổi thành './anh-sinh-vien.jpg'.
        - Ưu điểm: ảnh nằm ngay trong dự án nên trang vẫn hiển thị khi không có Internet.
      */
      anhDaiDien: './images.jpg',

      /*
        Điểm trung bình: kiểu số (Number) - DỮ LIỆU CHÍNH THỨC.
        Thang điểm 10. Giá trị này quyết định xếp loại và màu sắc xếp loại.
        Chỉ được gán khi chuỗi nhập vào là số hợp lệ trong khoảng 0 – 10.
      */
      diemTrungBinh: DU_LIEU_BAN_DAU.diemTrungBinh,

      /*
        Trạng thái đang học: kiểu boolean (true / false).
        - true  -> sinh viên đang học  -> hiển thị "Đang học".
        - false -> sinh viên đã nghỉ    -> hiển thị "Đã nghỉ".
        Dùng boolean thay vì chuỗi để dễ đảo trạng thái và dễ kiểm tra điều kiện.
      */
      dangHoc: true,

      /*
        Cờ bật/tắt phần giới thiệu: kiểu boolean.
        - true  -> phần giới thiệu hiển thị.
        - false -> phần giới thiệu bị ẩn (nhờ v-show).
        Giá trị ban đầu là true để người dùng thấy nội dung ngay khi mở trang.
      */
      hienGioiThieu: true,

      /*
        Nội dung giới thiệu: kiểu chuỗi (String).
        Được hiển thị bên trong khối v-show ở trên.
      */
      gioiThieu:
        'Sinh viên yêu thích lập trình web, đang tìm hiểu Vue.js và mong muốn ' +
        'trở thành lập trình viên Front-end chuyên nghiệp.',

      /*
        Danh sách sở thích: kiểu mảng chuỗi (Array of String).
        Được hiển thị bằng v-for, mỗi phần tử là một dòng <li>.
      */
      danhSachSoThich: ['Đọc sách công nghệ', 'Chơi thể thao', 'Nghe nhạc'],

      /*
        ------------------------------------------------------------------
        CÁC BIẾN TẠM CHO Ô NHẬP (bản nháp đang gõ)
        ------------------------------------------------------------------
        Tách riêng "bản nháp" khỏi "dữ liệu chính thức" để có thể kiểm tra
        trước khi cập nhật. Nếu dùng chung một biến, dữ liệu trên thẻ sinh viên
        sẽ bị thay đổi ngay cả khi người dùng vừa gõ một giá trị sai.

        - hoTenNhap : chuỗi đang hiển thị trong ô nhập họ tên.
      */
      hoTenNhap: DU_LIEU_BAN_DAU.hoTen,

      /*
        - tenLopNhap : chuỗi đang hiển thị trong ô nhập lớp.
      */
      tenLopNhap: DU_LIEU_BAN_DAU.tenLop,

      /*
        - diemNhap : chuỗi đang hiển thị trong ô nhập điểm.
          Lưu dạng CHUỖI (không phải số) để giữ được những trạng thái gõ dở
          như "8." hoặc "0." mà vẫn kiểm tra được.
          String(...) chuyển số 8.2 thành chuỗi "8.2" ban đầu.
      */
      diemNhap: String(DU_LIEU_BAN_DAU.diemTrungBinh),

      /*
        Thông báo lỗi của từng ô nhập.
        - Chuỗi rỗng '' nghĩa là KHÔNG có lỗi -> không hiển thị gì.
        - Có nội dung nghĩa là đang lỗi -> hiển thị dòng chữ đỏ dưới ô nhập.
      */
      loiHoTen: '',
      loiTenLop: '',
      loiDiem: '',
    };
  },

  /*
    ------------------------------------------------------------------------
    computed: nơi khai báo các THUỘC TÍNH TÍNH TOÁN.
    - Giá trị của computed được TÍNH LẠI TỰ ĐỘNG khi dữ liệu mà nó phụ thuộc thay đổi.
    - Kết quả được CACHE (lưu lại), nên gọi nhiều lần trong template vẫn chỉ tính 1 lần
      nếu dữ liệu đầu vào chưa đổi -> hiệu năng tốt hơn gọi hàm (method) trong template.
    ------------------------------------------------------------------------
  */
  computed: {
    /*
      KIỂM TRA ĐIỂM CÓ HỢP LỆ KHÔNG
      Trả về true/false. Điều kiện hợp lệ:
        - Là kiểu số (typeof === 'number').
        - Không phải NaN (Not a Number).
        - Nằm trong khoảng 0 đến 10.
      Computed này là "chốt chặn cuối cùng": dù dữ liệu bị thay đổi từ đâu,
      giao diện vẫn không hiển thị một xếp loại sai.
    */
    diemHopLe() {
      /*
        Lấy điểm hiện tại ra biến cục bộ cho dễ đọc.
      */
      const diem = this.diemTrungBinh;

      /*
        Kết hợp 4 điều kiện bằng toán tử && (tất cả phải cùng đúng).
        Number.isNaN(diem) kiểm tra giá trị không phải số.
      */
      return (
        typeof diem === 'number' &&
        Number.isNaN(diem) === false &&
        diem >= 0 &&
        diem <= 10
      );
    },

    /*
      XẾP LOẠI (Yêu cầu 4)
      Trả về một chuỗi: 'Giỏi' / 'Khá' / 'Trung bình' / 'Chưa đạt'.
      Quy tắc theo đề bài:
        - Từ 8 đến 10          : Giỏi
        - Từ 6.5 đến dưới 8    : Khá
        - Từ 5 đến dưới 6.5    : Trung bình
        - Dưới 5               : Chưa đạt
      Cách kiểm tra: dùng if / else if lần lượt từ mốc CAO xuống THẤP.
      Khi một điều kiện đúng, các nhánh sau không được xét nữa.
    */
    xepLoai() {
      /*
        Nếu điểm không hợp lệ (âm, lớn hơn 10, không phải số) thì KHÔNG xếp loại.
        Đây là lớp bảo vệ thứ hai, phòng khi dữ liệu bị gán sai từ code khác.
      */
      if (this.diemHopLe === false) {
        return 'Không hợp lệ';
      }

      /*
        Lấy điểm trung bình từ data ra một biến cục bộ cho dễ đọc.
      */
      const diem = this.diemTrungBinh;

      /*
        Trường hợp 1: điểm từ 8 đến 10 -> xếp loại "Giỏi".
      */
      if (diem >= 8 && diem <= 10) {
        return 'Giỏi';
      }

      /*
        Trường hợp 2: điểm từ 6.5 đến dưới 8 -> xếp loại "Khá".
        "diem < 8" thể hiện chữ "dưới 8" trong đề bài.
      */
      if (diem >= 6.5 && diem < 8) {
        return 'Khá';
      }

      /*
        Trường hợp 3: điểm từ 5 đến dưới 6.5 -> xếp loại "Trung bình".
      */
      if (diem >= 5 && diem < 6.5) {
        return 'Trung bình';
      }

      /*
        Trường hợp 4: các trường hợp còn lại (điểm từ 0 đến dưới 5)
        -> "Chưa đạt". Dùng "return" cuối cùng thay cho else để code phẳng, dễ đọc.
      */
      return 'Chưa đạt';
    },

    /*
      MÀU SẮC XẾP LOẠI (Yêu cầu 5)
      Trả về TÊN CLASS CSS tương ứng với từng xếp loại.
      Class này được gắn vào thẻ bằng :class="mauXepLoai" ở template.
      Nhờ vậy, khi xếp loại đổi thì class đổi -> màu sắc đổi theo.
      Các class được định nghĩa trong phần <style> bên dưới:
        .loai-gioi, .loai-kha, .loai-trung-binh, .loai-chua-dat, .loai-khong-hop-le
    */
    mauXepLoai() {
      /*
        Dùng cấu trúc switch-case để ánh xạ 1-1 giữa xếp loại và class màu.
        "this.xepLoai" là computed ở trên -> switch này tự chạy lại khi điểm đổi.
      */
      switch (this.xepLoai) {
        /*
          Nếu xếp loại là "Giỏi" -> class "loai-gioi" (màu xanh lá).
        */
        case 'Giỏi':
          return 'loai-gioi';

        /*
          Nếu xếp loại là "Khá" -> class "loai-kha" (màu xanh dương).
        */
        case 'Khá':
          return 'loai-kha';

        /*
          Nếu xếp loại là "Trung bình" -> class "loai-trung-binh" (màu cam).
        */
        case 'Trung bình':
          return 'loai-trung-binh';

        /*
          Nếu điểm không hợp lệ -> class "loai-khong-hop-le" (màu đỏ đậm).
        */
        case 'Không hợp lệ':
          return 'loai-khong-hop-le';

        /*
          Mặc định (Chưa đạt) -> class "loai-chua-dat" (màu đỏ).
        */
        default:
          return 'loai-chua-dat';
      }
    },

    /*
      ĐIỂM TRUNG BÌNH ĐỂ HIỂN THỊ
      Chỉ phục vụ mục đích HIỂN THỊ, không làm thay đổi giá trị dùng để xếp loại.
      - Nếu điểm không hợp lệ -> trả về '--' để giao diện không hiện "NaN".
      - Nếu hợp lệ -> gọi method dinhDangDiem để bỏ các số 0 thừa:
          8.2  -> "8.2"
          8    -> "8"
          8.25 -> "8.25"
      Lưu ý: KHÔNG làm tròn về 1 chữ số thập phân, vì làm tròn có thể làm
      thay đổi xếp loại (ví dụ 6.49 làm tròn thành 6.5 sẽ đổi từ "Trung bình" sang "Khá").
    */
    diemTrungBinhHienThi() {
      /*
        Nếu điểm không hợp lệ, trả về chuỗi gạch ngang thay cho số.
      */
      if (this.diemHopLe === false) {
        return '--';
      }

      /*
        Gọi method định dạng điểm đã khai báo bên dưới.
        Computed vẫn theo dõi được sự thay đổi của "diemTrungBinh" vì method
        dinhDangDiem có đọc giá trị này.
      */
      return this.dinhDangDiem(this.diemTrungBinh);
    },

    /*
      TRẠNG THÁI HỌC TẬP (Yêu cầu 6)
      Dựa trên boolean "dangHoc" để trả về chuỗi tiếng Việt tương ứng.
      - true  -> 'Đang học'
      - false -> 'Đã nghỉ'
      Toán tử ba ngôi "? :" giúp viết gọn thay cho if / else.
    */
    trangThai() {
      return this.dangHoc ? 'Đang học' : 'Đã nghỉ';
    },

    /*
      MÀU SẮC TRẠNG THÁI
      Trả về class CSS tương ứng:
      - 'trang-thai-dang-hoc' : màu xanh lá (đang học).
      - 'trang-thai-da-nghi'  : màu xám   (đã nghỉ).
      Class được gắn bằng :class="mauTrangThai" ở template.
    */
    mauTrangThai() {
      return this.dangHoc ? 'trang-thai-dang-hoc' : 'trang-thai-da-nghi';
    },
  },

  /*
    ------------------------------------------------------------------------
    methods: nơi khai báo các HÀM XỬ LÝ (được gọi từ template hoặc từ code khác).
    Khác với computed: method KHÔNG được cache, mỗi lần gọi là chạy lại từ đầu.
    ------------------------------------------------------------------------
  */
  methods: {
    /*
      ĐỊNH DẠNG ĐIỂM ĐỂ HIỂN THỊ
      Nhận vào một số, trả về chuỗi gọn gàng.
      - toFixed(2)      : giữ tối đa 2 chữ số thập phân (tránh lỗi số thực
                          kiểu 8.199999... của JavaScript).
      - Number(...)     : chuyển "8.20" thành số 8.2 (bỏ số 0 thừa ở cuối).
      - .toString()     : chuyển số 8.2 thành chuỗi "8.2" để hiển thị.
      Ví dụ: 8 -> "8"; 8.2 -> "8.2"; 8.25 -> "8.25".
    */
    dinhDangDiem(diem) {
      /*
        Chuyển giá trị đầu vào thành số (phòng trường hợp truyền vào chuỗi).
      */
      const so = Number(diem);

      /*
        Nếu không phải số, trả về '--' để tránh hiển thị "NaN".
      */
      if (Number.isNaN(so)) {
        return '--';
      }

      /*
        Làm tròn tối đa 2 chữ số thập phân rồi bỏ các số 0 không cần thiết.
      */
      return Number(so.toFixed(2)).toString();
    },

    /*
      KIỂM TRA HỌ TÊN NGAY KHI NHẬP
      Được gọi từ sự kiện @input của ô họ tên.
      Tham số "event" là sự kiện DOM do trình duyệt tạo ra.
      Quy tắc hợp lệ:
        1. Không được rỗng (sau khi bỏ khoảng trắng hai đầu).
        2. Không quá 50 ký tự.
        3. Chỉ chứa chữ cái (kể cả chữ có dấu), khoảng trắng và . ' -
    */
    kiemTraHoTen(event) {
      /*
        Lấy chuỗi người dùng vừa gõ từ ô nhập.
        event.target là thẻ <input>, .value là nội dung hiện tại của ô.
      */
      const giaTri = event.target.value;

      /*
        Lưu lại bản nháp để ô nhập hiển thị đúng những gì người dùng gõ
        (kể cả khi dữ liệu chính thức chưa được cập nhật).
      */
      this.hoTenNhap = giaTri;

      /*
        Kiểm tra 1: sau khi bỏ khoảng trắng hai đầu mà rỗng -> báo lỗi và DỪNG.
        "return" giúp không chạy các kiểm tra phía dưới và không cập nhật dữ liệu.
      */
      if (giaTri.trim() === '') {
        this.loiHoTen = 'Họ tên không được để trống.';
        return;
      }

      /*
        Kiểm tra 2: độ dài sau khi bỏ khoảng trắng hai đầu không được vượt 50.
      */
      if (giaTri.trim().length > 50) {
        this.loiHoTen = 'Họ tên không được vượt quá 50 ký tự.';
        return;
      }

      /*
        Kiểm tra 3: chỉ cho phép chữ cái, khoảng trắng và . ' -
        Giải thích biểu thức chính quy /^[\p{L}\s.'-]+$/u :
          ^        : bắt đầu chuỗi
          [\p{L}...] : \p{L} là MỌI chữ cái Unicode (bao gồm ắ, ễ, ộ, ...)
          \s       : khoảng trắng
          . ' -    : dấu chấm, dấu nháy đơn, dấu gạch nối
          +        : một hoặc nhiều ký tự thuộc nhóm trên
          $        : kết thúc chuỗi
          u        : bật chế độ Unicode để \p{L} hoạt động đúng
        Nếu chuỗi chứa chữ số hoặc ký tự đặc biệt khác -> không khớp -> báo lỗi.
      */
      if (/^[\p{L}\s.'-]+$/u.test(giaTri) === false) {
        this.loiHoTen = "Họ tên chỉ được chứa chữ cái, khoảng trắng và các ký tự . ' -";
        return;
      }

      /*
        Nếu vượt qua cả 3 kiểm tra:
        - Xoá thông báo lỗi (gán chuỗi rỗng).
        - Cập nhật dữ liệu chính thức "hoTen" bằng giá trị đã bỏ khoảng trắng hai đầu.
        -> Thẻ sinh viên, alt của ảnh tự cập nhật ngay lập tức.
      */
      this.loiHoTen = '';
      this.hoTen = giaTri.trim();
    },

    /*
      CHỐT HỌ TÊN KHI RỜI Ô NHẬP (sự kiện @blur)
      Mục đích:
        - Nếu đang có lỗi: khôi phục ô nhập về giá trị hợp lệ gần nhất và xoá lỗi.
        - Nếu hợp lệ: đồng bộ ô nhập với dữ liệu chính thức (đã được bỏ khoảng trắng).
    */
    chotHoTen() {
      /*
        Trường hợp đang lỗi: trả ô nhập về giá trị "hoTen" đang dùng.
        Nhờ vậy người dùng không thể để lại một giá trị sai trong ô.
      */
      if (this.loiHoTen !== '') {
        this.hoTenNhap = this.hoTen;
        this.loiHoTen = '';
        return;
      }

      /*
        Trường hợp hợp lệ: chuẩn hoá khoảng trắng trước khi đồng bộ.
        - replace(/\s+/g, ' ') : gộp nhiều khoảng trắng (kể cả tab) liên tiếp
                                  thành ĐÚNG MỘT dấu cách.
        - trim()               : bỏ khoảng trắng ở hai đầu chuỗi.
        Ví dụ: "Nguyễn  Văn   An " -> "Nguyễn Văn An".
        Chỉ chuẩn hoá khi RỜI ô (blur) để con trỏ không bị nhảy khi đang gõ.
      */
      const hoTenDaChuanHoa = this.hoTen.replace(/\s+/g, ' ').trim();

      /*
        Ghi giá trị đã chuẩn hoá vào dữ liệu chính thức.
      */
      this.hoTen = hoTenDaChuanHoa;

      /*
        Đồng bộ ô nhập với giá trị đã chuẩn hoá để người dùng thấy kết quả.
      */
      this.hoTenNhap = hoTenDaChuanHoa;
    },

    /*
      KIỂM TRA TÊN LỚP NGAY KHI NHẬP
      Quy tắc hợp lệ:
        1. Không được rỗng.
        2. Không quá 30 ký tự.
        3. Chỉ chứa chữ cái, chữ số, khoảng trắng và các ký tự . _ -
           (cho phép các kiểu tên lớp như "CNTT K18A", "DH_CNTT.01").
    */
    kiemTraTenLop(event) {
      /*
        Lấy giá trị hiện tại của ô nhập lớp.
      */
      const giaTri = event.target.value;

      /*
        Cập nhật bản nháp để ô nhập hiển thị đúng nội dung đang gõ.
      */
      this.tenLopNhap = giaTri;

      /*
        Kiểm tra 1: không được rỗng.
      */
      if (giaTri.trim() === '') {
        this.loiTenLop = 'Tên lớp không được để trống.';
        return;
      }

      /*
        Kiểm tra 2: độ dài tối đa 30 ký tự.
      */
      if (giaTri.trim().length > 30) {
        this.loiTenLop = 'Tên lớp không được vượt quá 30 ký tự.';
        return;
      }

      /*
        Kiểm tra 3: biểu thức chính quy
          /^[\p{L}\p{N}\s._-]+$/u
          \p{L} : chữ cái (kể cả tiếng Việt)
          \p{N} : chữ số
          \s    : khoảng trắng
          . _ - : dấu chấm, gạch dưới, gạch nối
        Nếu có ký tự khác (ví dụ @, #, /) -> báo lỗi.
      */
      if (/^[\p{L}\p{N}\s._-]+$/u.test(giaTri) === false) {
        this.loiTenLop = 'Tên lớp chỉ được chứa chữ, số, khoảng trắng và các ký tự . _ -';
        return;
      }

      /*
        Hợp lệ: xoá lỗi và cập nhật dữ liệu chính thức "tenLop".
        Tên lớp hiển thị bằng v-text sẽ đổi ngay.
      */
      this.loiTenLop = '';
      this.tenLop = giaTri.trim();
    },

    /*
      CHỐT TÊN LỚP KHI RỜI Ô NHẬP
      Giống chotHoTen: lỗi thì khôi phục, hợp lệ thì đồng bộ lại bản nháp.
    */
    chotTenLop() {
      /*
        Nếu đang lỗi -> khôi phục giá trị hợp lệ gần nhất và xoá thông báo lỗi.
      */
      if (this.loiTenLop !== '') {
        this.tenLopNhap = this.tenLop;
        this.loiTenLop = '';
        return;
      }

      /*
        Nếu hợp lệ -> chuẩn hoá khoảng trắng rồi đồng bộ cả dữ liệu và ô nhập.
        - replace(/\s+/g, ' ') : gộp khoảng trắng liên tiếp thành một dấu cách.
        - trim()               : bỏ khoảng trắng hai đầu.
        Ví dụ: "CNTT   K18A " -> "CNTT K18A".
      */
      const tenLopDaChuanHoa = this.tenLop.replace(/\s+/g, ' ').trim();

      /*
        Ghi giá trị đã chuẩn hoá vào dữ liệu chính thức.
      */
      this.tenLop = tenLopDaChuanHoa;

      /*
        Đồng bộ lại ô nhập để hiển thị đúng giá trị đã chuẩn hoá.
      */
      this.tenLopNhap = tenLopDaChuanHoa;
    },

    /*
      KIỂM TRA ĐIỂM TRUNG BÌNH NGAY KHI NHẬP
      Đây là phần quan trọng nhất của việc "ngăn chặn lỗi":
        - Điểm > 10  : báo lỗi ngay, KHÔNG cập nhật điểm/xếp loại/màu sắc.
        - Điểm < 0   : báo lỗi ngay, không cập nhật.
        - Không phải số (chữ, ký tự lạ) : báo lỗi ngay, không cập nhật.
        - Chuỗi rỗng : nhắc người dùng nhập điểm.
        - Hợp lệ     : cập nhật "diemTrungBinh" -> xếp loại và màu sắc tự đổi.
    */
    kiemTraDiem(event) {
      /*
        Lấy nguyên văn chuỗi người dùng gõ (ví dụ "11", "8,5", "abc").
      */
      const giaTriGoc = event.target.value;

      /*
        Cập nhật bản nháp để ô nhập hiển thị đúng những gì đang gõ.
        Nhờ vậy người dùng thấy được giá trị mình vừa nhập kèm thông báo lỗi.
      */
      this.diemNhap = giaTriGoc;

      /*
        Chuẩn hoá chuỗi để kiểm tra:
        - trim() : bỏ khoảng trắng hai đầu.
        - replace(',', '.') : đổi dấu phẩy thập phân (kiểu Việt Nam: 8,5)
          thành dấu chấm (8.5) để JavaScript hiểu đúng là số.
      */
      const giaTri = giaTriGoc.trim().replace(',', '.');

      /*
        Kiểm tra 1: chuỗi rỗng -> nhắc nhập, không cập nhật dữ liệu.
      */
      if (giaTri === '') {
        this.loiDiem = 'Vui lòng nhập điểm trung bình.';
        return;
      }

      /*
        Kiểm tra 2: định dạng số.
        Biểu thức chính quy /^\d+(\.\d{0,2})?$/ :
          ^        : bắt đầu chuỗi
          \d+      : một hoặc nhiều chữ số ở phần nguyên (0, 8, 10, 99, ...)
          (\.\d{0,2})? : phần thập phân không bắt buộc, gồm dấu chấm và
                         tối đa 2 chữ số (cho phép "8.", "8.5", "8.25")
          $        : kết thúc chuỗi
        Nhờ đó các chuỗi như "abc", "1e3", "--5", "8.5.1" đều bị từ chối.
      */
      if (/^\d+(\.\d{0,2})?$/.test(giaTri) === false) {
        this.loiDiem = 'Điểm phải là số không âm, ví dụ 8.5 (dùng dấu chấm hoặc dấu phẩy thập phân).';
        return;
      }

      /*
        Chuyển chuỗi hợp lệ thành số thực để so sánh.
        Number("8.") = 8, Number("8.25") = 8.25.
      */
      const soDiem = Number(giaTri);

      /*
        Kiểm tra 3: điểm không được lớn hơn 10.
        Đây chính là trường hợp "nhập 11" mà đề bài yêu cầu chặn ngay lập tức.
      */
      if (soDiem > 10) {
        this.loiDiem = 'Điểm không được lớn hơn 10. Giá trị vừa nhập đã bị từ chối.';
        return;
      }

      /*
        Kiểm tra 4: điểm không được nhỏ hơn 0.
        (Biểu thức chính quy ở trên đã chặn dấu trừ, nhưng vẫn kiểm tra lại
        để bảo vệ dữ liệu trong mọi tình huống.)
      */
      if (soDiem < 0) {
        this.loiDiem = 'Điểm không được nhỏ hơn 0.';
        return;
      }

      /*
        Tất cả kiểm tra đều đạt:
        - Xoá thông báo lỗi.
        - Ghi số điểm vào dữ liệu chính thức "diemTrungBinh".
        -> computed xepLoai, mauXepLoai, diemTrungBinhHienThi tự tính lại
        -> thẻ sinh viên đổi điểm, đổi xếp loại và đổi màu ngay lập tức.
      */
      this.loiDiem = '';
      this.diemTrungBinh = soDiem;
    },

    /*
      CHỐT ĐIỂM KHI RỜI Ô NHẬP
      - Nếu đang lỗi: khôi phục ô nhập về điểm hợp lệ gần nhất, xoá lỗi.
      - Nếu hợp lệ: hiển thị điểm đã định dạng gọn gàng (bỏ số 0 thừa).
    */
    chotDiem() {
      /*
        Trường hợp đang có lỗi: trả ô nhập về giá trị điểm đang dùng.
        Ví dụ người dùng gõ "11" (bị từ chối) rồi bấm ra ngoài -> ô tự trở về "8.2".
      */
      if (this.loiDiem !== '') {
        this.diemNhap = this.dinhDangDiem(this.diemTrungBinh);
        this.loiDiem = '';
        return;
      }

      /*
        Trường hợp hợp lệ: chuẩn hoá chuỗi hiển thị theo dữ liệu chính thức.
        "8,5" -> "8.5"; "8." -> "8".
      */
      this.diemNhap = this.dinhDangDiem(this.diemTrungBinh);
    },

    /*
      ĐỔI TRẠNG THÁI HỌC TẬP
      Đảo giá trị boolean: true -> false và ngược lại.
      Khi "dangHoc" đổi, computed "trangThai" và "mauTrangThai" tự tính lại.
    */
    doiTrangThai() {
      /*
        Dấu "!" là toán tử phủ định: !true = false, !false = true.
      */
      this.dangHoc = !this.dangHoc;
    },

    /*
      KHÔI PHỤC DỮ LIỆU BAN ĐẦU
      Đưa toàn bộ dữ liệu về giá trị mặc định, đồng thời:
        - Xoá cả 3 thông báo lỗi.
        - Đồng bộ lại 3 ô nhập với dữ liệu mới.
      Hữu ích khi muốn thử lại từ đầu sau khi đã sửa nhiều thông tin.
    */
    khoiPhucDuLieu() {
      /*
        Khôi phục dữ liệu chính thức từ hằng số DU_LIEU_BAN_DAU.
      */
      this.hoTen = DU_LIEU_BAN_DAU.hoTen;
      this.tenLop = DU_LIEU_BAN_DAU.tenLop;
      this.diemTrungBinh = DU_LIEU_BAN_DAU.diemTrungBinh;

      /*
        Đưa trạng thái học tập về "Đang học" (true).
      */
      this.dangHoc = true;

      /*
        Đồng bộ các ô nhập với dữ liệu vừa khôi phục.
        String(...) đổi số 8.2 thành chuỗi "8.2" cho ô nhập điểm.
      */
      this.hoTenNhap = DU_LIEU_BAN_DAU.hoTen;
      this.tenLopNhap = DU_LIEU_BAN_DAU.tenLop;
      this.diemNhap = String(DU_LIEU_BAN_DAU.diemTrungBinh);

      /*
        Xoá toàn bộ thông báo lỗi đang hiển thị.
      */
      this.loiHoTen = '';
      this.loiTenLop = '';
      this.loiDiem = '';
    },
  },
};
</script>

<style scoped>
/*
  "scoped" nghĩa là các quy tắc CSS dưới đây CHỈ áp dụng cho component này,
  không làm ảnh hưởng tới các component khác trong cùng ứng dụng.
*/

/* ---------------------------------------------------------------------------
   1. KHUNG TRANG
   --------------------------------------------------------------------------- */

/* Phần tử gốc: căn giữa nội dung, đặt phông chữ và màu nền cho cả trang. */
.trang-sinh-vien {
  /* Phông chữ ưu tiên theo thứ tự; trình duyệt dùng cái đầu tiên có sẵn. */
  font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
  /* Màu nền xám rất nhạt để thẻ trắng nổi bật lên. */
  background-color: #f4f6f8;
  /* Khoảng đệm bên trong, tạo khoảng trống quanh nội dung. */
  padding: 24px;
  /* Bo tròn 4 góc khung trang. */
  border-radius: 12px;
  /* Giới hạn chiều rộng để trang không quá rộng trên màn hình lớn. */
  max-width: 640px;
  /* Căn giữa khối này theo chiều ngang. */
  margin: 0 auto;
  /* Màu chữ mặc định. */
  color: #1f2937;
}

/* Tiêu đề chính của trang. */
.tieu-de {
  /* Căn giữa tiêu đề. */
  text-align: center;
  /* Màu chữ xanh đậm. */
  color: #1d4ed8;
  /* Khoảng cách phía dưới tiêu đề. */
  margin-bottom: 20px;
}

/* ---------------------------------------------------------------------------
   2. THẺ THÔNG TIN SINH VIÊN
   --------------------------------------------------------------------------- */

/* Thẻ trắng chứa ảnh và thông tin, xếp ảnh và chữ nằm ngang nhau. */
.the-sinh-vien {
  /* Bật Flexbox để dàn hàng ngang. */
  display: flex;
  /* Căn các phần tử theo trục dọc, canh giữa. */
  align-items: center;
  /* Khoảng cách giữa ảnh và khối chữ. */
  gap: 20px;
  /* Nền trắng cho thẻ. */
  background-color: #ffffff;
  /* Khoảng đệm bên trong thẻ. */
  padding: 20px;
  /* Bo góc thẻ. */
  border-radius: 12px;
  /* Đổ bóng nhẹ tạo cảm giác nổi. */
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

/* Ảnh đại diện sinh viên. */
.anh-dai-dien {
  /* Kích thước cố định để ảnh luôn vuông vắn. */
  width: 130px;
  height: 130px;
  /* Cắt ảnh cho vừa khung, không bị méo tỉ lệ. */
  object-fit: cover;
  /* Bo tròn hoàn toàn thành hình tròn. */
  border-radius: 50%;
  /* Viền xanh quanh ảnh. */
  border: 3px solid #bfdbfe;
  /* Không co ảnh lại khi khung hẹp. */
  flex-shrink: 0;
}

/* Khối chứa các dòng thông tin (chiếm hết phần còn lại). */
.khoi-thong-tin {
  /* Cho khối này giãn ra lấp đầy khoảng trống còn lại. */
  flex: 1;
  /* Cho phép xuống dòng khi nội dung quá dài, tránh tràn khung. */
  min-width: 0;
}

/* Họ tên sinh viên. */
.ho-ten {
  /* Bỏ khoảng cách mặc định của thẻ h2. */
  margin: 0 0 10px 0;
  /* Cỡ chữ lớn hơn để nổi bật. */
  font-size: 22px;
  /* Màu chữ đậm. */
  color: #111827;
  /* Tên dài sẽ tự xuống dòng thay vì tràn ra ngoài thẻ. */
  overflow-wrap: anywhere;
}

/* Mỗi dòng thông tin (mã SV, lớp, điểm, xếp loại, trạng thái). */
.dong-thong-tin {
  /* Khoảng cách dọc giữa các dòng. */
  margin: 6px 0;
  /* Cỡ chữ vừa phải. */
  font-size: 15px;
  /* Bật flexbox để nhãn và giá trị nằm cùng hàng. */
  display: flex;
  /* Canh giữa theo chiều dọc. */
  align-items: center;
  /* Khoảng cách giữa nhãn và giá trị. */
  gap: 6px;
  /* Cho phép xuống dòng khi nội dung dài (màn hình hẹp). */
  flex-wrap: wrap;
}

/* Nhãn (chữ "Mã sinh viên:", "Lớp:", ...). */
.nhan {
  /* Độ đậm vừa. */
  font-weight: 600;
  /* Màu xám đậm. */
  color: #4b5563;
  /* Đảm bảo nhãn không bị co lại khi giá trị dài. */
  flex-shrink: 0;
}

/* Đơn vị "/ 10" sau điểm trung bình. */
.don-vi {
  /* Màu xám nhạt hơn để không gây nhầm lẫn với điểm. */
  color: #6b7280;
  /* Cỡ chữ nhỏ hơn. */
  font-size: 13px;
}

/* ---------------------------------------------------------------------------
   3. MÀU SẮC XẾP LOẠI (áp dụng qua :class) - Yêu cầu 5
   Mỗi class dưới đây được gắn động bởi computed "mauXepLoai".
   --------------------------------------------------------------------------- */

/* Kiểu dùng chung cho nhãn xếp loại và nhãn trạng thái. */
.nhan-xep-loai,
.nhan-trang-thai {
  /* Khoảng đệm trong nhãn. */
  padding: 2px 10px;
  /* Bo tròn nhãn thành viên thuốc. */
  border-radius: 999px;
  /* Cỡ chữ nhỏ hơn một chút. */
  font-size: 13px;
  /* Chữ đậm. */
  font-weight: 700;
  /* Viền mảnh cùng màu chữ. */
  border: 1px solid currentColor;
  /* Hiệu ứng chuyển màu mượt khi xếp loại thay đổi. */
  transition: all 0.25s ease;
}

/* Xếp loại Giỏi: màu xanh lá. */
.loai-gioi {
  color: #15803d;
  background-color: #dcfce7;
}

/* Xếp loại Khá: màu xanh dương. */
.loai-kha {
  color: #1d4ed8;
  background-color: #dbeafe;
}

/* Xếp loại Trung bình: màu cam. */
.loai-trung-binh {
  color: #b45309;
  background-color: #fef3c7;
}

/* Xếp loại Chưa đạt: màu đỏ. */
.loai-chua-dat {
  color: #b91c1c;
  background-color: #fee2e2;
}

/* Điểm không hợp lệ (phòng trường hợp dữ liệu bị gán sai): màu đỏ đậm. */
.loai-khong-hop-le {
  color: #ffffff;
  background-color: #dc2626;
}

/* ---------------------------------------------------------------------------
   4. MÀU SẮC TRẠNG THÁI HỌC TẬP (áp dụng qua :class) - Yêu cầu 6
   --------------------------------------------------------------------------- */

/* Trạng thái "Đang học": màu xanh lá. */
.trang-thai-dang-hoc {
  color: #047857;
  background-color: #d1fae5;
}

/* Trạng thái "Đã nghỉ": màu xám. */
.trang-thai-da-nghi {
  color: #4b5563;
  background-color: #e5e7eb;
}

/* ---------------------------------------------------------------------------
   5. NÚT BẬT/TẮT VÀ KHỐI GIỚI THIỆU - Yêu cầu 7
   --------------------------------------------------------------------------- */

/* Nút bật/tắt phần giới thiệu. */
.nut-bat-tat {
  /* Hiển thị dạng khối riêng một dòng. */
  display: block;
  /* Cách thẻ thông tin phía trên 16px. */
  margin-top: 16px;
  /* Khoảng đệm trong nút. */
  padding: 10px 18px;
  /* Nền xanh dương. */
  background-color: #2563eb;
  /* Chữ trắng. */
  color: #ffffff;
  /* Bỏ viền mặc định của nút. */
  border: none;
  /* Bo góc nút. */
  border-radius: 8px;
  /* Cỡ chữ. */
  font-size: 15px;
  /* Đổi con trỏ thành bàn tay khi rê chuột vào. */
  cursor: pointer;
  /* Chuyển màu mượt khi hover. */
  transition: background-color 0.2s ease;
}

/* Trạng thái rê chuột lên nút. */
.nut-bat-tat:hover {
  /* Màu xanh đậm hơn khi hover. */
  background-color: #1d4ed8;
}

/* Khối giới thiệu (bật/tắt bằng v-show). */
.gioi-thieu {
  /* Cách nút phía trên 16px. */
  margin-top: 16px;
  /* Nền trắng. */
  background-color: #ffffff;
  /* Khoảng đệm trong khối. */
  padding: 16px 20px;
  /* Bo góc. */
  border-radius: 12px;
  /* Viền trái màu xanh để tạo điểm nhấn. */
  border-left: 4px solid #2563eb;
  /* Đổ bóng nhẹ. */
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

/* Tiêu đề nhỏ trong khối giới thiệu. */
.gioi-thieu h3 {
  /* Cách phía trên 0, phía dưới 8px. */
  margin: 0 0 8px 0;
  /* Cỡ chữ vừa. */
  font-size: 16px;
  /* Màu xanh đậm. */
  color: #1d4ed8;
}

/* Đoạn văn giới thiệu. */
.gioi-thieu p {
  /* Bỏ khoảng cách mặc định. */
  margin: 0;
  /* Giãn dòng cho dễ đọc. */
  line-height: 1.6;
  /* Cỡ chữ. */
  font-size: 15px;
}

/* Danh sách sở thích. */
.danh-sach-so-thich {
  /* Cách đoạn văn phía trên 8px. */
  margin: 8px 0 0 0;
  /* Khoảng đệm bên trái để chừa chỗ cho dấu đầu dòng. */
  padding-left: 20px;
  /* Giãn dòng. */
  line-height: 1.6;
}

/* ---------------------------------------------------------------------------
   6. PHẦN KIỂM TRA THAY ĐỔI DỮ LIỆU
   --------------------------------------------------------------------------- */

/* Khung bao ngoài của phần kiểm tra. */
.bang-dieu-khien {
  /* Cách phần trên 20px. */
  margin-top: 20px;
  /* Nền trắng. */
  background-color: #ffffff;
  /* Khoảng đệm bên trong. */
  padding: 16px 20px 20px;
  /* Bo góc. */
  border-radius: 12px;
  /* Đổ bóng nhẹ. */
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

/* Tiêu đề của phần kiểm tra. */
.bang-dieu-khien h3 {
  /* Bỏ khoảng cách mặc định, chừa 8px phía dưới. */
  margin: 0 0 8px 0;
  /* Cỡ chữ. */
  font-size: 16px;
  /* Màu chữ. */
  color: #374151;
}

/* Dòng gợi ý hướng dẫn sử dụng. */
.goi-y {
  /* Bỏ khoảng cách mặc định, chừa 14px phía dưới. */
  margin: 0 0 14px 0;
  /* Cỡ chữ nhỏ hơn. */
  font-size: 13px;
  /* Màu xám. */
  color: #6b7280;
  /* Giãn dòng cho dễ đọc. */
  line-height: 1.5;
}

/* Khung chứa các ô nhập, xếp thành cột dọc. */
.bieu-mau {
  /* Dùng flexbox. */
  display: flex;
  /* Xếp theo chiều dọc. */
  flex-direction: column;
  /* Khoảng cách giữa các hàng nhập. */
  gap: 14px;
}

/* Một hàng gồm nhãn + ô nhập + dòng lỗi. */
.hang-nhap {
  /* Dùng Grid chia 2 cột: nhãn cố định 90px, ô nhập chiếm phần còn lại. */
  display: grid;
  /* Cột 1 rộng 90px, cột 2 rộng phần còn lại. */
  grid-template-columns: 90px 1fr;
  /* Khoảng cách giữa các ô trong lưới. */
  gap: 4px 10px;
  /* Canh giữa nhãn theo chiều dọc so với ô nhập. */
  align-items: center;
}

/* Nhãn của ô nhập. */
.nhan-nhap {
  /* Chữ đậm vừa. */
  font-weight: 600;
  /* Cỡ chữ. */
  font-size: 14px;
  /* Màu xám đậm. */
  color: #374151;
}

/* Ô nhập dùng chung cho cả 3 trường. */
.hang-nhap input {
  /* Chiếm hết chiều rộng cột 2. */
  width: 100%;
  /* Khoảng đệm bên trong ô. */
  padding: 8px 10px;
  /* Cỡ chữ. */
  font-size: 15px;
  /* Màu chữ. */
  color: #111827;
  /* Viền xám mặc định. */
  border: 1px solid #d1d5db;
  /* Bo góc. */
  border-radius: 8px;
  /* Bỏ hiệu ứng viền mặc định của trình duyệt. */
  outline: none;
  /* Chuyển màu mượt khi focus/chuyển sang trạng thái lỗi. */
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

/* Khi ô nhập được chọn (focus). */
.hang-nhap input:focus {
  /* Viền xanh. */
  border-color: #2563eb;
  /* Quầng sáng xanh nhạt bao quanh. */
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

/* Ô nhập đang có dữ liệu KHÔNG hợp lệ. */
.o-nhap-loi {
  /* Viền đỏ. */
  border-color: #dc2626;
  /* Nền hồng rất nhạt để dễ nhận biết. */
  background-color: #fef2f2;
}

/* Ô nhập lỗi khi đang focus vẫn giữ viền đỏ. */
.o-nhap-loi:focus {
  border-color: #dc2626;
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15);
}

/* Dòng thông báo lỗi dưới ô nhập. */
.thong-bao-loi {
  /* Nằm ở cột 2, ngay dưới ô nhập. */
  grid-column: 2;
  /* Bỏ khoảng cách mặc định. */
  margin: 0;
  /* Cỡ chữ nhỏ. */
  font-size: 13px;
  /* Màu đỏ. */
  color: #dc2626;
  /* Giãn dòng. */
  line-height: 1.4;
}

/* Hàng chứa các nút thao tác. */
.hang-nut {
  /* Dàn nút nằm ngang. */
  display: flex;
  /* Cho phép xuống dòng khi màn hình hẹp. */
  flex-wrap: wrap;
  /* Khoảng cách giữa các nút. */
  gap: 10px;
  /* Cách phần ô nhập phía trên 16px. */
  margin-top: 16px;
}

/* Nút phụ (đổi trạng thái, khôi phục dữ liệu). */
.nut-phu {
  /* Khoảng đệm trong nút. */
  padding: 8px 14px;
  /* Nền trắng. */
  background-color: #ffffff;
  /* Màu chữ xanh. */
  color: #1d4ed8;
  /* Viền xanh. */
  border: 1px solid #2563eb;
  /* Bo góc. */
  border-radius: 8px;
  /* Cỡ chữ. */
  font-size: 14px;
  /* Con trỏ bàn tay khi hover. */
  cursor: pointer;
  /* Chuyển màu mượt. */
  transition: all 0.2s ease;
}

/* Khi rê chuột lên nút phụ: đảo màu (nền xanh, chữ trắng). */
.nut-phu:hover {
  background-color: #2563eb;
  color: #ffffff;
}

/* ---------------------------------------------------------------------------
   7. GIAO DIỆN MÀN HÌNH NHỎ (điện thoại)
   --------------------------------------------------------------------------- */

/* Áp dụng khi chiều rộng màn hình từ 480px trở xuống. */
@media (max-width: 480px) {
  /* Thẻ sinh viên chuyển sang xếp dọc, ảnh nằm trên. */
  .the-sinh-vien {
    flex-direction: column;
    text-align: center;
  }

  /* Các dòng thông tin canh giữa cho cân đối. */
  .dong-thong-tin {
    justify-content: center;
  }

  /* Hàng nhập chuyển thành một cột: nhãn ở trên, ô nhập ở dưới. */
  .hang-nhap {
    grid-template-columns: 1fr;
  }

  /* Dòng lỗi chiếm trọn chiều ngang thay vì chỉ cột 2. */
  .thong-bao-loi {
    grid-column: 1;
  }
}
</style>
