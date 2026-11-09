# Earthin SMTP Mailer

- **Slug:** `earthin-smtp-mailer`
- **Author:** Shaurya Joshi
- **Author URI:** https://plugins.earthin.online
- **Support URI:** https://plugins.earthin.online/support
- **License:** GPL-2.0-or-later
- **Requires at least:** 6.4
- **Requires PHP:** 8.1

## Overview
Earthin SMTP Mailer ensures reliable delivery of all WordPress transactional emails by piping them through dedicated, authenticated SMTP servers rather than the prone-to-spam PHP `mail()` function.

## Key Features
- Hooks cleanly into `phpmailer_init` without third-party dependencies.
- Full support for TLS (587), SSL (465), and unencrypted ports.
- Custom sender overrides for From Email and From Name.
- Instant test email verification interface.
- Sanitized credentials storage.
