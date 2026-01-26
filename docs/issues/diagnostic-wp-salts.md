# Diagnostic: WordPress Security Keys and Salts

**Labels:** `diagnostic`, `tier-1`, `security`  
**Milestone:** Diagnostic System Rebuild  
**Priority:** Critical (Tier 1)

---

## Overview

**Slug:** `wp-salts`  
**Category:** Security  
**Threat Level:** 90 (Critical)  
**Auto-Fixable:** No (requires careful timing to avoid mass logout)  
**Test Type:** Direct (< 0.1s)

### What to Check
Detect if WordPress security keys and salts are using default values, are too short, or are identical to each other, which compromises session security and cookie encryption.

### Why It Matters
- **Session Hijacking:** Weak salts allow attackers to forge authentication cookies
- **Password Security:** Salts protect password hashes in the database
- **Cookie Encryption:** Keys encrypt user sessions and login cookies
- **Brute Force Protection:** Strong random keys make attacks exponentially harder
- **Critical Infrastructure:** Salts are WordPress's primary security mechanism for user authentication

---

## Diagnostic Thresholds

### Critical (Threat Level 90)
**Condition:** One or more security constants are:
- Not defined at all
- Using default example values
- Shorter than 64 characters
- Identical to each other

**Site Health:** `critical`  
**Message:**
```
Your WordPress security keys and salts are missing, using default values, or are too weak. This is a critical security vulnerability that allows attackers to forge authentication cookies, hijack user sessions, and potentially gain administrative access to your site. Generate and install new security keys immediately.
```

### Good (No Finding)
**Condition:** All 8 security constants are:
- Defined
- At least 64 characters
- Unique from each other
- Not using default/example values

**Returns:** `null` (no finding)

---

## Implementation Details

### File Location
```
includes/diagnostics/tests/security/class-diagnostic-wp-salts.php
```

### Detection Method
```php
// Eight required constants
$required_salts = array(
    'AUTH_KEY',
    'SECURE_AUTH_KEY',
    'LOGGED_IN_KEY',
    'NONCE_KEY',
    'AUTH_SALT',
    'SECURE_AUTH_SALT',
    'LOGGED_IN_SALT',
    'NONCE_SALT',
);

$issues = array();

foreach ( $required_salts as $salt ) {
    // Check if defined
    if ( ! defined( $salt ) ) {
        $issues[] = "$salt is not defined";
        continue;
    }
    
    $value = constant( $salt );
    
    // Check if using default
    if ( $this->is_default_value( $value ) ) {
        $issues[] = "$salt is using default value";
    }
    
    // Check length (should be 64 characters)
    if ( strlen( $value ) < 64 ) {
        $issues[] = "$salt is too short (" . strlen( $value ) . " characters)";
    }
}

// Check for duplicates
if ( $this->has_duplicate_values( $required_salts ) ) {
    $issues[] = "Multiple constants have identical values";
}

if ( ! empty( $issues ) ) {
    return $this->create_finding( /* threat_level: 90, include $issues */ );
}

return null; // All salts are good
```

### Helper Methods

**`is_default_value( $value )`** - Check if value is a known default:
```php
private function is_default_value( $value ) {
    $defaults = array(
        'put your unique phrase here',
        'unique phrase',
        'example',
        '',
    );
    
    return in_array( strtolower( trim( $value ) ), $defaults, true );
}
```

**`has_duplicate_values( $constants )`** - Check if any values are identical:
```php
private function has_duplicate_values( $constants ) {
    $values = array();
    
    foreach ( $constants as $constant ) {
        if ( ! defined( $constant ) ) {
            continue;
        }
        $values[] = constant( $constant );
    }
    
    return count( $values ) !== count( array_unique( $values ) );
}
```

### Related Treatment
**Treatment Slug:** None  
**Auto-Fixable:** No  
**Reason:** Changing salts logs out ALL users immediately. This requires:
1. Advance warning to site owner
2. Notification to users
3. Coordination with site maintenance window
4. Cannot be done automatically without user consent

