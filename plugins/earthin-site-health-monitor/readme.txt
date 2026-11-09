=== Earthin Site Health Monitor ===
Contributors: ShauryaJoshi1122
Donate link: https://plugins.earthin.online
Tags: site-health, diagnostics, security, php-check, server-environment
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Real-time diagnostics, environment verification, security audits, and actionable health metrics for WordPress.

== Description ==

Earthin Site Health Monitor audits your WordPress server environment, configuration, and security settings, surfacing actionable insights with zero administrative clutter.

### Key Checks
* **PHP & Runtime Compliance**: Verifies PHP 8.1+ support and warns if running outdated End-of-Life PHP runtimes.
* **WordPress Core Version**: Confirms minimum required core versions and compatibility.
* **HTTPS & SSL Encryption**: Assesses whether transmission security is fully enforced.
* **Debug Exposure Guard**: Identifies dangerous `WP_DEBUG_DISPLAY` configurations that leak server paths to visitors.
* **Memory Limits**: Audits memory allocations to prevent runtime crashes during heavy tasks.
* **Upload Permissions**: Tests whether the media upload directory is writable.
* **Database Table Prefix**: Flags default `wp_` prefixes that make automated blind SQL attacks simpler.
* **Plugin Hygiene**: Counts inactive and dormant plugins that consume disk storage or pose vulnerabilities.

Developed by Shaurya Joshi for the Earthin ecosystem.

== Installation ==

1. Upload the `earthin-site-health-monitor` directory to `/wp-content/plugins/`.
2. Activate the plugin via WordPress **Plugins** screen.
3. Access **Tools > Earthin Site Health** to review your live environment diagnostics.

== Frequently Asked Questions ==

= Does this plugin expose server information to public visitors? =
No. Diagnostics are only accessible to administrators with `manage_options` capability.

= Does this plugin slow down the website? =
No. Checks are performed on-demand only when an administrator views the Site Health dashboard.

== Changelog ==

= 0.1.0 =
* Initial release with 8 core technical environment checks, categorized health badges, and administrative diagnostic dashboard.
