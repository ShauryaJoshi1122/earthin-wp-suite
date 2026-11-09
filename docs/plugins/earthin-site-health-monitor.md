# Earthin Site Health Monitor

**Developer:** Shaurya Joshi  
**Brand:** Earthin  
**Version:** 0.1.0  
**WordPress Compatibility:** 6.4+  
**PHP Requirement:** 8.1+  
**License:** GPL-2.0-or-later  
**Website:** [https://plugins.earthin.online/plugins/earthin-site-health-monitor](https://plugins.earthin.online/plugins/earthin-site-health-monitor)

---

## 1. Overview

Earthin Site Health Monitor audits your WordPress server environment, configuration, and security settings, surfacing actionable insights with zero administrative clutter.

---

## 2. Real Features Implemented

* **Core Diagnostics**:
  1. **PHP Runtime Compliance**: Verifies PHP 8.1+ compliance and warns against unsupported EOL versions.
  2. **WordPress Core Version**: Checks compliance with modern WordPress core standards.
  3. **SSL / HTTPS Transmission**: Verifies SSL encryption across the website.
  4. **Debug Leak Suppression**: Detects public error leakage via `WP_DEBUG_DISPLAY`.
  5. **PHP Memory Allocation**: Verifies sufficient memory limits (>= 128M).
  6. **Media Upload Permissions**: Validates filesystem write access for `/wp-content/uploads/`.
  7. **Database Table Prefix**: Warns against standard `wp_` prefixes that aid automated blind SQL injection.
  8. **Plugin Hygiene**: Identifies dormant inactive plugins.
* **Modern Administration Dashboard**:
  * Health status counters (Passing, Notice, Critical).
  * Clear recommendation instructions for each detected item.
  * Zero exposure of sensitive server paths or credentials to unauthenticated visitors.
