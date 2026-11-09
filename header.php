<?php
/**
 * Earthin WP Suite Root Theme Header
 *
 * @package Earthin
 */

if (file_exists(__DIR__ . '/themes/earthin/header.php')) {
    require_once __DIR__ . '/themes/earthin/header.php';
} else {
    wp_head();
}
