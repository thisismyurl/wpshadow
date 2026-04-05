# Readiness System Quick Reference

## For Plugin Developers

### Import & Use

```php
use WPShadow\Core\Readiness_Registry;

// Check diagnostic state
$state = Readiness_Registry::get_diagnostic_state(
    'WPShadow\Diagnostics\Tests\My_Diagnostic',
    'includes/diagnostics/tests/class-my-diagnostic.php'
);

// Check treatment state  
$state = Readiness_Registry::get_treatment_state(
    'WPShadow\Treatments\My_Treatment'
);

// Get full inventory
$inventory = Readiness_Registry::get_inventory();
```

### Filter Hooks (Copy-Paste)

#### Enable Beta in Development

```php
add_filter( 'wpshadow_include_beta_diagnostics', function() {
    return defined( 'WP_DEBUG' ) && WP_DEBUG;
} );
```

#### Custom Environment Policy

```php
add_filter( 'wpshadow_allowed_diagnostic_readiness_states', function( $default ) {
    $env = wp_get_environment_type();
    if ( 'staging' === $env ) {
        return array( 'production', 'beta' );
    }
    return $default;
} );
```

#### Override Specific Diagnostic

```php
add_filter( 'wpshadow_diagnostic_readiness_state', function( $state, $class, $path ) {
    if ( 'WPShadow\Diagnostics\Tests\My_Class' === $class ) {
        return 'beta'; // or 'planned'
    }
    return $state;
}, 10, 3 );
```

### State Constants

```php
Readiness_Registry::STATE_PRODUCTION  // 'production'
Readiness_Registry::STATE_BETA        // 'beta'
Readiness_Registry::STATE_PLANNED     // 'planned'
```

## For User Admins

### Enable Beta Diagnostics

**Option 1: Via Filter (in mu-plugin)**
```php
// wp-content/mu-plugins/enable-beta.php
add_filter( 'wpshadow_include_beta_diagnostics', '__return_true' );
```

**Option 2: Via Settings UI**
- Go to Settings > Diagnostics
- Click "Beta" tab
- Diagnostics with beta readiness will appear

### Export Audit Trail

**Via Settings UI:**
1. Settings > Diagnostics (scroll down)
2. "Governance & Compliance Report" section
3. Click "Export as JSON" or "Export as CSV"
4. Save timestamped file for audit documentation

**Via AJAX (Programmatic):**
```javascript
fetch(ajaxurl, {
    method: 'POST',
    body: new FormData(
        Object.assign(document.createElement('form'), {
            elements: [
                { name: 'action', value: 'wpshadow_export_readiness_inventory' },
                { name: 'format', value: 'json' },
                { name: 'nonce', value: wpshadowDashboardData.scan_settings_nonce }
            ]
        })
    )
}).then(r => r.blob()).then(blob => {
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'readiness-' + new Date().toISOString() + '.json';
    link.click();
});
```

### Run Readiness Tests

**Via Dashboard:**
1. WPShadow Dashboard > Run All Tests
2. Tests include: Readiness_Registry_Test, Readiness_Filtering_Test, Readiness_Export_Format_Test
3. Green ✓ = All readiness logic working correctly

## Readiness State Reference

| State | Path (Diagnostics) | Code (Treatments) | Default | Typical Case |
|-------|-------|-------|---------|---|
| **Production** | `/tests/` or `/verified/` | both `apply()` + `undo()` | ✅ Included | Ready for all environments |
| **Beta** | `/help/` | `apply()` OR `undo()` only | ❌ Hidden | New features, limited testing |
| **Planned** | `/todo/` | neither method | ❌ Hidden | Roadmap items, sketches |

## Filter Hooks Quick Table

| Hook | Type | Default | Use Case |
|------|------|---------|----------|
| `wpshadow_include_beta_diagnostics` | bool | `false` | Enable beta diagnostics |
| `wpshadow_include_planned_diagnostics` | bool | `false` | Enable planned diagnostics |
| `wpshadow_allowed_diagnostic_readiness_states` | array | `['production']` | Override all allowed states |
| `wpshadow_diagnostic_readiness_state` | string | computed | Override specific diagnostic |
| `wpshadow_include_beta_treatments` | bool | `false` | Enable beta treatments |
| `wpshadow_include_planned_treatments` | bool | `false` | Enable planned treatments |
| `wpshadow_allowed_treatment_readiness_states` | array | `['production']` | Override all allowed states |
| `wpshadow_treatment_readiness_state` | string | computed | Override specific treatment |
| `wpshadow_treatment_ready` | bool | `true` | Legacy compatibility override |

## Test Diagnostics

Three diagnostic tests validate the readiness system:

1. **Readiness_Registry_Test** — Run diagnostics page
   - Path-based classification
   - Reflection-based classification
   - Filter hook overrides
   - Inventory structure

2. **Readiness_Filtering_Test** — Run diagnostics page
   - Production-only filtering (default)
   - Beta/planned inclusion via filters
   - Custom allowed states
   - Readiness field in definitions

3. **Readiness_Export_Format_Test** — Run diagnostics page
   - JSON structure validation
   - CSV format validation
   - Timestamp validity
   - State constant values

## Common Tasks

### Task: Only allow production diagnostics on live site

```php
// wp-content/mu-plugins/governance-policy.php
add_filter( 'wpshadow_allowed_diagnostic_readiness_states', function() {
    return array( 'production' );
} );
```

### Task: Allow staging environment to test beta features

```php
add_filter( 'wpshadow_allowed_diagnostic_readiness_states', function() {
    if ( defined( 'WP_ENVIRONMENT_TYPE' ) && 'staging' === WP_ENVIRONMENT_TYPE ) {
        return array( 'production', 'beta' );
    }
    return array( 'production' );
} );
```

### Task: Import readiness from external governance tool

```php
add_filter( 'wpshadow_diagnostic_readiness_state', function( $state, $class, $path ) {
    // Query external tool
    $external_state = get_external_governance_state( $class );
    return $external_state ?: $state;
}, 10, 3 );
```

### Task: Generate compliance report

```php
$inventory = \WPShadow\Core\Readiness_Registry::get_inventory();
$json = wp_json_encode( $inventory, JSON_PRETTY_PRINT );
file_put_contents( WP_CONTENT_DIR . '/readiness-snapshot-' . time() . '.json', $json );
```

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Beta diagnostics don't show | Enable filter: `add_filter( 'wpshadow_include_beta_diagnostics', '__return_true' );` |
| Diagnostic marked as wrong state | Override via filter: `wpshadow_diagnostic_readiness_state` with correct state |
| Can't export inventory | Verify: have `manage_options` cap, check nonce, check browser console for JS errors |
| Tests are failing | Run individual test diagnostics from Settings > Diagnostics > Run All Tests |
| Filter not being applied | Verify hook name spelled correctly, priority is reasonable (10+), callback is callable |

## Documentation Links

- **Full Guide:** `docs/READINESS_GOVERNANCE.md`
- **API Reference:** `docs/READINESS_GOVERNANCE.md` → API Reference section
- **Code:** `includes/systems/core/class-readiness-registry.php`
- **Tests:** `includes/diagnostics/tests/class-readiness-*.php`

## Support

For issues or questions:
1. Check test diagnostics (Settings > Diagnostics > Run All Tests)
2. Review full documentation in `docs/READINESS_GOVERNANCE.md`
3. Check filter hook implementations in source code
4. Verify environment and capabilities via `wp_get_environment_type()` and current user caps
