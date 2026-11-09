export type PricingModel = 'free' | 'freemium' | 'premium';

export interface PluginCategory {
  id: string;
  name: string;
  slug: string;
  description: string;
}

export interface PluginRelease {
  version: string;
  date: string;
  downloadUrl?: string;
  checksumSha256?: string;
  minWpVersion: string;
  testedWpVersion: string;
  minPhpVersion: string;
  isStable: boolean;
  notes: string[];
}

export interface PluginItem {
  id: string;
  name: string;
  slug: string;
  tagline: string;
  shortDescription: string;
  description: string;
  category: string;
  pricingModel: PricingModel;
  freeVersionAvailable: boolean;
  premiumVersionAvailable: boolean;
  latestVersion: string;
  minWpVersion: string;
  testedWpVersion: string;
  minPhpVersion: string;
  featured: boolean;
  isPublished: boolean;
  features: string[];
  premiumFeatures?: string[];
  installationSteps: string[];
  releases: PluginRelease[];
  author?: string;
  authorUri?: string;
  activeInstalls?: string;
  // Admin download management & pricing
  allowDownload?: boolean;
  downloadUrl?: string;
  downloadAccess?: 'public' | 'registered' | 'licensed';
  downloadsCount?: number;
  inrPrice?: number;
}
