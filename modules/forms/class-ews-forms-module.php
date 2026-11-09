<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class Forms_Module implements Module_Interface {
	public function id() { return 'forms'; }
	public function name() { return __( 'Basic contact form', 'earthin-wp-suite' ); }
	public function description() { return __( 'A simple shortcode form with safe email handling and spam protection.', 'earthin-wp-suite' ); }
	public function is_enabled() { $s = get_option( 'ews_settings', array() ); return ! empty( $s['forms_enabled'] ); }

	public function boot() {
		add_shortcode( 'earthin_contact_form', array( $this, 'render' ) );
		add_action( 'init', array( $this, 'register_post_type' ) );

		if ( is_admin() ) {
			add_action( 'admin_post_ews_delete_submission', array( $this, 'handle_delete_submission' ) );
			add_action( 'admin_post_ews_export_submissions', array( $this, 'handle_export_submissions' ) );
		}
	}

	/**
	 * Register private post type to safely persist form submissions.
	 *
	 * @return void
	 */
	public function register_post_type() {
		register_post_type( 'ews_submission', array(
			'labels'              => array( 'name' => __( 'Form Submissions', 'earthin-wp-suite' ) ),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => false,
			'exclude_from_search' => true,
			'supports'            => array( 'title', 'editor' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		) );
	}

	/**
	 * Retrieve saved form submissions from the database.
	 *
	 * @param int $limit Maximum number of submissions to retrieve.
	 * @return array
	 */
	public static function get_submissions( int $limit = 50 ): array {
		$posts = get_posts( array(
			'post_type'      => 'ews_submission',
			'post_status'    => array( 'private', 'publish' ),
			'posts_per_page' => $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		$submissions = array();
		foreach ( $posts as $p ) {
			$post_id       = is_object( $p ) ? $p->ID : ( (int) $p );
			$post_obj      = is_object( $p ) ? $p : get_post( $post_id );
			$submissions[] = array(
				'id'         => $post_id,
				'date'       => get_the_date( 'Y-m-d H:i:s', $post_id ),
				'name'       => get_post_meta( $post_id, '_ews_sender_name', true ),
				'email'      => get_post_meta( $post_id, '_ews_sender_email', true ),
				'ip'         => get_post_meta( $post_id, '_ews_sender_ip', true ),
				'dispatched' => get_post_meta( $post_id, '_ews_email_dispatched', true ),
				'message'    => $post_obj ? $post_obj->post_content : '',
			);
		}

		return $submissions;
	}

	/**
	 * Delete a specific submission post.
	 *
	 * @param int $id Post ID.
	 * @return bool
	 */
	public static function delete_submission( int $id ): bool {
		$post = get_post( $id );
		if ( ! $post || 'ews_submission' !== $post->post_type ) {
			return false;
		}

		return (bool) wp_delete_post( $id, true );
	}

	/**
	 * Handle admin action to delete an individual submission.
	 *
	 * @return void
	 */
	public function handle_delete_submission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access denied.', 'earthin-wp-suite' ) );
		}

		check_admin_referer( 'ews_delete_submission' );

		$id = isset( $_POST['submission_id'] ) ? (int) $_POST['submission_id'] : 0;
		if ( $id > 0 ) {
			self::delete_submission( $id );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=earthin-wp-suite&tab=forms&ews_forms_msg=deleted' ) );
		exit;
	}

	/**
	 * Export submissions to CSV with formula injection protection.
	 *
	 * @return void
	 */
	public function handle_export_submissions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access denied.', 'earthin-wp-suite' ) );
		}

		check_admin_referer( 'ews_export_submissions' );

		$submissions = self::get_submissions( 1000 );

		if ( ! headers_sent() ) {
			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="earthin-submissions-' . gmdate( 'Y-m-d' ) . '.csv"' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );
		}

		$output = fopen( 'php://output', 'w' );
		if ( false === $output ) {
			exit;
		}

		// CSV Header
		fputcsv( $output, array( 'ID', 'Date', 'Name', 'Email', 'IP Address', 'Email Sent', 'Message' ) );

		foreach ( $submissions as $sub ) {
			// Defend against CSV injection (formula injection).
			$name    = self::sanitize_csv_cell( (string) $sub['name'] );
			$email   = self::sanitize_csv_cell( (string) $sub['email'] );
			$message = self::sanitize_csv_cell( (string) $sub['message'] );

			fputcsv( $output, array(
				$sub['id'],
				$sub['date'],
				$name,
				$email,
				$sub['ip'],
				'1' === (string) $sub['dispatched'] ? 'Yes' : 'No',
				$message,
			) );
		}

		fclose( $output );
		exit;
	}

	/**
	 * Neutralize dangerous formula injection characters in CSV fields.
	 *
	 * @param string $value Cell value.
	 * @return string Safe cell value.
	 */
	public static function sanitize_csv_cell( string $value ): string {
		$dangerous_chars = array( '=', '+', '-', '@', "\t", "\r" );
		if ( ! empty( $value ) && in_array( $value[0], $dangerous_chars, true ) ) {
			return "'" . $value;
		}
		return $value;
	}

	public function render() {
		$message      = '';
		$message_type = 'info';

		if ( isset( $_POST['ews_contact_submit'] ) ) {
			// 1. Nonce validation
			if ( ! isset( $_POST['ews_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ews_contact_nonce'] ) ), 'ews_contact_form' ) ) {
				$message      = __( 'Security check failed. Please refresh the page and try again.', 'earthin-wp-suite' );
				$message_type = 'error';
			} elseif ( ! empty( $_POST['ews_hp'] ) ) {
				// 2. Honeypot check (silently drop bot submissions without notifying bot)
				$message      = __( 'Your message has been sent.', 'earthin-wp-suite' );
				$message_type = 'success';
			} else {
				// 3. Rate limiting by IP: maximum 5 submissions per 10 minutes
				$ip_address = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' ) );
				$rate_key   = 'ews_rl_' . md5( $ip_address );
				$attempts   = (int) get_transient( $rate_key );

				if ( $attempts >= 5 ) {
					$message      = __( 'Too many submissions. Please wait a few minutes before trying again.', 'earthin-wp-suite' );
					$message_type = 'error';
				} else {
					// 4. Sanitize and validate inputs
					$name  = sanitize_text_field( wp_unslash( $_POST['ews_contact_name'] ?? '' ) );
					$email = sanitize_email( wp_unslash( $_POST['ews_contact_email'] ?? '' ) );
					$body  = sanitize_textarea_field( wp_unslash( $_POST['ews_contact_message'] ?? '' ) );

					// Strictly prevent email header injection (strip CR and LF).
					$clean_name  = trim( str_replace( array( "\r", "\n" ), '', $name ) );
					$clean_email = trim( str_replace( array( "\r", "\n" ), '', $email ) );

					if ( ! $clean_name || ! is_email( $clean_email ) || ! $body ) {
						$message      = __( 'Please complete all fields with a valid email address.', 'earthin-wp-suite' );
						$message_type = 'error';
					} else {
						// Increment rate limiter.
						set_transient( $rate_key, $attempts + 1, 10 * MINUTE_IN_SECONDS );

						// Store submission in database to prevent data loss.
						$sub_id = wp_insert_post( array(
							'post_type'    => 'ews_submission',
							'post_status'  => 'private',
							'post_title'   => sprintf( __( 'Submission from %s', 'earthin-wp-suite' ), $clean_name ),
							'post_content' => $body,
						), true );

						if ( ! is_wp_error( $sub_id ) ) {
							update_post_meta( $sub_id, '_ews_sender_name', $clean_name );
							update_post_meta( $sub_id, '_ews_sender_email', $clean_email );
							update_post_meta( $sub_id, '_ews_sender_ip', $ip_address );
						}

						// Dispatch email with safe sanitized headers.
						$admin_email = get_option( 'admin_email' );
						$subject     = sprintf( __( 'Website message from %s', 'earthin-wp-suite' ), $clean_name );
						$headers     = array( 'Reply-To: ' . $clean_name . ' <' . $clean_email . '>' );

						$sent = wp_mail( $admin_email, $subject, $body, $headers );

						if ( ! is_wp_error( $sub_id ) ) {
							update_post_meta( $sub_id, '_ews_email_dispatched', $sent ? '1' : '0' );
						}

						$message      = __( 'Your message has been sent successfully.', 'earthin-wp-suite' );
						$message_type = 'success';
					}
				}
			}
		}

		ob_start();
		if ( $message ) {
			$notice_class = 'success' === $message_type ? 'ews-notice-success' : 'ews-notice-error';
			echo '<p role="status" class="' . esc_attr( $notice_class ) . '">' . esc_html( $message ) . '</p>';
		}
		?>
		<form method="post" class="ews-contact-form">
			<?php wp_nonce_field( 'ews_contact_form', 'ews_contact_nonce' ); ?>
			<div style="display:none !important;" aria-hidden="true">
				<label><?php esc_html_e( 'Leave this field empty', 'earthin-wp-suite' ); ?>
					<input name="ews_hp" type="text" tabindex="-1" autocomplete="off" value="">
				</label>
			</div>
			<p><label><?php esc_html_e( 'Name', 'earthin-wp-suite' ); ?><br><input name="ews_contact_name" type="text" required maxlength="120" class="regular-text"></label></p>
			<p><label><?php esc_html_e( 'Email', 'earthin-wp-suite' ); ?><br><input name="ews_contact_email" type="email" required maxlength="190" class="regular-text"></label></p>
			<p><label><?php esc_html_e( 'Message', 'earthin-wp-suite' ); ?><br><textarea name="ews_contact_message" required maxlength="5000" rows="5" class="large-text"></textarea></label></p>
			<p><button type="submit" name="ews_contact_submit" value="1"><?php esc_html_e( 'Send message', 'earthin-wp-suite' ); ?></button></p>
		</form>
		<?php
		return ob_get_clean();
	}
}
