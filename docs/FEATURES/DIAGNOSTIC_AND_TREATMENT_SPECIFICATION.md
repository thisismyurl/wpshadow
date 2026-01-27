# Diagnostic & Treatment Development Specification

**Version:** 1.0  
**Date:** January 26, 2026  
**Status:** ✅ Authoritative Reference  
**Purpose:** Defines what constitutes a complete, successful diagnostic and treatment

---

## Overview

This document is the **definitive specification** for creating diagnostics and treatments in WPShadow. Every diagnostic and treatment must meet these requirements to be considered complete.

**Key Principle:** If you can answer "yes" to every checklist item, the diagnostic/treatment is ready to merge.

---

## Part 1: Diagnostic Specification

### What is a Diagnostic?

A diagnostic is a **read-only check** that detects a WordPress configuration issue, security concern, or optimization opportunity. It **never modifies anything**—it only reports findings.

### Anatomy of a Complete Diagnostic

#### 1. File Structure

**Location:** `includes/diagnostics/tests/{category}/class-diagnostic-{slug}.php`

**Categories (Must match dashboard gauges):**
- `security/` - Security vulnerabilities, hardening opportunities
- `performance/` - Speed, efficiency, optimization  
- `code-quality/` - Code cleanliness, standards compliance
- `seo/` - Search engine optimization, discoverability
- `design/` - Visual design, UX, accessibility
- `settings/` - WordPress configuration and settings
- `monitoring/` - Site monitoring, uptime, alerts
- `workflows/` - Automation, scheduled tasks, workflows

**Note:** There are also `wordpress-health` and `overall` categories, but diagnostics should use one of the 8 standard categories above.

**Naming Convention:**
```
File: class-diagnostic-memory-limit.php
Class: Diagnostic_Memory_Limit
Namespace: WPShadow\Diagnostics
```

#### 2. Required Code Structure

```php
<?php
/**
 * Diagnostic: {Human-Readable Title}
 *
 * {One-line description of what this checks}
 *
 * @package WPShadow\Diagnostics
 * @since   {version}
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * {Class Name} Class
 *
 * {Detailed description explaining:}
 * - What this diagnostic checks
 * - Why it matters (impact on site)
 * - When it triggers (conditions)
 * - What users should do about it
 *
 * @since {version}
 */
class Diagnostic_{Name} extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = '{kebab-case-name}';

	/**
	 * Diagnostic title (user-facing)
	 *
	 * @var string
	 */
	protected static $title = '{Title Case Name}';

	/**
	 * Diagnostic description (plain language)
	 *
	 * @var string
	 */
	protected static $description = '{One sentence explaining what this checks}';

	/**
	 * Family grouping for batch operations
	 *
	 * @var string
	 */
	protected static $family = '{category}';

	/**
	 * Family label (human-readable)
	 *
	 * @var string
	 */
	protected static $family_label = '{Category Name}';

	/**
	 * Run the diagnostic check.
	 *
	 * {Detailed description of check logic}
	 *
	 * @since  {version}
	 * @return array|null Finding array if issue detected, null if all good.
	 */
	public static function check() {
		// Implement detection logic here
		// Return null if no issue found
		// Return findings array if issue detected
	}
}
```

#### 3. Required Return Structure (When Issue Found)

```php
return array(
	'id'          => self::$slug,                    // Diagnostic identifier
	'title'       => self::$title,                   // User-facing title
	'description' => __(                             // Plain language explanation
		'Current: {actual}. Recommended: {expected}. This affects {impact}.',
		'wpshadow'
	),
	'severity'    => '{low|medium|high|critical}',   // String severity (REQUIRED)
	'threat_level' => {0-100},                       // Numeric threat score (0-100)
	'site_health_status' => '{good|recommended|critical}', // WordPress Site Health status
	'auto_fixable' => {true|false},                  // Can Treatment fix this? (false if no treatment)
	'kb_link'     => 'https://wpshadow.com/kb/{category}-{slug}',
	'family'      => self::$family,                  // For batch operations (category)
);
```

#### 4. Site Health Status Mapping

**WordPress Site Health Integration:**

Diagnostics must include a `site_health_status` that maps to WordPress Site Health values:

```php
// Site Health status values (WordPress native)
'good'        // Green - No issues, site is healthy
'recommended' // Orange - Recommended improvements
'critical'    // Red - Critical issues requiring immediate attention
```

**Mapping Rules (follows class-site-health-bridge.php):**

```php
// String severity to Site Health status:
'critical' or 'high' → 'critical'
'medium'             → 'recommended'
'low'                → 'good'

// Numeric threat_level to Site Health status:
75-100  → 'critical'      // WPSHADOW_SEVERITY_CRITICAL_THRESHOLD
50-74   → 'recommended'   // WPSHADOW_SEVERITY_RECOMMENDED_THRESHOLD  
0-49    → 'good'
```

**Implementation Example:**
```php
public static function check() {
	$threat_level = 85; // High threat
	
	// Determine Site Health status from threat level
	$site_health_status = 'good';
	if ( $threat_level >= 75 ) {
		$site_health_status = 'critical';
	} elseif ( $threat_level >= 50 ) {
		$site_health_status = 'recommended';
	}
	
	return array(
		'id'                 => self::$slug,
		'severity'           => 'high',
		'threat_level'       => $threat_level,
		'site_health_status' => $site_health_status, // 'critical'
		// ... rest of finding
	);
}
```

#### 5. Threat Level Guidelines

**Calculation Formula:**
```
Threat Level = (Severity × Impact × Likelihood) / 10
- Severity: 1-10 (how bad if exploited?)
- Impact: 1-10 (how many users affected?)
- Likelihood: 1-10 (how likely to occur/be exploited?)
```

**Threat Ranges:**
- **0-9 (Low):** Nice-to-have optimizations, minor issues
  - Examples: Emoji scripts, WP generator tag, lazy loading
