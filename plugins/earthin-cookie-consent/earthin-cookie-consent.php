<?php
/**
 * Plugin Name: Earthin Cookie Consent
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-cookie-consent
 * Description: Lightweight, GDPR and CCPA compliant cookie notice banner with granular category controls and script blocking.
 * Version: 1.0.0
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: earthin-cookie-consent
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('EARTHIN_COOKIE_CONSENT_VERSION', '1.0.0');
define('EARTHIN_COOKIE_CONSENT_PATH', plugin_dir_path(__FILE__));
define('EARTHIN_COOKIE_CONSENT_URL', plugin_dir_url(__FILE__));

class Earthin_Cookie_Consent {
    private static $instance = null;
    private $option_name = 'earthin_cookie_consent_settings';

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_footer', [$this, 'render_banner_markup']);
        add_shortcode('earthin_cookie_settings', [$this, 'render_settings_shortcode']);
    }

    public function default_settings() {
        return [
            'enabled' => 1,
            'banner_position' => 'bottom', // 'bottom', 'top', 'bottom-right'
            'banner_theme' => 'dark',      // 'dark', 'light'
            'message' => 'We use cookies on our website to give you the most relevant experience by remembering your preferences and repeat visits. By clicking "Accept All", you consent to the use of all cookies.',
            'accept_all_text' => 'Accept All',
            'reject_all_text' => 'Reject Non-Essential',
            'customize_text' => 'Customize Preferences',
            'save_preferences_text' => 'Save Preferences',
            'privacy_policy_url' => '',
            'enable_analytics_category' => 1,
            'enable_marketing_category' => 1,
            'enable_functional_category' => 1,
            'cookie_expiry_days' => 180,
        ];
    }

    public function get_settings() {
        $saved = get_option($this->option_name, []);
        return wp_parse_args($saved, $this->default_settings());
    }

    public function add_admin_menu() {
        add_options_page(
            __('Earthin Cookie Consent', 'earthin-cookie-consent'),
            __('Cookie Consent', 'earthin-cookie-consent'),
            'manage_options',
            'earthin-cookie-consent',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting('earthin_cookie_consent_group', $this->option_name, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings'],
            'default' => $this->default_settings(),
        ]);
    }

    public function sanitize_settings($input) {
        $defaults = $this->default_settings();
        $sanitized = [];

        $sanitized['enabled'] = !empty($input['enabled']) ? 1 : 0;
        $sanitized['banner_position'] = in_array($input['banner_position'] ?? '', ['bottom', 'top', 'bottom-right'], true) ? $input['banner_position'] : 'bottom';
        $sanitized['banner_theme'] = in_array($input['banner_theme'] ?? '', ['dark', 'light'], true) ? $input['banner_theme'] : 'dark';
        $sanitized['message'] = sanitize_textarea_field($input['message'] ?? $defaults['message']);
        $sanitized['accept_all_text'] = sanitize_text_field($input['accept_all_text'] ?? $defaults['accept_all_text']);
        $sanitized['reject_all_text'] = sanitize_text_field($input['reject_all_text'] ?? $defaults['reject_all_text']);
        $sanitized['customize_text'] = sanitize_text_field($input['customize_text'] ?? $defaults['customize_text']);
        $sanitized['save_preferences_text'] = sanitize_text_field($input['save_preferences_text'] ?? $defaults['save_preferences_text']);
        $sanitized['privacy_policy_url'] = esc_url_raw($input['privacy_policy_url'] ?? '');
        $sanitized['enable_analytics_category'] = !empty($input['enable_analytics_category']) ? 1 : 0;
        $sanitized['enable_marketing_category'] = !empty($input['enable_marketing_category']) ? 1 : 0;
        $sanitized['enable_functional_category'] = !empty($input['enable_functional_category']) ? 1 : 0;
        $sanitized['cookie_expiry_days'] = max(1, min(730, intval($input['cookie_expiry_days'] ?? 180)));

        return $sanitized;
    }

    public function render_settings_shortcode() {
        return '<button type="button" class="earthin-cc-reopen-btn" onclick="window.EarthinCookieConsent && window.EarthinCookieConsent.openModal()">' . esc_html__('Manage Cookie Preferences', 'earthin-cookie-consent') . '</button>';
    }

    public function render_banner_markup() {
        $settings = $this->get_settings();
        if (empty($settings['enabled'])) {
            return;
        }

        $is_dark = $settings['banner_theme'] === 'dark';
        $bg_color = $is_dark ? '#0f172a' : '#ffffff';
        $text_color = $is_dark ? '#f8fafc' : '#1e293b';
        $border_color = $is_dark ? '#1e293b' : '#e2e8f0';
        $pos = $settings['banner_position'];

        $pos_styles = 'bottom: 0; left: 0; right: 0;';
        if ($pos === 'top') {
            $pos_styles = 'top: 0; left: 0; right: 0;';
        } elseif ($pos === 'bottom-right') {
            $pos_styles = 'bottom: 24px; right: 24px; max-width: 440px; border-radius: 12px;';
        }
        ?>
        <style>
            #earthin-cc-banner {
                position: fixed;
                <?php echo $pos_styles; ?>
                background: <?php echo $bg_color; ?>;
                color: <?php echo $text_color; ?>;
                border: 1px solid <?php echo $border_color; ?>;
                padding: 20px 24px;
                z-index: 999998;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                box-shadow: 0 10px 30px rgba(0,0,0,0.25);
                display: none;
            }
            .earthin-cc-content {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                max-width: 1200px;
                margin: 0 auto;
            }
            .earthin-cc-text {
                flex: 1 1 500px;
                font-size: 14px;
                line-height: 1.6;
            }
            .earthin-cc-buttons {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }
            .earthin-cc-btn {
                padding: 10px 18px;
                font-size: 13px;
                font-weight: 600;
                border-radius: 6px;
                cursor: pointer;
                border: 1px solid transparent;
                transition: opacity 0.2s;
            }
            .earthin-cc-btn:hover { opacity: 0.9; }
            .earthin-cc-btn-primary { background: #10b981; color: #ffffff; }
            .earthin-cc-btn-secondary { background: #64748b; color: #ffffff; }
            .earthin-cc-btn-outline { background: transparent; border-color: <?php echo $border_color; ?>; color: <?php echo $text_color; ?>; }
            /* Modal */
            #earthin-cc-modal {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.6);
                z-index: 999999;
                align-items: center;
                justify-content: center;
                padding: 16px;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            }
            .earthin-cc-modal-box {
                background: <?php echo $bg_color; ?>;
                color: <?php echo $text_color; ?>;
                border-radius: 12px;
                max-width: 580px;
                width: 100%;
                padding: 24px;
                max-height: 90vh;
                overflow-y: auto;
                box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            }
            .earthin-cc-category-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px 0;
                border-bottom: 1px solid <?php echo $border_color; ?>;
            }
        </style>

        <div id="earthin-cc-banner" role="region" aria-label="<?php esc_attr_e('Cookie Consent Banner', 'earthin-cookie-consent'); ?>">
            <div class="earthin-cc-content">
                <div class="earthin-cc-text">
                    <?php echo esc_html($settings['message']); ?>
                    <?php if (!empty($settings['privacy_policy_url'])): ?>
                        <a href="<?php echo esc_url($settings['privacy_policy_url']); ?>" target="_blank" style="color: #10b981; text-decoration: underline; margin-left: 6px;"><?php esc_html_e('Learn More', 'earthin-cookie-consent'); ?></a>
                    <?php endif; ?>
                </div>
                <div class="earthin-cc-buttons">
                    <button type="button" class="earthin-cc-btn earthin-cc-btn-primary" onclick="EarthinCookieConsent.acceptAll()"><?php echo esc_html($settings['accept_all_text']); ?></button>
                    <button type="button" class="earthin-cc-btn earthin-cc-btn-secondary" onclick="EarthinCookieConsent.rejectAll()"><?php echo esc_html($settings['reject_all_text']); ?></button>
                    <button type="button" class="earthin-cc-btn earthin-cc-btn-outline" onclick="EarthinCookieConsent.openModal()"><?php echo esc_html($settings['customize_text']); ?></button>
                </div>
            </div>
        </div>

        <div id="earthin-cc-modal">
            <div class="earthin-cc-modal-box">
                <h3 style="margin-top:0;"><?php esc_html_e('Customize Cookie Preferences', 'earthin-cookie-consent'); ?></h3>
                <p style="font-size:13px; opacity:0.8;"><?php esc_html_e('Manage your settings for cookies used on this site. You can enable or disable different types below.', 'earthin-cookie-consent'); ?></p>

                <div class="earthin-cc-category-row">
                    <div>
                        <strong><?php esc_html_e('Necessary Cookies', 'earthin-cookie-consent'); ?></strong>
                        <div style="font-size:12px; opacity:0.75;"><?php esc_html_e('Essential for core security and navigation.', 'earthin-cookie-consent'); ?></div>
                    </div>
                    <div><input type="checkbox" checked disabled /> <em><?php esc_html_e('Always Active', 'earthin-cookie-consent'); ?></em></div>
                </div>

                <?php if (!empty($settings['enable_analytics_category'])): ?>
                <div class="earthin-cc-category-row">
                    <div>
                        <strong><?php esc_html_e('Analytics Cookies', 'earthin-cookie-consent'); ?></strong>
                        <div style="font-size:12px; opacity:0.75;"><?php esc_html_e('Help us understand visitor interaction and metrics.', 'earthin-cookie-consent'); ?></div>
                    </div>
                    <div><input type="checkbox" id="earthin-cat-analytics" /></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($settings['enable_marketing_category'])): ?>
                <div class="earthin-cc-category-row">
                    <div>
                        <strong><?php esc_html_e('Marketing Cookies', 'earthin-cookie-consent'); ?></strong>
                        <div style="font-size:12px; opacity:0.75;"><?php esc_html_e('Used to deliver personalized campaigns and ads.', 'earthin-cookie-consent'); ?></div>
                    </div>
                    <div><input type="checkbox" id="earthin-cat-marketing" /></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($settings['enable_functional_category'])): ?>
                <div class="earthin-cc-category-row">
                    <div>
                        <strong><?php esc_html_e('Functional Cookies', 'earthin-cookie-consent'); ?></strong>
                        <div style="font-size:12px; opacity:0.75;"><?php esc_html_e('Enable enhanced personalization and embedded widgets.', 'earthin-cookie-consent'); ?></div>
                    </div>
                    <div><input type="checkbox" id="earthin-cat-functional" /></div>
                </div>
                <?php endif; ?>

                <div style="margin-top: 24px; display:flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="earthin-cc-btn earthin-cc-btn-outline" onclick="EarthinCookieConsent.closeModal()"><?php esc_html_e('Cancel', 'earthin-cookie-consent'); ?></button>
                    <button type="button" class="earthin-cc-btn earthin-cc-btn-primary" onclick="EarthinCookieConsent.saveCustom()"><?php echo esc_html($settings['save_preferences_text']); ?></button>
                </div>
            </div>
        </div>

        <script>
        window.EarthinCookieConsent = (function() {
            var COOKIE_NAME = 'earthin_cookie_consent';
            var EXPIRY_DAYS = <?php echo intval($settings['cookie_expiry_days']); ?>;

            function getCookie(name) {
                var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
                return v ? decodeURIComponent(v[2]) : null;
            }

            function setCookie(name, val, days) {
                var d = new Date();
                d.setTime(d.getTime() + 24*60*60*1000*days);
                document.cookie = name + "=" + encodeURIComponent(val) + ";path=/;expires=" + d.toGMTString() + ";SameSite=Lax";
            }

            function executeScripts(categories) {
                var scripts = document.querySelectorAll('script[type="text/plain"][data-earthin-category]');
                scripts.forEach(function(s) {
                    var cat = s.getAttribute('data-earthin-category');
                    if (categories[cat]) {
                        var newScript = document.createElement('script');
                        if (s.src) {
                            newScript.src = s.src;
                        } else {
                            newScript.textContent = s.textContent;
                        }
                        document.head.appendChild(newScript);
                        s.remove();
                    }
                });
            }

            function applyConsent(prefs) {
                setCookie(COOKIE_NAME, JSON.stringify(prefs), EXPIRY_DAYS);
                var banner = document.getElementById('earthin-cc-banner');
                if (banner) banner.style.display = 'none';
                executeScripts(prefs);
                window.dispatchEvent(new CustomEvent('earthin_consent_updated', { detail: prefs }));
            }

            function init() {
                var val = getCookie(COOKIE_NAME);
                if (!val) {
                    var banner = document.getElementById('earthin-cc-banner');
                    if (banner) banner.style.display = 'block';
                } else {
                    try {
                        var prefs = JSON.parse(val);
                        executeScripts(prefs);
                    } catch(e) {}
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }

            return {
                acceptAll: function() {
                    applyConsent({ necessary: true, analytics: true, marketing: true, functional: true });
                },
                rejectAll: function() {
                    applyConsent({ necessary: true, analytics: false, marketing: false, functional: false });
                },
                openModal: function() {
                    var m = document.getElementById('earthin-cc-modal');
                    if (m) m.style.display = 'flex';
                },
                closeModal: function() {
                    var m = document.getElementById('earthin-cc-modal');
                    if (m) m.style.display = 'none';
                },
                saveCustom: function() {
                    var analytics = document.getElementById('earthin-cat-analytics');
                    var marketing = document.getElementById('earthin-cat-marketing');
                    var functional = document.getElementById('earthin-cat-functional');
                    var prefs = {
                        necessary: true,
                        analytics: analytics ? analytics.checked : false,
                        marketing: marketing ? marketing.checked : false,
                        functional: functional ? functional.checked : false
                    };
                    applyConsent(prefs);
                    this.closeModal();
                }
            };
        })();
        </script>
        <?php
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'earthin-cookie-consent'));
        }
        $settings = $this->get_settings();
        $is_enabled = !empty($settings['enabled']);
        ?>
        <div class="wrap" style="max-width:1100px; margin:20px auto 40px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,sans-serif;">
            <!-- Header Banner -->
            <div style="display:flex; align-items:center; justify-content:space-between; background:linear-gradient(135deg, #059669 0%, #047857 100%); padding:22px 28px; border-radius:12px; color:#fff; box-shadow:0 4px 18px rgba(5,150,105,0.22); margin-bottom:24px;">
                <div style="display:flex; align-items:center; gap:16px;">
                    <div style="background:rgba(255,255,255,0.2); border-radius:12px; width:50px; height:50px; display:flex; align-items:center; justify-content:center; font-size:26px;">🍪</div>
                    <div>
                        <h1 style="color:#fff; margin:0; font-size:22px; font-weight:700; line-height:1.2;"><?php esc_html_e('Earthin Cookie Consent', 'earthin-cookie-consent'); ?></h1>
                        <p style="margin:4px 0 0; color:#d1fae5; font-size:13px;"><?php esc_html_e('GDPR & CCPA compliant cookie notice banner with granular category controls', 'earthin-cookie-consent'); ?></p>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="background:rgba(255,255,255,0.18); padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600;">v<?php echo esc_html(EARTHIN_COOKIE_CONSENT_VERSION); ?></span>
                    <a href="https://plugins.earthin.online/docs" target="_blank" style="background:#fff; color:#047857; text-decoration:none; padding:6px 14px; border-radius:6px; font-size:12px; font-weight:600;">Documentation</a>
                </div>
            </div>

            <!-- Metric Cards -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px; margin-bottom:24px;">
                <div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
                    <div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e('Banner Status', 'earthin-cookie-consent'); ?></div>
                    <div style="font-size:20px; font-weight:700; margin-top:4px; color:<?php echo $is_enabled ? '#059669' : '#94a3b8'; ?>;">
                        <?php echo $is_enabled ? '● Active Front-End' : '○ Disabled'; ?>
                    </div>
                    <div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e('Displays to first-time visitors', 'earthin-cookie-consent'); ?></div>
                </div>

                <div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
                    <div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e('Compliance Standards', 'earthin-cookie-consent'); ?></div>
                    <div style="font-size:20px; font-weight:700; color:#0f172a; margin-top:4px;">GDPR + CCPA</div>
                    <div style="font-size:12px; color:#10b981; margin-top:2px; font-weight:500;">✓ Script Blocking Enabled</div>
                </div>

                <div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
                    <div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e('Cookie Memory', 'earthin-cookie-consent'); ?></div>
                    <div style="font-size:20px; font-weight:700; color:#0f172a; margin-top:4px;">
                        <?php echo esc_html($settings['cookie_expiry_days']); ?> <?php esc_html_e('Days', 'earthin-cookie-consent'); ?>
                    </div>
                    <div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e('Consent duration on client device', 'earthin-cookie-consent'); ?></div>
                </div>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields('earthin_cookie_consent_group'); ?>

                <div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">
                    <!-- Left Form Area -->
                    <div>
                        <!-- Appearance & Layout Card -->
                        <div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
                            <h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
                                <span>🎨</span> <?php esc_html_e('Banner Layout & Design', 'earthin-cookie-consent'); ?>
                            </h2>

                            <label style="display:flex; align-items:center; gap:10px; margin-bottom:16px; cursor:pointer;">
                                <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[enabled]" value="1" <?php checked($settings['enabled'], 1); ?> />
                                <span style="font-size:14px; font-weight:600; color:#0f172a;"><?php esc_html_e('Display Banner on Front-End', 'earthin-cookie-consent'); ?></span>
                            </label>

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                                <div>
                                    <label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e('Position', 'earthin-cookie-consent'); ?></label>
                                    <select name="<?php echo esc_attr($this->option_name); ?>[banner_position]" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;">
                                        <option value="bottom" <?php selected($settings['banner_position'], 'bottom'); ?>><?php esc_html_e('Bottom Bar (Full width)', 'earthin-cookie-consent'); ?></option>
                                        <option value="top" <?php selected($settings['banner_position'], 'top'); ?>><?php esc_html_e('Top Bar (Full width)', 'earthin-cookie-consent'); ?></option>
                                        <option value="bottom-right" <?php selected($settings['banner_position'], 'bottom-right'); ?>><?php esc_html_e('Floating Box (Bottom Right)', 'earthin-cookie-consent'); ?></option>
                                    </select>
                                </div>

                                <div>
                                    <label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e('Theme Style', 'earthin-cookie-consent'); ?></label>
                                    <select name="<?php echo esc_attr($this->option_name); ?>[banner_theme]" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;">
                                        <option value="dark" <?php selected($settings['banner_theme'], 'dark'); ?>><?php esc_html_e('Dark Slate (#0f172a)', 'earthin-cookie-consent'); ?></option>
                                        <option value="light" <?php selected($settings['banner_theme'], 'light'); ?>><?php esc_html_e('Clean White (#ffffff)', 'earthin-cookie-consent'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div style="margin-bottom:16px;">
                                <label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e('Banner Notice Text', 'earthin-cookie-consent'); ?></label>
                                <textarea class="large-text" rows="3" name="<?php echo esc_attr($this->option_name); ?>[message]" style="padding:10px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px; line-height:1.5;"><?php echo esc_textarea($settings['message']); ?></textarea>
                            </div>

                            <div style="display:grid; grid-template-columns:2fr 1fr; gap:16px;">
                                <div>
                                    <label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e('Privacy Policy URL', 'earthin-cookie-consent'); ?></label>
                                    <input type="url" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;" name="<?php echo esc_attr($this->option_name); ?>[privacy_policy_url]" value="<?php echo esc_attr($settings['privacy_policy_url']); ?>" placeholder="https://example.com/privacy-policy" />
                                </div>
                                <div>
                                    <label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e('Consent Expiry (Days)', 'earthin-cookie-consent'); ?></label>
                                    <input type="number" min="1" max="730" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;" name="<?php echo esc_attr($this->option_name); ?>[cookie_expiry_days]" value="<?php echo esc_attr($settings['cookie_expiry_days']); ?>" />
                                </div>
                            </div>
                        </div>

                        <!-- Categories Card -->
                        <div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
                            <h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
                                <span>🛡️</span> <?php esc_html_e('Consent Categories & Script Blocking', 'earthin-cookie-consent'); ?>
                            </h2>

                            <div style="display:flex; flex-direction:column; gap:12px;">
                                <label style="display:flex; align-items:flex-start; gap:12px; padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer;">
                                    <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[enable_analytics_category]" value="1" <?php checked($settings['enable_analytics_category'], 1); ?> style="margin-top:3px;" />
                                    <div>
                                        <strong style="font-size:13px; color:#0f172a; display:block;"><?php esc_html_e('Analytics Cookies', 'earthin-cookie-consent'); ?></strong>
                                        <span style="font-size:12px; color:#64748b;">Google Analytics, Plausible, Matomo, PostHog, etc.</span>
                                    </div>
                                </label>

                                <label style="display:flex; align-items:flex-start; gap:12px; padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer;">
                                    <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[enable_marketing_category]" value="1" <?php checked($settings['enable_marketing_category'], 1); ?> style="margin-top:3px;" />
                                    <div>
                                        <strong style="font-size:13px; color:#0f172a; display:block;"><?php esc_html_e('Marketing & Ads Cookies', 'earthin-cookie-consent'); ?></strong>
                                        <span style="font-size:12px; color:#64748b;">Facebook Pixel, Google Ads, TikTok Pixel, LinkedIn Insight Tag</span>
                                    </div>
                                </label>

                                <label style="display:flex; align-items:flex-start; gap:12px; padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer;">
                                    <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[enable_functional_category]" value="1" <?php checked($settings['enable_functional_category'], 1); ?> style="margin-top:3px;" />
                                    <div>
                                        <strong style="font-size:13px; color:#0f172a; display:block;"><?php esc_html_e('Functional Preferences', 'earthin-cookie-consent'); ?></strong>
                                        <span style="font-size:12px; color:#64748b;">Language selectors, currency converters, personalized preferences</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="button button-primary" style="background:#059669; border-color:#047857; color:#fff; font-weight:600; padding:8px 24px; height:auto; border-radius:6px; font-size:14px; box-shadow:0 2px 4px rgba(5,150,105,0.25);">
                            <?php esc_html_e('Save Cookie Consent Settings', 'earthin-cookie-consent'); ?>
                        </button>
                    </div>

                    <!-- Sidebar Info -->
                    <div>
                        <div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:20px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:20px;">
                            <h3 style="font-size:14px; font-weight:700; color:#0f172a; margin:0 0 10px;"><?php esc_html_e('Shortcode for Footer', 'earthin-cookie-consent'); ?></h3>
                            <p style="font-size:12px; color:#64748b; line-height:1.5; margin:0 0 10px;">
                                <?php esc_html_e('Insert this shortcode into your site footer or privacy policy page to let users reopen the consent modal anytime:', 'earthin-cookie-consent'); ?>
                            </p>
                            <code style="display:block; background:#f1f5f9; padding:8px 12px; border-radius:6px; font-size:12px; color:#0f172a; user-select:all;">[earthin_cookie_settings]</code>
                        </div>

                        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:18px;">
                            <div style="font-size:13px; font-weight:700; color:#166534; margin-bottom:8px;">💡 <?php esc_html_e('Script Blocking Syntax', 'earthin-cookie-consent'); ?></div>
                            <p style="font-size:12px; color:#15803d; line-height:1.5; margin:0 0 8px;">
                                <?php esc_html_e('To block scripts until consent is given, change script tags from:', 'earthin-cookie-consent'); ?>
                            </p>
                            <code style="display:block; background:#dcfce7; padding:6px 10px; border-radius:4px; font-size:11px; color:#166534; word-break:break-all;">&lt;script type="text/plain" data-earthin-category="analytics" src="..."&gt;</code>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Cookie_Consent', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-cookie-consent')) . '">' . __('Settings', 'earthin-cookie-consent') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
