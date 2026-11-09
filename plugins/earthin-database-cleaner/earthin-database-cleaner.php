<?php
/**
 * Plugin Name: Earthin Database Cleaner
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-database-cleaner
 * Description: Safe, preview-first WordPress database optimization tool to inspect and selectively clean revisions, transients, drafts, and orphaned metadata.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: earthin-database-cleaner
 */

namespace Earthin\DatabaseCleaner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EDC_VERSION', '0.1.0' );
define( 'EDC_PATH', plugin_dir_path( __FILE__ ) );
define( 'EDC_URL', plugin_dir_url( __FILE__ ) );

final class Plugin {

	private static ?Plugin $instance = null;

	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'admin_post_edc_cleanup', array( $this, 'handle_cleanup_submission' ) );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'earthin-database-cleaner', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function register_admin_menu(): void {
		add_management_page(
			__( 'Earthin DB Cleaner', 'earthin-database-cleaner' ),
			__( 'Earthin DB Cleaner', 'earthin-database-cleaner' ),
			'manage_options',
			'earthin-database-cleaner',
			array( $this, 'render_admin_page' )
		);
	}

	public function get_database_overview(): array {
		global $wpdb;

		$tables_status = $wpdb->get_results( "SHOW TABLE STATUS LIKE '" . $wpdb->esc_like( $wpdb->prefix ) . "%'", ARRAY_A );
		$total_data = 0;
		$total_index = 0;
		$total_overhead = 0;
		$table_count = is_array( $tables_status ) ? count( $tables_status ) : 0;

		if ( is_array( $tables_status ) ) {
			foreach ( $tables_status as $table ) {
				$total_data     += (float) ( $table['Data_length'] ?? 0 );
				$total_index    += (float) ( $table['Index_length'] ?? 0 );
				$total_overhead += (float) ( $table['Data_free'] ?? 0 );
			}
		}

		return array(
			'table_count'    => $table_count,
			'data_size_mb'   => round( $total_data / ( 1024 * 1024 ), 2 ),
			'index_size_mb'  => round( $total_index / ( 1024 * 1024 ), 2 ),
			'total_size_mb'  => round( ( $total_data + $total_index ) / ( 1024 * 1024 ), 2 ),
			'overhead_mb'    => round( $total_overhead / ( 1024 * 1024 ), 2 ),
		);
	}

	public function get_cleanable_inventory(): array {
		global $wpdb;

		// 1. Post revisions
		$revisions = (int) $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'revision'" );

		// 2. Auto drafts
		$auto_drafts = (int) $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_status = 'auto-draft'" );

		// 3. Trashed posts
		$trashed_posts = (int) $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_status = 'trash'" );

		// 4. Spam comments
		$spam_comments = (int) $wpdb->get_var( "SELECT COUNT(comment_ID) FROM {$wpdb->comments} WHERE comment_approved = 'spam'" );

		// 5. Trashed comments
		$trashed_comments = (int) $wpdb->get_var( "SELECT COUNT(comment_ID) FROM {$wpdb->comments} WHERE comment_approved = 'trash'" );

		// 6. Expired transients
		$time = time();
		$expired_transients = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(option_id) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d",
				'_transient_timeout_%',
				$time
			)
		);

		// 7. Orphaned postmeta
		$orphaned_postmeta = (int) $wpdb->get_var(
			"SELECT COUNT(pm.meta_id) FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.ID IS NULL"
		);

		// 8. Orphaned commentmeta
		$orphaned_commentmeta = (int) $wpdb->get_var(
			"SELECT COUNT(cm.meta_id) FROM {$wpdb->commentmeta} cm LEFT JOIN {$wpdb->comments} c ON c.comment_ID = cm.comment_id WHERE c.comment_ID IS NULL"
		);

		return array(
			'revisions'            => array(
				'label'       => __( 'Post Revisions', 'earthin-database-cleaner' ),
				'count'       => $revisions,
				'description' => __( 'Historical revisions stored every time a post or page is saved.', 'earthin-database-cleaner' ),
			),
			'auto_drafts'          => array(
				'label'       => __( 'Auto Drafts', 'earthin-database-cleaner' ),
				'count'       => $auto_drafts,
				'description' => __( 'Abandoned automatic post drafts that were never saved or published.', 'earthin-database-cleaner' ),
			),
			'trashed_posts'        => array(
				'label'       => __( 'Trashed Posts', 'earthin-database-cleaner' ),
				'count'       => $trashed_posts,
				'description' => __( 'Posts, pages, and custom post types currently residing in Trash.', 'earthin-database-cleaner' ),
			),
			'spam_comments'        => array(
				'label'       => __( 'Spam Comments', 'earthin-database-cleaner' ),
				'count'       => $spam_comments,
				'description' => __( 'User or automated comments flagged as spam.', 'earthin-database-cleaner' ),
			),
			'trashed_comments'     => array(
				'label'       => __( 'Trashed Comments', 'earthin-database-cleaner' ),
				'count'       => $trashed_comments,
				'description' => __( 'Comments marked for deletion in comment trash.', 'earthin-database-cleaner' ),
			),
			'expired_transients'   => array(
				'label'       => __( 'Expired Transients', 'earthin-database-cleaner' ),
				'count'       => $expired_transients,
				'description' => __( 'Temporary cached options that have already expired and are no longer valid.', 'earthin-database-cleaner' ),
			),
			'orphaned_postmeta'    => array(
				'label'       => __( 'Orphaned Post Metadata', 'earthin-database-cleaner' ),
				'count'       => $orphaned_postmeta,
				'description' => __( 'Post metadata records pointing to posts that have already been deleted.', 'earthin-database-cleaner' ),
			),
			'orphaned_commentmeta' => array(
				'label'       => __( 'Orphaned Comment Metadata', 'earthin-database-cleaner' ),
				'count'       => $orphaned_commentmeta,
				'description' => __( 'Comment metadata records pointing to deleted comments.', 'earthin-database-cleaner' ),
			),
		);
	}

	public function handle_cleanup_submission(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'earthin-database-cleaner' ) );
		}

		check_admin_referer( 'edc_cleanup_action', 'edc_cleanup_nonce' );

		if ( empty( $_POST['edc_confirm_purge'] ) ) {
			wp_safe_redirect( add_query_arg( array(
				'page' => 'earthin-database-cleaner',
				'edc_error' => 'not_confirmed',
			), admin_url( 'tools.php' ) ) );
			exit;
		}

		$selected = isset( $_POST['edc_items'] ) && is_array( $_POST['edc_items'] )
			? array_map( 'sanitize_key', $_POST['edc_items'] )
			: array();

		if ( empty( $selected ) ) {
			wp_safe_redirect( add_query_arg( array(
				'page' => 'earthin-database-cleaner',
				'edc_error' => 'none_selected',
			), admin_url( 'tools.php' ) ) );
			exit;
		}

		global $wpdb;
		$purged_counts = array();

		// 1. Revisions
		if ( in_array( 'revisions', $selected, true ) ) {
			$res = $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'revision'" );
			$purged_counts['revisions'] = (int) $res;
		}

		// 2. Auto drafts
		if ( in_array( 'auto_drafts', $selected, true ) ) {
			$res = $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_status = 'auto-draft'" );
			$purged_counts['auto_drafts'] = (int) $res;
		}

		// 3. Trashed posts
		if ( in_array( 'trashed_posts', $selected, true ) ) {
			$res = $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_status = 'trash'" );
			$purged_counts['trashed_posts'] = (int) $res;
		}

		// 4. Spam comments
		if ( in_array( 'spam_comments', $selected, true ) ) {
			$res = $wpdb->query( "DELETE FROM {$wpdb->comments} WHERE comment_approved = 'spam'" );
			$purged_counts['spam_comments'] = (int) $res;
		}

		// 5. Trashed comments
		if ( in_array( 'trashed_comments', $selected, true ) ) {
			$res = $wpdb->query( "DELETE FROM {$wpdb->comments} WHERE comment_approved = 'trash'" );
			$purged_counts['trashed_comments'] = (int) $res;
		}

		// 6. Expired transients
		if ( in_array( 'expired_transients', $selected, true ) ) {
			$time = time();
			$expired = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d",
					'_transient_timeout_%',
					$time
				)
			);
			$count = 0;
			if ( ! empty( $expired ) ) {
				foreach ( $expired as $timeout_key ) {
					$transient_key = substr( $timeout_key, strlen( '_transient_timeout_' ) );
					delete_transient( $transient_key );
					$count++;
				}
			}
			$purged_counts['expired_transients'] = $count;
		}

		// 7. Orphaned postmeta
		if ( in_array( 'orphaned_postmeta', $selected, true ) ) {
			$res = $wpdb->query(
				"DELETE pm FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.ID IS NULL"
			);
			$purged_counts['orphaned_postmeta'] = (int) $res;
		}

		// 8. Orphaned commentmeta
		if ( in_array( 'orphaned_commentmeta', $selected, true ) ) {
			$res = $wpdb->query(
				"DELETE cm FROM {$wpdb->commentmeta} cm LEFT JOIN {$wpdb->comments} c ON c.comment_ID = cm.comment_id WHERE c.comment_ID IS NULL"
			);
			$purged_counts['orphaned_commentmeta'] = (int) $res;
		}

		// Store report in transient for 60 seconds
		$user_id = get_current_user_id();
		set_transient( 'edc_report_' . $user_id, $purged_counts, 60 );

		wp_safe_redirect( add_query_arg( array(
			'page'        => 'earthin-database-cleaner',
			'edc_success' => '1',
		), admin_url( 'tools.php' ) ) );
		exit;
	}

	public function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'earthin-database-cleaner' ) );
		}

		$overview  = $this->get_database_overview();
		$inventory = $this->get_cleanable_inventory();
		$user_id   = get_current_user_id();
		$report    = get_transient( 'edc_report_' . $user_id );
		if ( false !== $report ) {
			delete_transient( 'edc_report_' . $user_id );
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Earthin Database Cleaner', 'earthin-database-cleaner' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Preview and selectively purge expired transients, orphaned metadata, and unneeded revisions to keep your WordPress database lean.', 'earthin-database-cleaner' ); ?>
			</p>

			<?php if ( ! empty( $_GET['edc_error'] ) ) : ?>
				<div class="notice notice-error is-dismissible">
					<p>
						<?php
						if ( 'not_confirmed' === $_GET['edc_error'] ) {
							esc_html_e( 'Operation aborted: You must check the confirmation box before performing a database cleanup.', 'earthin-database-cleaner' );
						} else {
							esc_html_e( 'Please select at least one cleanup category.', 'earthin-database-cleaner' );
						}
						?>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( is_array( $report ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><strong><?php esc_html_e( 'Cleanup complete!', 'earthin-database-cleaner' ); ?></strong></p>
					<ul style="list-style:disc; margin-left:20px;">
						<?php
						$total_purged = 0;
						foreach ( $report as $key => $count ) {
							$total_purged += (int) $count;
							$label = isset( $inventory[ $key ]['label'] ) ? $inventory[ $key ]['label'] : $key;
							printf(
								'<li>%s: %s</li>',
								esc_html( $label ),
								esc_html( sprintf( _n( '%s record removed', '%s records removed', $count, 'earthin-database-cleaner' ), number_format_i18n( $count ) ) )
							);
						}
						?>
					</ul>
					<p><?php printf( esc_html__( 'Total purged: %s redundant rows.', 'earthin-database-cleaner' ), '<strong>' . (int) $total_purged . '</strong>' ); ?></p>
				</div>
			<?php endif; ?>

			<!-- Database Metric Cards -->
			<div style="display:flex; gap:16px; margin:24px 0; flex-wrap:wrap;">
				<div style="flex:1; min-width:180px; background:#ffffff; border:1px solid #cbd5e1; border-radius:8px; padding:16px;">
					<div style="font-size:24px; font-weight:700; color:#0f172a;"><?php echo (int) $overview['table_count']; ?></div>
					<div style="font-size:13px; color:#64748b;"><?php esc_html_e( 'Tables (Prefix Matched)', 'earthin-database-cleaner' ); ?></div>
				</div>
				<div style="flex:1; min-width:180px; background:#ffffff; border:1px solid #cbd5e1; border-radius:8px; padding:16px;">
					<div style="font-size:24px; font-weight:700; color:#0f172a;"><?php echo esc_html( $overview['total_size_mb'] ); ?> MB</div>
					<div style="font-size:13px; color:#64748b;"><?php esc_html_e( 'Total Database Size', 'earthin-database-cleaner' ); ?></div>
				</div>
				<div style="flex:1; min-width:180px; background:#ffffff; border:1px solid #cbd5e1; border-radius:8px; padding:16px;">
					<div style="font-size:24px; font-weight:700; color:#0f172a;"><?php echo esc_html( $overview['overhead_mb'] ); ?> MB</div>
					<div style="font-size:13px; color:#64748b;"><?php esc_html_e( 'Free Overhead Space', 'earthin-database-cleaner' ); ?></div>
				</div>
			</div>

			<!-- Cleanup Form -->
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:20px;">
				<input type="hidden" name="action" value="edc_cleanup">
				<?php wp_nonce_field( 'edc_cleanup_action', 'edc_cleanup_nonce' ); ?>

				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th style="width:40px;"><input type="checkbox" id="edc-select-all" onclick="document.querySelectorAll('.edc-item-check').forEach(cb => { if(!cb.disabled) cb.checked = this.checked; });"></th>
							<th style="width:240px;"><?php esc_html_e( 'Category', 'earthin-database-cleaner' ); ?></th>
							<th style="width:140px;"><?php esc_html_e( 'Redundant Rows', 'earthin-database-cleaner' ); ?></th>
							<th><?php esc_html_e( 'Details', 'earthin-database-cleaner' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $inventory as $key => $data ) : ?>
							<?php $has_records = $data['count'] > 0; ?>
							<tr>
								<td>
									<input type="checkbox" name="edc_items[]" value="<?php echo esc_attr( $key ); ?>" class="edc-item-check" <?php disabled( ! $has_records ); ?>>
								</td>
								<td><strong><?php echo esc_html( $data['label'] ); ?></strong></td>
								<td>
									<?php if ( $has_records ) : ?>
										<span style="background:#fee2e2; color:#b91c1c; padding:2px 8px; border-radius:9999px; font-weight:600; font-size:12px;">
											<?php echo number_format_i18n( $data['count'] ); ?>
										</span>
									<?php else : ?>
										<span style="background:#f1f5f9; color:#64748b; padding:2px 8px; border-radius:9999px; font-weight:500; font-size:12px;">
											0
										</span>
									<?php endif; ?>
								</td>
								<td style="color:#475569; font-size:13px;"><?php echo esc_html( $data['description'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<div style="background:#fffbeb; border:1px solid #fef3c7; border-left:4px solid #f59e0b; border-radius:6px; padding:16px; margin:24px 0;">
					<h3 style="margin-top:0; color:#b45309;"><?php esc_html_e( 'Safety Notice & Administrator Confirmation', 'earthin-database-cleaner' ); ?></h3>
					<p style="color:#78350f; font-size:13px;">
						<?php esc_html_e( 'Database cleanups are permanent. Although these routines target only redundant historical records, it is recommended to take a full database backup before running any cleanup.', 'earthin-database-cleaner' ); ?>
					</p>
					<label style="display:block; margin-top:12px; font-weight:600; color:#78350f;">
						<input type="checkbox" name="edc_confirm_purge" value="1" required>
						<?php esc_html_e( 'I understand that deleted rows cannot be recovered and confirm this cleanup.', 'earthin-database-cleaner' ); ?>
					</label>
				</div>

				<div style="margin-top:16px;">
					<?php submit_button( __( 'Execute Selected Cleanup', 'earthin-database-cleaner' ), 'primary', 'submit', false, array( 'onclick' => 'return confirm("' . esc_js( __( 'Are you sure you want to permanently delete the selected database records?', 'earthin-database-cleaner' ) ) . '");' ) ); ?>
				</div>
			</form>
		</div>
		<?php
	}
}

Plugin::instance();
