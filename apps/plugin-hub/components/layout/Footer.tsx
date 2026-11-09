import React from 'react';
import Link from 'next/link';
import { Layers, ShieldCheck, Heart } from 'lucide-react';

export function Footer() {
  return (
    <footer className="border-t border-slate-200 bg-slate-50 text-slate-600">
      <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 gap-8 md:grid-cols-4 lg:gap-12">
          {/* Brand info */}
          <div className="space-y-4">
            <Link href="/" className="flex items-center space-x-2">
              <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white shadow-sm">
                <Layers className="h-4 w-4" />
              </div>
              <span className="text-base font-bold text-slate-900">
                Earthin <span className="text-emerald-600">Plugin Hub</span>
              </span>
            </Link>
            <p className="text-sm text-slate-600 leading-relaxed">
              Official marketplace and distribution repository for Earthin WordPress plugins. Built with strict security, high performance, and clean modularity.
            </p>
            <div className="flex items-center space-x-1.5 text-xs text-slate-500">
              <ShieldCheck className="h-4 w-4 text-emerald-600" />
              <span>GPL-2.0+ Compatible Software</span>
            </div>
          </div>

          {/* Directory Links */}
          <div>
            <h4 className="text-xs font-semibold uppercase tracking-wider text-slate-900">Marketplace</h4>
            <ul className="mt-3 space-y-2 text-sm">
              <li>
                <Link href="/plugins" className="hover:text-emerald-600 transition-colors">
                  All Plugins
                </Link>
              </li>
              <li>
                <Link href="/categories" className="hover:text-emerald-600 transition-colors">
                  Plugin Categories
                </Link>
              </li>
              <li>
                <Link href="/pricing" className="hover:text-emerald-600 transition-colors">
                  Pricing & Freemium
                </Link>
              </li>
              <li>
                <Link href="/account" className="hover:text-emerald-600 transition-colors">
                  Customer Dashboard
                </Link>
              </li>
            </ul>
          </div>

          {/* Resources */}
          <div>
            <h4 className="text-xs font-semibold uppercase tracking-wider text-slate-900">Resources</h4>
            <ul className="mt-3 space-y-2 text-sm">
              <li>
                <Link href="/docs" className="hover:text-emerald-600 transition-colors">
                  Documentation & Guides
                </Link>
              </li>
              <li>
                <Link href="/changelog" className="hover:text-emerald-600 transition-colors">
                  Release Notes
                </Link>
              </li>
              <li>
                <Link href="/support" className="hover:text-emerald-600 transition-colors">
                  Support Center
                </Link>
              </li>
              <li>
                <Link href="/admin" className="hover:text-emerald-600 transition-colors">
                  Admin Portal
                </Link>
              </li>
            </ul>
          </div>

          {/* Company & Legal */}
          <div>
            <h4 className="text-xs font-semibold uppercase tracking-wider text-slate-900">Studio</h4>
            <ul className="mt-3 space-y-2 text-sm">
              <li>
                <Link href="/about" className="hover:text-emerald-600 transition-colors">
                  About Earthin
                </Link>
              </li>
              <li>
                <Link href="/contact" className="hover:text-emerald-600 transition-colors">
                  Contact Us
                </Link>
              </li>
              <li>
                <a href="https://github.com/ShauryaJoshi1122/earthin-wp-suite" target="_blank" rel="noopener noreferrer" className="hover:text-emerald-600 transition-colors">
                  GitHub Repository
                </a>
              </li>
            </ul>
          </div>
        </div>

        <div className="mt-10 border-t border-slate-200/80 pt-6 text-xs text-slate-500 flex flex-col md:flex-row items-center justify-between gap-4">
          <p>© {new Date().getFullYear()} Earthin. All rights reserved. WordPress® is a registered trademark of the WordPress Foundation.</p>
          <p className="flex items-center gap-1">
            Engineered for stability and developer happiness.
          </p>
        </div>
      </div>
    </footer>
  );
}
