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
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Earthin Cookie Consent Settings', 'earthin-cookie-consent'); ?></h1>
            <p><?php esc_html_e('Configure GDPR and CCPA compliant cookie notice banners and consent categories.', 'earthin-cookie-consent'); ?></p>

            <form method="post" action="options.php">
                <?php settings_fields('earthin_cookie_consent_group'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('Enable Cookie Banner', 'earthin-cookie-consent'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[enabled]" value="1" <?php checked($settings['enabled'], 1); ?> />
                                <?php esc_html_e('Display banner on frontend for unconsented visitors', 'earthin-cookie-consent'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Banner Position', 'earthin-cookie-consent'); ?></th>
                        <td>
                            <select name="<?php echo esc_attr($this->option_name); ?>[banner_position]">
                                <option value="bottom" <?php selected($settings['banner_position'], 'bottom'); ?>><?php esc_html_e('Bottom Bar (Full width)', 'earthin-cookie-consent'); ?></option>
                                <option value="top" <?php selected($settings['banner_position'], 'top'); ?>><?php esc_html_e('Top Bar (Full width)', 'earthin-cookie-consent'); ?></option>
                                <option value="bottom-right" <?php selected($settings['banner_position'], 'bottom-right'); ?>><?php esc_html_e('Floating Box (Bottom Right)', 'earthin-cookie-consent'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Theme Style', 'earthin-cookie-consent'); ?></th>
                        <td>
                            <select name="<?php echo esc_attr($this->option_name); ?>[banner_theme]">
                                <option value="dark" <?php selected($settings['banner_theme'], 'dark'); ?>><?php esc_html_e('Dark', 'earthin-cookie-consent'); ?></option>
                                <option value="light" <?php selected($settings['banner_theme'], 'light'); ?>><?php esc_html_e('Light', 'earthin-cookie-consent'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Banner Notice Text', 'earthin-cookie-consent'); ?></th>
                        <td>
                            <textarea class="large-text" rows="3" name="<?php echo esc_attr($this->option_name); ?>[message]"><?php echo esc_textarea($settings['message']); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Privacy Policy URL', 'earthin-cookie-consent'); ?></th>
                        <td>
                            <input type="url" class="regular-text" name="<?php echo esc_attr($this->option_name); ?>[privacy_policy_url]" value="<?php echo esc_attr($settings['privacy_policy_url']); ?>" placeholder="https://example.com/privacy-policy" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Cookie Expiry', 'earthin-cookie-consent'); ?></th>
                        <td>
                            <input type="number" min="1" max="730" name="<?php echo esc_attr($this->option_name); ?>[cookie_expiry_days]" value="<?php echo esc_attr($settings['cookie_expiry_days']); ?>" />
                            <span><?php esc_html_e('days', 'earthin-cookie-consent'); ?></span>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e('Categories & Script Blocking', 'earthin-cookie-consent'); ?></h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('Granular Categories', 'earthin-cookie-consent'); ?></th>
                        <td>
                            <label><input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[enable_analytics_category]" value="1" <?php checked($settings['enable_analytics_category'], 1); ?> /> <?php esc_html_e('Enable Analytics Category', 'earthin-cookie-consent'); ?></label><br>
                            <label><input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[enable_marketing_category]" value="1" <?php checked($settings['enable_marketing_category'], 1); ?> /> <?php esc_html_e('Enable Marketing Category', 'earthin-cookie-consent'); ?></label><br>
                            <label><input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[enable_functional_category]" value="1" <?php checked($settings['enable_functional_category'], 1); ?> /> <?php esc_html_e('Enable Functional Category', 'earthin-cookie-consent'); ?></label>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Cookie_Consent', 'get_instance']);