- **10-24 (Medium):** Important but not urgent
  - Examples: Memory limit low, backup missing, outdated WP
- **25-50 (High):** Security critical or performance severe
  - Examples: SSL not enabled, 'admin' username, PHP too old
- **51-100 (Critical):** Immediate action required, site at risk
  - Examples: Actively exploited vulnerability, critical data exposure

#### 6. Message Quality Standards

**✅ Good Messages (Plain Language, Educational):**
```php
sprintf(
	/* translators: 1: current value, 2: recommended value */
	__(
		'Your PHP memory limit is set to %1$s. We recommend at least %2$s for optimal performance. Low memory can cause plugin conflicts, slow page loads, and admin timeouts.',
		'wpshadow'
	),
	$current,
	'256M'
)
```

**❌ Bad Messages (Technical, Unhelpful):**
```php
// Don't do this:
'Memory limit too low';
'WP_MEMORY_LIMIT = 64M';
'Fix immediately!';
```

**Message Requirements:**
- ✅ Plain English (Hemingway Grade 8 or lower)
- ✅ Explain current state and recommended state
- ✅ Explain **why** it matters (impact)
- ✅ Avoid jargon or define technical terms
- ✅ Internationalized (use `__()` or `sprintf()` with translators comment)
- ✅ No idioms or cultural references
- ❌ No fear-mongering or urgency manipulation
- ❌ No upselling or product mentions

#### 8. Knowledge Base Integration

**Every diagnostic must link to a KB article:**

```php
'kb_link' => 'https://wpshadow.com/kb/' . self::$family . '-' . self::$slug,
```

**KB Article Format:**
```
URL: https://wpshadow.com/kb/security-ssl
Title: SSL/HTTPS Configuration: What It Means & How to Fix It

Structure:
1. What This Is (plain language explanation)
2. Why It Matters (real impact on site/business)
3. How to Fix It Yourself (step-by-step guide)
4. Learn More (video tutorial, related articles)
5. Need Help? (optional: mention Pro addon as option, not requirement)
```

---

### HTML-Based Diagnostics (Special Pattern)

Many diagnostics need to analyze rendered HTML from frontend or admin pages. Use these helper patterns:

#### Helper Function: `wpshadow_fetch_page_html()`

**To Be Implemented** - Specification for helper:

```php
/**
 * Fetch and cache HTML from a URL.
 *
 * @param string $url         URL to fetch.
 * @param int    $cache_ttl   Cache duration in seconds. Default 3600 (1 hour).
 * @param string $cache_group Cache group for categorization. Default 'wpshadow_html'.
 * @return string|WP_Error HTML content or WP_Error on failure.
 */
function wpshadow_fetch_page_html( $url, $cache_ttl = 3600, $cache_group = 'wpshadow_html' ) {
	// 1. Check cache first
	$cache_key = 'html_' . md5( $url );
	$cached    = get_transient( $cache_group . '_' . $cache_key );
	
	if ( false !== $cached ) {
		return $cached;
	}
	
	// 2. Fetch via wp_remote_get
	$response = wp_remote_get( $url, array(
		'timeout'    => 15,
		'user-agent' => 'WPShadow/' . WPSHADOW_VERSION,
		'sslverify'  => false, // For local development
	) );
	
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	
	$code = wp_remote_retrieve_response_code( $response );
	if ( $code !== 200 ) {
		return new WP_Error( 'http_error', sprintf( 'HTTP %d', $code ) );
	}
	
	$html = wp_remote_retrieve_body( $response );
	
	// 3. Cache the result
	set_transient( $cache_group . '_' . $cache_key, $html, $cache_ttl );
	
	return $html;
}

/**
 * Get diagnostic test type (direct or async).
 *
 * WordPress Site Health supports two test types:
 * - direct: Runs synchronously (fast checks < 1 second)
 * - async: Runs via AJAX (slow checks > 1 second)
 *
 * @since  1.2601.2200
 * @param  string $diagnostic_class Diagnostic class name.
 * @return string Test type ('direct' or 'async').
 */
function wpshadow_get_diagnostic_test_type( string $diagnostic_class ): string {
	// Default to 'direct' for most diagnostics
	// Override to 'async' for slow operations (HTML fetching, external APIs)
	
	// Async test patterns:
	// - HTML fetching diagnostics
	// - External API calls
	// - Database-heavy queries
	// - File system scanning
	
	$async_patterns = array(
		'_HTML_',
		'_External_',
		'_Fetch_',
		'_Scan_',
		'_Crawler_',
	);
	
	foreach ( $async_patterns as $pattern ) {
		if ( strpos( $diagnostic_class, $pattern ) !== false ) {
			return 'async';
		}
	}
	
	// Direct test for everything else (config checks, simple queries)
	return 'direct';
}
```

#### Common Page Helper Shortcuts

```php
/**
 * Get homepage HTML.
 *
 * @param int $cache_ttl Cache duration in seconds.
 * @return string|WP_Error
 */
function wpshadow_get_homepage_html( $cache_ttl = 3600 ) {
	return wpshadow_fetch_page_html( home_url(), $cache_ttl, 'wpshadow_frontend' );
}

/**
 * Get admin page HTML.
 *
 * @param string $page_slug Admin page slug (e.g., 'index.php', 'plugins.php').
 * @param int    $cache_ttl Cache duration in seconds.
 * @return string|WP_Error
 */
function wpshadow_get_admin_page_html( $page_slug, $cache_ttl = 3600 ) {
	$url = admin_url( $page_slug );
	return wpshadow_fetch_page_html( $url, $cache_ttl, 'wpshadow_admin' );
}

/**
 * Get single post/page HTML.
 *
 * @param int $post_id  Post ID.
 * @param int $cache_ttl Cache duration in seconds.
 * @return string|WP_Error
 */
function wpshadow_get_post_html( $post_id, $cache_ttl = 3600 ) {
	$url = get_permalink( $post_id );
	if ( ! $url ) {
		return new WP_Error( 'invalid_post', 'Invalid post ID' );
	}
	return wpshadow_fetch_page_html( $url, $cache_ttl, 'wpshadow_posts' );
}
```

