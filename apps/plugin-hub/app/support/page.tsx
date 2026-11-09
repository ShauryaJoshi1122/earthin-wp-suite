'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { LifeBuoy, Send, CheckCircle2, AlertCircle, BookOpen, GitPullRequest } from 'lucide-react';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/Card';

export default function SupportPage() {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    plugin: 'earthin-wp-suite',
    subject: '',
    message: '',
  });

  const [submitted, setSubmitted] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setError('');

    // Client-side sanitization and validation
    const name = formData.name.trim();
    const email = formData.email.trim();
    const message = formData.message.trim();

    if (!name || !email || !message) {
      setError('Please complete all required fields.');
      return;
    }

    if (!email.includes('@') || !email.includes('.')) {
      setError('Please provide a valid email address.');
      return;
    }

    // In Milestone 1, record submission state gracefully
    setSubmitted(true);
  };

  return (
    <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 space-y-16">
      {/* Header */}
      <div className="max-w-3xl space-y-3">
        <Badge variant="emerald">CUSTOMER SUPPORT</Badge>
        <h1 className="text-4xl font-extrabold tracking-tight text-slate-900">
          Earthin Help & Support Center
        </h1>
        <p className="text-base text-slate-600">
          Have questions about module setup, compatibility, or encountered an issue? We are here to help.
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-12 items-start">
        {/* Contact Form */}
        <div className="lg:col-span-2">
          <Card className="bg-white border-slate-200">
            <CardHeader>
              <CardTitle className="text-xl">Submit a Support Inquiry</CardTitle>
              <p className="text-xs text-slate-500">
                Responses are typically provided within 1 business day.
              </p>
            </CardHeader>

            <CardContent>
              {submitted ? (
                <div className="p-8 text-center space-y-3 rounded-xl bg-emerald-50 border border-emerald-100">
                  <CheckCircle2 className="h-10 w-10 text-emerald-600 mx-auto" />
                  <h3 className="text-lg font-bold text-slate-900">Support Request Received</h3>
                  <p className="text-sm text-slate-600 max-w-md mx-auto">
                    Thank you, {formData.name}. Your support inquiry has been recorded. Our engineering team will review the details and reach out to {formData.email}.
                  </p>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => {
                      setSubmitted(false);
                      setFormData({ name: '', email: '', plugin: 'earthin-wp-suite', subject: '', message: '' });
                    }}
                  >
                    Submit Another Inquiry
                  </Button>
                </div>
              ) : (
                <form onSubmit={handleSubmit} className="space-y-4">
                  {error && (
                    <div className="p-3 text-xs rounded-lg bg-rose-50 text-rose-700 border border-rose-200 flex items-center gap-2">
                      <AlertCircle className="h-4 w-4 shrink-0" />
                      <span>{error}</span>
                    </div>
                  )}

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div className="space-y-1">
                      <label className="text-xs font-semibold text-slate-700">
                        Your Name <span className="text-rose-500">*</span>
                      </label>
                      <input
                        type="text"
                        required
                        value={formData.name}
                        onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                        className="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white"
                        placeholder="John Doe"
                      />
                    </div>

                    <div className="space-y-1">
                      <label className="text-xs font-semibold text-slate-700">
                        Your Email <span className="text-rose-500">*</span>
                      </label>
                      <input
                        type="email"
                        required
                        value={formData.email}
                        onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                        className="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white"
                        placeholder="john@example.com"
                      />
                    </div>
                  </div>

                  <div className="space-y-1">
                    <label className="text-xs font-semibold text-slate-700">
                      Associated Plugin
                    </label>
                    <select
                      value={formData.plugin}
                      onChange={(e) => setFormData({ ...formData, plugin: e.target.value })}
                      className="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white"
                    >
                      <option value="earthin-wp-suite">Earthin WP Suite</option>
                      <option value="general">General Marketplace Question</option>
                      <option value="licensing">Licensing / Pricing Inquiry</option>
                    </select>
                  </div>

                  <div className="space-y-1">
                    <label className="text-xs font-semibold text-slate-700">
                      Subject
                    </label>
                    <input
                      type="text"
                      value={formData.subject}
                      onChange={(e) => setFormData({ ...formData, subject: e.target.value })}
                      className="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white"
                      placeholder="e.g. Question about 404 redirects"
                    />
                  </div>

                  <div className="space-y-1">
                    <label className="text-xs font-semibold text-slate-700">
                      Detailed Message <span className="text-rose-500">*</span>
                    </label>
                    <textarea
                      required
                      rows={5}
                      value={formData.message}
                      onChange={(e) => setFormData({ ...formData, message: e.target.value })}
                      className="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white"
                      placeholder="Describe your question or issue in detail..."
                    />
                  </div>

                  <Button type="submit" variant="primary" size="md">
                    <Send className="mr-2 h-4 w-4" />
                    Submit Support Request
                  </Button>
                </form>
              )}
            </CardContent>
          </Card>
        </div>

        {/* Resources & Alternative Channels */}
        <div className="space-y-6">
          <Card className="bg-white border-slate-200">
            <CardHeader>
              <CardTitle className="text-base">Self-Service Resources</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <Link href="/docs" className="flex items-start gap-3 p-3 rounded-lg hover:bg-slate-50 transition-colors">
                <BookOpen className="h-5 w-5 text-emerald-600 shrink-0 mt-0.5" />
                <div>
                  <h4 className="text-xs font-bold text-slate-900">Documentation</h4>
                  <p className="text-xs text-slate-500">Browse setup guides and FAQs.</p>
                </div>
              </Link>
              <a
                href="https://github.com/ShauryaJoshi1122/earthin-wp-suite/issues"
                target="_blank"
                rel="noopener noreferrer"
                className="flex items-start gap-3 p-3 rounded-lg hover:bg-slate-50 transition-colors"
              >
                <GitPullRequest className="h-5 w-5 text-emerald-600 shrink-0 mt-0.5" />
                <div>
                  <h4 className="text-xs font-bold text-slate-900">GitHub Issues</h4>
                  <p className="text-xs text-slate-500">Report verifiable defects or pull requests.</p>
                </div>
              </a>
            </CardContent>
          </Card>

          <Card className="bg-slate-900 text-white border-slate-800">
            <CardHeader>
              <div className="flex items-center gap-2 mb-1">
                <LifeBuoy className="h-4 w-4 text-emerald-400" />
                <CardTitle className="text-sm text-white">Community & Direct Help</CardTitle>
              </div>
              <p className="text-xs text-slate-400">
                Earthin is committed to supporting our open-source tools with transparent, reliable updates.
              </p>
            </CardHeader>
          </Card>
        </div>
      </div>
    </div>
  );
}
