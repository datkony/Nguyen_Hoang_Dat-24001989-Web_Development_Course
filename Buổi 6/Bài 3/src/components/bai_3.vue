<!--
  ============================================================================
  BÀI 3: QUẢN LÝ CÔNG VIỆC (VUE 3 - SINGLE FILE COMPONENT)
  ----------------------------------------------------------------------------
  File này gồm 3 phần chuẩn của một Vue SFC:
    1. <template> : phần giao diện (HTML + các chỉ thị của Vue)
    2. <script>   : phần logic (dữ liệu, computed, methods)
    3. <style>    : phần trình bày (CSS), dùng "scoped" để chỉ áp dụng cho file này

  TÓM TẮT CÁC YÊU CẦU CỦA ĐỀ VÀ CÁCH THỰC HIỆN:
    1. Mỗi công việc gồm id, tên và trạng thái hoàn thành
       -> mảng "danhSachCongViec" chứa các object { id, ten, hoanThanh }.
    2. Thêm công việc bằng nút "Thêm" hoặc nhấn Enter
       -> <form @submit.prevent="themCongViec"> có nút type="submit";
          trình duyệt tự gửi form khi nhấn Enter trong ô nhập.
    3. Không cho thêm tên rỗng hoặc chỉ chứa khoảng trắng
       -> method "themCongViec" dùng trim() và dừng lại nếu chuỗi rỗng;
          computed "loiNhap" hiển thị thông báo lỗi.
    4. Hiển thị danh sách bằng v-for, dùng id làm :key
       -> <li v-for="congViec in danhSachDaLoc" :key="congViec.id">.
    5. Đánh dấu hoàn thành bằng checkbox; việc đã xong hiển thị gạch ngang
       -> checkbox dùng v-model="congViec.hoanThanh";
          class "gach-ngang" dùng text-decoration: line-through.
    6. Xóa từng công việc
       -> nút "Xóa" gọi method "xoaCongViec(congViec.id)".
    7. Lọc theo ba trạng thái: Tất cả / Chưa hoàn thành / Đã hoàn thành
       -> biến "boLoc" + computed "danhSachDaLoc".
    8. Dùng computed tính tổng số công việc và số việc chưa hoàn thành
       -> computed "tongSoCongViec" và "soViecChuaHoanThanh".
    9. Nút "Xóa các việc đã hoàn thành"
       -> method "xoaCacViecDaHoanThanh".
   10. Thông báo phù hợp khi danh sách hoặc kết quả lọc rỗng
       -> hai khối "trang-thai-rong" tương ứng hai trường hợp.

  KẾT QUẢ: mọi thao tác thêm, xóa, đổi trạng thái và lọc đều cập nhật
  danh sách + số lượng ngay trên trang, KHÔNG tải lại trang.
  ============================================================================
-->

