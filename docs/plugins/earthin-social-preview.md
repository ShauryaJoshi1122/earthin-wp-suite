# Earthin Social Preview

**Slug:** `earthin-social-preview`  
**Brand:** Earthin  
**Author:** Shaurya Joshi  
**Author URI:** https://plugins.earthin.online  
**Website:** https://plugins.earthin.online/plugins/earthin-social-preview  
**License:** GPL-2.0-or-later  
**Requires at least:** 5.8  
**Tested up to:** 6.7  
**Requires PHP:** 7.4  

---

## 1. Overview & Architecture

Earthin Social Preview injects Open Graph and Twitter Card metadata tags into WordPress page headers while providing an interactive live preview card within the post edit screen.

## 2. Meta Tags Emitted

- `og:site_name`, `og:type`, `og:title`, `og:description`, `og:url`, `og:image`
- `twitter:card` (summary_large_image), `twitter:title`, `twitter:description`, `twitter:image`, `twitter:site`

## 3. Post Editor Features

- Interactive real-time card preview mirroring Facebook and Twitter share cards.
- Per-post custom social title, description, and 1200x630 banner image overrides stored in `_earthin_social_*` post meta.
- Global fallback image configured in Settings > Social Preview.
