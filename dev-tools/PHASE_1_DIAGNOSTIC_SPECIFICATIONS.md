# Phase 1 Diagnostic Specifications

**Date:** February 4, 2026
**Status:** ✅ Triaged & Ready for Implementation
**Total Diagnostics:** 47 (9 categories)

> **This document contains the complete technical specifications for all Phase 1 critical diagnostics, based on triage decisions made with the project owner.**

---

## Implementation Priority

**Build Order (Quick Wins First):**
1. **Email Deliverability** (9 diagnostics)
2. **SSL/TLS Certificates** (4 diagnostics)
3. **Hosting Environment** (6 diagnostics)
4. Database Health (5 diagnostics)
5. File System Permissions (5 diagnostics)
6. Backup & Recovery (6 diagnostics)
7. DNS Configuration (4 diagnostics)
8. Downtime Prevention (4 diagnostics)
9. Real User Monitoring (4 diagnostics)

---

## Category 1: Email Deliverability (9 Diagnostics)

**Location:** `includes/diagnostics/tests/monitoring/`
**Auto-Fix:** Information only (no auto-fix treatments)
**Scan Frequency:** Tiered - SMTP connectivity every scan, DNS checks cached 24h

### 1.1 SMTP Server Connectivity

**Specification:**
- Test configured port first (from wp-config.php or active SMTP plugins)
- Check both wp-config.php constants AND popular plugins (WP Mail SMTP, Easy WP SMTP, Post SMTP)
- If configured port fails, test fallback ports (25, 465, 587)
- Timeout threshold: 10 seconds
- Return finding if connection fails or times out

**WordPress APIs:**
```php
// Check wp-config.php constants
defined('WPMS_ON') && defined('WPMS_SMTP_HOST')

// Check WP Mail SMTP plugin
get_option('wp_mail_smtp')

// Test connection
fsockopen($host, $port, $errno, $errstr, 10)
```

### 1.2 SMTP Authentication

**Specification:**
- Validate SMTP username/password if configured
- Test authentication timeout (10 seconds)
- Check if auth method matches server requirements (PLAIN, LOGIN, CRAM-MD5)

### 1.3 SPF Record Published

**Specification:**
- Check both site URL domain AND "from" email address domain
- Query DNS for SPF record: `checkdnsrr($domain, 'TXT')`
- Validate SPF syntax
- If missing: Flag + provide KB article link with setup instructions
- Cache result: 24 hours

### 1.4 DKIM Records Configured

**Specification:**
- Check both site URL domain AND "from" email address domain
- Verify DKIM records exist in DNS
- Validate public key format
- Check selector configuration
- If missing: Flag + provide KB article link
- Cache result: 24 hours

### 1.5 DMARC Policy Set

**Specification:**
- Check both site URL domain AND "from" email address domain
- Query DNS for DMARC policy
- Validate policy alignment with SPF/DKIM
- If missing: Flag + provide KB article link
- Cache result: 24 hours

### 1.6 Email Bounce Rate Tracking

**Specification:**
- Store bounce data in Activity Logger (not custom table or options)
- Track bounce rate over time
- Threshold: >5% triggers warning (industry standard)
- Calculate: (bounced emails / total sent) * 100

**Activity Logger Integration:**
```php
\WPShadow\Core\Activity_Logger::log('email_bounced', array(
    'recipient' => $email,
    'reason' => $bounce_reason,
    'timestamp' => time(),
));
```

### 1.7 Transactional Email Delivery

**Specification:**
- Send test email to admin using `wp_mail()`
- Track delivery success/failure
- Measure delivery time
- Log result to Activity Logger

### 1.8 From Address Whitelist

**Specification:**
- Validate "from" address matches domain
- Check for spoofing risks (from domain ≠ site domain)
- Verify sender policy compliance

### 1.9 Email Logging Enabled

**Specification:**
- Check if email logging is active (plugin or custom)
- Validate log retention policy
- Warn if no logging available

---

## Category 2: Database Health (5 Diagnostics)

**Location:** `includes/diagnostics/tests/performance/`
**Auto-Fix:** Information only
**Scan Frequency:** Cached 24 hours, run during off-hours (3am local time preferred)

