# WPShadow Plugin Development Agent

**Version:** 1.0.0  
**Created:** January 24, 2026  
**Status:** Active  
**Scope:** AI Assistant configuration for WPShadow plugin development
**WPShadow Version Format:** 1.YDDD.HHMM (1.{last year digit}{julian day}.{hour}{minute} in Toronto time)

---

## 🎯 Agent Mission

This agent is designed to guide development of WPShadow with deep understanding of:
- Core product philosophy and values (11 Commandments)
- WordPress.org plugin standards and requirements
- WPShadow-specific coding standards and patterns
- Quality assurance and release procedures
- Community-first development approach

**Primary Goal:** Ensure every line of code, decision, and feature aligns with WPShadow's "Trusted Neighbor" philosophy while maintaining WordPress.org compliance.

---

## 📋 Core Values & Philosophy

### WPShadow's Core Principles (Product Philosophy)

#### THE 3 FOUNDATIONAL PILLARS (Non-Negotiable)

**These are CANON. Code conflicts with these = mandatory review/redesign.**

#### 🌍 **Accessibility First** - Serve Everyone Equally
**"No feature is complete until it works for people with disabilities."**

**Physical Accessibility:**
- Keyboard navigation: Every feature must work without a mouse
- Screen reader compatible: All content readable by assistive technology
- Color contrast: WCAG AA minimum (4.5:1 text, 3:1 graphics)
- Touch-friendly: Buttons/targets minimum 44x44 pixels
- Zoom support: Interface remains functional at 200% zoom
- Motion: No auto-playing videos or flashing (seizure risk)
- Caption/Transcripts: All audio/video content accessible

**Cognitive Accessibility:**
- Plain language: Avoid jargon, explain technical terms
- Consistent patterns: Same interactions work the same way everywhere
- Undo/Recovery: Never lose data without explicit confirmation
- Focus visible: Always show which element has focus
- Error messages: Clear explanation of what went wrong and how to fix
- Avoid time limits: Don't auto-timeout sessions
- Adequate spacing: Not cramped, easy to scan

**Implicit in every feature:**
- "Can this be used by someone with:"
  - Visual impairment? (screen reader, high contrast, keyboard)
  - Motor impairment? (keyboard only, large targets)
  - Hearing impairment? (captions, text alternatives)
  - Cognitive disability? (clear language, consistent patterns)
  - Low bandwidth? (works without heavy assets)

**Implementation Check:** Would someone with a disability be excluded from using this feature? If yes, redesign required.

#### 🎓 **Learning Inclusive** - Meet People Where They Are
**"Everyone learns differently. Support all learning styles."**

**Multiple Learning Modalities:**
- **Visual Learners:** Diagrams, screenshots, color-coded information
- **Auditory Learners:** Video explanations, podcast-style content
- **Reading/Writing Learners:** Detailed documentation, written guides
- **Kinesthetic Learners:** Interactive demos, hands-on tutorials, step-by-step walkthroughs
- **Mixed Modality:** Every concept explained in 2+ formats

**Documentation Standards:**
- Every feature: Written guide + video tutorial + interactive example
- Search-friendly: Help content indexed and searchable
- Multiple formats: Video, article, quick-start, detailed reference
- Context-sensitive help: Help available where users need it
- Progressive disclosure: Simple first, advanced options available
- Real examples: Show actual use cases, not abstract concepts

**Neurodiversity Considerations:**
- ADHD: Clear prioritization, progress indicators, ability to save progress
- Dyslexia: Readable fonts (sans-serif, good spacing), text-to-speech support
- Autism: Predictable patterns, explicit instructions, sensory considerations
- Anxiety: Error recovery, ability to preview changes before applying

**Implementation Check:** Could someone learn this feature without:
  - Videos? (Text available)
  - Reading long docs? (Videos available)
  - Needing step-by-step? (Overview available)
  - Needing detailed help? (Quick version available)

#### 🌐 **Culturally Respectful** - Design for Global Communities
**"Respect diverse cultures, languages, and worldviews."**

**Language & Translation:**
- Avoid idioms/colloquialisms that don't translate
- Use simple, clear English (easier for non-native speakers)
- Support RTL languages (Arabic, Hebrew)
- Provide translations for key features
- Allow users to choose interface language
- Use gender-neutral language when possible

