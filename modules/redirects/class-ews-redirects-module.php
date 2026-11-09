<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class Redirects_Module implements Module_Interface {
	public function id() { return 'redirects'; }
	public function name() { return __( 'Redirects', 'earthin-wp-suite' ); }
	public function description() { return __( 'Manage exact-path redirects.', 'earthin-wp-suite' ); }
	public function is_enabled() { $s = get_option( 'ews_settings', array() ); return ! empty( $s['redirects_enabled'] ); }
	public function boot() { add_action( 'template_redirect', array( $this, 'maybe_redirect' ), 1 ); }

	/**
	 * Perform exact-path redirect when matched.
	 * Allows triggering on 404s so broken URLs can be recovered.
	 *
	 * @return void
	 */
	public function maybe_redirect() {
		// Never redirect in admin or when previewing a draft.
		if ( is_admin() || is_preview() ) { return; }

		$rules = get_option( 'ews_redirect_rules', array() );
		if ( ! is_array( $rules ) || empty( $rules ) ) { return; }

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
		$raw_path    = wp_parse_url( $request_uri, PHP_URL_PATH );
		$path        = '/' . trim( (string) $raw_path, '/' );

		$allowed_statuses = array( 301, 302, 307, 308 );

		foreach ( $rules as $rule ) {
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
