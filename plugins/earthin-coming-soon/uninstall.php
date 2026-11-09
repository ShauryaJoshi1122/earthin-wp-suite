<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('earthin_coming_soon_options');
delete_option('earthin_coming_soon_subscribers');
