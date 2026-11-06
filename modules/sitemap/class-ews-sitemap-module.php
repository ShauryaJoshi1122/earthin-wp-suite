<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class Sitemap_Module implements Module_Interface {
	public function id() { return 'sitemap'; }
	public function name() { return __( 'XML sitemap', 'earthin-wp-suite' ); }
	public function description() { return __( 'Use and configure the WordPress core XML sitemap.', 'earthin-wp-suite' ); }
	public function is_enabled() { $s = get_option( 'ews_settings', array() ); return ! empty( $s['sitemap_enabled'] ); }
	public function boot() {
		add_filter( 'wp_sitemaps_enabled', '__return_true' );
		add_filter( 'wp_sitemaps_posts_query_args', array( $this, 'exclude_private_types' ), 10, 2 );
	}
	public function exclude_private_types( $args, $post_type ) {
		$object = get_post_type_object( $post_type );
		if ( ! $object || ! $object->public || ! $object->publicly_queryable ) { $args['post__in'] = array( 0 ); }
		return $args;
	}
}
