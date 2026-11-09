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
            echo '<div class="updated"><p>' . esc_html__('Pro settings saved.', 'earthin-cookie-consent-pro') . '</p></div>';
        }

        if (isset($_POST['earthin_export_audit']) && check_admin_referer('earthin_pro_settings_nonce')) {
            // Handled or displayed
        }

        $settings = $this->get_settings();
        $logs = get_option('earthin_cc_audit_logs', []);
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Earthin Cookie Consent Pro Settings', 'earthin-cookie-consent-pro'); ?></h1>
            <p><?php esc_html_e('Advanced compliance logging, geo-targeting policies, and styling customizer.', 'earthin-cookie-consent-pro'); ?></p>

            <form method="post">
                <?php wp_nonce_field('earthin_pro_settings_nonce'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('Geo-Targeting Policy', 'earthin-cookie-consent-pro'); ?></th>
                        <td>
                            <select name="geo_targeting">
                                <option value="all" <?php selected($settings['geo_targeting'], 'all'); ?>><?php esc_html_e('Show Banner Worldwide to All Visitors', 'earthin-cookie-consent-pro'); ?></option>
                                <option value="eu_only" <?php selected($settings['geo_targeting'], 'eu_only'); ?>><?php esc_html_e('Target EU and UK Visitors Only (GDPR Mode)', 'earthin-cookie-consent-pro'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Consent Audit Trail', 'earthin-cookie-consent-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="audit_logging" value="1" <?php checked($settings['audit_logging'], 1); ?> />
                                <?php esc_html_e('Record anonymous proof of consent events (essential for GDPR audit readiness)', 'earthin-cookie-consent-pro'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Custom CSS', 'earthin-cookie-consent-pro'); ?></th>
                        <td>
                            <textarea name="custom_css" rows="4" class="large-text code"><?php echo esc_textarea($settings['custom_css']); ?></textarea>
                            <p class="description"><?php esc_html_e('Inject custom CSS rules to seamlessly blend the cookie banner with your theme.', 'earthin-cookie-consent-pro'); ?></p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="earthin_save_pro" class="button button-primary" value="<?php esc_attr_e('Save Pro Settings', 'earthin-cookie-consent-pro'); ?>" />
                </p>
            </form>

            <hr>
            <h2><?php esc_html_e('Consent Audit Trail (Proof of Consent)', 'earthin-cookie-consent-pro'); ?></h2>
            <?php if (empty($logs)): ?>
                <p><?php esc_html_e('No consent records logged yet.', 'earthin-cookie-consent-pro'); ?></p>
            <?php else: ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Timestamp', 'earthin-cookie-consent-pro'); ?></th>
                            <th><?php esc_html_e('Anonymized Hash', 'earthin-cookie-consent-pro'); ?></th>
                            <th><?php esc_html_e('Consent Decision', 'earthin-cookie-consent-pro'); ?></th>
                            <th><?php esc_html_e('Client Agent', 'earthin-cookie-consent-pro'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($logs, 0, 25) as $log): ?>
                            <tr>
                                <td><?php echo esc_html($log['timestamp']); ?></td>
                                <td><code><?php echo esc_html($log['ip_hash']); ?></code></td>
                                <td>
                                    <?php
                                    $prefs = $log['preferences'] ?? [];
                                    $parts = [];
                                    foreach ($prefs as $k => $v) {
                                        $parts[] = esc_html($k) . ': ' . ($v ? '<span style="color:green">Yes</span>' : '<span style="color:red">No</span>');
                                    }
                                    echo implode(' | ', $parts);
                                    ?>
                                </td>
                                <td><span style="font-size:12px;"><?php echo esc_html($log['user_agent']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Cookie_Consent_Pro', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function() {
     = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-cookie-consent-pro')) . '">' . __('Settings', 'earthin-cookie-consent-pro') . '</a>';
    array_unshift(, );
    return ;
});
