import React from 'react';
import Link from 'next/link';
import { Package, ArrowRight, CheckCircle2 } from 'lucide-react';
import { PluginItem } from '@/lib/types';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/Card';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';

export interface PluginCardProps {
  plugin: PluginItem;
}

export function PluginCard({ plugin }: PluginCardProps) {
  const badgeVariant = plugin.pricingModel === 'free' ? 'emerald' : 'indigo';

  return (
    <Card className="flex flex-col justify-between h-full border-slate-200 hover:border-slate-300">
      <div>
        <CardHeader>
          <div className="flex items-start justify-between gap-3 mb-2">
            <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100/80">
              <Package className="h-6 w-6" />
            </div>
            <div className="flex items-center gap-1.5">
              <Badge variant={badgeVariant}>
                {plugin.pricingModel.toUpperCase()}
              </Badge>
              <Badge variant="slate">v{plugin.latestVersion}</Badge>
            </div>
          </div>
          <CardTitle className="text-lg">
            <Link href={`/plugins/${plugin.slug}`} className="hover:text-emerald-700 transition-colors">
              {plugin.name}
            </Link>
          </CardTitle>
          <CardDescription className="line-clamp-2 mt-1">
            {plugin.tagline}
          </CardDescription>
        </CardHeader>

        <CardContent>
          <div className="space-y-2 mb-4">
            {plugin.features.slice(0, 3).map((feat, idx) => (
              <div key={idx} className="flex items-center text-xs text-slate-600 gap-1.5">
                <CheckCircle2 className="h-3.5 w-3.5 text-emerald-600 shrink-0" />
                <span className="truncate">{feat}</span>
              </div>
            ))}
          </div>

          <div className="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <span className="font-medium text-slate-600">By {plugin.author || 'Shaurya Joshi'}</span>
            <span>PHP {plugin.minPhpVersion}+ &bull; WP {plugin.minWpVersion}+</span>
          </div>
        </CardContent>
      </div>

      <div className="p-6 pt-0 mt-4">
        <Link href={`/plugins/${plugin.slug}`} className="w-full">
          <Button variant="outline" size="sm" className="w-full justify-between group">
            <span>View Details</span>
            <ArrowRight className="h-3.5 w-3.5 group-hover:translate-x-0.5 transition-transform" />
          </Button>
        </Link>
      </div>
    </Card>
  );
}