### 2.1 Database Integrity Check

**Specification:**
- Check ALL tables in database (core + plugins + themes + custom)
- Use MySQL `CHECK TABLE` command
- Run during off-hours (3am local time)
- Cache result: 24 hours
- Report: table name, status, message

**Implementation:**
```php
global $wpdb;
$tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);
foreach ($tables as $table) {
    $result = $wpdb->get_row("CHECK TABLE {$table[0]}", ARRAY_A);
    // Log if status != 'OK'
}
```

### 2.2 Slow Query Detection

**Specification:**
- Enable MySQL slow query log temporarily
- Threshold: >1 second = slow query
- Analyze log after scan completes
- Report count of slow queries (not full SQL for security)
- Cache result: 24 hours

**Implementation:**
```php
// Enable slow query log
$wpdb->query("SET GLOBAL slow_query_log = 'ON'");
$wpdb->query("SET GLOBAL long_query_time = 1");

// Run diagnostics...

// Disable after scan
$wpdb->query("SET GLOBAL slow_query_log = 'OFF'");
```

### 2.3 Table Optimization Status

**Specification:**
- Check for fragmented tables
- Analyze AUTO_INCREMENT values
- Check table overhead
- Cache result: 24 hours

### 2.4 Database Size and Growth Trend

**Specification:**
- Calculate total database size
- Track growth over time (Activity Logger)
- Warn if growth is unusually fast (>20% per month)

### 2.5 Backup Restoration Test

**Specification:**
- Verify backup files exist and are readable
- Check file integrity (not corrupted)
- Validate backup contains expected data (check for key files/tables)
- DO NOT attempt actual restoration (too risky)
- "Recent" = within last 7 days

---

## Category 3: SSL/TLS Certificates (4 Diagnostics)

**Location:** `includes/diagnostics/tests/security/`
**Auto-Fix:** Information only
**Scan Frequency:** Cached 24 hours

### 3.1 SSL Certificate Expiration

**Specification:**
- Two-tier warnings:
  - Warning: 60 days before expiration
  - Critical: 30 days before expiration
- Check current domain certificate
- Cache result: 24 hours

**Implementation:**
```php
$url = get_site_url();
$stream = stream_context_create(array("ssl" => array("capture_peer_cert" => true)));
$read = fopen($url, "rb", false, $stream);
$cert = stream_context_get_params($read);
$certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
$valid_until = $certinfo['validTo_time_t'];
$days_remaining = floor(($valid_until - time()) / 86400);
```

### 3.2 Certificate Chain Validation

**Specification:**
- Verify entire certificate chain
- Check if certificate is trusted (not self-signed in production)
- Handle self-signed certificates:
  - Detect environment (dev/staging vs production)
  - Error in production, warning in dev/staging
  - If uncertain, issue warning (not error)

**Environment Detection:**
```php
// Production indicators
$is_production = (
    !defined('WP_DEBUG') || !WP_DEBUG
) && (
    strpos(get_site_url(), 'localhost') === false &&
    strpos(get_site_url(), '.local') === false &&
    strpos(get_site_url(), 'staging') === false &&
    strpos(get_site_url(), 'dev.') === false
);
```

### 3.3 SSL Protocol Version

**Specification:**
- Check TLS version (should be TLS 1.2 or 1.3)
- Warn if using TLS 1.0 or 1.1 (deprecated)
- Error if using SSLv2 or SSLv3 (insecure)

### 3.4 Mixed Content Detection

**Specification:**
- Scan homepage for HTTP resources loaded on HTTPS page
- Check images, scripts, stylesheets
- Report count of mixed content issues

---

## Category 4: Backup & Recovery (6 Diagnostics)

**Location:** `includes/diagnostics/tests/monitoring/`
**Auto-Fix:** Information only
**Scan Frequency:** Every scan (fast check)

### 4.1 Backup Configured and Running

**Specification:**
- Check for backup plugins (top 10):
  1. UpdraftPlus
  2. BackWPup
  3. VaultPress
  4. Jetpack Backup
  5. All-in-One WP Migration
  6. Duplicator
  7. BackupBuddy
  8. WP Time Capsule
  9. BlogVault
  10. ManageWP
