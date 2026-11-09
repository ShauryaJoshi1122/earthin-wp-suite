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
		<div class="wrap">
			<h1><?php esc_html_e( 'Earthin Image Optimizer', 'earthin-image-optimizer' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Compress images locally on your server without sending sensitive media to external third-party cloud APIs.', 'earthin-image-optimizer' ); ?>
			</p>

			<!-- Server Capability Notice -->
			<?php if ( ! $has_gd ) : ?>
				<div class="notice notice-error inline" style="margin-top:16px;">
					<p><strong><?php esc_html_e( 'Requirement Missing:', 'earthin-image-optimizer' ); ?></strong> <?php esc_html_e( 'The PHP GD extension is required for local image compression but is not enabled on your server. Please contact your host to enable php-gd.', 'earthin-image-optimizer' ); ?></p>
				</div>
			<?php endif; ?>

			<!-- Stats Cards -->
			<div style="display:flex; gap:16px; margin:24px 0; flex-wrap:wrap;">
				<div style="flex:1; min-width:200px; background:#ffffff; border:1px solid #cbd5e1; border-radius:8px; padding:16px;">
					<div style="font-size:24px; font-weight:700; color:#10b981;"><?php echo number_format_i18n( $stats['count'] ); ?></div>
					<div style="font-size:13px; color:#64748b;"><?php esc_html_e( 'Images Compressed', 'earthin-image-optimizer' ); ?></div>
				</div>
				<div style="flex:1; min-width:200px; background:#ffffff; border:1px solid #cbd5e1; border-radius:8px; padding:16px;">
					<div style="font-size:24px; font-weight:700; color:#0f172a;"><?php echo esc_html( $stats['saved_formatted'] ?: '0 B' ); ?></div>
					<div style="font-size:13px; color:#64748b;"><?php esc_html_e( 'Disk Space Reclaimed', 'earthin-image-optimizer' ); ?></div>
				</div>
				<div style="flex:1; min-width:200px; background:#ffffff; border:1px solid #cbd5e1; border-radius:8px; padding:16px;">
					<div style="font-size:24px; font-weight:700; color:#6366f1;"><?php echo $has_webp ? esc_html__( 'Supported', 'earthin-image-optimizer' ) : esc_html__( 'Unavailable', 'earthin-image-optimizer' ); ?></div>
					<div style="font-size:13px; color:#64748b;"><?php esc_html_e( 'Server WebP Engine', 'earthin-image-optimizer' ); ?></div>
				</div>
			</div>

			<form method="post" action="options.php" style="margin-top:20px;">
				<?php settings_fields( 'eio_settings_group' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Automatic Upload Compression', 'earthin-image-optimizer' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( EIO_OPTION_KEY ); ?>[auto_optimize]" value="1" <?php checked( 1, $settings['auto_optimize'] ); ?>>
								<?php esc_html_e( 'Automatically compress newly uploaded images', 'earthin-image-optimizer' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'When enabled, images added to the media library are optimized on upload.', 'earthin-image-optimizer' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Preserve Original Backups', 'earthin-image-optimizer' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( EIO_OPTION_KEY ); ?>[backup_originals]" value="1" <?php checked( 1, $settings['backup_originals'] ); ?>>
								<?php esc_html_e( 'Store full-quality original copies in protected backup storage', 'earthin-image-optimizer' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Enables one-click "Restore Original" from the WordPress Media Library at any time.', 'earthin-image-optimizer' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'JPEG Compression Quality', 'earthin-image-optimizer' ); ?></th>
						<td>
							<input type="number" name="<?php echo esc_attr( EIO_OPTION_KEY ); ?>[jpeg_quality]" value="<?php echo esc_attr( $settings['jpeg_quality'] ); ?>" min="50" max="100" class="small-text">
							<p class="description"><?php esc_html_e( 'Recommended: 82 for the optimal balance between visual clarity and file size.', 'earthin-image-optimizer' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'PNG Compression Level', 'earthin-image-optimizer' ); ?></th>
						<td>
							<input type="number" name="<?php echo esc_attr( EIO_OPTION_KEY ); ?>[png_level]" value="<?php echo esc_attr( $settings['png_level'] ); ?>" min="1" max="9" class="small-text">
							<p class="description"><?php esc_html_e( 'Zlib compression level (1-9). Recommended: 6.', 'earthin-image-optimizer' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Generate Next-Gen WebP Copies', 'earthin-image-optimizer' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( EIO_OPTION_KEY ); ?>[generate_webp]" value="1" <?php checked( 1, $settings['generate_webp'] ); ?> <?php disabled( ! $has_webp ); ?>>
								<?php esc_html_e( 'Create next-gen WebP version alongside JPEG/PNG', 'earthin-image-optimizer' ); ?>
							</label>
							<p class="description"><?php echo $has_webp ? esc_html__( 'PHP GD WebP encoder detected and ready.', 'earthin-image-optimizer' ) : esc_html__( 'WebP generation requires imagewebp support in PHP GD.', 'earthin-image-optimizer' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Strip EXIF Camera Metadata', 'earthin-image-optimizer' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( EIO_OPTION_KEY ); ?>[strip_exif]" value="1" <?php checked( 1, $settings['strip_exif'] ); ?>>
								<?php esc_html_e( 'Remove geolocation, camera model, and creation metadata from images', 'earthin-image-optimizer' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Improves visitor privacy and reduces file weight without changing pixel data.', 'earthin-image-optimizer' ); ?></p>
						</td>
					</tr>
				</table>

				<div style="margin-top:20px;">
					<?php submit_button( __( 'Save Optimizer Settings', 'earthin-image-optimizer' ) ); ?>
				</div>
			</form>
		</div>
		<?php
	}
}

Plugin::instance();

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function() {
     = '<a href="' . esc_url(admin_url('upload.php?page=earthin-image-optimizer')) . '">' . __('Settings', 'earthin-image-optimizer') . '</a>';
    array_unshift(, );
    return ;
});
