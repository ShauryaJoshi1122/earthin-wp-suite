# Earthin Page Builder

**Slug:** `earthin-page-builder`  
**Brand:** Earthin  
**Author:** Shaurya Joshi  
**Author URI:** https://plugins.earthin.online  
**Website:** https://plugins.earthin.online/plugins/earthin-page-builder  
**License:** GPL-2.0-or-later  
**Requires at least:** 5.8  
**Tested up to:** 6.7  
**Requires PHP:** 7.4  

---

## 1. Overview & Architecture

Earthin Page Builder is a lightweight visual layout builder designed to solve the DOM bloat and page speed penalties typical of traditional WordPress builders.

## 2. Core Architecture

- **Clean Markup Engine**: Renders clean semantic HTML without nested wrapper containers.
- **Storage Strategy**: Serializes layout blocks into JSON format inside `_earthin_builder_layout` post meta.
- **Toggle Mode**: Easily activated/deactivated per post or page via `_earthin_builder_enabled` without altering native content.
- **Supported Blocks**:
  - Headings
  - Paragraphs & Rich Text
  - Call to Action Buttons
  - Info & Alert Boxes
- **Extensible Hooks**: Exposes `earthin_pb_render_custom_block` filter for Pro add-ons and third-party extensions.
