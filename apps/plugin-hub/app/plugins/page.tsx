'use client';

import React, { useState, useMemo } from 'react';
import { Search, Filter, SlidersHorizontal, Package } from 'lucide-react';
import { CATEGORIES, PLUGINS } from '@/lib/catalog';
import { PluginCard } from '@/components/plugins/PluginCard';
import { Badge } from '@/components/ui/Badge';

export default function PluginsDirectoryPage() {
  const [search, setSearch] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('all');
  const [selectedPricing, setSelectedPricing] = useState('all');
  const [sortBy, setSortBy] = useState<'newest' | 'name'>('newest');

  const filteredPlugins = useMemo(() => {
    return PLUGINS.filter((p) => {
      if (!p.isPublished) return false;

      // Search match
      const matchSearch =
        p.name.toLowerCase().includes(search.toLowerCase()) ||
        p.tagline.toLowerCase().includes(search.toLowerCase()) ||
        p.features.some((f) => f.toLowerCase().includes(search.toLowerCase()));

      if (!matchSearch) return false;

      // Category match
      if (selectedCategory !== 'all' && p.category !== selectedCategory) {
        return false;
      }

      // Pricing match
      if (selectedPricing !== 'all' && p.pricingModel !== selectedPricing) {
        return false;
      }

      return true;
    }).sort((a, b) => {
      if (sortBy === 'name') {
        return a.name.localeCompare(b.name);
      }
      return 0;
    });
  }, [search, selectedCategory, selectedPricing, sortBy]);

  return (
    <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 space-y-10">
      {/* Header */}
      <div>
        <Badge variant="emerald" className="mb-2">OFFICIAL DIRECTORY</Badge>
        <h1 className="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
          WordPress Plugin Directory
        </h1>
        <p className="mt-2 text-base text-slate-600 max-w-2xl">
          Explore production-ready WordPress plugins built with modern PHP standards, strict security guards, and modular architecture.
        </p>
      </div>

      {/* Search & Filter Toolbar */}
      <div className="flex flex-col md:flex-row gap-4 justify-between items-stretch md:items-center bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
        {/* Search Input */}
        <div className="relative flex-1">
          <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
          <input
            type="text"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search plugins by name, module, or keyword..."
            className="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
          />
        </div>

        {/* Filter Controls */}
        <div className="flex flex-wrap items-center gap-3">
          {/* Category Dropdown */}
          <div className="flex items-center gap-1.5 text-xs text-slate-600 font-medium">
            <Filter className="h-3.5 w-3.5 text-slate-400" />
            <select
              value={selectedCategory}
              onChange={(e) => setSelectedCategory(e.target.value)}
              className="py-1.5 px-2.5 text-xs bg-slate-50 border border-slate-200 rounded-md focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
              {CATEGORIES.map((cat) => (
                <option key={cat.id} value={cat.slug}>
                  {cat.name}
                </option>
              ))}
            </select>
          </div>

          {/* Pricing Dropdown */}
          <div className="flex items-center gap-1.5 text-xs text-slate-600 font-medium">
            <select
              value={selectedPricing}
              onChange={(e) => setSelectedPricing(e.target.value)}
              className="py-1.5 px-2.5 text-xs bg-slate-50 border border-slate-200 rounded-md focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
              <option value="all">All Pricing</option>
              <option value="free">Free</option>
              <option value="freemium">Freemium</option>
              <option value="premium">Premium</option>
            </select>
          </div>

          {/* Sort Dropdown */}
          <div className="flex items-center gap-1.5 text-xs text-slate-600 font-medium">
            <SlidersHorizontal className="h-3.5 w-3.5 text-slate-400" />
            <select
              value={sortBy}
              onChange={(e) => setSortBy(e.target.value as 'newest' | 'name')}
              className="py-1.5 px-2.5 text-xs bg-slate-50 border border-slate-200 rounded-md focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
              <option value="newest">Latest Release</option>
              <option value="name">Alphabetical</option>
            </select>
          </div>
        </div>
      </div>

      {/* Results Count */}
      <div className="text-xs font-semibold text-slate-500 tracking-wide uppercase">
        Showing {filteredPlugins.length} {filteredPlugins.length === 1 ? 'plugin' : 'plugins'}
      </div>

      {/* Grid */}
      {filteredPlugins.length > 0 ? (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {filteredPlugins.map((plugin) => (
            <PluginCard key={plugin.id} plugin={plugin} />
          ))}
        </div>
      ) : (
        <div className="text-center py-16 bg-white rounded-xl border border-slate-200/80 p-8 space-y-3">
          <Package className="h-10 w-10 text-slate-400 mx-auto" />
          <h3 className="text-base font-bold text-slate-800">No matching plugins found</h3>
          <p className="text-sm text-slate-500 max-w-md mx-auto">
            Try adjusting your search query or reset category and pricing filters to see all available tools.
          </p>
          <button
            type="button"
            onClick={() => {
              setSearch('');
              setSelectedCategory('all');
              setSelectedPricing('all');
            }}
            className="text-xs text-emerald-700 font-semibold hover:underline"
          >
            Reset All Filters
          </button>
        </div>
      )}
    </div>
  );
}
