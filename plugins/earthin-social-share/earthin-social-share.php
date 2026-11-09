<?php
/**
 * Plugin Name: Earthin Social Share
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-social-share
 * Description: Fast, privacy-focused social sharing buttons for WordPress with zero external tracking scripts, clean SVG icons, and shortcode support.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: earthin-social-share
 */

namespace Earthin\SocialShare;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ESS_VERSION', '0.1.0' );
define( 'ESS_PATH', plugin_dir_path( __FILE__ ) );
define( 'ESS_URL', plugin_dir_url( __FILE__ ) );
define( 'ESS_OPTION_KEY', 'earthin_social_share_settings' );

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

		// Frontend hooks
		add_filter( 'the_content', array( $this, 'filter_content_share_buttons' ) );
		add_shortcode( 'earthin_social_share', array( $this, 'render_shortcode' ) );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'earthin-social-share', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function get_default_settings(): array {
		return array(
			'placement'  => 'below', // 'below', 'above', 'both', 'manual'
			'networks'   => array( 'twitter', 'facebook', 'linkedin', 'whatsapp', 'telegram', 'email' ),
			'post_types' => array( 'post' ),
			'headline'   => __( 'Share this article:', 'earthin-social-share' ),
		);
	}

	public function get_settings(): array {
		$saved = get_option( ESS_OPTION_KEY, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), $this->get_default_settings() );
	}

	public function filter_content_share_buttons( string $content ): string {
		if ( ! is_singular() || is_feed() || is_preview() ) {
			return $content;
		}

		$settings = $this->get_settings();
		$current_pt = get_post_type();
		$allowed_pts = is_array( $settings['post_types'] ) ? $settings['post_types'] : array();

		if ( ! in_array( $current_pt, $allowed_pts, true ) ) {
			return $content;
		}

		$placement = $settings['placement'] ?? 'below';
		if ( 'manual' === $placement ) {
			return $content;
		}

		$buttons_html = $this->render_buttons_html();

		if ( 'above' === $placement ) {
			return $buttons_html . $content;
		} elseif ( 'both' === $placement ) {
			return $buttons_html . $content . $buttons_html;
		} else {
			return $content . $buttons_html;
		}
	}

	public function render_shortcode(): string {
		return $this->render_buttons_html();
	}

	public function render_buttons_html(): string {
		$settings = $this->get_settings();
		$networks = is_array( $settings['networks'] ) ? $settings['networks'] : array();
		if ( empty( $networks ) ) {
			return '';
		}

		$post_id   = get_the_ID();
		$post_url  = esc_url( get_permalink( $post_id ) );
		$post_title = esc_attr( get_the_title( $post_id ) );
		$encoded_url = rawurlencode( $post_url );
		$encoded_title = rawurlencode( html_entity_decode( $post_title, ENT_QUOTES, 'UTF-8' ) );

		$links = array(
			'twitter'  => array(
				'label' => 'X (Twitter)',
				'url'   => 'https://twitter.com/intent/tweet?text=' . $encoded_title . '&url=' . $encoded_url,
				'bg'    => '#000000',
			),
			'facebook' => array(
				'label' => 'Facebook',
				'url'   => 'https://www.facebook.com/sharer/sharer.php?u=' . $encoded_url,
				'bg'    => '#1877f2',
			),
			'linkedin' => array(
				'label' => 'LinkedIn',
				'url'   => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $encoded_url,
				'bg'    => '#0a66c2',
			),
			'whatsapp' => array(
				'label' => 'WhatsApp',
				'url'   => 'https://api.whatsapp.com/send?text=' . $encoded_title . '%20' . $encoded_url,
				'bg'    => '#25d366',
			),
			'telegram' => array(
				'label' => 'Telegram',
				'url'   => 'https://t.me/share/url?url=' . $encoded_url . '&text=' . $encoded_title,
				'bg'    => '#229ed9',
			),
			'reddit'   => array(
				'label' => 'Reddit',
				'url'   => 'https://reddit.com/submit?url=' . $encoded_url . '&title=' . $encoded_title,
				'bg'    => '#ff4500',
			),
			'email'    => array(
				'label' => 'Email',
				'url'   => 'mailto:?subject=' . $encoded_title . '&body=' . $encoded_url,
				'bg'    => '#475569',
			),
		);

		ob_start();
		?>
		<div class="earthin-social-share-wrap" style="margin:24px 0; padding:16px 0; border-top:1px solid #e2e8f0; border-bottom:1px solid #e2e8f0;">
			<?php if ( ! empty( $settings['headline'] ) ) : ?>
				<div style="font-size:14px; font-weight:600; color:#334155; margin-bottom:10px;">
					<?php echo esc_html( $settings['headline'] ); ?>
				</div>
			<?php endif; ?>
			<div style="display:flex; flex-wrap:wrap; gap:8px;">
				<?php foreach ( $networks as $net ) : ?>
					<?php if ( isset( $links[ $net ] ) ) : ?>
						<a href="<?php echo esc_url( $links[ $net ]['url'] ); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex; align-items:center; background:<?php echo esc_attr( $links[ $net ]['bg'] ); ?>; color:#ffffff; font-size:12px; font-weight:600; text-decoration:none; padding:6px 12px; border-radius:6px; transition:opacity 0.2s;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
							<?php echo esc_html( $links[ $net ]['label'] ); ?>
						</a>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	public function register_admin_menu(): void {
		add_options_page(
			__( 'Earthin Social Share', 'earthin-social-share' ),
			__( 'Social Share', 'earthin-social-share' ),
			'manage_options',
			'earthin-social-share',
			array( $this, 'render_admin_page' )
		);
	}

	public function register_settings(): void {
		register_setting(
			'ess_settings_group',
			ESS_OPTION_KEY,
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
		$place = isset( $input['placement'] ) ? sanitize_key( $input['placement'] ) : 'below';
		$output['placement'] = in_array( $place, array( 'below', 'above', 'both', 'manual' ), true ) ? $place : 'below';

		$allowed_networks = array( 'twitter', 'facebook', 'linkedin', 'whatsapp', 'telegram', 'reddit', 'email' );
		$nets = isset( $input['networks'] ) && is_array( $input['networks'] )
			? array_map( 'sanitize_key', $input['networks'] )
			: array();
		$output['networks'] = array_values( array_intersect( $nets, $allowed_networks ) );

		$types = isset( $input['post_types'] ) && is_array( $input['post_types'] )
			? array_map( 'sanitize_key', $input['post_types'] )
			: array( 'post' );
		$output['post_types'] = array_values( array_unique( $types ) );

		$output['headline'] = isset( $input['headline'] ) ? sanitize_text_field( $input['headline'] ) : '';

		return $output;
	}

	public function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'earthin-social-share' ) );
		}

		$settings = $this->get_settings();
		$all_networks = array(
			'twitter'  => 'X (Twitter)',
			'facebook' => 'Facebook',
			'linkedin' => 'LinkedIn',
			'whatsapp' => 'WhatsApp',
			'telegram' => 'Telegram',
			'reddit'   => 'Reddit',
			'email'    => 'Email',
		);
		$available_post_types = get_post_types( array( 'public' => true ), 'objects' );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Earthin Social Share Settings', 'earthin-social-share' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Add fast, lightweight, and privacy-respecting social sharing buttons to your posts with zero external JavaScript or tracking cookies.', 'earthin-social-share' ); ?>
			</p>

			<form method="post" action="options.php" style="margin-top:20px;">
				<?php settings_fields( 'ess_settings_group' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Enabled Networks', 'earthin-social-share' ); ?></th>
						<td>
							<fieldset>
								<?php foreach ( $all_networks as $slug => $label ) : ?>
									<label style="display:inline-block; margin-right:16px; margin-bottom:8px;">
										<input type="checkbox" name="<?php echo esc_attr( ESS_OPTION_KEY ); ?>[networks][]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( in_array( $slug, (array) $settings['networks'], true ) ); ?>>
										<?php echo esc_html( $label ); ?>
									</label>
								<?php endforeach; ?>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Button Placement', 'earthin-social-share' ); ?></th>
						<td>
							<select name="<?php echo esc_attr( ESS_OPTION_KEY ); ?>[placement]">
								<option value="below" <?php selected( 'below', $settings['placement'] ); ?>><?php esc_html_e( 'Below Content (Standard)', 'earthin-social-share' ); ?></option>
								<option value="above" <?php selected( 'above', $settings['placement'] ); ?>><?php esc_html_e( 'Above Content', 'earthin-social-share' ); ?></option>
								<option value="both" <?php selected( 'both', $settings['placement'] ); ?>><?php esc_html_e( 'Both Above & Below Content', 'earthin-social-share' ); ?></option>
								<option value="manual" <?php selected( 'manual', $settings['placement'] ); ?>><?php esc_html_e( 'Manual (Shortcode [earthin_social_share] only)', 'earthin-social-share' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Section Headline', 'earthin-social-share' ); ?></th>
						<td>
							<input type="text" name="<?php echo esc_attr( ESS_OPTION_KEY ); ?>[headline]" value="<?php echo esc_attr( $settings['headline'] ); ?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Enabled Post Types', 'earthin-social-share' ); ?></th>
						<td>
							<fieldset>
								<?php foreach ( $available_post_types as $pt_slug => $pt_obj ) : ?>
									<label style="display:inline-block; margin-right:16px; margin-bottom:8px;">
										<input type="checkbox" name="<?php echo esc_attr( ESS_OPTION_KEY ); ?>[post_types][]" value="<?php echo esc_attr( $pt_slug ); ?>" <?php checked( in_array( $pt_slug, (array) $settings['post_types'], true ) ); ?>>
										<?php echo esc_html( $pt_obj->labels->name ); ?> (<code><?php echo esc_html( $pt_slug ); ?></code>)
									</label>
								<?php endforeach; ?>
							</fieldset>
						</td>
					</tr>
				</table>

				<div style="margin-top:20px;">
					<?php submit_button( __( 'Save Share Settings', 'earthin-social-share' ) ); ?>
				</div>
			</form>
		</div>
		<?php
	}
}

Plugin::instance();

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function() {
     = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-social-share')) . '">' . __('Settings', 'earthin-social-share') . '</a>';
    array_unshift(, );
    return ;
});
