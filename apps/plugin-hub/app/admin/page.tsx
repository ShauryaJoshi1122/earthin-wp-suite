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
  Download,
  Copy,
  Check,
  Smartphone,
  ExternalLink,
  ToggleLeft,
  ToggleRight
} from 'lucide-react';
import { db, PaymentSettingsRecord, OrderRecord, LicenseRecord } from '@/lib/db';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/Card';

export default function AdminDashboardPage() {
  const [activeTab, setActiveTab] = useState<'overview' | 'downloads' | 'upi' | 'plugins' | 'orders' | 'licenses'>('downloads');
  
  // State from DB
  const [plugins, setPlugins] = useState(db.getAllPlugins());
  const [orders, setOrders] = useState(db.getOrders());
  const [licenses, setLicenses] = useState(db.getLicenses());
  const [paymentSettings, setPaymentSettings] = useState<PaymentSettingsRecord>(db.getPaymentSettings());
  
  // UPI Form State
  const [upiForm, setUpiForm] = useState({
    upiId: paymentSettings.upiId,
    upiDisplayName: paymentSettings.upiDisplayName,
    defaultPrice: paymentSettings.defaultPrice || 1499,
    instructions: paymentSettings.instructions,
  });

  // Dynamic QR Test Amount State
  const [testAmount, setTestAmount] = useState<number>(paymentSettings.defaultPrice || 1499);
  const [testNote, setTestNote] = useState<string>('Earthin Plugin Hub Purchase');
  const [copiedUpi, setCopiedUpi] = useState(false);
  const [saveSuccess, setSaveSuccess] = useState(false);

  // Download Editing State
  const [editingSlug, setEditingSlug] = useState<string | null>(null);
  const [editPrice, setEditPrice] = useState<number>(0);
  const [editUrl, setEditUrl] = useState<string>('');
  const [editAccess, setEditAccess] = useState<'public' | 'registered' | 'licensed'>('public');

  // Total downloads count
  const totalDownloads = plugins.reduce((acc, curr) => acc + (curr.downloadsCount || 0), 0);

  // Toggle download permission for a plugin
  const handleToggleDownload = (slug: string) => {
    db.togglePluginDownload(slug);
    setPlugins([...db.getAllPlugins()]);
  };

  // Toggle publish state
  const handleTogglePublish = (slug: string) => {
    db.togglePluginPublished(slug);
    setPlugins([...db.getAllPlugins()]);
  };

  // Save specific plugin download config
  const handleSavePluginConfig = (slug: string) => {
    const plugin = plugins.find((p) => p.slug === slug);
    if (!plugin) return;
    db.updatePluginDownloadConfig(slug, {
      allowDownload: plugin.allowDownload ?? true,
      downloadUrl: editUrl,
      downloadAccess: editAccess,
      inrPrice: editPrice,
    });
    setPlugins([...db.getAllPlugins()]);
    setEditingSlug(null);
  };

  // Save UPI Settings
  const handleSaveUpi = (e: React.FormEvent) => {
    e.preventDefault();
    const updated = db.updatePaymentSettings(upiForm);
    setPaymentSettings(updated);
    setSaveSuccess(true);
    setTimeout(() => setSaveSuccess(false), 3000);
  };

  // Copy UPI ID
  const handleCopyUpi = () => {
    navigator.clipboard.writeText(upiForm.upiId);
    setCopiedUpi(true);
    setTimeout(() => setCopiedUpi(false), 2000);
  };

  // Approve Order
  const handleApproveOrder = (orderId: string) => {
    db.approveOrder(orderId);
    setOrders([...db.getOrders()]);
    setLicenses([...db.getLicenses()]);
  };

  // Calculate dynamic UPI URL and QR Code URL based on current upiId and testAmount
  const currentUpiUrl = `upi://pay?pa=${encodeURIComponent(upiForm.upiId.trim())}&pn=${encodeURIComponent(upiForm.upiDisplayName.trim())}&am=${testAmount}&cu=INR&tn=${encodeURIComponent(testNote.trim())}`;
  const dynamicQrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=${encodeURIComponent(currentUpiUrl)}`;

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
          <p className="text-sm text-slate-500 mt-1">
            Allow/restrict website downloads, manage dynamic price-coded UPI QR codes, review orders, and control product licenses.
          </p>
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

      {/* Navigation Tabs */}
      <div className="flex border-b border-slate-200 mt-6 overflow-x-auto space-x-8">
        <button
          onClick={() => setActiveTab('downloads')}
          className={`py-3 text-sm font-semibold border-b-2 flex items-center gap-2 transition-colors whitespace-nowrap ${
            activeTab === 'downloads'
              ? 'border-emerald-700 text-emerald-800'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          }`}
        >
          <Download className="h-4 w-4" />
          Plugin Downloads & Access Control
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
          UPI ID & Dynamic Price QR
        </button>
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
          Catalog Visibility ({plugins.length})
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

      {/* ========================================================================= */}
      {/* TAB 1: PLUGIN DOWNLOADS & ACCESS CONTROL (User Request Feature 1) */}
      {/* ========================================================================= */}
      {activeTab === 'downloads' && (
        <div className="space-y-6 mt-6">
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-emerald-50/70 p-5 rounded-2xl border border-emerald-200">
            <div>
              <div className="flex items-center gap-2">
                <Download className="h-5 w-5 text-emerald-700" />
                <h2 className="text-base font-bold text-emerald-950">Website Download Management</h2>
              </div>
              <p className="text-xs text-emerald-800 mt-1 max-w-2xl leading-relaxed">
                Control which plugins visitors can download from this website. When toggled <strong>Allowed</strong>, visitors see a 1-click Download button on product pages. When <strong>Disabled</strong>, public downloads are locked and visitors are prompted to upgrade or contact you.
              </p>
            </div>
            <div className="text-right shrink-0">
              <span className="text-xs text-slate-500 uppercase tracking-wider block">Total Ecosystem Downloads</span>
              <span className="text-2xl font-black text-emerald-800">{totalDownloads.toLocaleString()}</span>
            </div>
          </div>

          <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table className="w-full text-left text-sm text-slate-600">
              <thead className="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                <tr>
                  <th className="py-3.5 px-4">Plugin</th>
                  <th className="py-3.5 px-4">Download Permission</th>
                  <th className="py-3.5 px-4">Access Tier</th>
                  <th className="py-3.5 px-4">Price (INR)</th>
                  <th className="py-3.5 px-4 text-center">Downloads</th>
                  <th className="py-3.5 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {plugins.map((plugin) => {
                  const isEditing = editingSlug === plugin.slug;
                  const isAllowed = plugin.allowDownload !== false;

                  return (
                    <tr key={plugin.slug} className="hover:bg-slate-50/50">
                      <td className="py-3.5 px-4">
                        <div className="font-semibold text-slate-900">{plugin.name}</div>
                        <div className="text-xs text-slate-400 font-mono">slug: {plugin.slug}</div>
                      </td>

                      <td className="py-3.5 px-4">
                        <button
                          type="button"
                          onClick={() => handleToggleDownload(plugin.slug)}
                          className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold transition-colors ${
                            isAllowed
                              ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200'
                              : 'bg-rose-100 text-rose-800 hover:bg-rose-200'
                          }`}
                        >
                          {isAllowed ? (
                            <>
                              <CheckCircle2 className="h-3.5 w-3.5 text-emerald-600" />
                              DOWNLOAD ALLOWED
                            </>
                          ) : (
                            <>
                              <span className="h-2 w-2 rounded-full bg-rose-600" />
                              DOWNLOAD RESTRICTED
                            </>
                          )}
                        </button>
                      </td>

                      <td className="py-3.5 px-4">
                        {isEditing ? (
                          <select
                            value={editAccess}
                            onChange={(e) => setEditAccess(e.target.value as any)}
                            className="px-2 py-1 text-xs border border-slate-300 rounded"
                          >
                            <option value="public">Public (Open for everyone)</option>
                            <option value="registered">Registered Members Only</option>
                            <option value="licensed">Paid License Key Only</option>
                          </select>
                        ) : (
                          <Badge variant={plugin.downloadAccess === 'public' ? 'emerald' : plugin.downloadAccess === 'registered' ? 'indigo' : 'amber'}>
                            {plugin.downloadAccess === 'public' ? 'Open Download' : plugin.downloadAccess === 'registered' ? 'Registered' : 'Pro License'}
                          </Badge>
                        )}
                      </td>

                      <td className="py-3.5 px-4 font-mono font-semibold">
                        {isEditing ? (
                          <div className="flex items-center gap-1">
                            <span>₹</span>
                            <input
                              type="number"
                              value={editPrice}
                              onChange={(e) => setEditPrice(Number(e.target.value))}
                              className="w-20 px-2 py-1 text-xs border border-slate-300 rounded"
                            />
                          </div>
                        ) : (
                          plugin.inrPrice ? `₹${plugin.inrPrice.toLocaleString('en-IN')}` : 'Free ($0)'
                        )}
                      </td>

                      <td className="py-3.5 px-4 text-center font-mono font-semibold text-slate-700">
                        {plugin.downloadsCount ?? 0}
                      </td>

                      <td className="py-3.5 px-4 text-right space-x-2">
                        {isEditing ? (
                          <>
                            <Button size="sm" variant="primary" onClick={() => handleSavePluginConfig(plugin.slug)}>
                              Save
                            </Button>
                            <Button size="sm" variant="ghost" onClick={() => setEditingSlug(null)}>
                              Cancel
                            </Button>
                          </>
                        ) : (
                          <>
                            <Button
                              size="sm"
                              variant="outline"
                              onClick={() => {
                                setEditingSlug(plugin.slug);
                                setEditPrice(plugin.inrPrice || 0);
                                setEditUrl(plugin.downloadUrl || '');
                                setEditAccess(plugin.downloadAccess || 'public');
                              }}
                            >
                              Edit Rules
                            </Button>
                            <a
                              href={`/api/download/${plugin.slug}`}
                              target="_blank"
                              rel="noopener noreferrer"
                              className="inline-block"
                            >
                              <Button size="sm" variant="ghost" title="Test Download">
                                <Download className="h-3.5 w-3.5 text-slate-500" />
                              </Button>
                            </a>
                          </>
                        )}
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* ========================================================================= */}
      {/* TAB 2: UPI ID & AUTOMATIC DYNAMIC PRICE QR (User Request Feature 2) */}
      {/* ========================================================================= */}
      {activeTab === 'upi' && (
        <div className="space-y-6 mt-6">
          <div className="bg-gradient-to-r from-emerald-50 via-teal-50 to-indigo-50 p-6 rounded-2xl border border-emerald-200">
            <div className="flex items-center gap-2">
              <QrCode className="h-6 w-6 text-emerald-700" />
              <h2 className="text-xl font-extrabold text-slate-900">Dynamic UPI & Automatic Price QR Generator</h2>
            </div>
            <p className="text-sm text-slate-600 mt-1 max-w-3xl leading-relaxed">
              Enter your UPI ID below. When any price is set or selected (e.g. ₹499, ₹1,499, ₹3,999), the system <strong>automatically bakes the price directly into the UPI QR Code</strong> (`am=price`). When customers scan the QR in Google Pay, PhonePe, or Paytm, the exact price and your UPI ID appear instantly without manual typing!
            </p>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {/* Left Column: UPI Configuration Form */}
            <div className="lg:col-span-6 space-y-6">
              <Card className="border-slate-200">
                <CardHeader>
                  <CardTitle className="text-base flex items-center justify-between">
                    <span>Your UPI Receiving Credentials</span>
                    <Badge variant="emerald">Live Receiver</Badge>
                  </CardTitle>
                  <CardDescription>
                    All customer payments across Earthin Plugin Hub will be routed to this UPI ID.
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  {saveSuccess && (
                    <div className="mb-4 p-3 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold flex items-center gap-2">
                      <CheckCircle2 className="h-4 w-4 text-emerald-600" />
                      UPI settings saved! Automatic QR code has been generated for all checkouts.
                    </div>
                  )}

                  <form onSubmit={handleSaveUpi} className="space-y-4">
                    <div>
                      <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Your UPI ID / VPA
                      </label>
                      <div className="flex gap-2">
                        <input
                          type="text"
                          value={upiForm.upiId}
                          onChange={(e) => setUpiForm({ ...upiForm, upiId: e.target.value })}
                          placeholder="e.g. yourname@okhdfcbank or yourphone@paytm"
                          className="flex-1 px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono font-bold text-slate-900"
                          required
                        />
                        <button
                          type="button"
                          onClick={handleCopyUpi}
                          className="px-3 py-2 border border-slate-300 rounded-lg hover:bg-slate-50 text-xs font-semibold flex items-center gap-1"
                        >
                          {copiedUpi ? <Check className="h-3.5 w-3.5 text-emerald-600" /> : <Copy className="h-3.5 w-3.5 text-slate-500" />}
                          {copiedUpi ? 'Copied' : 'Copy'}
                        </button>
                      </div>
                      <p className="text-[11px] text-slate-500 mt-1">Accepts payments from GPay, PhonePe, Paytm, BHIM, AmazonPay, and all banking UPI apps.</p>
                    </div>

                    <div>
                      <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Payee / Business Display Name
                      </label>
                      <input
                        type="text"
                        value={upiForm.upiDisplayName}
                        onChange={(e) => setUpiForm({ ...upiForm, upiDisplayName: e.target.value })}
                        placeholder="e.g. Shaurya Joshi / Earthin WP Suite"
                        className="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 font-semibold text-slate-900"
                        required
                      />
                    </div>

                    <div>
                      <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Default Fallback Price (₹ INR)
                      </label>
                      <input
                        type="number"
                        value={upiForm.defaultPrice}
                        onChange={(e) => {
                          const val = Number(e.target.value);
                          setUpiForm({ ...upiForm, defaultPrice: val });
                          setTestAmount(val);
                        }}
                        className="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono font-bold text-slate-900"
                        required
                      />
                    </div>

                    <div>
                      <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Customer Checkout Instructions
                      </label>
                      <textarea
                        rows={3}
                        value={upiForm.instructions}
                        onChange={(e) => setUpiForm({ ...upiForm, instructions: e.target.value })}
                        className="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        required
                      />
                    </div>

                    <Button type="submit" variant="primary" className="w-full">
                      Save & Activate UPI ID
                    </Button>
                  </form>
                </CardContent>
              </Card>
            </div>

            {/* Right Column: Live Real-Time Price QR Generator */}
            <div className="lg:col-span-6 space-y-6">
              <Card className="border-slate-200 bg-white">
                <CardHeader>
                  <CardTitle className="text-base flex items-center justify-between">
                    <span>Live Dynamic Price QR Preview</span>
                    <span className="text-xs font-mono text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded">
                      AUTO-GENERATED
                    </span>
                  </CardTitle>
                  <CardDescription>
                    Test any price to see the QR code generate dynamically in real-time.
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-6">
                  {/* Test Price Selector */}
                  <div className="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                    <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                      Change Price to Test Automatic QR Generation:
                    </label>
                    <div className="flex gap-2">
                      {[499, 999, 1499, 3999, 11999].map((amt) => (
                        <button
                          key={amt}
                          type="button"
                          onClick={() => setTestAmount(amt)}
                          className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-all ${
                            testAmount === amt
                              ? 'bg-emerald-600 text-white shadow-sm'
                              : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-100'
                          }`}
                        >
                          ₹{amt.toLocaleString()}
                        </button>
                      ))}
                    </div>
                    <div className="flex items-center gap-2 pt-1">
                      <span className="text-xs font-bold text-slate-500">Custom Amount (₹):</span>
                      <input
                        type="number"
                        value={testAmount}
                        onChange={(e) => setTestAmount(Math.max(1, Number(e.target.value)))}
                        className="w-32 px-3 py-1.5 text-sm border border-slate-300 rounded-lg font-mono font-bold text-slate-900 bg-white"
                      />
                    </div>
                  </div>

                  {/* Scannable Dynamic QR Box */}
                  <div className="flex flex-col items-center justify-center p-6 bg-slate-50 rounded-2xl border border-slate-200">
                    <div className="bg-white p-5 rounded-2xl shadow-md border border-slate-200 text-center relative group">
                      <img
                        src={dynamicQrCodeUrl}
                        alt={`UPI QR Code for INR ${testAmount}`}
                        className="h-56 w-56 mx-auto object-contain transition-transform group-hover:scale-105"
                      />
                      <div className="mt-3 pt-3 border-t border-slate-100">
                        <div className="text-xs text-slate-500 uppercase font-semibold">Payable Amount</div>
                        <div className="text-2xl font-black text-emerald-700">₹{testAmount.toLocaleString('en-IN')}</div>
                        <div className="text-xs font-mono font-semibold text-slate-600 mt-1">{upiForm.upiId}</div>
                      </div>
                    </div>

                    {/* Direct UPI Mobile Action */}
                    <div className="mt-4 flex flex-col items-center gap-2 w-full max-w-sm">
                      <a
                        href={currentUpiUrl}
                        className="w-full"
                      >
                        <Button variant="primary" size="md" className="w-full flex items-center justify-center gap-2">
                          <Smartphone className="h-4 w-4" />
                          Test Pay via UPI App (GPay/PhonePe)
                        </Button>
                      </a>
                      <p className="text-[11px] text-slate-500 text-center">
                        On mobile, clicking this directly launches your UPI app with ₹{testAmount} pre-filled.
                      </p>
                    </div>

                    {/* Raw URI String */}
                    <div className="w-full mt-4 bg-white p-3 rounded-lg border border-slate-200 text-[11px] font-mono text-slate-600 break-all">
                      <span className="font-bold text-slate-800">Generated URI: </span>
                      {currentUpiUrl}
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      )}

      {/* ========================================================================= */}
      {/* TAB 3: OVERVIEW & REVENUE METRICS */}
      {/* ========================================================================= */}
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
                  All 20 verified standalone products
                </p>
              </CardContent>
            </Card>

            <Card className="border-slate-200">
              <CardContent className="pt-6">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Downloads</p>
                    <p className="text-3xl font-extrabold text-slate-900 mt-1">{totalDownloads}</p>
                  </div>
                  <div className="h-12 w-12 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
                    <Download className="h-6 w-6" />
                  </div>
                </div>
                <p className="text-xs text-slate-500 mt-3">Active across all 20 plugins</p>
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

      {/* ========================================================================= */}
      {/* TAB 4: PLUGINS CATALOG VISIBILITY */}
      {/* ========================================================================= */}
      {activeTab === 'plugins' && (
        <div className="space-y-6 mt-6">
          <div className="flex items-center justify-between">
            <h2 className="text-lg font-bold text-slate-900">All 20 WordPress Plugins</h2>
            <p className="text-xs text-slate-500">Toggle public marketplace visibility.</p>
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
                  <th className="py-3.5 px-4">Marketplace Status</th>
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

      {/* ========================================================================= */}
      {/* TAB 5: ORDERS */}
      {/* ========================================================================= */}
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

      {/* ========================================================================= */}
      {/* TAB 6: LICENSES */}
      {/* ========================================================================= */}
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
