# DRY (Don't Repeat Yourself) Audit Report
**WPShadow Core Plugin**
**Date:** January 31, 2026
**Auditor:** AI Assistant

---

## Executive Summary

This audit identifies code duplication and opportunities for refactoring across the WPShadow plugin to improve maintainability, reduce bugs, and decrease codebase size.

### Key Findings:
- **✅ EXCELLENT:** Base class architecture (Diagnostic_Base, Treatment_Base, AJAX_Handler_Base)
- **⚠️ MODERATE DUPLICATION:** Security validation patterns
- **⚠️ MODERATE DUPLICATION:** Database query patterns
- **⚠️ HIGH DUPLICATION:** CSS design system classes
- **⚠️ MODERATE DUPLICATION:** JavaScript AJAX patterns
- **✅ GOOD:** Minimal treatment implementations (only 1 found - database cleanup)

---

## 1. Architecture Assessment: ✅ EXCELLENT

### Base Classes (Good DRY Pattern)
The plugin uses proper inheritance for core functionality:

**Diagnostic_Base** - 150+ diagnostics inherit from this
- Standardized `check()` method
- Consistent return structure
- Built-in hooks (`before_diagnostic_check`, `after_diagnostic_check`)

**Treatment_Base** - 1 treatment found (more expected)
- Standardized `apply()` method
- Built-in dry-run support
- Permission checks via `can_apply()`

**AJAX_Handler_Base** - 30+ AJAX handlers inherit from this
- Built-in nonce verification via `verify_request()`
- Parameter sanitization via `get_post_param()`
- Standardized responses via `send_success()` / `send_error()`

**Recommendation:** ✅ No changes needed - this is exemplary DRY architecture.

---

## 2. Security Validation Patterns: ⚠️ MODERATE DUPLICATION

### Pattern 1: Manual Nonce Verification (20+ instances)
Many files manually check nonces instead of using base class:

**Example duplications:**
```php
// Pattern A: Manual wp_verify_nonce
if ( ! wp_verify_nonce( $_POST['nonce'], 'action_name' ) ) { wp_die(); }

// Pattern B: check_ajax_referer
check_ajax_referer( 'action_name', 'nonce' );

// Pattern C: AJAX_Handler_Base (best practice)
self::verify_request( 'action_name', 'manage_options' );
```

**Files with manual nonce checks:**
- `includes/onboarding/class-feature-tour.php` (3 instances)
- `includes/admin/class-first-activation-welcome.php` (2 instances)
- `includes/admin/class-guardian-inactive-notice.php` (2 instances)
- `includes/admin/class-phone-home-indicator.php` (1 instance)
- `includes/admin/class-privacy-dashboard-page.php` (3 instances)
- `includes/content/class-training-widget.php` (2 instances)
- `includes/content/class-weekly-tips-widget.php` (1 instance)
- `includes/admin/ajax/consent-preferences-handler.php` (2 instances)

**Recommendation:**
1. Create `Security_Validator` helper class for non-AJAX contexts
2. Migrate classes to extend `AJAX_Handler_Base` where appropriate
3. Consolidate manual checks into utility methods

---

## 3. Input Sanitization: ⚠️ HIGH DUPLICATION

### Pattern: `sanitize_text_field( wp_unslash( $_POST['key'] ) )`

This exact pattern appears 20+ times across the codebase:

**Example files:**
- `includes/admin/class-auto-deploy.php`
- `includes/screens/class-phase4-settings-page.php`
- `.tmp-vault/includes/class-timu-vault.php` (16+ instances!)
- `includes/workflow/class-command.php`

**Current (repeated):**
```php
$value = sanitize_text_field( wp_unslash( $_POST['field'] ) );
$email = sanitize_email( wp_unslash( $_POST['email'] ) );
$url = esc_url_raw( wp_unslash( $_POST['url'] ) );
```

**Better approach:**
```php
// AJAX_Handler_Base already provides this!
$value = self::get_post_param( 'field', 'text', '', true );
$email = self::get_post_param( 'email', 'email', '', true );
$url = self::get_post_param( 'url', 'url', '', true );
```

**Recommendation:**
1. ✅ `AJAX_Handler_Base::get_post_param()` already exists and handles this
2. Migrate all AJAX handlers to use this method
3. Create `Request_Sanitizer` utility class for non-AJAX contexts

---

## 4. Capability Checks: ⚠️ MODERATE DUPLICATION

### Pattern: `current_user_can( 'manage_options' )`

Found 40+ instances of repeated capability checks:

**Common pattern:**
```php
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( __( 'Insufficient permissions', 'wpshadow' ) );
}
```

**Files with multiple instances:**
- `.tmp-vault/includes/class-timu-vault.php` (11 instances)
- `includes/admin/class-privacy-dashboard-page.php` (3 instances)
- Multiple AJAX handlers (should use AJAX_Handler_Base)

