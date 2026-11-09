<?php
/**
 * Earthin WP Suite Root Theme Index
 *
 * Automatically delegates to the Earthin flagship theme when the monorepo
 * is uploaded directly as a WordPress theme package.
 *
 * @package Earthin
 * @author Shaurya Joshi
 * @link https://plugins.earthin.online
 */

if (file_exists(__DIR__ . '/themes/earthin/index.php')) {
    require_once __DIR__ . '/themes/earthin/index.php';
} else {
    get_header();
    echo '<main class="earthin-container"><h1>' . esc_html__('Welcome to Earthin WP Suite', 'earthin') . '</h1></main>';
    get_footer();
}
