<?php
/**
 * Fired when the plugin is deleted.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('earthin_login_shield_settings');
delete_option('earthin_login_shield_logs');

global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_earthin_ls_%' OR option_name LIKE '_transient_timeout_earthin_ls_%'");