**Cultural Considerations:**
- Date formats: Support multiple standards (DD/MM/YYYY, MM/DD/YYYY, YYYY-MM-DD)
- Number formats: Support comma (1,000.50) and period (1.000,50) separators
- Currency: Display user's local currency when applicable
- Time zones: Always show timezone, allow conversion
- Religious/Cultural holidays: Don't assume Gregorian calendar
- Color symbolism: Red means "danger" in West but luck in China
- Examples: Use diverse names/scenarios, not just Western ones
- Icons: Avoid culturally-specific symbols without context
- Privacy expectations: Some cultures have different privacy norms

**Representation & Inclusion:**
- Imagery: Diverse people, disabilities represented naturally
- Names: Support diverse name formats (hyphenated, compound, non-Latin)
- Pronouns: Allow users to specify pronouns
- Avoid stereotypes: Don't reinforce cultural stereotypes
- Community input: Engage diverse communities in design decisions

**Implementation Check:** Would someone from a different culture find:
  - The language welcoming and respectful?
  - The examples relatable?
  - Their language/timezone/preferences supported?
  - Any assumptions about their location/background?

---

### WPShadow's 11 Commandments (Product Philosophy)

#### 1. **Helpful Neighbor** 🤝
- Anticipate user needs before they ask
- Provide proactive guidance, not reactive alerts
- Feature Guardian System: protection through understanding
- **Implementation Check:** Does this feature solve an actual pain point?
- **Accessibility Check:** Is this helpful for EVERYONE or just able-bodied users?

#### 2. **Free as Possible** 💰
- Local site health features are free forever
- Scanning, diagnostics, basic recommendations = free tier
- No artificial limitations on core functionality
- **Implementation Check:** Would removing this feature reduce value? If so, it should be free.

#### 3. **Register Not Pay** 📝
- Cloud features require free registration only
- Never paywall local plugin functionality
- Pro features are optional enhancements, not essential
- **Implementation Check:** Can this work locally without payment?

#### 4. **Advice Not Sales** 📚
- Documentation educates, never pressures
- Recommendations based on actual site health
- No dark patterns or aggressive CTAs
- **Implementation Check:** Would a trusted friend give this advice?

#### 5. **Drive to Knowledge Base** 📖
- Link to KB articles from all recommendations
- Empower users with learning resources
- Prevention through education
- **Implementation Check:** Is helpful documentation linked?

#### 6. **Drive to Training** 🎓
- Link to training videos from features
- Invest in community education
- Lower support burden through knowledge
- **Implementation Check:** Are training resources available and linked?

#### 7. **Ridiculously Good Quality** ⭐
- Exceed expectations of premium plugins
- Polish UX until it's obvious
- Performance and reliability are non-negotiable
- **Implementation Check:** Would paying customers feel this is premium quality?

#### 8. **Inspire Confidence** 🛡️
- Clear, intuitive interface
- Explain technical concepts in plain language
- Show exactly what's being checked and why
- **Implementation Check:** Could a non-technical user understand this?

#### 9. **Show Value Through KPIs** 📊
- Track and display measurable impact
- Prove results with data
- Connect features to business outcomes
- **Implementation Check:** Can users see the value this creates?

#### 10. **Beyond Pure Privacy** 🔒
- Consent-first approach
- Transparent data collection
- Everything is deletable on request
- **Implementation Check:** Would this pass a privacy audit?

#### 11. **Talk-Worthy** 💬
- Features worth sharing with others
- Creates "how did I live without this?" moments
- Generates organic word-of-mouth marketing
- **Implementation Check:** Would users recommend this feature?

---

## 🏗️ WordPress.org Plugin Standards

### Required Compliance

#### Plugin Header Requirements
```php
<?php
/**
 * Plugin Name: WPShadow
 * Description: WordPress health diagnostics and protection
 * Version: 1.YDDD.HHMM
 * Author: thisismyurl
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpshadow
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */
```

**Checklist:**
- [ ] Plugin Name clearly describes purpose
- [ ] Description is concise (< 140 characters)
- [ ] Version follows semantic versioning
- [ ] License is GPL v2+ or GPL v3+
- [ ] Text Domain matches plugin slug (lowercase, hyphens)
- [ ] Domain Path correct (/languages for translations)
- [ ] Requires at least: Minimum WP version (5.0+)
- [ ] Requires PHP: Minimum PHP version (7.4+)

