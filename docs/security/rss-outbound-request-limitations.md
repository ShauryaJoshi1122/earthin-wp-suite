# RSS outbound-request security: current limitations

## Status

The RSS importer performs useful defense-in-depth checks, but the current implementation is **not fully protected against DNS rebinding**. Do not describe it as SSRF-proof or as providing complete SSRF protection.

The importer currently:
- Accepts only HTTP and HTTPS URLs validated by WordPress and the module.
- Rejects direct private/reserved IP addresses and selected internal hostnames.
- Resolves hostnames before the request and rejects private/reserved IPv4 results returned by the current lookup.
- Uses `wp_safe_remote_get()` with a 15-second timeout, redirects disabled, unsafe-URL rejection enabled, and a 2 MiB response limit.
- Parses only the response body returned by that single request and imports items as drafts.

These controls reduce risk, but they do not bind the network connection to the exact IP address that was checked.

## Why the remaining gap is real

WordPress core's `wp_http_validate_url()` currently resolves a hostname using `gethostbyname()`, which is an IPv4 lookup. The Requests transport subsequently connects using the hostname from the URL. That means validation-time DNS resolution and connection-time resolution are separate operations. A hostname can return different answers between those operations, and IPv6 address selection is not covered by an IPv4-only check.

The WordPress HTTP API uses the bundled Requests library and may select different transports. The cURL transport has a cURL-specific hook, while the fsockopen transport builds its socket destination from the hostname. A cURL-only address-pinning hook would therefore not establish a portable guarantee across all supported WordPress hosting configurations. Proxy configuration adds another transport boundary that must be considered.

References to the upstream implementation:
- [WordPress URL validation](https://github.com/WordPress/wordpress-develop/blob/trunk/src/wp-includes/http.php) — inspect `wp_http_validate_url()`.
- [WordPress Requests hook bridge](https://github.com/WordPress/wordpress-develop/blob/trunk/src/wp-includes/class-wp-http-requests-hooks.php) — maps Requests hooks into WordPress actions.
- [Requests cURL transport](https://github.com/WordPress/wordpress-develop/blob/trunk/src/wp-includes/Requests/src/Transport/Curl.php).
- [Requests fsockopen transport](https://github.com/WordPress/wordpress-develop/blob/trunk/src/wp-includes/Requests/src/Transport/Fsockopen.php).
- [WordPress HTTP API](https://developer.wordpress.org/reference/functions/wp_safe_remote_get/).

The exact implementation must be rechecked against each WordPress version supported by the plugin. Upstream `trunk` is evidence for the current development line, not a substitute for testing the minimum supported WordPress release.

## What a defensible complete mitigation requires

A future implementation should use one of these approaches:

1. **A dedicated, pinned transport:** resolve all A and AAAA answers, reject the request if any answer is non-public, then force the actual connection to one of the vetted addresses while preserving the original hostname for HTTP Host and TLS certificate/SNI verification. Every redirect hop must repeat the full validation and pinning process. The design must explicitly handle proxies and fail closed when the required transport is unavailable.
2. **A controlled outbound-fetch service:** send feed retrieval through a separately managed service or network boundary that enforces destination-address policy at connection time. This can be more portable than trying to retrofit pinning into every WordPress hosting environment.

Do not implement a cURL-only workaround and call it complete unless the plugin explicitly requires that transport, verifies it is actually selected, safely handles proxies, and fails closed on unsupported environments. Do not silently fall back to an unpinned transport.

## Required tests before claiming completion

- DNS results containing public IPv4 only.
- DNS results containing public IPv6 only.
- Mixed public and private A/AAAA answers.
- DNS lookup failure and empty answers.
- DNS answers changing between validation and connection.
- Redirects to private IPv4 and IPv6 destinations.
- Redirect loops and excessive redirect chains.
- cURL, streams/fsockopen, and configured proxy behavior.
- TLS hostname verification when connecting to a pinned IP.
- Response-size and timeout enforcement on the actual transport.
- WordPress integration tests against the minimum and current supported WordPress versions.

Standalone stubs that merely inspect request arguments are useful unit tests, but they do not prove the actual socket destination or prevent DNS rebinding.

## Current product claim

Use wording such as:

> RSS feed requests include URL validation, unsafe-destination checks, disabled automatic redirects, timeouts, and response-size limits. DNS-rebinding resistance is not yet guaranteed across all WordPress HTTP transports.

Do not use wording such as "SSRF-proof", "complete SSRF protection", or "DNS-rebinding protected" until connection-time destination enforcement is implemented and tested.
