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
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Earthin Cache Accelerator', 'earthin-cache-accelerator'); ?></h1>
            <p><?php echo esc_html__('High-efficiency static HTML caching engine developed by Shaurya Joshi.', 'earthin-cache-accelerator'); ?></p>
            <?php settings_errors('earthin_cache'); ?>

            <div style="display:flex;gap:20px;margin-top:20px;">
                <div style="flex:2;background:#fff;padding:24px;border:1px solid #ccd0d4;border-radius:8px;">
                    <h2><?php echo esc_html__('Cache Controls & Settings', 'earthin-cache-accelerator'); ?></h2>
                    <form method="post">
                        <?php wp_nonce_field('earthin_save_cache_settings_nonce'); ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php echo esc_html__('Enable Static Caching', 'earthin-cache-accelerator'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="enabled" value="1" <?php checked(1, $this->options['enabled']); ?> />
                                        <?php echo esc_html__('Cache visited public pages to static disk files.', 'earthin-cache-accelerator'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('Cache Lifespan (TTL in seconds)', 'earthin-cache-accelerator'); ?></th>
                                <td>
                                    <input type="number" name="cache_ttl" value="<?php echo esc_attr($this->options['cache_ttl']); ?>" min="300" step="300" class="regular-text" />
                                    <p class="description"><?php echo esc_html__('Default is 86400 (24 hours). Minimum is 300 seconds.', 'earthin-cache-accelerator'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('Auto-Purge on Post Update', 'earthin-cache-accelerator'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="purge_on_post_save" value="1" <?php checked(1, $this->options['purge_on_post_save']); ?> />
                                        <?php echo esc_html__('Automatically clear cache when posts or pages are published/updated.', 'earthin-cache-accelerator'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('Bypass for Logged-In Users', 'earthin-cache-accelerator'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="bypass_logged_in" value="1" <?php checked(1, $this->options['bypass_logged_in']); ?> />
                                        <?php echo esc_html__('Serve fresh, dynamic content for logged-in administrators and authors.', 'earthin-cache-accelerator'); ?>
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
                        <p class="submit">
                            <input type="submit" name="earthin_save_cache_settings" class="button button-primary" value="<?php echo esc_attr__('Save Configuration', 'earthin-cache-accelerator'); ?>" />
                        </p>
                    </form>
                </div>

                <div style="flex:1;background:#fff;padding:24px;border:1px solid #ccd0d4;border-radius:8px;">
                    <h2><?php echo esc_html__('Cache Health & Storage', 'earthin-cache-accelerator'); ?></h2>
                    <p><strong><?php echo esc_html__('Cached HTML Files:', 'earthin-cache-accelerator'); ?></strong> <?php echo esc_html((string)$total_files); ?></p>
                    <p><strong><?php echo esc_html__('Disk Space Consumed:', 'earthin-cache-accelerator'); ?></strong> <?php echo esc_html($formatted_size); ?></p>
                    <p><strong><?php echo esc_html__('Cache Directory:', 'earthin-cache-accelerator'); ?></strong><br><code><?php echo esc_html($this->cache_dir); ?></code></p>
                    
                    <form method="post" style="margin-top:20px;">
                        <?php wp_nonce_field('earthin_purge_cache_nonce'); ?>
                        <input type="submit" name="earthin_purge_cache" class="button button-secondary" style="color:#d63638;border-color:#d63638;" value="<?php echo esc_attr__('Purge Entire Cache Now', 'earthin-cache-accelerator'); ?>" />
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Cache_Accelerator', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function() {
     = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-cache-accelerator')) . '">' . __('Settings', 'earthin-cache-accelerator') . '</a>';
    array_unshift(, );
    return ;
});
