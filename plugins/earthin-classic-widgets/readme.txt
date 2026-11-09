=== Earthin Classic Widgets ===
Contributors: ShauryaJoshi1122
Donate link: https://plugins.earthin.online
Tags: classic-widgets, widgets, block-widgets, disable-gutenberg-widgets, appearance
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Restores the traditional WordPress widget administration screen and disables the block-based widget editor.

== Description ==

Earthin Classic Widgets restores the classic WordPress widgets settings screens in Appearance > Widgets and the Customizer.

When active, it cleanly disables the block-based widget editor using the official `use_widgets_block_editor` WordPress filter, preserving complete theme and legacy widget compatibility.

Developed by Shaurya Joshi for the Earthin ecosystem.

== Installation ==

1. Upload the `earthin-classic-widgets` directory to `/wp-content/plugins/`.
2. Activate via WordPress **Plugins** screen.
3. Your traditional widget management interface is immediately active. Settings can be adjusted under **Settings > Earthin Widgets**.

== Changelog ==

= 0.1.0 =
* Initial release with official `use_widgets_block_editor` filter integration, administrative toggle, and safe fallback.
