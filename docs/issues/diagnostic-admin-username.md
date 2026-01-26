# Diagnostic: Default Admin Username

**Labels:** `diagnostic`, `tier-1`, `security`  
**Milestone:** Diagnostic System Rebuild  
**Priority:** Critical (Tier 1)

---

## Overview

**Slug:** `admin-username`  
**Category:** Security  
**Threat Level:** 75 (High)  
**Auto-Fixable:** No (requires manual user creation/deletion)  
**Test Type:** Direct (< 0.1s)

### What to Check
Detect if any user account has the username "admin" (or common variations like "administrator"), which is a primary target for brute force attacks.

### Why It Matters
- **Brute Force Target:** Attackers know 50% of sites use "admin" username
- **Half the Puzzle:** Username is 50% of login credentials; attackers only need to guess password
- **Automated Attacks:** Bots constantly try "admin" + common passwords
- **Security Best Practice:** Using unique usernames makes brute force attacks exponentially harder

---

## Diagnostic Thresholds

### Critical (Threat Level 75)
**Condition:** User with username "admin" exists AND has administrator role  
**Site Health:** `critical`  
**Message:**
```
You have an administrator account with the username "admin". This is a primary target for brute force attacks because attackers already know half of your login credentials. Create a new admin account with a unique username and delete the "admin" account.
```

### Recommended (Threat Level 50)
**Condition:** User with username "admin" exists BUT has lower role (editor, author, etc.)  
**Site Health:** `recommended`  
**Message:**
```
You have a user account with the username "admin", though it's not an administrator. While less critical, this still provides attackers with a known username to target. Consider renaming or deleting this account.
```

### Good (No Finding)
**Condition:** No users with username "admin" (or common variations)  
**Returns:** `null` (no finding)

---

## Implementation Details

### File Location
```
includes/diagnostics/tests/security/class-diagnostic-admin-username.php
```

### Detection Method
```php
// Check for "admin" username
$admin_user = get_user_by( 'login', 'admin' );

if ( ! $admin_user ) {
    return null; // No admin user found
}

// Check role
if ( in_array( 'administrator', (array) $admin_user->roles, true ) ) {
    // Critical: Admin with full privileges
    return $this->create_finding( /* threat_level: 75 */ );
}

// Recommended: Lower-privileged admin username
return $this->create_finding( /* threat_level: 50 */ );
```

### Username Variations to Check
- `admin` (primary)
- `administrator` (secondary)
- `Admin` (case variation)
- `ADMIN` (case variation)

**Note:** WordPress usernames are case-insensitive, so `get_user_by( 'login', 'admin' )` will match all case variations.

### Related Treatment
**Treatment Slug:** None  
**Auto-Fixable:** No  
**Reason:** Cannot safely automate user account changes (risk of locking out legitimate admin)

**Manual Steps Required:**
1. Create new admin account with unique username
2. Log in as new admin
3. Delete old "admin" account
4. Attribute old content to new admin (or other user)

---

## Required Data Points

### User Data to Check
- Username (via `get_user_by( 'login', 'admin' )`)
- User roles (via `$user->roles`)
- User ID (for potential future treatment coordination)

