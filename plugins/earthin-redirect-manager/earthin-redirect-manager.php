<?php
/**
 * Plugin Name: Earthin Redirect Manager
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-redirect-manager
 * Description: Fast, loop-protected 301/302 URL redirect manager with hit tracking, CSV import/export, and zero database bloat.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: earthin-redirect-manager
 */

namespace Earthin\RedirectManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ERM_VERSION', '0.1.0' );
define( 'ERM_PATH', plugin_dir_path( __FILE__ ) );
define( 'ERM_URL', plugin_dir_url( __FILE__ ) );
define( 'ERM_OPTION_KEY', 'erm_redirect_rules' );

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
		add_action( 'template_redirect', array( $this, 'maybe_redirect' ), 1 );

		if ( is_admin() ) {
			add_action( 'admin_post_erm_add_redirect', array( $this, 'handle_add_redirect' ) );
			add_action( 'admin_post_erm_toggle_redirect', array( $this, 'handle_toggle_redirect' ) );
			add_action( 'admin_post_erm_delete_redirect', array( $this, 'handle_delete_redirect' ) );
			add_action( 'admin_post_erm_export_csv', array( $this, 'handle_export_csv' ) );
			add_action( 'admin_post_erm_import_csv', array( $this, 'handle_import_csv' ) );
		}
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'earthin-redirect-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function is_suite_redirects_active(): bool {
		if ( class_exists( '\\Earthin\\WPSuite\\Plugin' ) ) {
			$suite_settings = get_option( 'ews_settings', array() );
			return ! empty( $suite_settings['redirects_enabled'] );
		}
		return false;
	}

	public function register_admin_menu(): void {
		add_management_page(
			__( 'Earthin Redirects', 'earthin-redirect-manager' ),
			__( 'Earthin Redirects', 'earthin-redirect-manager' ),
			'manage_options',
			'earthin-redirect-manager',
			array( $this, 'render_admin_page' )
		);
	}

	public static function get_rules(): array {
		$rules = get_option( ERM_OPTION_KEY, array() );
		return is_array( $rules ) ? $rules : array();
	}

	public static function save_rules( array $rules ): bool {
		return update_option( ERM_OPTION_KEY, array_values( $rules ) );
	}

	public function maybe_redirect(): void {
		if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			return;
		}

		// Mutual conflict guard: Yield if Earthin WP Suite Redirects Module is active
		if ( $this->is_suite_redirects_active() ) {
			return;
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$parsed_path = wp_parse_url( $request_uri, PHP_URL_PATH );
		if ( ! $parsed_path ) {
			return;
		}

		$request_path = strtolower( '/' . trim( $parsed_path, '/' ) );
		$rules        = self::get_rules();
		$matched_idx  = null;
		$target_rule  = null;

		foreach ( $rules as $idx => $rule ) {
			if ( empty( $rule['enabled'] ) ) {
				continue;
			}
			$source_path = strtolower( '/' . trim( $rule['from'] ?? '', '/' ) );
			if ( $source_path === $request_path ) {
				$matched_idx = $idx;
				$target_rule = $rule;
				break;
			}
		}

		if ( null !== $target_rule && null !== $matched_idx ) {
			// Increment hit count
			$rules[ $matched_idx ]['hits'] = ( (int) ( $rules[ $matched_idx ]['hits'] ?? 0 ) ) + 1;
			$rules[ $matched_idx ]['last_hit'] = time();
			self::save_rules( $rules );

			$status = in_array( (int) $target_rule['status'], array( 301, 302, 307, 308 ), true ) ? (int) $target_rule['status'] : 301;
			$dest   = $target_rule['to'];

			if ( ! preg_match( '#^https?://#i', $dest ) ) {
				$dest = home_url( '/' . ltrim( $dest, '/' ) );
			}

			wp_safe_redirect( esc_url_raw( $dest ), $status );
			exit;
		}
	}

	public function handle_add_redirect(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'earthin-redirect-manager' ) );
		}
		check_admin_referer( 'erm_add_redirect_action', 'erm_nonce' );

		$from   = isset( $_POST['from'] ) ? sanitize_text_field( wp_unslash( $_POST['from'] ) ) : '';
		$to     = isset( $_POST['to'] ) ? esc_url_raw( trim( (string) wp_unslash( $_POST['to'] ) ) ) : '';
		$status = isset( $_POST['status'] ) ? (int) $_POST['status'] : 301;

		$from_clean = '/' . trim( $from, '/' );

		if ( '/' === $from_clean || empty( $from_clean ) || empty( $to ) ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'earthin-redirect-manager', 'erm_error' => 'empty_fields' ), admin_url( 'tools.php' ) ) );
			exit;
		}

		// Loop detection
		$to_path = wp_parse_url( $to, PHP_URL_PATH );
		if ( $to_path && strtolower( '/' . trim( $to_path, '/' ) ) === strtolower( $from_clean ) ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'earthin-redirect-manager', 'erm_error' => 'loop_detected' ), admin_url( 'tools.php' ) ) );
			exit;
		}

		$rules = self::get_rules();
		foreach ( $rules as $existing ) {
			if ( strtolower( '/' . trim( $existing['from'] ?? '', '/' ) ) === strtolower( $from_clean ) ) {
				wp_safe_redirect( add_query_arg( array( 'page' => 'earthin-redirect-manager', 'erm_error' => 'duplicate_source' ), admin_url( 'tools.php' ) ) );
				exit;
			}
		}

		$rules[] = array(
			'id'       => wp_generate_uuid4(),
			'from'     => $from_clean,
			'to'       => $to,
			'status'   => in_array( $status, array( 301, 302, 307, 308 ), true ) ? $status : 301,
			'enabled'  => 1,
			'hits'     => 0,
			'created'  => time(),
		);

		self::save_rules( $rules );
		wp_safe_redirect( add_query_arg( array( 'page' => 'earthin-redirect-manager', 'erm_success' => 'added' ), admin_url( 'tools.php' ) ) );
		exit;
	}

	public function handle_toggle_redirect(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'earthin-redirect-manager' ) );
		}
		$id = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
		check_admin_referer( 'erm_toggle_' . $id );

		$rules = self::get_rules();
		foreach ( $rules as &$rule ) {
			if ( ( $rule['id'] ?? '' ) === $id ) {
				$rule['enabled'] = empty( $rule['enabled'] ) ? 1 : 0;
				break;
			}
		}

		self::save_rules( $rules );
		wp_safe_redirect( add_query_arg( array( 'page' => 'earthin-redirect-manager', 'erm_success' => 'updated' ), admin_url( 'tools.php' ) ) );
		exit;
	}

	public function handle_delete_redirect(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'earthin-redirect-manager' ) );
		}
		$id = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
		check_admin_referer( 'erm_delete_' . $id );

		$rules = self::get_rules();
		$new_rules = array();
		foreach ( $rules as $rule ) {
			if ( ( $rule['id'] ?? '' ) !== $id ) {
				$new_rules[] = $rule;
			}
		}

		self::save_rules( $new_rules );
		wp_safe_redirect( add_query_arg( array( 'page' => 'earthin-redirect-manager', 'erm_success' => 'deleted' ), admin_url( 'tools.php' ) ) );
		exit;
	}

	public function handle_export_csv(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'earthin-redirect-manager' ) );
		}
		check_admin_referer( 'erm_export_csv_action' );

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=earthin-redirects-' . gmdate( 'Y-m-d' ) . '.csv' );

		$output = fopen( 'php://output', 'w' );
		fputcsv( $output, array( 'Source Path', 'Target URL', 'Status Code', 'Hits', 'Enabled' ) );

		$rules = self::get_rules();
		foreach ( $rules as $rule ) {
			fputcsv( $output, array(
				$rule['from'] ?? '',
				$rule['to'] ?? '',
				$rule['status'] ?? 301,
				$rule['hits'] ?? 0,
				! empty( $rule['enabled'] ) ? 'yes' : 'no',
			) );
		}
		fclose( $output );
		exit;
	}

	public function handle_import_csv(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'earthin-redirect-manager' ) );
		}
		check_admin_referer( 'erm_import_csv_action', 'erm_import_nonce' );

		if ( empty( $_FILES['csv_file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['csv_file']['tmp_name'] ) ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'earthin-redirect-manager', 'erm_error' => 'no_file' ), admin_url( 'tools.php' ) ) );
			exit;
		}

		$handle = fopen( $_FILES['csv_file']['tmp_name'], 'r' );
		if ( ! $handle ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'earthin-redirect-manager', 'erm_error' => 'file_read_error' ), admin_url( 'tools.php' ) ) );
			exit;
		}

		$header = fgetcsv( $handle );
		$rules = self::get_rules();
		$imported = 0;

		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			if ( count( $row ) < 2 ) {
				continue;
			}
			$from   = '/' . trim( sanitize_text_field( $row[0] ), '/' );
			$to     = esc_url_raw( trim( $row[1] ) );
			$status = isset( $row[2] ) && in_array( (int) $row[2], array( 301, 302, 307, 308 ), true ) ? (int) $row[2] : 301;

			if ( empty( $from ) || empty( $to ) || '/' === $from ) {
				continue;
			}

			// Avoid loops
			$to_path = wp_parse_url( $to, PHP_URL_PATH );
			if ( $to_path && strtolower( '/' . trim( $to_path, '/' ) ) === strtolower( $from ) ) {
				continue;
			}

			// Add or update
			$found = false;
			foreach ( $rules as &$existing ) {
				if ( strtolower( '/' . trim( $existing['from'] ?? '', '/' ) ) === strtolower( $from ) ) {
					$existing['to']     = $to;
					$existing['status'] = $status;
					$found = true;
					$imported++;
					break;
				}
			}
			if ( ! $found ) {
				$rules[] = array(
					'id'      => wp_generate_uuid4(),
					'from'    => $from,
					'to'      => $to,
					'status'  => $status,
					'enabled' => 1,
					'hits'    => 0,
					'created' => time(),
				);
				$imported++;
			}
		}
		fclose( $handle );

		self::save_rules( $rules );
		wp_safe_redirect( add_query_arg( array( 'page' => 'earthin-redirect-manager', 'erm_imported' => $imported ), admin_url( 'tools.php' ) ) );
		exit;
	}

	public function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'earthin-redirect-manager' ) );
		}

		$rules = self::get_rules();
		$suite_active = $this->is_suite_redirects_active();

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Earthin Redirect Manager', 'earthin-redirect-manager' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Create and manage performant 301/302 URL redirects with automated loop prevention, hit tracking, and CSV import/export.', 'earthin-redirect-manager' ); ?>
			</p>

			<?php if ( $suite_active ) : ?>
				<div class="notice notice-info inline" style="margin-top:16px;">
					<p>
						<strong><?php esc_html_e( 'Mutual Conflict Guard Active:', 'earthin-redirect-manager' ); ?></strong>
						<?php esc_html_e( 'Earthin WP Suite Redirects Module is active. Standalone frontend interception is safely bypassed to avoid duplicate execution.', 'earthin-redirect-manager' ); ?>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $_GET['erm_error'] ) ) : ?>
				<div class="notice notice-error is-dismissible">
					<p>
						<?php
						$err = sanitize_key( $_GET['erm_error'] );
						if ( 'loop_detected' === $err ) {
							esc_html_e( 'Redirect loop detected: Source path and target URL resolve to the same location.', 'earthin-redirect-manager' );
						} elseif ( 'duplicate_source' === $err ) {
							esc_html_e( 'A redirect rule for this source path already exists.', 'earthin-redirect-manager' );
						} else {
							esc_html_e( 'Please check your inputs and try again.', 'earthin-redirect-manager' );
						}
						?>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $_GET['erm_imported'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php printf( esc_html__( 'Successfully imported %d redirect rules from CSV.', 'earthin-redirect-manager' ), (int) $_GET['erm_imported'] ); ?></p>
				</div>
			<?php endif; ?>

		$total_rules = count( $rules );
		?>
		<div class="earthin-admin-wrap">
			<style>
				.earthin-admin-wrap { max-width: 1240px; margin: 20px 20px 0 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
				.earthin-header { background: #0f172a; color: #ffffff; padding: 22px 28px; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08); flex-wrap: wrap; gap: 16px; }
				.earthin-header-left { display: flex; align-items: center; gap: 14px; }
				.earthin-logo-badge { background: #059669; color: #ffffff; font-weight: 800; font-size: 14px; padding: 6px 12px; border-radius: 8px; letter-spacing: 0.04em; display: inline-flex; align-items: center; gap: 6px; }
				.earthin-title-group h1 { color: #ffffff; margin: 0; font-size: 20px; font-weight: 700; line-height: 1.2; display: flex; align-items: center; gap: 10px; }
				.earthin-version-tag { font-size: 11px; background: rgba(255,255,255,0.15); color: #cbd5e1; padding: 2px 8px; border-radius: 999px; font-weight: 600; }
				.earthin-title-group p { margin: 4px 0 0; color: #94a3b8; font-size: 13px; }
				.earthin-header-links { display: flex; gap: 10px; align-items: center; }
				.earthin-link-btn { background: rgba(255,255,255,0.08); color: #e2e8f0 !important; text-decoration: none !important; font-size: 12px; font-weight: 600; padding: 7px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.12); transition: all 0.15s ease; }
				.earthin-link-btn:hover { background: rgba(255,255,255,0.18); color: #ffffff !important; }
				.earthin-card { background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); padding: 24px 28px; margin-bottom: 24px; }
				.earthin-card-header { padding-bottom: 14px; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
				.earthin-card-title { font-size: 16px; font-weight: 700; color: #0f172a; margin: 0; }
				.earthin-btn-primary { background: #059669 !important; border-color: #059669 !important; color: #ffffff !important; font-weight: 700 !important; padding: 8px 22px !important; border-radius: 8px !important; box-shadow: 0 2px 4px rgba(5,150,105,0.2) !important; text-shadow: none !important; cursor: pointer; }
				.earthin-btn-primary:hover { background: #047857 !important; }
				.earthin-btn-secondary { background: #f8fafc !important; border: 1px solid #cbd5e1 !important; color: #334155 !important; font-weight: 700 !important; padding: 7px 16px !important; border-radius: 8px !important; text-shadow: none !important; cursor: pointer; text-decoration: none; display: inline-block; }
				.earthin-badge-pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
				.earthin-badge-success { background: #ecfdf5; color: #059669; }
				.earthin-badge-neutral { background: #f1f5f9; color: #64748b; }
				.earthin-input { border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 14px; box-sizing: border-box; }
				.earthin-input:focus { border-color: #059669; outline: none; box-shadow: 0 0 0 3px rgba(5,150,105,0.15); }
				.earthin-table { width: 100%; border-collapse: collapse; }
				.earthin-table th { background: #f8fafc; padding: 12px 14px; font-size: 12px; font-weight: 700; color: #475569; text-align: left; border-bottom: 2px solid #e2e8f0; }
				.earthin-table td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #334155; }
				.earthin-table tr:hover td { background: #fafbfc; }
			</style>

			<header class="earthin-header">
				<div class="earthin-header-left">
					<span class="earthin-logo-badge">🌱 Earthin</span>
					<div class="earthin-title-group">
						<h1>
							<?php echo esc_html__( 'Earthin Redirect Manager', 'earthin-redirect-manager' ); ?>
							<span class="earthin-version-tag">v1.0.0</span>
							<span class="earthin-badge-pill earthin-badge-success"><?php echo sprintf( esc_html__( '%d Active Rules', 'earthin-redirect-manager' ), $total_rules ); ?></span>
						</h1>
						<p><?php echo esc_html__( 'Fast 301, 302, 307 & 308 URL redirection engine without .htaccess edits by Shaurya Joshi.', 'earthin-redirect-manager' ); ?></p>
					</div>
				</div>
				<div class="earthin-header-links">
					<a href="https://plugins.earthin.online" target="_blank" class="earthin-link-btn"><?php echo esc_html__( 'Plugin Hub', 'earthin-redirect-manager' ); ?> &nearr;</a>
					<a href="https://plugins.earthin.online/docs" target="_blank" class="earthin-link-btn"><?php echo esc_html__( 'Documentation', 'earthin-redirect-manager' ); ?></a>
					<a href="https://plugins.earthin.online/support" target="_blank" class="earthin-link-btn"><?php echo esc_html__( 'Support', 'earthin-redirect-manager' ); ?></a>
				</div>
			</header>

			<!-- Add New Rule Form -->
			<div class="earthin-card">
				<div class="earthin-card-header">
					<h2 class="earthin-card-title"><?php esc_html_e( 'Create URL Redirection Rule', 'earthin-redirect-manager' ); ?></h2>
				</div>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="erm_add_redirect">
					<?php wp_nonce_field( 'erm_add_redirect_action', 'erm_nonce' ); ?>
					<div style="display:flex; gap:16px; flex-wrap:wrap; align-items:flex-end;">
						<div style="flex:1; min-width:220px;">
							<label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;"><?php esc_html_e( 'Source Relative Path', 'earthin-redirect-manager' ); ?></label>
							<input type="text" name="from" placeholder="/old-page-url" class="earthin-input" required style="width:100%;">
						</div>
						<div style="flex:2; min-width:280px;">
							<label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;"><?php esc_html_e( 'Target Destination Path or URL', 'earthin-redirect-manager' ); ?></label>
							<input type="text" name="to" placeholder="/new-page-url or https://destination.com" class="earthin-input" required style="width:100%;">
						</div>
						<div style="width:160px;">
							<label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;"><?php esc_html_e( 'HTTP Status', 'earthin-redirect-manager' ); ?></label>
							<select name="status" class="earthin-input" style="width:100%;">
								<option value="301"><?php esc_html_e( '301 Permanent', 'earthin-redirect-manager' ); ?></option>
								<option value="302"><?php esc_html_e( '302 Temporary', 'earthin-redirect-manager' ); ?></option>
								<option value="307"><?php esc_html_e( '307 Temporary', 'earthin-redirect-manager' ); ?></option>
								<option value="308"><?php esc_html_e( '308 Permanent', 'earthin-redirect-manager' ); ?></option>
							</select>
						</div>
						<div>
							<input type="submit" class="button earthin-btn-primary" value="<?php esc_attr_e( 'Add Redirect', 'earthin-redirect-manager' ); ?>" />
						</div>
					</div>
				</form>
			</div>

			<!-- Active Rules Table -->
			<div class="earthin-card" style="padding:0; overflow:hidden;">
				<div style="padding:20px 24px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
					<h2 class="earthin-card-title"><?php printf( esc_html__( 'Active Redirects (%d)', 'earthin-redirect-manager' ), $total_rules ); ?></h2>
					<div style="display:flex; gap:8px;">
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=erm_export_csv' ), 'erm_export_csv_action' ) ); ?>" class="button earthin-btn-secondary" style="font-size:12px !important;">
							&darr; <?php esc_html_e( 'Export CSV', 'earthin-redirect-manager' ); ?>
						</a>
					</div>
				</div>

				<div style="overflow-x:auto;">
					<table class="earthin-table">
						<thead>
							<tr>
								<th style="width:260px;"><?php esc_html_e( 'Source Path', 'earthin-redirect-manager' ); ?></th>
								<th><?php esc_html_e( 'Target Destination', 'earthin-redirect-manager' ); ?></th>
								<th style="width:90px;"><?php esc_html_e( 'Code', 'earthin-redirect-manager' ); ?></th>
								<th style="width:90px;"><?php esc_html_e( 'Hits', 'earthin-redirect-manager' ); ?></th>
								<th style="width:100px;"><?php esc_html_e( 'Status', 'earthin-redirect-manager' ); ?></th>
								<th style="width:140px; text-align:right;"><?php esc_html_e( 'Actions', 'earthin-redirect-manager' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty( $rules ) ) : ?>
								<tr>
									<td colspan="6" style="text-align:center; padding:36px; color:#64748b;">
										<?php esc_html_e( 'No redirects configured yet. Add your first rule above.', 'earthin-redirect-manager' ); ?>
									</td>
								</tr>
							<?php else : ?>
								<?php foreach ( $rules as $rule ) : ?>
									<?php
									$rid = $rule['id'] ?? '';
									$toggle_url = wp_nonce_url( admin_url( 'admin-post.php?action=erm_toggle_redirect&id=' . $rid ), 'erm_toggle_' . $rid );
									$delete_url = wp_nonce_url( admin_url( 'admin-post.php?action=erm_delete_redirect&id=' . $rid ), 'erm_delete_' . $rid );
									?>
									<tr>
										<td><code style="background:#f1f5f9; padding:3px 8px; border-radius:6px;"><?php echo esc_html( $rule['from'] ?? '' ); ?></code></td>
										<td><a href="<?php echo esc_url( $rule['to'] ?? '' ); ?>" target="_blank" rel="noopener noreferrer" style="color:#059669; text-decoration:none;"><?php echo esc_html( $rule['to'] ?? '' ); ?> &nearr;</a></td>
										<td><span class="earthin-badge-pill earthin-badge-neutral"><?php echo (int) ( $rule['status'] ?? 301 ); ?></span></td>
										<td><strong><?php echo number_format_i18n( (int) ( $rule['hits'] ?? 0 ) ); ?></strong></td>
										<td>
											<?php if ( ! empty( $rule['enabled'] ) ) : ?>
												<span class="earthin-badge-pill earthin-badge-success"><?php esc_html_e( 'Active', 'earthin-redirect-manager' ); ?></span>
											<?php else : ?>
												<span class="earthin-badge-pill earthin-badge-neutral"><?php esc_html_e( 'Paused', 'earthin-redirect-manager' ); ?></span>
											<?php endif; ?>
										</td>
										<td style="text-align:right; white-space:nowrap;">
											<a href="<?php echo esc_url( $toggle_url ); ?>" style="color:#0284c7; text-decoration:none; font-weight:600; font-size:12px;">
												<?php echo ! empty( $rule['enabled'] ) ? esc_html__( 'Pause', 'earthin-redirect-manager' ) : esc_html__( 'Resume', 'earthin-redirect-manager' ); ?>
											</a> &bull; 
											<a href="<?php echo esc_url( $delete_url ); ?>" style="color:#ef4444; text-decoration:none; font-weight:600; font-size:12px;" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this redirect?', 'earthin-redirect-manager' ) ); ?>');">
												<?php esc_html_e( 'Delete', 'earthin-redirect-manager' ); ?>
											</a>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>

			<!-- CSV Import Box -->
			<div class="earthin-card">
				<div class="earthin-card-header">
					<h3 class="earthin-card-title"><?php esc_html_e( 'Bulk Import Rules via CSV', 'earthin-redirect-manager' ); ?></h3>
				</div>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
					<input type="hidden" name="action" value="erm_import_csv">
					<?php wp_nonce_field( 'erm_import_csv_action', 'erm_import_nonce' ); ?>
					<input type="file" name="csv_file" accept=".csv" required class="earthin-input" style="padding:6px 10px;">
					<input type="submit" class="button earthin-btn-secondary" value="<?php esc_attr_e( 'Upload & Import CSV', 'earthin-redirect-manager' ); ?>" />
				</form>
			</div>
		</div>
		<?php
	}
}

Plugin::instance();

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('tools.php?page=earthin-redirect-manager')) . '">' . __('Settings', 'earthin-redirect-manager') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