<template>
  <!--
    <div class="trang-cong-viec"> là phần tử gốc (root) của component.
    Mỗi component trong Vue chỉ được có DUY NHẤT một phần tử gốc ở cấp ngoài cùng.
  -->
  <div class="trang-cong-viec">

    <!-- ==================== PHẦN TIÊU ĐỀ ==================== -->
    <header class="phan-dau">
      <!-- Dòng nhãn nhỏ giúp nhận biết đây là bài 3 của môn học. -->
      <p class="nhan-nho">Vue 3 · Bài 3</p>

      <!-- Tiêu đề chính của trang, nội dung tĩnh nên không cần chỉ thị Vue. -->
      <h1 class="tieu-de">Quản lý công việc</h1>

      <!-- Dòng mô tả ngắn giúp người dùng hiểu các chức năng của trang. -->
      <p class="mo-ta">
        Thêm công việc mới, đánh dấu hoàn thành, xóa từng việc hoặc xóa tất cả
        việc đã xong. Mọi thay đổi được cập nhật ngay, không tải lại trang.
      </p>
    </header>

    <!-- ==================== KHU VỰC THÊM CÔNG VIỆC ==================== -->
    <!--
      - @submit.prevent="themCongViec" : lắng nghe sự kiện submit của form.
        ".prevent" tự động gọi event.preventDefault() để trình duyệt KHÔNG tải lại
        trang (hành vi mặc định của form HTML). Sau đó Vue gọi method "themCongViec".
      - Form có một nút type="submit", nên khi người dùng nhấn Enter trong ô nhập,
        trình duyệt sẽ tự động gửi form (implicit submission). Nhờ vậy yêu cầu
        "thêm bằng nút Thêm hoặc nhấn Enter" chỉ cần MỘT luồng xử lý duy nhất.
      - novalidate : tắt phần kiểm tra mặc định của trình duyệt để ta chủ động
        hiển thị thông báo tiếng Việt bằng Vue.
    -->
    <form class="bieu-mau-them" novalidate @submit.prevent="themCongViec">

      <!-- Nhãn của ô nhập, liên kết với ô nhập qua thuộc tính for = id. -->
      <label class="nhan-o" for="o-ten-cong-viec">Tên công việc</label>

      <!-- Cụm gồm ô nhập và nút "Thêm" nằm cùng một hàng. -->
      <div class="cum-nhap">

        <!--
          Ô nhập tên công việc:
          - ref="oNhapTen" : đánh dấu phần tử để code có thể gọi focus() sau khi thêm
            (giúp người dùng nhập tiếp công việc kế tiếp mà không phải bấm lại ô).
          - v-model="tenMoi" : ràng buộc hai chiều giữa ô nhập và biến "tenMoi".
            KHÔNG dùng modifier .trim ở đây vì .trim của Vue cắt khoảng trắng ngay
            trên từng lần gõ, có thể gây khó chịu khi nhập tên có nhiều từ.
            Việc trim được thực hiện an toàn một lần khi bấm "Thêm"/nhấn Enter.
          - :class="{ 'o-nhap-loi': loiNhap !== '' }" : thêm viền đỏ khi đang có lỗi.
          - :aria-invalid : báo cho trình đọc màn hình biết ô đang sai.
          - :aria-describedby : chỉ trỏ tới dòng lỗi KHI dòng lỗi thực sự tồn tại.
          - autocomplete="off" : tránh trình duyệt gợi ý những tên đã nhập trước đó.
          - @input="xuLyKhiNhap" : mỗi khi nội dung thay đổi, xóa trạng thái lỗi cũ
            nếu người dùng đã nhập được nội dung hợp lệ.
        -->
        <input
          id="o-ten-cong-viec"
          ref="oNhapTen"
          v-model="tenMoi"
          class="o-nhap"
          :class="{ 'o-nhap-loi': loiNhap !== '' }"
          type="text"
          placeholder="Ví dụ: Ôn tập Vue 3"
          autocomplete="off"
          aria-required="true"
          :aria-invalid="loiNhap !== ''"
          :aria-describedby="loiNhap !== '' ? 'goi-y-nhap loi-nhap' : 'goi-y-nhap'"
          @input="xuLyKhiNhap"
        />

        <!--
          Nút "Thêm":
          - type="submit" : gửi form -> kích hoạt @submit.prevent="themCongViec".
            Nút này cũng là "nút gửi mặc định" để phím Enter trong ô nhập hoạt động.
          - Nội dung nút là chữ "Thêm" đúng yêu cầu của đề.
        -->
        <button type="submit" class="nut-them">Thêm</button>
      </div>

      <!-- Dòng gợi ý cách thêm công việc và quy tắc kiểm tra tên. -->
      <p id="goi-y-nhap" class="goi-y">
        Nhập tên rồi bấm nút <strong>Thêm</strong> hoặc nhấn <kbd>Enter</kbd>.
        Tên không được để trống hoặc chỉ chứa khoảng trắng.
      </p>

      <!--
        Dòng lỗi chỉ xuất hiện khi:
          - Người dùng đã bấm "Thêm" (hoặc nhấn Enter) ít nhất một lần, VÀ
          - Tên sau khi trim vẫn rỗng.
        - v-if giúp thêm/xóa phần tử khỏi DOM theo điều kiện.
        - role="alert" yêu cầu trình đọc màn hình đọc ngay thông báo lỗi.
      -->
      <p
        v-if="loiNhap !== ''"
        id="loi-nhap"
        class="thong-bao-loi"
        role="alert"
      >
        {{ loiNhap }}
      </p>
    </form>

    <!-- ==================== BẢNG THỐNG KÊ ==================== -->
    <!--
      YÊU CẦU 8: dùng computed để tính tổng số công việc và số việc chưa hoàn thành.
      - aria-live="polite" : trình đọc màn hình thông báo khi các con số thay đổi.
      - Các con số đều là computed nên tự cập nhật ngay khi thêm/xóa/đổi trạng thái.
    -->
    <section
      class="bang-thong-ke"
      aria-label="Thống kê công việc"
      aria-live="polite"
    >
      <!-- Thẻ 1: tổng số công việc hiện có trong danh sách. -->
      <div class="the-thong-ke">
        <!-- Nhãn của con số. -->
        <span class="nhan-thong-ke">Tổng công việc</span>
        <!-- computed "tongSoCongViec" = độ dài mảng danh sách. -->
        <strong class="so-thong-ke">{{ tongSoCongViec }}</strong>
      </div>

      <!-- Thẻ 2: số công việc CHƯA hoàn thành (yêu cầu 8). -->
      <div class="the-thong-ke the-chua-xong">
        <!-- Nhãn của con số. -->
        <span class="nhan-thong-ke">Chưa hoàn thành</span>
        <!-- computed "soViecChuaHoanThanh" đếm các object có hoanThanh === false. -->
        <strong class="so-thong-ke">{{ soViecChuaHoanThanh }}</strong>
      </div>

      <!-- Thẻ 3: số công việc ĐÃ hoàn thành (phục vụ bộ lọc và nút xóa hàng loạt). -->
      <div class="the-thong-ke the-da-xong">
        <!-- Nhãn của con số. -->
        <span class="nhan-thong-ke">Đã hoàn thành</span>
        <!-- computed "soViecDaHoanThanh" đếm các object có hoanThanh === true. -->
        <strong class="so-thong-ke">{{ soViecDaHoanThanh }}</strong>
      </div>
    </section>

    <!-- ==================== BỘ LỌC VÀ NÚT XÓA HÀNG LOẠT ==================== -->
    <section class="khu-dieu-khien" aria-label="Bộ lọc và thao tác">

      <!--
        Nhóm ba nút lọc (yêu cầu 7).
        - role="group" + aria-label giúp trình đọc màn hình hiểu đây là một nhóm lựa chọn.
        - Mỗi nút là type="button" để KHÔNG vô tình gửi form (dù các nút này nằm
          ngoài form, đặt type rõ ràng vẫn là thói quen an toàn).
        - :class="{ 'dang-chon': ... }" tô sáng nút đang được chọn.
        - :aria-pressed báo trạng thái bật/tắt của từng nút cho công nghệ hỗ trợ.
        - @click="doiBoLoc(...)" đổi giá trị biến "boLoc"; computed "danhSachDaLoc"
          sẽ tự tính lại danh sách hiển thị.
      -->
      <div class="nhom-loc" role="group" aria-label="Lọc theo trạng thái">

        <!-- Nút lọc "Tất cả": hiển thị toàn bộ công việc. -->
        <button
          type="button"
          class="nut-loc"
          :class="{ 'dang-chon': boLoc === 'tat-ca' }"
          :aria-pressed="boLoc === 'tat-ca'"
          @click="doiBoLoc('tat-ca')"
        >
          Tất cả
          <!-- Số đếm nhỏ bên cạnh nhãn, lấy từ computed tổng số công việc. -->
          <span class="so-dem">{{ tongSoCongViec }}</span>
        </button>

        <!-- Nút lọc "Chưa hoàn thành": chỉ hiển thị việc có hoanThanh === false. -->
        <button
          type="button"
          class="nut-loc"
          :class="{ 'dang-chon': boLoc === 'chua-hoan-thanh' }"
          :aria-pressed="boLoc === 'chua-hoan-thanh'"
          @click="doiBoLoc('chua-hoan-thanh')"
        >
          Chưa hoàn thành
          <!-- Số đếm lấy từ computed "soViecChuaHoanThanh" (yêu cầu 8). -->
          <span class="so-dem">{{ soViecChuaHoanThanh }}</span>
        </button>

        <!-- Nút lọc "Đã hoàn thành": chỉ hiển thị việc có hoanThanh === true. -->
        <button
          type="button"
          class="nut-loc"
          :class="{ 'dang-chon': boLoc === 'da-hoan-thanh' }"
          :aria-pressed="boLoc === 'da-hoan-thanh'"
          @click="doiBoLoc('da-hoan-thanh')"
        >
          Đã hoàn thành
          <!-- Số đếm lấy từ computed "soViecDaHoanThanh". -->
          <span class="so-dem">{{ soViecDaHoanThanh }}</span>
        </button>
      </div>

      <!--
        Nút "Xóa các việc đã hoàn thành" (yêu cầu 9):
        - type="button" : không gửi form.
        - :disabled="coViecDaHoanThanh === false" : khóa nút khi không có việc nào
          đã hoàn thành, tránh thao tác vô nghĩa.
        - :title : giải thích lý do nút bị khóa cho người dùng.
        - @click="xoaCacViecDaHoanThanh" : gọi method lọc bỏ các việc đã xong.
      -->
      <button
        type="button"
        class="nut-xoa-hang-loat"
        :disabled="coViecDaHoanThanh === false"
        :title="coViecDaHoanThanh
          ? 'Xóa toàn bộ công việc đã hoàn thành'
          : 'Chưa có công việc nào đã hoàn thành để xóa'"
        @click="xoaCacViecDaHoanThanh"
      >
        Xóa các việc đã hoàn thành
      </button>
    </section>

    <!-- ==================== DANH SÁCH CÔNG VIỆC ==================== -->
    <!-- Khu vực danh sách, tiêu đề liên kết với section qua aria-labelledby. -->
    <section class="khu-danh-sach" aria-labelledby="tieu-de-danh-sach">

      <!-- Phần đầu của danh sách: tiêu đề + mô tả bộ lọc hiện tại. -->
      <div class="dau-danh-sach">
        <!-- Tiêu đề của khu vực danh sách. -->
        <h2 id="tieu-de-danh-sach" class="tieu-de-danh-sach">
          Danh sách công việc
        </h2>

        <!--
          Mô tả động cho biết đang hiển thị bao nhiêu công việc và theo bộ lọc nào.
          aria-live="polite" giúp trình đọc màn hình cập nhật khi bộ lọc thay đổi.
        -->
        <p class="mo-ta-loc" aria-live="polite">{{ moTaBoLoc }}</p>
      </div>

      <!--
        YÊU CẦU 10 - TRƯỜNG HỢP 1: DANH SÁCH HOÀN TOÀN RỖNG.
        - v-if kiểm tra tổng số công việc bằng 0.
        - Đây là thông báo dành cho người dùng chưa có công việc nào,
          khác với thông báo "kết quả lọc rỗng" ở khối bên dưới.
      -->
      <div v-if="tongSoCongViec === 0" class="trang-thai-rong">
        <!-- Biểu tượng trang trí, ẩn khỏi trình đọc màn hình vì chỉ mang tính minh họa. -->
        <span class="bieu-tuong" aria-hidden="true">📝</span>
        <!-- Tiêu đề thông báo danh sách rỗng. -->
        <p class="tieu-de-rong">Danh sách công việc đang trống</p>
        <!-- Hướng dẫn người dùng cách bắt đầu. -->
        <p class="mo-ta-rong">
          Hãy nhập tên công việc ở khung phía trên rồi bấm "Thêm" hoặc nhấn Enter
          để tạo công việc đầu tiên.
        </p>
      </div>

      <!--
        YÊU CẦU 10 - TRƯỜNG HỢP 2: DANH SÁCH CÓ VIỆC NHƯNG KẾT QUẢ LỌC RỖNG.
        - v-else-if chỉ chạy khi tổng số công việc > 0 (đã qua khối v-if ở trên)
          nhưng mảng sau khi lọc không còn phần tử nào.
        - Tiêu đề và mô tả thay đổi theo bộ lọc đang chọn (computed bên dưới).
      -->
      <div v-else-if="danhSachDaLoc.length === 0" class="trang-thai-rong">
        <!-- Biểu tượng kính lúp gợi ý đây là kết quả của việc lọc. -->
        <span class="bieu-tuong" aria-hidden="true">🔍</span>
        <!-- Tiêu đề phù hợp với từng bộ lọc (chưa hoàn thành / đã hoàn thành). -->
        <p class="tieu-de-rong">{{ tieuDeRongTheoLoc }}</p>
        <!-- Mô tả gợi ý hành động tiếp theo. -->
        <p class="mo-ta-rong">{{ moTaRongTheoLoc }}</p>
      </div>

      <!--
        TRƯỜNG HỢP CÓ KẾT QUẢ: hiển thị danh sách (yêu cầu 4).
        - v-else : chỉ render khi cả hai điều kiện rỗng ở trên đều sai.
        - v-for="congViec in danhSachDaLoc" : lặp qua danh sách ĐÃ LỌC,
          nên khi đổi bộ lọc, danh sách tự cập nhật mà không cần viết lại HTML.
        - :key="congViec.id" : dùng id duy nhất làm khóa, đúng yêu cầu của đề.
          Khóa giúp Vue tái sử dụng DOM chính xác khi thêm/xóa/sắp xếp.
      -->
      <ul v-else class="danh-sach">
        <li
          v-for="congViec in danhSachDaLoc"
          :key="congViec.id"
          class="dong-cong-viec"
          :class="{ 'viec-da-xong': congViec.hoanThanh }"
        >
          <!--
            Nhãn bao quanh checkbox và tên công việc:
            - Bấm vào BẤT KỲ chỗ nào trong nhãn (kể cả chữ) cũng đổi được trạng thái,
              giúp vùng bấm lớn và dễ dùng trên điện thoại.
          -->
          <label class="nhan-kiem-tra">

            <!--
              Checkbox đánh dấu hoàn thành (yêu cầu 5):
              - v-model="congViec.hoanThanh" : ràng buộc hai chiều trực tiếp với
                thuộc tính "hoanThanh" của từng object trong mảng.
                Tick -> true, bỏ tick -> false; Vue tự cập nhật giao diện và computed.
              - :aria-label : đọc rõ tên công việc cho trình đọc màn hình,
                vì checkbox không có chữ mô tả riêng bên trong.
            -->
            <input
              v-model="congViec.hoanThanh"
              class="o-kiem-tra"
              type="checkbox"
              :aria-label="'Đánh dấu hoàn thành cho công việc: ' + congViec.ten"
            />

            <!--
              Tên công việc:
              - :class="{ 'gach-ngang': congViec.hoanThanh }" : khi đã hoàn thành,
                thêm class gạch ngang (text-decoration: line-through) đúng yêu cầu 5.
            -->
            <span
              class="ten-cong-viec"
              :class="{ 'gach-ngang': congViec.hoanThanh }"
            >
              {{ congViec.ten }}
            </span>
          </label>

          <!--
            Nhãn trạng thái bằng chữ, giúp người dùng nhận biết nhanh mà không
            phải chỉ dựa vào gạch ngang hay màu sắc.
            - :class chọn màu xanh (đã xong) hoặc màu cam (chưa xong).
          -->
          <span
            class="nhan-trang-thai"
            :class="congViec.hoanThanh ? 'trang-thai-da-xong' : 'trang-thai-chua-xong'"
          >
            {{ congViec.hoanThanh ? 'Đã hoàn thành' : 'Chưa hoàn thành' }}
          </span>

          <!--
            Nút xóa từng công việc (yêu cầu 6):
            - type="button" : không gửi form (quan trọng vì đây là hành động xóa).
            - @click="xoaCongViec(congViec.id)" : truyền id của đúng công việc
              để method tìm và xóa chính xác phần tử đó.
            - :aria-label và :title : mô tả rõ sẽ xóa công việc nào.
          -->
          <button
            type="button"
            class="nut-xoa"
            :aria-label="'Xóa công việc: ' + congViec.ten"
            :title="'Xóa công việc: ' + congViec.ten"
            @click="xoaCongViec(congViec.id)"
          >
            Xóa
          </button>
        </li>
      </ul>
    </section>

    <!-- ==================== CHÂN TRANG ==================== -->
    <footer class="chan-trang">
      <!-- Ghi chú ngắn khẳng định ứng dụng xử lý ngay trên trang, không tải lại. -->
      <p>Bài 3 · Vue 3 + Vite — Thêm, xóa, đổi trạng thái và lọc công việc không cần tải lại trang.</p>
    </footer>

  </div>
