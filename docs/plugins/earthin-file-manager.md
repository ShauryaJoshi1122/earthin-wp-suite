# Earthin File Manager

- **Slug:** `earthin-file-manager`
- **Author:** Shaurya Joshi
- **Author URI:** https://plugins.earthin.online
- **Support URI:** https://plugins.earthin.online/support
- **License:** GPL-2.0-or-later
- **Requires at least:** 6.4
- **Requires PHP:** 8.1

## Overview
Earthin File Manager provides an administrative file explorer and editor interface inside WordPress, designed with security-first sandboxing to prevent directory traversal exploits.

## Key Features
- Path containment validator checking canonical `realpath` against `ABSPATH`.
- Octal file permission display (e.g. 0644, 0755) to audit file security.
- Built-in text editor for theme assets, configuration files, and script templates.
- Explicit whitelist of editable extensions with security blocks on binaries.
