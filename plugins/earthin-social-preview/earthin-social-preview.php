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
        $has_fallback = !empty($settings['default_og_image']);
        ?>
        <div class="wrap" style="max-width:1100px; margin:20px auto 40px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,sans-serif;">
            <!-- Header Banner -->
            <div style="display:flex; align-items:center; justify-content:space-between; background:linear-gradient(135deg, #059669 0%, #047857 100%); padding:22px 28px; border-radius:12px; color:#fff; box-shadow:0 4px 18px rgba(5,150,105,0.22); margin-bottom:24px;">
                <div style="display:flex; align-items:center; gap:16px;">
                    <div style="background:rgba(255,255,255,0.2); border-radius:12px; width:50px; height:50px; display:flex; align-items:center; justify-content:center; font-size:26px;">🖼️</div>
                    <div>
                        <h1 style="color:#fff; margin:0; font-size:22px; font-weight:700; line-height:1.2;"><?php esc_html_e('Earthin Social Preview', 'earthin-social-preview'); ?></h1>
                        <p style="margin:4px 0 0; color:#d1fae5; font-size:13px;"><?php esc_html_e('Automatic Open Graph and Twitter / X Card metadata generator with live post previews', 'earthin-social-preview'); ?></p>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="background:rgba(255,255,255,0.18); padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600;">v<?php echo esc_html(EARTHIN_SOCIAL_PREVIEW_VERSION); ?></span>
                    <a href="https://plugins.earthin.online/docs" target="_blank" style="background:#fff; color:#047857; text-decoration:none; padding:6px 14px; border-radius:6px; font-size:12px; font-weight:600;">Documentation</a>
                </div>
            </div>

            <!-- Quick Stat Cards -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px; margin-bottom:24px;">
                <div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
                    <div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e('Open Graph Engine', 'earthin-social-preview'); ?></div>
                    <div style="font-size:20px; font-weight:700; margin-top:4px; color:<?php echo !empty($settings['enable_og']) ? '#059669' : '#94a3b8'; ?>;">
                        <?php echo !empty($settings['enable_og']) ? '✓ Active (Facebook/LinkedIn)' : '○ Disabled'; ?>
                    </div>
                    <div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e('Standard og:title & og:image tags', 'earthin-social-preview'); ?></div>
                </div>

                <div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
                    <div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e('Twitter / X Cards', 'earthin-social-preview'); ?></div>
                    <div style="font-size:20px; font-weight:700; margin-top:4px; color:<?php echo !empty($settings['enable_twitter']) ? '#059669' : '#94a3b8'; ?>;">
                        <?php echo !empty($settings['enable_twitter']) ? '✓ Large Image Cards' : '○ Disabled'; ?>
                    </div>
                    <div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e('Optimized summary_large_image', 'earthin-social-preview'); ?></div>
                </div>

                <div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
                    <div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e('Default Fallback Image', 'earthin-social-preview'); ?></div>
                    <div style="font-size:18px; font-weight:700; color:#0f172a; margin-top:6px;">
                        <?php echo $has_fallback ? '<span style="color:#10b981;">✓ Configured</span>' : '<span style="color:#f59e0b;">Not Set</span>'; ?>
                    </div>
                    <div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e('Applied when no featured image exists', 'earthin-social-preview'); ?></div>
                </div>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields('earthin_social_preview_group'); ?>

                <div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">
                    <!-- Main Form Fields -->
                    <div>
                        <!-- Protocols Card -->
                        <div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
                            <h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
                                <span>🌐</span> <?php esc_html_e('Social Protocols', 'earthin-social-preview'); ?>
                            </h2>

                            <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:16px;">
                                <label style="display:flex; align-items:flex-start; gap:12px; padding:14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer;">
                                    <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[enable_og]" value="1" <?php checked($settings['enable_og'], 1); ?> style="margin-top:3px;" />
                                    <div>
                                        <strong style="font-size:13px; color:#0f172a; display:block;"><?php esc_html_e('Enable Open Graph (Facebook, WhatsApp, LinkedIn, Pinterest)', 'earthin-social-preview'); ?></strong>
                                        <span style="font-size:12px; color:#64748b;">Injects og:title, og:description, og:image, og:url, and og:site_name into the page head.</span>
                                    </div>
                                </label>

                                <label style="display:flex; align-items:flex-start; gap:12px; padding:14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer;">
                                    <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[enable_twitter]" value="1" <?php checked($settings['enable_twitter'], 1); ?> style="margin-top:3px;" />
                                    <div>
                                        <strong style="font-size:13px; color:#0f172a; display:block;"><?php esc_html_e('Enable Twitter / X Cards', 'earthin-social-preview'); ?></strong>
                                        <span style="font-size:12px; color:#64748b;">Injects twitter:card (summary_large_image), twitter:title, twitter:description, and twitter:site.</span>
                                    </div>
                                </label>
                            </div>

                            <div>
                                <label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e('Twitter / X Site Handle (without @)', 'earthin-social-preview'); ?></label>
                                <div style="display:flex; align-items:center;">
                                    <span style="background:#f1f5f9; border:1px solid #cbd5e1; border-right:none; padding:8px 12px; border-radius:6px 0 0 6px; font-size:13px; color:#64748b;">@</span>
                                    <input type="text" style="width:100%; max-width:320px; padding:8px 12px; border-radius:0 6px 6px 0; border:1px solid #cbd5e1; font-size:13px;" name="<?php echo esc_attr($this->option_name); ?>[twitter_site_handle]" value="<?php echo esc_attr($settings['twitter_site_handle']); ?>" placeholder="earthin_online" />
                                </div>
                            </div>
                        </div>

                        <!-- Fallback Sharing Image Card -->
                        <div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
                            <h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
                                <span>🖼️</span> <?php esc_html_e('Fallback Sharing Image', 'earthin-social-preview'); ?>
                            </h2>

                            <div style="margin-bottom:12px;">
                                <label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e('Default Image URL', 'earthin-social-preview'); ?></label>
                                <input type="url" class="large-text" style="padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;" name="<?php echo esc_attr($this->option_name); ?>[default_og_image]" value="<?php echo esc_attr($settings['default_og_image']); ?>" placeholder="https://example.com/wp-content/uploads/default-share.jpg" />
                                <p style="font-size:12px; color:#64748b; margin-top:6px;">
                                    <?php esc_html_e('Used whenever a shared post or page has neither a custom social image nor a featured post thumbnail. Recommended resolution: 1200 x 630 pixels.', 'earthin-social-preview'); ?>
                                </p>
                            </div>

                            <?php if ($has_fallback): ?>
                                <div style="margin-top:14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px;">
                                    <div style="font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; margin-bottom:8px;"><?php esc_html_e('Current Fallback Preview:', 'earthin-social-preview'); ?></div>
                                    <img src="<?php echo esc_url($settings['default_og_image']); ?>" alt="Fallback Preview" style="max-height:160px; max-width:100%; border-radius:6px; display:block; object-fit:cover;" />
                                </div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="button button-primary" style="background:#059669; border-color:#047857; color:#fff; font-weight:600; padding:8px 24px; height:auto; border-radius:6px; font-size:14px; box-shadow:0 2px 4px rgba(5,150,105,0.25);">
                            <?php esc_html_e('Save Social Preview Settings', 'earthin-social-preview'); ?>
                        </button>
                    </div>

                    <!-- Sidebar Info & Post Editor Tip -->
                    <div>
                        <div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:20px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:20px;">
                            <h3 style="font-size:14px; font-weight:700; color:#0f172a; margin:0 0 10px;"><?php esc_html_e('Per-Post Meta Box', 'earthin-social-preview'); ?></h3>
                            <p style="font-size:12px; color:#64748b; line-height:1.5; margin:0 0 10px;">
                                <?php esc_html_e('An interactive Social Card Preview box is automatically injected into the post editor for all posts, pages, and custom post types.', 'earthin-social-preview'); ?>
                            </p>
                            <p style="font-size:12px; color:#64748b; line-height:1.5; margin:0;">
                                <?php esc_html_e('You can customize the card title, teaser text, and image individually for each post.', 'earthin-social-preview'); ?>
                            </p>
                        </div>

                        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:18px;">
                            <div style="font-size:13px; font-weight:700; color:#166534; margin-bottom:8px;">💡 <?php esc_html_e('Testing Your Tags', 'earthin-social-preview'); ?></div>
                            <p style="font-size:12px; color:#15803d; line-height:1.5; margin:0;">
                                <?php esc_html_e('Use the official Facebook Sharing Debugger and Twitter Card Validator to preview how social platforms cache your URLs.', 'earthin-social-preview'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Social_Preview', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-social-preview')) . '">' . __('Settings', 'earthin-social-preview') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
