import React from 'react';
import Link from 'next/link';
import { ArrowRight, ShieldCheck, Zap, Layers, RefreshCw, Download, CheckCircle, BookOpen, ExternalLink, LayoutTemplate, Sparkles } from 'lucide-react';
import { Button } from '@/components/ui/Button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/Card';
import { Badge } from '@/components/ui/Badge';
import { PluginCard } from '@/components/plugins/PluginCard';
import { getFeaturedPlugins } from '@/lib/catalog';
import { getAllThemes } from '@/lib/themes';

export default function HomePage() {
  const featured = getFeaturedPlugins();
  const themes = getAllThemes();

  const benefits = [
    {
      icon: <Layers className="h-6 w-6 text-emerald-600" />,
      title: 'Modular Architecture',
      description: 'Zero bloat. Toggle only the specific modules you need. Every module operates independently without crashing other components.',
    },
    {
      icon: <ShieldCheck className="h-6 w-6 text-emerald-600" />,
      title: 'Hardened Security by Default',
      description: 'Strict nonces, capability checks, SSRF feed filtering, CSV formula defense, and XSS script tag escaping built into every layer.',
    },
    {
      icon: <RefreshCw className="h-6 w-6 text-emerald-600" />,
      title: 'Conflict-Free Compatibility',
      description: 'Gracefully yields frontend meta when prominent third-party SEO or security plugins are detected to eliminate tag duplication.',
    },
    {
      icon: <Zap className="h-6 w-6 text-emerald-600" />,
      title: 'Lightweight & High-Speed',
      description: 'Strictly avoids loading unnecessary frontend styles or scripts. Clean WordPress core API usage over bloated external wrappers.',
    },
  ];

  return (
    <div className="space-y-20 pb-20">
      {/* 1. Hero Section */}
      <section className="relative overflow-hidden bg-gradient-to-b from-emerald-50/60 via-slate-50 to-white pt-16 pb-20 md:pt-24 md:pb-28 border-b border-slate-200/60">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center space-y-6">
          <div className="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800 shadow-sm">
            <span className="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse" />
            <span>Earthin WP Suite v0.2.0 Available</span>
          </div>

          <h1 className="text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl md:text-6xl max-w-4xl mx-auto leading-tight">
            The Official Home for{' '}
            <span className="text-emerald-600">Earthin WordPress Plugins</span>
          </h1>

          <p className="mx-auto max-w-2xl text-lg sm:text-xl text-slate-600 leading-relaxed">
            Engineered for developers, publishers, and site owners who demand modularity, verifiable security, and zero bloated code.
          </p>

          <div className="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
            <Link href="/plugins">
              <Button size="lg" variant="primary" className="w-full sm:w-auto shadow-md">
                Browse Plugins
                <ArrowRight className="ml-2 h-4 w-4" />
              </Button>
            </Link>
            <Link href="/themes">
              <Button size="lg" variant="outline" className="w-full sm:w-auto border-emerald-600 text-emerald-800 bg-emerald-50/50 hover:bg-emerald-100/50">
                <LayoutTemplate className="mr-2 h-4 w-4 text-emerald-600" />
                Explore Themes (4)
              </Button>
            </Link>
            <Link href="/docs">
              <Button size="lg" variant="ghost" className="w-full sm:w-auto">
                <BookOpen className="mr-2 h-4 w-4" />
                Documentation
              </Button>
            </Link>
          </div>

          <div className="pt-8 flex flex-wrap items-center justify-center gap-8 text-xs text-slate-500 font-medium">
            <span className="flex items-center gap-1.5">
              <CheckCircle className="h-4 w-4 text-emerald-600" />
              100% Free Core Releases
            </span>
            <span className="flex items-center gap-1.5">
              <CheckCircle className="h-4 w-4 text-emerald-600" />
              Strict WordPress Standards
            </span>
            <span className="flex items-center gap-1.5">
              <CheckCircle className="h-4 w-4 text-emerald-600" />
              GPL-2.0+ Open Source
            </span>
          </div>
        </div>
      </section>

      {/* 2. Featured Plugins Section */}
      <section className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
          <div>
            <Badge variant="emerald" className="mb-2">CATALOG SPOTLIGHT</Badge>
            <h2 className="text-3xl font-bold tracking-tight text-slate-900">Featured Plugins</h2>
            <p className="text-slate-600 mt-1">Discover thoroughly tested and actively supported WordPress tools.</p>
          </div>
          <Link href="/plugins" className="text-sm font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
            View all plugins <ArrowRight className="h-4 w-4" />
          </Link>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {featured.map((plugin) => (
            <PluginCard key={plugin.id} plugin={plugin} />
          ))}
        </div>
      </section>

      {/* 2.5 Official Earthin WordPress Themes Section */}
      <section className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
          <div>
            <div className="inline-flex items-center gap-1.5 mb-2">
              <Badge variant="emerald">THEMES SUITE</Badge>
              <span className="text-xs font-semibold text-slate-500 font-mono">4 Standalone Themes</span>
            </div>
            <h2 className="text-3xl font-bold tracking-tight text-slate-900">Official WordPress Themes</h2>
            <p className="text-slate-600 mt-1">Lightweight, modern themes engineered with zero-bloat, clean semantic CSS, and instant UPI checkout readiness.</p>
          </div>
          <Link href="/themes" className="text-sm font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
            Explore all themes & templates <ArrowRight className="h-4 w-4" />
          </Link>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {themes.map((t) => (
            <Card key={t.id} className="flex flex-col border-slate-200 hover:border-emerald-300 transition-all hover:shadow-md bg-white overflow-hidden group">
              <div className={`p-4 text-white ${
                t.accentColor === 'emerald' ? 'bg-gradient-to-br from-emerald-800 to-slate-900' :
                t.accentColor === 'teal' ? 'bg-gradient-to-br from-teal-800 to-slate-900' :
                t.accentColor === 'blue' ? 'bg-gradient-to-br from-blue-800 to-slate-900' :
                'bg-gradient-to-br from-purple-900 to-slate-900'
              }`}>
                <span className="text-[10px] font-bold uppercase tracking-wider bg-white/20 backdrop-blur-sm px-2 py-0.5 rounded text-white">
                  {t.badge}
                </span>
                <h3 className="text-lg font-bold text-white mt-2 group-hover:text-emerald-300 transition-colors">
                  {t.name}
                </h3>
                <span className="text-xs text-slate-300 block">{t.category}</span>
              </div>

              <CardContent className="p-4 flex-1 flex flex-col justify-between space-y-4">
                <p className="text-xs text-slate-600 leading-relaxed line-clamp-3">
                  {t.tagline}
                </p>

                <div className="space-y-3 pt-2 border-t border-slate-100">
                  <div className="flex items-center justify-between text-xs text-slate-500 font-medium">
                    <span>{t.templatesCount} Templates</span>
                    <span className="font-mono text-[11px]">PHP {t.phpRequires}</span>
                  </div>

                  <div className="flex items-center justify-between gap-2">
                    <Link href="/themes" className="text-xs font-semibold text-emerald-700 hover:underline flex items-center gap-1">
                      Details &rarr;
                    </Link>
                    <div className="flex items-center gap-1.5">
                      <a
                        href={t.downloadUrl}
                        download={`${t.slug}.zip`}
                        className="text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-2 py-1 rounded transition-colors flex items-center gap-1"
                        title={`Download ${t.slug}.zip`}
                      >
                        <Download className="h-3 w-3" />
                        ZIP
                      </a>
                      <a
                        href={t.githubUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-xs text-slate-500 hover:text-slate-800 transition-colors p-1"
                        title="Inspect source code"
                      >
                        <ExternalLink className="h-3.5 w-3.5" />
                      </a>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      </section>

      {/* 3. Key Benefits Section */}
      <section className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="text-center max-w-2xl mx-auto mb-12">
          <Badge variant="slate" className="mb-2">CORE PRINCIPLES</Badge>
          <h2 className="text-3xl font-bold tracking-tight text-slate-900">Why Choose Earthin Plugins?</h2>
          <p className="text-slate-600 mt-2">
            Built from first principles to give WordPress site managers complete control without the fragile dependencies of conventional plugin bundles.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
          {benefits.map((b, idx) => (
            <div key={idx} className="flex gap-4 p-6 rounded-xl border border-slate-200/80 bg-white shadow-sm">
              <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-emerald-50 border border-emerald-100">
                {b.icon}
              </div>
              <div className="space-y-1">
                <h3 className="text-lg font-bold text-slate-900">{b.title}</h3>
                <p className="text-sm text-slate-600 leading-relaxed">{b.description}</p>
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* 4. Freemium Business Model Explanation */}
      <section className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white p-8 md:p-12 shadow-xl border border-slate-700">
          <div className="max-w-3xl space-y-4">
            <Badge variant="amber" className="bg-amber-400/20 text-amber-300 border-amber-400/30">
              TRANSPARENT FREEMIUM PHILOSOPHY
            </Badge>
            <h2 className="text-3xl font-bold tracking-tight text-white">
              Free Forever Core. Advanced Pro Tools for Teams.
            </h2>
            <p className="text-slate-300 leading-relaxed">
              Every Earthin plugin provides a fully functional, production-ready free version with no forced watermarks, artificial post caps, or nag screens. Premium enhancements cater to power publishers, agency workflows, and automated multi-site orchestration.
            </p>
            <div className="pt-4 flex flex-wrap gap-4">
              <Link href="/pricing">
                <Button variant="primary" size="md">
                  Explore Licensing Options
                </Button>
              </Link>
              <Link href="/plugins/earthin-wp-suite">
                <Button variant="secondary" size="md" className="border border-slate-600">
                  Inspect WP Suite Details
                </Button>
              </Link>
            </div>
          </div>
        </div>
      </section>

      {/* 5. Latest Release Snapshot & Changelog */}
      <section className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between mb-8">
          <div>
            <Badge variant="slate" className="mb-2">RELEASE ACTIVITY</Badge>
            <h2 className="text-2xl font-bold text-slate-900">Latest Release Highlights</h2>
          </div>
          <Link href="/changelog" className="text-sm font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
            Complete Changelog <ArrowRight className="h-4 w-4" />
          </Link>
        </div>

        <Card className="border-emerald-200/80 bg-white">
          <CardHeader>
            <div className="flex flex-wrap items-center justify-between gap-2">
              <div className="flex items-center gap-3">
                <CardTitle className="text-xl">Earthin WP Suite</CardTitle>
                <Badge variant="emerald">v0.2.0 Stable</Badge>
              </div>
              <span className="text-xs text-slate-500 font-mono">Released: 2026-11-09</span>
            </div>
            <CardDescription>
              Major hardening and interface release establishing the tabbed management dashboard, SSRF feed protection, and exact-path redirect runtime.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <ul className="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-slate-700 mb-4">
              <li className="flex items-center gap-2">
                <CheckCircle className="h-4 w-4 text-emerald-600 shrink-0" />
                <span>Tabbed admin dashboard with real-time status diagnostics</span>
              </li>
              <li className="flex items-center gap-2">
                <CheckCircle className="h-4 w-4 text-emerald-600 shrink-0" />
                <span>Redirect engine with 404 recovery and hit tracking</span>
              </li>
              <li className="flex items-center gap-2">
                <CheckCircle className="h-4 w-4 text-emerald-600 shrink-0" />
                <span>Form inquiries viewer with formula-safe CSV export</span>
              </li>
              <li className="flex items-center gap-2">
                <CheckCircle className="h-4 w-4 text-emerald-600 shrink-0" />
                <span>SSRF feed protection blocking private and loopback networks</span>
              </li>
            </ul>
            <div className="pt-4 border-t border-slate-100 flex items-center justify-between">
              <Link href="/plugins/earthin-wp-suite">
                <Button size="sm" variant="outline">Learn More</Button>
              </Link>
              <Link href="/changelog">
                <span className="text-xs text-slate-500 hover:text-slate-800 transition-colors">Read all notes &rarr;</span>
              </Link>
            </div>
          </CardContent>
        </Card>
      </section>
    </div>
  );
}
