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
