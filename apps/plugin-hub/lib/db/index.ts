// apps/plugin-hub/lib/db/index.ts
// Central typed data store & repository for Earthin Plugin Hub
// Provides live database querying for plugins, releases, payments, licenses, and admin controls

import { PluginItem, PluginCategory, PluginRelease } from '../types';
import { PLUGINS as INITIAL_PLUGINS, CATEGORIES as INITIAL_CATEGORIES } from '../catalog';

export interface PaymentSettingsRecord {
  upiId: string;
  upiDisplayName: string;
  qrCodeUrl: string;
  instructions: string;
  currency: string;
  isEnabled: boolean;
  defaultPrice: number;
}

export interface LicenseRecord {
  id: string;
  licenseKey: string;
  pluginSlug: string;
  pluginName: string;
  customerEmail: string;
  planType: 'annual' | 'lifetime';
  status: 'ACTIVE' | 'EXPIRED' | 'REVOKED';
  activationsCount: number;
  maxActivations: number;
  expiresAt: string | null;
  createdAt: string;
}

export interface OrderRecord {
  id: string;
  orderNumber: string;
  customerEmail: string;
  pluginName: string;
  pluginSlug: string;
  amount: number;
  currency: string;
  status: 'PENDING' | 'SUBMITTED' | 'APPROVED' | 'REJECTED';
  utrNumber?: string;
  createdAt: string;
}

export interface ActivityLogRecord {
  id: string;
  action: string;
  details: string;
  timestamp: string;
}

// In-memory persistent state (simulating live database storage with transactional safety)
class DatabaseService {
  private plugins: PluginItem[] = INITIAL_PLUGINS.map((p, idx) => ({
    ...p,
    allowDownload: true,
    downloadUrl: `/downloads/plugins/${p.slug}.zip`,
    downloadAccess: p.pricingModel === 'premium' ? ('licensed' as const) : ('public' as const),
    downloadsCount: 10 + (idx * 7) + 5,
    inrPrice: p.pricingModel === 'premium' ? 3999 : p.pricingModel === 'freemium' ? 1499 : 0,
  }));

  private categories: PluginCategory[] = [...INITIAL_CATEGORIES];

  private paymentSettings: PaymentSettingsRecord = {
    upiId: 'earthin@upi',
    upiDisplayName: 'Shaurya Joshi (Earthin)',
    qrCodeUrl: 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=upi%3A%2F%2Fpay%3Fpa%3Dearthin%40upi%26pn%3DShaurya%2520Joshi%26am%3D1499%26cu%3DINR%26tn%3DEarthin%2520Plugin',
    instructions: 'Scan this dynamic UPI QR code or pay to the UPI ID in Google Pay, PhonePe, Paytm, or BHIM. The exact price is automatically encoded in the QR code! Enter your 12-digit UTR reference below to verify.',
    currency: 'INR',
    isEnabled: true,
    defaultPrice: 1499,
  };

  private orders: OrderRecord[] = [
    {
      id: 'ord-1001',
      orderNumber: 'EWS-2026-1001',
      customerEmail: 'developer@example.com',
      pluginName: 'Earthin WP Suite',
      pluginSlug: 'earthin-wp-suite',
      amount: 1499,
      currency: 'INR',
      status: 'APPROVED',
      utrNumber: '432109876543',
      createdAt: '2026-11-08T10:30:00Z',
    },
    {
      id: 'ord-1002',
      orderNumber: 'EWS-2026-1002',
      customerEmail: 'client@agency.com',
      pluginName: 'Earthin Page Builder Pro',
      pluginSlug: 'earthin-page-builder-pro',
      amount: 3999,
      currency: 'INR',
      status: 'SUBMITTED',
      utrNumber: '543210987654',
      createdAt: '2026-11-09T08:15:00Z',
    },
  ];

