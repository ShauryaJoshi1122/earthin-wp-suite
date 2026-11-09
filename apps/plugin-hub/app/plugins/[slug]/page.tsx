import React from 'react';
import { notFound } from 'next/navigation';
import Link from 'next/link';
import {
  Package,
  Download,
  ShieldCheck,
  CheckCircle2,
  ExternalLink,
  BookOpen,
  ArrowLeft,
  LifeBuoy,
  FileCode,
  Tag,
  GitBranch,
} from 'lucide-react';
import { getPluginBySlug, PLUGINS } from '@/lib/catalog';
import { Button } from '@/components/ui/Button';
import { Badge } from '@/components/ui/Badge';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/Card';

export function generateStaticParams() {
  return PLUGINS.map((p) => ({
    slug: p.slug,
  }));
}

export default function PluginProductPage({ params }: { params: { slug: string } }) {
  const plugin = getPluginBySlug(params.slug);

  if (!plugin) {
    notFound();
  }

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 space-y-12">
      {/* Back button */}
      <div>
        <Link
          href="/plugins"
          className="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors"
        >
          <ArrowLeft className="mr-1.5 h-3.5 w-3.5" />
          Back to Plugin Directory
        </Link>
      </div>

      {/* Product Hero */}
      <div className="flex flex-col lg:flex-row gap-8 items-start justify-between bg-white p-8 rounded-2xl border border-slate-200/80 shadow-sm">
        <div className="space-y-4 max-w-2xl">
          <div className="flex items-center gap-3">
            <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-100 shadow-sm">
              <Package className="h-8 w-8" />
            </div>
            <div>
              <div className="flex items-center gap-2">
                <h1 className="text-3xl font-extrabold text-slate-900">{plugin.name}</h1>
                <Badge variant={plugin.pricingModel === 'free' ? 'emerald' : 'indigo'}>
                  {plugin.pricingModel.toUpperCase()}
                </Badge>
              </div>
              <p className="text-sm text-slate-500 font-mono mt-0.5">Version {plugin.latestVersion}</p>
            </div>
          </div>

          <p className="text-lg text-slate-700 leading-relaxed font-medium">
            {plugin.tagline}
          </p>

          <p className="text-sm text-slate-600 leading-relaxed whitespace-pre-line">
            {plugin.description}
          </p>

          <div className="flex flex-wrap items-center gap-3 pt-2">
            <a
              href="https://github.com/ShauryaJoshi1122/earthin-wp-suite/archive/refs/heads/main.zip"
              className="inline-block"
              download
            >
              <Button size="md" variant="primary">
                <Download className="mr-2 h-4 w-4" />
                Download Free Release
              </Button>
            </a>
            <a
              href="https://github.com/ShauryaJoshi1122/earthin-wp-suite"
              target="_blank"
              rel="noopener noreferrer"
              className="inline-block"
            >
              <Button size="md" variant="outline">
                <ExternalLink className="mr-2 h-4 w-4" />
                GitHub Repository
              </Button>
            </a>
            <Link href="/docs">
              <Button size="md" variant="ghost">
                <BookOpen className="mr-2 h-4 w-4" />
                Documentation
              </Button>
            </Link>
          </div>
        </div>

        {/* Sidebar Specifications */}
        <div className="w-full lg:w-80 shrink-0 bg-slate-50 p-6 rounded-xl border border-slate-200/80 space-y-4">
          <h3 className="text-xs font-bold uppercase tracking-wider text-slate-900">Technical Details</h3>
          <dl className="divide-y divide-slate-200 text-xs">
            <div className="py-2.5 flex justify-between">
              <dt className="text-slate-500">Current Version</dt>
              <dd className="font-semibold text-slate-800 font-mono">{plugin.latestVersion}</dd>
            </div>
            <div className="py-2.5 flex justify-between">
              <dt className="text-slate-500">Requires WordPress</dt>
              <dd className="font-semibold text-slate-800">{plugin.minWpVersion}+</dd>
            </div>
            <div className="py-2.5 flex justify-between">
              <dt className="text-slate-500">Requires PHP</dt>
              <dd className="font-semibold text-slate-800">{plugin.minPhpVersion}+</dd>
            </div>
            <div className="py-2.5 flex justify-between">
              <dt className="text-slate-500">Developer</dt>
              <dd className="font-semibold text-slate-800">{plugin.author || 'Shaurya Joshi'}</dd>
            </div>
            <div className="py-2.5 flex justify-between">
              <dt className="text-slate-500">License</dt>
              <dd className="font-semibold text-slate-800">GPL-2.0 or later</dd>
            </div>
            <div className="py-2.5 flex justify-between">
              <dt className="text-slate-500">Tested up to</dt>
              <dd className="font-semibold text-slate-800">WordPress {plugin.testedWpVersion}</dd>
            </div>
          </dl>

          <div className="pt-2">
            <Link href="/support" className="w-full">
              <Button variant="outline" size="sm" className="w-full justify-center">
                <LifeBuoy className="mr-2 h-3.5 w-3.5" />
                Contact Plugin Support
              </Button>
            </Link>
          </div>
        </div>
      </div>

      {/* Features & Modules Breakdown */}
      <div className="space-y-6">
        <div>
          <Badge variant="emerald" className="mb-2">ARCHITECTURE</Badge>
          <h2 className="text-2xl font-bold text-slate-900">Included Modules & Capabilities</h2>
          <p className="text-sm text-slate-600">Every feature is independently toggled and monitored via the suite dashboard.</p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {plugin.features.map((feat, idx) => (
            <div key={idx} className="flex items-start gap-3 p-4 bg-white rounded-xl border border-slate-200/80 shadow-sm">
              <CheckCircle2 className="h-5 w-5 text-emerald-600 shrink-0 mt-0.5" />
              <span className="text-sm text-slate-700 leading-snug">{feat}</span>
            </div>
          ))}
        </div>
      </div>

      {/* Free vs Premium Comparison */}
      {plugin.premiumFeatures && plugin.premiumFeatures.length > 0 && (
        <div className="space-y-6">
          <div>
            <Badge variant="indigo" className="mb-2">TIER COMPARISON</Badge>
            <h2 className="text-2xl font-bold text-slate-900">Free Core vs Pro Plan Comparison</h2>
            <p className="text-sm text-slate-600">Understand what is available today and planned in upcoming pro milestones.</p>
          </div>

          <div className="overflow-x-auto rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <table className="w-full text-left text-sm">
              <thead className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-700 uppercase">
                <tr>
                  <th className="py-3 px-6">Feature</th>
                  <th className="py-3 px-6 text-center w-36">Free Core</th>
                  <th className="py-3 px-6 text-center w-36">Pro / Team</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-700">
                <tr>
                  <td className="py-3.5 px-6 font-medium">Core SEO, Canonical, and Description Meta</td>
                  <td className="py-3.5 px-6 text-center"><CheckCircle2 className="h-4 w-4 text-emerald-600 mx-auto" /></td>
                  <td className="py-3.5 px-6 text-center"><CheckCircle2 className="h-4 w-4 text-emerald-600 mx-auto" /></td>
                </tr>
                <tr>
                  <td className="py-3.5 px-6 font-medium">Exact-Path Redirects & 404 Recovery</td>
                  <td className="py-3.5 px-6 text-center"><CheckCircle2 className="h-4 w-4 text-emerald-600 mx-auto" /></td>
                  <td className="py-3.5 px-6 text-center"><CheckCircle2 className="h-4 w-4 text-emerald-600 mx-auto" /></td>
                </tr>
                <tr>
                  <td className="py-3.5 px-6 font-medium">Anti-Spam Honeypot & Contact Form Submissions</td>
                  <td className="py-3.5 px-6 text-center"><CheckCircle2 className="h-4 w-4 text-emerald-600 mx-auto" /></td>
                  <td className="py-3.5 px-6 text-center"><CheckCircle2 className="h-4 w-4 text-emerald-600 mx-auto" /></td>
                </tr>
                <tr>
                  <td className="py-3.5 px-6 font-medium">Formula-Safe CSV Submission Export</td>
                  <td className="py-3.5 px-6 text-center"><CheckCircle2 className="h-4 w-4 text-emerald-600 mx-auto" /></td>
                  <td className="py-3.5 px-6 text-center"><CheckCircle2 className="h-4 w-4 text-emerald-600 mx-auto" /></td>
                </tr>
                <tr>
                  <td className="py-3.5 px-6 font-medium">Automated Cron-Scheduled Background RSS Ingestion</td>
                  <td className="py-3.5 px-6 text-center text-slate-300">&mdash;</td>
                  <td className="py-3.5 px-6 text-center"><CheckCircle2 className="h-4 w-4 text-emerald-600 mx-auto" /></td>
                </tr>
                <tr>
                  <td className="py-3.5 px-6 font-medium">AI Editorial Outlines & Automated Summarization</td>
                  <td className="py-3.5 px-6 text-center text-slate-300">&mdash;</td>
                  <td className="py-3.5 px-6 text-center"><CheckCircle2 className="h-4 w-4 text-emerald-600 mx-auto" /></td>
                </tr>
                <tr>
                  <td className="py-3.5 px-6 font-medium">Automated Site & Database Snapshot Backups</td>
                  <td className="py-3.5 px-6 text-center text-slate-300">&mdash;</td>
                  <td className="py-3.5 px-6 text-center"><CheckCircle2 className="h-4 w-4 text-emerald-600 mx-auto" /></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Installation Instructions */}
      <div className="space-y-4">
        <h2 className="text-xl font-bold text-slate-900">Installation Guide</h2>
        <div className="bg-white p-6 rounded-xl border border-slate-200/80 shadow-sm space-y-3">
          <ol className="list-decimal list-inside space-y-2 text-sm text-slate-700 leading-relaxed">
            {plugin.installationSteps.map((step, idx) => (
              <li key={idx} className="pl-1">
                {step}
              </li>
            ))}
          </ol>
        </div>
      </div>

      {/* Release Changelog */}
      <div className="space-y-4">
        <h2 className="text-xl font-bold text-slate-900">Version History</h2>
        <div className="space-y-4">
          {plugin.releases.map((rel) => (
            <Card key={rel.version} className="bg-white">
              <CardHeader className="mb-2">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <CardTitle className="text-base font-bold font-mono">v{rel.version}</CardTitle>
                    {rel.isStable && <Badge variant="emerald">Stable</Badge>}
                  </div>
                  <span className="text-xs text-slate-500 font-mono">{rel.date}</span>
                </div>
              </CardHeader>
              <CardContent>
                <ul className="list-disc list-inside space-y-1 text-xs text-slate-600">
                  {rel.notes.map((note, nIdx) => (
                    <li key={nIdx}>{note}</li>
                  ))}
                </ul>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </div>
  );
}
