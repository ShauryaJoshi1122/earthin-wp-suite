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
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Earthin Visitor Counter & Traffic Analytics', 'earthin-visitor-counter'); ?></h1>
            <p><?php esc_html_e('Self-hosted, 100% GDPR-compliant web statistics stored on your own database.', 'earthin-visitor-counter'); ?></p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin: 20px 0;">
                <div style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                    <div style="font-size: 13px; color: #64748b; font-weight: 600; text-transform: uppercase;"><?php esc_html_e('All-Time Pageviews', 'earthin-visitor-counter'); ?></div>
                    <div style="font-size: 32px; font-weight: 700; color: #0f172a; margin-top: 8px;"><?php echo number_format_i18n($total_views); ?></div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
                <div>
                    <h2><?php esc_html_e('Past 14 Days Activity', 'earthin-visitor-counter'); ?></h2>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Date', 'earthin-visitor-counter'); ?></th>
                                <th><?php esc_html_e('Unique Visitors', 'earthin-visitor-counter'); ?></th>
                                <th><?php esc_html_e('Total Pageviews', 'earthin-visitor-counter'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($daily_stats)): ?>
                                <tr><td colspan="3"><?php esc_html_e('No traffic recorded yet.', 'earthin-visitor-counter'); ?></td></tr>
                            <?php else: ?>
                                <?php foreach ($daily_stats as $day): ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($day['visit_date']); ?></strong></td>
                                        <td><?php echo number_format_i18n((int) $day['unique_visitors']); ?></td>
                                        <td><?php echo number_format_i18n((int) $day['pageviews']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div>
                    <h2><?php esc_html_e('Top Visited Content', 'earthin-visitor-counter'); ?></h2>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Page Title', 'earthin-visitor-counter'); ?></th>
                                <th><?php esc_html_e('Views', 'earthin-visitor-counter'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($top_pages)): ?>
                                <tr><td colspan="2"><?php esc_html_e('No page hits recorded yet.', 'earthin-visitor-counter'); ?></td></tr>
                            <?php else: ?>
                                <?php foreach ($top_pages as $page): ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo esc_url(get_permalink($page['post_id'])); ?>" target="_blank">
                                                <?php echo esc_html(get_the_title($page['post_id']) ?: __('(No title)', 'earthin-visitor-counter')); ?>
                                            </a>
                                        </td>
                                        <td><strong><?php echo number_format_i18n((int) $page['views']); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div style="margin-top: 24px; background: #fff; padding: 16px; border: 1px solid #ccd0d4; border-radius: 8px;">
                        <h3 style="margin-top: 0;"><?php esc_html_e('Available Shortcodes', 'earthin-visitor-counter'); ?></h3>
                        <p><code>[earthin_visitor_counter]</code> - <?php esc_html_e('Displays all-time visits counter.', 'earthin-visitor-counter'); ?></p>
                        <p><code>[earthin_page_views]</code> - <?php esc_html_e('Displays views for the current post/page.', 'earthin-visitor-counter'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

add_action('plugins_loaded', ['Earthin_Visitor_Counter', 'get_instance']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function() {
     = '<a href="' . esc_url(admin_url('admin.php?page=earthin-visitor-counter')) . '">' . __('Settings', 'earthin-visitor-counter') . '</a>';
    array_unshift(, );
    return ;
});
