<?php
/**
 * Plugin Name: Earthin File Manager
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-file-manager
 * Description: Secure in-dashboard file explorer and editor with strict path sanitization, traversal protection, and permissions inspector.
 * Version: 1.0.0
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * Text Domain: earthin-file-manager
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class Earthin_File_Manager {
    private static ?Earthin_File_Manager $instance = null;
    private string $base_path;

    public static function get_instance(): Earthin_File_Manager {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->base_path = wp_normalize_path(ABSPATH);
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_init', [$this, 'handle_file_actions']);
    }

    public function register_admin_menu(): void {
        add_management_page(
            __('Earthin Files', 'earthin-file-manager'),
            __('Earthin Files', 'earthin-file-manager'),
            'manage_options',
            'earthin-file-manager',
            [$this, 'render_admin_page']
        );
    }

    private function sanitize_target_path(string $relative_or_abs): ?string {
        $normalized = wp_normalize_path($relative_or_abs);
        if (strpos($normalized, $this->base_path) !== 0) {
            $normalized = wp_normalize_path($this->base_path . '/' . ltrim($normalized, '/'));
        }
        
        $real = realpath($normalized);
        if (!$real) {
            return null;
        }

        $real_normalized = wp_normalize_path($real);
        if (strpos($real_normalized, $this->base_path) !== 0) {
            return null; // Path traversal blocked!
        }

        return $real_normalized;
    }

    public function handle_file_actions(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['earthin_save_file']) && check_admin_referer('earthin_save_file_nonce')) {
            $target = $this->sanitize_target_path($_POST['file_path'] ?? '');
            if (!$target || !is_file($target)) {
                add_settings_error('earthin_files', 'invalid_path', __('Invalid target path or unauthorized traversal detected.', 'earthin-file-manager'), 'error');
                return;
            }

            // Check if file extension is allowed to be edited
            $allowed_exts = ['php', 'css', 'js', 'txt', 'json', 'html', 'htaccess', 'svg', 'xml', 'md'];
            $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_exts, true) && basename($target) !== '.htaccess') {
                add_settings_error('earthin_files', 'disallowed_ext', __('Editing this file extension is restricted for security.', 'earthin-file-manager'), 'error');
                return;
            }

            $content = wp_unslash($_POST['file_content'] ?? '');
            if (is_writable($target)) {
                file_put_contents($target, $content);
                add_settings_error('earthin_files', 'file_saved', sprintf(__('File %s successfully updated.', 'earthin-file-manager'), basename($target)), 'updated');
            } else {
                add_settings_error('earthin_files', 'not_writable', __('File is not writable by the web server process.', 'earthin-file-manager'), 'error');
            }
        }
    }

    public function render_admin_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $req_dir = isset($_GET['dir']) ? sanitize_text_field(wp_unslash($_GET['dir'])) : $this->base_path;
        $current_dir = $this->sanitize_target_path($req_dir);
        if (!$current_dir || !is_dir($current_dir)) {
            $current_dir = $this->base_path;
        }

        $edit_file = isset($_GET['edit']) ? $this->sanitize_target_path(sanitize_text_field(wp_unslash($_GET['edit']))) : null;
        $edit_content = ($edit_file && is_file($edit_file)) ? file_get_contents($edit_file) : '';

        // Scan directory
        $raw_items = @scandir($current_dir) ?: [];
        $items = [];
        foreach ($raw_items as $item) {
            if ('.' === $item) continue;
            if ('..' === $item && $current_dir === $this->base_path) continue;

            $full_path = wp_normalize_path($current_dir . '/' . $item);
            $is_dir = is_dir($full_path);
            $perms = substr(sprintf('%o', fileperms($full_path)), -4);
            $size = $is_dir ? '-' : size_format(filesize($full_path), 2);
            $modified = gmdate('Y-m-d H:i', filemtime($full_path));

            $items[] = [
                'name' => $item,
                'path' => $full_path,
                'is_dir' => $is_dir,
                'perms' => $perms,
                'size' => $size,
                'modified' => $modified,
            ];
        }
        ?>
        <div class="earthin-admin-wrap">
            <style>
                .earthin-admin-wrap { max-width: 1240px; margin: 20px 20px 0 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
                .earthin-header { background: #0f172a; color: #ffffff; padding: 22px 28px; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08); flex-wrap: wrap; gap: 16px; }
                .earthin-header-left { display: flex; align-items: center; gap: 14px; }
                .earthin-logo-badge { background: #059669; color: #ffffff; font-weight: 800; font-size: 14px; padding: 6px 12px; border-radius: 8px; letter-spacing: 0.04em; display: inline-flex; align-items: center; gap: 6px; }
                .earthin-title-group h1 { color: #ffffff; margin: 0; font-size: 20px; font-weight: 700; line-height: 1.2; display: flex; align-items: center; gap: 10px; }
                .earthin-version-tag { font-size: 11px; background: rgba(255,255,255,0.15); color: #cbd5e1; padding: 2px 8px; border-radius: 999px; font-weight: 600; }
                .earthin-title-group p { margin: 4px 0 0; color: #94a3b8; font-size: 13px; }
                .earthin-header-links { display: flex; gap: 10px; align-items: center; }
                .earthin-link-btn { background: rgba(255,255,255,0.08); color: #e2e8f0 !important; text-decoration: none !important; font-size: 12px; font-weight: 600; padding: 7px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.12); transition: all 0.15s ease; }
                .earthin-link-btn:hover { background: rgba(255,255,255,0.18); color: #ffffff !important; }
                .earthin-card { background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); padding: 24px 28px; margin-bottom: 24px; }
                .earthin-card-header { padding-bottom: 14px; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
                .earthin-card-title { font-size: 16px; font-weight: 700; color: #0f172a; margin: 0; }
                .earthin-btn-primary { background: #059669 !important; border-color: #059669 !important; color: #ffffff !important; font-weight: 700 !important; padding: 8px 22px !important; border-radius: 8px !important; box-shadow: 0 2px 4px rgba(5,150,105,0.2) !important; text-shadow: none !important; cursor: pointer; text-decoration: none; display: inline-block; }
                .earthin-btn-primary:hover { background: #047857 !important; }
                .earthin-btn-secondary { background: #f8fafc !important; border: 1px solid #cbd5e1 !important; color: #334155 !important; font-weight: 700 !important; padding: 8px 18px !important; border-radius: 8px !important; text-shadow: none !important; cursor: pointer; text-decoration: none; display: inline-block; }
                .earthin-badge-pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
                .earthin-badge-success { background: #ecfdf5; color: #059669; }
                .earthin-table { width: 100%; border-collapse: collapse; }
                .earthin-table th { background: #f8fafc; padding: 12px 14px; font-size: 12px; font-weight: 700; color: #475569; text-align: left; border-bottom: 2px solid #e2e8f0; }
                .earthin-table td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #334155; }
                .earthin-table tr:hover td { background: #fafbfc; }
                .earthin-textarea-code { width: 100%; background: #0f172a; color: #38bdf8; font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace; font-size: 13px; line-height: 1.5; padding: 16px; border-radius: 8px; border: 1px solid #1e293b; box-sizing: border-box; }
            </style>

            <header class="earthin-header">
                <div class="earthin-header-left">
                    <span class="earthin-logo-badge">🌱 Earthin</span>
                    <div class="earthin-title-group">
                        <h1>
                            <?php echo esc_html__('Earthin File Manager', 'earthin-file-manager'); ?>
                            <span class="earthin-version-tag">v1.0.0</span>
                            <span class="earthin-badge-pill earthin-badge-success"><?php echo esc_html__('Sandboxed ABSPATH', 'earthin-file-manager'); ?></span>
                        </h1>
                        <p><?php echo esc_html__('Hardened server file explorer and code editor engineered by Shaurya Joshi.', 'earthin-file-manager'); ?></p>
                    </div>
                </div>
                <div class="earthin-header-links">
                    <a href="https://plugins.earthin.online" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Plugin Hub', 'earthin-file-manager'); ?> &nearr;</a>
                    <a href="https://plugins.earthin.online/docs" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Documentation', 'earthin-file-manager'); ?></a>
                    <a href="https://plugins.earthin.online/support" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Support', 'earthin-file-manager'); ?></a>
                </div>
            </header>

            <?php settings_errors('earthin_files'); ?>

            <div class="earthin-card" style="padding: 18px 24px;">
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:13px; font-weight:700; color:#475569;"><?php echo esc_html__('Location:', 'earthin-file-manager'); ?></span>
                        <code style="background:#f1f5f9; padding:5px 10px; border-radius:6px; font-size:13px; color:#0f172a; word-break:break-all;"><?php echo esc_html($current_dir); ?></code>
                    </div>
                    <div>
                        <a href="<?php echo esc_url(admin_url('tools.php?page=earthin-file-manager&dir=' . urlencode($this->base_path))); ?>" class="button earthin-btn-secondary">
                            &uarr; <?php echo esc_html__('Root Directory', 'earthin-file-manager'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <?php if ($edit_file && is_file($edit_file)) : ?>
                <div class="earthin-card">
                    <div class="earthin-card-header">
                        <h2 class="earthin-card-title">
                            <?php echo esc_html__('Editing File:', 'earthin-file-manager'); ?>
                            <code style="color:#059669;"><?php echo esc_html(basename($edit_file)); ?></code>
                        </h2>
                    </div>
                    <form method="post">
                        <?php wp_nonce_field('earthin_save_file_nonce'); ?>
                        <input type="hidden" name="file_path" value="<?php echo esc_attr($edit_file); ?>" />
                        <textarea name="file_content" rows="18" class="earthin-textarea-code"><?php echo esc_textarea($edit_content); ?></textarea>
                        <p class="submit" style="margin-top:20px; padding-top:16px; border-top:1px solid #f1f5f9; display:flex; gap:10px;">
                            <input type="submit" name="earthin_save_file" class="button earthin-btn-primary" value="<?php echo esc_attr__('Save File Changes', 'earthin-file-manager'); ?>" />
                            <a href="<?php echo esc_url(admin_url('tools.php?page=earthin-file-manager&dir=' . urlencode(dirname($edit_file)))); ?>" class="button earthin-btn-secondary">
                                <?php echo esc_html__('Close Editor', 'earthin-file-manager'); ?>
                            </a>
                        </p>
                    </form>
                </div>
            <?php endif; ?>

            <div class="earthin-card" style="padding:0; overflow:hidden;">
                <div style="padding: 20px 24px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                    <h2 class="earthin-card-title"><?php echo esc_html__('Directory Contents', 'earthin-file-manager'); ?> (<?php echo count($items); ?> items)</h2>
                </div>
                <div style="overflow-x: auto;">
                    <table class="earthin-table">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('Name', 'earthin-file-manager'); ?></th>
                                <th style="width:110px;"><?php echo esc_html__('Size', 'earthin-file-manager'); ?></th>
                                <th style="width:110px;"><?php echo esc_html__('Permissions', 'earthin-file-manager'); ?></th>
                                <th style="width:160px;"><?php echo esc_html__('Last Modified', 'earthin-file-manager'); ?></th>
                                <th style="width:100px; text-align:right;"><?php echo esc_html__('Action', 'earthin-file-manager'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item) : ?>
                                <tr>
                                    <td>
                                        <?php if ($item['is_dir']) : ?>
                                            <span style="font-size:16px;">📁</span> <strong>
                                                <a href="<?php echo esc_url(admin_url('tools.php?page=earthin-file-manager&dir=' . urlencode($item['path']))); ?>" style="color:#0f172a; text-decoration:none;">
                                                    <?php echo esc_html($item['name']); ?>
                                                </a>
                                            </strong>
                                        <?php else : ?>
                                            <span style="font-size:16px;">📄</span> <span style="color:#334155;"><?php echo esc_html($item['name']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span style="font-size:12px; color:#64748b;"><?php echo esc_html($item['size']); ?></span></td>
                                    <td><code style="background:#f1f5f9; padding:2px 6px; border-radius:4px; font-size:12px;"><?php echo esc_html($item['perms']); ?></code></td>
                                    <td><span style="font-size:12px; color:#64748b;"><?php echo esc_html($item['modified']); ?></span></td>
                                    <td style="text-align:right;">
                                        <?php if (!$item['is_dir']) : ?>
                                            <a href="<?php echo esc_url(admin_url('tools.php?page=earthin-file-manager&dir=' . urlencode($current_dir) . '&edit=' . urlencode($item['path']))); ?>" class="button earthin-btn-secondary" style="padding:4px 12px !important; font-size:12px !important;">
                                                <?php echo esc_html__('Edit', 'earthin-file-manager'); ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_File_Manager', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('tools.php?page=earthin-file-manager')) . '">' . __('Settings', 'earthin-file-manager') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
