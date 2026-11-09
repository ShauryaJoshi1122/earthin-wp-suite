# Earthin Cookie Consent Pro

**Slug:** `earthin-cookie-consent-pro`  
**Brand:** Earthin  
**Author:** Shaurya Joshi  
**Author URI:** https://plugins.earthin.online  
**Website:** https://plugins.earthin.online/plugins/earthin-cookie-consent-pro  
**License:** GPL-2.0-or-later  
**Requires at least:** 5.8  
**Tested up to:** 6.7  
**Requires PHP:** 7.4  

---

## 1. Overview & Architecture

Earthin Cookie Consent Pro is the commercial add-on for Earthin Cookie Consent. It adds proof-of-consent audit logging, geo-targeting policies, and CSS customizer capabilities.

## 2. Advanced Features

- **Audit Trail (Proof of Consent)**: Automatically logs consent events via AJAX endpoint `earthin_log_consent`. Hashes client IP using `hash('sha256', $ip . wp_salt())` for pseudonymized compliance records.
- **Geo-Targeting Policies**: Configure banner display for all visitors worldwide or exclusively for EU and UK jurisdictions.
- **Custom CSS Engine**: Inject theme-tailored styles directly into the banner interface.
- **Parent Plugin Guard**: Verifies that the base `Earthin_Cookie_Consent` class is active, surfacing an admin warning if missing.
