<?php
/**
 * Plugin Name: Earthin Visitor Counter
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-visitor-counter
 * Description: Privacy-first, self-hosted traffic counter and visitor statistics for WordPress with shortcode counters and admin reports.
 * Version: 1.0.0
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: earthin-visitor-counter
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('EARTHIN_VISITOR_COUNTER_VERSION', '1.0.0');
define('EARTHIN_VISITOR_COUNTER_PATH', plugin_dir_path(__FILE__));

class Earthin_Visitor_Counter {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        register_activation_hook(__FILE__, [$this, 'create_tables']);
        add_action('template_redirect', [$this, 'record_visit']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);
        add_shortcode('earthin_visitor_counter', [$this, 'render_total_counter_shortcode']);
        add_shortcode('earthin_page_views', [$this, 'render_page_views_shortcode']);
    }

    public function create_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'earthin_visitor_stats';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            visit_date date NOT NULL,
            visitor_hash varchar(32) NOT NULL,
            post_id bigint(20) DEFAULT 0,
            referrer varchar(255) DEFAULT '',
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY visit_date (visit_date),
            KEY visitor_hash (visitor_hash),
            KEY post_id (post_id)
        ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private function is_bot() {
        $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
        $bots = ['bot', 'crawler', 'spider', 'curl', 'wget', 'slurp', 'mediapartners', 'facebookexternalhit'];
        foreach ($bots as $bot) {
            if (strpos($ua, $bot) !== false) {
                return true;
            }
        }
        return false;
    }

    public function record_visit() {
        if (is_admin() || is_feed() || is_robots() || is_trackback()) {
            return;
        }

        // Exclude bots
        if ($this->is_bot()) {
            return;
        }

        // Exclude logged in admins if configured
        if (current_user_can('manage_options')) {
            return;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $today = current_time('Y-m-d');
        $visitor_hash = md5($ip . $today . wp_salt('auth'));
        $post_id = is_singular() ? get_the_ID() : 0;
        $referrer = wp_strip_all_tags(substr($_SERVER['HTTP_REFERER'] ?? '', 0, 255));

        // Increment total counts in options for ultra-fast counter lookups
        $total_views = (int) get_option('earthin_vc_total_views', 0) + 1;
        update_option('earthin_vc_total_views', $total_views, false);

        if ($post_id > 0) {
            $post_views = (int) get_post_meta($post_id, '_earthin_views', true) + 1;
            update_post_meta($post_id, '_earthin_views', $post_views);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'earthin_visitor_stats';

        // Check if table exists before inserting (in case activation hook didn't fire in direct upload)
        $wpdb->insert(
            $table,
            [
                'visit_date' => $today,
                'visitor_hash' => $visitor_hash,
                'post_id' => $post_id,
                'referrer' => $referrer,
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%d', '%s', '%s']
        );
    }

    public function render_total_counter_shortcode($atts) {
        $total = number_format_i18n((int) get_option('earthin_vc_total_views', 0));
        return '<span class="earthin-visitor-counter-badge">' . esc_html($total) . '</span>';
    }

    public function render_page_views_shortcode($atts) {
        $post_id = get_the_ID();
        if (!$post_id) {
            return '0';
        }
        $views = number_format_i18n((int) get_post_meta($post_id, '_earthin_views', true));
        return '<span class="earthin-page-views-badge">' . esc_html($views) . '</span>';
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Visitor Counter', 'earthin-visitor-counter'),
            __('Visitor Stats', 'earthin-visitor-counter'),
            'manage_options',
            'earthin-visitor-counter',
            [$this, 'render_stats_page'],
            'dashicons-chart-area',
            75
        );
    }

    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'earthin_visitor_summary_widget',
            __('Earthin Visitor Summary', 'earthin-visitor-counter'),
            [$this, 'render_dashboard_widget']
        );
    }

    public function render_dashboard_widget() {
        global $wpdb;
        $table = $wpdb->prefix . 'earthin_visitor_stats';
        $today = current_time('Y-m-d');

        $today_unique = 0;
        $today_hits = 0;

        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table) {
            $today_unique = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT visitor_hash) FROM {$table} WHERE visit_date = %s", $today));
            $today_hits = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE visit_date = %s", $today));
        }

        $total_views = (int) get_option('earthin_vc_total_views', 0);
        ?>
        <div style="display: flex; gap: 15px; text-align: center; margin-top: 10px;">
            <div style="flex:1; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 22px; font-weight: 700; color: #0f172a;"><?php echo number_format_i18n($today_unique); ?></div>
                <div style="font-size: 12px; color: #64748b;"><?php esc_html_e("Today's Visitors", 'earthin-visitor-counter'); ?></div>
            </div>
            <div style="flex:1; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 22px; font-weight: 700; color: #10b981;"><?php echo number_format_i18n($today_hits); ?></div>
                <div style="font-size: 12px; color: #64748b;"><?php esc_html_e("Today's Pageviews", 'earthin-visitor-counter'); ?></div>
            </div>
            <div style="flex:1; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 22px; font-weight: 700; color: #3b82f6;"><?php echo number_format_i18n($total_views); ?></div>
                <div style="font-size: 12px; color: #64748b;"><?php esc_html_e('All-Time Views', 'earthin-visitor-counter'); ?></div>
            </div>
        </div>
        <p style="margin-top: 15px; margin-bottom: 0;">
            <a href="<?php echo esc_url(admin_url('admin.php?page=earthin-visitor-counter')); ?>" class="button button-secondary"><?php esc_html_e('View Detailed Analytics', 'earthin-visitor-counter'); ?></a>
        </p>
        <?php
    }

    public function render_stats_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'earthin-visitor-counter'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'earthin_visitor_stats';
        $today = current_time('Y-m-d');

        $daily_stats = [];
        $top_pages = [];
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table) {
            $daily_stats = $wpdb->get_results(
                "SELECT visit_date, COUNT(*) as pageviews, COUNT(DISTINCT visitor_hash) as unique_visitors 
                 FROM {$table} 
                 GROUP BY visit_date 
                 ORDER BY visit_date DESC 
                 LIMIT 14",
                ARRAY_A
            );

            $top_pages = $wpdb->get_results(
                "SELECT post_id, COUNT(*) as views 
                 FROM {$table} 
                 WHERE post_id > 0 
                 GROUP BY post_id 
                 ORDER BY views DESC 
                 LIMIT 10",
                ARRAY_A
            );
        }

        $total_views = (int) get_option('earthin_vc_total_views', 0);
        $fourteen_day_visitors = 0;
        $fourteen_day_views = 0;
        foreach ($daily_stats as $d) {
            $fourteen_day_visitors += (int) $d['unique_visitors'];
            $fourteen_day_views += (int) $d['pageviews'];
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
                .earthin-grid { display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 24px; }
                @media(max-width: 960px) { .earthin-grid { grid-template-columns: 1fr; } }
                .earthin-card { background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); padding: 24px 28px; margin-bottom: 24px; }
                .earthin-card-header { padding-bottom: 14px; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
                .earthin-card-title { font-size: 16px; font-weight: 700; color: #0f172a; margin: 0; }
                .earthin-badge-pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
                .earthin-badge-success { background: #ecfdf5; color: #059669; }
                .earthin-badge-neutral { background: #f1f5f9; color: #64748b; }
                .earthin-table { width: 100%; border-collapse: collapse; }
                .earthin-table th { background: #f8fafc; padding: 12px 14px; font-size: 12px; font-weight: 700; color: #475569; text-align: left; border-bottom: 2px solid #e2e8f0; }
                .earthin-table td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #334155; }
                .earthin-table tr:hover td { background: #fafbfc; }
            </style>

            <header class="earthin-header">
                <div class="earthin-header-left">
                    <span class="earthin-logo-badge">🌱 Earthin</span>
                    <div class="earthin-title-group">
                        <h1>
                            <?php esc_html_e('Earthin Visitor Counter & Traffic Analytics', 'earthin-visitor-counter'); ?>
                            <span class="earthin-version-tag">v1.0.0</span>
                            <span class="earthin-badge-pill earthin-badge-success"><?php echo esc_html__('100% GDPR Private', 'earthin-visitor-counter'); ?></span>
                        </h1>
                        <p><?php esc_html_e('Self-hosted, cookie-free web metrics stored privately in your database by Shaurya Joshi.', 'earthin-visitor-counter'); ?></p>
                    </div>
                </div>
                <div class="earthin-header-links">
                    <a href="https://plugins.earthin.online" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Plugin Hub', 'earthin-visitor-counter'); ?> &nearr;</a>
                    <a href="https://plugins.earthin.online/docs" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Documentation', 'earthin-visitor-counter'); ?></a>
                    <a href="https://plugins.earthin.online/support" target="_blank" class="earthin-link-btn"><?php echo esc_html__('Support', 'earthin-visitor-counter'); ?></a>
                </div>
            </header>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin:24px 0;">
                <div class="earthin-card" style="padding:18px 24px; margin-bottom:0;">
                    <div style="font-size:28px; font-weight:800; color:#059669;"><?php echo number_format_i18n($total_views); ?></div>
                    <div style="font-size:12px; font-weight:700; text-transform:uppercase; color:#64748b; margin-top:4px;"><?php esc_html_e('All-Time Pageviews', 'earthin-visitor-counter'); ?></div>
                </div>
                <div class="earthin-card" style="padding:18px 24px; margin-bottom:0;">
                    <div style="font-size:28px; font-weight:800; color:#0284c7;"><?php echo number_format_i18n($fourteen_day_visitors); ?></div>
                    <div style="font-size:12px; font-weight:700; text-transform:uppercase; color:#64748b; margin-top:4px;"><?php esc_html_e('14-Day Unique Visitors', 'earthin-visitor-counter'); ?></div>
                </div>
                <div class="earthin-card" style="padding:18px 24px; margin-bottom:0;">
                    <div style="font-size:28px; font-weight:800; color:#6366f1;"><?php echo number_format_i18n($fourteen_day_views); ?></div>
                    <div style="font-size:12px; font-weight:700; text-transform:uppercase; color:#64748b; margin-top:4px;"><?php esc_html_e('14-Day Total Pageviews', 'earthin-visitor-counter'); ?></div>
                </div>
            </div>

            <div class="earthin-grid">
                <div class="earthin-card" style="padding:0; overflow:hidden;">
                    <div style="padding:20px 24px; border-bottom:1px solid #f1f5f9;">
                        <h2 class="earthin-card-title"><?php esc_html_e('Past 14 Days Activity Trend', 'earthin-visitor-counter'); ?></h2>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="earthin-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Date', 'earthin-visitor-counter'); ?></th>
                                    <th style="width:140px;"><?php esc_html_e('Unique Visitors', 'earthin-visitor-counter'); ?></th>
                                    <th style="width:140px;"><?php esc_html_e('Total Pageviews', 'earthin-visitor-counter'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($daily_stats)): ?>
                                    <tr><td colspan="3" style="text-align:center; padding:32px; color:#64748b;"><?php esc_html_e('No traffic recorded yet. Visits will populate in real time.', 'earthin-visitor-counter'); ?></td></tr>
                                <?php else: ?>
                                    <?php foreach ($daily_stats as $day): ?>
                                        <tr>
                                            <td><span class="earthin-badge-pill earthin-badge-neutral"><?php echo esc_html($day['visit_date']); ?></span></td>
                                            <td><strong><?php echo number_format_i18n((int) $day['unique_visitors']); ?></strong></td>
                                            <td><span style="color:#059669; font-weight:700;"><?php echo number_format_i18n((int) $day['pageviews']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <div class="earthin-card" style="padding:0; overflow:hidden; margin-bottom:24px;">
                        <div style="padding:20px 24px; border-bottom:1px solid #f1f5f9;">
                            <h2 class="earthin-card-title"><?php esc_html_e('Top Visited Content', 'earthin-visitor-counter'); ?></h2>
                        </div>
                        <div style="overflow-x:auto;">
                            <table class="earthin-table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Page Title', 'earthin-visitor-counter'); ?></th>
                                        <th style="width:90px; text-align:right;"><?php esc_html_e('Views', 'earthin-visitor-counter'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($top_pages)): ?>
                                        <tr><td colspan="2" style="text-align:center; padding:24px; color:#64748b;"><?php esc_html_e('No page hits recorded yet.', 'earthin-visitor-counter'); ?></td></tr>
                                    <?php else: ?>
                                        <?php foreach ($top_pages as $page): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?php echo esc_url(get_permalink($page['post_id'])); ?>" target="_blank" style="color:#0f172a; text-decoration:none; font-weight:600;">
                                                        <?php echo esc_html(get_the_title($page['post_id']) ?: __('(No title)', 'earthin-visitor-counter')); ?> &nearr;
                                                    </a>
                                                </td>
                                                <td style="text-align:right;"><span class="earthin-badge-pill earthin-badge-success"><?php echo number_format_i18n((int) $page['views']); ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="earthin-card">
                        <div class="earthin-card-header">
                            <h3 class="earthin-card-title"><?php esc_html_e('Available Shortcodes', 'earthin-visitor-counter'); ?></h3>
                        </div>
                        <div style="margin-bottom:12px;">
                            <code style="background:#ecfdf5; color:#047857; font-weight:700; padding:4px 8px; border-radius:6px; font-size:12px;">[earthin_visitor_counter]</code>
                            <p style="font-size:12px; color:#64748b; margin:4px 0 0;"><?php esc_html_e('Displays total all-time site visits counter.', 'earthin-visitor-counter'); ?></p>
                        </div>
                        <div>
                            <code style="background:#ecfdf5; color:#047857; font-weight:700; padding:4px 8px; border-radius:6px; font-size:12px;">[earthin_page_views]</code>
                            <p style="font-size:12px; color:#64748b; margin:4px 0 0;"><?php esc_html_e('Displays view counter for the current single post/page.', 'earthin-visitor-counter'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Visitor_Counter', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=earthin-visitor-counter')) . '">' . __('Settings', 'earthin-visitor-counter') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
