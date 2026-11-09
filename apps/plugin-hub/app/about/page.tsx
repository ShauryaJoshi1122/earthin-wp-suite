import React from 'react';
import Link from 'next/link';
import { Package, Shield, Heart, Sparkles, CheckCircle2, ArrowRight } from 'lucide-react';
import { Button } from '@/components/ui/Button';

export const metadata = {
  title: 'About Earthin - WordPress Software Studio',
  description: 'Learn about Earthin, our open-source philosophy, and founder Shaurya Joshi.',
};

export default function AboutPage() {
  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
      {/* Hero */}
      <div className="text-center space-y-4 mb-14">
        <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs font-semibold">
          <Sparkles className="h-3.5 w-3.5" />
          The Earthin Story
        </div>
        <h1 className="text-4xl font-extrabold text-slate-900 tracking-tight sm:text-5xl">
          Crafting clean, independent WordPress tools.
        </h1>
        <p className="text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
          Earthin is an independent WordPress engineering studio founded by <strong>Shaurya Joshi</strong>, dedicated to high performance, zero data-tracking, and decoupled plugin architectures.
        </p>
      </div>

      {/* Core Values */}
      <div className="space-y-12">
        <section className="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm space-y-4">
          <h2 className="text-2xl font-bold text-slate-900">Why Earthin Exists</h2>
          <p className="text-sm text-slate-600 leading-relaxed">
            The modern WordPress ecosystem has become cluttered with bloated, advertisement-heavy plugins that compromise server speed, track customer behavior, and lock basic features behind aggressive paywalls.
          </p>
          <p className="text-sm text-slate-600 leading-relaxed">
            Earthin was born with a singular focus: building lightweight, honest, and performant WordPress tools that do their jobs with elegance. Every plugin is developed with clean WordPress APIs, strict capability checks, and zero intrusive telemetry.
          </p>
        </section>

        <section className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="p-6 bg-slate-50 rounded-xl border border-slate-200/80 space-y-2">
            <Shield className="h-6 w-6 text-emerald-700" />
            <h3 className="font-bold text-slate-900">Security & Privacy First</h3>
            <p className="text-xs text-slate-500 leading-relaxed">
              We never track visitor IP addresses or phone home with unnecessary telemetry. Everything runs locally on your infrastructure.
            </p>
          </div>
          <div className="p-6 bg-slate-50 rounded-xl border border-slate-200/80 space-y-2">
            <Package className="h-6 w-6 text-indigo-700" />
            <h3 className="font-bold text-slate-900">Decoupled Architecture</h3>
            <p className="text-xs text-slate-500 leading-relaxed">
              Whether you install the all-in-one Earthin WP Suite or a single-purpose utility, every plugin operates with zero cross-module interference.
            </p>
          </div>
          <div className="p-6 bg-slate-50 rounded-xl border border-slate-200/80 space-y-2">
            <Heart className="h-6 w-6 text-rose-600" />
            <h3 className="font-bold text-slate-900">Honest Freemium</h3>
            <p className="text-xs text-slate-500 leading-relaxed">
              Essential safety, database maintenance, and core functionality remain 100% free forever under the GPL license.
            </p>
          </div>
        </section>

        {/* Developer Attribution */}
        <section className="p-8 bg-emerald-50/60 rounded-2xl border border-emerald-100 flex flex-col sm:flex-row items-center gap-6">
          <div className="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-emerald-700 text-white font-bold text-2xl shadow-sm">
            SJ
          </div>
          <div className="space-y-1 text-center sm:text-left">
            <h3 className="text-lg font-bold text-slate-900">Shaurya Joshi</h3>
            <p className="text-xs font-semibold text-emerald-800">Founder & Lead Plugin Architect</p>
            <p className="text-xs text-slate-600 leading-relaxed pt-1">
              Software engineer and open-source enthusiast focused on building reliable, secure web platforms and empowering independent creators.
            </p>
          </div>
        </section>

        {/* Call to action */}
        <div className="text-center pt-4">
          <Link href="/plugins">
            <Button size="lg" variant="primary">
              Explore Our WordPress Plugins
              <ArrowRight className="ml-2 h-4 w-4" />
            </Button>
          </Link>
        </div>
      </div>
    </div>
  );
}
