import { PluginItem, PluginCategory } from './types';

export const CATEGORIES: PluginCategory[] = [
  {
    id: 'all',
    name: 'All Plugins',
    slug: 'all',
    description: 'Browse all official WordPress plugins developed by Earthin.',
  },
  {
    id: 'seo',
    name: 'SEO & Suite',
    slug: 'seo',
    description: 'Search engine optimization, XML sitemaps, Open Graph cards, and all-in-one management.',
  },
  {
    id: 'performance',
    name: 'Performance & Media',
    slug: 'performance',
    description: 'Local image optimization, lossless compression, and next-gen WebP formatting.',
  },
  {
    id: 'database',
    name: 'Database & Maintenance',
    slug: 'database',
    description: 'Database inspection, selective revision cleanup, and transient optimization.',
  },
  {
    id: 'security',
    name: 'Security & Utilities',
    slug: 'security',
    description: 'Technical site health diagnostics, responsive maintenance mode, and URL redirection.',
  },
  {
    id: 'editor',
    name: 'Editor & Widgets',
    slug: 'editor',
    description: 'Classic editor controls, TinyMCE typography enhancements, and traditional widget interfaces.',
  },
  {
    id: 'social',
    name: 'Social & Branding',
    slug: 'social',
    description: 'Lightweight privacy-friendly social sharing buttons and white label administration.',
  },
];

