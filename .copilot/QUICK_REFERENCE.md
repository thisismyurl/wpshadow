# WPShadow Agent Quick Reference Card

**Last Updated:** January 24, 2026  
**Purpose:** Quick lookup for agent-assisted development

---

## 🎯 The 11 Commandments (Quick Check)

| # | Commandment | Check | Example |
|---|-------------|-------|---------|
| 1 | **Helpful Neighbor** | Does this anticipate user needs? | Guardian System proactively protects |
| 2 | **Free as Possible** | Is core functionality free? | Diagnostics & scanning always free |
| 3 | **Register Not Pay** | Does this work locally without payment? | All local features work free |
| 4 | **Advice Not Sales** | Is this educational, not pushy? | Recommendations based on health |
| 5 | **Drive to KB** | Are KB articles linked? | Every recommendation links to help |
| 6 | **Drive to Training** | Are training videos available? | Links to video tutorials |
| 7 | **Ridiculously Good** | Would paying customers feel premium? | UX exceeds paid plugin quality |
| 8 | **Inspire Confidence** | Is this intuitive for non-technical users? | Clear explanations in plain language |
| 9 | **Show Value (KPIs)** | Can users see the measurable impact? | Displays business value metrics |
| 10 | **Beyond Pure Privacy** | Is this consent-first and transparent? | Users control all data collection |
| 11 | **Talk-Worthy** | Would users share this feature? | Features create "wow" moments |

---

## 🔒 WordPress.org Essential Checklist

### Plugin Header (Required)
```php
<?php
/**
 * Plugin Name: WPShadow
 * Description: Short description
 * Version: 1.2601.2148
 * Author: thisismyurl
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpshadow
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */
```

### Security Essentials

**Input:**
```php
sanitize_text_field()    // Plain text
wp_kses_post()           // HTML content
sanitize_email()         // Email
esc_url()                // URL
sanitize_key()           // Options/keys
```

**Output:**
```php
esc_html()       // Plain text
wp_kses_post()   // HTML
esc_url()        // URLs
esc_attr()       // HTML attributes
esc_js()         // JavaScript
```

**Verify:**
```php
check_admin_referer('action', 'field')  // Nonce
current_user_can('capability')          // Capability
$wpdb->prepare()                        // SQL
```

### File Structure
```
includes/core/           # Base classes
includes/diagnostics/    # 648 Diagnostics
includes/treatments/     # Remediation
includes/admin/          # Admin pages
assets/css/              # Stylesheets
assets/js/               # JavaScript
languages/               # Translations
```

---

## 💻 Naming Conventions

| Type | Pattern | Example |
|------|---------|---------|
| Constants | `SCREAMING_SNAKE_CASE` | `WPSHADOW_VERSION` |
| Global Functions | `wpshadow_verb_noun()` | `wpshadow_init_admin()` |
| AJAX Functions | `WPSHADOW_verb_noun()` | `WPSHADOW_ajax_scan()` |
| Classes | `Noun_Style` namespaced | `WPShadow\Diagnostics\Diagnostic_Example` |
| Files | `class-{name}.php` | `class-diagnostic-example.php` |
| Hooks (Actions) | `wpshadow_*` | `wpshadow_diagnostic_executed` |
| Hooks (Filters) | `wpshadow_*` | `wpshadow_diagnostic_result` |

---

## 📋 Diagnostic Template

```php
namespace WPShadow\Diagnostics;

class Diagnostic_Example extends \WPShadow\Core\Diagnostic_Base {
    const ID          = 'category-diagnostic-name';
    const NAME        = 'User-Friendly Name';
    const DESCRIPTION = 'What this checks';
    const CATEGORY    = 'security'; // security|performance|seo|health|backup|compatibility|compliance
    
    public function execute(): array {
        try {
            $is_healthy = $this->check_condition();
            
            return [
                'status'  => $is_healthy ? 'pass' : 'fail',
                'message' => 'What the user needs to know',
                'details' => 'Technical details for experts',
                'kpi'     => 'Business impact if applicable',
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }
}
```

**File:** `includes/diagnostics/class-diagnostic-example.php`

---

## 🧪 Pre-Commit Checklist

### Code Quality
- [ ] No var_dump(), dd(), console.log()
- [ ] All functions have docblocks
- [ ] Variable names are descriptive
- [ ] DRY principle applied
- [ ] No magic strings (use constants)

