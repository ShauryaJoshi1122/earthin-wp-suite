# Earthin Cache Accelerator

- **Slug:** `earthin-cache-accelerator`
- **Author:** Shaurya Joshi
- **Author URI:** https://plugins.earthin.online
- **Support URI:** https://plugins.earthin.online/support
- **License:** GPL-2.0-or-later
- **Requires at least:** 6.4
- **Requires PHP:** 8.1

## Overview
Earthin Cache Accelerator provides static HTML disk caching for WordPress. By serving cached static pages directly to visitors, TTFB (Time To First Byte) is dropped to milliseconds while shielding the WordPress database from heavy traffic spikes.

## Key Features
- High-performance static HTML disk buffer.
- Configurable cache TTL with safe minimum threshold.
- Auto-purge hooks on post publish and comment changes.
- Bypass mechanisms for logged-in administrators and dynamic POST verbs.
- Direct disk usage and cached file statistics in WordPress admin.
- Browser cache HTTP header injection (`Cache-Control`, `X-Earthin-Cache`).
