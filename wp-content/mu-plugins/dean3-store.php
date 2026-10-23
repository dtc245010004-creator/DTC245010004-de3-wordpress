<?php
/**
 * Plugin Name: De An 3 Product Showcase
 * Description: Adds a small product catalog and a Vietnamese showcase landing page.
 * Version: 1.1.0
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
    $catalog_version = '3';
    if (get_option('dean3_showcase_catalog_version') === $catalog_version) {
        return;
    }

    if (get_stylesheet() !== 'dean3-store') {
        switch_theme('dean3-store');
    }

    if (get_option('blogname') === 'Website quảng bá sản phẩm') {
        update_option('blogname', 'An Nhiên · Trà Việt');
    }

    $products = [
        [
            'legacy_title' => 'Bình giữ nhiệt Mộc 500 ml',
            'title' => 'Trà Shan Tuyết cổ thụ Hà Giang · 100 g',
            'excerpt' => 'Vị trà đậm dịu, hương núi rừng thoảng nhẹ; hợp để pha ấm nóng trong những buổi sáng chậm rãi.',
            'price' => '185000',
            'old_price' => '220000',
            'mark' => 'SHAN',
            'badge' => 'Trà vùng cao',
            'content' => 'Trà Shan Tuyết được giới thiệu với hương thơm mộc mạc, vị chát dịu và hậu vị ngọt. Gói 100 g phù hợp để thưởng trà tại nhà hoặc làm quà nhỏ. Dùng nước nóng vừa sôi, tráng trà nhanh rồi điều chỉnh thời gian hãm theo khẩu vị.',
        ],
        [
            'legacy_title' => 'Tai nghe không dây AirSound Mini',
            'title' => 'Trà sen Tây Hồ ướp bông · 100 g',
            'excerpt' => 'Hương sen thanh thoát hòa cùng vị trà xanh êm dịu, dành cho một khoảng nghỉ nhẹ nhàng trong ngày.',
            'price' => '295000',
            'old_price' => '350000',
            'mark' => 'SEN',
            'badge' => 'Ướp hương sen',
            'content' => 'Trà sen mang hương thơm dịu, vị trà cân bằng và dễ thưởng thức cùng gia đình. Gói 100 g thích hợp pha ấm nhỏ, dùng sau bữa ăn hoặc trong những buổi trò chuyện. Bảo quản kín, nơi khô ráo và tránh ánh nắng trực tiếp.',
        ],
        [
            'legacy_title' => 'Đèn bàn Lumina Study',
            'title' => 'Chè xanh Tân Cương Thái Nguyên · 200 g',
            'excerpt' => 'Chén trà xanh trong, vị chát vừa và hậu ngọt; lựa chọn quen thuộc cho nếp trà Việt mỗi ngày.',
            'price' => '165000',
            'old_price' => '195000',
            'mark' => 'THÁI',
            'badge' => 'Trà xanh Việt',
            'content' => 'Chè xanh Tân Cương có hương cốm nhẹ, vị chát dịu và hậu vị ngọt. Túi 200 g dùng được nhiều lần pha, phù hợp uống nóng hằng ngày. Nên tráng trà nhanh và hãm trong thời gian ngắn để giữ vị thanh.',
        ],
    ];

    foreach ($products as $product) {
        $existing = get_page_by_title($product['title'], OBJECT, 'dean3_product');
        if (!$existing && !empty($product['legacy_title'])) {
            $existing = get_page_by_title($product['legacy_title'], OBJECT, 'dean3_product');
        }
        $post_data = [
                'post_type' => 'dean3_product',
                'post_status' => 'publish',
                'post_title' => $product['title'],
                'post_excerpt' => $product['excerpt'],
                'post_content' => $product['content'],
        ];
        if ($existing) {
            $post_data['ID'] = $existing->ID;
            $product_id = wp_update_post($post_data, true);
        } else {
            $product_id = wp_insert_post($post_data, true);
        }
        if (!is_wp_error($product_id) && $product_id) {
            update_post_meta($product_id, '_dean3_price', $product['price']);
            update_post_meta($product_id, '_dean3_old_price', $product['old_price']);
            update_post_meta($product_id, '_dean3_mark', $product['mark']);
            update_post_meta($product_id, '_dean3_badge', $product['badge']);
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
    update_option('dean3_showcase_catalog_version', $catalog_version);
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
        $badge = get_post_meta($product_id, '_dean3_badge', true);
        $tone = ['sage', 'rose', 'butter'][$index % 3];
        echo '<article class="product-card">';
        echo '<a class="product-visual ' . esc_attr($tone) . '" href="' . esc_url(get_permalink()) . '" aria-label="Xem ' . esc_attr(get_the_title()) . '">';
        if (has_post_thumbnail()) {
            echo get_the_post_thumbnail($product_id, 'medium_large', ['class' => 'product-image', 'loading' => 'lazy']);
        } else {
            echo '<span class="product-mark">' . esc_html($mark ?: 'AN') . '</span>';
            echo '<span class="product-orbit" aria-hidden="true"></span>';
        }
        echo '<span class="product-badge">' . esc_html($badge ?: 'Trà Việt') . '</span></a>';
        echo '<div class="product-info"><p class="product-kicker">TRÀ VIỆT · AN NHIÊN</p>';
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