- Also check manual backup directories:
  - `wp-content/backups/`
  - `wp-content/uploads/backups/`
  - `wp-content/ai1wm-backups/`
  - Custom backup paths

### 4.2 Backup Frequency

**Specification:**
- Check backup plugin schedule
- Recommend: Daily for high-traffic, weekly minimum for low-traffic
- "Recent" backup = within last 7 days

### 4.3 Backup Retention Policy

**Specification:**
- Check how many backup versions are stored
- Recommend: Minimum 7 versions (weekly for a month)

### 4.4 Database Backup Working

**Specification:**
- Verify database backup files exist
- Check file size (should be >1KB)
- Verify created within last 7 days
- Validate file integrity (safely check if readable)

### 4.5 File Backup Working

**Specification:**
- Verify file backup archives exist
- Check file size (should be significant)
- Created within last 7 days
- Validate integrity (safely check if readable, not corrupted)

### 4.6 Offsite Backup Storage

**Specification:**
- Check if backups are stored remotely (not just locally)
- Look for cloud storage integrations:
  - AWS S3
  - Google Drive
  - Dropbox
  - Microsoft OneDrive
  - Rackspace Cloud
- Warn if only local backups found

---

## Category 5: File System Permissions (5 Diagnostics)

**Location:** `includes/diagnostics/tests/security/`
**Auto-Fix:** Information only
**Scan Frequency:** Every scan (fast check)

### 5.1 wp-content Directory Writable

**Specification:**
- Check if `wp-content/` is writable
- Environment-aware permission recommendations:
  - Shared hosting: Often needs 775 or 777
  - VPS/Dedicated: Prefer 755 or 750
  - Detect server setup and recommend accordingly

**Permission Check:**
```php
$path = WP_CONTENT_DIR;
$is_writable = is_writable($path);
$perms = substr(sprintf('%o', fileperms($path)), -3);
$owner = fileowner($path);
$group = filegroup($path);
```

### 5.2 Uploads Directory Permissions

**Specification:**
- Check `wp-content/uploads/` permissions
- Must be writable for media uploads
- Environment-aware recommendations

### 5.3 Plugins Directory Permissions

**Specification:**
- Check `wp-content/plugins/` permissions
- Should be writable for plugin updates
- Environment-aware recommendations

### 5.4 Themes Directory Permissions

**Specification:**
- Check `wp-content/themes/` permissions
- Should be writable for theme updates
- Environment-aware recommendations

### 5.5 Logs Directory Writable

**Specification:**
- Check if logs directory exists and is writable
- Common locations:
  - `wp-content/debug.log`
  - `wp-content/logs/`
  - Custom log paths
- Create directory if needed for diagnostics

---

## Category 6: Hosting Environment (6 Diagnostics)

**Location:** `includes/diagnostics/tests/settings/`
**Auto-Fix:** Information only
**Scan Frequency:** Every scan (fast check)

### 6.1 PHP Version Check

**Specification:**
- Minimum required: PHP 8.1+
- Check for End of Life (EOL) status
- Warn if below 8.1 OR if version is EOL
- Also suggest upgrading if not on latest stable

**Implementation:**
```php
$php_version = PHP_VERSION;
$min_version = '8.1.0';
$is_below_minimum = version_compare($php_version, $min_version, '<');

// Check EOL (manually maintained list or API)
$eol_versions = array('7.4', '8.0'); // Update as needed
$is_eol = in_array(PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION, $eol_versions);
```

### 6.2 Required PHP Extensions

**Specification:**
- Required extensions (error if missing):
  - mysqli
  - GD
  - curl
- Recommended extensions (warning if missing):
  - mbstring
  - zip
  - imagick
  - opcache
  - intl
  - exif

**Check:**
```php
$required = array('mysqli', 'gd', 'curl');
$missing = array();
foreach ($required as $ext) {
    if (!extension_loaded($ext)) {
        $missing[] = $ext;
    }
}
```

### 6.3 Server Memory Allocation

**Specification:**
- Minimum: 128M
- Recommended: 256M
- Check `memory_limit` in php.ini
- Compare to `WP_MEMORY_LIMIT` and `WP_MAX_MEMORY_LIMIT`

