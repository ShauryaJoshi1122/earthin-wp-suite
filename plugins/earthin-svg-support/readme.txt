=== Earthin SVG Safe Sanitizer ===
Contributors: ShauryaJoshi1122
Donate link: https://plugins.earthin.online
Tags: svg, sanitize, upload svg, media library, security
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enables SVG uploads in the Media Library with an automated XML sanitizer blocking scripts, XXE attacks, and inline malicious handlers.

== Description ==

By default, WordPress blocks SVG uploads due to XML-based cross-site scripting (XSS) risks. Earthin SVG Safe Sanitizer safely unlocks SVG vector graphics by passing every uploaded vector file through a rigorous parser that scrubs `<script>` tags, inline event handlers, XXE entity exploits, and malicious data URIs.

Features:
* Unlocks SVG and SVGZ vector uploads for users with `upload_files` capability.
* Real-time XML sanitizer stripping XSS payloads and script injections.
* Protection against XML External Entity (XXE) and billion laughs expansion attacks.
* Automatic CSS rules rendering crisp SVG previews in the Media Library grid.

== Installation ==

1. Upload `earthin-svg-support` to `/wp-content/plugins/`.
2. Activate via 'Plugins' in WordPress.
3. Upload `.svg` files directly via Media > Add New.

== Changelog ==

= 1.0.0 =
* Initial production release.
