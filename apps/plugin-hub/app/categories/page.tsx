import React from 'react';
import Link from 'next/link';
import { Package, Shield, Zap, Database, ArrowRight, Layout, Share2 } from 'lucide-react';
import { CATEGORIES, PLUGINS } from '@/lib/catalog';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/Card';
import { Badge } from '@/components/ui/Badge';

export const metadata = {
  title: 'Plugin Categories - Earthin Plugin Hub',
  description: 'Explore Earthin WordPress plugins by functional category.',
};

export default function CategoriesPage() {
  const getIconForCategory = (slug: string) => {
    switch (slug) {
      case 'seo':
        return <Zap className="h-6 w-6 text-amber-600" />;
      case 'performance':
        return <Zap className="h-6 w-6 text-indigo-600" />;
      case 'database':
        return <Database className="h-6 w-6 text-emerald-600" />;
      case 'security':
        return <Shield className="h-6 w-6 text-rose-600" />;
      case 'editor':
        return <Layout className="h-6 w-6 text-cyan-600" />;
      case 'social':
        return <Share2 className="h-6 w-6 text-violet-600" />;
      default:
        return <Package className="h-6 w-6 text-slate-600" />;
    }
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div className="text-center max-w-2xl mx-auto mb-12">
        <h1 className="text-3xl font-extrabold text-slate-900 tracking-tight sm:text-4xl">
          Plugin Categories
        </h1>
        <p className="mt-3 text-base text-slate-500">
          Find the right tools for your WordPress stack across our specialized, modular categories.
        </p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {CATEGORIES.filter((c) => c.slug !== 'all').map((cat) => {
          const matchingPlugins = PLUGINS.filter((p) => p.category === cat.slug);
          return (
            <Link key={cat.id} href={`/plugins?category=${cat.slug}`} className="group">
              <Card className="h-full border-slate-200 group-hover:border-slate-300 transition-all flex flex-col justify-between">
                <div>
                  <CardHeader>
                    <div className="flex items-center justify-between mb-2">
                      <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50 border border-slate-100 group-hover:scale-105 transition-transform">
                        {getIconForCategory(cat.slug)}
                      </div>
                      <Badge variant="slate">{matchingPlugins.length} Plugins</Badge>
                    </div>
                    <CardTitle className="text-xl group-hover:text-emerald-700 transition-colors">
                      {cat.name}
                    </CardTitle>
                    <CardDescription className="mt-2 text-sm">
                      {cat.description}
                    </CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="text-xs text-slate-500 space-y-1">
                      {matchingPlugins.slice(0, 3).map((p) => (
                        <div key={p.id} className="truncate font-medium text-slate-700">
                          &bull; {p.name}
                        </div>
                      ))}
                    </div>
                  </CardContent>
                </div>
                <div className="p-6 pt-0">
                  <span className="text-xs font-semibold text-emerald-700 flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                    Explore category <ArrowRight className="h-3.5 w-3.5" />
                  </span>
                </div>
              </Card>
            </Link>
          );
        })}
      </div>
    </div>
  );
}
