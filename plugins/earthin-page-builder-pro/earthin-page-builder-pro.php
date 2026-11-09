<?php
/**
 * Plugin Name: Earthin Page Builder Pro
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-page-builder-pro
 * Description: Premium layout blocks and styling presets for Earthin Page Builder, including pricing tables, FAQs, and testimonials.
 * Version: 1.0.0
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: earthin-page-builder-pro
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('EARTHIN_PAGE_BUILDER_PRO_VERSION', '1.0.0');
define('EARTHIN_PAGE_BUILDER_PRO_PATH', plugin_dir_path(__FILE__));

class Earthin_Page_Builder_Pro {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_init', [$this, 'check_parent_plugin']);
        add_filter('earthin_pb_render_custom_block', [$this, 'render_pro_blocks'], 10, 2);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_pro_styles']);
    }

    public function check_parent_plugin() {
        if (!class_exists('Earthin_Page_Builder')) {
            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-warning is-dismissible">
                    <p><?php esc_html_e('Earthin Page Builder Pro requires the core "Earthin Page Builder" plugin to be active.', 'earthin-page-builder-pro'); ?></p>
                </div>
                <?php
            });
        }
    }

    public function enqueue_pro_styles() {
        wp_register_style('earthin-pb-pro-styles', false);
        wp_enqueue_style('earthin-pb-pro-styles');
        $css = "
            .earthin-pb-pricing { display: flex; flex-wrap: wrap; gap: 24px; margin: 30px 0; justify-content: center; }
            .earthin-pb-pricing-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 32px 24px; flex: 1 1 300px; max-width: 360px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
            .earthin-pb-pricing-price { font-size: 38px; font-weight: 800; color: #0f172a; margin: 16px 0; }
            .earthin-pb-testimonial { background: #f8fafc; border-left: 4px solid #10b981; padding: 20px 24px; border-radius: 0 8px 8px 0; margin: 24px 0; font-style: italic; }
            .earthin-pb-author { font-style: normal; font-weight: 600; color: #0f172a; margin-top: 10px; }
        ";
        wp_add_inline_style('earthin-pb-pro-styles', $css);
    }

    public function render_pro_blocks($output, $block) {
        $type = $block['type'] ?? '';
        if ($type === 'testimonial') {
            $quote = esc_html($block['quote'] ?? '');
            $author = esc_html($block['author'] ?? 'Satisfied Customer');
            $output .= "
                <div class=\"earthin-pb-testimonial\">
                    <div>&ldquo;{$quote}&rdquo;</div>
                    <div class=\"earthin-pb-author\">&mdash; {$author}</div>
                </div>
            ";
        } elseif ($type === 'pricing') {
            $title = esc_html($block['title'] ?? 'Pro Plan');
            $price = esc_html($block['price'] ?? '$49');
            $btn_text = esc_html($block['btn_text'] ?? 'Get Started');
            $btn_url = esc_url($block['btn_url'] ?? '#');
            $output .= "
                <div class=\"earthin-pb-pricing\">
                    <div class=\"earthin-pb-pricing-card\">
                        <h3>{$title}</h3>
                        <div class=\"earthin-pb-pricing-price\">{$price}</div>
                        <p><a href=\"{$btn_url}\" class=\"earthin-pb-btn earthin-pb-btn-primary\">{$btn_text}</a></p>
                    </div>
                </div>
            ";
        }
        return $output;
    }
}

add_action('plugins_loaded', ['Earthin_Page_Builder_Pro', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function() {
     = '<a href="' . esc_url(admin_url('admin.php?page=earthin-page-builder')) . '">' . __('Settings', 'earthin-page-builder-pro') . '</a>';
    array_unshift(, );
    return ;
});