</template>

<script>
/*
  ============================================================================
  CÁC HẰNG SỐ DÙNG CHUNG
  ----------------------------------------------------------------------------
  Gom các giá trị "phép thuật" (magic value) vào MỘT nơi để tránh gõ sai
  và giúp việc bảo trì dễ dàng. Khi cần đổi mã bộ lọc, chỉ sửa ở đây.
  - Object.freeze(...) : "đóng băng" object/array, không cho phép vô tình sửa.
  ============================================================================
*/

// Ba trạng thái lọc của đề bài, lưu dưới dạng MÃ (không lưu câu chữ hiển thị).
// Việc tách mã khỏi nhãn giúp so sánh chính xác và không phụ thuộc vào câu chữ.
const BO_LOC = Object.freeze({
  // Hiển thị toàn bộ công việc.
  TAT_CA: 'tat-ca',
  // Chỉ hiển thị công việc chưa hoàn thành.
  CHUA_HOAN_THANH: 'chua-hoan-thanh',
  // Chỉ hiển thị công việc đã hoàn thành.
  DA_HOAN_THANH: 'da-hoan-thanh'
})

// Mảng các mã bộ lọc hợp lệ, dùng để kiểm tra method "doiBoLoc"
// không bị gọi với một giá trị lạ (phòng vệ dữ liệu).
const CAC_BO_LOC_HOP_LE = Object.freeze([
  // Bộ lọc tất cả.
  BO_LOC.TAT_CA,
  // Bộ lọc chưa hoàn thành.
  BO_LOC.CHUA_HOAN_THANH,
  // Bộ lọc đã hoàn thành.
  BO_LOC.DA_HOAN_THANH
])

/*
  Dữ liệu mẫu ban đầu gồm 3 công việc để người dùng thấy ngay cách hoạt động.
  - Mỗi object có ĐÚNG 3 thuộc tính theo yêu cầu 1:
      + id        : số nguyên duy nhất, dùng làm :key trong v-for.
      + ten       : tên công việc.
      + hoanThanh : true (đã xong) hoặc false (chưa xong).
  - Mảng và từng object đều được Object.freeze để tránh sửa nhầm hằng số.
  - Khi khởi tạo data, ta SAO CHÉP từng object (xem "taoDuLieuBanDau"),
    nên dữ liệu trong component vẫn thay đổi được bình thường.
*/
const CONG_VIEC_MAU = Object.freeze([
  // Công việc mẫu 1: đã hoàn thành.
  Object.freeze({
    id: 1,
    ten: 'Đọc lại kiến thức Vue 3 cơ bản',
    hoanThanh: true
  }),
  // Công việc mẫu 2: chưa hoàn thành.
  Object.freeze({
    id: 2,
    ten: 'Xây dựng giao diện danh sách công việc',
    hoanThanh: false
  }),
  // Công việc mẫu 3: chưa hoàn thành.
  Object.freeze({
    id: 3,
    ten: 'Kiểm thử thêm, xóa, đổi trạng thái và lọc',
    hoanThanh: false
  })
])

