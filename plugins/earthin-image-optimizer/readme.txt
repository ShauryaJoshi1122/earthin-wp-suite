=== Earthin Image Optimizer ===
Contributors: ShauryaJoshi1122
Donate link: https://plugins.earthin.online
Tags: image-optimizer, compress-images, webp, convert-webp, optimize-jpeg
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

High-efficiency local WordPress image optimization engine with lossless/lossy compression, WebP conversion, and original backup protection.

== Description ==

Earthin Image Optimizer reduces media file sizes directly on your WordPress server using native PHP image libraries. Your images and visitor privacy are never exposed to external cloud APIs or recurring SaaS subscriptions.

### Key Capabilities
* **100% Local Optimization**: Uses PHP GD engine on your server for fast processing with zero API keys or external quotas.
* **Safe Original Preservation**: Automatically preserves full-resolution backup copies in protected upload storage (`uploads/earthin-image-backups/`).
* **One-Click Media Library Restoration**: Revert back to original image files at any time directly from the WordPress Media table.
* **No Negative Compression Guarantee**: If compressed output is equal to or larger than the original, the original file is strictly retained so files never grow.
* **Next-Gen WebP Conversion**: Automatically produces lightweight `.webp` counterparts for compatible browsers when supported by PHP GD.
* **Privacy-Friendly EXIF Stripping**: Removes embedded camera model, location, and serial number metadata.
* **Real Disk Space Statistics**: Accurately tracks verified bytes reclaimed and total images optimized.

Developed by Shaurya Joshi for the Earthin ecosystem.

== Installation ==

1. Upload the `earthin-image-optimizer` folder to the `/wp-content/plugins/` directory.
2. Activate via WordPress **Plugins** screen.
3. Access **Media > Earthin Optimizer** to configure compression quality and backup preferences.

== Frequently Asked Questions ==

= Does this plugin require an API key or monthly subscription? =
No. It compresses images locally using your server's PHP GD library with zero external calls or usage limits.

= Can I restore original images if needed? =
Yes. If "Preserve Original Backups" is enabled (default), a copy of the original file is stored safely and can be restored from the Media Library with one click.

== Changelog ==

= 0.1.0 =
* Initial release with local GD compression, EXIF stripping, WebP creation, original file backup preservation, and Media Library integration.
