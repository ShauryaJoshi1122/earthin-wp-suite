<?php
/**
 * Plugin Name: Earthin Site Health Monitor
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-site-health-monitor
 * Description: Real-time diagnostics, environment verification, security configuration audits, and actionable health metrics for WordPress.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: earthin-site-health-monitor
 */

namespace Earthin\SiteHealthMonitor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ESHM_VERSION', '0.1.0' );
define( 'ESHM_PATH', plugin_dir_path( __FILE__ ) );
define( 'ESHM_URL', plugin_dir_url( __FILE__ ) );

final class Plugin {

	private static ?Plugin $instance = null;

	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'earthin-site-health-monitor', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function register_admin_menu(): void {
		add_management_page(
			__( 'Earthin Site Health', 'earthin-site-health-monitor' ),
			__( 'Earthin Site Health', 'earthin-site-health-monitor' ),
			'manage_options',
			'earthin-site-health-monitor',
			array( $this, 'render_admin_page' )
		);
	}

	public function run_diagnostics(): array {
		global $wpdb;

		$results = array();

		// 1. PHP Version
		$php_version = PHP_VERSION;
		if ( version_compare( $php_version, '8.1', '>=' ) ) {
			$results[] = array(
				'badge'       => 'good',
				'title'       => __( 'PHP Version', 'earthin-site-health-monitor' ),
				'value'       => $php_version,
				'description' => __( 'Your server is running a modern, supported PHP version.', 'earthin-site-health-monitor' ),
				'action'      => '',
			);
		} elseif ( version_compare( $php_version, '8.0', '>=' ) ) {
			$results[] = array(
				'badge'       => 'warning',
				'title'       => __( 'PHP Version', 'earthin-site-health-monitor' ),
				'value'       => $php_version,
				'description' => __( 'PHP 8.0 has reached official End-of-Life (EOL). Upgrade to PHP 8.1 or higher for performance and security patches.', 'earthin-site-health-monitor' ),
				'action'      => __( 'Contact your hosting provider to switch to PHP 8.1 or 8.2.', 'earthin-site-health-monitor' ),
			);
		} else {
			$results[] = array(
				'badge'       => 'critical',
				'title'       => __( 'PHP Version', 'earthin-site-health-monitor' ),
				'value'       => $php_version,
				'description' => __( 'Your PHP version is severely outdated, unsupported, and vulnerable.', 'earthin-site-health-monitor' ),
				'action'      => __( 'Immediate upgrade to PHP 8.1+ required.', 'earthin-site-health-monitor' ),
			);
		}

		// 2. WordPress Version
		$wp_version = get_bloginfo( 'version' );
		if ( version_compare( $wp_version, '6.4', '>=' ) ) {
			$results[] = array(
				'badge'       => 'good',
				'title'       => __( 'WordPress Version', 'earthin-site-health-monitor' ),
				'value'       => $wp_version,
				'description' => __( 'Your WordPress installation meets Earthin requirements.', 'earthin-site-health-monitor' ),
				'action'      => '',
			);
		} else {
			$results[] = array(
				'badge'       => 'warning',
				'title'       => __( 'WordPress Version', 'earthin-site-health-monitor' ),
				'value'       => $wp_version,
				'description' => __( 'WordPress core is below version 6.4. Some modern features may be degraded.', 'earthin-site-health-monitor' ),
				'action'      => __( 'Update WordPress to the latest version.', 'earthin-site-health-monitor' ),
			);
		}

		// 3. SSL / HTTPS
		$is_ssl = is_ssl();
		if ( $is_ssl ) {
			$results[] = array(
				'badge'       => 'good',
				'title'       => __( 'SSL / HTTPS Connection', 'earthin-site-health-monitor' ),
				'value'       => __( 'Enabled (Secure)', 'earthin-site-health-monitor' ),
				'description' => __( 'Your website serves content encrypted over HTTPS.', 'earthin-site-health-monitor' ),
				'action'      => '',
			);
		} else {
			$results[] = array(
				'badge'       => 'critical',
				'title'       => __( 'SSL / HTTPS Connection', 'earthin-site-health-monitor' ),
				'value'       => __( 'Disabled (Insecure HTTP)', 'earthin-site-health-monitor' ),
				'description' => __( 'Your website is not using SSL. Passwords and personal data are sent in plain text.', 'earthin-site-health-monitor' ),
				'action'      => __( 'Install an SSL certificate and redirect traffic to HTTPS.', 'earthin-site-health-monitor' ),
			);
		}

		// 4. Debug Display in Production
		$debug_display = defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;
		if ( ! $debug_display ) {
			$results[] = array(
				'badge'       => 'good',
				'title'       => __( 'Debug Output Suppression', 'earthin-site-health-monitor' ),
				'value'       => __( 'Suppressed (Safe)', 'earthin-site-health-monitor' ),
				'description' => __( 'Errors and PHP warnings are not displayed to frontend visitors.', 'earthin-site-health-monitor' ),
				'action'      => '',
			);
		} else {
			$results[] = array(
				'badge'       => 'warning',
				'title'       => __( 'Debug Output Suppression', 'earthin-site-health-monitor' ),
				'value'       => __( 'WP_DEBUG_DISPLAY is Enabled', 'earthin-site-health-monitor' ),
				'description' => __( 'PHP errors and internal paths may be exposed to public visitors.', 'earthin-site-health-monitor' ),
				'action'      => __( 'Add define("WP_DEBUG_DISPLAY", false); to your wp-config.php.', 'earthin-site-health-monitor' ),
			);
		}

		// 5. Memory Limit
		$memory_limit = ini_get( 'memory_limit' );
		$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );
		if ( $memory_bytes >= 268435456 ) { // 256M
			$results[] = array(
				'badge'       => 'good',
				'title'       => __( 'PHP Memory Limit', 'earthin-site-health-monitor' ),
				'value'       => $memory_limit,
				'description' => __( 'Ample memory available for processing high-traffic operations.', 'earthin-site-health-monitor' ),
				'action'      => '',
			);
		} elseif ( $memory_bytes >= 134217728 ) { // 128M
			$results[] = array(
				'badge'       => 'good',
				'title'       => __( 'PHP Memory Limit', 'earthin-site-health-monitor' ),
				'value'       => $memory_limit,
				'description' => __( 'Adequate memory for standard WordPress operations.', 'earthin-site-health-monitor' ),
				'action'      => '',
			);
		} else {
			$results[] = array(
				'badge'       => 'warning',
				'title'       => __( 'PHP Memory Limit', 'earthin-site-health-monitor' ),
				'value'       => $memory_limit,
				'description' => __( 'Memory limit is below 128M. Complex plugins or imports may trigger memory exhaustion.', 'earthin-site-health-monitor' ),
				'action'      => __( 'Increase WP_MEMORY_LIMIT to at least 128M in wp-config.php.', 'earthin-site-health-monitor' ),
			);
		}

