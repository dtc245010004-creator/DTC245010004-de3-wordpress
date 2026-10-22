<?php
/**
 * Plugin Name: De An 3 Product Showcase
 * Description: Adds a small product catalog and a Vietnamese showcase landing page.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'dean3_register_product_type', 5);
function dean3_register_product_type(): void
{
    register_post_type('dean3_product', [
        'labels' => [
            'name' => 'Sản phẩm',
            'singular_name' => 'Sản phẩm',
            'add_new_item' => 'Thêm sản phẩm',
            'edit_item' => 'Sửa sản phẩm',
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'san-pham'],
        'menu_icon' => 'dashicons-products',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
        'show_in_rest' => true,
    ]);
}

add_action('init', 'dean3_seed_showcase', 20);
function dean3_seed_showcase(): void
{
    if (get_option('dean3_showcase_seeded')) {
        return;
    }

    if (get_stylesheet() !== 'dean3-store') {
        switch_theme('dean3-store');
    }

    $products = [
        [
            'title' => 'Bình giữ nhiệt Mộc 500 ml',
            'excerpt' => 'Giữ nóng hoặc lạnh suốt ngày dài, thân thép không gỉ và nắp chống tràn.',
            'price' => '389000',
            'old_price' => '499000',
            'mark' => 'MỘC',
            'content' => 'Thiết kế gọn nhẹ cho những chuyến đi hằng ngày. Lớp thép không gỉ dễ vệ sinh, nắp kín hạn chế rò nước trong túi.',
        ],
        [
            'title' => 'Tai nghe không dây AirSound Mini',
            'excerpt' => 'Âm thanh rõ, hộp sạc nhỏ gọn và kết nối ổn định cho ngày bận rộn.',
            'price' => '1290000',
            'old_price' => '1590000',
            'mark' => 'AIR',
            'content' => 'AirSound Mini có thiết kế nhẹ, thao tác cảm ứng đơn giản và hộp sạc bỏ túi. Phù hợp nghe nhạc, học trực tuyến và gọi điện.',
        ],
        [
            'title' => 'Đèn bàn Lumina Study',
            'excerpt' => 'Ánh sáng dịu mắt, ba mức sáng và chân đế gọn cho góc học tập.',
            'price' => '549000',
            'old_price' => '699000',
            'mark' => 'LUMI',
            'content' => 'Đèn bàn Lumina Study có ba mức sáng, cổ đèn điều chỉnh linh hoạt và thiết kế tối giản cho bàn học hoặc bàn làm việc.',
        ],
    ];

    foreach ($products as $product) {
        $existing = get_page_by_title($product['title'], OBJECT, 'dean3_product');
        if (!$existing) {
            $product_id = wp_insert_post([
                'post_type' => 'dean3_product',
                'post_status' => 'publish',
                'post_title' => $product['title'],
                'post_excerpt' => $product['excerpt'],
                'post_content' => $product['content'],
            ]);
            if (!is_wp_error($product_id) && $product_id) {
                update_post_meta($product_id, '_dean3_price', $product['price']);
                update_post_meta($product_id, '_dean3_old_price', $product['old_price']);
                update_post_meta($product_id, '_dean3_mark', $product['mark']);
            }
        }
    }

    $page = get_page_by_path('cua-hang');
    if (!$page) {
        $page_id = wp_insert_post([
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => 'Cửa hàng An Nhiên',
            'post_name' => 'cua-hang',
            'post_content' => '[dean3_products]',
        ]);
        if (!is_wp_error($page_id) && $page_id) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $page_id);
        }
    } else {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $page->ID);
    }

    update_option('dean3_showcase_seeded', 1);
}

add_shortcode('dean3_products', 'dean3_render_products');
function dean3_render_products(): string
{
    $query = new WP_Query([
        'post_type' => 'dean3_product',
        'post_status' => 'publish',
        'posts_per_page' => 6,
        'orderby' => 'menu_order date',
        'order' => 'ASC',
    ]);

    if (!$query->have_posts()) {
        return '<p>Danh mục sản phẩm đang được cập nhật.</p>';
    }

    ob_start();
    echo '<div class="product-grid">';
    $index = 0;
    while ($query->have_posts()) {
        $query->the_post();
        $product_id = get_the_ID();
        $price = (int) get_post_meta($product_id, '_dean3_price', true);
        $old_price = (int) get_post_meta($product_id, '_dean3_old_price', true);
        $mark = get_post_meta($product_id, '_dean3_mark', true);
        $tone = ['sage', 'rose', 'butter'][$index % 3];
        echo '<article class="product-card">';
        echo '<a class="product-visual ' . esc_attr($tone) . '" href="' . esc_url(get_permalink()) . '" aria-label="Xem ' . esc_attr(get_the_title()) . '">';
        if (has_post_thumbnail()) {
            echo get_the_post_thumbnail($product_id, 'medium_large', ['class' => 'product-image', 'loading' => 'lazy']);
        } else {
            echo '<span class="product-mark">' . esc_html($mark ?: 'AN') . '</span>';
            echo '<span class="product-orbit" aria-hidden="true"></span>';
        }
        echo '<span class="product-badge">Được yêu thích</span></a>';
        echo '<div class="product-info"><p class="product-kicker">AN NHIÊN SELECT</p>';
        echo '<h3><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h3>';
        echo '<p class="product-excerpt">' . esc_html(get_the_excerpt()) . '</p>';
        echo '<div class="product-bottom"><p class="product-price">' . esc_html(number_format_i18n($price)) . ' <small>₫</small>';
        if ($old_price > $price) {
            echo '<del>' . esc_html(number_format_i18n($old_price)) . ' ₫</del>';
        }
        echo '</p><a class="product-link" href="' . esc_url(get_permalink()) . '" aria-label="Xem chi tiết ' . esc_attr(get_the_title()) . '">↗</a></div></div></article>';
        $index++;
    }
    echo '</div>';
    wp_reset_postdata();
    return (string) ob_get_clean();
}
