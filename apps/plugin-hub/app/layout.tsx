import type { Metadata } from 'next';
import './globals.css';
import { Navbar } from '@/components/layout/Navbar';
import { Footer } from '@/components/layout/Footer';

export const metadata: Metadata = {
  title: 'Earthin Plugin Hub — Official WordPress Plugins',
  description: 'Discover, download, and manage modular, secure, and production-ready WordPress plugins engineered by Earthin.',
  metadataBase: new URL('https://plugins.earthin.online'),
  openGraph: {
    title: 'Earthin Plugin Hub — Official WordPress Plugins',
    description: 'Discover, download, and manage modular, secure, and production-ready WordPress plugins engineered by Earthin.',
    url: 'https://plugins.earthin.online',
    siteName: 'Earthin Plugin Hub',
    locale: 'en_US',
    type: 'website',
  },
  twitter: {
    card: 'summary_large_image',
    title: 'Earthin Plugin Hub — Official WordPress Plugins',
    description: 'Discover, download, and manage modular, secure, and production-ready WordPress plugins engineered by Earthin.',
  },
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en">
      <body className="min-h-screen flex flex-col justify-between">
        <div>
          <Navbar />
          <main>{children}</main>
        </div>
        <Footer />
      </body>
    </html>
  );
}
