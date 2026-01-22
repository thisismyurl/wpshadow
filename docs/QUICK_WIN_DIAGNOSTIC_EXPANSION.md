# Quick-Win Diagnostic Expansion: Categories with 1-2 Tests

**Purpose:** Immediate expansion opportunities for underserved categories  
**Time Estimate:** 4-6 hours to implement 30+ new diagnostics  
**Revenue Impact:** High (fills critical gaps that competitors have)

---

## 📊 Ultra-Low Volume Categories (1 Test Each)

### 1️⃣ **Abandoned Plugins** → 7 Tests

**Current:** Plugin count check only  
**Business Case:** "This 2-year-old plugin hasn't been updated—could be a security risk"

**Expansion Plan:**

```php
// STUB 1: class-diagnostic-abandoned-plugin-author.php
// Check: Does plugin author still maintain the plugin?
// Detection: GitHub profile activity, WordPress.org plugin page updates
// KB: https://wpshadow.com/kb/abandoned-plugins-risk/
// Revenue: Core free + Guardian module (security implications)

// STUB 2: class-diagnostic-plugin-last-commit.php
// Check: When was the last code commit to this plugin's repository?
// Detection: GitHub API query last commit date vs today
// Data: "Last commit: 847 days ago" (red flag if >180 days)

// STUB 3: class-diagnostic-plugin-github-stars.php
// Check: Is plugin GitHub stars declining or stagnant?
// Detection: Star count trend over 12 months (if available)
// Data: "This plugin's GitHub stars haven't grown in 18 months"

// STUB 4: class-diagnostic-plugin-open-issues.php
// Check: Backlog of unresolved issues on GitHub?
// Detection: Open issues/PRs count vs closed (quality indicator)
// Threshold: If open:closed ratio > 1:5, flag as abandonment risk

// STUB 5: class-diagnostic-plugin-support-forum.php
// Check: Does plugin have WordPress.org support forum activity?
// Detection: Last reply date, response time to questions
// Data: "Last support forum reply: 6 months ago"

// STUB 6: class-diagnostic-plugin-alternative-recommendation.php
// Check: Are there actively maintained alternatives?
// Detection: Search WordPress plugin directory for similar plugins
// Data: "ActiveAlternative: [Plugin Name] (last updated: 45 days ago)"

// STUB 7: class-diagnostic-plugin-security-advisory.php
// Check: Any known CVEs or security vulnerabilities reported?
// Detection: Plugin Vulnerabilities API, WPScan vulnerability database
// Impact: Severity level + exploitation likelihood
```

**KPI Tracking:**
- Time saved: 30 minutes/plugin (avoiding security incidents)
- Issues found: Count of abandoned plugins
- Value: Estimated cost of security incident prevention

---

### 2️⃣ **Dark Mode** → 5 Tests

**Current:** Prefers-color-scheme detection only  
**Business Case:** "Dark mode is broken—text is invisible on dark backgrounds"

**Expansion Plan:**

```php
// STUB 1: class-diagnostic-dark-mode-contrast.php
// Check: What's the color contrast in dark mode?
// Detection: Parse CSS, simulate dark mode, measure contrast ratios
// Threshold: WCAG AA (4.5:1 for text)
// Finding: "H2 headers have 2.1:1 contrast in dark mode (FAIL)"

// STUB 2: class-diagnostic-dark-mode-font-rendering.php
// Check: Is font rendering quality degraded in dark mode?
// Detection: Font weight, line height, letter spacing with dark background
// Data: "Body text becomes illegible at 14px in dark mode"

// STUB 3: class-diagnostic-dark-mode-image-visibility.php
// Check: Are images visible in dark mode?
// Detection: Check images with no alt text or border
// Finding: "Logo image is 100% transparent in dark mode"

// STUB 4: class-diagnostic-dark-mode-preference-persistence.php
// Check: Does dark mode preference persist across sessions?
// Detection: localStorage, sessionStorage, or cookie check
// Finding: "Dark mode toggle resets on every page load"

// STUB 5: class-diagnostic-dark-mode-toggle-presence.php
// Check: Is there a dark mode toggle available?
// Detection: Look for theme switcher UI element
// Accessibility: Ensure toggle is keyboard accessible, has proper ARIA labels
```

