<?php
/**
 * Plugin Name: Earthin Sticky Header & Navigation
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-sticky-header
 * Description: Smooth sticky header on scroll, scroll-to-top button, and animated reading progress bar with zero external dependencies.
 * Version: 1.0.0
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * Text Domain: earthin-sticky-header
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class Earthin_Sticky_Header {
    private static ?Earthin_Sticky_Header $instance = null;
    private array $options;

    public static function get_instance(): Earthin_Sticky_Header {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->options = get_option('earthin_sticky_header_options', [
            'sticky_enabled' => 1,
            'header_selector' => 'header, .site-header, #masthead',
            'progress_bar' => 1,
            'progress_color' => '#059669',
            'scroll_to_top' => 1,
            'mobile_enabled' => 1,
        ]);

        add_action('wp_head', [$this, 'inject_frontend_styles']);
        add_action('wp_footer', [$this, 'inject_frontend_scripts_and_markup']);
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_init', [$this, 'handle_admin_actions']);
    }

    public function inject_frontend_styles(): void {
        if (is_admin()) return;
        ?>
        <style id="earthin-sticky-header-css">
            <?php if (!empty($this->options['progress_bar'])) : ?>
            #earthin-reading-progress {
                position: fixed;
                top: 0;
                left: 0;
                height: 3px;
                background-color: <?php echo esc_attr($this->options['progress_color'] ?? '#059669'); ?>;
                z-index: 999999;
                width: 0%;
                transition: width 0.1s linear;
            }
            <?php endif; ?>

            <?php if (!empty($this->options['scroll_to_top'])) : ?>
            #earthin-scroll-top-btn {
                position: fixed;
                bottom: 30px;
                right: 30px;
                width: 44px;
                height: 44px;
                background: #0f172a;
                color: #ffffff;
                border: none;
                border-radius: 50%;
                font-size: 20px;
                cursor: pointer;
                display: none;
                align-items: center;
                justify-content: center;
                z-index: 99999;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                transition: opacity 0.2s ease, transform 0.2s ease;
            }
            #earthin-scroll-top-btn:hover {
                transform: translateY(-2px);
                background: <?php echo esc_attr($this->options['progress_color'] ?? '#059669'); ?>;
            }
            <?php endif; ?>

            .earthin-is-sticky {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                z-index: 9999 !important;
                box-shadow: 0 4px 15px rgba(0,0,0,0.08) !important;
                animation: earthinSlideDown 0.3s ease-out forwards;
            }

            @keyframes earthinSlideDown {
                from { transform: translateY(-100%); }
                to { transform: translateY(0); }
            }
        </style>
        <?php
    }

    public function inject_frontend_scripts_and_markup(): void {
        if (is_admin()) return;
        ?>
        <?php if (!empty($this->options['progress_bar'])) : ?>
            <div id="earthin-reading-progress"></div>
        <?php endif; ?>

        <?php if (!empty($this->options['scroll_to_top'])) : ?>
            <button id="earthin-scroll-top-btn" aria-label="<?php esc_attr_e('Scroll to top', 'earthin-sticky-header'); ?>">↑</button>
        <?php endif; ?>

        <script id="earthin-sticky-header-js">
            (function() {
                'use strict';
                var headerSelector = <?php echo wp_json_encode($this->options['header_selector'] ?? 'header'); ?>;
                var stickyEnabled = <?php echo !empty($this->options['sticky_enabled']) ? 'true' : 'false'; ?>;
                var progressBar = document.getElementById('earthin-reading-progress');
                var scrollBtn = document.getElementById('earthin-scroll-top-btn');
                var headerEl = document.querySelector(headerSelector);
                var lastScroll = 0;

                window.addEventListener('scroll', function() {
                    var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    var docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;

                    // Progress bar
                    if (progressBar && docHeight > 0) {
                        var progress = (scrollTop / docHeight) * 100;
                        progressBar.style.width = Math.min(progress, 100) + '%';
                    }

                    // Scroll to top button
                    if (scrollBtn) {
                        scrollBtn.style.display = (scrollTop > 300) ? 'flex' : 'none';
                    }

                    // Sticky Header
                    if (stickyEnabled && headerEl) {
                        if (scrollTop > 180) {
                            if (!headerEl.classList.contains('earthin-is-sticky')) {
                                headerEl.classList.add('earthin-is-sticky');
                            }
                        } else {
                            if (headerEl.classList.contains('earthin-is-sticky')) {
                                headerEl.classList.remove('earthin-is-sticky');
                            }
                        }
                    }
                    lastScroll = scrollTop;
                }, { passive: true });

                if (scrollBtn) {
                    scrollBtn.addEventListener('click', function() {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                }
            })();
        </script>
        <?php
    }

    public function register_admin_menu(): void {
        add_options_page(
            __('Earthin Sticky Header', 'earthin-sticky-header'),
            __('Earthin Sticky Header', 'earthin-sticky-header'),
            'manage_options',
            'earthin-sticky-header',
            [$this, 'render_admin_page']
        );
    }

    public function handle_admin_actions(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['earthin_save_sticky']) && check_admin_referer('earthin_sticky_save_nonce')) {
            $this->options['sticky_enabled'] = isset($_POST['sticky_enabled']) ? 1 : 0;
            $this->options['header_selector'] = sanitize_text_field($_POST['header_selector'] ?? 'header');
            $this->options['progress_bar'] = isset($_POST['progress_bar']) ? 1 : 0;
            $this->options['progress_color'] = sanitize_hex_color($_POST['progress_color'] ?? '#059669');
            $this->options['scroll_to_top'] = isset($_POST['scroll_to_top']) ? 1 : 0;
            $this->options['mobile_enabled'] = isset($_POST['mobile_enabled']) ? 1 : 0;

            update_option('earthin_sticky_header_options', $this->options);
            add_settings_error('earthin_sticky', 'saved', __('Sticky header configuration saved.', 'earthin-sticky-header'), 'updated');
        }
    }

    public function render_admin_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        $is_sticky = !empty($this->options['sticky_enabled']);
        $status_label = $is_sticky ? __('Sticky Header Active', 'earthin-sticky-header') : __('Sticky Header Disabled', 'earthin-sticky-header');
        $status_badge_class = $is_sticky ? 'earthin-badge-success' : 'earthin-badge-warning';
        ?>
        <div class="earthin-admin-wrap">
            <style>
                .earthin-admin-wrap { max-width: 1200px; margin: 20px 20px 0 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
                .earthin-header { background: #0f172a; color: #ffffff; padding: 22px 28px; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08); flex-wrap: wrap; gap: 16px; }
                .earthin-header-left { display: flex; align-items: center; gap: 14px; }
                .earthin-logo-badge { background: #059669; color: #ffffff; font-weight: 800; font-size: 14px; padding: 6px 12px; border-radius: 8px; letter-spacing: 0.04em; display: inline-flex; align-items: center; gap: 6px; }
                .earthin-title-group h1 { color: #ffffff; margin: 0; font-size: 20px; font-weight: 700; line-height: 1.2; display: flex; align-items: center; gap: 10px; }
                .earthin-version-tag { font-size: 11px; background: rgba(255,255,255,0.15); color: #cbd5e1; padding: 2px 8px; border-radius: 999px; font-weight: 600; }
                .earthin-title-group p { margin: 4px 0 0; color: #94a3b8; font-size: 13px; }
                .earthin-header-links { display: flex; gap: 10px; align-items: center; }
                .earthin-link-btn { background: rgba(255,255,255,0.08); color: #e2e8f0 !important; text-decoration: none !important; font-size: 12px; font-weight: 600; padding: 7px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.12); transition: all 0.15s ease; }
                .earthin-link-btn:hover { background: rgba(255,255,255,0.18); color: #ffffff !important; }
                .earthin-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
                @media(max-width: 900px) { .earthin-grid { grid-template-columns: 1fr; } }
                .earthin-card { background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); padding: 24px 28px; margin-bottom: 24px; }
                .earthin-card-header { padding-bottom: 14px; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
                .earthin-card-title { font-size: 16px; font-weight: 700; color: #0f172a; margin: 0; }
                .earthin-btn-primary { background: #059669 !important; border-color: #059669 !important; color: #ffffff !important; font-weight: 700 !important; padding: 8px 22px !important; border-radius: 8px !important; box-shadow: 0 2px 4px rgba(5,150,105,0.2) !important; text-shadow: none !important; cursor: pointer; }
                .earthin-btn-primary:hover { background: #047857 !important; }
                .earthin-badge-pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
                .earthin-badge-success { background: #ecfdf5; color: #059669; }
                .earthin-badge-warning { background: #fffbeb; color: #d97706; }
                .earthin-input { border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 14px; width: 100%; max-width: 380px; }
                .earthin-input:focus { border-color: #059669; outline: none; box-shadow: 0 0 0 3px rgba(5,150,105,0.15); }
            </style>

            <header class="earthin-header">
                <div class="earthin-header-left">
                    <span class="earthin-logo-badge">🌱 Earthin</span>
                    <div class="earthin-title-group">
                        <h1>
                            <?php echo esc_html__('Earthin Sticky Header & Navigation', 'earthin-sticky-header'); ?>
                            <span class="earthin-version-tag">v1.0.0</span>
                            <span class="earthin-badge-pill <?php echo esc_attr($status_badge_class); ?>"><?php echo esc_html($status_label); ?></span>
                        </h1>
                        <p><?php echo esc_html__('Hardware-accelerated sticky navigation, reading progress, and return-to-top meter.', 'earthin-sticky-header'); ?></p>
                    </div>
                </div>
                <div class="earthin-header-links">
                    <a href="https://plugins.earthin.online" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Plugin Hub', 'earthin-sticky-header'); ?> &nearr;</a>
                    <a href="https://plugins.earthin.online/docs" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Documentation', 'earthin-sticky-header'); ?></a>
                    <a href="https://plugins.earthin.online/support" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Support', 'earthin-sticky-header'); ?></a>
                </div>
            </header>

            <?php settings_errors('earthin_sticky'); ?>

            <div class="earthin-grid">
                <div class="earthin-card">
                    <div class="earthin-card-header">
                        <h2 class="earthin-card-title"><?php esc_html_e('Sticky Navigation Options', 'earthin-sticky-header'); ?></h2>
                    </div>

                    <form method="post">
                        <?php wp_nonce_field('earthin_sticky_save_nonce'); ?>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><?php esc_html_e('Sticky Header on Scroll', 'earthin-sticky-header'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="sticky_enabled" value="1" <?php checked(1, $this->options['sticky_enabled']); ?> />
                                        <strong><?php esc_html_e('Fix header seamlessly to top of browser on scroll', 'earthin-sticky-header'); ?></strong>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Header CSS Selector', 'earthin-sticky-header'); ?></th>
                                <td>
                                    <input type="text" name="header_selector" value="<?php echo esc_attr($this->options['header_selector']); ?>" class="earthin-input" />
                                    <p class="description"><?php esc_html_e('Default: header or .site-header. Matches your theme masthead container.', 'earthin-sticky-header'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Reading Progress Bar', 'earthin-sticky-header'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="progress_bar" value="1" <?php checked(1, $this->options['progress_bar']); ?> />
                                        <?php esc_html_e('Display reading completion meter at top of browser.', 'earthin-sticky-header'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Progress Meter Accent Color', 'earthin-sticky-header'); ?></th>
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <input type="color" name="progress_color" value="<?php echo esc_attr($this->options['progress_color']); ?>" style="width:40px; height:36px; border:none; border-radius:6px; cursor:pointer;" />
                                        <code style="background:#f1f5f9; padding:6px 12px; border-radius:6px;"><?php echo esc_html($this->options['progress_color']); ?></code>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Scroll to Top Button', 'earthin-sticky-header'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="scroll_to_top" value="1" <?php checked(1, $this->options['scroll_to_top']); ?> />
                                        <?php esc_html_e('Render smooth floating button to quickly return to top of page.', 'earthin-sticky-header'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>

                        <p class="submit" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #f1f5f9;">
                            <input type="submit" name="earthin_save_sticky" class="button earthin-btn-primary" value="<?php esc_attr_e('Save Configuration', 'earthin-sticky-header'); ?>" />
                        </p>
                    </form>
                </div>

                <div>
                    <div class="earthin-card">
                        <div class="earthin-card-header">
                            <h2 class="earthin-card-title"><?php esc_html_e('Feature Status', 'earthin-sticky-header'); ?></h2>
                        </div>
                        <p style="font-size:13px; color:#475569; margin:8px 0;">
                            <strong><?php esc_html_e('Header Fixed:', 'earthin-sticky-header'); ?></strong>
                            <code><?php echo !empty($this->options['sticky_enabled']) ? 'Active' : 'Disabled'; ?></code>
                        </p>
                        <p style="font-size:13px; color:#475569; margin:8px 0;">
                            <strong><?php esc_html_e('Progress Meter:', 'earthin-sticky-header'); ?></strong>
                            <code><?php echo !empty($this->options['progress_bar']) ? 'Active' : 'Disabled'; ?></code>
                        </p>
                        <p style="font-size:13px; color:#475569; margin:8px 0;">
                            <strong><?php esc_html_e('Back to Top:', 'earthin-sticky-header'); ?></strong>
                            <code><?php echo !empty($this->options['scroll_to_top']) ? 'Active' : 'Disabled'; ?></code>
                        </p>
                    </div>

                    <div class="earthin-card">
                        <div class="earthin-card-header">
                            <h2 class="earthin-card-title"><?php esc_html_e('Performance Note', 'earthin-sticky-header'); ?></h2>
                        </div>
                        <p style="font-size:13px; color:#64748b; line-height:1.6;">
                            <?php esc_html_e('Uses passive window scroll listeners and CSS transform hardware acceleration to guarantee 60fps scrolling without layout reflows.', 'earthin-sticky-header'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Sticky_Header', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-sticky-header')) . '">' . __('Settings', 'earthin-sticky-header') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
