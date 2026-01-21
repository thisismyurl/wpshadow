# Phase 4 Implementation Plan - Quick Wins (4-6 hours)

## Strategic Overview

This document outlines the highest-impact DRY violations to fix before WordCamp presentation. Focus on **consolidation** rather than new features.

---

## Task 1: Create Color Utils Class (20 minutes)
**File:** `includes/core/class-color-utils.php`  
**Impact:** Consolidates color logic used in 3+ places  
**Current Locations:**
- [wpshadow.php:225](wpshadow.php#L225) - hex_to_rgb()
- [wpshadow.php:244](wpshadow.php#L244) - contrast_ratio()
- WCAG compliance checks

**After:** All color operations go through Color_Utils

```php
// includes/core/class-color-utils.php
<?php
namespace WPShadow\Core;

class Color_Utils {
    /**
     * Convert hex color to RGB array
     */
    public static function hex_to_rgb( $hex ) { ... }
    
    /**
     * Calculate WCAG contrast ratio
     */
    public static function contrast_ratio( $fg, $bg ) { ... }
    
    /**
     * Check if contrast meets accessibility standard
     */
    public static function is_accessible_contrast( $fg, $bg, $level = 'AA' ) {
        $ratio = self::contrast_ratio( $fg, $bg );
        return $level === 'AAA' ? $ratio >= 7 : $ratio >= 4.5;
    }
}
```

**Update Points:**
- [wpshadow.php:225](wpshadow.php#L225) → Use `Color_Utils::hex_to_rgb()`
- [wpshadow.php:244](wpshadow.php#L244) → Use `Color_Utils::contrast_ratio()`
- All AJAX calls for contrast checking

---

## Task 2: Create Theme Data Provider (30 minutes)
**File:** `includes/core/class-theme-data-provider.php`  
**Impact:** Consolidates 3 theme getter functions  
**Current Functions:**
- [wpshadow.php:270](wpshadow.php#L270) - get_theme_color_contexts()
- [wpshadow.php:320](wpshadow.php#L320) - get_theme_palette_colors()
- [wpshadow.php:351](wpshadow.php#L351) - get_theme_background_color()

**Pattern:** All use same fallback chain (block theme → classic → defaults)

```php
// includes/core/class-theme-data-provider.php
<?php
namespace WPShadow\Core;

class Theme_Data_Provider {
    /**
     * Get theme color palette with fallbacks
     */
    public static function get_palette() { ... }
    
    /**
     * Get theme background color
     */
    public static function get_background_color() { ... }
    
    /**
     * Get color context combinations for a11y testing
     */
    public static function get_color_contexts() { ... }
    
    /**
     * Shared: Get block theme setting
     */
    private static function get_block_theme_setting( $path ) { ... }
    
    /**
     * Shared: Get classic theme support
     */
    private static function get_classic_theme_support( $key ) { ... }
}
```

**Update Points:**
- [wpshadow.php:270](wpshadow.php#L270) → Use `Theme_Data_Provider::get_color_contexts()`
- [wpshadow.php:320](wpshadow.php#L320) → Use `Theme_Data_Provider::get_palette()`
- [wpshadow.php:351](wpshadow.php#L351) → Use `Theme_Data_Provider::get_background_color()`
- All AJAX calls for theme contrast checking

---

## Task 3: Upgrade Tooltip Manager (20 minutes)
**File:** Create upgraded version of tooltip loading  
**Current:** Uses static variable caching (request-level only)  
**Upgrade:** Use transient caching (persistent)

**Changes to:** [wpshadow.php:371](wpshadow.php#L371)

```php
// Before: request-level static cache
static $tooltips = array();
if ( isset( $tooltips[ $category ] ) ) {
    return $tooltips[ $category ];
}

// After: persistent transient cache
$cache_key = 'wpshadow_tooltips_' . ( $category ?: 'all' );
$tooltips = get_transient( $cache_key );
if ( false !== $tooltips ) {
    return $tooltips;
}

// Load from JSON, then cache for 24 hours
$tooltips = self::load_from_json( $category );
set_transient( $cache_key, $tooltips, 24 * HOUR_IN_SECONDS );
return $tooltips;
```

**Benefit:** Survives across page loads, better for multisite

---

## Task 4: Create User Preferences Manager (20 minutes)
**File:** `includes/core/class-user-preferences-manager.php`  
**Impact:** Consolidates scattered user meta get/set patterns

**Current Issues:**
- [wpshadow.php:457](wpshadow.php#L457) - `get_user_tip_prefs()`
- [wpshadow.php:470](wpshadow.php#L470) - `save_user_tip_prefs()`
- Dark mode preferences in treatments

```php
// includes/core/class-user-preferences-manager.php
<?php
namespace WPShadow\Core;

class User_Preferences_Manager {
    private static $schema = array(
        'tip_prefs' => array( 'default' => array(), 'type' => 'array' ),
        'dark_mode' => array( 'default' => false, 'type' => 'boolean' ),
    );
    
    /**
     * Get user preference with validation
     */
    public static function get( $user_id, $key, $default = null ) {
        if ( ! isset( self::$schema[ $key ] ) ) {
            return $default;
        }
        
        $meta = get_user_meta( $user_id, "wpshadow_{$key}", true );
        return $meta ?: ( $default ?? self::$schema[ $key ]['default'] );
    }
    
    /**
     * Set user preference with validation
     */
    public static function set( $user_id, $key, $value ) {
        if ( ! isset( self::$schema[ $key ] ) ) {
            return false;
        }
        
        // Type validation
        $type = self::$schema[ $key ]['type'];
        if ( $type === 'boolean' ) {
            $value = (bool) $value;
        } elseif ( $type === 'array' ) {
            $value = (array) $value;
        }
        
        update_user_meta( $user_id, "wpshadow_{$key}", $value );
        return true;
    }
}
```

**Update Points:**
- Replace `wpshadow_get_user_tip_prefs()` with `User_Preferences_Manager::get()`
- Replace `wpshadow_save_user_tip_prefs()` with `User_Preferences_Manager::set()`
- Dark mode treatment uses same manager

---

## Task 5: Migrate Workflow AJAX Handlers (90 minutes)
**File:** 8 new classes in `includes/admin/ajax/`  
**Current:** [includes/workflow/class-workflow-ajax.php](includes/workflow/class-workflow-ajax.php)  
**Pattern:** Match existing handler structure

**Handlers to migrate:**
1. `wp_ajax_wpshadow_save_workflow` → class-save-workflow-handler.php
2. `wp_ajax_wpshadow_load_workflows` → class-load-workflows-handler.php
3. `wp_ajax_wpshadow_get_workflow` → class-get-workflow-handler.php
4. `wp_ajax_wpshadow_delete_workflow` → class-delete-workflow-handler.php
5. `wp_ajax_wpshadow_toggle_workflow` → class-toggle-workflow-handler.php
6. `wp_ajax_wpshadow_generate_workflow_name` → class-generate-workflow-name-handler.php
7. `wp_ajax_wpshadow_get_available_actions` → class-get-available-actions-handler.php
8. `wp_ajax_wpshadow_get_action_config` → class-get-action-config-handler.php

**Template for each:**
```php
<?php
namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

class Save_Workflow_Handler extends AJAX_Handler_Base {
    public static function register() {
        add_action( 'wp_ajax_wpshadow_save_workflow', array( __CLASS__, 'handle' ) );
    }
    
    public static function handle() {
        self::verify_request( 'wpshadow_workflow', 'manage_options' );
        
        $workflow_id = self::get_post_param( 'workflow_id', 'key', '', true );
        $workflow_data = self::get_post_param( 'workflow', 'textarea' );
        
        // Business logic
        $result = \WPShadow\Workflow\Workflow_Manager::save_workflow( 
            $workflow_id, 
            json_decode( $workflow_data, true ) 
        );
        
        if ( $result ) {
            self::send_success( array( 'workflow' => $result ) );
        } else {
            self::send_error( 'Failed to save workflow' );
        }
    }
}
```

**After completion:**
- Delete inline handlers from [includes/workflow/class-workflow-ajax.php](includes/workflow/class-workflow-ajax.php)
- Add registration calls to [wpshadow.php](wpshadow.php) (after line 590)
- Result: -120 lines of duplicate security checks

---

## Implementation Checklist

- [ ] **Task 1 (20 min):** Create Color_Utils class
  - [ ] Create file: `includes/core/class-color-utils.php`
  - [ ] Move hex_to_rgb() logic
  - [ ] Move contrast_ratio() logic
  - [ ] Update calls in wpshadow.php
  - [ ] Verify no syntax errors

- [ ] **Task 2 (30 min):** Create Theme_Data_Provider class
  - [ ] Create file: `includes/core/class-theme-data-provider.php`
  - [ ] Move get_theme_palette_colors() logic
  - [ ] Move get_theme_background_color() logic
  - [ ] Move get_theme_color_contexts() logic
  - [ ] Update calls in wpshadow.php
  - [ ] Verify no syntax errors

- [ ] **Task 3 (20 min):** Upgrade Tooltip Manager
  - [ ] Update wpshadow_get_tooltip_catalog() to use transient
  - [ ] Add cache invalidation hooks (where appropriate)
  - [ ] Test with multiple categories
  - [ ] Verify cache persistence

- [ ] **Task 4 (20 min):** Create User_Preferences_Manager
  - [ ] Create file: `includes/core/class-user-preferences-manager.php`
  - [ ] Define schema array
  - [ ] Implement get() method with validation
  - [ ] Implement set() method with validation
  - [ ] Update wpshadow_get_user_tip_prefs() to use manager
  - [ ] Update wpshadow_save_user_tip_prefs() to use manager
  - [ ] Verify no syntax errors

- [ ] **Task 5 (90 min):** Migrate 8 Workflow AJAX Handlers
  - [ ] Create: class-save-workflow-handler.php
  - [ ] Create: class-load-workflows-handler.php
  - [ ] Create: class-get-workflow-handler.php
  - [ ] Create: class-delete-workflow-handler.php
  - [ ] Create: class-toggle-workflow-handler.php
  - [ ] Create: class-generate-workflow-name-handler.php
  - [ ] Create: class-get-available-actions-handler.php
  - [ ] Create: class-get-action-config-handler.php
  - [ ] Add registration calls to wpshadow.php
  - [ ] Remove inline handlers from class-workflow-ajax.php
  - [ ] Verify all 8 handlers work via AJAX
  - [ ] Run full syntax check

---

## Code Quality Checks

After each task:
```bash
# Check syntax
php -l includes/core/class-*.php
php -l includes/admin/ajax/class-*.php

# Check for unused functions
grep -n "function wpshadow_hex_to_rgb" wpshadow.php
# Should be: No matches (if successfully replaced)
```

---

## Total Time Estimate

| Task | Time | Dependencies |
|------|------|--------------|
| Task 1: Color Utils | 20 min | None |
| Task 2: Theme Provider | 30 min | Task 1 (for reference) |
| Task 3: Tooltip Manager | 20 min | None |
| Task 4: User Preferences | 20 min | None |
| Task 5: Workflow Handlers | 90 min | All base classes ready |
| **Total** | **3 hours** | **Sequential** |

---

## Success Metrics

After Phase 4 completion:

**Code Quality:**
- ✅ Zero duplicate security checks in AJAX handlers
- ✅ Zero inline closures >30 lines
- ✅ All helper functions consolidated in utility classes
- ✅ All AJAX handlers in classes (25/25 migrated)

**Performance:**
- ✅ Tooltip loading uses persistent caching
- ✅ User preferences use centralized manager
- ✅ Color utilities ready for theme analysis optimization

**WordPress Standards:**
- ✅ All code follows WordPress coding standards
- ✅ All code has docblocks
- ✅ All code uses type hints
- ✅ Zero PHP warnings/notices

**Presentation Ready:**
- ✅ Can demonstrate architecture to senior developers
- ✅ Code tells story: Problem → Solution → Metrics
- ✅ Before/after comparisons show ~500 lines eliminated
- ✅ Performance improvements measurable

---

## Next: Phase 5 (Performance Optimization)

After Phase 4 is complete and tested:

- [ ] Implement Options_Loader class (batch option queries)
- [ ] Add transient caching for expensive operations
- [ ] Create Operation_Cache class
- [ ] Lazy-load diagnostics based on scan type
- [ ] Create Remote_Request_Manager with retry logic

---

## Notes for Implementation

1. **Always test in WordPress admin** after each migration
2. **Keep old functions temporarily** with deprecation notices (for safety)
3. **Check browser console** for JavaScript errors after AJAX migrations
4. **Verify multisite** if you have multisite setup
5. **Run `composer phpcs`** at end to catch style issues

---

## Estimated WordCamp Readiness: 95%

After completing Phase 4:
- ✅ Architecture clean & understandable
- ✅ No duplicate security patterns
- ✅ All AJAX handlers class-based
- ✅ Consolidation complete
- ⏳ Performance caching (Phase 5)
- ⏳ Settings API (Phase 6)

**Recommendation:** Complete Phase 4 today, Phase 5-6 can wait until day before presentation.
