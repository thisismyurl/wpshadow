# Diagnostic: HTTPS/SSL Enabled

**Labels:** `diagnostic`, `tier-1`, `security`  
**Milestone:** Diagnostic System Rebuild  
**Priority:** Critical (Tier 1)

---

## Overview

**Slug:** `https-enabled`  
**Category:** Security  
**Threat Level:** 85 (Critical)  
**Auto-Fixable:** No (requires SSL certificate and server configuration)  
**Test Type:** Direct (< 0.1s)

### What to Check
Detect if the site is accessible via HTTPS, if HTTPS is enforced for all pages, and if WordPress is configured to use HTTPS URLs.

### Why It Matters
- **Data Privacy:** HTTP transmits all data in plain text (passwords, personal info, etc.)
- **Man-in-the-Middle Attacks:** Attackers can intercept and modify unencrypted traffic
- **SEO Penalty:** Google ranks HTTPS sites higher than HTTP sites
- **Browser Warnings:** Modern browsers show "Not Secure" warnings for HTTP sites
- **User Trust:** Visitors expect padlock icon; lack of it reduces conversion rates
- **Legal Compliance:** GDPR and other regulations require encrypted data transmission

---

## Diagnostic Thresholds

### Critical (Threat Level 85)
**Condition:** Site is HTTP only (no SSL certificate)  
**Site Health:** `critical`  
**Message:**
```
Your site is not using HTTPS (SSL/TLS encryption). All data transmitted between your site and visitors—including passwords, personal information, and payment details—is sent in plain text and can be intercepted by attackers. Install an SSL certificate immediately.
```

### Recommended (Threat Level 60)
**Condition:** SSL certificate exists BUT WordPress URLs still use HTTP  
**Site Health:** `recommended`  
**Message:**
```
Your server has an SSL certificate installed, but WordPress is still configured to use HTTP URLs. Visitors can access your site via HTTPS, but WordPress is not enforcing it. Update your site and home URLs to use HTTPS.
```

### Recommended (Threat Level 45)
**Condition:** WordPress uses HTTPS BUT redirect is missing (HTTP still accessible)  
**Site Health:** `recommended`  
**Message:**
```
Your WordPress URLs use HTTPS, but your site is still accessible via HTTP without automatic redirect. This creates duplicate content issues for SEO and exposes users to downgrade attacks. Configure your server to redirect all HTTP traffic to HTTPS.
```

### Good (No Finding)
**Condition:** HTTPS enforced sitewide, WordPress configured correctly  
**Returns:** `null` (no finding)

---

## Implementation Details

### File Location
```
includes/diagnostics/tests/security/class-diagnostic-https-enabled.php
```

### Detection Method
```php
// Check 1: Is site URL using HTTPS?
$site_url = get_option( 'siteurl' );
$home_url = get_option( 'home' );
$uses_https = ( strpos( $site_url, 'https://' ) === 0 && strpos( $home_url, 'https://' ) === 0 );

// Check 2: Is SSL certificate valid?
$ssl_available = $this->check_ssl_certificate();

// Check 3: Is HTTP redirected to HTTPS?
$http_redirects = $this->check_http_redirect();

// Logic
if ( ! $ssl_available ) {
    // Critical: No SSL at all
    return $this->create_finding( /* threat_level: 85, no_ssl */ );
}

if ( ! $uses_https ) {
    // Recommended: SSL exists but not configured in WordPress
    return $this->create_finding( /* threat_level: 60, ssl_not_configured */ );
}

if ( ! $http_redirects ) {
    // Recommended: HTTPS configured but HTTP still accessible
    return $this->create_finding( /* threat_level: 45, no_redirect */ );
}

return null; // All good
```

### Helper Methods

