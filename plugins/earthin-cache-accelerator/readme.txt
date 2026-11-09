=== Earthin Cache Accelerator ===
Contributors: ShauryaJoshi1122
Donate link: https://plugins.earthin.online
Tags: cache, caching, performance, speed, static cache
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

High-performance static HTML page caching engine with automatic cache invalidation, cache preloading, and browser header tuning.

== Description ==

Earthin Cache Accelerator generates static HTML files of your WordPress pages to deliver sub-millisecond server response times. It significantly reduces database queries, CPU load, and bandwidth costs.

Features:
* Static HTML page caching with disk write buffers.
* Configurable cache time-to-live (TTL).
* Automatic cache invalidation upon post, page, or comment updates.
* Intelligent bypass for logged-in users and dynamic request verbs.
* Browser caching header configuration (`Cache-Control`).
* One-click manual cache purge button.

== Installation ==

1. Upload the `earthin-cache-accelerator` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure cache TTL and rules under 'Settings' > 'Earthin Cache'.

== Changelog ==

= 1.0.0 =
* Initial production release under the Earthin Suite ecosystem.
