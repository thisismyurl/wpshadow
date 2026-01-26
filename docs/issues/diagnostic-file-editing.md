# Diagnostic: File Editing Enabled

**Labels:** `diagnostic`, `tier-1`, `security`, `auto-fixable`  
**Milestone:** Diagnostic System Rebuild  
**Priority:** Critical (Tier 1)

---

## Overview

**Slug:** `file-editing`  
**Category:** Security  
**Threat Level:** 70 (High)  
**Auto-Fixable:** Yes  
**Test Type:** Direct (< 0.1s)

### What to Check
Detect if the built-in WordPress theme and plugin file editors are enabled in the admin panel, which allows code injection if an admin account is compromised.

### Why It Matters
- **Code Injection:** Compromised admin can inject malicious code directly through browser
- **No Audit Trail:** File edits through WordPress leave no server-level logs
- **One-Click Backdoor:** Attacker can add backdoor code to any theme/plugin file
- **Privilege Escalation:** Even limited admin access becomes full server control
- **Best Practice:** Professional sites should use version control and SFTP, not browser editing

---

## Diagnostic Thresholds

### High (Threat Level 70)
**Condition:** `DISALLOW_FILE_EDIT` constant is not defined OR is set to `false`  
**Site Health:** `recommended`  
**Message:**
```
WordPress theme and plugin file editors are enabled in your admin panel. If an administrator account is compromised, attackers can inject malicious code directly into your site's files through the browser. Disable file editing for better security.
```

### Good (No Finding)
**Condition:** `DISALLOW_FILE_EDIT` is defined and set to `true`  
**Returns:** `null` (no finding)

---

## Implementation Details

### File Location
```
includes/diagnostics/tests/security/class-diagnostic-file-editing.php
```

### Detection Method
```php
// Check if DISALLOW_FILE_EDIT is defined and true
if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT === true ) {
    return null; // File editing is disabled (good)
}

// File editing is enabled (security risk)
return $this->create_finding(
    /* threat_level: 70 */
);
```

### Related Treatment
**Treatment Slug:** `file-editing`  
**Treatment File:** `includes/treatments/class-treatment-file-editing.php`  
**Action:** Add `define( 'DISALLOW_FILE_EDIT', true );` to wp-config.php

**Process:**
1. Backup wp-config.php
2. Read current wp-config.php content
3. Add line after `<?php` or before `/* That's all, stop editing! */`
4. Write updated content
5. Verify constant is now defined

**Undo:** Remove the added line and restore from backup

---

## Required Data Points

### Constant to Check
- `DISALLOW_FILE_EDIT` - Disables theme/plugin editor in admin

**Note:** There is also `DISALLOW_FILE_MODS` which disables plugin/theme installation AND editing. This is a stronger restriction but may be too restrictive for most sites. This diagnostic only checks `DISALLOW_FILE_EDIT`.

### Additional Context
- User capability check: `edit_themes` and `edit_plugins` (these capabilities are removed when file editing is disabled)
- Count of recent file modifications (if detectable) to gauge usage
- Hosting environment (managed WordPress hosts often disable this automatically)

---

## Messages (Plain Language)

### Title
```
Theme and Plugin File Editors Are Enabled
```

### Short Description
```
WordPress file editors allow code editing through the browser, which creates a security risk if an admin account is compromised.
```

### Long Description
```
Your WordPress admin panel includes built-in file editors for themes and plugins (Appearance → Theme File Editor and Plugins → Plugin File Editor). While convenient for quick edits, these editors create a significant security risk.

**Why this matters:**

If an attacker gains access to any administrator account—through phishing, password guessing, or session hijacking—they can:

• Inject malicious code directly into your site's PHP files
• Create backdoors that persist even after changing passwords
• Steal customer data or inject spam content
• Redirect visitors to malware sites
• Deface your site or take it offline completely

**Real-world scenario:**

1. Attacker compromises an admin account (weak password, phishing email)
2. Logs into WordPress admin
3. Goes to Appearance → Theme File Editor
4. Opens functions.php
5. Adds backdoor code (3 lines) to maintain access forever
6. Even after changing password, attacker still has access

**Professional best practice:**

Production sites should use:
• Version control (Git) for code changes
• SFTP/SSH for file editing
• Staging environment for testing
• Code review process

Browser-based file editing is convenient but dangerous. Disabling it removes a major attack vector without impacting normal site operation.
```

### What to Do About It
```
**Recommended Action:** Disable file editing

Click "Apply Treatment" to automatically add this line to your wp-config.php file:
```php
define( 'DISALLOW_FILE_EDIT', true );
```

This will:
• Remove "Theme File Editor" from Appearance menu
• Remove "Plugin File Editor" from Plugins menu
• Prevent file editing through WordPress admin
• NOT affect plugin/theme installation or updates

**Manual Alternative:**

1. Download wp-config.php from your server (via SFTP or hosting file manager)
2. Open it in a text editor
3. Find this line: `/* That's all, stop editing! Happy publishing. */`
4. Add this line BEFORE it:
   `define( 'DISALLOW_FILE_EDIT', true );`
5. Save and upload back to server
6. Refresh WordPress admin - editor menu items will be gone

**If you need to edit files later:**
• Use SFTP client (FileZilla, Cyberduck)
• Use hosting control panel file manager
• Use SSH/command line
• Use local development environment

**Can I re-enable it temporarily?**
Yes, but NOT recommended. If you must:
1. Edit wp-config.php
2. Change `true` to `false` or comment out the line
3. Make your edits in WordPress admin
4. Immediately re-disable file editing when done
5. Review all edited files for security issues
```