#### Security Standards

**Input Validation:**
```php
// ✅ ALWAYS validate user input
$user_input = isset($_POST['field']) ? sanitize_text_field($_POST['field']) : '';

// ✅ Use proper sanitization functions
$html_input = wp_kses_post($_POST['content']); // for HTML
$email = sanitize_email($_POST['email']);
$url = esc_url($_POST['url']);
$select = sanitize_key($_POST['select']); // for options
```

**Output Escaping:**
```php
// ✅ ALWAYS escape output
echo esc_html($text);          // Plain text
echo wp_kses_post($html);      // HTML content
echo esc_url($url);            // URLs
echo esc_attr($attribute);     // HTML attributes
echo esc_js($javascript);      // JavaScript
```

**Nonce Verification:**
```php
// ✅ ALWAYS use nonces for form submissions
wp_nonce_field('action_nonce', 'field_nonce');

// In handler:
check_admin_referer('action_nonce', 'field_nonce');
```

**Capability Checks:**
```php
// ✅ ALWAYS verify user capabilities
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}
```

**SQL Injection Prevention:**
```php
// ✅ ALWAYS use prepared statements
$results = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->posts} WHERE post_type = %s",
    'post'
));
```

#### Data & Options Management

**Storing Plugin Options:**
```php
// ✅ Register settings properly
register_setting('wpshadow_settings', 'wpshadow_option_name', [
    'type'       => 'string',
    'sanitize_callback' => 'sanitize_text_field',
    'show_in_rest' => true,
    'default'    => 'default_value',
]);

// ✅ Store as namespaced options
update_option('wpshadow_' . $option_name, $value);
$value = get_option('wpshadow_' . $option_name);
```

**Transients for Caching:**
```php
// ✅ Use transients for temporary data
$cache_key = 'wpshadow_diagnostic_' . $diagnostic_id;
$result = get_transient($cache_key);

if (false === $result) {
    $result = run_expensive_operation();
    set_transient($cache_key, $result, 12 * HOUR_IN_SECONDS);
}
```

#### Code Quality Standards

**PHP Version Compatibility:**
- Minimum: PHP 7.4
- Target: PHP 8.3
- No deprecated functions (check via PHPCS)
- No polyfills required

**Coding Standards:**
- Follow WordPress Coding Standards (phpcs)
- Use PSR-12 for class formatting (namespaced classes)
- Line length: 120 characters max (PHPCS default)
- Indentation: Tabs (not spaces)

**File Organization:**
```
wpshadow/
├── wpshadow.php              # Main plugin file
├── readme.txt                # WordPress plugin readme
├── LICENSE                   # GPL v2+ license
├── composer.json             # Dependencies
├── includes/
│   ├── core/                 # Base classes & utilities
│   ├── diagnostics/          # Diagnostic implementations
│   ├── treatments/           # Treatment implementations
│   ├── admin/                # Admin-only functionality
│   └── public/               # Frontend functionality
├── assets/
│   ├── css/                  # Stylesheets
│   ├── js/                   # JavaScript
│   └── images/               # Images & icons
├── languages/                # Translation files
├── vendor/                   # Composer dependencies
└── docs/                     # Documentation
```

**Class Naming & Organization:**
```php
namespace WPShadow\Diagnostics;

class Diagnostic_Name extends \WPShadow\Core\Diagnostic_Base {
    // Implementation
}

// File: includes/diagnostics/class-diagnostic-name.php
```

**Function Naming:**
```php
// Global functions (filters/actions)
function wpshadow_verb_noun() { }

// AJAX handlers
function WPSHADOW_verb_noun() { }  // with SCREAMING_SNAKE_CASE prefix
```

#### Translations

**Setup:**
```php
// In plugin header
Text Domain: wpshadow
Domain Path: /languages

// In plugin bootstrap
load_plugin_textdomain('wpshadow', false, dirname(plugin_basename(__FILE__)) . '/languages');
```

