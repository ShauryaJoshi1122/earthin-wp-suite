export interface EarthinTheme {
  id: string;
  slug: string;
  name: string;
  tagline: string;
  category: 'Multi-Purpose' | 'Writing & Editorial' | 'eCommerce & Store' | 'Agency & SaaS';
  version: string;
  templatesCount: number;
  phpRequires: string;
  wpRequires: string;
  license: string;
  badge: string;
  accentColor: string;
  description: string;
  features: string[];
  templates?: string[];
  downloadUrl: string;
  githubUrl: string;
}

export const EARTHIN_THEMES: EarthinTheme[] = [
  {
    id: 'earthin',
    slug: 'earthin',
    name: 'Earthin Flagship',
    tagline: 'Ultra-lightweight multi-purpose theme with 14 high-speed production templates.',
    category: 'Multi-Purpose',
    version: '1.0.0',
    templatesCount: 14,
    phpRequires: '8.1+',
    wpRequires: '6.4+',
    license: 'GPL-2.0+',
    badge: '14 TEMPLATES INCLUDED',
    accentColor: 'emerald',
    description: 'The core flagship theme of the Earthin suite. Engineered with zero external framework dependencies, providing 14 specialized templates for news portals, SaaS pricing, corporate profiles, knowledge bases, and landing page funnels.',
    features: [
      '14 Production Page Templates (News Magazine, Pricing Matrix, Knowledge Base, Team, Changelog, etc.)',
      'Dynamic body class hooks for sidebar and full-width layouts',
      'Native Gutenberg Block & Earthin Page Builder Pro compatibility',
      'Sub-100ms TTFB latency and 100/100 Core Web Vitals pass rate',
      'Threaded comments, author bio boxes, and automated reading time calculations',
    ],
    templates: [
      'News & Editorial Magazine (template-news.php)',
      'Pricing & Plans with Billing Switcher (template-pricing.php)',
      'Knowledge Base & Instant Search (template-knowledgebase.php)',
      'Team & Careers Board (template-team.php)',
      'Changelog & Timeline Release Notes (template-changelog.php)',
      'Case Studies & 5-Star Testimonials (template-testimonials.php)',
      'Full Width No-Sidebar Layout (template-full-width.php)',
      'Blank Canvas Landing Page (template-canvas.php)',
      'Distraction-Free Longform Reading (template-reading.php)',
      'Contact Info Grid & Accordion (template-contact.php)',
      'About Us & Company Profile (template-about.php)',
      'Services & Solutions Roadmap (template-services.php)',
      'Portfolio & Project Showcase (template-portfolio.php)',
      'Left Sidebar Editorial Wiki (template-left-sidebar.php)',
    ],
    downloadUrl: '/api/download/theme/earthin',
    githubUrl: 'https://github.com/ShauryaJoshi1122/earthin-wp-suite/tree/main/themes/earthin',
  },
  {
    id: 'earthin-minimal',
    slug: 'earthin-minimal',
    name: 'Earthin Minimal',
    tagline: 'Distraction-free typography-first theme for writers, essayists, and journalists.',
    category: 'Writing & Editorial',
    version: '1.0.0',
    templatesCount: 6,
    phpRequires: '8.1+',
    wpRequires: '6.4+',
    license: 'GPL-2.0+',
    badge: 'WRITER & TYPOGRAPHY',
    accentColor: 'teal',
    description: 'Designed exclusively for readers and writers. Strips away all visual distractions, featuring pure CSS dark mode, real-time scroll reading progress indicator, estimated read times, and elegant serif typography.',
    features: [
      'Real-time scroll reading progress indicator bar',
      'Pure CSS dark/light mode toggle with localStorage persistence',
      'Automated reading time estimator (X min read)',
      'High-legibility editorial serif/sans font pairings',
      'Zero render-blocking scripts for instant page paint',
    ],
    templates: [
      'Editorial Stream Index (index.php)',
      'Longform Article Layout (single.php)',
      'Clean Standard Page (page.php)',
      'Categorized Archive (archive.php)',
      'Search Results (search.php)',
      'Minimal 404 Recovery (404.php)',
    ],
    downloadUrl: '/api/download/theme/earthin-minimal',
    githubUrl: 'https://github.com/ShauryaJoshi1122/earthin-wp-suite/tree/main/themes/earthin-minimal',
  },
  {
    id: 'earthin-store',
    slug: 'earthin-store',
    name: 'Earthin Store',
    tagline: 'High-converting digital storefront and eCommerce theme with instant UPI QR checkout.',
    category: 'eCommerce & Store',
    version: '1.0.0',
    templatesCount: 6,
    phpRequires: '8.1+',
    wpRequires: '6.4+',
    license: 'GPL-2.0+',
    badge: 'INSTANT UPI QR READY',
    accentColor: 'blue',
    description: 'Engineered specifically for selling digital downloads, software licenses, plugins, and merchandise. Includes direct UPI QR payment scan badges, product catalog grid with sale tags, and conversion trust seals.',
    features: [
      'Direct integration with Earthin Dynamic UPI QR Payment Engine',
      'Product catalog cards with sale badges, price strikes, and customer reviews',
      'Sticky navigation with promo announcement banner and mini-cart indicator',
      'Trust guarantee strip (30-day refund, instant download, SSL secured)',
      'Frictionless checkout layout optimized for mobile and desktop conversions',
    ],
    templates: [
      'Digital Store Catalog (index.php)',
      'Product Detail & Checkout Layout (single.php)',
      'Standard Store Page (page.php)',
      'Category Product Archive (archive.php)',
      'Catalog Search (search.php)',
      'Store 404 Page (404.php)',
    ],
    downloadUrl: '/api/download/theme/earthin-store',
    githubUrl: 'https://github.com/ShauryaJoshi1122/earthin-wp-suite/tree/main/themes/earthin-store',
  },
  {
    id: 'earthin-agency',
    slug: 'earthin-agency',
    name: 'Earthin Agency',
    tagline: 'Sleek dark-mode bento grid theme for SaaS startups, tech consultancies, and agencies.',
    category: 'Agency & SaaS',
    version: '1.0.0',
    templatesCount: 6,
    phpRequires: '8.1+',
    wpRequires: '6.4+',
    license: 'GPL-2.0+',
    badge: 'DARK BENTO GRID',
    accentColor: 'purple',
    description: 'High-impact corporate and tech startup theme featuring modern dark-mode bento grid layouts, live interactive infrastructure performance counters, case study spotlights, and strategic consultation booking banners.',
    features: [
      'Asymmetric dark-mode bento grid showcase cards',
      'Real-time metrics counter (Uptime SLA, TTFB, Active Installs, PageSpeed)',
      'Interactive client work & case studies showcase grid',
      'High-conversion discovery call and project consultation CTAs',
      'Semantic CSS styling with WCAG AAA accessibility compliance',
    ],
    templates: [
      'Agency Homepage with Bento Grid (index.php)',
      'Case Study Spotlight Layout (single.php)',
      'Corporate Information Page (page.php)',
      'Project & News Archive (archive.php)',
      'Site Search (search.php)',
      'Cyber 404 Route (404.php)',
    ],
    downloadUrl: '/api/download/theme/earthin-agency',
    githubUrl: 'https://github.com/ShauryaJoshi1122/earthin-wp-suite/tree/main/themes/earthin-agency',
  },
];

export function getAllThemes(): EarthinTheme[] {
  return EARTHIN_THEMES;
}

export function getThemeBySlug(slug: string): EarthinTheme | undefined {
  return EARTHIN_THEMES.find((t) => t.slug === slug);
}
