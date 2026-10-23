<?php get_header(); ?>
<main>
  <section class="hero">
    <div class="hero-copy">
      <p class="eyebrow">AN NHIÊN · TRÀ VIỆT TUYỂN CHỌN</p>
      <h1>Chậm một chút,<br>thưởng trà <em>mỗi ngày.</em></h1>
      <p>Khám phá những vị trà Việt được chọn cho tách trà thơm dịu, những cuộc chuyện trò và khoảng nghỉ bình yên.</p>
      <div class="hero-actions">
        <a class="button" href="#san-pham">Khám phá các vị trà</a>
        <a class="text-link" href="#cau-chuyen">Về An Nhiên <span>↗</span></a>
      </div>
      <div class="hero-meta"><span>Hương vị trà Việt</span><span>Chăm chút từng ấm trà</span></div>
    </div>
    <div class="hero-art" aria-label="Minh họa lá trà và hũ trà An Nhiên" role="img">
      <div class="art-disc"><span class="art-sun"></span><span class="art-leaf one"></span><span class="art-leaf two"></span><span class="art-tea-caddy"></span></div>
      <div class="art-caption"><strong>Hương trà an yên</strong>cho khoảng nghỉ dịu dàng</div>
    </div>
  </section>
  <section class="trust-strip" aria-label="Cam kết">
    <div class="trust-item"><span class="trust-icon">✳</span><span>Trà Việt từ những vùng nguyên liệu tiêu biểu</span></div>
    <div class="trust-item"><span class="trust-icon">↗</span><span>Gợi ý cách pha để dễ thưởng thức</span></div>
    <div class="trust-item"><span class="trust-icon">♡</span><span>Đóng gói cẩn thận, giữ hương trà</span></div>
  </section>
  <section class="products-section" id="san-pham">
    <div class="section-heading">
      <div><p class="section-kicker">BỘ SƯU TẬP TRÀ AN NHIÊN</p><h2>Chọn vị trà cho riêng bạn</h2></div>
      <p>Từ vị trà xanh thân quen đến hương sen thanh nhẹ, mỗi lựa chọn đều dành cho một khoảnh khắc thưởng trà.</p>
    </div>
    <?php echo do_shortcode('[dean3_products]'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
  </section>
  <section class="story-section" id="cau-chuyen">
    <div class="story-copy"><p class="section-kicker">CÂU CHUYỆN AN NHIÊN</p><h2>Từ vùng trà Việt đến tách trà nhà bạn.</h2><p>An Nhiên giới thiệu những loại trà quen thuộc của Việt Nam, kèm thông tin về hương vị và cách pha để bạn dễ tìm được chén trà hợp gu. Dù là một buổi sáng cần chút tỉnh táo hay buổi chiều muốn chậm lại, luôn có một ấm trà vừa vặn.</p><a class="text-link" href="mailto:hello@annhien.example">Cần tư vấn chọn trà? Liên hệ với chúng tôi <span>↗</span></a></div>
    <div class="story-note"><blockquote>“Pha một ấm trà, chừa lại cho mình một khoảng yên.”</blockquote><p>AN NHIÊN · TRÀ VIỆT CHO NHỮNG NGÀY AN NHIÊN</p></div>
  </section>
</main>
<?php get_footer(); ?>

