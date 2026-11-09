'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { 
  Package, 
  CreditCard, 
  Key, 
  Activity, 
  QrCode, 
  CheckCircle2, 
  Clock, 
  Settings, 
  Eye, 
  EyeOff, 
  ArrowUpRight,
  ShieldCheck,
  TrendingUp,
  Download
} from 'lucide-react';
import { db, PaymentSettingsRecord, OrderRecord, LicenseRecord } from '@/lib/db';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/Card';

export default function AdminDashboardPage() {
  const [activeTab, setActiveTab] = useState<'overview' | 'plugins' | 'upi' | 'orders' | 'licenses' | 'logs'>('overview');
  
  // State from DB
  const [plugins, setPlugins] = useState(db.getAllPlugins());
  const [orders, setOrders] = useState(db.getOrders());
  const [licenses, setLicenses] = useState(db.getLicenses());
  const [paymentSettings, setPaymentSettings] = useState<PaymentSettingsRecord>(db.getPaymentSettings());
  const [upiForm, setUpiForm] = useState({
    upiId: paymentSettings.upiId,
    upiDisplayName: paymentSettings.upiDisplayName,
    instructions: paymentSettings.instructions,
  });
  const [saveSuccess, setSaveSuccess] = useState(false);

  // Toggle plugin publish state
  const handleTogglePublish = (slug: string) => {
    db.togglePluginPublished(slug);
    setPlugins([...db.getAllPlugins()]);
  };

  // Save UPI Settings
  const handleSaveUpi = (e: React.FormEvent) => {
    e.preventDefault();
    const updated = db.updatePaymentSettings(upiForm);
    setPaymentSettings(updated);
    setSaveSuccess(true);
    setTimeout(() => setSaveSuccess(false), 3000);
  };

  // Approve Order
  const handleApproveOrder = (orderId: string) => {
    db.approveOrder(orderId);
    setOrders([...db.getOrders()]);
    setLicenses([...db.getLicenses()]);
  };

  const totalRevenue = orders
    .filter((o) => o.status === 'APPROVED')
    .reduce((acc, curr) => acc + curr.amount, 0);

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-8 border-b border-slate-200">
        <div>
          <div className="flex items-center gap-2">
            <span className="h-2 w-2 rounded-full bg-emerald-500 animate-pulse" />
            <span className="text-xs font-semibold uppercase tracking-wider text-emerald-800">Owner Portal</span>
          </div>
          <h1 className="text-3xl font-extrabold text-slate-900 mt-1">Earthin Central Administration</h1>
          <p className="text-sm text-slate-500 mt-1">Manage plugins, UPI & QR payments, customer orders, and software licenses.</p>
        </div>
        <div className="flex items-center gap-3">
          <Link href="/plugins">
            <Button variant="outline" size="sm">
              <Eye className="mr-1.5 h-4 w-4" />
              View Public Hub
            </Button>
          </Link>
          <Badge variant="emerald">Live Production</Badge>
        </div>
      </div>

      {/* Tabs */}
      <div className="flex border-b border-slate-200 mt-6 overflow-x-auto space-x-8">
        <button
          onClick={() => setActiveTab('overview')}
          className={`py-3 text-sm font-semibold border-b-2 flex items-center gap-2 transition-colors whitespace-nowrap ${
            activeTab === 'overview'
              ? 'border-emerald-700 text-emerald-800'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          }`}
        >
          <Activity className="h-4 w-4" />
          Overview & Metrics
        </button>
        <button
          onClick={() => setActiveTab('plugins')}
          className={`py-3 text-sm font-semibold border-b-2 flex items-center gap-2 transition-colors whitespace-nowrap ${
            activeTab === 'plugins'
              ? 'border-emerald-700 text-emerald-800'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          }`}
        >
          <Package className="h-4 w-4" />
          Plugins ({plugins.length})
        </button>
        <button
          onClick={() => setActiveTab('upi')}
          className={`py-3 text-sm font-semibold border-b-2 flex items-center gap-2 transition-colors whitespace-nowrap ${
            activeTab === 'upi'
              ? 'border-emerald-700 text-emerald-800'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          }`}
        >
          <QrCode className="h-4 w-4" />
          QR & UPI Payments
        </button>
        <button
          onClick={() => setActiveTab('orders')}
          className={`py-3 text-sm font-semibold border-b-2 flex items-center gap-2 transition-colors whitespace-nowrap ${
            activeTab === 'orders'
              ? 'border-emerald-700 text-emerald-800'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          }`}
        >
          <CreditCard className="h-4 w-4" />
          Orders ({orders.length})
        </button>
        <button
          onClick={() => setActiveTab('licenses')}
          className={`py-3 text-sm font-semibold border-b-2 flex items-center gap-2 transition-colors whitespace-nowrap ${
            activeTab === 'licenses'
              ? 'border-emerald-700 text-emerald-800'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          }`}
        >
          <Key className="h-4 w-4" />
          Licenses ({licenses.length})
        </button>
      </div>

      {/* Tab: Overview */}
      {activeTab === 'overview' && (
        <div className="space-y-8 mt-6">
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <Card className="border-slate-200">
              <CardContent className="pt-6">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Catalog Plugins</p>
                    <p className="text-3xl font-extrabold text-slate-900 mt-1">{plugins.length}</p>
                  </div>
                  <div className="h-12 w-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                    <Package className="h-6 w-6" />
                  </div>
                </div>
                <p className="text-xs text-slate-500 mt-3 flex items-center gap-1">
                  <CheckCircle2 className="h-3.5 w-3.5 text-emerald-600" />
                  All 11 verified standalone products
                </p>
              </CardContent>
            </Card>

            <Card className="border-slate-200">
              <CardContent className="pt-6">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Orders</p>
                    <p className="text-3xl font-extrabold text-slate-900 mt-1">{orders.length}</p>
                  </div>
                  <div className="h-12 w-12 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center">
                    <CreditCard className="h-6 w-6" />
                  </div>
                </div>
                <p className="text-xs text-slate-500 mt-3">Verified UTR receipts</p>
              </CardContent>
            </Card>

            <Card className="border-slate-200">
              <CardContent className="pt-6">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Licenses</p>
                    <p className="text-3xl font-extrabold text-slate-900 mt-1">{licenses.length}</p>
                  </div>
                  <div className="h-12 w-12 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
                    <Key className="h-6 w-6" />
                  </div>
                </div>
                <p className="text-xs text-slate-500 mt-3">Active entitlements issued</p>
              </CardContent>
            </Card>

            <Card className="border-slate-200">
              <CardContent className="pt-6">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Revenue</p>
                    <p className="text-3xl font-extrabold text-emerald-700 mt-1">₹{totalRevenue.toLocaleString('en-IN')}</p>
                  </div>
                  <div className="h-12 w-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                    <TrendingUp className="h-6 w-6" />
                  </div>
                </div>
                <p className="text-xs text-slate-500 mt-3">Direct UPI settlement</p>
              </CardContent>
            </Card>
          </div>

          {/* Quick Overview Table */}
          <Card className="border-slate-200">
            <CardHeader>
              <CardTitle className="text-base">Recent Customer Orders</CardTitle>
              <CardDescription>Orders submitted via UPI QR with UTR numbers awaiting review or fulfilled.</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="overflow-x-auto">
                <table className="w-full text-left text-sm text-slate-600">
                  <thead className="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                    <tr>
                      <th className="py-3 px-4">Order ID</th>
                      <th className="py-3 px-4">Customer</th>
                      <th className="py-3 px-4">Product</th>
                      <th className="py-3 px-4">Amount</th>
                      <th className="py-3 px-4">UTR Number</th>
                      <th className="py-3 px-4">Status</th>
                      <th className="py-3 px-4 text-right">Action</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {orders.map((ord) => (
                      <tr key={ord.id} className="hover:bg-slate-50/50">
                        <td className="py-3.5 px-4 font-mono font-medium text-slate-900">{ord.orderNumber}</td>
                        <td className="py-3.5 px-4">{ord.customerEmail}</td>
                        <td className="py-3.5 px-4 font-semibold text-slate-800">{ord.pluginName}</td>
                        <td className="py-3.5 px-4 font-bold text-slate-900">₹{ord.amount}</td>
                        <td className="py-3.5 px-4 font-mono text-xs">{ord.utrNumber || 'N/A'}</td>
                        <td className="py-3.5 px-4">
                          <Badge variant={ord.status === 'APPROVED' ? 'emerald' : ord.status === 'SUBMITTED' ? 'amber' : 'slate'}>
                            {ord.status}
                          </Badge>
                        </td>
                        <td className="py-3.5 px-4 text-right">
                          {ord.status === 'SUBMITTED' && (
                            <Button size="sm" variant="primary" onClick={() => handleApproveOrder(ord.id)}>
                              Approve & Grant License
                            </Button>
                          )}
                          {ord.status === 'APPROVED' && (
                            <span className="text-xs text-emerald-700 font-semibold flex items-center justify-end gap-1">
                              <CheckCircle2 className="h-4 w-4" /> Fulfilled
                            </span>
                          )}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </CardContent>
          </Card>
        </div>
      )}

      {/* Tab: Plugins */}
      {activeTab === 'plugins' && (
        <div className="space-y-6 mt-6">
          <div className="flex items-center justify-between">
            <h2 className="text-lg font-bold text-slate-900">Registered WordPress Plugins</h2>
            <p className="text-xs text-slate-500">Changes reflect immediately on the public marketplace.</p>
          </div>

          <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table className="w-full text-left text-sm text-slate-600">
              <thead className="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                <tr>
                  <th className="py-3.5 px-4">Plugin Name</th>
                  <th className="py-3.5 px-4">Category</th>
                  <th className="py-3.5 px-4">Version</th>
                  <th className="py-3.5 px-4">Pricing</th>
                  <th className="py-3.5 px-4">Compatibility</th>
                  <th className="py-3.5 px-4">Status</th>
                  <th className="py-3.5 px-4 text-right">Toggle Visibility</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {plugins.map((plugin) => (
                  <tr key={plugin.id} className="hover:bg-slate-50/50">
                    <td className="py-3.5 px-4">
                      <div className="font-semibold text-slate-900">{plugin.name}</div>
                      <div className="text-xs text-slate-400 font-mono">slug: {plugin.slug}</div>
                    </td>
                    <td className="py-3.5 px-4">
                      <Badge variant="slate">{plugin.category.toUpperCase()}</Badge>
                    </td>
                    <td className="py-3.5 px-4 font-mono font-medium">v{plugin.latestVersion}</td>
                    <td className="py-3.5 px-4 font-semibold text-slate-800">
                      {plugin.pricingModel.toUpperCase()}
                    </td>
                    <td className="py-3.5 px-4 text-xs text-slate-500">
                      PHP {plugin.minPhpVersion}+ &bull; WP {plugin.minWpVersion}+
                    </td>
                    <td className="py-3.5 px-4">
                      <Badge variant={plugin.isPublished ? 'emerald' : 'slate'}>
                        {plugin.isPublished ? 'Published' : 'Hidden'}
                      </Badge>
                    </td>
                    <td className="py-3.5 px-4 text-right">
                      <Button
                        size="sm"
                        variant={plugin.isPublished ? 'outline' : 'secondary'}
                        onClick={() => handleTogglePublish(plugin.slug)}
                      >
                        {plugin.isPublished ? (
                          <>
                            <EyeOff className="mr-1.5 h-3.5 w-3.5" />
                            Unpublish
                          </>
                        ) : (
                          <>
                            <Eye className="mr-1.5 h-3.5 w-3.5" />
                            Publish
                          </>
                        )}
                      </Button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Tab: UPI & QR Code Settings */}
      {activeTab === 'upi' && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-6">
          <Card className="border-slate-200">
            <CardHeader>
              <CardTitle className="text-base">UPI Merchant Configuration</CardTitle>
              <CardDescription>
                Configure the direct UPI payment instructions and receiving VPA address displayed on public checkouts.
              </CardDescription>
            </CardHeader>
            <CardContent>
              {saveSuccess && (
                <div className="mb-4 p-3 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold flex items-center gap-2">
                  <CheckCircle2 className="h-4 w-4 text-emerald-600" />
                  UPI payment settings updated successfully!
                </div>
              )}

              <form onSubmit={handleSaveUpi} className="space-y-4">
                <div>
                  <label className="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    UPI ID / VPA
                  </label>
                  <input
                    type="text"
                    value={upiForm.upiId}
                    onChange={(e) => setUpiForm({ ...upiForm, upiId: e.target.value })}
                    className="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono"
                    required
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    Display Name
                  </label>
                  <input
                    type="text"
                    value={upiForm.upiDisplayName}
                    onChange={(e) => setUpiForm({ ...upiForm, upiDisplayName: e.target.value })}
                    className="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    required
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    Payment Instructions
                  </label>
                  <textarea
                    rows={4}
                    value={upiForm.instructions}
                    onChange={(e) => setUpiForm({ ...upiForm, instructions: e.target.value })}
                    className="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    required
                  />
                </div>

                <Button type="submit" variant="primary">
                  Save Payment Settings
                </Button>
              </form>
            </CardContent>
          </Card>

          <Card className="border-slate-200 flex flex-col justify-between">
            <CardHeader>
              <CardTitle className="text-base">Live Checkout Preview</CardTitle>
              <CardDescription>
                This dynamic QR code and VPA is what customers see during checkout.
              </CardDescription>
            </CardHeader>
            <CardContent className="flex flex-col items-center justify-center p-6 bg-slate-50 rounded-xl border border-dashed border-slate-200">
              <div className="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 text-center">
                <img
                  src={paymentSettings.qrCodeUrl}
                  alt="UPI QR Code"
                  className="h-48 w-48 mx-auto object-contain"
                />
                <div className="mt-3">
                  <div className="text-sm font-bold text-slate-900">{paymentSettings.upiDisplayName}</div>
                  <div className="text-xs font-mono font-semibold text-emerald-700 mt-0.5">{paymentSettings.upiId}</div>
                </div>
              </div>
              <p className="text-xs text-slate-500 text-center mt-4 max-w-sm">
                Customers scan using any UPI App (GPay, PhonePe, Paytm), submit their 12-digit UTR reference, and receive their license key upon review.
              </p>
            </CardContent>
          </Card>
        </div>
      )}

      {/* Tab: Orders */}
      {activeTab === 'orders' && (
        <div className="space-y-6 mt-6">
          <div className="flex items-center justify-between">
            <h2 className="text-lg font-bold text-slate-900">Customer Orders & UTR Verification</h2>
            <span className="text-xs text-slate-500">{orders.length} total orders recorded</span>
          </div>

          <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table className="w-full text-left text-sm text-slate-600">
              <thead className="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                <tr>
                  <th className="py-3.5 px-4">Order ID</th>
                  <th className="py-3.5 px-4">Customer</th>
                  <th className="py-3.5 px-4">Plugin</th>
                  <th className="py-3.5 px-4">Amount</th>
                  <th className="py-3.5 px-4">UTR / Transaction Ref</th>
                  <th className="py-3.5 px-4">Status</th>
                  <th className="py-3.5 px-4 text-right">Action</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {orders.map((ord) => (
                  <tr key={ord.id} className="hover:bg-slate-50/50">
                    <td className="py-3.5 px-4 font-mono font-medium text-slate-900">{ord.orderNumber}</td>
                    <td className="py-3.5 px-4">{ord.customerEmail}</td>
                    <td className="py-3.5 px-4 font-semibold text-slate-800">{ord.pluginName}</td>
                    <td className="py-3.5 px-4 font-bold text-slate-900">₹{ord.amount}</td>
                    <td className="py-3.5 px-4 font-mono text-xs text-slate-700">{ord.utrNumber || 'Awaiting Payment'}</td>
                    <td className="py-3.5 px-4">
                      <Badge variant={ord.status === 'APPROVED' ? 'emerald' : ord.status === 'SUBMITTED' ? 'amber' : 'slate'}>
                        {ord.status}
                      </Badge>
                    </td>
                    <td className="py-3.5 px-4 text-right">
                      {ord.status === 'SUBMITTED' && (
                        <Button size="sm" variant="primary" onClick={() => handleApproveOrder(ord.id)}>
                          Approve & Issue License
                        </Button>
                      )}
                      {ord.status === 'APPROVED' && (
                        <span className="text-xs text-emerald-700 font-semibold flex items-center justify-end gap-1">
                          <CheckCircle2 className="h-4 w-4" /> License Active
                        </span>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Tab: Licenses */}
      {activeTab === 'licenses' && (
        <div className="space-y-6 mt-6">
          <div className="flex items-center justify-between">
            <h2 className="text-lg font-bold text-slate-900">Issued Product Licenses</h2>
            <span className="text-xs text-slate-500">{licenses.length} active licenses</span>
          </div>

          <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table className="w-full text-left text-sm text-slate-600">
              <thead className="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                <tr>
                  <th className="py-3.5 px-4">License Key</th>
                  <th className="py-3.5 px-4">Plugin</th>
                  <th className="py-3.5 px-4">Customer Email</th>
                  <th className="py-3.5 px-4">Plan Type</th>
                  <th className="py-3.5 px-4">Activations</th>
                  <th className="py-3.5 px-4">Status</th>
                  <th className="py-3.5 px-4">Expires At</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {licenses.map((lic) => (
                  <tr key={lic.id} className="hover:bg-slate-50/50">
                    <td className="py-3.5 px-4 font-mono font-bold text-slate-900 text-xs">{lic.licenseKey}</td>
                    <td className="py-3.5 px-4 font-semibold text-slate-800">{lic.pluginName}</td>
                    <td className="py-3.5 px-4">{lic.customerEmail}</td>
                    <td className="py-3.5 px-4 uppercase text-xs font-semibold text-slate-600">{lic.planType}</td>
                    <td className="py-3.5 px-4 text-xs font-mono">{lic.activationsCount} / {lic.maxActivations} sites</td>
                    <td className="py-3.5 px-4">
                      <Badge variant="emerald">{lic.status}</Badge>
                    </td>
                    <td className="py-3.5 px-4 text-xs text-slate-500">
                      {lic.expiresAt ? new Date(lic.expiresAt).toLocaleDateString() : 'Lifetime'}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
}
