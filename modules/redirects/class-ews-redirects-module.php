<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class Redirects_Module implements Module_Interface {
	public function id() { return 'redirects'; }
	public function name() { return __( 'Redirects', 'earthin-wp-suite' ); }
	public function description() { return __( 'Manage exact-path redirects.', 'earthin-wp-suite' ); }
	public function is_enabled() { $s = get_option( 'ews_settings', array() ); return ! empty( $s['redirects_enabled'] ); }
	public function boot() { add_action( 'template_redirect', array( $this, 'maybe_redirect' ), 1 ); }
	public function maybe_redirect() {
		if ( is_admin() || is_preview() || is_404() ) { return; }
		$rules = get_option( 'ews_redirect_rules', array() );
		if ( ! is_array( $rules ) ) { return; }
		$path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH );
		foreach ( $rules as $rule ) {
			if ( empty( $rule['from'] ) || empty( $rule['to'] ) ) { continue; }
			$from = '/' . trim( sanitize_text_field( $rule['from'] ), '/' );
			if ( $from === $path ) {
				$to = esc_url_raw( $rule['to'] );
				if ( $to && wp_safe_redirect( $to, 301, 'Earthin WP Suite' ) ) { exit; }
			}
		}
	}
}
