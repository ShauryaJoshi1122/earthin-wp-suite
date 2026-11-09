=== Earthin Web Push ===
Contributors: ShauryaJoshi1122
Donate link: https://plugins.earthin.online
Tags: push notifications, web push, vapid, service worker, browser push
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Direct, self-hosted browser web push notifications using open standard VAPID protocols and Service Workers without third-party vendor lock-in.

== Description ==

**Earthin Web Push** is an open, standards-compliant web push notification platform for WordPress. Re-engage visitors directly in their browser using native Service Worker and PushManager APIs without paying recurring monthly subscription fees to third-party push providers.

### Key Features

* **Open Standard VAPID Architecture**: Compatible with all major browsers (Chrome, Firefox, Safari, Edge, Android).
* **Self-Hosted Subscriber Database**: You own 100% of your subscriber data. No customer lock-in or third-party data monetization.
* **Front-end Opt-in Prompt**: Elegant floating notification bell allowing visitors to grant notification permissions in a single tap.
* **Autonomous Service Worker**: Dynamic service worker generation handling push payload delivery and background click routing.

== Installation ==

1. Upload `earthin-push` to `/wp-content/plugins/`.
2. Activate via WordPress Plugins menu.
3. Configure your VAPID keys under Web Push in the admin dashboard.

== Changelog ==

= 1.0.0 =
* Initial public release with VAPID subscription store and background service worker.
