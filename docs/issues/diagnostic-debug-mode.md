# Diagnostic: Debug Mode in Production

**Labels:** `diagnostic`, `tier-1`, `security`, `auto-fixable`  
**Milestone:** Diagnostic System Rebuild  
**Priority:** Critical (Tier 1)

---

## Overview

**Slug:** `debug-mode`  
**Category:** Security  
**Threat Level:** 80 (High)  
**Auto-Fixable:** Yes  
**Test Type:** Direct (< 0.1s)

### What to Check
Detect if `WP_DEBUG`, `WP_DEBUG_DISPLAY`, or `WP_DEBUG_LOG` are enabled on a production site, exposing sensitive error information to attackers.

### Why It Matters
- **Security Risk:** Debug mode exposes file paths, database queries, and error stack traces
- **Attack Surface:** Gives attackers insight into plugin versions, file structure, and potential vulnerabilities
- **Performance:** Debug logging slows down the site
- **User Experience:** Error messages shown to visitors look unprofessional

---

## Diagnostic Thresholds

### Critical (Threat Level 80)
**Condition:** `WP_DEBUG` is `true` AND `WP_DEBUG_DISPLAY` is `true`  
**Site Health:** `critical`  
**Message:**
```
Debug mode is enabled and displaying errors publicly. This exposes sensitive information about your site's structure, plugins, and potential vulnerabilities to attackers. Disable debug mode immediately on production sites.
```

### Recommended (Threat Level 50)
**Condition:** `WP_DEBUG` is `true` AND `WP_DEBUG_LOG` is `true` (but display is off)  
**Site Health:** `recommended`  
**Message:**
```
Debug logging is enabled in production. While errors aren't displayed publicly, the debug.log file may expose sensitive information. Consider disabling debug mode unless actively troubleshooting.
```

### Good (No Finding)
**Condition:** `WP_DEBUG` is `false` (or undefined)  
**Returns:** `null` (no finding)

---

## Implementation Details

### File Location
```
includes/diagnostics/tests/security/class-diagnostic-debug-mode.php
```

### Detection Method
```php
// Check constants in wp-config.php
$wp_debug = defined( 'WP_DEBUG' ) && WP_DEBUG;
$wp_debug_display = defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;
$wp_debug_log = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;

// Severity logic
if ( $wp_debug && $wp_debug_display ) {
    // Critical: Public error display
    return $this->create_finding( /* threat_level: 80 */ );
} elseif ( $wp_debug && $wp_debug_log ) {
    // Recommended: Logging only
    return $this->create_finding( /* threat_level: 50 */ );
}

return null; // All good
```

### Related Treatment
**Treatment Slug:** `debug-mode`  
**Treatment File:** `includes/treatments/class-treatment-debug-mode.php`  
**Action:** Update wp-config.php to set:
- `WP_DEBUG` → `false`
- `WP_DEBUG_DISPLAY` → `false`
- `WP_DEBUG_LOG` → `false`

**Backup:** Create wp-config.php backup before modification  
**Undo:** Restore from backup

---

## Required Data Points

### Constants to Check
- `WP_DEBUG` - Main debug flag
- `WP_DEBUG_DISPLAY` - Show errors publicly
- `WP_DEBUG_LOG` - Write to debug.log
- `SCRIPT_DEBUG` - Use unminified JS/CSS (optional, lower priority)

### Environment Context
- `wp_get_environment_type()` - Should be 'production', 'staging', or 'development'
- If 'development' or 'local', debug mode is expected (lower threat level)

---

## Messages (Plain Language)

### Title
```
Debug Mode Enabled in Production
```

### Short Description
```
WordPress debug mode is active, which can expose sensitive information to attackers.
```

### Long Description (Critical)
```
Your site is currently displaying detailed error messages and debug information publicly. This is a serious security risk because it reveals:

• File paths and directory structure
• Database query details
• Plugin and theme versions
• PHP configuration details
• Error stack traces with code snippets

Attackers can use this information to identify vulnerabilities and plan targeted attacks. Debug mode should only be enabled temporarily in development environments, never on live sites.
```

### Long Description (Recommended)
```
Your site has debug logging enabled, which writes detailed error information to the debug.log file. While this file isn't displayed publicly, it may:

• Accumulate sensitive information over time
• Slow down your site with excessive logging
• Fill up disk space if errors are frequent
• Be accessible if file permissions are misconfigured

Consider disabling debug mode unless you're actively troubleshooting an issue.
```

