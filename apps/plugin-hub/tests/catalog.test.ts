import { describe, it, expect } from 'vitest';
import { getAllPlugins, getPluginBySlug, getFeaturedPlugins, CATEGORIES } from '../lib/catalog';
import { cn, formatVersion } from '../lib/utils';

describe('Plugin Catalog Data', () => {
  it('returns all 25 official Earthin plugins from catalog', () => {
    const plugins = getAllPlugins();
    expect(plugins.length).toBe(25);
  });

  it('retrieves Earthin WP Suite with accurate version and compatibility claims', () => {
    const plugin = getPluginBySlug('earthin-wp-suite');
    expect(plugin).toBeDefined();
    expect(plugin?.name).toBe('Earthin WP Suite');
    expect(plugin?.author).toBe('Shaurya Joshi');
    expect(plugin?.latestVersion).toBe('0.2.0');
    expect(plugin?.minPhpVersion).toBe('8.1');
    expect(plugin?.minWpVersion).toBe('6.4');
    expect(plugin?.pricingModel).toBe('freemium');
    expect(plugin?.features.length).toBeGreaterThan(5);
  });

  it('retrieves all Earthin plugins with correct author and requirements', () => {
    const slugs = [
      'earthin-wp-suite',
      'earthin-image-optimizer',
      'earthin-database-cleaner',
      'earthin-site-health-monitor',
      'earthin-maintenance-mode',
      'earthin-redirect-manager',
      'earthin-classic-widgets',
      'earthin-classic-editor',
      'earthin-advanced-editor',
      'earthin-white-label',
      'earthin-social-share',
      'earthin-chat-connect',
      'earthin-login-shield',
      'earthin-cookie-consent',
      'earthin-cookie-consent-pro',
      'earthin-visitor-counter',
      'earthin-social-preview',
      'earthin-push',
      'earthin-page-builder',
      'earthin-page-builder-pro',
      'earthin-cache-accelerator',
      'earthin-smtp-mailer',
      'earthin-code-snippets',
      'earthin-file-manager',
      'earthin-security-2fa',
    ];

    slugs.forEach((slug) => {
      const plugin = getPluginBySlug(slug);
      expect(plugin).toBeDefined();
      expect(plugin?.author).toBe('Shaurya Joshi');
      expect(plugin?.authorUri).toBe('https://plugins.earthin.online');
      expect(plugin?.releases.length).toBeGreaterThan(0);
      expect(plugin?.features.length).toBeGreaterThan(0);
    });
  });

  it('returns undefined for nonexistent plugin slug', () => {
    const plugin = getPluginBySlug('non-existent-plugin');
    expect(plugin).toBeUndefined();
  });

  it('returns featured plugins', () => {
    const featured = getFeaturedPlugins();
    expect(featured.length).toBeGreaterThan(0);
    expect(featured.every((p) => p.featured)).toBe(true);
  });

  it('verifies category slugs are unique', () => {
    const slugs = CATEGORIES.map((c) => c.slug);
    const unique = new Set(slugs);
    expect(slugs.length).toBe(unique.size);
  });
});

describe('Utils', () => {
  it('merges tailwind classes properly', () => {
    expect(cn('px-2 py-1', 'px-4')).toBe('py-1 px-4');
  });

  it('formats version strings consistently', () => {
    expect(formatVersion('0.2.0')).toBe('v0.2.0');
    expect(formatVersion('v0.2.0')).toBe('v0.2.0');
  });
});