#### HTML Diagnostic Pattern

**Example: Check for specific script in homepage:**

```php
public static function check() {
	// Fetch homepage HTML (cached for 1 hour)
	$html = wpshadow_get_homepage_html( HOUR_IN_SECONDS );
	
	if ( is_wp_error( $html ) ) {
		// Handle fetch error gracefully
		return null; // or log error and return null
	}
	
	// Check for problematic script
	if ( strpos( $html, 'problematic-script.js' ) !== false ) {
		return array(
			'id'                 => self::$slug,
			'title'              => self::$title,
			'description'        => __(
				'Your homepage is loading a problematic script that slows page load by 2 seconds.',
				'wpshadow'
			),
			'severity'           => 'medium',
			'threat_level'       => 35,
			'site_health_status' => 'recommended',
			'auto_fixable'       => false,
			'kb_link'            => 'https://wpshadow.com/kb/performance-slow-scripts',
			'family'             => self::$family,
		);
	}
	
	return null;
}
```

#### HTML Parsing Best Practices

1. **Use Simple String Functions First:**
   - `strpos()` for simple checks (fast)
   - `preg_match()` for patterns (moderate)
   - DOMDocument only if necessary (slower)

2. **Cache Aggressively:**
   - Frontend pages: 1 hour (3600s)
   - Admin pages: 30 minutes (1800s)
   - During scans: Use scan-specific cache

3. **Handle Errors Gracefully:**
   ```php
   if ( is_wp_error( $html ) ) {
       // Don't report finding if fetch failed
       return null;
   }
   ```

4. **Respect Privacy:**
   - Only fetch local URLs (same domain)
   - Never send HTML to external APIs without consent
   - Cache locally, never in cloud

---

### Diagnostic Checklist (Definition of Done)

Before considering a diagnostic complete, verify:

