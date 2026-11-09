import React from 'react';
import { cn } from '@/lib/utils';

export interface BadgeProps extends React.HTMLAttributes<HTMLSpanElement> {
  variant?: 'emerald' | 'indigo' | 'amber' | 'slate' | 'purple';
}

export function Badge({ className, variant = 'slate', children, ...props }: BadgeProps) {
  const base = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium tracking-wide';

  const variants = {
    emerald: 'bg-emerald-100 text-emerald-800 border border-emerald-200',
    indigo: 'bg-indigo-100 text-indigo-800 border border-indigo-200',
    amber: 'bg-amber-100 text-amber-800 border border-amber-200',
    slate: 'bg-slate-100 text-slate-800 border border-slate-200',
    purple: 'bg-purple-100 text-purple-800 border border-purple-200',
  };

  return (
    <span className={cn(base, variants[variant], className)} {...props}>
      {children}
    </span>
  );
}
