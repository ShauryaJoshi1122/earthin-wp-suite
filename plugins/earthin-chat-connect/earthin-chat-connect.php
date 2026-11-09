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

        $active_channels_count = 0;
        foreach (['whatsapp', 'telegram', 'phone', 'email'] as $c) {
            if (!empty($settings['channels'][$c]['active'])) {
                $active_channels_count++;
            }
        }
        $is_enabled = !empty($settings['enabled']);
        ?>
        <div class="wrap" style="max-width:1100px; margin:20px auto 40px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,sans-serif;">
            <!-- Header Banner -->
            <div style="display:flex; align-items:center; justify-content:space-between; background:linear-gradient(135deg, #059669 0%, #047857 100%); padding:22px 28px; border-radius:12px; color:#fff; box-shadow:0 4px 18px rgba(5,150,105,0.22); margin-bottom:24px;">
                <div style="display:flex; align-items:center; gap:16px;">
                    <div style="background:rgba(255,255,255,0.2); border-radius:12px; width:50px; height:50px; display:flex; align-items:center; justify-content:center; font-size:26px;">💬</div>
                    <div>
                        <h1 style="color:#fff; margin:0; font-size:22px; font-weight:700; line-height:1.2;"><?php esc_html_e('Earthin Chat Connect', 'earthin-chat-connect'); ?></h1>
                        <p style="margin:4px 0 0; color:#d1fae5; font-size:13px;"><?php esc_html_e('Direct visitor engagement via WhatsApp, Telegram, Phone & Email floating widgets', 'earthin-chat-connect'); ?></p>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="background:rgba(255,255,255,0.18); padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600;">v<?php echo esc_html(EARTHIN_CHAT_CONNECT_VERSION); ?></span>
                    <a href="https://plugins.earthin.online/docs" target="_blank" style="background:#fff; color:#047857; text-decoration:none; padding:6px 14px; border-radius:6px; font-size:12px; font-weight:600;">Documentation</a>
                </div>
            </div>

            <!-- Metric Cards -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px; margin-bottom:24px;">
                <div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
                    <div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e('Widget Status', 'earthin-chat-connect'); ?></div>
                    <div style="font-size:20px; font-weight:700; margin-top:4px; color:<?php echo $is_enabled ? '#059669' : '#94a3b8'; ?>;">
                        <?php echo $is_enabled ? '● Active Front-End' : '○ Disabled'; ?>
                    </div>
                    <div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e('Floating button presence', 'earthin-chat-connect'); ?></div>
                </div>

                <div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
                    <div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e('Active Channels', 'earthin-chat-connect'); ?></div>
                    <div style="font-size:24px; font-weight:700; color:#059669; margin-top:4px;">
                        <?php echo esc_html($active_channels_count); ?> / 4
                    </div>
                    <div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e('WhatsApp, Telegram, Phone, Email', 'earthin-chat-connect'); ?></div>
                </div>

                <div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
                    <div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e('Position', 'earthin-chat-connect'); ?></div>
                    <div style="font-size:18px; font-weight:700; color:#0f172a; margin-top:6px;">
                        <?php echo $settings['position'] === 'bottom-left' ? 'Bottom Left' : 'Bottom Right'; ?>
                    </div>
                    <div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e('Responsive anchored floating icon', 'earthin-chat-connect'); ?></div>
                </div>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields('earthin_chat_connect_group'); ?>

                <div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">
                    <!-- Main Channels & Design Column -->
                    <div>
                        <!-- Widget General Options Card -->
                        <div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
                            <h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
                                <span>⚙️</span> <?php esc_html_e('General Widget Settings', 'earthin-chat-connect'); ?>
                            </h2>

                            <label style="display:flex; align-items:center; gap:10px; margin-bottom:16px; cursor:pointer;">
                                <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[enabled]" value="1" <?php checked($settings['enabled'], 1); ?> />
                                <span style="font-size:14px; font-weight:600; color:#0f172a;"><?php esc_html_e('Enable Chat Button on Website Front-End', 'earthin-chat-connect'); ?></span>
                            </label>

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                                <div>
                                    <label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e('Widget Position', 'earthin-chat-connect'); ?></label>
                                    <select name="<?php echo esc_attr($this->option_name); ?>[position]" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;">
                                        <option value="bottom-right" <?php selected($settings['position'], 'bottom-right'); ?>><?php esc_html_e('Bottom Right', 'earthin-chat-connect'); ?></option>
                                        <option value="bottom-left" <?php selected($settings['position'], 'bottom-left'); ?>><?php esc_html_e('Bottom Left', 'earthin-chat-connect'); ?></option>
                                    </select>
                                </div>

                                <div>
                                    <label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e('Button Accent Color', 'earthin-chat-connect'); ?></label>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <input type="color" name="<?php echo esc_attr($this->option_name); ?>[button_color]" value="<?php echo esc_attr($settings['button_color']); ?>" style="width:42px; height:36px; padding:2px; border:1px solid #cbd5e1; border-radius:6px; cursor:pointer;" />
                                        <span style="font-size:13px; color:#64748b; font-family:monospace;"><?php echo esc_html($settings['button_color']); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top:16px;">
                                <label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e('Callout / Tooltip Text', 'earthin-chat-connect'); ?></label>
                                <input type="text" class="large-text" name="<?php echo esc_attr($this->option_name); ?>[tooltip_text]" value="<?php echo esc_attr($settings['tooltip_text']); ?>" style="padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;" placeholder="Need help? Chat with us!" />
                            </div>
                        </div>

                        <!-- Communication Channels Card -->
                        <div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
                            <h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
                                <span>📱</span> <?php esc_html_e('Communication Channels', 'earthin-chat-connect'); ?>
                            </h2>

                            <!-- WhatsApp -->
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:18px; margin-bottom:16px;">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <span style="font-size:20px;">🟢</span>
                                        <strong style="font-size:14px; color:#0f172a;">WhatsApp</strong>
                                    </div>
                                    <label style="cursor:pointer; display:flex; align-items:center; gap:6px; font-size:13px; font-weight:600; color:#059669;">
                                        <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[channels][whatsapp][active]" value="1" <?php checked($settings['channels']['whatsapp']['active'], 1); ?> />
                                        <?php esc_html_e('Active', 'earthin-chat-connect'); ?>
                                    </label>
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div>
                                        <label style="font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:4px;"><?php esc_html_e('Phone Number (with country code):', 'earthin-chat-connect'); ?></label>
                                        <input type="text" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;" name="<?php echo esc_attr($this->option_name); ?>[channels][whatsapp][phone]" value="<?php echo esc_attr($settings['channels']['whatsapp']['phone']); ?>" placeholder="14155552671" />
                                    </div>
                                    <div>
                                        <label style="font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:4px;"><?php esc_html_e('Menu Label:', 'earthin-chat-connect'); ?></label>
                                        <input type="text" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;" name="<?php echo esc_attr($this->option_name); ?>[channels][whatsapp][label]" value="<?php echo esc_attr($settings['channels']['whatsapp']['label']); ?>" placeholder="WhatsApp" />
                                    </div>
                                </div>
                                <div style="margin-top:10px;">
                                    <label style="font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:4px;"><?php esc_html_e('Pre-filled Message:', 'earthin-chat-connect'); ?></label>
                                    <input type="text" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;" name="<?php echo esc_attr($this->option_name); ?>[channels][whatsapp][message]" value="<?php echo esc_attr($settings['channels']['whatsapp']['message']); ?>" />
                                </div>
                            </div>

                            <!-- Telegram -->
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:18px; margin-bottom:16px;">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <span style="font-size:20px;">✈️</span>
                                        <strong style="font-size:14px; color:#0f172a;">Telegram</strong>
                                    </div>
                                    <label style="cursor:pointer; display:flex; align-items:center; gap:6px; font-size:13px; font-weight:600; color:#059669;">
                                        <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[channels][telegram][active]" value="1" <?php checked($settings['channels']['telegram']['active'], 1); ?> />
                                        <?php esc_html_e('Active', 'earthin-chat-connect'); ?>
                                    </label>
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div>
                                        <label style="font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:4px;"><?php esc_html_e('Username (without @):', 'earthin-chat-connect'); ?></label>
                                        <input type="text" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;" name="<?php echo esc_attr($this->option_name); ?>[channels][telegram][username]" value="<?php echo esc_attr($settings['channels']['telegram']['username']); ?>" placeholder="your_telegram_handle" />
                                    </div>
                                    <div>
                                        <label style="font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:4px;"><?php esc_html_e('Menu Label:', 'earthin-chat-connect'); ?></label>
                                        <input type="text" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;" name="<?php echo esc_attr($this->option_name); ?>[channels][telegram][label]" value="<?php echo esc_attr($settings['channels']['telegram']['label']); ?>" placeholder="Telegram" />
                                    </div>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:18px; margin-bottom:16px;">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <span style="font-size:20px;">📞</span>
                                        <strong style="font-size:14px; color:#0f172a;"><?php esc_html_e('Direct Phone Call', 'earthin-chat-connect'); ?></strong>
                                    </div>
                                    <label style="cursor:pointer; display:flex; align-items:center; gap:6px; font-size:13px; font-weight:600; color:#059669;">
                                        <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[channels][phone][active]" value="1" <?php checked($settings['channels']['phone']['active'], 1); ?> />
                                        <?php esc_html_e('Active', 'earthin-chat-connect'); ?>
                                    </label>
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div>
                                        <label style="font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:4px;"><?php esc_html_e('Phone Number:', 'earthin-chat-connect'); ?></label>
                                        <input type="text" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;" name="<?php echo esc_attr($this->option_name); ?>[channels][phone][number]" value="<?php echo esc_attr($settings['channels']['phone']['number']); ?>" placeholder="+1 (555) 000-0000" />
                                    </div>
                                    <div>
                                        <label style="font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:4px;"><?php esc_html_e('Menu Label:', 'earthin-chat-connect'); ?></label>
                                        <input type="text" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;" name="<?php echo esc_attr($this->option_name); ?>[channels][phone][label]" value="<?php echo esc_attr($settings['channels']['phone']['label']); ?>" placeholder="Call Us" />
                                    </div>
                                </div>
                            </div>

                            <!-- Email -->
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:18px;">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <span style="font-size:20px;">✉️</span>
                                        <strong style="font-size:14px; color:#0f172a;"><?php esc_html_e('Email Inquiry', 'earthin-chat-connect'); ?></strong>
                                    </div>
                                    <label style="cursor:pointer; display:flex; align-items:center; gap:6px; font-size:13px; font-weight:600; color:#059669;">
                                        <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[channels][email][active]" value="1" <?php checked($settings['channels']['email']['active'], 1); ?> />
                                        <?php esc_html_e('Active', 'earthin-chat-connect'); ?>
                                    </label>
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div>
                                        <label style="font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:4px;"><?php esc_html_e('Email Address:', 'earthin-chat-connect'); ?></label>
                                        <input type="email" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;" name="<?php echo esc_attr($this->option_name); ?>[channels][email][address]" value="<?php echo esc_attr($settings['channels']['email']['address']); ?>" placeholder="support@yourdomain.com" />
                                    </div>
                                    <div>
                                        <label style="font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:4px;"><?php esc_html_e('Subject:', 'earthin-chat-connect'); ?></label>
                                        <input type="text" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;" name="<?php echo esc_attr($this->option_name); ?>[channels][email][subject]" value="<?php echo esc_attr($settings['channels']['email']['subject']); ?>" placeholder="Inquiry from website" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="button button-primary" style="background:#059669; border-color:#047857; color:#fff; font-weight:600; padding:8px 24px; height:auto; border-radius:6px; font-size:14px; box-shadow:0 2px 4px rgba(5,150,105,0.25);">
                            <?php esc_html_e('Save Chat Connect Settings', 'earthin-chat-connect'); ?>
                        </button>
                    </div>

                    <!-- Sidebar Info & Preview -->
                    <div>
                        <div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:20px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:20px;">
                            <h3 style="font-size:14px; font-weight:700; color:#0f172a; margin:0 0 12px;"><?php esc_html_e('Live Widget Preview', 'earthin-chat-connect'); ?></h3>
                            <div style="background:#f1f5f9; border-radius:8px; height:180px; position:relative; overflow:hidden; border:1px dashed #cbd5e1; display:flex; align-items:center; justify-content:center;">
                                <span style="font-size:12px; color:#94a3b8;"><?php esc_html_e('Website Page Simulation', 'earthin-chat-connect'); ?></span>
                                <div style="position:absolute; bottom:16px; <?php echo $settings['position'] === 'bottom-left' ? 'left:16px;' : 'right:16px;'; ?> width:48px; height:48px; background:<?php echo esc_attr($settings['button_color']); ?>; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-size:22px; box-shadow:0 4px 12px rgba(0,0,0,0.18);">
                                    💬
                                </div>
                            </div>
                        </div>

                        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:18px;">
                            <div style="font-size:13px; font-weight:700; color:#166534; margin-bottom:8px;">🔒 <?php esc_html_e('Zero Tracking / 100% Privacy', 'earthin-chat-connect'); ?></div>
                            <p style="font-size:12px; color:#15803d; line-height:1.5; margin:0;">
                                <?php esc_html_e('Earthin Chat Connect communicates directly using native WhatsApp (wa.me) and Telegram protocols without third-party cookies or telemetry servers.', 'earthin-chat-connect'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Chat_Connect', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-chat-connect')) . '">' . __('Settings', 'earthin-chat-connect') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});

