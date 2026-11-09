<?php
/**
 * Plugin Name: Earthin SMTP Mailer
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-smtp-mailer
 * Description: Re-routes all WordPress outbound emails through authenticated custom SMTP servers, with real-time test mailer and error tracking.
 * Version: 1.0.0
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * Text Domain: earthin-smtp-mailer
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class Earthin_SMTP_Mailer {
    private static ?Earthin_SMTP_Mailer $instance = null;
    private array $settings;

    public static function get_instance(): Earthin_SMTP_Mailer {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->settings = get_option('earthin_smtp_settings', [
            'enabled' => 0,
            'host' => '',
            'port' => 587,
            'encryption' => 'tls',
            'auth' => 1,
            'username' => '',
            'password' => '',
            'from_email' => get_option('admin_email'),
            'from_name' => get_bloginfo('name'),
        ]);

        add_action('phpmailer_init', [$this, 'configure_phpmailer']);
        add_filter('wp_mail_from', [$this, 'filter_from_email']);
        add_filter('wp_mail_from_name', [$this, 'filter_from_name']);
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_init', [$this, 'handle_admin_actions']);
    }

    public function configure_phpmailer($phpmailer): void {
        if (empty($this->settings['enabled']) || empty($this->settings['host'])) {
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->Host = sanitize_text_field($this->settings['host']);
        $phpmailer->Port = (int) ($this->settings['port'] ?? 587);
        $phpmailer->SMTPAuth = !empty($this->settings['auth']);
        
        if (!empty($this->settings['auth'])) {
            $phpmailer->Username = sanitize_text_field($this->settings['username']);
            $phpmailer->Password = $this->settings['password'];
        }

        $encryption = strtolower($this->settings['encryption'] ?? 'tls');
        if ('ssl' === $encryption || 'tls' === $encryption) {
            $phpmailer->SMTPSecure = $encryption;
        } else {
            $phpmailer->SMTPSecure = '';
            $phpmailer->SMTPAutoTLS = false;
        }

        if (!empty($this->settings['from_email']) && is_email($this->settings['from_email'])) {
            $phpmailer->setFrom($this->settings['from_email'], $this->settings['from_name'] ?: get_bloginfo('name'));
        }
    }

    public function filter_from_email(string $original): string {
        if (!empty($this->settings['enabled']) && !empty($this->settings['from_email']) && is_email($this->settings['from_email'])) {
            return sanitize_email($this->settings['from_email']);
        }
        return $original;
    }

    public function filter_from_name(string $original): string {
        if (!empty($this->settings['enabled']) && !empty($this->settings['from_name'])) {
            return sanitize_text_field($this->settings['from_name']);
        }
        return $original;
    }

    public function register_admin_menu(): void {
        add_options_page(
            __('Earthin SMTP', 'earthin-smtp-mailer'),
            __('Earthin SMTP', 'earthin-smtp-mailer'),
            'manage_options',
            'earthin-smtp-mailer',
            [$this, 'render_admin_page']
        );
    }

    public function handle_admin_actions(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Save Settings
        if (isset($_POST['earthin_save_smtp']) && check_admin_referer('earthin_smtp_save_nonce')) {
            $this->settings['enabled'] = isset($_POST['enabled']) ? 1 : 0;
            $this->settings['host'] = sanitize_text_field($_POST['host'] ?? '');
            $this->settings['port'] = (int) ($_POST['port'] ?? 587);
            $this->settings['encryption'] = sanitize_text_field($_POST['encryption'] ?? 'tls');
            $this->settings['auth'] = isset($_POST['auth']) ? 1 : 0;
            $this->settings['username'] = sanitize_text_field($_POST['username'] ?? '');
            
            // Only update password if a new one was provided
            if (!empty($_POST['password'])) {
                $this->settings['password'] = sanitize_text_field($_POST['password']);
            }
            
            $this->settings['from_email'] = sanitize_email($_POST['from_email'] ?? get_option('admin_email'));
            $this->settings['from_name'] = sanitize_text_field($_POST['from_name'] ?? get_bloginfo('name'));

            update_option('earthin_smtp_settings', $this->settings);
            add_settings_error('earthin_smtp', 'smtp_saved', __('SMTP settings successfully saved.', 'earthin-smtp-mailer'), 'updated');
        }

        // Send Test Email
        if (isset($_POST['earthin_send_test_email']) && check_admin_referer('earthin_smtp_test_nonce')) {
            $recipient = sanitize_email($_POST['test_email'] ?? '');
            if (!is_email($recipient)) {
                add_settings_error('earthin_smtp', 'invalid_email', __('Please provide a valid recipient email address.', 'earthin-smtp-mailer'), 'error');
                return;
            }

            $subject = sprintf('[Earthin SMTP] Test Email Verification - %s', get_bloginfo('name'));
            $message = sprintf(
                "Hello,\n\nThis is a test email sent from %s via Earthin SMTP Mailer to confirm that your SMTP connection settings are working reliably.\n\nTimestamp: %s UTC\nAuthor: Shaurya Joshi\nPlatform: Earthin Suite",
                get_bloginfo('name'),
                gmdate('Y-m-d H:i:s')
            );

            $result = wp_mail($recipient, $subject, $message);
            if ($result) {
                add_settings_error('earthin_smtp', 'test_success', sprintf(__('Test email successfully delivered to %s!', 'earthin-smtp-mailer'), $recipient), 'updated');
            } else {
                add_settings_error('earthin_smtp', 'test_failed', __('Failed to send test email. Please check your SMTP host, port, credentials, and firewall.', 'earthin-smtp-mailer'), 'error');
            }
        }
    }

    public function render_admin_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        $is_enabled = !empty($this->settings['enabled']);
        $status_label = $is_enabled ? __('SMTP Routing Active', 'earthin-smtp-mailer') : __('SMTP Disabled', 'earthin-smtp-mailer');
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
                .earthin-btn-primary { background: #059669 !important; border-color: #059669 !important; color: #ffffff !important; font-weight: 700 !important; padding: 8px 22px !important; border-radius: 8px !important; box-shadow: 0 2px 4px rgba(5,150,105,0.2) !important; text-shadow: none !important; cursor: pointer; }
                .earthin-btn-primary:hover { background: #047857 !important; }
                .earthin-btn-secondary { background: #f8fafc !important; border: 1px solid #cbd5e1 !important; color: #334155 !important; font-weight: 700 !important; padding: 8px 18px !important; border-radius: 8px !important; text-shadow: none !important; cursor: pointer; }
                .earthin-btn-secondary:hover { background: #f1f5f9 !important; }
                .earthin-badge-pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
                .earthin-badge-success { background: #ecfdf5; color: #059669; }
                .earthin-badge-warning { background: #fffbeb; color: #d97706; }
                .earthin-input { border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 14px; width: 100%; max-width: 420px; }
                .earthin-input:focus { border-color: #059669; outline: none; box-shadow: 0 0 0 3px rgba(5,150,105,0.15); }
                .earthin-select { border: 1px solid #cbd5e1; border-radius: 8px; padding: 7px 12px; font-size: 14px; }
            </style>

            <header class="earthin-header">
                <div class="earthin-header-left">
                    <span class="earthin-logo-badge">🌱 Earthin</span>
                    <div class="earthin-title-group">
                        <h1>
                            <?php echo esc_html__('Earthin SMTP Mailer', 'earthin-smtp-mailer'); ?>
                            <span class="earthin-version-tag">v1.0.0</span>
                            <span class="earthin-badge-pill <?php echo esc_attr($status_badge_class); ?>"><?php echo esc_html($status_label); ?></span>
                        </h1>
                        <p><?php echo esc_html__('Enterprise transactional SMTP routing engine engineered by Shaurya Joshi.', 'earthin-smtp-mailer'); ?></p>
                    </div>
                </div>
                <div class="earthin-header-links">
                    <a href="https://plugins.earthin.online" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Plugin Hub', 'earthin-smtp-mailer'); ?> &nearr;</a>
                    <a href="https://plugins.earthin.online/docs" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Documentation', 'earthin-smtp-mailer'); ?></a>
                    <a href="https://plugins.earthin.online/support" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Support', 'earthin-smtp-mailer'); ?></a>
                </div>
            </header>

            <?php settings_errors('earthin_smtp'); ?>

            <div class="earthin-grid">
                <div class="earthin-card">
                    <div class="earthin-card-header">
                        <h2 class="earthin-card-title"><?php echo esc_html__('SMTP Gateway Configuration', 'earthin-smtp-mailer'); ?></h2>
                    </div>

                    <form method="post">
                        <?php wp_nonce_field('earthin_smtp_save_nonce'); ?>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><?php echo esc_html__('Enable Custom SMTP', 'earthin-smtp-mailer'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="enabled" value="1" <?php checked(1, $this->settings['enabled']); ?> />
                                        <strong><?php echo esc_html__('Route all WordPress outbound emails through authenticated SMTP.', 'earthin-smtp-mailer'); ?></strong>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('SMTP Host', 'earthin-smtp-mailer'); ?></th>
                                <td>
                                    <input type="text" name="host" value="<?php echo esc_attr($this->settings['host']); ?>" placeholder="smtp.gmail.com, mail.example.com" class="earthin-input" required />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('Encryption & Port', 'earthin-smtp-mailer'); ?></th>
                                <td>
                                    <select name="encryption" class="earthin-select">
                                        <option value="tls" <?php selected('tls', $this->settings['encryption']); ?>>TLS (Recommended - Port 587)</option>
                                        <option value="ssl" <?php selected('ssl', $this->settings['encryption']); ?>>SSL (Port 465)</option>
                                        <option value="none" <?php selected('none', $this->settings['encryption']); ?>>None (Port 25)</option>
                                    </select>
                                    &nbsp;
                                    <label><?php echo esc_html__('Port:', 'earthin-smtp-mailer'); ?>
                                        <input type="number" name="port" value="<?php echo esc_attr($this->settings['port']); ?>" class="earthin-input" style="width:90px;" />
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('Authentication', 'earthin-smtp-mailer'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="auth" value="1" <?php checked(1, $this->settings['auth']); ?> />
                                        <?php echo esc_html__('SMTP server requires authentication credentials', 'earthin-smtp-mailer'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('SMTP Username', 'earthin-smtp-mailer'); ?></th>
                                <td>
                                    <input type="text" name="username" value="<?php echo esc_attr($this->settings['username']); ?>" class="earthin-input" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('SMTP Password', 'earthin-smtp-mailer'); ?></th>
                                <td>
                                    <input type="password" name="password" placeholder="<?php echo !empty($this->settings['password']) ? '••••••••' : ''; ?>" class="earthin-input" />
                                    <p class="description"><?php echo esc_html__('Leave blank to retain current secure password.', 'earthin-smtp-mailer'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('From Email Address', 'earthin-smtp-mailer'); ?></th>
                                <td>
                                    <input type="email" name="from_email" value="<?php echo esc_attr($this->settings['from_email']); ?>" class="earthin-input" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('From Name', 'earthin-smtp-mailer'); ?></th>
                                <td>
                                    <input type="text" name="from_name" value="<?php echo esc_attr($this->settings['from_name']); ?>" class="earthin-input" />
                                </td>
                            </tr>
                        </table>

                        <p class="submit" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #f1f5f9;">
                            <input type="submit" name="earthin_save_smtp" class="button earthin-btn-primary" value="<?php echo esc_attr__('Save SMTP Settings', 'earthin-smtp-mailer'); ?>" />
                        </p>
                    </form>
                </div>

                <div>
                    <div class="earthin-card">
                        <div class="earthin-card-header">
                            <h2 class="earthin-card-title"><?php echo esc_html__('Send Test Email', 'earthin-smtp-mailer'); ?></h2>
                        </div>
                        <p style="font-size: 13px; color: #64748b; line-height: 1.5;">
                            <?php echo esc_html__('Verify that your SMTP configuration connects properly and successfully dispatches inbox-ready emails.', 'earthin-smtp-mailer'); ?>
                        </p>
                        <form method="post" style="margin-top: 15px;">
                            <?php wp_nonce_field('earthin_smtp_test_nonce'); ?>
                            <p>
                                <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:5px;">
                                    <?php echo esc_html__('Destination Email Address:', 'earthin-smtp-mailer'); ?>
                                </label>
                                <input type="email" name="test_email" value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>" class="earthin-input" required />
                            </p>
                            <p style="margin-top: 14px;">
                                <input type="submit" name="earthin_send_test_email" class="button earthin-btn-secondary" value="<?php echo esc_attr__('Send Test Message', 'earthin-smtp-mailer'); ?>" />
                            </p>
                        </form>
                    </div>

                    <div class="earthin-card">
                        <div class="earthin-card-header">
                            <h2 class="earthin-card-title"><?php echo esc_html__('Connection Status', 'earthin-smtp-mailer'); ?></h2>
                        </div>
                        <p style="font-size: 13px; color: #475569; margin: 6px 0;">
                            <strong><?php echo esc_html__('Current Host:', 'earthin-smtp-mailer'); ?></strong>
                            <code><?php echo esc_html($this->settings['host'] ?: 'None'); ?></code>
                        </p>
                        <p style="font-size: 13px; color: #475569; margin: 6px 0;">
                            <strong><?php echo esc_html__('Port & Security:', 'earthin-smtp-mailer'); ?></strong>
                            <code><?php echo esc_html((string)$this->settings['port'] . ' (' . strtoupper($this->settings['encryption']) . ')'); ?></code>
                        </p>
                        <p style="font-size: 13px; color: #475569; margin: 6px 0;">
                            <strong><?php echo esc_html__('Authenticated:', 'earthin-smtp-mailer'); ?></strong>
                            <code><?php echo !empty($this->settings['auth']) ? 'Yes' : 'No'; ?></code>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_SMTP_Mailer', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-smtp-mailer')) . '">' . __('Settings', 'earthin-smtp-mailer') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
