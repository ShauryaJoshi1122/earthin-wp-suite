=== Earthin Code Snippets ===
Contributors: ShauryaJoshi1122
Donate link: https://plugins.earthin.online
Tags: snippets, code snippets, php, css, javascript
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Clean and safe code snippet manager for executing custom PHP, CSS, JavaScript, and HTML with error protection.

== Description ==

Earthin Code Snippets allows website owners to extend WordPress functionality without modifying the active theme's `functions.php` or risking update overrides. Built with a built-in error boundary, syntax errors automatically deactivate the offending snippet instead of crashing the site.

Features:
* Add custom PHP functions, filters, and actions safely.
* Inject custom CSS stylesheets into `<head>`.
* Inject JavaScript scripts into `wp_footer`.
* Run snippets globally, admin-only, or frontend-only.
* Automatic fatal error trap to prevent site lockouts.

== Installation ==

1. Upload `earthin-code-snippets` to the `/wp-content/plugins/` directory.
2. Activate the plugin through 'Plugins' in WordPress.
3. Access 'Tools' > 'Earthin Snippets' to create custom snippets.

== Changelog ==

= 1.0.0 =
* Initial production release.
