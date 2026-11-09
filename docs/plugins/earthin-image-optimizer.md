# Earthin Image Optimizer

**Developer:** Shaurya Joshi  
**Brand:** Earthin  
**Version:** 0.1.0  
**WordPress Compatibility:** 6.4+  
**PHP Requirement:** 8.1+  
**License:** GPL-2.0-or-later  
**Website:** [https://plugins.earthin.online/plugins/earthin-image-optimizer](https://plugins.earthin.online/plugins/earthin-image-optimizer)

---

## 1. Overview

Earthin Image Optimizer is a local-first WordPress image compression engine designed to reclaim disk storage and accelerate page loads without sending media to external cloud APIs or requiring subscription keys.

---

## 2. Real Features Implemented

* **100% Local Compression Engine**: Uses native PHP GD extensions to process images directly on your server.
* **JPEG & PNG Optimization**:
  * Configurable JPEG compression quality (default: 82).
  * Configurable PNG compression level (default: 6, preserving full alpha transparency).
* **Next-Gen WebP Creation**:
  * Automatically creates lightweight `.webp` versions when `imagewebp` is supported by PHP GD.
* **Privacy-Friendly EXIF Stripping**:
  * Strips camera make/model, timestamp, and GPS geolocation metadata.
* **Original Backup Preservation**:
  * Stores full-resolution original copies in protected directory `wp-content/uploads/earthin-image-backups/` protected with `.htaccess` and silence `index.php`.
* **Zero Negative Compression Guarantee**:
  * If re-encoded image is larger than or equal to the original, the original is strictly kept.
* **WordPress Media Library Integration**:
  * Custom column showing savings percentage and disk space saved.
  * One-click "Restore Original" button.

---

## 3. Security Considerations

* Validates MIME types with `wp_get_image_mime`.
* Restricts access to authorized users with `upload_files` and `manage_options` capabilities.
* All actions protected by WordPress nonces.
