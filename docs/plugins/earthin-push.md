# Earthin Web Push

**Slug:** `earthin-push`  
**Brand:** Earthin  
**Author:** Shaurya Joshi  
**Author URI:** https://plugins.earthin.online  
**Website:** https://plugins.earthin.online/plugins/earthin-push  
**License:** GPL-2.0-or-later  
**Requires at least:** 5.8  
**Tested up to:** 6.7  
**Requires PHP:** 7.4  

---

## 1. Overview & Architecture

Earthin Web Push delivers native browser notifications via open-standard VAPID (Voluntary Application Server Identification) protocols and Service Workers without third-party vendor lock-in.

## 2. Technical Stack

- **Subscriber Database**: Table `wp_earthin_push_subscribers` storing endpoint URLs, `p256dh` public keys, and `auth_secret` strings.
- **Dynamic Service Worker**: Serves `/earthin-sw.js` directly with standard `Service-Worker-Allowed: /` header to intercept push events and background clicks.
- **Front-end Opt-in Bell**: Lightweight floating bell prompting browser `Notification.requestPermission()` and saving subscriptions via AJAX.
- **Self-Hosted Privacy**: No telemetry sent to external marketing vendors.
