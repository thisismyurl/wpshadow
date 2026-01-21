# WPShadow Treatments

This folder contains treatment/fix implementations that address detected problems.

## What is a Treatment?

A treatment is a solution to a specific finding detected by a diagnostic. Treatments handle the actual fixing of issues - they modify configurations, update options, or write files to resolve problems.

## Structure

Each treatment is a separate class implementing `Treatment_Interface`:

```php
namespace WPShadow\Treatments;

class Treatment_Example implements Treatment_Interface {
    public static function get_finding_id() {
        return 'example-finding-id';
    }
    
    public static function can_apply() {
        // Check if prerequisites are met
        return true;
    }
    
    public static function apply() {
        // Apply the fix
        KPI_Tracker::log_fix_applied( 'example-finding-id', 'auto' );
        return array( 'success' => true, 'message' => 'Fixed!' );
    }
    
    public static function undo() {
        // Revert the fix if possible
        return array( 'success' => true, 'message' => 'Undone!' );
    }
}
```

## Current Treatments

1. **Treatment_Permalinks** - Sets SEO-friendly permalink structure
   - Finding: `permalinks-plain`
   - Action: Updates `permalink_structure` option to `/%postname%/`
   - Undo: Restores previous structure
   - Safe: Yes (database option only)

2. **Treatment_Memory_Limit** - Increases PHP memory limit
   - Finding: `memory-limit-low`
   - Action: Modifies wp-config.php to set `WP_MEMORY_LIMIT`
   - Undo: Restores from backup
   - Safe: Yes (creates backup before writing)

## Registry System

The `Treatment_Registry` class manages all treatments:

- **init()** - Loads all treatment classes
- **get_treatment( finding_id )** - Get treatment for a finding
- **apply_treatment( finding_id )** - Apply a treatment
- **register( class_name )** - Register new treatment
- **unregister( class_name )** - Remove treatment

## KPI Tracking

All treatments must log their execution for metrics:

```php
KPI_Tracker::log_fix_applied( $finding_id, $method );
```

Supported methods:
- `'auto'` - Automated fix by WPShadow Guardian
- `'manual'` - User manually applied fix
- `'user'` - User triggered the fix
- `'api'` - Applied via API/external system

## Safety Guidelines

### Before Applying a Fix

1. **Validate Prerequisites** - Use `can_apply()` to ensure all conditions are met
2. **Create Backups** - Always save current state for undo
3. **Check Permissions** - Verify user has capability to apply fix
4. **Verify Nonce** - Ensure AJAX request is legitimate

### During Fix Application

1. **Use Transactions** - For multi-step fixes, use transactions if possible
2. **Validate Results** - Verify the fix actually worked
3. **Log Changes** - Document what was changed
4. **Handle Errors** - Catch exceptions and return meaningful errors

### After Fix Application

1. **Verify Success** - Run diagnostic again to confirm
2. **Log Metrics** - Track the fix for KPI purposes
3. **Notify User** - Provide clear success/failure message
4. **Test Side Effects** - Ensure fix doesn't break other things

## Field Reference

### Treatment_Interface Methods

| Method | Return | Purpose |
|--------|--------|---------|
| `get_finding_id()` | string | Unique finding ID this treats |
| `can_apply()` | bool | Can this treatment run now? |
| `apply()` | array | Execute the fix, return result |
| `undo()` | array | Revert the fix if possible |

### Result Array

Both `apply()` and `undo()` return:

```php
array(
    'success' => true/false,
    'message' => 'User-friendly message',
    'data'    => array() // optional additional data
)
```

## Treatment Types

### Database Changes
- Use WordPress `update_option()`, `add_option()` functions
- Always provide undo functionality
- Safe for auto-fix (reversible)

```php
public static function apply() {
    $old = get_option( 'my_option' );
    update_option( 'wpshadow_prev_my_option', $old );
    update_option( 'my_option', 'new_value' );
    return array( 'success' => true, 'message' => '...' );
}

public static function undo() {
    $old = get_option( 'wpshadow_prev_my_option' );
    update_option( 'my_option', $old );
    return array( 'success' => true, 'message' => '...' );
}
```

### File Modifications
- Always create backups first
- Use `file_put_contents()` with careful validation
- Provide undo via backup restoration
- Generally require extra user caution

```php
public static function apply() {
    $file = ABSPATH . 'some-file.php';
    copy( $file, $file . '.bak' );
    file_put_contents( $file, $new_content );
    return array( 'success' => true, 'message' => '...' );
}
```

### Admin-Only Changes
- Things requiring network-level permissions
- Plugin activation/deactivation
- User role modifications
- Require explicit approval

### Read-Only Treatments
- Some findings might not have direct treatments
- They require user action (e.g., "install backup plugin")
- Set `Treatment_Interface::can_apply()` to return false
- Guide user to manual solution

## Adding New Treatments

1. Create file: `class-treatment-YOUR-NAME.php`
2. Implement `Treatment_Interface`
3. Add class name to `Treatment_Registry::$treatments` array
4. Update diagnostics to reference finding ID
5. Test apply and undo thoroughly
6. Document in this README

## Best Practices

1. **Single Responsibility** - One treatment per finding
2. **Idempotent** - Safe to apply multiple times
3. **Testable** - Easy to verify success
4. **Documented** - Clear description of changes
5. **Backups** - Always enable undo
6. **User-Friendly** - Clear messages and explanations
7. **KPI Tracked** - Log all fixes for metrics
8. **Safe by Default** - Never auto-apply dangerous fixes
