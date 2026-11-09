=== Earthin WP Suite ===
Contributors: ShauryaJoshi1122
Donate link: https://plugins.earthin.online
Tags: seo, security, redirects, forms, sitemap, performance, administration
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 0.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A comprehensive, modular WordPress management suite developed under the Earthin brand by Shaurya Joshi.

== Description ==

Earthin WP Suite is an all-in-one WordPress optimization and website management suite featuring a decoupled, modular architecture. Every module operates independently and can be toggled without performance overhead or cross-module interference.

### Core Modules Included

* **SEO Management**: Custom meta titles, meta descriptions, canonical URLs, and robots indexing directives for posts and pages.
* **Schema Markup**: Structured JSON-LD breadcrumb and article schema generation with automated escaping.
* **XML Sitemaps**: Dynamic, search-engine compliant XML sitemap generation with hourly cache invalidation.
* **Social Previews**: Open Graph and Twitter Card metadata generation with fallbacks.
* **Security Hardening**: Configurable HTTP security headers including X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, and Referrer-Policy.
* **URL Redirects**: Performant 301, 302, 307, and 308 redirect manager with infinite loop prevention, external destination validation, and hit analytics.
* **Safe Contact Forms**: Lightweight frontend form shortcode with anti-spam honeypot, email header injection protection, and CSV export.
* **RSS Automation**: Scheduled draft publishing from external RSS feeds with hardened SSRF protection against private network queries.

Developed by Shaurya Joshi for the Earthin ecosystem.

== Installation ==

1. Upload the `earthin-wp-suite` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Access the **Earthin Suite** menu in the WordPress admin bar to configure active modules and settings.

== Frequently Asked Questions ==

= Does disabling a module remove its data? =
No. Disabling a module safely unhooks its runtime callbacks while preserving all saved configuration and historical logs.

= Are redirects safe from infinite loops? =
Yes. The redirects module validates that source and destination paths do not resolve to the same relative URI before saving.

= Where can I get support or report issues? =
Visit our official support desk at https://plugins.earthin.online/support or submit an issue on GitHub.

== Changelog ==

= 0.2.0 =
* Feature: Tabbed administration dashboard with Modules & Controls, Redirects Manager, Form Inquiries, and System Status views.
* Feature: Redirects administration interface with rule creation, loop validation, status code selection, and hit tracking.
* Feature: Form inquiries viewer with submission persistence, deletion controls, and formula-injection-safe CSV export.
* Feature: System diagnostics screen reporting environment metrics, memory limits, and third-party SEO plugin conflict status.
* Security: Hardened RSS import against Server-Side Request Forgery (SSRF) and private network requests.
* Security: Added anti-spam honeypot and email header injection protection to the forms module.
* Bugfix: Resolved issue where redirects failed to trigger on 404 requests.
* Bugfix: Fixed external redirect handling and added support for safe destination validation.
* Enhanced schema generation with secure script tag escaping and proper article typing.
* Added activation and deactivation hooks with version verification.
* Synchronized version numbers across bootstrap, documentation, and metadata.
* Updated author attribution to Shaurya Joshi with official Earthin Plugin Hub references.

= 0.1.0 =
* Initial modular release with core registry, settings framework, and basic SEO/Security tools.
