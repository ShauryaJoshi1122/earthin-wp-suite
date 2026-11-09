<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class Social_Preview_Module implements Module_Interface {
	public function id() { return 'social-preview'; }
	public function name() { return __( 'Social Preview', 'earthin-wp-suite' ); }
	public function description() { return __( 'Open Graph and X card metadata using featured images.', 'earthin-wp-suite' ); }
	public function is_enabled() { $s = get_option( 'ews_settings', array() ); return ! isset( $s['social_preview_enabled'] ) || (bool) $s['social_preview_enabled']; }
	public function boot() {
		if ( $this->has_seo_provider() ) { return; }
		add_action( 'wp_head', array( $this, 'output' ), 3 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta' ) );
	}
	private function has_seo_provider() {
		return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' );
	}
	public function add_meta_box() {
		foreach ( get_post_types( array( 'public' => true ), 'names' ) as $type ) {
			add_meta_box( 'ews-social-image', __( 'Social sharing image', 'earthin-wp-suite' ), array( $this, 'render_meta_box' ), $type, 'side' );
		}
	}
	public function render_meta_box( $post ) {
		wp_nonce_field( 'ews_social_image_' . $post->ID, 'ews_social_nonce' );
		$value = get_post_meta( $post->ID, '_ews_social_image', true );
		echo '<p><label for="ews-social-image-url">' . esc_html__( 'Image URL (optional)', 'earthin-wp-suite' ) . '</label></p>';
		echo '<input class="widefat" type="url" id="ews-social-image-url" name="ews_social_image" value="' . esc_attr( $value ) . '" placeholder="https://example.com/image.jpg" />';
		echo '<p class="description">' . esc_html__( 'Leave blank to use the featured image.', 'earthin-wp-suite' ) . '</p>';
	}
	public function save_meta( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! isset( $_POST['ews_social_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ews_social_nonce'] ) ), 'ews_social_image_' . $post_id ) ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
		$url = isset( $_POST['ews_social_image'] ) ? esc_url_raw( wp_unslash( $_POST['ews_social_image'] ) ) : '';
		if ( $url ) { update_post_meta( $post_id, '_ews_social_image', $url ); } else { delete_post_meta( $post_id, '_ews_social_image' ); }
	}
	public function output() {
		if ( is_admin() || is_feed() || is_404() ) { return; }

		$post_id     = is_singular() ? get_queried_object_id() : 0;
		$title       = $post_id ? get_the_title( $post_id ) : get_bloginfo( 'name' );
		$url         = $post_id ? get_permalink( $post_id ) : home_url( '/' );
		$description = $post_id ? get_the_excerpt( $post_id ) : get_bloginfo( 'description' );

		if ( $post_id && ! $description ) {
			$raw_content = get_post_field( 'post_content', $post_id );
			$description = wp_trim_words( wp_strip_all_tags( (string) $raw_content ), 28 );
		}
		if ( ! $description ) {
			$description = get_bloginfo( 'description' );
		}

		$image = '';
		if ( $post_id ) {
			$image = get_post_meta( $post_id, '_ews_social_image', true );
			if ( ! $image && has_post_thumbnail( $post_id ) ) {
				$image = get_the_post_thumbnail_url( $post_id, 'full' );
			}
		}
		if ( ! $image ) {
			$image = get_site_icon_url( 512 );
		}

		$tags = array(
			'og:type'             => is_singular() ? 'article' : 'website',
			'og:site_name'        => get_bloginfo( 'name' ),
			'og:title'            => $title,
			'og:description'      => $description,
			'og:url'              => $url,
			'twitter:card'        => $image ? 'summary_large_image' : 'summary',
			'twitter:title'       => $title,
			'twitter:description' => $description,
		);

		if ( $image ) {
			$tags['og:image']      = $image;
			$tags['twitter:image'] = $image;
		}

		$url_keys = array( 'og:url', 'og:image', 'twitter:image' );

		foreach ( $tags as $property => $content ) {
			if ( '' !== (string) $content ) {
				$attribute     = 0 === strpos( $property, 'twitter:' ) ? 'name' : 'property';
				$clean_content = in_array( $property, $url_keys, true ) ? esc_url( $content ) : esc_attr( wp_strip_all_tags( $content ) );
				echo '<meta ' . esc_attr( $attribute ) . '="' . esc_attr( $property ) . '" content="' . $clean_content . '" />' . "\n";
			}
		}
	}
}
