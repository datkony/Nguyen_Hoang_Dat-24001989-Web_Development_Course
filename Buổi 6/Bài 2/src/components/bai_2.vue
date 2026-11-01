<!--
  ============================================================================
  BÀI 2: FORM ĐẶT VÉ XEM PHIM (VUE 3 - SINGLE FILE COMPONENT)
  ----------------------------------------------------------------------------
  File này gồm 3 phần chuẩn của một Vue SFC:
    1. <template> : phần giao diện (HTML + các chỉ thị của Vue)
    2. <script>   : phần logic (dữ liệu, computed, methods)
    3. <style>    : phần trình bày (CSS), dùng "scoped" để chỉ áp dụng cho file này

  TÓM TẮT CÁC YÊU CẦU CỦA ĐỀ VÀ CÁCH THỰC HIỆN:
    1. Họ tên khách hàng dùng v-model.trim            -> ô nhập "Họ tên khách hàng".
    2. Chọn suất chiếu bằng <select> (4 suất)          -> ô "Suất chiếu".
    3. Chọn loại vé bằng radio, 70.000 / 100.000 đồng  -> nhóm "Loại vé".
    4. Số lượng vé dùng v-model.number, nguyên 1 – 10  -> ô số lượng + nút tăng/giảm.
    5. Checkbox combo bắp nước, 45.000 đồng mỗi đơn    -> ô "Combo bắp nước".
    6. Computed tổng tiền = giá vé × số lượng + combo  -> computed "tongTien".
    7. Nút tăng, giảm số lượng và nút đặt lại form     -> "tangSoLuong", "giamSoLuong", "datLaiForm".
    8. Nút "Đặt vé" chỉ bật khi họ tên không rỗng
       và số lượng hợp lệ                             -> computed "coTheDatVe".
    9. @submit.prevent hiển thị xác nhận trên trang    -> method "datVe" + khối "hop-xac-nhan".

  KIỂM TRA THEO ĐỀ: chọn 2 vé VIP + combo
    -> 100.000 × 2 + 45.000 = 245.000 đồng (computed "tongTien").
  ============================================================================
-->

