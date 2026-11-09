<?php
/**
 * Earthin Minimal Theme Functions
 *
 * @package Earthin_Minimal
 * @author Shaurya Joshi
 * @link https://plugins.earthin.online
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('earthin_minimal_setup')) :
    function earthin_minimal_setup(): void {
        load_theme_textdomain('earthin-minimal', get_template_directory() . '/languages');

        add_theme_support('automatic-feed-links');
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        set_post_thumbnail_size(1000, 560, true);

        register_nav_menus([
            'primary' => __('Primary Navigation', 'earthin-minimal'),
        ]);

        add_theme_support('html5', [
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        ]);

        add_theme_support('align-wide');
        add_theme_support('responsive-embeds');
        add_theme_support('editor-styles');
        add_editor_style('style.css');
    }
endif;
add_action('after_setup_theme', 'earthin_minimal_setup');

function earthin_minimal_scripts(): void {
    wp_enqueue_style('earthin-minimal-style', get_stylesheet_uri(), [], '1.0.0');

    // Inline reading progress tracker
    if (is_singular('post')) {
        wp_add_inline_script(
            'earthin-minimal-style',
            'document.addEventListener("scroll", function() {
                var doc = document.documentElement;
                var total = doc.scrollHeight - doc.clientHeight;
                var current = doc.scrollTop;
                var bar = document.querySelector(".em-reading-progress-bar");
                if (bar && total > 0) {
                    bar.style.width = Math.min(100, Math.max(0, (current / total) * 100)) + "%";
                }
            });'
        );
    }

    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'earthin_minimal_scripts');

function earthin_minimal_reading_time(): string {
    $content = get_post_field('post_content', get_the_ID());
    $word_count = str_word_count(strip_tags($content));
    $minutes = max(1, (int) ceil($word_count / 200));
    return sprintf(_n('%d min read', '%d mins read', $minutes, 'earthin-minimal'), $minutes);
}
