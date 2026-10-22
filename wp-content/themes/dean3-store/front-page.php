<?php get_header(); ?>
<main>
  <section class="hero">
    <div class="hero-copy">
      <p class="eyebrow">AN NHIÊN · TUYỂN CHỌN TỬ TẾ</p>
      <h1>Chọn điều tốt<br>cho nhịp sống <em>mỗi ngày.</em></h1>
      <p>Những món đồ nhỏ được chọn vì sự hữu ích, thiết kế bền đẹp và cảm giác dễ chịu khi dùng lâu dài.</p>
      <div class="hero-actions">
        <a class="button" href="#san-pham">Khám phá bộ sưu tập</a>
        <a class="text-link" href="#cau-chuyen">Về An Nhiên <span>↗</span></a>
      </div>
      <div class="hero-meta"><span>Thiết kế có chủ đích</span><span>Chăm chút từng chi tiết</span></div>
    </div>
    <div class="hero-art" aria-label="Minh họa sản phẩm thủ công và thiên nhiên" role="img">
      <div class="art-disc"><span class="art-sun"></span><span class="art-leaf one"></span><span class="art-leaf two"></span><span class="art-vase"></span></div>
      <div class="art-caption"><strong>Một chút an yên</strong>cho ngày thường bận rộn</div>
    </div>
  </section>
  <section class="trust-strip" aria-label="Cam kết">
    <div class="trust-item"><span class="trust-icon">✳</span><span>Chọn lọc từ chất liệu đáng tin cậy</span></div>
    <div class="trust-item"><span class="trust-icon">↗</span><span>Thiết kế hữu ích, dễ dùng mỗi ngày</span></div>
    <div class="trust-item"><span class="trust-icon">♡</span><span>Đóng gói gọn gàng, hạn chế lãng phí</span></div>
  </section>
  <section class="products-section" id="san-pham">
    <div class="section-heading">
      <div><p class="section-kicker">BỘ SƯU TẬP AN NHIÊN</p><h2>Đồ tốt cho ngày thường</h2></div>
      <p>Chọn một món đồ phù hợp với góc làm việc, chuyến đi hoặc khoảng nghỉ nhỏ của bạn.</p>
    </div>
    <?php echo do_shortcode('[dean3_products]'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
  </section>
  <section class="story-section" id="cau-chuyen">
    <div class="story-copy"><p class="section-kicker">CÂU CHUYỆN AN NHIÊN</p><h2>Ít hơn, nhưng tốt hơn.</h2><p>An Nhiên giới thiệu những sản phẩm thiết thực với thông tin rõ ràng để bạn dễ chọn món đồ phù hợp. Mỗi sản phẩm đều hướng đến công năng tốt, kiểu dáng giản dị và trải nghiệm dùng lâu dài.</p><a class="text-link" href="mailto:hello@annhien.example">Cần tư vấn? Liên hệ với chúng tôi <span>↗</span></a></div>
    <div class="story-note"><blockquote>“Một lựa chọn nhỏ, hữu ích có thể làm ngày thường nhẹ nhàng hơn.”</blockquote><p>AN NHIÊN · ĐỒ DÙNG CHO CUỘC SỐNG HIỆN ĐẠI</p></div>
  </section>
</main>
<?php get_footer(); ?>
