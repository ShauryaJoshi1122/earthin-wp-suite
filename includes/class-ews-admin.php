<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class Admin {
	public function boot() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'settings' ) );
	}
	public function menu() {
		add_menu_page( __( 'Earthin WP Suite', 'earthin-wp-suite' ), __( 'Earthin Suite', 'earthin-wp-suite' ), 'manage_options', 'earthin-wp-suite', array( $this, 'page' ), 'dashicons-admin-tools', 58 );
	}
	public function settings() {
		register_setting( 'ews_settings_group', 'ews_settings', array( 'type' => 'array', 'sanitize_callback' => array( $this, 'sanitize' ), 'default' => array( 'seo_enabled' => 1, 'social_preview_enabled' => 1 ) ) );
	}
	public function sanitize( $input ) {
		if ( ! current_user_can( 'manage_options' ) ) { return get_option( 'ews_settings', array() ); }
		$input = is_array( $input ) ? $input : array();
		return array( 'seo_enabled' => empty( $input['seo_enabled'] ) ? 0 : 1, 'social_preview_enabled' => empty( $input['social_preview_enabled'] ) ? 0 : 1 );
	}
	public function page() {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Access denied.', 'earthin-wp-suite' ) ); }
		$s = get_option( 'ews_settings', array( 'seo_enabled' => 1, 'social_preview_enabled' => 1 ) );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Earthin WP Suite', 'earthin-wp-suite' ); ?></h1>
			<p><?php esc_html_e( 'Modular WordPress tools by Earthin. Enable or disable modules independently.', 'earthin-wp-suite' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'ews_settings_group' ); ?>
				<table class="form-table"><tr><th><?php esc_html_e( 'SEO module', 'earthin-wp-suite' ); ?></th><td><label><input type="checkbox" name="ews_settings[seo_enabled]" value="1" <?php checked( ! empty( $s['seo_enabled'] ) ); ?>> <?php esc_html_e( 'Enabled', 'earthin-wp-suite' ); ?></label></td></tr>
				<tr><th><?php esc_html_e( 'Social Preview module', 'earthin-wp-suite' ); ?></th><td><label><input type="checkbox" name="ews_settings[social_preview_enabled]" value="1" <?php checked( ! empty( $s['social_preview_enabled'] ) ); ?>> <?php esc_html_e( 'Enabled', 'earthin-wp-suite' ); ?></label></td></tr></table>
				<?php submit_button(); ?>
			</form>
			<p><?php esc_html_e( 'Foundation dashboard is active. Feature modules are still under development.', 'earthin-wp-suite' ); ?></p>
		</div>
		<?php
	}
}
