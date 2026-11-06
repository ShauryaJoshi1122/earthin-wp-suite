<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class Forms_Module implements Module_Interface {
	public function id() { return 'forms'; }
	public function name() { return __( 'Basic contact form', 'earthin-wp-suite' ); }
	public function description() { return __( 'A simple shortcode form with safe email handling.', 'earthin-wp-suite' ); }
	public function is_enabled() { $s = get_option( 'ews_settings', array() ); return ! empty( $s['forms_enabled'] ); }
	public function boot() { add_shortcode( 'earthin_contact_form', array( $this, 'render' ) ); }
	public function render() {
		$message = '';
		if ( isset( $_POST['ews_contact_submit'] ) ) {
			if ( ! isset( $_POST['ews_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ews_contact_nonce'] ) ), 'ews_contact_form' ) ) {
				$message = __( 'Please refresh the page and try again.', 'earthin-wp-suite' );
			} else {
				$name = sanitize_text_field( wp_unslash( $_POST['ews_contact_name'] ?? '' ) );
				$email = sanitize_email( wp_unslash( $_POST['ews_contact_email'] ?? '' ) );
				$body = sanitize_textarea_field( wp_unslash( $_POST['ews_contact_message'] ?? '' ) );
				if ( ! $name || ! is_email( $email ) || ! $body ) {
					$message = __( 'Please complete all fields with a valid email address.', 'earthin-wp-suite' );
				} else {
					$sent = wp_mail( get_option( 'admin_email' ), sprintf( __( 'Website message from %s', 'earthin-wp-suite' ), $name ), $body, array( 'Reply-To: ' . $name . ' <' . $email . '>' ) );
					$message = $sent ? __( 'Your message has been sent.', 'earthin-wp-suite' ) : __( 'Email could not be sent. Please try again later.', 'earthin-wp-suite' );
				}
			}
		}
		ob_start();
		if ( $message ) { echo '<p role="status">' . esc_html( $message ) . '</p>'; }
		?>
		<form method="post">
			<?php wp_nonce_field( 'ews_contact_form', 'ews_contact_nonce' ); ?>
			<p><label><?php esc_html_e( 'Name', 'earthin-wp-suite' ); ?><br><input name="ews_contact_name" type="text" required maxlength="120"></label></p>
			<p><label><?php esc_html_e( 'Email', 'earthin-wp-suite' ); ?><br><input name="ews_contact_email" type="email" required maxlength="190"></label></p>
			<p><label><?php esc_html_e( 'Message', 'earthin-wp-suite' ); ?><br><textarea name="ews_contact_message" required maxlength="5000"></textarea></label></p>
			<p><button type="submit" name="ews_contact_submit" value="1"><?php esc_html_e( 'Send message', 'earthin-wp-suite' ); ?></button></p>
		</form>
		<?php
		return ob_get_clean();
	}
}
