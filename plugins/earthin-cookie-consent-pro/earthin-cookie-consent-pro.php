<?php
/**
 * Plugin Name: Earthin Cookie Consent Pro
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-cookie-consent-pro
 * Description: Premium add-on for Earthin Cookie Consent with Geo-targeting rules, consent audit trails, and advanced styling customizer.
 * Version: 1.0.0
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: earthin-cookie-consent-pro
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('EARTHIN_COOKIE_CONSENT_PRO_VERSION', '1.0.0');
define('EARTHIN_COOKIE_CONSENT_PRO_PATH', plugin_dir_path(__FILE__));

class Earthin_Cookie_Consent_Pro {
    private static $instance = null;
    private $option_name = 'earthin_cookie_consent_pro_settings';

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_init', [$this, 'check_parent_plugin']);
        add_action('admin_menu', [$this, 'add_pro_menu'], 20);
        add_action('wp_ajax_earthin_log_consent', [$this, 'ajax_log_consent']);
        add_action('wp_ajax_nopriv_earthin_log_consent', [$this, 'ajax_log_consent']);
        add_action('wp_footer', [$this, 'enqueue_pro_scripts']);
    }

    public function check_parent_plugin() {
        if (!class_exists('Earthin_Cookie_Consent')) {
            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-warning is-dismissible">
                    <p><?php esc_html_e('Earthin Cookie Consent Pro requires the core "Earthin Cookie Consent" plugin to be installed and active.', 'earthin-cookie-consent-pro'); ?></p>
                </div>
                <?php
            });
        }
    }

    public function add_pro_menu() {
        add_submenu_page(
            'options-general.php',
            __('Cookie Consent Pro', 'earthin-cookie-consent-pro'),
            __('Cookie Consent Pro', 'earthin-cookie-consent-pro'),
            'manage_options',
            'earthin-cookie-consent-pro',
            [$this, 'render_pro_page']
        );
    }

    public function default_settings() {
        return [
            'geo_targeting' => 'all', // 'all', 'eu_only'
            'audit_logging' => 1,
            'accent_color' => '#10b981',
            'custom_css' => '',
        ];
    }

    public function get_settings() {
        $saved = get_option($this->option_name, []);
        return wp_parse_args($saved, $this->default_settings());
    }

    public function ajax_log_consent() {
        check_ajax_referer('earthin_consent_pro_nonce', 'security');

        $settings = $this->get_settings();
        if (empty($settings['audit_logging'])) {
            wp_send_json_success();
        }

        $raw_prefs = sanitize_text_field($_POST['preferences'] ?? '{}');
        $ip = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $ip_hash = hash('sha256', $ip . wp_salt());

        $logs = get_option('earthin_cc_audit_logs', []);
        if (!is_array($logs)) {
            $logs = [];
        }

        array_unshift($logs, [
            'timestamp' => current_time('mysql'),
            'ip_hash' => substr($ip_hash, 0, 16) . '...',
            'preferences' => json_decode(stripslashes($raw_prefs), true),
            'user_agent' => sanitize_text_field(substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 100)),
        ]);

        if (count($logs) > 100) {
            $logs = array_slice($logs, 0, 100);
        }

        update_option('earthin_cc_audit_logs', $logs, false);
        wp_send_json_success();
    }

    public function enqueue_pro_scripts() {
        $nonce = wp_create_nonce('earthin_consent_pro_nonce');
        $ajax_url = admin_url('admin-ajax.php');
        $settings = $this->get_settings();

        if (!empty($settings['custom_css'])) {
            echo '<style>' . wp_strip_all_tags($settings['custom_css']) . '</style>';
        }
        ?>
        <script>
        window.addEventListener('earthin_consent_updated', function(e) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo esc_url($ajax_url); ?>', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('action=earthin_log_consent&security=<?php echo esc_js($nonce); ?>&preferences=' + encodeURIComponent(JSON.stringify(e.detail)));
        });
        </script>
        <?php
    }

    public function render_pro_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'earthin-cookie-consent-pro'));
        }

        if (isset($_POST['earthin_save_pro']) && check_admin_referer('earthin_pro_settings_nonce')) {
            $settings = [
                'geo_targeting' => in_array($_POST['geo_targeting'] ?? '', ['all', 'eu_only'], true) ? $_POST['geo_targeting'] : 'all',
                'audit_logging' => !empty($_POST['audit_logging']) ? 1 : 0,
                'accent_color' => sanitize_hex_color($_POST['accent_color'] ?? '#10b981') ?: '#10b981',
                'custom_css' => sanitize_textarea_field($_POST['custom_css'] ?? ''),
            ];
            update_option($this->option_name, $settings);
            echo '<div class="notice notice-success is-dismissible" style="border-left-color:#059669;"><p style="font-weight:600; color:#065f46;">' . esc_html__('Earthin Cookie Consent Pro settings saved successfully.', 'earthin-cookie-consent-pro') . '</p></div>';
        }

        $settings = $this->get_settings();
        $logs = get_option('earthin_cc_audit_logs', []);
        $total_logs = is_array($logs) ? count($logs) : 0;
        ?>
        <div class="wrap" style="max-width:1100px; margin:20px auto 40px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,sans-serif;">
            <!-- Header Banner -->
            <div style="display:flex; align-items:center; justify-content:space-between; background:linear-gradient(135deg, #059669 0%, #047857 100%); padding:22px 28px; border-radius:12px; color:#fff; box-shadow:0 4px 18px rgba(5,150,105,0.22); margin-bottom:24px;">
                <div style="display:flex; align-items:center; gap:16px;">
                    <div style="background:rgba(255,255,255,0.2); border-radius:12px; width:50px; height:50px; display:flex; align-items:center; justify-content:center; font-size:26px;">⭐</div>
                    <div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <h1 style="color:#fff; margin:0; font-size:22px; font-weight:700; line-height:1.2;"><?php esc_html_e('Earthin Cookie Consent Pro', 'earthin-cookie-consent-pro'); ?></h1>
                            <span style="background:#fef08a; color:#854d0e; font-size:11px; font-weight:800; padding:2px 8px; border-radius:10px; text-transform:uppercase;">PRO</span>
                        </div>
                        <p style="margin:4px 0 0; color:#d1fae5; font-size:13px;"><?php esc_html_e('Audit trails, geo-targeting policies, and advanced design overrides', 'earthin-cookie-consent-pro'); ?></p>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="background:rgba(255,255,255,0.18); padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600;">v<?php echo esc_html(EARTHIN_COOKIE_CONSENT_PRO_VERSION); ?></span>
                    <a href="<?php echo esc_url(admin_url('options-general.php?page=earthin-cookie-consent')); ?>" style="background:#fff; color:#047857; text-decoration:none; padding:6px 14px; border-radius:6px; font-size:12px; font-weight:600;">← Core Settings</a>
                </div>
            </div>

            <!-- Quick Stat Cards -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px; margin-bottom:24px;">
                <div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
                    <div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e('Audit Trail Records', 'earthin-cookie-consent-pro'); ?></div>
                    <div style="font-size:24px; font-weight:700; color:#059669; margin-top:4px;">
                        <?php echo esc_html($total_logs); ?> / 100
                    </div>
                    <div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e('Stored proof-of-consent logs', 'earthin-cookie-consent-pro'); ?></div>
                </div>

                <div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
                    <div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e('Geo-Targeting Rule', 'earthin-cookie-consent-pro'); ?></div>
                    <div style="font-size:18px; font-weight:700; color:#0f172a; margin-top:6px;">
                        <?php echo $settings['geo_targeting'] === 'eu_only' ? 'EU & UK Visitors Only' : 'Worldwide (All Visitors)'; ?>
                    </div>
                    <div style="font-size:12px; color:#10b981; margin-top:2px; font-weight:500;">✓ Active Filter</div>
                </div>

                <div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
                    <div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e('Audit Status', 'earthin-cookie-consent-pro'); ?></div>
                    <div style="font-size:18px; font-weight:700; color:<?php echo !empty($settings['audit_logging']) ? '#059669' : '#64748b'; ?>; margin-top:6px;">
                        <?php echo !empty($settings['audit_logging']) ? '● Real-Time Logging' : '○ Disabled'; ?>
                    </div>
                    <div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e('Anonymized SHA256 hashed records', 'earthin-cookie-consent-pro'); ?></div>
                </div>
            </div>

            <!-- Pro Configuration Card -->
            <div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
                <h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
                    <span>⚡</span> <?php esc_html_e('Pro Features Configuration', 'earthin-cookie-consent-pro'); ?>
                </h2>

                <form method="post">
                    <?php wp_nonce_field('earthin_pro_settings_nonce'); ?>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                        <div>
                            <label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;">
                                <?php esc_html_e('Geo-Targeting Policy', 'earthin-cookie-consent-pro'); ?>
                            </label>
                            <select name="geo_targeting" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;">
                                <option value="all" <?php selected($settings['geo_targeting'], 'all'); ?>><?php esc_html_e('Show Banner Worldwide to All Visitors', 'earthin-cookie-consent-pro'); ?></option>
                                <option value="eu_only" <?php selected($settings['geo_targeting'], 'eu_only'); ?>><?php esc_html_e('Target EU and UK Visitors Only (GDPR Mode)', 'earthin-cookie-consent-pro'); ?></option>
                            </select>
                            <p style="font-size:12px; color:#64748b; margin-top:4px;">
                                <?php esc_html_e('Reduces banner fatigue by only showing consent prompts where strictly legally mandated.', 'earthin-cookie-consent-pro'); ?>
                            </p>
                        </div>

                        <div>
                            <label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;">
                                <?php esc_html_e('Proof of Consent Logging', 'earthin-cookie-consent-pro'); ?>
                            </label>
                            <label style="display:flex; align-items:flex-start; gap:10px; padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; cursor:pointer;">
                                <input type="checkbox" name="audit_logging" value="1" <?php checked($settings['audit_logging'], 1); ?> style="margin-top:2px;" />
                                <div>
                                    <strong style="font-size:13px; color:#0f172a; display:block;"><?php esc_html_e('Record anonymous consent events', 'earthin-cookie-consent-pro'); ?></strong>
                                    <span style="font-size:11px; color:#64748b;">(Stores anonymized client tokens for GDPR compliance audits)</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;">
                            <?php esc_html_e('Custom CSS Overrides', 'earthin-cookie-consent-pro'); ?>
                        </label>
                        <textarea name="custom_css" rows="4" class="large-text" style="font-family:monospace; font-size:12px; padding:10px; border-radius:6px; border:1px solid #cbd5e1; background:#f8fafc;" placeholder="#earthin-cc-banner { border-radius: 16px; }"><?php echo esc_textarea($settings['custom_css']); ?></textarea>
                    </div>

                    <button type="submit" name="earthin_save_pro" class="button button-primary" style="background:#059669; border-color:#047857; color:#fff; font-weight:600; padding:8px 24px; height:auto; border-radius:6px; font-size:14px; box-shadow:0 2px 4px rgba(5,150,105,0.25);">
                        <?php esc_html_e('Save Pro Settings', 'earthin-cookie-consent-pro'); ?>
                    </button>
                </form>
            </div>

            <!-- Audit Trail Log Table Card -->
            <div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03);">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9;">
                    <h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0; display:flex; align-items:center; gap:8px;">
                        <span>📜</span> <?php esc_html_e('Consent Audit Trail (Proof of Consent)', 'earthin-cookie-consent-pro'); ?>
                    </h2>
                    <span style="font-size:12px; color:#64748b;"><?php esc_html_e('Showing latest 25 events', 'earthin-cookie-consent-pro'); ?></span>
                </div>

                <?php if (empty($logs)): ?>
                    <div style="text-align:center; padding:32px 16px; color:#94a3b8;">
                        <span style="font-size:32px; display:block; margin-bottom:8px;">🍃</span>
                        <p style="font-size:14px; margin:0;"><?php esc_html_e('No consent records logged yet. Records will appear here as visitors interact with the banner.', 'earthin-cookie-consent-pro'); ?></p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse; text-align:left; font-size:12px;">
                            <thead>
                                <tr style="background:#f8fafc; border-bottom:2px solid #e2e8f0; color:#475569;">
                                    <th style="padding:10px 12px; font-weight:600;"><?php esc_html_e('Timestamp', 'earthin-cookie-consent-pro'); ?></th>
                                    <th style="padding:10px 12px; font-weight:600;"><?php esc_html_e('Anonymized SHA256 Hash', 'earthin-cookie-consent-pro'); ?></th>
                                    <th style="padding:10px 12px; font-weight:600;"><?php esc_html_e('Category Decisions', 'earthin-cookie-consent-pro'); ?></th>
                                    <th style="padding:10px 12px; font-weight:600;"><?php esc_html_e('User Agent', 'earthin-cookie-consent-pro'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($logs, 0, 25) as $log): ?>
                                    <tr style="border-bottom:1px solid #f1f5f9;">
                                        <td style="padding:10px 12px; color:#0f172a; white-space:nowrap;"><?php echo esc_html($log['timestamp']); ?></td>
                                        <td style="padding:10px 12px;"><code style="background:#f1f5f9; padding:2px 6px; border-radius:4px; font-size:11px;"><?php echo esc_html($log['ip_hash']); ?></code></td>
                                        <td style="padding:10px 12px;">
                                            <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                                <?php
                                                $prefs = $log['preferences'] ?? [];
                                                foreach ($prefs as $k => $v) {
                                                    $bg = $v ? '#dcfce7' : '#fee2e2';
                                                    $fg = $v ? '#166534' : '#991b1b';
                                                    echo '<span style="background:' . $bg . '; color:' . $fg . '; padding:2px 6px; border-radius:3px; font-size:10px; font-weight:600;">' . esc_html($k) . '</span>';
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <td style="padding:10px 12px; color:#64748b; font-size:11px; max-width:240px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                            <?php echo esc_html($log['user_agent']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Cookie_Consent_Pro', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-cookie-consent-pro')) . '">' . __('Settings', 'earthin-cookie-consent-pro') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