<template>
  <!--
    <div class="trang-dat-ve"> là phần tử gốc (root) của component.
    Mỗi component trong Vue chỉ được có DUY NHẤT một phần tử gốc ở cấp ngoài cùng.
  -->
  <div class="trang-dat-ve">

    <!-- ==================== PHẦN TIÊU ĐỀ ==================== -->
    <header class="phan-dau">
      <!-- Tiêu đề chính của trang, nội dung tĩnh nên không cần chỉ thị Vue. -->
      <h1 class="tieu-de">Đặt vé xem phim</h1>

      <!-- Dòng mô tả ngắn giúp người dùng hiểu mục đích của biểu mẫu. -->
      <p class="mo-ta">
        Chọn suất chiếu, loại vé, số lượng và combo bắp nước. Tổng tiền được
        cập nhật ngay khi bạn thay đổi thông tin.
      </p>
    </header>

    <!--
      ==================== BIỂU MẪU ĐẶT VÉ ====================
      - @submit.prevent="datVe" : lắng nghe sự kiện submit của form.
        ".prevent" tự động gọi event.preventDefault() để trình duyệt KHÔNG tải lại
        trang (hành vi mặc định của form HTML). Sau đó Vue gọi method "datVe".
        Đây chính là yêu cầu số 9 của đề bài.
      - novalidate : tắt phần kiểm tra mặc định của trình duyệt (bong bóng thông báo
        tiếng Anh, không thống nhất giữa các trình duyệt). Ta tự kiểm tra bằng Vue
        để có thông báo tiếng Việt và điều khiển nút "Đặt vé" chính xác.
    -->
    <form class="bieu-mau" novalidate @submit.prevent="datVe">

      <!-- ==================== 1. HỌ TÊN KHÁCH HÀNG ==================== -->
      <fieldset class="nhom-truong">
        <!-- Tiêu đề của nhóm trường (fieldset/legend giúp trình đọc màn hình hiểu cấu trúc). -->
        <legend class="nhan-nhom">Thông tin khách hàng</legend>

        <!-- Nhãn của ô nhập, liên kết với ô nhập qua thuộc tính for = id. -->
        <label class="nhan-o" for="o-ho-ten">
          Họ tên khách hàng
          <!-- Dấu * màu đỏ để báo đây là trường bắt buộc. -->
          <span class="bat-buoc" aria-hidden="true">*</span>
        </label>

        <!--
          Ô nhập họ tên:
          - v-model.trim="hoTen" : ràng buộc hai chiều giữa ô nhập và biến "hoTen".
            Modifier ".trim" tự động bỏ khoảng trắng ở ĐẦU và CUỐI chuỗi sau mỗi lần nhập.
            Nhờ đó "  Nguyễn Văn An  " được lưu thành "Nguyễn Văn An".
          - @blur="xuLyRoiOHoTen" : khi rời khỏi ô, đánh dấu "đã chạm" để hiện lỗi
            (nếu có) và trim thêm một lần cho chắc chắn.
          - maxlength="50" : chặn cứng việc gõ quá 50 ký tự.
          - autocomplete="name" : gợi ý trình duyệt tự điền tên (tăng trải nghiệm).
          - :class="{ 'o-nhap-loi': hienLoiHoTen }" : thêm viền đỏ khi đang có lỗi.
          - :aria-invalid="hienLoiHoTen" : báo cho trình đọc màn hình biết ô đang sai.
          - :aria-describedby : chỉ trỏ tới dòng lỗi KHI dòng lỗi thực sự tồn tại.
          - required + aria-required="true" : thể hiện về mặt ngữ nghĩa đây là trường bắt buộc
            (form đã có novalidate nên trình duyệt không tự chặn).
        -->
        <input
          id="o-ho-ten"
          v-model.trim="hoTen"
          class="o-nhap"
          :class="{ 'o-nhap-loi': hienLoiHoTen }"
          type="text"
          maxlength="50"
          autocomplete="name"
          placeholder="Ví dụ: Nguyễn Văn An"
          required
          aria-required="true"
          :aria-invalid="hienLoiHoTen"
          :aria-describedby="hienLoiHoTen ? 'loi-ho-ten' : undefined"
          @blur="xuLyRoiOHoTen"
        />

        <!--
          Dòng lỗi chỉ xuất hiện khi:
            - Người dùng đã chạm vào ô (hoặc đã bấm "Đặt vé"), VÀ
            - Họ tên sau khi trim vẫn rỗng.
          - v-if giúp thêm/xoá phần tử khỏi DOM theo điều kiện.
          - role="alert" yêu cầu trình đọc màn hình đọc ngay thông báo lỗi.
        -->
        <p
          v-if="hienLoiHoTen"
          id="loi-ho-ten"
          class="thong-bao-loi"
          role="alert"
        >
          {{ loiHoTen }}
        </p>
      </fieldset>

      <!-- ==================== 2. SUẤT CHIẾU ==================== -->
      <fieldset class="nhom-truong">
        <!-- Tiêu đề nhóm trường suất chiếu. -->
        <legend class="nhan-nhom">Suất chiếu</legend>

        <!-- Nhãn của ô chọn suất chiếu. -->
        <label class="nhan-o" for="o-suat-chieu">Chọn suất chiếu</label>

        <!--
          Ô chọn (select) suất chiếu:
          - v-model="suatChieu" : ràng buộc hai chiều; giá trị được chọn ghi vào biến "suatChieu".
            Với <select>, Vue lắng nghe sự kiện "change" và tự cập nhật biến.
          - v-for="suat in danhSachSuatChieu" : lặp qua mảng 4 suất chiếu trong data
            để sinh ra 4 thẻ <option>. Nhờ vậy chỉ cần sửa mảng là giao diện tự cập nhật.
          - :key="suat.ma" : khoá duy nhất cho mỗi option giúp Vue tối ưu việc render.
          - :value="suat.ma" : giá trị thực được ghi vào biến khi chọn (ví dụ "sang").
            Phần chữ hiển thị cho người dùng là "suat.nhan" (ví dụ "09:00 – Suất sáng").
        -->
        <select
          id="o-suat-chieu"
          v-model="suatChieu"
          class="o-nhap"
        >
          <!-- Lặp qua từng suất chiếu trong danh sách để tạo các lựa chọn. -->
          <option
            v-for="suat in danhSachSuatChieu"
            :key="suat.ma"
            :value="suat.ma"
          >
            {{ suat.nhan }}
          </option>
        </select>

        <!-- Gợi ý nhỏ: giá vé KHÔNG phụ thuộc suất chiếu, chỉ phụ thuộc loại vé. -->
        <p class="goi-y">Giá vé không thay đổi theo suất chiếu, chỉ phụ thuộc loại vé.</p>
      </fieldset>

      <!-- ==================== 3. LOẠI VÉ (RADIO) ==================== -->
      <fieldset class="nhom-truong">
        <!-- Tiêu đề nhóm trường loại vé. -->
        <legend class="nhan-nhom">Loại vé</legend>

        <!--
          Mỗi lựa chọn là một <label> bao quanh <input type="radio">.
          Nhờ bao quanh, bấm vào BẤT KỲ chỗ nào trong label (kể cả giá tiền)
          cũng chọn được radio -> vùng bấm lớn, dễ dùng trên điện thoại.

          Radio "Vé thường":
          - v-model="loaiVe" : cả hai radio cùng trỏ vào một biến.
            Chúng có cùng name="loai-ve" nên trình duyệt đảm bảo chỉ một cái được chọn.
          - value="thuong" : khi chọn, biến "loaiVe" nhận chuỗi "thuong".
          - :class="{ 'dang-chon': loaiVe === 'thuong' }" : tô sáng lựa chọn đang chọn.
        -->
        <label
          class="o-lua-chon"
          :class="{ 'dang-chon': loaiVe === 'thuong' }"
        >
          <input
            v-model="loaiVe"
            type="radio"
            name="loai-ve"
            value="thuong"
          />
          <span class="ten-lua-chon">Vé thường</span>
          <!-- Hiển thị giá lấy từ hằng số GIA_VE.thuong (tránh gõ cứng nhiều nơi). -->
          <span class="gia">{{ dinhDangTien(giaVeThuong) }}</span>
        </label>

        <!--
          Radio "Vé VIP": cùng biến "loaiVe", value="vip".
          Khi chọn, computed "giaVe" sẽ trả về 100.000 và tổng tiền tự tính lại.
        -->
        <label
          class="o-lua-chon"
          :class="{ 'dang-chon': loaiVe === 'vip' }"
        >
          <input
            v-model="loaiVe"
            type="radio"
            name="loai-ve"
            value="vip"
          />
          <span class="ten-lua-chon">Vé VIP</span>
          <!-- Hiển thị giá lấy từ hằng số GIA_VE.vip. -->
          <span class="gia">{{ dinhDangTien(giaVeVip) }}</span>
        </label>
      </fieldset>

      <!-- ==================== 4. SỐ LƯỢNG VÉ ==================== -->
      <fieldset class="nhom-truong">
        <!-- Tiêu đề nhóm trường số lượng vé. -->
        <legend class="nhan-nhom">Số lượng vé</legend>

        <!-- Nhãn của ô nhập số lượng. -->
        <label class="nhan-o" for="o-so-luong">
          Số lượng ({{ soLuongToiThieu }} – {{ soLuongToiDa }} vé)
        </label>

        <!-- Cụm gồm: nút giảm, ô nhập, nút tăng. -->
        <div class="cum-so-luong">

          <!--
            Nút GIẢM số lượng:
            - type="button" : RẤT QUAN TRỌNG. Nếu không có, trình duyệt hiểu đây là
              nút submit và sẽ gửi form ngoài ý muốn mỗi khi bấm giảm.
            - :disabled="khongTheGiam" : khoá nút khi số lượng đã là 1 (không giảm thêm).
            - @click="giamSoLuong" : gọi method giảm số lượng (có kẹp trong khoảng 1 – 10).
            - aria-label : mô tả nút cho trình đọc màn hình (vì nút chỉ có ký tự "−").
          -->
          <button
            type="button"
            class="nut-so-luong"
            :disabled="khongTheGiam"
            aria-label="Giảm số lượng vé"
            @click="giamSoLuong"
          >
            −
          </button>

          <!--
            Ô nhập số lượng:
            - v-model.number="soLuong" : ràng buộc hai chiều + modifier ".number".
              Modifier ".number" tự chuyển chuỗi người dùng nhập thành SỐ.
              Lưu ý: khi ô bị xoá trắng, Vue trả về chuỗi rỗng '' (không phải số 0),
              nên phần kiểm tra bên dưới phải xử lý riêng trường hợp này.
            - type="number" + min/max/step : gợi ý cho trình duyệt và hiện nút mũi tên tăng/giảm.
              Tuy nhiên người dùng vẫn có thể gõ tay giá trị sai (ví dụ 11, 2.5),
              nên ta vẫn phải kiểm tra bằng computed và chuẩn hoá khi rời ô.
            - inputmode="numeric" : trên điện thoại hiện bàn phím số.
            - @blur="chotSoLuong" : khi rời ô, tự chuẩn hoá về số nguyên trong 1 – 10
              (ví dụ 2.5 -> 2; 11 -> 10; rỗng -> 1), tránh để lại trạng thái sai.
            - :class / :aria-invalid : tô viền đỏ khi số lượng không hợp lệ.
          -->
          <input
            id="o-so-luong"
            v-model.number="soLuong"
            class="o-nhap o-so-luong"
            :class="{ 'o-nhap-loi': loiSoLuong !== '' }"
            type="number"
            min="1"
            max="10"
            step="1"
            inputmode="numeric"
            required
            aria-required="true"
            :aria-invalid="loiSoLuong !== ''"
            aria-describedby="goi-y-so-luong"
            @blur="chotSoLuong"
          />

          <!--
            Nút TĂNG số lượng:
            - type="button" để không vô tình submit form (giống nút giảm).
            - :disabled="khongTheTang" : khoá nút khi đã đạt 10 vé.
            - @click="tangSoLuong" : gọi method tăng số lượng.
          -->
          <button
            type="button"
            class="nut-so-luong"
            :disabled="khongTheTang"
            aria-label="Tăng số lượng vé"
            @click="tangSoLuong"
          >
            +
          </button>
        </div>

        <!-- Gợi ý quy tắc nhập số lượng, gắn với ô nhập qua aria-describedby. -->
        <p id="goi-y-so-luong" class="goi-y">
          Chỉ chấp nhận số nguyên từ {{ soLuongToiThieu }} đến {{ soLuongToiDa }}. Nút "−" và "+" luôn giữ số lượng trong khoảng này.
        </p>

        <!-- Dòng lỗi của số lượng, chỉ hiện khi có lỗi (chuỗi lỗi khác rỗng). -->
        <p
          v-if="loiSoLuong !== ''"
          class="thong-bao-loi"
          role="alert"
        >
          {{ loiSoLuong }}
        </p>
      </fieldset>

      <!-- ==================== 5. COMBO BẮP NƯỚC ==================== -->
      <fieldset class="nhom-truong">
        <!-- Tiêu đề nhóm trường combo. -->
        <legend class="nhan-nhom">Combo bắp nước</legend>

        <!--
          Checkbox chọn combo:
          - v-model="chonCombo" : ràng buộc hai chiều với biến boolean.
            Khi tick -> true; bỏ tick -> false.
          - Combo tính 45.000 đồng cho MỖI ĐƠN đặt vé (không nhân với số lượng vé).
          - Cả <label> được bấm, không chỉ riêng ô vuông nhỏ.
        -->
        <label
          class="o-lua-chon"
          :class="{ 'dang-chon': chonCombo }"
        >
          <input v-model="chonCombo" type="checkbox" />
          <span class="ten-lua-chon">Thêm combo bắp nước</span>
          <!-- Giá combo lấy từ hằng số GIA_COMBO. -->
          <span class="gia">{{ dinhDangTien(giaCombo) }} / đơn</span>
        </label>
      </fieldset>

      <!-- ==================== 6. BẢNG TÍNH TIỀN ==================== -->
      <!--
        <dl> (description list) phù hợp để hiển thị cặp nhãn – giá trị.
        Khối này dùng aria-live="polite" để trình đọc màn hình thông báo khi tổng tiền đổi.
      -->
      <section class="bang-tinh-tien" aria-live="polite">
        <!-- Tiêu đề nhỏ của bảng tính tiền. -->
        <h2 class="tieu-de-nho">Tạm tính</h2>

        <!-- Dòng 1: tiền vé = giá vé × số lượng. -->
        <div class="dong-tien">
          <span class="nhan-tien">Tiền vé</span>
          <span class="gia-tri-tien">
            <!--
              Chỉ hiển thị công thức khi số lượng hợp lệ.
              Khi số lượng sai (rỗng, 2.5, 11...), hiện ghi chú thay vì một con số gây hiểu nhầm.
            -->
            <span class="cong-thuc">
              {{ soLuongHopLe ? dinhDangTien(giaVe) + ' × ' + soLuong : 'Chưa có số lượng hợp lệ' }}
            </span>
            <!-- Thành tiền vé; là "—" nếu số lượng chưa hợp lệ. -->
            <strong>{{ tienVeHienThi }}</strong>
          </span>
        </div>

        <!-- Dòng 2: tiền combo (45.000 nếu có tick, ngược lại 0). -->
        <div class="dong-tien">
          <span class="nhan-tien">Combo bắp nước</span>
          <span class="gia-tri-tien">
            <!-- Ghi rõ "Có" hoặc "Không" để người dùng đối chiếu nhanh. -->
            <span class="cong-thuc">{{ chonCombo ? 'Có' : 'Không' }}</span>
            <!-- Thành tiền combo. -->
            <strong>{{ dinhDangTien(tienCombo) }}</strong>
          </span>
        </div>

        <!-- Dòng 3: tổng tiền, làm nổi bật bằng class "dong-tong". -->
        <div class="dong-tien dong-tong">
          <span class="nhan-tien">Tổng tiền</span>
          <!--
            tongTienHienThi là computed: nếu số lượng hợp lệ thì định dạng tongTien,
            ngược lại trả về "—" để không hiển thị số tiền sai.
          -->
          <span class="tong-tien">{{ tongTienHienThi }}</span>
        </div>

        <!-- Ghi lại công thức của đề bài để người dùng/người chấm dễ đối chiếu. -->
        <p class="cong-thuc-tong">
          Công thức: Giá vé × Số lượng + Tiền combo
        </p>
      </section>

      <!-- ==================== 7. CÁC NÚT THAO TÁC ==================== -->
      <div class="hang-nut">

        <!--
          Nút "Đặt vé":
          - type="submit" : gửi form -> kích hoạt @submit.prevent="datVe" ở thẻ <form>.
          - :disabled="!coTheDatVe" : CHỈ bật khi họ tên không rỗng VÀ số lượng hợp lệ
            (yêu cầu 8). Khi disabled, trình duyệt không cho bấm và không gửi form.
          - :title : giải thích lý do nút bị khoá, giúp người dùng không bị "mắc kẹt".
        -->
        <button
          type="submit"
          class="nut-dat-ve"
          :disabled="!coTheDatVe"
          :title="coTheDatVe ? 'Gửi thông tin đặt vé' : 'Vui lòng nhập họ tên và số lượng vé hợp lệ'"
        >
          Đặt vé
        </button>

        <!--
          Nút "Đặt lại form":
          - type="button" : KHÔNG submit form (nếu để mặc định, nó sẽ là nút submit).
          - @click="datLaiForm" : đưa toàn bộ trường về giá trị mặc định và xoá xác nhận cũ.
        -->
        <button
          type="button"
          class="nut-dat-lai"
          @click="datLaiForm"
        >
          Đặt lại form
        </button>
      </div>

      <!--
        Dòng nhắc chỉ hiện khi nút "Đặt vé" đang bị khoá.
        Giúp người dùng biết cần sửa gì thay vì chỉ thấy một nút xám không rõ lý do.
      -->
      <p v-if="!coTheDatVe" class="goi-y goi-y-chan-doan">
        Nhập họ tên khách hàng và số lượng vé hợp lệ (số nguyên 1 – 10) để bật nút "Đặt vé".
      </p>
    </form>

    <!--
      ==================== 9. KHỐI XÁC NHẬN SAU KHI GỬI ====================
      - v-if="xacNhan !== null" : chỉ hiển thị sau khi đặt vé thành công.
        Ban đầu biến "xacNhan" là null nên khối này chưa tồn tại trong DOM.
      - aria-live="polite" : trình đọc màn hình sẽ đọc nội dung mới khi khối xuất hiện.
    -->
    <section
      v-if="xacNhan !== null"
      class="hop-xac-nhan"
      aria-live="polite"
    >
      <!-- Tiêu đề của khối xác nhận. -->
      <h2 class="tieu-de-xac-nhan">Xác nhận đặt vé</h2>

      <!-- Lời cảm ơn kèm tên khách hàng đã nhập (đã được trim khi lưu vào xacNhan). -->
      <p class="loi-cam-on">
        Cảm ơn <strong>{{ xacNhan.hoTen }}</strong>, thông tin đặt vé của bạn như sau:
      </p>

      <!-- Danh sách các cặp nhãn – giá trị của vé vừa đặt. -->
      <dl class="chi-tiet-xac-nhan">
        <!-- Suất chiếu đã chọn (lưu tên hiển thị tại thời điểm đặt). -->
        <div class="dong-xac-nhan">
          <dt>Suất chiếu</dt>
          <dd>{{ xacNhan.suatChieu }}</dd>
        </div>

        <!-- Loại vé kèm đơn giá tại thời điểm đặt. -->
        <div class="dong-xac-nhan">
          <dt>Loại vé</dt>
          <dd>{{ xacNhan.loaiVe }} ({{ dinhDangTien(xacNhan.giaVe) }}/vé)</dd>
        </div>

        <!-- Số lượng vé đã đặt. -->
        <div class="dong-xac-nhan">
          <dt>Số lượng</dt>
          <dd>{{ xacNhan.soLuong }} vé</dd>
        </div>

        <!-- Có chọn combo hay không. -->
        <div class="dong-xac-nhan">
          <dt>Combo bắp nước</dt>
          <dd>{{ xacNhan.chonCombo ? 'Có' : 'Không' }}</dd>
        </div>

        <!-- Tiền vé = đơn giá × số lượng. -->
        <div class="dong-xac-nhan">
          <dt>Tiền vé</dt>
          <dd>{{ dinhDangTien(xacNhan.tienVe) }}</dd>
        </div>

        <!-- Tiền combo chỉ hiển thị khi khách có chọn combo. -->
        <div v-if="xacNhan.chonCombo" class="dong-xac-nhan">
          <dt>Tiền combo</dt>
          <dd>{{ dinhDangTien(xacNhan.tienCombo) }}</dd>
        </div>

        <!-- Tổng tiền cuối cùng, làm nổi bật. -->
        <div class="dong-xac-nhan dong-tong">
          <dt>Tổng tiền</dt>
          <dd>{{ dinhDangTien(xacNhan.tongTien) }}</dd>
        </div>
      </dl>

      <!-- Ghi chú để người dùng hiểu khối này là ảnh chụp của lần đặt vé gần nhất. -->
      <p class="goi-y">
        Thông tin xác nhận tương ứng với lần đặt vé gần nhất. Bấm "Đặt lại form" để đặt vé mới.
      </p>
    </section>

  </div>
