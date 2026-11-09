<?php
/**
 * Plugin Name: Earthin White Label
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-white-label
 * Description: Clean, reversible branding controls to customize the WordPress login screen, dashboard widgets, and administrative footer.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: earthin-white-label
 */

namespace Earthin\WhiteLabel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EWL_VERSION', '0.1.0' );
define( 'EWL_PATH', plugin_dir_path( __FILE__ ) );
define( 'EWL_URL', plugin_dir_url( __FILE__ ) );
define( 'EWL_OPTION_KEY', 'earthin_white_label_settings' );

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

		// Login branding hooks
		add_action( 'login_enqueue_scripts', array( $this, 'customize_login_screen' ) );
		add_filter( 'login_headerurl', array( $this, 'customize_login_url' ) );
		add_filter( 'login_headertext', array( $this, 'customize_login_title' ) );

		// Admin dashboard hooks
		add_action( 'wp_dashboard_setup', array( $this, 'customize_dashboard_widgets' ), 999 );
		add_filter( 'admin_footer_text', array( $this, 'customize_footer_text' ) );
		add_filter( 'update_footer', array( $this, 'customize_version_footer' ), 999 );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'earthin-white-label', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function get_default_settings(): array {
		return array(
			'login_logo_url'     => '',
			'login_logo_link'    => '',
			'login_bg_color'     => '#f1f5f9',
			'hide_wp_news'       => 1,
			'hide_quick_draft'   => 0,
			'hide_activity'      => 0,
			'hide_at_a_glance'   => 0,
			'custom_footer_text' => sprintf( __( 'Powered by %s', 'earthin-white-label' ), get_bloginfo( 'name' ) ),
			'hide_core_version'  => 1,
		);
	}

	public function get_settings(): array {
		$saved = get_option( EWL_OPTION_KEY, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), $this->get_default_settings() );
	}

	public function customize_login_screen(): void {
		$settings = $this->get_settings();
		$logo     = ! empty( $settings['login_logo_url'] ) ? esc_url( $settings['login_logo_url'] ) : '';
		$bg_color = ! empty( $settings['login_bg_color'] ) ? sanitize_hex_color( $settings['login_bg_color'] ) ?: '#f1f5f9' : '#f1f5f9';

		echo '<style type="text/css">';
		echo 'body.login { background-color: ' . esc_attr( $bg_color ) . ' !important; }';
		if ( ! empty( $logo ) ) {
			echo '#login h1 a, .login h1 a {';
			echo 'background-image: url(' . esc_url( $logo ) . ') !important;';
			echo 'background-size: contain !important;';
			echo 'background-position: center center !important;';
			echo 'width: 240px !important;';
			echo 'height: 80px !important;';
			echo '}';
		}
		echo '</style>';
	}

	public function customize_login_url( string $url ): string {
		$settings = $this->get_settings();
		if ( ! empty( $settings['login_logo_link'] ) ) {
			return esc_url( $settings['login_logo_link'] );
		}
		return home_url( '/' );
	}

	public function customize_login_title( string $title ): string {
		return get_bloginfo( 'name' );
	}

	public function customize_dashboard_widgets(): void {
		$settings = $this->get_settings();

		if ( ! empty( $settings['hide_wp_news'] ) ) {
			remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
		}
		if ( ! empty( $settings['hide_quick_draft'] ) ) {
			remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		}
		if ( ! empty( $settings['hide_activity'] ) ) {
			remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
		}
		if ( ! empty( $settings['hide_at_a_glance'] ) ) {
			remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
		}
	}

	public function customize_footer_text( string $default_text ): string {
		$settings = $this->get_settings();
		if ( ! empty( $settings['custom_footer_text'] ) ) {
			return wp_kses_post( $settings['custom_footer_text'] );
		}
		return $default_text;
	}

	public function customize_version_footer( string $default_version ): string {
		$settings = $this->get_settings();
		if ( ! empty( $settings['hide_core_version'] ) ) {
			return '';
		}
		return $default_version;
	}

	public function register_admin_menu(): void {
		add_options_page(
			__( 'Earthin White Label', 'earthin-white-label' ),
			__( 'White Label', 'earthin-white-label' ),
			'manage_options',
			'earthin-white-label',
			array( $this, 'render_admin_page' )
		);
	}

	public function register_settings(): void {
		register_setting(
			'ewl_settings_group',
			EWL_OPTION_KEY,
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
		$logo = isset( $input['login_logo_url'] ) ? esc_url_raw( trim( $input['login_logo_url'] ) ) : '';
		$output['login_logo_url']  = filter_var( $logo, FILTER_VALIDATE_URL ) ? $logo : '';

		$link = isset( $input['login_logo_link'] ) ? esc_url_raw( trim( $input['login_logo_link'] ) ) : '';
		$output['login_logo_link'] = filter_var( $link, FILTER_VALIDATE_URL ) ? $link : '';

		$output['login_bg_color']  = isset( $input['login_bg_color'] ) ? sanitize_hex_color( $input['login_bg_color'] ) ?: '#f1f5f9' : '#f1f5f9';

		$output['hide_wp_news']     = ! empty( $input['hide_wp_news'] ) ? 1 : 0;
		$output['hide_quick_draft'] = ! empty( $input['hide_quick_draft'] ) ? 1 : 0;
		$output['hide_activity']    = ! empty( $input['hide_activity'] ) ? 1 : 0;
		$output['hide_at_a_glance'] = ! empty( $input['hide_at_a_glance'] ) ? 1 : 0;

		$output['custom_footer_text'] = isset( $input['custom_footer_text'] ) ? wp_kses_post( $input['custom_footer_text'] ) : '';
		$output['hide_core_version']  = ! empty( $input['hide_core_version'] ) ? 1 : 0;

		return $output;
	}

	public function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'earthin-white-label' ) );
		}

		$settings = $this->get_settings();
		$has_custom_logo = ! empty( $settings['login_logo_url'] );

		$hidden_widgets_count = 0;
		foreach ( array( 'hide_wp_news', 'hide_quick_draft', 'hide_activity', 'hide_at_a_glance' ) as $wk ) {
			if ( ! empty( $settings[ $wk ] ) ) {
				$hidden_widgets_count++;
			}
		}
		?>
		<div class="wrap" style="max-width:1100px; margin:20px auto 40px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,sans-serif;">
			<!-- Header Banner -->
			<div style="display:flex; align-items:center; justify-content:space-between; background:linear-gradient(135deg, #059669 0%, #047857 100%); padding:22px 28px; border-radius:12px; color:#fff; box-shadow:0 4px 18px rgba(5,150,105,0.22); margin-bottom:24px;">
				<div style="display:flex; align-items:center; gap:16px;">
					<div style="background:rgba(255,255,255,0.2); border-radius:12px; width:50px; height:50px; display:flex; align-items:center; justify-content:center; font-size:26px;">🏷️</div>
					<div>
						<h1 style="color:#fff; margin:0; font-size:22px; font-weight:700; line-height:1.2;"><?php esc_html_e( 'Earthin White Label CMS', 'earthin-white-label' ); ?></h1>
						<p style="margin:4px 0 0; color:#d1fae5; font-size:13px;"><?php esc_html_e( 'Reversible branding controls for the WordPress login screen, dashboard widgets, and administrative footers', 'earthin-white-label' ); ?></p>
					</div>
				</div>
				<div style="display:flex; align-items:center; gap:10px;">
					<span style="background:rgba(255,255,255,0.18); padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600;">v<?php echo esc_html( EWL_VERSION ); ?></span>
					<a href="<?php echo esc_url( wp_login_url() ); ?>" target="_blank" class="button" style="background:#fff; color:#047857; border:none; padding:6px 14px; border-radius:6px; font-size:12px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:5px; height:auto; line-height:1.4;">
						<?php esc_html_e( 'Preview Login Screen ↗', 'earthin-white-label' ); ?>
					</a>
				</div>
			</div>

			<!-- Quick Stat Cards -->
			<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px; margin-bottom:24px;">
				<div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e( 'Login Screen Branding', 'earthin-white-label' ); ?></div>
					<div style="font-size:20px; font-weight:700; margin-top:4px; color:<?php echo $has_custom_logo ? '#059669' : '#0f172a'; ?>;">
						<?php echo $has_custom_logo ? '✓ Custom Brand Logo' : 'Default WordPress'; ?>
					</div>
					<div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e( 'Applied to wp-login.php', 'earthin-white-label' ); ?></div>
				</div>

				<div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e( 'Clean Dashboard', 'earthin-white-label' ); ?></div>
					<div style="font-size:24px; font-weight:700; color:#059669; margin-top:4px;">
						<?php echo esc_html( $hidden_widgets_count ); ?> / 4
					</div>
					<div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e( 'Clutter widgets removed', 'earthin-white-label' ); ?></div>
				</div>

				<div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e( 'Security Hardening', 'earthin-white-label' ); ?></div>
					<div style="font-size:18px; font-weight:700; color:#0f172a; margin-top:6px;">
						<?php echo ! empty( $settings['hide_core_version'] ) ? '<span style="color:#10b981;">✓ Version Hidden</span>' : 'Exposed'; ?>
					</div>
					<div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e( 'Prevents recon of WP release', 'earthin-white-label' ); ?></div>
				</div>
			</div>

			<form method="post" action="options.php">
				<?php settings_fields( 'ewl_settings_group' ); ?>

				<div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">
					<!-- Main Configuration Panel -->
					<div>
						<!-- Login Screen Card -->
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
							<h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
								<span>🔐</span> <?php esc_html_e( 'Login Screen Customization', 'earthin-white-label' ); ?>
							</h2>

							<div style="margin-bottom:16px;">
								<label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e( 'Custom Login Logo URL', 'earthin-white-label' ); ?></label>
								<input type="url" name="<?php echo esc_attr( EWL_OPTION_KEY ); ?>[login_logo_url]" value="<?php echo esc_attr( $settings['login_logo_url'] ); ?>" class="large-text" placeholder="https://example.com/wp-content/uploads/logo.png" style="padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;">
								<p style="font-size:12px; color:#64748b; margin-top:4px;"><?php esc_html_e( 'Replaces the default WordPress logo on wp-login.php with your client or agency branding.', 'earthin-white-label' ); ?></p>
							</div>

							<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
								<div>
									<label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e( 'Logo Destination Link', 'earthin-white-label' ); ?></label>
									<input type="url" name="<?php echo esc_attr( EWL_OPTION_KEY ); ?>[login_logo_link]" value="<?php echo esc_attr( $settings['login_logo_link'] ); ?>" class="large-text" placeholder="<?php echo esc_url( home_url( '/' ) ); ?>" style="padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;">
								</div>

								<div>
									<label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e( 'Login Background Color', 'earthin-white-label' ); ?></label>
									<input type="text" name="<?php echo esc_attr( EWL_OPTION_KEY ); ?>[login_bg_color]" value="<?php echo esc_attr( $settings['login_bg_color'] ); ?>" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-family:monospace; font-size:13px;" placeholder="#f1f5f9">
								</div>
							</div>
						</div>

						<!-- Admin Dashboard Cleanup Card -->
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
							<h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
								<span>📊</span> <?php esc_html_e( 'Dashboard & Admin UI Decluttering', 'earthin-white-label' ); ?>
							</h2>

							<div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:16px;">
								<label style="display:flex; align-items:center; gap:10px; padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; cursor:pointer;">
									<input type="checkbox" name="<?php echo esc_attr( EWL_OPTION_KEY ); ?>[hide_wp_news]" value="1" <?php checked( 1, $settings['hide_wp_news'] ); ?>>
									<span style="font-size:13px; font-weight:500; color:#1e293b;"><?php esc_html_e( 'Hide WP Events & News', 'earthin-white-label' ); ?></span>
								</label>

								<label style="display:flex; align-items:center; gap:10px; padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; cursor:pointer;">
									<input type="checkbox" name="<?php echo esc_attr( EWL_OPTION_KEY ); ?>[hide_quick_draft]" value="1" <?php checked( 1, $settings['hide_quick_draft'] ); ?>>
									<span style="font-size:13px; font-weight:500; color:#1e293b;"><?php esc_html_e( 'Hide Quick Draft Box', 'earthin-white-label' ); ?></span>
								</label>

								<label style="display:flex; align-items:center; gap:10px; padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; cursor:pointer;">
									<input type="checkbox" name="<?php echo esc_attr( EWL_OPTION_KEY ); ?>[hide_activity]" value="1" <?php checked( 1, $settings['hide_activity'] ); ?>>
									<span style="font-size:13px; font-weight:500; color:#1e293b;"><?php esc_html_e( 'Hide Activity Widget', 'earthin-white-label' ); ?></span>
								</label>

								<label style="display:flex; align-items:center; gap:10px; padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; cursor:pointer;">
									<input type="checkbox" name="<?php echo esc_attr( EWL_OPTION_KEY ); ?>[hide_at_a_glance]" value="1" <?php checked( 1, $settings['hide_at_a_glance'] ); ?>>
									<span style="font-size:13px; font-weight:500; color:#1e293b;"><?php esc_html_e( 'Hide At A Glance Widget', 'earthin-white-label' ); ?></span>
								</label>
							</div>

							<div style="margin-bottom:16px;">
								<label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e( 'Custom Admin Footer Text', 'earthin-white-label' ); ?></label>
								<input type="text" name="<?php echo esc_attr( EWL_OPTION_KEY ); ?>[custom_footer_text]" value="<?php echo esc_attr( $settings['custom_footer_text'] ); ?>" class="large-text" style="padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;">
								<p style="font-size:12px; color:#64748b; margin-top:4px;"><?php esc_html_e( 'Replaces the default "Thank you for creating with WordPress" message.', 'earthin-white-label' ); ?></p>
							</div>

							<label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
								<input type="checkbox" name="<?php echo esc_attr( EWL_OPTION_KEY ); ?>[hide_core_version]" value="1" <?php checked( 1, $settings['hide_core_version'] ); ?>>
								<span style="font-size:13px; font-weight:600; color:#0f172a;"><?php esc_html_e( 'Hide WordPress version string in bottom-right footer', 'earthin-white-label' ); ?></span>
							</label>
						</div>

						<button type="submit" class="button button-primary" style="background:#059669; border-color:#047857; color:#fff; font-weight:600; padding:8px 24px; height:auto; border-radius:6px; font-size:14px; box-shadow:0 2px 4px rgba(5,150,105,0.25);">
							<?php esc_html_e( 'Save White Label Settings', 'earthin-white-label' ); ?>
						</button>
					</div>

					<!-- Sidebar Info -->
					<div>
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:20px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:20px;">
							<h3 style="font-size:14px; font-weight:700; color:#0f172a; margin:0 0 10px;"><?php esc_html_e( 'Client-Friendly UX', 'earthin-white-label' ); ?></h3>
							<p style="font-size:12px; color:#64748b; line-height:1.5; margin:0 0 10px;">
								<?php esc_html_e( 'Removing standard clutter widgets like "WordPress Events and News" prevents non-technical clients from feeling overwhelmed in the admin area.', 'earthin-white-label' ); ?>
							</p>
						</div>

						<div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:18px;">
							<div style="font-size:13px; font-weight:700; color:#166534; margin-bottom:8px;">💡 <?php esc_html_e( '100% Reversible', 'earthin-white-label' ); ?></div>
							<p style="font-size:12px; color:#15803d; line-height:1.5; margin:0;">
								<?php esc_html_e( 'Earthin White Label never modifies core WordPress files. All branding settings can be reverted or turned off instantly at any time.', 'earthin-white-label' ); ?>
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
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-white-label')) . '">' . __('Settings', 'earthin-white-label') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
