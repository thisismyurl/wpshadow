## Quick Copy-Paste Templates for GitHub Issues

Use these exact templates to quickly create all 26 diagnostic issues. Just copy each section, go to https://github.com/thisismyurl/wpshadow/issues/new, and paste.

---

# PHASE 1: SECURITY (6 Issues)

## 1️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Vulnerable Plugin Detection`  
Labels: `diagnostic,security,enhancement,phase1`

```
## Description
Implement diagnostic to scan installed plugins against known CVE database and detect vulnerable plugin versions.

## What It Checks
- Fetches list of active plugins with version numbers
- Queries WordPress.org plugin API for security vulnerabilities
- Identifies plugins with known CVEs (Common Vulnerabilities and Exposures)
- Flags outdated plugin versions not available in official repository

## Why Valuable
- Plugins are the #1 WordPress vulnerability vector
- Most users don't know their installed plugins have known CVEs
- Auto-remediable via update availability detection

## Success Criteria
✅ Detects plugins with known vulnerabilities  
✅ Shows CVE links and severity  
✅ Suggests plugin updates  
✅ Uses WordPress.org API (not external services)  
✅ Handles network requests gracefully  
✅ KPI: "Vulnerabilities patched"  
✅ Unit tests pass (mock CVE database)  
✅ Performance < 5 seconds for 50+ plugins  

## Technical Requirements
- **File:** `includes/diagnostics/tests/security/class-diagnostic-vulnerable-plugin-detection.php`
- **Slug:** `vulnerable-plugin-detection`
- **Category:** `security`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 75+ (critical)
- **Auto-fixable:** No (user must approve updates)
- **KB Article:** `https://wpshadow.com/kb/security-vulnerable-plugin-detection`

## Testing Pattern
```php
// Test 1: Detects vulnerable plugin
public function testDetectsVulnerablePlugin() {
    // Mock plugin with known CVE
    // Run diagnostic
    // Assert finding returned with threat_level >= 75
}

// Test 2: Passes with current plugins
public function testPassesWithCurrentPlugins() {
    // Mock current plugin versions
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Extends Diagnostic_Base correctly
- [ ] Returns null when no vulnerabilities
- [ ] Returns proper finding array structure
- [ ] Includes all metadata fields
- [ ] KB article URL is correct
- [ ] PHPCS standards pass
- [ ] Activity Logger KPI tracking works
```

---

## 2️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Database User Privileges Validation`  
Labels: `diagnostic,security,enhancement,phase1`

```
## Description
Validate that WordPress database user has minimal required privileges (not SUPER).

## What It Checks
- Runs `SHOW GRANTS` for current database user
- Checks if SUPER privilege is granted (should not be)
- Flags overly permissive privileges (FILE, PROCESS, RELOAD)
- Validates least-privilege principle

## Why Valuable
- Database hardening: least-privilege principle
- SUPER privilege increases attack surface
- Helps sites pass security audits

## Success Criteria
✅ Detects SUPER privilege  
✅ Shows which privileges are excessive  
✅ Explains why least-privilege matters  
✅ Handles MySQL/MariaDB  
✅ Gracefully handles permission denied errors  
✅ KPI: "Database hardened"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/security/class-diagnostic-database-user-privileges.php`
- **Slug:** `database-user-privileges`
- **Category:** `security`
- **Threat Level:** 50 (high) for SUPER privilege
- **Auto-fixable:** No (requires hosting provider)
- **KB Article:** `https://wpshadow.com/kb/security-database-user-privileges`

## Testing Pattern
- Test with SUPER granted → critical threat
- Test with minimal privileges → passes
- Test permission denied → graceful handling
- Test both MySQL and MariaDB formats

## Validation Checklist
- [ ] Safely executes SHOW GRANTS (no modifications)
- [ ] Returns threat_level 75+ for SUPER
- [ ] Handles database errors gracefully
- [ ] PHPCS standards pass
```

---

## 3️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Admin User Enumeration Risk`  
Labels: `diagnostic,security,enhancement,phase1`

```
## Description
Detect if WordPress REST API exposes admin usernames via `/wp-json/wp/v2/users` endpoint.

## What It Checks
- Queries WordPress REST API users endpoint
- Verifies if user login/ID information is exposed
- Checks if endpoint accessible without authentication
- Validates access control headers

## Why Valuable
- Information disclosure attack vector
- Attackers enumerate valid admin accounts
- Common WordPress vulnerability in default config

## Success Criteria
✅ Detects REST API user enumeration  
✅ Shows exposed users  
✅ Auto-fixable via filter  
✅ Tests API response  
✅ KPI: "User enumeration blocked"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/security/class-diagnostic-rest-user-enumeration.php`
- **Slug:** `rest-user-enumeration`
- **Category:** `security`
- **Threat Level:** 50 (high)
- **Auto-fixable:** Yes (can add filter)
- **KB Article:** `https://wpshadow.com/kb/security-rest-user-enumeration`

## Testing Pattern
- REST API enabled/disabled scenarios
- Default settings → should find exposure
- With enumeration disabled → should pass
- Mock HTTP responses

