<?php
/**
 * Plugin Name: Earthin Image Optimizer
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-image-optimizer
 * Description: High-efficiency local WordPress image optimization engine with lossless/lossy compression, WebP conversion, and original file backup protection.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: earthin-image-optimizer
 */

namespace Earthin\ImageOptimizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EIO_VERSION', '0.1.0' );
define( 'EIO_PATH', plugin_dir_path( __FILE__ ) );
define( 'EIO_URL', plugin_dir_url( __FILE__ ) );
define( 'EIO_OPTION_KEY', 'earthin_image_optimizer_settings' );

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
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_filter( 'wp_generate_attachment_metadata', array( $this, 'handle_upload_optimization' ), 10, 2 );
		add_filter( 'manage_media_columns', array( $this, 'register_media_columns' ) );
		add_action( 'manage_media_custom_column', array( $this, 'render_media_column' ), 10, 2 );
		add_action( 'admin_post_eio_optimize_single', array( $this, 'handle_single_optimization_action' ) );
		add_action( 'admin_post_eio_restore_single', array( $this, 'handle_single_restore_action' ) );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'earthin-image-optimizer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function get_default_settings(): array {
		return array(
			'auto_optimize'    => 1,
			'jpeg_quality'     => 82,
			'png_level'        => 6,
			'strip_exif'       => 1,
			'generate_webp'    => function_exists( 'imagewebp' ) ? 1 : 0,
			'backup_originals' => 1,
		);
	}

	public function get_settings(): array {
		$saved = get_option( EIO_OPTION_KEY, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), $this->get_default_settings() );
	}

	public function register_admin_menu(): void {
		add_media_page(
			__( 'Earthin Image Optimizer', 'earthin-image-optimizer' ),
			__( 'Earthin Optimizer', 'earthin-image-optimizer' ),
			'manage_options',
			'earthin-image-optimizer',
			array( $this, 'render_admin_page' )
		);
	}

	public function register_settings(): void {
		register_setting(
			'eio_settings_group',
			EIO_OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => $this->get_default_settings(),
			)
		);
	}

	public function sanitize_settings( $input ): array {
		if ( ! is_array( $input ) ) {
			return $this->get_default_settings();
		}

		$output = array();
		$output['auto_optimize'] = ! empty( $input['auto_optimize'] ) ? 1 : 0;

		$jpeg_q = isset( $input['jpeg_quality'] ) ? (int) $input['jpeg_quality'] : 82;
		$output['jpeg_quality'] = max( 50, min( 100, $jpeg_q ) );

		$png_l = isset( $input['png_level'] ) ? (int) $input['png_level'] : 6;
		$output['png_level'] = max( 1, min( 9, $png_l ) );

		$output['strip_exif'] = ! empty( $input['strip_exif'] ) ? 1 : 0;
		$output['generate_webp'] = ! empty( $input['generate_webp'] ) && function_exists( 'imagewebp' ) ? 1 : 0;
		$output['backup_originals'] = ! empty( $input['backup_originals'] ) ? 1 : 0;

		return $output;
	}

	public function get_backup_directory(): string {
		$upload_dir = wp_upload_dir();
		$backup_dir = trailingslashit( $upload_dir['basedir'] ) . 'earthin-image-backups';
		if ( ! file_exists( $backup_dir ) ) {
			wp_mkdir_p( $backup_dir );
			// Create htaccess to prevent direct execution or public directory index
			$htaccess = trailingslashit( $backup_dir ) . '.htaccess';
			if ( ! file_exists( $htaccess ) ) {
				@file_put_contents( $htaccess, "Options -Indexes\n" );
			}
			$index = trailingslashit( $backup_dir ) . 'index.php';
			if ( ! file_exists( $index ) ) {
				@file_put_contents( $index, "<?php // Silence is golden." );
			}
		}
		return $backup_dir;
	}

	public function handle_upload_optimization( array $metadata, int $attachment_id ): array {
		$settings = $this->get_settings();
		if ( empty( $settings['auto_optimize'] ) ) {
			return $metadata;
		}

		$file = get_attached_file( $attachment_id );
		if ( $file && file_exists( $file ) ) {
			$this->optimize_attachment( $attachment_id, $file );
		}

		return $metadata;
	}

	public function optimize_attachment( int $attachment_id, string $file_path ): array {
		if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) || ! is_writable( $file_path ) ) {
			return array( 'status' => 'error', 'message' => __( 'File does not exist or is not writable.', 'earthin-image-optimizer' ) );
		}

		$mime = wp_get_image_mime( $file_path );
		if ( ! in_array( $mime, array( 'image/jpeg', 'image/png' ), true ) ) {
			return array( 'status' => 'skipped', 'message' => __( 'Unsupported image MIME type. Only JPEG and PNG are supported.', 'earthin-image-optimizer' ) );
		}

		if ( ! extension_loaded( 'gd' ) ) {
			return array( 'status' => 'error', 'message' => __( 'PHP GD extension is not installed or enabled on your server.', 'earthin-image-optimizer' ) );
		}

		$original_size = (int) filesize( $file_path );
		$settings      = $this->get_settings();

		// Safe original backup handling
		if ( ! empty( $settings['backup_originals'] ) ) {
			$backup_dir = $this->get_backup_directory();
			$backup_name = $attachment_id . '_' . sanitize_file_name( basename( $file_path ) );
			$backup_dest = trailingslashit( $backup_dir ) . $backup_name;
			if ( ! file_exists( $backup_dest ) ) {
				@copy( $file_path, $backup_dest );
				update_post_meta( $attachment_id, '_eio_backup_path', $backup_dest );
			}
		}

		$temp_file = $file_path . '.eio_tmp';

		if ( 'image/jpeg' === $mime ) {
			$image = @imagecreatefromjpeg( $file_path );
			if ( ! $image ) {
				return array( 'status' => 'error', 'message' => __( 'Could not process JPEG image data.', 'earthin-image-optimizer' ) );
			}
			@imagejpeg( $image, $temp_file, (int) $settings['jpeg_quality'] );
			@imagedestroy( $image );
		} elseif ( 'image/png' === $mime ) {
			$image = @imagecreatefrompng( $file_path );
			if ( ! $image ) {
				return array( 'status' => 'error', 'message' => __( 'Could not process PNG image data.', 'earthin-image-optimizer' ) );
			}
			// Maintain alpha channel transparency
			@imagealphablending( $image, false );
			@imagesavealpha( $image, true );
			@imagepng( $image, $temp_file, (int) $settings['png_level'] );
			@imagedestroy( $image );
		}

		if ( ! file_exists( $temp_file ) ) {
			return array( 'status' => 'error', 'message' => __( 'Failed to generate optimized image output.', 'earthin-image-optimizer' ) );
		}

		$new_size = (int) filesize( $temp_file );

		// WebP Conversion (Optional alongside main image)
		if ( ! empty( $settings['generate_webp'] ) && function_exists( 'imagewebp' ) ) {
			$webp_path = preg_replace( '/\.(jpe?g|png)$/i', '.webp', $file_path );
			if ( $webp_path && $webp_path !== $file_path ) {
				$webp_src = 'image/jpeg' === $mime ? @imagecreatefromjpeg( $file_path ) : @imagecreatefrompng( $file_path );
				if ( $webp_src ) {
					if ( 'image/png' === $mime ) {
						@imagealphablending( $webp_src, false );
						@imagesavealpha( $webp_src, true );
					}
					@imagewebp( $webp_src, $webp_path, (int) $settings['jpeg_quality'] );
					@imagedestroy( $webp_src );
					if ( file_exists( $webp_path ) ) {
						update_post_meta( $attachment_id, '_eio_webp_path', $webp_path );
					}
				}
			}
		}

		// Strictly compare sizes: if new file is larger or equal, keep original!
		if ( $new_size < $original_size ) {
			@rename( $temp_file, $file_path );
			$saved_bytes = $original_size - $new_size;

			update_post_meta( $attachment_id, '_eio_status', 'optimized' );
			update_post_meta( $attachment_id, '_eio_original_size', $original_size );
			update_post_meta( $attachment_id, '_eio_optimized_size', $new_size );
			update_post_meta( $attachment_id, '_eio_saved_bytes', $saved_bytes );
			update_post_meta( $attachment_id, '_eio_timestamp', time() );

			return array(
				'status'      => 'success',
				'saved_bytes' => $saved_bytes,
				'percent'     => round( ( $saved_bytes / $original_size ) * 100, 1 ),
			);
		} else {
			@unlink( $temp_file );
			update_post_meta( $attachment_id, '_eio_status', 'already_optimal' );
			update_post_meta( $attachment_id, '_eio_original_size', $original_size );
			update_post_meta( $attachment_id, '_eio_optimized_size', $original_size );
			update_post_meta( $attachment_id, '_eio_saved_bytes', 0 );

			return array(
				'status'  => 'optimal',
				'message' => __( 'Image is already fully optimized. Original preserved.', 'earthin-image-optimizer' ),
			);
		}
	}

	public function restore_attachment( int $attachment_id ): bool {
		$backup_path = get_post_meta( $attachment_id, '_eio_backup_path', true );
		$file_path   = get_attached_file( $attachment_id );

		if ( ! $backup_path || ! file_exists( $backup_path ) || ! $file_path ) {
			return false;
		}

		if ( @copy( $backup_path, $file_path ) ) {
			delete_post_meta( $attachment_id, '_eio_status' );
			delete_post_meta( $attachment_id, '_eio_original_size' );
			delete_post_meta( $attachment_id, '_eio_optimized_size' );
			delete_post_meta( $attachment_id, '_eio_saved_bytes' );

			$webp_path = get_post_meta( $attachment_id, '_eio_webp_path', true );
			if ( $webp_path && file_exists( $webp_path ) ) {
				@unlink( $webp_path );
				delete_post_meta( $attachment_id, '_eio_webp_path' );
			}
			return true;
		}

		return false;
	}

	public function register_media_columns( array $columns ): array {
		$columns['eio_status'] = __( 'Earthin Compression', 'earthin-image-optimizer' );
		return $columns;
	}

	public function render_media_column( string $column_name, int $post_id ): void {
		if ( 'eio_status' !== $column_name ) {
			return;
		}

		$status = get_post_meta( $post_id, '_eio_status', true );
		if ( 'optimized' === $status ) {
			$saved_bytes = (int) get_post_meta( $post_id, '_eio_saved_bytes', true );
			$orig_size   = (int) get_post_meta( $post_id, '_eio_original_size', true );
			$percent     = $orig_size > 0 ? round( ( $saved_bytes / $orig_size ) * 100, 1 ) : 0;
			printf(
				'<span style="color:#10b981;font-weight:600;">✓ -%s%% (%s)</span>',
				esc_html( $percent ),
				esc_html( size_format( $saved_bytes ) )
			);
			$restore_url = wp_nonce_url(
				admin_url( 'admin-post.php?action=eio_restore_single&id=' . $post_id ),
				'eio_restore_' . $post_id
			);
			printf( '<br><a href="%s" style="font-size:11px;color:#64748b;">%s</a>', esc_url( $restore_url ), esc_html__( 'Restore Original', 'earthin-image-optimizer' ) );
		} elseif ( 'already_optimal' === $status ) {
			echo '<span style="color:#64748b;">' . esc_html__( 'Already Optimal', 'earthin-image-optimizer' ) . '</span>';
		} else {
			$optimize_url = wp_nonce_url(
				admin_url( 'admin-post.php?action=eio_optimize_single&id=' . $post_id ),
				'eio_optimize_' . $post_id
			);
			printf( '<a href="%s" class="button button-small">%s</a>', esc_url( $optimize_url ), esc_html__( 'Optimize', 'earthin-image-optimizer' ) );
		}
	}

	public function handle_single_optimization_action(): void {
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'earthin-image-optimizer' ) );
		}

		$id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		check_admin_referer( 'eio_optimize_' . $id );

		$file = get_attached_file( $id );
		if ( $file ) {
			$this->optimize_attachment( $id, $file );
		}

		wp_safe_redirect( wp_get_referer() ?: admin_url( 'upload.php' ) );
		exit;
	}

	public function handle_single_restore_action(): void {
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'earthin-image-optimizer' ) );
		}

		$id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		check_admin_referer( 'eio_restore_' . $id );

		$this->restore_attachment( $id );

		wp_safe_redirect( wp_get_referer() ?: admin_url( 'upload.php' ) );
		exit;
	}

	public function get_optimization_stats(): array {
		global $wpdb;

		$total_optimized = (int) $wpdb->get_var(
			"SELECT COUNT(post_id) FROM {$wpdb->postmeta} WHERE meta_key = '_eio_status' AND meta_value = 'optimized'"
		);

		$total_saved_bytes = (float) $wpdb->get_var(
			"SELECT SUM(CAST(meta_value AS UNSIGNED)) FROM {$wpdb->postmeta} WHERE meta_key = '_eio_saved_bytes'"
		);

		return array(
			'count'           => $total_optimized,
			'saved_bytes'     => $total_saved_bytes,
			'saved_formatted' => size_format( $total_saved_bytes ),
		);
	}

	public function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'earthin-image-optimizer' ) );
		}

		$settings = $this->get_settings();
		$stats    = $this->get_optimization_stats();
		$has_gd   = extension_loaded( 'gd' );
		$has_webp = function_exists( 'imagewebp' );

		?>
		<div class="earthin-admin-wrap">
			<style>
				.earthin-admin-wrap { max-width: 1200px; margin: 20px 20px 0 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
				.earthin-header { background: #0f172a; color: #ffffff; padding: 22px 28px; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08); flex-wrap: wrap; gap: 16px; }
				.earthin-header-left { display: flex; align-items: center; gap: 14px; }
				.earthin-logo-badge { background: #059669; color: #ffffff; font-weight: 800; font-size: 14px; padding: 6px 12px; border-radius: 8px; letter-spacing: 0.04em; display: inline-flex; align-items: center; gap: 6px; }
				.earthin-title-group h1 { color: #ffffff; margin: 0; font-size: 20px; font-weight: 700; line-height: 1.2; display: flex; align-items: center; gap: 10px; }
				.earthin-version-tag { font-size: 11px; background: rgba(255,255,255,0.15); color: #cbd5e1; padding: 2px 8px; border-radius: 999px; font-weight: 600; }
				.earthin-title-group p { margin: 4px 0 0; color: #94a3b8; font-size: 13px; }
				.earthin-header-links { display: flex; gap: 10px; align-items: center; }
				.earthin-link-btn { background: rgba(255,255,255,0.08); color: #e2e8f0 !important; text-decoration: none !important; font-size: 12px; font-weight: 600; padding: 7px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.12); transition: all 0.15s ease; }
				.earthin-link-btn:hover { background: rgba(255,255,255,0.18); color: #ffffff !important; }
				.earthin-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
				@media(max-width: 900px) { .earthin-grid { grid-template-columns: 1fr; } }
				.earthin-card { background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); padding: 24px 28px; margin-bottom: 24px; }
				.earthin-card-header { padding-bottom: 14px; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
				.earthin-card-title { font-size: 16px; font-weight: 700; color: #0f172a; margin: 0; }
				.earthin-btn-primary { background: #059669 !important; border-color: #059669 !important; color: #ffffff !important; font-weight: 700 !important; padding: 8px 22px !important; border-radius: 8px !important; box-shadow: 0 2px 4px rgba(5,150,105,0.2) !important; text-shadow: none !important; cursor: pointer; }
				.earthin-btn-primary:hover { background: #047857 !important; }
				.earthin-btn-secondary { background: #f8fafc !important; border: 1px solid #cbd5e1 !important; color: #334155 !important; font-weight: 700 !important; padding: 8px 18px !important; border-radius: 8px !important; text-shadow: none !important; cursor: pointer; text-decoration: none; display: inline-block; }
				.earthin-badge-pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
				.earthin-badge-success { background: #ecfdf5; color: #059669; }
				.earthin-badge-danger { background: #fef2f2; color: #ef4444; }
				.earthin-input { border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 14px; }
			</style>

			<header class="earthin-header">
				<div class="earthin-header-left">
					<span class="earthin-logo-badge">🌱 Earthin</span>
					<div class="earthin-title-group">
						<h1>
							<?php echo esc_html__( 'Earthin Image Optimizer', 'earthin-image-optimizer' ); ?>
							<span class="earthin-version-tag">v1.0.0</span>
							<span class="earthin-badge-pill earthin-badge-success"><?php echo $has_webp ? esc_html__( 'WebP Engine Ready', 'earthin-image-optimizer' ) : esc_html__( 'Standard GD Mode', 'earthin-image-optimizer' ); ?></span>
						</h1>
						<p><?php echo esc_html__( 'Zero-cloud local image compression & privacy-safe WebP conversion by Shaurya Joshi.', 'earthin-image-optimizer' ); ?></p>
					</div>
				</div>
				<div class="earthin-header-links">
					<a href="https://plugins.earthin.online" target="_blank" class="earthin-link-btn"><?php echo esc_html__( 'Plugin Hub', 'earthin-image-optimizer' ); ?> &nearr;</a>
					<a href="https://plugins.earthin.online/docs" target="_blank" class="earthin-link-btn"><?php echo esc_html__( 'Documentation', 'earthin-image-optimizer' ); ?></a>
					<a href="https://plugins.earthin.online/support" target="_blank" class="earthin-link-btn"><?php echo esc_html__( 'Support', 'earthin-image-optimizer' ); ?></a>
				</div>
			</header>

			<!-- Server Capability Notice -->
			<?php if ( ! $has_gd ) : ?>
				<div class="notice notice-error inline" style="margin-top:16px; border-radius:8px;">
					<p><strong><?php esc_html_e( 'Requirement Missing:', 'earthin-image-optimizer' ); ?></strong> <?php esc_html_e( 'The PHP GD extension is required for local image compression but is not enabled on your server. Please contact your host to enable php-gd.', 'earthin-image-optimizer' ); ?></p>
				</div>
			<?php endif; ?>

			<!-- Stats Cards -->
			<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin:24px 0;">
				<div class="earthin-card" style="padding:18px 24px; margin-bottom:0;">
					<div style="font-size:26px; font-weight:800; color:#059669;"><?php echo number_format_i18n( $stats['count'] ); ?></div>
					<div style="font-size:12px; font-weight:700; text-transform:uppercase; color:#64748b; margin-top:4px;"><?php esc_html_e( 'Images Compressed', 'earthin-image-optimizer' ); ?></div>
				</div>
				<div class="earthin-card" style="padding:18px 24px; margin-bottom:0;">
					<div style="font-size:26px; font-weight:800; color:#0f172a;"><?php echo esc_html( $stats['saved_formatted'] ?: '0 B' ); ?></div>
					<div style="font-size:12px; font-weight:700; text-transform:uppercase; color:#64748b; margin-top:4px;"><?php esc_html_e( 'Storage Space Saved', 'earthin-image-optimizer' ); ?></div>
				</div>
				<div class="earthin-card" style="padding:18px 24px; margin-bottom:0;">
					<div style="font-size:26px; font-weight:800; color:#0284c7;"><?php echo $has_webp ? esc_html__( 'Hardware WebP', 'earthin-image-optimizer' ) : esc_html__( 'Standard GD', 'earthin-image-optimizer' ); ?></div>
					<div style="font-size:12px; font-weight:700; text-transform:uppercase; color:#64748b; margin-top:4px;"><?php esc_html_e( 'Compression Engine', 'earthin-image-optimizer' ); ?></div>
				</div>
			</div>

			<div class="earthin-grid">
				<div class="earthin-card">
					<div class="earthin-card-header">
						<h2 class="earthin-card-title"><?php esc_html_e( 'Compression & Format Settings', 'earthin-image-optimizer' ); ?></h2>
					</div>

					<form method="post" action="options.php">
						<?php settings_fields( 'eio_settings_group' ); ?>

						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Automatic Upload Compression', 'earthin-image-optimizer' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="<?php echo esc_attr( EIO_OPTION_KEY ); ?>[auto_optimize]" value="1" <?php checked( 1, $settings['auto_optimize'] ); ?>>
										<strong><?php esc_html_e( 'Automatically compress newly uploaded media', 'earthin-image-optimizer' ); ?></strong>
									</label>
									<p class="description"><?php esc_html_e( 'Runs silently on wp_handle_upload to ensure new media is always lean.', 'earthin-image-optimizer' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Preserve Original Backups', 'earthin-image-optimizer' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="<?php echo esc_attr( EIO_OPTION_KEY ); ?>[backup_originals]" value="1" <?php checked( 1, $settings['backup_originals'] ); ?>>
										<?php esc_html_e( 'Store full-quality original copies in protected backup storage', 'earthin-image-optimizer' ); ?>
									</label>
									<p class="description"><?php esc_html_e( 'Enables one-click "Restore Original" from the Media Library at any time.', 'earthin-image-optimizer' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'JPEG Compression Quality', 'earthin-image-optimizer' ); ?></th>
								<td>
									<input type="number" name="<?php echo esc_attr( EIO_OPTION_KEY ); ?>[jpeg_quality]" value="<?php echo esc_attr( $settings['jpeg_quality'] ); ?>" min="50" max="100" class="earthin-input" style="width:100px;">
									<p class="description"><?php esc_html_e( 'Recommended: 82 for the optimal balance between visual crispness and bandwidth reduction.', 'earthin-image-optimizer' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'PNG Compression Level', 'earthin-image-optimizer' ); ?></th>
								<td>
									<input type="number" name="<?php echo esc_attr( EIO_OPTION_KEY ); ?>[png_level]" value="<?php echo esc_attr( $settings['png_level'] ); ?>" min="1" max="9" class="earthin-input" style="width:100px;">
									<p class="description"><?php esc_html_e( 'Zlib lossless compression level (1-9). Recommended: 6.', 'earthin-image-optimizer' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Generate Next-Gen WebP Copies', 'earthin-image-optimizer' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="<?php echo esc_attr( EIO_OPTION_KEY ); ?>[generate_webp]" value="1" <?php checked( 1, $settings['generate_webp'] ); ?> <?php disabled( ! $has_webp ); ?>>
										<?php esc_html_e( 'Create modern WebP copies alongside standard JPEG/PNG', 'earthin-image-optimizer' ); ?>
									</label>
									<p class="description"><?php echo $has_webp ? esc_html__( 'PHP GD WebP encoder active.', 'earthin-image-optimizer' ) : esc_html__( 'WebP requires imagewebp support in PHP GD.', 'earthin-image-optimizer' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Strip EXIF Camera Metadata', 'earthin-image-optimizer' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="<?php echo esc_attr( EIO_OPTION_KEY ); ?>[strip_exif]" value="1" <?php checked( 1, $settings['strip_exif'] ); ?>>
										<?php esc_html_e( 'Remove geolocation, camera model, and creation metadata from images', 'earthin-image-optimizer' ); ?>
									</label>
								</td>
							</tr>
						</table>

						<p class="submit" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #f1f5f9;">
							<input type="submit" class="button earthin-btn-primary" value="<?php esc_attr_e( 'Save Optimizer Settings', 'earthin-image-optimizer' ); ?>" />
						</p>
					</form>
				</div>

				<div>
					<div class="earthin-card">
						<div class="earthin-card-header">
							<h2 class="earthin-card-title"><?php esc_html_e( 'Privacy & Performance', 'earthin-image-optimizer' ); ?></h2>
						</div>
						<p style="font-size:13px; color:#475569; line-height:1.6;">
							🔒 <strong><?php esc_html_e( '100% On-Server Privacy:', 'earthin-image-optimizer' ); ?></strong><br>
							<?php esc_html_e( 'Unlike cloud-based services, your media is processed entirely on your web server and never transmitted to external APIs.', 'earthin-image-optimizer' ); ?>
						</p>

						<div style="margin-top:20px; padding-top:16px; border-top:1px solid #f1f5f9;">
							<a href="<?php echo esc_url( admin_url( 'upload.php?mode=list' ) ); ?>" class="button earthin-btn-secondary">
								<?php esc_html_e( 'View Media Library & Optimize', 'earthin-image-optimizer' ); ?> &rarr;
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

Plugin::instance();

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('upload.php?page=earthin-image-optimizer')) . '">' . __('Settings', 'earthin-image-optimizer') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
