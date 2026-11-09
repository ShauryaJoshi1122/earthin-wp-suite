<?php
/**
 * Plugin Name: Earthin Chat Connect
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-chat-connect
 * Description: Connect with your website visitors through WhatsApp, Telegram, Phone, Email, and SMS with a lightweight, privacy-focused floating button.
 * Version: 1.0.0
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: earthin-chat-connect
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('EARTHIN_CHAT_CONNECT_VERSION', '1.0.0');
define('EARTHIN_CHAT_CONNECT_PATH', plugin_dir_path(__FILE__));
define('EARTHIN_CHAT_CONNECT_URL', plugin_dir_url(__FILE__));

class Earthin_Chat_Connect {
    private static $instance = null;
    private $option_name = 'earthin_chat_connect_settings';

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_footer', [$this, 'render_chat_widget']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
    }

    public function default_settings() {
        return [
            'enabled' => 1,
            'channels' => [
                'whatsapp' => [
                    'active' => 1,
                    'phone' => '',
                    'message' => 'Hello! I need assistance with your services.',
                    'label' => 'WhatsApp',
                ],
                'telegram' => [
                    'active' => 0,
                    'username' => '',
                    'label' => 'Telegram',
                ],
                'phone' => [
                    'active' => 0,
                    'number' => '',
                    'label' => 'Call Us',
                ],
                'email' => [
                    'active' => 0,
                    'address' => '',
                    'subject' => 'Inquiry from website',
                    'label' => 'Send Email',
                ],
            ],
            'position' => 'bottom-right', // 'bottom-right', 'bottom-left'
            'button_color' => '#10b981',
            'button_icon' => 'chat', // 'chat', 'support', 'message'
            'tooltip_text' => 'Need help? Chat with us!',
            'callout_enabled' => 1,
            'callout_delay' => 3,
            'hide_on_mobile' => 0,
            'hide_on_desktop' => 0,
        ];
    }

    public function get_settings() {
        $saved = get_option($this->option_name, []);
        return wp_parse_args($saved, $this->default_settings());
    }

    public function add_admin_menu() {
        add_options_page(
            __('Earthin Chat Connect', 'earthin-chat-connect'),
            __('Chat Connect', 'earthin-chat-connect'),
            'manage_options',
            'earthin-chat-connect',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting('earthin_chat_connect_group', $this->option_name, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings'],
            'default' => $this->default_settings(),
        ]);
    }

    public function sanitize_settings($input) {
        $sanitized = $this->default_settings();

        $sanitized['enabled'] = !empty($input['enabled']) ? 1 : 0;
        $sanitized['position'] = in_array($input['position'] ?? '', ['bottom-right', 'bottom-left'], true) ? $input['position'] : 'bottom-right';
        $sanitized['button_color'] = sanitize_hex_color($input['button_color'] ?? '#10b981') ?: '#10b981';
        $sanitized['tooltip_text'] = sanitize_text_field($input['tooltip_text'] ?? '');
        $sanitized['callout_enabled'] = !empty($input['callout_enabled']) ? 1 : 0;
        $sanitized['callout_delay'] = max(0, min(60, intval($input['callout_delay'] ?? 3)));
        $sanitized['hide_on_mobile'] = !empty($input['hide_on_mobile']) ? 1 : 0;
        $sanitized['hide_on_desktop'] = !empty($input['hide_on_desktop']) ? 1 : 0;

        // WhatsApp
        $sanitized['channels']['whatsapp']['active'] = !empty($input['channels']['whatsapp']['active']) ? 1 : 0;
        $sanitized['channels']['whatsapp']['phone'] = preg_replace('/[^0-9]/', '', $input['channels']['whatsapp']['phone'] ?? '');
        $sanitized['channels']['whatsapp']['message'] = sanitize_text_field($input['channels']['whatsapp']['message'] ?? '');
        $sanitized['channels']['whatsapp']['label'] = sanitize_text_field($input['channels']['whatsapp']['label'] ?? 'WhatsApp');

        // Telegram
        $sanitized['channels']['telegram']['active'] = !empty($input['channels']['telegram']['active']) ? 1 : 0;
        $sanitized['channels']['telegram']['username'] = sanitize_text_field(ltrim($input['channels']['telegram']['username'] ?? '', '@'));
        $sanitized['channels']['telegram']['label'] = sanitize_text_field($input['channels']['telegram']['label'] ?? 'Telegram');

        // Phone
        $sanitized['channels']['phone']['active'] = !empty($input['channels']['phone']['active']) ? 1 : 0;
        $sanitized['channels']['phone']['number'] = sanitize_text_field($input['channels']['phone']['number'] ?? '');
        $sanitized['channels']['phone']['label'] = sanitize_text_field($input['channels']['phone']['label'] ?? 'Call Us');

        // Email
        $sanitized['channels']['email']['active'] = !empty($input['channels']['email']['active']) ? 1 : 0;
        $sanitized['channels']['email']['address'] = sanitize_email($input['channels']['email']['address'] ?? '');
        $sanitized['channels']['email']['subject'] = sanitize_text_field($input['channels']['email']['subject'] ?? '');
        $sanitized['channels']['email']['label'] = sanitize_text_field($input['channels']['email']['label'] ?? 'Send Email');

        return $sanitized;
    }

    public function enqueue_frontend_assets() {
        $settings = $this->get_settings();
        if (empty($settings['enabled'])) {
            return;
        }

        wp_register_style('earthin-chat-connect', false);
        wp_enqueue_style('earthin-chat-connect');

        $pos_css = ($settings['position'] === 'bottom-left') ? 'left: 24px;' : 'right: 24px;';
        $css = "
            .earthin-chat-wrapper {
                position: fixed;
                bottom: 24px;
                {$pos_css}
                z-index: 999999;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            }
            .earthin-chat-main-btn {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: {$settings['button_color']};
                color: #ffffff;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 14px rgba(0,0,0,0.2);
                cursor: pointer;
                border: none;
                transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .earthin-chat-main-btn:hover {
                transform: scale(1.08);
            }
            .earthin-chat-panel {
                display: none;
                position: absolute;
                bottom: 70px;
                {$pos_css}
                width: 280px;
                background: #ffffff;
                border-radius: 14px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.18);
                overflow: hidden;
                border: 1px solid #e2e8f0;
            }
            .earthin-chat-header {
                background: {$settings['button_color']};
                color: #ffffff;
                padding: 16px;
                font-weight: 600;
                font-size: 15px;
            }
            .earthin-chat-list {
                list-style: none;
                margin: 0;
                padding: 8px 0;
            }
            .earthin-chat-item {
                display: flex;
                align-items: center;
                padding: 12px 18px;
                color: #1e293b;
                text-decoration: none;
                font-size: 14px;
                font-weight: 500;
                transition: background 0.15s;
            }
            .earthin-chat-item:hover {
                background: #f1f5f9;
                color: #0f172a;
            }
            .earthin-chat-icon {
                margin-right: 12px;
                width: 20px;
                height: 20px;
            }
        ";
        wp_add_inline_style('earthin-chat-connect', $css);
    }

    public function render_chat_widget() {
        $settings = $this->get_settings();
        if (empty($settings['enabled'])) {
            return;
        }

        $active_channels = [];
        if (!empty($settings['channels']['whatsapp']['active']) && !empty($settings['channels']['whatsapp']['phone'])) {
            $msg = urlencode($settings['channels']['whatsapp']['message']);
            $url = "https://wa.me/{$settings['channels']['whatsapp']['phone']}?text={$msg}";
            $active_channels[] = [
                'type' => 'whatsapp',
                'label' => $settings['channels']['whatsapp']['label'],
                'url' => $url,
            ];
        }
        if (!empty($settings['channels']['telegram']['active']) && !empty($settings['channels']['telegram']['username'])) {
            $url = "https://t.me/" . rawurlencode($settings['channels']['telegram']['username']);
            $active_channels[] = [
                'type' => 'telegram',
                'label' => $settings['channels']['telegram']['label'],
                'url' => $url,
            ];
        }
        if (!empty($settings['channels']['phone']['active']) && !empty($settings['channels']['phone']['number'])) {
            $url = "tel:" . esc_attr($settings['channels']['phone']['number']);
            $active_channels[] = [
                'type' => 'phone',
                'label' => $settings['channels']['phone']['label'],
                'url' => $url,
            ];
        }
        if (!empty($settings['channels']['email']['active']) && !empty($settings['channels']['email']['address'])) {
            $subj = rawurlencode($settings['channels']['email']['subject']);
            $url = "mailto:{$settings['channels']['email']['address']}?subject={$subj}";
            $active_channels[] = [
                'type' => 'email',
                'label' => $settings['channels']['email']['label'],
                'url' => $url,
            ];
        }

        if (empty($active_channels)) {
            return;
        }
        ?>
        <div class="earthin-chat-wrapper" id="earthin-chat-connect">
            <?php if (count($active_channels) === 1): ?>
                <a href="<?php echo esc_url($active_channels[0]['url']); ?>" target="_blank" rel="noopener noreferrer" class="earthin-chat-main-btn" aria-label="<?php echo esc_attr($active_channels[0]['label']); ?>">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                </a>
            <?php else: ?>
                <button type="button" class="earthin-chat-main-btn" id="earthin-chat-toggle" aria-expanded="false" aria-label="<?php esc_attr_e('Open contact options', 'earthin-chat-connect'); ?>">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                </button>
                <div class="earthin-chat-panel" id="earthin-chat-panel">
                    <div class="earthin-chat-header"><?php echo esc_html($settings['tooltip_text'] ?: __('Start a conversation', 'earthin-chat-connect')); ?></div>
                    <ul class="earthin-chat-list">
                        <?php foreach ($active_channels as $channel): ?>
                            <li>
                                <a href="<?php echo esc_url($channel['url']); ?>" target="_blank" rel="noopener noreferrer" class="earthin-chat-item">
                                    <span><?php echo esc_html($channel['label']); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <script>
                (function() {
                    var toggle = document.getElementById('earthin-chat-toggle');
                    var panel = document.getElementById('earthin-chat-panel');
                    if (toggle && panel) {
                        toggle.addEventListener('click', function(e) {
                            e.stopPropagation();
                            var isShown = panel.style.display === 'block';
                            panel.style.display = isShown ? 'none' : 'block';
                            toggle.setAttribute('aria-expanded', isShown ? 'false' : 'true');
                        });
                        document.addEventListener('click', function(e) {
                            if (!panel.contains(e.target) && e.target !== toggle) {
                                panel.style.display = 'none';
                                toggle.setAttribute('aria-expanded', 'false');
                            }
                        });
                    }
                })();
                </script>
            <?php endif; ?>
        </div>
        <?php
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'earthin-chat-connect'));
        }
        $settings = $this->get_settings();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Earthin Chat Connect Settings', 'earthin-chat-connect'); ?></h1>
            <p><?php esc_html_e('Connect directly with your website visitors via WhatsApp, Telegram, Phone, and Email.', 'earthin-chat-connect'); ?></p>

            <form method="post" action="options.php">
                <?php
                settings_fields('earthin_chat_connect_group');
                ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('Enable Chat Button', 'earthin-chat-connect'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[enabled]" value="1" <?php checked($settings['enabled'], 1); ?> />
                                <?php esc_html_e('Display chat widget on front end', 'earthin-chat-connect'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Widget Position', 'earthin-chat-connect'); ?></th>
                        <td>
                            <select name="<?php echo esc_attr($this->option_name); ?>[position]">
                                <option value="bottom-right" <?php selected($settings['position'], 'bottom-right'); ?>><?php esc_html_e('Bottom Right', 'earthin-chat-connect'); ?></option>
                                <option value="bottom-left" <?php selected($settings['position'], 'bottom-left'); ?>><?php esc_html_e('Bottom Left', 'earthin-chat-connect'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Button Color', 'earthin-chat-connect'); ?></th>
                        <td>
                            <input type="color" name="<?php echo esc_attr($this->option_name); ?>[button_color]" value="<?php echo esc_attr($settings['button_color']); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Header / Callout Text', 'earthin-chat-connect'); ?></th>
                        <td>
                            <input type="text" class="regular-text" name="<?php echo esc_attr($this->option_name); ?>[tooltip_text]" value="<?php echo esc_attr($settings['tooltip_text']); ?>" />
                        </td>
                    </tr>
                </table>

                <h2 class="title"><?php esc_html_e('Channels Configuration', 'earthin-chat-connect'); ?></h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('WhatsApp', 'earthin-chat-connect'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[channels][whatsapp][active]" value="1" <?php checked($settings['channels']['whatsapp']['active'], 1); ?> />
                                <?php esc_html_e('Enable WhatsApp', 'earthin-chat-connect'); ?>
                            </label>
                            <br><br>
                            <label><?php esc_html_e('Phone Number (with country code, digits only):', 'earthin-chat-connect'); ?><br>
                            <input type="text" class="regular-text" name="<?php echo esc_attr($this->option_name); ?>[channels][whatsapp][phone]" value="<?php echo esc_attr($settings['channels']['whatsapp']['phone']); ?>" placeholder="14155552671" /></label>
                            <br><br>
                            <label><?php esc_html_e('Default Pre-filled Message:', 'earthin-chat-connect'); ?><br>
                            <input type="text" class="large-text" name="<?php echo esc_attr($this->option_name); ?>[channels][whatsapp][message]" value="<?php echo esc_attr($settings['channels']['whatsapp']['message']); ?>" /></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Telegram', 'earthin-chat-connect'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[channels][telegram][active]" value="1" <?php checked($settings['channels']['telegram']['active'], 1); ?> />
                                <?php esc_html_e('Enable Telegram', 'earthin-chat-connect'); ?>
                            </label>
                            <br><br>
                            <label><?php esc_html_e('Telegram Username (without @):', 'earthin-chat-connect'); ?><br>
                            <input type="text" class="regular-text" name="<?php echo esc_attr($this->option_name); ?>[channels][telegram][username]" value="<?php echo esc_attr($settings['channels']['telegram']['username']); ?>" placeholder="your_telegram_handle" /></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Phone Call', 'earthin-chat-connect'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[channels][phone][active]" value="1" <?php checked($settings['channels']['phone']['active'], 1); ?> />
                                <?php esc_html_e('Enable Direct Phone Call', 'earthin-chat-connect'); ?>
                            </label>
                            <br><br>
                            <input type="text" class="regular-text" name="<?php echo esc_attr($this->option_name); ?>[channels][phone][number]" value="<?php echo esc_attr($settings['channels']['phone']['number']); ?>" placeholder="+1 (555) 000-0000" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Email', 'earthin-chat-connect'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[channels][email][active]" value="1" <?php checked($settings['channels']['email']['active'], 1); ?> />
                                <?php esc_html_e('Enable Email Inquiry', 'earthin-chat-connect'); ?>
                            </label>
                            <br><br>
                            <label><?php esc_html_e('Email Address:', 'earthin-chat-connect'); ?><br>
                            <input type="email" class="regular-text" name="<?php echo esc_attr($this->option_name); ?>[channels][email][address]" value="<?php echo esc_attr($settings['channels']['email']['address']); ?>" placeholder="support@yourdomain.com" /></label>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Chat_Connect', 'get_instance']);