## Validation Checklist
- [ ] Queries REST API correctly
- [ ] No false positives
- [ ] PHPCS standards pass
```

---

## 4️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Weak WordPress Salt/Security Keys`  
Labels: `diagnostic,security,enhancement,phase1`

```
## Description
Detect if wp-config.php has default WordPress.org salt values instead of unique keys.

## What It Checks
- Reads wp-config.php safely
- Extracts AUTH_KEY, SECURE_AUTH_KEY, LOGGED_IN_KEY, NONCE_KEY values
- Compares against WordPress.org default keys
- Flags weak or default keys

## Why Valuable
- Default keys = compromised across millions of sites
- Each site should have unique, random keys
- Critical for session security

## Success Criteria
✅ Detects default salt values  
✅ Shows how to regenerate  
✅ Auto-fixable (needs backup)  
✅ Validates key uniqueness  
✅ KPI: "Security keys regenerated"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/security/class-diagnostic-weak-wordpress-salts.php`
- **Slug:** `weak-wordpress-salts`
- **Category:** `security`
- **Threat Level:** 75 (critical)
- **Auto-fixable:** Yes (needs backup first)
- **KB Article:** `https://wpshadow.com/kb/security-weak-wordpress-salts`

## Testing Pattern
- Test with default keys → should flag
- Test with unique keys → should pass
- Test read-only wp-config → handle gracefully
- Mock file operations

## Validation Checklist
- [ ] Reads wp-config safely (no leaks)
- [ ] Compares keys correctly
- [ ] PHPCS standards pass
```

---

## 5️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Login Attempt Rate Limiting`  
Labels: `diagnostic,security,enhancement,phase1`

```
## Description
Detect if login attempts are rate-limited to prevent brute force attacks.

## What It Checks
- Verifies rate limiting is active on /wp-login.php
- Checks for failed login delays/blocks
- Validates rate limiting headers
- Tests brute force protection mechanism

## Why Valuable
- Default WordPress has no rate limiting
- Rate limiting prevents automated password guessing
- Effective against dictionary attacks

## Success Criteria
✅ Detects if rate limiting active  
✅ Shows protection status  
✅ Suggests solutions  
✅ Tests endpoint safely  
✅ KPI: "Failed login attempts blocked: N"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/security/class-diagnostic-login-rate-limiting.php`
- **Slug:** `login-rate-limiting`
- **Category:** `security`
- **Threat Level:** 50-75 (high to critical)
- **Auto-fixable:** Depends (suggest Guardian/Wordfence)
- **KB Article:** `https://wpshadow.com/kb/security-login-rate-limiting`

## Testing Pattern
- With rate limiting active → should pass
- Without rate limiting → should flag
- Multiple failed logins → check throttling
- Mock HTTP responses

## Validation Checklist
- [ ] Tests endpoint safely
- [ ] Doesn't trigger false lockouts
- [ ] PHPCS standards pass
```

---

## 6️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: SSL Certificate Expiration Monitoring`  
Labels: `diagnostic,security,enhancement,phase1`

```
## Description
Monitor SSL certificate expiration and alert when approaching.

## What It Checks
- Retrieves SSL certificate from domain
- Extracts expiration date
- Calculates days until expiration
- Triggers warnings: 30 days (medium), 7 days (critical)

## Why Valuable
- Expired SSL = broken HTTPS and browser warnings
- Users leave site with security errors
- Prevents unexpected outages

## Success Criteria
✅ Detects SSL expiration date  
✅ Shows days remaining  
✅ Warning at 30 days (threat 50)  
✅ Critical at 7 days (threat 75)  
✅ Handles missing SSL  
✅ KPI: "SSL monitoring active"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/security/class-diagnostic-ssl-expiration.php`
- **Slug:** `ssl-expiration`
- **Category:** `security`
- **Threat Level:** 50 (30 days), 75 (7 days)
- **Auto-fixable:** No (hosting provider responsibility)
- **KB Article:** `https://wpshadow.com/kb/security-ssl-expiration`

## Testing Pattern
- Valid SSL (future) → passes
- 30 days remaining → medium threat
- 7 days remaining → critical threat
- Expired SSL → high threat
- Missing SSL → graceful handling

## Validation Checklist
- [ ] Reads SSL cert correctly
- [ ] Calculates expiration correctly
- [ ] Proper threat_levels
- [ ] PHPCS standards pass
```

---

# PHASE 2: PERFORMANCE (5 Issues)

## 7️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Database Query Performance Audit`  
Labels: `diagnostic,performance,enhancement,phase2`

```
## Description
Identify slow SQL queries and suggest database index improvements.

## What It Checks
- Enables SAVEQUERIES constant
- Captures all database queries
- Identifies queries > 0.1s execution time
- Analyzes WHERE clauses for missing indexes
- Reports TOP 10 slowest queries

## Why Valuable
- Slow queries are #1 performance killer (after hosting)
- Database optimization = 50-80% improvement
- Users can target fixes immediately

## Success Criteria
✅ Identifies queries > 0.1s  
✅ Shows execution times  
✅ Suggests indexes  
✅ Performance < 30 seconds  
✅ Doesn't modify database  
✅ KPI: "Average query time: Xms"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/performance/class-diagnostic-query-performance.php`
- **Slug:** `query-performance`
- **Category:** `performance`
- **Threat Level:** 50 (high)
- **Auto-fixable:** No (requires manual index creation)
- **KB Article:** `https://wpshadow.com/kb/performance-query-performance`

