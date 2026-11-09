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
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Earthin Sticky Header & Navigation', 'earthin-sticky-header'); ?></h1>
            <p><?php echo esc_html__('Enhance user engagement with a fixed header on scroll, reading progress meter, and back-to-top button.', 'earthin-sticky-header'); ?></p>
            <?php settings_errors('earthin_sticky'); ?>

            <div style="background:#fff;padding:24px;border:1px solid #ccd0d4;border-radius:8px;max-width:700px;margin-top:20px;">
                <form method="post">
                    <?php wp_nonce_field('earthin_sticky_save_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Sticky Header on Scroll', 'earthin-sticky-header'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="sticky_enabled" value="1" <?php checked(1, $this->options['sticky_enabled']); ?> />
                                    <?php esc_html_e('Fix header to top of screen as the visitor scrolls down.', 'earthin-sticky-header'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Header CSS Selector', 'earthin-sticky-header'); ?></th>
                            <td>
                                <input type="text" name="header_selector" value="<?php echo esc_attr($this->options['header_selector']); ?>" class="regular-text" />
                                <p class="description"><?php esc_html_e('CSS selector for your theme header (e.g., header, .site-header, #masthead).', 'earthin-sticky-header'); ?></p>
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
                            <th scope="row"><?php esc_html_e('Progress Bar Color', 'earthin-sticky-header'); ?></th>
                            <td>
                                <input type="color" name="progress_color" value="<?php echo esc_attr($this->options['progress_color']); ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Scroll to Top Button', 'earthin-sticky-header'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="scroll_to_top" value="1" <?php checked(1, $this->options['scroll_to_top']); ?> />
                                    <?php esc_html_e('Show floating button to quickly return to top of page.', 'earthin-sticky-header'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" name="earthin_save_sticky" class="button button-primary" value="<?php esc_attr_e('Save Configuration', 'earthin-sticky-header'); ?>" />
                    </p>
                </form>
            </div>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Sticky_Header', 'get_instance']);