</template>

<script>
/*
  ============================================================================
  CÁC HẰNG SỐ DÙNG CHUNG
  ----------------------------------------------------------------------------
  Gom giá tiền và giới hạn vào MỘT nơi để tránh "hard code" rải rác.
  Khi cần đổi giá, chỉ sửa duy nhất ở đây; mọi nơi khác tự cập nhật theo.
  - Object.freeze(...) : "đóng băng" object, không cho phép vô tình sửa thuộc tính.
  ============================================================================
*/

// Bảng giá vé theo từng loại vé.
// Khoá (key) là mã loại vé dùng trong radio; giá trị (value) là đơn giá tính bằng đồng.
const GIA_VE = Object.freeze({
  // Vé thường: 70.000 đồng.
  thuong: 70000,
  // Vé VIP: 100.000 đồng.
  vip: 100000
})

// Giá combo bắp nước: 45.000 đồng cho MỖI ĐƠN đặt vé (không nhân với số lượng vé).
const GIA_COMBO = 45000

// Số lượng vé nhỏ nhất được phép đặt.
const SO_LUONG_TOI_THIEU = 1

// Số lượng vé lớn nhất được phép đặt.
const SO_LUONG_TOI_DA = 10

// Danh sách suất chiếu (đề yêu cầu ít nhất 3; ở đây có 4 suất).
// - ma  : giá trị kỹ thuật lưu trong biến "suatChieu" (không hiển thị).
// - nhan: chuỗi hiển thị cho người dùng trong <option>.
const DANH_SACH_SUAT_CHIEU = Object.freeze([
  // Suất sáng.
  { ma: 'sang', nhan: '09:00 – Suất sáng' },
  // Suất đầu giờ chiều.
  { ma: 'chieu', nhan: '13:30 – Suất chiều' },
  // Suất tối.
  { ma: 'toi', nhan: '18:00 – Suất tối' },
  // Suất đêm muộn.
  { ma: 'dem', nhan: '21:00 – Suất đêm' }
])

// Dữ liệu mặc định của form, dùng cho lần đầu mở trang VÀ cho nút "Đặt lại form".
// Nhờ dùng chung một hằng số, hai chỗ này không bao giờ bị lệch nhau.
const DU_LIEU_BAN_DAU = Object.freeze({
  // Họ tên ban đầu để trống: người dùng phải tự nhập -> nút "Đặt vé" bị khoá đúng yêu cầu 8.
  hoTen: '',
  // Suất chiếu mặc định: suất sáng.
  suatChieu: 'sang',
  // Loại vé mặc định: vé thường.
  loaiVe: 'thuong',
  // Số lượng mặc định: 1 vé.
  soLuong: 1,
  // Mặc định không chọn combo.
  chonCombo: false
})

// Bộ định dạng số theo chuẩn Việt Nam: dùng dấu chấm phân cách hàng nghìn (245000 -> 245.000).
// Tạo MỘT lần ở cấp module để không phải khởi tạo lại mỗi lần hiển thị giá.
const BO_DINH_DANG_SO = new Intl.NumberFormat('vi-VN', {
  // Giá tiền là số nguyên đồng nên không cần phần thập phân.
  maximumFractionDigits: 0
})