### Additional Context
- Count of administrator accounts (warn if only one admin and it's "admin")
- Last login date (if available via plugin) to gauge active use

---

## Messages (Plain Language)

### Title
```
Default "Admin" Username Detected
```

### Short Description
```
Your site has a user account named "admin", which is a primary target for brute force attacks.
```

### Long Description (Critical)
```
You have an administrator account with the username "admin". This is one of the most common security mistakes on WordPress sites.

**Why this matters:**

• Attackers know that many sites use "admin" as the username
• This gives them 50% of your login credentials for free
• Automated bots constantly try "admin" + common passwords
• Your site receives thousands of brute force attempts monthly

**Real-world impact:**

• Makes brute force attacks 100-1000x easier
• Increases server load from constant login attempts
• Can lead to account lockouts from security plugins
• If password is weak, account will be compromised

**Important:** Do NOT simply rename the admin account. Create a new account and properly delete the old one to ensure database references are updated.
```

### Long Description (Recommended)
```
You have a user account with the username "admin", though it doesn't have administrator privileges.

While less critical than an admin account, this still:

• Provides attackers with a known username to target
• Wastes server resources on failed login attempts
• May confuse legitimate users about account ownership

Consider renaming or deleting this account for better security.
```

### What to Do About It
```
**Recommended Action:** Create a new admin account and delete the "admin" user:

1. **Users → Add New** in WordPress admin
2. Create new user with:
   - Unique username (not "admin", "administrator", or your domain name)
   - Strong password (16+ characters, mixed case, numbers, symbols)
   - Role: Administrator
3. Log out and log back in as the new admin
4. **Users → All Users**, find "admin" account
5. Click **Delete**, choose "Attribute all content to:" your new admin
6. Confirm deletion

**Important:** Make sure you're logged in as the new admin before deleting the old "admin" account to avoid locking yourself out.

WPShadow cannot auto-fix this for security reasons (we never modify user accounts automatically).
```

---

## KB Article

**URL:** `https://wpshadow.com/kb/security-admin-username`

**Content Outline:**
1. Why "admin" username is dangerous
2. How brute force attacks work
3. Statistics: How many sites use "admin"
4. Step-by-step guide to creating new admin user
5. How to safely delete the old admin account
6. What to choose for a secure username
7. Additional security: 2FA, login limits, captcha
8. Related: Strong password requirements

---

## Site Health Bridge Mapping

| Threat Level | Site Health Status | Badge Color |
|--------------|-------------------|-------------|
| 75 (Critical) | `critical` | Red |
| 50 (Recommended) | `recommended` | Orange |
| 0 (Good) | `good` | Green |

---

## Implementation Checklist

### Code Requirements
- [ ] File created: `includes/diagnostics/tests/security/class-diagnostic-admin-username.php`
- [ ] Extends `Diagnostic_Base`
- [ ] Namespace: `WPShadow\Diagnostics`
- [ ] Protected properties: `$slug`, `$title`, `$description`, `$category`, `$kb_url`
- [ ] `check()` method returns `array|null`
- [ ] Return format includes: `id`, `title`, `description`, `severity`, `threat_level`, `site_health_status`, `category`, `kb_url`, `auto_fixable` (false)
- [ ] Test type: `direct` (user query, < 0.1s)

### Message Quality
- [ ] Title is clear and actionable (Grade 8 reading level)
- [ ] Short description explains the issue in one sentence
- [ ] Long description explains WHY it matters with real-world impact
- [ ] Includes specific statistics where helpful
- [ ] No technical jargon without explanation
- [ ] "What to Do" section provides step-by-step instructions
- [ ] Warnings about account lockout included
- [ ] All strings use `__()` for i18n with 'wpshadow' text domain

### Threat Level Logic
- [ ] Critical (75): "admin" user has administrator role
- [ ] Recommended (50): "admin" user exists but lower role
- [ ] Good (0): No "admin" username found
- [ ] Returns `null` when no issue found
- [ ] Threat level matches severity of actual risk

### Site Health Integration
- [ ] Threat level 75 → `site_health_status` = 'critical'
- [ ] Threat level 50 → `site_health_status` = 'recommended'
- [ ] Uses threshold constants: `WPSHADOW_SEVERITY_CRITICAL_THRESHOLD` (75), `WPSHADOW_SEVERITY_RECOMMENDED_THRESHOLD` (50)

### Category & Metadata
- [ ] Category: `security` (matches dashboard gauge)
- [ ] KB URL follows format: `https://wpshadow.com/kb/security-admin-username`
- [ ] Auto-fixable: `false` (requires manual user changes)
- [ ] Treatment slug: N/A (no treatment available)

### Documentation
- [ ] Class-level PHPDoc explaining purpose, impact, conditions
- [ ] Method-level PHPDoc for `check()` with `@return array|null`
- [ ] Inline comments explaining why not auto-fixable
- [ ] Security rationale documented

### Testing
- [ ] PHP syntax validated: `php -l class-diagnostic-admin-username.php`
- [ ] PHPCS passes (WordPress coding standards)
- [ ] Tested with "admin" user (administrator role) - should return critical finding
- [ ] Tested with "admin" user (lower role) - should return recommended finding
- [ ] Tested without "admin" user - should return null
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

---

**Issue Created:** 2026-01-26  
**Specification Version:** 1.0  
**Estimated Implementation Time:** 2-3 hours
