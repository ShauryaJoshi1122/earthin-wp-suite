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

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('add_meta_boxes', [$this, 'add_builder_metabox']);
        add_action('save_post', [$this, 'save_builder_data']);
        add_filter('the_content', [$this, 'render_frontend_layout']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_styles']);
    }

    public function enqueue_frontend_styles() {
        if (is_singular()) {
            $is_builder = get_post_meta(get_the_ID(), '_earthin_builder_enabled', true);
            if ($is_builder) {
                wp_register_style('earthin-pb-styles', false);
                wp_enqueue_style('earthin-pb-styles');
                $css = "
                    .earthin-pb-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; font-family: inherit; }
                    .earthin-pb-section { padding: 40px 0; }
                    .earthin-pb-row { display: flex; flex-wrap: wrap; gap: 24px; margin-bottom: 24px; }
                    .earthin-pb-col { flex: 1 1 0; min-width: 280px; }
                    .earthin-pb-heading { font-weight: 700; margin-bottom: 12px; line-height: 1.25; }
                    .earthin-pb-btn { display: inline-block; padding: 12px 24px; border-radius: 6px; font-weight: 600; text-decoration: none; transition: opacity 0.2s; }
                    .earthin-pb-btn-primary { background: #10b981; color: #ffffff !important; }
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
        $post_types = ['page', 'post'];
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
}

add_action('plugins_loaded', ['Earthin_Page_Builder', 'get_instance']);
