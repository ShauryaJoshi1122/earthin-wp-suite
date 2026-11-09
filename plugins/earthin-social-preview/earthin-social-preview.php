<?php
/**
 * Plugin Name: Earthin Social Preview
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-social-preview
 * Description: Automatic Open Graph and Twitter Card metadata generation with interactive live preview in the WordPress post editor.
 * Version: 1.0.0
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: earthin-social-preview
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('EARTHIN_SOCIAL_PREVIEW_VERSION', '1.0.0');
define('EARTHIN_SOCIAL_PREVIEW_PATH', plugin_dir_path(__FILE__));

class Earthin_Social_Preview {
    private static $instance = null;
    private $option_name = 'earthin_social_preview_settings';

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('add_meta_boxes', [$this, 'add_social_metabox']);
        add_action('save_post', [$this, 'save_post_metadata']);
        add_action('wp_head', [$this, 'output_social_meta_tags'], 5);
    }

    public function default_settings() {
        return [
            'default_og_image' => '',
            'twitter_site_handle' => '',
            'enable_og' => 1,
            'enable_twitter' => 1,
        ];
    }

    public function get_settings() {
        $saved = get_option($this->option_name, []);
        return wp_parse_args($saved, $this->default_settings());
    }

    public function add_admin_menu() {
        add_options_page(
            __('Earthin Social Preview', 'earthin-social-preview'),
            __('Social Preview', 'earthin-social-preview'),
            'manage_options',
            'earthin-social-preview',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting('earthin_social_preview_group', $this->option_name, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings'],
            'default' => $this->default_settings(),
        ]);
    }

    public function sanitize_settings($input) {
        $sanitized = $this->default_settings();
        $sanitized['default_og_image'] = esc_url_raw($input['default_og_image'] ?? '');
        $sanitized['twitter_site_handle'] = sanitize_text_field(ltrim($input['twitter_site_handle'] ?? '', '@'));
        $sanitized['enable_og'] = !empty($input['enable_og']) ? 1 : 0;
        $sanitized['enable_twitter'] = !empty($input['enable_twitter']) ? 1 : 0;
        return $sanitized;
    }

    public function add_social_metabox() {
        $post_types = get_post_types(['public' => true]);
        foreach ($post_types as $pt) {
            add_meta_box(
                'earthin_social_metabox',
                __('Earthin Social Card Preview', 'earthin-social-preview'),
                [$this, 'render_metabox'],
                $pt,
                'normal',
                'high'
            );
        }
    }

    public function render_metabox($post) {
        wp_nonce_field('earthin_social_meta_nonce', 'earthin_social_nonce');
        $title = get_post_meta($post->ID, '_earthin_social_title', true);
        $desc = get_post_meta($post->ID, '_earthin_social_desc', true);
        $image = get_post_meta($post->ID, '_earthin_social_image', true);

        $display_title = $title ?: get_the_title($post->ID) ?: 'Post Title';
        $display_desc = $desc ?: wp_trim_words(strip_shortcodes($post->post_content), 24) ?: 'Preview summary description for social shares...';
        ?>
        <style>
            .earthin-preview-card {
                max-width: 500px;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                overflow: hidden;
                background: #ffffff;
                box-shadow: 0 2px 8px rgba(0,0,0,0.06);
                margin: 16px 0;
            }
            .earthin-preview-img {
                height: 220px;
                background: #f1f5f9;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #94a3b8;
                background-size: cover;
                background-position: center;
            }
            .earthin-preview-body {
                padding: 14px;
            }
            .earthin-preview-domain {
                font-size: 11px;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .earthin-preview-title {
                font-size: 15px;
                font-weight: 700;
                color: #0f172a;
                margin: 4px 0 6px 0;
            }
            .earthin-preview-desc {
                font-size: 13px;
                color: #475569;
                line-height: 1.4;
            }
        </style>

        <div class="earthin-social-fields">
            <p>
                <label><strong><?php esc_html_e('Custom Social Title (Overrides default post title):', 'earthin-social-preview'); ?></strong></label><br>
                <input type="text" name="_earthin_social_title" id="earthin_s_title" class="large-text" value="<?php echo esc_attr($title); ?>" placeholder="<?php echo esc_attr(get_the_title($post->ID)); ?>" />
            </p>
            <p>
                <label><strong><?php esc_html_e('Custom Social Description:', 'earthin-social-preview'); ?></strong></label><br>
                <textarea name="_earthin_social_desc" id="earthin_s_desc" class="large-text" rows="2" placeholder="<?php esc_attr_e('Leave blank to use post excerpt', 'earthin-social-preview'); ?>"><?php echo esc_textarea($desc); ?></textarea>
            </p>
            <p>
                <label><strong><?php esc_html_e('Custom Social Image URL:', 'earthin-social-preview'); ?></strong></label><br>
                <input type="url" name="_earthin_social_image" id="earthin_s_img" class="large-text" value="<?php echo esc_attr($image); ?>" placeholder="https://example.com/image.jpg" />
            </p>

            <h4><?php esc_html_e('Live Facebook & Twitter Preview Card', 'earthin-social-preview'); ?></h4>
            <div class="earthin-preview-card">
                <div class="earthin-preview-img" id="card_preview_img" style="<?php if ($image) echo 'background-image:url(' . esc_url($image) . ');'; ?>">
                    <?php if (!$image): ?><span><?php esc_html_e('1200 x 630 Recommended Image', 'earthin-social-preview'); ?></span><?php endif; ?>
                </div>
                <div class="earthin-preview-body">
                    <div class="earthin-preview-domain"><?php echo esc_html(wp_parse_url(home_url(), PHP_URL_HOST)); ?></div>
                    <div class="earthin-preview-title" id="card_preview_title"><?php echo esc_html($display_title); ?></div>
                    <div class="earthin-preview-desc" id="card_preview_desc"><?php echo esc_html($display_desc); ?></div>
                </div>
            </div>
        </div>

        <script>
        (function() {
            var titleInput = document.getElementById('earthin_s_title');
            var descInput = document.getElementById('earthin_s_desc');
            var imgInput = document.getElementById('earthin_s_img');
            var cardTitle = document.getElementById('card_preview_title');
            var cardDesc = document.getElementById('card_preview_desc');
            var cardImg = document.getElementById('card_preview_img');

            if (titleInput && cardTitle) {
                titleInput.addEventListener('input', function() {
                    cardTitle.textContent = titleInput.value.trim() || '<?php echo esc_js(get_the_title($post->ID)); ?>';
                });
            }
            if (descInput && cardDesc) {
                descInput.addEventListener('input', function() {
                    cardDesc.textContent = descInput.value.trim() || 'Preview summary description for social shares...';
                });
            }
            if (imgInput && cardImg) {
                imgInput.addEventListener('input', function() {
                    if (imgInput.value.trim()) {
                        cardImg.style.backgroundImage = 'url(' + imgInput.value.trim() + ')';
                        cardImg.innerHTML = '';
                    } else {
                        cardImg.style.backgroundImage = '';
                        cardImg.innerHTML = '<span>1200 x 630 Recommended Image</span>';
                    }
                });
            }
        })();
        </script>
        <?php
    }

    public function save_post_metadata($post_id) {
        if (!isset($_POST['earthin_social_nonce']) || !wp_verify_nonce($_POST['earthin_social_nonce'], 'earthin_social_meta_nonce')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['_earthin_social_title'])) {
            update_post_meta($post_id, '_earthin_social_title', sanitize_text_field($_POST['_earthin_social_title']));
        }
        if (isset($_POST['_earthin_social_desc'])) {
            update_post_meta($post_id, '_earthin_social_desc', sanitize_textarea_field($_POST['_earthin_social_desc']));
        }
        if (isset($_POST['_earthin_social_image'])) {
            update_post_meta($post_id, '_earthin_social_image', esc_url_raw($_POST['_earthin_social_image']));
        }
    }

    public function output_social_meta_tags() {
        if (is_admin()) {
            return;
        }

        $settings = $this->get_settings();
        $title = get_bloginfo('name');
        $desc = get_bloginfo('description');
        $image = $settings['default_og_image'];
        $url = home_url('/');
        $type = 'website';

        if (is_singular()) {
            $post_id = get_the_ID();
            $custom_title = get_post_meta($post_id, '_earthin_social_title', true);
            $custom_desc = get_post_meta($post_id, '_earthin_social_desc', true);
            $custom_image = get_post_meta($post_id, '_earthin_social_image', true);

            $title = $custom_title ?: get_the_title($post_id);
            $desc = $custom_desc ?: wp_trim_words(strip_shortcodes(get_post_field('post_content', $post_id)), 30);
            $url = get_permalink($post_id);
            $type = 'article';

            if ($custom_image) {
                $image = $custom_image;
            } elseif (has_post_thumbnail($post_id)) {
                $image = get_the_post_thumbnail_url($post_id, 'large');
            }
        }

        echo "\n<!-- Earthin Social Preview Tags -->\n";
        if (!empty($settings['enable_og'])) {
            echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . "\" />\n";
            echo '<meta property="og:type" content="' . esc_attr($type) . "\" />\n";
            echo '<meta property="og:title" content="' . esc_attr($title) . "\" />\n";
            echo '<meta property="og:description" content="' . esc_attr($desc) . "\" />\n";
            echo '<meta property="og:url" content="' . esc_url($url) . "\" />\n";
            if (!empty($image)) {
                echo '<meta property="og:image" content="' . esc_url($image) . "\" />\n";
            }
        }

        if (!empty($settings['enable_twitter'])) {
            echo '<meta name="twitter:card" content="summary_large_image" />\n';
            echo '<meta name="twitter:title" content="' . esc_attr($title) . "\" />\n";
            echo '<meta name="twitter:description" content="' . esc_attr($desc) . "\" />\n";
            if (!empty($image)) {
                echo '<meta name="twitter:image" content="' . esc_url($image) . "\" />\n";
            }
            if (!empty($settings['twitter_site_handle'])) {
                echo '<meta name="twitter:site" content="@' . esc_attr($settings['twitter_site_handle']) . "\" />\n";
            }
        }
        echo "<!-- /Earthin Social Preview Tags -->\n\n";
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'earthin-social-preview'));
        }
        $settings = $this->get_settings();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Earthin Social Preview Settings', 'earthin-social-preview'); ?></h1>
            <p><?php esc_html_e('Configure Open Graph and Twitter Card defaults for social sharing.', 'earthin-social-preview'); ?></p>

            <form method="post" action="options.php">
                <?php settings_fields('earthin_social_preview_group'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('Enable Open Graph', 'earthin-social-preview'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[enable_og]" value="1" <?php checked($settings['enable_og'], 1); ?> />
                                <?php esc_html_e('Generate Facebook, LinkedIn, WhatsApp Open Graph tags', 'earthin-social-preview'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Enable Twitter Cards', 'earthin-social-preview'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[enable_twitter]" value="1" <?php checked($settings['enable_twitter'], 1); ?> />
                                <?php esc_html_e('Generate Twitter / X Cards meta tags', 'earthin-social-preview'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Default Sharing Image URL', 'earthin-social-preview'); ?></th>
                        <td>
                            <input type="url" class="regular-text" name="<?php echo esc_attr($this->option_name); ?>[default_og_image]" value="<?php echo esc_attr($settings['default_og_image']); ?>" placeholder="https://example.com/default-share-image.jpg" />
                            <p class="description"><?php esc_html_e('Used as fallback when a post does not have a featured image or custom social image. Recommended size: 1200x630.', 'earthin-social-preview'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Twitter / X Handle', 'earthin-social-preview'); ?></th>
                        <td>
                            <input type="text" class="regular-text" name="<?php echo esc_attr($this->option_name); ?>[twitter_site_handle]" value="<?php echo esc_attr($settings['twitter_site_handle']); ?>" placeholder="earthin_online" />
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Social_Preview', 'get_instance']);
