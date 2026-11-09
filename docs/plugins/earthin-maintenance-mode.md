# Earthin Maintenance Mode

**Developer:** Shaurya Joshi  
**Brand:** Earthin  
**Version:** 0.1.0  
**WordPress Compatibility:** 6.4+  
**PHP Requirement:** 8.1+  
**License:** GPL-2.0-or-later  
**Website:** [https://plugins.earthin.online/plugins/earthin-maintenance-mode](https://plugins.earthin.online/plugins/earthin-maintenance-mode)

---

## 1. Overview

Earthin Maintenance Mode provides a modern, responsive, and SEO-friendly maintenance and coming soon page with built-in administrative bypass.

---

## 2. Real Features Implemented

* **Zero Lockout Architecture**:
  * Users with `manage_options` automatically bypass the maintenance screen and view the live website.
  * Preserves `/wp-admin/`, `wp-login.php`, `wp-register.php`, password reset, and CRON operations.
* **SEO & Crawler Preservation**:
  * Emits HTTP 503 (Service Unavailable) status header with configurable `Retry-After` header (default 3600 seconds) to prevent search engine de-indexing.
  * Emits `X-Robots-Tag: noindex, nofollow, nosnippet`.
* **Design & Customization**:
  * Custom page browser title, headline, and rich HTML message (`wp_kses_post`).
  * Custom logo image URL.
  * Custom background color, accent color, and text color.
  * Pure CSS responsive layout with zero external script dependencies.
* **Live Admin Preview**:
  * Live preview link allowing administrators to inspect the maintenance screen without enabling it for visitors.
