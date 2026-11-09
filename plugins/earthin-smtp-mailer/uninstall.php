<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('earthin_smtp_settings');