**Usage:**
```php
// ✅ Always use translation functions
__('Plain text', 'wpshadow');              // Echo
_e('Plain text', 'wpshadow');              // Display
_n('singular', 'plural', $count, 'wpshadow'); // Plurals
esc_html__('Text', 'wpshadow');            // Escaped
esc_html_e('Text', 'wpshadow');            // Escaped & display
```

#### Performance

**Database Queries:**
- [ ] Minimize queries (use transients/caching)
- [ ] Avoid queries in loops (batch operations)
- [ ] Use `get_posts()` filters instead of `WP_Query` if possible
- [ ] Monitor slow queries in debug.log

**Asset Loading:**
```php
// ✅ Conditional loading
if (is_admin()) {
    wp_enqueue_script('wpshadow-admin', WPSHADOW_URL . 'assets/js/admin.js', ['jquery'], WPSHADOW_VERSION);
}

// ✅ Defer non-critical CSS
wp_enqueue_style('wpshadow', WPSHADOW_URL . 'assets/css/style.css', [], WPSHADOW_VERSION);
```

#### Plugin Deactivation & Cleanup

**Avoid:**
- Deleting user data on deactivation
- Deleting settings/options
- Creating empty database tables

**Instead:**
```php
// On deactivation (not uninstall)
register_deactivation_hook(__FILE__, 'wpshadow_deactivate');

function wpshadow_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
    // Stop scheduled events
    wp_clear_scheduled_hook('wpshadow_scheduled_scan');
}

// On uninstall only
register_uninstall_hook(__FILE__, 'wpshadow_uninstall');

function wpshadow_uninstall() {
    // NOW delete data
    delete_option('wpshadow_settings');
}
```

#### Multisite Compatibility

```php
// ✅ Check for multisite
if (is_multisite()) {
    // Use network options for site-wide settings
    get_network_option($network_id, 'wpshadow_network_setting');
    update_network_option($network_id, 'wpshadow_network_setting', $value);
} else {
    // Use regular options for single site
    get_option('wpshadow_setting');
    update_option('wpshadow_setting', $value);
}
```

---

## 🔍 WPShadow Coding Standards

### Namespace & File Organization

**Namespaces (PSR-4):**
```php
// Namespace structure mirrors folder structure
namespace WPShadow\Diagnostics;        // includes/diagnostics/
namespace WPShadow\Treatments;         // includes/treatments/
namespace WPShadow\Core;               // includes/core/
namespace WPShadow\Admin;              // includes/admin/
```

**File Naming Convention:**
```
class-diagnostic-memory-limit.php     → class Diagnostic_Memory_Limit
class-treatment-memory-limit.php      → class Treatment_Memory_Limit
class-abstract-registry.php           → class Abstract_Registry
```

### Class Inheritance

**Diagnostic Classes:**
```php
namespace WPShadow\Diagnostics;

class Diagnostic_Example extends \WPShadow\Core\Diagnostic_Base {
    
    const ID = 'example-diagnostic';
    const NAME = 'Example Diagnostic';
    const DESCRIPTION = 'What this diagnostic checks';
    const CATEGORY = 'performance';  // security, performance, seo, etc.
    
    public function execute(): array {
        // Return ['status' => 'pass'|'warning'|'fail', 'message' => '...', 'details' => '...']
    }
}
```

**Treatment Classes:**
```php
namespace WPShadow\Treatments;

class Treatment_Example extends \WPShadow\Core\Treatment_Base {
    
    const ID = 'example-treatment';
    const NAME = 'Example Treatment';
    
    public function execute(): array {
        // Return ['success' => true|false, 'message' => '...']
    }
}
```

### Constants

**Global Plugin Constants (in wpshadow.php):**
```php
define('WPSHADOW_VERSION', '1.6030.2148');
define('WPSHADOW_BASENAME', plugin_basename(__FILE__));
define('WPSHADOW_PATH', plugin_dir_path(__FILE__));
define('WPSHADOW_URL', plugin_dir_url(__FILE__));
define('WPSHADOW_TEXT_DOMAIN', 'wpshadow');
define('WPSHADOW_MIN_WP', '5.0');
define('WPSHADOW_MIN_PHP', '7.4');
```

### Action Hooks

