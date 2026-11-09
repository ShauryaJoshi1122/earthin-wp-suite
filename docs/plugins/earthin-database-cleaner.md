# Earthin Database Cleaner

**Developer:** Shaurya Joshi  
**Brand:** Earthin  
**Version:** 0.1.0  
**WordPress Compatibility:** 6.4+  
**PHP Requirement:** 8.1+  
**License:** GPL-2.0-or-later  
**Website:** [https://plugins.earthin.online/plugins/earthin-database-cleaner](https://plugins.earthin.online/plugins/earthin-database-cleaner)

---

## 1. Overview

Earthin Database Cleaner is a preview-first WordPress database maintenance utility that identifies redundant revisions, orphaned metadata, and expired transients, allowing administrators to selectively clean them.

---

## 2. Real Features Implemented

* **Database Metrics Overview**:
  * Real-time calculation of total table count (prefix-filtered), total database size (MB), and recoverable free overhead.
* **Preview Inventory & Selective Purging**:
  * Post revisions
  * Auto drafts
  * Trashed posts
  * Spam comments
  * Trashed comments
  * Expired transients (calculates expired timeouts against current `time()`)
  * Orphaned postmeta (records with no corresponding post ID)
  * Orphaned commentmeta (records with no corresponding comment ID)
* **Explicit Safety Guards**:
  * Never deletes records automatically.
  * Mandatory administrator confirmation checkbox.
  * Browser confirmation prompt.
  * Capability check strictly enforcing `manage_options`.
  * Nonce verification on all cleanup requests.
* **Cleanup Logging**:
  * Reports exact rows purged per category upon completion.

---

## 3. Security Considerations

* All queries prepared using `$wpdb->prepare` and core schema table aliases (`$wpdb->posts`, `$wpdb->comments`, `$wpdb->postmeta`).
* No arbitrary SQL execution allowed.
