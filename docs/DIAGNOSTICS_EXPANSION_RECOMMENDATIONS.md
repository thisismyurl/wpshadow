# WPShadow Diagnostic Expansion Recommendations

**Date:** January 27, 2026  
**Status:** Strategic Analysis & Recommendations  
**Scope:** New diagnostics aligned with core commandments and dashboard categories

---

## Current Dashboard Categories (10 Gauges)

The dashboard displays 10 health gauges across these categories:

1. **🛡️ Security** - Vulnerabilities, hardening, protection
2. **⚡ Performance** - Speed, caching, optimization
3. **💻 Code Quality** - Standards, best practices, technical debt
4. **🔍 SEO** - Search visibility, optimization, discoverability
5. **🎨 Design** - Visual design, UX, accessibility
6. **⚙️ Settings** - WordPress configuration, core settings
7. **👁️ Monitoring** - Uptime, alerts, health checks
8. **🔄 Workflows** - Automation, scheduling, task execution
9. **📊 WordPress Health** - WordPress native Site Health
10. **💚 Overall Health** - Calculated aggregate score

---

## Analysis: Genuine Value Diagnostics

### **Alignment with Core Commandments**

Based on WPShadow's 11 Commandments (from copilot-instructions.md):

| Commandment | Application to New Diagnostics |
|---|---|
| **Helpful Neighbor Experience** | Show WHY an issue matters + actionable solutions |
| **Free as Possible** | All diagnostics free; auto-fixes free where possible |
| **Ridiculously Good for Free** | Better diagnostic coverage than premium competitors |
| **Drive to Knowledge Base** | Every diagnostic links to educational KB articles |
| **Everything Has a KPI** | Each fix logs measurable impact (time, security, performance) |
| **Privacy First** | No external API calls without explicit opt-in consent |
| **Inspire Confidence** | Always show backups before risky changes; undo available |
| **Talk-About-Worthy** | Features that surprise and delight users |

---

## Recommended New Diagnostics (By Category)

### 1. **SECURITY** (6 high-impact additions)

