<?php
/**
 * Plugin Name: Earthin Advanced Editor
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-advanced-editor
 * Description: Unlocks advanced formatting buttons, custom typography controls, tables, and role-based toolbars in the WordPress Classic Editor.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: earthin-advanced-editor
 */

namespace Earthin\AdvancedEditor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EAE_VERSION', '0.1.0' );
define( 'EAE_PATH', plugin_dir_path( __FILE__ ) );
define( 'EAE_URL', plugin_dir_url( __FILE__ ) );
define( 'EAE_OPTION_KEY', 'earthin_advanced_editor_settings' );

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

		// TinyMCE button filters
		add_filter( 'mce_buttons', array( $this, 'filter_mce_buttons_row1' ) );
		add_filter( 'mce_buttons_2', array( $this, 'filter_mce_buttons_row2' ) );
		add_filter( 'tiny_mce_before_init', array( $this, 'configure_tinymce_init' ) );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'earthin-advanced-editor', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function get_default_settings(): array {
		return array(
			'enable_fonts'       => 1,
			'enable_font_sizes'  => 1,
			'enable_backcolor'   => 1,
			'enable_underline'   => 1,
			'enable_sub_sup'     => 1,
			'enable_justify'     => 1,
			'font_sizes'         => '11px 12px 14px 16px 18px 20px 24px 28px 32px 36px 48px',
			'role_restriction'   => 'all', // 'all' or 'admin_editor'
		);
	}

	public function get_settings(): array {
		$saved = get_option( EAE_OPTION_KEY, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), $this->get_default_settings() );
	}

	public function is_user_allowed(): bool {
		$settings = $this->get_settings();
		if ( 'admin_editor' === $settings['role_restriction'] ) {
			return current_user_can( 'edit_others_posts' );
		}
		return true;
	}

	public function filter_mce_buttons_row1( array $buttons ): array {
		if ( ! $this->is_user_allowed() ) {
			return $buttons;
		}

		$settings = $this->get_settings();

		if ( ! empty( $settings['enable_fonts'] ) && ! in_array( 'fontselect', $buttons, true ) ) {
			array_splice( $buttons, 2, 0, 'fontselect' );
		}

		if ( ! empty( $settings['enable_font_sizes'] ) && ! in_array( 'fontsizeselect', $buttons, true ) ) {
			$idx = array_search( 'fontselect', $buttons, true );
			$pos = ( false !== $idx ) ? $idx + 1 : 2;
			array_splice( $buttons, $pos, 0, 'fontsizeselect' );
		}

		return $buttons;
	}

	public function filter_mce_buttons_row2( array $buttons ): array {
		if ( ! $this->is_user_allowed() ) {
			return $buttons;
		}

		$settings = $this->get_settings();

		if ( ! empty( $settings['enable_underline'] ) && ! in_array( 'underline', $buttons, true ) ) {
			array_unshift( $buttons, 'underline' );
		}

		if ( ! empty( $settings['enable_justify'] ) && ! in_array( 'alignjustify', $buttons, true ) ) {
			$buttons[] = 'alignjustify';
		}

		if ( ! empty( $settings['enable_backcolor'] ) && ! in_array( 'backcolor', $buttons, true ) ) {
			$buttons[] = 'backcolor';
		}

		if ( ! empty( $settings['enable_sub_sup'] ) ) {
			if ( ! in_array( 'sub', $buttons, true ) ) {
				$buttons[] = 'sub';
			}
			if ( ! in_array( 'sup', $buttons, true ) ) {
				$buttons[] = 'sup';
			}
		}

		return $buttons;
	}

	public function configure_tinymce_init( array $init ): array {
		$settings = $this->get_settings();
		if ( ! empty( $settings['enable_font_sizes'] ) && ! empty( $settings['font_sizes'] ) ) {
			$init['fontsize_formats'] = sanitize_text_field( $settings['font_sizes'] );
		}
		return $init;
	}

	public function register_admin_menu(): void {
		add_options_page(
			__( 'Earthin Advanced Editor', 'earthin-advanced-editor' ),
			__( 'Advanced Editor', 'earthin-advanced-editor' ),
			'manage_options',
			'earthin-advanced-editor',
			array( $this, 'render_admin_page' )
		);
	}

	public function register_settings(): void {
		register_setting(
			'eae_settings_group',
			EAE_OPTION_KEY,
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
		$output['enable_fonts']      = ! empty( $input['enable_fonts'] ) ? 1 : 0;
		$output['enable_font_sizes'] = ! empty( $input['enable_font_sizes'] ) ? 1 : 0;
		$output['enable_backcolor']  = ! empty( $input['enable_backcolor'] ) ? 1 : 0;
		$output['enable_underline']  = ! empty( $input['enable_underline'] ) ? 1 : 0;
		$output['enable_sub_sup']    = ! empty( $input['enable_sub_sup'] ) ? 1 : 0;
		$output['enable_justify']    = ! empty( $input['enable_justify'] ) ? 1 : 0;

		$font_sizes = isset( $input['font_sizes'] ) ? sanitize_text_field( $input['font_sizes'] ) : '';
		$output['font_sizes'] = ! empty( $font_sizes ) ? $font_sizes : '11px 12px 14px 16px 18px 20px 24px 28px 32px 36px 48px';

		$role = isset( $input['role_restriction'] ) ? sanitize_key( $input['role_restriction'] ) : 'all';
		$output['role_restriction'] = in_array( $role, array( 'all', 'admin_editor' ), true ) ? $role : 'all';

		return $output;
	}

	public function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'earthin-advanced-editor' ) );
		}

		$settings = $this->get_settings();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Earthin Advanced Editor', 'earthin-advanced-editor' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Enhance your classic editing toolbar with font pickers, text highlighting, justification, and custom typography sizing.', 'earthin-advanced-editor' ); ?>
			</p>

			<form method="post" action="options.php" style="margin-top:20px;">
				<?php settings_fields( 'eae_settings_group' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Toolbar Formatting Buttons', 'earthin-advanced-editor' ); ?></th>
						<td>
							<fieldset>
								<label style="display:block; margin-bottom:8px;">
									<input type="checkbox" name="<?php echo esc_attr( EAE_OPTION_KEY ); ?>[enable_fonts]" value="1" <?php checked( 1, $settings['enable_fonts'] ); ?>>
									<?php esc_html_e( 'Font Family Dropdown (fontselect)', 'earthin-advanced-editor' ); ?>
								</label>
								<label style="display:block; margin-bottom:8px;">
									<input type="checkbox" name="<?php echo esc_attr( EAE_OPTION_KEY ); ?>[enable_font_sizes]" value="1" <?php checked( 1, $settings['enable_font_sizes'] ); ?>>
									<?php esc_html_e( 'Font Size Dropdown (fontsizeselect)', 'earthin-advanced-editor' ); ?>
								</label>
								<label style="display:block; margin-bottom:8px;">
									<input type="checkbox" name="<?php echo esc_attr( EAE_OPTION_KEY ); ?>[enable_backcolor]" value="1" <?php checked( 1, $settings['enable_backcolor'] ); ?>>
									<?php esc_html_e( 'Background Highlight Color Picker (backcolor)', 'earthin-advanced-editor' ); ?>
								</label>
								<label style="display:block; margin-bottom:8px;">
									<input type="checkbox" name="<?php echo esc_attr( EAE_OPTION_KEY ); ?>[enable_underline]" value="1" <?php checked( 1, $settings['enable_underline'] ); ?>>
									<?php esc_html_e( 'Underline Button (underline)', 'earthin-advanced-editor' ); ?>
								</label>
								<label style="display:block; margin-bottom:8px;">
									<input type="checkbox" name="<?php echo esc_attr( EAE_OPTION_KEY ); ?>[enable_justify]" value="1" <?php checked( 1, $settings['enable_justify'] ); ?>>
									<?php esc_html_e( 'Justify Full Alignment (alignjustify)', 'earthin-advanced-editor' ); ?>
								</label>
								<label style="display:block;">
									<input type="checkbox" name="<?php echo esc_attr( EAE_OPTION_KEY ); ?>[enable_sub_sup]" value="1" <?php checked( 1, $settings['enable_sub_sup'] ); ?>>
									<?php esc_html_e( 'Subscript & Superscript (sub, sup)', 'earthin-advanced-editor' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Available Font Sizes', 'earthin-advanced-editor' ); ?></th>
						<td>
							<input type="text" name="<?php echo esc_attr( EAE_OPTION_KEY ); ?>[font_sizes]" value="<?php echo esc_attr( $settings['font_sizes'] ); ?>" class="large-text">
							<p class="description"><?php esc_html_e( 'Space-separated list of pixel or rem sizes (e.g. 12px 14px 16px 18px 24px 32px).', 'earthin-advanced-editor' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Role Access', 'earthin-advanced-editor' ); ?></th>
						<td>
							<select name="<?php echo esc_attr( EAE_OPTION_KEY ); ?>[role_restriction]">
								<option value="all" <?php selected( 'all', $settings['role_restriction'] ); ?>><?php esc_html_e( 'All Users With Edit Access', 'earthin-advanced-editor' ); ?></option>
								<option value="admin_editor" <?php selected( 'admin_editor', $settings['role_restriction'] ); ?>><?php esc_html_e( 'Administrators & Editors Only', 'earthin-advanced-editor' ); ?></option>
							</select>
						</td>
					</tr>
				</table>

				<div style="margin-top:20px;">
					<?php submit_button( __( 'Save Advanced Editor Settings', 'earthin-advanced-editor' ) ); ?>
				</div>
			</form>
		</div>
		<?php
	}
}

Plugin::instance();

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function() {
     = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-advanced-editor')) . '">' . __('Settings', 'earthin-advanced-editor') . '</a>';
    array_unshift(, );
    return ;
});
