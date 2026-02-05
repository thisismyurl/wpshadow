#!/usr/bin/env python3
"""
GitHub Issue Creator for Phase 1 Diagnostics
Creates GitHub issues for all Phase 1 critical diagnostics from the roadmap
"""

import subprocess
import json
import sys

# Phase 1 diagnostics with detailed information
PHASE_1_ISSUES = [
    {
        "title": "[Phase 1] Email Deliverability Diagnostics - SMTP & DNS Email Configuration",
        "labels": ["diagnostic", "phase-1", "critical", "email"],
        "description": """## 📧 Feature: Email Deliverability Diagnostics

### Impact Level: 🔴 CRITICAL
**User Impact:** Without working email, sites can't send password resets, notifications, orders, or contact form responses. This is a business-breaking issue that users often don't discover until too late.

### Context
Currently, WPShadow has no way to alert users about email delivery failures. Users can spend weeks unaware that:
- Contact form emails aren't being sent
- Customers never receive order confirmations  
- Password reset emails disappear
- Admin notifications are lost
- Comments aren't being notified

### Proposed Diagnostics (9 tests)

1. **SMTP Server Connectivity**
   - Checks if SMTP server responds on port 25/465/587
   - Tests connection timeout handling
   - Example: `wp_mail_smtp` host configuration validation

2. **SMTP Authentication**
   - Validates SMTP username/password if configured
   - Tests for timeout during auth
   - Checks if auth method matches server requirements

3. **SPF Record Published**
   - Queries DNS for SPF record on domain
   - Validates SPF syntax
   - Checks if sending server IP is included

4. **DKIM Records Configured**
   - Verifies DKIM records exist in DNS
   - Validates public key format
   - Checks selector configuration

5. **DMARC Policy Set**
   - Queries DNS for DMARC policy
   - Validates policy alignment with SPF/DKIM
   - Suggests policy improvements

6. **Email Bounce Rate Tracking**
   - Monitors returned/bounced emails over time
   - Flags high bounce rates (>5%)
   - Suggests troubleshooting steps

7. **Transactional Email Delivery**
   - Tests sending actual test email to admin
   - Tracks delivery success/failure
   - Measures delivery time

8. **From Address Whitelist**
   - Validates "from" address matches domain
   - Checks for spoofing risks
   - Verifies sender policy compliance

9. **Email Logging Enabled**
   - Checks if email logging is active (if plugin used)
   - Validates log retention policy
   - Warns if no logging available

### Implementation Strategy

**File Location:** \`includes/diagnostics/tests/monitoring/\`

**Template Base:**
- Extend \`Diagnostic_Base\`
- Use WordPress's \`wp_mail\` to send test emails
- Query DNS using \`checkdnsrr()\` for record validation
- Use \`PHPMailer\` where needed for SMTP testing

**Test Framework:**
- Create 9 separate diagnostic classes (one per check)
- Each implements \`check()\` method returning Finding array
- Register all 9 in \`Diagnostic_Registry\`

**WordPress APIs to Use:**
- \`get_option('siteurl')\` - Get site URL
- \`get_bloginfo('admin_email')\` - Get admin email
- \`wp_mail()\` - Send test email
- \`checkdnsrr()\` - Query DNS records
- \`gethostbyname()\` - IP resolution

**Example Dashboard Output:**
- 📧 Email Deliverability: 7/9 checks passing
  - ✅ SMTP connected
  - ✅ SPF record published
  - ⚠️ DKIM not configured (warning)
  - ❌ DMARC policy missing (critical)

### User-Facing Text (Friendly Neighbor Tone)

**For Admin:**
> "We noticed your DMARC policy isn't set. Without it, your emails are more likely to end up in spam folders. This usually takes 5 minutes to fix. Here's how... [link to guide]"

**For Audit:**
> "Email deliverability: All systems healthy. Your emails are configured for maximum inbox placement."

### Testing Considerations

- Test with various email service providers (Gmail, Outlook, etc.)
- Handle DNS propagation delays gracefully
- Don't actually send emails in dry-run mode
- Provide helpful error messages for each failure type
- Cache DNS results (30 minute TTL) to avoid rate limits

### Success Criteria
- ✅ All 9 diagnostics implemented and tested
- ✅ Admin alerted immediately to delivery failures
- ✅ Each diagnostic provides actionable fix suggestions
- ✅ No false positives on DNS lookups
- ✅ Performance: each check completes in <1 second
- ✅ Backward compatible with WordPress 6.4+

### Effort Estimate
- Implementation: 2-3 hours
- Testing: 2 hours
- Documentation: 1 hour
- **Total: 5-6 hours**

### Related Issues
- None (first Phase 1 diagnostic)

### Developer Notes
- Use \`wp_remote_post()\` for SMTP connection validation
- Handle transient failures gracefully (DNS timeouts, etc.)
- Consider caching diagnostic results (5-minute cache)
- Avoid sending test emails if user opted out of communications
"""
    },
    {
        "title": "[Phase 1] Database Health Diagnostics - Integrity, Performance & Backup Testing",
        "labels": ["diagnostic", "phase-1", "critical", "database"],
        "description": """## 🗄️ Feature: Database Health Diagnostics

### Impact Level: 🔴 CRITICAL
**User Impact:** Database corruption or slowdown leads to site crashes, data loss, and lost revenue. Users often don't know their database is unhealthy until their site goes down.

### Context
WPShadow has extensive performance diagnostics but lacks specific database health checks:
- Table corruption goes undetected
- Slow queries aren't identified
- Database isn't optimized
- Backup restoration isn't tested
- Growth trends aren't tracked

### Proposed Diagnostics (5 tests)

1. **Database Integrity Check**
   - Runs \`CHECK TABLE\` on critical tables (wp_posts, wp_postmeta, wp_users, wp_options)
   - Detects table corruption
   - Suggests table repair if needed

2. **Slow Query Detection**
   - Queries \`PROCESSLIST\` for running queries
   - Identifies queries running >1 second
   - Flags queries on non-indexed columns
   - Suggests query optimization

3. **Table Optimization Status**
   - Checks table fragmentation
   - Analyzes AUTO_INCREMENT nearness to max value
   - Detects tables that need optimization
   - Suggests running \`OPTIMIZE TABLE\`

4. **Database Size & Growth Trend**
   - Measures current database size
   - Tracks size change over time
   - Warns if growth is abnormal (>10% weekly)
   - Projects when disk space will be full

5. **Backup Restoration Test**
   - Verifies a recent backup exists
   - Attempts point-in-time recovery calculation
   - Validates backup completeness
   - Ensures recovery plan is viable

### Implementation Strategy

**File Location:** \`includes/diagnostics/tests/performance/\` or \`monitoring/\`

**Database Queries:**
\`\`\`php
// Integrity check
CHECK TABLE \`wp_posts\`, \`wp_postmeta\`, \`wp_users\`;

// Slow query detection
SELECT * FROM information_schema.PROCESSLIST WHERE TIME > 1;

// Fragmentation analysis
SELECT 
    TABLE_NAME,
    DATA_FREE / 1024 / 1024 as fragmentation_mb
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE();

// Growth trend
SELECT 
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE();
\`\`\`

**WordPress APIs:**
- \`$wpdb->get_results()\` - Execute diagnostic queries
- \`$wpdb->prepare()\` - Safe query preparation
- \`get_option( 'blog_charset' )\` - Charset validation

### Testing Considerations

- Safely execute diagnostic queries (read-only)
- Handle permission errors gracefully (non-admin DB users)
- Cache results to avoid performance impact (15-minute cache)
- Detect InnoDB vs MyISAM table types
- Handle MariaDB vs MySQL differences

### Success Criteria
- ✅ All 5 diagnostics implemented and tested
- ✅ Detects table corruption before it causes data loss
- ✅ Identifies slow queries with actionable suggestions
- ✅ Tracks database growth trends automatically
- ✅ Validates backup restoration capability
- ✅ No false positives on normal database operations
- ✅ No performance degradation to site

### Effort Estimate
- Implementation: 2-3 hours
- Testing: 1.5 hours
- Documentation: 1 hour
- **Total: 4.5-5.5 hours**

### Related Diagnostics
- Backup & Disaster Recovery (Phase 1 #5)
- Performance monitoring (existing diagnostics)
"""
    },
    {
        "title": "[Phase 1] File System Permissions Diagnostics - Directory & File Access Validation",
        "labels": ["diagnostic", "phase-1", "critical", "security"],
        "description": """## 📁 Feature: File System Permissions Diagnostics

### Impact Level: 🔴 CRITICAL
**User Impact:** Wrong file permissions cause plugin updates to fail silently, uploads to be rejected, and site modifications to be blocked. Users blame WordPress or hosting without understanding the real cause.

### Context
Current diagnostics don't validate directory permissions, leading to:
- Plugin/theme updates fail mysteriously
- Media uploads rejected
- Admin can't write to config files
- Log files can't be created
- Temporary files can't be written

### Proposed Diagnostics (5 tests)

1. **wp-content Directory Writable**
   - Checks if \`wp-content/\` is world-writable or owner-writable
   - Tests actual write capability
   - Flags if server can't write files
   - Suggests permission fixes

2. **Uploads Directory Permissions**
   - Validates uploads folder permissions (should be 755 or 750)
   - Checks for overly permissive 777 (security risk)
   - Verifies server can write uploads
   - Tests file deletion capability

3. **Plugins Directory Permissions**
   - Checks plugins folder for correct permissions
   - Validates plugin files are readable
   - Tests if new plugins can be added
   - Flags permission mismatches

4. **Themes Directory Permissions**
   - Validates themes folder permissions
   - Checks if theme files are readable
   - Tests if new themes can be installed
   - Detects read-only issues

5. **Logs Directory Writable** (if exists)
   - Checks if custom logs directory is writable
   - Validates error logs can be created
   - Tests permissions on existing log files
   - Warns if logs are world-readable (privacy issue)

### Implementation Strategy

**File Location:** \`includes/diagnostics/tests/settings/\` or \`security/\`

**PHP Functions:**
\`\`\`php
// Check if writable
is_writable( WP_CONTENT_DIR )

// Get actual permissions
substr(sprintf('%o', fileperms($path)), -4)

// Test write capability
$test_file = $dir . '/.permission-test-' . wp_generate_uuid4();
@file_put_contents($test_file, 'test');
$result = file_exists($test_file);
@unlink($test_file);
\`\`\`

**Permission Reference:**
- 755: Owner full access, others read+execute (standard)
- 750: Owner & group full access, others none (secure)
- 777: Everyone full access (security risk!)
- 644: Owner read+write, others read only (files)

### Checking Strategy

1. Verify directory exists
2. Test actual write capability (don't just check permissions)
3. Clean up test files
4. Report both permission mode and write capability
5. Suggest fixes with specific chmod commands

### Success Criteria
- ✅ All 5 diagnostics implemented and tested
- ✅ Catches permission issues before they break updates
- ✅ Distinguishes between permission mode and actual access
- ✅ Provides specific chmod commands for fixes
- ✅ Handles shared hosting limitations gracefully
- ✅ No false positives on properly configured systems

### Effort Estimate
- Implementation: 1.5-2 hours
- Testing: 1.5 hours
- Documentation: 1 hour
- **Total: 4-4.5 hours**

### Related Diagnostics
- Security: existing permission checks for core files
- Settings: existing WordPress directory validation
"""
    },
    {
        "title": "[Phase 1] Hosting Environment Diagnostics - Server Configuration & Compatibility",
        "labels": ["diagnostic", "phase-1", "critical", "settings"],
        "description": """## 🖥️ Feature: Hosting Environment Diagnostics

### Impact Level: 🔴 CRITICAL
**User Impact:** Incompatible hosting environment causes site crashes, poor performance, and broken features. Users with budget hosts often don't know their server is inadequate until problems occur.

### Context
Current diagnostics validate WordPress config but not hosting environment:
- PHP version too old for plugins
- Required PHP extensions missing
- Server memory too low
- Execution time too short for batch operations
- Upload size limit prevents large files
- MySQL version incompatible

### Proposed Diagnostics (6 tests)

1. **PHP Version Minimum**
   - Checks current PHP version (must be 8.1+)
   - Flags if version reaches EOL in next 6 months
   - Suggests version upgrade timeline
   - Compatible with WordPress 6.4+

2. **Required PHP Extensions**
   - Validates \`mysqli\` present (database)
   - Checks for \`GD\` library (image processing)
   - Verifies \`curl\` available (HTTP requests)
   - Checks \`json\` extension (parsing)
   - Flags if OpenSSL missing (security)
   - Suggests extension installation

3. **Server Memory Allocation**
   - Checks \`memory_limit\` setting
   - Minimum requirement: 128MB
   - Recommended: 256MB or higher
   - Warns if less than minimum
   - Explains impact (batch operations fail)

4. **PHP Max Execution Time**
   - Verifies \`max_execution_time\` setting
   - Minimum requirement: 30 seconds
   - Recommended: 60+ seconds
   - Flags if too low for backups/imports
   - Suggests adjustments

5. **Upload Size Limit**
   - Checks \`upload_max_filesize\` setting
   - Minimum: 64MB (for media)
   - Recommended: 256MB+ (for flexibility)
   - Validates post size limit alignment
   - Warns if uploading large files will fail

6. **MySQL/MariaDB Version**
   - Checks database version
   - Minimum: MySQL 5.7 or MariaDB 10.2
   - Recommended: MySQL 8.0+ or MariaDB 10.4+
   - Warns if version reaches EOL
   - Tests for known compatibility issues

### Implementation Strategy

**File Location:** \`includes/diagnostics/tests/settings/\`

**PHP Functions:**
\`\`\`php
// PHP version
phpversion()

// Extensions
extension_loaded('mysqli')
phpversion('mysqli')

// Configuration
ini_get('memory_limit')
ini_get('max_execution_time')
ini_get('upload_max_filesize')

// MySQL version
global $wpdb;
$wpdb->db_version()
\`\`\`

### Testing Considerations

- Handle shared hosting restrictions (some settings read-only)
- Validate both global and per-folder configurations
- Check .htaccess and php.ini overrides
- Warn about EOL versions
- Provide specific upgrade instructions

### Success Criteria
- ✅ All 6 diagnostics implemented and tested
- ✅ Detects hosting incompatibilities before site breaks
- ✅ Provides clear, actionable upgrade paths
- ✅ Explains impact of each requirement
- ✅ Works on various hosting platforms (shared, VPS, cloud)
- ✅ No false positives on properly configured servers

### Effort Estimate
- Implementation: 1.5-2 hours
- Testing: 1.5 hours
- Documentation: 1 hour
- **Total: 4-4.5 hours**

### Related Diagnostics
- Performance: existing optimization diagnostics
- Security: existing security checks
"""
    },
    {
        "title": "[Phase 1] Backup & Disaster Recovery Diagnostics - Backup Configuration & Testing",
        "labels": ["diagnostic", "phase-1", "critical", "monitoring"],
        "description": """## 💾 Feature: Backup & Disaster Recovery Diagnostics

### Impact Level: 🔴 CRITICAL  
**User Impact:** Without tested backups, site data loss is catastrophic. Most users don't test backups until they need them (too late). This is the difference between "bad day" and "company closing."

### Context
Current diagnostics don't validate disaster recovery:
- Backup configured but never tested
- Backup retention too short
- No offsite backups (local-only risk)
- Database backup incomplete
- File backup failing silently
- Recovery procedure untested

### Proposed Diagnostics (6 tests)

1. **Backup Configured & Running**
   - Checks if backup plugin/service active
   - Verifies backup runs on schedule
   - Validates backup log for errors
   - Flags if no backup found in 7 days
   - Detects orphaned backup attempts

2. **Backup Frequency Adequate**
   - Checks backup schedule (daily minimum for active sites)
   - Suggests frequency based on content update rate
   - Warns if backups only weekly (data loss up to 7 days)
   - Flags if no schedule configured
   - Recommends frequency based on site importance

3. **Backup Retention Policy**
   - Validates how long backups kept
   - Minimum: 14 days (2 weeks)
   - Recommended: 30 days (1 month)
   - Flags if retention too short
   - Warns if no retention policy
   - Calculates restore window

4. **Database Backup Working**
   - Verifies database backup exists
   - Checks backup file size (should be meaningful)
   - Validates backup timestamp is recent
   - Tests backup integrity (can decompress/read)
   - Flags corrupted backups

5. **File Backup Working**
   - Validates file backup includes wp-content, uploads
   - Checks backup completeness
   - Verifies recent backup exists
   - Tests backup can be extracted
   - Flags missing critical directories

6. **Offsite Backup Storage**
   - Checks if backups stored remotely (AWS S3, Google Drive, etc.)
   - Validates offsite storage is accessible
   - Tests offsite backup connectivity
   - Flags if backups only local (single point of failure)
   - Suggests offsite storage options
   - Validates encryption for offsite backups

### Implementation Strategy

**File Location:** \`includes/diagnostics/tests/monitoring/\`

**Backup Plugin Detection:**
\`\`\`php
// Check for popular backup plugins
class_exists('UpdraftPlus_Admin')        // UpdraftPlus
class_exists('BackWPup')                 // BackWPup
defined('JETPACK_BACKUP_ACTIVE')         // Jetpack Backup
class_exists('VaultPress_Backup')        // VaultPress
defined('BACKUPBUDDY_LITE_VERSION')      // BackupBuddy
\`\`\`

**Backup Validation:**
\`\`\`php
// Get latest backup
$backups = apply_filters('wpshadow_get_backups', array());
$latest = array_pop($backups);

// Check age
$age_seconds = time() - filemtime($latest['file']);
$age_days = $age_seconds / 86400;

// Validate size
$size_mb = filesize($latest['file']) / 1024 / 1024;
if ($size_mb < 1) {
    return array('message' => 'Backup file too small, may be incomplete');
}
\`\`\`

### Testing Considerations

- Support major backup plugins (UpdraftPlus, BackWPup, Jetpack, VaultPress)
- Handle custom backup scripts gracefully
- Don't interfere with running backups
- Test restore capability without actually restoring
- Validate encryption keys are stored securely
- Check backup scheduling engine (WP-Cron, system cron, external service)

### Success Criteria
- ✅ All 6 diagnostics implemented and tested
- ✅ Detects backup failures before disaster
- ✅ Validates backup retention meets needs
- ✅ Confirms offsite storage is working
- ✅ Provides disaster recovery assessment
- ✅ Works with all major backup plugins
- ✅ Suggests actionable improvements

### Effort Estimate
- Implementation: 3-4 hours
- Testing: 2 hours
- Documentation: 1 hour
- **Total: 6-7 hours**

### Related Diagnostics
- Database Health (Phase 1 #2)
- File System Permissions (Phase 1 #3)
- Monitoring: existing health checks
"""
    },
    {
        "title": "[Phase 1] SSL/TLS Certificate Diagnostics - Certificate Validity & Security Headers",
        "labels": ["diagnostic", "phase-1", "critical", "security"],
        "description": """## 🔒 Feature: SSL/TLS Certificate Diagnostics

### Impact Level: 🔴 CRITICAL
**User Impact:** Expired SSL certificates cause browser warnings, lost SEO rankings, and customer distrust. Certificate issues are non-obvious and can cause site shutdown.

### Context
Current diagnostics validate HTTPS but not certificate health:
- Certificate expires without warning
- Domain doesn't match certificate
- Mixed HTTP/HTTPS content
- HSTS not configured
- Security warnings in browsers
- SEO penalties from broken SSL

### Proposed Diagnostics (4 tests)

1. **Certificate Expiration Check**
   - Connects to site and reads certificate
   - Checks expiration date
   - Warns 30 days before expiration
   - Alerts when <7 days (critical)
   - Already expired = failure
   - Suggests renewal timeline

2. **Certificate Domain Validity**
   - Validates certificate matches domain(s)
   - Checks primary domain + subdomains
   - Handles wildcard certificates correctly
   - Detects domain mismatch (certificate warning)
   - Validates Subject Alternative Names (SANs)

3. **Mixed Content Detection**
   - Scans site pages for http:// resources
   - Detects mixed HTTP/HTTPS
   - Identifies common culprits (images, scripts, iframes)
   - Suggests fixes (//example.com or https://)
   - Tests from multiple pages
   - Provides mixed content breakdown

4. **HSTS Headers Configured**
   - Checks for HSTS header (Strict-Transport-Security)
   - Validates max-age setting (1 year+ recommended)
   - Checks for includeSubDomains directive
   - Suggests HSTS preload registration
   - Tests header on multiple endpoints
   - Validates preload eligibility

### Implementation Strategy

**File Location:** \`includes/diagnostics/tests/security/\`

**Certificate Reading:**
\`\`\`php
// Get certificate info
$stream = stream_context_create(['ssl' => ['capture_peer_cert' => true]]);
$socket = stream_socket_client('ssl://' . $domain . ':443', $error, $errorStr, 10, STREAM_CLIENT_CONNECT, $stream);
$cert = stream_context_get_params($socket);
$certInfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);

// Check expiration
$expires = $certInfo['validTo_time_t'];
$days_until_expiry = ($expires - time()) / 86400;
\`\`\`

**Mixed Content Detection:**
\`\`\`php
// Get homepage HTML
$response = wp_remote_get(home_url('/'));
$html = wp_remote_retrieve_body($response);

// Check for http:// URLs
preg_match_all('/(?:src|href|data)=[\'"]http:\\/\\/[^\'\"]+[\'"]/', $html, $matches);
\`\`\`

**HSTS Header Check:**
\`\`\`php
// Check response headers
$response = wp_remote_head(home_url('/'));
$headers = wp_remote_retrieve_headers($response);
$hsts = $headers['Strict-Transport-Security'] ?? null;
\`\`\`

### Testing Considerations

- Handle Let's Encrypt certificates correctly
- Support self-signed certificates (warn user)
- Handle certificate chains properly
- Test from outside firewall if possible
- Cache certificate info (24-hour TTL)
- Handle DNS resolution delays
- Don't fail if certificate validation libraries missing

### Success Criteria
- ✅ All 4 diagnostics implemented and tested
- ✅ Alerts users 30+ days before certificate expiration
- ✅ Detects domain/certificate mismatches immediately
- ✅ Identifies mixed content with specific fixes
- ✅ Suggests HSTS configuration
- ✅ Works with all certificate authorities
- ✅ No false positives on valid certificates

### Effort Estimate
- Implementation: 2-3 hours
- Testing: 1.5 hours
- Documentation: 1 hour
- **Total: 4.5-5.5 hours**

### Related Diagnostics
- Security: existing HTTPS checks
- Performance: existing security headers
"""
    },
    {
        "title": "[Phase 1] DNS Configuration Diagnostics - DNS Records & Propagation Validation",
        "labels": ["diagnostic", "phase-1", "important", "settings"],
        "description": """## 🌐 Feature: DNS Configuration Diagnostics

### Impact Level: 🔴 CRITICAL
**User Impact:** Misconfigured DNS breaks email delivery, CDN routing, and affects site performance globally. DNS issues are often invisible to users until they affect critical functionality.

### Context
Current diagnostics don't validate DNS health:
- DNS A records point to wrong IP
- DNS propagation incomplete
- MX records missing or incorrect
- CNAME records for CDN broken
- DNS propagation delays cause temporary failures
- Email routing failures

### Proposed Diagnostics (4 tests)

1. **DNS A Record Validation**
   - Queries DNS for A record
   - Compares returned IP vs. server's actual IP
   - Detects pointing to wrong datacenter
   - Identifies DNS propagation issues
   - Validates IPv4 configuration
   - Tests from multiple DNS servers

2. **DNS Propagation Status**
   - Checks if DNS has fully propagated
   - Tests from major DNS resolvers (Google 8.8.8.8, Cloudflare 1.1.1.1)
   - Identifies regions with stale DNS
   - Estimates propagation completion
   - Warns if incomplete (24+ hours after update)
   - Shows TTL remaining

3. **MX Records Configuration**
   - Queries for MX records on domain
   - Verifies MX record exists and valid
   - Checks for multiple MX records (backup mail servers)
   - Validates priority settings
   - Tests MX server reachability
   - Suggests mail server configuration

4. **CNAME Records & CDN Configuration**
   - Detects if using CDN (Cloudflare, AWS CloudFront, etc.)
   - Validates CNAME record points to CDN
   - Confirms CDN origin configured
   - Tests CDN response headers
   - Validates SSL for CDN endpoint
   - Checks cache settings

### Implementation Strategy

**File Location:** \`includes/diagnostics/tests/settings/\`

**DNS Queries:**
\`\`\`php
// A record lookup
$dns_a = dns_get_record($domain, DNS_A);
// Returns: [['host' => 'example.com', 'type' => 'A', 'ip' => '192.0.2.1', 'ttl' => 3600]]

// MX records
$dns_mx = dns_get_record($domain, DNS_MX);
// Returns: [['host' => 'example.com', 'type' => 'MX', 'pri' => 10, 'target' => 'mail.example.com']]

// CNAME records
$dns_cname = dns_get_record($domain, DNS_CNAME);

// Query multiple resolvers
$resolvers = ['8.8.8.8', '1.1.1.1', '208.67.222.222'];
foreach ($resolvers as $resolver) {
    $result = dns_get_record($domain, DNS_A, $authns, $addtl);
}
\`\`\`

**IP Validation:**
\`\`\`php
// Get server IP
$server_ip = gethostbyname($_SERVER['HTTP_HOST']);
// Or from hosting provider API

// Compare with DNS
if ($dns_result['ip'] !== $server_ip) {
    return array('message' => 'DNS points to different IP');
}
\`\`\`

### Testing Considerations

- Handle DNS caching gracefully (wait for TTL expiry)
- Support IPv6 (AAAA records) validation
- Handle DNS over HTTPS if available
- Test from multiple DNS servers
- Cache results appropriately (5-minute TTL)
- Handle NXDOMAIN gracefully
- Support both root domain and www

### Success Criteria
- ✅ All 4 diagnostics implemented and tested
- ✅ Detects DNS misconfigurations early
- ✅ Validates DNS propagation completion
- ✅ Confirms email routing configuration
- ✅ Tests CDN integration
- ✅ Works with all DNS providers
- ✅ Clear troubleshooting suggestions

### Effort Estimate
- Implementation: 1.5-2 hours
- Testing: 1.5 hours
- Documentation: 1 hour
- **Total: 4-4.5 hours**

### Related Diagnostics
- Email Deliverability (Phase 1 #1)
- SSL/TLS Certificate (Phase 1 #6)
- Settings: existing DNS checks
"""
    },
    {
        "title": "[Phase 1] Real User Monitoring (RUM) Diagnostics - Web Vitals & Performance Metrics",
        "labels": ["diagnostic", "phase-1", "important", "monitoring"],
        "description": """## 📊 Feature: Real User Monitoring Diagnostics

### Impact Level: 🔴 CRITICAL
**User Impact:** Lab performance tests don't match real-world user experience. Real User Monitoring reveals actual slowness impacting conversions and SEO rankings.

### Context
Current diagnostics test synthetic performance but miss real-user data:
- Lab tests pass but real users see slowness
- Mobile performance differs from desktop
- Page-specific issues hidden
- Regional performance differences
- User experience degradation goes unnoticed

### Proposed Diagnostics (4 tests)

1. **Core Web Vitals Baseline**
   - Establishes baseline for Largest Contentful Paint (LCP)
   - Measures First Input Delay (FID)
   - Tracks Cumulative Layout Shift (CLS)
   - Compares against Google's recommended thresholds
   - Alerts if trending worse over time
   - Links to Google Search Console data

2. **Real Traffic Monitoring Active**
   - Detects if Google Analytics or similar active
   - Validates measurement script is loaded
   - Confirms data collection working
   - Checks sampling isn't filtering data
   - Validates Web Vitals library installed
   - Tests beacon delivery to analytics

3. **Performance Alerts Configured**
   - Checks if performance alerts are set up
   - Validates alert thresholds (e.g., if LCP > 2.5s)
   - Confirms notification delivery
   - Tests alert functionality
   - Suggests appropriate thresholds
   - Recommends alert recipients

4. **Mobile vs Desktop Performance**
   - Compares mobile performance metrics vs desktop
   - Identifies mobile-specific issues
   - Measures mobile Core Web Vitals separately
   - Tracks mobile user satisfaction
   - Suggests mobile optimizations
   - Validates mobile page rendering

### Implementation Strategy

**File Location:** \`includes/diagnostics/tests/monitoring/\` or \`performance/\`

**Web Vitals Integration:**
\`\`\`php
// Check for Web Vitals library
// Look for Google Analytics with Web Vitals module
// Check Search Console connection for real data
// Validate gtag.js or analytics.js loaded

// Get RUM data from analytics API
$analytics_data = $this->get_analytics_data([
    'metrics' => ['LCP', 'FID', 'CLS'],
    'period' => 'last_28_days'
]);

// Compare to thresholds
$good_lcp = 2.5; // seconds (Google standard)
$good_fid = 100; // milliseconds
$good_cls = 0.1; // unitless
\`\`\`

**Traffic Monitoring Detection:**
\`\`\`php
// Check for analytics scripts
preg_match('/google-analytics|gtag|_gaq/', $page_html);

// Validate measurement ID
$ga_measurement_id = get_option('google_analytics_measurement_id');
$gtag_configured = !empty($ga_measurement_id);
\`\`\`

### Testing Considerations

- Require Google Analytics or compatible service
- Handle data collection delays (data available after 24 hours)
- Support multiple analytics platforms
- Validate data is actually flowing
- Don't break on missing analytics
- Cache analytics data appropriately
- Handle GDPR/privacy considerations
- Test both HTTP and beacon delivery

### Success Criteria
- ✅ All 4 diagnostics implemented and tested
- ✅ Establishes real-user performance baseline
- ✅ Alerts on performance degradation
- ✅ Compares mobile vs. desktop clearly
- ✅ Provides actionable optimization suggestions
- ✅ Integrates with Google Search Console
- ✅ Validates measurement setup

### Effort Estimate
- Implementation: 2-3 hours
- Testing: 1.5 hours
- Documentation: 1 hour
- **Total: 4.5-5.5 hours**

### Related Diagnostics
- Performance: existing optimization diagnostics
- SEO: existing Core Web Vitals tracking
"""
    },
    {
        "title": "[Phase 1] Downtime Prevention & Monitoring Diagnostics - Uptime Tracking & Alerting",
        "labels": ["diagnostic", "phase-1", "critical", "monitoring"],
        "description": """## ⏱️ Feature: Downtime Prevention & Monitoring Diagnostics

### Impact Level: 🔴 CRITICAL
**User Impact:** Site downtime costs businesses revenue immediately. Hours of undetected downtime can cost thousands. Early detection enables quick fixes before customer impact.

### Context
Current diagnostics don't monitor uptime:
- Site goes down silently
- Users discover outages before owners
- Downtime undetected for hours
- No incident response plan
- Recovery time not measured
- Revenue impact unknown

### Proposed Diagnostics (4 tests)

1. **Uptime Monitoring Active**
   - Detects if uptime monitoring service running
   - Validates external monitoring configured
   - Checks monitoring service connectivity
   - Confirms ping frequency (every 1-5 minutes)
   - Validates multiple monitoring locations
   - Tests alert delivery

2. **Downtime History & Availability**
   - Retrieves downtime history from monitoring service
   - Calculates availability percentage (99.9%, etc.)
   - Tracks downtime incidents
   - Identifies recurring issues
   - Shows downtime trends over time
   - Compares to SLA targets

3. **Monitoring Alerts Configured**
   - Verifies alert recipients configured
   - Tests alert delivery (email, SMS, Slack, etc.)
   - Validates alert thresholds (e.g., down for 5+ minutes)
   - Confirms alert escalation policy
   - Checks alert fatigue (too many false positives?)
   - Suggests alert optimization

4. **Incident Response Plan**
   - Checks if incident response documented
   - Validates escalation contacts
   - Confirms backup protocols documented
   - Verifies backup/restore procedures tested
   - Checks if failover configured
   - Suggests incident response plan template

### Implementation Strategy

**File Location:** \`includes/diagnostics/tests/monitoring/\`

**Uptime Service Detection:**
\`\`\`php
// Check for major uptime monitoring services
if (class_exists('UptimeRobot_API')) { /* UptimeRobot */ }
if (class_exists('Pingdom_Integration')) { /* Pingdom */ }
if (function_exists('better_uptime_check')) { /* Better Uptime */ }
if (defined('CUSTOM_MONITORING_ACTIVE')) { /* Custom */ }

// Get uptime data via API
$uptime_data = [
    'service' => 'UptimeRobot',
    'availability' => 99.95,
    'downtime_24h' => 0,
    'downtime_7d' => 14, // minutes
    'downtime_30d' => 43 // minutes
];
\`\`\`

**Availability Calculation:**
\`\`\`php
// Calculate percentage
$total_minutes_30d = 30 * 24 * 60; // 43,200 minutes
$uptime_minutes_30d = $total_minutes_30d - $downtime_minutes;
$availability_percentage = ($uptime_minutes_30d / $total_minutes_30d) * 100;

// Classify
if ($availability_percentage >= 99.9) {
    $status = 'Excellent (99.9%+)';
} elseif ($availability_percentage >= 99) {
    $status = 'Good (99%+)';
} else {
    $status = 'Needs attention (<99%)';
}
\`\`\`

### Testing Considerations

- Support major uptime monitoring services
- Handle custom monitoring scripts
- Cache uptime data appropriately (hourly)
- Don't break if monitoring not active (warn instead)
- Suggest appropriate monitoring services
- Test with various incident scenarios
- Validate alert delivery testing
- Handle timezone differences for incident reports

### Success Criteria
- ✅ All 4 diagnostics implemented and tested
- ✅ Detects monitoring gaps immediately
- ✅ Provides availability trending
- ✅ Confirms alert delivery working
- ✅ Documents incident response capability
- ✅ Suggests monitoring best practices
- ✅ Integrates with popular services

### Effort Estimate
- Implementation: 2-3 hours
- Testing: 1.5 hours
- Documentation: 1 hour
- **Total: 4.5-5.5 hours**

### Related Diagnostics
- Database Health (Phase 1 #2)
- SSL/TLS Certificate (Phase 1 #6)
- Backup & Disaster Recovery (Phase 1 #5)
"""
    }
]