**`check_ssl_certificate()`** - Test if SSL cert is valid:
```php
private function check_ssl_certificate() {
    $site_url = get_option( 'siteurl' );
    $https_url = str_replace( 'http://', 'https://', $site_url );
    
    // Use wp_remote_head to test HTTPS
    $response = wp_remote_head( $https_url, array(
        'timeout'   => 5,
        'sslverify' => true,
    ) );
    
    // If no WP_Error and 200 response, SSL is valid
    return ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200;
}
```

**`check_http_redirect()`** - Test if HTTP redirects to HTTPS:
```php
private function check_http_redirect() {
    $site_url = get_option( 'siteurl' );
    $http_url = str_replace( 'https://', 'http://', $site_url );
    
    $response = wp_remote_head( $http_url, array(
        'timeout'     => 5,
        'redirection' => 0, // Don't follow redirects, just check for them
        'sslverify'   => false,
    ) );
    
    if ( is_wp_error( $response ) ) {
        return false;
    }
    
    $code = wp_remote_retrieve_response_code( $response );
    
    // 301 or 302 redirect
    return in_array( $code, array( 301, 302 ), true );
}
```

### Related Treatment
**Treatment Slug:** `https-enabled` (partial)  
**Auto-Fixable:** Partially  
**What can be automated:**
- Updating WordPress site and home URLs to HTTPS
- Adding redirect rules to .htaccess (if Apache)

**What cannot be automated:**
- Installing SSL certificate (requires server access or hosting panel)
- Configuring nginx (no .htaccess equivalent)

**Manual Steps Required:**
1. Install SSL certificate via hosting panel or Let's Encrypt
2. Test HTTPS access
3. Use WPShadow treatment to update WordPress URLs and add redirects
4. Update hardcoded HTTP links in database (search/replace)

---

## Required Data Points

### WordPress Configuration
- `siteurl` option (WordPress Address URL)
- `home` option (Site Address URL)
- `FORCE_SSL_ADMIN` constant (if defined)

### Server Configuration
- SSL certificate validity (test via wp_remote_head)
- HTTP to HTTPS redirect status (test via wp_remote_head)
- Server type (Apache vs nginx) for treatment logic

### Additional Context
- Mixed content warnings (check browser console)
- Count of HTTP URLs in database content
- Certificate expiration date (if accessible)

---

## Messages (Plain Language)

### Title (No SSL)
```
HTTPS Not Enabled - Site Is Not Secure
```

### Title (SSL Exists But Not Configured)
```
SSL Certificate Installed But Not Active
```

### Title (No Redirect)
```
HTTP to HTTPS Redirect Missing
```

### Short Description (No SSL)
```
Your site is not using HTTPS encryption, which exposes visitor data to interception and triggers browser security warnings.
```

### Short Description (SSL Not Configured)
```
Your server has an SSL certificate, but WordPress is still configured to use HTTP URLs instead of HTTPS.
```

### Short Description (No Redirect)
```
Your site uses HTTPS, but HTTP traffic isn't automatically redirected, creating security and SEO issues.
```

### Long Description (No SSL)
```
Your site is currently accessible only via HTTP (unencrypted connection). This means:

**Security Risks:**
• All data transmitted is in plain text (passwords, emails, form submissions)
• Attackers on public WiFi can intercept and read this data
• Man-in-the-middle attacks can modify content in transit
• No verification that visitors are connecting to your real site

**User Experience Impact:**
• Modern browsers show "Not Secure" warning in address bar
• Reduces visitor trust and conversions
• Payment processors won't work (Stripe, PayPal require HTTPS)
• Can't use modern web features (geolocation, camera access, etc.)

**SEO & Compliance:**
• Google ranks HTTPS sites higher than HTTP sites
• GDPR requires encrypted data transmission
• PCI compliance impossible without HTTPS

**Good News:** Most hosting providers offer free SSL certificates via Let's Encrypt. Installation takes 5-10 minutes.
```

