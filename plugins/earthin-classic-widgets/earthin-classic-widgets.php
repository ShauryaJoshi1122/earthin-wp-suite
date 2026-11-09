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
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Earthin Classic Widgets', 'earthin-classic-widgets' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Easily switch between the traditional WordPress widget administration screen and the modern block-based widget editor.', 'earthin-classic-widgets' ); ?>
			</p>

			<form method="post" action="options.php" style="margin-top:20px;">
				<?php settings_fields( 'ecw_settings_group' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Widget Editor Interface', 'earthin-classic-widgets' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( ECW_OPTION_KEY ); ?>[enabled]" value="1" <?php checked( 1, $settings['enabled'] ); ?>>
								<?php esc_html_e( 'Enable Classic Widgets (Disables Gutenberg in Appearance > Widgets)', 'earthin-classic-widgets' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'When checked, the familiar traditional drag-and-drop widget interface is restored in both Appearance > Widgets and the Customizer.', 'earthin-classic-widgets' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<div style="margin-top:20px;">
					<?php submit_button( __( 'Save Settings', 'earthin-classic-widgets' ) ); ?>
				</div>
			</form>
		</div>
		<?php
	}
}

Plugin::instance();

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function() {
     = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-classic-widgets')) . '">' . __('Settings', 'earthin-classic-widgets') . '</a>';
    array_unshift(, );
    return ;
});
