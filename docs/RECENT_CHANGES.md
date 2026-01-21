# Recent Changes - January 2026

**Last Updated:** January 21, 2026  
**Version:** 1.2601.2112

## Summary

Recent enhancements focused on security diagnostics, automated fixes, KB URL standardization, and comprehensive documentation updates.

---

## New Features

### 1. Post via Email Security Diagnostics

**Problem:** Post via Email feature can be a security vulnerability if misconfigured.

**Solution:** Added two separate security checks (as per user request):

#### `Diagnostic_Post_Via_Email`
- **File:** `includes/diagnostics/class-diagnostic-post-via-email.php`
- **Threat Level:** 16 (high)
- **Severity:** high
- **Checks:** Is Post via Email enabled?
- **Returns:** Finding when `mailserver_url` option is set
- **Auto-Fixable:** Via file editors treatment
- **KB Link:** `https://wpshadow.com/kb/settings-post-via-email-security`

#### `Diagnostic_Post_Via_Email_Category`
- **File:** `includes/diagnostics/class-diagnostic-post-via-email-category.php`
- **Threat Level:** 12 (medium)
- **Severity:** medium
- **Checks:** Is default post category "Uncategorized" (ID: 1)?
- **Returns:** Finding when using default category AND Post via Email enabled
- **Auto-Fixable:** Yes
- **KB Link:** `https://wpshadow.com/kb/settings-uncategorized-routing`

**Registration:** Both diagnostics registered in `Diagnostic_Registry` and included in quick scan.

---

### 2. File Editors Auto-Fix Treatment

**Problem:** Theme/plugin file editors are disabled but not auto-fixable.

**Solution:** Created `Treatment_File_Editors` to safely disable editors.

#### `Treatment_File_Editors`
- **File:** `includes/treatments/class-treatment-file-editors.php`
- **Namespace:** `WPShadow\Treatments`
- **Implements:** `Interface_Treatment`
- **Linked Diagnostic:** `Diagnostic_Initial_Setup` (file-editors finding)
- **Auto-Applicable:** Yes

**Implementation:**
```php
public function apply(): bool {
    // 1. Create wp-config.php backup (.bak)
    $backup = $this->create_wp_config_backup();
    
    // 2. Add/update DISALLOW_FILE_EDIT constant
    $result = $this->update_wp_config_constant(
        'DISALLOW_FILE_EDIT',
        true
    );
    
    // 3. Log KPI metrics (15 min saved)
    if ( $result ) {
        $this->log_kpi( 'file-editors-disabled', 15 );
    }
    
    return $result;
}

public function undo(): bool {
    // Restore wp-config.php from .bak backup
    return $this->restore_wp_config_backup();
}
```

**Registration:** Registered in `Treatment_Registry` and linked to `file-editors` finding ID.

---

### 3. KB URL Standardization

**Old Format:** `https://wpshadow.com/docs/wordpress-basics/{article}`
**New Format:** `https://wpshadow.com/kb/{context}-{slug}`

**Changes:**
- All 8 tooltip JSON files updated
- 100+ KB URLs migrated
- Context detection from page selectors and field IDs
- Slug generation from tooltip titles

**Example Conversions:**

| Old URL | New URL | Context | Slug |
|---------|---------|---------|------|
| `/docs/wordpress-basics/dashboard` | `/kb/navigation-dashboard` | navigation | dashboard |
| `/docs/general-settings` | `/kb/settings-general-site-title` | settings | site-title |
| `/docs/user-password` | `/kb/user-new-user-password` | user-new | user-password |
| `/docs/profile-personal` | `/kb/profile-personal-options` | profile | personal-options |

**Files Updated:**
- `includes/data/tooltips.json` (general)
- `includes/data/tooltips-settings.json`
- `includes/data/tooltips-people.json`
- `includes/data/tooltips-navigation.json`
- `includes/data/tooltips-content.json`
- `includes/data/tooltips-design.json`
- `includes/data/tooltips-extensions.json`
- `includes/data/tooltips-maintenance.json`

