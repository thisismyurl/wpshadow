# WPShadow Plugin Split - Implementation Strategy

## Approach: Minimal Refactor with Maximum Compatibility

### Core Strategy
Rather than moving features around, we'll use a **feature registration hook** system where:

1. **wpshadow.php** remains the main plugin file with:
   - All core infrastructure (dashboard, settings, capabilities, etc.)
   - **Only free features (L1-L2)** loaded and required
   - Hook system for Pro to extend

2. **wpshadow-pro.php** is a lightweight extension that:
   - Checks for wpshadow.php active, fails gracefully if not
   - Loads only **paid features (L3-L5)**
   - Requires license verification for activation
   - Hooks into existing dashboard/admin/API systems

---

## Phase 1: Update wpshadow.php (Core Plugin)

### Tasks:
1. **Update plugin header** to clarify this is the "Core" version
2. **Remove paid feature requires** (28 features to delete from requires)
3. **Remove paid feature registrations** from WPSHADOW_register_core_features()
4. **Create Pro registration hook**: `wpshadow_pro_register_features`
5. **Create Pro-only paths** in code where needed

### Changes in wpshadow.php:

#### 1. Plugin Header Update
- Change Name: "WPShadow" → "WPShadow Core" or "WPShadow"
- Add: "Pro upgrades available"
- Change Description to focus on free features

#### 2. Feature Requires (DELETE these 27 files):
```php
// PAID FEATURES - Moved to wpshadow-pro.php
// L3+: asset-minification, brute-force-protection, cdn-integration, etc.
```

#### 3. Feature Registration (KEEP only 29 free features):
```php
// Keep in WPSHADOW_register_core_features():
- All L1 features (28)
- L2: a11y-audit

// Remove from WPSHADOW_register_core_features():
- All L3+ features (27)
```

#### 4. Add Pro Hook After Free Features Registered:
```php
// After free features, allow Pro plugin to register its features
do_action( 'wpshadow_pro_register_features' );
```

---

## Phase 2: Create wpshadow-pro.php

### File Structure:
```
/wpshadow-pro.php (main plugin file, ~200 lines)
/pro/
  ├── features/ (symlink or copy of paid feature files)
  ├── admin/ (Pro-specific admin classes if needed)
  └── includes/ (Pro-specific helpers)
```

### wpshadow-pro.php Content:

```php
<?php
/**
 * Plugin Name:         WPShadow Pro
 * Plugin URI:          https://wpshadow.com/
 * Description:         Professional features for WPShadow
 * Version:             1.2601.75000
 * Requires Plugin:     wpshadow  (requires wpshadow.php active)
 * Requires at least:   6.4
 * Requires PHP:        8.1.29
 * License:             GPL2
 * @package WPShadow\Pro
 */

declare(strict_types=1);

namespace WPShadow\Pro;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =====================================================================
// STEP 1: Verify wpshadow.php is active
// =====================================================================
if ( ! defined( 'WPSHADOW_PATH' ) || ! is_plugin_active( 'wpshadow/wpshadow.php' ) ) {
    add_action( 'admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo 'WPShadow Pro requires WPShadow Core plugin to be active.';
        echo '</p></div>';
    });
    return; // Exit early, don't load Pro
}

// =====================================================================
// STEP 2: Define Pro constants
// =====================================================================
define( 'WPSHADOW_PRO_VERSION', '1.2601.75000' );
define( 'WPSHADOW_PRO_FILE', __FILE__ );
define( 'WPSHADOW_PRO_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPSHADOW_PRO_URL', plugin_dir_url( __FILE__ ) );
define( 'WPSHADOW_PRO_BASENAME', plugin_basename( __FILE__ ) );

// =====================================================================
// STEP 3: Load Pro feature files on appropriate hook
// =====================================================================
add_action( 'wpshadow_pro_register_features', __NAMESPACE__ . '\\load_pro_features', 10 );

/**
 * Load all Pro feature files
 */
function load_pro_features(): void {
    // Load License Level 3 features
    require_once WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-asset-minification.php';
    require_once WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-brute-force-protection.php';
    // ... etc
    
    // Register Pro features
    register_WPSHADOW_feature( new \\WPShadow\\CoreSupport\\WPSHADOW_Feature_Asset_Minification() );
    register_WPSHADOW_feature( new \\WPShadow\\CoreSupport\\WPSHADOW_Feature_Brute_Force_Protection() );
    // ... etc
}

// =====================================================================
// STEP 4: License verification (Pro-specific)
// =====================================================================
add_action( 'admin_init', __NAMESPACE__ . '\\verify_pro_license', 5 );

/**
 * Verify Pro license is valid
 */
function verify_pro_license(): void {
    // Check if license is valid
    // If not, disable Pro features
    // Show admin notice
}

// =====================================================================
// STEP 5: Initialize Pro
// =====================================================================
do_action( 'wpshadow_pro_loaded' );
```

