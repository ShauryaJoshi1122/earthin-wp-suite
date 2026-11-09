<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE '_earthin_2fa_%'");