/*
  Hàm tạo dữ liệu ban đầu cho component.
  - Trả về một OBJECT MỚI mỗi lần gọi.
  - Nhờ vậy nếu sau này có nút "đặt lại" hoặc component được tạo lại,
    dữ liệu mẫu luôn sạch và không bị dính các thay đổi của lần trước.
  - Dùng map() để sao chép từng công việc thành object mới (không dùng chung
    tham chiếu với hằng số CONG_VIEC_MAU đã bị đóng băng).
*/
function taoDuLieuBanDau() {
  // Trả về object chứa toàn bộ state ban đầu.
  return {
    // Sao chép từng công việc mẫu thành object mới có thể thay đổi được.
    danhSachCongViec: CONG_VIEC_MAU.map(function (congViec) {
      // Tạo object mới với đúng 3 thuộc tính của yêu cầu 1.
      return {
        // Giữ nguyên id mẫu.
        id: congViec.id,
        // Giữ nguyên tên mẫu.
        ten: congViec.ten,
        // Giữ nguyên trạng thái mẫu.
        hoanThanh: congViec.hoanThanh
      }
    }),

    // Nội dung ô nhập tên công việc, ban đầu để trống.
    tenMoi: '',

    // Cờ cho biết người dùng đã bấm "Thêm" ít nhất một lần hay chưa.
    // Dùng để chỉ hiện lỗi SAU khi người dùng thực sự thử thêm, tránh việc
    // vừa mở trang đã thấy một dòng lỗi đỏ dù chưa làm gì.
    daThuThem: false,

    // Bộ lọc đang áp dụng, mặc định là hiển thị tất cả công việc.
    boLoc: BO_LOC.TAT_CA
  }
}

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
  name: 'Bai3QuanLyCongViec',

  /*
    ------------------------------------------------------------------------
    data(): hàm trả về một OBJECT chứa TOÀN BỘ dữ liệu (state) của component.
    Mọi thuộc tính khai báo ở đây đều có tính PHẢN ỨNG (reactive):
    khi giá trị thay đổi, Vue tự động cập nhật lại giao diện liên quan.
    ------------------------------------------------------------------------
  */
  data() {
    // Gọi hàm tạo dữ liệu ban đầu để có một bản sao sạch, độc lập.
    return taoDuLieuBanDau()
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
      TÊN MỚI SAU KHI BỎ KHOẢNG TRẮNG Ở HAI ĐẦU.
      - trim() cắt mọi khoảng trắng ở đầu và cuối chuỗi (dấu cách, tab, xuống dòng...).
      - Kết quả này được dùng để kiểm tra hợp lệ và để lưu vào danh sách,
        nhờ vậy tên công việc không bao giờ có khoảng trắng thừa ở hai đầu.
    */
    tenMoiDaTrim() {
      // Trả về chuỗi đã được cắt khoảng trắng hai đầu.
      return this.tenMoi.trim()
    },

    /*
      TÊN MỚI CÓ HỢP LỆ KHÔNG.
      - Quy tắc của yêu cầu 3: sau khi trim, chuỗi phải KHÁC RỖNG.
      - Chuỗi chỉ chứa khoảng trắng sẽ trim thành '' -> không hợp lệ.
      - Trả về true/false.
    */
    tenMoiHopLe() {
      // So sánh chuỗi đã trim với chuỗi rỗng.
      return this.tenMoiDaTrim !== ''
    },

    /*
      THÔNG BÁO LỖI CỦA Ô NHẬP TÊN.
      - Trả về chuỗi rỗng '' nếu không có lỗi (để template ẩn dòng lỗi).
      - Chỉ trả về câu thông báo khi:
          + Người dùng đã thử thêm (daThuThem === true), VÀ
          + Tên hiện tại không hợp lệ.
      - Nhờ vậy lúc mới mở trang, ô nhập trống nhưng không hiện lỗi.
    */
    loiNhap() {
      // Nếu chưa từng bấm "Thêm"/nhấn Enter thì chưa cần hiện lỗi.
      if (this.daThuThem === false) {
        // Không có lỗi để hiển thị.
        return ''
      }

      // Nếu tên không hợp lệ (rỗng hoặc chỉ toàn khoảng trắng) -> trả về câu nhắc.
      if (this.tenMoiHopLe === false) {
        // Câu thông báo hiển thị bên dưới ô nhập.
        return 'Vui lòng nhập tên công việc (không được để trống hoặc chỉ chứa khoảng trắng).'
      }

      // Tên hợp lệ -> không có lỗi.
      return ''
    },

    /*
      TỔNG SỐ CÔNG VIỆC (YÊU CẦU 8 - computed bắt buộc).
      - Bằng độ dài của mảng danh sách, tự cập nhật khi thêm hoặc xóa.
    */
    tongSoCongViec() {
      // Trả về số phần tử hiện có trong mảng.
      return this.danhSachCongViec.length
    },

    /*
      SỐ CÔNG VIỆC CHƯA HOÀN THÀNH (YÊU CẦU 8 - computed bắt buộc).
      - Đếm các công việc có hoanThanh === false.
      - Dùng filter() để tạo mảng tạm chỉ gồm việc chưa xong, rồi lấy length.
      - Tự cập nhật khi tick/bỏ tick checkbox hoặc thêm/xóa công việc.
    */
    soViecChuaHoanThanh() {
      // Lọc các công việc chưa hoàn thành.
      return this.danhSachCongViec.filter(function (congViec) {
        // Chỉ giữ lại công việc có trạng thái false.
        return congViec.hoanThanh === false
      }).length
    },

    /*
      SỐ CÔNG VIỆC ĐÃ HOÀN THÀNH.
      - Không bắt buộc bởi yêu cầu 8 nhưng cần cho:
          + Số đếm trên nút lọc "Đã hoàn thành".
          + Việc bật/tắt nút "Xóa các việc đã hoàn thành" (yêu cầu 9).
    */
    soViecDaHoanThanh() {
      // Lọc các công việc đã hoàn thành.
      return this.danhSachCongViec.filter(function (congViec) {
        // Chỉ giữ lại công việc có trạng thái true.
        return congViec.hoanThanh === true
      }).length
    },

    /*
      CÓ CÔNG VIỆC NÀO ĐÃ HOÀN THÀNH KHÔNG.
      - Trả về true nếu số việc đã xong lớn hơn 0.
      - Dùng để bật/tắt (disabled) nút "Xóa các việc đã hoàn thành".
    */
    coViecDaHoanThanh() {
      // So sánh số việc đã hoàn thành với 0.
      return this.soViecDaHoanThanh > 0
    },

    /*
      DANH SÁCH CÔNG VIỆC SAU KHI LỌC (YÊU CẦU 7 + 4).
      - Dựa vào biến "boLoc" để trả về mảng phù hợp.
      - Khi "boLoc" đổi, computed tự tính lại -> v-for tự cập nhật giao diện.
      - Lưu ý: filter() trả về MẢNG MỚI, không làm thay đổi mảng gốc.
    */
    danhSachDaLoc() {
      // Trường hợp lọc "Chưa hoàn thành".
      if (this.boLoc === BO_LOC.CHUA_HOAN_THANH) {
        // Chỉ giữ các công việc có hoanThanh === false.
        return this.danhSachCongViec.filter(function (congViec) {
          // Điều kiện giữ lại: chưa hoàn thành.
          return congViec.hoanThanh === false
        })
      }

      // Trường hợp lọc "Đã hoàn thành".
      if (this.boLoc === BO_LOC.DA_HOAN_THANH) {
        // Chỉ giữ các công việc có hoanThanh === true.
        return this.danhSachCongViec.filter(function (congViec) {
          // Điều kiện giữ lại: đã hoàn thành.
          return congViec.hoanThanh === true
        })
      }

      // Mặc định (và cũng là trường hợp "Tất cả"): trả về toàn bộ mảng gốc.
      // Việc trả về chính mảng gốc là an toàn vì template chỉ đọc, không sửa trực tiếp.
      return this.danhSachCongViec
    },

    /*
      MÔ TẢ NGẮN VỀ BỘ LỌC ĐANG ÁP DỤNG.
      - Hiển thị bên cạnh tiêu đề danh sách, ví dụ:
        "Đang hiển thị 2/3 công việc chưa hoàn thành."
      - Giúp người dùng hiểu vì sao danh sách chỉ có vài dòng.
    */
    moTaBoLoc() {
      // Số công việc đang thực sự hiển thị sau khi lọc.
      const soDangHienThi = this.danhSachDaLoc.length

      // Nếu danh sách hoàn toàn trống, dùng câu mô tả riêng.
      if (this.tongSoCongViec === 0) {
        // Thông báo chưa có dữ liệu.
        return 'Chưa có công việc nào trong danh sách.'
      }

      // Trường hợp đang lọc việc chưa hoàn thành.
      if (this.boLoc === BO_LOC.CHUA_HOAN_THANH) {
        // Nêu rõ số đang hiển thị trên tổng số.
        return `Đang hiển thị ${soDangHienThi}/${this.tongSoCongViec} công việc chưa hoàn thành.`
      }

      // Trường hợp đang lọc việc đã hoàn thành.
      if (this.boLoc === BO_LOC.DA_HOAN_THANH) {
        // Nêu rõ số đang hiển thị trên tổng số.
        return `Đang hiển thị ${soDangHienThi}/${this.tongSoCongViec} công việc đã hoàn thành.`
      }

      // Trường hợp "Tất cả".
      return `Đang hiển thị tất cả ${soDangHienThi} công việc.`
    },

    /*
      TIÊU ĐỀ CỦA KHỐI THÔNG BÁO KHI KẾT QUẢ LỌC RỖNG (YÊU CẦU 10).
      - Chỉ dùng khi danh sách gốc CÓ công việc nhưng bộ lọc hiện tại không có kết quả.
      - Câu chữ thay đổi theo bộ lọc để thông báo luôn phù hợp ngữ cảnh.
    */
    tieuDeRongTheoLoc() {
      // Khi lọc "Chưa hoàn thành" mà không còn việc nào -> nghĩa là đã xong hết.
      if (this.boLoc === BO_LOC.CHUA_HOAN_THANH) {
        // Câu khen ngợi, phù hợp trạng thái hoàn thành mọi việc.
        return 'Không còn công việc nào chưa hoàn thành'
      }

      // Khi lọc "Đã hoàn thành" mà chưa có việc nào được đánh dấu.
      if (this.boLoc === BO_LOC.DA_HOAN_THANH) {
        // Giải thích đúng lý do danh sách trống.
        return 'Chưa có công việc nào được hoàn thành'
      }

      // Phương án dự phòng cho mọi trường hợp bất thường.
      return 'Không có công việc nào phù hợp'
    },

    /*
      MÔ TẢ / GỢI Ý HÀNH ĐỘNG TIẾP THEO KHI KẾT QUẢ LỌC RỖNG (YÊU CẦU 10).
      - Giúp người dùng biết nên làm gì thay vì chỉ thấy một khung trống.
    */
    moTaRongTheoLoc() {
      // Trường hợp đã hoàn thành hết công việc.
      if (this.boLoc === BO_LOC.CHUA_HOAN_THANH) {
        // Gợi ý thêm việc mới hoặc xem bộ lọc khác.
        return 'Tuyệt vời! Bạn đã hoàn thành mọi công việc. Hãy thêm việc mới hoặc chuyển sang bộ lọc khác.'
      }

      // Trường hợp chưa có công việc nào được đánh dấu hoàn thành.
      if (this.boLoc === BO_LOC.DA_HOAN_THANH) {
        // Gợi ý tick checkbox để đánh dấu hoàn thành.
        return 'Hãy đánh dấu hoàn thành một công việc bằng checkbox, hoặc chuyển sang bộ lọc khác.'
      }

      // Phương án dự phòng.
      return 'Hãy thử chọn bộ lọc khác hoặc thêm công việc mới.'
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
      TẠO ID MỚI DUY NHẤT CHO CÔNG VIỆC SẮP THÊM.
      - Tìm id lớn nhất hiện có trong danh sách rồi cộng thêm 1.
      - Cách này an toàn kể cả khi người dùng đã xóa nhiều công việc:
        id không bao giờ trùng với một công việc còn lại.
      - Không dùng Date.now() vì hai lần thêm trong cùng một mili-giây
        có thể tạo ra id trùng nhau.
    */
    taoIdMoi() {
      // Khởi tạo id lớn nhất là 0 (mọi id hợp lệ đều là số dương).
      let idLonNhat = 0

      // Duyệt qua từng công việc hiện có để tìm id lớn nhất.
      this.danhSachCongViec.forEach(function (congViec) {
        // Chuyển id về số để so sánh an toàn (phòng khi id là chuỗi số).
        const id = Number(congViec.id)

        // Chỉ xét các id hữu hạn (bỏ qua NaN, Infinity) và lớn hơn mốc hiện tại.
        if (Number.isFinite(id) === true && id > idLonNhat) {
          // Cập nhật mốc id lớn nhất.
          idLonNhat = id
        }
      })

      // Lấy phần nguyên của id lớn nhất rồi cộng 1 để được id nguyên, duy nhất.
      return Math.floor(idLonNhat) + 1
    },

    /*
      ĐƯA CON TRỎ VỀ Ô NHẬP TÊN.
      - Dùng this.$refs.oNhapTen (tham chiếu tới input có ref="oNhapTen").
      - Luôn kiểm tra phần tử tồn tại trước khi gọi focus() để tránh lỗi
        nếu component đã bị gỡ khỏi DOM.
    */
    focusONhapTen() {
      // Lấy phần tử ô nhập tên từ refs.
      const oNhap = this.$refs.oNhapTen

      // Nếu tìm thấy phần tử thì đưa con trỏ vào đó.
      if (oNhap) {
        // Gọi focus() để người dùng gõ tiếp ngay, không cần bấm chuột.
        oNhap.focus()
      }
    },

    /*
      XỬ LÝ KHI NGƯỜI DÙNG GÕ TRONG Ô NHẬP TÊN (sự kiện @input).
      - Nếu trước đó đang hiện lỗi mà bây giờ nội dung đã hợp lệ,
        tắt cờ "đã thử thêm" để dòng lỗi biến mất ngay.
      - Nếu người dùng vẫn chỉ gõ khoảng trắng, lỗi vẫn được giữ lại
        (vì tenMoi.trim() vẫn rỗng) -> phản hồi nhất quán.
    */
    xuLyKhiNhap() {
      // Chỉ xóa lỗi khi nội dung hiện tại thực sự hợp lệ.
      if (this.tenMoiHopLe === true && this.daThuThem === true) {
        // Hạ cờ để computed "loiNhap" trả về chuỗi rỗng.
        this.daThuThem = false
      }
    },

    /*
      THÊM CÔNG VIỆC MỚI (YÊU CẦU 2 + 3).
      - Được gọi từ @submit.prevent của form, nên chạy được cho cả hai cách:
          + Bấm nút "Thêm" (type="submit").
          + Nhấn Enter trong ô nhập (trình duyệt tự gửi form).
      - Các bước:
          1. Đánh dấu đã thử thêm (để hiện lỗi nếu cần).
          2. Lấy tên đã trim; nếu rỗng thì DỪNG, không thêm.
          3. Tạo object công việc mới với id duy nhất và hoanThanh = false.
          4. Thêm vào ĐẦU danh sách để người dùng thấy ngay công việc vừa tạo.
          5. Xóa ô nhập, tắt cờ lỗi, đưa con trỏ về ô nhập.
    */
    themCongViec() {
      // Bước 1: đánh dấu người dùng đã thực sự thử thêm công việc.
      this.daThuThem = true

      // Bước 2: lấy tên đã bỏ khoảng trắng hai đầu.
      const tenDaLamSach = this.tenMoiDaTrim

      // Nếu tên rỗng hoặc chỉ chứa khoảng trắng -> không thêm (yêu cầu 3).
      if (tenDaLamSach === '') {
        // Đưa con trỏ về ô nhập để người dùng sửa ngay.
        this.$nextTick(this.focusONhapTen)
        // Kết thúc method, không tạo công việc.
        return
      }

      // Bước 3: tạo object công việc mới đúng cấu trúc yêu cầu 1.
      const congViecMoi = {
        // id duy nhất, sinh từ id lớn nhất hiện có + 1.
        id: this.taoIdMoi(),
        // Tên đã trim, không còn khoảng trắng thừa ở hai đầu.
        ten: tenDaLamSach,
        // Công việc mới luôn bắt đầu ở trạng thái CHƯA hoàn thành.
        hoanThanh: false
      }

      // Bước 4: thêm công việc mới vào ĐẦU mảng để hiển thị ở đầu danh sách.
      this.danhSachCongViec.unshift(congViecMoi)

      // Bước 5a: xóa nội dung ô nhập để sẵn sàng cho công việc kế tiếp.
      this.tenMoi = ''

      // Bước 5b: tắt cờ lỗi vì lần thêm này đã thành công.
      this.daThuThem = false

      // Nếu đang lọc "Đã hoàn thành", công việc mới (chưa xong) sẽ bị ẩn đi.
      // Chuyển về "Tất cả" để người dùng nhìn thấy ngay công việc vừa thêm.
      if (this.boLoc === BO_LOC.DA_HOAN_THANH) {
        // Đổi bộ lọc về trạng thái hiển thị mọi công việc.
        this.boLoc = BO_LOC.TAT_CA
      }

      // Bước 5c: sau khi Vue cập nhật DOM, đưa con trỏ về ô nhập.
      this.$nextTick(this.focusONhapTen)
    },

    /*
      ĐỔI BỘ LỌC (YÊU CẦU 7).
      - Chỉ chấp nhận ba mã bộ lọc đã định nghĩa.
      - Nếu nhận giá trị lạ (ví dụ do gọi sai từ code), bỏ qua để bảo vệ state.
    */
    doiBoLoc(boLocMoi) {
      // Kiểm tra giá trị mới có nằm trong danh sách hợp lệ hay không.
      if (CAC_BO_LOC_HOP_LE.includes(boLocMoi) === false) {
        // Giá trị không hợp lệ -> không thay đổi gì.
        return
      }

      // Gán bộ lọc mới; computed "danhSachDaLoc" sẽ tự tính lại.
      this.boLoc = boLocMoi
    },

    /*
      XÓA MỘT CÔNG VIỆC THEO ID (YÊU CẦU 6).
      - Tìm vị trí của công việc trong mảng bằng findIndex().
      - Nếu không tìm thấy (vị trí -1) thì dừng, tránh xóa nhầm phần tử khác.
      - Dùng splice(vị trí, 1) để xóa đúng MỘT phần tử.
    */
    xoaCongViec(idCanXoa) {
      // Tìm chỉ số của công việc có id trùng với id được truyền vào.
      const viTri = this.danhSachCongViec.findIndex(function (congViec) {
        // So sánh id của từng công việc với id cần xóa.
        return congViec.id === idCanXoa
      })

      // Nếu không tìm thấy công việc thì kết thúc sớm.
      if (viTri === -1) {
        // Không có gì để xóa.
        return
      }

      // Xóa đúng một phần tử tại vị trí tìm được.
      this.danhSachCongViec.splice(viTri, 1)
    },

    /*
      XÓA TOÀN BỘ CÔNG VIỆC ĐÃ HOÀN THÀNH (YÊU CẦU 9).
      - Nếu không có việc nào đã xong thì dừng (nút thực tế đã bị disabled,
        nhưng vẫn kiểm tra để method an toàn khi được gọi từ nơi khác).
      - Tạo mảng mới chỉ gồm các việc CHƯA hoàn thành rồi gán lại.
        Cách này giữ nguyên thứ tự các việc còn lại và không sửa mảng gốc
        trong lúc đang duyệt (tránh lỗi bỏ sót phần tử).
    */
    xoaCacViecDaHoanThanh() {
      // Nếu không có công việc nào đã hoàn thành thì không làm gì.
      if (this.coViecDaHoanThanh === false) {
        // Kết thúc sớm.
        return
      }

      // Giữ lại những công việc CHƯA hoàn thành, loại bỏ các việc đã xong.
      this.danhSachCongViec = this.danhSachCongViec.filter(function (congViec) {
        // Điều kiện giữ lại: hoanThanh phải là false.
        return congViec.hoanThanh === false
      })
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
.trang-cong-viec {
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

/* Dòng nhãn nhỏ phía trên tiêu đề. */
.nhan-nho {
  /* Bỏ khoảng lề mặc định. */
  margin: 0 0 4px 0;
  /* Cỡ chữ nhỏ. */
  font-size: 12px;
  /* Chữ đậm. */
  font-weight: 700;
  /* Chữ in hoa nhẹ để tạo điểm nhấn. */
  letter-spacing: 1px;
  /* Chữ in hoa. */
  text-transform: uppercase;
  /* Màu xanh. */
  color: #2563eb;
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
  max-width: 600px;
  /* Màu xám vừa. */
  color: #4b5563;
  /* Cỡ chữ nhỏ hơn tiêu đề. */
  font-size: 15px;
}

/* ---------------------------------------------------------------------------
   2. KHU VỰC THÊM CÔNG VIỆC
   --------------------------------------------------------------------------- */

/* Khung biểu mẫu thêm công việc. */
.bieu-mau-them {
  /* Nền xám rất nhạt để tách khỏi phần còn lại. */
  background-color: #f8fafc;
  /* Viền xám nhạt. */
  border: 1px solid #e2e8f0;
  /* Bo tròn. */
  border-radius: 12px;
  /* Khoảng đệm trong. */
  padding: 16px;
  /* Khoảng cách phía dưới. */
  margin-bottom: 18px;
}

/* Nhãn của ô nhập tên. */
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

/* Cụm gồm ô nhập và nút "Thêm". */
.cum-nhap {
  /* Dùng flex để xếp ô nhập và nút nằm ngang. */
  display: flex;
  /* Khoảng cách giữa ô nhập và nút. */
  gap: 10px;
}

/* Ô nhập tên công việc. */
.o-nhap {
  /* Chiếm hết khoảng trống còn lại, đẩy nút sang phải. */
  flex: 1;
  /* Chiều rộng tối thiểu để ô không bị co quá nhỏ. */
  min-width: 0;
  /* Khoảng đệm trong ô. */
  padding: 11px 13px;
  /* Viền xám nhạt. */
  border: 1px solid #cbd5e1;
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

/* Nút "Thêm". */
.nut-them {
  /* Không co lại khi màn hình hẹp. */
  flex-shrink: 0;
  /* Khoảng đệm trong nút. */
  padding: 11px 24px;
  /* Bỏ viền mặc định. */
  border: none;
  /* Bo tròn. */
  border-radius: 8px;
  /* Nền xanh đậm. */
  background-color: #2563eb;
  /* Chữ trắng. */
  color: #ffffff;
  /* Cỡ chữ vừa. */
  font-size: 15px;
  /* Chữ đậm. */
  font-weight: 700;
  /* Con trỏ bàn tay. */
  cursor: pointer;
  /* Chuyển màu mượt. */
  transition: background-color 0.2s ease, transform 0.1s ease;
}

/* Hover nút "Thêm". */
.nut-them:hover {
  /* Nền xanh đậm hơn. */
  background-color: #1d4ed8;
}

/* Nhấn xuống tạo hiệu ứng nút lún nhẹ. */
.nut-them:active {
  /* Dịch xuống 1px. */
  transform: translateY(1px);
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

/* Phím Enter hiển thị dạng phím bấm nhỏ. */
.goi-y kbd {
  /* Khoảng đệm nhỏ. */
  padding: 1px 5px;
  /* Viền xám. */
  border: 1px solid #d1d5db;
  /* Bo tròn nhẹ. */
  border-radius: 4px;
  /* Nền trắng. */
  background-color: #ffffff;
  /* Cỡ chữ nhỏ hơn. */
  font-size: 12px;
  /* Phông chữ đơn cách để giống phím. */
  font-family: Consolas, 'Courier New', monospace;
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
   3. BẢNG THỐNG KÊ
   --------------------------------------------------------------------------- */

/* Khung chứa ba thẻ thống kê. */
.bang-thong-ke {
  /* Chia thành ba cột bằng nhau. */
  display: grid;
  /* Ba cột, mỗi cột rộng bằng nhau. */
  grid-template-columns: repeat(3, 1fr);
  /* Khoảng cách giữa các thẻ. */
  gap: 12px;
  /* Khoảng cách phía dưới. */
  margin-bottom: 18px;
}

/* Mỗi thẻ thống kê. */
.the-thong-ke {
  /* Dùng flex để xếp nhãn và số. */
  display: flex;
  /* Xếp nhãn phía trên, số ở dưới. */
  flex-direction: column;
  /* Khoảng cách nhỏ giữa nhãn và số. */
  gap: 2px;
  /* Khoảng đệm trong thẻ. */
  padding: 12px 16px;
  /* Viền xanh rất nhạt. */
  border: 1px solid #dbeafe;
  /* Bo tròn. */
  border-radius: 10px;
  /* Nền xanh rất nhạt. */
  background-color: #eff6ff;
}

/* Nhãn của thẻ thống kê. */
.nhan-thong-ke {
  /* Cỡ chữ nhỏ. */
  font-size: 12px;
  /* Chữ hơi đậm. */
  font-weight: 600;
  /* Màu xám xanh. */
  color: #475569;
  /* Chữ in hoa nhẹ. */
  letter-spacing: 0.3px;
  /* Chữ in hoa. */
  text-transform: uppercase;
}

/* Con số thống kê. */
.so-thong-ke {
  /* Cỡ chữ lớn. */
  font-size: 24px;
  /* Chữ rất đậm. */
  font-weight: 800;
  /* Màu xanh đậm. */
  color: #1d4ed8;
  /* Giãn dòng gọn. */
  line-height: 1.2;
}

/* Thẻ "Chưa hoàn thành": màu cam để phân biệt. */
.the-thong-ke.the-chua-xong {
  /* Viền cam nhạt. */
  border-color: #fde68a;
  /* Nền vàng rất nhạt. */
  background-color: #fffbeb;
}

/* Con số của thẻ "Chưa hoàn thành". */
.the-thong-ke.the-chua-xong .so-thong-ke {
  /* Màu cam đậm. */
  color: #b45309;
}

/* Thẻ "Đã hoàn thành": màu xanh lá để phân biệt. */
.the-thong-ke.the-da-xong {
  /* Viền xanh lá nhạt. */
  border-color: #bbf7d0;
  /* Nền xanh lá rất nhạt. */
  background-color: #f0fdf4;
}

/* Con số của thẻ "Đã hoàn thành". */
.the-thong-ke.the-da-xong .so-thong-ke {
  /* Màu xanh lá đậm. */
  color: #15803d;
}

/* ---------------------------------------------------------------------------
   4. BỘ LỌC VÀ NÚT XÓA HÀNG LOẠT
   --------------------------------------------------------------------------- */

/* Khu vực chứa bộ lọc bên trái và nút xóa bên phải. */
.khu-dieu-khien {
  /* Dùng flex để xếp hai khối nằm ngang. */
  display: flex;
  /* Đẩy hai khối ra hai đầu. */
  justify-content: space-between;
  /* Canh giữa theo chiều dọc. */
  align-items: center;
  /* Khoảng cách giữa hai khối. */
  gap: 12px;
  /* Cho phép xuống dòng trên màn hình hẹp. */
  flex-wrap: wrap;
  /* Khoảng cách phía dưới. */
  margin-bottom: 18px;
}

/* Nhóm ba nút lọc. */
.nhom-loc {
  /* Dùng flex để xếp ba nút nằm ngang. */
  display: flex;
  /* Khoảng cách giữa các nút. */
  gap: 6px;
  /* Cho phép xuống dòng khi không đủ chỗ. */
  flex-wrap: wrap;
}

/* Mỗi nút lọc. */
.nut-loc {
  /* Dùng flex để xếp nhãn và số đếm. */
  display: inline-flex;
  /* Canh giữa theo chiều dọc. */
  align-items: center;
  /* Khoảng cách giữa nhãn và số. */
  gap: 6px;
  /* Khoảng đệm trong nút. */
  padding: 8px 12px;
  /* Viền xám nhạt. */
  border: 1px solid #d1d5db;
  /* Bo tròn. */
  border-radius: 8px;
  /* Nền trắng. */
  background-color: #ffffff;
  /* Màu chữ xám đậm. */
  color: #374151;
  /* Cỡ chữ nhỏ vừa. */
  font-size: 14px;
  /* Chữ hơi đậm. */
  font-weight: 600;
  /* Con trỏ bàn tay. */
  cursor: pointer;
  /* Chuyển màu mượt. */
  transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}

/* Hover nút lọc chưa được chọn. */
.nut-loc:hover {
  /* Nền xám rất nhạt. */
  background-color: #f3f4f6;
}

/* Nút lọc đang được chọn (áp dụng qua :class). */
.nut-loc.dang-chon {
  /* Viền xanh. */
  border-color: #2563eb;
  /* Nền xanh đậm. */
  background-color: #2563eb;
  /* Chữ trắng. */
  color: #ffffff;
}

/* Số đếm nhỏ bên trong nút lọc. */
.so-dem {
  /* Khoảng đệm nhỏ. */
  padding: 1px 7px;
  /* Bo tròn hoàn toàn thành viên thuốc. */
  border-radius: 999px;
  /* Nền xám nhạt. */
  background-color: #e5e7eb;
  /* Màu chữ xám đậm. */
  color: #374151;
  /* Cỡ chữ nhỏ. */
  font-size: 12px;
  /* Chữ đậm. */
  font-weight: 700;
}

/* Số đếm khi nút lọc đang được chọn. */
.nut-loc.dang-chon .so-dem {
  /* Nền trắng mờ để nổi trên nền xanh. */
  background-color: rgba(255, 255, 255, 0.22);
  /* Chữ trắng. */
  color: #ffffff;
}

/* Nút "Xóa các việc đã hoàn thành". */
.nut-xoa-hang-loat {
  /* Khoảng đệm trong nút. */
  padding: 8px 14px;
  /* Viền đỏ nhạt. */
  border: 1px solid #fecaca;
  /* Bo tròn. */
  border-radius: 8px;
  /* Nền đỏ rất nhạt. */
  background-color: #fef2f2;
  /* Màu chữ đỏ. */
  color: #b91c1c;
  /* Cỡ chữ nhỏ vừa. */
  font-size: 14px;
  /* Chữ hơi đậm. */
  font-weight: 600;
  /* Con trỏ bàn tay. */
  cursor: pointer;
  /* Chuyển màu mượt. */
  transition: background-color 0.2s ease, opacity 0.2s ease;
}

/* Hover nút xóa hàng loạt khi đang bật. */
.nut-xoa-hang-loat:hover:not(:disabled) {
  /* Nền đỏ đậm hơn một chút. */
  background-color: #fee2e2;
}

/* Trạng thái nút xóa hàng loạt bị khóa. */
.nut-xoa-hang-loat:disabled {
  /* Giảm độ mờ để thấy rõ không bấm được. */
  opacity: 0.45;
  /* Con trỏ không cho bấm. */
  cursor: not-allowed;
}

/* ---------------------------------------------------------------------------
   5. DANH SÁCH CÔNG VIỆC
   --------------------------------------------------------------------------- */

/* Khung khu vực danh sách. */
.khu-danh-sach {
  /* Khoảng cách phía trên. */
  margin-top: 4px;
}

/* Phần đầu danh sách: tiêu đề và mô tả bộ lọc. */
.dau-danh-sach {
  /* Dùng flex để xếp tiêu đề và mô tả. */
  display: flex;
  /* Đẩy hai bên ra xa nhau. */
  justify-content: space-between;
  /* Canh giữa theo chiều dọc. */
  align-items: baseline;
  /* Khoảng cách giữa hai phần. */
  gap: 10px;
  /* Cho phép xuống dòng trên màn hình hẹp. */
  flex-wrap: wrap;
  /* Khoảng cách phía dưới. */
  margin-bottom: 10px;
}

/* Tiêu đề của danh sách. */
.tieu-de-danh-sach {
  /* Bỏ khoảng lề mặc định. */
  margin: 0;
  /* Cỡ chữ vừa. */
  font-size: 18px;
  /* Màu xanh đậm. */
  color: #1e3a8a;
  /* Chữ đậm. */
  font-weight: 700;
}

/* Mô tả bộ lọc đang áp dụng. */
.mo-ta-loc {
  /* Bỏ khoảng lề mặc định. */
  margin: 0;
  /* Cỡ chữ nhỏ. */
  font-size: 13px;
  /* Màu xám. */
  color: #6b7280;
}

/* Danh sách công việc: bỏ kiểu danh sách mặc định. */
.danh-sach {
  /* Bỏ dấu chấm đầu dòng. */
  list-style: none;
  /* Bỏ khoảng lề mặc định. */
  margin: 0;
  /* Bỏ khoảng đệm mặc định. */
  padding: 0;
  /* Dùng grid để tạo khoảng cách đều giữa các dòng. */
  display: grid;
  /* Khoảng cách giữa các công việc. */
  gap: 8px;
}

/* Mỗi dòng công việc. */
.dong-cong-viec {
  /* Dùng flex để xếp checkbox + tên, nhãn trạng thái và nút xóa. */
  display: flex;
  /* Canh giữa theo chiều dọc. */
  align-items: center;
  /* Khoảng cách giữa các phần tử. */
  gap: 12px;
  /* Khoảng đệm trong dòng. */
  padding: 11px 14px;
  /* Viền xám nhạt. */
  border: 1px solid #e5e7eb;
  /* Bo tròn. */
  border-radius: 10px;
  /* Nền trắng. */
  background-color: #ffffff;
  /* Chuyển màu mượt khi trạng thái thay đổi. */
  transition: background-color 0.2s ease, border-color 0.2s ease;
}

/* Dòng công việc đã hoàn thành. */
.dong-cong-viec.viec-da-xong {
  /* Nền xám rất nhạt để dòng đã xong trông "lùi về phía sau". */
  background-color: #f9fafb;
  /* Viền xám nhạt hơn. */
  border-color: #eef2f7;
}

/* Nhãn bao quanh checkbox và tên công việc (vùng bấm lớn). */
.nhan-kiem-tra {
  /* Chiếm hết khoảng trống còn lại. */
  flex: 1;
  /* Cho phép nội dung co lại để không đẩy các phần tử khác ra ngoài. */
  min-width: 0;
  /* Dùng flex để xếp checkbox và tên nằm ngang. */
  display: flex;
  /* Canh giữa theo chiều dọc. */
  align-items: flex-start;
  /* Khoảng cách giữa checkbox và tên. */
  gap: 10px;
  /* Con trỏ bàn tay vì bấm vào nhãn là đổi trạng thái. */
  cursor: pointer;
}

/* Checkbox đánh dấu hoàn thành. */
.o-kiem-tra {
  /* Kích thước lớn hơn mặc định cho dễ bấm. */
  width: 18px;
  height: 18px;
  /* Không co lại. */
  flex-shrink: 0;
  /* Căn theo dòng chữ đầu tiên. */
  margin-top: 2px;
  /* Con trỏ bàn tay. */
  cursor: pointer;
  /* Màu xanh cho ô đã chọn (trình duyệt hỗ trợ accent-color). */
  accent-color: #2563eb;
}

/* Tên công việc. */
.ten-cong-viec {
  /* Cho phép xuống dòng với tên rất dài. */
  overflow-wrap: anywhere;
  /* Màu chữ xám đậm. */
  color: #1f2937;
  /* Cỡ chữ vừa. */
  font-size: 15px;
  /* Chữ đậm vừa. */
  font-weight: 500;
  /* Giãn dòng dễ đọc. */
  line-height: 1.45;
  /* Chuyển màu mượt khi đổi trạng thái. */
  transition: color 0.2s ease;
}

/* Tên công việc đã hoàn thành: gạch ngang (yêu cầu 5). */
.ten-cong-viec.gach-ngang {
  /* Gạch ngang giữa chữ. */
  text-decoration: line-through;
  /* Độ dày nét gạch để dễ thấy. */
  text-decoration-thickness: 2px;
  /* Màu xám nhạt hơn để thể hiện đã xong. */
  color: #9ca3af;
}

/* Nhãn trạng thái bằng chữ. */
.nhan-trang-thai {
  /* Không xuống dòng giữa các từ. */
  white-space: nowrap;
  /* Khoảng đệm nhỏ. */
  padding: 3px 9px;
  /* Bo tròn hoàn toàn. */
  border-radius: 999px;
  /* Cỡ chữ nhỏ. */
  font-size: 12px;
  /* Chữ đậm. */
  font-weight: 700;
  /* Không co lại khi tên dài. */
  flex-shrink: 0;
}

/* Nhãn trạng thái "Đã hoàn thành". */
.trang-thai-da-xong {
  /* Nền xanh lá nhạt. */
  background-color: #dcfce7;
  /* Chữ xanh lá đậm. */
  color: #15803d;
}

/* Nhãn trạng thái "Chưa hoàn thành". */
.trang-thai-chua-xong {
  /* Nền vàng nhạt. */
  background-color: #fef3c7;
  /* Chữ cam đậm. */
  color: #b45309;
}

/* Nút xóa từng công việc. */
.nut-xoa {
  /* Không co lại khi tên dài. */
  flex-shrink: 0;
  /* Khoảng đệm trong nút. */
  padding: 6px 11px;
  /* Viền đỏ nhạt. */
  border: 1px solid #fecaca;
  /* Bo tròn. */
  border-radius: 7px;
  /* Nền đỏ rất nhạt. */
  background-color: #fef2f2;
  /* Màu chữ đỏ. */
  color: #b91c1c;
  /* Cỡ chữ nhỏ. */
  font-size: 13px;
  /* Chữ đậm. */
  font-weight: 600;
  /* Con trỏ bàn tay. */
  cursor: pointer;
  /* Chuyển màu mượt. */
  transition: background-color 0.2s ease, border-color 0.2s ease;
}

/* Hover nút xóa. */
.nut-xoa:hover {
  /* Nền đỏ đậm hơn. */
  background-color: #fee2e2;
  /* Viền đỏ đậm hơn. */
  border-color: #fca5a5;
}

/* ---------------------------------------------------------------------------
   6. THÔNG BÁO KHI RỖNG
   --------------------------------------------------------------------------- */

/* Khung thông báo khi danh sách hoặc kết quả lọc rỗng. */
.trang-thai-rong {
  /* Canh giữa nội dung. */
  text-align: center;
  /* Khoảng đệm rộng tạo cảm giác khung thông báo. */
  padding: 30px 20px;
  /* Viền đứt nét để phân biệt với danh sách thật. */
  border: 2px dashed #dbeafe;
  /* Bo tròn. */
  border-radius: 12px;
  /* Nền xanh rất nhạt. */
  background-color: #f8fbff;
}

/* Biểu tượng minh họa trong khung rỗng. */
.bieu-tuong {
  /* Hiển thị dạng khối để chiếm một dòng. */
  display: block;
  /* Cỡ chữ lớn. */
  font-size: 32px;
  /* Giãn dòng gọn. */
  line-height: 1.2;
  /* Khoảng cách phía dưới. */
  margin-bottom: 6px;
}

/* Tiêu đề của khung thông báo rỗng. */
.tieu-de-rong {
  /* Bỏ khoảng lề mặc định. */
  margin: 0 0 4px 0;
  /* Cỡ chữ vừa. */
  font-size: 16px;
  /* Màu xanh đậm. */
  color: #1e3a8a;
  /* Chữ đậm. */
  font-weight: 700;
}

/* Mô tả / gợi ý trong khung thông báo rỗng. */
.mo-ta-rong {
  /* Bỏ khoảng lề mặc định. */
  margin: 0 auto;
  /* Giới hạn chiều rộng để dòng chữ dễ đọc. */
  max-width: 460px;
  /* Cỡ chữ nhỏ vừa. */
  font-size: 14px;
  /* Màu xám. */
  color: #6b7280;
}

/* ---------------------------------------------------------------------------
   7. CHÂN TRANG
   --------------------------------------------------------------------------- */

/* Khối chân trang. */
.chan-trang {
  /* Khoảng cách phía trên. */
  margin-top: 24px;
  /* Đường kẻ mảnh phía trên. */
  border-top: 1px solid #e5e7eb;
  /* Khoảng đệm phía trên. */
  padding-top: 12px;
  /* Canh giữa. */
  text-align: center;
}

/* Dòng chữ chân trang. */
.chan-trang p {
  /* Bỏ khoảng lề mặc định. */
  margin: 0;
  /* Cỡ chữ nhỏ. */
  font-size: 12px;
  /* Màu xám. */
  color: #9ca3af;
}

/* ---------------------------------------------------------------------------
   8. HIỂN THỊ TRÊN MÀN HÌNH NHỎ (điện thoại)
   --------------------------------------------------------------------------- */

/* Áp dụng khi chiều rộng màn hình từ 560px trở xuống. */
@media (max-width: 560px) {
  /* Giảm khoảng đệm khung trang để có thêm chỗ cho nội dung. */
  .trang-cong-viec {
    /* Khoảng đệm nhỏ hơn. */
    padding: 18px;
  }

  /* Giảm cỡ tiêu đề chính. */
  .tieu-de {
    /* Cỡ chữ nhỏ hơn. */
    font-size: 23px;
  }

  /* Cụm nhập chuyển sang xếp dọc. */
  .cum-nhap {
    /* Xếp ô nhập và nút thành cột. */
    flex-direction: column;
  }

  /* Nút "Thêm" chiếm toàn bộ chiều rộng. */
  .nut-them {
    /* Chiều rộng 100%. */
    width: 100%;
  }

  /* Bảng thống kê chuyển thành một cột. */
  .bang-thong-ke {
    /* Mỗi thẻ chiếm một dòng. */
    grid-template-columns: 1fr;
  }

  /* Khu điều khiển chuyển sang xếp dọc. */
  .khu-dieu-khien {
    /* Xếp bộ lọc và nút xóa thành cột. */
    flex-direction: column;
    /* Canh trái. */
    align-items: stretch;
  }

  /* Nhóm lọc chiếm toàn bộ chiều rộng. */
  .nhom-loc {
    /* Chia đều ba nút trên một hàng nếu đủ chỗ. */
    display: grid;
    /* Ba cột bằng nhau. */
    grid-template-columns: repeat(3, 1fr);
  }

  /* Nút lọc canh giữa nội dung. */
  .nut-loc {
    /* Canh giữa nhãn và số đếm. */
    justify-content: center;
    /* Cho phép nhãn dài xuống dòng. */
    text-align: center;
  }

  /* Nút xóa hàng loạt chiếm toàn bộ chiều rộng. */
  .nut-xoa-hang-loat {
    /* Chiều rộng 100%. */
    width: 100%;
  }

  /* Dòng công việc cho phép xuống dòng. */
  .dong-cong-viec {
    /* Cho phép các phần tử rơi xuống dòng khi thiếu chỗ. */
    flex-wrap: wrap;
  }

  /* Nhãn kiểm tra chiếm toàn bộ dòng đầu. */
  .nhan-kiem-tra {
    /* Chiếm 100% chiều rộng. */
    flex-basis: 100%;
  }

  /* Nhãn trạng thái và nút xóa nằm ở dòng dưới. */
  .nhan-trang-thai {
    /* Đẩy nhãn trạng thái sang trái. */
    margin-left: 28px;
  }

  /* Nút xóa đẩy sang phải. */
  .nut-xoa {
    /* Tự động chiếm khoảng trống còn lại bên trái. */
    margin-left: auto;
  }
}

/* ---------------------------------------------------------------------------
   9. HỖ TRỢ BÀN PHÍM (focus rõ ràng)
   --------------------------------------------------------------------------- */

/* Khi di chuyển bằng phím Tab, hiển thị viền focus rõ ràng cho nút. */
.nut-them:focus-visible,
.nut-loc:focus-visible,
.nut-xoa-hang-loat:focus-visible,
.nut-xoa:focus-visible {
  /* Bỏ viền mặc định. */
  outline: none;
  /* Thêm quầng sáng xanh đậm. */
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.35);
}

/* Khi di chuyển bằng phím Tab, hiển thị viền focus cho checkbox. */
.o-kiem-tra:focus-visible {
  /* Bỏ viền mặc định. */
  outline: none;
  /* Thêm quầng sáng xanh. */
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.3);
}
</style>