### 6.4 PHP Max Execution Time

**Specification:**
- Minimum: 30 seconds
- Recommended: 60 seconds for admin, 30 for frontend
- Check `max_execution_time` in php.ini

### 6.5 Upload Size Limit

**Specification:**
- Minimum: 64M
- Recommended: 128M+
- Check `upload_max_filesize` and `post_max_size`

### 6.6 MySQL/MariaDB Version

**Specification:**
- Minimum: MySQL 5.7+ or MariaDB 10.2+
- Check for EOL versions
- Recommend upgrading if not on supported version

**Check:**
```php
global $wpdb;
$version = $wpdb->get_var("SELECT VERSION()");
```

---

## Category 7: DNS Configuration (4 Diagnostics)

**Location:** `includes/diagnostics/tests/settings/`
**Auto-Fix:** Information only
**Scan Frequency:** Cached 24 hours

### 7.1 DNS Record Validation

**Specification:**
- Check comprehensive DNS records:
  - A records (IPv4)
  - AAAA records (IPv6)
  - MX records (email routing)
  - CNAME records (if applicable)
  - TXT records (SPF, DKIM, DMARC, verification)
- Validate all resolve correctly
- Cache result: 24 hours

**Implementation:**
```php
$domain = parse_url(get_site_url(), PHP_URL_HOST);

// A record
$a_record = gethostbyname($domain);

// MX record
$mx_records = array();
getmxrr($domain, $mx_records);

// DNS records
$records = dns_get_record($domain, DNS_A + DNS_AAAA + DNS_MX + DNS_CNAME + DNS_TXT);
```

### 7.2 DNS Propagation Check

**Specification:**
- Query multiple DNS servers
- Compare results for consistency
- Flag if propagation incomplete

### 7.3 Subdomain Configuration

**Specification:**
- Check if www and non-www versions resolve
- Verify proper redirects are in place
- Check for canonical URL consistency

### 7.4 Nameserver Configuration

**Specification:**
- Verify nameservers are responding
- Check for multiple nameservers (redundancy)
- Validate nameserver authority for domain

---

## Category 8: Downtime Prevention (4 Diagnostics)

**Location:** `includes/diagnostics/tests/monitoring/`
**Auto-Fix:** Information only
**Scan Frequency:** Continuous (for heartbeat), reported in scans

### 8.1 Uptime Monitoring

**Specification:**
- Build local heartbeat system (store uptime data in Activity Logger)
- Also check for external monitoring services:
  - UptimeRobot
  - Pingdom
  - StatusCake
  - Site24x7
- **Special handling:** If WPShadow Guardian/Cloud is active, integrate with that
- Store uptime percentage over time
- "Downtime" = HTTP 500/502/503/504 OR timeout

**Implementation:**
```php
// Local heartbeat via WP Cron
add_action('wpshadow_heartbeat', function() {
    $start = microtime(true);
    $response = wp_remote_get(home_url());
    $duration = microtime(true) - $start;

    $status = wp_remote_retrieve_response_code($response);
    $is_up = ($status >= 200 && $status < 400);

    \WPShadow\Core\Activity_Logger::log('heartbeat', array(
        'status' => $is_up ? 'up' : 'down',
        'response_code' => $status,
        'response_time' => $duration,
    ));
});
```

### 8.2 Error Rate Monitoring

**Specification:**
- Track PHP errors, warnings, notices
- Monitor HTTP 500 errors
- Calculate error rate (errors per 1000 requests)
- Threshold: >1% error rate = warning

### 8.3 Resource Limits Approaching

**Specification:**
- Monitor memory usage approaching limits
- Check disk space (warn if <10% free)
- Track CPU usage spikes
- Warn before limits are hit

### 8.4 Critical Plugin/Theme Conflicts

**Specification:**
- Detect fatal errors from plugins/themes
- Monitor error log for plugin conflicts
- Track recently activated plugins with errors

---

## Category 9: Real User Monitoring (4 Diagnostics)

**Location:** `includes/diagnostics/tests/performance/`
**Auto-Fix:** Information only
**Scan Frequency:** Continuous collection, reported in scans

### 9.1 Page Load Time Tracking

