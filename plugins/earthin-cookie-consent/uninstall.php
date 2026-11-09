<?php
/**
 * Fired when the plugin is deleted.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('earthin_cookie_consent_settings');