---

## Phase 3: File Structure Changes

### Option A: Keep Paid Features in wpshadow/ (Recommended)
```
wpshadow/
├── wpshadow.php (loads free features only)
├── wpshadow-pro.php (requires free features, loads paid)
├── includes/features/
│   ├── [29 free features]
│   ├── [27 paid features] ← Both plugins reference these
```

**Pros**: 
- No file duplication
- Single source of truth
- Easiest to maintain

**Cons**:
- Free users see Pro code in repo (minor)

### Option B: Copy Paid Features to pro/ folder
```
wpshadow/
├── wpshadow.php (loads free only)
├── wpshadow-pro.php (loads pro features)
├── includes/features/
│   └── [29 free features]
├── pro/
│   └── features/
│       └── [27 paid features] ← Copied
```

**Pros**:
- Clear separation
- Pro can be distributed separately

**Cons**:
- Code duplication
- Maintenance overhead

---

## Implementation Steps

### Step 1: Backup Current State
```bash
git checkout -b feature/plugin-split
git commit -m "Backup: Full plugin before split"
```

### Step 2: Remove Paid Features from wpshadow.php
- Delete 27 paid feature `require_once` statements (lines ~740-830)
- Delete 27 paid feature registrations from `WPSHADOW_register_core_features()`
- Delete 27 import statements at top of file

### Step 3: Add Pro Registration Hook
- After `WPSHADOW_register_core_features()` completes, add:
  ```php
  do_action( 'wpshadow_pro_register_features' );
  ```

### Step 4: Create wpshadow-pro.php
- Use template above
- Import all 27 paid feature classes
- Register them in `load_pro_features()` via hook

### Step 5: Create/Update .gitignore
- If distributing Pro separately:
  - Create separate `wpshadow-pro` repo
  - Or create `/pro` folder as separate module

### Step 6: Test Scenarios
1. ✅ Free plugin runs alone
2. ✅ Pro plugin won't load without free
3. ✅ Pro plugin loads paid features
4. ✅ Dashboard shows all features correctly
5. ✅ License system works

---

## Hook Reference for Pro Integration

### Core Provides These Hooks:

**Action: `wpshadow_pro_register_features`**
- Fired after all free features registered
- Pro loads paid features here
- Priority: 10 (default)
- Args: none

**Filter: `wpshadow_dashboard_sections`**
- Allows Pro to add dashboard sections
- Returns array of sections

**Action: `wpshadow_pro_loaded`**
- Fired after wpshadow-pro.php initializes
- For future extensions

### Pro License Check:

**Option 1: Check on each feature use**
```php
if ( ! WPSHADOW_Pro_License::is_valid() ) {
    return false; // feature disabled
}
```

**Option 2: Check on plugin activation**
```php
register_activation_hook( __FILE__, function() {
    if ( ! WPSHADOW_Pro_License::is_valid() ) {
        deactivate_plugins( WPSHADOW_PRO_BASENAME );
        wp_die( 'Invalid license' );
    }
});
```

---

## File Changes Summary

### wpshadow.php:
- **Deletions**: 27 feature requires + 27 feature registrations + 27 imports = ~300 lines
- **Additions**: 1 hook call (`do_action( 'wpshadow_pro_register_features' )`)
- **Net change**: -299 lines (cleaner!)

### wpshadow-pro.php (NEW):
- **Size**: ~200 lines
- **Contains**: Dependency check + feature requires + feature registrations + license check

### Total:
- **wpshadow.php**: 2824 → 2525 lines (~11% smaller)
- **wpshadow-pro.php**: 200 lines (new)
- **Total plugin code**: 2725 lines (better organized)

---

## Version Compatibility

Both plugins ship at **v1.2601.75000** initially

### Pro's `Requires Plugin` Header
```php
Requires Plugin: wpshadow
```

This is the standard WordPress way to declare plugin dependencies (WP 6.0+).

---

## Next Steps

Ready to proceed with:
1. ✅ Phase 1: Clean up wpshadow.php (remove paid features)
2. ✅ Phase 2: Create wpshadow-pro.php
3. ✅ Phase 3: Test all scenarios
4. ✅ Phase 4: Documentation

Shall I proceed?
