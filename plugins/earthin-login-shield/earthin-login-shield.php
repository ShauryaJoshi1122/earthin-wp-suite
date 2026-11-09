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
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Earthin Login Shield', 'earthin-login-shield'); ?></h1>
            <p><?php esc_html_e('Defend your WordPress admin against brute-force attacks and credential stuffing.', 'earthin-login-shield'); ?></p>

            <form method="post" action="options.php">
                <?php settings_fields('earthin_login_shield_group'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('Allowed Retries', 'earthin-login-shield'); ?></th>
                        <td>
                            <input type="number" min="1" max="20" name="<?php echo esc_attr($this->option_name); ?>[allowed_retries]" value="<?php echo esc_attr($settings['allowed_retries']); ?>" />
                            <p class="description"><?php esc_html_e('Maximum failed login attempts before lockout.', 'earthin-login-shield'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Lockout Duration', 'earthin-login-shield'); ?></th>
                        <td>
                            <input type="number" min="1" max="1440" name="<?php echo esc_attr($this->option_name); ?>[lockout_duration]" value="<?php echo esc_attr($settings['lockout_duration']); ?>" />
                            <span><?php esc_html_e('minutes', 'earthin-login-shield'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Extended Lockout Threshold', 'earthin-login-shield'); ?></th>
                        <td>
                            <input type="number" min="1" max="10" name="<?php echo esc_attr($this->option_name); ?>[max_lockouts]" value="<?php echo esc_attr($settings['max_lockouts']); ?>" />
                            <span><?php esc_html_e('lockouts', 'earthin-login-shield'); ?></span>
                            <p class="description"><?php esc_html_e('Number of regular lockouts before triggering extended lockout.', 'earthin-login-shield'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Extended Lockout Duration', 'earthin-login-shield'); ?></th>
                        <td>
                            <input type="number" min="1" max="168" name="<?php echo esc_attr($this->option_name); ?>[long_lockout_duration]" value="<?php echo esc_attr($settings['long_lockout_duration']); ?>" />
                            <span><?php esc_html_e('hours', 'earthin-login-shield'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('XML-RPC Protection', 'earthin-login-shield'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[disable_xmlrpc]" value="1" <?php checked($settings['disable_xmlrpc'], 1); ?> />
                                <?php esc_html_e('Disable XML-RPC API completely (recommended if not using Jetpack or mobile app)', 'earthin-login-shield'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('IP Whitelist (Never Locked Out)', 'earthin-login-shield'); ?></th>
                        <td>
                            <textarea class="large-text" rows="3" name="<?php echo esc_attr($this->option_name); ?>[whitelist_ips]"><?php echo esc_textarea($settings['whitelist_ips']); ?></textarea>
                            <p class="description"><?php esc_html_e('One IP address per line. Your current IP: ', 'earthin-login-shield'); ?><code><?php echo esc_html($this->get_client_ip()); ?></code></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('IP Blacklist (Always Blocked)', 'earthin-login-shield'); ?></th>
                        <td>
                            <textarea class="large-text" rows="3" name="<?php echo esc_attr($this->option_name); ?>[blacklist_ips]"><?php echo esc_textarea($settings['blacklist_ips']); ?></textarea>
                            <p class="description"><?php esc_html_e('One IP address per line.', 'earthin-login-shield'); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

            <hr>
            <h2><?php esc_html_e('Recent Failed Login Activity', 'earthin-login-shield'); ?></h2>
            <?php if (empty($logs)): ?>
                <p><?php esc_html_e('No failed login attempts recorded.', 'earthin-login-shield'); ?></p>
            <?php else: ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Timestamp', 'earthin-login-shield'); ?></th>
                            <th><?php esc_html_e('IP Address', 'earthin-login-shield'); ?></th>
                            <th><?php esc_html_e('Attempted Username', 'earthin-login-shield'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo esc_html($log['timestamp']); ?></td>
                                <td><code><?php echo esc_html($log['ip']); ?></code></td>
                                <td><?php echo esc_html($log['username']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <form method="post" style="margin-top: 15px;">
                    <?php wp_nonce_field('earthin_clear_logs_nonce'); ?>
                    <input type="submit" name="earthin_clear_logs" class="button button-secondary" value="<?php esc_attr_e('Clear Activity Log', 'earthin-login-shield'); ?>" onclick="return confirm('<?php esc_attr_e('Are you sure you want to clear all logs?', 'earthin-login-shield'); ?>');" />
                </form>
            <?php endif; ?>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Login_Shield', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function() {
     = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-login-shield')) . '">' . __('Settings', 'earthin-login-shield') . '</a>';
    array_unshift(, );
    return ;
});
