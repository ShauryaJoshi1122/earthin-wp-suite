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
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Earthin White Label CMS', 'earthin-white-label' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Customize your WordPress administrative branding, login appearance, and client dashboard experience with 100% reversible settings.', 'earthin-white-label' ); ?>
			</p>

			<form method="post" action="options.php" style="margin-top:20px;">
				<?php settings_fields( 'ewl_settings_group' ); ?>

				<h2 class="title"><?php esc_html_e( 'Login Screen Branding', 'earthin-white-label' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Custom Login Logo URL', 'earthin-white-label' ); ?></th>
						<td>
							<input type="url" name="<?php echo esc_attr( EWL_OPTION_KEY ); ?>[login_logo_url]" value="<?php echo esc_attr( $settings['login_logo_url'] ); ?>" class="large-text" placeholder="https://example.com/logo.png">
							<p class="description"><?php esc_html_e( 'Enter the URL of your brand logo to replace the default WordPress logo on wp-login.php.', 'earthin-white-label' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Logo Destination Link', 'earthin-white-label' ); ?></th>
						<td>
							<input type="url" name="<?php echo esc_attr( EWL_OPTION_KEY ); ?>[login_logo_link]" value="<?php echo esc_attr( $settings['login_logo_link'] ); ?>" class="large-text" placeholder="<?php echo esc_url( home_url( '/' ) ); ?>">
							<p class="description"><?php esc_html_e( 'Destination URL when a user clicks the login logo (defaults to website home page).', 'earthin-white-label' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Login Background Color', 'earthin-white-label' ); ?></th>
						<td>
							<input type="text" name="<?php echo esc_attr( EWL_OPTION_KEY ); ?>[login_bg_color]" value="<?php echo esc_attr( $settings['login_bg_color'] ); ?>" class="small-text">
						</td>
					</tr>
				</table>

				<h2 class="title" style="margin-top:24px;"><?php esc_html_e( 'Admin Dashboard & Widgets', 'earthin-white-label' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Hide Dashboard Widgets', 'earthin-white-label' ); ?></th>
						<td>
							<fieldset>
								<label style="display:block; margin-bottom:8px;">
									<input type="checkbox" name="<?php echo esc_attr( EWL_OPTION_KEY ); ?>[hide_wp_news]" value="1" <?php checked( 1, $settings['hide_wp_news'] ); ?>>
									<?php esc_html_e( 'Hide "WordPress Events and News" widget', 'earthin-white-label' ); ?>
								</label>
								<label style="display:block; margin-bottom:8px;">
									<input type="checkbox" name="<?php echo esc_attr( EWL_OPTION_KEY ); ?>[hide_quick_draft]" value="1" <?php checked( 1, $settings['hide_quick_draft'] ); ?>>
									<?php esc_html_e( 'Hide "Quick Draft" widget', 'earthin-white-label' ); ?>
								</label>
								<label style="display:block; margin-bottom:8px;">
									<input type="checkbox" name="<?php echo esc_attr( EWL_OPTION_KEY ); ?>[hide_activity]" value="1" <?php checked( 1, $settings['hide_activity'] ); ?>>
									<?php esc_html_e( 'Hide "Activity" widget', 'earthin-white-label' ); ?>
								</label>
								<label style="display:block;">
									<input type="checkbox" name="<?php echo esc_attr( EWL_OPTION_KEY ); ?>[hide_at_a_glance]" value="1" <?php checked( 1, $settings['hide_at_a_glance'] ); ?>>
									<?php esc_html_e( 'Hide "At a Glance" widget', 'earthin-white-label' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Custom Admin Footer Text', 'earthin-white-label' ); ?></th>
						<td>
							<input type="text" name="<?php echo esc_attr( EWL_OPTION_KEY ); ?>[custom_footer_text]" value="<?php echo esc_attr( $settings['custom_footer_text'] ); ?>" class="large-text">
							<p class="description"><?php esc_html_e( 'Replaces the default "Thank you for creating with WordPress" message in admin footers.', 'earthin-white-label' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Security: Hide Core Version', 'earthin-white-label' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( EWL_OPTION_KEY ); ?>[hide_core_version]" value="1" <?php checked( 1, $settings['hide_core_version'] ); ?>>
								<?php esc_html_e( 'Hide WordPress version string in bottom admin footer', 'earthin-white-label' ); ?>
							</label>
						</td>
					</tr>
				</table>

				<div style="margin-top:20px;">
					<?php submit_button( __( 'Save White Label Settings', 'earthin-white-label' ) ); ?>
				</div>
			</form>
		</div>
		<?php
	}
}

Plugin::instance();

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function() {
     = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-white-label')) . '">' . __('Settings', 'earthin-white-label') . '</a>';
    array_unshift(, );
    return ;
});
