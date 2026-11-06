<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class Schema_Module implements Module_Interface {
	public function id() { return 'schema'; }
	public function name() { return __( 'Structured data', 'earthin-wp-suite' ); }
	public function description() { return __( 'Basic WebSite and Article JSON-LD.', 'earthin-wp-suite' ); }
	public function is_enabled() { $s = get_option( 'ews_settings', array() ); return ! empty( $s['schema_enabled'] ); }
	public function boot() {
		if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) ) { return; }
		add_action( 'wp_head', array( $this, 'output' ), 4 );
	}
	public function output() {
		if ( is_admin() || is_feed() || is_404() ) { return; }
		$data = array(
			'@context' => 'https://schema.org',
			'@type' => 'WebSite',
			'name' => get_bloginfo( 'name' ),
			'url' => home_url( '/' ),
		);
		if ( is_singular( 'post' ) ) {
			$post_id = get_queried_object_id();
			$data = array(
				'@context' => 'https://schema.org',
				'@type' => 'NewsArticle',
				'headline' => get_the_title( $post_id ),
				'datePublished' => get_post_time( DATE_W3C, true, $post_id ),
				'dateModified' => get_post_modified_time( DATE_W3C, true, $post_id ),
				'mainEntityOfPage' => get_permalink( $post_id ),
				'author' => array( '@type' => 'Person', 'name' => get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) ) ),
				'publisher' => array( '@type' => 'Organization', 'name' => get_bloginfo( 'name' ) ),
			);
			if ( has_post_thumbnail( $post_id ) ) { $data['image'] = get_the_post_thumbnail_url( $post_id, 'full' ); }
		}
		echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}
