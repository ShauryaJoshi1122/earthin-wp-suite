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
		<div class="wrap">
			<h1><?php esc_html_e( 'Earthin Classic Editor Settings', 'earthin-classic-editor' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Configure the default WordPress editing experience for posts, pages, and custom post types.', 'earthin-classic-editor' ); ?>
			</p>

			<form method="post" action="options.php" style="margin-top:20px;">
				<?php settings_fields( 'ece_settings_group' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Default Editor for All Users', 'earthin-classic-editor' ); ?></th>
						<td>
							<fieldset>
								<label style="display:block; margin-bottom:8px;">
									<input type="radio" name="<?php echo esc_attr( ECE_OPTION_KEY ); ?>[default_editor]" value="classic" <?php checked( 'classic', $settings['default_editor'] ); ?>>
									<strong><?php esc_html_e( 'Classic Editor (TinyMCE)', 'earthin-classic-editor' ); ?></strong>
									<span class="description" style="display:block; margin-left:24px;"><?php esc_html_e( 'Restores the traditional WordPress edit screen with rich text toolbar.', 'earthin-classic-editor' ); ?></span>
								</label>
								<label style="display:block;">
									<input type="radio" name="<?php echo esc_attr( ECE_OPTION_KEY ); ?>[default_editor]" value="block" <?php checked( 'block', $settings['default_editor'] ); ?>>
									<strong><?php esc_html_e( 'Block Editor (Gutenberg)', 'earthin-classic-editor' ); ?></strong>
									<span class="description" style="display:block; margin-left:24px;"><?php esc_html_e( 'Keeps modern WordPress block editor as the primary default.', 'earthin-classic-editor' ); ?></span>
								</label>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Allow Users to Switch Editors', 'earthin-classic-editor' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( ECE_OPTION_KEY ); ?>[allow_users]" value="1" <?php checked( 1, $settings['allow_users'] ); ?>>
								<?php esc_html_e( 'Yes, show "Edit (Classic)" and "Edit (Block)" links in post listing tables', 'earthin-classic-editor' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Enabled Post Types', 'earthin-classic-editor' ); ?></th>
						<td>
							<fieldset>
								<?php foreach ( $available_post_types as $pt_slug => $pt_obj ) : ?>
									<label style="display:inline-block; margin-right:16px; margin-bottom:8px;">
										<input type="checkbox" name="<?php echo esc_attr( ECE_OPTION_KEY ); ?>[post_types][]" value="<?php echo esc_attr( $pt_slug ); ?>" <?php checked( in_array( $pt_slug, (array) $settings['post_types'], true ) ); ?>>
										<?php echo esc_html( $pt_obj->labels->name ); ?> (<code><?php echo esc_html( $pt_slug ); ?></code>)
									</label>
								<?php endforeach; ?>
							</fieldset>
						</td>
					</tr>
				</table>

				<div style="margin-top:20px;">
					<?php submit_button( __( 'Save Editor Settings', 'earthin-classic-editor' ) ); ?>
				</div>
			</form>
		</div>
		<?php
	}
}

Plugin::instance();

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function() {
     = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-classic-editor')) . '">' . __('Settings', 'earthin-classic-editor') . '</a>';
    array_unshift(, );
    return ;
});
