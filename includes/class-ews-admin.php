<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class Admin {
	private $defaults = array( 'seo_enabled' => 1, 'social_preview_enabled' => 1, 'security_enabled' => 0, 'redirects_enabled' => 0, 'rss_enabled' => 0 );
	public function boot() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'settings' ) );
	}
	public function menu() {
		add_menu_page( __( 'Earthin WP Suite', 'earthin-wp-suite' ), __( 'Earthin Suite', 'earthin-wp-suite' ), 'manage_options', 'earthin-wp-suite', array( $this, 'page' ), 'dashicons-admin-tools', 58 );
	}
	public function settings() {
		register_setting( 'ews_settings_group', 'ews_settings', array( 'type' => 'array', 'sanitize_callback' => array( $this, 'sanitize' ), 'default' => $this->defaults ) );
	}
	public function sanitize( $input ) {
		if ( ! current_user_can( 'manage_options' ) ) { return get_option( 'ews_settings', $this->defaults ); }
		$input = is_array( $input ) ? $input : array();
		$clean = array();
		foreach ( $this->defaults as $key => $default ) { $clean[ $key ] = empty( $input[ $key ] ) ? 0 : 1; }
		return $clean;
	}
	public function page() {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Access denied.', 'earthin-wp-suite' ) ); }
		$s = wp_parse_args( get_option( 'ews_settings', array() ), $this->defaults );
		$modules = array(
			'seo_enabled' => __( 'SEO metadata and canonical URLs', 'earthin-wp-suite' ),
			'social_preview_enabled' => __( 'Social previews (Open Graph / X cards)', 'earthin-wp-suite' ),
			'security_enabled' => __( 'Security headers and XML-RPC disable', 'earthin-wp-suite' ),
			'redirects_enabled' => __( 'Exact-path redirects (rules must be added by an administrator)', 'earthin-wp-suite' ),
			'rss_enabled' => __( 'RSS import to draft review queue', 'earthin-wp-suite' ),
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Earthin WP Suite', 'earthin-wp-suite' ); ?></h1>
			<p><?php esc_html_e( 'Modular WordPress tools by Earthin. Enable only the modules you intend to use.', 'earthin-wp-suite' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'ews_settings_group' ); ?>
				<table class="widefat striped" style="max-width:900px"><tbody>
				<?php foreach ( $modules as $key => $label ) : ?>
					<tr><th scope="row"><?php echo esc_html( $label ); ?></th><td><label><input type="checkbox" name="ews_settings[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( ! empty( $s[ $key ] ) ); ?>> <?php esc_html_e( 'Enabled', 'earthin-wp-suite' ); ?></label></td></tr>
				<?php endforeach; ?>
				</tbody></table>
				<?php submit_button(); ?>
			</form>
			<hr>
			<h2><?php esc_html_e( 'Import RSS feed to drafts', 'earthin-wp-suite' ); ?></h2>
			<p><?php esc_html_e( 'Imports up to five new items as drafts. Nothing is auto-published. Enable the RSS module first.', 'earthin-wp-suite' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ews_import_rss">
				<?php wp_nonce_field( 'ews_import_rss' ); ?>
				<input type="url" name="ews_feed_url" class="regular-text" placeholder="https://example.com/feed/" required>
				<?php submit_button( __( 'Import drafts', 'earthin-wp-suite' ), 'secondary', 'submit', false ); ?>
			</form>
			<p class="description"><?php esc_html_e( 'SEO and social modules skip their frontend tags when a recognized SEO plugin is active to reduce duplicate metadata.', 'earthin-wp-suite' ); ?></p>
		</div>
		<?php
	}
}
