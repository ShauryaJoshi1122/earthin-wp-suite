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
			'twitter'  => array( 'name' => 'X (Twitter)', 'color' => '#000000', 'icon' => '𝕏' ),
			'facebook' => array( 'name' => 'Facebook', 'color' => '#1877f2', 'icon' => 'f' ),
			'linkedin' => array( 'name' => 'LinkedIn', 'color' => '#0a66c2', 'icon' => 'in' ),
			'whatsapp' => array( 'name' => 'WhatsApp', 'color' => '#25d366', 'icon' => '🟢' ),
			'telegram' => array( 'name' => 'Telegram', 'color' => '#229ed9', 'icon' => '✈️' ),
			'reddit'   => array( 'name' => 'Reddit', 'color' => '#ff4500', 'icon' => '🔴' ),
			'email'    => array( 'name' => 'Email', 'color' => '#475569', 'icon' => '✉️' ),
		);
		$available_post_types = get_post_types( array( 'public' => true ), 'objects' );
		$active_count = count( (array) $settings['networks'] );
		?>
		<div class="wrap" style="max-width:1100px; margin:20px auto 40px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,sans-serif;">
			<!-- Header Banner -->
			<div style="display:flex; align-items:center; justify-content:space-between; background:linear-gradient(135deg, #059669 0%, #047857 100%); padding:22px 28px; border-radius:12px; color:#fff; box-shadow:0 4px 18px rgba(5,150,105,0.22); margin-bottom:24px;">
				<div style="display:flex; align-items:center; gap:16px;">
					<div style="background:rgba(255,255,255,0.2); border-radius:12px; width:50px; height:50px; display:flex; align-items:center; justify-content:center; font-size:26px;">🚀</div>
					<div>
						<h1 style="color:#fff; margin:0; font-size:22px; font-weight:700; line-height:1.2;"><?php esc_html_e( 'Earthin Social Share', 'earthin-social-share' ); ?></h1>
						<p style="margin:4px 0 0; color:#d1fae5; font-size:13px;"><?php esc_html_e( 'Lightning-fast, zero-tracking social sharing buttons with pure native URLs & SVG icons', 'earthin-social-share' ); ?></p>
					</div>
				</div>
				<div style="display:flex; align-items:center; gap:10px;">
					<span style="background:rgba(255,255,255,0.18); padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600;">v<?php echo esc_html( ESS_VERSION ); ?></span>
					<a href="https://plugins.earthin.online/docs" target="_blank" style="background:#fff; color:#047857; text-decoration:none; padding:6px 14px; border-radius:6px; font-size:12px; font-weight:600;">Documentation</a>
				</div>
			</div>

			<!-- Metric Cards -->
			<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px; margin-bottom:24px;">
				<div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e( 'Active Channels', 'earthin-social-share' ); ?></div>
					<div style="font-size:24px; font-weight:700; color:#059669; margin-top:4px;">
						<?php echo esc_html( $active_count ); ?> / 7
					</div>
					<div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e( 'Selected sharing networks', 'earthin-social-share' ); ?></div>
				</div>

				<div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e( 'Placement Mode', 'earthin-social-share' ); ?></div>
					<div style="font-size:18px; font-weight:700; color:#0f172a; margin-top:6px;">
						<?php
						$mode_labels = array(
							'below'  => 'Below Content',
							'above'  => 'Above Content',
							'both'   => 'Both Above & Below',
							'manual' => 'Manual Shortcode',
						);
						echo esc_html( $mode_labels[ $settings['placement'] ] ?? 'Below Content' );
						?>
					</div>
					<div style="font-size:12px; color:#10b981; margin-top:2px; font-weight:500;">✓ Automated Filter</div>
				</div>

				<div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e( 'Privacy Standard', 'earthin-social-share' ); ?></div>
					<div style="font-size:18px; font-weight:700; color:#059669; margin-top:6px;">100% Tracking Free</div>
					<div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e( 'Zero JS widgets / GDPR compliant', 'earthin-social-share' ); ?></div>
				</div>
			</div>

			<form method="post" action="options.php">
				<?php settings_fields( 'ess_settings_group' ); ?>

				<div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">
					<!-- Main Settings -->
					<div>
						<!-- Networks Card -->
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
							<h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
								<span>🌐</span> <?php esc_html_e( 'Select Active Sharing Networks', 'earthin-social-share' ); ?>
							</h2>

							<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:12px;">
								<?php foreach ( $all_networks as $slug => $data ) : ?>
									<label style="display:flex; align-items:center; gap:10px; padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer;">
										<input type="checkbox" name="<?php echo esc_attr( ESS_OPTION_KEY ); ?>[networks][]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( in_array( $slug, (array) $settings['networks'], true ) ); ?>>
										<span style="display:inline-flex; align-items:center; justify-content:center; width:24px; height:24px; border-radius:4px; background:<?php echo esc_attr( $data['color'] ); ?>; color:#fff; font-size:11px; font-weight:700;">
											<?php echo esc_html( $data['icon'] ); ?>
										</span>
										<strong style="font-size:13px; color:#0f172a;"><?php echo esc_html( $data['name'] ); ?></strong>
									</label>
								<?php endforeach; ?>
							</div>
						</div>

						<!-- Placement & Copy Card -->
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
							<h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
								<span>📍</span> <?php esc_html_e( 'Placement & Headline', 'earthin-social-share' ); ?>
							</h2>

							<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
								<div>
									<label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e( 'Button Placement', 'earthin-social-share' ); ?></label>
									<select name="<?php echo esc_attr( ESS_OPTION_KEY ); ?>[placement]" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;">
										<option value="below" <?php selected( 'below', $settings['placement'] ); ?>><?php esc_html_e( 'Below Content (Standard)', 'earthin-social-share' ); ?></option>
										<option value="above" <?php selected( 'above', $settings['placement'] ); ?>><?php esc_html_e( 'Above Content', 'earthin-social-share' ); ?></option>
										<option value="both" <?php selected( 'both', $settings['placement'] ); ?>><?php esc_html_e( 'Both Above & Below Content', 'earthin-social-share' ); ?></option>
										<option value="manual" <?php selected( 'manual', $settings['placement'] ); ?>><?php esc_html_e( 'Manual (Shortcode only)', 'earthin-social-share' ); ?></option>
									</select>
								</div>

								<div>
									<label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px;"><?php esc_html_e( 'Section Headline', 'earthin-social-share' ); ?></label>
									<input type="text" name="<?php echo esc_attr( ESS_OPTION_KEY ); ?>[headline]" value="<?php echo esc_attr( $settings['headline'] ); ?>" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px;">
								</div>
							</div>

							<!-- Post Types -->
							<div>
								<label style="font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:8px;"><?php esc_html_e( 'Enabled Post Types', 'earthin-social-share' ); ?></label>
								<div style="display:flex; flex-wrap:wrap; gap:10px;">
									<?php foreach ( $available_post_types as $pt_slug => $pt_obj ) : ?>
										<label style="display:flex; align-items:center; gap:8px; padding:8px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; cursor:pointer;">
											<input type="checkbox" name="<?php echo esc_attr( ESS_OPTION_KEY ); ?>[post_types][]" value="<?php echo esc_attr( $pt_slug ); ?>" <?php checked( in_array( $pt_slug, (array) $settings['post_types'], true ) ); ?>>
											<span style="font-size:13px; font-weight:500; color:#1e293b;"><?php echo esc_html( $pt_obj->labels->name ); ?></span>
											<code style="font-size:11px; color:#64748b; background:#e2e8f0; padding:1px 4px; border-radius:3px;"><?php echo esc_html( $pt_slug ); ?></code>
										</label>
									<?php endforeach; ?>
								</div>
							</div>
						</div>

						<!-- Live Button Appearance Preview -->
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
							<h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
								<span>👁️</span> <?php esc_html_e( 'Front-End Appearance Preview', 'earthin-social-share' ); ?>
							</h2>
							<div style="background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px; padding:18px;">
								<?php if ( ! empty( $settings['headline'] ) ) : ?>
									<div style="font-size:13px; font-weight:600; color:#334155; margin-bottom:10px;">
										<?php echo esc_html( $settings['headline'] ); ?>
									</div>
								<?php endif; ?>
								<div style="display:flex; flex-wrap:wrap; gap:8px;">
									<?php foreach ( (array) $settings['networks'] as $net ) :
										if ( ! isset( $all_networks[ $net ] ) ) continue;
										$nd = $all_networks[ $net ];
										?>
										<div style="display:inline-flex; align-items:center; gap:6px; background:<?php echo esc_attr( $nd['color'] ); ?>; color:#fff; font-size:12px; font-weight:600; padding:6px 14px; border-radius:6px; box-shadow:0 1px 3px rgba(0,0,0,0.12);">
											<span><?php echo esc_html( $nd['icon'] ); ?></span>
											<span><?php echo esc_html( $nd['name'] ); ?></span>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>

						<button type="submit" class="button button-primary" style="background:#059669; border-color:#047857; color:#fff; font-weight:600; padding:8px 24px; height:auto; border-radius:6px; font-size:14px; box-shadow:0 2px 4px rgba(5,150,105,0.25);">
							<?php esc_html_e( 'Save Share Settings', 'earthin-social-share' ); ?>
						</button>
					</div>

					<!-- Sidebar Info -->
					<div>
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:20px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:20px;">
							<h3 style="font-size:14px; font-weight:700; color:#0f172a; margin:0 0 10px;"><?php esc_html_e( 'Manual Shortcode', 'earthin-social-share' ); ?></h3>
							<p style="font-size:12px; color:#64748b; line-height:1.5; margin:0 0 8px;">
								<?php esc_html_e( 'You can place the social share buttons anywhere in your content, sidebar, or templates using:', 'earthin-social-share' ); ?>
							</p>
							<code style="display:block; background:#f1f5f9; padding:8px 12px; border-radius:6px; font-size:12px; color:#0f172a; user-select:all;">[earthin_social_share]</code>
						</div>

						<div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:18px;">
							<div style="font-size:13px; font-weight:700; color:#166534; margin-bottom:8px;">⚡ <?php esc_html_e( 'Zero Third-Party JS', 'earthin-social-share' ); ?></div>
							<p style="font-size:12px; color:#15803d; line-height:1.5; margin:0;">
								<?php esc_html_e( 'Unlike heavy social share plugins that load slow tracking iframes from Facebook and Twitter, Earthin generates direct native share intents with zero speed penalty.', 'earthin-social-share' ); ?>
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
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-social-share')) . '">' . __('Settings', 'earthin-social-share') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
