<?php get_header(); ?>
<main class="single-product">
  <?php while (have_posts()) : the_post();
    $price = (int) get_post_meta(get_the_ID(), '_dean3_price', true);
    $old_price = (int) get_post_meta(get_the_ID(), '_dean3_old_price', true);
  ?>
    <p class="section-kicker">AN NHIÊN · CHI TIẾT TRÀ</p>
    <h1 class="entry-title"><?php the_title(); ?></h1>
    <div class="entry-content"><?php the_content(); ?></div>
    <p class="product-price"><?php echo esc_html(number_format_i18n($price)); ?> ₫ <?php if ($old_price > $price) : ?><del><?php echo esc_html(number_format_i18n($old_price)); ?> ₫</del><?php endif; ?></p>
    <a class="button" href="mailto:hello@annhien.example?subject=<?php echo rawurlencode('Tư vấn ' . get_the_title()); ?>">Tư vấn loại trà này</a><br>
    <a class="single-back" href="<?php echo esc_url(home_url('/#san-pham')); ?>">← Quay lại bộ sưu tập</a>
  <?php endwhile; ?>
</main>
<?php get_footer(); ?>

