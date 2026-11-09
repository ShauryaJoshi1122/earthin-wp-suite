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
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Earthin Form Builder & Entries', 'earthin-form-builder'); ?></h1>
            <p><?php echo esc_html__('Embed responsive contact forms using shortcode: [earthin_form id="contact" title="Get in Touch"]', 'earthin-form-builder'); ?></p>
            <?php settings_errors('earthin_forms'); ?>

            <div style="background:#fff;padding:24px;border:1px solid #ccd0d4;border-radius:8px;margin-top:20px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
                    <h2><?php echo esc_html__('Form Inquiries & Submissions', 'earthin-form-builder'); ?> (<?php echo count($entries); ?>)</h2>
                    <?php if (!empty($entries)) : ?>
                        <form method="post" onsubmit="return confirm('Clear all saved form entries?');">
                            <?php wp_nonce_field('earthin_clear_entries_nonce'); ?>
                            <input type="submit" name="earthin_clear_entries" class="button button-secondary" style="color:#d63638;" value="<?php echo esc_attr__('Clear All Entries', 'earthin-form-builder'); ?>" />
                        </form>
                    <?php endif; ?>
                </div>

                <?php if (empty($entries)) : ?>
                    <p><?php echo esc_html__('No submissions recorded yet. Embed a shortcode into any post or page to begin capturing leads.', 'earthin-form-builder'); ?></p>
                <?php else : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width:160px;"><?php echo esc_html__('Date', 'earthin-form-builder'); ?></th>
                                <th style="width:140px;"><?php echo esc_html__('Sender Name', 'earthin-form-builder'); ?></th>
                                <th style="width:180px;"><?php echo esc_html__('Email', 'earthin-form-builder'); ?></th>
                                <th><?php echo esc_html__('Message', 'earthin-form-builder'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entries as $e) : ?>
                                <tr>
                                    <td><?php echo esc_html($e['created_at']); ?></td>
                                    <td><strong><?php echo esc_html($e['name']); ?></strong></td>
                                    <td><a href="mailto:<?php echo esc_attr($e['email']); ?>"><?php echo esc_html($e['email']); ?></a></td>
                                    <td><?php echo nl2br(esc_html($e['message'])); ?></td>
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

add_action('plugins_loaded', ['Earthin_Form_Builder', 'get_instance']);
