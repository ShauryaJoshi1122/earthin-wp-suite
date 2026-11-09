<?php
/**
 * Fired when the plugin is deleted.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('earthin_push_settings');

global $wpdb;
$table = $wpdb->prefix . 'earthin_push_subscribers';
$wpdb->query("DROP TABLE IF EXISTS {$table}");
