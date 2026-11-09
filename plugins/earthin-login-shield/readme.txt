=== Earthin Login Shield ===
Contributors: ShauryaJoshi1122
Donate link: https://plugins.earthin.online
Tags: security, login attempts, brute force, rate limit, xmlrpc
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Defend your WordPress site against brute force login attacks and credential stuffing with intelligent IP lockouts and XML-RPC shielding.

== Description ==

**Earthin Login Shield** provides robust brute-force security for WordPress login portals. It detects repeated failed authentication attempts, enforces customizable temporary and extended IP lockouts, and disables risky XML-RPC interfaces.

### Key Features

* **Configurable Lockout Rules**: Specify retry limit, lockout duration, and long lockout penalties.
* **IP Whitelist & Blacklist**: Safeguard trusted administrator IPs while permanently banning malicious networks.
* **XML-RPC Shield**: Turn off XML-RPC brute-force entry points with a single toggle.
* **Audit Logs**: Review the last 50 failed attempts with exact timestamps and usernames.
* **Zero External Dependencies**: Operates completely on your server with no third-party cloud lock-in.

== Installation ==

1. Upload the `earthin-login-shield` folder to `/wp-content/plugins/`.
2. Activate via the WordPress 'Plugins' page.
3. Fine-tune your security thresholds under Settings -> Login Shield.

== Changelog ==

= 1.0.0 =
* Initial public release with IP lockout engine, audit logging, and XML-RPC defense.
