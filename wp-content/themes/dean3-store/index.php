<?php get_header(); ?>
<main class="page-content">
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <article <?php post_class(); ?>><h1><?php the_title(); ?></h1><div><?php the_content(); ?></div></article>
  <?php endwhile; else : ?>
    <p>Nội dung đang được cập nhật.</p>
  <?php endif; ?>
</main>
<?php get_footer(); ?>
