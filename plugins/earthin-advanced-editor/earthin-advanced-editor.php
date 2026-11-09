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
		<div class="wrap" style="max-width:1100px; margin:20px auto 40px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,sans-serif;">
			<!-- Header Banner -->
			<div style="display:flex; align-items:center; justify-content:space-between; background:linear-gradient(135deg, #059669 0%, #047857 100%); padding:22px 28px; border-radius:12px; color:#fff; box-shadow:0 4px 18px rgba(5,150,105,0.22); margin-bottom:24px;">
				<div style="display:flex; align-items:center; gap:16px;">
					<div style="background:rgba(255,255,255,0.2); border-radius:12px; width:50px; height:50px; display:flex; align-items:center; justify-content:center; font-size:26px;">✍️</div>
					<div>
						<h1 style="color:#fff; margin:0; font-size:22px; font-weight:700; line-height:1.2;"><?php esc_html_e( 'Earthin Advanced Editor', 'earthin-advanced-editor' ); ?></h1>
						<p style="margin:4px 0 0; color:#d1fae5; font-size:13px;"><?php esc_html_e( 'Classic Editor typography controls, background highlight palette, & role-based toolbars', 'earthin-advanced-editor' ); ?></p>
					</div>
				</div>
				<div style="display:flex; align-items:center; gap:10px;">
					<span style="background:rgba(255,255,255,0.18); padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600; letter-spacing:0.3px;">v<?php echo esc_html( EAE_VERSION ); ?></span>
					<a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>" class="button" style="background:#fff; color:#047857; border:none; padding:6px 14px; border-radius:6px; font-size:12px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:5px; height:auto; line-height:1.4;">
						<span>+</span> <?php esc_html_e( 'Test in New Post', 'earthin-advanced-editor' ); ?>
					</a>
				</div>
			</div>

			<!-- Quick Stats / Feature Badges -->
			<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px; margin-bottom:24px;">
				<div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e( 'Active Tools', 'earthin-advanced-editor' ); ?></div>
					<div style="font-size:24px; font-weight:700; color:#059669; margin-top:4px;">
						<?php
						$active_count = 0;
						foreach ( array( 'enable_fonts', 'enable_font_sizes', 'enable_backcolor', 'enable_underline', 'enable_justify', 'enable_sub_sup' ) as $key ) {
							if ( ! empty( $settings[ $key ] ) ) {
								$active_count++;
							}
						}
						echo esc_html( $active_count ) . ' / 6';
						?>
					</div>
					<div style="font-size:12px; color:#94a3b8; margin-top:2px;"><?php esc_html_e( 'Toolbar additions enabled', 'earthin-advanced-editor' ); ?></div>
				</div>

				<div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e( 'Target Engine', 'earthin-advanced-editor' ); ?></div>
					<div style="font-size:20px; font-weight:700; color:#0f172a; margin-top:6px;">TinyMCE 4+</div>
					<div style="font-size:12px; color:#10b981; margin-top:2px; font-weight:500;">✓ <?php esc_html_e( 'Zero Bloat / Native Filter', 'earthin-advanced-editor' ); ?></div>
				</div>

				<div style="background:#fff; padding:18px 20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="font-size:11px; font-weight:600; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;"><?php esc_html_e( 'Role Permission', 'earthin-advanced-editor' ); ?></div>
					<div style="font-size:18px; font-weight:700; color:#0f172a; margin-top:8px;">
						<?php echo 'admin_editor' === $settings['role_restriction'] ? esc_html__( 'Admin & Editor', 'earthin-advanced-editor' ) : esc_html__( 'All Editors', 'earthin-advanced-editor' ); ?>
					</div>
					<div style="font-size:12px; color:#64748b; margin-top:2px;"><?php esc_html_e( 'Granular toolbar safety', 'earthin-advanced-editor' ); ?></div>
				</div>
			</div>

			<form method="post" action="options.php">
				<?php settings_fields( 'eae_settings_group' ); ?>

				<div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">
					<!-- Main Form Settings -->
					<div>
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
							<h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
								<span>🔤</span> <?php esc_html_e( 'Classic Editor Toolbar Buttons', 'earthin-advanced-editor' ); ?>
							</h2>
							<p style="color:#64748b; font-size:13px; margin:0 0 16px;">
								<?php esc_html_e( 'Toggle the enhanced buttons you want injected into TinyMCE Row 1 and Row 2:', 'earthin-advanced-editor' ); ?>
							</p>

							<div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
								<label style="display:flex; align-items:flex-start; gap:10px; padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer;">
									<input type="checkbox" style="margin-top:2px;" name="<?php echo esc_attr( EAE_OPTION_KEY ); ?>[enable_fonts]" value="1" <?php checked( 1, $settings['enable_fonts'] ); ?>>
									<div>
										<strong style="font-size:13px; color:#0f172a; display:block;"><?php esc_html_e( 'Font Family Picker', 'earthin-advanced-editor' ); ?></strong>
										<span style="font-size:11px; color:#64748b;">(fontselect) Arial, Georgia, Tahoma, etc.</span>
									</div>
								</label>

								<label style="display:flex; align-items:flex-start; gap:10px; padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer;">
									<input type="checkbox" style="margin-top:2px;" name="<?php echo esc_attr( EAE_OPTION_KEY ); ?>[enable_font_sizes]" value="1" <?php checked( 1, $settings['enable_font_sizes'] ); ?>>
									<div>
										<strong style="font-size:13px; color:#0f172a; display:block;"><?php esc_html_e( 'Font Size Selector', 'earthin-advanced-editor' ); ?></strong>
										<span style="font-size:11px; color:#64748b;">(fontsizeselect) Custom pt/px sizing</span>
									</div>
								</label>

								<label style="display:flex; align-items:flex-start; gap:10px; padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer;">
									<input type="checkbox" style="margin-top:2px;" name="<?php echo esc_attr( EAE_OPTION_KEY ); ?>[enable_backcolor]" value="1" <?php checked( 1, $settings['enable_backcolor'] ); ?>>
									<div>
										<strong style="font-size:13px; color:#0f172a; display:block;"><?php esc_html_e( 'Background Highlight', 'earthin-advanced-editor' ); ?></strong>
										<span style="font-size:11px; color:#64748b;">(backcolor) Text background marker</span>
									</div>
								</label>

								<label style="display:flex; align-items:flex-start; gap:10px; padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer;">
									<input type="checkbox" style="margin-top:2px;" name="<?php echo esc_attr( EAE_OPTION_KEY ); ?>[enable_underline]" value="1" <?php checked( 1, $settings['enable_underline'] ); ?>>
									<div>
										<strong style="font-size:13px; color:#0f172a; display:block;"><?php esc_html_e( 'Underline Tool', 'earthin-advanced-editor' ); ?></strong>
										<span style="font-size:11px; color:#64748b;">(underline) Dedicated underline button</span>
									</div>
								</label>

								<label style="display:flex; align-items:flex-start; gap:10px; padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer;">
									<input type="checkbox" style="margin-top:2px;" name="<?php echo esc_attr( EAE_OPTION_KEY ); ?>[enable_justify]" value="1" <?php checked( 1, $settings['enable_justify'] ); ?>>
									<div>
										<strong style="font-size:13px; color:#0f172a; display:block;"><?php esc_html_e( 'Justify Alignment', 'earthin-advanced-editor' ); ?></strong>
										<span style="font-size:11px; color:#64748b;">(alignjustify) Full block alignment</span>
									</div>
								</label>

								<label style="display:flex; align-items:flex-start; gap:10px; padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer;">
									<input type="checkbox" style="margin-top:2px;" name="<?php echo esc_attr( EAE_OPTION_KEY ); ?>[enable_sub_sup]" value="1" <?php checked( 1, $settings['enable_sub_sup'] ); ?>>
									<div>
										<strong style="font-size:13px; color:#0f172a; display:block;"><?php esc_html_e( 'Subscript & Superscript', 'earthin-advanced-editor' ); ?></strong>
										<span style="font-size:11px; color:#64748b;">(sub, sup) Formulas and citations</span>
									</div>
								</label>
							</div>
						</div>

						<!-- Typography Configuration -->
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
							<h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
								<span>📐</span> <?php esc_html_e( 'Custom Font Size Scale', 'earthin-advanced-editor' ); ?>
							</h2>
							<div style="margin-bottom:12px;">
								<label for="eae_font_sizes" style="font-weight:600; font-size:13px; color:#334155; display:block; margin-bottom:6px;">
									<?php esc_html_e( 'Available Sizes List', 'earthin-advanced-editor' ); ?>
								</label>
								<input type="text" id="eae_font_sizes" name="<?php echo esc_attr( EAE_OPTION_KEY ); ?>[font_sizes]" value="<?php echo esc_attr( $settings['font_sizes'] ); ?>" class="large-text" style="padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-family:monospace; font-size:13px;">
								<p style="font-size:12px; color:#64748b; margin-top:6px;">
									<?php esc_html_e( 'Space-separated pixel or rem values. Example: 11px 12px 14px 16px 18px 20px 24px 28px 32px 36px 48px', 'earthin-advanced-editor' ); ?>
								</p>
							</div>

							<!-- Size Badges Preview -->
							<div style="background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px; padding:12px; margin-top:14px;">
								<div style="font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; margin-bottom:8px;"><?php esc_html_e( 'Preview Defined Steps:', 'earthin-advanced-editor' ); ?></div>
								<div style="display:flex; flex-wrap:wrap; gap:6px;">
									<?php
									$sizes = explode( ' ', trim( $settings['font_sizes'] ) );
									foreach ( $sizes as $s ) {
										$s = trim( $s );
										if ( empty( $s ) ) continue;
										echo '<span style="background:#e0f2fe; color:#0369a1; padding:3px 8px; border-radius:4px; font-size:12px; font-family:monospace; font-weight:600;">' . esc_html( $s ) . '</span>';
									}
									?>
								</div>
							</div>
						</div>

						<!-- Access Control -->
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:24px;">
							<h2 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;">
								<span>🛡️</span> <?php esc_html_e( 'Role Access Control', 'earthin-advanced-editor' ); ?>
							</h2>
							<label for="eae_role" style="font-weight:600; font-size:13px; color:#334155; display:block; margin-bottom:6px;">
								<?php esc_html_e( 'Who can see advanced buttons?', 'earthin-advanced-editor' ); ?>
							</label>
							<select id="eae_role" name="<?php echo esc_attr( EAE_OPTION_KEY ); ?>[role_restriction]" style="padding:6px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px; max-width:320px; width:100%;">
								<option value="all" <?php selected( 'all', $settings['role_restriction'] ); ?>><?php esc_html_e( 'All Users With Post Editing Access', 'earthin-advanced-editor' ); ?></option>
								<option value="admin_editor" <?php selected( 'admin_editor', $settings['role_restriction'] ); ?>><?php esc_html_e( 'Administrators & Editors Only', 'earthin-advanced-editor' ); ?></option>
							</select>
							<p style="font-size:12px; color:#64748b; margin-top:6px;">
								<?php esc_html_e( 'Keep contributor and author toolbars clean and uncluttered by restricting custom formatting to senior editors.', 'earthin-advanced-editor' ); ?>
							</p>
						</div>

						<button type="submit" class="button button-primary" style="background:#059669; border-color:#047857; color:#fff; font-weight:600; padding:8px 24px; height:auto; border-radius:6px; font-size:14px; box-shadow:0 2px 4px rgba(5,150,105,0.25);">
							<?php esc_html_e( 'Save Advanced Editor Settings', 'earthin-advanced-editor' ); ?>
						</button>
					</div>

					<!-- Sidebar Info -->
					<div>
						<div style="background:#fff; border-radius:10px; border:1px solid #e2e8f0; padding:20px; box-shadow:0 2px 6px rgba(0,0,0,0.03); margin-bottom:20px;">
							<h3 style="font-size:14px; font-weight:700; color:#0f172a; margin:0 0 12px;"><?php esc_html_e( 'How It Works', 'earthin-advanced-editor' ); ?></h3>
							<p style="font-size:12px; color:#64748b; line-height:1.5; margin:0 0 10px;">
								<?php esc_html_e( 'WordPress bundles a full TinyMCE engine but hides several useful typography buttons by default.', 'earthin-advanced-editor' ); ?>
							</p>
							<p style="font-size:12px; color:#64748b; line-height:1.5; margin:0;">
								<?php esc_html_e( 'Earthin Advanced Editor enables these buttons natively without injecting heavy third-party JavaScript or slowing down your page load.', 'earthin-advanced-editor' ); ?>
							</p>
						</div>

						<div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:18px;">
							<div style="font-size:13px; font-weight:700; color:#166534; margin-bottom:8px;">💡 <?php esc_html_e( 'Pro Tip', 'earthin-advanced-editor' ); ?></div>
							<p style="font-size:12px; color:#15803d; line-height:1.5; margin:0 0 10px;">
								<?php esc_html_e( 'If you also want to restore the classic editor post screen completely, make sure the Earthin Classic Editor plugin is active.', 'earthin-advanced-editor' ); ?>
							</p>
							<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" style="font-size:12px; font-weight:600; color:#047857; text-decoration:underline;">
								<?php esc_html_e( 'Manage Plugins →', 'earthin-advanced-editor' ); ?>
							</a>
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
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=earthin-advanced-editor')) . '">' . __('Settings', 'earthin-advanced-editor') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