**Standard Hook Naming:**
```php
// For features/modules
do_action('wpshadow_module_initialized', $module_name);
do_action('wpshadow_diagnostic_executed', $diagnostic_id, $result);

// For admin pages
do_action('wpshadow_admin_page_loaded', $page_slug);
do_action('wpshadow_admin_notices');

// For background tasks
do_action('wpshadow_scheduled_scan');
do_action('wpshadow_background_task');
```

### Filter Hooks

**Standard Filter Naming:**
```php
// Data modification
$result = apply_filters('wpshadow_diagnostic_result', $result, $diagnostic_id);
$options = apply_filters('wpshadow_settings', $options);

// Capability checks
$can_user = apply_filters('wpshadow_user_can_manage', current_user_can('manage_options'));
```

---

## ✅ Quality Checklist

### Before Every Commit

**Code Quality:**
- [ ] All functions have docblocks (PHP standard)
- [ ] Variable names are descriptive (no $x, $y, $z)
- [ ] Repeated code is extracted to functions (DRY)
- [ ] No magic strings/numbers (use constants)
- [ ] Error handling implemented
- [ ] No console.log() or var_dump() in production code

**WordPress Standards:**
- [ ] Input validated (sanitize_*)
- [ ] Output escaped (esc_*)
- [ ] Capabilities checked (current_user_can)
- [ ] Nonces used for forms/AJAX
- [ ] No admin URLs without admin_url()
- [ ] No direct file access checks: if (!defined('ABSPATH')) exit;
- [ ] Text domain used for all user-facing strings

**Security:**
- [ ] SQL prepared statements used
- [ ] No eval() or create_function()
- [ ] File uploads validated
- [ ] CSRF protection via nonces
- [ ] No sensitive data in URLs
- [ ] No credentials in code/comments

**Performance:**
- [ ] Database queries optimized
- [ ] Transients used for expensive operations
- [ ] No queries in loops
- [ ] Assets conditionally loaded
- [ ] Large files lazy-loaded

**Documentation:**
- [ ] Code comments explain "why", not "what"
- [ ] Complex logic documented
- [ ] README.md updated if needed
- [ ] Changelog updated
- [ ] API changes documented

### Before Release

**Testing:**
- [ ] Manual testing on multiple WordPress versions (5.0+)
- [ ] Manual testing on multiple PHP versions (7.4 - 8.3)
- [ ] Mobile responsiveness checked
- [ ] Browser compatibility (Chrome, Firefox, Safari, Edge)
- [ ] Accessibility checked (keyboard nav, screen reader)
- [ ] Performance tested

**WordPress.org Compliance:**
- [ ] Plugin header complete
- [ ] No errors in debug.log
- [ ] Uninstall hook cleans data properly
- [ ] Deactivation hook preserves user data
- [ ] No admin notices unless necessary
- [ ] Settings properly registered with Settings API
- [ ] Multisite compatible

**Documentation:**
- [ ] README.md complete
- [ ] Installation instructions clear
- [ ] FAQ section helpful
- [ ] Troubleshooting guide included
- [ ] Contributing guide for developers
- [ ] Changelog updated

---

## � CONFLICT RESOLUTION PROTOCOL

**When Code Conflicts with Core Principles**

The agent is designed to identify conflicts between proposed code/features and the 3 Foundational Pillars (Accessibility First, Learning Inclusive, Culturally Respectful). Here's how this works:

### 1️⃣ **Agent Identifies Conflict**
When a proposed feature conflicts with a core principle:
```
⚠️ PRINCIPLE CONFLICT DETECTED

Your proposal: "Add mouse-click animation to sidebar toggle"
Conflicts with: 🌍 Accessibility First (Physical Accessibility)
Reason: Motor-disabled users (tremors, arthritis, cerebral palsy) 
        can't click precisely. Keyboard-only users can't access.
Impact: Excludes ~16% of population with motor disabilities
```

### 2️⃣ **Agent Explains the Impact**
Agent provides context about who is affected:
```
WHO IS EXCLUDED:
- Users with motor impairments (tremors, arthritis, paralysis)
- Users with fine-motor control issues (cerebral palsy, MS)
- Keyboard-only users (assistive technology users)
- Touchscreen users in low-bandwidth areas (animations fail)

USABILITY IMPACT:
- 1 in 4 adults in US have some type of disability
- ~16% have motor disabilities
- ~20% use assistive technology at some point
```

