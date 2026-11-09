<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('earthin_cache_options');

$cache_dir = trailingslashit(WP_CONTENT_DIR) . 'cache/earthin-cache/';
if (is_dir($cache_dir)) {
    $files = glob($cache_dir . '*');
    if (is_array($files)) {
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
    @rmdir($cache_dir);
}
