import React from 'react';
import Link from 'next/link';
import { BookOpen, ShieldCheck, Terminal, HelpCircle, Layers, CheckCircle2 } from 'lucide-react';
import { Badge } from '@/components/ui/Badge';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/Card';

export default function DocsPage() {
  const sections = [
    {
      id: 'quickstart',
      title: 'Quick Start & Installation',
      description: 'How to install and activate Earthin WordPress plugins in your environment.',
      content: [
        'Download the official release archive from the Earthin Plugin Hub or releases page.',
        'Upload the ZIP file to your WordPress admin under Plugins > Add New > Upload Plugin.',
        'Activate the plugin. Settings are automatically populated with safe defaults.',
        'Open the Earthin Suite dashboard from the WordPress admin menu.',
      ],
    },
    {
      id: 'modules',
      title: 'Module Manager & Independence',
      description: 'Understanding independent module toggles and collision avoidance.',
      content: [
        'Every module (SEO, Social, Security, Redirects, RSS, Forms, Sitemap, Schema) has an independent toggle switch.',
        'Disabling one module will never disrupt, erase, or disable other features.',
        'Third-Party SEO Compatibility: If Yoast SEO, Rank Math, All in One SEO, SEOPress, or The SEO Framework is active, Earthin Suite automatically suppresses its own frontend SEO & Social tags to avoid duplicate meta.',
      ],
    },
    {
      id: 'redirects',
      title: 'Exact-Path Redirects & 404 Recovery',
      description: 'Configuring URL rerouting and 404 recovery rules.',
      content: [
        'Redirect rules allow exact relative path matching (e.g. /old-page) to target paths or external URLs.',
        'Runs during template_redirect and actively executes on 404 requests to recover broken links.',
        'Built-in loop detection prevents redirecting a path back to itself.',
        'Every redirect hit is logged in the hit counter on the admin dashboard.',
      ],
    },
    {
      id: 'forms',
      title: 'Safe Contact Form Shortcode',
      description: 'Embedding contact forms with honeypot spam protection.',
      content: [
        'Insert [earthin_contact_form] in any post, page, or widget.',
        'Submissions are protected against spam with an invisible honeypot field.',
        'Anti-injection defense strips CRLF characters from sender headers to prevent email header injection.',
        'Every submission is permanently saved in the database under private post type ews_submission so messages are never lost if email delivery fails.',
        'Administrators can view, manage, and export submissions to CSV with formula injection protection.',
      ],
    },
    {
      id: 'security',
      title: 'Security Principles & SSRF Defense',
      description: 'Built-in security hardening across the plugin suite.',
      content: [
        'Privilege Checks: Privileged administrative actions strictly require manage_options or publish_posts.',
        'Nonces: Every form submission, setting update, or deletion is guarded with WordPress nonces.',
        'SSRF Protection: RSS imports validate URLs using DNS resolution and strictly block private, loopback, and reserved network IP destinations (e.g. 127.0.0.1, 10.x.x.x, 169.254.169.254).',
        'JSON-LD Escaping: JSON structured data is hex-encoded with JSON_HEX_TAG to prevent script tag breakout XSS.',
      ],
    },
  ];

  const faqs = [
    {
      q: 'Does Earthin WP Suite work alongside my existing caching plugin?',
      a: 'Yes. Earthin WP Suite uses standard WordPress hooks (wp_head, send_headers, template_redirect) and does not interfere with caching plugins such as WP Super Cache, W3 Total Cache, or LiteSpeed Cache.',
    },
    {
      q: 'Will uninstalling the plugin delete my website content or settings?',
      a: 'No. The uninstall procedure is explicitly non-destructive by default. It preserves site settings, redirect rules, and form submissions unless a full wipe is manually triggered.',
    },
    {
      q: 'What PHP version is required?',
      a: 'PHP 8.1 or higher is required. The plugin entrypoint checks PHP_VERSION before loading modern syntax, displaying a friendly admin notice if the server is incompatible.',
    },
    {
      q: 'Where do I report bugs or submit feature requests?',
      a: 'You can submit issues directly via our GitHub repository or contact our team through the Support page.',
    },
  ];

  return (
    <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 space-y-16">
      {/* Header */}
      <div className="max-w-3xl space-y-3">
        <Badge variant="emerald">KNOWLEDGE BASE</Badge>
        <h1 className="text-4xl font-extrabold tracking-tight text-slate-900">
          Documentation & Guides
        </h1>
        <p className="text-base text-slate-600">
          Everything you need to configure, operate, and troubleshoot Earthin WordPress plugins.
        </p>
      </div>

      {/* Guide Cards */}
      <div className="space-y-8">
        {sections.map((sec) => (
          <Card key={sec.id} className="bg-white border-slate-200">
            <CardHeader>
              <CardTitle className="text-xl text-slate-900">{sec.title}</CardTitle>
              <p className="text-xs text-slate-500">{sec.description}</p>
            </CardHeader>
            <CardContent>
              <ul className="space-y-2 text-xs sm:text-sm text-slate-700">
                {sec.content.map((c, idx) => (
                  <li key={idx} className="flex items-start gap-2.5">
                    <CheckCircle2 className="h-4 w-4 text-emerald-600 shrink-0 mt-0.5" />
                    <span className="leading-relaxed">{c}</span>
                  </li>
                ))}
              </ul>
            </CardContent>
          </Card>
        ))}
      </div>

      {/* FAQs */}
      <div className="space-y-6">
        <div>
          <Badge variant="slate" className="mb-2">HELP CENTER</Badge>
          <h2 className="text-2xl font-bold text-slate-900">Frequently Asked Questions</h2>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {faqs.map((faq, idx) => (
            <div key={idx} className="p-6 bg-white rounded-xl border border-slate-200/80 shadow-sm space-y-2">
              <h4 className="text-sm font-bold text-slate-900">{faq.q}</h4>
              <p className="text-xs text-slate-600 leading-relaxed">{faq.a}</p>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
