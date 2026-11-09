import { NextRequest, NextResponse } from 'next/server';
import { db } from '@/lib/db';
import path from 'path';
import fs from 'fs';

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

  // Check if standalone ZIP exists in public/downloads/plugins
  const zipPath = path.join(process.cwd(), 'public', 'downloads', 'plugins', `${slug}.zip`);
  if (fs.existsSync(zipPath)) {
    const fileBuffer = fs.readFileSync(zipPath);
    return new NextResponse(fileBuffer, {
      status: 200,
      headers: {
        'Content-Type': 'application/zip',
        'Content-Disposition': `attachment; filename="${slug}.zip"`,
        'Cache-Control': 'public, max-age=3600',
      },
    });
  }

  // Return redirect to the plugin zip file or GitHub archive
  const targetUrl = plugin.downloadUrl || `/downloads/plugins/${slug}.zip`;
  return NextResponse.redirect(new URL(targetUrl, request.url), 302);
}