## Testing Pattern
- Mock slow query execution
- Mock SAVEQUERIES data
- Test large result sets
- Validate timing calculation
- Test TOP 10 slowest identification

## Validation Checklist
- [ ] Captures queries correctly
- [ ] Identifies slow queries
- [ ] Suggests indexes
- [ ] KPI tracking works
- [ ] PHPCS standards pass
```

---

## 8️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Static Asset Caching Headers`  
Labels: `diagnostic,performance,enhancement,phase2`

```
## Description
Check if CSS/JS/images have proper Cache-Control headers.

## What It Checks
- Fetches CSS/JS/image files
- Examines Cache-Control directives
- Validates cache duration (should be > 7 days)
- Checks ETag and Last-Modified headers

## Why Valuable
- Missing headers = full download every page load
- Proper headers = 30-50% faster subsequent loads
- Reduces bandwidth costs

## Success Criteria
✅ Detects missing Cache-Control  
✅ Shows current durations  
✅ Suggests 1 year for static  
✅ Handles CDN  
✅ KPI: "Assets cached for X days"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/performance/class-diagnostic-asset-caching-headers.php`
- **Slug:** `asset-caching-headers`
- **Category:** `performance`
- **Threat Level:** 25-50 (medium)
- **Auto-fixable:** Yes (add .htaccess rules)
- **KB Article:** `https://wpshadow.com/kb/performance-asset-caching-headers`

## Testing Pattern
- No cache headers → should flag
- Short cache (7 days) → warning
- Long cache (1 year) → pass
- Mock HTTP responses

## Validation Checklist
- [ ] Fetches assets correctly
- [ ] Parses headers
- [ ] Shows performance impact
- [ ] PHPCS standards pass
```

---

## 9️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Lazy Loading Implementation`  
Labels: `diagnostic,performance,enhancement,phase2`

```
## Description
Detect if images use lazy loading for off-screen images.

## What It Checks
- Fetches homepage and sample posts
- Parses HTML for img tags
- Checks for loading='lazy' attribute
- Validates native lazy loading support

## Why Valuable
- Initial page load 30-50% faster
- Reduces initial bandwidth
- Better Core Web Vitals

## Success Criteria
✅ Detects images without lazy loading  
✅ Shows percentage lazy-loaded  
✅ Suggests best practices  
✅ Validates implementation  
✅ KPI: "Page load time reduced by X%"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/performance/class-diagnostic-lazy-loading.php`
- **Slug:** `lazy-loading`
- **Category:** `performance`
- **Threat Level:** 25-50 (medium)
- **Auto-fixable:** Yes (inject loading='lazy')
- **KB Article:** `https://wpshadow.com/kb/performance-lazy-loading`

## Testing Pattern
- No lazy loading → should flag
- Partial lazy loading → show percentage
- Full lazy loading → should pass
- Parse HTML correctly

## Validation Checklist
- [ ] Parses HTML correctly
- [ ] Identifies images
- [ ] Shows status
- [ ] PHPCS standards pass
```

---

## 🔟 COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Unused CSS/JavaScript Detection`  
Labels: `diagnostic,performance,enhancement,phase2`

```
## Description
Identify CSS/JS enqueued but not used on pages.

## What It Checks
- Checks global $wp_scripts and $wp_styles
- Compares with actual HTML rendering
- Identifies unused assets
- Calculates unused payload size

## Why Valuable
- Plugin registers asset but never uses it (common)
- Unnecessary assets slow pages
- Identifies optimization opportunities

## Success Criteria
✅ Detects unused CSS/JS  
✅ Shows file sizes  
✅ Calculates savings  
✅ Handles conditionals  
✅ KPI: "X% of assets unused"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/performance/class-diagnostic-unused-assets.php`
- **Slug:** `unused-assets`
- **Category:** `performance`
- **Threat Level:** 10-25 (low to medium)
- **Auto-fixable:** No (too risky)
- **KB Article:** `https://wpshadow.com/kb/performance-unused-assets`

## Testing Pattern
- Mock $wp_scripts and $wp_styles
- Mock HTML rendering
- Test combinations
- Validate calculations

## Validation Checklist
- [ ] Compares with globals
- [ ] Identifies unused
- [ ] Calculates savings
- [ ] PHPCS standards pass
```

---

## 1️⃣1️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: CDN Readiness Check`  
Labels: `diagnostic,performance,enhancement,phase2`

```
## Description
Analyze if site structure is ready for CDN integration.

## What It Checks
- Checks for absolute vs relative URLs
- Validates asset path consistency
- Detects dynamic URL issues
- Tests CDN header compatibility

## Why Valuable
- CDN = 30-80% faster delivery globally
- Positions for scaling
- Identifies blockers early

## Success Criteria
✅ Detects URL issues  
✅ Shows CDN status  
✅ Suggests changes  
✅ Handles subdomains  
✅ KPI: "Site CDN-ready"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/performance/class-diagnostic-cdn-readiness.php`
- **Slug:** `cdn-readiness`
- **Category:** `performance`
- **Threat Level:** 10-25 (low to medium)
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/performance-cdn-readiness`

## Testing Pattern
- Various URL structures
- Protocol-relative URLs
- Absolute URLs
- Validate compatibility

## Validation Checklist
- [ ] Analyzes URLs
- [ ] Shows status
- [ ] Suggests changes
- [ ] PHPCS standards pass
```

