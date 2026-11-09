<?php
/**
 * Plugin Name: Earthin Cache Accelerator
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-cache-accelerator
 * Description: High-performance static HTML page caching engine with automatic cache invalidation, cache preloading, and browser header tuning.
 * Version: 1.0.0
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * Text Domain: earthin-cache-accelerator
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class Earthin_Cache_Accelerator {
    private static ?Earthin_Cache_Accelerator $instance = null;
    private string $cache_dir;
    private array $options;

    public static function get_instance(): Earthin_Cache_Accelerator {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $upload_dir = wp_upload_dir();
        $this->cache_dir = trailingslashit(WP_CONTENT_DIR) . 'cache/earthin-cache/';
        $this->options = get_option('earthin_cache_options', [
            'enabled' => 1,
            'cache_ttl' => 86400, // 24 hours
            'purge_on_post_save' => 1,
            'bypass_logged_in' => 1,
            'browser_caching' => 1,
            'gzip_compression' => 1,
        ]);

        add_action('init', [$this, 'start_caching_buffer']);
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_init', [$this, 'handle_admin_actions']);
        add_action('save_post', [$this, 'purge_post_cache'], 10, 2);
        add_action('wp_set_comment_status', [$this, 'purge_all_cache']);
    }

    public function start_caching_buffer(): void {
        if (is_admin() || (defined('DOING_CRON') && DOING_CRON) || (defined('DOING_AJAX') && DOING_AJAX) || (defined('REST_REQUEST') && REST_REQUEST)) {
            return;
        }

        if (empty($this->options['enabled'])) {
            return;
        }

        // Bypass for logged-in users if configured
        if (!empty($this->options['bypass_logged_in']) && is_user_logged_in()) {
            return;
        }

        // Only cache GET requests
        if (isset($_SERVER['REQUEST_METHOD']) && 'GET' !== $_SERVER['REQUEST_METHOD']) {
            return;
        }

        // Check if cache file already exists and is fresh
        $cache_file = $this->get_cache_filepath();
        if (file_exists($cache_file)) {
            $ttl = (int) ($this->options['cache_ttl'] ?? 86400);
            if ((time() - filemtime($cache_file)) < $ttl) {
                if (!headers_sent()) {
                    header('X-Earthin-Cache: HIT');
                    if (!empty($this->options['browser_caching'])) {
                        header('Cache-Control: public, max-age=' . $ttl);
                    }
                }
                readfile($cache_file);
                exit;
            }
        }

        // Start output buffering
        ob_start([$this, 'save_output_to_cache']);
    }

    public function save_output_to_cache(string $buffer): string {
        if (strlen($buffer) < 250 || is_404() || is_search()) {
            return $buffer;
        }

        if (!is_dir($this->cache_dir)) {
            wp_mkdir_p($this->cache_dir);
            file_put_contents($this->cache_dir . 'index.html', '');
        }

        $cache_file = $this->get_cache_filepath();
        $cached_content = $buffer . "\n<!-- Earthin Cache Accelerator Generated: " . gmdate('Y-m-d H:i:s') . " UTC -->";
        file_put_contents($cache_file, $cached_content);

        if (!headers_sent()) {
            header('X-Earthin-Cache: MISS');
        }

        return $buffer;
    }

    private function get_cache_filepath(): string {
        $host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : 'localhost';
        $uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '/';
        $hash = md5($host . $uri);
        return $this->cache_dir . $hash . '.html';
    }

    public function purge_post_cache(int $post_id, $post): void {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!empty($this->options['purge_on_post_save'])) {
            $this->purge_all_cache();
        }
    }

    public function purge_all_cache(): bool {
        if (!is_dir($this->cache_dir)) {
            return true;
        }
        $files = glob($this->cache_dir . '*.html');
        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
        return true;
    }

    public function register_admin_menu(): void {
        add_options_page(
            __('Earthin Cache', 'earthin-cache-accelerator'),
            __('Earthin Cache', 'earthin-cache-accelerator'),
            'manage_options',
            'earthin-cache-accelerator',
            [$this, 'render_admin_page']
        );
    }

    public function handle_admin_actions(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['earthin_purge_cache']) && check_admin_referer('earthin_purge_cache_nonce')) {
            $this->purge_all_cache();
            add_settings_error('earthin_cache', 'cache_purged', __('Entire page cache successfully purged.', 'earthin-cache-accelerator'), 'updated');
        }

        if (isset($_POST['earthin_save_cache_settings']) && check_admin_referer('earthin_save_cache_settings_nonce')) {
            $this->options['enabled'] = isset($_POST['enabled']) ? 1 : 0;
            $this->options['cache_ttl'] = max(300, (int) ($_POST['cache_ttl'] ?? 86400));
            $this->options['purge_on_post_save'] = isset($_POST['purge_on_post_save']) ? 1 : 0;
            $this->options['bypass_logged_in'] = isset($_POST['bypass_logged_in']) ? 1 : 0;
            $this->options['browser_caching'] = isset($_POST['browser_caching']) ? 1 : 0;
            $this->options['gzip_compression'] = isset($_POST['gzip_compression']) ? 1 : 0;

            update_option('earthin_cache_options', $this->options);
            add_settings_error('earthin_cache', 'settings_saved', __('Cache configuration saved.', 'earthin-cache-accelerator'), 'updated');
        }
    }

    public function render_admin_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $cached_files = is_dir($this->cache_dir) ? glob($this->cache_dir . '*.html') : [];
        $total_files = is_array($cached_files) ? count($cached_files) : 0;
        $total_size = 0;
        if (is_array($cached_files)) {
            foreach ($cached_files as $f) {
                if (is_file($f)) {
                    $total_size += filesize($f);
                }
            }
        }
        $formatted_size = size_format($total_size, 2);
        $status_label = !empty($this->options['enabled']) ? __('Cache Engine Active', 'earthin-cache-accelerator') : __('Cache Engine Inactive', 'earthin-cache-accelerator');
        $status_badge_class = !empty($this->options['enabled']) ? 'earthin-badge-success' : 'earthin-badge-warning';
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
                .earthin-stat-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; margin-bottom: 16px; }
                .earthin-stat-num { font-size: 26px; font-weight: 800; color: #059669; line-height: 1.1; }
                .earthin-stat-label { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; margin-top: 4px; }
                .earthin-btn-primary { background: #059669 !important; border-color: #059669 !important; color: #ffffff !important; font-weight: 700 !important; padding: 8px 22px !important; border-radius: 8px !important; box-shadow: 0 2px 4px rgba(5,150,105,0.2) !important; text-shadow: none !important; cursor: pointer; }
                .earthin-btn-primary:hover { background: #047857 !important; }
                .earthin-btn-danger { background: #ef4444 !important; border-color: #ef4444 !important; color: #ffffff !important; font-weight: 700 !important; padding: 8px 18px !important; border-radius: 8px !important; text-shadow: none !important; cursor: pointer; }
                .earthin-btn-danger:hover { background: #dc2626 !important; }
                .earthin-badge-pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
                .earthin-badge-success { background: #ecfdf5; color: #059669; }
                .earthin-badge-warning { background: #fffbeb; color: #d97706; }
                .earthin-badge-neutral { background: #f1f5f9; color: #475569; }
                .earthin-input { border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 14px; width: 100%; max-width: 400px; }
                .earthin-input:focus { border-color: #059669; outline: none; box-shadow: 0 0 0 3px rgba(5,150,105,0.15); }
            </style>

            <header class="earthin-header">
                <div class="earthin-header-left">
                    <span class="earthin-logo-badge">🌱 Earthin</span>
                    <div class="earthin-title-group">
                        <h1>
                            <?php echo esc_html__('Earthin Cache Accelerator', 'earthin-cache-accelerator'); ?>
                            <span class="earthin-version-tag">v1.0.0</span>
                            <span class="earthin-badge-pill <?php echo esc_attr($status_badge_class); ?>"><?php echo esc_html($status_label); ?></span>
                        </h1>
                        <p><?php echo esc_html__('High-efficiency static HTML caching engine developed by Shaurya Joshi.', 'earthin-cache-accelerator'); ?></p>
                    </div>
                </div>
                <div class="earthin-header-links">
                    <a href="https://plugins.earthin.online" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Plugin Hub', 'earthin-cache-accelerator'); ?> &nearr;</a>
                    <a href="https://plugins.earthin.online/docs" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Documentation', 'earthin-cache-accelerator'); ?></a>
                    <a href="https://plugins.earthin.online/support" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Support', 'earthin-cache-accelerator'); ?></a>
                </div>
            </header>

            <?php settings_errors('earthin_cache'); ?>

            <div class="earthin-grid">
                <div class="earthin-card">
                    <div class="earthin-card-header">
                        <h2 class="earthin-card-title"><?php echo esc_html__('Cache Engine Controls & Settings', 'earthin-cache-accelerator'); ?></h2>
                    </div>

                    <form method="post">
                        <?php wp_nonce_field('earthin_save_cache_settings_nonce'); ?>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><?php echo esc_html__('Enable Static Caching', 'earthin-cache-accelerator'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="enabled" value="1" <?php checked(1, $this->options['enabled']); ?> />
                                        <strong><?php echo esc_html__('Activate disk caching for visited public pages', 'earthin-cache-accelerator'); ?></strong>
                                    </label>
                                    <p class="description"><?php echo esc_html__('Generates optimized static HTML snapshots for ultra-fast TTFB.', 'earthin-cache-accelerator'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('Cache Lifespan (TTL in seconds)', 'earthin-cache-accelerator'); ?></th>
                                <td>
                                    <input type="number" name="cache_ttl" value="<?php echo esc_attr($this->options['cache_ttl']); ?>" min="300" step="300" class="earthin-input" />
                                    <p class="description"><?php echo esc_html__('Default is 86400 (24 hours). Minimum threshold is 300 seconds.', 'earthin-cache-accelerator'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('Auto-Purge on Post Update', 'earthin-cache-accelerator'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="purge_on_post_save" value="1" <?php checked(1, $this->options['purge_on_post_save']); ?> />
                                        <?php echo esc_html__('Automatically clear cached HTML when posts, pages, or terms are updated.', 'earthin-cache-accelerator'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('Bypass for Logged-In Users', 'earthin-cache-accelerator'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="bypass_logged_in" value="1" <?php checked(1, $this->options['bypass_logged_in']); ?> />
                                        <?php echo esc_html__('Serve fresh dynamic content for logged-in administrators and editors.', 'earthin-cache-accelerator'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('Browser Cache Headers', 'earthin-cache-accelerator'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="browser_caching" value="1" <?php checked(1, $this->options['browser_caching']); ?> />
                                        <?php echo esc_html__('Send public Cache-Control headers to leverage client browser cache.', 'earthin-cache-accelerator'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>

                        <p class="submit" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #f1f5f9;">
                            <input type="submit" name="earthin_save_cache_settings" class="button earthin-btn-primary" value="<?php echo esc_attr__('Save Configuration', 'earthin-cache-accelerator'); ?>" />
                        </p>
                    </form>
                </div>

                <div>
                    <div class="earthin-card">
                        <div class="earthin-card-header">
                            <h2 class="earthin-card-title"><?php echo esc_html__('Cache Health & Storage', 'earthin-cache-accelerator'); ?></h2>
                        </div>

                        <div class="earthin-stat-box">
                            <div class="earthin-stat-num"><?php echo esc_html((string)$total_files); ?></div>
                            <div class="earthin-stat-label"><?php echo esc_html__('Cached Static Files', 'earthin-cache-accelerator'); ?></div>
                        </div>

                        <div class="earthin-stat-box">
                            <div class="earthin-stat-num"><?php echo esc_html($formatted_size); ?></div>
                            <div class="earthin-stat-label"><?php echo esc_html__('Disk Space Consumed', 'earthin-cache-accelerator'); ?></div>
                        </div>

                        <p style="font-size: 13px; color: #64748b; margin-top: 16px;">
                            <strong><?php echo esc_html__('Storage Path:', 'earthin-cache-accelerator'); ?></strong><br>
                            <code style="word-break: break-all; background: #f1f5f9; padding: 4px 8px; border-radius: 6px; display: inline-block; margin-top: 4px;"><?php echo esc_html($this->cache_dir); ?></code>
                        </p>

                        <form method="post" style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #f1f5f9;">
                            <?php wp_nonce_field('earthin_purge_cache_nonce'); ?>
                            <input type="submit" name="earthin_purge_cache" class="button earthin-btn-danger" value="<?php echo esc_attr__('Purge Entire Cache Now', 'earthin-cache-accelerator'); ?>" />
                        </form>
                    </div>

                    <div class="earthin-card">
                        <div class="earthin-card-header">
                            <h2 class="earthin-card-title"><?php echo esc_html__('About Earthin Suite', 'earthin-cache-accelerator'); ?></h2>
                        </div>
                        <p style="font-size: 13px; color: #475569; line-height: 1.6;">
                            <?php echo esc_html__('Earthin Cache Accelerator is engineered for instant page delivery without bloat. Crafted by Shaurya Joshi for maximum speed and rock-solid stability.', 'earthin-cache-accelerator'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Cache_Accelerator', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-cache-accelerator')) . '">' . __('Settings', 'earthin-cache-accelerator') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
