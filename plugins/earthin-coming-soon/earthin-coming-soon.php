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
        $is_active = !empty($this->options['enabled']);
        $status_label = $is_active ? __('Splash Page Active', 'earthin-coming-soon') : __('Site Visible to Public', 'earthin-coming-soon');
        $status_badge_class = $is_active ? 'earthin-badge-warning' : 'earthin-badge-success';
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
                .earthin-btn-secondary { background: #f8fafc !important; border: 1px solid #cbd5e1 !important; color: #334155 !important; font-weight: 700 !important; padding: 8px 18px !important; border-radius: 8px !important; text-shadow: none !important; cursor: pointer; text-decoration: none; display: inline-block; }
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
                            <?php echo esc_html__('Earthin Coming Soon & Countdown', 'earthin-coming-soon'); ?>
                            <span class="earthin-version-tag">v1.0.0</span>
                            <span class="earthin-badge-pill <?php echo esc_attr($status_badge_class); ?>"><?php echo esc_html($status_label); ?></span>
                        </h1>
                        <p><?php echo esc_html__('High-converting launch splash page & countdown clock engineered by Shaurya Joshi.', 'earthin-coming-soon'); ?></p>
                    </div>
                </div>
                <div class="earthin-header-links">
                    <a href="https://plugins.earthin.online" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Plugin Hub', 'earthin-coming-soon'); ?> &nearr;</a>
                    <a href="https://plugins.earthin.online/docs" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Documentation', 'earthin-coming-soon'); ?></a>
                    <a href="https://plugins.earthin.online/support" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Support', 'earthin-coming-soon'); ?></a>
                </div>
            </header>

            <?php settings_errors('earthin_cs'); ?>

            <div class="earthin-grid">
                <div class="earthin-card">
                    <div class="earthin-card-header">
                        <h2 class="earthin-card-title"><?php esc_html_e('Launch Splash Controls', 'earthin-coming-soon'); ?></h2>
                    </div>

                    <form method="post">
                        <?php wp_nonce_field('earthin_cs_admin_nonce'); ?>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><?php esc_html_e('Coming Soon Mode', 'earthin-coming-soon'); ?></th>
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
                                    <input type="text" name="headline" value="<?php echo esc_attr($this->options['headline']); ?>" class="earthin-input" required />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Subheadline Description', 'earthin-coming-soon'); ?></th>
                                <td>
                                    <textarea name="subheadline" rows="3" class="earthin-input"><?php echo esc_textarea($this->options['subheadline']); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Target Launch Date', 'earthin-coming-soon'); ?></th>
                                <td>
                                    <input type="date" name="target_date" value="<?php echo esc_attr($this->options['target_date']); ?>" class="earthin-input" style="max-width:240px;" required />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Client Bypass Secret Key', 'earthin-coming-soon'); ?></th>
                                <td>
                                    <input type="text" name="secret_key" value="<?php echo esc_attr($this->options['secret_key']); ?>" class="earthin-input" style="max-width:240px;" />
                                    <p class="description" style="margin-top:8px;">
                                        <?php esc_html_e('Bypass URL to allow clients to preview the real site without logging in:', 'earthin-coming-soon'); ?><br>
                                        <code style="background:#f1f5f9; padding:4px 8px; border-radius:6px; display:inline-block; margin-top:4px;"><a href="<?php echo esc_url($preview_url); ?>" target="_blank" style="color:#059669; text-decoration:none;"><?php echo esc_html($preview_url); ?> &nearr;</a></code>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Email Lead Capture', 'earthin-coming-soon'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="lead_capture" value="1" <?php checked(1, $this->options['lead_capture']); ?> />
                                        <?php esc_html_e('Display newsletter email capture box on splash page.', 'earthin-coming-soon'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>

                        <p class="submit" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #f1f5f9;">
                            <input type="submit" name="earthin_save_cs" class="button earthin-btn-primary" value="<?php esc_attr_e('Save Configuration', 'earthin-coming-soon'); ?>" />
                        </p>
                    </form>
                </div>

                <div>
                    <div class="earthin-card">
                        <div class="earthin-card-header">
                            <h2 class="earthin-card-title"><?php esc_html_e('Collected Leads', 'earthin-coming-soon'); ?></h2>
                        </div>

                        <div class="earthin-stat-box">
                            <div class="earthin-stat-num"><?php echo count($subscribers); ?></div>
                            <div class="earthin-stat-label"><?php esc_html_e('Subscribed Prospects', 'earthin-coming-soon'); ?></div>
                        </div>

                        <?php if (empty($subscribers)) : ?>
                            <p style="font-size:13px; color:#64748b;"><?php esc_html_e('No email subscribers recorded yet.', 'earthin-coming-soon'); ?></p>
                        <?php else : ?>
                            <ul style="padding-left:0; list-style:none; max-height:260px; overflow-y:auto; margin:0;">
                                <?php foreach ($subscribers as $s) : ?>
                                    <li style="padding:6px 0; border-bottom:1px solid #f1f5f9; font-size:13px; color:#334155;">
                                        ✉️ <a href="mailto:<?php echo esc_attr($s); ?>" style="color:#059669; text-decoration:none;"><?php echo esc_html($s); ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div class="earthin-card">
                        <div class="earthin-card-header">
                            <h2 class="earthin-card-title"><?php esc_html_e('Live Preview', 'earthin-coming-soon'); ?></h2>
                        </div>
                        <p style="font-size:13px; color:#64748b; line-height:1.5;">
                            <?php esc_html_e('See exactly how your countdown clock and animated splash page look to visitors.', 'earthin-coming-soon'); ?>
                        </p>
                        <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" class="button earthin-btn-secondary" style="margin-top:8px;">
                            <?php esc_html_e('Open Frontend in New Tab', 'earthin-coming-soon'); ?> &nearr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Coming_Soon', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-coming-soon')) . '">' . __('Settings', 'earthin-coming-soon') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
