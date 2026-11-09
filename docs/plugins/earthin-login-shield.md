# Earthin Login Shield

**Slug:** `earthin-login-shield`  
**Brand:** Earthin  
**Author:** Shaurya Joshi  
**Author URI:** https://plugins.earthin.online  
**Website:** https://plugins.earthin.online/plugins/earthin-login-shield  
**License:** GPL-2.0-or-later  
**Requires at least:** 5.8  
**Tested up to:** 6.7  
**Requires PHP:** 7.4  

---

## 1. Overview & Architecture

Earthin Login Shield protects WordPress administration against brute-force attacks and credential stuffing. It implements rate-limiting against `wp-login.php` authentication requests and provides a toggle to completely disable XML-RPC.

## 2. Security Mechanisms

1. **Pre-Authentication Lockout Guard**: Hooked into `authenticate` at priority 25, evaluating whether the client IP has an active lockout transient before verifying credentials.
2. **Exponential / Multi-Tier Lockouts**:
   - Short lockout (default: 20 minutes) after allowed retries (default: 4).
   - Long lockout (default: 24 hours) after repeated lockout threshold (default: 3).
3. **IP Allowlist & Denylist**:
   - Allowlist: Trusted administrator IPs bypass rate limits.
   - Denylist: Blocked IPs receive immediate hard rejection.
4. **XML-RPC Shield**: Hooked into `xmlrpc_enabled` returning `false` when activated.
5. **Audit Logging**: Keeps the last 50 failed authentication attempts with client IP, username, and timestamps.