**Better approach:**
```php
// For AJAX
self::verify_request( 'nonce_action', 'manage_options' ); // Already in AJAX_Handler_Base!

// For admin pages
if ( ! Security_Validator::verify_capability( 'manage_options' ) ) {
    wp_die( Security_Validator::get_permission_error() );
}
```

**Recommendation:**
1. Create `Security_Validator::verify_capability()` utility
2. Standardize error messages through this utility
3. AJAX handlers should use existing `verify_request()` method

---

## 5. Database Query Patterns: ⚠️ LOW-MODERATE DUPLICATION

### Pattern: Prepared Statements

Found 11 instances of `$wpdb->prepare()` queries - mostly unique, but some patterns:

**Common SELECT pattern:**
```php
$wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $id )
```

**Appears in:**
- `includes/core/class-visual-comparator.php`
- `includes/reporting/class-report-snapshot-manager.php` (2 instances)

**Recommendation:**
- ✅ Low duplication - queries are mostly unique
- Consider adding `Query_Builder` class only if query patterns increase
- Current usage is acceptable

---

## 6. CSS Design System: ⚠️ HIGH DUPLICATION

### Issue: Multiple CSS files define similar components

**Design system classes found across 32 CSS files:**
- `.wps-card` (defined in multiple files)
- `.wps-button` / `.wps-button-group`
- `.wps-badge`
- `.wps-card-header`, `.wps-card-title`, `.wps-card-footer`

**Files with overlapping styles:**
- `assets/css/design-system.css` (✅ Master file - 285 lines)
- `assets/css/kanban-board-consolidated.css` (redefines .wps-card components)
- `assets/css/admin-pages.css` (adds .wps-card variants)
- `includes/views/reports/site-dna.php` (inline styles for .wps-card)

**Recommendation:**
1. **HIGH PRIORITY:** Consolidate all design system styles into `design-system.css`
2. Remove duplicate definitions from other CSS files
3. Use CSS custom properties (already implemented) for theming
4. Eliminate inline `<style>` blocks in PHP views

---

## 7. JavaScript AJAX Patterns: ⚠️ MODERATE DUPLICATION

### Issue: 36 JavaScript files with similar AJAX patterns

**Common pattern (repeated in many files):**
```javascript
$.ajax({
    url: ajaxurl,
    type: 'POST',
    data: {
        action: 'wpshadow_action',
        nonce: wpShadowData.nonce,
        param: value
    },
    success: function(response) {
        if (response.success) {
            // Handle success
        }
    },
    error: function() {
        // Handle error
    }
});
```

**Files with AJAX calls:**
- `assets/js/guardian-scan-interface.js`
- `assets/js/workflow-builder.js`
- `assets/js/kanban-board.js`
- `assets/js/admin-pages.js`
- `assets/js/gamification.js`
- Many more (36 total JS files)

**Recommendation:**
1. Create `wpshadow-ajax-helper.js` utility module
2. Provide standardized methods: `wpShadowAjax.post()`, `wpShadowAjax.get()`
3. Built-in loading states, error handling, and nonce management

**Example utility:**
```javascript
// wpshadow-ajax-helper.js
window.wpShadowAjax = {
    post: function(action, data, options) {
        options = options || {};

        return $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: Object.assign({
                action: 'wpshadow_' + action,
                nonce: wpShadowData.nonce
            }, data),
            beforeSend: options.beforeSend,
            success: function(response) {
                if (response.success && options.success) {
                    options.success(response.data);
                } else if (!response.success && options.error) {
                    options.error(response.data ? response.data.message : 'Unknown error');
                }
            },
            error: options.error || function() {
                alert('An error occurred. Please try again.');
            }
        });
    }
};
```

---

## 8. Diagnostic Implementation Patterns: ✅ GOOD

### Assessment: 150+ Diagnostics with Minimal Duplication

**Why this works well:**
1. All extend `Diagnostic_Base`
2. Standardized structure (slug, title, description, family)
3. Single responsibility per diagnostic
4. No shared logic duplication

**Example structure (repeated correctly 150+ times):**
```php
class Diagnostic_Example extends Diagnostic_Base {
    protected static $slug = 'example';
    protected static $title = 'Example Check';
    protected static $description = 'Description';
    protected static $family = 'security';

    public static function check() {
        // Unique logic here
        return $finding_or_null;
    }
}
```

**Recommendation:** ✅ No changes needed - this is proper use of inheritance.

---

## 9. Treatment Implementation: ℹ️ INSUFFICIENT DATA

### Current State: Only 1 Treatment Found

**Found:**
- `includes/treatments/class-treatment-database-transient-cleanup.php`

**Expected:** Many more treatments based on 150+ diagnostics

**Recommendation:**
- Verify if treatments are missing or in different location
- If missing: Create treatment implementations for auto-fixable diagnostics
- If elsewhere: Audit for duplication once located

---

## 10. Specific Duplication Cases

### Case A: Guardian Token Balance Widget (Recent Edit)
**File:** `includes/guardian/class-token-balance-widget.php`

