'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { Check, ShieldCheck, QrCode, CreditCard, Sparkles } from 'lucide-react';
import { Button } from '@/components/ui/Button';
import { Badge } from '@/components/ui/Badge';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/Card';

export default function PricingPage() {
  const [selectedPlan, setSelectedPlan] = useState<string | null>(null);

  const plans = [
    {
      id: 'free',
      name: 'Free Core',
      badge: 'POPULAR & FREE',
      badgeVariant: 'emerald' as const,
      priceUSD: '$0',
      priceINR: '₹0',
      period: 'forever',
      description: 'Production-ready essential WordPress tools with zero cost and no artificial limits.',
      features: [
        'Complete SEO meta, document title & canonical tags',
        'Open Graph and Twitter/X card social sharing previews',
        'Exact-path redirect manager with 404 recovery',
        'Safe contact form with honeypot anti-spam',
        'Image Optimizer with local GD compression',
        'Database Cleaner with preview inventory',
        'Visitor Counter with privacy-first salted hashing',
        'GPL-2.0+ open source license',
      ],
      ctaText: 'Browse Free Plugins',
      ctaHref: '/plugins',
      ctaVariant: 'outline' as const,
    },
    {
      id: 'pro',
      name: 'Pro Single Site',
      badge: 'RECOMMENDED',
      badgeVariant: 'indigo' as const,
      priceUSD: '$49',
      priceINR: '₹3,999',
      period: '/ site / year',
      description: 'Power automation, visual design blocks, and premium compliance for business websites.',
      features: [
        'Everything in Free Core',
        'Earthin Page Builder Pro with pricing & testimonials',
        'Earthin Cookie Consent Pro with GDPR audit trails',
        'Automated scheduled RSS syncing & background queue',
        'Regex pattern redirects & 404 monitoring',
        'VAPID Web Push notifications campaign engine',
        'Automated one-click plugin updates',
        'Standard direct email support',
      ],
      ctaText: 'Buy Pro License',
      ctaVariant: 'primary' as const,
    },
    {
      id: 'agency',
      name: 'Agency Multi-Site',
      badge: 'BEST VALUE',
      badgeVariant: 'amber' as const,
      priceUSD: '$149',
      priceINR: '₹11,999',
      period: '/ unlimited sites',
      description: 'Centralized licensing, multi-site network controls, and priority engineering support.',
      features: [
        'Everything in Pro Single Site',
        'Unlimited site activations & client handoff',
        'Earthin White Label administrative branding',
        'Centralized license key management dashboard',
        'Domain activation & deactivation control',
        'Priority feature requests & engineering escalation',
        'Direct developer Slack / WhatsApp support',
      ],
      ctaText: 'Get Agency Access',
      ctaVariant: 'secondary' as const,
    },
  ];

  return (
    <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 space-y-16">
      {/* Header */}
      <div className="text-center max-w-3xl mx-auto space-y-4">
        <Badge variant="emerald">OFFICIAL EARTHIN PRICING</Badge>
        <h1 className="text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl">
          Transparent Pricing for Every WordPress Project
        </h1>
        <p className="text-lg text-slate-600">
          Every Earthin plugin offers an independent, production-grade free version. Upgrade to Pro or Agency for advanced modules, automated workflows, and direct developer support.
        </p>
      </div>

      {/* Pricing Cards Grid */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">
        {plans.map((plan) => (
          <Card key={plan.id} className="flex flex-col justify-between h-full bg-white border-slate-200 hover:shadow-lg transition-shadow">
            <div>
              <CardHeader>
                <div className="flex items-center justify-between mb-2">
                  <Badge variant={plan.badgeVariant}>{plan.badge}</Badge>
                </div>
                <CardTitle className="text-2xl">{plan.name}</CardTitle>
                <CardDescription className="text-xs mt-1">{plan.description}</CardDescription>
                <div className="mt-4 pt-4 border-t border-slate-100 flex items-baseline gap-2">
                  <span className="text-4xl font-extrabold text-slate-900">{plan.priceUSD}</span>
                  <span className="text-sm font-semibold text-slate-500">({plan.priceINR})</span>
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
              {plan.ctaHref ? (
                <Link href={plan.ctaHref} className="w-full">
                  <Button variant={plan.ctaVariant} size="md" className="w-full">
                    {plan.ctaText}
                  </Button>
                </Link>
              ) : (
                <Button
                  variant={plan.ctaVariant}
                  size="md"
                  className="w-full"
                  onClick={() => setSelectedPlan(plan.id)}
                >
                  {plan.ctaText}
                </Button>
              )}
            </div>
          </Card>
        ))}
      </div>

      {/* UPI Payment Modal / Details */}
      {selectedPlan && (
        <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl max-w-lg w-full p-8 shadow-2xl border border-slate-200 space-y-6">
            <div className="flex justify-between items-start">
              <div>
                <Badge variant="indigo">INSTANT UPI CHECKOUT</Badge>
                <h3 className="text-xl font-bold text-slate-900 mt-2">
                  Order: {selectedPlan === 'pro' ? 'Earthin Pro Single Site ($49 / ₹3,999)' : 'Earthin Agency Multi-Site ($149 / ₹11,999)'}
                </h3>
              </div>
              <button
                type="button"
                onClick={() => setSelectedPlan(null)}
                className="text-slate-400 hover:text-slate-600 font-bold text-lg"
              >
                ✕
              </button>
            </div>

            <div className="bg-slate-50 p-4 rounded-xl border border-slate-200 text-center space-y-3">
              <div className="inline-block p-3 bg-white rounded-lg shadow-sm border border-slate-200">
                <QrCode className="h-28 w-28 text-slate-800 mx-auto" />
              </div>
              <div>
                <div className="text-xs text-slate-500 font-medium">Official Earthin UPI VPA</div>
                <div className="text-sm font-mono font-bold text-emerald-600 bg-emerald-50 px-3 py-1 rounded-md inline-block mt-1">
                  pay@earthin.online
                </div>
              </div>
              <p className="text-xs text-slate-600">
                Scan using Google Pay, PhonePe, Paytm, or any BHIM UPI application.
              </p>
            </div>

            <div className="space-y-3">
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                Submit Your 12-Digit UTR / Transaction Reference
              </label>
              <input
                type="text"
                placeholder="e.g. 431289123456"
                className="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono"
              />
              <p className="text-[11px] text-slate-500">
                Once submitted, your order is verified and your license key will be automatically issued in your <Link href="/account" className="text-emerald-600 underline font-semibold">Account Portal</Link>.
              </p>
            </div>

            <div className="flex gap-3">
              <Button variant="outline" className="flex-1" onClick={() => setSelectedPlan(null)}>
                Cancel
              </Button>
              <Button
                variant="primary"
                className="flex-1"
                onClick={() => {
                  alert('Thank you! Your payment verification request has been queued. You can view your license status in your Account portal.');
                  setSelectedPlan(null);
                }}
              >
                Submit Verification
              </Button>
            </div>
          </div>
        </div>
      )}

      {/* Transparent Disclaimer */}
      <div className="rounded-xl border border-slate-200 bg-slate-50 p-6 max-w-3xl mx-auto flex items-start gap-4">
        <ShieldCheck className="h-6 w-6 text-emerald-600 shrink-0 mt-0.5" />
        <div className="space-y-1">
          <h4 className="text-sm font-bold text-slate-900">Direct Support by Shaurya Joshi</h4>
          <p className="text-xs text-slate-600 leading-relaxed">
            All Earthin plugins are actively developed by Shaurya Joshi under modern WordPress engineering standards. All purchases include 1 year of automated updates, security patches, and direct developer support.
          </p>
        </div>
      </div>
    </div>
  );
}
