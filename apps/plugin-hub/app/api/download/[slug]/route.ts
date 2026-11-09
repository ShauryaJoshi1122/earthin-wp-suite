import { NextRequest, NextResponse } from 'next/server';
import { db } from '@/lib/db';

export async function GET(
  request: NextRequest,
  { params }: { params: { slug: string } }
) {
  const slug = params.slug;
  const plugin = db.getPluginBySlug(slug);

  if (!plugin) {
    return NextResponse.json({ error: 'Plugin not found' }, { status: 404 });
  }

  // Check if admin allows download for this plugin
  if (plugin.allowDownload === false) {
    return NextResponse.json(
      {
        error: 'Download restricted',
        message: `Downloads for ${plugin.name} have been temporarily restricted by the administrator.`,
      },
      { status: 403 }
    );
  }

  // Increment download counter
  db.incrementDownloadCount(slug);

  // Return redirect to the plugin zip file or GitHub archive
  const targetUrl = plugin.downloadUrl || 'https://github.com/ShauryaJoshi1122/earthin-wp-suite/archive/refs/heads/main.zip';
  return NextResponse.redirect(new URL(targetUrl), 302);
}
