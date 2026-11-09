'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { Check, ShieldCheck, QrCode, CreditCard, Sparkles, Smartphone } from 'lucide-react';
import { Button } from '@/components/ui/Button';
import { Badge } from '@/components/ui/Badge';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/Card';
import { db } from '@/lib/db';

export default function PricingPage() {
  const [selectedPlan, setSelectedPlan] = useState<string | null>(null);
  const [utrNumber, setUtrNumber] = useState('');
  const [submitted, setSubmitted] = useState(false);
  const paymentSettings = db.getPaymentSettings();

  const plans = [
    {
      id: 'free',
      name: 'Free Core',
      badge: 'POPULAR & FREE',
      badgeVariant: 'emerald' as const,
      priceUSD: '$0',
      priceINR: '₹0',
      inrAmount: 0,
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
      inrAmount: 3999,
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
      inrAmount: 11999,
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

  const selectedPlanData = plans.find((p) => p.id === selectedPlan);
  const planAmount = selectedPlanData ? selectedPlanData.inrAmount : 3999;
  const planName = selectedPlanData ? selectedPlanData.name : 'Earthin License';

  const upiUri = `upi://pay?pa=${encodeURIComponent(paymentSettings.upiId)}&pn=${encodeURIComponent(paymentSettings.upiDisplayName)}&am=${planAmount}&cu=INR&tn=${encodeURIComponent(`${planName} Order`)}`;
  const dynamicQrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=${encodeURIComponent(upiUri)}`;

  const handleSubmitUtr = (e: React.FormEvent) => {
    e.preventDefault();
    if (utrNumber.trim().length >= 8) {
      db.createOrder('customer@earthin.online', selectedPlan || 'pro-single', planAmount);
      setSubmitted(true);
      setTimeout(() => {
        setSelectedPlan(null);
        setSubmitted(false);
        setUtrNumber('');
        alert('Thank you! Your payment verification request has been queued. You can view your license status in your Account portal.');
      }, 1500);
    }
  };

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

      {/* Dynamic UPI Payment Modal */}
      {selectedPlan && (
        <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl max-w-lg w-full p-8 shadow-2xl border border-slate-200 space-y-6">
            <div className="flex justify-between items-start">
              <div>
                <Badge variant="indigo">DYNAMIC PRICE UPI QR</Badge>
                <h3 className="text-xl font-bold text-slate-900 mt-2">
                  Order: {selectedPlanData?.name} ({selectedPlanData?.priceUSD} / {selectedPlanData?.priceINR})
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

            {/* Dynamic Scannable Price QR */}
            <div className="bg-slate-50 p-6 rounded-2xl border border-slate-200 text-center space-y-3">
              <div className="inline-block p-4 bg-white rounded-xl shadow-sm border border-slate-200">
                <img
                  src={dynamicQrCodeUrl}
                  alt={`UPI QR Code for ${planAmount} INR`}
                  className="h-48 w-48 mx-auto object-contain"
                />
              </div>

              <div>
                <div className="text-xs text-slate-500 uppercase font-semibold">Exact Amount Encoded in QR</div>
                <div className="text-3xl font-black text-emerald-700">₹{planAmount.toLocaleString('en-IN')}</div>
                <div className="text-xs font-mono font-bold text-slate-700 mt-1 bg-white px-3 py-1 rounded-md inline-block border border-slate-200">
                  {paymentSettings.upiId}
                </div>
              </div>

              <p className="text-xs text-slate-500">
                Scan with Google Pay, PhonePe, Paytm, or BHIM. The amount ₹{planAmount} is already encoded!
              </p>

              <a href={upiUri} className="block mt-2">
                <Button size="sm" variant="outline" className="w-full text-xs flex items-center justify-center gap-1.5">
                  <Smartphone className="h-3.5 w-3.5" />
                  Open in UPI App on Mobile
                </Button>
              </a>
            </div>

            {/* UTR Reference Form */}
            <form onSubmit={handleSubmitUtr} className="space-y-3">
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                Submit Your 12-Digit UTR / Transaction Reference
              </label>
              <input
                type="text"
                required
                placeholder="e.g. 431289123456"
                value={utrNumber}
                onChange={(e) => setUtrNumber(e.target.value)}
                className="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono"
              />
              <p className="text-[11px] text-slate-500">
                Once submitted, your order is verified and your license key will be automatically issued in your <Link href="/account" className="text-emerald-600 underline font-semibold">Account Portal</Link>.
              </p>

              <div className="flex gap-3 pt-2">
                <Button type="button" variant="outline" className="flex-1" onClick={() => setSelectedPlan(null)}>
                  Cancel
                </Button>
                <Button type="submit" variant="primary" className="flex-1">
                  {submitted ? 'Submitted!' : 'Submit Verification'}
                </Button>
              </div>
            </form>
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
