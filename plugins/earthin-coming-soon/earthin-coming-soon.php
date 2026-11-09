<?php
/**
 * Plugin Name: Earthin Coming Soon & Countdown
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-coming-soon
 * Description: Under construction landing screen with live countdown timer, subscriber lead capture, and secret client preview URL.
 * Version: 1.0.0
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * Text Domain: earthin-coming-soon
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class Earthin_Coming_Soon {
    private static ?Earthin_Coming_Soon $instance = null;
    private array $options;

    public static function get_instance(): Earthin_Coming_Soon {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->options = get_option('earthin_coming_soon_options', [
            'enabled' => 0,
            'headline' => 'Something Amazing is Coming Soon',
            'subheadline' => 'We are putting the finishing touches on our new website. Subscribe below to be notified upon launch.',
            'target_date' => gmdate('Y-m-d', strtotime('+30 days')),
            'secret_key' => 'preview123',
            'lead_capture' => 1,
            'http_503' => 0,
        ]);

        add_action('template_redirect', [$this, 'intercept_coming_soon']);
        add_action('init', [$this, 'handle_lead_capture']);
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_init', [$this, 'handle_admin_actions']);
    }

    public function intercept_coming_soon(): void {
        if (empty($this->options['enabled']) || is_user_logged_in() || is_admin()) {
            return;
        }

        // Check secret bypass key
        $bypass = sanitize_text_field($_GET['earthin_preview'] ?? '');
        if (!empty($bypass) && hash_equals($this->options['secret_key'] ?? 'preview123', $bypass)) {
            // Set temporary cookie for 4 hours
            setcookie('earthin_preview_access', '1', time() + 14400, '/');
            return;
        }

        if (isset($_COOKIE['earthin_preview_access']) && '1' === $_COOKIE['earthin_preview_access']) {
            return;
        }

        if (!empty($this->options['http_503'])) {
            header('HTTP/1.1 503 Service Temporarily Unavailable');
            header('Status: 503 Service Temporarily Unavailable');
            header('Retry-After: 86400');
        }

        $this->render_landing_page();
        exit;
    }

    public function handle_lead_capture(): void {
        if (isset($_POST['earthin_cs_subscribe'], $_POST['cs_email']) && check_admin_referer('earthin_cs_nonce')) {
            $email = sanitize_email(wp_unslash($_POST['cs_email']));
            if (is_email($email)) {
                $subscribers = get_option('earthin_coming_soon_subscribers', []);
                if (!in_array($email, $subscribers, true)) {
                    $subscribers[] = $email;
                    update_option('earthin_coming_soon_subscribers', $subscribers);
                }
            }
            wp_safe_redirect(add_query_arg('subscribed', '1', home_url('/')));
            exit;
        }
    }

    private function render_landing_page(): void {
        $target_timestamp = strtotime($this->options['target_date'] . ' 00:00:00') * 1000;
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo esc_html(get_bloginfo('name')); ?> &mdash; <?php echo esc_html($this->options['headline']); ?></title>
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    background: #0f172a;
                    color: #f8fafc;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    padding: 24px;
                    text-align: center;
                }
                .card {
                    max-width: 600px;
                    background: #1e293b;
                    padding: 48px 32px;
                    border-radius: 16px;
                    border: 1px solid #334155;
                    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3);
                }
                h1 { font-size: 2.2rem; font-weight: 800; color: #ffffff; margin-bottom: 12px; }
                p { font-size: 1.05rem; color: #94a3b8; line-height: 1.6; margin-bottom: 32px; }
                .countdown { display: flex; justify-content: center; gap: 16px; margin-bottom: 36px; }
                .time-box {
                    background: #0f172a;
                    padding: 16px 20px;
                    border-radius: 10px;
                    border: 1px solid #334155;
                    min-width: 80px;
                }
                .time-box span { display: block; font-size: 2rem; font-weight: 800; color: #10b981; }
                .time-box label { font-size: 0.75rem; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em; }
                form { display: flex; gap: 8px; justify-content: center; max-width: 440px; margin: 0 auto; }
                input[type="email"] {
                    flex: 1;
                    padding: 12px 16px;
                    border-radius: 8px;
                    border: 1px solid #475569;
                    background: #0f172a;
                    color: #fff;
                    font-size: 0.95rem;
                }
                button {
                    background: #059669;
                    color: #fff;
                    border: none;
                    padding: 12px 20px;
                    border-radius: 8px;
                    font-weight: 700;
                    cursor: pointer;
                }
                button:hover { background: #047857; }
                .success-msg { background: #064e3b; color: #6ee7b7; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; }
            </style>
        </head>
        <body>
            <div class="card">
                <?php if (isset($_GET['subscribed'])) : ?>
                    <div class="success-msg">Thank you for subscribing! We will notify you upon launch.</div>
                <?php endif; ?>
                <h1><?php echo esc_html($this->options['headline']); ?></h1>
                <p><?php echo esc_html($this->options['subheadline']); ?></p>

                <div class="countdown">
                    <div class="time-box"><span id="days">00</span><label>Days</label></div>
                    <div class="time-box"><span id="hours">00</span><label>Hours</label></div>
                    <div class="time-box"><span id="minutes">00</span><label>Minutes</label></div>
                    <div class="time-box"><span id="seconds">00</span><label>Seconds</label></div>
                </div>

                <?php if (!empty($this->options['lead_capture'])) : ?>
                    <form method="post">
                        <?php wp_nonce_field('earthin_cs_nonce'); ?>
                        <input type="email" name="cs_email" placeholder="Enter your email" required />
                        <button type="submit" name="earthin_cs_subscribe">Notify Me</button>
                    </form>
                <?php endif; ?>
            </div>

            <script>
                var target = <?php echo (int) $target_timestamp; ?>;
                function updateCountdown() {
                    var now = new Date().getTime();
                    var dist = target - now;
                    if (dist < 0) dist = 0;
                    var d = Math.floor(dist / (1000 * 60 * 60 * 24));
                    var h = Math.floor((dist % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    var m = Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60));
                    var s = Math.floor((dist % (1000 * 60)) / 1000);
                    document.getElementById('days').textContent = d < 10 ? '0' + d : d;
                    document.getElementById('hours').textContent = h < 10 ? '0' + h : h;
                    document.getElementById('minutes').textContent = m < 10 ? '0' + m : m;
                    document.getElementById('seconds').textContent = s < 10 ? '0' + s : s;
                }
                setInterval(updateCountdown, 1000);
                updateCountdown();
            </script>
        </body>
        </html>
        <?php
    }

    public function register_admin_menu(): void {
        add_options_page(
            __('Earthin Coming Soon', 'earthin-coming-soon'),
            __('Earthin Coming Soon', 'earthin-coming-soon'),
            'manage_options',
            'earthin-coming-soon',
            [$this, 'render_admin_page']
        );
    }

    public function handle_admin_actions(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['earthin_save_cs']) && check_admin_referer('earthin_cs_admin_nonce')) {
            $this->options['enabled'] = isset($_POST['enabled']) ? 1 : 0;
            $this->options['headline'] = sanitize_text_field($_POST['headline'] ?? '');
            $this->options['subheadline'] = sanitize_text_field($_POST['subheadline'] ?? '');
            $this->options['target_date'] = sanitize_text_field($_POST['target_date'] ?? gmdate('Y-m-d'));
            $this->options['secret_key'] = sanitize_key($_POST['secret_key'] ?? 'preview123');
            $this->options['lead_capture'] = isset($_POST['lead_capture']) ? 1 : 0;
            $this->options['http_503'] = isset($_POST['http_503']) ? 1 : 0;

            update_option('earthin_coming_soon_options', $this->options);
            add_settings_error('earthin_cs', 'saved', __('Coming Soon settings saved.', 'earthin-coming-soon'), 'updated');
        }
    }

    public function render_admin_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $subscribers = get_option('earthin_coming_soon_subscribers', []);
        $preview_url = home_url('/?earthin_preview=' . urlencode($this->options['secret_key'] ?? 'preview123'));
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Earthin Coming Soon & Countdown', 'earthin-coming-soon'); ?></h1>
            <p><?php echo esc_html__('Enable an under-construction splash page while building your site.', 'earthin-coming-soon'); ?></p>
            <?php settings_errors('earthin_cs'); ?>

            <div style="display:flex;gap:20px;margin-top:20px;">
                <div style="flex:2;background:#fff;padding:24px;border:1px solid #ccd0d4;border-radius:8px;">
                    <form method="post">
                        <?php wp_nonce_field('earthin_cs_admin_nonce'); ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e('Enable Coming Soon Mode', 'earthin-coming-soon'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="enabled" value="1" <?php checked(1, $this->options['enabled']); ?> />
                                        <strong><?php esc_html_e('Activate splash screen for all non-logged-in visitors.', 'earthin-coming-soon'); ?></strong>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Headline', 'earthin-coming-soon'); ?></th>
                                <td>
                                    <input type="text" name="headline" value="<?php echo esc_attr($this->options['headline']); ?>" class="large-text" required />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Subheadline', 'earthin-coming-soon'); ?></th>
                                <td>
                                    <textarea name="subheadline" rows="3" class="large-text"><?php echo esc_textarea($this->options['subheadline']); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Launch Target Date', 'earthin-coming-soon'); ?></th>
                                <td>
                                    <input type="date" name="target_date" value="<?php echo esc_attr($this->options['target_date']); ?>" required />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Client Bypass Secret', 'earthin-coming-soon'); ?></th>
                                <td>
                                    <input type="text" name="secret_key" value="<?php echo esc_attr($this->options['secret_key']); ?>" class="regular-text" />
                                    <p class="description">
                                        <?php esc_html_e('Share this secret preview link with clients to let them browse without logging in:', 'earthin-coming-soon'); ?><br>
                                        <code><a href="<?php echo esc_url($preview_url); ?>" target="_blank"><?php echo esc_html($preview_url); ?></a></code>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Lead Capture Form', 'earthin-coming-soon'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="lead_capture" value="1" <?php checked(1, $this->options['lead_capture']); ?> />
                                        <?php esc_html_e('Allow visitors to submit email addresses to be notified upon launch.', 'earthin-coming-soon'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                        <p class="submit">
                            <input type="submit" name="earthin_save_cs" class="button button-primary" value="<?php esc_attr_e('Save Configuration', 'earthin-coming-soon'); ?>" />
                        </p>
                    </form>
                </div>

                <div style="flex:1;background:#fff;padding:24px;border:1px solid #ccd0d4;border-radius:8px;">
                    <h2><?php esc_html_e('Subscribed Leads', 'earthin-coming-soon'); ?> (<?php echo count($subscribers); ?>)</h2>
                    <?php if (empty($subscribers)) : ?>
                        <p class="description"><?php esc_html_e('No subscribers yet.', 'earthin-coming-soon'); ?></p>
                    <?php else : ?>
                        <ol style="padding-left:20px;max-height:300px;overflow-y:auto;">
                            <?php foreach ($subscribers as $s) : ?>
                                <li><code><?php echo esc_html($s); ?></code></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Coming_Soon', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function() {
     = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-coming-soon')) . '">' . __('Settings', 'earthin-coming-soon') . '</a>';
    array_unshift(, );
    return ;
});
