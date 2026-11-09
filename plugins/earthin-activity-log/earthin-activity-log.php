<?php
/**
 * Plugin Name: Earthin WP Activity Log
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-activity-log
 * Description: Real-time security audit trail monitoring user authentications, content modifications, and plugin status changes.
 * Version: 1.0.0
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * Text Domain: earthin-activity-log
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class Earthin_Activity_Log {
    private static ?Earthin_Activity_Log $instance = null;

    public static function get_instance(): Earthin_Activity_Log {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Auth Hooks
        add_action('wp_login', [$this, 'log_login'], 10, 2);
        add_action('wp_login_failed', [$this, 'log_failed_login']);
        add_action('clear_auth_cookie', [$this, 'log_logout']);

        // Post Hooks
        add_action('save_post', [$this, 'log_post_save'], 10, 3);
        add_action('before_delete_post', [$this, 'log_post_delete']);

        // Plugin Hooks
        add_action('activated_plugin', [$this, 'log_plugin_activated']);
        add_action('deactivated_plugin', [$this, 'log_plugin_deactivated']);

        // Admin Menu
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_init', [$this, 'handle_admin_actions']);
    }

    private function get_client_ip(): string {
        return sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    }

    public function record_event(string $action, string $type, string $details, string $user = 'System'): void {
        $logs = get_option('earthin_activity_events', []);
        $logs[] = [
            'id' => uniqid('log_'),
            'type' => $type,
            'action' => $action,
            'details' => $details,
            'user' => $user,
            'ip' => $this->get_client_ip(),
            'timestamp' => current_time('mysql'),
        ];

        // Keep rolling buffer of 1000 events
        if (count($logs) > 1000) {
            $logs = array_slice($logs, -1000);
        }

        update_option('earthin_activity_events', $logs);
    }

    public function log_login(string $user_login, WP_User $user): void {
        $this->record_event('User Logged In', 'auth', sprintf('User %s successfully logged in.', $user_login), $user_login);
    }

    public function log_failed_login(string $username): void {
        $this->record_event('Failed Login Attempt', 'security', sprintf('Failed login attempt for username: %s', sanitize_text_field($username)), 'Anonymous');
    }

    public function log_logout(): void {
        $current_user = wp_get_current_user();
        if ($current_user && $current_user->exists()) {
            $this->record_event('User Logged Out', 'auth', sprintf('User %s logged out.', $current_user->user_login), $current_user->user_login);
        }
    }

    public function log_post_save(int $post_id, WP_Post $post, bool $update): void {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if ('revision' === $post->post_type) return;

        $current_user = wp_get_current_user();
        $user_login = ($current_user && $current_user->exists()) ? $current_user->user_login : 'System';
        $verb = $update ? 'updated' : 'created';
        $this->record_event(
            ucfirst($verb) . ' ' . ucfirst($post->post_type),
            'content',
            sprintf('%s "%s" (ID: %d) was %s.', ucfirst($post->post_type), $post->post_title ?: 'Untitled', $post_id, $verb),
            $user_login
        );
    }

    public function log_post_delete(int $post_id): void {
        $post = get_post($post_id);
        if (!$post || 'revision' === $post->post_type) return;

        $current_user = wp_get_current_user();
        $user_login = ($current_user && $current_user->exists()) ? $current_user->user_login : 'System';
        $this->record_event('Deleted ' . ucfirst($post->post_type), 'content', sprintf('Post "%s" (ID: %d) permanently deleted.', $post->post_title, $post_id), $user_login);
    }

    public function log_plugin_activated(string $plugin): void {
        $current_user = wp_get_current_user();
        $user_login = ($current_user && $current_user->exists()) ? $current_user->user_login : 'System';
        $this->record_event('Plugin Activated', 'system', sprintf('Plugin "%s" was activated.', $plugin), $user_login);
    }

    public function log_plugin_deactivated(string $plugin): void {
        $current_user = wp_get_current_user();
        $user_login = ($current_user && $current_user->exists()) ? $current_user->user_login : 'System';
        $this->record_event('Plugin Deactivated', 'system', sprintf('Plugin "%s" was deactivated.', $plugin), $user_login);
    }

    public function register_admin_menu(): void {
        add_management_page(
            __('Earthin Activity Log', 'earthin-activity-log'),
            __('Earthin Activity Log', 'earthin-activity-log'),
            'manage_options',
            'earthin-activity-log',
            [$this, 'render_admin_page']
        );
    }

    public function handle_admin_actions(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['earthin_clear_activity_log']) && check_admin_referer('earthin_clear_activity_nonce')) {
            update_option('earthin_activity_events', []);
            add_settings_error('earthin_activity', 'cleared', __('Activity log buffer cleared.', 'earthin-activity-log'), 'updated');
        }
    }

    public function render_admin_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $logs = array_reverse(get_option('earthin_activity_events', []));
        $total_logs = count($logs);
        $security_count = 0;
        $auth_count = 0;
        foreach ($logs as $l) {
            if (($l['type'] ?? '') === 'security') $security_count++;
            if (($l['type'] ?? '') === 'auth') $auth_count++;
        }
        ?>
        <div class="earthin-admin-wrap">
            <style>
                .earthin-admin-wrap { max-width: 1240px; margin: 20px 20px 0 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
                .earthin-header { background: #0f172a; color: #ffffff; padding: 22px 28px; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08); flex-wrap: wrap; gap: 16px; }
                .earthin-header-left { display: flex; align-items: center; gap: 14px; }
                .earthin-logo-badge { background: #059669; color: #ffffff; font-weight: 800; font-size: 14px; padding: 6px 12px; border-radius: 8px; letter-spacing: 0.04em; display: inline-flex; align-items: center; gap: 6px; }
                .earthin-title-group h1 { color: #ffffff; margin: 0; font-size: 20px; font-weight: 700; line-height: 1.2; display: flex; align-items: center; gap: 10px; }
                .earthin-version-tag { font-size: 11px; background: rgba(255,255,255,0.15); color: #cbd5e1; padding: 2px 8px; border-radius: 999px; font-weight: 600; }
                .earthin-title-group p { margin: 4px 0 0; color: #94a3b8; font-size: 13px; }
                .earthin-header-links { display: flex; gap: 10px; align-items: center; }
                .earthin-link-btn { background: rgba(255,255,255,0.08); color: #e2e8f0 !important; text-decoration: none !important; font-size: 12px; font-weight: 600; padding: 7px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.12); transition: all 0.15s ease; }
                .earthin-link-btn:hover { background: rgba(255,255,255,0.18); color: #ffffff !important; }
                .earthin-card { background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); padding: 24px 28px; margin-bottom: 24px; }
                .earthin-card-header { padding-bottom: 14px; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
                .earthin-card-title { font-size: 16px; font-weight: 700; color: #0f172a; margin: 0; }
                .earthin-btn-danger { background: #ef4444 !important; border-color: #ef4444 !important; color: #ffffff !important; font-weight: 700 !important; padding: 6px 14px !important; border-radius: 8px !important; text-shadow: none !important; cursor: pointer; }
                .earthin-badge-pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
                .earthin-badge-success { background: #ecfdf5; color: #059669; }
                .earthin-badge-danger { background: #fef2f2; color: #ef4444; }
                .earthin-badge-neutral { background: #f1f5f9; color: #475569; }
                .earthin-badge-auth { background: #e0f2fe; color: #0284c7; }
                .earthin-badge-post { background: #fef3c7; color: #b45309; }
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
                            <?php echo esc_html__('Earthin WP Activity Log', 'earthin-activity-log'); ?>
                            <span class="earthin-version-tag">v1.0.0</span>
                            <span class="earthin-badge-pill earthin-badge-success"><?php echo esc_html__('Audit Monitoring Active', 'earthin-activity-log'); ?></span>
                        </h1>
                        <p><?php echo esc_html__('Real-time security and administrative audit trail engineered by Shaurya Joshi.', 'earthin-activity-log'); ?></p>
                    </div>
                </div>
                <div class="earthin-header-links">
                    <a href="https://plugins.earthin.online" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Plugin Hub', 'earthin-activity-log'); ?> &nearr;</a>
                    <a href="https://plugins.earthin.online/docs" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Documentation', 'earthin-activity-log'); ?></a>
                    <a href="https://plugins.earthin.online/support" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Support', 'earthin-activity-log'); ?></a>
                </div>
            </header>

            <?php settings_errors('earthin_activity'); ?>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
                <div class="earthin-card" style="padding:18px 24px; margin-bottom:0;">
                    <div style="font-size:24px; font-weight:800; color:#059669;"><?php echo esc_html((string)$total_logs); ?></div>
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; margin-top:4px;"><?php esc_html_e('Total Logged Events', 'earthin-activity-log'); ?></div>
                </div>
                <div class="earthin-card" style="padding:18px 24px; margin-bottom:0;">
                    <div style="font-size:24px; font-weight:800; color:#0284c7;"><?php echo esc_html((string)$auth_count); ?></div>
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; margin-top:4px;"><?php esc_html_e('Authentication Events', 'earthin-activity-log'); ?></div>
                </div>
                <div class="earthin-card" style="padding:18px 24px; margin-bottom:0;">
                    <div style="font-size:24px; font-weight:800; color:#ef4444;"><?php echo esc_html((string)$security_count); ?></div>
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; margin-top:4px;"><?php esc_html_e('Security & Critical Actions', 'earthin-activity-log'); ?></div>
                </div>
            </div>

            <div class="earthin-card" style="padding:0; overflow:hidden;">
                <div style="padding: 20px 24px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                    <h2 class="earthin-card-title"><?php echo esc_html__('Recent Security Audit Events', 'earthin-activity-log'); ?></h2>
                    <?php if (!empty($logs)) : ?>
                        <form method="post" onsubmit="return confirm('Purge the entire activity audit log?');">
                            <?php wp_nonce_field('earthin_clear_activity_nonce'); ?>
                            <input type="submit" name="earthin_clear_activity_log" class="button earthin-btn-danger" value="<?php echo esc_attr__('Purge Activity Log', 'earthin-activity-log'); ?>" />
                        </form>
                    <?php endif; ?>
                </div>

                <?php if (empty($logs)) : ?>
                    <div style="padding: 40px; text-align: center; color: #64748b;">
                        <p style="margin: 0; font-size: 14px;"><?php echo esc_html__('No audit events logged yet. Events record automatically as administrators authenticate and make changes.', 'earthin-activity-log'); ?></p>
                    </div>
                <?php else : ?>
                    <div style="overflow-x: auto;">
                        <table class="earthin-table">
                            <thead>
                                <tr>
                                    <th style="width:160px;"><?php esc_html_e('Timestamp', 'earthin-activity-log'); ?></th>
                                    <th style="width:130px;"><?php esc_html_e('Operator User', 'earthin-activity-log'); ?></th>
                                    <th style="width:150px;"><?php esc_html_e('Event Type', 'earthin-activity-log'); ?></th>
                                    <th><?php esc_html_e('Audit Details', 'earthin-activity-log'); ?></th>
                                    <th style="width:130px; text-align:right;"><?php esc_html_e('Origin IP', 'earthin-activity-log'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log) : 
                                    $type = $log['type'] ?? 'info';
                                    $pill_class = 'earthin-badge-' . ($type === 'security' ? 'danger' : ($type === 'auth' ? 'auth' : ($type === 'post' ? 'post' : 'neutral')));
                                ?>
                                    <tr>
                                        <td><span class="earthin-badge-pill earthin-badge-neutral"><?php echo esc_html($log['timestamp']); ?></span></td>
                                        <td><strong style="color:#0f172a;"><?php echo esc_html($log['user']); ?></strong></td>
                                        <td><span class="earthin-badge-pill <?php echo esc_attr($pill_class); ?>"><?php echo esc_html($log['action']); ?></span></td>
                                        <td><span style="color:#334155; line-height:1.5;"><?php echo esc_html($log['details']); ?></span></td>
                                        <td style="text-align:right;"><code style="background:#f1f5f9; padding:2px 6px; border-radius:4px; font-size:12px;"><?php echo esc_html($log['ip']); ?></code></td>
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

add_action('plugins_loaded', ['Earthin_Activity_Log', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('tools.php?page=earthin-activity-log')) . '">' . __('Settings', 'earthin-activity-log') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
