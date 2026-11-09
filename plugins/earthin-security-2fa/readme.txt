=== Earthin Security 2FA ===
Contributors: ShauryaJoshi1122
Donate link: https://plugins.earthin.online
Tags: 2fa, two-factor authentication, security, login, totp
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Two-factor authentication (TOTP) security suite compatible with Google Authenticator, Authy, and 1Password with emergency scratch codes.

== Description ==

Earthin Security 2FA secures WordPress user logins using time-based one-time password (TOTP) authentication standard RFC 6238. Protect administrative and editor accounts from credential stuffing, dictionary attacks, and stolen passwords.

Features:
* RFC 6238 compliant TOTP algorithm with drift tolerance.
* Scannable QR code generation for rapid authenticator app pairing.
* Works with Google Authenticator, Authy, 1Password, and Microsoft Authenticator.
* Generates 5 one-time recovery scratch codes upon activation.
* Seamless login flow injection.

== Installation ==

1. Upload `earthin-security-2fa` to the `/wp-content/plugins/` directory.
2. Activate via 'Plugins' in WordPress.
3. Navigate to 'Users' > 'Profile' and scan the QR code to activate 2FA.

== Changelog ==

= 1.0.0 =
* Initial production release.