---

# PHASE 3: CODE QUALITY & SEO (8 Issues)

## 1️⃣2️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: PHP Error Logging Status`  
Labels: `diagnostic,code-quality,enhancement,phase3`

```
## Description
Verify error logs exist and are being written to.

## What It Checks
- Check WP_DEBUG is enabled
- Validate debug.log exists and writable
- Count errors from past 7 days
- Flag debug enabled on production without protection

## Why Valuable
- Developers can't fix errors they don't see
- Silent failures hide bugs
- Error logs are critical for debugging

## Success Criteria
✅ Error logging active  
✅ Log file exists  
✅ Counts errors  
✅ Proper permissions  
✅ KPI: "Error logging active; X errors"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/code-quality/class-diagnostic-php-error-logging.php`
- **Slug:** `php-error-logging`
- **Category:** `code-quality`
- **Threat Level:** 25-50
- **Auto-fixable:** Yes (enable logging)
- **KB Article:** `https://wpshadow.com/kb/code-quality-php-error-logging`

## Testing Pattern
- WP_DEBUG on/off
- Log file writable/not
- Count errors
- Mock file ops

## Validation Checklist
- [ ] Detects WP_DEBUG
- [ ] Validates log location
- [ ] Counts errors
- [ ] PHPCS standards pass
```

---

## 1️⃣3️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: WordPress Coding Standards Compliance`  
Labels: `diagnostic,code-quality,enhancement,phase3`

```
## Description
Check if theme/plugin code follows WordPress standards.

## What It Checks
- Analyze custom theme/plugin files
- Check PHPCS violations
- Report naming issues
- Detect spacing problems

## Why Valuable
- Consistent code easier to maintain
- Standards ensure quality
- Makes code more readable

## Success Criteria
✅ Detects violations  
✅ Shows details  
✅ Shows compliance %  
✅ KPI: "Code standards: X%"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/code-quality/class-diagnostic-coding-standards.php`
- **Slug:** `coding-standards-compliance`
- **Category:** `code-quality`
- **Threat Level:** 10-25
- **Auto-fixable:** Partial
- **KB Article:** `https://wpshadow.com/kb/code-quality-coding-standards`

## Testing Pattern
- Mock files with violations
- Mock PHPCS output
- Test compliance calc

## Validation Checklist
- [ ] Integrates PHPCS
- [ ] Reports violations
- [ ] Calculates %
- [ ] PHPCS standards pass
```

---

## 1️⃣4️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Function Naming Convention Audit`  
Labels: `diagnostic,code-quality,enhancement,phase3`

```
## Description
Validate custom plugin functions use snake_case convention.

## What It Checks
- Parse plugin files for functions
- Extract function names
- Validate naming format
- Flag violations

## Why Valuable
- WordPress convention consistency
- Prevents naming conflicts
- Makes code readable

## Success Criteria
✅ Detects violations  
✅ Shows compliance %  
✅ Suggests correct names  
✅ KPI: "Naming compliance: X%"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/code-quality/class-diagnostic-function-naming.php`
- **Slug:** `function-naming-convention`
- **Category:** `code-quality`
- **Threat Level:** 10-20
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/code-quality-function-naming`

## Testing Pattern
- Parse PHP correctly
- Extract names
- Validate format
- Test edge cases

## Validation Checklist
- [ ] Parses functions
- [ ] Validates format
- [ ] Shows violations
- [ ] PHPCS standards pass
```

---

## 1️⃣5️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Dead Code Detection`  
Labels: `diagnostic,code-quality,enhancement,phase3`

```
## Description
Identify functions defined but never called.

## What It Checks
- Parse functions in theme/plugins
- Search codebase for usage
- Identify orphaned functions
- Calculate dead code %

## Why Valuable
- Technical debt
- Clutters codebase
- Impacts readability

## Success Criteria
✅ Detects unused functions  
✅ Shows list  
✅ KPI: "Unused functions: N"  
✅ Shows dead code %  

## Technical Requirements
- **File:** `includes/diagnostics/tests/code-quality/class-diagnostic-dead-code.php`
- **Slug:** `dead-code-detection`
- **Category:** `code-quality`
- **Threat Level:** 10-25
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/code-quality-dead-code`

## Testing Pattern
- Parse PHP
- Track definitions
- Search usage
- Test edge cases

## Validation Checklist
- [ ] Parses functions
- [ ] Searches for use
- [ ] Identifies dead code
- [ ] PHPCS standards pass
```

---

## 1️⃣6️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Missing Meta Tags Audit`  
Labels: `diagnostic,seo,enhancement,phase3`

