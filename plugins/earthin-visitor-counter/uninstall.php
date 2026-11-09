<?php
/**
 * Fired when the plugin is deleted.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('earthin_vc_total_views');

global $wpdb;
$table = $wpdb->prefix . 'earthin_visitor_stats';
$wpdb->query("DROP TABLE IF EXISTS {$table}");
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_earthin_views'");