**KPI Tracking:**
- Time saved: 15 minutes (manual dark mode testing)
- Issues found: Count of dark mode failures
- User satisfaction: Estimated improvement if fixed

---

### 3️⃣ **Exit Intent** → 6 Tests

**Current:** Exit behavior detection only  
**Business Case:** "Our exit-intent popup isn't working—people aren't seeing the offer"

**Expansion Plan:**

```php
// STUB 1: class-diagnostic-exit-intent-mouse-tracking.php
// Check: Is mouse tracking for exit intent accurate?
// Detection: Verify mouseout event listeners on body/document
// Issue: Some plugins don't detect all exit paths (sidebar, address bar)

// STUB 2: class-diagnostic-exit-intent-frequency-cap.php
// Check: Is exit intent frequency-capped to avoid annoyance?
// Detection: Check localStorage/cookie for showExitIntent counters
// Finding: "Exit popup shows 47 times per session (should be 1-2)"

// STUB 3: class-diagnostic-exit-intent-user-retention.php
// Check: What % of exiting users are retained by exit intent?
// Detection: Analytics data correlation (bounce rate → conversion)
// Data: "Exit intent converts 3.2% of would-be bouncers"

// STUB 4: class-diagnostic-exit-intent-conversion-rate.php
// Check: What's the conversion rate for users who see exit intent?
// Detection: Goal/conversion tracking (GA4 event analysis)
// Data: "Exit intent viewers: 12% conversion; Non-viewers: 8% conversion"

// STUB 5: class-diagnostic-exit-intent-recovery-email.php
// Check: Is there a recovery email follow-up for exiting users?
// Detection: Email automation setup (Zapier, ActiveCampaign, etc.)
// Finding: "Exit intent shown but no follow-up email configured"

// STUB 6: class-diagnostic-exit-intent-psychology.php
// Check: Is the exit intent offer actually compelling?
// Detection: A/B test data, offer relevance, urgency language
// Recommendation: "Consider adding scarcity language: 'Only 3 spots left'"
```

**KPI Tracking:**
- Time saved: 10 minutes/session (manual analytics review)
- Issues found: Count of exit intent optimization opportunities
- Revenue impact: Estimated $ recovered from prevented bounces

---

### 4️⃣ **Favicon** → 5 Tests

**Current:** Favicon presence detection only  
**Business Case:** "Favicon isn't showing in browser tabs—hurts brand visibility"

**Expansion Plan:**

```php
// STUB 1: class-diagnostic-favicon-format.php
// Check: Is favicon in modern format (SVG, PNG)?
// Detection: Check favicon MIME type
// Warning: "Using outdated .ico format (larger file size)"
// Recommendation: "Convert to SVG (scales infinitely, smallest size)"

// STUB 2: class-diagnostic-favicon-sizes.php
// Check: Are all required favicon sizes provided?
// Detection: Check for: 16x16, 32x32, 64x64, 128x128, 256x256
// Finding: "Only providing 16x16 (fuzzy on 4K displays)"

// STUB 3: class-diagnostic-favicon-apple-touch.php
// Check: Is Apple touch icon configured for iOS?
// Detection: Check meta rel="apple-touch-icon"
// Finding: "Missing apple-touch-icon (users don't bookmark your site)"

// STUB 4: class-diagnostic-favicon-android-adaptive.php
// Check: Is adaptive icon configured for Android?
// Detection: Check meta "theme-color" and adaptive icon setup
// Finding: "Not using Android adaptive icons (old rectangular look)"

// STUB 5: class-diagnostic-favicon-cdn-delivery.php
// Check: Is favicon being cached on CDN?
// Detection: Check Cache-Control headers on favicon request
// Issue: "Favicon has 24-hour expiry (miss optimization)"
// Rec: "Set to 1-year expiry (rarely changes)"
```

