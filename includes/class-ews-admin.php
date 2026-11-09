<?php
namespace Earthin\WPSuite;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Admin {
	private static $defaults = array(
		'seo_enabled'            => 1,
		'social_preview_enabled' => 1,
		'security_enabled'       => 0,
		'redirects_enabled'      => 0,
		'rss_enabled'            => 0,
		'schema_enabled'         => 0,
		'sitemap_enabled'        => 0,
		'forms_enabled'          => 0,
	);

	/**
	 * Get the default settings array.
	 *
	 * @return array
	 */
	public static function get_default_settings(): array {
		return self::$defaults;
	}

	public function boot() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'settings' ) );
	}

	public function menu() {
		add_menu_page(
			__( 'Earthin WP Suite', 'earthin-wp-suite' ),
			__( 'Earthin Suite', 'earthin-wp-suite' ),
			'manage_options',
			'earthin-wp-suite',
			array( $this, 'page' ),
			'dashicons-admin-tools',
			58
		);
	}

	public function settings() {
		register_setting(
			'ews_settings_group',
			'ews_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => self::$defaults,
			)
		);
	}

	public function sanitize( $input ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return get_option( 'ews_settings', self::$defaults );
		}
		$input = is_array( $input ) ? $input : array();
		$clean = array();
		foreach ( self::$defaults as $key => $default ) {
			$clean[ $key ] = empty( $input[ $key ] ) ? 0 : 1;
		}
		return $clean;
	}

	/**
	 * Main admin page router and container.
	 *
	 * @return void
	 */
	public function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access denied.', 'earthin-wp-suite' ) );
		}

		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'modules';
		$allowed_tabs = array( 'modules', 'redirects', 'forms', 'status' );
		if ( ! in_array( $current_tab, $allowed_tabs, true ) ) {
			$current_tab = 'modules';
		}

		$settings = wp_parse_args( get_option( 'ews_settings', array() ), self::$defaults );
		?>
		<div class="wrap ews-admin-wrap">
			<h1><?php esc_html_e( 'Earthin WP Suite', 'earthin-wp-suite' ); ?> <span class="ews-version-tag">v<?php echo esc_html( EWS_VERSION ); ?></span></h1>
			<p class="description"><?php esc_html_e( 'A unified, high-performance modular WordPress toolset by Earthin.', 'earthin-wp-suite' ); ?></p>

			<nav class="nav-tab-wrapper" style="margin-bottom: 20px;">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=earthin-wp-suite&tab=modules' ) ); ?>" class="nav-tab <?php echo 'modules' === $current_tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-admin-generic" style="vertical-align:text-top;margin-right:4px;"></span><?php esc_html_e( 'Modules & Controls', 'earthin-wp-suite' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=earthin-wp-suite&tab=redirects' ) ); ?>" class="nav-tab <?php echo 'redirects' === $current_tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-redo" style="vertical-align:text-top;margin-right:4px;"></span><?php esc_html_e( 'Redirects', 'earthin-wp-suite' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=earthin-wp-suite&tab=forms' ) ); ?>" class="nav-tab <?php echo 'forms' === $current_tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-feedback" style="vertical-align:text-top;margin-right:4px;"></span><?php esc_html_e( 'Form Inquiries', 'earthin-wp-suite' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=earthin-wp-suite&tab=status' ) ); ?>" class="nav-tab <?php echo 'status' === $current_tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-dashboard" style="vertical-align:text-top;margin-right:4px;"></span><?php esc_html_e( 'System Status', 'earthin-wp-suite' ); ?>
				</a>
			</nav>

			<div class="ews-tab-content">
				<?php
				switch ( $current_tab ) {
					case 'redirects':
						$this->render_redirects_tab();
						break;
					case 'forms':
						$this->render_forms_tab();
						break;
					case 'status':
						$this->render_status_tab( $settings );
						break;
					case 'modules':
					default:
						$this->render_modules_tab( $settings );
						break;
				}
				?>
			</div>
		</div>

		<style>
			.ews-admin-wrap .nav-tab { font-weight: 500; }
			.ews-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
			.ews-badge-active { background: #d1fae5; color: #065f46; }
			.ews-badge-inactive { background: #f3f4f6; color: #6b7280; }
			.ews-card { background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px; margin-bottom: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
			.ews-version-tag { font-size: 13px; font-weight: normal; color: #6b7280; background: #e5e7eb; padding: 2px 8px; border-radius: 12px; margin-left: 6px; }
			.ews-code-preview { background: #f8fafc; border: 1px solid #e2e8f0; padding: 2px 6px; border-radius: 3px; font-family: monospace; font-size: 12px; }
		</style>
		<?php
	}

	/**
	 * Tab 1: Modules and core toggles.
	 *
	 * @param array $s Current settings.
	 * @return void
	 */
	private function render_modules_tab( array $s ) {
		$modules = array(
			'seo_enabled'            => array(
				'title'       => __( 'SEO Metadata & Canonical URLs', 'earthin-wp-suite' ),
				'description' => __( 'Automatic document title, robots directives, meta descriptions, and canonical tags for singular, archive, and taxonomy pages.', 'earthin-wp-suite' ),
			),
			'social_preview_enabled' => array(
				'title'       => __( 'Social Previews (Open Graph & X Cards)', 'earthin-wp-suite' ),
				'description' => __( 'High-resolution Open Graph and Twitter/X card preview meta with featured image fallback and custom image override.', 'earthin-wp-suite' ),
			),
			'security_enabled'       => array(
				'title'       => __( 'Security Basics & XML-RPC Disable', 'earthin-wp-suite' ),
				'description' => __( 'Enforces nosniff, strict-origin referrer policy, camera/geo permissions policy, and disables legacy XML-RPC pingbacks.', 'earthin-wp-suite' ),
			),
			'redirects_enabled'      => array(
				'title'       => __( 'Exact-Path Redirect Engine', 'earthin-wp-suite' ),
				'description' => __( 'Recovers 404 broken URLs and reroutes traffic using 301, 302, 307, or 308 codes with hit counting and loop guards.', 'earthin-wp-suite' ),
			),
			'rss_enabled'            => array(
				'title'       => __( 'RSS Import Queue', 'earthin-wp-suite' ),
				'description' => __( 'Safely imports external feeds into draft review queue with strict SSRF defense against internal networks.', 'earthin-wp-suite' ),
			),
			'schema_enabled'         => array(
				'title'       => __( 'Structured Data JSON-LD', 'earthin-wp-suite' ),
				'description' => __( 'Injects valid WebSite, WebPage, and BlogPosting schema markup with XSS script tag escaping and organization logo.', 'earthin-wp-suite' ),
			),
			'sitemap_enabled'        => array(
				'title'       => __( 'XML Sitemap Management', 'earthin-wp-suite' ),
				'description' => __( 'Configures and controls native WordPress XML sitemaps with automatic exclusion of private and queryable types.', 'earthin-wp-suite' ),
			),
			'forms_enabled'          => array(
				'title'       => __( 'Safe Contact Forms', 'earthin-wp-suite' ),
				'description' => __( 'Shortcode contact form [earthin_contact_form] with honeypot spam protection, CRLF injection defense, and database persistence.', 'earthin-wp-suite' ),
			),
		);

		$rss_status = isset( $_GET['ews_rss'] ) ? sanitize_text_field( wp_unslash( $_GET['ews_rss'] ) ) : '';
		$rss_count  = isset( $_GET['count'] ) ? (int) $_GET['count'] : 0;
		?>
		<?php if ( 'done' === $rss_status ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( sprintf( __( 'RSS import complete: %d items imported as drafts for editorial review.', 'earthin-wp-suite' ), $rss_count ) ); ?></p></div>
		<?php elseif ( 'invalid' === $rss_status ) : ?>
			<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Feed URL was rejected: invalid URL or private/reserved IP destination.', 'earthin-wp-suite' ); ?></p></div>
		<?php elseif ( 'failed' === $rss_status ) : ?>
			<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Failed to fetch or parse the RSS feed.', 'earthin-wp-suite' ); ?></p></div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'ews_settings_group' ); ?>
			<table class="widefat striped" style="max-width: 960px;">
				<thead>
					<tr>
						<th style="width: 280px;"><?php esc_html_e( 'Module', 'earthin-wp-suite' ); ?></th>
						<th><?php esc_html_e( 'Description', 'earthin-wp-suite' ); ?></th>
						<th style="width: 140px; text-align: center;"><?php esc_html_e( 'Status', 'earthin-wp-suite' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $modules as $key => $info ) :
					$is_enabled = ! empty( $s[ $key ] );
				?>
					<tr>
						<td>
							<strong><?php echo esc_html( $info['title'] ); ?></strong>
						</td>
						<td>
							<p class="description" style="margin:0;"><?php echo esc_html( $info['description'] ); ?></p>
						</td>
						<td style="text-align: center;">
							<label>
								<input type="checkbox" name="ews_settings[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $is_enabled ); ?>>
								<span class="ews-badge <?php echo $is_enabled ? 'ews-badge-active' : 'ews-badge-inactive'; ?>">
									<?php echo $is_enabled ? esc_html__( 'Active', 'earthin-wp-suite' ) : esc_html__( 'Disabled', 'earthin-wp-suite' ); ?>
								</span>
							</label>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php submit_button( __( 'Save Module Settings', 'earthin-wp-suite' ) ); ?>
		</form>

		<div class="ews-card" style="max-width: 920px; margin-top: 30px;">
			<h2><?php esc_html_e( 'Quick RSS Import to Drafts', 'earthin-wp-suite' ); ?></h2>
			<?php if ( ! empty( $s['rss_enabled'] ) ) : ?>
				<p class="description"><?php esc_html_e( 'Fetches up to 5 latest articles from an RSS/Atom source directly into your Draft queue. Protected against SSRF.', 'earthin-wp-suite' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:8px;align-items:center;">
					<input type="hidden" name="action" value="ews_import_rss">
					<?php wp_nonce_field( 'ews_import_rss' ); ?>
					<input type="url" name="ews_feed_url" class="regular-text" placeholder="https://example.com/feed.xml" required style="width: 400px;">
					<?php submit_button( __( 'Import to Drafts', 'earthin-wp-suite' ), 'secondary', 'submit', false ); ?>
				</form>
			<?php else : ?>
				<p class="description"><?php esc_html_e( 'The RSS module is currently disabled. Enable "RSS Import Queue" above to use this feature.', 'earthin-wp-suite' ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Tab 2: Redirect management.
	 *
	 * @return void
	 */
	private function render_redirects_tab() {
		$msg = isset( $_GET['ews_redirect_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['ews_redirect_msg'] ) ) : '';
		$err = isset( $_GET['err'] ) ? sanitize_text_field( wp_unslash( $_GET['err'] ) ) : '';
		$rules = Redirects_Module::get_rules();
		?>
		<?php if ( 'added' === $msg ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Redirect rule added successfully.', 'earthin-wp-suite' ); ?></p></div>
		<?php elseif ( 'deleted' === $msg ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Redirect rule deleted.', 'earthin-wp-suite' ); ?></p></div>
		<?php elseif ( 'error' === $msg && $err ) : ?>
			<div class="notice notice-error is-dismissible"><p><?php echo esc_html( $err ); ?></p></div>
		<?php endif; ?>

		<div class="ews-card" style="max-width: 960px;">
			<h2><?php esc_html_e( 'Add New Redirect Rule', 'earthin-wp-suite' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ews_add_redirect">
				<?php wp_nonce_field( 'ews_add_redirect' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="ews_redirect_from"><?php esc_html_e( 'Source Path', 'earthin-wp-suite' ); ?></label></th>
						<td>
							<input type="text" id="ews_redirect_from" name="ews_redirect_from" class="regular-text" placeholder="/old-slug" required>
							<p class="description"><?php esc_html_e( 'Relative path to redirect from (e.g. /old-article or /old-path/).', 'earthin-wp-suite' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="ews_redirect_to"><?php esc_html_e( 'Destination Target', 'earthin-wp-suite' ); ?></label></th>
						<td>
							<input type="text" id="ews_redirect_to" name="ews_redirect_to" class="regular-text" placeholder="/new-slug or https://example.com" required>
							<p class="description"><?php esc_html_e( 'Relative internal path or full external URL.', 'earthin-wp-suite' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="ews_redirect_status"><?php esc_html_e( 'Redirect Type', 'earthin-wp-suite' ); ?></label></th>
						<td>
							<select id="ews_redirect_status" name="ews_redirect_status">
								<option value="301"><?php esc_html_e( '301 Moved Permanently (SEO recommended)', 'earthin-wp-suite' ); ?></option>
								<option value="302"><?php esc_html_e( '302 Found / Temporary', 'earthin-wp-suite' ); ?></option>
								<option value="307"><?php esc_html_e( '307 Temporary Redirect', 'earthin-wp-suite' ); ?></option>
								<option value="308"><?php esc_html_e( '308 Permanent Redirect', 'earthin-wp-suite' ); ?></option>
							</select>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Add Redirect Rule', 'earthin-wp-suite' ), 'primary', 'submit', false ); ?>
			</form>
		</div>

		<h2><?php esc_html_e( 'Active Redirect Rules', 'earthin-wp-suite' ); ?></h2>
		<table class="widefat striped" style="max-width: 960px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Source Path', 'earthin-wp-suite' ); ?></th>
					<th><?php esc_html_e( 'Destination Target', 'earthin-wp-suite' ); ?></th>
					<th style="width: 90px; text-align: center;"><?php esc_html_e( 'Status', 'earthin-wp-suite' ); ?></th>
					<th style="width: 80px; text-align: center;"><?php esc_html_e( 'Hits', 'earthin-wp-suite' ); ?></th>
					<th style="width: 100px; text-align: right;"><?php esc_html_e( 'Actions', 'earthin-wp-suite' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $rules ) ) : ?>
				<tr>
					<td colspan="5" style="text-align: center; padding: 20px; color: #6b7280;">
						<?php esc_html_e( 'No redirect rules configured yet.', 'earthin-wp-suite' ); ?>
					</td>
				</tr>
			<?php else : ?>
				<?php foreach ( $rules as $index => $rule ) : ?>
					<tr>
						<td><code><?php echo esc_html( $rule['from'] ); ?></code></td>
						<td><code><?php echo esc_html( $rule['to'] ); ?></code></td>
						<td style="text-align: center;"><span class="ews-badge ews-badge-active"><?php echo esc_html( $rule['status'] ?? 301 ); ?></span></td>
						<td style="text-align: center;"><strong><?php echo esc_html( (int) ( $rule['hits'] ?? 0 ) ); ?></strong></td>
						<td style="text-align: right;">
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;" onsubmit="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this redirect?', 'earthin-wp-suite' ) ); ?>');">
								<input type="hidden" name="action" value="ews_delete_redirect">
								<input type="hidden" name="ews_redirect_index" value="<?php echo esc_attr( $index ); ?>">
								<?php wp_nonce_field( 'ews_delete_redirect' ); ?>
								<button type="submit" class="button button-small button-link-delete"><?php esc_html_e( 'Delete', 'earthin-wp-suite' ); ?></button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Tab 3: Form submissions viewer.
	 *
	 * @return void
	 */
	private function render_forms_tab() {
		$msg = isset( $_GET['ews_forms_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['ews_forms_msg'] ) ) : '';
		$submissions = Forms_Module::get_submissions( 100 );
		?>
		<?php if ( 'deleted' === $msg ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Form inquiry deleted.', 'earthin-wp-suite' ); ?></p></div>
		<?php endif; ?>

		<div style="display: flex; justify-content: space-between; align-items: center; max-width: 1000px; margin-bottom: 12px;">
			<p class="description" style="margin: 0;">
				<?php esc_html_e( 'Messages received from the [earthin_contact_form] shortcode saved securely in the database.', 'earthin-wp-suite' ); ?>
			</p>
			<?php if ( ! empty( $submissions ) ) : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="ews_export_submissions">
					<?php wp_nonce_field( 'ews_export_submissions' ); ?>
					<?php submit_button( __( 'Export to CSV', 'earthin-wp-suite' ), 'secondary', 'submit', false ); ?>
				</form>
			<?php endif; ?>
		</div>

		<table class="widefat striped" style="max-width: 1000px;">
			<thead>
				<tr>
					<th style="width: 140px;"><?php esc_html_e( 'Date', 'earthin-wp-suite' ); ?></th>
					<th style="width: 160px;"><?php esc_html_e( 'Sender', 'earthin-wp-suite' ); ?></th>
					<th style="width: 180px;"><?php esc_html_e( 'Email', 'earthin-wp-suite' ); ?></th>
					<th><?php esc_html_e( 'Message', 'earthin-wp-suite' ); ?></th>
					<th style="width: 90px; text-align: center;"><?php esc_html_e( 'Email Sent', 'earthin-wp-suite' ); ?></th>
					<th style="width: 80px; text-align: right;"><?php esc_html_e( 'Actions', 'earthin-wp-suite' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $submissions ) ) : ?>
				<tr>
					<td colspan="6" style="text-align: center; padding: 25px; color: #6b7280;">
						<?php esc_html_e( 'No contact form submissions recorded yet.', 'earthin-wp-suite' ); ?>
					</td>
				</tr>
			<?php else : ?>
				<?php foreach ( $submissions as $sub ) : ?>
					<tr>
						<td><small><?php echo esc_html( $sub['date'] ); ?></small></td>
						<td><strong><?php echo esc_html( $sub['name'] ); ?></strong></td>
						<td><a href="mailto:<?php echo esc_attr( $sub['email'] ); ?>"><?php echo esc_html( $sub['email'] ); ?></a></td>
						<td><span title="<?php echo esc_attr( $sub['message'] ); ?>"><?php echo esc_html( wp_trim_words( $sub['message'], 18 ) ); ?></span></td>
						<td style="text-align: center;">
							<span class="ews-badge <?php echo '1' === (string) $sub['dispatched'] ? 'ews-badge-active' : 'ews-badge-inactive'; ?>">
								<?php echo '1' === (string) $sub['dispatched'] ? esc_html__( 'Yes', 'earthin-wp-suite' ) : esc_html__( 'No', 'earthin-wp-suite' ); ?>
							</span>
						</td>
						<td style="text-align: right;">
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;" onsubmit="return confirm('<?php echo esc_js( __( 'Delete this inquiry permanently?', 'earthin-wp-suite' ) ); ?>');">
								<input type="hidden" name="action" value="ews_delete_submission">
								<input type="hidden" name="submission_id" value="<?php echo esc_attr( $sub['id'] ); ?>">
								<?php wp_nonce_field( 'ews_delete_submission' ); ?>
								<button type="submit" class="button button-small button-link-delete"><?php esc_html_e( 'Delete', 'earthin-wp-suite' ); ?></button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Tab 4: System status and third-party plugin diagnostics.
	 *
	 * @param array $s Current settings.
	 * @return void
	 */
	private function render_status_tab( array $s ) {
		global $wp_version;

		$php_ok = version_compare( PHP_VERSION, '8.1', '>=' );
		$wp_ok  = version_compare( $wp_version ?? '6.4', '6.4', '>=' );

		$detected_seo = array();
		if ( defined( 'WPSEO_VERSION' ) ) { $detected_seo[] = 'Yoast SEO (' . WPSEO_VERSION . ')'; }
		if ( defined( 'RANK_MATH_VERSION' ) ) { $detected_seo[] = 'Rank Math (' . RANK_MATH_VERSION . ')'; }
		if ( defined( 'AIOSEO_VERSION' ) ) { $detected_seo[] = 'All in One SEO (' . AIOSEO_VERSION . ')'; }
		if ( defined( 'SEOPRESS_VERSION' ) ) { $detected_seo[] = 'SEOPress (' . SEOPRESS_VERSION . ')'; }
		if ( defined( 'THE_SEO_FRAMEWORK_VERSION' ) ) { $detected_seo[] = 'The SEO Framework (' . THE_SEO_FRAMEWORK_VERSION . ')'; }

		$active_count = 0;
		foreach ( self::$defaults as $key => $val ) {
			if ( ! empty( $s[ $key ] ) ) { $active_count++; }
		}
		?>
		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; max-width: 1000px;">
			<div class="ews-card">
				<h3><?php esc_html_e( 'Environment Health', 'earthin-wp-suite' ); ?></h3>
				<table class="widefat striped">
					<tbody>
						<tr>
							<td><strong><?php esc_html_e( 'PHP Version', 'earthin-wp-suite' ); ?></strong></td>
							<td>
								<code><?php echo esc_html( PHP_VERSION ); ?></code>
								<span class="ews-badge <?php echo $php_ok ? 'ews-badge-active' : 'ews-badge-inactive'; ?>">
									<?php echo $php_ok ? esc_html__( 'Compatible (>=8.1)', 'earthin-wp-suite' ) : esc_html__( 'Incompatible', 'earthin-wp-suite' ); ?>
								</span>
							</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'WordPress Version', 'earthin-wp-suite' ); ?></strong></td>
							<td>
								<code><?php echo esc_html( $wp_version ?? '6.4+' ); ?></code>
								<span class="ews-badge <?php echo $wp_ok ? 'ews-badge-active' : 'ews-badge-inactive'; ?>">
									<?php echo $wp_ok ? esc_html__( 'Compatible (>=6.4)', 'earthin-wp-suite' ) : esc_html__( 'Needs update', 'earthin-wp-suite' ); ?>
								</span>
							</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Memory Limit', 'earthin-wp-suite' ); ?></strong></td>
							<td><code><?php echo esc_html( ini_get( 'memory_limit' ) ); ?></code></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Max Execution Time', 'earthin-wp-suite' ); ?></strong></td>
							<td><code><?php echo esc_html( ini_get( 'max_execution_time' ) ); ?>s</code></td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="ews-card">
				<h3><?php esc_html_e( 'Plugin Compatibility Diagnostics', 'earthin-wp-suite' ); ?></h3>
				<table class="widefat striped">
					<tbody>
						<tr>
							<td><strong><?php esc_html_e( 'Third-Party SEO Collision', 'earthin-wp-suite' ); ?></strong></td>
							<td>
								<?php if ( empty( $detected_seo ) ) : ?>
									<span class="ews-badge ews-badge-active"><?php esc_html_e( 'None detected', 'earthin-wp-suite' ); ?></span>
									<p class="description" style="margin:4px 0 0;"><?php esc_html_e( 'Earthin Suite is actively managing frontend SEO & Social metadata.', 'earthin-wp-suite' ); ?></p>
								<?php else : ?>
									<span class="ews-badge ews-badge-inactive"><?php esc_html_e( 'Suppressed', 'earthin-wp-suite' ); ?></span>
									<p class="description" style="margin:4px 0 0;">
										<?php echo esc_html( sprintf( __( 'Detected: %s. Earthin Suite gracefully yielded frontend SEO/Social tags to prevent duplicate meta.', 'earthin-wp-suite' ), implode( ', ', $detected_seo ) ) ); ?>
									</p>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Active Modules', 'earthin-wp-suite' ); ?></strong></td>
							<td>
								<strong><?php echo esc_html( $active_count ); ?></strong> / <?php echo esc_html( count( self::$defaults ) ); ?>
							</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Suite Version', 'earthin-wp-suite' ); ?></strong></td>
							<td><code><?php echo esc_html( EWS_VERSION ); ?></code></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}
}
