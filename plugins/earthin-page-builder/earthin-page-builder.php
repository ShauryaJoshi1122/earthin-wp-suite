<?php
/**
 * Plugin Name: Earthin Page Builder
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-page-builder
 * Description: Fast, lightweight visual page builder for WordPress generating clean, bloat-free semantic markup.
 * Version: 1.0.0
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: earthin-page-builder
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('EARTHIN_PAGE_BUILDER_VERSION', '1.0.0');
define('EARTHIN_PAGE_BUILDER_PATH', plugin_dir_path(__FILE__));
define('EARTHIN_PAGE_BUILDER_URL', plugin_dir_url(__FILE__));

class Earthin_Page_Builder {
    private static $instance = null;
    private $option_name = 'earthin_page_builder_settings';

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('add_meta_boxes', [$this, 'add_builder_metabox']);
        add_action('save_post', [$this, 'save_builder_data']);
        add_filter('the_content', [$this, 'render_frontend_layout']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_styles']);
    }

    public function default_settings() {
        return [
            'post_types' => ['page', 'post'],
            'container_width' => 1200,
            'accent_color' => '#059669',
        ];
    }

    public function get_settings() {
        $saved = get_option($this->option_name, []);
        return wp_parse_args($saved, $this->default_settings());
    }

    public function add_admin_menu() {
        add_options_page(
            __('Earthin Page Builder', 'earthin-page-builder'),
            __('Earthin Builder', 'earthin-page-builder'),
            'manage_options',
            'earthin-page-builder',
            [$this, 'render_admin_page']
        );
    }

    public function register_settings() {
        register_setting('earthin_pb_settings_group', $this->option_name, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings'],
            'default' => $this->default_settings(),
        ]);
    }

    public function sanitize_settings($input) {
        $output = $this->default_settings();
        if (isset($input['post_types']) && is_array($input['post_types'])) {
            $output['post_types'] = array_values(array_map('sanitize_key', $input['post_types']));
        }
        $width = isset($input['container_width']) ? intval($input['container_width']) : 1200;
        $output['container_width'] = max(600, min(2400, $width));
        $output['accent_color'] = sanitize_hex_color($input['accent_color'] ?? '#059669') ?: '#059669';
        return $output;
    }

    public function enqueue_frontend_styles() {
        if (is_singular()) {
            $is_builder = get_post_meta(get_the_ID(), '_earthin_builder_enabled', true);
            if ($is_builder) {
                $settings = $this->get_settings();
                $width = intval($settings['container_width']);
                $accent = esc_attr($settings['accent_color']);

                wp_register_style('earthin-pb-styles', false);
                wp_enqueue_style('earthin-pb-styles');
                $css = "
                    .earthin-pb-container { max-width: {$width}px; margin: 0 auto; padding: 0 20px; font-family: inherit; }
                    .earthin-pb-section { padding: 40px 0; }
                    .earthin-pb-row { display: flex; flex-wrap: wrap; gap: 24px; margin-bottom: 24px; }
                    .earthin-pb-col { flex: 1 1 0; min-width: 280px; }
                    .earthin-pb-heading { font-weight: 700; margin-bottom: 12px; line-height: 1.25; }
                    .earthin-pb-btn { display: inline-block; padding: 12px 24px; border-radius: 6px; font-weight: 600; text-decoration: none; transition: opacity 0.2s; }
                    .earthin-pb-btn-primary { background: {$accent}; color: #ffffff !important; }
                    .earthin-pb-btn-secondary { background: #3b82f6; color: #ffffff !important; }
                    .earthin-pb-alert { padding: 16px; border-radius: 8px; margin: 16px 0; }
                    .earthin-pb-alert-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; }
                    .earthin-pb-alert-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
                ";
                wp_add_inline_style('earthin-pb-styles', $css);
            }
        }
    }

    public function add_builder_metabox() {
        $settings = $this->get_settings();
        $post_types = !empty($settings['post_types']) && is_array($settings['post_types']) ? $settings['post_types'] : ['page', 'post'];
        foreach ($post_types as $pt) {
            add_meta_box(
                'earthin_page_builder_box',
                __('Earthin Visual Layout Builder', 'earthin-page-builder'),
                [$this, 'render_metabox'],
                $pt,
                'normal',
                'high'
            );
        }
    }

    public function render_metabox($post) {
        wp_nonce_field('earthin_pb_save_nonce', 'earthin_pb_nonce');
        $enabled = get_post_meta($post->ID, '_earthin_builder_enabled', true);
        $layout_raw = get_post_meta($post->ID, '_earthin_builder_layout', true);
        $layout = !empty($layout_raw) ? json_decode($layout_raw, true) : [];
        if (!is_array($layout)) {
            $layout = [];
        }
        ?>
        <style>
            .earthin-pb-editor-wrap { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; }
            .earthin-pb-block-item { background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 14px; margin-bottom: 12px; }
            .earthin-pb-block-head { display: flex; justify-content: space-between; font-weight: 600; margin-bottom: 10px; }
        </style>

        <div class="earthin-pb-editor-wrap">
            <p>
                <label>
                    <input type="checkbox" name="earthin_builder_enabled" value="1" <?php checked($enabled, '1'); ?> />
                    <strong><?php esc_html_e('Enable Earthin Page Builder for this content', 'earthin-page-builder'); ?></strong>
                </label>
            </p>

            <div id="earthin-pb-canvas">
                <p class="description"><?php esc_html_e('Configure structured layout blocks below. Content will render cleanly on the frontend without heavy wrapper bloat.', 'earthin-page-builder'); ?></p>
                
                <div id="earthin-pb-blocks-container">
                    <?php if (!empty($layout)): ?>
                        <?php foreach ($layout as $i => $block): ?>
                            <div class="earthin-pb-block-item">
                                <div class="earthin-pb-block-head">
                                    <span><?php echo esc_html(strtoupper($block['type'] ?? 'BLOCK')); ?></span>
                                    <button type="button" class="button button-link-delete" onclick="this.closest('.earthin-pb-block-item').remove()"><?php esc_html_e('Remove', 'earthin-page-builder'); ?></button>
                                </div>
                                <input type="hidden" name="earthin_pb_blocks[<?php echo $i; ?>][type]" value="<?php echo esc_attr($block['type']); ?>" />
                                <?php if ($block['type'] === 'heading'): ?>
                                    <input type="text" class="large-text" name="earthin_pb_blocks[<?php echo $i; ?>][text]" value="<?php echo esc_attr($block['text'] ?? ''); ?>" placeholder="Heading Title" />
                                <?php elseif ($block['type'] === 'button'): ?>
                                    <input type="text" style="width:48%;" name="earthin_pb_blocks[<?php echo $i; ?>][label]" value="<?php echo esc_attr($block['label'] ?? ''); ?>" placeholder="Button Label" />
                                    <input type="url" style="width:48%;" name="earthin_pb_blocks[<?php echo $i; ?>][url]" value="<?php echo esc_attr($block['url'] ?? ''); ?>" placeholder="https://example.com" />
                                <?php else: ?>
                                    <textarea class="large-text" rows="3" name="earthin_pb_blocks[<?php echo $i; ?>][content]"><?php echo esc_textarea($block['content'] ?? ''); ?></textarea>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div style="display: flex; gap: 8px; margin-top: 14px;">
                    <button type="button" class="button" onclick="EarthinPB.addBlock('heading')">+ <?php esc_html_e('Add Heading', 'earthin-page-builder'); ?></button>
                    <button type="button" class="button" onclick="EarthinPB.addBlock('text')">+ <?php esc_html_e('Add Text Block', 'earthin-page-builder'); ?></button>
                    <button type="button" class="button" onclick="EarthinPB.addBlock('button')">+ <?php esc_html_e('Add Button', 'earthin-page-builder'); ?></button>
                    <button type="button" class="button" onclick="EarthinPB.addBlock('alert')">+ <?php esc_html_e('Add Alert Box', 'earthin-page-builder'); ?></button>
                </div>
            </div>
        </div>

        <script>
        var EarthinPB = {
            blockIndex: <?php echo count($layout); ?>,
            addBlock: function(type) {
                var container = document.getElementById('earthin-pb-blocks-container');
                var idx = this.blockIndex++;
                var html = '<div class="earthin-pb-block-item">' +
                    '<div class="earthin-pb-block-head"><span>' + type.toUpperCase() + '</span><button type="button" class="button button-link-delete" onclick="this.closest(\'.earthin-pb-block-item\').remove()">Remove</button></div>' +
                    '<input type="hidden" name="earthin_pb_blocks[' + idx + '][type]" value="' + type + '" />';
                if (type === 'heading') {
                    html += '<input type="text" class="large-text" name="earthin_pb_blocks[' + idx + '][text]" placeholder="Enter Heading Title..." />';
                } else if (type === 'button') {
                    html += '<input type="text" style="width:48%;" name="earthin_pb_blocks[' + idx + '][label]" placeholder="Button Label" /> ' +
                            '<input type="url" style="width:48%;" name="earthin_pb_blocks[' + idx + '][url]" placeholder="https://example.com" />';
                } else if (type === 'alert') {
                    html += '<textarea class="large-text" rows="2" name="earthin_pb_blocks[' + idx + '][content]" placeholder="Important notice text..."></textarea>';
                } else {
                    html += '<textarea class="large-text" rows="4" name="earthin_pb_blocks[' + idx + '][content]" placeholder="Write paragraph content..."></textarea>';
                }
                html += '</div>';
                container.insertAdjacentHTML('beforeend', html);
            }
        };
        </script>
        <?php
    }

    public function save_builder_data($post_id) {
        if (!isset($_POST['earthin_pb_nonce']) || !wp_verify_nonce($_POST['earthin_pb_nonce'], 'earthin_pb_save_nonce')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $enabled = !empty($_POST['earthin_builder_enabled']) ? '1' : '0';
        update_post_meta($post_id, '_earthin_builder_enabled', $enabled);

        $blocks = [];
        if (isset($_POST['earthin_pb_blocks']) && is_array($_POST['earthin_pb_blocks'])) {
            foreach ($_POST['earthin_pb_blocks'] as $raw) {
                $type = sanitize_key($raw['type'] ?? '');
                if ($type === 'heading') {
                    $blocks[] = [
                        'type' => 'heading',
                        'text' => sanitize_text_field($raw['text'] ?? ''),
                    ];
                } elseif ($type === 'button') {
                    $blocks[] = [
                        'type' => 'button',
                        'label' => sanitize_text_field($raw['label'] ?? ''),
                        'url' => esc_url_raw($raw['url'] ?? ''),
                    ];
                } elseif ($type === 'alert') {
                    $blocks[] = [
                        'type' => 'alert',
                        'content' => sanitize_textarea_field($raw['content'] ?? ''),
                    ];
                } elseif ($type === 'text') {
                    $blocks[] = [
                        'type' => 'text',
                        'content' => wp_kses_post($raw['content'] ?? ''),
                    ];
                }
            }
        }
        update_post_meta($post_id, '_earthin_builder_layout', wp_json_encode($blocks));
    }

    public function render_frontend_layout($content) {
        if (!is_singular()) {
            return $content;
        }

        $post_id = get_the_ID();
        $enabled = get_post_meta($post_id, '_earthin_builder_enabled', true);
        if (!$enabled) {
            return $content;
        }

        $raw_layout = get_post_meta($post_id, '_earthin_builder_layout', true);
        $blocks = !empty($raw_layout) ? json_decode($raw_layout, true) : [];
        if (empty($blocks) || !is_array($blocks)) {
            return $content;
        }

        $out = '<div class="earthin-pb-container">';
        foreach ($blocks as $b) {
            $type = $b['type'] ?? '';
            if ($type === 'heading') {
                $out .= '<h2 class="earthin-pb-heading">' . esc_html($b['text'] ?? '') . '</h2>';
            } elseif ($type === 'button') {
                $out .= '<p><a href="' . esc_url($b['url'] ?? '#') . '" class="earthin-pb-btn earthin-pb-btn-primary">' . esc_html($b['label'] ?? 'Learn More') . '</a></p>';
            } elseif ($type === 'alert') {
                $out .= '<div class="earthin-pb-alert earthin-pb-alert-info">' . esc_html($b['content'] ?? '') . '</div>';
            } elseif ($type === 'text') {
                $out .= '<div class="earthin-pb-text">' . wpautop(wp_kses_post($b['content'] ?? '')) . '</div>';
            }
            $out = apply_filters('earthin_pb_render_custom_block', $out, $b);
        }
        $out .= '</div>';

        return $out;
    }

    public function render_admin_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access.', 'earthin-page-builder'));
        }
        $settings = $this->get_settings();
        $available_post_types = get_post_types(['public' => true], 'objects');
        $pro_active = class_exists('Earthin_Page_Builder_Pro');
        ?>
        <div class="wrap" style="max-width:1100px; margin:20px auto 40px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,sans-serif;">
            <!-- Header Banner -->
            <div style="display:flex; align-items:center; justify-content:space-between; background:linear-gradient(135deg, #059669 0%, #047857 100%); padding:22px 28px; border-radius:12px; color:#fff; box-shadow:0 4px 18px rgba(5,150,105,0.22); margin-bottom:24px;">
                <div style="display:flex; align-items:center; gap:16px;">
                    <div style="background:rgba(255,255,255,0.2); border-radius:12px; width:50px; height:50px; display:flex; align-items:center; justify-content:center; font-size:26px;">🏗️</div>
                    <div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <h1 style="color:#fff; margin:0; font-size:22px; font-weight:700; line-height:1.2;"><?php esc_html_e('Earthin Page Builder', 'earthin-page-builder'); ?></h1>
                            <?php if ($pro_active): ?>
                                <span style="background:#fef08a; color:#854d0e; font-size:11px; font-weight:800; padding:2px 8px; border-radius:10px; text-transform:uppercase;">PRO ACTIVE</span>
                            <?php endif; ?>
                        </div>
                        <p style="margin:4px 0 0; color:#d1fae5; font-size:13px;"><?php esc_html_e('Ultra-lightweight visual layout builder generating clean, semantic, bloat-free HTML', 'earthin-page-builder'); ?></p>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="background:rgba(255,255,255,0.18); padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600;">v<?php echo esc_html(EARTHIN_PAGE_BUILDER_VERSION); ?></span>
                    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=page')); ?>" class="button" style="background:#fff; color:#047857; border:none; padding:6px 14px; border-radius:6px; font-size:12px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:5px; height:auto; line-height:1.4;">
                        <span>+</span> <?php esc_html_e('Build New Page', 'earthin-page-builder'); ?>
                    </a>
                </div>
            </div>

            <!-- Quick Stat Cards -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px; margin-bottom:24px;">
                <div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
                    <div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e('Active Post Types', 'earthin-page-builder'); ?></div>
                    <div style="font-size:24px; font-weight:700; color:#059669; margin-top:4px;">
                        <?php echo count((array)$settings['post_types']); ?> <?php esc_html_e('Types', 'earthin-page-builder'); ?>
                    </div>
                    <div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e('Pages, Posts & Custom types', 'earthin-page-builder'); ?></div>
                </div>

                <div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
                    <div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e('Max Container Width', 'earthin-page-builder'); ?></div>
                    <div style="font-size:20px; font-weight:700; color:#0f172a; margin-top:4px;">
                        <?php echo intval($settings['container_width']); ?>px
                    </div>
                    <div style="font-size:12px; color:#10b981; margin-top:2px; font-weight:500;">✓ Responsive Flexbox Grid</div>
                </div>

                <div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
                    <div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e('CSS Footprint', 'earthin-page-builder'); ?></div>
                    <div style="font-size:20px; font-weight:700; color:#059669; margin-top:4px;">&lt; 2 KB (Zero Bloat)</div>
                    <div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e('No jQuery, no external fonts', 'earthin-page-builder'); ?></div>
                </div>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields('earthin_pb_settings_group'); ?>

                <div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">
                    <!-- Main Settings Column -->
                    <div>
                        <!-- Layout & Design Options -->
                        <div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
                            <h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
                                <span>📐</span> <?php esc_html_e('Global Layout Settings', 'earthin-page-builder'); ?>
                            </h2>

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                                <div>
                                    <label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e('Container Max Width (px)', 'earthin-page-builder'); ?></label>
                                    <input type="number" min="600" max="2400" step="10" name="<?php echo esc_attr($this->option_name); ?>[container_width]" value="<?php echo esc_attr($settings['container_width']); ?>" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;" />
                                    <p style="font-size:12px; color:#64748b; margin-top:4px;"><?php esc_html_e('Max width for centered page builder content.', 'earthin-page-builder'); ?></p>
                                </div>

                                <div>
                                    <label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e('Primary Accent Color', 'earthin-page-builder'); ?></label>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <input type="color" name="<?php echo esc_attr($this->option_name); ?>[accent_color]" value="<?php echo esc_attr($settings['accent_color']); ?>" style="width:42px; height:36px; padding:2px; border:1px solid #cbd5e1; border-radius:6px; cursor:pointer;" />
                                        <input type="text" style="width:110px; padding:8px 10px; border-radius:6px; border:1px solid #cbd5e1; font-family:monospace; font-size:13px;" value="<?php echo esc_attr($settings['accent_color']); ?>" readonly />
                                    </div>
                                    <p style="font-size:12px; color:#64748b; margin-top:4px;"><?php esc_html_e('Button and accent highlight color.', 'earthin-page-builder'); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Enabled Post Types Card -->
                        <div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
                            <h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
                                <span>📑</span> <?php esc_html_e('Enabled Post Types', 'earthin-page-builder'); ?>
                            </h2>
                            <p style="font-size:13px; color:#64748b; margin:0 0 16px;">
                                <?php esc_html_e('Select which post types display the Earthin Visual Layout Builder meta box in their editor:', 'earthin-page-builder'); ?>
                            </p>

                            <div style="display:flex; flex-wrap:wrap; gap:10px;">
                                <?php foreach ($available_post_types as $pt_slug => $pt_obj): ?>
                                    <label style="display:flex; align-items:center; gap:8px; padding:10px 14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; cursor:pointer;">
                                        <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[post_types][]" value="<?php echo esc_attr($pt_slug); ?>" <?php checked(in_array($pt_slug, (array)$settings['post_types'], true)); ?> />
                                        <span style="font-size:13px; font-weight:600; color:#0f172a;"><?php echo esc_html($pt_obj->labels->name); ?></span>
                                        <code style="font-size:11px; color:#64748b; background:#e2e8f0; padding:1px 5px; border-radius:3px;"><?php echo esc_html($pt_slug); ?></code>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button type="submit" class="button button-primary" style="background:#059669; border-color:#047857; color:#fff; font-weight:600; padding:8px 24px; height:auto; border-radius:6px; font-size:14px; box-shadow:0 2px 4px rgba(5,150,105,0.25);">
                            <?php esc_html_e('Save Builder Settings', 'earthin-page-builder'); ?>
                        </button>
                    </div>

                    <!-- Sidebar Info -->
                    <div>
                        <div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:20px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:20px;">
                            <h3 style="font-size:14px; font-weight:700; color:#0f172a; margin:0 0 10px;"><?php esc_html_e('Available Blocks', 'earthin-page-builder'); ?></h3>
                            <ul style="font-size:12px; color:#64748b; line-height:1.6; margin:0; padding-left:16px;">
                                <li><strong>Heading Block</strong> &mdash; H2 section headers</li>
                                <li><strong>Rich Text Block</strong> &mdash; Paragraphs with HTML formatting</li>
                                <li><strong>Call-to-Action Button</strong> &mdash; Clickable primary/secondary buttons</li>
                                <li><strong>Alert / Callout Box</strong> &mdash; Highlighted informational banners</li>
                                <?php if ($pro_active): ?>
                                    <li style="color:#059669; font-weight:600;">Pricing Tables & Cards (Pro)</li>
                                    <li style="color:#059669; font-weight:600;">Client Testimonials & Quotes (Pro)</li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:18px;">
                            <div style="font-size:13px; font-weight:700; color:#166534; margin-bottom:8px;">💡 <?php esc_html_e('Zero Bloat Guarantee', 'earthin-page-builder'); ?></div>
                            <p style="font-size:12px; color:#15803d; line-height:1.5; margin:0;">
                                <?php esc_html_e('Unlike heavy commercial page builders that inject dozens of nested div wrappers and 500KB+ of external JS, Earthin renders pure semantic HTML for 100/100 Google PageSpeed scores.', 'earthin-page-builder'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Page_Builder', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-page-builder')) . '">' . __('Settings', 'earthin-page-builder') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