```
## Description
Check pages for missing title, meta description, OG tags, structured data.

## What It Checks
- Fetch homepage + sample posts
- Parse HTML for meta tags
- Check title tag
- Check meta description
- Check OG tags
- Check structured data (schema.org)

## Why Valuable
- Missing SEO tags = poor search visibility
- Poor social sharing without OG tags
- Structured data helps search ranking

## Success Criteria
✅ Detects missing tags  
✅ Shows completion %  
✅ Suggests best practices  
✅ KPI: "SEO tags completeness: X%"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/seo/class-diagnostic-missing-meta-tags.php`
- **Slug:** `missing-meta-tags`
- **Category:** `seo`
- **Threat Level:** 25-50
- **Auto-fixable:** No (needs plugin like Yoast)
- **KB Article:** `https://wpshadow.com/kb/seo-missing-meta-tags`

## Testing Pattern
- Fetch pages
- Parse HTML
- Check tags
- Validate content

## Validation Checklist
- [ ] Fetches pages
- [ ] Parses HTML
- [ ] Finds tags
- [ ] Shows completion
- [ ] PHPCS standards pass
```

---

## 1️⃣7️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Sitemap Quality Check`  
Labels: `diagnostic,seo,enhancement,phase3`

```
## Description
Verify sitemap exists, valid XML, includes all public posts.

## What It Checks
- Fetch sitemap.xml
- Validate XML structure
- Count URLs included
- Check for broken links
- Verify post inclusion

## Why Valuable
- Search engines use sitemaps to discover content
- Missing sitemap = slower indexing
- Broken sitemaps prevent crawling

## Success Criteria
✅ Sitemap exists  
✅ Valid XML  
✅ Includes all posts  
✅ KPI: "Sitemap status: X URLs"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/seo/class-diagnostic-sitemap-quality.php`
- **Slug:** `sitemap-quality`
- **Category:** `seo`
- **Threat Level:** 25-50
- **Auto-fixable:** Yes (regenerate)
- **KB Article:** `https://wpshadow.com/kb/seo-sitemap-quality`

## Testing Pattern
- Fetch sitemap.xml
- Parse XML
- Validate structure
- Count URLs

## Validation Checklist
- [ ] Fetches sitemap
- [ ] Validates XML
- [ ] Counts URLs
- [ ] PHPCS standards pass
```

---

## 1️⃣8️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Robots.txt Validation`  
Labels: `diagnostic,seo,enhancement,phase3`

```
## Description
Validate robots.txt exists and doesn't block search engines.

## What It Checks
- Fetch robots.txt
- Parse rules
- Check for Disallow: /
- Validate syntax
- Verify User-agent directives

## Why Valuable
- Accidental Disallow: / blocks entire site
- Malformed robots.txt breaks crawling
- Proper config ensures indexing

## Success Criteria
✅ Robots.txt exists  
✅ Valid syntax  
✅ Not blocking search  
✅ KPI: "Robots.txt valid"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/seo/class-diagnostic-robots-txt-validation.php`
- **Slug:** `robots-txt-validation`
- **Category:** `seo`
- **Threat Level:** 25-50
- **Auto-fixable:** Yes (create default)
- **KB Article:** `https://wpshadow.com/kb/seo-robots-txt-validation`

## Testing Pattern
- Fetch robots.txt
- Parse rules
- Validate syntax
- Check directives

## Validation Checklist
- [ ] Fetches file
- [ ] Parses rules
- [ ] Validates syntax
- [ ] PHPCS standards pass
```

---

## 1️⃣9️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Internal Linking Health`  
Labels: `diagnostic,seo,enhancement,phase3`

```
## Description
Analyze content for strategic internal links; identify orphaned pages.

## What It Checks
- Crawl site for all posts/pages
- Build link graph
- Identify pages with no internal links
- Check for orphaned content
- Validate link distribution

## Why Valuable
- Internal links distribute page authority
- Orphaned pages underperform
- Strategic linking improves ranking

## Success Criteria
✅ Detects orphaned pages  
✅ Shows link density  
✅ Suggests linking opportunities  
✅ KPI: "Internal link density: X%"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/seo/class-diagnostic-internal-linking.php`
- **Slug:** `internal-linking-health`
- **Category:** `seo`
- **Threat Level:** 10-25
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/seo-internal-linking`

## Testing Pattern
- Crawl site
- Build link graph
- Identify isolated
- Calculate density

## Validation Checklist
- [ ] Crawls site
- [ ] Builds graph
- [ ] Finds isolated
- [ ] Shows density
- [ ] PHPCS standards pass
```

---

# PHASE 4: DESIGN, SETTINGS, MONITORING (11 Issues)

## 2️⃣0️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: WCAG Color Contrast Validation`  
Labels: `diagnostic,design,enhancement,phase4`

```
## Description
Verify all text meets WCAG AA (4.5:1) or AAA (7:1) contrast ratios.

## What It Checks
- Fetch homepage and key pages
- Extract computed colors
- Calculate contrast ratios
- Flag below 4.5:1
- Test different zoom levels

## Why Valuable
- Accessibility: helps 1 in 12 males (color blindness)
- Legal compliance requirement
- Better UX for low-vision users

## Success Criteria
✅ Detects contrast issues  
✅ Shows contrast ratios  
✅ Meets WCAG AA standard  
✅ KPI: "Contrast compliance: X%"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/design/class-diagnostic-wcag-contrast.php`
- **Slug:** `wcag-color-contrast`
- **Category:** `design`
- **Threat Level:** 50-75
- **Auto-fixable:** No (needs design changes)
- **KB Article:** `https://wpshadow.com/kb/design-wcag-contrast`

