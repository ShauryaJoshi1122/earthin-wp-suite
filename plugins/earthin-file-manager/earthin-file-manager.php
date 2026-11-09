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
        <div class="wrap">
            <h1><?php echo esc_html__('Earthin File Manager', 'earthin-file-manager'); ?></h1>
            <p><?php echo esc_html__('Secure directory explorer and code editor engineered by Shaurya Joshi.', 'earthin-file-manager'); ?></p>
            <?php settings_errors('earthin_files'); ?>

            <div style="background:#fff;padding:16px;border:1px solid #ccd0d4;border-radius:8px;margin-top:15px;display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <strong><?php echo esc_html__('Current Path:', 'earthin-file-manager'); ?></strong>
                    <code><?php echo esc_html($current_dir); ?></code>
                </div>
                <div>
                    <a href="<?php echo esc_url(admin_url('tools.php?page=earthin-file-manager&dir=' . urlencode($this->base_path))); ?>" class="button button-secondary">
                        <?php echo esc_html__('Root (ABSPATH)', 'earthin-file-manager'); ?>
                    </a>
                </div>
            </div>

            <?php if ($edit_file && is_file($edit_file)) : ?>
                <div style="background:#fff;padding:24px;border:1px solid #ccd0d4;border-radius:8px;margin-top:20px;">
                    <h2><?php echo esc_html__('Editing File:', 'earthin-file-manager'); ?> <code><?php echo esc_html(basename($edit_file)); ?></code></h2>
                    <form method="post">
                        <?php wp_nonce_field('earthin_save_file_nonce'); ?>
                        <input type="hidden" name="file_path" value="<?php echo esc_attr($edit_file); ?>" />
                        <textarea name="file_content" rows="18" style="width:100%;font-family:monospace;font-size:13px;"><?php echo esc_textarea($edit_content); ?></textarea>
                        <p class="submit">
                            <input type="submit" name="earthin_save_file" class="button button-primary" value="<?php echo esc_attr__('Save File Changes', 'earthin-file-manager'); ?>" />
                            <a href="<?php echo esc_url(admin_url('tools.php?page=earthin-file-manager&dir=' . urlencode(dirname($edit_file)))); ?>" class="button button-secondary">
                                <?php echo esc_html__('Close Editor', 'earthin-file-manager'); ?>
                            </a>
                        </p>
                    </form>
                </div>
            <?php endif; ?>

            <div style="background:#fff;padding:24px;border:1px solid #ccd0d4;border-radius:8px;margin-top:20px;">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('File / Directory Name', 'earthin-file-manager'); ?></th>
                            <th style="width:100px;"><?php echo esc_html__('Size', 'earthin-file-manager'); ?></th>
                            <th style="width:100px;"><?php echo esc_html__('Permissions', 'earthin-file-manager'); ?></th>
                            <th style="width:160px;"><?php echo esc_html__('Last Modified', 'earthin-file-manager'); ?></th>
                            <th style="width:120px;"><?php echo esc_html__('Action', 'earthin-file-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item) : ?>
                            <tr>
                                <td>
                                    <?php if ($item['is_dir']) : ?>
                                        📁 <strong>
                                            <a href="<?php echo esc_url(admin_url('tools.php?page=earthin-file-manager&dir=' . urlencode($item['path']))); ?>">
                                                <?php echo esc_html($item['name']); ?>
                                            </a>
                                        </strong>
                                    <?php else : ?>
                                        📄 <?php echo esc_html($item['name']); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($item['size']); ?></td>
                                <td><code><?php echo esc_html($item['perms']); ?></code></td>
                                <td><?php echo esc_html($item['modified']); ?></td>
                                <td>
                                    <?php if (!$item['is_dir']) : ?>
                                        <a href="<?php echo esc_url(admin_url('tools.php?page=earthin-file-manager&dir=' . urlencode($current_dir) . '&edit=' . urlencode($item['path']))); ?>" class="button button-small">
                                            Edit
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_File_Manager', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function() {
     = '<a href="' . esc_url(admin_url('tools.php?page=earthin-file-manager')) . '">' . __('Settings', 'earthin-file-manager') . '</a>';
    array_unshift(, );
    return ;
});
