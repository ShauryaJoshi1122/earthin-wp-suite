<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class Schema_Module implements Module_Interface {
	public function id() { return 'schema'; }
	public function name() { return __( 'Structured data', 'earthin-wp-suite' ); }
	public function description() { return __( 'Basic WebSite, WebPage, and Article JSON-LD structured data.', 'earthin-wp-suite' ); }
	public function is_enabled() { $s = get_option( 'ews_settings', array() ); return ! empty( $s['schema_enabled'] ); }

	public function boot() {
		if ( $this->has_schema_provider() ) { return; }
		add_action( 'wp_head', array( $this, 'output' ), 4 );
	}

	private function has_schema_provider() {
		return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' );
	}

	public function output() {
		if ( is_admin() || is_feed() || is_404() ) { return; }

		$site_name = get_bloginfo( 'name' );
		$site_url  = home_url( '/' );
		$site_icon = get_site_icon_url( 512 );

		$publisher = array(
			'@type' => 'Organization',
			'name'  => $site_name,
			'url'   => $site_url,
		);

		if ( $site_icon ) {
			$publisher['logo'] = array(
				'@type' => 'ImageObject',
				'url'   => $site_icon,
			);
		}

		if ( is_front_page() || is_home() ) {
			$data = array(
				'@context'  => 'https://schema.org',
				'@type'     => 'WebSite',
				'name'      => $site_name,
				'url'       => $site_url,
				'publisher' => $publisher,
			);
		} elseif ( is_singular( 'post' ) ) {
			$post_id    = get_queried_object_id();
			$author_id  = (int) get_post_field( 'post_author', $post_id );
			$author_name = get_the_author_meta( 'display_name', $author_id );

			$data = array(
				'@context'         => 'https://schema.org',
				'@type'            => 'BlogPosting',
				'headline'         => wp_strip_all_tags( get_the_title( $post_id ) ),
				'datePublished'    => get_post_time( DATE_W3C, true, $post_id ),
				'dateModified'     => get_post_modified_time( DATE_W3C, true, $post_id ),
				'mainEntityOfPage' => get_permalink( $post_id ),
				'author'           => array(
					'@type' => 'Person',
					'name'  => $author_name ? $author_name : $site_name,
				),
				'publisher'        => $publisher,
			);

			if ( has_post_thumbnail( $post_id ) ) {
				$data['image'] = get_the_post_thumbnail_url( $post_id, 'full' );
			}
		} elseif ( is_singular() ) {
			$post_id = get_queried_object_id();
			$data    = array(
				'@context'         => 'https://schema.org',
				'@type'            => 'WebPage',
				'name'             => wp_strip_all_tags( get_the_title( $post_id ) ),
				'url'              => get_permalink( $post_id ),
				'publisher'        => $publisher,
				'datePublished'    => get_post_time( DATE_W3C, true, $post_id ),
				'dateModified'     => get_post_modified_time( DATE_W3C, true, $post_id ),
			);
		} else {
			$data = array(
				'@context'  => 'https://schema.org',
				'@type'     => 'WebSite',
				'name'      => $site_name,
				'url'       => $site_url,
				'publisher' => $publisher,
			);
		}

		// Use JSON_HEX flags to guarantee no closing script tags or unescaped HTML characters break out.
		$flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
		echo '<script type="application/ld+json">' . wp_json_encode( $data, $flags ) . '</script>' . "\n";
	}
}
