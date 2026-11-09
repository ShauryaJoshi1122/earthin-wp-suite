<?php
/**
 * Plugin Name: Earthin Classic Widgets
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-classic-widgets
 * Description: Restores the traditional WordPress widget administration screen and disables the block-based widget editor.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: earthin-classic-widgets
 */

namespace Earthin\ClassicWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ECW_VERSION', '0.1.0' );
define( 'ECW_PATH', plugin_dir_path( __FILE__ ) );
define( 'ECW_URL', plugin_dir_url( __FILE__ ) );
define( 'ECW_OPTION_KEY', 'earthin_classic_widgets_settings' );

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

		// Filter widget block editor
		add_filter( 'use_widgets_block_editor', array( $this, 'filter_widgets_block_editor' ), 999 );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'earthin-classic-widgets', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function get_default_settings(): array {
		return array(
			'enabled' => 1,
		);
	}

	public function get_settings(): array {
		$saved = get_option( ECW_OPTION_KEY, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), $this->get_default_settings() );
	}

	public function filter_widgets_block_editor( bool $use_block_editor ): bool {
		$settings = $this->get_settings();
		if ( ! empty( $settings['enabled'] ) ) {
			return false; // Force classic widgets screen
		}
		return $use_block_editor;
	}

	public function register_admin_menu(): void {
		add_options_page(
			__( 'Earthin Classic Widgets', 'earthin-classic-widgets' ),
			__( 'Earthin Widgets', 'earthin-classic-widgets' ),
			'manage_options',
			'earthin-classic-widgets',
			array( $this, 'render_admin_page' )
		);
	}

	public function register_settings(): void {
		register_setting(
			'ecw_settings_group',
			ECW_OPTION_KEY,
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
		return array(
			'enabled' => ! empty( $input['enabled'] ) ? 1 : 0,
		);
	}

	public function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'earthin-classic-widgets' ) );
		}

		$settings = $this->get_settings();
		$is_active = ! empty( $settings['enabled'] );
		?>
		<div class="wrap" style="max-width:1100px; margin:20px auto 40px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,sans-serif;">
			<!-- Header Banner -->
			<div style="display:flex; align-items:center; justify-content:space-between; background:linear-gradient(135deg, #059669 0%, #047857 100%); padding:22px 28px; border-radius:12px; color:#fff; box-shadow:0 4px 18px rgba(5,150,105,0.22); margin-bottom:24px;">
				<div style="display:flex; align-items:center; gap:16px;">
					<div style="background:rgba(255,255,255,0.2); border-radius:12px; width:50px; height:50px; display:flex; align-items:center; justify-content:center; font-size:26px;">🧩</div>
					<div>
						<h1 style="color:#fff; margin:0; font-size:22px; font-weight:700; line-height:1.2;"><?php esc_html_e( 'Earthin Classic Widgets', 'earthin-classic-widgets' ); ?></h1>
						<p style="margin:4px 0 0; color:#d1fae5; font-size:13px;"><?php esc_html_e( 'Restore the traditional drag-and-drop widget administration screen', 'earthin-classic-widgets' ); ?></p>
					</div>
				</div>
				<div style="display:flex; align-items:center; gap:10px;">
					<span style="background:rgba(255,255,255,0.18); padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600;">v<?php echo esc_html( ECW_VERSION ); ?></span>
					<a href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>" class="button" style="background:#fff; color:#047857; border:none; padding:6px 14px; border-radius:6px; font-size:12px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:5px; height:auto; line-height:1.4;">
						<?php esc_html_e( 'Go to Widgets Screen →', 'earthin-classic-widgets' ); ?>
					</a>
				</div>
			</div>

			<!-- Quick Stat Cards -->
			<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px; margin-bottom:24px;">
				<div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e( 'Current Mode', 'earthin-classic-widgets' ); ?></div>
					<div style="font-size:20px; font-weight:700; margin-top:4px; color:<?php echo $is_active ? '#059669' : '#64748b'; ?>;">
						<?php echo $is_active ? '✓ Classic Drag & Drop' : 'Gutenberg Block Widgets'; ?>
					</div>
					<div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e( 'Appearance > Widgets interface', 'earthin-classic-widgets' ); ?></div>
				</div>

				<div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e( 'Customizer Integration', 'earthin-classic-widgets' ); ?></div>
					<div style="font-size:20px; font-weight:700; color:#0f172a; margin-top:4px;">
						<?php echo $is_active ? '<span style="color:#10b981;">✓ Synced</span>' : 'Native Gutenberg'; ?>
					</div>
					<div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e( 'Customizer widget panels aligned', 'earthin-classic-widgets' ); ?></div>
				</div>

				<div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e( 'Performance Footprint', 'earthin-classic-widgets' ); ?></div>
					<div style="font-size:20px; font-weight:700; color:#059669; margin-top:4px;">0 KB Overhead</div>
					<div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e( 'Pure WordPress filter mechanism', 'earthin-classic-widgets' ); ?></div>
				</div>
			</div>

			<form method="post" action="options.php">
				<?php settings_fields( 'ecw_settings_group' ); ?>

				<div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">
					<!-- Settings Box -->
					<div>
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
							<h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
								<span>⚙️</span> <?php esc_html_e( 'Interface Settings', 'earthin-classic-widgets' ); ?>
							</h2>

							<label style="display:flex; align-items:flex-start; gap:14px; padding:18px; border:1px solid <?php echo $is_active ? '#059669' : '#cbd5e1'; ?>; background:<?php echo $is_active ? '#f0fdf4' : '#f8fafc'; ?>; border-radius:8px; cursor:pointer;">
								<input type="checkbox" name="<?php echo esc_attr( ECW_OPTION_KEY ); ?>[enabled]" value="1" <?php checked( 1, $settings['enabled'] ); ?> style="margin-top:3px;">
								<div>
									<strong style="font-size:14px; color:#0f172a; display:block;"><?php esc_html_e( 'Enable Classic Widgets (Disables Gutenberg in Appearance > Widgets)', 'earthin-classic-widgets' ); ?></strong>
									<span style="font-size:12px; color:#64748b; line-height:1.4; display:block; margin-top:4px;">
										<?php esc_html_e( 'When checked, the familiar traditional drag-and-drop widget interface is restored in both Appearance > Widgets and the live WordPress Customizer.', 'earthin-classic-widgets' ); ?>
									</span>
								</div>
							</label>
						</div>

						<button type="submit" class="button button-primary" style="background:#059669; border-color:#047857; color:#fff; font-weight:600; padding:8px 24px; height:auto; border-radius:6px; font-size:14px; box-shadow:0 2px 4px rgba(5,150,105,0.25);">
							<?php esc_html_e( 'Save Widget Settings', 'earthin-classic-widgets' ); ?>
						</button>
					</div>

					<!-- Sidebar Info -->
					<div>
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:20px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:20px;">
							<h3 style="font-size:14px; font-weight:700; color:#0f172a; margin:0 0 12px;"><?php esc_html_e( 'Why Classic Widgets?', 'earthin-classic-widgets' ); ?></h3>
							<ul style="font-size:12px; color:#64748b; line-height:1.6; margin:0; padding-left:16px;">
								<li><?php esc_html_e( 'Fixes layout glitches and widget disappearances caused by block widget migrations.', 'earthin-classic-widgets' ); ?></li>
								<li><?php esc_html_e( 'Maintains support for legacy PHP widgets with complex settings forms.', 'earthin-classic-widgets' ); ?></li>
								<li><?php esc_html_e( 'Super fast and simple drag-and-drop sidebars management.', 'earthin-classic-widgets' ); ?></li>
							</ul>
						</div>

						<div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:18px;">
							<div style="font-size:13px; font-weight:700; color:#166534; margin-bottom:8px;">🌱 <?php esc_html_e( 'Earthin Suite', 'earthin-classic-widgets' ); ?></div>
							<p style="font-size:12px; color:#15803d; line-height:1.5; margin:0;">
								<?php esc_html_e( 'Works seamlessly alongside Earthin Theme and other Earthin plugins to give you complete design control.', 'earthin-classic-widgets' ); ?>
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
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-classic-widgets')) . '">' . __('Settings', 'earthin-classic-widgets') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
