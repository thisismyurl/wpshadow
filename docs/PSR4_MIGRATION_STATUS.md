# PSR-4 Migration Status

**Last Updated:** January 26, 2026  
**Version:** 1.2601.210247

## Current State

### ✅ Phase 1: COMPLETE - Autoloader Enabled
- Composer autoloader loaded in `wpshadow.php`
- Coexists with manual `require_once` statements
- No breaking changes
- Site fully functional

### ✅ Phase 2: COMPLETE - AJAX Handlers Renamed
- All 72 AJAX handler files renamed from WordPress style to PSR-4 style
  - Before: `class-consent-preferences-handler.php`
  - After: `Consent_Preferences_Handler.php`
- Updated `ajax-handlers-loader.php` with new filenames
- Updated `kanban-module.php` references
- Still using manual loading (required until Phase 3)

### ⏸️ Phase 3: BLOCKED - Directory Structure
**Problem:** PSR-4 autoloading requires case-sensitive directory names matching namespaces

**Current Structure:**
```
includes/admin/ajax/           (lowercase)
includes/diagnostics/          (lowercase)
includes/treatments/           (lowercase)
```

**Required for PSR-4:**
```
includes/Admin/Ajax/           (capitalized)
includes/Diagnostics/          (capitalized)
includes/Treatments/           (capitalized)
```

**Namespace Examples:**
- `WPShadow\Admin\Ajax\Consent_Preferences_Handler` requires `includes/Admin/Ajax/Consent_Preferences_Handler.php`
- `WPShadow\Diagnostics\Diagnostic_Memory_Limit` requires `includes/Diagnostics/Diagnostic_Memory_Limit.php`

## Blockers

1. **Case-Sensitive Paths:** Linux servers require exact case match
2. **1,347 Files:** Large-scale rename operation
3. **External References:** Third-party code may reference old paths
4. **Git History:** Renames will affect blame/history

## Options Going Forward

### Option A: Complete PSR-4 Migration (Recommended for Long-Term)
**Steps:**
1. Rename all directories to match namespace capitalization
2. Rename all files to match class names
3. Remove manual `require_once` statements
4. Test thoroughly

**Impact:**
- 100+ directory renames
- 1,347 file renames
- Potential compatibility issues
- Cleaner architecture long-term

### Option B: Hybrid Approach (Current State)
**Keep:**
- Composer autoloader enabled
- Manual loading for existing files
- WordPress-style filenames

**Benefits:**
- No breaking changes
- Works now
- Can migrate incrementally

**Drawbacks:**
- Manual maintenance required
- Not fully PSR-4 compliant

### Option C: Custom Autoloader
**Create:**
- Custom autoloader that maps WordPress-style names to class names
- Coexists with Composer autoloader

**Example:**
```php
spl_autoload_register(function ($class) {
    if (strpos($class, 'WPShadow\\') !== 0) return;
    
    $file = str_replace('WPShadow\\', 'includes/', $class);
    $file = strtolower(str_replace('_', '-', $file));
    $file = str_replace('\\', '/', $file);
    $file = WPSHADOW_PATH . 'class-' . $file . '.php';
    
    if (file_exists($file)) require_once $file;
});
```

## Recommendation

**Continue with Option B (Hybrid) until:**
1. Major version bump (2.0.0)
2. Full testing environment available
3. Can allocate time for complete migration

**Current Status:** Phase 2 complete, AJAX handlers PSR-4 ready but still manually loaded.

## Files Modified (Phase 1 & 2)

- `wpshadow.php` - Enabled Composer autoloader
- `includes/admin/ajax/*.php` - 72 files renamed to PSR-4 format
- `includes/admin/ajax/ajax-handlers-loader.php` - Updated with new filenames
- `includes/kanban/kanban-module.php` - Updated handler references

## Next Steps (When Ready for Phase 3)

1. Backup entire codebase
2. Create directory rename script
3. Test on staging environment
4. Execute directory renames with Git tracking
5. Update all manual requires
6. Remove ajax-handlers-loader.php
7. Test all functionality
8. Deploy with version bump to 2.0.0
