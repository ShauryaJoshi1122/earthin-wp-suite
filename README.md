# Earthin WP Suite

A modular all-in-one WordPress plugin suite by [Earthin](https://earthin.online/).

## Current status — 0.1.0 foundation

Implemented:
- WordPress plugin bootstrap and module coordinator.
- Module interface and registry.
- Administrator dashboard with independent SEO and Social Preview toggles.
- WordPress Settings API sanitization and administrator capability checks.
- Non-destructive uninstall behavior.

Scaffolded only:
- SEO module.
- Social Preview module. Frontend metadata output is intentionally not active until compatibility detection is implemented.

Not implemented:
- Schema, XML sitemap, media manager, AI newsroom, RSS automation.
- Security, performance, backups, redirects, forms, analytics, SMTP, Gutenberg tools.
- Plugin inventory scanner, migration engine, rollback.
- Full test suite, compatibility matrix, release packaging, update infrastructure.

## Requirements
- WordPress 6.4+
- PHP 8.1+

## Development principles
- Use WordPress APIs and preserve core behavior.
- Check capabilities and nonces for privileged actions.
- Sanitize input, escape output, and use prepared SQL.
- Avoid duplicate metadata when other SEO plugins are active.
- Make migrations opt-in, backed up, verifiable, and reversible where possible.
- Never store API secrets in source control.

## Roadmap
1. Foundation and dashboard.
2. SEO, schema, sitemap, social preview, and media.
3. AI newsroom and RSS automation.
4. Security, performance, backup, redirects, forms, analytics, SMTP, and Gutenberg.
5. Migration scanner with preview, backup, verification, and rollback.
6. Automated tests, compatibility testing, packaging, documentation, and update infrastructure.

This repository currently contains the initial foundation only; roadmap items are not represented as completed.
