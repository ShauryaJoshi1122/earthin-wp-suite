=== Earthin Maintenance Mode ===
Contributors: ShauryaJoshi1122
Donate link: https://plugins.earthin.online
Tags: maintenance-mode, coming-soon, under-construction, 503-status, administration
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A clean, responsive, and SEO-friendly maintenance mode and coming soon page with administrative bypass.

== Description ==

Earthin Maintenance Mode allows website administrators to display a modern, high-performance maintenance page to regular visitors while working behind the scenes.

### Features
* **Zero Lockout Architecture**: Automatically permits logged-in administrators to browse and test the frontend normally while visitors see the maintenance screen.
* **Safe Authentication Exclusions**: Never blocks `wp-login.php`, `wp-admin/`, password recovery, or REST API authentication endpoints.
* **Search Engine Friendly**: Sends proper HTTP 503 (Service Unavailable) status headers with configurable `Retry-After` intervals to preserve your site's search engine rankings.
* **Coming Soon / Preview Support**: Optional HTTP 200 OK mode for pre-launch websites.
* **Full Design Customization**: Customize page titles, headline, rich HTML message, logo image URL, background color, accent color, and typography.
* **Live Admin Preview**: Preview the maintenance page in a new tab without turning it on for visitors.
* **Lightweight**: Zero external dependencies and fast pure-CSS rendering.

Developed by Shaurya Joshi for the Earthin ecosystem.

== Installation ==

1. Upload the `earthin-maintenance-mode` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Settings > Earthin Maintenance** to configure your maintenance message and activate when ready.

== Frequently Asked Questions ==

= Can administrators be locked out? =
No. The plugin explicitly verifies `manage_options` capability and never intercepts `/wp-admin/` or `wp-login.php`.

= How does it affect SEO? =
When active, it sends an HTTP 503 status code with a `Retry-After` header. This notifies Google, Bing, and other search engines that your site is undergoing temporary maintenance, preserving your index rankings and preventing de-indexing.

== Changelog ==

= 0.1.0 =
* Initial release with administrative bypass, HTTP 503 response header handling, color customization, and live preview.
