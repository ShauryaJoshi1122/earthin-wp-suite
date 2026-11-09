<?php
/**
 * Plugin Name: Earthin SVG Safe Sanitizer
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-svg-support
 * Description: Enables SVG uploads in the Media Library with an automated XML sanitizer blocking scripts, XXE attacks, and inline malicious handlers.
 * Version: 1.0.0
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * Text Domain: earthin-svg-support
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class Earthin_SVG_Support {
    private static ?Earthin_SVG_Support $instance = null;

    public static function get_instance(): Earthin_SVG_Support {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter('upload_mimes', [$this, 'allow_svg_mime_type']);
        add_filter('wp_handle_upload_prefilter', [$this, 'sanitize_svg_upload']);
        add_filter('wp_check_filetype_and_ext', [$this, 'fix_svg_filetype_check'], 10, 4);
        add_action('admin_head', [$this, 'inject_media_grid_svg_style']);
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_init', [$this, 'handle_admin_actions']);
    }

    public function register_admin_menu(): void {
        add_options_page(
            __('Earthin SVG Support', 'earthin-svg-support'),
            __('Earthin SVG Support', 'earthin-svg-support'),
            'manage_options',
            'earthin-svg-support',
            [$this, 'render_admin_page']
        );
    }

    public function handle_admin_actions(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['earthin_save_svg_settings']) && check_admin_referer('earthin_svg_settings_nonce')) {
            $admin_only = isset($_POST['admin_only']) ? 1 : 0;
            update_option('earthin_svg_admin_only', $admin_only);
            add_settings_error('earthin_svg', 'saved', __('SVG upload security settings saved.', 'earthin-svg-support'), 'updated');
        }
    }

    public function allow_svg_mime_type(array $mimes): array {
        $admin_only = (bool) get_option('earthin_svg_admin_only', 0);
        $can_upload = $admin_only ? current_user_can('manage_options') : current_user_can('upload_files');

        if ($can_upload) {
            $mimes['svg'] = 'image/svg+xml';
            $mimes['svgz'] = 'image/svg+xml';
        }
        return $mimes;
    }

    public function fix_svg_filetype_check($data, $file, $filename, $mimes) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ('svg' === $ext) {
            $data['ext'] = 'svg';
            $data['type'] = 'image/svg+xml';
            $data['proper_filename'] = $filename;
        }
        return $data;
    }

    public function sanitize_svg_upload(array $file): array {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ('svg' !== $ext) {
            return $file;
        }

        $filepath = $file['tmp_name'];
        if (!is_file($filepath)) {
            return $file;
        }

        $content = file_get_contents($filepath);
        if (false === $content) {
            $file['error'] = __('Unable to read SVG file.', 'earthin-svg-support');
            return $file;
        }

        // XXE and Entity Expansion Guard
        if (preg_match('/<!ENTITY/i', $content) || preg_match('/<!DOCTYPE[^>]*\[/i', $content)) {
            $file['error'] = __('Security block: SVG contains forbidden XML entity or DTD definition.', 'earthin-svg-support');
            return $file;
        }

        // Sanitize dangerous elements and attributes
        $sanitized = $this->sanitize_svg_content($content);
        if (!$sanitized) {
            $file['error'] = __('Security block: Malicious script tags or event handlers detected in SVG.', 'earthin-svg-support');
            return $file;
        }

        file_put_contents($filepath, $sanitized);
        return $file;
    }

    private function sanitize_svg_content(string $svg): ?string {
        // Disallow dangerous tags
        $forbidden_tags = ['script', 'foreignobject', 'iframe', 'embed', 'object', 'applet', 'meta', 'link'];
        foreach ($forbidden_tags as $tag) {
            if (preg_match('/<\s*' . $tag . '\b[^>]*>/i', $svg)) {
                return null;
            }
        }

        // Strip inline event handlers (e.g. onload, onerror, onclick)
        $svg = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $svg);

        // Strip javascript: protocols in href / xlink:href
        $svg = preg_replace('/(href|xlink:href)\s*=\s*["\']\s*javascript:[^"\']*["\']/i', '', $svg);

        // Strip data: URIs containing base64 scripts
        $svg = preg_replace('/data:\s*text\/html/i', '', $svg);

        return $svg;
    }

    public function inject_media_grid_svg_style(): void {
        echo '<style>
            .attachment .thumbnail img[src$=".svg"],
            .media-icon img[src$=".svg"] {
                width: 100% !important;
                height: auto !important;
            }
        </style>';
    }

    public function render_admin_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $admin_only = (bool) get_option('earthin_svg_admin_only', 0);
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
                .earthin-btn-primary { background: #059669 !important; border-color: #059669 !important; color: #ffffff !important; font-weight: 700 !important; padding: 8px 22px !important; border-radius: 8px !important; box-shadow: 0 2px 4px rgba(5,150,105,0.2) !important; text-shadow: none !important; cursor: pointer; }
                .earthin-btn-primary:hover { background: #047857 !important; }
                .earthin-btn-secondary { background: #f8fafc !important; border: 1px solid #cbd5e1 !important; color: #334155 !important; font-weight: 700 !important; padding: 8px 18px !important; border-radius: 8px !important; text-shadow: none !important; cursor: pointer; text-decoration: none; display: inline-block; }
                .earthin-badge-pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
                .earthin-badge-success { background: #ecfdf5; color: #059669; }
                .earthin-badge-neutral { background: #f1f5f9; color: #64748b; }
            </style>

            <header class="earthin-header">
                <div class="earthin-header-left">
                    <span class="earthin-logo-badge">🌱 Earthin</span>
                    <div class="earthin-title-group">
                        <h1>
                            <?php echo esc_html__('Earthin SVG Support & Sanitizer', 'earthin-svg-support'); ?>
                            <span class="earthin-version-tag">v1.0.0</span>
                            <span class="earthin-badge-pill earthin-badge-success"><?php echo esc_html__('XML Sanitization Active', 'earthin-svg-support'); ?></span>
                        </h1>
                        <p><?php echo esc_html__('Hardened SVG vector upload engine with XSS/XXE protection by Shaurya Joshi.', 'earthin-svg-support'); ?></p>
                    </div>
                </div>
                <div class="earthin-header-links">
                    <a href="https://plugins.earthin.online" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Plugin Hub', 'earthin-svg-support'); ?> &nearr;</a>
                    <a href="https://plugins.earthin.online/docs" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Documentation', 'earthin-svg-support'); ?></a>
                    <a href="https://plugins.earthin.online/support" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Support', 'earthin-svg-support'); ?></a>
                </div>
            </header>

            <?php settings_errors('earthin_svg'); ?>

            <div class="earthin-grid">
                <div class="earthin-card">
                    <div class="earthin-card-header">
                        <h2 class="earthin-card-title"><?php esc_html_e('SVG Security Controls', 'earthin-svg-support'); ?></h2>
                    </div>

                    <form method="post">
                        <?php wp_nonce_field('earthin_svg_settings_nonce'); ?>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><?php esc_html_e('Restrict Upload Permissions', 'earthin-svg-support'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="admin_only" value="1" <?php checked(1, $admin_only); ?> />
                                        <strong><?php esc_html_e('Allow only Administrators to upload SVG files', 'earthin-svg-support'); ?></strong>
                                    </label>
                                    <p class="description"><?php esc_html_e('When unchecked, Editors and Authors with upload capability can also upload sanitized SVGs.', 'earthin-svg-support'); ?></p>
                                </td>
                            </tr>
                        </table>

                        <p class="submit" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #f1f5f9;">
                            <input type="submit" name="earthin_save_svg_settings" class="button earthin-btn-primary" value="<?php esc_attr_e('Save Configuration', 'earthin-svg-support'); ?>" />
                        </p>
                    </form>
                </div>

                <div>
                    <div class="earthin-card">
                        <div class="earthin-card-header">
                            <h2 class="earthin-card-title"><?php esc_html_e('Active Protection Layers', 'earthin-svg-support'); ?></h2>
                        </div>
                        <ul style="padding-left: 0; list-style: none; margin: 0; font-size: 13px; color: #334155;">
                            <li style="padding: 6px 0; border-bottom: 1px solid #f1f5f9;">
                                ✔ <strong><?php esc_html_e('XXE XML Entity Guard:', 'earthin-svg-support'); ?></strong> Blocks <!ENTITY attacks
                            </li>
                            <li style="padding: 6px 0; border-bottom: 1px solid #f1f5f9;">
                                ✔ <strong><?php esc_html_e('XSS Script Purge:', 'earthin-svg-support'); ?></strong> Strips &lt;script&gt;, &lt;iframe&gt;, &lt;foreignObject&gt;
                            </li>
                            <li style="padding: 6px 0; border-bottom: 1px solid #f1f5f9;">
                                ✔ <strong><?php esc_html_e('Event Handler Sanitizer:', 'earthin-svg-support'); ?></strong> Neutralizes onload, onerror, onclick
                            </li>
                            <li style="padding: 6px 0;">
                                ✔ <strong><?php esc_html_e('Media Library Grid Fix:', 'earthin-svg-support'); ?></strong> Native thumbnail rendering
                            </li>
                        </ul>

                        <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #f1f5f9;">
                            <a href="<?php echo esc_url(admin_url('upload.php')); ?>" class="button earthin-btn-secondary">
                                <?php esc_html_e('Go to Media Library', 'earthin-svg-support'); ?> &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_SVG_Support', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-svg-support')) . '">' . __('Settings', 'earthin-svg-support') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});

