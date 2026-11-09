<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class SEO_Module implements Module_Interface {
	public function id() { return 'seo'; }
	public function name() { return __( 'SEO', 'earthin-wp-suite' ); }
	public function description() { return __( 'Basic SEO title, description, robots directives, and canonical URLs.', 'earthin-wp-suite' ); }
	public function is_enabled() { $s = get_option( 'ews_settings', array() ); return ! isset( $s['seo_enabled'] ) || (bool) $s['seo_enabled']; }

	public function boot() {
		if ( $this->has_seo_provider() ) { return; }
		add_action( 'wp_head', array( $this, 'output' ), 2 );
		add_filter( 'pre_get_document_title', array( $this, 'document_title' ), 15 );
	}

	private function has_seo_provider() {
		return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' );
	}

	/**
	 * Provide document title if custom post title is defined.
	 *
	 * @param string $title Current title.
	 * @return string Modified title.
	 */
	public function document_title( $title ) {
		if ( is_singular() ) {
			$post_id      = get_queried_object_id();
			$custom_title = get_post_meta( $post_id, '_ews_seo_title', true );
			if ( $custom_title ) {
				return sanitize_text_field( $custom_title );
			}
		}
		return $title;
	}

	public function output() {
		if ( is_admin() || is_feed() ) { return; }

		// Robots directives.
		if ( is_404() || is_search() ) {
			echo '<meta name="robots" content="noindex, follow" />' . "\n";
			return;
		}

		$post_id = is_singular() ? get_queried_object_id() : 0;
		$url     = $post_id ? get_permalink( $post_id ) : home_url( '/' );

		// Check per-post description or fallback to excerpt/bloginfo.
		$description = '';
		if ( $post_id ) {
			$description = get_post_meta( $post_id, '_ews_seo_description', true );
			if ( ! $description ) {
				$description = get_the_excerpt( $post_id );
			}
			if ( ! $description ) {
				$raw_content = get_post_field( 'post_content', $post_id );
				$description = wp_trim_words( wp_strip_all_tags( (string) $raw_content ), 28 );
			}
		}
		if ( ! $description ) {
			$description = get_bloginfo( 'description' );
		}

		if ( '0' !== get_option( 'blog_public' ) ) {
			echo '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />' . "\n";
		}
		if ( $url ) {
			echo '<link rel="canonical" href="' . esc_url( $url ) . '" />' . "\n";
		}
		if ( $description ) {
			echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $description ) ) . '" />' . "\n";
		}
	}
}
