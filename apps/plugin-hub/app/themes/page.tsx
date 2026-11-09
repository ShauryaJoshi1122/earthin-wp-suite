'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { Layers, Download, CheckCircle, ExternalLink, Sparkles, LayoutTemplate, Shield, ArrowRight, Eye, Code2 } from 'lucide-react';
import { Button } from '@/components/ui/Button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/Card';
import { Badge } from '@/components/ui/Badge';
import { getAllThemes, EarthinTheme } from '@/lib/themes';

export default function ThemesPage() {
  const themes = getAllThemes();
  const [selectedCategory, setSelectedCategory] = useState<string>('All');
  const [expandedTemplates, setExpandedTemplates] = useState<string | null>(null);

  const categories = ['All', 'Multi-Purpose', 'Writing & Editorial', 'eCommerce & Store', 'Agency & SaaS'];

  const filteredThemes = selectedCategory === 'All'
    ? themes
    : themes.filter((t) => t.category === selectedCategory);

  const toggleTemplates = (id: string) => {
    setExpandedTemplates(expandedTemplates === id ? null : id);
  };

  return (
    <div className="space-y-16 pb-20">
      {/* 1. Header Hero */}
      <section className="bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 text-white pt-16 pb-20 border-b border-slate-700">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center space-y-5">
          <div className="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-400">
            <Sparkles className="h-3.5 w-3.5" />
            <span>4 STANDALONE THEMES • 14 SPECIALIZED TEMPLATES</span>
          </div>

          <h1 className="text-4xl font-extrabold tracking-tight sm:text-5xl md:text-6xl max-w-3xl mx-auto">
            Official <span className="text-emerald-400">Earthin Themes</span>
          </h1>

          <p className="mx-auto max-w-2xl text-lg text-slate-300 leading-relaxed">
            Ultra-lightweight, zero-bloat WordPress themes crafted for extreme performance, clean typography, instant UPI QR payments, and high-conversion publishing.
          </p>

          <div className="pt-4 flex flex-wrap items-center justify-center gap-6 text-xs text-slate-400 font-medium">
            <span className="flex items-center gap-1.5">
              <CheckCircle className="h-4 w-4 text-emerald-400" />
              100/100 Core Web Vitals
            </span>
            <span className="flex items-center gap-1.5">
              <CheckCircle className="h-4 w-4 text-emerald-400" />
              GPL-2.0+ Open Source
            </span>
            <span className="flex items-center gap-1.5">
              <CheckCircle className="h-4 w-4 text-emerald-400" />
              Strict WordPress Standards
            </span>
            <span className="flex items-center gap-1.5">
              <CheckCircle className="h-4 w-4 text-emerald-400" />
              PHP 8.1 & 8.2 Verified
            </span>
          </div>
        </div>
      </section>

      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-12">
        {/* 2. Category Filter Bar */}
        <div className="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
          <div className="flex flex-wrap gap-2">
            {categories.map((cat) => (
              <button
                key={cat}
                onClick={() => setSelectedCategory(cat)}
                className={`px-4 py-2 rounded-full text-sm font-semibold transition-all ${
                  selectedCategory === cat
                    ? 'bg-emerald-600 text-white shadow-sm'
                    : 'bg-white border border-slate-200 text-slate-600 hover:border-slate-300 hover:text-slate-900'
                }`}
              >
                {cat}
              </button>
            ))}
          </div>

          <span className="text-sm text-slate-500 font-medium">
            Showing {filteredThemes.length} {filteredThemes.length === 1 ? 'theme' : 'themes'}
          </span>
        </div>

        {/* 3. Themes Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          {filteredThemes.map((theme) => {
            const isExpanded = expandedTemplates === theme.id;
            return (
              <Card key={theme.id} className="flex flex-col border-slate-200 hover:border-slate-300 shadow-sm transition-all hover:shadow-md overflow-hidden bg-white">
                {/* Visual Banner Header */}
                <div className={`p-6 text-white ${
                  theme.accentColor === 'emerald' ? 'bg-gradient-to-r from-emerald-800 to-slate-900' :
                  theme.accentColor === 'teal' ? 'bg-gradient-to-r from-teal-800 to-slate-900' :
                  theme.accentColor === 'blue' ? 'bg-gradient-to-r from-blue-800 to-slate-900' :
                  'bg-gradient-to-r from-purple-900 to-slate-900'
                }`}>
                  <div className="flex items-center justify-between gap-2 mb-3">
                    <span className="text-xs font-bold uppercase tracking-wider bg-white/20 backdrop-blur-sm px-2.5 py-1 rounded-full text-white">
                      {theme.badge}
                    </span>
                    <span className="text-xs font-mono bg-black/30 px-2 py-0.5 rounded text-slate-300">
                      v{theme.version} • PHP {theme.phpRequires}
                    </span>
                  </div>
                  <h3 className="text-2xl font-extrabold tracking-tight text-white mb-1">
                    {theme.name}
                  </h3>
                  <p className="text-sm text-slate-200 leading-relaxed">
                    {theme.tagline}
                  </p>
                </div>

                <CardContent className="p-6 flex-1 flex flex-col justify-between space-y-6">
                  {/* Description & Features */}
                  <div className="space-y-4">
                    <p className="text-sm text-slate-600 leading-relaxed">
                      {theme.description}
                    </p>

                    <div>
                      <h4 className="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Key Highlights</h4>
                      <ul className="space-y-1.5 text-xs text-slate-700">
                        {theme.features.map((feat, idx) => (
                          <li key={idx} className="flex items-start gap-2">
                            <CheckCircle className="h-4 w-4 text-emerald-600 shrink-0 mt-0.5" />
                            <span>{feat}</span>
                          </li>
                        ))}
                      </ul>
                    </div>

                    {/* Template List Dropdown for Themes with templates */}
                    {theme.templates && theme.templates.length > 0 && (
                      <div className="pt-2">
                        <button
                          type="button"
                          onClick={() => toggleTemplates(theme.id)}
                          className="flex items-center justify-between w-full p-2.5 rounded-lg bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition-colors"
                        >
                          <span className="flex items-center gap-1.5">
                            <LayoutTemplate className="h-4 w-4 text-emerald-600" />
                            Included Templates ({theme.templates.length})
                          </span>
                          <span className="text-emerald-700">{isExpanded ? 'Hide ▲' : 'Show All ▼'}</span>
                        </button>

                        {isExpanded && (
                          <div className="mt-2 p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs text-slate-600 space-y-1 animate-fadeIn">
                            {theme.templates.map((tpl, tIdx) => (
                              <div key={tIdx} className="flex items-center gap-2 py-0.5">
                                <span className="text-emerald-600 font-mono text-[11px]">•</span>
                                <span className="font-mono text-[11px] text-slate-800">{tpl}</span>
                              </div>
                            ))}
                          </div>
                        )}
                      </div>
                    )}
                  </div>

                  {/* Action Buttons */}
                  <div className="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <div className="flex items-center gap-2">
                      <a
                        href={theme.githubUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 border border-slate-200 px-3 py-1.5 rounded-md hover:bg-slate-50 transition-colors"
                      >
                        <Code2 className="h-3.5 w-3.5" />
                        Inspect Source
                      </a>
                    </div>

                    <a
                      href="https://github.com/ShauryaJoshi1122/earthin-wp-suite/archive/refs/heads/main.zip"
                      className="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 px-4 py-2 rounded-md shadow-sm transition-colors"
                    >
                      <Download className="h-3.5 w-3.5" />
                      Download Theme ZIP
                    </a>
                  </div>
                </CardContent>
              </Card>
            );
          })}
        </div>

        {/* 4. Comparison Table */}
        <section className="pt-10 border-t border-slate-200 space-y-6">
          <div className="text-center max-w-2xl mx-auto">
            <Badge variant="emerald" className="mb-2">SIDE-BY-SIDE MATRIX</Badge>
            <h2 className="text-2xl font-bold tracking-tight text-slate-900">Which Earthin Theme Fits Your Project?</h2>
            <p className="text-sm text-slate-600 mt-1">Compare specifications, included templates, and payment integrations.</p>
          </div>

          <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table className="w-full text-left text-sm border-collapse">
              <thead>
                <tr className="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase tracking-wider text-slate-700">
                  <th className="p-4">Theme</th>
                  <th className="p-4">Best For</th>
                  <th className="p-4 text-center">Templates</th>
                  <th className="p-4 text-center">Dark Mode</th>
                  <th className="p-4 text-center">UPI QR Engine</th>
                  <th className="p-4 text-center">PageSpeed TTFB</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-700">
                <tr>
                  <td className="p-4 font-bold text-slate-900">Earthin Flagship</td>
                  <td className="p-4">Multi-purpose, News, Knowledge Base, Portals</td>
                  <td className="p-4 text-center font-bold text-emerald-600">14 Templates</td>
                  <td className="p-4 text-center">Included</td>
                  <td className="p-4 text-center">Ready</td>
                  <td className="p-4 text-center font-mono text-xs">&lt;100ms</td>
                </tr>
                <tr>
                  <td className="p-4 font-bold text-slate-900">Earthin Minimal</td>
                  <td className="p-4">Writers, Journalists, Clean Essays</td>
                  <td className="p-4 text-center font-semibold">6 Templates</td>
                  <td className="p-4 text-center font-bold text-emerald-600">Pure CSS Toggle</td>
                  <td className="p-4 text-center">Compatible</td>
                  <td className="p-4 text-center font-mono text-xs">&lt;80ms</td>
                </tr>
                <tr>
                  <td className="p-4 font-bold text-slate-900">Earthin Store</td>
                  <td className="p-4">Digital Software, Plugins, Merch Sales</td>
                  <td className="p-4 text-center font-semibold">6 Templates</td>
                  <td className="p-4 text-center">Compatible</td>
                  <td className="p-4 text-center font-bold text-emerald-600">Built-in Native</td>
                  <td className="p-4 text-center font-mono text-xs">&lt;110ms</td>
                </tr>
                <tr>
                  <td className="p-4 font-bold text-slate-900">Earthin Agency</td>
                  <td className="p-4">SaaS Startups, Technical Studios, Consultancies</td>
                  <td className="p-4 text-center font-semibold">6 Templates</td>
                  <td className="p-4 text-center font-bold text-emerald-600">Cyber Dark Default</td>
                  <td className="p-4 text-center">Ready</td>
                  <td className="p-4 text-center font-mono text-xs">&lt;120ms</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        {/* 5. 1-Click Installation Guide */}
        <section className="rounded-2xl bg-slate-900 text-white p-8 md:p-12 border border-slate-800">
          <div className="max-w-3xl space-y-4">
            <Badge variant="emerald" className="bg-emerald-500/20 text-emerald-400 border-emerald-500/30">
              QUICK SETUP
            </Badge>
            <h2 className="text-2xl md:text-3xl font-bold tracking-tight text-white">
              How to Install Earthin Themes in WordPress
            </h2>
            <ol className="list-decimal list-inside space-y-2 text-slate-300 text-sm leading-relaxed">
              <li>Download the theme ZIP archive using the buttons above.</li>
              <li>Log in to your WordPress dashboard (<code className="bg-slate-800 px-1.5 py-0.5 rounded text-emerald-300">/wp-admin</code>).</li>
              <li>Navigate to <strong>Appearance → Themes → Add New → Upload Theme</strong>.</li>
              <li>Select your downloaded ZIP file, click <strong>Install Now</strong>, and then click <strong>Activate</strong>.</li>
              <li>All custom page templates will instantly appear under <strong>Page Attributes → Template</strong> in your editor.</li>
            </ol>
            <div className="pt-4 flex gap-4">
              <Link href="/docs">
                <Button variant="primary" size="md">
                  View Full Theme Documentation
                </Button>
              </Link>
            </div>
          </div>
        </section>
      </div>
    </div>
  );
}
