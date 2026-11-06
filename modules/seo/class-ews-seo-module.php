<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class SEO_Module implements Module_Interface {
	public function id() { return 'seo'; }
	public function name() { return __( 'SEO', 'earthin-wp-suite' ); }
	public function description() { return __( 'Basic SEO metadata and canonical URLs.', 'earthin-wp-suite' ); }
	public function is_enabled() { $s = get_option( 'ews_settings', array() ); return ! isset( $s['seo_enabled'] ) || (bool) $s['seo_enabled']; }
	public function boot() {
		if ( $this->has_seo_provider() ) { return; }
		add_action( 'wp_head', array( $this, 'output' ), 2 );
	}
	private function has_seo_provider() {
		return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' );
	}
	public function output() {
		if ( is_admin() || is_feed() || is_404() ) { return; }
		$url = is_singular() ? get_permalink() : home_url( '/' );
		$title = is_singular() ? get_the_title() : get_bloginfo( 'name' );
		$description = is_singular() ? get_the_excerpt() : get_bloginfo( 'description' );
		if ( is_singular() && ! $description ) { $description = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', get_queried_object_id() ) ), 28 ); }
		if ( ! $description ) { $description = get_bloginfo( 'description' ); }
		echo '<link rel="canonical" href="' . esc_url( $url ) . '" />' . "\n";
		echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $description ) ) . '" />' . "\n";
	}
}