### What to Do About It
```
**Recommended Action:** Disable debug mode by editing wp-config.php:

1. Locate the line: define( 'WP_DEBUG', true );
2. Change it to: define( 'WP_DEBUG', false );
3. Remove or disable WP_DEBUG_DISPLAY and WP_DEBUG_LOG

Or use WPShadow's auto-fix to disable debug mode safely with automatic backup.
```

---

## KB Article

**URL:** `https://wpshadow.com/kb/security-debug-mode`

**Content Outline:**
1. What is WordPress debug mode?
2. Why is it dangerous in production?
3. When should you use debug mode?
4. How to disable it safely
5. Alternative: Using query monitor plugin
6. How to enable debug mode temporarily
7. Best practices for debugging on live sites

---

## Site Health Bridge Mapping

| Threat Level | Site Health Status | Badge Color |
|--------------|-------------------|-------------|
| 80 (Critical) | `critical` | Red |
| 50 (Recommended) | `recommended` | Orange |
| 0 (Good) | `good` | Green |

---

## Implementation Checklist

### Code Requirements
- [ ] File created: `includes/diagnostics/tests/security/class-diagnostic-debug-mode.php`
- [ ] Extends `Diagnostic_Base`
- [ ] Namespace: `WPShadow\Diagnostics`
- [ ] Protected properties: `$slug`, `$title`, `$description`, `$category`, `$kb_url`
- [ ] `check()` method returns `array|null`
- [ ] Return format includes: `id`, `title`, `description`, `severity`, `threat_level`, `site_health_status`, `category`, `kb_url`, `auto_fixable`, `treatment_slug`
- [ ] Test type: `direct` (config check, < 0.1s)

### Message Quality
- [ ] Title is clear and actionable (Grade 8 reading level)
- [ ] Short description explains the issue in one sentence
- [ ] Long description explains WHY it matters, not just WHAT
- [ ] Includes specific impact statements
- [ ] No technical jargon without explanation
- [ ] "What to Do" section provides clear next steps
- [ ] All strings use `__()` for i18n with 'wpshadow' text domain

### Threat Level Logic
- [ ] Critical (80): Public error display enabled
- [ ] Recommended (50): Logging only, no public display
- [ ] Good (0): Debug mode disabled
- [ ] Returns `null` when no issue found
- [ ] Threat level matches severity of actual risk

### Site Health Integration
- [ ] Threat level 80 → `site_health_status` = 'critical'
- [ ] Threat level 50 → `site_health_status` = 'recommended'
- [ ] Uses threshold constants: `WPSHADOW_SEVERITY_CRITICAL_THRESHOLD` (75), `WPSHADOW_SEVERITY_RECOMMENDED_THRESHOLD` (50)

### Category & Metadata
- [ ] Category: `security` (matches dashboard gauge)
- [ ] KB URL follows format: `https://wpshadow.com/kb/security-debug-mode`
- [ ] Auto-fixable: `true`
- [ ] Treatment slug: `debug-mode`

### Documentation
- [ ] Class-level PHPDoc explaining purpose, impact, conditions
- [ ] Method-level PHPDoc for `check()` with `@return array|null`
- [ ] Inline comments for complex logic
- [ ] Examples in docblocks where helpful

### Testing
- [ ] PHP syntax validated: `php -l class-diagnostic-debug-mode.php`
- [ ] PHPCS passes (WordPress coding standards)
- [ ] Tested with debug enabled (should return finding)
- [ ] Tested with debug disabled (should return null)
- [ ] Tested on wpshadow.com (document result: pass/fail)

### No Stubs Policy
- [ ] No TODO comments
- [ ] No placeholder functions
- [ ] No commented-out code blocks
- [ ] All logic fully implemented
- [ ] If anything is incomplete, STOP and ask for clarification using template:
  ```
  ⚠️ INCOMPLETE INFORMATION - Cannot Proceed
  
  Diagnostic: debug-mode
  Missing Information: [what you need]
  
  Questions:
  1. [Specific question]
  2. [Specific question]
  
  Cannot create stub. Awaiting answers to complete implementation.
  ```

---

## Related Documentation
- [DIAGNOSTIC_AND_TREATMENT_SPECIFICATION.md](../DIAGNOSTIC_AND_TREATMENT_SPECIFICATION.md)
- [DIAGNOSTICS_IMPLEMENTATION_TRACKER.md](../DIAGNOSTICS_IMPLEMENTATION_TRACKER.md)
- [ARCHITECTURE.md](../ARCHITECTURE.md)

---

**Issue Created:** 2026-01-26  
**Specification Version:** 1.0  
**Estimated Implementation Time:** 2-3 hours