		// 6. Uploads Directory Writable
		$upload_dir = wp_upload_dir();
		$uploads_writable = wp_is_writable( $upload_dir['basedir'] );
		if ( $uploads_writable ) {
			$results[] = array(
				'badge'       => 'good',
				'title'       => __( 'Media Uploads Directory', 'earthin-site-health-monitor' ),
				'value'       => __( 'Writable', 'earthin-site-health-monitor' ),
				'description' => __( 'WordPress can create folders and store uploaded media files.', 'earthin-site-health-monitor' ),
				'action'      => '',
			);
		} else {
			$results[] = array(
				'badge'       => 'critical',
				'title'       => __( 'Media Uploads Directory', 'earthin-site-health-monitor' ),
				'value'       => __( 'Not Writable', 'earthin-site-health-monitor' ),
				'description' => __( 'WordPress cannot save media uploads to wp-content/uploads.', 'earthin-site-health-monitor' ),
				'action'      => __( 'Check directory file permissions (typically 755 for folders).', 'earthin-site-health-monitor' ),
			);
		}

		// 7. Database Table Prefix
		if ( 'wp_' !== $wpdb->prefix ) {
			$results[] = array(
				'badge'       => 'good',
				'title'       => __( 'Database Table Prefix', 'earthin-site-health-monitor' ),
				'value'       => __( 'Custom Prefix', 'earthin-site-health-monitor' ),
				'description' => __( 'Using a non-default prefix helps mitigate automated SQL injection targeting default table names.', 'earthin-site-health-monitor' ),
				'action'      => '',
			);
		} else {
			$results[] = array(
				'badge'       => 'warning',
				'title'       => __( 'Database Table Prefix', 'earthin-site-health-monitor' ),
				'value'       => 'wp_ (Default)',
				'description' => __( 'Using the standard default table prefix makes automated blind SQL scans easier for attackers.', 'earthin-site-health-monitor' ),
				'action'      => __( 'Consider changing table prefix during maintenance if feasible.', 'earthin-site-health-monitor' ),
			);
		}

