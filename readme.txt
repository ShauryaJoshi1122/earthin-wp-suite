=== Earthin WP Suite ===
Contributors: earthin
Tags: wordpress, seo, social-sharing, website-management
Requires at least: 6.4
Requires PHP: 8.1
Stable tag: 0.2.0
License: GPLv2 or later

A modular WordPress management suite by Earthin.

== Description ==
Earthin WP Suite provides a modular architecture with independent modules for SEO, social previews, structured data schema, XML sitemaps, security headers, URL redirects, safe contact forms, and RSS draft automation.

== Installation ==
1. Upload the plugin folder to the WordPress plugins directory (`wp-content/plugins/earthin-wp-suite`).
2. Activate Earthin WP Suite through the 'Plugins' menu in WordPress.
3. Open Earthin Suite in the WordPress admin menu to configure enabled modules.

== Changelog ==
= 0.2.0 =
* Security: Hardened RSS import against Server-Side Request Forgery (SSRF) and private network requests.
* Security: Added anti-spam honeypot and email header injection protection to the forms module.
* Bugfix: Resolved issue where redirects failed to trigger on 404 requests.
* Bugfix: Fixed external redirect handling and added support for safe destination validation.
* Enhanced schema generation with secure script tag escaping and proper article typing.
* Added activation and deactivation hooks with version verification.
* Synchronized version numbers across bootstrap, documentation, and metadata.

= 0.1.0 =
* Added plugin bootstrap, module interface and registry.
* Added administrator dashboard and module toggles.
* Preserved settings during uninstall.
