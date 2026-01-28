#!/usr/bin/env python3
"""
Generate GitHub issues for WPShadow Privacy Diagnostic Expansion
"""

import json
import subprocess
import os

OWNER = "thisismyurl"
REPO = "wpshadow"

# Phase 1: Core Privacy Diagnostics (Data Collection & Tracking)
PHASE1_ISSUES = [
    {
        "title": "Diagnostic: Google Analytics Tracking Detection & GDPR Compliance",
        "body": """## Description
Detect and analyze Google Analytics installations, verify GDPR compliance settings (anonymizeIp), and check for consent mechanism integration.

## What to Check For
- [ ] Detect GA scripts (gtag.js, analytics.js, ga.js) in header/footer
- [ ] Identify GA version in use
- [ ] Verify anonymizeIp parameter is enabled (GDPR requirement)
- [ ] Check if GA loads before consent banner
- [ ] Detect Google Analytics 4 (GA4) vs Universal Analytics
- [ ] Verify consent mode v2 is implemented (if GA4)
- [ ] Check for enhanced ecommerce tracking without PII filtering

## Test Cases
1. Site with GA loaded before consent = FAIL
2. Site with anonymizeIp disabled = WARN
3. Site with GA4 but no consent mode = WARN
4. Site with proper consent mode v2 = PASS
5. Site without GA = PASS (no issue)

## KPI Impact
- Privacy compliance score: +10 points if compliant
- GDPR violation risk: Reduced from "High" to "Low"
- User data protection: Measured in % of tracking calls anonymized

## Severity & Auto-Fix
- Severity: Medium (if non-compliant)
- Auto-fix Available: Yes - Can add anonymizeIp parameter
- Dry-run First: Yes - Show changes before applying

## Technical Notes
- Non-destructive read-only check
- Works across GA tracking implementations
- Must handle multisite configurations
- Related to: Google Site Kit, MonsterInsights, ExactMetrics plugins""",
        "labels": ["diagnostic", "privacy", "tracking", "gdpr", "analytics", "phase-1", "high-priority"]
    },
    {
        "title": "Diagnostic: Facebook Pixel Installation & Privacy Compliance",
        "body": """## Description
Detect Facebook Pixel implementations and verify privacy compliance, including consent requirements and server-side API usage (more private alternative).

## What to Check For
- [ ] Detect Facebook Pixel ID in HTML (fbq tracking)
- [ ] Identify if Pixel uses client-side tracking (less private)
- [ ] Check if Pixel respects Do Not Track headers
- [ ] Verify consent mechanism blocks Pixel before loading
- [ ] Detect Conversions API usage (server-side - more private)
- [ ] Check if Enhanced Conversions is enabled without user consent
- [ ] Identify custom events that may transmit PII

## Test Cases
1. Pixel with client-side tracking, no consent = FAIL
2. Pixel with consent integration = PASS
3. Conversions API only (server-side) = PASS
4. No Pixel detected = PASS (no issue)
5. Enhanced Conversions without consent = WARN

## KPI Impact
- Privacy score: +15 points for server-side implementation
- User data exposure: Reduced tracking surface area
- Compliance gap: Identifies GDPR/CCPA violations

## Technical Notes
- Check for fbq('track') calls with pixel ID
- Scan for Conversions API endpoints
- Verify consent manager integration (OneTrust, Termly, etc.)
- Handle WordPress plugins: Facebook for WooCommerce, Pixel Caffeine""",
        "labels": ["diagnostic", "privacy", "tracking", "compliance", "social-media", "phase-1"]
    },
    {
        "title": "Diagnostic: Third-Party Cookie Audit & Consent Management",
        "body": """## Description
Scan for all cookies set by external domains, categorize them (essential/analytics/marketing), and verify consent is required before setting.

## What to Check For
- [ ] Extract all Set-Cookie headers and identify domain origins
- [ ] Categorize cookies: Essential, Analytics, Marketing, Social
- [ ] Detect cookies without consent mechanism
- [ ] Check for first-party cookies (site domain) vs third-party
- [ ] Identify cookie consent plugins (Complianz, OneTrust, Termly, etc.)
- [ ] Verify consent banners block third-party cookies before loading
- [ ] Check cookie retention/expiration times

## Test Cases
1. Third-party cookies without consent manager = FAIL
2. Site with active cookie consent plugin = PASS
3. Essential cookies only = PASS (no third-party)
4. Multiple non-essential cookies visible immediately = FAIL
5. Cookies with GDPR-compliant expiration (13 months max) = PASS

## KPI Impact
- Privacy score: +10 points per compliant category
- Tracking surface: # of third-party cookies identified
- GDPR compliance: Reduces fine risk from €20M potential violation

## Technical Notes
- Use browser automation to capture Set-Cookie headers
- Parse cookie attributes: Secure, HttpOnly, SameSite, Domain, Expires
- Cross-reference with known cookie tracking services
- Map to plugin sources (Google, Facebook, etc.)""",
        "labels": ["diagnostic", "privacy", "cookies", "gdpr", "compliance", "phase-2"]
    },
    {
        "title": "Diagnostic: External API Calls Inventory & Data Flow Mapping",
        "body": """## Description
Detect all wp_remote_get/post calls to external services and map data flow, identifying what information leaves the site.

## What to Check For
- [ ] Scan for wp_remote_get/post/request calls in PHP code
- [ ] Identify external domain targets and services (Google, Facebook, Mailchimp, etc.)
- [ ] Extract API endpoints being called
- [ ] Detect if IP addresses are being transmitted
- [ ] Check for user email/ID transmission
- [ ] Identify API keys visible in source code (security issue!)
- [ ] Verify HTTPS encryption for all external calls
- [ ] Check frequency/rate of API calls

## Test Cases
1. API call sending user email without consent = FAIL
2. All external calls encrypted with HTTPS = PASS
3. API key exposed in code = CRITICAL
4. Excessive API calls (>100/minute) to third party = WARN
5. Only essential service calls (payment processing) = PASS

## KPI Impact
- Data leakage risk: Quantified by # of PII transmission incidents
- External dependencies: Mapped for compliance audit
- Privacy score: +5-20 points depending on service necessity

## Technical Notes
- Parse wp_remote_* function calls using AST analysis
- Check function arguments for data being transmitted
- Maintain whitelist of approved services (Stripe, PayPal, etc.)
- Flag any calls transmitting PII without documented justification""",
        "labels": ["diagnostic", "privacy", "security", "api", "data-flow", "phase-2"]
    },
    {
        "title": "Diagnostic: Email Marketing Service Detection & Consent Verification",
        "body": """## Description
Identify email marketing service integrations (MailChimp, ConvertKit, etc.) and verify GDPR-compliant consent mechanisms, including double opt-in.

## What to Check For
- [ ] Detect email marketing plugins (Mailchimp, ConvertKit, Klaviyo, etc.)
- [ ] Verify double opt-in is enabled (GDPR best practice)
- [ ] Check for GDPR consent checkbox on signup forms
- [ ] Identify API integration security (token storage, encryption)
- [ ] Verify legitimate interest disclosure in forms
- [ ] Check for silent email collection (should have explicit consent)
- [ ] Detect automated email list synchronization

## Test Cases
1. Mailchimp form without double opt-in = WARN
2. ConvertKit with GDPR consent checkbox = PASS
3. Automated list sync sending emails without consent = FAIL
4. Email collection form with legitimate interest disclosure = PASS
5. No email marketing services detected = PASS (no issue)

## KPI Impact
- Email list quality: Double opt-in increases engagement 20-30%
- GDPR compliance: Prevents €20M+ fines
- Consent rate: Tracks % of users with documented consent

## Technical Notes
- Plugin detection: Scan for Mailchimp for WordPress, Constant Contact, etc.
- Form analysis: Check for GDPR-specific fields in opt-in forms
- API verification: Check token storage and encryption method
- Database scan: Identify email collection tables and retention periods""",
        "labels": ["diagnostic", "privacy", "email-marketing", "gdpr", "compliance", "phase-1"]
    },
    {
        "title": "Diagnostic: Analytics Loading Before Consent Detection",
        "body": """## Description
Detect if tracking scripts (Google Analytics, hotjar, Mixpanel, etc.) load before user consent is obtained, which violates GDPR/CCPA.

## What to Check For
- [ ] Check script loading order in HTML head/body
- [ ] Identify consent banner detection and timing
- [ ] Verify tracking scripts load AFTER consent check passes
- [ ] Detect deferred/async loading vs synchronous loading
- [ ] Check for consent cookie presence before page load
- [ ] Identify tracking scripts without consent wrappers
- [ ] Verify cookie consent plugins delay third-party scripts

## Test Cases
1. Analytics script in head, no consent cookie = FAIL
2. Analytics in head but wrapped with consent check = PASS
3. Consent cookie present before external scripts load = PASS
4. Tracking pixels (1x1 GIF) loaded without consent = FAIL
5. All analytics deferred until consent = PASS

## KPI Impact
- GDPR violation risk: Reduces potential €20M fine exposure
- Consent compliance: % of tracking calls with documented consent
- Privacy score: +20 points for proper implementation

## Technical Notes
- Capture initial page load sequence (document.ready timing)
- Compare script src attributes with known tracking services
- Check for explicit consent wrapper functions (gtag('consent'))
- Verify consent plugins actually block third-party scripts""",
        "labels": ["diagnostic", "privacy", "tracking", "gdpr", "compliance", "critical", "phase-1"]
    },
    {
        "title": "Diagnostic: User IP Address Logging & Anonymization Verification",
        "body": """## Description
Detect plugins logging full IP addresses, verify if IP anonymization is enabled, and check retention policies comply with GDPR.

## What to Check For
- [ ] Identify plugins storing IP addresses (Wordfence, Akismet, WP Statistics, etc.)
- [ ] Check if IP anonymization is enabled (e.g., last octet masked: 192.168.1.0)
- [ ] Verify data retention policy (should be <13 months per GDPR)
- [ ] Detect logging without anonymization (security plugins, comment spam)
- [ ] Check access logs for unmasked visitor IPs
- [ ] Identify server-side tracking storing IPs
- [ ] Verify GDPR data processing agreement with logging service

## Test Cases
1. Full IPs logged with no anonymization = FAIL
2. IPs anonymized to /24 CIDR block = PASS
3. IP retention policy >13 months = FAIL
4. Security plugin logging IPs without anonymization = WARN
5. IPs masked after 30 days = PASS

## KPI Impact
- GDPR compliance: Avoid €4-10M fine for unauthorized IP storage
- Privacy score: +15 points for proper anonymization
- Data minimization: Reduces personal data stored

## Technical Notes
- Database query: Check wp_logs, wp_statistics tables
- Settings scan: Look for "anonymize", "mask", "privacy" options
- Plugin detection: Wordfence, Sucuri, All In One WP Security, WP Statistics
- Log file analysis: Check Apache/Nginx access logs""",
        "labels": ["diagnostic", "privacy", "gdpr", "data-protection", "security", "phase-2"]
    },
    {
        "title": "Diagnostic: Geolocation Data Collection Detection & Privacy Verification",
        "body": """## Description
Identify plugins collecting geolocation data (GPS, IP-based location, maps) and verify user notification, consent, and legitimate purpose.

## What to Check For
- [ ] Detect geolocation plugins (store locators, local SEO tools)
- [ ] Identify if geolocation collection requires user consent
- [ ] Check for user notification disclosing geolocation use
- [ ] Verify data retention period and deletion policies
- [ ] Detect Google Maps API usage (privacy implications)
- [ ] Check for IP-to-location services (MaxMind, GeoIP2, etc.)
- [ ] Identify location-based marketing or targeting

## Test Cases
1. Geolocation collected without user notification = FAIL
2. Geolocation requires explicit consent before collection = PASS
3. Location data retention >12 months = FAIL
4. Google Maps API key exposed in frontend = SECURITY ISSUE
5. Location data with 30-day retention = PASS

## KPI Impact
- Privacy score: +10 points for proper consent
- GDPR compliance: Prevents fine for unauthorized location collection
- User trust: Transparent location data practices

## Technical Notes
- Script detection: Search for geolocation JS APIs
- Plugin scan: Store Locator Plus, local business plugins
- API verification: Check Google Maps configuration
- Database: Identify location data tables and retention settings""",
        "labels": ["diagnostic", "privacy", "geolocation", "consent", "phase-2"]
    }
]