## Testing Pattern
- Fetch pages
- Extract colors
- Calculate ratios
- Flag violations

## Validation Checklist
- [ ] Fetches pages
- [ ] Extracts colors
- [ ] Calculates ratios
- [ ] Meets WCAG AA
- [ ] PHPCS standards pass
```

---

## 2️⃣1️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Mobile Responsiveness Check`  
Labels: `diagnostic,design,enhancement,phase4`

```
## Description
Check if pages render correctly on mobile.

## What It Checks
- Headless browser viewport check
- Validate viewport meta tag
- Check responsive CSS
- Test on common breakpoints
- Detect horizontal scroll

## Why Valuable
- 60%+ traffic is mobile
- Bad mobile UX = higher bounce
- Google ranks mobile-first

## Success Criteria
✅ Mobile viewport tag present  
✅ Responsive CSS works  
✅ No horizontal scroll  
✅ Readable font size  
✅ KPI: "Mobile score: X/100"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/design/class-diagnostic-mobile-responsiveness.php`
- **Slug:** `mobile-responsiveness`
- **Category:** `design`
- **Threat Level:** 50-75
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/design-mobile-responsiveness`

## Testing Pattern
- Headless browser
- Check viewport
- Test breakpoints
- Validate CSS

## Validation Checklist
- [ ] Checks viewport
- [ ] Tests responsive
- [ ] Validates CSS
- [ ] PHPCS standards pass
```

---

## 2️⃣2️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Font Loading Performance`  
Labels: `diagnostic,design,enhancement,phase4`

```
## Description
Check if fonts use font-display: swap and prevent layout shifts.

## What It Checks
- Parse CSS for font-face rules
- Check font-display values
- Detect layout shift (CLS)
- Validate loading strategy

## Why Valuable
- Font loading delays rendering
- Layout shift poor UX
- font-display: swap is best practice

## Success Criteria
✅ Font-display: swap set  
✅ No layout shift  
✅ Optimal loading  
✅ KPI: "Font rendering optimized"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/design/class-diagnostic-font-loading.php`
- **Slug:** `font-loading-performance`
- **Category:** `design`
- **Threat Level:** 10-25
- **Auto-fixable:** Yes (update CSS)
- **KB Article:** `https://wpshadow.com/kb/design-font-loading`

## Testing Pattern
- Parse CSS
- Check font-display
- Detect shift
- Validate strategy

## Validation Checklist
- [ ] Parses CSS
- [ ] Checks display
- [ ] Detects shift
- [ ] PHPCS standards pass
```

---

## 2️⃣3️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Dark Mode Support`  
Labels: `diagnostic,design,enhancement,phase4`

```
## Description
Verify theme respects prefers-color-scheme media query.

## What It Checks
- Headless browser dark mode preference
- Check CSS media queries
- Validate dark mode colors
- Test contrast in dark mode

## Why Valuable
- 30%+ users prefer dark mode
- Improves accessibility
- Reduces eye strain

## Success Criteria
✅ Respects prefers-color-scheme  
✅ Dark mode CSS present  
✅ Proper contrast  
✅ KPI: "Dark mode compatible"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/design/class-diagnostic-dark-mode-support.php`
- **Slug:** `dark-mode-support`
- **Category:** `design`
- **Threat Level:** 10-25
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/design-dark-mode-support`

## Testing Pattern
- Headless browser with dark mode
- Check media queries
- Validate colors
- Test contrast

## Validation Checklist
- [ ] Checks media query
- [ ] Tests rendering
- [ ] Validates contrast
- [ ] PHPCS standards pass
```

---

## 2️⃣4️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: WordPress Version Freshness`  
Labels: `diagnostic,settings,enhancement,phase4`

```
## Description
Check if WordPress is updated to latest version.

## What It Checks
- Get current WP version
- Fetch latest version from wordpress.org API
- Calculate version lag
- Flag if 2+ versions behind

## Why Valuable
- 90% of hacks exploit known vulnerabilities
- Old versions = major security risk

## Success Criteria
✅ Detects current version  
✅ Shows available version  
✅ Alerts if outdated  
✅ Shows version lag  
✅ KPI: "WordPress current"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/settings/class-diagnostic-wp-version-freshness.php`
- **Slug:** `wordpress-version-freshness`
- **Category:** `settings`
- **Threat Level:** 50-75 if outdated
- **Auto-fixable:** No (needs staging)
- **KB Article:** `https://wpshadow.com/kb/settings-wp-version-freshness`

## Testing Pattern
- Get WP version
- Fetch latest API
- Compare versions
- Calculate lag

## Validation Checklist
- [ ] Gets current version
- [ ] Fetches latest
- [ ] Calculates lag
- [ ] Alerts if old
- [ ] PHPCS standards pass
```

---

## 2️⃣5️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Plugin/Theme Active Count`  
Labels: `diagnostic,settings,enhancement,phase4`

