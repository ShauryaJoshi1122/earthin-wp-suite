# Earthin Redirect Manager

**Developer:** Shaurya Joshi  
**Brand:** Earthin  
**Version:** 0.1.0  
**WordPress Compatibility:** 6.4+  
**PHP Requirement:** 8.1+  
**License:** GPL-2.0-or-later  
**Website:** [https://plugins.earthin.online/plugins/earthin-redirect-manager](https://plugins.earthin.online/plugins/earthin-redirect-manager)

---

## 1. Overview

Earthin Redirect Manager is a standalone, lightweight, loop-protected 301 and 302 URL redirection manager with built-in hit tracking and CSV import/export capabilities.

---

## 2. Real Features Implemented

* **Exact-Path Redirection**:
  * Clean route matching on relative request URIs.
  * Status code support: 301 (Permanent), 302 (Temporary), 307 (Temporary), 308 (Permanent).
* **Automated Loop Prevention**:
  * Validates destination targets to prevent self-referencing redirect loops.
* **Hit Counter & Activity Tracking**:
  * Real-time hit tracking counter for each rule.
* **Toggle Rules**:
  * Disable individual redirects temporarily without deleting them.
* **CSV Data Portability**:
  * One-click CSV export.
  * Robust CSV import with sanitization and validation.
* **Mutual Conflict Guard**:
  * Automatically yields execution if `earthin-wp-suite`'s Redirects module is active, preventing duplicate redirect hooks.