**KPI Tracking:**
- Time saved: 5 minutes (manual favicon testing)
- Brand perception: "Favicon visible on all devices/browsers"
- Bookmark-ability: Estimated impact on bookmarks

---

### 5️⃣ **Database Deadlock** → 6 Tests

**Current:** Deadlock detection only  
**Business Case:** "Database sometimes locks up—customers can't checkout"

**Expansion Plan:**

```php
// STUB 1: class-diagnostic-deadlock-frequency.php
// Check: How often do database deadlocks occur?
// Detection: Parse MySQL error logs for "Deadlock found"
// Data: "Deadlock occurred 23 times in past 24 hours"

// STUB 2: class-diagnostic-deadlock-query-victims.php
// Check: Which queries are being killed by deadlock?
// Detection: Parse MySQL LATEST DETECTED DEADLOCK in error log
// Query: "UPDATE wp_postmeta WHERE post_id = X"

// STUB 3: class-diagnostic-transaction-isolation-level.php
// Check: What's the transaction isolation level?
// Detection: SHOW VARIABLES WHERE variable_name = 'transaction_isolation'
// Current: READ UNCOMMITTED? (risky) or REPEATABLE READ? (common)

// STUB 4: class-diagnostic-lock-wait-timeout.php
// Check: Is lock wait timeout appropriate?
// Detection: innodb_lock_wait_timeout setting (default 50 seconds)
// Issue: "Lock wait timeout = 1 second (too aggressive, kills queries)"

// STUB 5: class-diagnostic-concurrent-transactions.php
// Check: How many concurrent transactions are typical?
// Detection: SHOW ENGINE INNODB STATUS parsing
// Data: "Current transactions: 47 (high concurrency)"

// STUB 6: class-diagnostic-deadlock-recovery-velocity.php
// Check: How quickly do queries recover after deadlock?
// Detection: Application retry logic in error logs
// Metric: "Average recovery: 2.3 seconds (good)"
```

**KPI Tracking:**
- Time saved: 1 hour/incident (manual deadlock investigation)
- Revenue impact: $ lost during checkout downtime
- Customer satisfaction: Impact of transient errors

---

### 6️⃣ **Emoji Loading** → 4 Tests

**Current:** Emoji support detection only  
**Business Case:** "Emoji aren't rendering—posts look broken"

**Expansion Plan:**

```php
// STUB 1: class-diagnostic-emoji-font-size.php
// Check: Is emoji font file too large?
// Detection: wp-emoji-release.min.css + svg file size
// Current: WordPress emoji SVG is ~40-50KB
// Rec: "Consider native emoji (users have native emoji fonts)"

// STUB 2: class-diagnostic-emoji-loading-method.php
// Check: How are emoji being loaded?
// Detection: SVG/PNG/Font-based (WordPress uses SVG)
// Alternative: "Use native emoji (requires no download)"

// STUB 3: class-diagnostic-emoji-rendering-quality.php
// Check: Are emoji rendering clearly?
// Detection: Check if emoji SVG is being properly cached
// Finding: "Emoji font missing on 15% of site views (cache miss)"

// STUB 4: class-diagnostic-emoji-fallback-text.php
// Check: Is fallback text provided for non-emoji clients?
// Detection: Check alt text or aria-label on emoji images
// A11y: "Emoji missing alt text for screenreaders"
```

**KPI Tracking:**
- Time saved: 5 minutes (manual emoji testing)
- Page load impact: "Emoji loading adds 60ms TTFB"
- Accessibility: "Users with emoji fonts native save 40KB"

---

## 📊 Two-Test Categories with High Expansion Potential

### 7️⃣ **Cookie Security** (2→6 Tests)

**Current:** Secure flag, HttpOnly flag  
**Expansion:**

```php
// ADD: class-diagnostic-cookie-samesite.php (SameSite=Strict/Lax/None)
// ADD: class-diagnostic-cookie-expiration.php (Too long? Session vs persistent)
// ADD: class-diagnostic-cookie-size-bloat.php (Cookie too large?)
// ADD: class-diagnostic-cookie-third-party-consent.php (Tracking cookies have consent?)
// ADD: class-diagnostic-cookie-encryption-at-rest.php (Are sensitive cookies encrypted?)
```

