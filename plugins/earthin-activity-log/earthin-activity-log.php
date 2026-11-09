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
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Earthin WP Activity Log', 'earthin-activity-log'); ?></h1>
            <p><?php echo esc_html__('Real-time security audit trail tracking administrative actions and system modifications.', 'earthin-activity-log'); ?></p>
            <?php settings_errors('earthin_activity'); ?>

            <div style="background:#fff;padding:24px;border:1px solid #ccd0d4;border-radius:8px;margin-top:20px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
                    <h2><?php echo esc_html__('Security Audit Events', 'earthin-activity-log'); ?> (<?php echo count($logs); ?>)</h2>
                    <?php if (!empty($logs)) : ?>
                        <form method="post" onsubmit="return confirm('Clear the activity audit log?');">
                            <?php wp_nonce_field('earthin_clear_activity_nonce'); ?>
                            <input type="submit" name="earthin_clear_activity_log" class="button button-secondary" style="color:#d63638;" value="<?php echo esc_attr__('Purge Activity Log', 'earthin-activity-log'); ?>" />
                        </form>
                    <?php endif; ?>
                </div>

                <?php if (empty($logs)) : ?>
                    <p><?php echo esc_html__('No audit events logged yet. Events will record automatically as users authenticate and publish content.', 'earthin-activity-log'); ?></p>
                <?php else : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width:160px;"><?php esc_html_e('Timestamp', 'earthin-activity-log'); ?></th>
                                <th style="width:120px;"><?php esc_html_e('User', 'earthin-activity-log'); ?></th>
                                <th style="width:160px;"><?php esc_html_e('Event Type', 'earthin-activity-log'); ?></th>
                                <th><?php esc_html_e('Details', 'earthin-activity-log'); ?></th>
                                <th style="width:130px;"><?php esc_html_e('IP Address', 'earthin-activity-log'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log) : ?>
                                <tr>
                                    <td><code><?php echo esc_html($log['timestamp']); ?></code></td>
                                    <td><strong><?php echo esc_html($log['user']); ?></strong></td>
                                    <td>
                                        <span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;
                                        <?php echo 'security' === $log['type'] ? 'background:#fee2e2;color:#991b1b;' : ('auth' === $log['type'] ? 'background:#ecfdf5;color:#065f46;' : 'background:#f1f5f9;color:#334155;'); ?>">
                                            <?php echo esc_html($log['action']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html($log['details']); ?></td>
                                    <td><code><?php echo esc_html($log['ip']); ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Activity_Log', 'get_instance']);
