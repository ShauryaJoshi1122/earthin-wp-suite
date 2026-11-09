<?php
/**
 * Plugin Name: Earthin Code Snippets
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-code-snippets
 * Description: Clean and safe code snippet manager for executing custom PHP, CSS, JavaScript, and HTML with error protection.
 * Version: 1.0.0
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * Text Domain: earthin-code-snippets
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class Earthin_Code_Snippets {
    private static ?Earthin_Code_Snippets $instance = null;
    private array $snippets;

    public static function get_instance(): Earthin_Code_Snippets {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->snippets = get_option('earthin_code_snippets', []);

        add_action('init', [$this, 'execute_php_snippets'], 1);
        add_action('wp_head', [$this, 'inject_head_snippets']);
        add_action('wp_footer', [$this, 'inject_footer_snippets']);
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_init', [$this, 'handle_admin_actions']);
    }

    public function execute_php_snippets(): void {
        foreach ($this->snippets as $id => $snippet) {
            if (empty($snippet['active']) || 'php' !== ($snippet['type'] ?? '')) {
                continue;
            }

            // Check location scope
            $scope = $snippet['scope'] ?? 'global';
            if ('admin' === $scope && !is_admin()) {
                continue;
            }
            if ('frontend' === $scope && is_admin()) {
                continue;
            }

            // Safe execution via try-catch
            try {
                $code = $snippet['code'] ?? '';
                // Strip opening and closing php tags if present
                $code = preg_replace('/^<\?(php)?/i', '', trim($code));
                $code = preg_replace('/\?>$/', '', trim($code));
                
                if (!empty($code)) {
                    eval($code);
                }
            } catch (\Throwable $e) {
                // Auto-deactivate offending snippet to prevent crash
                $this->snippets[$id]['active'] = 0;
                $this->snippets[$id]['last_error'] = $e->getMessage();
                update_option('earthin_code_snippets', $this->snippets);
            }
        }
    }

    public function inject_head_snippets(): void {
        foreach ($this->snippets as $snippet) {
            if (empty($snippet['active'])) {
                continue;
            }
            if ('css' === ($snippet['type'] ?? '')) {
                echo "<style id=\"earthin-snippet-" . esc_attr($snippet['id']) . "\">\n" . wp_strip_all_tags($snippet['code']) . "\n</style>\n";
            }
            if ('html_head' === ($snippet['type'] ?? '')) {
                echo $snippet['code'] . "\n";
            }
        }
    }

    public function inject_footer_snippets(): void {
        foreach ($this->snippets as $snippet) {
            if (empty($snippet['active'])) {
                continue;
            }
            if ('js' === ($snippet['type'] ?? '')) {
                echo "<script id=\"earthin-snippet-" . esc_attr($snippet['id']) . "\">\n" . $snippet['code'] . "\n</script>\n";
            }
        }
    }

    public function register_admin_menu(): void {
        add_management_page(
            __('Earthin Snippets', 'earthin-code-snippets'),
            __('Earthin Snippets', 'earthin-code-snippets'),
            'manage_options',
            'earthin-code-snippets',
            [$this, 'render_admin_page']
        );
    }

    public function handle_admin_actions(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Add or Edit Snippet
        if (isset($_POST['earthin_save_snippet']) && check_admin_referer('earthin_snippet_nonce')) {
            $id = sanitize_key($_POST['snippet_id'] ?? ('snip_' . time()));
            $name = sanitize_text_field($_POST['snippet_name'] ?? 'Untitled Snippet');
            $type = sanitize_key($_POST['snippet_type'] ?? 'php');
            $scope = sanitize_key($_POST['snippet_scope'] ?? 'global');
            $code = wp_unslash($_POST['snippet_code'] ?? '');
            $active = isset($_POST['snippet_active']) ? 1 : 0;

            $this->snippets[$id] = [
                'id' => $id,
                'name' => $name,
                'type' => $type,
                'scope' => $scope,
                'code' => $code,
                'active' => $active,
                'updated_at' => current_time('mysql'),
            ];

            update_option('earthin_code_snippets', $this->snippets);
            add_settings_error('earthin_snippets', 'saved', __('Snippet saved successfully.', 'earthin-code-snippets'), 'updated');
        }

        // Toggle Snippet
        if (isset($_GET['action'], $_GET['snippet_id']) && 'toggle' === $_GET['action'] && check_admin_referer('earthin_toggle_' . $_GET['snippet_id'])) {
            $id = sanitize_key($_GET['snippet_id']);
            if (isset($this->snippets[$id])) {
                $this->snippets[$id]['active'] = empty($this->snippets[$id]['active']) ? 1 : 0;
                update_option('earthin_code_snippets', $this->snippets);
                add_settings_error('earthin_snippets', 'toggled', __('Snippet status updated.', 'earthin-code-snippets'), 'updated');
            }
        }

        // Delete Snippet
        if (isset($_GET['action'], $_GET['snippet_id']) && 'delete' === $_GET['action'] && check_admin_referer('earthin_delete_' . $_GET['snippet_id'])) {
            $id = sanitize_key($_GET['snippet_id']);
            if (isset($this->snippets[$id])) {
                unset($this->snippets[$id]);
                update_option('earthin_code_snippets', $this->snippets);
                add_settings_error('earthin_snippets', 'deleted', __('Snippet deleted.', 'earthin-code-snippets'), 'updated');
            }
        }
    }

    public function render_admin_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $edit_id = isset($_GET['edit']) ? sanitize_key($_GET['edit']) : null;
        $edit_snippet = $edit_id && isset($this->snippets[$edit_id]) ? $this->snippets[$edit_id] : null;
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Earthin Code Snippets', 'earthin-code-snippets'); ?></h1>
            <p><?php echo esc_html__('Add custom PHP code, CSS styling, and JavaScript without editing theme template files.', 'earthin-code-snippets'); ?></p>
            <?php settings_errors('earthin_snippets'); ?>

            <div style="display:flex;gap:20px;margin-top:20px;">
                <!-- Snippet List -->
                <div style="flex:3;background:#fff;padding:24px;border:1px solid #ccd0d4;border-radius:8px;">
                    <h2><?php echo esc_html__('All Snippets', 'earthin-code-snippets'); ?> (<?php echo count($this->snippets); ?>)</h2>
                    
                    <?php if (empty($this->snippets)) : ?>
                        <p><?php echo esc_html__('No snippets created yet. Use the editor on the right to add your first snippet.', 'earthin-code-snippets'); ?></p>
                    <?php else : ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php echo esc_html__('Name', 'earthin-code-snippets'); ?></th>
                                    <th style="width:90px;"><?php echo esc_html__('Type', 'earthin-code-snippets'); ?></th>
                                    <th style="width:100px;"><?php echo esc_html__('Scope', 'earthin-code-snippets'); ?></th>
                                    <th style="width:80px;"><?php echo esc_html__('Status', 'earthin-code-snippets'); ?></th>
                                    <th style="width:140px;"><?php echo esc_html__('Actions', 'earthin-code-snippets'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($this->snippets as $snip) : ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($snip['name']); ?></strong></td>
                                        <td><code><?php echo esc_html(strtoupper($snip['type'])); ?></code></td>
                                        <td><?php echo esc_html(ucfirst($snip['scope'] ?? 'global')); ?></td>
                                        <td>
                                            <?php if (!empty($snip['active'])) : ?>
                                                <span style="color:#008a20;font-weight:bold;">Active</span>
                                            <?php else : ?>
                                                <span style="color:#888;">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('tools.php?page=earthin-code-snippets&action=toggle&snippet_id=' . $snip['id']), 'earthin_toggle_' . $snip['id'])); ?>">
                                                <?php echo !empty($snip['active']) ? 'Deactivate' : 'Activate'; ?>
                                            </a> | 
                                            <a href="<?php echo esc_url(admin_url('tools.php?page=earthin-code-snippets&edit=' . $snip['id'])); ?>">
                                                Edit
                                            </a> | 
                                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('tools.php?page=earthin-code-snippets&action=delete&snippet_id=' . $snip['id']), 'earthin_delete_' . $snip['id'])); ?>" onclick="return confirm('Delete snippet?');" style="color:#d63638;">
                                                Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Add/Edit Form -->
                <div style="flex:2;background:#fff;padding:24px;border:1px solid #ccd0d4;border-radius:8px;">
                    <h2><?php echo $edit_snippet ? esc_html__('Edit Snippet', 'earthin-code-snippets') : esc_html__('Add New Snippet', 'earthin-code-snippets'); ?></h2>
                    <form method="post">
                        <?php wp_nonce_field('earthin_snippet_nonce'); ?>
                        <input type="hidden" name="snippet_id" value="<?php echo esc_attr($edit_snippet['id'] ?? ('snip_' . time())); ?>" />

                        <p>
                            <label><strong><?php echo esc_html__('Snippet Title', 'earthin-code-snippets'); ?></strong></label><br>
                            <input type="text" name="snippet_name" value="<?php echo esc_attr($edit_snippet['name'] ?? ''); ?>" style="width:100%;" required />
                        </p>

                        <p>
                            <label><strong><?php echo esc_html__('Type', 'earthin-code-snippets'); ?></strong></label><br>
                            <select name="snippet_type" style="width:100%;">
                                <option value="php" <?php selected('php', $edit_snippet['type'] ?? 'php'); ?>>PHP Code</option>
                                <option value="css" <?php selected('css', $edit_snippet['type'] ?? ''); ?>>CSS Stylesheet</option>
                                <option value="js" <?php selected('js', $edit_snippet['type'] ?? ''); ?>>JavaScript</option>
                                <option value="html_head" <?php selected('html_head', $edit_snippet['type'] ?? ''); ?>>HTML Head Tag</option>
                            </select>
                        </p>

                        <p>
                            <label><strong><?php echo esc_html__('Execution Location', 'earthin-code-snippets'); ?></strong></label><br>
                            <select name="snippet_scope" style="width:100%;">
                                <option value="global" <?php selected('global', $edit_snippet['scope'] ?? 'global'); ?>>Run Everywhere (Global)</option>
                                <option value="frontend" <?php selected('frontend', $edit_snippet['scope'] ?? ''); ?>>Frontend Only</option>
                                <option value="admin" <?php selected('admin', $edit_snippet['scope'] ?? ''); ?>>Admin Only</option>
                            </select>
                        </p>

                        <p>
                            <label><strong><?php echo esc_html__('Code Content', 'earthin-code-snippets'); ?></strong></label><br>
                            <textarea name="snippet_code" rows="12" style="width:100%;font-family:monospace;font-size:12px;"><?php echo esc_textarea($edit_snippet['code'] ?? ''); ?></textarea>
                        </p>

                        <p>
                            <label>
                                <input type="checkbox" name="snippet_active" value="1" <?php checked(1, $edit_snippet['active'] ?? 1); ?> />
                                <?php echo esc_html__('Activate snippet immediately', 'earthin-code-snippets'); ?>
                            </label>
                        </p>

                        <p class="submit">
                            <input type="submit" name="earthin_save_snippet" class="button button-primary" value="<?php echo esc_attr__('Save Snippet', 'earthin-code-snippets'); ?>" />
                            <?php if ($edit_snippet) : ?>
                                <a href="<?php echo esc_url(admin_url('tools.php?page=earthin-code-snippets')); ?>" class="button button-secondary"><?php echo esc_html__('Cancel', 'earthin-code-snippets'); ?></a>
                            <?php endif; ?>
                        </p>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Code_Snippets', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function() {
     = '<a href="' . esc_url(admin_url('tools.php?page=earthin-code-snippets')) . '">' . __('Settings', 'earthin-code-snippets') . '</a>';
    array_unshift(, );
    return ;
});
