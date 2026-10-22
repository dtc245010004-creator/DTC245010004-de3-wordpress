<?php
if (!defined('ABSPATH')) {
    exit;
}

function dean3_store_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
}
add_action('after_setup_theme', 'dean3_store_setup');

function dean3_store_assets(): void
{
    wp_enqueue_style('dean3-store', get_stylesheet_uri(), [], '1.1.0');
}
add_action('wp_enqueue_scripts', 'dean3_store_assets');
