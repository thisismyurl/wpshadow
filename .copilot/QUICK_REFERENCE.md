# WPShadow Agent Quick Reference Card

**Last Updated:** January 24, 2026  
**Purpose:** Quick lookup for agent-assisted development

---

## � THE 3 FOUNDATIONAL PILLARS (CANON)

### Quick Reference: Core Principles

| Pillar | Check This | Red Flags | Examples |
|--------|-----------|-----------|----------|
| **🌍 Accessibility First** | Can someone with disabilities use this? | Mouse-only, color-only, no captions, hardcoded fonts | Keyboard nav, screen reader, high contrast, 44px targets |
| **🎓 Learning Inclusive** | Do all learning styles work? | Only video OR only text, no examples, assumes prior knowledge | Video + article, interactive demo, real-world example |
| **🌐 Culturally Respectful** | Is this welcoming globally? | Idioms, hardcoded dates, gendered language, no translations | RTL support, flexible formats, inclusive imagery |

### Accessibility First: Quick Check

**🔴 RED FLAGS (Don't Ship):**
- ❌ Feature requires mouse click (no keyboard alternative)
- ❌ No alt text on images
- ❌ Color-only information (red for error, green for success)
- ❌ Small buttons/text unreadable at 200% zoom
- ❌ Animation without pause option (seizure risk)
- ❌ No captions on video
- ❌ Time limits without user control
- ❌ Screen reader can't access content

**🟡 YELLOW FLAGS (Plan Remediation):**
- ⚠️ Complex tables without headers
- ⚠️ Error messages aren't clear
- ⚠️ Focus indicator hard to see
- ⚠️ Works but slow on low bandwidth
- ⚠️ Requires mouse + keyboard to be useful

**🟢 GREEN CHECKS (Ship Confidently):**
- ✅ Keyboard navigation works
- ✅ Screen reader can read all content
- ✅ Colors tested (WCAG AA minimum)
- ✅ Text readable at 200% zoom
- ✅ Motion respects prefers-reduced-motion
- ✅ Captions/transcripts included
- ✅ No time limits on interactions
- ✅ Works on 1 Mbps connection

### Learning Inclusive: Quick Check

**Documentation Checklist:**
- [ ] Written guide (searchable, readable)
- [ ] Video tutorial (slow pace, clear narration)
- [ ] Interactive example (users can play with it)
- [ ] Step-by-step screenshots
- [ ] Real-world use case
- [ ] For technical users AND non-technical users

**Content Format Check:**
| Need | Provide |
|------|---------|
| Visual learners | Diagrams, screenshots, icons |
| Auditory learners | Video, podcast, narration |
| Reading/writing learners | Articles, guides, examples |
| Kinesthetic learners | Interactive demo, hands-on practice |

### Culturally Respectful: Quick Check

| Consideration | Check | Examples |
|---|---|---|
| **Language** | Plain English, no idioms | ❌ "Break a leg" ✅ "Good luck" |
| **Dates** | Flexible formats | ❌ Hardcoded 12/25/2026 ✅ User's locale |
| **Numbers** | Locale-aware | ❌ Always "1,000.50" ✅ Respect localization |
| **Translation** | Planned or supported | ❌ English only ✅ Translatable strings |
| **RTL Support** | For Arabic, Hebrew, etc | ❌ Hardcoded left-align ✅ Flexible layout |
| **Names** | Support diverse formats | ❌ "First + Last" only ✅ Supports any format |
| **Imagery** | Diverse representation | ❌ Only white faces ✅ Diverse people & disabilities |
| **Assumptions** | None about culture | ❌ "Everyone celebrates Christmas" ✅ Religion-neutral |

---

## �🎯 The 11 Commandments (Quick Check)

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

### 🌍 Accessibility (MUST HAVE)
- [ ] Keyboard navigation works (no mouse required)
- [ ] Screen reader can read all content
- [ ] Text readable at 200% zoom
- [ ] Color contrast WCAG AA minimum
- [ ] Images have alt text (or `alt=""` if decorative)
- [ ] Motion respects prefers-reduced-motion
- [ ] Buttons/targets 44x44px minimum
- [ ] No time limits on interactions
- [ ] Error messages are clear & helpful
- [ ] Focus indicator always visible

### 🎓 Learning (MUST HAVE)
- [ ] Documentation exists in text & video format
- [ ] Feature has real-world usage example
- [ ] Non-technical users can understand it
- [ ] Screenshots provided for visual learners
- [ ] Step-by-step instructions for kinesthetic learners
- [ ] Terminology explained for first-time users

### 🌐 Cultural (MUST HAVE)
- [ ] Uses simple, clear language (no idioms)
- [ ] No hardcoded date/number/currency formats
- [ ] Support for RTL languages (if applicable)
- [ ] Imagery is diverse and respectful
- [ ] No assumptions about user's culture/location
- [ ] Translations planned or strings marked translatable

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

**NOTE:** If any section has unchecked boxes, feature needs remediation before shipping.

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

### Accessibility Mistakes (CANON - Don't Ship)

| Mistake | Problem | Solution |
|---------|---------|----------|
| Mouse-only interaction | Excludes motor-disabled users | Keyboard: Space/Enter for buttons, Tab for nav |
| Missing alt text | Screen readers can't read images | Always add: `alt="description of image"` |
| Color-only info | Colorblind users miss information | Add text: "❌ Error" or "✅ Success" |
| Small buttons (< 44px) | Hard to tap on mobile, impossible for tremors | Make buttons 44x44px minimum |
| No focus indicator | Keyboard users lost | Use `:focus { outline: 2px solid color; }` |
| Animations can't stop | Flashing causes seizures, motion-sick users | Respect: `prefers-reduced-motion` CSS |
| No captions on video | Deaf users can't watch | Add captions/transcripts |
| Hardcoded zoom level | Breaks at 200% zoom | Use relative sizing: `rem`, `em`, `%` |
| Time limits | Users with cognition issues need more time | Remove auto-timeouts or add "extend" button |
| No skip navigation | Screen readers waste time on headers | Add: `<a href="#main">Skip to content</a>` |

### Learning Mistakes (CANON - Plan Remediation)

| Mistake | Problem | Solution |
|---------|---------|----------|
| Only video, no text | Deaf users, slow connection users stuck | Provide transcripts & written guides |
| Only text, no video | Visual/kinesthetic learners confused | Create video walkthrough |
| No real examples | Users don't know how to actually use it | Show step-by-step real-world usage |
| Jargon without explanation | Non-technical users lost | Explain in simple language first |
| No screenshots | Complex features hard to follow | Show visual steps for each action |
| Wall of text | Overwhelming | Break into sections, use headings, lists |

### Cultural Mistakes (CANON - Plan Remediation)

| Mistake | Problem | Solution |
|---------|---------|----------|
| Hardcoded dates (12/25/2026) | Wrong format in other countries | Use `date_i18n()` or locale-aware formats |
| Only number format "1,000.50" | Wrong in countries using "1.000,50" | Use locale-aware formatting |
| Hardcoded currency "$" | Wrong symbol in other countries | Show user's local currency |
| Idioms: "break a leg", "piece of cake" | Confusing for non-native English speakers | Use plain: "good luck", "easy" |
| Gendered language "he/his" | Excludes non-binary people | Use "they/their" or avoid pronouns |
| No translation available | Non-English speakers excluded | Use translation tags: `__()`, `_e()` |
| English-only error messages | Users can't understand errors | Support multiple languages or use universal icons |
| No RTL support | Arabic/Hebrew speakers see backwards text | Test with RTL languages, use flexbox |
| Assumes Christian calendar | Non-Christian users confused by dates | Support multiple calendar systems |
| Only Western names | Diverse users feel excluded | Test with: hyphenated, compound, non-Latin names |

### Code Mistakes (Classic)

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
