<?php
/**
 * Plugin Name: Earthin Classic Editor
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-classic-editor
 * Description: Enables the WordPress Classic Editor and TinyMCE writing experience with per-post-type and user-level editor controls.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: earthin-classic-editor
 */

namespace Earthin\ClassicEditor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ECE_VERSION', '0.1.0' );
define( 'ECE_PATH', plugin_dir_path( __FILE__ ) );
define( 'ECE_URL', plugin_dir_url( __FILE__ ) );
define( 'ECE_OPTION_KEY', 'earthin_classic_editor_settings' );

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

		// Core editor replacement filters
		add_filter( 'use_block_editor_for_post_type', array( $this, 'filter_use_block_editor_for_post_type' ), 100, 2 );
		add_filter( 'use_block_editor_for_post', array( $this, 'filter_use_block_editor_for_post' ), 100, 2 );
		add_filter( 'post_row_actions', array( $this, 'add_editor_row_actions' ), 10, 2 );
		add_filter( 'page_row_actions', array( $this, 'add_editor_row_actions' ), 10, 2 );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'earthin-classic-editor', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function get_default_settings(): array {
		return array(
			'default_editor' => 'classic', // 'classic' or 'block'
			'allow_users'    => 1,
			'post_types'     => array( 'post', 'page' ),
		);
	}

	public function get_settings(): array {
		$saved = get_option( ECE_OPTION_KEY, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), $this->get_default_settings() );
	}

	public function filter_use_block_editor_for_post_type( bool $use_block_editor, string $post_type ): bool {
		$settings = $this->get_settings();
		$enabled_types = is_array( $settings['post_types'] ) ? $settings['post_types'] : array();

		if ( ! in_array( $post_type, $enabled_types, true ) ) {
			return $use_block_editor;
		}

		if ( 'classic' === $settings['default_editor'] ) {
			return false;
		}

		return $use_block_editor;
	}

	public function filter_use_block_editor_for_post( bool $use_block_editor, $post ): bool {
		if ( ! $post instanceof \WP_Post ) {
			return $use_block_editor;
		}

		$settings = $this->get_settings();
		if ( empty( $settings['allow_users'] ) ) {
			return $this->filter_use_block_editor_for_post_type( $use_block_editor, $post->post_type );
		}

		// Check explicit query parameter override
		if ( isset( $_GET['classic-editor'] ) ) {
			return false;
		}
		if ( isset( $_GET['block-editor'] ) ) {
			return true;
		}

		// Check per-post remembered editor
		$which = get_post_meta( $post->ID, '_earthin_which_editor', true );
		if ( 'classic' === $which ) {
			return false;
		}
		if ( 'block' === $which ) {
			return true;
		}

		return $this->filter_use_block_editor_for_post_type( $use_block_editor, $post->post_type );
	}

	public function add_editor_row_actions( array $actions, \WP_Post $post ): array {
		$settings = $this->get_settings();
		if ( empty( $settings['allow_users'] ) ) {
			return $actions;
		}

		$post_type_obj = get_post_type_object( $post->post_type );
		if ( ! $post_type_obj || ! current_user_can( $post_type_obj->cap->edit_post, $post->ID ) ) {
			return $actions;
		}

		$classic_url = add_query_arg( array(
			'post'           => $post->ID,
			'action'         => 'edit',
			'classic-editor' => '',
		), admin_url( 'post.php' ) );

		$block_url = add_query_arg( array(
			'post'         => $post->ID,
			'action'       => 'edit',
			'block-editor' => '',
		), admin_url( 'post.php' ) );

		$actions['classic_edit'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $classic_url ),
			esc_html__( 'Edit (Classic)', 'earthin-classic-editor' )
		);

		$actions['block_edit'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $block_url ),
			esc_html__( 'Edit (Block)', 'earthin-classic-editor' )
		);

		return $actions;
	}

	public function register_admin_menu(): void {
		add_options_page(
			__( 'Earthin Classic Editor', 'earthin-classic-editor' ),
			__( 'Earthin Editor', 'earthin-classic-editor' ),
			'manage_options',
			'earthin-classic-editor',
			array( $this, 'render_admin_page' )
		);
	}

	public function register_settings(): void {
		register_setting(
			'ece_settings_group',
			ECE_OPTION_KEY,
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
		$editor = isset( $input['default_editor'] ) ? sanitize_key( $input['default_editor'] ) : 'classic';
		$output['default_editor'] = in_array( $editor, array( 'classic', 'block' ), true ) ? $editor : 'classic';
		$output['allow_users']    = ! empty( $input['allow_users'] ) ? 1 : 0;

		$types = isset( $input['post_types'] ) && is_array( $input['post_types'] )
			? array_map( 'sanitize_key', $input['post_types'] )
			: array( 'post', 'page' );
		$output['post_types'] = array_values( array_unique( $types ) );

		return $output;
	}

	public function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'earthin-classic-editor' ) );
		}

		$settings = $this->get_settings();
		$available_post_types = get_post_types( array( 'show_ui' => true ), 'objects' );
		?>
		<div class="wrap" style="max-width:1100px; margin:20px auto 40px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,sans-serif;">
			<!-- Header Banner -->
			<div style="display:flex; align-items:center; justify-content:space-between; background:linear-gradient(135deg, #059669 0%, #047857 100%); padding:22px 28px; border-radius:12px; color:#fff; box-shadow:0 4px 18px rgba(5,150,105,0.22); margin-bottom:24px;">
				<div style="display:flex; align-items:center; gap:16px;">
					<div style="background:rgba(255,255,255,0.2); border-radius:12px; width:50px; height:50px; display:flex; align-items:center; justify-content:center; font-size:26px;">📝</div>
					<div>
						<h1 style="color:#fff; margin:0; font-size:22px; font-weight:700; line-height:1.2;"><?php esc_html_e( 'Earthin Classic Editor', 'earthin-classic-editor' ); ?></h1>
						<p style="margin:4px 0 0; color:#d1fae5; font-size:13px;"><?php esc_html_e( 'Restore the beloved Classic TinyMCE writing experience with granular post-type rules', 'earthin-classic-editor' ); ?></p>
					</div>
				</div>
				<div style="display:flex; align-items:center; gap:10px;">
					<span style="background:rgba(255,255,255,0.18); padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600;">v<?php echo esc_html( ECE_VERSION ); ?></span>
					<a href="https://plugins.earthin.online/docs" target="_blank" style="background:#fff; color:#047857; text-decoration:none; padding:6px 14px; border-radius:6px; font-size:12px; font-weight:600;">Documentation</a>
				</div>
			</div>

			<!-- Quick Stat Cards -->
			<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px; margin-bottom:24px;">
				<div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e( 'Default Editor Mode', 'earthin-classic-editor' ); ?></div>
					<div style="font-size:20px; font-weight:700; color:#059669; margin-top:4px;">
						<?php echo 'classic' === $settings['default_editor'] ? 'Classic TinyMCE' : 'Gutenberg Blocks'; ?>
					</div>
					<div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e( 'Active site-wide default', 'earthin-classic-editor' ); ?></div>
				</div>

				<div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e( 'User Switcher Links', 'earthin-classic-editor' ); ?></div>
					<div style="font-size:20px; font-weight:700; color:#0f172a; margin-top:4px;">
						<?php echo ! empty( $settings['allow_users'] ) ? '<span style="color:#10b981;">✓ Enabled</span>' : '<span style="color:#64748b;">Disabled</span>'; ?>
					</div>
					<div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e( 'Shows Edit (Classic) in row actions', 'earthin-classic-editor' ); ?></div>
				</div>

				<div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e( 'Active Post Types', 'earthin-classic-editor' ); ?></div>
					<div style="font-size:20px; font-weight:700; color:#0f172a; margin-top:4px;">
						<?php echo esc_html( count( (array) $settings['post_types'] ) ); ?> <?php esc_html_e( 'Types', 'earthin-classic-editor' ); ?>
					</div>
					<div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e( 'Posts, Pages & Custom Types', 'earthin-classic-editor' ); ?></div>
				</div>
			</div>

			<form method="post" action="options.php">
				<?php settings_fields( 'ece_settings_group' ); ?>

				<div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">
					<!-- Main Configuration Panel -->
					<div>
						<!-- Default Editor Selection -->
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
							<h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
								<span>🎯</span> <?php esc_html_e( 'Default Editor for All Users', 'earthin-classic-editor' ); ?>
							</h2>

							<div style="display:flex; flex-direction:column; gap:12px;">
								<label style="display:flex; align-items:flex-start; gap:12px; padding:16px; border:1px solid <?php echo 'classic' === $settings['default_editor'] ? '#059669' : '#e2e8f0'; ?>; background:<?php echo 'classic' === $settings['default_editor'] ? '#f0fdf4' : '#fff'; ?>; border-radius:8px; cursor:pointer;">
									<input type="radio" name="<?php echo esc_attr( ECE_OPTION_KEY ); ?>[default_editor]" value="classic" <?php checked( 'classic', $settings['default_editor'] ); ?> style="margin-top:3px;">
									<div>
										<strong style="font-size:14px; color:#0f172a; display:block;"><?php esc_html_e( 'Classic Editor (TinyMCE)', 'earthin-classic-editor' ); ?></strong>
										<span style="font-size:12px; color:#64748b; line-height:1.4; display:block; margin-top:2px;">
											<?php esc_html_e( 'Restores the traditional WordPress edit screen with reliable rich text editing, meta boxes, and speed.', 'earthin-classic-editor' ); ?>
										</span>
									</div>
								</label>

								<label style="display:flex; align-items:flex-start; gap:12px; padding:16px; border:1px solid <?php echo 'block' === $settings['default_editor'] ? '#059669' : '#e2e8f0'; ?>; background:<?php echo 'block' === $settings['default_editor'] ? '#f0fdf4' : '#fff'; ?>; border-radius:8px; cursor:pointer;">
									<input type="radio" name="<?php echo esc_attr( ECE_OPTION_KEY ); ?>[default_editor]" value="block" <?php checked( 'block', $settings['default_editor'] ); ?> style="margin-top:3px;">
									<div>
										<strong style="font-size:14px; color:#0f172a; display:block;"><?php esc_html_e( 'Block Editor (Gutenberg)', 'earthin-classic-editor' ); ?></strong>
										<span style="font-size:12px; color:#64748b; line-height:1.4; display:block; margin-top:2px;">
											<?php esc_html_e( 'Keeps modern WordPress block editor as primary default, with optional classic switching on demand.', 'earthin-classic-editor' ); ?>
										</span>
									</div>
								</label>
							</div>
						</div>

						<!-- User Switching Option -->
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
							<h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
								<span>🔄</span> <?php esc_html_e( 'User Flexibility', 'earthin-classic-editor' ); ?>
							</h2>

							<label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
								<input type="checkbox" name="<?php echo esc_attr( ECE_OPTION_KEY ); ?>[allow_users]" value="1" <?php checked( 1, $settings['allow_users'] ); ?>>
								<span style="font-size:13px; font-weight:600; color:#0f172a;">
									<?php esc_html_e( 'Allow authors to switch editors per post (shows "Edit (Classic)" and "Edit (Block)" action links)', 'earthin-classic-editor' ); ?>
								</span>
							</label>
							<p style="font-size:12px; color:#64748b; margin:6px 0 0 24px;">
								<?php esc_html_e( 'WordPress will also remember which editor was last opened for each post, giving writers seamless continuity.', 'earthin-classic-editor' ); ?>
							</p>
						</div>

						<!-- Post Types Selection -->
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
							<h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
								<span>📑</span> <?php esc_html_e( 'Target Post Types', 'earthin-classic-editor' ); ?>
							</h2>
							<p style="color:#64748b; font-size:13px; margin:0 0 16px;">
								<?php esc_html_e( 'Select which post types should be controlled by this plugin:', 'earthin-classic-editor' ); ?>
							</p>

							<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:10px;">
								<?php foreach ( $available_post_types as $pt_slug => $pt_obj ) : ?>
									<label style="display:flex; align-items:center; gap:8px; padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; cursor:pointer;">
										<input type="checkbox" name="<?php echo esc_attr( ECE_OPTION_KEY ); ?>[post_types][]" value="<?php echo esc_attr( $pt_slug ); ?>" <?php checked( in_array( $pt_slug, (array) $settings['post_types'], true ) ); ?>>
										<span style="font-size:13px; font-weight:500; color:#1e293b;"><?php echo esc_html( $pt_obj->labels->name ); ?></span>
										<code style="font-size:11px; color:#64748b; background:#e2e8f0; padding:1px 5px; border-radius:3px; margin-left:auto;"><?php echo esc_html( $pt_slug ); ?></code>
									</label>
								<?php endforeach; ?>
							</div>
						</div>

						<button type="submit" class="button button-primary" style="background:#059669; border-color:#047857; color:#fff; font-weight:600; padding:8px 24px; height:auto; border-radius:6px; font-size:14px; box-shadow:0 2px 4px rgba(5,150,105,0.25);">
							<?php esc_html_e( 'Save Editor Settings', 'earthin-classic-editor' ); ?>
						</button>
					</div>

					<!-- Sidebar Info -->
					<div>
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:20px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:20px;">
							<h3 style="font-size:14px; font-weight:700; color:#0f172a; margin:0 0 12px;"><?php esc_html_e( 'Why Classic Editor?', 'earthin-classic-editor' ); ?></h3>
							<ul style="font-size:12px; color:#64748b; line-height:1.6; margin:0; padding-left:16px;">
								<li><?php esc_html_e( 'Lightning fast editing without heavy JavaScript overhead.', 'earthin-classic-editor' ); ?></li>
								<li><?php esc_html_e( '100% backward compatible with older plugins & metaboxes.', 'earthin-classic-editor' ); ?></li>
								<li><?php esc_html_e( 'Familiar interface preferred by content teams & copywriters.', 'earthin-classic-editor' ); ?></li>
							</ul>
						</div>

						<div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:18px;">
							<div style="font-size:13px; font-weight:700; color:#166534; margin-bottom:8px;">💡 <?php esc_html_e( 'Pair with Advanced Editor', 'earthin-classic-editor' ); ?></div>
							<p style="font-size:12px; color:#15803d; line-height:1.5; margin:0;">
								<?php esc_html_e( 'Combine with Earthin Advanced Editor to unlock custom font sizes, text markers, and justification controls right in your Classic toolbar.', 'earthin-classic-editor' ); ?>
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
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-classic-editor')) . '">' . __('Settings', 'earthin-classic-editor') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
