'use client';

import React, { useEffect } from 'react';
import { AlertTriangle, RefreshCw } from 'lucide-react';
import { Button } from '@/components/ui/Button';

export default function ErrorBoundary({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    // Log unexpected errors safely
    console.error('[Earthin Plugin Hub] Application Error:', error);
  }, [error]);

  return (
    <div className="mx-auto max-w-7xl px-4 py-28 sm:px-6 lg:px-8 text-center space-y-4">
      <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 mx-auto">
        <AlertTriangle className="h-8 w-8" />
      </div>
      <h1 className="text-3xl font-extrabold text-slate-900">Application Error</h1>
      <p className="text-sm text-slate-600 max-w-md mx-auto">
        An unexpected error occurred while rendering this page. Our team has been notified.
      </p>
      <div className="pt-4 flex justify-center gap-3">
        <Button variant="primary" size="md" onClick={() => reset()}>
          <RefreshCw className="mr-2 h-4 w-4" />
          Try Again
        </Button>
      </div>
    </div>
  );
}
