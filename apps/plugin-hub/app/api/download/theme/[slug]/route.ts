import { NextRequest, NextResponse } from 'next/server';
import { getThemeBySlug } from '@/lib/themes';
import path from 'path';
import fs from 'fs';

export async function GET(
  request: NextRequest,
  { params }: { params: { slug: string } }
) {
  const slug = params.slug;
  const theme = getThemeBySlug(slug);

  if (!theme) {
    return NextResponse.json({ error: 'Theme not found' }, { status: 404 });
  }

  // Check if static pre-packaged ZIP exists in public/downloads/themes
  const zipPath = path.join(process.cwd(), 'public', 'downloads', 'themes', `${slug}.zip`);
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

  // Fallback direct URL redirect
  return NextResponse.redirect(new URL(`/downloads/themes/${slug}.zip`, request.url), 302);
}