#### A. **Vulnerable Plugin Detection** ⭐⭐⭐⭐⭐
- **What it checks:** Scans installed plugins against known CVE database
- **Why valuable:** #1 WordPress vulnerability vector; users don't know their plugins are vulnerable
- **Testable:** ✅ Mock CVE database in tests; validate against known-vulnerable plugin versions
- **Auto-fixable:** ⚠️ Warning only (don't auto-update; show update availability)
- **KPI:** Track "Vulnerabilities patched" in activity log
- **Commandment alignment:** Helpful Neighbor (shows CVE links), Drive to KB (link to remediation guides)

**Implementation:**
```php
class Diagnostic_Vulnerable_Plugin_Detection extends Diagnostic_Base {
    // Check each plugin against WordPress.org plugin API
    // Flag if plugin not in official repository or has known CVEs
    // Show severity + update availability
}
```

#### B. **Database User Privileges Validation** ⭐⭐⭐⭐
- **What it checks:** Database user has minimum required privileges (not SUPER)
- **Why valuable:** Hardening: least-privilege principle for DB access
- **Testable:** ✅ Check `SHOW GRANTS` output; test with limited-privilege user
- **Auto-fixable:** ❌ Requires manual DB user recreation
- **KPI:** "Database hardened" (binary security checkpoint)
- **Commandment alignment:** Ridiculously Good (goes beyond basic checks)

#### C. **Admin User Enumeration Risk** ⭐⭐⭐⭐
- **What it checks:** WordPress REST API exposes admin usernames via `/wp-json/wp/v2/users`
- **Why valuable:** Information disclosure; attackers enumerate valid admin accounts
- **Testable:** ✅ Curl REST API endpoint; parse response for user login info
- **Auto-fixable:** ✅ Can disable REST API user enumeration via filter
- **KPI:** "User enumeration blocked"
- **Commandment alignment:** Privacy First (protects admin info)

#### D. **Weak WordPress Salt/Security Keys** ⭐⭐⭐⭐
- **What it checks:** wp-config.php still has default WordPress.org salt values
- **Why valuable:** Default keys = compromised cookies across millions of sites
- **Testable:** ✅ Compare against WordPress.org default key generator output
- **Auto-fixable:** ✅ Can regenerate and update wp-config.php
- **KPI:** "Security keys regenerated"
- **Commandment alignment:** Ridiculously Good (advanced security hardening)

#### E. **Login Attempt Rate Limiting** ⭐⭐⭐⭐
- **What it checks:** Are login attempts being rate-limited against brute force?
- **Why valuable:** Brute force attacks; built-in WordPress has no rate limiting
- **Testable:** ✅ Simulate login attempts; measure response times/IP blocks
- **Auto-fixable:** ⚠️ Needs external service (Guardian Pro feature?) or local implementation
- **KPI:** "Failed login attempts blocked: N"
- **Commandment alignment:** Inspire Confidence (shows attack prevention)

#### F. **SSL Certificate Expiration Monitoring** ⭐⭐⭐⭐⭐
- **What it checks:** SSL cert expiration date (< 30 days = warning, < 7 days = critical)
- **Why valuable:** Expired SSL = broken HTTPS; users see browser warnings
- **Testable:** ✅ Mock future dates; validate warning thresholds
- **Auto-fixable:** ⚠️ Alerting only (renewal is hosting provider responsibility)
- **KPI:** Track "SSL monitoring active"
- **Commandment alignment:** Helpful Neighbor (proactive alerts prevent outages)

---

### 2. **PERFORMANCE** (5 high-impact additions)

#### A. **Database Query Performance Audit** ⭐⭐⭐⭐⭐
- **What it checks:** Slow SQL queries (> 0.1s execution time); missing indexes
- **Why valuable:** Slow queries = site slow; biggest performance killer after hosting
- **Testable:** ✅ Enable `SAVEQUERIES` constant; capture slow queries in tests
- **Auto-fixable:** ⚠️ Show queries; suggest indexes but don't auto-create
- **KPI:** "Average query time: Xms"; track improvement
- **Commandment alignment:** Ridiculously Good (goes beyond surface-level checks)

**Implementation:**
```php
class Diagnostic_Database_Query_Performance extends Diagnostic_Base {
    // Enable SAVEQUERIES during scan
    // Identify queries > 0.1s
    // Extract missing index candidates from WHERE clauses
    // Show TOP 10 slowest queries
}
```

#### B. **Static Asset Caching Headers** ⭐⭐⭐⭐
- **What it checks:** CSS/JS/images have proper `Cache-Control` headers (not 1 year = bad)
- **Why valuable:** Missing caching headers = every page reload fetches full assets
- **Testable:** ✅ curl `-I` to check headers; validate cache directives
- **Auto-fixable:** ✅ Can add `.htaccess` rules or `wp-config.php` headers
- **KPI:** "Assets cached for X days"
- **Commandment alignment:** Helpful Neighbor (shows performance impact: "saves Xsec per page load")

#### C. **Lazy Loading Implementation** ⭐⭐⭐⭐
- **What it checks:** Images use `loading="lazy"` attribute; off-screen images not loaded initially
- **Why valuable:** Initial page load speed; typically 30-50% improvement
- **Testable:** ✅ Parse HTML for `loading` attribute; compare with/without
- **Auto-fixable:** ✅ Can inject `loading="lazy"` into `<img>` tags
- **KPI:** "Page load time reduced by X%"
- **Commandment alignment:** Everything Has a KPI (shows measurable speed improvement)

#### D. **Unused CSS/JavaScript Detection** ⭐⭐⭐
- **What it checks:** Enqueued scripts/styles that aren't actually used on pages
- **Why valuable:** Common scenario: plugin registers asset but never prints it
- **Testable:** ✅ Capture `$wp_scripts` and `$wp_styles`; check if handle called in HTML
- **Auto-fixable:** ⚠️ Warning only (auto-dequeue risky without testing)
- **KPI:** "X% of assets unused"
- **Commandment alignment:** Code Quality (improves page load)

#### E. **Content Delivery Network (CDN) Readiness** ⭐⭐⭐
- **What it checks:** Is site structured to work with CDN? Are assets relative or absolute?
- **Why valuable:** CDN = 30-80% faster content delivery globally
- **Testable:** ✅ Check for absolute URLs vs. relative; validate CDN compatibility
- **Auto-fixable:** ⚠️ Needs CDN plugin integration
- **KPI:** "Site CDN-ready"
- **Commandment alignment:** Ridiculously Good (positions users for scaling)

---

### 3. **CODE QUALITY** (4 high-impact additions)

#### A. **PHP Error Logging Status** ⭐⭐⭐⭐⭐
- **What it checks:** Error logs exist and are being written to; no fatal errors silently failing
- **Why valuable:** Developers can't fix errors they don't see; silent failures hide bugs
- **Testable:** ✅ Trigger test errors; verify they're logged; check log file location
- **Auto-fixable:** ✅ Can create log file; update `wp-config.php` to enable logging
- **KPI:** "Error logging active; X errors in past 7 days"
- **Commandment alignment:** Inspire Confidence (transparency into site health)

**Implementation:**
```php
class Diagnostic_PHP_Error_Logging extends Diagnostic_Base {
    // Check WP_DEBUG enabled
    // Validate wp-content/debug.log exists & writable
    // Count errors from past 7 days
    // Flag if debug enabled on production without protection
}
```

#### B. **WordPress Coding Standards Compliance** ⭐⭐⭐
- **What it checks:** Theme/plugin code follows WordPress coding standards (for custom themes/plugins)
- **Why valuable:** Inconsistent code = maintenance nightmare; hard to read/debug
- **Testable:** ✅ Run PHPCS on theme files; report violations
- **Auto-fixable:** ⚠️ Report only; auto-fix risky
- **KPI:** "Code standards compliance: X%"
- **Commandment alignment:** Code Quality (sets quality baseline)

#### C. **Function Naming Convention Audit** ⭐⭐
- **What it checks:** Custom plugin functions follow snake_case convention (not camelCase)
- **Why valuable:** WordPress convention consistency; prevents naming conflicts
- **Testable:** ✅ Parse custom plugin files; extract function names; validate format
- **Auto-fixable:** ❌ Requires code modification
- **KPI:** "Naming compliance: X%"

#### D. **Dead Code Detection** ⭐⭐⭐
- **What it checks:** Functions defined in themes/plugins but never called
- **Why valuable:** Technical debt; clutters codebase; maintenance burden
- **Testable:** ✅ Parse functions; search for usage in codebase
- **Auto-fixable:** ❌ Too risky to auto-delete
- **KPI:** "Unused functions: N"
- **Commandment alignment:** Code Quality (reduces technical debt)

---

### 4. **SEO** (4 high-impact additions)

#### A. **Missing Meta Tags Audit** ⭐⭐⭐⭐⭐
- **What it checks:** Pages missing title, meta description, OG tags, structured data
- **Why valuable:** Missing SEO tags = poor search visibility + poor social sharing
- **Testable:** ✅ Fetch pages; parse HTML for tags; validate content
- **Auto-fixable:** ⚠️ Can suggest defaults; needs Yoast/RankMath plugin to auto-set
- **KPI:** "SEO tags completeness: X%"
- **Commandment alignment:** Everything Has a KPI (shows search visibility impact)

**Implementation:**
```php
class Diagnostic_Missing_Meta_Tags extends Diagnostic_Base {
    // Fetch homepage + sample posts
    // Check for <title>, <meta name="description">, og:* tags
    // Check for structured data (schema.org)
    // Report missing tags
}
```

#### B. **Sitemap Quality Check** ⭐⭐⭐⭐
- **What it checks:** Sitemap exists, valid XML, includes all public posts
- **Why valuable:** Search engines use sitemaps to discover content; missing = slower indexing
- **Testable:** ✅ Fetch sitemap.xml; validate XML structure; count URLs
- **Auto-fixable:** ✅ Can regenerate with WordPress native sitemap
- **KPI:** "Sitemap status: X URLs indexed"
- **Commandment alignment:** Helpful Neighbor (shows indexing status)

#### C. **Robots.txt Validation** ⭐⭐⭐
- **What it checks:** robots.txt exists and doesn't accidentally block search engines
- **Why valuable:** Accidental `Disallow: /` blocks entire site from indexing
- **Testable:** ✅ Fetch robots.txt; parse rules; validate syntax
- **Auto-fixable:** ✅ Can regenerate default robots.txt
- **KPI:** "Robots.txt valid"

#### D. **Internal Linking Health** ⭐⭐⭐
- **What it checks:** Content uses internal links strategically; orphaned pages (no internal links)
- **Why valuable:** Internal links distribute page authority; orphaned pages underperform
- **Testable:** ✅ Crawl site; build link graph; identify isolated pages
- **Auto-fixable:** ⚠️ Suggest internal link opportunities
- **KPI:** "Internal link density: X%"
- **Commandment alignment:** Drive to KB (suggest KB articles for linking)

---

### 5. **DESIGN** (4 high-impact additions)

#### A. **WCAG Color Contrast Validation** ⭐⭐⭐⭐⭐
- **What it checks:** All text meets WCAG AA (4.5:1) or AAA (7:1) contrast ratios
- **Why valuable:** Accessibility; helps 1 in 12 males with color blindness; legal compliance
- **Testable:** ✅ Use accessibility library (axe-core); validate colors in design tokens
- **Auto-fixable:** ⚠️ Flag issues; suggest color adjustments
- **KPI:** "Contrast compliance: X%"
- **Commandment alignment:** WCAG AA compliance required; Accessibility First (non-negotiable)

**Implementation:**
```php
class Diagnostic_Wcag_Color_Contrast extends Diagnostic_Base {
    // Fetch homepage + key pages
    // Extract computed colors from CSS
    // Calculate contrast ratios for all text
    // Flag anything below 4.5:1
}
```

#### B. **Mobile Responsiveness Check** ⭐⭐⭐⭐⭐
- **What it checks:** Pages render correctly on mobile (no horizontal scroll, readable font size)
- **Why valuable:** 60%+ traffic now mobile; bad mobile UX = higher bounce rate
- **Testable:** ✅ Headless browser viewport check; validate viewport meta tag
- **Auto-fixable:** ⚠️ Detect if theme is responsive; flag if not
- **KPI:** "Mobile score: X/100"
- **Commandment alignment:** Ridiculously Good (mobile-first approach)

#### C. **Font Loading Performance** ⭐⭐⭐⭐
- **What it checks:** Custom fonts use `font-display: swap` (not block); preventing layout shift
- **Why valuable:** Font loading delays page rendering; layout shift poor UX
- **Testable:** ✅ Parse CSS; check font-display values; validate loading strategy
- **Auto-fixable:** ✅ Can update CSS with optimal font-display
- **KPI:** "Font rendering optimized"

#### D. **Dark Mode Support** ⭐⭐⭐
- **What it checks:** Theme respects `prefers-color-scheme` media query
- **Why valuable:** 30%+ users prefer dark mode; improves accessibility (reduces eye strain)
- **Testable:** ✅ Headless browser with dark mode preference; visual validation
- **Auto-fixable:** ⚠️ Suggest CSS media query implementation
- **KPI:** "Dark mode compatible"
- **Commandment alignment:** Accessibility First (respects user preferences)

---

### 6. **SETTINGS** (3 high-impact additions)

#### A. **WordPress Version Freshness** ⭐⭐⭐⭐⭐
- **What it checks:** Core WordPress updated to latest version (not 2+ versions behind)
- **Why valuable:** 90% of hacks exploit known WordPress vulnerabilities; old versions = risk
- **Testable:** ✅ Compare `get_bloginfo('version')` against latest release
- **Auto-fixable:** ⚠️ Alert only (auto-update risky; needs staging)
- **KPI:** "WordPress current version: X"
- **Commandment alignment:** Helpful Neighbor (shows security gaps)

**Implementation:**
```php
class Diagnostic_WordPress_Version_Freshness extends Diagnostic_Base {
    // Get current WP version from global
    // Fetch latest version from wordpress.org API
    // Calculate version lag
    // Flag if > 2 minor versions behind
}
```

#### B. **Plugin/Theme Active Count** ⭐⭐⭐
- **What it checks:** Too many active plugins (> 50 = slow site + security risk)
- **Why valuable:** Each plugin = potential vulnerability + performance hit
- **Testable:** ✅ Count active plugins; benchmark performance
- **Auto-fixable:** ⚠️ Alert only; suggest consolidation
- **KPI:** "Active plugins: N (recommended < 30)"
- **Commandment alignment:** Ridiculously Good (goes beyond basic settings)

#### C. **Admin Email Configuration** ⭐⭐⭐
- **What it checks:** Admin email is valid, not generic (not `admin@example.com`), not shared
- **Why valuable:** Compromised admin email = account takeover; alerts go to invalid email
- **Testable:** ✅ Check email format; verify it's not default; validate MX records
- **Auto-fixable:** ⚠️ Suggest update
- **KPI:** "Admin contact configured"

---

### 7. **MONITORING** (4 high-impact additions)

#### A. **Site Uptime History** ⭐⭐⭐⭐⭐
- **What it checks:** Track site uptime over past 30 days; detect patterns
- **Why valuable:** Early warning of hosting issues; quantifiable reliability
- **Testable:** ✅ Background ping service; store results; calculate percentage
- **Auto-fixable:** ⚠️ Alerting only
- **KPI:** "Uptime: X% (past 30 days)"
- **Commandment alignment:** Everything Has a KPI (measurable reliability)

**Implementation:**
```php
class Diagnostic_Site_Uptime_History extends Diagnostic_Base {
    // Query local uptime tracking data
    // Calculate percentage for past 24h, 7d, 30d
    // Flag if < 99% (critical), < 99.5% (warning)
    // Show trend chart
}
```

#### B. **SSL Certificate Chain Validation** ⭐⭐⭐⭐
- **What it checks:** SSL cert, intermediate certs, and root cert all valid; no breaks in chain
- **Why valuable:** Broken cert chain = browser security warning = users distrust site
- **Testable:** ✅ OpenSSL analysis; validate cert chain
- **Auto-fixable:** ❌ Requires hosting provider fix
- **KPI:** "SSL chain valid"
- **Commandment alignment:** Inspire Confidence (validates security)

#### C. **Email Deliverability Health** ⭐⭐⭐⭐
- **What it checks:** SPF/DKIM/DMARC records configured; domain sending from is configured
- **Why valuable:** Bad email config = emails go to spam; users don't get notifications
- **Testable:** ✅ Fetch DNS records; validate SPF/DKIM syntax
- **Auto-fixable:** ⚠️ Can suggest DNS records (but can't auto-update DNS)
- **KPI:** "Email deliverability: X%"
- **Commandment alignment:** Helpful Neighbor (prevents silent failures)

#### D. **Backup Frequency Validation** ⭐⭐⭐⭐
- **What it checks:** Last backup < 7 days old; backups are being created regularly
- **Why valuable:** Sites without recent backups can't recover from hacks/corruption
- **Testable:** ✅ Check backup plugin metadata; validate backup dates
- **Auto-fixable:** ⚠️ Alert if not configured; link to backup setup
- **KPI:** "Last backup: X days ago"
- **Commandment alignment:** Inspire Confidence (recovery assurance)

---

### 8. **WORKFLOWS** (3 high-impact additions)

#### A. **Scheduled Task Execution Health** ⭐⭐⭐⭐⭐
- **What it checks:** WordPress cron jobs executing regularly (not piling up in queue)
- **Why valuable:** Stuck cron = automated tasks don't run; backups don't run, cleanups don't happen
- **Testable:** ✅ Check `wp_scheduled_meta` table; validate execution timestamps
- **Auto-fixable:** ⚠️ Can trigger manual execution; suggest true cron setup
- **KPI:** "Scheduled tasks on track; last exec: Xh ago"
- **Commandment alignment:** Everything Has a KPI (execution reliability)

**Implementation:**
```php
class Diagnostic_Scheduled_Task_Execution extends Diagnostic_Base {
    // Query wp_options for cron data
    // Check if next scheduled time > current time by > 1 hour
    // Check if loopback request succeeds
    // Flag if cron system not running
}
```

#### B. **Workflow Trigger Validation** ⭐⭐⭐
- **What it checks:** Workflows have valid triggers; hooks they depend on are registered
- **Why valuable:** Orphaned workflows never execute; wasted automation effort
- **Testable:** ✅ Load workflows; validate trigger hooks registered
- **Auto-fixable:** ⚠️ Alert if trigger unavailable
- **KPI:** "Active workflows: N (all valid)"
- **Commandment alignment:** Code Quality (validates automation infrastructure)

#### C. **Workflow Execution Performance** ⭐⭐⭐
- **What it checks:** Workflows complete within timeout (> 30s = too slow)
- **Why valuable:** Slow workflows = missed triggers or server errors
- **Testable:** ✅ Time workflow execution; identify bottlenecks
- **Auto-fixable:** ⚠️ Report and suggest optimization
- **KPI:** "Workflow completion time: Xms"
- **Commandment alignment:** Performance (workflow speed impacts user experience)

---

## Implementation Priority Matrix

```
HIGH IMPACT + EASILY TESTABLE + VALUABLE (Implement First)
├─ Vulnerable Plugin Detection
├─ Database Query Performance Audit
├─ WCAG Color Contrast Validation
├─ SSL Certificate Expiration Monitoring
├─ PHP Error Logging Status
├─ Site Uptime History
├─ Missing Meta Tags Audit
└─ Scheduled Task Execution Health

MEDIUM IMPACT + TESTABLE (Implement Second)
├─ Login Attempt Rate Limiting
├─ Static Asset Caching Headers
├─ Mobile Responsiveness Check
├─ Database User Privileges Validation
├─ Admin User Enumeration Risk
└─ WordPress Version Freshness

LOWER IMPACT + COMPLEX TESTING (Implement Third)
├─ Lazy Loading Implementation
├─ Unused CSS/JavaScript Detection
├─ Email Deliverability Health
├─ SSL Certificate Chain Validation
└─ Dark Mode Support
```

---

## Testing Strategy for All New Diagnostics

### **Repeatable Testing Pattern**

Every diagnostic should support this test pattern:

```php
public function testDiagnosticDetectsPositive(): void {
    // 1. Set up scenario WHERE issue WOULD be found
    // 2. Run diagnostic
    // 3. Assert finding returned (not null)
    // 4. Assert finding has expected severity
}

public function testDiagnosticDetectsNegative(): void {
    // 1. Set up scenario where issue NOT present (good state)
    // 2. Run diagnostic
    // 3. Assert null returned (no finding)
}

public function testFindingMetadata(): void {
    // 1. Get finding
    // 2. Assert all required fields present
    // 3. Assert KPI data structure valid
}
```

### **Mock Data Strategy**

- **Database:** Use SQLite in-memory for tests; populate with sample data
- **Files:** Use temp directories; clean up after tests
- **Network:** Mock HTTP responses; don't make real API calls
- **Time:** Mock `time()` function to test scheduling/expiration scenarios

---

## KPI Tracking Integration

Each new diagnostic should log KPIs to Activity Logger:

```php
\WPShadow\Core\Activity_Logger::log(
    'diagnostic_completed',
    array(
        'diagnostic_slug' => self::$slug,
        'issue_found'     => (bool) $finding,
        'severity'        => $finding ? $finding['threat_level'] : null,
        'kpi'             => array(
            'vulnerabilities_found' => $vulnerability_count,
            'fixable_automatically' => $auto_fixable_count,
        ),
    )
);
```

This ensures every diagnostic contributes to the "Everything Has a KPI" commandment.

---

## Phased Implementation Plan

### **Phase 1: Security Foundation (Weeks 1-2)**
- Vulnerable Plugin Detection
- Database User Privileges Validation
- Admin User Enumeration Risk
- Weak WordPress Salt/Security Keys

### **Phase 2: Performance & Database (Weeks 3-4)**
- Database Query Performance Audit
- Static Asset Caching Headers
- Lazy Loading Implementation
- Unused CSS/JavaScript Detection

### **Phase 3: Code Quality & SEO (Weeks 5-6)**
- PHP Error Logging Status
- Missing Meta Tags Audit
- Sitemap Quality Check
- WordPress Coding Standards Compliance

### **Phase 4: Design & Monitoring (Weeks 7-8)**
- WCAG Color Contrast Validation
- Mobile Responsiveness Check
- SSL Certificate Expiration Monitoring
- Site Uptime History

### **Phase 5: Workflows & Advanced (Weeks 9-10)**
- Scheduled Task Execution Health
- Workflow Trigger Validation
- Email Deliverability Health
- Advanced integrations

---

## Success Metrics

### **For Each New Diagnostic:**
- ✅ Testable in isolation (unit tests pass)
- ✅ Detectable via automated scanning (no manual setup)
- ✅ Aligns with WPShadow commandments
- ✅ Has corresponding treatment where auto-fixable
- ✅ KPI data logged for measurement
- ✅ Documented in KB article
- ✅ No false positives (validation on 100+ test sites)

### **Overall Diagnostic Coverage:**
- Current: ~1,165 placeholder diagnostics (mostly unimplemented)
- Target after implementation: 30-40 **production-ready**, genuinely valuable diagnostics
- Quality over quantity: One great diagnostic > 1,000 broken ones

---

## Questions to Answer First

Before starting implementation, recommend validating:

1. **Plugin CVE Database:** Which source for vulnerability data? (WordPress.org API, CVE feed, etc.)
2. **Uptime Monitoring:** Should this be local pinging or integration with external service?
3. **Email Deliverability:** Should we use external API (Mailtrap, etc.) or DNS-only checks?
4. **Performance Baselines:** What constitutes "slow" query (0.1s? 0.5s?)
5. **Guardian Integration:** Should these diagnostics leverage Guardian Pro features? Or all free?

---

## Conclusion

The recommendations above prioritize **genuine user value** aligned with WPShadow's philosophy:

- **Helpful Neighbor:** Each diagnostic explains WHY + HOW to fix
- **Free as Possible:** All diagnostics free; treatments where feasible free
- **Ridiculously Good:** Coverage exceeding premium competitors
- **Testable & Measurable:** Every diagnostic has unit tests and KPI tracking
- **Privacy First:** No unauthorized external calls
- **Talk-About-Worthy:** Features users will recommend

Focus on **depth over breadth**: 30 production-grade diagnostics are more valuable than 1,000 half-implemented ones.
