<?php
/**
 * Earthin Agency Theme Functions
 *
 * @package Earthin_Agency
 * @author Shaurya Joshi
 * @link https://plugins.earthin.online
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('earthin_agency_setup')) :
    function earthin_agency_setup(): void {
        load_theme_textdomain('earthin-agency', get_template_directory() . '/languages');

        add_theme_support('automatic-feed-links');
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        set_post_thumbnail_size(1200, 630, true);

        register_nav_menus([
            'primary' => __('Agency Header Menu', 'earthin-agency'),
            'footer'  => __('Agency Footer Menu', 'earthin-agency'),
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

        add_theme_support('custom-logo', [
            'height'      => 80,
            'width'       => 260,
            'flex-width'  => true,
            'flex-height' => true,
        ]);

        add_theme_support('align-wide');
        add_theme_support('responsive-embeds');
        add_theme_support('editor-styles');
        add_editor_style('style.css');
    }
endif;
add_action('after_setup_theme', 'earthin_agency_setup');

function earthin_agency_scripts(): void {
    wp_enqueue_style('earthin-agency-style', get_stylesheet_uri(), [], '1.0.0');

    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'earthin_agency_scripts');
