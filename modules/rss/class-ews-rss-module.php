<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class RSS_Module implements Module_Interface {
	public function id() { return 'rss'; }
	public function name() { return __( 'RSS import', 'earthin-wp-suite' ); }
	public function description() { return __( 'Import RSS items into a review queue as drafts.', 'earthin-wp-suite' ); }
	public function is_enabled() { $s = get_option( 'ews_settings', array() ); return ! empty( $s['rss_enabled'] ); }
	public function boot() { add_action( 'admin_post_ews_import_rss', array( $this, 'import' ) ); }
	public function import() {
		if ( ! current_user_can( 'edit_posts' ) ) { wp_die( esc_html__( 'Access denied.', 'earthin-wp-suite' ) ); }
		check_admin_referer( 'ews_import_rss' );
		$url = isset( $_POST['ews_feed_url'] ) ? esc_url_raw( wp_unslash( $_POST['ews_feed_url'] ) ) : '';
		if ( ! $url || ! wp_http_validate_url( $url ) ) { wp_safe_redirect( admin_url( 'admin.php?page=earthin-wp-suite&ews_rss=invalid' ) ); exit; }
		require_once ABSPATH . WPINC . '/feed.php';
		$feed = fetch_feed( $url );
		if ( is_wp_error( $feed ) ) { wp_safe_redirect( admin_url( 'admin.php?page=earthin-wp-suite&ews_rss=failed' ) ); exit; }
		foreach ( $feed->get_items( 0, min( 5, (int) $feed->get_item_quantity( 5 ) ) ) as $item ) {
			$link = esc_url_raw( $item->get_link() );
			if ( $link && get_posts( array( 'post_type' => 'any', 'meta_key' => '_ews_source_url', 'meta_value' => $link, 'fields' => 'ids', 'posts_per_page' => 1 ) ) ) { continue; }
			$id = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'draft', 'post_title' => sanitize_text_field( wp_strip_all_tags( $item->get_title() ) ), 'post_content' => wp_kses_post( $item->get_content() ) ), true );
			if ( ! is_wp_error( $id ) && $link ) { update_post_meta( $id, '_ews_source_url', $link ); }
		}
		wp_safe_redirect( admin_url( 'admin.php?page=earthin-wp-suite&ews_rss=done' ) );
		exit;
	}
}
