<?php
/**
 * Earthin WP Suite Root Theme Footer
 *
 * @package Earthin
 */

if (file_exists(__DIR__ . '/themes/earthin/footer.php')) {
    require_once __DIR__ . '/themes/earthin/footer.php';
} else {
    wp_footer();
}
