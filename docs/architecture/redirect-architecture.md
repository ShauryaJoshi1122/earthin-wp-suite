# Architecture Evaluation: Earthin Redirect Engine (Suite vs. Standalone)

## 1. Executive Summary

In WordPress ecosystems, users fall into two distinct personas:
1. **Full-Suite Users**: Administrators seeking a single, integrated plugin for SEO, security, forms, sitemaps, and URL redirection (`earthin-wp-suite`).
2. **Specialized Single-Purpose Users**: Website managers or developers who already use separate plugins for SEO (e.g. Yoast or RankMath) and only need a fast, secure, standalone redirect manager without the footprint of a full management suite (`earthin-redirect-manager`).

To satisfy both personas without shipping conflicting duplicate redirect hooks, we adopted an **Isolated Engine with Active Mutual Detection** pattern.

---

## 2. Architectural Tradeoffs

| Criterion | Keep Only in Suite | Extract Completely from Suite | Standalone with Mutual Detection (Selected) |
| :--- | :--- | :--- | :--- |
| **Footprint for Single-Need Users** | Heavy (requires entire suite) | Lightweight | Lightweight |
| **Backward Compatibility with Suite v0.2.0** | 100% preserved | Breaks suite users | 100% preserved |
| **Collision Risk on `template_redirect`** | None | None | Eliminated via runtime guard |
| **Import / Export Portability** | Limited | High | High (CSV import/export supported) |

---

## 3. Mutual Conflict Avoidance Implementation

To ensure that two redirect engines never execute concurrently on the same request:

1. **Namespace Isolation**:
   - Suite Module: `\Earthin\WPSuite\Redirects_Module` (Option key: `ews_redirect_rules`).
   - Standalone Plugin: `\Earthin\RedirectManager\Plugin` (Option key: `erm_redirect_rules`).

2. **Active Conflict Guard in Standalone Plugin**:
   When `earthin-redirect-manager` boots on `template_redirect`, it evaluates:
   ```php
   if ( class_exists( '\\Earthin\\WPSuite\\Plugin' ) ) {
       $suite_settings = get_option( 'ews_settings', array() );
       if ( ! empty( $suite_settings['redirects_enabled'] ) ) {
           // Yield frontend interception to Earthin WP Suite to prevent duplicate execution
           return;
       }
   }
   ```

3. **Administrative Notice**:
   If both plugins are active simultaneously with redirects enabled, the standalone plugin displays an informative notice advising the administrator that Earthin WP Suite has priority, preventing redundant configuration.

---

## 4. Conclusion

This architecture preserves zero regressions in `earthin-wp-suite` v0.2.0 while enabling `earthin-redirect-manager` to be distributed as a standalone, lightweight, feature-rich redirect solution.