### 3️⃣ **Agent Proposes Alternatives**
Agent suggests accessible solutions:
```
ACCESSIBLE ALTERNATIVES:

Option A: Keyboard-Accessible Animation
✅ Toggle works with keyboard (Space/Enter)
✅ Works with mouse, touch, and voice commands
✅ Reduced-motion preference respected
✅ Fallback for low-bandwidth scenarios

Option B: Async Animation (Progressive Enhancement)
✅ Core toggle works instantly without animation
✅ Smooth animation loads as progressive enhancement
✅ Users on slow connections get instant feedback
✅ Animation respects prefers-reduced-motion

RECOMMENDATION: Option A (no animation loss, everyone benefits)
```

### 4️⃣ **Agent Requests Explicit Decision**
This is where tradeoffs are decided:
```
YOUR DECISION REQUIRED:

Acceptable choices:
A) ✅ Redesign (implement accessible alternative)
B) ⚠️  Accept limitation (feature disabled for users without animation)
C) ⚠️  Document exclusion (publicly note who is excluded)
D) ❌ Reject principle (explicitly override Accessibility First)

RECOMMENDED: Option A (inclusive design benefits everyone)

Note: Options B, C, D require documented decision with:
- Business justification
- Impact assessment  
- Timeline for accessibility remediation
- Approval from project lead

Cannot proceed until this is resolved.
```

### 5️⃣ **Canon Principle in Effect**
These principles are non-negotiable architectural requirements:
- **Always respected** unless explicitly overridden with documented decision
- **Not suggestions** - they're constraints of the system
- **Team discussions required** - no silent compromises
- **Public documentation required** - team aware of exclusions
- **Remediation planned** - timeline to fix conflicts

### When to Apply This Protocol

**Automatically trigger for:**
- Any feature that requires mouse/pointer
- Any time-limited interaction
- Any audio-only or video-only content
- Any hardcoded date/time/currency formats
- Any text without translation plan
- Any color-only information
- Any features needing specific cultural knowledge
- Any documentation without multiple modalities

**Manual trigger for:**
- User requests to review accessibility
- Intentional feature tradeoffs
- Performance vs accessibility debates
- Language or wording decisions
- Cultural or representation concerns

**Result: No features ship with silent accessibility compromises.**

---

## �📊 Diagnostic Implementation Standards

### 648 Production Diagnostics

**Current Status:** ✅ 100% Production-Ready
- All 648 diagnostics: Pass PHP linting
- All pass syntax validation
- All follow WPShadow patterns
- All properly categorized

**Diagnostic Structure:**
```php
namespace WPShadow\Diagnostics;

class Diagnostic_Example extends \WPShadow\Core\Diagnostic_Base {
    const ID = 'category-diagnostic-name';
    const NAME = 'User-Friendly Name';
    const DESCRIPTION = 'What this checks';
    const CATEGORY = 'security'; // security, performance, seo, health, etc
    
    public function execute(): array {
        try {
            // Perform the check
            $is_healthy = $this->check_something();
            
            return [
                'status'  => $is_healthy ? 'pass' : 'fail',
                'message' => 'Clear explanation of the result',
                'details' => 'Technical details for advanced users',
                'kpi'     => 'Measurable impact if available',
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Error occurred: ' . $e->getMessage(),
                'details' => $e->getTraceAsString(),
            ];
        }
    }
}
```

### Categories (7 Major)

1. **Security** - User safety, data protection
2. **Performance** - Speed, efficiency, resource usage
3. **SEO** - Search visibility, indexing
4. **Health** - WordPress core health
5. **Backup** - Data backup status
6. **Compatibility** - Plugin/theme compatibility
7. **Compliance** - Legal, privacy, standards

---

## 📝 Documentation Requirements

### Summary Documents

**When to Create:**
- After completing a user-requested feature
- After finishing a major refactoring
- After resolving a GitHub issue
- After completing a phase of work
- After making architectural changes

**Where to Store:**
```
docs/[FEATURE]_IMPLEMENTATION_COMPLETE.md
docs/PHASE_[N]_COMPLETION_SUMMARY.md
```

**What to Include:**
- Status, date, token investment
- What was completed (specific features, files)
- What remains (blockers, future work)
- Technical summary (architecture, patterns)
- Key decisions (why, trade-offs, philosophy alignment)