**Manual Process:**
1. Visit https://api.wordpress.org/secret-key/1.1/salt/
2. Copy the generated keys
3. Backup wp-config.php
4. Replace old salts in wp-config.php
5. Note: All users will be logged out
6. Clear object cache if using one

---

## Required Data Points

### Constants to Check
All 8 security constants (defined in wp-config.php):
- `AUTH_KEY`
- `SECURE_AUTH_KEY`
- `LOGGED_IN_KEY`
- `NONCE_KEY`
- `AUTH_SALT`
- `SECURE_AUTH_SALT`
- `LOGGED_IN_SALT`
- `NONCE_SALT`

### Validation Criteria
- Each constant must be defined
- Each must be at least 64 characters long
- No default values ("put your unique phrase here", etc.)
- All values must be unique (no duplicates)
- Should contain mix of characters (a-z, A-Z, 0-9, symbols)

### Additional Context
- Count of active user sessions (to gauge logout impact)
- Last modified date of wp-config.php (when were salts last updated?)
- WordPress recommends changing salts every 6-12 months

---

## Messages (Plain Language)

### Title
```
WordPress Security Keys Are Weak or Missing
```

### Short Description
```
Your WordPress security keys and salts are not properly configured, which makes your site vulnerable to session hijacking attacks.
```

### Long Description
```
WordPress uses 8 security keys and salts to encrypt user sessions, protect cookies, and secure password hashes. We found the following issues:

{List specific issues found}

**What this means:**

Security keys and salts are like the master lock on your site's user authentication system. When they're weak, missing, or using default values:

• Attackers can forge authentication cookies
• User sessions can be hijacked without passwords
• Attackers can impersonate administrators
• Password hashes in the database are easier to crack
• Brute force attacks on cookies become feasible

**How attacks work:**

1. Attacker captures your authentication cookie
2. Using weak or default salts, they decrypt it
3. They modify the cookie to impersonate an administrator
4. They gain full access to your site without knowing any passwords

This is one of the most critical security settings in WordPress. Many major site compromises started with weak security keys.
```

### What to Do About It
```
**Recommended Action:** Generate and install new security keys

**Step 1: Generate New Keys**
Visit this URL (opens automatically when you click "Fix It"):
https://api.wordpress.org/secret-key/1.1/salt/

Copy all 8 define() statements.

**Step 2: Backup wp-config.php**
Before making changes, download a backup of your wp-config.php file.

**Step 3: Update wp-config.php**
1. Open wp-config.php in a text editor
2. Find the section with AUTH_KEY, SECURE_AUTH_KEY, etc. (around line 50)
3. Replace all 8 define() statements with the new ones
4. Save the file

**Step 4: Upload (if editing locally)**
Upload the updated wp-config.php back to your server.

**Important Side Effect:** All users will be logged out immediately, including you. This is normal and expected. They'll need to log back in with their username and password.

**Security Note:** Consider changing your security keys every 6-12 months as part of routine maintenance. You can do this anytime without warning if you suspect a security breach.

**Need Help?** See our step-by-step video guide: https://wpshadow.com/kb/security-wp-salts#video-guide
```

---

## KB Article

**URL:** `https://wpshadow.com/kb/security-wp-salts`

**Content Outline:**
1. What are WordPress security keys and salts?
2. How they protect your site (technical explanation)
3. Why default values are dangerous
4. How to generate new keys (step-by-step with screenshots)
5. What happens when you change keys (all users logged out)
6. How often to rotate keys (best practices)
7. Multisite considerations
8. Advanced: Using wp-cli to update salts
9. Related: Cookie security, session management
10. Video tutorial: Complete walkthrough

---

## Site Health Bridge Mapping

| Threat Level | Site Health Status | Badge Color |
|--------------|-------------------|-------------|
| 90 (Critical) | `critical` | Red |
| 0 (Good) | `good` | Green |

---

## Implementation Checklist