**Specification:**
- Server-side timing by default (no consent needed)
- Client-side Navigation Timing API if user opts in (GDPR compliant)
- Store data in Activity Logger
- Report: median, 90th percentile, 99th percentile load times

**Server-side:**
```php
// Track PHP execution time
add_action('shutdown', function() {
    $duration = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];

    \WPShadow\Core\Activity_Logger::log('page_load_server', array(
        'url' => $_SERVER['REQUEST_URI'],
        'duration' => $duration,
        'memory' => memory_get_peak_usage(),
    ));
});
```

**Client-side (opt-in):**
```javascript
// Only if user consented to tracking
if (window.performance && wpShadowTrackingConsent) {
    window.addEventListener('load', function() {
        const perfData = performance.getEntriesByType('navigation')[0];
        // Send to Activity Logger via AJAX
    });
}
```

### 9.2 Core Web Vitals

**Specification:**
- Track Largest Contentful Paint (LCP)
- Track First Input Delay (FID)
- Track Cumulative Layout Shift (CLS)
- Requires client-side tracking (opt-in only)
- Privacy-first: server-side estimation as fallback

### 9.3 Device and Browser Breakdown

**Specification:**
- Track user agent strings (anonymized)
- Report: desktop vs mobile vs tablet
- Report: browser distribution
- Requires user consent for detailed tracking

### 9.4 Geographic Performance

**Specification:**
- Track performance by geographic region (if using CDN)
- Report slowest regions
- Requires external service or CDN integration
- Privacy-first: no IP tracking without consent

---

## Global Implementation Standards

### Security (Commandment #10: Beyond Pure)
- All diagnostics follow security best practices
- No raw SQL (use `$wpdb->prepare()`)
- Nonce verification for all actions
- Capability checks (`manage_options`)
- Sanitize all input, escape all output

### Privacy (Commandment #10: Beyond Pure)
- No tracking without consent
- Server-side default, client-side opt-in
- Data encrypted and anonymized
- GDPR compliant by default
- Clear privacy policy links

### Performance
- Tiered caching:
  - Fast checks: Every scan (< 1 second)
  - Medium checks: Cached 1 hour
  - Expensive checks: Cached 24 hours, run off-hours
- Use transients for caching
- Batch database queries where possible

### User Experience (Commandments #1, #8)
- Helpful error messages explaining WHY
- Always link to KB articles
- Show impact: "This could cause X"
- Provide solutions, not just problems
- Inspire confidence with clear explanations

### Accessibility (CANON Pillar 🌍)
- All dashboard UI keyboard navigable
- Screen reader compatible
- WCAG 2.1 AA compliant
- Clear, plain language
- No color-only information

### Documentation (CANON Pillar 🎓)
- Every diagnostic has KB article
- Text + video format
- Real-world examples
- Step-by-step fixes
- Multiple learning modalities

### Internationalization (CANON Pillar 🌐)
- All strings translatable
- Use text domain: `'wpshadow'`
- RTL language support
- Locale-aware date/time/numbers
- No idioms or colloquialisms

---

## Testing Requirements

### Before Shipping Each Diagnostic

**Functional Testing:**
- [ ] Diagnostic detects issue correctly
- [ ] Returns proper finding array structure
- [ ] KB link works and is helpful
- [ ] Caching works as specified
- [ ] Performance acceptable (< 1 second for fast checks)

**Security Testing:**
- [ ] No SQL injection vulnerabilities
- [ ] Proper nonce verification
- [ ] Capability checks in place
- [ ] Input sanitized
- [ ] Output escaped

**Accessibility Testing:**
- [ ] Keyboard navigation works
- [ ] Screen reader compatible
- [ ] WCAG AA color contrast
- [ ] Clear focus indicators

**Privacy Testing:**
- [ ] No tracking without consent
- [ ] Data properly anonymized
- [ ] GDPR compliant
- [ ] Privacy policy linked

**Code Quality:**
- [ ] PHPCS compliant (WordPress-Extra)
- [ ] Properly documented (PHPDoc)
- [ ] Follows coding standards
- [ ] Unit tests written (if applicable)

---

## File Structure

