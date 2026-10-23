<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="site-shell">
  <header class="site-header">
    <a class="brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="An Nhiên - trang chủ">
      <span class="brand-mark" aria-hidden="true">AN</span>
      <span>AN NHIÊN<small>TRÀ VIỆT TUYỂN CHỌN</small></span>
    </a>
    <nav class="main-nav" aria-label="Điều hướng chính">
      <a href="<?php echo esc_url(home_url('/#san-pham')); ?>">Các loại trà</a>
      <a href="<?php echo esc_url(home_url('/#cau-chuyen')); ?>">Câu chuyện</a>
      <a class="nav-cta" href="<?php echo esc_url(home_url('/#san-pham')); ?>">Chọn trà</a>
    </nav>
  </header>