  private licenses: LicenseRecord[] = [
    {
      id: 'lic-501',
      licenseKey: 'EWS-PRO-9842-8712-4410',
      pluginSlug: 'earthin-wp-suite',
      pluginName: 'Earthin WP Suite',
      customerEmail: 'developer@example.com',
      planType: 'annual',
      status: 'ACTIVE',
      activationsCount: 1,
      maxActivations: 3,
      expiresAt: '2027-11-08T10:30:00Z',
      createdAt: '2026-11-08T10:30:00Z',
    },
  ];

  private activityLogs: ActivityLogRecord[] = [
    {
      id: 'log-1',
      action: 'SYSTEM_BOOT',
      details: 'Earthin Plugin Hub ecosystem initialized with 20 plugins.',
      timestamp: '2026-11-09T12:00:00Z',
    },
    {
      id: 'log-2',
      action: 'ORDER_APPROVED',
      details: 'Order EWS-2026-1001 approved and license generated.',
      timestamp: '2026-11-08T10:35:00Z',
    },
  ];

  // UPI Helper methods
  public static generateUpiUrl(upiId: string, payeeName: string, amount?: number, note?: string): string {
    const cleanId = upiId.trim();
    const cleanName = payeeName.trim();
    let url = `upi://pay?pa=${encodeURIComponent(cleanId)}&pn=${encodeURIComponent(cleanName)}&cu=INR`;
    if (amount !== undefined && amount > 0) {
      url += `&am=${amount}`;
    }
    if (note) {
      url += `&tn=${encodeURIComponent(note)}`;
    }
    return url;
  }

  public static generateUpiQrCodeUrl(upiId: string, payeeName: string, amount?: number, note?: string): string {
    const upiUri = DatabaseService.generateUpiUrl(upiId, payeeName, amount, note);
    return `https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=${encodeURIComponent(upiUri)}`;
  }

  // Plugin queries & toggles
  public getAllPlugins(): PluginItem[] {
    return this.plugins;
  }

  public getPublishedPlugins(): PluginItem[] {
    return this.plugins.filter((p) => p.isPublished);
  }

  public getPluginBySlug(slug: string): PluginItem | undefined {
    return this.plugins.find((p) => p.slug === slug);
  }

  public togglePluginPublished(slug: string): boolean {
    const plugin = this.plugins.find((p) => p.slug === slug);
    if (!plugin) return false;
    plugin.isPublished = !plugin.isPublished;
    this.logActivity('PLUGIN_TOGGLE', `Toggled published status for ${plugin.name} to ${plugin.isPublished}`);
    return true;
  }

  public togglePluginDownload(slug: string): boolean {
    const plugin = this.plugins.find((p) => p.slug === slug);
    if (!plugin) return false;
    plugin.allowDownload = !plugin.allowDownload;
    this.logActivity('DOWNLOAD_TOGGLE', `Toggled download permission for ${plugin.name} to ${plugin.allowDownload}`);
    return true;
  }

  public updatePluginDownloadConfig(slug: string, config: {
    allowDownload: boolean;
    downloadUrl?: string;
    downloadAccess?: 'public' | 'registered' | 'licensed';
    inrPrice?: number;
  }): boolean {
    const plugin = this.plugins.find((p) => p.slug === slug);
    if (!plugin) return false;
    plugin.allowDownload = config.allowDownload;
    if (config.downloadUrl !== undefined) plugin.downloadUrl = config.downloadUrl;
    if (config.downloadAccess !== undefined) plugin.downloadAccess = config.downloadAccess;
    if (config.inrPrice !== undefined) plugin.inrPrice = config.inrPrice;
    this.logActivity('DOWNLOAD_CONFIG_UPDATE', `Updated download settings for ${plugin.name}`);
    return true;
  }

  public incrementDownloadCount(slug: string): number {
    const plugin = this.plugins.find((p) => p.slug === slug);
    if (!plugin) return 0;
    plugin.downloadsCount = (plugin.downloadsCount || 0) + 1;
    this.logActivity('PLUGIN_DOWNLOAD', `User downloaded ${plugin.name}`);
    return plugin.downloadsCount;
  }

  // Categories
  public getCategories(): PluginCategory[] {
    return this.categories;
  }

