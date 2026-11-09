# Earthin Cookie Consent

**Slug:** `earthin-cookie-consent`  
**Brand:** Earthin  
**Author:** Shaurya Joshi  
**Author URI:** https://plugins.earthin.online  
**Website:** https://plugins.earthin.online/plugins/earthin-cookie-consent  
**License:** GPL-2.0-or-later  
**Requires at least:** 5.8  
**Tested up to:** 6.7  
**Requires PHP:** 7.4  

---

## 1. Overview & Architecture

Earthin Cookie Consent is an autonomous, lightweight compliance banner ensuring adherence to GDPR, ePrivacy Directive, and CCPA regulations without external cloud subscription fees.

## 2. Core Capabilities

- **Consent Decision Model**: Supports "Accept All", "Reject Non-Essential", and "Customize Preferences".
- **Category Segmentation**:
  - `necessary`: Core essential navigation (always active).
  - `analytics`: Website statistics and traffic insight.
  - `marketing`: Advertising, retargeting, and pixels.
  - `functional`: Personalization and embedded widgets.
- **Client Script Interception**: Allows developers to wrap third-party script tags with `type="text/plain" data-earthin-category="analytics"`. Scripts are executed immediately once consent is saved.
- **Event Dispatcher**: Triggers `earthin_consent_updated` window event when user preferences are saved.
- **Shortcode**: `[earthin_cookie_settings]` generates a button allowing visitors to reopen the preference modal at any time.