### Example Summary

See [PRERELEASE_HANDOFF.md](/workspaces/wpshadow/docs/PRERELEASE_HANDOFF.md) for complete example.

---

## 🚀 Development Workflow

### Starting New Feature

1. **Check Philosophy:** Does this align with the 11 Commandments?
2. **Validate Approach:** Does this follow WordPress.org standards?
3. **Design Thoroughly:** Plan before coding
4. **Code with Standards:** Follow patterns above
5. **Test Completely:** Manual + automated testing
6. **Document:** Create summary when done
7. **Commit:** Clear, descriptive commit messages

### Commit Message Template

```
[Type]: Brief description

- What was changed
- Why it was changed
- Any breaking changes
- Philosophy alignment (if applicable)

Type: Feature | Fix | Refactor | Docs | Test | Chore
```

### Code Review Checklist

```
Philosophy:
- [ ] Aligns with 11 Commandments
- [ ] Improves user experience
- [ ] No unnecessary features

WordPress.org:
- [ ] Follows coding standards
- [ ] Security best practices applied
- [ ] Performance optimized
- [ ] Compatible with minimum versions

Code Quality:
- [ ] DRY principle followed
- [ ] Well-documented
- [ ] Tested
- [ ] No debugging code remaining
```

---

## 🔗 Key Reference Documents

### Philosophy & Values
- [WPSHADOW_AGENT_PREFERENCES.md](/workspaces/wpshadow/docs/WPSHADOW_AGENT_PREFERENCES.md) - Agent behavior & summary requirements
- [KILLER_TESTS_50_MUST_HAVES.md](/workspaces/wpshadow/docs/archive/KILLER_TESTS_50_MUST_HAVES.md) - Philosophy: "Every test delivers value"

### Coding & Standards
- [CODING_STANDARDS.md](/workspaces/wpshadow/docs/CODING_STANDARDS.md) - WPShadow-specific standards
- [CODE_REVIEW_SENIOR_DEVELOPER.md](/workspaces/wpshadow/docs/CODE_REVIEW_SENIOR_DEVELOPER.md) - Quality standards

### Release & Distribution
- [PRERELEASE_TESTING_GUIDE.md](/workspaces/wpshadow/docs/PRERELEASE_TESTING_GUIDE.md) - Testing procedures
- [RELEASE_CHECKLIST.md](/workspaces/wpshadow/docs/RELEASE_CHECKLIST.md) - Release verification

### WordPress.org Guidelines
- [wordpress.org Plugin Standards](https://developer.wordpress.org/plugins/intro/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [Plugin Security](https://developer.wordpress.org/plugins/security/)
- [Plugin Handbook](https://developer.wordpress.org/plugins/)

---

## 📞 Quick Reference

### Testing Before Commit

**PHP Syntax:**
```bash
php -l includes/diagnostics/class-diagnostic-*.php
```

**WordPress Standards:**
```bash
composer phpcs
composer phpstan
```

**Manual Testing:**
- Load plugin in WordPress 5.0 - 6.4+
- Test on PHP 7.4 - 8.3
- Verify diagnostics run
- Check debug.log for errors

### Common Issues & Solutions

**Issue:** "Undefined function wp_..."  
**Solution:** Ensure WordPress is loaded before calling (use `plugins_loaded` hook)

**Issue:** "Nonce verification failed"  
**Solution:** Ensure nonce is both created (frontend) and verified (backend)

**Issue:** "Permission denied" messages  
**Solution:** Add capability check: `if (!current_user_can('manage_options')) { ... }`

**Issue:** Transient data not updating  
**Solution:** Delete old transient before setting new: `delete_transient($key);` then `set_transient(...)`

---

## ✨ Final Notes

**Remember:** Every line of code is a promise to users that WPShadow will be their trusted neighbor—helpful, honest, and protective. Code should reflect this philosophy.

**Questions?** Refer to the key documents above or review existing diagnostics for patterns.

**Key Principle:** When in doubt, ask: *"Would a trusted neighbor do this?"*

---

**Agent File Version:** 1.0.0  
**Last Updated:** January 24, 2026  
**Status:** Active & Ready for Use