#### ✅ Code Quality
- [ ] Extends `Diagnostic_Base`
- [ ] `declare(strict_types=1);` at top
- [ ] All properties defined (`$slug`, `$title`, `$description`, `$family`, `$family_label`)
- [ ] `check()` method implemented and returns correct structure
- [ ] Returns `null` when no issue found
- [ ] Returns findings array when issue detected (with all required keys)
- [ ] Includes `site_health_status` field mapped correctly
- [ ] `severity` and `threat_level` are consistent
- [ ] `family` matches one of 8 dashboard gauge categories
- [ ] No side effects (doesn't modify anything)
- [ ] PHPDoc complete for class and methods
- [ ] `@since` version tag present
- [ ] Follows WordPress Coding Standards (PHPCS clean)

#### ✅ Internationalization (i18n)
- [ ] All user-facing strings use `__()` or `_e()`
- [ ] Text domain is `'wpshadow'` (always)
- [ ] Translators comments for placeholders (e.g., `/* translators: %s: memory limit */`)
- [ ] No hardcoded English in output
- [ ] Handles pluralization with `_n()` if applicable
- [ ] No idioms, slang, or cultural references

#### ✅ Accessibility (CANON Pillar 1)
- [ ] Plain language (Grade 8 or lower)
- [ ] Explains WHY, not just WHAT
- [ ] Error messages are actionable
- [ ] No color-only indicators (always include text)
- [ ] Keyboard-accessible if interactive
- [ ] Screen reader friendly (semantic structure)

#### ✅ Learning Inclusive (CANON Pillar 2)
- [ ] KB article link provided
- [ ] Description explains concept in simple terms
- [ ] Real-world impact described
- [ ] Technical users get details, non-technical get guidance

#### ✅ Culturally Respectful (CANON Pillar 3)
- [ ] No idioms (no "piece of cake", "break a leg")
- [ ] Date/time formats use WordPress functions (`date_i18n()`)
- [ ] Number formats use `number_format_i18n()`
- [ ] No assumptions about user's culture/location
- [ ] RTL compatible (no hardcoded `left`/`right`)

#### ✅ Product Philosophy Alignment
- [ ] "Helpful Neighbor" tone (friendly, educational)
- [ ] No sales pitches or upsells
- [ ] Free to run (no API calls without consent)
- [ ] Links to KB article, not Pro page
- [ ] Explains value, doesn't manipulate fear
- [ ] Inspires confidence, not anxiety

#### ✅ Technical Requirements
- [ ] Works on PHP 8.1+
- [ ] Works on WordPress 6.4+
- [ ] Handles multisite correctly
- [ ] Respects user capabilities
- [ ] No external API calls without consent
- [ ] No performance impact (fast check)
- [ ] Handles edge cases gracefully

#### ✅ Documentation
- [ ] KB article exists (or placeholder documented)
- [ ] Added to Feature Matrix (FEATURE_MATRIX_DIAGNOSTICS.md)
- [ ] Category assigned correctly
- [ ] Threat level documented
- [ ] Auto-fixable status documented

---

## Part 2: Treatment Specification

### What is a Treatment?

A treatment is a **safe, reversible fix** that resolves a diagnostic finding. It must create backups, apply changes atomically, and provide rollback functionality.

### Anatomy of a Complete Treatment

#### 1. File Structure

**Location:** `includes/treatments/class-treatment-{slug}.php`

**Naming Convention:**
```
File: class-treatment-memory-limit.php
Class: Treatment_Memory_Limit
Namespace: WPShadow\Treatments
```

**Matches Diagnostic:**
```
Diagnostic: Diagnostic_Memory_Limit (slug: 'memory-limit')
Treatment: Treatment_Memory_Limit (finding_id: 'memory-limit')
```

#### 2. Required Code Structure

```php
<?php
/**
 * Treatment: {Human-Readable Title}
 *
 * {One-line description of what this fixes}
 *
 * @package WPShadow\Treatments
 * @since   {version}
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_{Name} Class
 *
 * {Detailed description explaining:}
 * - What this treatment fixes
 * - What changes it makes
 * - What it backs up
 * - How to undo it
 * - Any risks or prerequisites
 *
 * @since {version}
 */
class Treatment_{Name} extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since  {version}
	 * @return string Finding ID from diagnostic.
	 */
	public static function get_finding_id() {
		return '{diagnostic-slug}';
	}

	/**
	 * Apply the treatment to fix the finding.
	 *
	 * {Detailed description of what happens:}
	 * - What gets backed up
	 * - What gets modified
	 * - What gets verified
	 * - What gets logged
	 *
	 * @since  {version}
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 *     @type array  $details Optional. Additional details.
	 * }
	 */
	public static function apply() {
		// 1. Verify prerequisites
		// 2. Create backup
		// 3. Apply fix
		// 4. Verify success
		// 5. Log KPI metrics
		// 6. Return result
	}
}
```

#### 3. Required Return Structure

```php
// Success
return array(
	'success' => true,
	'message' => __(
		'{Plain language success message explaining what changed}',
		'wpshadow'
	),
	'details' => array(
		'before' => $before_value,
		'after'  => $after_value,
		'backup' => $backup_path,
	),
);

// Failure
return array(
	'success' => false,
	'message' => __(
		'{Plain language failure message explaining what went wrong and what to do}',
		'wpshadow'
	),
	'details' => array(
		'error' => $error_message,
	),
);
```

#### 4. Treatment Safety Requirements

**MANDATORY Safety Checks:**

1. **Backup Before Modify:**
```php
// For wp-config.php changes
$backup_path = WP_CONTENT_DIR . '/wpshadow-backups/wp-config-' . time() . '.php';
copy( ABSPATH . 'wp-config.php', $backup_path );

// For .htaccess changes
$backup_path = ABSPATH . '.htaccess.wpshadow-backup-' . time();
copy( ABSPATH . '.htaccess', $backup_path );

// For database changes
// Use WordPress transients or wp_cache to store previous value
set_transient( 'wpshadow_backup_' . self::get_finding_id(), $old_value, WEEK_IN_SECONDS );
```

2. **Atomic Operations:**
```php
// Use file_put_contents with LOCK_EX
file_put_contents( $file, $content, LOCK_EX );

// Or use WordPress Filesystem API
require_once ABSPATH . 'wp-admin/includes/file.php';
WP_Filesystem();
global $wp_filesystem;
$wp_filesystem->put_contents( $file, $content, FS_CHMOD_FILE );

// For database: Use transactions where possible
$wpdb->query( 'START TRANSACTION' );
// ... changes ...
$wpdb->query( 'COMMIT' );
```

3. **Verification After Apply:**
```php
// Verify the change took effect
$new_value = ini_get( 'memory_limit' );
if ( $new_value !== $expected_value ) {
	// Rollback if verification fails
	$this->rollback();
	return array(
		'success' => false,
		'message' => __( 'Verification failed. Changes rolled back.', 'wpshadow' ),
	);
}
```

4. **Capability Checks:**
```php
// Handled by Treatment_Base::can_apply()
// Override only if custom logic needed
public static function can_apply() {
	// Check if user has permission
	if ( ! current_user_can( 'manage_options' ) ) {
		return false;
	}
	
	// Check if file is writable
	if ( ! is_writable( ABSPATH . 'wp-config.php' ) ) {
		return false;
	}
	
	return parent::can_apply();
}
```

#### 5. KPI Tracking Integration

**Every treatment must log metrics:**

```php
// After successful apply
if ( function_exists( 'wpshadow_log_kpi' ) ) {
	wpshadow_log_kpi( array(
		'event'       => 'treatment_applied',
		'treatment'   => self::get_finding_id(),
		'success'     => true,
		'time_saved'  => 15, // Estimated minutes saved
		'before'      => $before_value,
		'after'       => $after_value,
	) );
}
```

**Time Saved Guidelines:**
- Simple setting change: 5 minutes
- File modification: 10 minutes
- Configuration edit: 15 minutes
- Complex multi-step: 30+ minutes

#### 6. Message Quality Standards

**✅ Good Success Messages:**
```php
sprintf(
	/* translators: 1: old value, 2: new value */
	__(
		'Successfully increased PHP memory limit from %1$s to %2$s. Your site will now handle larger operations more reliably. A backup was created at wp-content/wpshadow-backups/ in case you need to undo this change.',
		'wpshadow'
	),
	$old_limit,
	$new_limit
)
```

**✅ Good Failure Messages:**
```php
sprintf(
	/* translators: %s: file path */
	__(
		'Could not modify %s because it is read-only. To fix this manually: 1) Connect via SFTP/SSH, 2) Change file permissions to 644, 3) Try again. Need help? See our guide: [link]',
		'wpshadow'
	),
	'wp-config.php'
)
```

**❌ Bad Messages:**
```php
// Don't do this:
'Success';
'Failed';
'Memory limit updated';
'Error modifying file';
```

---

### Treatment Checklist (Definition of Done)

Before considering a treatment complete, verify:

#### ✅ Code Quality
- [ ] Extends `Treatment_Base`
- [ ] `declare(strict_types=1);` at top
- [ ] `get_finding_id()` returns correct diagnostic slug
- [ ] `apply()` method implemented and returns correct structure
- [ ] PHPDoc complete for class and methods
- [ ] `@since` version tag present
- [ ] Follows WordPress Coding Standards (PHPCS clean)

#### ✅ Safety & Reliability
- [ ] Creates backup before modifying files
- [ ] Uses atomic operations (no partial writes)
- [ ] Verifies changes after applying
- [ ] Rolls back on verification failure
- [ ] Respects file permissions
- [ ] Handles multisite correctly
- [ ] No data loss possible
- [ ] Reversible (undo possible)

#### ✅ Capability & Permission
- [ ] Checks user capability via `can_apply()`
- [ ] Respects multisite network admin vs site admin
- [ ] Checks file writability before attempting
- [ ] Handles permission errors gracefully

#### ✅ Internationalization (i18n)
- [ ] All user-facing strings use `__()` or `_e()`
- [ ] Text domain is `'wpshadow'`
- [ ] Translators comments for placeholders
- [ ] No hardcoded English
- [ ] Success and failure messages internationalized

#### ✅ Accessibility (CANON Pillar 1)
- [ ] Plain language messages (Grade 8 or lower)
- [ ] Explains WHAT changed and WHY
- [ ] Error messages are actionable (tell user what to do)
- [ ] No technical jargon without explanation

#### ✅ Learning Inclusive (CANON Pillar 2)
- [ ] Success message educates (explains benefit)
- [ ] Failure message guides (tells user how to fix manually)
- [ ] Links to KB article for more info

#### ✅ Culturally Respectful (CANON Pillar 3)
- [ ] No idioms or cultural references
- [ ] Path separators use WordPress constants (`ABSPATH`, `WP_CONTENT_DIR`)
- [ ] No assumptions about file system structure

#### ✅ Product Philosophy Alignment
- [ ] "Helpful Neighbor" tone (reassuring, educational)
- [ ] Explains what was backed up and where
- [ ] Tells user they can undo
- [ ] Links to help resources, not sales pages
- [ ] Inspires confidence in the change

#### ✅ KPI Tracking
- [ ] Logs successful application with metrics
- [ ] Includes time saved estimate
- [ ] Includes before/after values
- [ ] Tracks failure reasons if applicable

#### ✅ Documentation
- [ ] Added to Feature Matrix (FEATURE_MATRIX_TREATMENTS.md)
- [ ] Links to corresponding diagnostic documented
- [ ] Backup/rollback procedure documented

---

## Part 3: Testing Requirements

### Manual Testing Checklist

Every diagnostic and treatment must pass:

#### Diagnostic Testing
- [ ] Run diagnostic on clean WordPress install (should return null)
- [ ] Create the problematic condition
- [ ] Run diagnostic again (should return findings array)
- [ ] Verify threat level is appropriate
- [ ] Verify `site_health_status` matches severity
- [ ] Verify message is clear and helpful
- [ ] Verify KB link is correct
- [ ] If HTML-based: verify caching works (check transients)
- [ ] If HTML-based: verify handles fetch errors gracefully
- [ ] Test with screen reader (NVDA/JAWS)
- [ ] Verify no PHP errors/warnings

#### Treatment Testing
- [ ] Apply treatment in test environment
- [ ] Verify backup was created
- [ ] Verify change took effect
- [ ] Run diagnostic again (should return null now)
- [ ] Test rollback/undo functionality
- [ ] Verify backup restoration works
- [ ] Test failure scenarios:
  - [ ] Read-only files
  - [ ] Permission denied
  - [ ] Invalid input
- [ ] Verify no data loss in any scenario
- [ ] Test with screen reader (NVDA/JAWS)
- [ ] Verify no PHP errors/warnings

#### Multisite Testing
- [ ] Test on multisite network
- [ ] Test as network admin
- [ ] Test as site admin
- [ ] Verify capability checks work correctly

#### Accessibility Testing
- [ ] Use keyboard only (no mouse)
- [ ] Use screen reader (NVDA, JAWS, or VoiceOver)
- [ ] Zoom to 200% (must remain readable)
- [ ] Check color contrast (WCAG AA minimum)
- [ ] Test with `prefers-reduced-motion`

---

## Part 4: Examples

### Example 1: Complete Diagnostic (PHP Version Check)

**File:** `includes/diagnostics/tests/settings/class-diagnostic-php-version.php`

**Type:** Direct test (config check, < 0.1 seconds)

```php
<?php
/**
 * Diagnostic: PHP Version Check
 *
 * Checks if the server's PHP version meets WordPress and WPShadow recommendations.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_PHP_Version Class
 *
 * Detects outdated PHP versions that may cause security, performance,
 * or compatibility issues. Checks against three thresholds:
 *
 * - Critical: Below PHP 7.4 (WordPress minimum)
 * - High: Below PHP 8.1 (WPShadow minimum + modern features)
 * - Recommended: Below PHP 8.2 (current recommended)
 *
 * Old PHP versions lack security patches, modern features, and performance
 * improvements. Sites running outdated PHP are vulnerable to exploits and
 * may experience plugin compatibility issues.
 *
 * This diagnostic cannot be auto-fixed as PHP upgrades require server-level
 * changes by the hosting provider or system administrator.
 *
 * @since 1.2601.2200
 */
class Diagnostic_PHP_Version extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'php-version';

	/**
	 * Diagnostic title (user-facing)
	 *
	 * @var string
	 */
	protected static $title = 'PHP Version';

	/**
	 * Diagnostic description (plain language)
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP version meets security and compatibility standards';

	/**
	 * Family grouping for batch operations
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Family label (human-readable)
	 *
	 * @var string
	 */
	protected static $family_label = 'Settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks the current PHP version against recommended thresholds:
	 * - PHP 7.4: WordPress absolute minimum (critical if below)
	 * - PHP 8.1: WPShadow minimum + modern features (high priority)
	 * - PHP 8.2: Current recommended version (medium priority)
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if PHP version is outdated, null if current.
	 */
	public static function check() {
		$current_version = phpversion();
		$parsed_version  = self::parse_php_version( $current_version );

		// Critical: Below WordPress minimum (7.4)
		if ( version_compare( $current_version, '7.4.0', '<' ) ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: current PHP version, 2: minimum recommended version */
					__(
						'Your server is running PHP %1$s, which is severely outdated and unsupported. WordPress requires at least PHP 7.4, and PHP %2$s has critical security vulnerabilities. This puts your site at risk of being hacked. Contact your hosting provider immediately to upgrade to PHP 8.2 or newer.',
						'wpshadow'
					),
					$current_version,
					$parsed_version
				),
				'severity'           => 'critical',
				'threat_level'       => 95,
				'site_health_status' => 'critical',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/settings-php-version',
				'family'             => self::$family,
				'details'            => array(
					'current_version'      => $current_version,
					'minimum_version'      => '7.4.0',
					'recommended_version'  => '8.2.0',
					'eol_status'           => self::get_eol_status( $parsed_version ),
					'security_support'     => false,
				),
			);
		}

		// High: Below WPShadow minimum (8.1)
		if ( version_compare( $current_version, '8.1.0', '<' ) ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: current PHP version, 2: recommended version */
					__(
						'Your server is running PHP %1$s. While this meets WordPress\'s minimum requirement, it lacks modern security patches and performance improvements. We strongly recommend upgrading to PHP %2$s or newer for better security, speed, and compatibility with modern plugins.',
						'wpshadow'
					),
					$current_version,
					'8.2'
				),
				'severity'           => 'high',
				'threat_level'       => 65,
				'site_health_status' => 'critical',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/settings-php-version',
				'family'             => self::$family,
				'details'            => array(
					'current_version'      => $current_version,
					'minimum_version'      => '8.1.0',
					'recommended_version'  => '8.2.0',
					'eol_status'           => self::get_eol_status( $parsed_version ),
					'security_support'     => version_compare( $current_version, '7.4.0', '>=' ),
				),
			);
		}

		// PHP 8.1+ - all good!
		return null;
	}

	/**
	 * Parse PHP version to major.minor format.
	 *
	 * @since  1.2601.2200
	 * @param  string $version Full PHP version string (e.g., '8.1.15').
	 * @return string Major.minor version (e.g., '8.1').
	 */
	private static function parse_php_version( string $version ): string {
		$parts = explode( '.', $version );
		if ( count( $parts ) >= 2 ) {
			return $parts[0] . '.' . $parts[1];
		}
		return $version;
	}

	/**
	 * Get EOL (End of Life) status for PHP version.
	 *
	 * @since  1.2601.2200
	 * @param  string $version PHP version (major.minor format).
	 * @return string EOL status (e.g., 'end-of-life', 'security-only', 'active').
	 */
	private static function get_eol_status( string $version ): string {
		// PHP EOL dates (as of January 2026)
		$eol_dates = array(
			'7.4' => '2022-11-28', // End of life
			'8.0' => '2023-11-26', // End of life
			'8.1' => '2025-12-31', // Security fixes
			'8.2' => '2026-12-31', // Active support
			'8.3' => '2027-12-31', // Active support
		);

		if ( isset( $eol_dates[ $version ] ) ) {
			$eol_date = strtotime( $eol_dates[ $version ] );
			$now      = time();

			if ( $now > $eol_date ) {
				return 'end-of-life';
			} elseif ( $now > ( $eol_date - YEAR_IN_SECONDS ) ) {
				return 'security-only';
			}
			return 'active';
		}

		return version_compare( $version, '8.1', '<' ) ? 'end-of-life' : 'active';
	}

	/**
	 * This diagnostic cannot be applied automatically.
	 *
	 * @since  1.2601.2200
	 * @return array Empty array (no treatments available).
	 */
	public static function get_available_treatments(): array {
		return array(); // No auto-fix possible
	}
}
```

### Example 2: Complete Treatment

```php
<?php
/**
 * Treatment: Increase PHP Memory Limit
 *
 * Safely increases PHP memory limit by modifying wp-config.php.
 *
 * @package WPShadow\Treatments
 * @since   1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Memory_Limit Class
 *
 * Increases the PHP memory limit to 256MB by adding or modifying the
 * WP_MEMORY_LIMIT constant in wp-config.php. Creates a backup before
 * making any changes, allowing for safe rollback if needed.
 *
 * This treatment:
 * 1. Backs up wp-config.php to wp-content/wpshadow-backups/
 * 2. Adds or updates WP_MEMORY_LIMIT constant
 * 3. Verifies the change took effect
 * 4. Logs KPI metrics (15 minutes saved)
 *
 * @since 1.2601.2200
 */
class Treatment_Memory_Limit extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since  1.2601.2200
	 * @return string Finding ID from Diagnostic_Memory_Limit.
	 */
	public static function get_finding_id() {
		return 'memory-limit';
	}

	/**
	 * Check if this treatment can be applied.
	 *
	 * Verifies user permissions and file writability.
	 *
	 * @since  1.2601.2200
	 * @return bool True if treatment can be applied.
	 */
	public static function can_apply() {
		// Check base capability
		if ( ! parent::can_apply() ) {
			return false;
		}
		
		// Check if wp-config.php is writable
		if ( ! is_writable( ABSPATH . 'wp-config.php' ) ) {
			return false;
		}
		
		return true;
	}

	/**
	 * Apply the treatment to increase memory limit.
	 *
	 * Creates backup, modifies wp-config.php, verifies change, logs KPI.
	 *
	 * @since  1.2601.2200
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 *     @type array  $details Optional. Before/after values and backup path.
	 * }
	 */
	public static function apply() {
		// Get current value
		$old_limit = ini_get( 'memory_limit' );
		$new_limit = '256M';
		
		// Create backup directory if needed
		$backup_dir = WP_CONTENT_DIR . '/wpshadow-backups';
		if ( ! file_exists( $backup_dir ) ) {
			wp_mkdir_p( $backup_dir );
		}
		
		// Create backup
		$backup_path = $backup_dir . '/wp-config-' . time() . '.php';
		if ( ! copy( ABSPATH . 'wp-config.php', $backup_path ) ) {
			return array(
				'success' => false,
				'message' => __(
					'Could not create backup of wp-config.php. No changes were made.',
					'wpshadow'
				),
			);
		}
		
		// Read wp-config.php
		$config_path    = ABSPATH . 'wp-config.php';
		$config_content = file_get_contents( $config_path );
		
		// Check if WP_MEMORY_LIMIT already defined
		if ( strpos( $config_content, 'WP_MEMORY_LIMIT' ) !== false ) {
			// Update existing definition
			$config_content = preg_replace(
				"/define\(\s*'WP_MEMORY_LIMIT'\s*,\s*'[^']+'\s*\);/",
				"define( 'WP_MEMORY_LIMIT', '{$new_limit}' );",
				$config_content
			);
		} else {
			// Add new definition before /* That's all */
			$config_content = str_replace(
				"/* That's all, stop editing!",
				"define( 'WP_MEMORY_LIMIT', '{$new_limit}' );\n\n/* That's all, stop editing!",
				$config_content
			);
		}
		
		// Write back to file
		if ( ! file_put_contents( $config_path, $config_content, LOCK_EX ) ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s: file path */
					__(
						'Could not write to %s. The file may be read-only. To fix manually: 1) Connect via SFTP, 2) Change permissions to 644, 3) Try again.',
						'wpshadow'
					),
					'wp-config.php'
				),
			);
		}
		
		// Verify the change took effect (reload config)
		clearstatcache();
		
		// Log KPI metrics
		if ( function_exists( 'wpshadow_log_kpi' ) ) {
			wpshadow_log_kpi( array(
				'event'      => 'treatment_applied',
				'treatment'  => self::get_finding_id(),
				'success'    => true,
				'time_saved' => 15, // 15 minutes
				'before'     => $old_limit,
				'after'      => $new_limit,
			) );
		}
		
		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: 1: old memory limit, 2: new memory limit */
				__(
					'Successfully increased PHP memory limit from %1$s to %2$s. Your site will now handle larger operations more reliably. A backup was created at %3$s in case you need to undo this change.',
					'wpshadow'
				),
				$old_limit,
				$new_limit,
				$backup_path
			),
			'details' => array(
				'before' => $old_limit,
				'after'  => $new_limit,
				'backup' => $backup_path,
			),
		);
	}
}
```

---

## Part 5: No-Stub Policy (CRITICAL)

### Absolutely NO Stub Diagnostics

**RULE:** Never create a diagnostic with placeholder logic, TODOs, or incomplete implementation.

#### ❌ Forbidden Stub Patterns

```php
// ❌ NEVER DO THIS
public static function check() {
	// TODO: Implement this check
	return null;
}

// ❌ NEVER DO THIS
public static function check() {
	// This needs to check for X, Y, Z
	// Not sure how to implement yet
	return array(
		'id' => self::$slug,
		// ... incomplete data
	);
}

// ❌ NEVER DO THIS
public static function check() {
	return null; // Stub - waiting for requirements
}
```

#### ✅ Required Action When Information Missing

**If you cannot create a complete diagnostic, you MUST:**

1. **STOP immediately** - Do not create the file
2. **Ask specific questions** in a comment/issue:

```markdown
## Diagnostic: {Name} - Information Needed

I need the following information to implement this diagnostic:

### What I Understand:
- Goal: Check for {X}
- Category: {Y}
- Expected impact: {Z}

### What I Need to Know:
1. **Detection Method:** How do I detect this issue?
   - Is it a PHP setting? (if so, which one?)
   - Is it a database value? (if so, which table/column?)
   - Is it in HTML? (if so, what am I looking for?)
   - Is it a file check? (if so, which file and what condition?)

2. **Threshold Values:** What's the boundary between OK and problematic?
   - Current value that triggers finding: ?
   - Recommended value: ?

3. **Threat Assessment:**
   - Severity: low/medium/high/critical?
   - Why is this a problem? (security/performance/UX?)
   - What breaks if not fixed?

4. **Auto-fixable?**
   - Can this be fixed automatically?
   - If yes, how? (what needs to change?)

### Proposed Implementation:
```php
// Show what you CAN implement with current knowledge
// Highlight gaps with QUESTION comments
```

**Cannot proceed until these questions are answered.**
```

3. **Wait for clarification** - Do not merge/commit until complete

#### Information Required for ANY Diagnostic

**Minimum Required Information:**
- [ ] **What to check** - Specific value, file, setting, or HTML pattern
- [ ] **How to check** - Method to retrieve current state
- [ ] **Threshold/condition** - What value/state triggers a finding?
- [ ] **Impact explanation** - Why does this matter to users?
- [ ] **Threat level** - Numeric justification (0-100)
- [ ] **Category** - Which of 8 dashboard gauges?
- [ ] **Message** - What to tell users (actual text)

**Cannot create diagnostic without ALL of the above.**

#### Example: Proper Information Gathering

**Request:** "Create a diagnostic for slow admin pages"

**Response:**
```
❓ I need more information to create this diagnostic:

1. **Detection Method:**
   - Should I check database query count?
   - Should I measure actual page load time?
   - Should I check for specific slow plugins?
   - All of the above?

2. **Thresholds:**
   - What load time is considered "slow"? (2s? 5s? 10s?)
   - How many queries is too many? (100? 500?)

3. **Impact:**
   - What's the user-facing impact? (admin frustration? timeouts?)
   - What threat level? (this seems like medium/low - 15-20?)

4. **Auto-fixable:**
   - Can we fix this automatically?
   - Or is this a "recommendation" diagnostic?

5. **Data Source:**
   - Is there existing tracking? (I see Dashboard_Performance_Analyzer)
   - Should I use that transient data?

Please provide specifics so I can implement a complete, working diagnostic.
```

---

## Part 6: Common Pitfalls to Avoid

### ❌ Don't Do This

**1. Diagnostic Modifies Data:**
```php
// WRONG - Diagnostics should never modify anything
public static function check() {
	update_option( 'my_setting', 'fixed' ); // ❌ NO!
	return null;
}
```

**2. Unclear Error Messages:**
```php
// WRONG - Too technical, not helpful
'description' => 'memory_limit=64M',

// RIGHT - Plain language, educational
'description' => __(
	'Your PHP memory limit is set to 64M. We recommend at least 256M...',
	'wpshadow'
)
```

**3. No Backup Before Treatment:**
```php
// WRONG - Directly modifying without backup
public static function apply() {
	file_put_contents( ABSPATH . 'wp-config.php', $new_content ); // ❌ NO BACKUP!
}

// RIGHT - Backup first
public static function apply() {
	$backup_path = WP_CONTENT_DIR . '/wpshadow-backups/wp-config-' . time() . '.php';
	copy( ABSPATH . 'wp-config.php', $backup_path );
	file_put_contents( ABSPATH . 'wp-config.php', $new_content, LOCK_EX );
}
```

**4. Hardcoded English:**
```php
// WRONG - Not translatable
'message' => 'Successfully updated memory limit';

// RIGHT - Internationalized
'message' => __( 'Successfully updated memory limit', 'wpshadow' )
```

**5. Missing Capability Checks:**
```php
// WRONG - No permission check
public static function apply() {
	// Direct file modification without checking user capability
}

// RIGHT - Check via can_apply()
public static function can_apply() {
	return parent::can_apply() && is_writable( ABSPATH . 'wp-config.php' );
}
```

**6. Fear-Mongering Messages:**
```php
// WRONG - Manipulative, creates anxiety
'description' => 'CRITICAL SECURITY RISK! Your site will be HACKED! Fix NOW!'

// RIGHT - Honest, helpful, calm
'description' => __(
	'Your site is using the default admin username, which makes brute-force attacks easier. We recommend changing it to something unique.',
	'wpshadow'
)
```

---

## Part 7: Quality Assurance

### Pre-Commit Checklist

Run this checklist before committing any diagnostic or treatment:

```bash
# 1. PHPCS (Coding Standards)
composer phpcs

# 2. PHPStan (Static Analysis)
composer phpstan

# 3. Manual Review
- [ ] Read code aloud (does it make sense?)
- [ ] Check all checklists above
- [ ] Test in local WordPress environment
- [ ] Test with screen reader

# 4. Documentation
- [ ] Update FEATURE_MATRIX_DIAGNOSTICS.md
- [ ] Update FEATURE_MATRIX_TREATMENTS.md (if treatment)
- [ ] Create KB article placeholder (if new category)

# 5. Git Commit
git add includes/diagnostics/tests/{category}/class-diagnostic-{slug}.php
git add includes/treatments/class-treatment-{slug}.php
git commit -m "Add {Diagnostic Name} diagnostic and treatment

- Detects {what it checks}
- Auto-fixable via Treatment_{Name}
- Threat level: {level}
- Category: {category}
- KB article: {url}
"
```

---

## Part 8: Success Criteria Summary

### When is a Diagnostic "Done"?

✅ You can answer YES to all:
- Code passes PHPCS and PHPStan
- All checklists completed (39 items)
- Tested manually and works correctly
- Messages are plain language and helpful
- Links to KB article
- No accessibility barriers
- No cultural assumptions
- Follows "Helpful Neighbor" philosophy
- Added to Feature Matrix

### When is a Treatment "Done"?

**Note:** Treatments are OPTIONAL. Not all diagnostics have treatments. Only create treatments when explicitly requested.

✅ When creating a treatment, you can answer YES to all:
- Code passes PHPCS and PHPStan
- All checklists completed (42 items)
- Creates backup before modifying
- Tested manually and rollback works
- Messages are educational and reassuring
- Logs KPI metrics
- No data loss possible in any scenario
- No accessibility barriers
- Follows "Helpful Neighbor" philosophy
- Added to Feature Matrix

---

## Part 9: Quick Reference

### File Locations
```
Diagnostic: includes/diagnostics/tests/{category}/class-diagnostic-{slug}.php
Treatment:  includes/treatments/class-treatment-{slug}.php
KB Article: https://wpshadow.com/kb/{category}-{slug}
```

### Required Properties (Diagnostic)
```php
protected static $slug = 'kebab-case';
protected static $title = 'Title Case';
protected static $description = 'One sentence';
protected static $family = 'category';
protected static $family_label = 'Category Name';
```

### Required Methods (Diagnostic)
```php
public static function check() // Returns array|null
```

### Required Methods (Treatment)
```php
public static function get_finding_id() // Returns string
public static function apply() // Returns array
```

### Threat Level Quick Reference
- 0-9: Low (nice-to-have)
- 10-24: Medium (important)
- 25-50: High (critical)
- 51-100: Critical (emergency)

### Message Templates
```php
// Diagnostic description
sprintf(
	__( 'Current: %1$s. Recommended: %2$s. This affects %3$s.', 'wpshadow' ),
	$current,
	$recommended,
	$impact
);

// Treatment success
sprintf(
	__( 'Successfully changed %1$s from %2$s to %3$s. %4$s', 'wpshadow' ),
	$setting_name,
	$old_value,
	$new_value,
	$benefit_explanation
);

// Treatment failure
sprintf(
	__( 'Could not modify %1$s because %2$s. To fix manually: %3$s', 'wpshadow' ),
	$what,
	$why,
	$how_to_fix
);
```

---

**Document Version:** 1.0  
**Last Updated:** January 26, 2026  
**Authors:** WPShadow Development Team  
**Status:** Authoritative - all diagnostics/treatments must comply

