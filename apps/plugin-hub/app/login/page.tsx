'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { Package, Lock, Mail, ArrowRight, ShieldCheck, CheckCircle2, AlertCircle } from 'lucide-react';
import { Button } from '@/components/ui/Button';
import { loginUser } from '@/lib/auth';

export default function LoginPage() {
  const router = useRouter();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [notice, setNotice] = useState<{ type: 'admin' | 'customer'; message: string } | null>(null);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    const user = loginUser(email, password);

    if (user.role === 'ADMIN') {
      setNotice({
        type: 'admin',
        message: 'Admin credentials recognized! Redirecting to Owner Administration...',
      });
      setTimeout(() => {
        router.push('/admin');
      }, 700);
    } else {
      setNotice({
        type: 'customer',
        message: `Welcome back, ${user.name}! Redirecting to your Customer Account...`,
      });
      setTimeout(() => {
        router.push('/account');
      }, 700);
    }
  };

  return (
    <div className="min-h-[80vh] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
      <div className="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <Link href="/" className="inline-flex items-center gap-2 mb-4">
          <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-700 text-white shadow-sm">
            <Package className="h-5 w-5" />
          </div>
          <span className="font-bold text-xl text-slate-900 tracking-tight">Earthin</span>
        </Link>
        <h2 className="text-2xl font-extrabold text-slate-900 tracking-tight">Sign in to your account</h2>
        <p className="mt-2 text-sm text-slate-500">
          Or{' '}
          <Link href="/register" className="font-semibold text-emerald-700 hover:text-emerald-800 underline">
            create a new Earthin account
          </Link>
        </p>
      </div>

      <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div className="bg-white py-8 px-6 shadow-sm border border-slate-200/80 rounded-2xl sm:px-10">
          {notice && (
            <div
              className={`mb-5 p-3 rounded-xl border text-xs font-semibold flex items-center gap-2 ${
                notice.type === 'admin'
                  ? 'bg-purple-50 text-purple-900 border-purple-200'
                  : 'bg-emerald-50 text-emerald-900 border-emerald-200'
              }`}
            >
              <CheckCircle2 className="h-4 w-4 shrink-0" />
              <span>{notice.message}</span>
            </div>
          )}

          <form className="space-y-4" onSubmit={handleSubmit}>
            <div>
              <label className="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                Email address
              </label>
              <div className="relative">
                <input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="admin@earthin.online or your@email.com"
                  required
                  className="w-full px-3 py-2 pl-9 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium"
                />
                <Mail className="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
              </div>
            </div>

            <div>
              <div className="flex items-center justify-between mb-1">
                <label className="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                  Password
                </label>
                <a href="#forgot" className="text-xs text-emerald-700 hover:underline">
                  Forgot password?
                </a>
              </div>
              <div className="relative">
                <input
                  type="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="••••••••"
                  required
                  className="w-full px-3 py-2 pl-9 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                />
                <Lock className="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
              </div>
            </div>

            <Button type="submit" variant="primary" className="w-full justify-center" disabled={loading}>
              {loading ? 'Verifying...' : 'Sign In'}
              <ArrowRight className="ml-2 h-4 w-4" />
            </Button>
          </form>
        </div>
      </div>
    </div>
  );
}
