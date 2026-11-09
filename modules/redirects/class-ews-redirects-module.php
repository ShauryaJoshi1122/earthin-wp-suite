<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class Redirects_Module implements Module_Interface {
	public function id() { return 'redirects'; }
	public function name() { return __( 'Redirects', 'earthin-wp-suite' ); }
	public function description() { return __( 'Manage exact-path redirects with hit counting and loop detection.', 'earthin-wp-suite' ); }
	public function is_enabled() { $s = get_option( 'ews_settings', array() ); return ! empty( $s['redirects_enabled'] ); }

	public function boot() {
		add_action( 'template_redirect', array( $this, 'maybe_redirect' ), 1 );

		if ( is_admin() ) {
			add_action( 'admin_post_ews_add_redirect', array( $this, 'handle_add_redirect' ) );
			add_action( 'admin_post_ews_delete_redirect', array( $this, 'handle_delete_redirect' ) );
		}
	}

	/**
	 * Get all configured redirect rules.
	 *
	 * @return array
	 */
	public static function get_rules(): array {
		$rules = get_option( 'ews_redirect_rules', array() );
		return is_array( $rules ) ? $rules : array();
	}

	/**
	 * Add a new redirect rule with validation.
	 *
	 * @param string $from Source relative path.
	 * @param string $to Target URL or relative path.
	 * @param int $status HTTP status code.
	 * @return true|string Returns true on success or an error string on failure.
	 */
	public static function add_rule( string $from, string $to, int $status = 301 ) {
		$from_clean = '/' . trim( sanitize_text_field( $from ), '/' );
		$to_clean   = trim( $to );

		if ( '/' === $from_clean || empty( $from_clean ) ) {
			return __( 'Source path cannot be empty or root.', 'earthin-wp-suite' );
		}

		if ( empty( $to_clean ) ) {
			return __( 'Destination target cannot be empty.', 'earthin-wp-suite' );
		}

		// Prevent redirect loop.
		$to_path = wp_parse_url( $to_clean, PHP_URL_PATH );
		if ( $to_path && strtolower( '/' . trim( $to_path, '/' ) ) === strtolower( $from_clean ) ) {
			return __( 'Redirect loop detected: source and destination resolve to the same path.', 'earthin-wp-suite' );
		}

		$allowed_statuses = array( 301, 302, 307, 308 );
		if ( ! in_array( $status, $allowed_statuses, true ) ) {
			$status = 301;
		}

		$rules = self::get_rules();

		// Check if source path already exists.
		foreach ( $rules as $existing ) {
			$existing_from = '/' . trim( $existing['from'] ?? '', '/' );
			if ( strtolower( $existing_from ) === strtolower( $from_clean ) ) {
				return __( 'A redirect rule for this source path already exists.', 'earthin-wp-suite' );
			}
		}

		$rules[] = array(
			'from'       => $from_clean,
			'to'         => $to_clean,
			'status'     => $status,
			'hits'       => 0,
			'created_at' => current_time( 'mysql' ),
		);

		update_option( 'ews_redirect_rules', $rules );
		return true;
	}

	/**
	 * Delete a redirect rule by array index.
	 *
	 * @param int $index Rule index.
	 * @return bool
	 */
	public static function delete_rule( int $index ): bool {
		$rules = self::get_rules();
		if ( ! isset( $rules[ $index ] ) ) {
			return false;
		}

		array_splice( $rules, $index, 1 );
		return update_option( 'ews_redirect_rules', $rules );
	}

	/**
	 * Increment hit count for a specific rule.
	 *
	 * @param int $index Rule index.
	 * @return void
	 */
	public static function increment_hits( int $index ) {
		$rules = self::get_rules();
		if ( isset( $rules[ $index ] ) ) {
			$rules[ $index ]['hits'] = isset( $rules[ $index ]['hits'] ) ? ( (int) $rules[ $index ]['hits'] + 1 ) : 1;
			update_option( 'ews_redirect_rules', $rules );
		}
	}

	/**
	 * Handle admin post action to add a redirect.
	 *
	 * @return void
	 */
	public function handle_add_redirect() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access denied.', 'earthin-wp-suite' ) );
		}

		check_admin_referer( 'ews_add_redirect' );

		$from   = isset( $_POST['ews_redirect_from'] ) ? sanitize_text_field( wp_unslash( $_POST['ews_redirect_from'] ) ) : '';
		$to     = isset( $_POST['ews_redirect_to'] ) ? esc_url_raw( trim( wp_unslash( $_POST['ews_redirect_to'] ) ) ) : '';
		$status = isset( $_POST['ews_redirect_status'] ) ? (int) $_POST['ews_redirect_status'] : 301;

		$result = self::add_rule( $from, $to, $status );

		if ( true === $result ) {
			wp_safe_redirect( admin_url( 'admin.php?page=earthin-wp-suite&tab=redirects&ews_redirect_msg=added' ) );
		} else {
			wp_safe_redirect( admin_url( 'admin.php?page=earthin-wp-suite&tab=redirects&ews_redirect_msg=error&err=' . rawurlencode( $result ) ) );
		}
		exit;
	}

	/**
	 * Handle admin post action to delete a redirect.
	 *
	 * @return void
	 */
	public function handle_delete_redirect() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access denied.', 'earthin-wp-suite' ) );
		}

		check_admin_referer( 'ews_delete_redirect' );

		$index = isset( $_POST['ews_redirect_index'] ) ? (int) $_POST['ews_redirect_index'] : -1;
		if ( $index >= 0 ) {
			self::delete_rule( $index );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=earthin-wp-suite&tab=redirects&ews_redirect_msg=deleted' ) );
		exit;
	}

	/**
	 * Perform exact-path redirect when matched.
	 * Allows triggering on 404s so broken URLs can be recovered.
	 *
	 * @return void
	 */
	public function maybe_redirect() {
		// Never redirect in admin or when previewing a draft.
		if ( is_admin() || is_preview() ) { return; }

		$rules = self::get_rules();
		if ( empty( $rules ) ) { return; }

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
		$raw_path    = wp_parse_url( $request_uri, PHP_URL_PATH );
		$path        = '/' . trim( (string) $raw_path, '/' );

		$allowed_statuses = array( 301, 302, 307, 308 );

		foreach ( $rules as $index => $rule ) {
			if ( empty( $rule['from'] ) || empty( $rule['to'] ) ) { continue; }

			$from = '/' . trim( sanitize_text_field( $rule['from'] ), '/' );

			// Case-insensitive exact path matching without trailing slash ambiguity.
			if ( strtolower( $from ) === strtolower( $path ) ) {
				$to = esc_url_raw( trim( $rule['to'] ) );
				if ( ! $to ) { continue; }

				// Prevent direct self-referencing redirect loops.
				$to_path = wp_parse_url( $to, PHP_URL_PATH );
				if ( $to_path && '/' . trim( $to_path, '/' ) === $path ) {
					continue;
				}

				// Record hit before executing redirect.
				self::increment_hits( $index );

				$status = isset( $rule['status'] ) && in_array( (int) $rule['status'], $allowed_statuses, true ) ? (int) $rule['status'] : 301;

				// Distinguish between local safe redirects and valid external redirects.
				$to_host   = wp_parse_url( $to, PHP_URL_HOST );
				$home_host = wp_parse_url( home_url(), PHP_URL_HOST );

				if ( ! $to_host || $to_host === $home_host ) {
					wp_safe_redirect( $to, $status, 'Earthin WP Suite' );
				} else {
					// External redirect with validated URL schema.
					$scheme = wp_parse_url( $to, PHP_URL_SCHEME );
					if ( in_array( $scheme, array( 'http', 'https' ), true ) ) {
						wp_redirect( $to, $status, 'Earthin WP Suite' );
					}
				}
				exit;
			}
		}
	}
}