export const PLUGINS: PluginItem[] = [
  {
    id: 'earthin-wp-suite',
    name: 'Earthin WP Suite',
    slug: 'earthin-wp-suite',
    author: 'Shaurya Joshi',
    authorUri: 'https://plugins.earthin.online',
    tagline: 'All-in-one modular suite for SEO, social cards, redirects, safe forms, and site management.',
    shortDescription: 'Unified WordPress suite providing independent modules for canonical SEO, Open Graph previews, exact-path redirects with hit counting, safe forms, and RSS draft queueing.',
    description: `Earthin WP Suite is a modern, modular WordPress plugin designed to replace multiple disconnected utility plugins with a single, secure, and coordinated architecture.

Every module can be independently enabled or disabled without breaking unrelated features. When an established SEO plugin is detected, Earthin WP Suite automatically yields frontend tags to avoid metadata collision.

Built with strict WordPress coding standards, secure nonces, capability authorization, CSRF protection, and SSRF defense on all external feed operations.`,
    category: 'seo',
    pricingModel: 'freemium',
    freeVersionAvailable: true,
    premiumVersionAvailable: true,
    latestVersion: '0.2.0',
    minWpVersion: '6.4',
    testedWpVersion: '6.7',
    minPhpVersion: '8.1',
    featured: true,
    isPublished: true,
    features: [
      'Document title and meta description generation with canonical link tags',
      'Open Graph and Twitter/X card social sharing previews with custom image override',
      'Structured data JSON-LD engine with hex-escaped script tag protection (XSS defense)',
      'XML sitemap integration configuring native WordPress core sitemaps',
      'Low-risk security response headers (nosniff, referrer-policy) and XML-RPC disable',
      'Exact-path redirect manager with 404 recovery, loop guard, status code selection, and hit tracking',
      'Shortcode contact form [earthin_contact_form] with honeypot anti-spam, header injection guard, and DB persistence',
      'RSS import review queue importing up to 5 items to drafts with strict SSRF defense',
      'Multi-tab administration dashboard with real-time system diagnostics',
    ],
    premiumFeatures: [
      'Automated scheduled RSS syncing and background ingestion',
      'AI-assisted editorial rewriting and outline generation with provider abstraction',
      'Advanced regex redirect pattern matching and 404 error log monitoring',
      'Visual drag-and-drop contact form builder with email delivery webhooks',
      'Automated full-site and database backup snapshots with verification',
      'Multi-site network administration and centralized license keys',
    ],
    installationSteps: [
      'Download the earthin-wp-suite ZIP archive from the releases tab or directory.',
      'In your WordPress admin panel, go to Plugins > Add New > Upload Plugin.',
      'Select the downloaded ZIP file and click Install Now.',
      'Click Activate Plugin.',
      'Navigate to the Earthin Suite menu item in your WordPress admin bar to configure enabled modules.',
    ],
    releases: [
      {
        version: '0.2.0',
        date: '2026-11-09',
        minWpVersion: '6.4',
        testedWpVersion: '6.7',
        minPhpVersion: '8.1',
        isStable: true,
        notes: [
          'Tabbed administration dashboard with Modules, Redirects Manager, Form Inquiries, and System Status.',
          'Redirects administrative interface with status code selection (301, 302, 307, 308) and real-time hit tracking.',
          'Form inquiries viewer with submission persistence and formula-injection-safe CSV export.',
          'Hardened RSS module against Server-Side Request Forgery (SSRF) and private network resolution.',
          'Added anti-spam honeypot and email header injection protection to the forms module.',
          'Resolved redirect 404 bypass allowing broken URL recovery.',
          'JSON-LD structured data engine with XSS script tag escaping.',
        ],
      },
      {
        version: '0.1.0',
        date: '2026-11-06',
        minWpVersion: '6.4',
        testedWpVersion: '6.7',
        minPhpVersion: '8.1',
        isStable: false,
        notes: [
          'Initial plugin bootstrap, module manager, and admin settings scaffold.',
          'Basic SEO metadata and social preview fields.',
          'Non-destructive uninstall lifecycle.',
        ],
      },
    ],
  },
  {
    id: 'earthin-image-optimizer',
    name: 'Earthin Image Optimizer',
    slug: 'earthin-image-optimizer',
    author: 'Shaurya Joshi',
    authorUri: 'https://plugins.earthin.online',
    tagline: 'Local-first WordPress image compression engine with original file backup protection.',
    shortDescription: 'Compress JPEG and PNG images locally on your server without third-party API keys or recurring SaaS subscriptions.',
    description: `Earthin Image Optimizer reduces media file sizes directly on your server using native PHP GD image libraries. Your images and visitor privacy are never transmitted to external cloud APIs.

Featuring safe original file preservation, one-click restoration from the WordPress Media Library, configurable JPEG/PNG compression thresholds, privacy-friendly EXIF stripping, and optional next-gen WebP generation.

Strict guarantee: If compression does not result in a smaller file size, the original image is strictly retained so files never grow.`,
    category: 'performance',
    pricingModel: 'freemium',
    freeVersionAvailable: true,
    premiumVersionAvailable: true,
    latestVersion: '0.1.0',
    minWpVersion: '6.4',
    testedWpVersion: '6.7',
    minPhpVersion: '8.1',
    featured: true,
    isPublished: true,
    features: [
      '100% local PHP GD compression engine requiring zero external API keys',
      'Configurable JPEG quality slider and PNG zlib compression levels',
      'Automatic WebP generation when imagewebp is supported by server PHP',
      'Privacy-focused EXIF camera and GPS metadata removal',
      'Protected original backup directory preventing direct web execution',
      'One-click Media Library restoration to revert to full-resolution originals',
      'Zero negative compression guarantee: original files kept if compression produces larger output',
      'Real-time disk space savings accumulator and statistics dashboard',
    ],
    premiumFeatures: [
      'Cloud-based lossy AVIF conversion and super-resolution upscaling',
      'Automated background bulk optimization cron for thousands of legacy images',
      'CDN offloading integration for Amazon S3, Cloudflare R2, and DigitalOcean Spaces',
    ],
    installationSteps: [
      'Upload the earthin-image-optimizer folder to /wp-content/plugins/.',
      'Activate through the Plugins screen in WordPress.',
      'Go to Media > Earthin Optimizer to configure compression quality.',
    ],
    releases: [
      {
        version: '0.1.0',
        date: '2026-11-09',
        minWpVersion: '6.4',
        testedWpVersion: '6.7',
        minPhpVersion: '8.1',
        isStable: true,
        notes: [
          'Initial release with local GD compression, EXIF stripping, WebP creation, original backup preservation, and Media Library integration.',
        ],
      },
    ],
  },
  {
    id: 'earthin-database-cleaner',
    name: 'Earthin Database Cleaner',
    slug: 'earthin-database-cleaner',
    author: 'Shaurya Joshi',
    authorUri: 'https://plugins.earthin.online',
    tagline: 'Preview-first WordPress database optimization tool to safely purge revisions and orphaned data.',
    shortDescription: 'Inspect database overhead and selectively clean revisions, auto-drafts, expired transients, and orphaned postmeta with mandatory admin confirmation.',
    description: `Earthin Database Cleaner inspects your WordPress database for redundant clutter and provides a transparent, preview-first interface for reclaiming storage space safely.

Unlike aggressive tools that delete records blindly, Earthin Database Cleaner requires an explicit confirmation checkbox, nonces, and administrator capabilities before executing prepared SQL delete routines.

Features comprehensive coverage of post revisions, auto-drafts, trash, spam comments, expired transients, and orphaned post/comment metadata.`,
    category: 'database',
    pricingModel: 'freemium',
    freeVersionAvailable: true,
    premiumVersionAvailable: true,
    latestVersion: '0.1.0',
    minWpVersion: '6.4',
    testedWpVersion: '6.7',
    minPhpVersion: '8.1',
    featured: true,
    isPublished: true,
    features: [
      'Live database status overview reporting prefix-matched table count, database size (MB), and overhead',
      'Preview inventory showing exact row counts before any destructive action is taken',
      'Selective purging: choose exactly which record types to optimize',
      'Safe post revision and auto-draft purging',
      'Expired transient cleanup comparing expiration timestamps against time()',
      'Orphaned postmeta and commentmeta detection and removal',
      'Strict safety guard: requires explicit admin confirmation checkbox and capability verification',
      'Detailed post-cleanup audit log summarizing purged rows',
    ],
    premiumFeatures: [
      'Scheduled recurring automated database optimization with safety thresholds',
      'Automatic pre-cleanup database export snapshot and rollback mechanism',
      'Unused database table scanner for uninstalled plugins',
    ],
    installationSteps: [
      'Upload the earthin-database-cleaner folder to /wp-content/plugins/.',
      'Activate via the Plugins screen in WordPress.',
      'Access Tools > Earthin DB Cleaner to inspect redundant rows and clean.',
    ],
    releases: [
      {
        version: '0.1.0',
        date: '2026-11-09',
        minWpVersion: '6.4',
        testedWpVersion: '6.7',
        minPhpVersion: '8.1',
        isStable: true,
        notes: [
          'Initial release with database overview, preview inventory, 8 cleanable categories, explicit confirmation guard, and detailed cleanup reporting.',
        ],
      },
    ],
  },
  {
    id: 'earthin-site-health-monitor',
    name: 'Earthin Site Health Monitor',
    slug: 'earthin-site-health-monitor',
    author: 'Shaurya Joshi',
    authorUri: 'https://plugins.earthin.online',
    tagline: 'Technical environment diagnostics, configuration audits, and actionable health metrics.',
    shortDescription: 'Audit PHP compliance, SSL enforcement, debug leaks, memory limits, and table prefix security with actionable recommendations.',
    description: `Earthin Site Health Monitor audits your WordPress server environment, configuration, and security settings, surfacing actionable insights with zero administrative clutter.

Provides clear categorization across Passing, Notice, and Critical health badges to help website owners identify vulnerabilities such as public error display leaks (WP_DEBUG_DISPLAY), insecure HTTP transmission, and memory bottlenecks before they disrupt operations.`,
    category: 'security',
    pricingModel: 'free',
    freeVersionAvailable: true,
    premiumVersionAvailable: false,
    latestVersion: '0.1.0',
    minWpVersion: '6.4',
    testedWpVersion: '6.7',
    minPhpVersion: '8.1',
    featured: false,
    isPublished: true,
    features: [
      'PHP runtime compliance check verifying PHP 8.1+ support and flagging EOL runtimes',
      'WordPress core version compliance verification',
      'SSL and HTTPS transport encryption enforcement audit',
      'Public debug leak guard detecting active WP_DEBUG_DISPLAY in production',
      'PHP memory allocation audit ensuring adequate headroom for heavy operations',
      'Media uploads directory write permission validation',
      'Database table prefix audit warning against automated blind SQL scans targeting default wp_',
      'Plugin hygiene inspection identifying inactive and dormant plugins',
      'Read-only operation: zero database bloat and zero public information exposure',
    ],
    installationSteps: [
      'Upload the earthin-site-health-monitor folder to /wp-content/plugins/.',
      'Activate through the Plugins screen in WordPress.',
      'Go to Tools > Earthin Site Health to view your environment diagnostics.',
    ],
    releases: [
      {
        version: '0.1.0',
        date: '2026-11-09',
        minWpVersion: '6.4',
        testedWpVersion: '6.7',
        minPhpVersion: '8.1',
        isStable: true,
        notes: [
          'Initial release with 8 core technical environment checks, categorized health badges, and administrative diagnostic dashboard.',
        ],
      },
    ],
  },
  {
    id: 'earthin-maintenance-mode',
    name: 'Earthin Maintenance Mode',
    slug: 'earthin-maintenance-mode',
    author: 'Shaurya Joshi',
    authorUri: 'https://plugins.earthin.online',
    tagline: 'Clean, responsive, and SEO-friendly maintenance mode with zero-lockout administrative bypass.',
    shortDescription: 'Display a customizable maintenance screen to visitors with HTTP 503 status code while administrators browse the live website freely.',
    description: `Earthin Maintenance Mode allows website administrators to display a modern, high-performance maintenance page to regular visitors while working behind the scenes.

Engineered with a zero-lockout architecture: logged-in administrators with manage_options automatically bypass the maintenance screen, while normal visitors receive an SEO-friendly HTTP 503 Service Unavailable header with configurable Retry-After timing to preserve search rankings.

Never interferes with wp-login.php, /wp-admin/, password resets, or CRON operations.`,
    category: 'security',
    pricingModel: 'free',
    freeVersionAvailable: true,
    premiumVersionAvailable: false,
    latestVersion: '0.1.0',
    minWpVersion: '6.4',
    testedWpVersion: '6.7',
    minPhpVersion: '8.1',
    featured: false,
    isPublished: true,
    features: [
      'Zero-lockout architecture: administrators automatically bypass the maintenance screen',
      'Safe authentication exclusions: never blocks wp-login.php, wp-admin, or password recovery',
      'SEO-friendly HTTP 503 response header with configurable Retry-After header',
      'Optional HTTP 200 OK mode for coming soon and pre-launch teaser pages',
      'Full visual customization: custom browser title, headline, rich HTML message, and logo',
      'Custom theme styling: background color, accent color, and text color controls',
      'Live admin preview mode to inspect designs without enabling them for visitors',
      'Admin bar indicator showing active maintenance status',
    ],
    installationSteps: [
      'Upload the earthin-maintenance-mode directory to /wp-content/plugins/.',
      'Activate via the Plugins screen in WordPress.',
      'Go to Settings > Earthin Maintenance to configure and activate.',
    ],
    releases: [
      {
        version: '0.1.0',
        date: '2026-11-09',
        minWpVersion: '6.4',
        testedWpVersion: '6.7',
        minPhpVersion: '8.1',
        isStable: true,
        notes: [
          'Initial release with administrative bypass, HTTP 503 response header handling, color customization, and live preview.',
        ],
      },
    ],
  },
  {
    id: 'earthin-redirect-manager',
    name: 'Earthin Redirect Manager',
    slug: 'earthin-redirect-manager',
    author: 'Shaurya Joshi',
    authorUri: 'https://plugins.earthin.online',
    tagline: 'Fast, loop-protected 301/302 URL redirect manager with CSV import/export and hit tracking.',
    shortDescription: 'Dedicated standalone redirect manager with automated loop detection, status code selection, rule toggles, and mutual conflict guard.',
    description: `Earthin Redirect Manager provides a clean, standalone administrative interface to create, monitor, and manage 301 and 302 URL redirects.

Designed for website owners who only need URL redirection without loading a multi-module management suite. Features automated self-referencing loop prevention, status code selection (301, 302, 307, 308), rule toggling, real-time hit tracking, and complete CSV import/export capabilities.

Includes an active mutual conflict guard that automatically yields frontend processing if Earthin WP Suite's Redirect module is active, preventing duplicate redirect hooks.`,
    category: 'security',
    pricingModel: 'freemium',
    freeVersionAvailable: true,
    premiumVersionAvailable: true,
    latestVersion: '0.1.0',
    minWpVersion: '6.4',
    testedWpVersion: '6.7',
    minPhpVersion: '8.1',
    featured: false,
    isPublished: true,
    features: [
      'Exact-path URL redirection with clean matching on relative request URIs',
      'Supported HTTP status codes: 301 (Permanent), 302 (Temporary), 307 (Temporary), 308 (Permanent)',
      'Automated redirect loop detection preventing self-referencing rules before saving',
      'Hit tracking counter and last-accessed timestamp for every redirect rule',
      'Rule toggle controls to disable redirects temporarily without deleting them',
      'CSV export to download complete redirect maps for backup or staging synchronization',
      'CSV import with robust path sanitization and duplicate handling',
      'Mutual conflict guard yielding frontend execution when Earthin WP Suite redirects are enabled',
    ],
    premiumFeatures: [
      'Wildcard and Regular Expression (Regex) path pattern matching',
      'Automated 404 error log monitor with one-click redirect creation',
      'Query parameter forwarding and UTM campaign tracking preservation',
    ],
    installationSteps: [
      'Upload the earthin-redirect-manager folder to /wp-content/plugins/.',
      'Activate through the Plugins screen in WordPress.',
      'Navigate to Tools > Earthin Redirects to create and manage redirects.',
    ],
    releases: [
      {
        version: '0.1.0',
        date: '2026-11-09',
        minWpVersion: '6.4',
        testedWpVersion: '6.7',
        minPhpVersion: '8.1',
        isStable: true,
        notes: [
          'Initial standalone release with loop detection, hit counting, rule toggling, CSV import/export, and mutual conflict guard.',
        ],
      },
    ],
  },
  {
    id: 'earthin-classic-widgets',
    name: 'Earthin Classic Widgets',
    slug: 'earthin-classic-widgets',
    author: 'Shaurya Joshi',
    authorUri: 'https://plugins.earthin.online',
    tagline: 'Restores the traditional WordPress widget administration screen and disables block-based widgets.',
    shortDescription: 'Restore the classic Appearance > Widgets drag-and-drop screen using official WordPress filters.',
    description: `Earthin Classic Widgets restores the traditional WordPress widget administration screen in Appearance > Widgets and the Customizer.

When active, it cleanly disables the block-based widget editor using the official use_widgets_block_editor WordPress core filter, preserving complete theme and legacy widget compatibility with zero bloat.`,
    category: 'editor',
    pricingModel: 'free',
    freeVersionAvailable: true,
    premiumVersionAvailable: false,
    latestVersion: '0.1.0',
    minWpVersion: '6.4',
    testedWpVersion: '6.7',
    minPhpVersion: '8.1',
    featured: false,
    isPublished: true,
    features: [
      'Disables Gutenberg block-based widget editor using official use_widgets_block_editor filter',
      'Restores classic drag-and-drop widget administration in Appearance > Widgets',
      'Restores classic widget management inside the WordPress Customizer',
      'Zero database bloat: operates cleanly via core filters with safe toggle fallback',
    ],
    installationSteps: [
      'Upload the earthin-classic-widgets directory to /wp-content/plugins/.',
      'Activate via the Plugins screen in WordPress.',
      'Your traditional widget screen is restored immediately.',
    ],
    releases: [
      {
        version: '0.1.0',
        date: '2026-11-09',
        minWpVersion: '6.4',
        testedWpVersion: '6.7',
        minPhpVersion: '8.1',
        isStable: true,
        notes: [
          'Initial release with official use_widgets_block_editor filter integration, administrative toggle, and safe fallback.',
        ],
      },
    ],
  },
  {
    id: 'earthin-classic-editor',
    name: 'Earthin Classic Editor',
    slug: 'earthin-classic-editor',
    author: 'Shaurya Joshi',
    authorUri: 'https://plugins.earthin.online',
    tagline: 'Restores the classic TinyMCE writing experience with per-post-type and dual-editor controls.',
    shortDescription: 'Enables the traditional WordPress Classic Editor with per-post-type options and dual editor links.',
    description: `Earthin Classic Editor restores the traditional WordPress editing screen and TinyMCE rich text editor.

Features default editor selection (Classic Editor vs Block Editor), post type exclusions, and an optional dual editor mode displaying "Edit (Classic)" and "Edit (Block)" links in post listing tables.`,
    category: 'editor',
    pricingModel: 'free',
    freeVersionAvailable: true,
    premiumVersionAvailable: false,
    latestVersion: '0.1.0',
    minWpVersion: '6.4',
    testedWpVersion: '6.7',
    minPhpVersion: '8.1',
    featured: false,
    isPublished: true,
    features: [
      'Restores classic TinyMCE post and page editing screen',
      'Configurable site-wide default editor (Classic or Block Editor)',
      'Dual editor mode: provides Edit (Classic) and Edit (Block) quick action links',
      'Granular post type selection to target specific custom post types',
      'Official WordPress use_block_editor_for_post_type filter integration',
    ],
    installationSteps: [
      'Upload earthin-classic-editor to /wp-content/plugins/.',
      'Activate via the Plugins screen in WordPress.',
      'Configure preferences in Settings > Earthin Editor.',
    ],
    releases: [
      {
        version: '0.1.0',
        date: '2026-11-09',
        minWpVersion: '6.4',
        testedWpVersion: '6.7',
        minPhpVersion: '8.1',
        isStable: true,
        notes: [
          'Initial release with default editor selection, dual editor links, and post-type filtering.',
        ],
      },
    ],
  },
  {
    id: 'earthin-advanced-editor',
    name: 'Earthin Advanced Editor',
    slug: 'earthin-advanced-editor',
    author: 'Shaurya Joshi',
    authorUri: 'https://plugins.earthin.online',
    tagline: 'Unlocks advanced formatting buttons, typography sizing, and role-based toolbars in TinyMCE.',
    shortDescription: 'Enhance the classic editor with font families, font sizes, text highlighting, and subscript/superscript.',
    description: `Earthin Advanced Editor unlocks hidden formatting capabilities within WordPress's classic TinyMCE editor.

Add font size selectors, font family pickers, text background highlighting, full justification, underline, and subscript/superscript buttons with optional role-based toolbar restrictions.`,
    category: 'editor',
    pricingModel: 'free',
    freeVersionAvailable: true,
    premiumVersionAvailable: false,
    latestVersion: '0.1.0',
    minWpVersion: '6.4',
    testedWpVersion: '6.7',
    minPhpVersion: '8.1',
    featured: false,
    isPublished: true,
    features: [
      'TinyMCE toolbar button injections: fontselect, fontsizeselect, backcolor, underline, alignjustify, sub, sup',
      'Custom space-separated font size increments via tiny_mce_before_init',
      'Role-based permissions: restrict advanced formatting to Administrators and Editors',
      'Compatible alongside Earthin Classic Editor with zero feature overlap',
    ],
    installationSteps: [
      'Upload earthin-advanced-editor to /wp-content/plugins/.',
      'Activate through the Plugins screen in WordPress.',
      'Configure enabled toolbar buttons in Settings > Advanced Editor.',
    ],
    releases: [
      {
        version: '0.1.0',
        date: '2026-11-09',
        minWpVersion: '6.4',
        testedWpVersion: '6.7',
        minPhpVersion: '8.1',
        isStable: true,
        notes: [
          'Initial release with TinyMCE row 1 and row 2 toolbar button injections, font sizing, and role filters.',
        ],
      },
    ],
  },
  {
    id: 'earthin-white-label',
    name: 'Earthin White Label',
    slug: 'earthin-white-label',
    author: 'Shaurya Joshi',
    authorUri: 'https://plugins.earthin.online',
    tagline: 'Clean, reversible branding controls for the WordPress login screen and client administration.',
    shortDescription: 'Replace the WordPress login logo, hide unnecessary dashboard widgets, and customize admin footer credits.',
    description: `Earthin White Label enables administrators and agencies to deliver a clean, customized, and branded WordPress administration portal to their clients.

Replaces the default WordPress login logo and link, cleans up distracting default dashboard widgets, removes WordPress core version numbers, and adds customized agency footer credits. 100% reversible with zero core file modifications.`,
    category: 'social',
    pricingModel: 'free',
    freeVersionAvailable: true,
    premiumVersionAvailable: false,
    latestVersion: '0.1.0',
    minWpVersion: '6.4',
    testedWpVersion: '6.7',
    minPhpVersion: '8.1',
    featured: false,
    isPublished: true,
    features: [
      'Custom login logo URL and destination hyperlink on wp-login.php',
      'Custom login background color styling',
      'Selectively remove default dashboard widgets (News, Quick Draft, Activity, At a Glance)',
      'Custom admin footer text and version concealment for enhanced security',
      'Completely reversible: restoring defaults immediately restores standard WordPress UI',
    ],
    installationSteps: [
      'Upload earthin-white-label to /wp-content/plugins/.',
      'Activate via the Plugins screen in WordPress.',
      'Configure login and dashboard branding in Settings > White Label.',
    ],
    releases: [
      {
        version: '0.1.0',
        date: '2026-11-09',
        minWpVersion: '6.4',
        testedWpVersion: '6.7',
        minPhpVersion: '8.1',
        isStable: true,
        notes: [
          'Initial release with login logo replacement, dashboard widget visibility controls, custom footer text, and version concealment.',
        ],
      },
    ],
  },
  {
    id: 'earthin-social-share',
    name: 'Earthin Social Share',
    slug: 'earthin-social-share',
    author: 'Shaurya Joshi',
    authorUri: 'https://plugins.earthin.online',
    tagline: 'Fast, privacy-focused social sharing buttons with zero external tracking scripts or cookies.',
    shortDescription: 'Add lightweight social sharing buttons for X, Facebook, LinkedIn, WhatsApp, and Telegram without tracking scripts.',
    description: `Earthin Social Share adds clean, lightweight social sharing links to your WordPress posts and pages without loading slow third-party JavaScript or invasive tracking cookies.

Features pure HTML/CSS sharing intent buttons for X (Twitter), Facebook, LinkedIn, WhatsApp, Telegram, Reddit, and direct Email. Offers automatic placement above or below content, post-type selection, and a manual shortcode.`,
    category: 'social',
    pricingModel: 'free',
    freeVersionAvailable: true,
    premiumVersionAvailable: false,
    latestVersion: '0.1.0',
    minWpVersion: '6.4',
    testedWpVersion: '6.7',
    minPhpVersion: '8.1',
    featured: false,
    isPublished: true,
    features: [
      'Zero external scripts: 100% pure HTML and CSS with official sharing intent links',
      'Supported networks: X (Twitter), Facebook, LinkedIn, WhatsApp, Telegram, Reddit, and Email',
      'Flexible automatic placement: below content, above content, both, or shortcode only',
      'Shortcode support: [earthin_social_share] for custom layout placement',
      'Granular post-type targeting (Posts, Pages, custom post types)',
      'Un-render-blocking, accessible markup with rel="noopener noreferrer"',
    ],
    installationSteps: [
      'Upload earthin-social-share to /wp-content/plugins/.',
      'Activate via the Plugins screen in WordPress.',
      'Select active sharing networks in Settings > Social Share.',
    ],
    releases: [
      {
        version: '0.1.0',
        date: '2026-11-09',
        minWpVersion: '6.4',
        testedWpVersion: '6.7',
        minPhpVersion: '8.1',
        isStable: true,
        notes: [
          'Initial release with 7 social sharing intent networks, automated post insertion, shortcode support, and pure CSS styling.',
        ],
      },
    ],
  },
];

export function getPluginBySlug(slug: string): PluginItem | undefined {
  return PLUGINS.find((p) => p.slug === slug);
}

export function getAllPlugins(): PluginItem[] {
  return PLUGINS.filter((p) => p.isPublished);
}

export function getFeaturedPlugins(): PluginItem[] {
  return PLUGINS.filter((p) => p.isPublished && p.featured);
}
