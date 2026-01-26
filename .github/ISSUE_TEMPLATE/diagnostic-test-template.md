---
name: Diagnostic Test Implementation
about: Template for implementing and testing a diagnostic check
title: '[DIAGNOSTIC] Implement: {Diagnostic Name}'
labels: diagnostic, needs-implementation
assignees: ''
---

## Diagnostic Overview

**File Name:** `class-diagnostic-{slug}.php`  
**Diagnostic Slug:** `{slug}`  
**Category:** {Security|Performance|Code Quality|WordPress Config|Monitoring|Workflow/System}  
**Threat Level:** {High|Medium|Low}  
**Auto-Fixable:** {Yes|No}

## What This Diagnostic Should Test

### Purpose
{Brief description of what this diagnostic checks for}

### Why It Matters
{Explanation of the impact - security risk, performance issue, best practice violation, etc.}

### Expected Behavior
- **PASS (returns null):** {Describe the healthy state}
- **FAIL (returns finding array):** {Describe when an issue is detected}

## Implementation Checklist

### 1. File Setup
- [ ] Create file in `/workspaces/wpshadow/includes/diagnostics/tests/`
- [ ] Use filename pattern: `class-diagnostic-{slug}.php`
- [ ] Add `declare(strict_types=1);` at top
- [ ] Set namespace to `WPShadow\Diagnostics`
- [ ] Add ABSPATH check

### 2. Class Structure
- [ ] Extend `WPShadow\Core\Diagnostic_Base`
- [ ] Define protected static properties:
  - [ ] `$slug` - machine-readable identifier
  - [ ] `$title` - human-readable name
  - [ ] `$description` - plain English explanation
  - [ ] `$category` - diagnostic category
  - [ ] `$threat_level` - numerical threat score (0-100)
  - [ ] `$family` - grouping family (optional)
  - [ ] `$family_label` - human-readable family name (optional)

### 3. Implement `check()` Method
- [ ] Method signature: `public static function check(): ?array`
- [ ] Perform the diagnostic check using WordPress APIs
- [ ] Return `null` if no issue found (site is healthy)
- [ ] Return finding array if issue detected with structure:
  ```php
  return array(
      'id'           => self::$slug,
      'title'        => self::$title,
      'description'  => __( 'Detailed finding description', 'wpshadow' ),
      'category'     => 'security', // or appropriate category
      'severity'     => 'high', // high, medium, low
      'threat_level' => 85, // 0-100
      'auto_fixable' => false, // or true if treatment exists
      'timestamp'    => current_time( 'mysql' ),
  );
  ```

### 4. Code Quality Standards
- [ ] Use WordPress APIs (no direct database queries)
- [ ] Sanitize all inputs
- [ ] Escape all outputs
- [ ] Use text domain `'wpshadow'` for translations
- [ ] Follow WordPress Coding Standards
- [ ] Add PHPDoc comments for all methods
- [ ] Use type hints for parameters and return values

### 5. Security Requirements
- [ ] No `eval()` or similar dangerous functions
- [ ] No raw SQL queries (use `$wpdb->prepare()` if database access needed)
- [ ] Check capabilities where appropriate
- [ ] Validate and sanitize any user input
- [ ] No external API calls without user consent

### 6. Testing
- [ ] Manual test on fresh WordPress install
- [ ] Test both PASS and FAIL conditions
- [ ] Verify null return when healthy
- [ ] Verify finding array when issue exists
- [ ] Test on multisite if applicable
- [ ] Check performance (should complete in < 1 second)

### 7. Documentation
- [ ] Add inline comments explaining logic
- [ ] Document any edge cases or assumptions
- [ ] Include KB link reference if available
- [ ] Add to Feature Matrix documentation

### 8. Integration
- [ ] Register in `Diagnostic_Registry`
- [ ] Add to appropriate category
- [ ] Link to treatment if auto-fixable
- [ ] Update `FEATURE_MATRIX_DIAGNOSTICS.md`

## Suggested Implementation Approach

### Step 1: Determine What to Check
{Specific WordPress functions, options, or states to examine}

