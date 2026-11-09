# Earthin Visitor Counter

**Slug:** `earthin-visitor-counter`  
**Brand:** Earthin  
**Author:** Shaurya Joshi  
**Author URI:** https://plugins.earthin.online  
**Website:** https://plugins.earthin.online/plugins/earthin-visitor-counter  
**License:** GPL-2.0-or-later  
**Requires at least:** 5.8  
**Tested up to:** 6.7  
**Requires PHP:** 7.4  

---

## 1. Overview & Architecture

Earthin Visitor Counter is a privacy-first web statistics and traffic analytics engine hosted entirely on the local WordPress database. It eliminates the need for privacy-invasive third-party analytics trackers.

## 2. Technical Implementation

- **Database Table**: Creates `wp_earthin_visitor_stats` with indexed `visit_date`, `visitor_hash`, and `post_id`.
- **Salted Hashing**: Generates anonymous hashes `md5($ip . $today . wp_salt('auth'))` so raw visitor IP addresses are never permanently retained.
- **Bot & Crawler Filter**: Evaluates User-Agent strings against known search bot signatures to prevent traffic skew.
- **Shortcodes**:
  - `[earthin_visitor_counter]`: Displays formatted all-time site hits count.
  - `[earthin_page_views]`: Displays formatted view count for the current single post or page.
- **Admin Dashboard**: Adds a WordPress dashboard summary widget showing today's unique visitors and pageviews.