# Phase 2: Data Storage & Retention
PHASE2_ISSUES = [
    {
        "title": "Diagnostic: Expired Transients Not Being Deleted (Data Bloat)",
        "body": """## Description
Detect transients that have expired but not been automatically deleted from wp_options table, indicating data bloat and potential privacy issue.

## What to Check For
- [ ] Query wp_options for transients with _transient prefix
- [ ] Compare transient expiration time with current time
- [ ] Identify expired transients still in database
- [ ] Calculate total storage space used by expired transients
- [ ] Check if transients contain personal data (emails, IPs)
- [ ] Verify cleanup cron jobs are running
- [ ] Identify plugins creating long-lived transients

## Test Cases
1. Database with 1000+ expired transients = FAIL
2. All transients cleaned up properly = PASS
3. Expired transients containing email addresses = FAIL
4. Database cleanup cron failing = WARN
5. <100 expired transients (acceptable overhead) = PASS

## KPI Impact
- Database bloat: Quantified in MB/GB of wasted space
- Privacy: Reduces storage of potentially sensitive cached data
- Performance: Cleanup can improve query speed 5-10%

## Implementation Notes
- Query: SELECT * FROM wp_options WHERE option_name LIKE '%_transient_%'
- Calculate: DATE_ADD(option_value, INTERVAL expiration_seconds)
- Check: wp_cron or system cron settings
- Cleanup: Run wp_delete_expired_transients()

## Auto-fix Available
- Yes: Delete expired transients automatically
- Dry-run: Show count/storage to be freed first
- Schedule: Set up automated cleanup if not running""",
        "labels": ["diagnostic", "privacy", "data-retention", "database", "performance", "phase-2"]
    },
    {
        "title": "Diagnostic: User Activity Log Retention Policy Verification",
        "body": """## Description
Verify plugins storing user activity logs (WooCommerce orders, comment history, admin actions) have GDPR-compliant retention policies.

## What to Check For
- [ ] Identify plugins creating activity/audit logs
- [ ] Check retention period configuration (should be <13 months per GDPR)
- [ ] Verify automatic deletion of old log entries
- [ ] Detect log tables without expiration index
- [ ] Check for PII in logs (user emails, IPs, etc.)
- [ ] Identify backup/archive logs not included in retention
- [ ] Verify log access controls (admin-only visibility)

## Test Cases
1. Activity logs retained indefinitely = FAIL
2. Logs retained for 6 months with auto-deletion = PASS
3. Logs with PII retained 25 months = FAIL
4. Multiple backup tables with no retention policy = FAIL
5. Logs deleted automatically after 13 months = PASS

## KPI Impact
- GDPR compliance: Reduce fine risk from improper data retention
- Privacy score: +15 points for proper retention policies
- Data minimization: Reduces historical data stored

## Implementation Notes
- Database scan: Identify log tables (wp_postmeta, wp_usermeta, woo_logs)
- Plugin detection: Activity Log, WP Activity Log, WooCommerce, etc.
- Settings check: Look for "retention", "delete", "purge" options
- Cron verification: Ensure cleanup jobs scheduled""",
        "labels": ["diagnostic", "privacy", "gdpr", "data-retention", "compliance", "phase-2"]
    },
    {
        "title": "Diagnostic: Deleted User Data Cleanup Verification (Right to Erasure)",
        "body": """## Description
Verify that when users are deleted, all associated personal data is properly removed from database, including orphaned meta records, comments, and logs.

## What to Check For
- [ ] Query for orphaned user meta records (user_id pointing to deleted user)
- [ ] Check comment author IDs for deleted users
- [ ] Identify order history for deleted users (WooCommerce)
- [ ] Check for abandoned form submissions with user data
- [ ] Verify user deletion removes from email lists (Mailchimp, etc.)
- [ ] Identify plugin-specific user data not deleted
- [ ] Check backup/archive tables for deleted user references

## Test Cases
1. Deleted user data still in comments table = FAIL
2. Orphaned user meta records exist = FAIL
3. WooCommerce order history remains but anonymized = PASS
4. Plugin user data table cleaned up on user deletion = PASS
5. No traces of deleted user except non-PII activity log = PASS

## KPI Impact
- GDPR right to erasure: Ensures compliance
- Privacy score: +20 points for complete erasure
- Data minimization: Removes all personal data on request

## Implementation Notes
- Database queries to identify orphaned records
- Test user deletion workflow end-to-end
- Check hook: do_action('delete_user') for plugin cleanup
- Verify plugin hooks implement user deletion cleanup""",
        "labels": ["diagnostic", "privacy", "gdpr", "data-erasure", "compliance", "critical", "phase-5"]
    },
    {
        "title": "Diagnostic: Database Table Encryption for Sensitive Data",
        "body": """## Description
Identify database tables storing sensitive data and verify encryption is enabled at rest. Flag plaintext storage of passwords, credit cards, or PII.

## What to Check For
- [ ] Scan for plaintext passwords in database (security issue!)
- [ ] Check if credit card data is stored locally (PCI violation!)
- [ ] Verify encryption for sensitive columns (email, phone, address)
- [ ] Identify database server encryption configuration
- [ ] Check for encrypted columns using AES/RSA
- [ ] Verify backup encryption is enabled
- [ ] Detect legacy plugins storing unencrypted sensitive data

## Test Cases
1. Plaintext passwords in wp_users = CRITICAL
2. Credit card numbers in any table = CRITICAL
3. Sensitive data encrypted with current encryption = PASS
4. Backups unencrypted but data encrypted = WARN
5. All sensitive data encrypted at rest = PASS

## KPI Impact
- Security score: +50 points for proper encryption
- PCI compliance: Prevents credit card theft liability
- GDPR security: Demonstrates data protection measures

## Implementation Notes
- Database introspection: Check column types and sample data
- Encryption verification: Test decrypt/encrypt capabilities
- Backup audit: Verify encryption for backups
- Legacy plugin scan: Older plugins may store plaintext""",
        "labels": ["diagnostic", "privacy", "security", "encryption", "pci-compliance", "phase-3"]
    }
]

def create_issue(title, body, labels):
    """Create a GitHub issue using the API"""
    print(f"Creating: {title[:60]}...")
    # Placeholder - actual implementation would use gh CLI or API
    return True

if __name__ == "__main__":
    print("Total issues to create:", len(PHASE1_ISSUES) + len(PHASE2_ISSUES))
    for issue in PHASE1_ISSUES + PHASE2_ISSUES:
        create_issue(issue["title"], issue["body"], issue["labels"])
