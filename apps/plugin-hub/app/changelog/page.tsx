import React from 'react';
import Link from 'next/link';
import { GitCommit, Tag, Calendar, ShieldCheck, CheckCircle } from 'lucide-react';
import { Badge } from '@/components/ui/Badge';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/Card';

export default function ChangelogPage() {
  const changelogs = [
    {
      pluginName: 'Earthin WP Suite',
      slug: 'earthin-wp-suite',
      version: '0.2.0',
      date: 'November 9, 2026',
      badge: 'STABLE RELEASE',
      badgeVariant: 'emerald' as const,
      summary: 'Extensive architecture hardening, full 4-tab administration dashboard, SSRF feed defense, exact-path redirect manager, and form persistence.',
      sections: [
        {
          type: 'Security',
          items: [
            'SSRF Hardening: Implemented DNS resolution and strict IP filtering in the RSS module, blocking private (RFC 1918), reserved, and cloud metadata destinations (169.254.169.254).',
            'Header Injection Defense: Stripped carriage return (\\r) and line feed (\\n) characters from contact form sender names and email addresses.',
            'Anti-Spam Honeypot: Added hidden honeypot validation to [earthin_contact_form] shortcode.',
            'JSON-LD XSS Defense: Used JSON_HEX_TAG flags to prevent </script> tag breakouts in structured data.',
            'CSV Injection Protection: Neutralized dangerous leading spreadsheet characters (=, +, -, @) during inquiry export.',
          ],
        },
        {
          type: 'New Features',
          items: [
            'Multi-Tab Admin Dashboard: Added clean tabbed interface for Modules & Controls, Redirects Manager, Form Inquiries, and System Status.',
            'Redirect Management UI: Complete administrative interface to add, view, and delete exact-path redirects with hit counting and loop guards.',
            'Form Submissions Persistence: Inquiries submitted via shortcode are permanently recorded in the database under private post type ews_submission.',
            'Formula-Safe CSV Export: Export saved inquiries to CSV with one click.',
            'System Diagnostics Tab: Real-time telemetry reporting PHP/WP version compliance, memory limits, and third-party SEO plugin detection status.',
          ],
        },
        {
          type: 'Bug Fixes & Improvements',
          items: [
            'Resolved 404 Redirect Bypass: Removed early is_404() return so broken URLs are actively recovered by redirect rules.',
            'External Redirection: Added safe handling for external URLs using wp_redirect while maintaining wp_safe_redirect for internal links.',
            'Document Title Integration: Connected custom post SEO titles to pre_get_document_title.',
            'Standardized Versioning: Synchronized plugin version 0.2.0 across bootstrap, readme.txt, and documentation.',
            'Automated Test Suite: Created standalone unit test suite and CI test runner in GitHub Actions.',
          ],
        },
      ],
    },
    {
      pluginName: 'Earthin WP Suite',
      slug: 'earthin-wp-suite',
      version: '0.1.0',
      date: 'November 6, 2026',
      badge: 'INITIAL SCAFFOLD',
      badgeVariant: 'slate' as const,
      summary: 'Initial architectural foundation and core module registration.',
      sections: [
        {
          type: 'Initial Features',
          items: [
            'Created central plugin bootstrap and singleton Plugin class.',
            'Added Module_Manager registry with is_enabled boot loop.',
            'Added initial SEO, Social Preview, Security, Redirects, RSS, Schema, Sitemap, and Forms module scaffolds.',
            'Configured Settings API sanitization and manage_options capability checks.',
            'Added non-destructive uninstall procedure preserving site data.',
          ],
        },
      ],
    },
  ];

  return (
    <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 space-y-12">
      {/* Header */}
      <div className="max-w-3xl space-y-3">
        <Badge variant="emerald">RELEASE NOTES</Badge>
        <h1 className="text-4xl font-extrabold tracking-tight text-slate-900">
          Changelog & Updates
        </h1>
        <p className="text-base text-slate-600">
          Transparent, verifiable release history for all Earthin WordPress plugins and tools.
        </p>
      </div>

      {/* Changelog Timeline */}
      <div className="space-y-10">
        {changelogs.map((entry) => (
          <Card key={entry.version} className="bg-white border-slate-200">
            <CardHeader className="border-b border-slate-100 pb-4 mb-4">
              <div className="flex flex-wrap items-center justify-between gap-3">
                <div className="flex items-center gap-3">
                  <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white font-mono font-bold text-sm">
                    {entry.version}
                  </span>
                  <div>
                    <CardTitle className="text-xl">
                      <Link href={`/plugins/${entry.slug}`} className="hover:text-emerald-700 transition-colors">
                        {entry.pluginName}
                      </Link>
                    </CardTitle>
                    <p className="text-xs text-slate-500 flex items-center gap-1.5 mt-0.5">
                      <Calendar className="h-3.5 w-3.5 text-slate-400" />
                      {entry.date}
                    </p>
                  </div>
                </div>
                <Badge variant={entry.badgeVariant}>{entry.badge}</Badge>
              </div>
              <p className="text-sm text-slate-600 mt-3 font-medium">
                {entry.summary}
              </p>
            </CardHeader>

            <CardContent className="space-y-6 pt-2">
              {entry.sections.map((sec, sIdx) => (
                <div key={sIdx} className="space-y-2">
                  <h4 className="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center gap-1.5">
                    <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                    {sec.type}
                  </h4>
                  <ul className="space-y-1.5 text-xs text-slate-600 pl-3">
                    {sec.items.map((item, iIdx) => (
                      <li key={iIdx} className="leading-relaxed list-disc">
                        {item}
                      </li>
                    ))}
                  </ul>
                </div>
              ))}
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
}
