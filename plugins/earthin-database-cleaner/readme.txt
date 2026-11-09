=== Earthin Database Cleaner ===
Contributors: ShauryaJoshi1122
Donate link: https://plugins.earthin.online
Tags: database, optimize-database, clean-database, delete-revisions, transients
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A safe, preview-first WordPress database optimization tool to inspect and selectively clean revisions, transients, drafts, and orphaned metadata.

== Description ==

Earthin Database Cleaner inspects your WordPress database for redundant clutter and provides a transparent, preview-first interface for reclaiming storage space safely.

### Cleanable Records
* **Post Revisions**: Historical revisions generated during page and post editing.
* **Auto Drafts**: Abandoned automatic drafts that were never published.
* **Trashed Posts**: Items lingering in post, page, and custom post type trash.
* **Spam & Trashed Comments**: Unsolicited spam comments and discarded comments.
* **Expired Transients**: Transient cache keys whose validity timestamp has already passed.
* **Orphaned Post Metadata**: Postmeta records associated with posts that no longer exist.
* **Orphaned Comment Metadata**: Commentmeta records associated with deleted comments.

### Safety Guarantee
* **Never Automated**: Deletions never occur automatically in the background.
* **Preview Inventory**: Inspect exact counts before making any changes.
* **Selective Purging**: Choose exactly which categories to clean.
* **Explicit Admin Confirmation**: Requires an explicit confirmation checkbox and admin capability check (`manage_options`).
* **Prepared Queries**: All database queries are prepared and strictly sanitized.

Developed by Shaurya Joshi for the Earthin ecosystem.

== Installation ==

1. Upload the `earthin-database-cleaner` folder to `/wp-content/plugins/`.
2. Activate through the **Plugins** screen in WordPress.
3. Access **Tools > Earthin DB Cleaner** to preview cleanable rows and optimize.

== Frequently Asked Questions ==

= Does it delete published posts or user data? =
No. It only targets orphaned metadata, trash, spam, expired transients, and revision history.

= Should I backup my database first? =
Yes. Although these routines are rigorously scoped, creating a database backup prior to performing any database cleanup is always recommended.

== Changelog ==

= 0.1.0 =
* Initial release with database overview, preview inventory, 8 cleanable categories, explicit confirmation guard, and detailed cleanup reporting.
