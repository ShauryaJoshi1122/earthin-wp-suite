<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class SEO_Module implements Module_Interface {
	public function id() { return 'seo'; }
	public function name() { return __( 'SEO', 'earthin-wp-suite' ); }
	public function description() { return __( 'SEO metadata and discovery tools.', 'earthin-wp-suite' ); }
	public function is_enabled() { $s = get_option( 'ews_settings', array() ); return ! isset( $s['seo_enabled'] ) || (bool) $s['seo_enabled']; }
	public function boot() { /* Feature implementation is scheduled for the next milestone. */ }
}
