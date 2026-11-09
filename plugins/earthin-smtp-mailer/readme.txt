=== Earthin SMTP Mailer ===
Contributors: ShauryaJoshi1122
Donate link: https://plugins.earthin.online
Tags: smtp, email, mail, phpmailer, transactional email
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Re-routes all WordPress outbound emails through authenticated custom SMTP servers, with real-time test mailer and error tracking.

== Description ==

Earthin SMTP Mailer replaces the unreliable default PHP `mail()` function with a clean, authenticated SMTP connection. It hooks directly into WordPress `phpmailer_init` to guarantee delivery of registration emails, password resets, contact form submissions, and order receipts.

Features:
* Direct integration with PHPMailer.
* Supports SSL, TLS, and STARTTLS encryption protocols.
* Custom From Email and From Name overriding.
* Secure password handling with zero plain-text leaks in HTML.
* Built-in instant Test Email dispatch tool.

== Installation ==

1. Upload `earthin-smtp-mailer` to the `/wp-content/plugins/` directory.
2. Activate the plugin via the 'Plugins' menu in WordPress.
3. Configure your host, port, and credentials in 'Settings' > 'Earthin SMTP'.
4. Send a test email to verify successful delivery.

== Changelog ==

= 1.0.0 =
* Initial release of Earthin SMTP Mailer.