		// 8. Inactive Plugins Count
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );
		$inactive_count = max( 0, count( $all_plugins ) - count( $active_plugins ) );

		if ( $inactive_count <= 2 ) {
			$results[] = array(
				'badge'       => 'good',
				'title'       => __( 'Inactive Plugins', 'earthin-site-health-monitor' ),
				'value'       => sprintf( _n( '%s inactive plugin', '%s inactive plugins', $inactive_count, 'earthin-site-health-monitor' ), number_format_i18n( $inactive_count ) ),
				'description' => __( 'Your plugin catalog is tidy and well-maintained.', 'earthin-site-health-monitor' ),
				'action'      => '',
			);
		} else {
			$results[] = array(
				'badge'       => 'warning',
				'title'       => __( 'Inactive Plugins', 'earthin-site-health-monitor' ),
				'value'       => sprintf( _n( '%s inactive plugin', '%s inactive plugins', $inactive_count, 'earthin-site-health-monitor' ), number_format_i18n( $inactive_count ) ),
				'description' => __( 'Dormant inactive plugins consume disk space and can pose security risks if left unpatched.', 'earthin-site-health-monitor' ),
				'action'      => __( 'Delete plugins you are no longer using.', 'earthin-site-health-monitor' ),
			);
		}

		return $results;
	}

	public function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'earthin-site-health-monitor' ) );
		}

		$diagnostics = $this->run_diagnostics();
		$counts = array( 'good' => 0, 'warning' => 0, 'critical' => 0 );
		foreach ( $diagnostics as $item ) {
			if ( isset( $counts[ $item['badge'] ] ) ) {
				$counts[ $item['badge'] ]++;
			}
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Earthin Site Health Monitor', 'earthin-site-health-monitor' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Comprehensive technical environment checks and actionable recommendations for optimal WordPress security and performance.', 'earthin-site-health-monitor' ); ?>
			</p>

			<!-- Overview Cards -->
			<div style="display:flex; gap:16px; margin:24px 0; flex-wrap:wrap;">
				<div style="flex:1; min-width:200px; background:#ffffff; border:1px solid #cbd5e1; border-left:4px solid #10b981; border-radius:8px; padding:16px;">
					<div style="font-size:28px; font-weight:700; color:#10b981;"><?php echo (int) $counts['good']; ?></div>
					<div style="font-weight:600; color:#334155; margin-top:4px;"><?php esc_html_e( 'Items Passing', 'earthin-site-health-monitor' ); ?></div>
				</div>
				<div style="flex:1; min-width:200px; background:#ffffff; border:1px solid #cbd5e1; border-left:4px solid #f59e0b; border-radius:8px; padding:16px;">
					<div style="font-size:28px; font-weight:700; color:#f59e0b;"><?php echo (int) $counts['warning']; ?></div>
					<div style="font-weight:600; color:#334155; margin-top:4px;"><?php esc_html_e( 'Recommended Improvements', 'earthin-site-health-monitor' ); ?></div>
				</div>
				<div style="flex:1; min-width:200px; background:#ffffff; border:1px solid #cbd5e1; border-left:4px solid #ef4444; border-radius:8px; padding:16px;">
					<div style="font-size:28px; font-weight:700; color:#ef4444;"><?php echo (int) $counts['critical']; ?></div>
					<div style="font-weight:600; color:#334155; margin-top:4px;"><?php esc_html_e( 'Critical Alerts', 'earthin-site-health-monitor' ); ?></div>
				</div>
			</div>

			<!-- Diagnostics Table -->
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width:120px;"><?php esc_html_e( 'Status', 'earthin-site-health-monitor' ); ?></th>
						<th style="width:220px;"><?php esc_html_e( 'Diagnostic Item', 'earthin-site-health-monitor' ); ?></th>
						<th style="width:200px;"><?php esc_html_e( 'Detected Value', 'earthin-site-health-monitor' ); ?></th>
						<th><?php esc_html_e( 'Assessment & Recommended Action', 'earthin-site-health-monitor' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $diagnostics as $d ) : ?>
						<tr>
							<td>
								<?php if ( 'good' === $d['badge'] ) : ?>
									<span style="background:#dcfce7; color:#15803d; padding:3px 8px; border-radius:4px; font-weight:600; font-size:12px;">✓ <?php esc_html_e( 'Pass', 'earthin-site-health-monitor' ); ?></span>
								<?php elseif ( 'warning' === $d['badge'] ) : ?>
									<span style="background:#fef3c7; color:#b45309; padding:3px 8px; border-radius:4px; font-weight:600; font-size:12px;">! <?php esc_html_e( 'Notice', 'earthin-site-health-monitor' ); ?></span>
								<?php else : ?>
									<span style="background:#fee2e2; color:#b91c1c; padding:3px 8px; border-radius:4px; font-weight:600; font-size:12px;">✕ <?php esc_html_e( 'Critical', 'earthin-site-health-monitor' ); ?></span>
								<?php endif; ?>
							</td>
							<td><strong><?php echo esc_html( $d['title'] ); ?></strong></td>
							<td><code><?php echo esc_html( $d['value'] ); ?></code></td>
							<td>
								<div><?php echo esc_html( $d['description'] ); ?></div>
								<?php if ( ! empty( $d['action'] ) ) : ?>
									<div style="margin-top:4px; color:#475569; font-size:12px;">
										<strong><?php esc_html_e( 'Recommendation:', 'earthin-site-health-monitor' ); ?></strong> <?php echo esc_html( $d['action'] ); ?>
									</div>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<div style="margin-top:24px; padding:16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; color:#64748b;">
				<strong><?php esc_html_e( 'Security Disclaimer:', 'earthin-site-health-monitor' ); ?></strong>
				<?php esc_html_e( 'Earthin Site Health Monitor provides administrative configuration verification. A clean report indicates good operational posture but does not guarantee immunity from zero-day exploits or credential compromise.', 'earthin-site-health-monitor' ); ?>
			</div>
		</div>
		<?php
	}
}

Plugin::instance();