```
## Description
Alert if too many active plugins/themes (>50 = slow + risk).

## What It Checks
- Count active plugins
- Count active themes
- Flag excessive count
- Benchmark performance impact

## Why Valuable
- Each plugin = potential vulnerability
- Too many plugins = slow site
- Consolidation improves security

## Success Criteria
✅ Counts plugins  
✅ Shows recommendations  
✅ Flags excessive  
✅ KPI: "Active plugins: N"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/settings/class-diagnostic-active-count.php`
- **Slug:** `plugin-theme-active-count`
- **Category:** `settings`
- **Threat Level:** 25-50 if > 50 active
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/settings-active-count`

## Testing Pattern
- Count plugins
- Count themes
- Flag if excessive
- Test performance

## Validation Checklist
- [ ] Counts correctly
- [ ] Shows recs
- [ ] Flags if > 50
- [ ] PHPCS standards pass
```

---

## 2️⃣6️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Admin Email Configuration`  
Labels: `diagnostic,settings,enhancement,phase4`

```
## Description
Verify admin email is valid, not generic, and alerts working.

## What It Checks
- Validate email format
- Check if not generic (not admin@example.com)
- Not shared/forwarded
- Validate MX records

## Why Valuable
- Compromised admin email = account takeover
- Alerts must go to valid email
- Generic emails at risk

## Success Criteria
✅ Email is valid  
✅ Not generic  
✅ MX records good  
✅ Alerts working  
✅ KPI: "Admin contact configured"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/settings/class-diagnostic-admin-email.php`
- **Slug:** `admin-email-configuration`
- **Category:** `settings`
- **Threat Level:** 25-50 if generic
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/settings-admin-email`

## Testing Pattern
- Validate format
- Check if generic
- Validate MX
- Test alerts

## Validation Checklist
- [ ] Validates format
- [ ] Checks generic
- [ ] Validates MX
- [ ] PHPCS standards pass
```

---

## 2️⃣7️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Site Uptime History`  
Labels: `diagnostic,monitoring,enhancement,phase4`

```
## Description
Track site uptime over past 30 days and detect patterns.

## What It Checks
- Query local uptime tracking data
- Calculate percentage for 24h, 7d, 30d
- Detect downtime patterns
- Flag if < 99%

## Why Valuable
- Early warning of hosting issues
- Quantifiable reliability
- KPI for service level

## Success Criteria
✅ Tracks uptime  
✅ Shows percentages  
✅ Detects patterns  
✅ Alerts if < 99%  
✅ KPI: "Uptime: X% (30 days)"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/monitoring/class-diagnostic-uptime-history.php`
- **Slug:** `site-uptime-history`
- **Category:** `monitoring`
- **Threat Level:** 25-50 if < 99%
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/monitoring-uptime-history`

## Testing Pattern
- Query uptime data
- Calculate %
- Test patterns
- Validate alerts

## Validation Checklist
- [ ] Queries data
- [ ] Calculates %
- [ ] Shows trend
- [ ] PHPCS standards pass
```

---

## 2️⃣8️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: SSL Certificate Chain Validation`  
Labels: `diagnostic,monitoring,enhancement,phase4`

```
## Description
Verify SSL cert, intermediate certs, and root form valid chain.

## What It Checks
- Retrieve full SSL certificate chain
- Validate cert signature chain
- Check intermediate certs
- Verify root certificate

## Why Valuable
- Broken chain = browser warnings
- Users distrust site
- Certificate validation critical

## Success Criteria
✅ Cert chain valid  
✅ No breaks detected  
✅ All certs current  
✅ KPI: "SSL chain valid"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/monitoring/class-diagnostic-ssl-chain.php`
- **Slug:** `ssl-certificate-chain`
- **Category:** `monitoring`
- **Threat Level:** 50-75 if broken
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/monitoring-ssl-chain`

## Testing Pattern
- Retrieve chain
- Validate sigs
- Check certs
- Verify root

## Validation Checklist
- [ ] Retrieves chain
- [ ] Validates sigs
- [ ] Checks certs
- [ ] PHPCS standards pass
```

---

## 2️⃣9️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Email Deliverability Health`  
Labels: `diagnostic,monitoring,enhancement,phase5`

```
## Description
Check SPF/DKIM/DMARC records are configured properly.

## What It Checks
- Query DNS for SPF record
- Check DKIM configuration
- Validate DMARC policy
- Verify mail server setup

## Why Valuable
- Bad email config = emails go to spam
- Users don't get notifications
- Deliverability critical

## Success Criteria
✅ SPF configured  
✅ DKIM enabled  
✅ DMARC policy set  
✅ Proper alignment  
✅ KPI: "Email deliverability: X%"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/monitoring/class-diagnostic-email-deliverability.php`
- **Slug:** `email-deliverability-health`
- **Category:** `monitoring`
- **Threat Level:** 25-50 if misconfigured
- **Auto-fixable:** No (DNS only)
- **KB Article:** `https://wpshadow.com/kb/monitoring-email-deliverability`

## Testing Pattern
- Query DNS
- Check records
- Validate policy
- Verify alignment

