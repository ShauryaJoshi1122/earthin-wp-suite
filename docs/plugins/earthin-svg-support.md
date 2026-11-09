# Earthin SVG Safe Sanitizer

- **Slug:** `earthin-svg-support`
- **Author:** Shaurya Joshi
- **Author URI:** https://plugins.earthin.online
- **Support URI:** https://plugins.earthin.online/support
- **License:** GPL-2.0-or-later
- **Requires at least:** 6.4
- **Requires PHP:** 8.1

## Overview
Earthin SVG Safe Sanitizer enables secure SVG vector file uploads to the WordPress Media Library with an automated XML verification pipeline that rejects entity expansions and strips script attacks.

## Key Features
- Whitelists `.svg` and `.svgz` mime types cleanly via `upload_mimes`.
- XML parser that strips nested `<script>`, `<iframe>`, and `<foreignobject>` tags.
- Eliminates inline DOM event handlers (`onload`, `onclick`, `onerror`).
- Fixes WordPress media grid display to render vector thumbnails cleanly.
