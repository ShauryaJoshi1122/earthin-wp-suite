<?php
/**
 * Plugin Name: Earthin Web Push
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-push
 * Description: Privacy-first, self-hosted browser web push notifications using open standard VAPID protocols and Service Workers.
 * Version: 1.0.0
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: earthin-push
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('EARTHIN_PUSH_VERSION', '1.0.0');
define('EARTHIN_PUSH_PATH', plugin_dir_path(__FILE__));
define('EARTHIN_PUSH_URL', plugin_dir_url(__FILE__));

class Earthin_Web_Push {
    private static $instance = null;
    private $option_name = 'earthin_push_settings';

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        register_activation_hook(__FILE__, [$this, 'create_subscriber_table']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_footer', [$this, 'render_subscribe_prompt']);
        add_action('wp_ajax_earthin_save_push_subscription', [$this, 'ajax_save_subscription']);
        add_action('wp_ajax_nopriv_earthin_save_push_subscription', [$this, 'ajax_save_subscription']);
        add_action('parse_request', [$this, 'serve_service_worker']);
    }

    public function create_subscriber_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'earthin_push_subscribers';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            endpoint text NOT NULL,
            p256dh varchar(255) NOT NULL,
            auth_secret varchar(255) NOT NULL,
            user_agent varchar(255) DEFAULT '',
            created_at datetime NOT NULL,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function default_settings() {
        return [
            'enabled' => 1,
            'vapid_public_key' => '',
            'vapid_private_key' => '',
            'prompt_title' => 'Get Instant Updates',
            'prompt_text' => 'Subscribe to browser notifications for the latest news and announcements.',
            'bell_position' => 'bottom-left',
        ];
    }

    public function get_settings() {
        $saved = get_option($this->option_name, []);
        return wp_parse_args($saved, $this->default_settings());
    }

    public function serve_service_worker($wp) {
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/earthin-sw.js') !== false) {
            header('Content-Type: application/javascript; charset=utf-8');
            header('Service-Worker-Allowed: /');
            echo "
            self.addEventListener('push', function(event) {
                if (event.data) {
                    var data = event.data.json();
                    var title = data.title || 'New Update';
                    var options = {
                        body: data.body || '',
                        icon: data.icon || '/favicon.ico',
                        data: { url: data.url || '/' }
                    };
                    event.waitUntil(self.registration.showNotification(title, options));
                }
            });
            self.addEventListener('notificationclick', function(event) {
                event.notification.close();
                var url = (event.notification.data && event.notification.data.url) ? event.notification.data.url : '/';
                event.waitUntil(clients.openWindow(url));
            });
            ";
            exit;
        }
    }

    public function ajax_save_subscription() {
        check_ajax_referer('earthin_push_nonce', 'security');

        $endpoint = esc_url_raw($_POST['endpoint'] ?? '');
        $p256dh = sanitize_text_field($_POST['p256dh'] ?? '');
        $auth = sanitize_text_field($_POST['auth'] ?? '');

        if (empty($endpoint) || empty($p256dh) || empty($auth)) {
            wp_send_json_error(['message' => 'Invalid subscription payload']);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'earthin_push_subscribers';

        // Check if exists
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE endpoint = %s", $endpoint));
        if (!$existing) {
            $wpdb->insert(
                $table,
                [
                    'endpoint' => $endpoint,
                    'p256dh' => $p256dh,
                    'auth_secret' => $auth,
                    'user_agent' => sanitize_text_field(substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)),
                    'created_at' => current_time('mysql'),
                ],
                ['%s', '%s', '%s', '%s', '%s']
            );
        }

        wp_send_json_success(['message' => 'Subscription registered']);
    }

    public function render_subscribe_prompt() {
        $settings = $this->get_settings();
        if (empty($settings['enabled']) || is_admin()) {
            return;
        }

        $nonce = wp_create_nonce('earthin_push_nonce');
        $ajax_url = admin_url('admin-ajax.php');
        $pub_key = esc_js($settings['vapid_public_key']);
        ?>
        <div id="earthin-push-bell" style="position:fixed; bottom:24px; left:24px; z-index:999990; cursor:pointer; background:#3b82f6; width:48px; height:48px; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 12px rgba(0,0,0,0.15); color:#fff;" title="<?php esc_attr_e('Subscribe to Push Notifications', 'earthin-push'); ?>">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
        </div>
        <script>
        (function() {
            var bell = document.getElementById('earthin-push-bell');
            if (!bell || !('serviceWorker' in navigator) || !('PushManager' in window)) return;

            navigator.serviceWorker.register('/earthin-sw.js').catch(function(){});

            bell.addEventListener('click', function() {
                Notification.requestPermission().then(function(permission) {
                    if (permission === 'granted') {
                        navigator.serviceWorker.ready.then(function(reg) {
                            reg.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: '<?php echo $pub_key; ?>' || undefined
                            }).then(function(sub) {
                                var rawKey = sub.getKey ? sub.getKey('p256dh') : '';
                                var rawAuth = sub.getKey ? sub.getKey('auth') : '';
                                var p256dh = rawKey ? btoa(String.fromCharCode.apply(null, new Uint8Array(rawKey))) : '';
                                var auth = rawAuth ? btoa(String.fromCharCode.apply(null, new Uint8Array(rawAuth))) : '';

                                var xhr = new XMLHttpRequest();
                                xhr.open('POST', '<?php echo esc_url($ajax_url); ?>', true);
                                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                                xhr.send('action=earthin_save_push_subscription&security=<?php echo esc_js($nonce); ?>&endpoint=' + encodeURIComponent(sub.endpoint) + '&p256dh=' + encodeURIComponent(p256dh) + '&auth=' + encodeURIComponent(auth));
                                alert('Thank you for subscribing to notifications!');
                                bell.style.display = 'none';
                            }).catch(function(err) {
                                console.log('Subscription failed', err);
                            });
                        });
                    }
                });
            });
        })();
        </script>
        <?php
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Earthin Web Push', 'earthin-push'),
            __('Web Push', 'earthin-push'),
            'manage_options',
            'earthin-push',
            [$this, 'render_admin_dashboard'],
            'dashicons-bell',
            76
        );
    }

    public function render_admin_dashboard() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'earthin-push'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'earthin_push_subscribers';
        $sub_count = 0;
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table) {
            $sub_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        }

        if (isset($_POST['earthin_save_keys']) && check_admin_referer('earthin_push_keys_nonce')) {
            $settings = $this->get_settings();
            $settings['vapid_public_key'] = sanitize_text_field($_POST['vapid_public_key'] ?? '');
            $settings['vapid_private_key'] = sanitize_text_field($_POST['vapid_private_key'] ?? '');
            $settings['enabled'] = !empty($_POST['enabled']) ? 1 : 0;
            update_option($this->option_name, $settings);
            echo '<div class="updated"><p>' . esc_html__('Push settings saved.', 'earthin-push') . '</p></div>';
        }

        $settings = $this->get_settings();
        $is_enabled = !empty($settings['enabled']);
        $status_label = $is_enabled ? __('Push Service Active', 'earthin-push') : __('Push Service Inactive', 'earthin-push');
        $status_badge_class = $is_enabled ? 'earthin-badge-success' : 'earthin-badge-warning';
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
                .earthin-badge-pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
                .earthin-badge-success { background: #ecfdf5; color: #059669; }
                .earthin-badge-warning { background: #fffbeb; color: #d97706; }
                .earthin-input { border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 14px; width: 100%; box-sizing: border-box; }
                .earthin-input:focus { border-color: #059669; outline: none; box-shadow: 0 0 0 3px rgba(5,150,105,0.15); }
            </style>

            <header class="earthin-header">
                <div class="earthin-header-left">
                    <span class="earthin-logo-badge">🌱 Earthin</span>
                    <div class="earthin-title-group">
                        <h1>
                            <?php esc_html_e('Earthin Web Push Dashboard', 'earthin-push'); ?>
                            <span class="earthin-version-tag">v1.0.0</span>
                            <span class="earthin-badge-pill <?php echo esc_attr($status_badge_class); ?>"><?php echo esc_html($status_label); ?></span>
                        </h1>
                        <p><?php esc_html_e('Self-hosted, vendor-free web push notifications with open VAPID protocol by Shaurya Joshi.', 'earthin-push'); ?></p>
                    </div>
                </div>
                <div class="earthin-header-links">
                    <a href="https://plugins.earthin.online" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Plugin Hub', 'earthin-push'); ?> &nearr;</a>
                    <a href="https://plugins.earthin.online/docs" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Documentation', 'earthin-push'); ?></a>
                    <a href="https://plugins.earthin.online/support" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Support', 'earthin-push'); ?></a>
                </div>
            </header>

            <div class="earthin-grid">
                <div class="earthin-card">
                    <div class="earthin-card-header">
                        <h2 class="earthin-card-title"><?php esc_html_e('VAPID Open Protocol Configuration', 'earthin-push'); ?></h2>
                    </div>

                    <form method="post">
                        <?php wp_nonce_field('earthin_push_keys_nonce'); ?>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><?php esc_html_e('Enable Push Bell Prompt', 'earthin-push'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="enabled" value="1" <?php checked($settings['enabled'], 1); ?> />
                                        <strong><?php esc_html_e('Render subscription prompt button on frontend', 'earthin-push'); ?></strong>
                                    </label>
                                    <p class="description"><?php esc_html_e('Allows visitors to subscribe to real-time browser alerts with 1 click.', 'earthin-push'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('VAPID Public Key', 'earthin-push'); ?></th>
                                <td>
                                    <input type="text" class="earthin-input" name="vapid_public_key" value="<?php echo esc_attr($settings['vapid_public_key']); ?>" placeholder="BEl62iUYgU..." style="font-family:monospace; font-size:12px;" />
                                    <p class="description"><?php esc_html_e('Application server public key sent to browser push managers.', 'earthin-push'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('VAPID Private Key', 'earthin-push'); ?></th>
                                <td>
                                    <input type="password" class="earthin-input" name="vapid_private_key" value="<?php echo esc_attr($settings['vapid_private_key']); ?>" style="font-family:monospace; font-size:12px;" />
                                    <p class="description"><?php esc_html_e('Secret key used to cryptographically sign outbound push payloads.', 'earthin-push'); ?></p>
                                </td>
                            </tr>
                        </table>

                        <p class="submit" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #f1f5f9;">
                            <input type="submit" name="earthin_save_keys" class="button earthin-btn-primary" value="<?php esc_attr_e('Save Configuration', 'earthin-push'); ?>" />
                        </p>
                    </form>
                </div>

                <div>
                    <div class="earthin-card">
                        <div class="earthin-card-header">
                            <h2 class="earthin-card-title"><?php esc_html_e('Audience Reach', 'earthin-push'); ?></h2>
                        </div>

                        <div class="earthin-stat-box">
                            <div class="earthin-stat-num"><?php echo number_format_i18n($sub_count); ?></div>
                            <div class="earthin-stat-label"><?php esc_html_e('Active Push Subscribers', 'earthin-push'); ?></div>
                        </div>

                        <div style="margin-top:16px;">
                            <strong style="font-size:13px; color:#334155; display:block; margin-bottom:8px;"><?php esc_html_e('Supported Browsers:', 'earthin-push'); ?></strong>
                            <div style="font-size:12px; color:#64748b; line-height:1.6;">
                                ✔ Chrome, Chromium & Brave<br>
                                ✔ Mozilla Firefox (Desktop & Android)<br>
                                ✔ Microsoft Edge<br>
                                ✔ Apple Safari 16+ (macOS & iOS PWA)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Web_Push', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=earthin-push')) . '">' . __('Settings', 'earthin-push') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
