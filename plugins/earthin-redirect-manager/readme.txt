=== Earthin Redirect Manager ===
Contributors: ShauryaJoshi1122
Donate link: https://plugins.earthin.online
Tags: redirects, 301-redirects, 302-redirects, 404-redirect, url-manager
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Fast, loop-protected 301/302 URL redirect manager with hit tracking, CSV import/export, and zero database bloat.

== Description ==

Earthin Redirect Manager provides a clean, standalone administrative interface to create, monitor, and manage 301 and 302 URL redirects.

### Features
* **Loop Prevention**: Automatically analyzes destination targets to stop self-referencing redirect loops before rules are saved.
* **Hit Counter & Analytics**: Records hit counters and last-used timestamps for each redirect rule.
* **Toggle Rules**: Temporarily disable rules without deleting them.
* **Status Code Selection**: Full support for 301 (Permanent), 302 (Temporary), 307 (Temporary), and 308 (Permanent).
* **CSV Export & Import**: Easily migrate redirect maps between staging and production environments.
* **Mutual Conflict Guard**: Automatically yields frontend processing if Earthin WP Suite's Redirect module is active, preventing duplicate redirect execution.

Developed by Shaurya Joshi for the Earthin ecosystem.

== Installation ==

1. Upload the `earthin-redirect-manager` folder to the `/wp-content/plugins/` directory.
2. Activate via WordPress **Plugins** screen.
3. Access **Tools > Earthin Redirects** to manage your URL redirect rules.

== Frequently Asked Questions ==

= Can I use this together with Earthin WP Suite? =
Yes. If both are active and Earthin WP Suite has redirects enabled, Earthin Redirect Manager detects it automatically and yields frontend execution to prevent duplicate hooks.

= How are rules stored? =
Rules are stored efficiently in a single serialized option record in the WordPress options table, keeping database overhead minimal.

== Changelog ==

= 0.1.0 =
* Initial standalone release with loop detection, hit counting, rule toggling, CSV import/export, and mutual conflict guard.
