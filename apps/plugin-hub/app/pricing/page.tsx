import React from 'react';
import Link from 'next/link';
import { Check, ShieldCheck, HelpCircle } from 'lucide-react';
import { Button } from '@/components/ui/Button';
import { Badge } from '@/components/ui/Badge';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/Card';

export default function PricingPage() {
  const plans = [
    {
      name: 'Free Core',
      badge: 'CURRENTLY AVAILABLE',
      badgeVariant: 'emerald' as const,
      price: '$0',
      period: 'forever',
      description: 'Production-ready essential WordPress tools with zero cost and no artificial limits.',
      features: [
        'Complete SEO meta, document title & canonical tags',
        'Open Graph and Twitter/X card social sharing previews',
        'Exact-path redirect manager with 404 broken URL recovery',
        'Safe contact form with honeypot anti-spam & DB storage',
        'Manual RSS draft importer with strict SSRF defense',
        'JSON-LD structured data engine with script hex-escaping',
        'Native XML sitemap integration',
        'GPL-2.0+ open source license',
        'Community support & documentation',
      ],
      ctaText: 'Download Free Release',
      ctaHref: '/plugins/earthin-wp-suite',
      ctaVariant: 'primary' as const,
    },
    {
      name: 'Pro Single Site',
      badge: 'ROADMAP MILESTONE',
      badgeVariant: 'indigo' as const,
      price: 'Planned',
      period: '/ site / year',
      description: 'Power automation, AI assistance, and automated backups for high-traffic publishers.',
      features: [
        'Everything in Free Core',
        'Automated cron-scheduled RSS feed ingestion',
        'AI newsroom assistant with outline generation',
        'Regex pattern redirects & 404 log monitoring',
        'Visual form builder with automated email webhooks',
        'Automated database & full-site backup snapshots',
        'One-click automated plugin updates via Plugin Hub',
        'Direct email support',
      ],
      ctaText: 'Coming in Milestone 6',
      ctaHref: '/docs',
      ctaVariant: 'outline' as const,
      disabled: true,
    },
    {
      name: 'Agency Multi-Site',
      badge: 'ROADMAP MILESTONE',
      badgeVariant: 'amber' as const,
      price: 'Planned',
      period: '/ up to 25 sites',
      description: 'Centralized licensing, multi-site network controls, and priority engineering support.',
      features: [
        'Everything in Pro Single Site',
        'Multi-site network administration support',
        'Centralized license key management dashboard',
        'Domain activation & deactivation control',
        'White-label administrative branding options',
        'Priority feature requests & escalation queue',
      ],
      ctaText: 'Coming in Milestone 6',
      ctaHref: '/docs',
      ctaVariant: 'outline' as const,
      disabled: true,
    },
  ];

  return (
    <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 space-y-16">
      {/* Header */}
      <div className="text-center max-w-3xl mx-auto space-y-4">
        <Badge variant="emerald">TRANSPARENT LICENSING</Badge>
        <h1 className="text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl">
          Predictable Pricing for Every Stage
        </h1>
        <p className="text-lg text-slate-600">
          Our core plugin functionality is 100% free and open-source. Premium tiers are being designed for automated publishing workflows, AI editorial tooling, and multi-site agency infrastructure.
        </p>
      </div>

      {/* Pricing Cards Grid */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">
        {plans.map((plan) => (
          <Card key={plan.name} className="flex flex-col justify-between h-full bg-white border-slate-200">
            <div>
              <CardHeader>
                <div className="flex items-center justify-between mb-2">
                  <Badge variant={plan.badgeVariant}>{plan.badge}</Badge>
                </div>
                <CardTitle className="text-2xl">{plan.name}</CardTitle>
                <CardDescription className="text-xs mt-1">{plan.description}</CardDescription>
                <div className="mt-4 pt-4 border-t border-slate-100 flex items-baseline gap-1">
                  <span className="text-4xl font-extrabold text-slate-900">{plan.price}</span>
                  <span className="text-xs text-slate-500 font-medium">{plan.period}</span>
                </div>
              </CardHeader>

              <CardContent>
                <ul className="space-y-2.5 text-xs text-slate-600">
                  {plan.features.map((feat, fIdx) => (
                    <li key={fIdx} className="flex items-start gap-2">
                      <Check className="h-4 w-4 text-emerald-600 shrink-0 mt-0.5" />
                      <span>{feat}</span>
                    </li>
                  ))}
                </ul>
              </CardContent>
            </div>

            <div className="p-6 pt-0 mt-6">
              <Link href={plan.ctaHref} className="w-full">
                <Button
                  variant={plan.ctaVariant}
                  size="md"
                  className="w-full"
                  disabled={plan.disabled}
                >
                  {plan.ctaText}
                </Button>
              </Link>
            </div>
          </Card>
        ))}
      </div>

      {/* Transparent Disclaimer */}
      <div className="rounded-xl border border-slate-200 bg-slate-50 p-6 max-w-3xl mx-auto flex items-start gap-4">
        <ShieldCheck className="h-6 w-6 text-emerald-600 shrink-0 mt-0.5" />
        <div className="space-y-1">
          <h4 className="text-sm font-bold text-slate-900">Zero Simulated Transactions</h4>
          <p className="text-xs text-slate-600 leading-relaxed">
            In accordance with our engineering principles, we do not simulate payments or take credit card details. Payment provider integration (Stripe / LemonSqueezy) will be introduced in Milestone 6 alongside automated license generation. All free core versions can be downloaded and used immediately.
          </p>
        </div>
      </div>
    </div>
  );
}
