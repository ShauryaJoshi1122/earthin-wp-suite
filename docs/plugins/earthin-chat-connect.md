# Earthin Chat Connect

**Slug:** `earthin-chat-connect`  
**Brand:** Earthin  
**Author:** Shaurya Joshi  
**Author URI:** https://plugins.earthin.online  
**Website:** https://plugins.earthin.online/plugins/earthin-chat-connect  
**License:** GPL-2.0-or-later  
**Requires at least:** 5.8  
**Tested up to:** 6.7  
**Requires PHP:** 7.4  

---

## 1. Overview & Architecture

Earthin Chat Connect is a lightweight, privacy-focused floating contact button for WordPress websites. It allows visitors to launch instant direct conversations via WhatsApp, Telegram, Direct Phone Call, or Email.

Unlike third-party live chat widgets that load external scripts, tracking pixels, and heavy JavaScript bundles, Earthin Chat Connect uses pure HTML, responsive CSS, and minimal native JavaScript.

## 2. Supported Channels

1. **WhatsApp**: Generates direct protocol links to `https://wa.me/{phone}?text={encoded_message}`.
2. **Telegram**: Links directly to `https://t.me/{username}`.
3. **Phone**: Standard `tel:` link for instant mobile dialing.
4. **Email**: Native `mailto:` link with preconfigured subject line.

## 3. Configuration & Options

Stored in `wp_options` under `earthin_chat_connect_settings`:
- `enabled`: Front-end widget toggle.
- `position`: `bottom-right` or `bottom-left`.
- `button_color`: Hex color picker for brand styling.
- `tooltip_text`: Header prompt message.
- `channels`: Associative array for each channel's active state, target address/handle, and custom label.

## 4. Privacy & Compliance

- **No Cookies**: Zero tracking cookies set.
- **No Third-Party Requests**: No external CDNs or remote API dependencies.
- **Sanitization**: All input sanitized via `sanitize_text_field`, `sanitize_hex_color`, and `sanitize_email`.
- **Capability Guard**: Settings protected by `manage_options` check.