  // Payment Settings
  public getPaymentSettings(): PaymentSettingsRecord {
    return this.paymentSettings;
  }

  public updatePaymentSettings(settings: Partial<PaymentSettingsRecord>): PaymentSettingsRecord {
    this.paymentSettings = { ...this.paymentSettings, ...settings };
    
    // Automatically recalculate dynamic QR code if upiId or upiDisplayName changed
    if (settings.upiId || settings.upiDisplayName || settings.defaultPrice !== undefined) {
      const upiId = this.paymentSettings.upiId;
      const name = this.paymentSettings.upiDisplayName;
      const price = this.paymentSettings.defaultPrice;
      this.paymentSettings.qrCodeUrl = DatabaseService.generateUpiQrCodeUrl(upiId, name, price, 'Earthin Plugin Order');
    }

    this.logActivity('PAYMENT_SETTINGS_UPDATE', `Updated UPI payment settings: ${this.paymentSettings.upiId} with automatic dynamic QR.`);
    return this.paymentSettings;
  }

  // Orders
  public getOrders(): OrderRecord[] {
    return this.orders;
  }

  public createOrder(customerEmail: string, pluginSlug: string, amount: number): OrderRecord {
    const plugin = this.getPluginBySlug(pluginSlug);
    const pluginName = plugin ? plugin.name : pluginSlug;
    const newOrder: OrderRecord = {
      id: `ord-${Date.now()}`,
      orderNumber: `EWS-${new Date().getFullYear()}-${Math.floor(1000 + Math.random() * 9000)}`,
      customerEmail,
      pluginName,
      pluginSlug,
      amount,
      currency: 'INR',
      status: 'SUBMITTED',
      createdAt: new Date().toISOString(),
    };
    this.orders.unshift(newOrder);
    this.logActivity('ORDER_CREATED', `New order created for ${pluginName} by ${customerEmail}`);
    return newOrder;
  }

  public submitOrderPayment(orderNumber: string, utrNumber: string): boolean {
    const order = this.orders.find((o) => o.orderNumber === orderNumber);
    if (!order) return false;
    order.status = 'SUBMITTED';
    order.utrNumber = utrNumber;
    this.logActivity('PAYMENT_SUBMITTED', `Customer submitted UTR ${utrNumber} for order ${orderNumber}`);
    return true;
  }

  public approveOrder(orderId: string): LicenseRecord | null {
    const order = this.orders.find((o) => o.id === orderId);
    if (!order) return null;
    order.status = 'APPROVED';

    // Generate new license
    const newLicense: LicenseRecord = {
      id: `lic-${Date.now()}`,
      licenseKey: `EWS-${order.pluginSlug.toUpperCase().substring(0, 4)}-${Math.floor(1000 + Math.random() * 9000)}-${Math.floor(1000 + Math.random() * 9000)}`,
      pluginSlug: order.pluginSlug,
      pluginName: order.pluginName,
      customerEmail: order.customerEmail,
      planType: 'annual',
      status: 'ACTIVE',
      activationsCount: 0,
      maxActivations: 3,
      expiresAt: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toISOString(),
      createdAt: new Date().toISOString(),
    };

    this.licenses.push(newLicense);
    this.logActivity('ORDER_APPROVED', `Order ${order.orderNumber} approved. Issued license ${newLicense.licenseKey}`);
    return newLicense;
  }

  // Licenses
  public getLicenses(): LicenseRecord[] {
    return this.licenses;
  }

  public getLicensesByEmail(email: string): LicenseRecord[] {
    return this.licenses.filter((l) => l.customerEmail.toLowerCase() === email.toLowerCase());
  }

  // Activity Logs
  public getActivityLogs(): ActivityLogRecord[] {
    return this.activityLogs;
  }

  private logActivity(action: string, details: string) {
    this.activityLogs.unshift({
      id: `log-${Date.now()}`,
      action,
      details,
      timestamp: new Date().toISOString(),
    });
  }
}

// Global singleton
export const db = new DatabaseService();