### Code Requirements
- [ ] File created: `includes/diagnostics/tests/security/class-diagnostic-wp-salts.php`
- [ ] Extends `Diagnostic_Base`
- [ ] Namespace: `WPShadow\Diagnostics`
- [ ] Protected properties: `$slug`, `$title`, `$description`, `$category`, `$kb_url`
- [ ] `check()` method returns `array|null`
- [ ] Helper method: `is_default_value()` to check known defaults
- [ ] Helper method: `has_duplicate_values()` to check uniqueness
- [ ] Return format includes: `id`, `title`, `description`, `severity`, `threat_level`, `site_health_status`, `category`, `kb_url`, `auto_fixable` (false), specific issues found
- [ ] Test type: `direct` (constant check, < 0.1s)

### Message Quality
- [ ] Title is clear and urgent (Grade 8 reading level)
- [ ] Short description explains the issue in one sentence
- [ ] Long description explains WHY it matters with attack scenarios
- [ ] Includes real-world impact examples
- [ ] Explains how attacks work (simplified)
- [ ] No technical jargon without explanation
- [ ] "What to Do" section provides complete step-by-step guide
- [ ] Warnings about logout included (all users)
- [ ] Links to API generator included
- [ ] All strings use `__()` for i18n with 'wpshadow' text domain

### Threat Level Logic
- [ ] Critical (90): Any salt missing, too short, default, or duplicate
- [ ] Good (0): All 8 salts properly configured
- [ ] Returns `null` when no issue found
- [ ] Returns list of specific issues when problems detected
- [ ] Threat level matches severity (authentication compromise is critical)

### Site Health Integration
- [ ] Threat level 90 → `site_health_status` = 'critical'
- [ ] Uses threshold constant: `WPSHADOW_SEVERITY_CRITICAL_THRESHOLD` (75)
- [ ] Properly categorized as critical security issue

### Category & Metadata
- [ ] Category: `security` (matches dashboard gauge)
- [ ] KB URL follows format: `https://wpshadow.com/kb/security-wp-salts`
- [ ] Auto-fixable: `false` (requires user coordination due to mass logout)
- [ ] Treatment slug: N/A (no automated treatment)
- [ ] External link: `https://api.wordpress.org/secret-key/1.1/salt/`

### Documentation
- [ ] Class-level PHPDoc explaining purpose, impact, conditions
- [ ] Method-level PHPDoc for `check()` with `@return array|null`
- [ ] Method-level PHPDoc for helper methods
- [ ] Inline comments explaining why not auto-fixable
- [ ] Security rationale documented
- [ ] Logout impact documented

### Testing
- [ ] PHP syntax validated: `php -l class-diagnostic-wp-salts.php`
- [ ] PHPCS passes (WordPress coding standards)
- [ ] Tested with missing salts - should return critical finding
- [ ] Tested with default values - should return critical finding
- [ ] Tested with short values - should return critical finding
- [ ] Tested with duplicate values - should return critical finding
- [ ] Tested with proper unique 64+ char values - should return null
- [ ] Tested on wpshadow.com (document result: pass/fail)

### No Stubs Policy
- [ ] No TODO comments
- [ ] No placeholder functions
- [ ] No commented-out code blocks
- [ ] All logic fully implemented
- [ ] Clearly documented why auto-fix is not available

---

## Related Documentation
- [DIAGNOSTIC_AND_TREATMENT_SPECIFICATION.md](../DIAGNOSTIC_AND_TREATMENT_SPECIFICATION.md)
- [DIAGNOSTICS_IMPLEMENTATION_TRACKER.md](../DIAGNOSTICS_IMPLEMENTATION_TRACKER.md)
- [ARCHITECTURE.md](../ARCHITECTURE.md)
- WordPress Codex: [Security Keys](https://wordpress.org/support/article/editing-wp-config-php/#security-keys)

---

**Issue Created:** 2026-01-26  
**Specification Version:** 1.0  
**Estimated Implementation Time:** 3-4 hours
