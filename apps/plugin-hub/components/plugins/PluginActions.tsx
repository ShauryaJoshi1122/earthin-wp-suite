'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { Download, QrCode, ShieldCheck, CheckCircle2, Lock, Smartphone, ExternalLink } from 'lucide-react';
import { Button } from '@/components/ui/Button';
import { Badge } from '@/components/ui/Badge';
import { db, PaymentSettingsRecord } from '@/lib/db';

interface PluginActionsProps {
  slug: string;
  name: string;
  pricingModel: string;
  allowDownload?: boolean;
  inrPrice?: number;
}

export function PluginActions({
  slug,
  name,
  pricingModel,
  allowDownload = true,
  inrPrice = 1499,
}: PluginActionsProps) {
  const [showCheckout, setShowCheckout] = useState(false);
  const [utrNumber, setUtrNumber] = useState('');
  const [submitted, setSubmitted] = useState(false);
  const paymentSettings = db.getPaymentSettings();

  const price = inrPrice > 0 ? inrPrice : 1499;
  const upiUri = `upi://pay?pa=${encodeURIComponent(paymentSettings.upiId)}&pn=${encodeURIComponent(paymentSettings.upiDisplayName)}&am=${price}&cu=INR&tn=${encodeURIComponent(`${name} License`)}`;
  const dynamicQrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=${encodeURIComponent(upiUri)}`;

  const handleSubmitUtr = (e: React.FormEvent) => {
    e.preventDefault();
    if (utrNumber.trim().length >= 8) {
      db.createOrder('customer@earthin.online', slug, price);
      setSubmitted(true);
      setTimeout(() => {
        setShowCheckout(false);
        setSubmitted(false);
        setUtrNumber('');
        alert('Thank you! Your UTR has been submitted for verification. Check your Account portal for the license key.');
      }, 1500);
    }
  };

  return (
    <div className="space-y-4 pt-2">
      <div className="flex flex-wrap items-center gap-3">
        {/* Direct Download Button (Admin Allowed Check) */}
        {allowDownload ? (
          <a
            href={`/api/download/${slug}`}
            download
            className="inline-block"
          >
            <Button size="md" variant="primary" className="flex items-center gap-2">
              <Download className="h-4 w-4" />
              Download Plugin (.zip)
            </Button>
          </a>
        ) : (
          <div className="flex items-center gap-2 px-4 py-2.5 bg-slate-100 rounded-lg text-xs font-semibold text-slate-500 border border-slate-200">
            <Lock className="h-4 w-4 text-slate-400" />
            Downloads Temporarily Restricted by Admin
          </div>
        )}

        {/* UPI Instant QR Checkout for Pro or Direct License */}
        {pricingModel !== 'free' && (
          <Button
            size="md"
            variant="secondary"
            onClick={() => setShowCheckout(true)}
            className="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white"
          >
            <QrCode className="h-4 w-4" />
            Instant UPI QR (₹{price.toLocaleString('en-IN')})
          </Button>
        )}

        <a
          href="https://github.com/ShauryaJoshi1122/earthin-wp-suite"
          target="_blank"
          rel="noopener noreferrer"
          className="inline-block"
        >
          <Button size="md" variant="outline">
            <ExternalLink className="mr-1.5 h-4 w-4" />
            GitHub
          </Button>
        </a>
      </div>

      {/* Dynamic Price UPI Modal */}
      {showCheckout && (
        <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-5">
            <div className="flex justify-between items-start">
              <div>
                <Badge variant="indigo">DYNAMIC PRICE UPI QR</Badge>
                <h3 className="text-lg font-bold text-slate-900 mt-1">
                  Pay for {name}
                </h3>
              </div>
              <button
                type="button"
                onClick={() => setShowCheckout(false)}
                className="text-slate-400 hover:text-slate-600 font-bold text-lg"
              >
                ✕
              </button>
            </div>

            {/* Dynamic Scannable QR Code */}
            <div className="bg-slate-50 p-5 rounded-2xl border border-slate-200 text-center space-y-3">
              <div className="inline-block p-3 bg-white rounded-xl shadow-sm border border-slate-200">
                <img
                  src={dynamicQrCodeUrl}
                  alt={`UPI QR Code for ${name} - ₹${price}`}
                  className="h-48 w-48 mx-auto object-contain"
                />
              </div>

              <div>
                <div className="text-xs text-slate-500 uppercase font-semibold">Exact Amount Encoded</div>
                <div className="text-2xl font-black text-emerald-700">₹{price.toLocaleString('en-IN')}</div>
                <div className="text-xs font-mono font-bold text-slate-700 mt-1 bg-white px-2 py-1 rounded inline-block border border-slate-200">
                  {paymentSettings.upiId}
                </div>
              </div>

              <p className="text-xs text-slate-500">
                Scan with GPay, PhonePe, Paytm, or BHIM. The amount is already filled in!
              </p>

              <a href={upiUri} className="block mt-2">
                <Button size="sm" variant="outline" className="w-full text-xs flex items-center justify-center gap-1.5">
                  <Smartphone className="h-3.5 w-3.5" />
                  Open in UPI App on Mobile
                </Button>
              </a>
            </div>

            {/* UTR Verification Form */}
            <form onSubmit={handleSubmitUtr} className="space-y-3">
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                Submit 12-Digit UTR / Transaction Ref
              </label>
              <input
                type="text"
                required
                placeholder="e.g. 431289123456"
                value={utrNumber}
                onChange={(e) => setUtrNumber(e.target.value)}
                className="w-full px-3.5 py-2 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono"
              />

              <div className="flex gap-2 pt-2">
                <Button type="button" variant="outline" className="flex-1" onClick={() => setShowCheckout(false)}>
                  Cancel
                </Button>
                <Button type="submit" variant="primary" className="flex-1">
                  {submitted ? 'Submitted!' : 'Verify & Get License'}
                </Button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
