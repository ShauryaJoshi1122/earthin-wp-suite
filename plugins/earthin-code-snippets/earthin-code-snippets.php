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
        $total_snippets = count($this->snippets);
        $active_count = 0;
        foreach ($this->snippets as $snip) {
            if (!empty($snip['active'])) {
                $active_count++;
            }
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
                .earthin-grid { display: grid; grid-template-columns: 1.7fr 1.3fr; gap: 24px; }
                @media(max-width: 960px) { .earthin-grid { grid-template-columns: 1fr; } }
                .earthin-card { background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); padding: 24px 28px; margin-bottom: 24px; }
                .earthin-card-header { padding-bottom: 14px; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
                .earthin-card-title { font-size: 16px; font-weight: 700; color: #0f172a; margin: 0; }
                .earthin-btn-primary { background: #059669 !important; border-color: #059669 !important; color: #ffffff !important; font-weight: 700 !important; padding: 8px 22px !important; border-radius: 8px !important; box-shadow: 0 2px 4px rgba(5,150,105,0.2) !important; text-shadow: none !important; cursor: pointer; }
                .earthin-btn-primary:hover { background: #047857 !important; }
                .earthin-btn-secondary { background: #f8fafc !important; border: 1px solid #cbd5e1 !important; color: #334155 !important; font-weight: 700 !important; padding: 8px 18px !important; border-radius: 8px !important; text-shadow: none !important; cursor: pointer; text-decoration: none; display: inline-block; }
                .earthin-badge-pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
                .earthin-badge-success { background: #ecfdf5; color: #059669; }
                .earthin-badge-neutral { background: #f1f5f9; color: #64748b; }
                .earthin-badge-php { background: #e0e7ff; color: #4338ca; }
                .earthin-badge-css { background: #e0f2fe; color: #0284c7; }
                .earthin-badge-js { background: #fef3c7; color: #b45309; }
                .earthin-badge-html { background: #fee2e2; color: #b91c1c; }
                .earthin-input { border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 14px; width: 100%; box-sizing: border-box; }
                .earthin-input:focus { border-color: #059669; outline: none; box-shadow: 0 0 0 3px rgba(5,150,105,0.15); }
                .earthin-textarea-code { width: 100%; background: #0f172a; color: #38bdf8; font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace; font-size: 13px; line-height: 1.5; padding: 14px; border-radius: 8px; border: 1px solid #1e293b; box-sizing: border-box; }
                .earthin-table { width: 100%; border-collapse: collapse; }
                .earthin-table th { background: #f8fafc; padding: 10px 14px; font-size: 12px; font-weight: 700; color: #475569; text-align: left; border-bottom: 2px solid #e2e8f0; }
                .earthin-table td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #334155; }
                .earthin-table tr:hover td { background: #fafbfc; }
                .earthin-action-link { font-weight: 600; text-decoration: none; font-size: 12px; }
            </style>

            <header class="earthin-header">
                <div class="earthin-header-left">
                    <span class="earthin-logo-badge">🌱 Earthin</span>
                    <div class="earthin-title-group">
                        <h1>
                            <?php echo esc_html__('Earthin Code Snippets', 'earthin-code-snippets'); ?>
                            <span class="earthin-version-tag">v1.0.0</span>
                            <span class="earthin-badge-pill earthin-badge-success"><?php echo sprintf(esc_html__('%d Active', 'earthin-code-snippets'), $active_count); ?></span>
                        </h1>
                        <p><?php echo esc_html__('Safe snippet execution sandbox engineered by Shaurya Joshi.', 'earthin-code-snippets'); ?></p>
                    </div>
                </div>
                <div class="earthin-header-links">
                    <a href="https://plugins.earthin.online" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Plugin Hub', 'earthin-code-snippets'); ?> &nearr;</a>
                    <a href="https://plugins.earthin.online/docs" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Documentation', 'earthin-code-snippets'); ?></a>
                    <a href="https://plugins.earthin.online/support" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Support', 'earthin-code-snippets'); ?></a>
                </div>
            </header>

            <?php settings_errors('earthin_snippets'); ?>

            <div class="earthin-grid">
                <!-- Snippet List -->
                <div class="earthin-card">
                    <div class="earthin-card-header">
                        <h2 class="earthin-card-title"><?php echo esc_html__('Snippet Library', 'earthin-code-snippets'); ?> (<?php echo esc_html((string)$total_snippets); ?>)</h2>
                    </div>
                    
                    <?php if (empty($this->snippets)) : ?>
                        <div style="padding: 30px; text-align: center; color: #64748b; background: #f8fafc; border-radius: 8px;">
                            <p style="margin: 0; font-size: 14px;"><?php echo esc_html__('No custom snippets configured yet. Use the editor to add your first PHP, CSS, or JS snippet.', 'earthin-code-snippets'); ?></p>
                        </div>
                    <?php else : ?>
                        <div style="overflow-x: auto;">
                            <table class="earthin-table">
                                <thead>
                                    <tr>
                                        <th><?php echo esc_html__('Snippet', 'earthin-code-snippets'); ?></th>
                                        <th style="width:75px;"><?php echo esc_html__('Type', 'earthin-code-snippets'); ?></th>
                                        <th style="width:90px;"><?php echo esc_html__('Scope', 'earthin-code-snippets'); ?></th>
                                        <th style="width:85px;"><?php echo esc_html__('Status', 'earthin-code-snippets'); ?></th>
                                        <th style="width:130px; text-align:right;"><?php echo esc_html__('Actions', 'earthin-code-snippets'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($this->snippets as $snip) : 
                                        $type = strtolower($snip['type'] ?? 'php');
                                        $type_class = 'earthin-badge-' . ($type === 'php' ? 'php' : ($type === 'css' ? 'css' : ($type === 'js' ? 'js' : 'html')));
                                    ?>
                                        <tr>
                                            <td><strong><?php echo esc_html($snip['name']); ?></strong></td>
                                            <td><span class="earthin-badge-pill <?php echo esc_attr($type_class); ?>"><?php echo esc_html(strtoupper($snip['type'])); ?></span></td>
                                            <td><span style="font-size:12px; color:#475569;"><?php echo esc_html(ucfirst($snip['scope'] ?? 'global')); ?></span></td>
                                            <td>
                                                <?php if (!empty($snip['active'])) : ?>
                                                    <span class="earthin-badge-pill earthin-badge-success"><?php echo esc_html__('Active', 'earthin-code-snippets'); ?></span>
                                                <?php else : ?>
                                                    <span class="earthin-badge-pill earthin-badge-neutral"><?php echo esc_html__('Paused', 'earthin-code-snippets'); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align:right; white-space:nowrap;">
                                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('tools.php?page=earthin-code-snippets&action=toggle&snippet_id=' . $snip['id']), 'earthin_toggle_' . $snip['id'])); ?>" class="earthin-action-link" style="color: #059669;">
                                                    <?php echo !empty($snip['active']) ? esc_html__('Pause', 'earthin-code-snippets') : esc_html__('Activate', 'earthin-code-snippets'); ?>
                                                </a> &bull; 
                                                <a href="<?php echo esc_url(admin_url('tools.php?page=earthin-code-snippets&edit=' . $snip['id'])); ?>" class="earthin-action-link" style="color: #0284c7;">
                                                    <?php echo esc_html__('Edit', 'earthin-code-snippets'); ?>
                                                </a> &bull; 
                                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('tools.php?page=earthin-code-snippets&action=delete&snippet_id=' . $snip['id']), 'earthin_delete_' . $snip['id'])); ?>" onclick="return confirm('Delete snippet?');" class="earthin-action-link" style="color: #ef4444;">
                                                    <?php echo esc_html__('Delete', 'earthin-code-snippets'); ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Add/Edit Form -->
                <div class="earthin-card">
                    <div class="earthin-card-header">
                        <h2 class="earthin-card-title"><?php echo $edit_snippet ? esc_html__('Edit Snippet', 'earthin-code-snippets') : esc_html__('Add New Snippet', 'earthin-code-snippets'); ?></h2>
                    </div>
                    <form method="post">
                        <?php wp_nonce_field('earthin_snippet_nonce'); ?>
                        <input type="hidden" name="snippet_id" value="<?php echo esc_attr($edit_snippet['id'] ?? ('snip_' . time())); ?>" />

                        <p>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:5px;"><?php echo esc_html__('Snippet Title', 'earthin-code-snippets'); ?></label>
                            <input type="text" name="snippet_name" value="<?php echo esc_attr($edit_snippet['name'] ?? ''); ?>" placeholder="<?php echo esc_attr__('e.g., Disable XML-RPC, Custom Header Styling', 'earthin-code-snippets'); ?>" class="earthin-input" required />
                        </p>

                        <div style="display:flex; gap:12px; margin-bottom:14px;">
                            <div style="flex:1;">
                                <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:5px;"><?php echo esc_html__('Snippet Type', 'earthin-code-snippets'); ?></label>
                                <select name="snippet_type" class="earthin-input">
                                    <option value="php" <?php selected('php', $edit_snippet['type'] ?? 'php'); ?>>PHP Code</option>
                                    <option value="css" <?php selected('css', $edit_snippet['type'] ?? ''); ?>>CSS Stylesheet</option>
                                    <option value="js" <?php selected('js', $edit_snippet['type'] ?? ''); ?>>JavaScript</option>
                                    <option value="html_head" <?php selected('html_head', $edit_snippet['type'] ?? ''); ?>>HTML Head Tag</option>
                                </select>
                            </div>
                            <div style="flex:1;">
                                <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:5px;"><?php echo esc_html__('Execution Scope', 'earthin-code-snippets'); ?></label>
                                <select name="snippet_scope" class="earthin-input">
                                    <option value="global" <?php selected('global', $edit_snippet['scope'] ?? 'global'); ?>>Run Everywhere</option>
                                    <option value="frontend" <?php selected('frontend', $edit_snippet['scope'] ?? ''); ?>>Frontend Only</option>
                                    <option value="admin" <?php selected('admin', $edit_snippet['scope'] ?? ''); ?>>Admin Only</option>
                                </select>
                            </div>
                        </div>

                        <p>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:5px;"><?php echo esc_html__('Code Body', 'earthin-code-snippets'); ?></label>
                            <textarea name="snippet_code" rows="12" class="earthin-textarea-code" placeholder="<?php echo esc_attr__('// Paste your code here (without <?php opening tags)', 'earthin-code-snippets'); ?>"><?php echo esc_textarea($edit_snippet['code'] ?? ''); ?></textarea>
                        </p>

                        <p style="margin-top: 14px;">
                            <label>
                                <input type="checkbox" name="snippet_active" value="1" <?php checked(1, $edit_snippet['active'] ?? 1); ?> />
                                <strong><?php echo esc_html__('Activate snippet upon saving', 'earthin-code-snippets'); ?></strong>
                            </label>
                        </p>

                        <p class="submit" style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #f1f5f9; display: flex; gap: 10px;">
                            <input type="submit" name="earthin_save_snippet" class="button earthin-btn-primary" value="<?php echo esc_attr__('Save Snippet', 'earthin-code-snippets'); ?>" />
                            <?php if ($edit_snippet) : ?>
                                <a href="<?php echo esc_url(admin_url('tools.php?page=earthin-code-snippets')); ?>" class="button earthin-btn-secondary"><?php echo esc_html__('Cancel', 'earthin-code-snippets'); ?></a>
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

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('tools.php?page=earthin-code-snippets')) . '">' . __('Settings', 'earthin-code-snippets') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
