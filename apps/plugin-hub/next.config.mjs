// On Android / Termux where native SWC binaries are not published,
// instruct Next.js to use the WebAssembly SWC compiler fallback.
if (process.platform === 'android') {
  process.versions.webcontainer = '1';
}

/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  poweredByHeader: false,
  experimental: {
    useWasmBinary: true,
  },
};

export default nextConfig;