def create_github_issue(title, description, labels):
    """Create a GitHub issue using gh CLI"""
    try:
        cmd = [
            'gh', 'issue', 'create',
            '--title', title,
            '--body', description,
        ]
        
        # Add labels
        for label in labels:
            cmd.extend(['--label', label])
        
        result = subprocess.run(cmd, capture_output=True, text=True, check=False)
        
        if result.returncode == 0:
            # Extract issue number from output
            import re
            match = re.search(r'#(\d+)', result.stdout)
            issue_num = match.group(1) if match else 'unknown'
            print(f"✅ Created: {title.split(']')[0]}] → Issue #{issue_num}")
            return True
        else:
            print(f"❌ Failed: {title.split(']')[0]}]")
            print(f"   Error: {result.stderr}")
            return False
    except Exception as e:
        print(f"❌ Error creating issue: {e}")
        return False

def main():
    print("\n" + "="*80)
    print("Creating GitHub Issues for Phase 1 Critical Diagnostics")
    print("="*80 + "\n")
    
    success_count = 0
    fail_count = 0
    
    for i, issue_data in enumerate(PHASE_1_ISSUES, 1):
        print(f"\n[{i}/{len(PHASE_1_ISSUES)}] Creating GitHub issue...")
        if create_github_issue(
            issue_data['title'],
            issue_data['description'],
            issue_data['labels']
        ):
            success_count += 1
        else:
            fail_count += 1
    
    print("\n" + "="*80)
    print(f"Summary: {success_count} created, {fail_count} failed")
    print("="*80 + "\n")
    
    return 0 if fail_count == 0 else 1

if __name__ == '__main__':
    sys.exit(main())