### Step 2: Identify WordPress APIs
{List relevant WordPress functions or classes to use}
- Example: `get_user_by()`, `get_option()`, `wp_get_theme()`, etc.

### Step 3: Define Conditions
**Healthy State (return null):**
{Specific conditions that indicate no issue}

**Issue State (return finding):**
{Specific conditions that indicate a problem}

### Step 4: Code Skeleton
```php
<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Diagnostic_{ClassName} extends Diagnostic_Base {

    protected static $slug         = '{slug}';
    protected static $title        = '{Human Readable Title}';
    protected static $description  = '{Brief description of what this checks}';
    protected static $category     = '{category}';
    protected static $threat_level = '{threat-level-number}';

    /**
     * Run the diagnostic check
     *
     * @return ?array Null if pass, array of findings if fail
     */
    public static function check(): ?array {
        // TODO: Implement check logic
        
        // Check if condition exists
        $has_issue = false; // Your logic here
        
        if ( ! $has_issue ) {
            return null; // Healthy - no issue found
        }
        
        // Issue detected - return finding
        return array(
            'id'           => self::$slug,
            'title'        => self::$title,
            'description'  => __( 'Detailed explanation of the issue', 'wpshadow' ),
            'category'     => self::$category,
            'severity'     => 'medium', // high, medium, low
            'threat_level' => self::$threat_level,
            'auto_fixable' => false, // Change to true if treatment exists
            'timestamp'    => current_time( 'mysql' ),
        );
    }
}
```

## Example Reference Diagnostics

Similar diagnostics to reference for implementation patterns:

1. **Admin Username Check** (`class-diagnostic-admin-username.php`)
   - Simple user check
   - Returns null or finding based on user existence

2. **Debug Mode Check** (`class-diagnostic-debug-mode-enabled.php`)
   - Constant checking
   - Multiple conditions (WP_DEBUG, WP_DEBUG_LOG)

3. **SSL Check** (if available)
   - Environment/configuration check
   - Boolean state evaluation

## Testing Scenarios

### Scenario 1: Healthy State
**Setup:**
{Describe how to set up a test environment where check should pass}

**Expected Result:** `check()` returns `null`

### Scenario 2: Issue Detected
**Setup:**
{Describe how to set up a test environment where check should fail}

**Expected Result:** `check()` returns finding array with proper structure

### Scenario 3: Edge Cases
{List any edge cases or special scenarios to test}

## Related Diagnostics

{List any related diagnostics that check similar things or might conflict}

## Treatment Implementation (if auto-fixable)

If this diagnostic is auto-fixable, a corresponding treatment should be created:

**Treatment File:** `class-treatment-{slug}.php`  
**Location:** `/workspaces/wpshadow/includes/treatments/`

Treatment should:
- [ ] Fix the issue detected by this diagnostic
- [ ] Create backups before making changes
- [ ] Be fully reversible (implement `undo()` method)
- [ ] Log to KPI tracker
- [ ] Provide clear success/failure messages

## Links & Resources

- **Feature Matrix:** `/docs/FEATURE_MATRIX_DIAGNOSTICS.md`
- **Diagnostic Template:** `/docs/DIAGNOSTIC_TEMPLATE.md`
- **Architecture Guide:** `/docs/ARCHITECTURE.md`
- **Coding Standards:** `/docs/CODING_STANDARDS.md`
- **Product Philosophy:** `/docs/PRODUCT_PHILOSOPHY.md`

## Definition of Done

- [ ] File created with correct naming and structure
- [ ] All class properties defined
- [ ] `check()` method implemented and tested
- [ ] Both pass and fail conditions tested manually
- [ ] Code follows WordPress Coding Standards
- [ ] PHPDoc comments added
- [ ] Security requirements met
- [ ] Registered in Diagnostic_Registry
- [ ] Documentation updated
- [ ] Treatment created (if auto-fixable)
- [ ] Peer review completed
- [ ] Merged to main branch

---

**Assignee Instructions:**
1. Fill in the placeholders with specific information
2. Follow the implementation checklist step-by-step
3. Test thoroughly before marking as complete
4. Update all checkboxes as you progress
5. Link related PRs and issues
