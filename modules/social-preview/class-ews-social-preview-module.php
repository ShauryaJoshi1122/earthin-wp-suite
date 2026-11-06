<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class Social_Preview_Module implements Module_Interface {
	public function id() { return 'social-preview'; }
	public function name() { return __( 'Social Preview', 'earthin-wp-suite' ); }
	public function description() { return __( 'Open Graph and social card metadata.', 'earthin-wp-suite' ); }
	public function is_enabled() { $s = get_option( 'ews_settings', array() ); return ! isset( $s['social_preview_enabled'] ) || (bool) $s['social_preview_enabled']; }
	public function boot() { /* Output waits for SEO-provider conflict detection. */ }
}
