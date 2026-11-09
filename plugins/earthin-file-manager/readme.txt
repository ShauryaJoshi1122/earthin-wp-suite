=== Earthin File Manager ===
Contributors: ShauryaJoshi1122
Donate link: https://plugins.earthin.online
Tags: file manager, files, editor, explorer, permissions
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Secure in-dashboard file explorer and editor with strict path sanitization, traversal protection, and permissions inspector.

== Description ==

Earthin File Manager allows authorized site administrators to inspect directory trees, view file permissions, and edit code assets directly within the WordPress dashboard without opening an external FTP client.

Features:
* Traversal barrier: strict `realpath` containment to `ABSPATH`.
* File permission inspector displaying octal attributes (0644, 0755).
* Integrated code editor for `.php`, `.css`, `.js`, `.json`, and `.html`.
* Size formatting and timestamp auditing.
* Capability gating restricted exclusively to `manage_options`.

== Installation ==

1. Upload `earthin-file-manager` to the `/wp-content/plugins/` directory.
2. Activate via 'Plugins' in WordPress.
3. Access 'Tools' > 'Earthin Files' to manage server files.

== Changelog ==

= 1.0.0 =
* Initial production release.