### WordPress.org
- [ ] Input validated: `sanitize_*`
- [ ] Output escaped: `esc_*`
- [ ] Capabilities checked
- [ ] Nonces used on forms/AJAX
- [ ] No direct file access issues
- [ ] Text domain used everywhere

### Security
- [ ] SQL prepared statements
- [ ] No eval() or create_function()
- [ ] File uploads validated
- [ ] No credentials in code
- [ ] CSRF protection via nonces

### Performance
- [ ] Queries optimized
- [ ] Transients used for expensive ops
- [ ] No queries in loops
- [ ] Assets conditionally loaded

---

## 📝 Writing Summary Documents

**When:** After completing features, phases, or major work

**Where:** 
```
docs/[FEATURE]_IMPLEMENTATION_COMPLETE.md
docs/PHASE_[N]_COMPLETION_SUMMARY.md
```

**What to Include:**
```markdown
# Feature Name - Implementation Summary

**Status:** Completed  
**Date:** January 24, 2026  
**Lines:** XXX new, YYY modified  

## What Was Completed
- Feature 1: [description]
- Feature 2: [description]

## Files Changed
- file.php - XX lines (Created)
- file2.php - YY lines (Modified)

## Key Decisions
1. [Decision] - [Why] → [Result]

## Philosophy Alignment
✅ Commandment #X: [How it aligns]

## Testing Status
- [x] Unit tests
- [ ] Integration tests

## Next Steps
- Step 1
- Step 2
```

---

## 🚨 Common Mistakes to Avoid

| Mistake | Problem | Solution |
|---------|---------|----------|
| Unescaped output | XSS vulnerability | Use `esc_html()`, `esc_attr()`, etc |
| Unvalidated input | Data corruption | Use `sanitize_*()` functions |
| Missing nonces | CSRF vulnerability | Add `wp_nonce_field()` + verify |
| No capability check | Unauthorized access | Check `current_user_can()` |
| Queries in loops | Performance issue | Batch queries or use transients |
| Hardcoded strings | Not translatable | Use `__()` with text domain |
| Direct file access | Security issue | Check `if (!defined('ABSPATH'))` |
| Deleting on deactivation | User loses data | Only delete on `uninstall_hook` |
| Magic numbers | Unclear intent | Define as constants |
| No error handling | Silent failures | Try/catch with user feedback |

---

## 🔗 One-Minute Links

**Core Philosophy:**
- 11 Commandments: `/docs/archive/KILLER_TESTS_50_MUST_HAVES.md`
- Agent Preferences: `/docs/WPSHADOW_AGENT_PREFERENCES.md`

**Coding Standards:**
- WPShadow Standards: `/docs/CODING_STANDARDS.md`
- WordPress.org: https://developer.wordpress.org/plugins/

**Quality & Release:**
- Testing Guide: `/docs/PRERELEASE_TESTING_GUIDE.md`
- Release Checklist: `/docs/RELEASE_CHECKLIST.md`

**Diagnostics:**
- 648 diagnostics located: `/includes/diagnostics/`
- Each follows pattern: `class-diagnostic-{name}.php`

---

## ✅ Quality Tiers

**Tier 1: Acceptable** 
- [ ] Runs without fatal errors
- [ ] Follows WordPress.org standards
- [ ] Basic security implemented

**Tier 2: Good**
- [x] Above + comprehensive documentation
- [x] Above + performance optimized
- [x] Above + mobile responsive

**Tier 3: Great (WPShadow Target)**
- [x] Above + exceeds premium plugin quality
- [x] Above + guides users effectively
- [x] Above + inspires confidence
- [x] Above + demonstrates clear KPI value

---

## 🎯 Before Opening PR

```bash
# Syntax check
php -l includes/**/*.php

# WordPress standards
composer phpcs

# Static analysis
composer phpstan

# Manual test
# 1. Load in WordPress 5.0 - 6.4+
# 2. Test on PHP 7.4 - 8.3
# 3. Check debug.log for errors
# 4. Verify mobile responsiveness
# 5. Test accessibility (keyboard, screen reader)
```

---

## 💡 Key Principle

> **"Would a trusted neighbor do this?"**

If the answer is no, reconsider the approach. Every line of code should reflect WPShadow's commitment to being a genuinely helpful, trustworthy tool.

---

**Version:** 1.0.0  
**For:** AI Agents assisting WPShadow development  
**Updated:** January 24, 2026
