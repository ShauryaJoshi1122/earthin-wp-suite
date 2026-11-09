'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { 
  Key, 
  Download, 
  Package, 
  Copy, 
  Check, 
  CreditCard, 
  LifeBuoy, 
  ArrowRight,
  ShieldCheck,
  ExternalLink
} from 'lucide-react';
import { db, LicenseRecord } from '@/lib/db';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/Card';

export default function CustomerAccountPage() {
  const [copiedKey, setCopiedKey] = useState<string | null>(null);
  const customerEmail = 'developer@example.com';
  const licenses = db.getLicensesByEmail(customerEmail);
  const orders = db.getOrders().filter((o) => o.customerEmail.toLowerCase() === customerEmail.toLowerCase());

  const handleCopy = (key: string) => {
    navigator.clipboard.writeText(key);
    setCopiedKey(key);
    setTimeout(() => setCopiedKey(null), 2500);
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      {/* Account Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-8 border-b border-slate-200">
        <div>
          <span className="text-xs font-semibold uppercase tracking-wider text-emerald-800">Customer Dashboard</span>
          <h1 className="text-3xl font-extrabold text-slate-900 mt-1">My Earthin Account</h1>
          <p className="text-sm text-slate-500 mt-1">Manage your active plugin licenses, authorized domains, and verified download releases.</p>
        </div>
        <div className="flex items-center gap-2">
          <Badge variant="emerald">Verified Customer</Badge>
          <span className="text-xs font-mono text-slate-500">{customerEmail}</span>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
        {/* Main Content: Licenses & Downloads */}
        <div className="lg:col-span-2 space-y-6">
          <Card className="border-slate-200">
            <CardHeader>
              <div className="flex items-center justify-between">
                <div>
                  <CardTitle className="text-base">Active Licenses ({licenses.length})</CardTitle>
                  <CardDescription>Your registered product keys for automated WordPress plugin updates.</CardDescription>
                </div>
                <Key className="h-5 w-5 text-emerald-700" />
              </div>
            </CardHeader>
            <CardContent>
              {licenses.length === 0 ? (
                <div className="text-center py-8 text-slate-500 text-sm">
                  No active licenses found. Browse the{' '}
                  <Link href="/plugins" className="text-emerald-700 underline font-medium">
                    Plugin Directory
                  </Link>{' '}
                  to download free plugins or upgrade to Pro.
                </div>
              ) : (
                <div className="space-y-4">
                  {licenses.map((lic) => (
                    <div
                      key={lic.id}
                      className="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition-colors"
                    >
                      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                          <div className="flex items-center gap-2">
                            <h3 className="font-bold text-slate-900">{lic.pluginName}</h3>
                            <Badge variant="emerald">{lic.status}</Badge>
                            <Badge variant="slate">{lic.planType.toUpperCase()}</Badge>
                          </div>
                          <div className="flex items-center gap-2 mt-2">
                            <code className="text-xs font-mono font-bold bg-white px-2.5 py-1 rounded border border-slate-200 text-slate-800">
                              {lic.licenseKey}
                            </code>
                            <button
                              onClick={() => handleCopy(lic.licenseKey)}
                              className="text-xs text-slate-500 hover:text-emerald-700 flex items-center gap-1 font-medium transition-colors"
                            >
                              {copiedKey === lic.licenseKey ? (
                                <>
                                  <Check className="h-3.5 w-3.5 text-emerald-600" /> Copied!
                                </>
                              ) : (
                                <>
                                  <Copy className="h-3.5 w-3.5" /> Copy
                                </>
                              )}
                            </button>
                          </div>
                        </div>

                        <div className="text-right shrink-0">
                          <span className="text-xs text-slate-500 block">
                            Activations: {lic.activationsCount} / {lic.maxActivations} domains
                          </span>
                          <span className="text-xs text-slate-400 block mt-0.5">
                            Expires: {lic.expiresAt ? new Date(lic.expiresAt).toLocaleDateString() : 'Never (Lifetime)'}
                          </span>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>

          {/* Orders History */}
          <Card className="border-slate-200">
            <CardHeader>
              <CardTitle className="text-base">Order History & Invoices</CardTitle>
              <CardDescription>Verified UPI and manual transactions associated with your account.</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="overflow-x-auto">
                <table className="w-full text-left text-sm text-slate-600">
                  <thead className="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                    <tr>
                      <th className="py-2.5 px-3">Order Number</th>
                      <th className="py-2.5 px-3">Item</th>
                      <th className="py-2.5 px-3">Amount</th>
                      <th className="py-2.5 px-3">Status</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {orders.map((ord) => (
                      <tr key={ord.id}>
                        <td className="py-2.5 px-3 font-mono font-medium text-slate-900">{ord.orderNumber}</td>
                        <td className="py-2.5 px-3 font-medium text-slate-800">{ord.pluginName}</td>
                        <td className="py-2.5 px-3 font-bold text-slate-900">₹{ord.amount}</td>
                        <td className="py-2.5 px-3">
                          <Badge variant={ord.status === 'APPROVED' ? 'emerald' : 'amber'}>
                            {ord.status}
                          </Badge>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Sidebar: Support & Quick Actions */}
        <div className="space-y-6">
          <Card className="border-slate-200 bg-gradient-to-br from-emerald-50/50 to-white">
            <CardHeader>
              <CardTitle className="text-base flex items-center gap-2 text-emerald-950">
                <ShieldCheck className="h-5 w-5 text-emerald-700" />
                Guaranteed Direct Updates
              </CardTitle>
              <CardDescription className="text-emerald-800/80">
                Activate your license key in your WordPress admin panel under <strong>Settings &gt; Earthin Suite &gt; License</strong> to receive one-click background updates.
              </CardDescription>
            </CardHeader>
            <CardContent>
              <Link href="/docs">
                <Button variant="outline" size="sm" className="w-full justify-between">
                  License Setup Guide
                  <ArrowRight className="h-4 w-4" />
                </Button>
              </Link>
            </CardContent>
          </Card>

          <Card className="border-slate-200">
            <CardHeader>
              <CardTitle className="text-base flex items-center gap-2">
                <LifeBuoy className="h-5 w-5 text-indigo-600" />
                Priority Support
              </CardTitle>
              <CardDescription>
                Need help installing or troubleshooting an Earthin plugin? Our engineering team responds within 24 hours.
              </CardDescription>
            </CardHeader>
            <CardContent>
              <Link href="/support">
                <Button variant="primary" size="sm" className="w-full justify-between">
                  Open Support Ticket
                  <ArrowRight className="h-4 w-4" />
                </Button>
              </Link>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
