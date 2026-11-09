<?php
/**
 * Earthin WP Suite Root Theme Functions
 *
 * Automatically delegates to the Earthin flagship theme when the monorepo
 * is uploaded directly as a WordPress theme package.
 *
 * @package Earthin
 * @author Shaurya Joshi
 * @link https://plugins.earthin.online
 */

if (!defined('ABSPATH')) {
    exit;
}

if (file_exists(__DIR__ . '/themes/earthin/functions.php')) {
    require_once __DIR__ . '/themes/earthin/functions.php';
}