---

## KB Article

**URL:** `https://wpshadow.com/kb/security-file-editing`

**Content Outline:**
1. What are WordPress file editors?
2. Why file editing is a security risk
3. How attackers exploit file editors
4. Real-world examples of file editor attacks
5. How to disable file editing (step-by-step)
6. How to edit files safely (SFTP, SSH, local dev)
7. Difference between DISALLOW_FILE_EDIT and DISALLOW_FILE_MODS
8. Impact on managed WordPress hosting (often already disabled)
9. How to set up local development environment
10. Best practices for code changes on production sites
11. Video tutorial: Disabling file editors

---

## Site Health Bridge Mapping

| Threat Level | Site Health Status | Badge Color |
|--------------|-------------------|-------------|
| 70 (High) | `recommended` | Orange |
| 0 (Good) | `good` | Green |

---

## Implementation Checklist

### Code Requirements
- [ ] File created: `includes/diagnostics/tests/security/class-diagnostic-file-editing.php`
- [ ] Extends `Diagnostic_Base`
- [ ] Namespace: `WPShadow\Diagnostics`
- [ ] Protected properties: `$slug`, `$title`, `$description`, `$category`, `$kb_url`
- [ ] `check()` method returns `array|null`
- [ ] Return format includes: `id`, `title`, `description`, `severity`, `threat_level`, `site_health_status`, `category`, `kb_url`, `auto_fixable` (true), `treatment_slug`
- [ ] Test type: `direct` (constant check, < 0.01s)

### Message Quality
- [ ] Title is clear and actionable (Grade 8 reading level)
- [ ] Short description explains the issue in one sentence
- [ ] Long description explains WHY with attack scenario
- [ ] Includes real-world example of exploitation
- [ ] No technical jargon without explanation
- [ ] "What to Do" provides auto-fix option and manual steps
- [ ] Alternatives to file editing explained (SFTP, SSH, local dev)
- [ ] Code example for wp-config.php edit included
- [ ] All strings use `__()` for i18n with 'wpshadow' text domain

### Threat Level Logic
- [ ] High (70): DISALLOW_FILE_EDIT not defined or false
- [ ] Good (0): DISALLOW_FILE_EDIT defined and true
- [ ] Returns `null` when no issue found
- [ ] Threat level appropriate (high but not critical - requires compromised admin)

### Site Health Integration
- [ ] Threat level 70 → `site_health_status` = 'recommended' (below 75 threshold)
- [ ] Uses threshold constant: `WPSHADOW_SEVERITY_RECOMMENDED_THRESHOLD` (50)
- [ ] Properly categorized as security recommendation

### Category & Metadata
- [ ] Category: `security` (matches dashboard gauge)
- [ ] KB URL follows format: `https://wpshadow.com/kb/security-file-editing`
- [ ] Auto-fixable: `true` (can add constant to wp-config.php)
- [ ] Treatment slug: `file-editing`

### Documentation
- [ ] Class-level PHPDoc explaining purpose, impact, conditions
- [ ] Method-level PHPDoc for `check()` with `@return array|null`
- [ ] Inline comments explaining constant check
- [ ] Note about DISALLOW_FILE_MODS difference

### Testing
- [ ] PHP syntax validated: `php -l class-diagnostic-file-editing.php`
- [ ] PHPCS passes (WordPress coding standards)
- [ ] Tested without DISALLOW_FILE_EDIT - should return finding
- [ ] Tested with DISALLOW_FILE_EDIT = false - should return finding
- [ ] Tested with DISALLOW_FILE_EDIT = true - should return null
- [ ] Tested on wpshadow.com (document result: pass/fail)
- [ ] Verify editors disappear after treatment applied

### No Stubs Policy
- [ ] No TODO comments
- [ ] No placeholder functions
- [ ] No commented-out code blocks
- [ ] All logic fully implemented
- [ ] Treatment implementation planned and documented

---

## Related Documentation
- [DIAGNOSTIC_AND_TREATMENT_SPECIFICATION.md](../DIAGNOSTIC_AND_TREATMENT_SPECIFICATION.md)
- [DIAGNOSTICS_IMPLEMENTATION_TRACKER.md](../DIAGNOSTICS_IMPLEMENTATION_TRACKER.md)
- [ARCHITECTURE.md](../ARCHITECTURE.md)
- WordPress Codex: [Editing wp-config.php](https://wordpress.org/support/article/editing-wp-config-php/)

---

**Issue Created:** 2026-01-26  
**Specification Version:** 1.0  
**Estimated Implementation Time:** 2-3 hours