---

### 4. Tooltip Enhancements

**Disabled Pages:** Tooltips no longer show on:
- `plugins.php` - Plugin list (no need for ? help)
- `edit.php` - Posts list (confusing with post interface)
- `edit-comments.php` - Comments list (redundant)
- Admin bar (filtered selectors starting with `#wp-admin-bar-`)

**Implementation:** [wpshadow.php](wpshadow.php#L1853-L1881)

**New Tooltip:** "Send User Notification" on `wp-admin/user-new.php`
- File: `includes/data/tooltips-people.json`
- Explains notification behavior when creating new users
- Links to KB: `/kb/user-new-send-notification`

**Updated Tooltip:** Settings menu tooltip
- Explains WPShadow Settings page access
- Links to KB: `/kb/settings-wpshadow-settings`

---

## Version Updates

### Plugin Version

**Old:** `0.0.1`  
**New:** `1.2601.2112`

**Format:** `1.YYMM.DDHH`
- `1` - Plugin version (always 1)
- `26` - Year (2 digits, 2026)
- `01` - Month (2 digits, January)
- `20` - Day (2 digits, 20th)
- `12` - Hour (24-hour, 12:00 PM)

**Updated In:**
- [wpshadow.php](wpshadow.php#L5) (header)
- [wpshadow.php](wpshadow.php#L13) (constant)
- [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md#L10)
- [docs/SYSTEM_OVERVIEW.md](docs/SYSTEM_OVERVIEW.md#L9)
- [docs/CODING_STANDARDS.md](docs/CODING_STANDARDS.md#L14)

---

## Documentation Updates

### Critical Documentation Rewritten

1. **[ARCHITECTURE.md](docs/ARCHITECTURE.md)** - Completely rewritten
   - Updated from old class patterns to `WPShadow\` namespaces
   - Accurate directory structure (no more fictional `features/` folder)
   - Documented all 6 core systems
   - 500+ lines of current information

2. **[SYSTEM_OVERVIEW.md](docs/SYSTEM_OVERVIEW.md)** - Feature counts corrected
   - Diagnostics: 9 → 57
   - Treatments: 2 → 44
   - Updated examples

3. **[CODING_STANDARDS.md](docs/CODING_STANDARDS.md)** - Namespace patterns updated
   - Classes: Old `WPSHADOW_Style` → New `Noun_Style` with namespaces
   - Namespaces: Documented actual structure
   - Version: Updated to 1.2601.2112

### Feature Matrices Created

1. **[FEATURE_MATRIX_DIAGNOSTICS.md](docs/FEATURE_MATRIX_DIAGNOSTICS.md)** - NEW
   - All 57 diagnostics listed with details
   - Organized by category (security, performance, config, etc.)
   - Includes threat levels and auto-fixable status
   - Recent additions documented

2. **[FEATURE_MATRIX_TREATMENTS.md](docs/FEATURE_MATRIX_TREATMENTS.md)** - NEW
   - All 44 treatments listed with details
   - Implementation pattern shown
   - Diagnostic ↔ Treatment pairs documented
   - KPI tracking explained

### Enhanced Guides

1. **[TOOLTIP_QUICK_REFERENCE.md](docs/TOOLTIP_QUICK_REFERENCE.md)** - KB URL format updated
   - Old format: `/docs/wordpress-basics/...`
   - New format: `/kb/{context}-{slug}`
   - Examples provided

2. **[WORKFLOW_BUILDER.md](docs/WORKFLOW_BUILDER.md)** - File references corrected
   - Updated actual workflow system files
   - Clarified system components

3. **[DASHBOARD_LAYOUT_GUIDE.md](docs/DASHBOARD_LAYOUT_GUIDE.md)** - Accuracy clarified
   - Added notes about implementation status
   - Core sections documented as active

---

## Migration Guide

### For Developers

**Old Diagnostic/Treatment Pattern:**
```php
class WPSHADOW_Diagnostic_Example { ... }
// file: includes/diagnostics/class-wps-diagnostic-example.php
```

**New Pattern:**
```php
namespace WPShadow\Diagnostics;
class Diagnostic_Example { ... }
// file: includes/diagnostics/class-diagnostic-example.php
```

**Update Steps:**
1. Change class name from `WPSHADOW_` to namespace-based
2. Move file to appropriate namespace folder
3. Update file naming to `class-{name}.php`
4. Update imports: `use WPShadow\Diagnostics\Diagnostic_Example`

### For KB Links

**Old Format:**
```
https://wpshadow.com/docs/wordpress-basics/security-headers
```

**New Format:**
```
https://wpshadow.com/kb/settings-security-headers
// or
https://wpshadow.com/kb/user-new-user-password
// depends on context: settings, user-new, profile, etc.
```

**Context Mapping:**
- Settings pages → `settings`
- User creation → `user-new`
- User profile → `profile`
- Navigation → `navigation`
- Content management → `content`
- Design/appearance → `design`
- Plugins/extensions → `extensions`
- Maintenance → `maintenance`

---

## Diagnostic Registry Updates

**Quick Scan Now Includes:**
- ✅ Initial Setup checks (includes file-editors check)
- ✅ Post via Email security check (NEW)
- ✅ Post via Email category check (NEW)
- ✅ 35+ other critical diagnostics

**Full Scan Includes:**
- ✅ All 57 diagnostics
- ✅ Performance optimization checks
- ✅ Code quality checks
- ✅ WordPress configuration validation

---

## Treatment Registry Updates

**Auto-Fixable Treatments:**
- ✅ File Editors (NEW)
- ✅ Memory Limit
- ✅ SSL Configuration
- ✅ Debug Mode
- ✅ 40+ other treatments

**All treatments:**
- Create backup before applying
- Fully reversible via `undo()`
- Log KPI metrics automatically
- One-at-a-time execution for safety

---

## Testing Recommendations

### For New Features

1. **Post via Email Diagnostics**
   - Test with/without Post via Email enabled
   - Verify category check works correctly
   - Confirm quick scan detects both

2. **File Editors Treatment**
   - Verify wp-config.php backup created
   - Test undo() restores correctly
   - Confirm KPI metrics logged

3. **KB URL Format**
   - Verify all tooltips link correctly
   - Check excluded pages don't show tooltips
   - Confirm new tooltip appears on user-new.php

### For Documentation

1. Run through ARCHITECTURE.md - verify examples match actual code
2. Check all links in feature matrices
3. Verify KB URLs are accessible
4. Test workflow examples in WORKFLOW_BUILDER.md

---

## Backward Compatibility

✅ All changes are backward compatible:
- Old diagnostic/treatment code still works
- New namespace imports available alongside old
- KB URL change is frontend-only
- Version format change is display-only

---

## Performance Impact

**None anticipated:**
- New diagnostics use same efficient checking patterns
- File editors treatment runs once, cached after
- KB URL format is lookup-time only
- Documentation changes are static

---

## Known Limitations

1. **Post via Email Treatment:** Currently only through file-editors treatment
2. **Kanban Board:** Still in development (documented as partial)
3. **Some workflow features:** May be phase-gated

---

## Next Steps

1. ✅ Version standardized to 1.YYMM.DDHH format
2. ✅ Post via Email security added
3. ✅ File Editors auto-fix implemented
4. ✅ KB URLs standardized
5. ✅ Documentation updated and verified
6. ⏳ Create pro addon documentation
7. ⏳ Add integration guide for custom diagnostics/treatments
8. ⏳ Performance benchmarks for diagnostics

---

*See [DOC_AUDIT_FINDINGS.md](DOC_AUDIT_FINDINGS.md) and [DOC_UPDATES_COMPLETED.md](DOC_UPDATES_COMPLETED.md) for previous updates.*