**Duplicate code:**
- Status dot HTML (repeated in multiple menu items)
- Similar nonce creation patterns
- Repeated jQuery AJAX patterns

**Current:**
```php
// Line 57-59: Status dot definition
$status_dot = $is_enabled
    ? '<span class="wpshadow-status-dot wpshadow-status-active">...</span>'
    : '<span class="wpshadow-status-dot wpshadow-status-inactive">...</span>';

// Line 59-73: Similar pattern repeated
```

**Recommendation:**
- Extract status dot generation to helper method
- Reuse across admin bar items

---

## Priority Recommendations

### 🔴 HIGH PRIORITY (Immediate Impact)

1. **Consolidate CSS Design System**
   - Estimated savings: 500-1000 lines of duplicate CSS
   - Impact: Easier theming, consistent UI, smaller file size
   - Files to merge: 5-7 CSS files with overlapping component definitions

2. **Create JavaScript AJAX Helper**
   - Estimated savings: 200-400 lines of duplicate JavaScript
   - Impact: Consistent error handling, easier testing, better UX
   - Files to refactor: 20-30 JavaScript files

3. **Migrate Manual Nonce Checks to Utilities**
   - Estimated savings: 50-100 lines of duplicate security code
   - Impact: Fewer security bugs, consistent security practices
   - Files to refactor: 12-15 PHP files

### 🟡 MEDIUM PRIORITY (Quality Improvement)

4. **Create Security_Validator Utility Class**
   - Consolidate capability checks
   - Standardize permission error messages
   - Provide multisite-aware capability checking

5. **Standardize Input Sanitization**
   - Migrate to `AJAX_Handler_Base::get_post_param()` where possible
   - Create `Request_Sanitizer` for non-AJAX contexts

6. **Extract Repeated HTML Patterns**
   - Create view helper methods for common components
   - Examples: Status badges, action buttons, card layouts

### 🟢 LOW PRIORITY (Optimization)

7. **Investigate Missing Treatments**
   - Only 1 treatment found vs 150+ diagnostics
   - Verify architecture - may be intentional

8. **Database Query Abstraction**
   - Current duplication is low
   - Only implement if pattern increases

---

## Metrics

### Current State
- **Total Files Audited:** ~500
- **Diagnostics Found:** 150+
- **Treatments Found:** 1
- **AJAX Handlers Found:** 30+
- **CSS Files:** 32
- **JavaScript Files:** 36

### Duplication Estimates
- **Security Validation:** 40+ instances of manual checks
- **Input Sanitization:** 20+ instances of `sanitize_text_field( wp_unslash() )`
- **CSS Components:** 5-7 files redefine design system classes
- **JavaScript AJAX:** 30+ files with similar AJAX patterns
- **Capability Checks:** 40+ instances of `current_user_can( 'manage_options' )`

### Potential Impact
- **Code Reduction:** 15-20% (estimated 1,500-2,500 lines)
- **Maintainability:** High improvement
- **Bug Reduction:** Medium-high (fewer places to update = fewer bugs)
- **Testing:** Easier (test utilities once vs 40+ instances)

---

## Implementation Roadmap

### Phase 1: CSS Consolidation (1-2 hours)
1. Audit all CSS files for component duplication
2. Consolidate into `design-system.css`
3. Remove duplicates from other files
4. Test all admin pages

### Phase 2: JavaScript AJAX Helper (2-3 hours)
1. Create `wpshadow-ajax-helper.js`
2. Migrate 5-10 files as proof of concept
3. Document new pattern
4. Gradually migrate remaining files

### Phase 3: Security Validation Utilities (2-3 hours)
1. Create `Security_Validator` class
2. Migrate manual nonce checks
3. Standardize capability checking
4. Update documentation

### Phase 4: Input Sanitization (1-2 hours)
1. Identify all manual sanitization patterns
2. Create `Request_Sanitizer` for non-AJAX contexts
3. Document `get_post_param()` usage
4. Migrate files gradually

---

## Conclusion

The WPShadow plugin demonstrates **excellent DRY architecture** in its core base classes (Diagnostic_Base, Treatment_Base, AJAX_Handler_Base). However, there are opportunities to reduce duplication in:

1. **CSS design system** (HIGH duplication)
2. **JavaScript AJAX patterns** (MODERATE duplication)
3. **Security validation** (MODERATE duplication)
4. **Input sanitization** (MODERATE duplication)

Implementing the recommendations would:
- Reduce codebase by ~15-20%
- Improve maintainability
- Reduce bug surface area
- Make testing easier
- Provide consistent user experience

**Overall Grade: B+** (Excellent architecture, moderate implementation duplication)

---

## Appendix: Tools Used

- `grep_search` for pattern detection
- `file_search` for file discovery
- Manual code review for context
- Pattern frequency analysis

**Note:** `.tmp-vault/` directory excluded from main recommendations as it appears to be temporary/third-party code.
