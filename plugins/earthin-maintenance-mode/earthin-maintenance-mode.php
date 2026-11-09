<?php
/**
 * Plugin Name: Earthin Maintenance Mode
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-maintenance-mode
 * Description: Clean, responsive, and SEO-friendly maintenance mode and coming soon page for WordPress with administrative bypass.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: earthin-maintenance-mode
 */

namespace Earthin\MaintenanceMode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EMM_VERSION', '0.1.0' );
define( 'EMM_PATH', plugin_dir_path( __FILE__ ) );
define( 'EMM_URL', plugin_dir_url( __FILE__ ) );
define( 'EMM_OPTION_KEY', 'earthin_maintenance_mode_settings' );

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
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_bar_menu', array( $this, 'render_admin_bar_notice' ), 999 );
		add_action( 'template_redirect', array( $this, 'handle_frontend_interception' ), 1 );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'earthin-maintenance-mode', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function get_default_settings(): array {
		return array(
			'enabled'            => 0,
			'status_code'        => 503,
			'retry_after'        => 3600,
			'page_title'         => __( 'Under Scheduled Maintenance', 'earthin-maintenance-mode' ),
			'headline'           => __( 'We will be back shortly!', 'earthin-maintenance-mode' ),
			'message'            => __( 'Our website is currently undergoing scheduled maintenance and upgrades. We apologize for the inconvenience and will be back online soon.', 'earthin-maintenance-mode' ),
			'logo_url'           => '',
			'bg_color'           => '#0f172a',
			'accent_color'       => '#10b981',
			'text_color'         => '#f8fafc',
			'bypass_capability'  => 'manage_options',
		);
	}

	public function get_settings(): array {
		$saved = get_option( EMM_OPTION_KEY, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), $this->get_default_settings() );
	}

	public function register_admin_menu(): void {
		add_options_page(
			__( 'Earthin Maintenance', 'earthin-maintenance-mode' ),
			__( 'Earthin Maintenance', 'earthin-maintenance-mode' ),
			'manage_options',
			'earthin-maintenance-mode',
			array( $this, 'render_admin_page' )
		);
	}

	public function register_settings(): void {
		register_setting(
			'emm_settings_group',
			EMM_OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => $this->get_default_settings(),
			)
		);
	}

	public function sanitize_settings( $input ): array {
		if ( ! is_array( $input ) ) {
			return $this->get_default_settings();
		}

		$output = array();
		$output['enabled'] = ! empty( $input['enabled'] ) ? 1 : 0;

		$allowed_codes = array( 503, 200 );
		$code = isset( $input['status_code'] ) ? (int) $input['status_code'] : 503;
		$output['status_code'] = in_array( $code, $allowed_codes, true ) ? $code : 503;

		$retry = isset( $input['retry_after'] ) ? (int) $input['retry_after'] : 3600;
		$output['retry_after'] = max( 300, min( 86400, $retry ) );

		$output['page_title'] = isset( $input['page_title'] ) ? sanitize_text_field( $input['page_title'] ) : '';
		$output['headline']   = isset( $input['headline'] ) ? sanitize_text_field( $input['headline'] ) : '';
		$output['message']    = isset( $input['message'] ) ? wp_kses_post( $input['message'] ) : '';

		$logo = isset( $input['logo_url'] ) ? esc_url_raw( trim( $input['logo_url'] ) ) : '';
		$output['logo_url']   = filter_var( $logo, FILTER_VALIDATE_URL ) ? $logo : '';

		$output['bg_color']     = isset( $input['bg_color'] ) ? sanitize_hex_color( $input['bg_color'] ) ?: '#0f172a' : '#0f172a';
		$output['accent_color'] = isset( $input['accent_color'] ) ? sanitize_hex_color( $input['accent_color'] ) ?: '#10b981' : '#10b981';
		$output['text_color']   = isset( $input['text_color'] ) ? sanitize_hex_color( $input['text_color'] ) ?: '#f8fafc' : '#f8fafc';

		$output['bypass_capability'] = 'manage_options';

		return $output;
	}

	public function render_admin_bar_notice( \WP_Admin_Bar $admin_bar ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = $this->get_settings();
		if ( empty( $settings['enabled'] ) ) {
			return;
		}

		$admin_bar->add_node( array(
			'id'    => 'emm-active-notice',
			'title' => '<span style="color:#f59e0b;font-weight:600;">● ' . esc_html__( 'Maintenance Mode Active', 'earthin-maintenance-mode' ) . '</span>',
			'href'  => admin_url( 'options-general.php?page=earthin-maintenance-mode' ),
			'meta'  => array(
				'title' => __( 'Earthin Maintenance Mode is currently enabled for regular visitors.', 'earthin-maintenance-mode' ),
			),
		) );
	}

	public function handle_frontend_interception(): void {
		$settings = $this->get_settings();
		$is_preview = isset( $_GET['emm_preview'] ) && '1' === $_GET['emm_preview'] && current_user_can( 'manage_options' );

		if ( empty( $settings['enabled'] ) && ! $is_preview ) {
			return;
		}

		// Never lock out administrative, recovery, or core login requests.
		if ( is_admin() || $this->is_login_page() || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) ) {
			return;
		}

		// Check capability bypass for logged-in authorized users.
		$bypass_cap = ! empty( $settings['bypass_capability'] ) ? $settings['bypass_capability'] : 'manage_options';
		if ( ! $is_preview && current_user_can( $bypass_cap ) ) {
			return;
		}

		$this->render_maintenance_template( $settings, $is_preview );
		exit;
	}

	private function is_login_page(): bool {
		$script_name = isset( $_SERVER['SCRIPT_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ) : '';
		if ( false !== strpos( $script_name, 'wp-login.php' ) || false !== strpos( $script_name, 'wp-register.php' ) ) {
			return true;
		}
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		return (bool) preg_match( '/(\/wp-login\.php|\/wp-register\.php|\/wp-activate\.php|\/wp-signup\.php)/i', $uri );
	}

	private function render_maintenance_template( array $settings, bool $is_preview ): void {
		if ( $is_preview ) {
			status_header( 200 );
		} else {
			$status_code = isset( $settings['status_code'] ) ? (int) $settings['status_code'] : 503;
			status_header( $status_code );
			if ( 503 === $status_code ) {
				$retry = isset( $settings['retry_after'] ) ? (int) $settings['retry_after'] : 3600;
				header( 'Retry-After: ' . $retry );
			}
		}

		header( 'Content-Type: text/html; charset=utf-8' );
		header( 'X-Robots-Tag: noindex, nofollow, nosnippet' );

		$title    = ! empty( $settings['page_title'] ) ? $settings['page_title'] : __( 'Scheduled Maintenance', 'earthin-maintenance-mode' );
		$headline = ! empty( $settings['headline'] ) ? $settings['headline'] : __( 'Maintenance Mode', 'earthin-maintenance-mode' );
		$message  = ! empty( $settings['message'] ) ? $settings['message'] : '';
		$bg_color = ! empty( $settings['bg_color'] ) ? $settings['bg_color'] : '#0f172a';
		$accent   = ! empty( $settings['accent_color'] ) ? $settings['accent_color'] : '#10b981';
		$text     = ! empty( $settings['text_color'] ) ? $settings['text_color'] : '#f8fafc';
		$logo     = ! empty( $settings['logo_url'] ) ? $settings['logo_url'] : '';

		?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex,nofollow,nosnippet">
	<title><?php echo esc_html( $title ); ?></title>
	<style>
		:root {
			--bg: <?php echo esc_attr( $bg_color ); ?>;
			--accent: <?php echo esc_attr( $accent ); ?>;
			--text: <?php echo esc_attr( $text ); ?>;
		}
		* { box-sizing: border-box; margin: 0; padding: 0; }
		body {
			background-color: var(--bg);
			color: var(--text);
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 24px;
			line-height: 1.6;
		}
		.emm-card {
			max-width: 620px;
			width: 100%;
			background: rgba(255, 255, 255, 0.04);
			border: 1px solid rgba(255, 255, 255, 0.1);
			border-radius: 16px;
			padding: 48px 36px;
			text-align: center;
			box-shadow: 0 20px 40px rgba(0,0,0,0.4);
		}
		.emm-badge {
			display: inline-block;
			padding: 4px 14px;
			font-size: 12px;
			font-weight: 600;
			text-transform: uppercase;
			letter-spacing: 0.05em;
			color: var(--accent);
			background: rgba(16, 185, 129, 0.12);
			border: 1px solid var(--accent);
			border-radius: 9999px;
			margin-bottom: 24px;
		}
		.emm-logo {
			max-height: 80px;
			max-width: 240px;
			margin: 0 auto 24px auto;
			display: block;
			object-fit: contain;
		}
		h1 {
			font-size: 32px;
			font-weight: 700;
			letter-spacing: -0.02em;
			margin-bottom: 16px;
		}
		.emm-message {
			font-size: 17px;
			opacity: 0.9;
			margin-bottom: 32px;
		}
		.emm-footer {
			margin-top: 36px;
			padding-top: 24px;
			border-top: 1px solid rgba(255, 255, 255, 0.08);
			font-size: 13px;
			opacity: 0.6;
		}
		.emm-footer a {
			color: inherit;
			text-decoration: underline;
		}
		.emm-preview-banner {
			position: fixed;
			top: 0;
			left: 0;
			right: 0;
			background: #f59e0b;
			color: #111827;
			padding: 8px 16px;
			font-size: 13px;
			font-weight: 600;
			text-align: center;
			box-shadow: 0 2px 8px rgba(0,0,0,0.2);
		}
	</style>
</head>
<body>
	<?php if ( $is_preview ) : ?>
		<div class="emm-preview-banner">
			<?php esc_html_e( 'Preview Mode: This maintenance page is only visible to administrators.', 'earthin-maintenance-mode' ); ?>
		</div>
	<?php endif; ?>

	<div class="emm-card">
		<?php if ( ! empty( $logo ) ) : ?>
			<img src="<?php echo esc_url( $logo ); ?>" alt="<?php bloginfo( 'name' ); ?>" class="emm-logo">
		<?php endif; ?>

		<div class="emm-badge"><?php esc_html_e( 'Maintenance In Progress', 'earthin-maintenance-mode' ); ?></div>

		<h1><?php echo esc_html( $headline ); ?></h1>

		<div class="emm-message">
			<?php echo wp_kses_post( $message ); ?>
		</div>

		<div class="emm-footer">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> &bull; <?php esc_html_e( 'Powered by Earthin', 'earthin-maintenance-mode' ); ?></p>
		</div>
	</div>
</body>
</html>
		<?php
	}

	public function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'earthin-maintenance-mode' ) );
		}

		$settings = $this->get_settings();
		$preview_url = add_query_arg( 'emm_preview', '1', home_url( '/' ) );
		$is_active = ! empty( $settings['enabled'] );
		?>
		<div class="wrap" style="max-width:1100px; margin:20px auto 40px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,sans-serif;">
			<!-- Header Banner -->
			<div style="display:flex; align-items:center; justify-content:space-between; background:linear-gradient(135deg, #059669 0%, #047857 100%); padding:22px 28px; border-radius:12px; color:#fff; box-shadow:0 4px 18px rgba(5,150,105,0.22); margin-bottom:24px;">
				<div style="display:flex; align-items:center; gap:16px;">
					<div style="background:rgba(255,255,255,0.2); border-radius:12px; width:50px; height:50px; display:flex; align-items:center; justify-content:center; font-size:26px;">🚧</div>
					<div>
						<h1 style="color:#fff; margin:0; font-size:22px; font-weight:700; line-height:1.2;"><?php esc_html_e( 'Earthin Maintenance Mode', 'earthin-maintenance-mode' ); ?></h1>
						<p style="margin:4px 0 0; color:#d1fae5; font-size:13px;"><?php esc_html_e( 'Clean, responsive splash screen with 503 SEO preserving headers and admin bypass', 'earthin-maintenance-mode' ); ?></p>
					</div>
				</div>
				<div style="display:flex; align-items:center; gap:10px;">
					<span style="background:rgba(255,255,255,0.18); padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600;">v<?php echo esc_html( EMM_VERSION ); ?></span>
					<a href="<?php echo esc_url( $preview_url ); ?>" target="_blank" class="button" style="background:#fff; color:#047857; border:none; padding:6px 14px; border-radius:6px; font-size:12px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:5px; height:auto; line-height:1.4;">
						<?php esc_html_e( 'Preview Splash Screen ↗', 'earthin-maintenance-mode' ); ?>
					</a>
				</div>
			</div>

			<!-- Quick Stat Cards -->
			<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px; margin-bottom:24px;">
				<div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e( 'Mode Status', 'earthin-maintenance-mode' ); ?></div>
					<div style="font-size:20px; font-weight:700; margin-top:4px; color:<?php echo $is_active ? '#f59e0b' : '#059669'; ?>;">
						<?php echo $is_active ? '● Maintenance Active' : '○ Live Mode (Off)'; ?>
					</div>
					<div style="font-size:12px; color:#94a3b8; margin-top:2px;">
						<?php echo $is_active ? esc_html__( 'Visitors see splash screen', 'earthin-maintenance-mode' ) : esc_html__( 'Site open to all visitors', 'earthin-maintenance-mode' ); ?>
					</div>
				</div>

				<div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e( 'SEO Protocol', 'earthin-maintenance-mode' ); ?></div>
					<div style="font-size:20px; font-weight:700; color:#0f172a; margin-top:4px;">
						HTTP <?php echo esc_html( $settings['status_code'] ); ?>
					</div>
					<div style="font-size:12px; color:#10b981; margin-top:2px; font-weight:500;">
						<?php echo 503 === (int) $settings['status_code'] ? '✓ Safe for Google & Rankings' : 'Preview 200 OK Mode'; ?>
					</div>
				</div>

				<div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e( 'Admin Bypass', 'earthin-maintenance-mode' ); ?></div>
					<div style="font-size:20px; font-weight:700; color:#059669; margin-top:4px;">✓ Automatic</div>
					<div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e( 'Logged-in admins see normal site', 'earthin-maintenance-mode' ); ?></div>
				</div>
			</div>

			<form method="post" action="options.php">
				<?php settings_fields( 'emm_settings_group' ); ?>

				<div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">
					<!-- Main Form Columns -->
					<div>
						<!-- Mode & SEO Card -->
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
							<h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
								<span>⚙️</span> <?php esc_html_e( 'Status & SEO Configuration', 'earthin-maintenance-mode' ); ?>
							</h2>

							<label style="display:flex; align-items:flex-start; gap:12px; padding:16px; border:1px solid <?php echo $is_active ? '#f59e0b' : '#cbd5e1'; ?>; background:<?php echo $is_active ? '#fffbeb' : '#f8fafc'; ?>; border-radius:8px; cursor:pointer; margin-bottom:16px;">
								<input type="checkbox" name="<?php echo esc_attr( EMM_OPTION_KEY ); ?>[enabled]" value="1" <?php checked( 1, $settings['enabled'] ); ?> style="margin-top:3px;">
								<div>
									<strong style="font-size:14px; color:#0f172a; display:block;"><?php esc_html_e( 'Enable Maintenance Mode', 'earthin-maintenance-mode' ); ?></strong>
									<span style="font-size:12px; color:#64748b; line-height:1.4; display:block; margin-top:2px;">
										<?php esc_html_e( 'Activate maintenance mode for all visitors. Administrators will always bypass this screen and see the regular live website.', 'earthin-maintenance-mode' ); ?>
									</span>
								</div>
							</label>

							<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
								<div>
									<label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e( 'HTTP Status Code', 'earthin-maintenance-mode' ); ?></label>
									<select name="<?php echo esc_attr( EMM_OPTION_KEY ); ?>[status_code]" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;">
										<option value="503" <?php selected( 503, $settings['status_code'] ); ?>><?php esc_html_e( '503 Service Unavailable (SEO Safe)', 'earthin-maintenance-mode' ); ?></option>
										<option value="200" <?php selected( 200, $settings['status_code'] ); ?>><?php esc_html_e( '200 OK (Preview / Coming Soon)', 'earthin-maintenance-mode' ); ?></option>
									</select>
								</div>
								<div>
									<label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e( 'Retry-After (Seconds)', 'earthin-maintenance-mode' ); ?></label>
									<input type="number" name="<?php echo esc_attr( EMM_OPTION_KEY ); ?>[retry_after]" value="<?php echo esc_attr( $settings['retry_after'] ); ?>" min="300" max="86400" step="300" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;">
								</div>
							</div>
						</div>

						<!-- Content & Copy Card -->
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
							<h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
								<span>📝</span> <?php esc_html_e( 'Screen Copy & Branding', 'earthin-maintenance-mode' ); ?>
							</h2>

							<div style="margin-bottom:16px;">
								<label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e( 'Browser Tab Title', 'earthin-maintenance-mode' ); ?></label>
								<input type="text" class="large-text" name="<?php echo esc_attr( EMM_OPTION_KEY ); ?>[page_title]" value="<?php echo esc_attr( $settings['page_title'] ); ?>" style="padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;">
							</div>

							<div style="margin-bottom:16px;">
								<label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e( 'Main Headline', 'earthin-maintenance-mode' ); ?></label>
								<input type="text" class="large-text" name="<?php echo esc_attr( EMM_OPTION_KEY ); ?>[headline]" value="<?php echo esc_attr( $settings['headline'] ); ?>" style="padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;">
							</div>

							<div style="margin-bottom:16px;">
								<label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e( 'Maintenance Message', 'earthin-maintenance-mode' ); ?></label>
								<?php
								wp_editor(
									$settings['message'],
									'emmmessage',
									array(
										'textarea_name' => esc_attr( EMM_OPTION_KEY ) . '[message]',
										'textarea_rows' => 4,
										'media_buttons' => false,
										'teeny'         => true,
									)
								);
								?>
							</div>

							<div>
								<label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e( 'Custom Logo URL (Optional)', 'earthin-maintenance-mode' ); ?></label>
								<input type="url" class="large-text" name="<?php echo esc_attr( EMM_OPTION_KEY ); ?>[logo_url]" value="<?php echo esc_attr( $settings['logo_url'] ); ?>" placeholder="https://example.com/logo.png" style="padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;">
							</div>
						</div>

						<!-- Design Colors Card -->
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
							<h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
								<span>🎨</span> <?php esc_html_e( 'Color Palette', 'earthin-maintenance-mode' ); ?>
							</h2>

							<div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
								<div>
									<label style="font-size:12px; font-weight:600; color:#334155; display:block; margin-bottom:4px;"><?php esc_html_e( 'Background', 'earthin-maintenance-mode' ); ?></label>
									<input type="text" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid #cbd5e1; font-family:monospace; font-size:13px;" name="<?php echo esc_attr( EMM_OPTION_KEY ); ?>[bg_color]" value="<?php echo esc_attr( $settings['bg_color'] ); ?>" placeholder="#0f172a">
								</div>
								<div>
									<label style="font-size:12px; font-weight:600; color:#334155; display:block; margin-bottom:4px;"><?php esc_html_e( 'Accent Badge', 'earthin-maintenance-mode' ); ?></label>
									<input type="text" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid #cbd5e1; font-family:monospace; font-size:13px;" name="<?php echo esc_attr( EMM_OPTION_KEY ); ?>[accent_color]" value="<?php echo esc_attr( $settings['accent_color'] ); ?>" placeholder="#10b981">
								</div>
								<div>
									<label style="font-size:12px; font-weight:600; color:#334155; display:block; margin-bottom:4px;"><?php esc_html_e( 'Text Color', 'earthin-maintenance-mode' ); ?></label>
									<input type="text" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid #cbd5e1; font-family:monospace; font-size:13px;" name="<?php echo esc_attr( EMM_OPTION_KEY ); ?>[text_color]" value="<?php echo esc_attr( $settings['text_color'] ); ?>" placeholder="#f8fafc">
								</div>
							</div>
						</div>

						<div style="display:flex; gap:12px; align-items:center;">
							<button type="submit" class="button button-primary" style="background:#059669; border-color:#047857; color:#fff; font-weight:600; padding:8px 24px; height:auto; border-radius:6px; font-size:14px; box-shadow:0 2px 4px rgba(5,150,105,0.25);">
								<?php esc_html_e( 'Save Maintenance Settings', 'earthin-maintenance-mode' ); ?>
							</button>
							<a href="<?php echo esc_url( $preview_url ); ?>" target="_blank" class="button" style="border:1px solid #cbd5e1; padding:8px 16px; border-radius:6px; font-size:13px; font-weight:600; height:auto; line-height:1.4;">
								<?php esc_html_e( 'Preview Splash Screen ↗', 'earthin-maintenance-mode' ); ?>
							</a>
						</div>
					</div>

					<!-- Sidebar Info -->
					<div>
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:20px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:20px;">
							<h3 style="font-size:14px; font-weight:700; color:#0f172a; margin:0 0 10px;"><?php esc_html_e( 'SEO Best Practices', 'earthin-maintenance-mode' ); ?></h3>
							<p style="font-size:12px; color:#64748b; line-height:1.5; margin:0 0 8px;">
								<?php esc_html_e( 'Never return a 200 OK code during technical downtime! Earthin automatically sends a 503 Service Unavailable header with Retry-After directives so Google never de-indexes your pages.', 'earthin-maintenance-mode' ); ?>
							</p>
						</div>

						<div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:18px;">
							<div style="font-size:13px; font-weight:700; color:#166534; margin-bottom:8px;">💡 <?php esc_html_e( 'Admin Bar Status Pill', 'earthin-maintenance-mode' ); ?></div>
							<p style="font-size:12px; color:#15803d; line-height:1.5; margin:0;">
								<?php esc_html_e( 'When maintenance mode is active, an amber warning pill appears in your top WordPress admin bar so you never accidentally leave maintenance mode on.', 'earthin-maintenance-mode' ); ?>
							</p>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
	}
}

Plugin::instance();

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-maintenance-mode')) . '">' . __('Settings', 'earthin-maintenance-mode') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
