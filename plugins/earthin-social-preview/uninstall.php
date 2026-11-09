<?php
/**
 * Fired when the plugin is deleted.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('earthin_social_preview_settings');

global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key IN ('_earthin_social_title', '_earthin_social_desc', '_earthin_social_image')");