/*
  ============================================================================
  PHẦN LOGIC CỦA COMPONENT
  Sử dụng Options API (data / computed / methods) vì cách viết này trực quan,
  phù hợp với nội dung môn học và dễ đọc với người mới học Vue.
  ============================================================================
*/
export default {
  /*
    "name" là tên component.
    Tên này giúp Vue DevTools hiển thị đúng tên khi gỡ lỗi (debug).
  */
  name: 'Bai2DatVeXemPhim',

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
        HỌ TÊN KHÁCH HÀNG (yêu cầu 1).
        - Kiểu chuỗi (String).
        - Được ràng buộc với ô nhập qua v-model.trim, nên Vue tự bỏ khoảng trắng
          ở hai đầu mỗi khi người dùng nhập.
        - Giá trị ban đầu lấy từ DU_LIEU_BAN_DAU (chuỗi rỗng).
      */
      hoTen: DU_LIEU_BAN_DAU.hoTen,

      /*
        SUẤT CHIẾU ĐÃ CHỌN (yêu cầu 2).
        - Lưu MÃ của suất chiếu ("sang", "chieu", "toi", "dem"), không lưu câu chữ hiển thị.
        - Cách lưu mã giúp so sánh chính xác và không phụ thuộc vào việc đổi câu chữ.
      */
      suatChieu: DU_LIEU_BAN_DAU.suatChieu,

      /*
        LOẠI VÉ ĐÃ CHỌN (yêu cầu 3).
        - Lưu mã loại vé ("thuong" hoặc "vip").
        - Đơn giá tương ứng được tra trong bảng GIA_VE qua computed "giaVe".
      */
      loaiVe: DU_LIEU_BAN_DAU.loaiVe,

      /*
        SỐ LƯỢNG VÉ (yêu cầu 4).
        - v-model.number sẽ ghi vào đây một SỐ khi người dùng nhập số hợp lệ.
        - Nếu người dùng xoá trắng ô, giá trị có thể là chuỗi rỗng '' -> phần kiểm tra
          "soLuongHopLe" xử lý riêng trường hợp này.
      */
      soLuong: DU_LIEU_BAN_DAU.soLuong,

      /*
        CÓ CHỌN COMBO BẮP NƯỚC KHÔNG (yêu cầu 5).
        - Kiểu boolean: true = có combo, false = không có.
        - Dùng boolean thay vì chuỗi để dễ kiểm tra điều kiện và dễ đảo giá trị.
      */
      chonCombo: DU_LIEU_BAN_DAU.chonCombo,

      /*
        CỜ "ĐÃ CHẠM VÀO Ô HỌ TÊN" (phục vụ trải nghiệm nhập liệu).
        - Ban đầu là false: chưa hiện lỗi khi người dùng vừa mở trang (tránh khó chịu).
        - Khi rời ô họ tên hoặc khi bấm "Đặt vé" mà dữ liệu sai -> chuyển thành true
          để dòng lỗi hiện ra.
      */
      daChamVaoHoTen: false,

      /*
        THÔNG TIN XÁC NHẬN SAU KHI ĐẶT VÉ (yêu cầu 9).
        - null nghĩa là chưa đặt vé -> khối xác nhận chưa hiển thị.
        - Khi đặt thành công, đây là một OBJECT "ảnh chụp" (snapshot) các giá trị tại
          thời điểm bấm nút. Nhờ vậy nếu sau đó người dùng sửa form, thông tin xác nhận
          vẫn phản ánh đúng lần đặt vé vừa rồi, không bị sai lệch.
      */
      xacNhan: null,

      /*
        DANH SÁCH SUẤT CHIẾU để template dùng v-for sinh các <option>.
        - Lấy từ hằng số DANH_SACH_SUAT_CHIEU ở trên.
      */
      danhSachSuatChieu: DANH_SACH_SUAT_CHIEU,

      /*
        CÁC CON SỐ CẤU HÌNH đưa vào data để template hiển thị được.
        - Giá vé thường (70.000 đồng).
        - Giá vé VIP (100.000 đồng).
        - Giá combo (45.000 đồng).
        - Số lượng tối thiểu / tối đa (1 / 10).
      */
      giaVeThuong: GIA_VE.thuong,
      giaVeVip: GIA_VE.vip,
      giaCombo: GIA_COMBO,
      soLuongToiThieu: SO_LUONG_TOI_THIEU,
      soLuongToiDa: SO_LUONG_TOI_DA
    }
  },

  /*
    ------------------------------------------------------------------------
    computed: nơi khai báo các THUỘC TÍNH TÍNH TOÁN.
    - Giá trị computed được TÍNH LẠI TỰ ĐỘNG khi dữ liệu mà nó phụ thuộc thay đổi.
    - Kết quả được CACHE (lưu lại), nên gọi nhiều lần trong template vẫn chỉ tính
      một lần nếu dữ liệu đầu vào chưa đổi -> hiệu năng tốt hơn gọi method.
    ------------------------------------------------------------------------
  */
  computed: {
    /*
      HỌ TÊN CÓ HỢP LỆ KHÔNG.
      - Quy tắc: sau khi bỏ khoảng trắng hai đầu, chuỗi phải KHÁC RỖNG.
      - Dùng trim() thêm một lần nữa để chắc chắn (dù v-model.trim đã trim),
        phòng trường hợp dữ liệu bị gán từ nơi khác trong code.
      - Trả về true/false.
    */
    hoTenHopLe() {
      // Lấy họ tên hiện tại, bỏ khoảng trắng hai đầu rồi so sánh với chuỗi rỗng.
      return this.hoTen.trim() !== ''
    },

    /*
      THÔNG BÁO LỖI CỦA HỌ TÊN.
      - Trả về chuỗi rỗng '' nếu hợp lệ (nghĩa là không có lỗi).
      - Trả về câu thông báo nếu không hợp lệ.
    */
    loiHoTen() {
      // Nếu họ tên rỗng (hoặc chỉ toàn khoảng trắng) -> trả về câu nhắc nhập.
      if (this.hoTenHopLe === false) {
        // Câu thông báo hiển thị bên dưới ô nhập.
        return 'Vui lòng nhập họ tên khách hàng.'
      }

      // Các trường hợp còn lại là hợp lệ -> không có lỗi.
      return ''
    },

    /*
      CÓ NÊN HIỆN LỖI HỌ TÊN KHÔNG.
      - Chỉ hiện khi người dùng đã chạm vào ô (hoặc đã bấm đặt vé) VÀ dữ liệu đang sai.
      - Tránh việc vừa mở trang đã thấy một dòng lỗi đỏ dù chưa làm gì.
    */
    hienLoiHoTen() {
      // Kết hợp hai điều kiện bằng &&: cả hai phải cùng đúng.
      return this.daChamVaoHoTen === true && this.loiHoTen !== ''
    },

    /*
      SỐ LƯỢNG VÉ CÓ HỢP LỆ KHÔNG.
      Quy tắc của đề: số NGUYÊN từ 1 đến 10.
      Các bước kiểm tra:
        1. Không phải chuỗi rỗng '' (trường hợp người dùng xoá trắng ô).
        2. Chuyển được thành số hữu hạn (không phải NaN, không phải Infinity).
        3. Là số nguyên (Number.isInteger).
        4. Nằm trong khoảng 1 – 10.
      Trả về true/false.
    */
    soLuongHopLe() {
      // Kiểm tra 1: chuỗi rỗng (ô bị xoá trắng) hoặc null/undefined đều không hợp lệ.
      if (this.soLuong === '' || this.soLuong === null || this.soLuong === undefined) {
        // Dừng sớm, trả về false.
        return false
      }

      // Chuyển giá trị hiện tại thành số để kiểm tra (v-model.number thường đã là số).
      const so = Number(this.soLuong)

      // Kiểm tra 2 + 3 + 4: số hữu hạn, là số nguyên, nằm trong 1 – 10.
      return (
        Number.isFinite(so) === true &&
        Number.isInteger(so) === true &&
        so >= SO_LUONG_TOI_THIEU &&
        so <= SO_LUONG_TOI_DA
      )
    },

    /*
      THÔNG BÁO LỖI CỦA SỐ LƯỢNG VÉ.
      - Trả về '' nếu hợp lệ.
      - Trả về câu thông báo CỤ THỂ cho từng trường hợp sai, giúp người dùng sửa nhanh.
    */
    loiSoLuong() {
      // Trường hợp 1: ô nhập đang để trống.
      if (this.soLuong === '' || this.soLuong === null || this.soLuong === undefined) {
        // Nhắc người dùng nhập số lượng (dùng hằng số để thông báo luôn khớp giới hạn).
        return `Vui lòng nhập số lượng vé (số nguyên từ ${SO_LUONG_TOI_THIEU} đến ${SO_LUONG_TOI_DA}).`
      }

      // Chuyển giá trị hiện tại thành số để kiểm tra chi tiết.
      const so = Number(this.soLuong)

      // Trường hợp 2: giá trị không phải số hữu hạn (NaN, Infinity...).
      if (Number.isFinite(so) === false) {
        // Yêu cầu nhập một số.
        return 'Số lượng vé phải là một số.'
      }

      // Trường hợp 3: là số nhưng không phải số nguyên (ví dụ 2.5).
      if (Number.isInteger(so) === false) {
        // Yêu cầu nhập số nguyên.
        return 'Số lượng vé phải là số nguyên.'
      }

      // Trường hợp 4: nhỏ hơn 1.
      if (so < SO_LUONG_TOI_THIEU) {
        // Nhắc giá trị nhỏ nhất (lấy từ hằng số để không bị lệch khi đổi cấu hình).
        return `Số lượng vé phải từ ${SO_LUONG_TOI_THIEU} trở lên.`
      }

      // Trường hợp 5: lớn hơn 10.
      if (so > SO_LUONG_TOI_DA) {
        // Nhắc giá trị lớn nhất (lấy từ hằng số).
        return `Số lượng vé tối đa là ${SO_LUONG_TOI_DA}.`
      }

      // Tất cả đều đạt -> không có lỗi.
      return ''
    },

    /*
      SUẤT CHIẾU ĐÃ CHỌN CÓ HỢP LỆ KHÔNG.
      - Kiểm tra mã suất chiếu có thực sự nằm trong danh sách hay không.
      - Trong giao diện bình thường luôn đúng (vì chỉ chọn được từ danh sách),
        nhưng kiểm tra thêm để bảo vệ dữ liệu nếu bị gán sai từ code khác.
    */
    suatChieuHopLe() {
      // Array.prototype.some trả về true nếu có ÍT NHẤT một phần tử thoả điều kiện.
      return this.danhSachSuatChieu.some((suat) => {
        // So sánh mã của từng suất với mã đang được chọn.
        return suat.ma === this.suatChieu
      })
    },

    /*
      SUẤT CHIẾU ĐANG CHỌN (trả về cả object { ma, nhan }).
      - Dùng để lấy câu chữ hiển thị (nhan) khi tạo xác nhận.
      - Trả về null nếu không tìm thấy (trường hợp bất thường).
    */
    suatChieuDaChon() {
      // Tìm phần tử đầu tiên có mã trùng với suất đang chọn.
      const suat = this.danhSachSuatChieu.find((phanTu) => {
        // Điều kiện tìm kiếm: mã trùng nhau.
        return phanTu.ma === this.suatChieu
      })

      // Nếu không tìm thấy, "suat" là undefined -> trả về null cho rõ nghĩa.
      return suat === undefined ? null : suat
    },

    /*
      TÊN HIỂN THỊ CỦA SUẤT CHIẾU (ví dụ "09:00 – Suất sáng").
      - Nếu không tìm thấy suất hợp lệ -> trả về "—" để giao diện không bị trống.
    */
    nhanSuatChieu() {
      // Nếu không có suất hợp lệ, trả về dấu gạch ngang.
      if (this.suatChieuDaChon === null) {
        // Chuỗi thay thế.
        return '—'
      }

      // Trả về tên hiển thị của suất chiếu đã tìm thấy.
      return this.suatChieuDaChon.nhan
    },

    /*
      LOẠI VÉ ĐANG CHỌN CÓ HỢP LỆ KHÔNG.
      - Hợp lệ khi "loaiVe" là một khoá THỰC SỰ được khai báo trong bảng giá GIA_VE.
      - Dùng hasOwnProperty thay vì chỉ kiểm tra undefined để tránh nhận nhầm
        các thuộc tính kế thừa từ Object.prototype (như "toString", "constructor").
    */
    loaiVeHopLe() {
      // Dùng hasOwnProperty để chỉ chấp nhận khoá THỰC SỰ khai báo trong bảng giá.
      // Cách này an toàn hơn GIA_VE[this.loaiVe] !== undefined vì tránh nhận nhầm
      // các thuộc tính kế thừa từ Object.prototype (ví dụ "toString", "constructor").
      return Object.prototype.hasOwnProperty.call(GIA_VE, this.loaiVe) === true
    },

    /*
      ĐƠN GIÁ VÉ THEO LOẠI VÉ ĐANG CHỌN.
      - Vé thường -> 70.000.
      - Vé VIP    -> 100.000.
      - Nếu dữ liệu bất thường -> 0 (tránh trả về undefined gây lỗi tính toán).
    */
    giaVe() {
      // Nếu loại vé không hợp lệ (không phải khoá thực sự trong bảng giá) -> trả về 0.
      if (this.loaiVeHopLe === false) {
        // Giá trị 0 an toàn, tránh trả về undefined/function gây lỗi tính toán.
        return 0
      }

      // Tra bảng giá theo mã loại vé đã được xác nhận hợp lệ.
      return GIA_VE[this.loaiVe]
    },

    /*
      TÊN HIỂN THỊ CỦA LOẠI VÉ.
      - Dùng if/else rõ ràng thay vì chuỗi ba ngôi lồng nhau để dễ đọc.
      - Nếu mã không hợp lệ -> "Không xác định".
    */
    nhanLoaiVe() {
      // Trường hợp vé VIP.
      if (this.loaiVe === 'vip') {
        // Trả về tên tiếng Việt của vé VIP.
        return 'Vé VIP'
      }

      // Trường hợp vé thường.
      if (this.loaiVe === 'thuong') {
        // Trả về tên tiếng Việt của vé thường.
        return 'Vé thường'
      }

      // Trường hợp mã lạ (phòng vệ).
      return 'Không xác định'
    },

    /*
      TIỀN VÉ = ĐƠN GIÁ × SỐ LƯỢNG.
      - Chỉ tính khi số lượng hợp lệ; nếu không, trả về 0 để tránh NaN.
      - Đây là một phần của công thức tổng tiền ở yêu cầu 6.
    */
    tienVe() {
      // Nếu số lượng không hợp lệ, chưa thể tính tiền vé -> trả về 0.
      if (this.soLuongHopLe === false) {
        // Giá trị 0 an toàn cho mọi phép cộng phía sau.
        return 0
      }

      // Công thức: đơn giá vé × số lượng vé (đã chắc chắn là số nguyên 1 – 10).
      return this.giaVe * Number(this.soLuong)
    },

    /*
      TIỀN COMBO.
      - Combo giá 45.000 đồng cho MỖI ĐƠN đặt vé (không phụ thuộc số lượng vé).
      - Có tick checkbox -> 45.000; không tick -> 0.
    */
    tienCombo() {
      // Toán tử ba ngôi: nếu chonCombo là true thì trả về GIA_COMBO, ngược lại 0.
      return this.chonCombo === true ? GIA_COMBO : 0
    },

    /*
      TỔNG TIỀN (YÊU CẦU 6 - computed bắt buộc).
      Công thức của đề:
        Tổng tiền = Giá vé × Số lượng + Tiền combo
      - Khi số lượng chưa hợp lệ, tienVe = 0. Tuy nhiên giao diện sẽ hiển thị "—"
        (qua tongTienHienThi) chứ không hiển thị một con số dễ gây hiểu nhầm.
      - Với 2 vé VIP + combo: 100.000 × 2 + 45.000 = 245.000.
    */
    tongTien() {
      // Cộng tiền vé và tiền combo. Cả hai đều là số nguyên đồng nên không có sai số thực.
      return this.tienVe + this.tienCombo
    },

    /*
      TIỀN VÉ ĐỂ HIỂN THỊ.
      - Nếu số lượng hợp lệ -> định dạng số tiền (ví dụ "140.000 đồng").
      - Nếu chưa hợp lệ -> "—" thay vì "0 đồng" gây hiểu nhầm.
    */
    tienVeHienThi() {
      // Kiểm tra điều kiện hợp lệ trước khi định dạng.
      if (this.soLuongHopLe === false) {
        // Dấu gạch ngang thể hiện "chưa tính được".
        return '—'
      }

      // Gọi method định dạng tiền để thêm dấu phân cách hàng nghìn và đơn vị "đồng".
      return this.dinhDangTien(this.tienVe)
    },

    /*
      TỔNG TIỀN ĐỂ HIỂN THỊ.
      - Nếu số lượng hợp lệ -> định dạng tongTien.
      - Nếu chưa hợp lệ -> "—".
    */
    tongTienHienThi() {
      // Chỉ hiển thị tổng tiền khi mọi dữ liệu tính tiền đã hợp lệ.
      if (this.soLuongHopLe === false) {
        // Chưa thể tính tổng tiền.
        return '—'
      }

      // Định dạng tổng tiền theo chuẩn Việt Nam, kèm chữ "đồng".
      return this.dinhDangTien(this.tongTien)
    },

    /*
      NÚT "ĐẶT VÉ" CÓ ĐƯỢC BẬT KHÔNG (YÊU CẦU 8).
      Điều kiện bắt buộc của đề:
        1. Họ tên không rỗng (sau khi trim).
        2. Số lượng hợp lệ (số nguyên 1 – 10).
      Ngoài ra kiểm tra thêm suất chiếu và loại vé để chống dữ liệu bị sửa sai
      từ bên ngoài giao diện. Trong sử dụng bình thường, hai điều kiện này luôn đúng
      vì người dùng chỉ chọn được từ danh sách có sẵn.
    */
    coTheDatVe() {
      // Tất cả điều kiện phải cùng đúng thì nút mới được bật.
      return (
        this.hoTenHopLe === true &&
        this.soLuongHopLe === true &&
        this.suatChieuHopLe === true &&
        this.loaiVeHopLe === true
      )
    },

    /*
      NÚT GIẢM SỐ LƯỢNG CÓ BỊ KHOÁ KHÔNG.
      - Chỉ khoá khi số lượng ĐANG HỢP LỆ và đã bằng mức nhỏ nhất (1).
      - Khi số lượng đang sai (ví dụ rỗng), vẫn cho bấm để method tự đưa về 1.
    */
    khongTheGiam() {
      // Điều kiện: dữ liệu hợp lệ và giá trị hiện tại <= 1.
      return this.soLuongHopLe === true && Number(this.soLuong) <= SO_LUONG_TOI_THIEU
    },

    /*
      NÚT TĂNG SỐ LƯỢNG CÓ BỊ KHOÁ KHÔNG.
      - Chỉ khoá khi số lượng ĐANG HỢP LỆ và đã bằng mức lớn nhất (10).
      - Khi số lượng đang sai (ví dụ 11), vẫn cho bấm để method tự kẹp về 10.
    */
    khongTheTang() {
      // Điều kiện: dữ liệu hợp lệ và giá trị hiện tại >= 10.
      return this.soLuongHopLe === true && Number(this.soLuong) >= SO_LUONG_TOI_DA
    }
  },

  /*
    ------------------------------------------------------------------------
    methods: nơi khai báo các HÀM XỬ LÝ (được gọi từ template hoặc từ code khác).
    Khác với computed: method KHÔNG được cache, mỗi lần gọi là chạy lại từ đầu.
    ------------------------------------------------------------------------
  */
  methods: {
    /*
      ĐỊNH DẠNG SỐ TIỀN THÀNH CHUỖI DỄ ĐỌC.
      Ví dụ:
        70000   -> "70.000 đồng"
        245000  -> "245.000 đồng"
        0       -> "0 đồng"
      Tham số:
        - soTien: số tiền cần định dạng (có thể là số hoặc chuỗi số).
    */
    dinhDangTien(soTien) {
      // Chuyển đầu vào thành số để chắc chắn định dạng đúng.
      const so = Number(soTien)

      // Nếu không phải số hữu hạn (NaN, Infinity) -> trả về dấu gạch ngang.
      if (Number.isFinite(so) === false) {
        // Không thể định dạng một giá trị không phải số.
        return '—'
      }

      // Dùng Intl.NumberFormat để thêm dấu chấm phân cách hàng nghìn,
      // sau đó nối thêm đơn vị tiền tệ tiếng Việt.
      return `${BO_DINH_DANG_SO.format(so)} đồng`
    },

    /*
      XỬ LÝ KHI RỜI Ô HỌ TÊN (sự kiện @blur).
      - Đánh dấu đã chạm vào ô để dòng lỗi (nếu có) được hiển thị.
      - Trim thêm một lần để dữ liệu chắc chắn không còn khoảng trắng thừa.
    */
    xuLyRoiOHoTen() {
      // Bật cờ "đã chạm" -> từ nay nếu họ tên rỗng thì hiện lỗi.
      this.daChamVaoHoTen = true

      // Gán lại họ tên đã bỏ khoảng trắng hai đầu (an toàn kép cùng v-model.trim).
      this.hoTen = this.hoTen.trim()
    },

    /*
      TĂNG SỐ LƯỢNG VÉ THÊM 1 (nút "+").
      - Luôn đảm bảo kết quả là số nguyên trong khoảng 1 – 10.
      - Xử lý được cả trường hợp dữ liệu hiện tại đang sai (rỗng, 2.5, 11...).
    */
    tangSoLuong() {
      // Chuyển giá trị hiện tại thành số (chuỗi rỗng '' sẽ thành 0).
      const soHienTai = Number(this.soLuong)

      // Nếu không phải số hữu hạn (NaN, Infinity) thì coi như 0 để phép cộng bên dưới an toàn.
      const coSo = Number.isFinite(soHienTai) === true ? Math.trunc(soHienTai) : 0

      // Cộng 1, sau đó kẹp (clamp) kết quả trong khoảng 1 – 10:
      // - Math.max(1, coSo + 1) đảm bảo không nhỏ hơn 1.
      // - Math.min(10, ...)      đảm bảo không lớn hơn 10.
      this.soLuong = Math.min(SO_LUONG_TOI_DA, Math.max(SO_LUONG_TOI_THIEU, coSo + 1))
    },

    /*
      GIẢM SỐ LƯỢNG VÉ ĐI 1 (nút "−").
      - Luôn đảm bảo kết quả là số nguyên trong khoảng 1 – 10.
      - Với số lẻ như 2.5, dùng Math.ceil để "làm tròn lên" trước khi trừ,
        tránh trường hợp 2.5 - 1 = 1.5 vẫn là số lẻ.
    */
    giamSoLuong() {
      // Chuyển giá trị hiện tại thành số (chuỗi rỗng '' sẽ thành 0).
      const soHienTai = Number(this.soLuong)

      // Nếu không phải số hữu hạn thì coi như mức nhỏ nhất để phép trừ an toàn.
      const coSo = Number.isFinite(soHienTai) === true
        ? Math.ceil(soHienTai)
        : SO_LUONG_TOI_THIEU

      // Trừ 1, sau đó kẹp kết quả trong khoảng 1 – 10.
      this.soLuong = Math.min(SO_LUONG_TOI_DA, Math.max(SO_LUONG_TOI_THIEU, coSo - 1))
    },

    /*
      CHUẨN HOÁ SỐ LƯỢNG KHI RỜI Ô NHẬP (sự kiện @blur).
      Mục đích: không bao giờ để ô số lượng ở trạng thái sai sau khi người dùng rời khỏi.
      Quy tắc:
        - Ô trống hoặc không phải số -> đưa về 1.
        - Số lẻ (2.5)               -> cắt phần thập phân (2).
        - Nhỏ hơn 1                 -> đưa về 1.
        - Lớn hơn 10                -> đưa về 10.
    */
    chotSoLuong() {
      // Trường hợp 1: ô nhập đang trống -> đưa về mức nhỏ nhất.
      if (this.soLuong === '' || this.soLuong === null || this.soLuong === undefined) {
        // Gán số lượng tối thiểu (1).
        this.soLuong = SO_LUONG_TOI_THIEU
        // Kết thúc method.
        return
      }

      // Chuyển giá trị hiện tại thành số.
      const soHienTai = Number(this.soLuong)

      // Trường hợp 2: không phải số hữu hạn -> đưa về mức nhỏ nhất.
      if (Number.isFinite(soHienTai) === false) {
        // Gán số lượng tối thiểu (1).
        this.soLuong = SO_LUONG_TOI_THIEU
        // Kết thúc method.
        return
      }

      // Cắt bỏ phần thập phân để được số nguyên (2.9 -> 2, -2.9 -> -2).
      const soNguyen = Math.trunc(soHienTai)

      // Kẹp số nguyên trong khoảng 1 – 10 rồi gán lại vào dữ liệu.
      this.soLuong = Math.min(SO_LUONG_TOI_DA, Math.max(SO_LUONG_TOI_THIEU, soNguyen))
    },

    /*
      XỬ LÝ GỬI FORM ĐẶT VÉ (YÊU CẦU 9).
      Được gọi từ @submit.prevent của thẻ <form>.
      Các bước:
        1. Đánh dấu đã chạm vào ô họ tên (để lỗi hiện ra nếu dữ liệu sai).
        2. Kiểm tra lại toàn bộ điều kiện (chốt chặn cuối cùng, kể cả khi nút bị vô hiệu
           hoá bị bỏ qua bởi thao tác bất thường).
        3. Tạo một OBJECT XÁC NHẬN chứa đầy đủ thông tin và số tiền đã tính.
        4. Gán vào "xacNhan" -> khối xác nhận tự hiện ra trên trang.
    */
    datVe() {
      // Bước 1: đánh dấu đã tương tác với ô họ tên.
      this.daChamVaoHoTen = true

      // Bước 2: nếu dữ liệu chưa hợp lệ thì DỪNG, không tạo xác nhận.
      if (this.coTheDatVe === false) {
        // Kết thúc sớm; giao diện đã hiển thị lỗi và nút đang bị khoá.
        return
      }

      // Lấy số lượng vé dưới dạng số (đã chắc chắn là số nguyên 1 – 10).
      const soLuong = Number(this.soLuong)

      // Lấy đơn giá vé theo loại vé đang chọn.
      const giaVe = this.giaVe

      // Tính tiền vé = đơn giá × số lượng.
      const tienVe = giaVe * soLuong

      // Tính tiền combo: có tick thì 45.000, không tick thì 0.
      const tienCombo = this.chonCombo === true ? GIA_COMBO : 0

      // Bước 3: tạo "ảnh chụp" thông tin đặt vé tại thời điểm này.
      // Dùng object mới (không tham chiếu tới data) để xác nhận không bị thay đổi
      // nếu sau đó người dùng sửa lại form.
      this.xacNhan = {
        // Họ tên đã trim, chắc chắn không rỗng.
        hoTen: this.hoTen.trim(),
        // Tên hiển thị của suất chiếu (ví dụ "18:00 – Suất tối").
        suatChieu: this.nhanSuatChieu,
        // Tên hiển thị của loại vé ("Vé thường" / "Vé VIP").
        loaiVe: this.nhanLoaiVe,
        // Số lượng vé (kiểu số).
        soLuong: soLuong,
        // Trạng thái combo (true/false).
        chonCombo: this.chonCombo,
        // Đơn giá vé tại thời điểm đặt.
        giaVe: giaVe,
        // Thành tiền vé.
        tienVe: tienVe,
        // Thành tiền combo.
        tienCombo: tienCombo,
        // Tổng tiền = tiền vé + tiền combo.
        tongTien: tienVe + tienCombo
      }
    },

    /*
      ĐẶT LẠI FORM (YÊU CẦU 7).
      - Đưa mọi trường về giá trị mặc định trong DU_LIEU_BAN_DAU.
      - Xoá thông tin xác nhận của lần đặt trước.
      - Tắt cờ "đã chạm" để không hiện lỗi ngay sau khi đặt lại.
    */
    datLaiForm() {
      // Khôi phục họ tên (chuỗi rỗng).
      this.hoTen = DU_LIEU_BAN_DAU.hoTen
      // Khôi phục suất chiếu mặc định.
      this.suatChieu = DU_LIEU_BAN_DAU.suatChieu
      // Khôi phục loại vé mặc định.
      this.loaiVe = DU_LIEU_BAN_DAU.loaiVe
      // Khôi phục số lượng mặc định.
      this.soLuong = DU_LIEU_BAN_DAU.soLuong
      // Bỏ chọn combo.
      this.chonCombo = DU_LIEU_BAN_DAU.chonCombo

      // Xoá khối xác nhận của lần đặt vé trước (nếu đang hiển thị).
      this.xacNhan = null

      // Đặt lại cờ "đã chạm" để form sạch như lúc mới mở.
      this.daChamVaoHoTen = false
    }
  }
}
</script>

