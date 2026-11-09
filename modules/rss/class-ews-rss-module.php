<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class RSS_Module implements Module_Interface {
	public function id() { return 'rss'; }
	public function name() { return __( 'RSS import', 'earthin-wp-suite' ); }
	public function description() { return __( 'Import RSS items into a review queue as drafts.', 'earthin-wp-suite' ); }
	public function is_enabled() { $s = get_option( 'ews_settings', array() ); return ! empty( $s['rss_enabled'] ); }
	public function boot() { add_action( 'admin_post_ews_import_rss', array( $this, 'import' ) ); }

	/**
	 * Validate a feed URL to prevent SSRF against internal/reserved network addresses.
	 *
	 * @param string $url URL to validate.
	 * @return bool True if destination is a valid public IP.
	 */
	public static function is_safe_feed_url( string $url ): bool {
		if ( ! $url || ! wp_http_validate_url( $url ) ) {
			return false;
		}

		$parsed = wp_parse_url( $url );
		if ( empty( $parsed['scheme'] ) || empty( $parsed['host'] ) ) {
			return false;
		}

		if ( ! in_array( strtolower( $parsed['scheme'] ), array( 'http', 'https' ), true ) ) {
			return false;
		}

		$host = strtolower( $parsed['host'] );

		// Disallow localhost and internal domain names.
		if ( 'localhost' === $host || str_ends_with( $host, '.local' ) || str_ends_with( $host, '.internal' ) || str_ends_with( $host, '.localhost' ) ) {
			return false;
		}

		// Check if host is a direct IP address.
		if ( filter_var( $host, FILTER_VALIDATE_IP ) ) {
			return (bool) filter_var( $host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
		}

		// Resolve hostname to IP addresses and verify all are public.
		$ips = @gethostbynamel( $host );
		if ( false === $ips || empty( $ips ) ) {
			return false;
		}

		foreach ( $ips as $ip ) {
			if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
				return false;
			}
			// Additional loopback checks.
			if ( '127.0.0.1' === $ip || '0.0.0.0' === $ip || '::1' === $ip ) {
				return false;
			}
		}

		return true;
	}

	public function import() {
		if ( ! current_user_can( 'publish_posts' ) ) {
			wp_die( esc_html__( 'Access denied. Administrator or editor capability required.', 'earthin-wp-suite' ) );
		}

		check_admin_referer( 'ews_import_rss' );

		$url = isset( $_POST['ews_feed_url'] ) ? esc_url_raw( wp_unslash( $_POST['ews_feed_url'] ) ) : '';

		if ( ! $this->is_safe_feed_url( $url ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=earthin-wp-suite&ews_rss=invalid' ) );
			exit;
		}

		require_once ABSPATH . WPINC . '/feed.php';

		$feed = fetch_feed( $url );
		if ( is_wp_error( $feed ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=earthin-wp-suite&ews_rss=failed' ) );
			exit;
		}

		$imported_count = 0;
		$max_items      = 5;
		$quantity       = min( $max_items, (int) $feed->get_item_quantity( $max_items ) );

		foreach ( $feed->get_items( 0, $quantity ) as $item ) {
			$link = esc_url_raw( (string) $item->get_link() );

			// Skip if item was already imported.
			if ( $link && get_posts( array( 'post_type' => 'any', 'meta_key' => '_ews_source_url', 'meta_value' => $link, 'fields' => 'ids', 'posts_per_page' => 1 ) ) ) {
				continue;
			}

			$title   = sanitize_text_field( wp_strip_all_tags( (string) $item->get_title() ) );
			$content = wp_kses_post( (string) $item->get_content() );

			if ( $link ) {
				$content .= "\n\n" . '<p class="ews-source-attribution"><em>' . sprintf(
					/* translators: 1: Source post title and link */
					esc_html__( 'Originally published at: %s', 'earthin-wp-suite' ),
					'<a href="' . esc_url( $link ) . '" rel="noopener noreferrer nofollow" target="_blank">' . esc_html( $title ? $title : $link ) . '</a>'
				) . '</em></p>';
			}

			$post_id = wp_insert_post( array(
				'post_type'    => 'post',
				'post_status'  => 'draft',
				'post_title'   => $title ? $title : __( 'Imported RSS Item', 'earthin-wp-suite' ),
				'post_content' => $content,
			), true );

			if ( ! is_wp_error( $post_id ) ) {
				if ( $link ) {
					update_post_meta( $post_id, '_ews_source_url', $link );
				}
				update_post_meta( $post_id, '_ews_source_feed', $url );
				update_post_meta( $post_id, '_ews_imported_at', current_time( 'mysql' ) );
				$imported_count++;
			}
		}

		wp_safe_redirect( admin_url( 'admin.php?page=earthin-wp-suite&ews_rss=done&count=' . $imported_count ) );
		exit;
	}
}