### Long Description (SSL Not Configured)
```
Your server has a valid SSL certificate installed, but WordPress is still configured to use HTTP URLs.

**Current Situation:**
• Visitors CAN access your site via HTTPS if they type "https://" manually
• But WordPress links all use "http://" internally
• This creates mixed content warnings in browsers
• Search engines may index HTTP version instead of HTTPS

**Impact:**
• Confusing user experience (sometimes secure, sometimes not)
• Mixed content warnings (broken padlock icon)
• SEO dilution (two versions of every page)
• Users may share HTTP links, bypassing security

**The Fix:** Update your WordPress site and home URLs to use "https://" instead of "http://". WPShadow can do this automatically with one click.
```

### Long Description (No Redirect)
```
Your site uses HTTPS, but HTTP traffic is not automatically redirected.

**Current Situation:**
• Both http://yoursite.com and https://yoursite.com work
• Users landing on HTTP version stay unencrypted
• Old bookmarks and links remain insecure

**Impact:**
• **SEO:** Duplicate content penalty (same content on two URLs)
• **Security:** Users on HTTP links remain vulnerable
• **Downgrade Attacks:** Attackers can force HTTP access
• **Mixed Signals:** Some pages secure, some not

**The Fix:** Add a server redirect rule that automatically sends all HTTP traffic to HTTPS. WPShadow can add this to your .htaccess file automatically (Apache servers).
```

### What to Do About It

**For No SSL:**
```
**Step 1: Install SSL Certificate**

Most hosting providers offer free SSL via Let's Encrypt:
• cPanel: "SSL/TLS" → "Let's Encrypt"
• Plesk: "SSL/TLS Certificates" → "Install Free Certificate"
• Managed WordPress: Usually automatic (check with support)

**Step 2: Update WordPress URLs**

Once SSL is installed, click "Apply Treatment" to:
• Update WordPress site and home URLs to HTTPS
• Add redirect rules to send HTTP → HTTPS
• Fix common mixed content issues

**Need Help?** See our complete SSL setup guide: https://wpshadow.com/kb/security-https-enabled#setup-guide
```

**For SSL Not Configured:**
```
Click "Apply Treatment" to automatically:
• Update WordPress site and home URLs to HTTPS
• Add redirect rules (if Apache server)
• Test HTTPS accessibility

**Manual Alternative:**
1. Go to **Settings → General**
2. Change "WordPress Address (URL)" to https://yoursite.com
3. Change "Site Address (URL)" to https://yoursite.com
4. Click "Save Changes"
5. You'll be logged out - log back in via HTTPS

**Important:** Make sure HTTPS works BEFORE changing these URLs (test by visiting https://yoursite.com manually).
```

**For No Redirect:**
```
Click "Apply Treatment" to add redirect rules to your .htaccess file (Apache servers).

**Manual Alternative (Apache):**
Add this to the top of your .htaccess file:
```
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

**For nginx:** Ask your hosting provider or add this to server config:
```
server {
    listen 80;
    server_name yoursite.com;
    return 301 https://$server_name$request_uri;
}
```
```

---

## KB Article

**URL:** `https://wpshadow.com/kb/security-https-enabled`

