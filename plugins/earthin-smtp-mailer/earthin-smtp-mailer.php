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
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Earthin SMTP Mailer', 'earthin-smtp-mailer'); ?></h1>
            <p><?php echo esc_html__('Deliver transactional emails reliably through your authenticated SMTP server.', 'earthin-smtp-mailer'); ?></p>
            <?php settings_errors('earthin_smtp'); ?>

            <div style="display:flex;gap:20px;margin-top:20px;">
                <div style="flex:2;background:#fff;padding:24px;border:1px solid #ccd0d4;border-radius:8px;">
                    <h2><?php echo esc_html__('SMTP Server Settings', 'earthin-smtp-mailer'); ?></h2>
                    <form method="post">
                        <?php wp_nonce_field('earthin_smtp_save_nonce'); ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php echo esc_html__('Enable Custom SMTP', 'earthin-smtp-mailer'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="enabled" value="1" <?php checked(1, $this->settings['enabled']); ?> />
                                        <?php echo esc_html__('Route all WordPress outbound emails through SMTP.', 'earthin-smtp-mailer'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('SMTP Host', 'earthin-smtp-mailer'); ?></th>
                                <td>
                                    <input type="text" name="host" value="<?php echo esc_attr($this->settings['host']); ?>" placeholder="smtp.gmail.com, mail.example.com" class="regular-text" required />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('Encryption & Port', 'earthin-smtp-mailer'); ?></th>
                                <td>
                                    <select name="encryption">
                                        <option value="tls" <?php selected('tls', $this->settings['encryption']); ?>>TLS (Recommended - Port 587)</option>
                                        <option value="ssl" <?php selected('ssl', $this->settings['encryption']); ?>>SSL (Port 465)</option>
                                        <option value="none" <?php selected('none', $this->settings['encryption']); ?>>None (Port 25)</option>
                                    </select>
                                    &nbsp;
                                    <label><?php echo esc_html__('Port:', 'earthin-smtp-mailer'); ?>
                                        <input type="number" name="port" value="<?php echo esc_attr($this->settings['port']); ?>" style="width:80px;" />
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('Authentication', 'earthin-smtp-mailer'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="auth" value="1" <?php checked(1, $this->settings['auth']); ?> />
                                        <?php echo esc_html__('SMTP server requires authentication', 'earthin-smtp-mailer'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('SMTP Username', 'earthin-smtp-mailer'); ?></th>
                                <td>
                                    <input type="text" name="username" value="<?php echo esc_attr($this->settings['username']); ?>" class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('SMTP Password', 'earthin-smtp-mailer'); ?></th>
                                <td>
                                    <input type="password" name="password" placeholder="<?php echo !empty($this->settings['password']) ? '••••••••' : ''; ?>" class="regular-text" />
                                    <p class="description"><?php echo esc_html__('Leave blank to keep existing password.', 'earthin-smtp-mailer'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('From Email Address', 'earthin-smtp-mailer'); ?></th>
                                <td>
                                    <input type="email" name="from_email" value="<?php echo esc_attr($this->settings['from_email']); ?>" class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('From Name', 'earthin-smtp-mailer'); ?></th>
                                <td>
                                    <input type="text" name="from_name" value="<?php echo esc_attr($this->settings['from_name']); ?>" class="regular-text" />
                                </td>
                            </tr>
                        </table>
                        <p class="submit">
                            <input type="submit" name="earthin_save_smtp" class="button button-primary" value="<?php echo esc_attr__('Save SMTP Settings', 'earthin-smtp-mailer'); ?>" />
                        </p>
                    </form>
                </div>

                <div style="flex:1;background:#fff;padding:24px;border:1px solid #ccd0d4;border-radius:8px;">
                    <h2><?php echo esc_html__('Send Test Email', 'earthin-smtp-mailer'); ?></h2>
                    <p class="description"><?php echo esc_html__('Verify that your SMTP configuration can successfully connect and dispatch emails without delivery blocks.', 'earthin-smtp-mailer'); ?></p>
                    <form method="post" style="margin-top:15px;">
                        <?php wp_nonce_field('earthin_smtp_test_nonce'); ?>
                        <p>
                            <label><strong><?php echo esc_html__('Send to Email:', 'earthin-smtp-mailer'); ?></strong></label><br>
                            <input type="email" name="test_email" value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>" class="regular-text" style="width:100%;margin-top:5px;" required />
                        </p>
                        <p>
                            <input type="submit" name="earthin_send_test_email" class="button button-secondary" value="<?php echo esc_attr__('Send Test Message', 'earthin-smtp-mailer'); ?>" />
                        </p>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_SMTP_Mailer', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function() {
     = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-smtp-mailer')) . '">' . __('Settings', 'earthin-smtp-mailer') . '</a>';
    array_unshift(, );
    return ;
});