## Validation Checklist
- [ ] Queries DNS
- [ ] Checks records
- [ ] Validates policy
- [ ] PHPCS standards pass
```

---

## 3️⃣0️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Backup Frequency Validation`  
Labels: `diagnostic,monitoring,enhancement,phase5`

```
## Description
Verify backups created regularly (last backup < 7 days old).

## What It Checks
- Query backup plugin metadata
- Get last backup date
- Calculate days since backup
- Flag if > 7 days old

## Why Valuable
- Sites without recent backups can't recover
- Backups critical for disaster recovery
- Regular backups save sites

## Success Criteria
✅ Last backup < 7 days  
✅ Backups regular  
✅ Backup system working  
✅ KPI: "Last backup: X days ago"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/monitoring/class-diagnostic-backup-frequency.php`
- **Slug:** `backup-frequency-validation`
- **Category:** `monitoring`
- **Threat Level:** 50-75 if no backups
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/monitoring-backup-frequency`

## Testing Pattern
- Query backup meta
- Get last date
- Calculate days
- Test alerts

## Validation Checklist
- [ ] Queries metadata
- [ ] Gets backup date
- [ ] Calculates days
- [ ] PHPCS standards pass
```

---

# PHASE 5: WORKFLOWS (3 Issues)

## 3️⃣1️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Scheduled Task Execution Health`  
Labels: `diagnostic,workflows,enhancement,phase5`

```
## Description
Monitor WordPress cron jobs to ensure regular execution.

## What It Checks
- Query wp_options for cron data
- Check if next scheduled time > current by > 1 hour
- Check if loopback request succeeds
- Validate cron system status

## Why Valuable
- Stuck cron = automated tasks don't run
- Backups, cleanups don't happen
- Cron health critical for automation

## Success Criteria
✅ Cron executing regularly  
✅ No queue buildup  
✅ Loopback request works  
✅ Tasks on schedule  
✅ KPI: "Scheduled tasks on track"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/workflows/class-diagnostic-scheduled-task-execution.php`
- **Slug:** `scheduled-task-execution-health`
- **Category:** `workflows`
- **Threat Level:** 50-75 if stuck
- **Auto-fixable:** No (requires hosting fix)
- **KB Article:** `https://wpshadow.com/kb/workflows-scheduled-tasks`

## Testing Pattern
- Query cron data
- Check timing
- Test loopback
- Validate status

## Validation Checklist
- [ ] Queries cron data
- [ ] Checks timing
- [ ] Tests loopback
- [ ] PHPCS standards pass
```

---

## 3️⃣2️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Workflow Trigger Validation`  
Labels: `diagnostic,workflows,enhancement,phase5`

```
## Description
Verify workflows have valid triggers and hooks are registered.

## What It Checks
- Load all workflows
- Validate trigger hooks registered
- Check for orphaned workflows
- Verify action hooks exist

## Why Valuable
- Orphaned workflows never execute
- Wasted automation effort
- Triggers must be valid

## Success Criteria
✅ All triggers valid  
✅ Hooks registered  
✅ No orphaned workflows  
✅ KPI: "Active workflows: N"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/workflows/class-diagnostic-workflow-triggers.php`
- **Slug:** `workflow-trigger-validation`
- **Category:** `workflows`
- **Threat Level:** 25-50 if orphaned
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/workflows-trigger-validation`

## Testing Pattern
- Load workflows
- Validate triggers
- Check hooks
- Test registration

## Validation Checklist
- [ ] Loads workflows
- [ ] Validates triggers
- [ ] Checks hooks
- [ ] PHPCS standards pass
```

---

## 3️⃣3️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Workflow Execution Performance`  
Labels: `diagnostic,workflows,enhancement,phase5`

```
## Description
Monitor workflow completion times and identify bottlenecks.

## What It Checks
- Track workflow execution times
- Identify slow steps
- Check for timeouts
- Validate performance metrics

## Why Valuable
- Slow workflows = missed triggers
- Timeouts cause failures
- Performance impacts automation

## Success Criteria
✅ Execution times tracked  
✅ No timeouts  
✅ Performance acceptable  
✅ Bottlenecks identified  
✅ KPI: "Workflow time: Xms"  

## Technical Requirements
- **File:** `includes/diagnostics/tests/workflows/class-diagnostic-workflow-performance.php`
- **Slug:** `workflow-execution-performance`
- **Category:** `workflows`
- **Threat Level:** 10-50 if slow
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/workflows-execution-performance`

## Testing Pattern
- Track execution
- Identify slow steps
- Check timeouts
- Validate metrics

## Validation Checklist
- [ ] Tracks execution
- [ ] Identifies slow
- [ ] Checks timeouts
- [ ] PHPCS standards pass
```

---

# Summary

33 total issues ready to create in GitHub. Each includes:
- Clear description
- Success criteria
- Technical requirements  
- Testing patterns
- Validation checklist

**Labels to use:**
- `diagnostic`
- Category: `security`, `performance`, `code-quality`, `seo`, `design`, `settings`, `monitoring`, `workflows`
- `enhancement`
- Phase: `phase1`, `phase2`, `phase3`, `phase4`, `phase5`

**Total time estimate to create all issues:** ~30 minutes (3-4 issues per minute if copy-pasting)
