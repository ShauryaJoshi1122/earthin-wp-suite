<?php
/**
 * Earthin Theme Functions & Definitions
 *
 * @package Earthin
 * @author Shaurya Joshi
 * @link https://plugins.earthin.online
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('earthin_setup')) :
    function earthin_setup(): void {
        load_theme_textdomain('earthin', get_template_directory() . '/languages');

        add_theme_support('automatic-feed-links');
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        set_post_thumbnail_size(1200, 630, true);

        register_nav_menus([
            'primary' => __('Primary Header Menu', 'earthin'),
            'footer'  => __('Footer Menu', 'earthin'),
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
add_action('after_setup_theme', 'earthin_setup');

function earthin_content_width(): void {
    $GLOBALS['content_width'] = apply_filters('earthin_content_width', 1200);
}
add_action('after_setup_theme', 'earthin_content_width', 0);

function earthin_scripts(): void {
    wp_enqueue_style('earthin-style', get_stylesheet_uri(), [], '1.0.0');

    wp_enqueue_script(
        'earthin-navigation',
        get_template_directory_uri() . '/assets/js/navigation.js',
        [],
        '1.0.0',
        ['strategy' => 'defer', 'in_footer' => true]
    );

    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'earthin_scripts');

function earthin_widgets_init(): void {
    register_sidebar([
        'name'          => __('Main Sidebar', 'earthin'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here to appear in your blog and page sidebar.', 'earthin'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ]);

    register_sidebar([
        'name'          => __('Footer Widget Area', 'earthin'),
        'id'            => 'footer-1',
        'description'   => __('Add widgets here to appear in your site footer.', 'earthin'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="footer-widget-title">',
        'after_title'   => '</h3>',
    ]);
}
add_action('widgets_init', 'earthin_widgets_init');

function earthin_body_classes(array $classes): array {
    $no_sidebar_templates = [
        'template-full-width.php',
        'template-canvas.php',
        'template-reading.php',
        'template-contact.php',
        'template-about.php',
        'template-services.php',
        'template-portfolio.php',
    ];

    foreach ($no_sidebar_templates as $tpl) {
        if (is_page_template($tpl)) {
            $classes = array_diff($classes, ['has-sidebar']);
            $classes[] = 'no-sidebar';
            $classes[] = 'earthin-no-sidebar';
            break;
        }
    }

    if (is_page_template('template-left-sidebar.php')) {
        $classes[] = 'earthin-left-sidebar';
        if (is_active_sidebar('sidebar-1')) {
            $classes[] = 'has-sidebar';
        }
    }

    return array_values(array_unique($classes));
}
add_filter('body_class', 'earthin_body_classes', 20);

// Load Template Tags & Customizer
require_once get_template_directory() . '/inc/template-tags.php';
require_once get_template_directory() . '/inc/customizer.php';