```
includes/diagnostics/tests/
├── monitoring/
│   ├── class-diagnostic-smtp-connectivity.php
│   ├── class-diagnostic-spf-record.php
│   ├── class-diagnostic-dkim-record.php
│   ├── class-diagnostic-dmarc-policy.php
│   ├── class-diagnostic-email-bounce-rate.php
│   ├── class-diagnostic-backup-configured.php
│   ├── class-diagnostic-backup-frequency.php
│   ├── class-diagnostic-backup-retention.php
│   ├── class-diagnostic-database-backup.php
│   ├── class-diagnostic-file-backup.php
│   ├── class-diagnostic-offsite-backup.php
│   ├── class-diagnostic-uptime-monitoring.php
│   ├── class-diagnostic-error-rate.php
│   ├── class-diagnostic-resource-limits.php
│   └── class-diagnostic-plugin-conflicts.php
│
├── security/
│   ├── class-diagnostic-ssl-expiration.php
│   ├── class-diagnostic-ssl-chain.php
│   ├── class-diagnostic-ssl-protocol.php
│   ├── class-diagnostic-mixed-content.php
│   ├── class-diagnostic-wp-content-writable.php
│   ├── class-diagnostic-uploads-permissions.php
│   ├── class-diagnostic-plugins-permissions.php
│   ├── class-diagnostic-themes-permissions.php
│   └── class-diagnostic-logs-writable.php
│
├── performance/
│   ├── class-diagnostic-database-integrity.php
│   ├── class-diagnostic-slow-queries.php
│   ├── class-diagnostic-table-optimization.php
│   ├── class-diagnostic-database-size.php
│   ├── class-diagnostic-backup-restoration.php
│   ├── class-diagnostic-page-load-time.php
│   ├── class-diagnostic-core-web-vitals.php
│   ├── class-diagnostic-device-breakdown.php
│   └── class-diagnostic-geographic-performance.php
│
└── settings/
    ├── class-diagnostic-php-version.php
    ├── class-diagnostic-php-extensions.php
    ├── class-diagnostic-server-memory.php
    ├── class-diagnostic-php-execution-time.php
    ├── class-diagnostic-upload-size-limit.php
    ├── class-diagnostic-mysql-version.php
    ├── class-diagnostic-dns-records.php
    ├── class-diagnostic-dns-propagation.php
    ├── class-diagnostic-subdomain-config.php
    └── class-diagnostic-nameserver-config.php
```

---

## Diagnostic Registry Registration

All diagnostics must be registered in `includes/diagnostics/class-diagnostic-registry.php`:

```php
// Email Deliverability
self::register('smtp-connectivity', Diagnostic_SMTP_Connectivity::class);
self::register('spf-record', Diagnostic_SPF_Record::class);
self::register('dkim-record', Diagnostic_DKIM_Record::class);
self::register('dmarc-policy', Diagnostic_DMARC_Policy::class);
self::register('email-bounce-rate', Diagnostic_Email_Bounce_Rate::class);
self::register('email-delivery-test', Diagnostic_Email_Delivery_Test::class);
self::register('from-address-whitelist', Diagnostic_From_Address_Whitelist::class);
self::register('email-logging', Diagnostic_Email_Logging::class);
self::register('smtp-authentication', Diagnostic_SMTP_Authentication::class);

// SSL/TLS
self::register('ssl-expiration', Diagnostic_SSL_Expiration::class);
self::register('ssl-chain', Diagnostic_SSL_Chain::class);
self::register('ssl-protocol', Diagnostic_SSL_Protocol::class);
self::register('mixed-content', Diagnostic_Mixed_Content::class);

// ... and so on for all 47 diagnostics
```

---

## Success Criteria

**Phase 1 Complete When:**
- ✅ All 47 diagnostics implemented
- ✅ All registered in Diagnostic_Registry
- ✅ All have KB articles (text + video)
- ✅ All pass security review
- ✅ All pass accessibility review
- ✅ All pass performance benchmarks
- ✅ All PHPCS compliant
- ✅ All properly documented

---

**Version:** 1.0
**Last Updated:** February 4, 2026
**Ready for Implementation:** ✅ Yes
**Next Step:** Generate diagnostic classes using specification
