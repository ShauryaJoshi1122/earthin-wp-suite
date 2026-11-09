# Earthin WP Suite

**Developer:** Shaurya Joshi  
**Brand:** Earthin  
**Version:** 0.2.0  
**WordPress Compatibility:** 6.4+  
**PHP Requirement:** 8.1+  
**License:** GPL-2.0-or-later  
**Website:** [https://plugins.earthin.online/plugins/earthin-wp-suite](https://plugins.earthin.online/plugins/earthin-wp-suite)

---

## 1. Overview

Earthin WP Suite is a modular WordPress management suite designed to replace dozens of single-purpose plugins with one unified, decoupled architecture.

---

## 2. Real Features Implemented in v0.2.0

* **Modular Registry & Administration**:
  * Decoupled module lifecycle (`Module_Interface`).
  * Tabbed dashboard with Modules & Controls, Redirects Manager, Form Inquiries, and System Status.
* **SEO Management Module**:
  * Custom meta title, meta description, canonical URLs, and robots directives.
  * Conflict guard that automatically detects third-party SEO plugins (Yoast, All in One SEO, Rank Math, SEOPress, The SEO Framework) to avoid duplicate meta tags.
* **Schema Markup Module**:
  * Structured JSON-LD breadcrumb and article schema generation with automated escaping.
* **XML Sitemaps Module**:
  * Search-engine compliant dynamic XML sitemap generation with hourly cache invalidation.
* **Social Previews Module**:
  * Open Graph and Twitter Card metadata generation with featured image and site icon fallbacks.
* **Security Hardening Module**:
  * Configurable HTTP response headers: X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Referrer-Policy, and XML-RPC disablement.
* **Redirects Management Module**:
  * Exact-path redirect engine with loop validation, status code selection (301, 302, 307, 308), and hit tracking.
* **Safe Contact Forms Module**:
  * Frontend shortcode `[earthin_contact_form]` with anti-spam honeypot, email header injection protection, IP rate limiting, database persistence, and CSV export.
* **RSS Automation Module**:
  * Draft-only import with strict Server-Side Request Forgery (SSRF) defense against private/loopback IP addresses.