<style scoped>
/*
  "scoped" nghĩa là các quy tắc CSS dưới đây CHỈ áp dụng cho component này,
  không làm ảnh hưởng tới các component khác trong cùng ứng dụng.
*/

/* ---------------------------------------------------------------------------
   1. KHUNG TRANG VÀ TIÊU ĐỀ
   --------------------------------------------------------------------------- */

/* Phần tử gốc của component. */
.trang-dat-ve {
  /* Màu nền trắng để nổi bật trên nền xanh xám của body. */
  background-color: #ffffff;
  /* Bo tròn các góc của khung. */
  border-radius: 16px;
  /* Khoảng đệm bên trong khung. */
  padding: 28px;
  /* Đổ bóng nhẹ tạo cảm giác thẻ nổi trên nền. */
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

/* Khối tiêu đề đầu trang. */
.phan-dau {
  /* Khoảng cách phía dưới khối tiêu đề. */
  margin-bottom: 22px;
  /* Canh giữa nội dung. */
  text-align: center;
}

/* Tiêu đề chính. */
.tieu-de {
  /* Bỏ khoảng lề mặc định của thẻ h1. */
  margin: 0 0 8px 0;
  /* Cỡ chữ lớn. */
  font-size: 28px;
  /* Màu xanh đậm chủ đạo. */
  color: #1d4ed8;
  /* Chữ đậm. */
  font-weight: 800;
}

/* Dòng mô tả dưới tiêu đề. */
.mo-ta {
  /* Bỏ khoảng lề mặc định. */
  margin: 0 auto;
  /* Giới hạn chiều rộng để dòng chữ không quá dài. */
  max-width: 560px;
  /* Màu xám vừa. */
  color: #4b5563;
  /* Cỡ chữ nhỏ hơn tiêu đề. */
  font-size: 15px;
}

/* ---------------------------------------------------------------------------
   2. BIỂU MẪU VÀ CÁC NHÓM TRƯỜNG
   --------------------------------------------------------------------------- */

/* Khung biểu mẫu chính. */
.bieu-mau {
  /* Dùng grid để xếp các nhóm trường thành một cột. */
  display: grid;
  /* Khoảng cách giữa các nhóm trường. */
  gap: 18px;
}

/* Mỗi nhóm trường (fieldset). */
.nhom-truong {
  /* Bỏ viền mặc định của fieldset. */
  border: 1px solid #e5e7eb;
  /* Bo tròn góc. */
  border-radius: 12px;
  /* Khoảng đệm bên trong. */
  padding: 14px 16px 16px;
  /* Bỏ khoảng lề mặc định. */
  margin: 0;
  /* Nền xám rất nhạt để tách nhóm. */
  background-color: #f9fafb;
}

/* Tiêu đề của nhóm trường (legend). */
.nhan-nhom {
  /* Đệm hai bên để chữ không dính vào đường viền. */
  padding: 0 6px;
  /* Chữ đậm. */
  font-weight: 700;
  /* Màu chữ xanh đậm. */
  color: #1e3a8a;
  /* Cỡ chữ vừa. */
  font-size: 15px;
}

/* Nhãn của từng ô nhập. */
.nhan-o {
  /* Hiển thị dạng khối để chiếm trọn một dòng. */
  display: block;
  /* Khoảng cách phía dưới nhãn. */
  margin-bottom: 6px;
  /* Chữ hơi đậm. */
  font-weight: 600;
  /* Màu chữ xám đậm. */
  color: #374151;
  /* Cỡ chữ vừa. */
  font-size: 14px;
}

/* Dấu * báo trường bắt buộc. */
.bat-buoc {
  /* Màu đỏ. */
  color: #dc2626;
  /* Đậm hơn để dễ thấy. */
  font-weight: 700;
}

/* Kiểu dùng chung cho ô nhập chữ, ô số và ô chọn. */
.o-nhap {
  /* Chiếm toàn bộ chiều rộng khung chứa. */
  width: 100%;
  /* Khoảng đệm trong ô. */
  padding: 10px 12px;
  /* Viền xám nhạt. */
  border: 1px solid #d1d5db;
  /* Bo tròn góc. */
  border-radius: 8px;
  /* Nền trắng. */
  background-color: #ffffff;
  /* Màu chữ. */
  color: #111827;
  /* Cỡ chữ vừa. */
  font-size: 15px;
  /* Chuyển màu mượt khi focus. */
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

/* Trạng thái khi ô nhập được chọn (focus). */
.o-nhap:focus {
  /* Đổi viền sang màu xanh. */
  border-color: #2563eb;
  /* Bỏ viền mặc định của trình duyệt. */
  outline: none;
  /* Thêm quầng sáng xanh để dễ nhận biết. */
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
}

/* Ô nhập đang có lỗi (áp dụng qua :class). */
.o-nhap-loi {
  /* Viền đỏ. */
  border-color: #dc2626;
  /* Nền hồng rất nhạt. */
  background-color: #fef2f2;
}

/* Ô nhập lỗi khi được focus. */
.o-nhap-loi:focus {
  /* Giữ viền đỏ. */
  border-color: #dc2626;
  /* Quầng sáng đỏ thay vì xanh. */
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.18);
}

/* Dòng gợi ý / thông báo phụ. */
.goi-y {
  /* Bỏ khoảng lề mặc định. */
  margin: 8px 0 0 0;
  /* Cỡ chữ nhỏ. */
  font-size: 13px;
  /* Màu xám. */
  color: #6b7280;
}

/* Dòng thông báo lỗi. */
.thong-bao-loi {
  /* Bỏ khoảng lề mặc định. */
  margin: 8px 0 0 0;
  /* Cỡ chữ nhỏ. */
  font-size: 13px;
  /* Chữ đậm vừa. */
  font-weight: 600;
  /* Màu đỏ. */
  color: #b91c1c;
}

/* ---------------------------------------------------------------------------
   3. CÁC LỰA CHỌN (RADIO / CHECKBOX)
   --------------------------------------------------------------------------- */

/* Mỗi dòng lựa chọn là một label lớn, bấm được toàn bộ. */
.o-lua-chon {
  /* Dùng flex để xếp ô chọn, tên và giá trên cùng một hàng. */
  display: flex;
  /* Canh giữa theo chiều dọc. */
  align-items: center;
  /* Khoảng cách giữa các phần tử. */
  gap: 10px;
  /* Khoảng đệm trong dòng. */
  padding: 10px 12px;
  /* Viền xám nhạt. */
  border: 1px solid #e5e7eb;
  /* Bo tròn. */
  border-radius: 10px;
  /* Nền trắng. */
  background-color: #ffffff;
  /* Con trỏ dạng bàn tay để biết có thể bấm. */
  cursor: pointer;
  /* Chuyển màu mượt khi chọn. */
  transition: border-color 0.2s ease, background-color 0.2s ease;
}

/* Khoảng cách giữa hai dòng lựa chọn liên tiếp. */
.o-lua-chon + .o-lua-chon {
  /* Tạo khoảng trống phía trên dòng thứ hai. */
  margin-top: 8px;
}

/* Dòng lựa chọn đang được chọn (áp dụng qua :class). */
.o-lua-chon.dang-chon {
  /* Viền xanh. */
  border-color: #2563eb;
  /* Nền xanh rất nhạt. */
  background-color: #eff6ff;
}

/* Ô radio / checkbox bên trong label. */
.o-lua-chon input {
  /* Kích thước lớn hơn mặc định một chút cho dễ bấm. */
  width: 18px;
  height: 18px;
  /* Không co lại khi màn hình hẹp. */
  flex-shrink: 0;
  /* Con trỏ bàn tay. */
  cursor: pointer;
  /* Màu xanh cho ô đã chọn (trình duyệt hỗ trợ accent-color). */
  accent-color: #2563eb;
}

/* Tên của lựa chọn (Vé thường, Vé VIP, Combo...). */
.ten-lua-chon {
  /* Chiếm hết khoảng trống còn lại để đẩy giá sang phải. */
  flex: 1;
  /* Chữ hơi đậm. */
  font-weight: 600;
  /* Màu chữ. */
  color: #1f2937;
}

/* Giá tiền hiển thị bên phải lựa chọn. */
.gia {
  /* Cỡ chữ nhỏ hơn. */
  font-size: 13px;
  /* Chữ đậm. */
  font-weight: 700;
  /* Màu xanh. */
  color: #1d4ed8;
  /* Không xuống dòng giữa số tiền và đơn vị. */
  white-space: nowrap;
}

/* ---------------------------------------------------------------------------
   4. CỤM TĂNG / GIẢM SỐ LƯỢNG
   --------------------------------------------------------------------------- */

/* Cụm gồm nút giảm, ô nhập, nút tăng. */
.cum-so-luong {
  /* Dùng flex để xếp ba phần tử nằm ngang. */
  display: flex;
  /* Canh giữa theo chiều dọc. */
  align-items: center;
  /* Khoảng cách giữa các phần tử. */
  gap: 8px;
}

/* Ô nhập số lượng nằm giữa hai nút. */
.o-so-luong {
  /* Canh giữa số trong ô. */
  text-align: center;
  /* Chữ đậm để dễ đọc. */
  font-weight: 700;
  /* Cỡ chữ lớn hơn. */
  font-size: 18px;
  /* Giới hạn chiều rộng để cụm nút không quá dài. */
  max-width: 110px;
}

/* Nút tăng / giảm hình vuông. */
.nut-so-luong {
  /* Kích thước vuông. */
  width: 42px;
  height: 42px;
  /* Không co lại. */
  flex-shrink: 0;
  /* Viền xanh nhạt. */
  border: 1px solid #bfdbfe;
  /* Bo tròn. */
  border-radius: 8px;
  /* Nền xanh rất nhạt. */
  background-color: #eff6ff;
  /* Màu chữ xanh. */
  color: #1d4ed8;
  /* Cỡ chữ lớn cho dấu + / −. */
  font-size: 20px;
  /* Chữ đậm. */
  font-weight: 700;
  /* Con trỏ bàn tay. */
  cursor: pointer;
  /* Chuyển màu mượt. */
  transition: background-color 0.2s ease, opacity 0.2s ease;
}

/* Hover khi nút đang bật. */
.nut-so-luong:hover:not(:disabled) {
  /* Nền xanh đậm hơn. */
  background-color: #dbeafe;
}

/* Trạng thái nút bị khoá. */
.nut-so-luong:disabled {
  /* Giảm độ mờ để thấy rõ không bấm được. */
  opacity: 0.45;
  /* Con trỏ thường (không phải bàn tay). */
  cursor: not-allowed;
}

/* ---------------------------------------------------------------------------
   5. BẢNG TÍNH TIỀN
   --------------------------------------------------------------------------- */

/* Khung tạm tính. */
.bang-tinh-tien {
  /* Nền xanh rất nhạt. */
  background-color: #eff6ff;
  /* Viền xanh nhạt. */
  border: 1px solid #bfdbfe;
  /* Bo tròn. */
  border-radius: 12px;
  /* Khoảng đệm trong. */
  padding: 16px;
}

/* Tiêu đề nhỏ của bảng tính tiền. */
.tieu-de-nho {
  /* Bỏ khoảng lề mặc định. */
  margin: 0 0 12px 0;
  /* Cỡ chữ vừa. */
  font-size: 16px;
  /* Màu xanh đậm. */
  color: #1e3a8a;
}

/* Mỗi dòng tiền (nhãn bên trái, số tiền bên phải). */
.dong-tien {
  /* Dùng flex để đẩy hai bên ra xa nhau. */
  display: flex;
  /* Nhãn bên trái, số tiền bên phải. */
  justify-content: space-between;
  /* Canh giữa theo chiều dọc. */
  align-items: center;
  /* Khoảng cách giữa nhãn và số tiền. */
  gap: 12px;
  /* Khoảng cách dọc giữa các dòng. */
  padding: 6px 0;
  /* Cỡ chữ vừa. */
  font-size: 14px;
}

/* Nhãn của dòng tiền. */
.nhan-tien {
  /* Màu xám đậm. */
  color: #374151;
  /* Không co lại khi số tiền dài. */
  flex-shrink: 0;
}

/* Phần giá trị tiền bên phải. */
.gia-tri-tien {
  /* Dùng flex để xếp công thức và số tiền. */
  display: flex;
  /* Canh giữa theo chiều dọc. */
  align-items: center;
  /* Khoảng cách giữa công thức và số tiền. */
  gap: 10px;
  /* Cho phép xuống dòng trên màn hình hẹp. */
  flex-wrap: wrap;
  /* Canh phải. */
  justify-content: flex-end;
  /* Canh chữ bên phải. */
  text-align: right;
}

/* Phần công thức / ghi chú nhỏ bên cạnh số tiền. */
.cong-thuc {
  /* Cỡ chữ nhỏ. */
  font-size: 12px;
  /* Màu xám. */
  color: #6b7280;
}

/* Dòng tổng tiền. */
.dong-tong {
  /* Đường kẻ phía trên để tách khỏi các dòng con. */
  border-top: 1px dashed #93c5fd;
  /* Khoảng cách phía trên. */
  margin-top: 6px;
  /* Khoảng đệm phía trên. */
  padding-top: 10px;
  /* Cỡ chữ lớn hơn. */
  font-size: 16px;
}

/* Chữ "Tổng tiền". */
.dong-tong .nhan-tien {
  /* Chữ đậm. */
  font-weight: 700;
  /* Màu xanh đậm. */
  color: #1e3a8a;
}

/* Số tổng tiền. */
.tong-tien {
  /* Cỡ chữ lớn. */
  font-size: 22px;
  /* Chữ rất đậm. */
  font-weight: 800;
  /* Màu đỏ nổi bật. */
  color: #dc2626;
  /* Không xuống dòng giữa số và đơn vị. */
  white-space: nowrap;
}

/* Dòng ghi chú công thức dưới cùng bảng tính tiền. */
.cong-thuc-tong {
  /* Bỏ khoảng lề mặc định. */
  margin: 10px 0 0 0;
  /* Cỡ chữ nhỏ. */
  font-size: 12px;
  /* Màu xám. */
  color: #6b7280;
  /* Canh phải. */
  text-align: right;
  /* Kiểu chữ nghiêng nhẹ để phân biệt với nội dung chính. */
  font-style: italic;
}

/* ---------------------------------------------------------------------------
   6. HÀNG NÚT THAO TÁC
   --------------------------------------------------------------------------- */

/* Hàng chứa nút Đặt vé và Đặt lại. */
.hang-nut {
  /* Dùng flex để xếp hai nút nằm ngang. */
  display: flex;
  /* Khoảng cách giữa hai nút. */
  gap: 12px;
  /* Cho phép xuống dòng khi màn hình hẹp. */
  flex-wrap: wrap;
}

/* Kiểu chung của hai nút. */
.nut-dat-ve,
.nut-dat-lai {
  /* Khoảng đệm trong nút. */
  padding: 12px 22px;
  /* Bỏ viền mặc định. */
  border: none;
  /* Bo tròn. */
  border-radius: 10px;
  /* Cỡ chữ vừa. */
  font-size: 15px;
  /* Chữ đậm. */
  font-weight: 700;
  /* Con trỏ bàn tay. */
  cursor: pointer;
  /* Chuyển màu mượt. */
  transition: background-color 0.2s ease, opacity 0.2s ease, transform 0.1s ease;
}

/* Nút "Đặt vé": chiếm phần lớn chiều rộng. */
.nut-dat-ve {
  /* Giãn ra lấp đầy khoảng trống còn lại. */
  flex: 1;
  /* Nền xanh đậm. */
  background-color: #2563eb;
  /* Chữ trắng. */
  color: #ffffff;
}

/* Hover khi nút Đặt vé đang bật. */
.nut-dat-ve:hover:not(:disabled) {
  /* Nền xanh đậm hơn. */
  background-color: #1d4ed8;
}

/* Nhấn xuống tạo hiệu ứng nút lún nhẹ. */
.nut-dat-ve:active:not(:disabled) {
  /* Dịch xuống 1px. */
  transform: translateY(1px);
}

/* Nút Đặt vé bị khoá. */
.nut-dat-ve:disabled {
  /* Nền xám. */
  background-color: #cbd5e1;
  /* Chữ xám nhạt. */
  color: #f8fafc;
  /* Con trỏ không cho bấm. */
  cursor: not-allowed;
  /* Giảm độ mờ. */
  opacity: 0.8;
}

/* Nút "Đặt lại form". */
.nut-dat-lai {
  /* Nền trắng. */
  background-color: #ffffff;
  /* Chữ xám đậm. */
  color: #374151;
  /* Viền xám. */
  border: 1px solid #d1d5db;
}

/* Hover nút Đặt lại. */
.nut-dat-lai:hover {
  /* Nền xám rất nhạt. */
  background-color: #f3f4f6;
}

/* Dòng chẩn đoán khi nút Đặt vé bị khoá. */
.goi-y-chan-doan {
  /* Canh giữa. */
  text-align: center;
  /* Màu cam đậm để gây chú ý. */
  color: #b45309;
  /* Chữ hơi đậm. */
  font-weight: 600;
}

/* ---------------------------------------------------------------------------
   7. KHỐI XÁC NHẬN ĐẶT VÉ
   --------------------------------------------------------------------------- */

/* Khung xác nhận sau khi đặt vé thành công. */
.hop-xac-nhan {
  /* Khoảng cách phía trên khung. */
  margin-top: 24px;
  /* Nền xanh lá rất nhạt. */
  background-color: #f0fdf4;
  /* Viền xanh lá. */
  border: 1px solid #86efac;
  /* Bo tròn. */
  border-radius: 12px;
  /* Khoảng đệm trong. */
  padding: 18px 20px;
}

/* Tiêu đề khối xác nhận. */
.tieu-de-xac-nhan {
  /* Bỏ khoảng lề mặc định. */
  margin: 0 0 10px 0;
  /* Cỡ chữ vừa. */
  font-size: 18px;
  /* Màu xanh lá đậm. */
  color: #15803d;
}

/* Lời cảm ơn. */
.loi-cam-on {
  /* Bỏ khoảng lề mặc định. */
  margin: 0 0 12px 0;
  /* Màu chữ xám đậm. */
  color: #1f2937;
}

/* Danh sách chi tiết xác nhận. */
.chi-tiet-xac-nhan {
  /* Bỏ khoảng lề mặc định của dl. */
  margin: 0;
}

/* Mỗi dòng trong bảng xác nhận. */
.dong-xac-nhan {
  /* Dùng flex để đẩy nhãn và giá trị sang hai bên. */
  display: flex;
  /* Canh hai bên. */
  justify-content: space-between;
  /* Canh giữa theo chiều dọc. */
  align-items: center;
  /* Khoảng cách giữa nhãn và giá trị. */
  gap: 12px;
  /* Khoảng cách dọc giữa các dòng. */
  padding: 6px 0;
  /* Cỡ chữ vừa. */
  font-size: 14px;
}

/* Nhãn của dòng xác nhận. */
.dong-xac-nhan dt {
  /* Màu xám đậm. */
  color: #4b5563;
  /* Chữ đậm vừa. */
  font-weight: 600;
}

/* Giá trị của dòng xác nhận. */
.dong-xac-nhan dd {
  /* Bỏ khoảng lề mặc định của dd. */
  margin: 0;
  /* Canh chữ bên phải. */
  text-align: right;
  /* Chữ đậm. */
  font-weight: 600;
  /* Màu chữ. */
  color: #111827;
}

/* Dòng tổng tiền trong xác nhận. */
.hop-xac-nhan .dong-tong {
  /* Đường kẻ phía trên. */
  border-top: 1px dashed #86efac;
  /* Khoảng cách phía trên. */
  margin-top: 6px;
  /* Khoảng đệm phía trên. */
  padding-top: 10px;
}

/* Nhãn tổng tiền. */
.hop-xac-nhan .dong-tong dt {
  /* Cỡ chữ lớn hơn. */
  font-size: 16px;
  /* Màu xanh lá đậm. */
  color: #15803d;
}

/* Số tổng tiền trong xác nhận. */
.hop-xac-nhan .dong-tong dd {
  /* Cỡ chữ lớn. */
  font-size: 20px;
  /* Chữ rất đậm. */
  font-weight: 800;
  /* Màu đỏ nổi bật. */
  color: #dc2626;
}

/* ---------------------------------------------------------------------------
   8. HIỂN THỊ TRÊN MÀN HÌNH NHỎ (điện thoại)
   --------------------------------------------------------------------------- */

/* Áp dụng khi chiều rộng màn hình từ 520px trở xuống. */
@media (max-width: 520px) {
  /* Giảm khoảng đệm khung trang để có thêm chỗ cho nội dung. */
  .trang-dat-ve {
    /* Khoảng đệm nhỏ hơn. */
    padding: 18px;
  }

  /* Giảm cỡ tiêu đề chính. */
  .tieu-de {
    /* Cỡ chữ nhỏ hơn. */
    font-size: 23px;
  }

  /* Hai nút xếp dọc thay vì nằm ngang. */
  .hang-nut {
    /* Chuyển hướng flex sang cột dọc. */
    flex-direction: column;
  }

  /* Nút Đặt vé chiếm toàn bộ chiều rộng. */
  .nut-dat-ve {
    /* Chiều rộng 100%. */
    width: 100%;
  }

  /* Nút Đặt lại chiếm toàn bộ chiều rộng. */
  .nut-dat-lai {
    /* Chiều rộng 100%. */
    width: 100%;
  }

  /* Dòng tiền chuyển sang xếp dọc cho dễ đọc. */
  .dong-tien {
    /* Xếp nhãn và giá trị thành cột. */
    flex-direction: column;
    /* Canh trái. */
    align-items: flex-start;
  }

  /* Phần giá trị tiền canh trái. */
  .gia-tri-tien {
    /* Canh trái. */
    justify-content: flex-start;
    /* Canh chữ bên trái. */
    text-align: left;
  }

  /* Ghi chú công thức canh trái. */
  .cong-thuc-tong {
    /* Canh trái trên màn hình nhỏ. */
    text-align: left;
  }
}
</style>