**KB Link:** https://wpshadow.com/kb/cookie-security/  
**Revenue:** Core + Guardian module

---

### 8️⃣ **Password Policy** (2→6 Tests)

**Current:** Minimum length, complexity requirements  
**Expansion:**

```php
// ADD: class-diagnostic-password-expiration-required.php
// ADD: class-diagnostic-password-reuse-prevention.php
// ADD: class-diagnostic-password-hashing-algorithm.php (bcrypt? md5?)
// ADD: class-diagnostic-compromised-password-check.php (Check HIBP)
// ADD: class-diagnostic-password-manager-compatibility.php
```

**KB Link:** https://wpshadow.com/kb/password-policy-requirements/  
**Revenue:** Guardian module (security + compliance)

---

### 9️⃣ **Email Delivery** (2→7 Tests)

**Current:** SPF/DKIM/DMARC, reputation  
**Expansion:**

```php
// ADD: class-diagnostic-email-template-responsiveness.php
// ADD: class-diagnostic-email-unsubscribe-compliance.php (CAN-SPAM law)
// ADD: class-diagnostic-email-sender-verification.php
// ADD: class-diagnostic-email-bounce-rate-tracking.php
// ADD: class-diagnostic-email-engagement-metrics.php (Open rate, click rate)
```

**KB Link:** https://wpshadow.com/kb/email-deliverability/  
**Revenue:** Core free + SaaS (HIBP checking, bounce tracking)

---

## 🎯 Implementation Priority

**Week 1 (Pick 3-4 for quick wins):**
1. Dark Mode (5 tests) - 2 hours
2. Favicon (5 tests) - 1.5 hours
3. Exit Intent (6 tests) - 2.5 hours
4. Abandoned Plugins (7 tests) - 3 hours

**Total: ~9 hours → +23 new diagnostics (97% expansion)**

**Week 2 (Add revenue-aligned):**
1. Password Policy (6 tests) - 2.5 hours
2. Cookie Security (6 tests) - 2.5 hours
3. Email Delivery (7 tests) - 2.5 hours

**Total: ~7.5 hours → +19 new diagnostics (42% additional)**

---

## 📈 Expected Impact

**Current State:** 2,458 diagnostics  
**After Week 1:** 2,481 diagnostics (+23, +0.9%)  
**After Week 2:** 2,500 diagnostics (+42, +1.7% total)

**More Importantly:**
- Fill 5 critical gaps (abandoned plugins, dark mode, etc.)
- Create 42 new "holy shit" moments
- 3x coverage in underserved categories
- Build competitive differentiation vs Wordfence/WP Rocket

---

## 📂 Stub Generation

Use the pattern from [includes/diagnostics-future/README.md](../includes/diagnostics-future/README.md):

```php
// Template for each diagnostic stub
namespace WPShadow\DiagnosticsFuture\[Category];
use WPShadow\Core\Diagnostic_Base;

class Diagnostic_[Name] extends Diagnostic_Base {
    protected static $slug = '[slug]';
    protected static $title = '[Human readable]';
    protected static $description = '[Problem description]';
    
    public static function check(): ?array {
        return [
            'id' => static::$slug,
            'title' => static::$title . ' [STUB]',
            'description' => static::$description,
            'kb_link' => 'https://wpshadow.com/kb/[slug]/',
            'module' => '[Guardian/Commerce/APM]',
            'priority' => 1,
            'stub' => true,
        ];
    }
}
```

---

## ✅ Next Action

1. Pick your first category (recommend: **Dark Mode** or **Favicon**)
2. Copy the expansion plan above
3. Create 5-7 stub files in `/includes/diagnostics-future/[category]/`
4. Add implementation details to each stub
5. Run `composer phpcs` to validate
6. Submit for review

**Estimated Time:** 1.5-2 hours per category → 23+ new diagnostics per week