**Content Outline:**
1. Why HTTPS matters (security, SEO, user trust)
2. How SSL/TLS encryption works (simplified)
3. How to install SSL certificate (by hosting provider)
4. How to update WordPress URLs (step-by-step)
5. How to add redirect rules (Apache and nginx)
6. Fixing mixed content warnings
7. Database search/replace for hardcoded HTTP
8. Testing SSL certificate validity
9. Certificate renewal (Let's Encrypt auto-renewal)
10. Troubleshooting common issues
11. Video tutorial: Complete HTTPS setup

---

## Site Health Bridge Mapping

| Threat Level | Site Health Status | Badge Color |
|--------------|-------------------|-------------|
| 85 (No SSL) | `critical` | Red |
| 60 (Not Configured) | `recommended` | Orange |
| 45 (No Redirect) | `recommended` | Orange |
| 0 (Good) | `good` | Green |

---

## Implementation Checklist

### Code Requirements
- [ ] File created: `includes/diagnostics/tests/security/class-diagnostic-https-enabled.php`
- [ ] Extends `Diagnostic_Base`
- [ ] Namespace: `WPShadow\Diagnostics`
- [ ] Protected properties: `$slug`, `$title`, `$description`, `$category`, `$kb_url`
- [ ] `check()` method returns `array|null`
- [ ] Helper method: `check_ssl_certificate()` using wp_remote_head
- [ ] Helper method: `check_http_redirect()` using wp_remote_head
- [ ] Return format includes: `id`, `title`, `description`, `severity`, `threat_level`, `site_health_status`, `category`, `kb_url`, `auto_fixable` (partial), `treatment_slug`
- [ ] Test type: `direct` (config + remote check, < 1s)
- [ ] Three severity tiers based on SSL status

### Message Quality
- [ ] Three distinct titles for three scenarios
- [ ] Short descriptions explain the specific issue
- [ ] Long descriptions explain WHY with real-world impact
- [ ] Includes security, SEO, and UX impact
- [ ] "What to Do" varies by scenario (install SSL vs configure vs add redirect)
- [ ] Code examples provided for .htaccess and nginx
- [ ] Warnings about testing HTTPS before changing URLs
- [ ] All strings use `__()` for i18n with 'wpshadow' text domain

### Threat Level Logic
- [ ] Critical (85): No SSL certificate at all
- [ ] Recommended (60): SSL exists but WordPress uses HTTP URLs
- [ ] Recommended (45): WordPress uses HTTPS but no redirect from HTTP
- [ ] Good (0): HTTPS fully configured and enforced
- [ ] Returns `null` when no issue found
- [ ] Threat level matches severity of risk

### Site Health Integration
- [ ] Threat level 85 → `site_health_status` = 'critical'
- [ ] Threat level 60/45 → `site_health_status` = 'recommended'
- [ ] Uses threshold constants correctly

### Category & Metadata
- [ ] Category: `security` (matches dashboard gauge)
- [ ] KB URL follows format: `https://wpshadow.com/kb/security-https-enabled`
- [ ] Auto-fixable: `true` (partial - can update URLs and add redirects)
- [ ] Treatment slug: `https-enabled`

### Documentation
- [ ] Class-level PHPDoc explaining purpose, three detection scenarios
- [ ] Method-level PHPDoc for `check()` with `@return array|null`
- [ ] Method-level PHPDoc for helper methods
- [ ] Inline comments explaining remote check logic
- [ ] Document what can/cannot be automated

### Testing
- [ ] PHP syntax validated: `php -l class-diagnostic-https-enabled.php`
- [ ] PHPCS passes (WordPress coding standards)
- [ ] Tested on HTTP-only site - should return critical finding
- [ ] Tested with SSL but HTTP URLs - should return recommended finding
- [ ] Tested with HTTPS URLs but no redirect - should return recommended finding
- [ ] Tested with full HTTPS enforcement - should return null
- [ ] Tested on wpshadow.com (document result: pass/fail)
- [ ] Remote checks handle wp_remote_head errors gracefully

### No Stubs Policy
- [ ] No TODO comments
- [ ] No placeholder functions
- [ ] No commented-out code blocks
- [ ] All logic fully implemented
- [ ] Document partial auto-fix limitations clearly

---

## Related Documentation
- [DIAGNOSTIC_AND_TREATMENT_SPECIFICATION.md](../DIAGNOSTIC_AND_TREATMENT_SPECIFICATION.md)
- [DIAGNOSTICS_IMPLEMENTATION_TRACKER.md](../DIAGNOSTICS_IMPLEMENTATION_TRACKER.md)
- [ARCHITECTURE.md](../ARCHITECTURE.md)
- Mozilla SSL Configuration Generator: https://ssl-config.mozilla.org/

---

**Issue Created:** 2026-01-26  
**Specification Version:** 1.0  
**Estimated Implementation Time:** 4-5 hours
