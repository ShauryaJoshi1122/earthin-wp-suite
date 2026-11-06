# Earthin WP Suite

A modular all-in-one WordPress plugin suite by [Earthin](https://earthin.online/).

## Current development status — 0.2.0

This repository contains an early development build. It is not yet a production replacement for every third-party WordPress plugin.

### Implemented in the repository
- Plugin bootstrap and module registry.
- Administrator dashboard with independent module toggles.
- WordPress Settings API sanitization and administrator capability checks.
- SEO description and canonical tags when no recognized SEO provider is active.
- Open Graph and X card metadata using featured image, site icon fallback, or a per-post custom image URL.
- Per-post social image field with nonce and capability checks.
- Optional security module adding conservative response headers and disabling XML-RPC.
- Optional exact-path redirect runtime (rules are currently managed through the database option, not yet through a UI).
- Manual RSS import of up to five items into **drafts**; duplicate source links are skipped.
- Non-destructive uninstall behavior.

### Important limitations
- SEO/social provider detection is a compatibility guard, not a complete integration with every SEO plugin.
- RSS imports are draft-only and require editorial review. There is no AI rewriting, automated scheduling, or auto-publishing.
- Security headers are intentionally conservative; no Content Security Policy or file-editing restrictions are forced.
- Redirect management UI, schema controls, dedicated sitemap implementation, media manager, and complete settings backup are not yet implemented.
- No database migration/rollback engine, forms, analytics, SMTP configuration, Gutenberg feature set, or plugin-update service yet.
- The plugin has not yet been validated against a real WordPress installation or a broad plugin/theme compatibility matrix.

## Requirements
- WordPress 6.4+
- PHP 8.1+

## Installation
1. Download or clone this repository into `wp-content/plugins/earthin-wp-suite`.
2. Activate **Earthin WP Suite** in WordPress.
3. Open **Earthin Suite** in the admin menu.
4. Enable only the modules you intend to use.

## Security and editorial principles
- Privileged operations require WordPress capability checks and nonces.
- Input is sanitized and output escaped.
- RSS items are created as drafts; the plugin does not publish external content automatically.
- Existing SEO providers suppress this plugin's SEO/social frontend output to reduce duplicate tags.
- Uninstall preserves site settings and content by default.
- Never commit API keys or credentials.

## Roadmap
1. Complete module settings UX and automated tests.
2. Schema, sitemap controls, media manager, redirects UI, and settings export/import.
3. AI newsroom with provider abstraction, human approval, and RSS scheduling.
4. Security diagnostics, performance tools, backup/restore verification, forms, analytics, SMTP, and Gutenberg.
5. Read-only plugin inventory scanner, migration preview, backup, per-plugin migration adapters, verification, and rollback.
6. Compatibility testing, packaging, documentation, and signed/controlled update infrastructure.

## License
GPL-2.0-or-later.
