<?php
/**
 * Plugin Name: Earthin Login Shield
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-login-shield
 * Description: Robust brute-force protection, login rate limiting, IP lockouts, and XML-RPC defense for WordPress.
 * Version: 1.0.0
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: earthin-login-shield
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('EARTHIN_LOGIN_SHIELD_VERSION', '1.0.0');
define('EARTHIN_LOGIN_SHIELD_PATH', plugin_dir_path(__FILE__));
define('EARTHIN_LOGIN_SHIELD_URL', plugin_dir_url(__FILE__));

class Earthin_Login_Shield {
    private static $instance = null;
    private $option_name = 'earthin_login_shield_settings';
    private $transient_prefix = 'earthin_ls_';

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_filter('authenticate', [$this, 'check_lockout_before_auth'], 25, 3);
        add_action('wp_login_failed', [$this, 'record_login_failure']);
        add_action('wp_login', [$this, 'clear_login_attempts'], 10, 2);
        add_filter('xmlrpc_enabled', [$this, 'filter_xmlrpc']);
    }

    public function default_settings() {
        return [
            'allowed_retries' => 4,
            'lockout_duration' => 20, // minutes
            'max_lockouts' => 3,
            'long_lockout_duration' => 24, // hours
            'disable_xmlrpc' => 1,
            'whitelist_ips' => '',
            'blacklist_ips' => '',
            'notify_admin_on_lockout' => 0,
        ];
    }

    public function get_settings() {
        $saved = get_option($this->option_name, []);
        return wp_parse_args($saved, $this->default_settings());
    }

    public function get_client_ip() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $candidate = trim($parts[0]);
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                $ip = $candidate;
            }
        }
        return sanitize_text_field($ip);
    }

    public function check_lockout_before_auth($user, $username, $password) {
        if (empty($username) && empty($password)) {
            return $user;
        }

        $ip = $this->get_client_ip();
        $settings = $this->get_settings();

        // Check Whitelist
        $whitelist = array_filter(array_map('trim', explode("\n", $settings['whitelist_ips'])));
        if (in_array($ip, $whitelist, true)) {
            return $user;
        }

        // Check Blacklist
        $blacklist = array_filter(array_map('trim', explode("\n", $settings['blacklist_ips'])));
        if (in_array($ip, $blacklist, true)) {
            return new WP_Error(
                'earthin_ip_blocked',
                __('<strong>Error</strong>: Access from your IP address has been permanently blocked.', 'earthin-login-shield')
            );
        }

        // Check Transient Lockout
        $lockout_until = get_transient($this->transient_prefix . 'lockout_' . md5($ip));
        if ($lockout_until && $lockout_until > time()) {
            $remaining = ceil(($lockout_until - time()) / 60);
            return new WP_Error(
                'earthin_too_many_attempts',
                sprintf(
                    /* translators: %d: number of minutes remaining */
                    __('<strong>Too many failed login attempts</strong>: Please try again in %d minute(s).', 'earthin-login-shield'),
                    $remaining
                )
            );
        }

        return $user;
    }

    public function record_login_failure($username) {
        $ip = $this->get_client_ip();
        $settings = $this->get_settings();

        // Check Whitelist
        $whitelist = array_filter(array_map('trim', explode("\n", $settings['whitelist_ips'])));
        if (in_array($ip, $whitelist, true)) {
            return;
        }

        $key = $this->transient_prefix . 'attempts_' . md5($ip);
        $attempts = intval(get_transient($key)) + 1;
        set_transient($key, $attempts, HOUR_IN_SECONDS);

        // Record log entry
        $this->log_failed_attempt($ip, $username);

        $allowed = max(1, intval($settings['allowed_retries']));
        if ($attempts >= $allowed) {
            // Apply lockout
            $lockout_count_key = $this->transient_prefix . 'lockouts_' . md5($ip);
            $lockout_count = intval(get_transient($lockout_count_key)) + 1;
            set_transient($lockout_count_key, $lockout_count, DAY_IN_SECONDS);

            $duration = ($lockout_count >= intval($settings['max_lockouts']))
                ? intval($settings['long_lockout_duration']) * HOUR_IN_SECONDS
                : intval($settings['lockout_duration']) * MINUTE_IN_SECONDS;

            set_transient($this->transient_prefix . 'lockout_' . md5($ip), time() + $duration, $duration);
            delete_transient($key); // Reset attempts after lockout triggered
        }
    }

    public function clear_login_attempts($user_login, $user) {
        $ip = $this->get_client_ip();
        delete_transient($this->transient_prefix . 'attempts_' . md5($ip));
    }

    public function filter_xmlrpc($enabled) {
        $settings = $this->get_settings();
        if (!empty($settings['disable_xmlrpc'])) {
            return false;
        }
        return $enabled;
    }

    private function log_failed_attempt($ip, $username) {
        $logs = get_option('earthin_login_shield_logs', []);
        if (!is_array($logs)) {
            $logs = [];
        }

        array_unshift($logs, [
            'ip' => $ip,
            'username' => sanitize_text_field($username),
            'timestamp' => current_time('mysql'),
        ]);

        // Keep last 50 entries
        if (count($logs) > 50) {
            $logs = array_slice($logs, 0, 50);
        }

        update_option('earthin_login_shield_logs', $logs, false);
    }

    public function add_admin_menu() {
        add_options_page(
            __('Earthin Login Shield', 'earthin-login-shield'),
            __('Login Shield', 'earthin-login-shield'),
            'manage_options',
            'earthin-login-shield',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting('earthin_login_shield_group', $this->option_name, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings'],
            'default' => $this->default_settings(),
        ]);
    }

    public function sanitize_settings($input) {
        $sanitized = $this->default_settings();
        $sanitized['allowed_retries'] = max(1, min(20, intval($input['allowed_retries'] ?? 4)));
        $sanitized['lockout_duration'] = max(1, min(1440, intval($input['lockout_duration'] ?? 20)));
        $sanitized['max_lockouts'] = max(1, min(10, intval($input['max_lockouts'] ?? 3)));
        $sanitized['long_lockout_duration'] = max(1, min(168, intval($input['long_lockout_duration'] ?? 24)));
        $sanitized['disable_xmlrpc'] = !empty($input['disable_xmlrpc']) ? 1 : 0;
        $sanitized['whitelist_ips'] = sanitize_textarea_field($input['whitelist_ips'] ?? '');
        $sanitized['blacklist_ips'] = sanitize_textarea_field($input['blacklist_ips'] ?? '');
        return $sanitized;
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'earthin-login-shield'));
        }

        // Handle clear logs
        if (isset($_POST['earthin_clear_logs']) && check_admin_referer('earthin_clear_logs_nonce')) {
            update_option('earthin_login_shield_logs', [], false);
            echo '<div class="updated"><p>' . esc_html__('Logs cleared successfully.', 'earthin-login-shield') . '</p></div>';
        }

        $settings = $this->get_settings();
        $logs = get_option('earthin_login_shield_logs', []);
        $total_blocked = count($logs);
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
                .earthin-stat-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; margin-bottom: 16px; }
                .earthin-stat-num { font-size: 26px; font-weight: 800; color: #059669; line-height: 1.1; }
                .earthin-stat-label { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; margin-top: 4px; }
                .earthin-btn-primary { background: #059669 !important; border-color: #059669 !important; color: #ffffff !important; font-weight: 700 !important; padding: 8px 22px !important; border-radius: 8px !important; box-shadow: 0 2px 4px rgba(5,150,105,0.2) !important; text-shadow: none !important; cursor: pointer; }
                .earthin-btn-primary:hover { background: #047857 !important; }
                .earthin-btn-danger { background: #ef4444 !important; border-color: #ef4444 !important; color: #ffffff !important; font-weight: 700 !important; padding: 6px 14px !important; border-radius: 8px !important; text-shadow: none !important; cursor: pointer; }
                .earthin-badge-pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
                .earthin-badge-success { background: #ecfdf5; color: #059669; }
                .earthin-badge-danger { background: #fef2f2; color: #ef4444; }
                .earthin-input { border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 14px; }
                .earthin-input:focus { border-color: #059669; outline: none; box-shadow: 0 0 0 3px rgba(5,150,105,0.15); }
                .earthin-table { width: 100%; border-collapse: collapse; }
                .earthin-table th { background: #f8fafc; padding: 12px 14px; font-size: 12px; font-weight: 700; color: #475569; text-align: left; border-bottom: 2px solid #e2e8f0; }
                .earthin-table td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #334155; }
                .earthin-table tr:hover td { background: #fafbfc; }
            </style>

            <header class="earthin-header">
                <div class="earthin-header-left">
                    <span class="earthin-logo-badge">🌱 Earthin</span>
                    <div class="earthin-title-group">
                        <h1>
                            <?php echo esc_html__('Earthin Login Shield', 'earthin-login-shield'); ?>
                            <span class="earthin-version-tag">v1.0.0</span>
                            <span class="earthin-badge-pill earthin-badge-success"><?php echo esc_html__('Brute-Force Guard Active', 'earthin-login-shield'); ?></span>
                        </h1>
                        <p><?php echo esc_html__('Hardened authentication armor against password brute-force and credential abuse.', 'earthin-login-shield'); ?></p>
                    </div>
                </div>
                <div class="earthin-header-links">
                    <a href="https://plugins.earthin.online" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Plugin Hub', 'earthin-login-shield'); ?> &nearr;</a>
                    <a href="https://plugins.earthin.online/docs" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Documentation', 'earthin-login-shield'); ?></a>
                    <a href="https://plugins.earthin.online/support" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Support', 'earthin-login-shield'); ?></a>
                </div>
            </header>

            <div class="earthin-grid">
                <div class="earthin-card">
                    <div class="earthin-card-header">
                        <h2 class="earthin-card-title"><?php esc_html_e('Brute Force Defense Parameters', 'earthin-login-shield'); ?></h2>
                    </div>

                    <form method="post" action="options.php">
                        <?php settings_fields('earthin_login_shield_group'); ?>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><?php esc_html_e('Allowed Retries', 'earthin-login-shield'); ?></th>
                                <td>
                                    <input type="number" min="1" max="20" name="<?php echo esc_attr($this->option_name); ?>[allowed_retries]" value="<?php echo esc_attr($settings['allowed_retries']); ?>" class="earthin-input" style="width:100px;" />
                                    <p class="description"><?php esc_html_e('Maximum failed login attempts before initial lockout.', 'earthin-login-shield'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Lockout Duration', 'earthin-login-shield'); ?></th>
                                <td>
                                    <input type="number" min="1" max="1440" name="<?php echo esc_attr($this->option_name); ?>[lockout_duration]" value="<?php echo esc_attr($settings['lockout_duration']); ?>" class="earthin-input" style="width:100px;" />
                                    <span><?php esc_html_e('minutes', 'earthin-login-shield'); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Extended Lockout Threshold', 'earthin-login-shield'); ?></th>
                                <td>
                                    <input type="number" min="1" max="10" name="<?php echo esc_attr($this->option_name); ?>[max_lockouts]" value="<?php echo esc_attr($settings['max_lockouts']); ?>" class="earthin-input" style="width:100px;" />
                                    <span><?php esc_html_e('lockouts', 'earthin-login-shield'); ?></span>
                                    <p class="description"><?php esc_html_e('Number of regular lockouts before triggering prolonged lockout.', 'earthin-login-shield'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Extended Lockout Duration', 'earthin-login-shield'); ?></th>
                                <td>
                                    <input type="number" min="1" max="168" name="<?php echo esc_attr($this->option_name); ?>[long_lockout_duration]" value="<?php echo esc_attr($settings['long_lockout_duration']); ?>" class="earthin-input" style="width:100px;" />
                                    <span><?php esc_html_e('hours', 'earthin-login-shield'); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('XML-RPC Protection', 'earthin-login-shield'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[disable_xmlrpc]" value="1" <?php checked($settings['disable_xmlrpc'], 1); ?> />
                                        <strong><?php esc_html_e('Disable XML-RPC API completely', 'earthin-login-shield'); ?></strong>
                                    </label>
                                    <p class="description"><?php esc_html_e('Closes common multicall amplification attack vectors.', 'earthin-login-shield'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('IP Whitelist', 'earthin-login-shield'); ?></th>
                                <td>
                                    <textarea class="earthin-input" style="width:100%; box-sizing:border-box;" rows="3" name="<?php echo esc_attr($this->option_name); ?>[whitelist_ips]"><?php echo esc_textarea($settings['whitelist_ips']); ?></textarea>
                                    <p class="description"><?php esc_html_e('One IP address per line. Your current IP:', 'earthin-login-shield'); ?> <code style="background:#ecfdf5; color:#047857; font-weight:700; padding:2px 6px; border-radius:4px;"><?php echo esc_html($this->get_client_ip()); ?></code></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('IP Blacklist', 'earthin-login-shield'); ?></th>
                                <td>
                                    <textarea class="earthin-input" style="width:100%; box-sizing:border-box;" rows="3" name="<?php echo esc_attr($this->option_name); ?>[blacklist_ips]"><?php echo esc_textarea($settings['blacklist_ips']); ?></textarea>
                                    <p class="description"><?php esc_html_e('One IP address per line. Automatically rejected on wp-login.php.', 'earthin-login-shield'); ?></p>
                                </td>
                            </tr>
                        </table>

                        <p class="submit" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #f1f5f9;">
                            <input type="submit" class="button earthin-btn-primary" value="<?php esc_attr_e('Save Security Parameters', 'earthin-login-shield'); ?>" />
                        </p>
                    </form>
                </div>

                <div>
                    <div class="earthin-card">
                        <div class="earthin-card-header">
                            <h2 class="earthin-card-title"><?php esc_html_e('Shield Intelligence', 'earthin-login-shield'); ?></h2>
                        </div>

                        <div class="earthin-stat-box">
                            <div class="earthin-stat-num"><?php echo esc_html((string)$total_blocked); ?></div>
                            <div class="earthin-stat-label"><?php esc_html_e('Total Failed Intrusions Logged', 'earthin-login-shield'); ?></div>
                        </div>

                        <p style="font-size:13px; color:#475569; margin:8px 0;">
                            <strong><?php esc_html_e('Max Retries:', 'earthin-login-shield'); ?></strong>
                            <code><?php echo esc_html((string)$settings['allowed_retries']); ?> attempts</code>
                        </p>
                        <p style="font-size:13px; color:#475569; margin:8px 0;">
                            <strong><?php esc_html_e('Initial Lockout:', 'earthin-login-shield'); ?></strong>
                            <code><?php echo esc_html((string)$settings['lockout_duration']); ?> mins</code>
                        </p>
                        <p style="font-size:13px; color:#475569; margin:8px 0;">
                            <strong><?php esc_html_e('XML-RPC API:', 'earthin-login-shield'); ?></strong>
                            <code><?php echo !empty($settings['disable_xmlrpc']) ? 'Blocked' : 'Enabled'; ?></code>
                        </p>
                    </div>

                    <div class="earthin-card">
                        <div class="earthin-card-header">
                            <h2 class="earthin-card-title"><?php esc_html_e('Best Practice Tip', 'earthin-login-shield'); ?></h2>
                        </div>
                        <p style="font-size:13px; color:#64748b; line-height:1.6;">
                            <?php esc_html_e('Combine Earthin Login Shield with Earthin 2FA for impenetrable defense against stolen credential attacks.', 'earthin-login-shield'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="earthin-card" style="padding:0; overflow:hidden;">
                <div style="padding: 20px 24px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                    <h2 class="earthin-card-title"><?php esc_html_e('Recent Failed Login Activity', 'earthin-login-shield'); ?></h2>
                    <?php if (!empty($logs)): ?>
                        <form method="post">
                            <?php wp_nonce_field('earthin_clear_logs_nonce'); ?>
                            <input type="submit" name="earthin_clear_logs" class="button earthin-btn-danger" value="<?php esc_attr_e('Clear Activity Log', 'earthin-login-shield'); ?>" onclick="return confirm('<?php esc_attr_e('Are you sure you want to clear all logs?', 'earthin-login-shield'); ?>');" />
                        </form>
                    <?php endif; ?>
                </div>

                <?php if (empty($logs)): ?>
                    <div style="padding: 40px; text-align: center; color: #64748b;">
                        <p style="margin: 0; font-size: 14px;"><?php esc_html_e('No failed login attempts recorded. System is clean and protected.', 'earthin-login-shield'); ?></p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="earthin-table">
                            <thead>
                                <tr>
                                    <th style="width:180px;"><?php esc_html_e('Timestamp', 'earthin-login-shield'); ?></th>
                                    <th style="width:160px;"><?php esc_html_e('IP Address', 'earthin-login-shield'); ?></th>
                                    <th><?php esc_html_e('Target Username Attempted', 'earthin-login-shield'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><span class="earthin-badge-pill earthin-badge-neutral"><?php echo esc_html($log['timestamp']); ?></span></td>
                                        <td><code style="background:#fee2e2; color:#991b1b; padding:3px 8px; border-radius:6px; font-weight:700;"><?php echo esc_html($log['ip']); ?></code></td>
                                        <td><strong><?php echo esc_html($log['username']); ?></strong></td>
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

add_action('plugins_loaded', ['Earthin_Login_Shield', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-login-shield')) . '">' . __('Settings', 'earthin-login-shield') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
