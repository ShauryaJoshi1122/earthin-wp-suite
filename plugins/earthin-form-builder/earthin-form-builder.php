<?php
/**
 * Plugin Name: Earthin Form Builder
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-form-builder
 * Description: Lightweight, responsive form builder with honeypot spam prevention, email notifications, and in-dashboard entry management.
 * Version: 1.0.0
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * Text Domain: earthin-form-builder
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class Earthin_Form_Builder {
    private static ?Earthin_Form_Builder $instance = null;

    public static function get_instance(): Earthin_Form_Builder {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode('earthin_form', [$this, 'render_form_shortcode']);
        add_action('init', [$this, 'handle_form_submission']);
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_init', [$this, 'handle_admin_actions']);
    }

    public function render_form_shortcode($atts): string {
        $atts = shortcode_atts([
            'id' => 'contact',
            'title' => __('Contact Us', 'earthin-form-builder'),
            'submit_label' => __('Send Message', 'earthin-form-builder'),
        ], $atts, 'earthin_form');

        $notice = '';
        if (isset($_GET['earthin_form_success']) && '1' === $_GET['earthin_form_success']) {
            $notice = '<div style="background:#ecfdf5;color:#065f46;padding:12px;border-radius:6px;margin-bottom:15px;border:1px solid #a7f3d0;font-weight:600;">' . esc_html__('Thank you! Your message has been received.', 'earthin-form-builder') . '</div>';
        }

        ob_start();
        ?>
        <div class="earthin-form-container" style="max-width:540px;background:#ffffff;padding:24px;border:1px solid #e2e8f0;border-radius:8px;">
            <?php echo $notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <h3 style="margin-top:0;color:#0f172a;"><?php echo esc_html($atts['title']); ?></h3>
            <form method="post" action="">
                <?php wp_nonce_field('earthin_form_submit_' . $atts['id'], 'earthin_form_nonce'); ?>
                <input type="hidden" name="earthin_form_id" value="<?php echo esc_attr($atts['id']); ?>" />
                
                <!-- Honeypot anti-spam field (hidden from real users) -->
                <div style="display:none;" aria-hidden="true">
                    <input type="text" name="earthin_hp_field" tabindex="-1" autocomplete="off" />
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block;font-size:0.875rem;font-weight:600;color:#334155;margin-bottom:4px;">
                        <?php esc_html_e('Your Name', 'earthin-form-builder'); ?> *
                    </label>
                    <input type="text" name="sender_name" required style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;box-sizing:border-box;" />
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block;font-size:0.875rem;font-weight:600;color:#334155;margin-bottom:4px;">
                        <?php esc_html_e('Your Email', 'earthin-form-builder'); ?> *
                    </label>
                    <input type="email" name="sender_email" required style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;box-sizing:border-box;" />
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:0.875rem;font-weight:600;color:#334155;margin-bottom:4px;">
                        <?php esc_html_e('Message', 'earthin-form-builder'); ?> *
                    </label>
                    <textarea name="sender_message" rows="4" required style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;box-sizing:border-box;"></textarea>
                </div>

                <button type="submit" name="earthin_form_submit" style="background:#059669;color:#ffffff;border:none;padding:10px 20px;border-radius:6px;font-weight:600;cursor:pointer;">
                    <?php echo esc_html($atts['submit_label']); ?>
                </button>
            </form>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    public function handle_form_submission(): void {
        if (!isset($_POST['earthin_form_submit'], $_POST['earthin_form_id'])) {
            return;
        }

        $form_id = sanitize_key($_POST['earthin_form_id']);
        if (!check_admin_referer('earthin_form_submit_' . $form_id, 'earthin_form_nonce')) {
            wp_die(esc_html__('Nonce verification failed.', 'earthin-form-builder'));
        }

        // Honeypot trap check
        if (!empty($_POST['earthin_hp_field'])) {
            // Silently drop bot submission
            wp_safe_redirect(add_query_arg('earthin_form_success', '1', wp_get_referer() ?: home_url()));
            exit;
        }

        $name = sanitize_text_field(wp_unslash($_POST['sender_name'] ?? ''));
        $email = sanitize_email(wp_unslash($_POST['sender_email'] ?? ''));
        $message = sanitize_textarea_field(wp_unslash($_POST['sender_message'] ?? ''));

        if (!is_email($email) || empty($name) || empty($message)) {
            return;
        }

        // Store in DB entries option
        $entries = get_option('earthin_form_entries', []);
        $entries[] = [
            'id' => uniqid('entry_'),
            'form_id' => $form_id,
            'name' => $name,
            'email' => $email,
            'message' => $message,
            'ip' => sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'),
            'created_at' => current_time('mysql'),
        ];
        // Keep last 500 entries
        if (count($entries) > 500) {
            $entries = array_slice($entries, -500);
        }
        update_option('earthin_form_entries', $entries);

        // Send email notification
        $admin_email = get_option('admin_email');
        $subject = sprintf('[%s Form Submission] Message from %s', get_bloginfo('name'), $name);
        $body = sprintf("New form inquiry received:\n\nForm: %s\nName: %s\nEmail: %s\n\nMessage:\n%s\n\nDate: %s", $form_id, $name, $email, $message, current_time('mysql'));
        @wp_mail($admin_email, $subject, $body, ['Reply-To: ' . $email]);

        wp_safe_redirect(add_query_arg('earthin_form_success', '1', wp_get_referer() ?: home_url()));
        exit;
    }

    public function register_admin_menu(): void {
        add_menu_page(
            __('Earthin Forms', 'earthin-form-builder'),
            __('Earthin Forms', 'earthin-form-builder'),
            'manage_options',
            'earthin-form-builder',
            [$this, 'render_admin_page'],
            'dashicons-feedback',
            32
        );
    }

    public function handle_admin_actions(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Clear all entries
        if (isset($_POST['earthin_clear_entries']) && check_admin_referer('earthin_clear_entries_nonce')) {
            update_option('earthin_form_entries', []);
            add_settings_error('earthin_forms', 'entries_cleared', __('All form submissions cleared.', 'earthin-form-builder'), 'updated');
        }
    }

    public function render_admin_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $entries = array_reverse(get_option('earthin_form_entries', []));
        $entries_count = count($entries);
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
                .earthin-badge-neutral { background: #f1f5f9; color: #64748b; }
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
                            <?php echo esc_html__('Earthin Form Builder & Entries', 'earthin-form-builder'); ?>
                            <span class="earthin-version-tag">v1.0.0</span>
                            <span class="earthin-badge-pill earthin-badge-success"><?php echo sprintf(esc_html__('%d Submissions', 'earthin-form-builder'), $entries_count); ?></span>
                        </h1>
                        <p><?php echo esc_html__('Lightweight honeypot-protected inquiry capture engineered by Shaurya Joshi.', 'earthin-form-builder'); ?></p>
                    </div>
                </div>
                <div class="earthin-header-links">
                    <a href="https://plugins.earthin.online" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Plugin Hub', 'earthin-form-builder'); ?> &nearr;</a>
                    <a href="https://plugins.earthin.online/docs" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Documentation', 'earthin-form-builder'); ?></a>
                    <a href="https://plugins.earthin.online/support" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Support', 'earthin-form-builder'); ?></a>
                </div>
            </header>

            <?php settings_errors('earthin_forms'); ?>

            <div class="earthin-card" style="padding: 18px 24px;">
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                    <div>
                        <strong style="color:#0f172a; font-size:14px;"><?php echo esc_html__('Embed Shortcode:', 'earthin-form-builder'); ?></strong>
                        <span style="font-size:13px; color:#64748b; margin-left:6px;"><?php echo esc_html__('Paste this shortcode anywhere on your site to render a responsive inquiry form:', 'earthin-form-builder'); ?></span>
                    </div>
                    <div>
                        <code style="background:#ecfdf5; color:#047857; font-weight:700; padding:6px 14px; border-radius:8px; border:1px solid #a7f3d0; font-size:13px;">[earthin_form id="contact" title="Get in Touch"]</code>
                    </div>
                </div>
            </div>

            <div class="earthin-card" style="padding:0; overflow:hidden;">
                <div style="padding: 20px 24px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                    <h2 class="earthin-card-title"><?php echo esc_html__('Recorded Form Submissions', 'earthin-form-builder'); ?> (<?php echo esc_html((string)$entries_count); ?>)</h2>
                    <?php if (!empty($entries)) : ?>
                        <form method="post" onsubmit="return confirm('Clear all saved form inquiries?');">
                            <?php wp_nonce_field('earthin_clear_entries_nonce'); ?>
                            <input type="submit" name="earthin_clear_entries" class="button earthin-btn-danger" value="<?php echo esc_attr__('Clear All Entries', 'earthin-form-builder'); ?>" />
                        </form>
                    <?php endif; ?>
                </div>

                <?php if (empty($entries)) : ?>
                    <div style="padding: 40px; text-align: center; color: #64748b;">
                        <p style="margin: 0; font-size: 14px;"><?php echo esc_html__('No inquiries logged yet. Embed the shortcode into any page to start capturing visitor messages.', 'earthin-form-builder'); ?></p>
                    </div>
                <?php else : ?>
                    <div style="overflow-x: auto;">
                        <table class="earthin-table">
                            <thead>
                                <tr>
                                    <th style="width:160px;"><?php echo esc_html__('Timestamp', 'earthin-form-builder'); ?></th>
                                    <th style="width:170px;"><?php echo esc_html__('Sender Name', 'earthin-form-builder'); ?></th>
                                    <th style="width:220px;"><?php echo esc_html__('Email Address', 'earthin-form-builder'); ?></th>
                                    <th><?php echo esc_html__('Message Inquiry', 'earthin-form-builder'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entries as $e) : ?>
                                    <tr>
                                        <td><span class="earthin-badge-pill earthin-badge-neutral"><?php echo esc_html($e['created_at']); ?></span></td>
                                        <td><strong style="color:#0f172a;"><?php echo esc_html($e['name']); ?></strong></td>
                                        <td><a href="mailto:<?php echo esc_attr($e['email']); ?>" style="color:#059669; font-weight:600; text-decoration:none;"><?php echo esc_html($e['email']); ?></a></td>
                                        <td><div style="max-height: 80px; overflow-y: auto; color:#334155; line-height:1.5;"><?php echo nl2br(esc_html($e['message'])); ?></div></td>
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

add_action('plugins_loaded', ['Earthin_Form_Builder', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=earthin-form-builder')) . '">' . __('Settings', 'earthin-form-builder') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
