// scripts/wasm-loader.cjs
// On Android / Termux environments where native @next/swc-android-arm64 binaries
// are unavailable, flag the runtime to activate Next.js WASM SWC compilation.
if (process.platform === 'android') {
  process.versions.webcontainer = '1';
}
