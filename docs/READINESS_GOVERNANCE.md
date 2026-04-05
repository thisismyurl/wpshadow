# Readiness Registry & Governance System

## Overview

The **Readiness Registry** is a centralized lifecycle classification system that enables WPShadow to distinguish between production-ready, beta, and planned diagnostics and treatments. This ensures audit-ready governance, clear audit trails, and prevents incomplete/experimental code from silently executing in production environments.

## Core Concepts

### Lifecycle States

Each diagnostic and treatment has one of three **readiness states**:

| State | Description | Default Behavior | Typical Use |
|-------|-------------|------------------|------------|
| `production` | Fully tested, hardened, audit-ready | ✅ Included by default | Mature diagnostics/treatments |
| `beta` | Internally validated, user-facing | ❌ Hidden by default | New features, limited rollout |
| `planned` | Incomplete, placeholder, work-in-progress | ❌ Hidden by default | Roadmap items, sketches |

### Classification Methods

**Diagnostics use path-based classification:**
```
includes/diagnostics/tests/      → production
includes/diagnostics/verified/   → production
includes/diagnostics/help/       → beta
includes/diagnostics/todo/       → planned
```

**Treatments use reflection-based classification:**
- Has both `apply()` and `undo()` → `production`
- Has only `apply()` OR only `undo()` → `beta`  
- Has neither → `planned`

## API Reference

### Readiness_Registry Class

**Location:** `includes/systems/core/class-readiness-registry.php`

#### Constants

```php
Readiness_Registry::STATE_PRODUCTION  // 'production'
Readiness_Registry::STATE_BETA        // 'beta'
Readiness_Registry::STATE_PLANNED     // 'planned'
```

#### Methods

##### `get_inventory(): array`

Returns a complete machine-readable inventory of all diagnostics and treatments with their lifecycle states.

**Returns:**
```php
[
    'generated_at' => 1712145600,           // Unix timestamp
    'diagnostics' => [
        [
            'class'   => 'WPShadow\Diagnostics\Tests\Db_Prefix_Intentional',
            'file'    => 'includes/diagnostics/tests/class-db-prefix-intentional.php',
            'state'   => 'production',
            'enabled' => true,
        ],
    ],
    'treatments' => [
        [
            'class'      => 'WPShadow\Treatments\Treatment_Autosave',
            'file'       => 'includes/treatments/class-treatment-autosave-interval-optimized.php',
            'state'      => 'production',
            'executable' => true,
        ],
    ],
]
```

##### `get_diagnostic_state(string $class_name, string $file_path): string`

Resolves a diagnostic's lifecycle state using path-based classification.

**Parameters:**
- `$class_name`: Diagnostic class name (e.g., `WPShadow\Diagnostics\Tests\Db_Prefix`). Used for filter hooks.
- `$file_path`: Backing file path (e.g., `includes/diagnostics/tests/class-db-prefix.php`)

**Returns:** One of `production`, `beta`, `planned`

**Example:**
```php
$state = Readiness_Registry::get_diagnostic_state(
    'WPShadow\Diagnostics\Tests\Db_Prefix',
    'includes/diagnostics/tests/class-db-prefix.php'
);
// Returns: 'production'
```

**Filter Hook:** `wpshadow_diagnostic_readiness_state`

##### `get_treatment_state(string $class_name): string`

Resolves a treatment's lifecycle state using reflection-based classification.

**Parameters:**
- `$class_name`: Treatment class name (must be instantiable)

**Returns:** One of `production`, `beta`, `planned`

**Example:**
```php
$state = Readiness_Registry::get_treatment_state(
    'WPShadow\Treatments\Treatment_Autosave'
);
// Returns: 'production' if class has both apply() and undo()
```

**Filter Hook:** `wpshadow_treatment_readiness_state`

### Diagnostic_Registry Enhancements

**Location:** `includes/systems/diagnostics/class-diagnostic-registry.php`

#### Key Methods

##### `get_diagnostic_definitions(): array`

Returns discovery-filtered diagnostic list (respects readiness state filters).

**Returns:** Array of diagnostic definition arrays, each with `readiness` field included.

**Respects Filters:**
- `wpshadow_include_beta_diagnostics` (bool) — Include beta diagnostics
- `wpshadow_include_planned_diagnostics` (bool) — Include planned diagnostics
- `wpshadow_allowed_diagnostic_readiness_states` (array) — Full override of allowed states

##### `discover_diagnostics(): array`

Discovers diagnostics from filesystem and **automatically filters by readiness** (production-only by default).

**Respects Filters:** Same as above

### Treatment_Registry Enhancements

**Location:** `includes/systems/treatments/class-treatment-registry.php`

#### Key Methods

##### `is_treatment_ready(string $class_name): bool`

Checks if a treatment is ready for execution (now uses Readiness_Registry state gating).

**Returns:** `true` if treatment is in allowed readiness states, `false` otherwise

**Respects Filters:**
- `wpshadow_include_beta_treatments` (bool) — Include beta treatments
- `wpshadow_include_planned_treatments` (bool) — Include planned treatments
- `wpshadow_allowed_treatment_readiness_states` (array) — Full override
- `wpshadow_treatment_ready` (bool) — Final override for legacy compatibility

## Filter Hooks Reference

### Diagnostic Readiness Hooks

#### `wpshadow_include_beta_diagnostics`

**Type:** `apply_filters( 'wpshadow_include_beta_diagnostics', false )`

**Default:** `false`

**Returns:** Boolean

**Purpose:** Enable/disable inclusion of beta diagnostics in discovery and listing.

**Example:**
```php
// Enable beta diagnostics globally
add_filter( 'wpshadow_include_beta_diagnostics', '__return_true' );

// Enable beta diagnostics for specific users only
add_filter( 'wpshadow_include_beta_diagnostics', function() {
    return current_user_can( 'manage_options' ) && defined( 'WP_DEBUG' );
} );
```

#### `wpshadow_include_planned_diagnostics`

**Type:** `apply_filters( 'wpshadow_include_planned_diagnostics', false )`

**Default:** `false`

**Returns:** Boolean

**Purpose:** Enable/disable inclusion of planned diagnostics in discovery and listing.

**Example:**
```php
// Enable planned diagnostics in development environment
add_filter( 'wpshadow_include_planned_diagnostics', function() {
    return defined( 'WP_DEBUG' ) && WP_DEBUG;
} );
```

#### `wpshadow_allowed_diagnostic_readiness_states`

**Type:** `apply_filters( 'wpshadow_allowed_diagnostic_readiness_states', ['production'], ... )`

**Default:** `['production']`

**Returns:** Array of allowed states (each item: `production`, `beta`, or `planned`)

**Purpose:** Override the complete list of allowed readiness states for diagnostics.

**Example:**
```php
// Allow all states
add_filter( 'wpshadow_allowed_diagnostic_readiness_states', function( $default ) {
    return array( 'production', 'beta', 'planned' );
} );

// Custom policy: production + beta for staging, production-only for live
add_filter( 'wpshadow_allowed_diagnostic_readiness_states', function( $default ) {
    if ( defined( 'WP_ENVIRONMENT_TYPE' ) && 'staging' === WP_ENVIRONMENT_TYPE ) {
        return array( 'production', 'beta' );
    }
    return $default;
} );
```

#### `wpshadow_diagnostic_readiness_state`

**Type:** `apply_filters( 'wpshadow_diagnostic_readiness_state', $state, $class_name, $file_path )`

**Parameters:**
- `$state` (string): Computed state from path-based classification
- `$class_name` (string): Diagnostic class name
- `$file_path` (string): File path

**Returns:** Resolved state (one of `production`, `beta`, `planned`)

**Purpose:** Override readiness state for specific diagnostics.

**Example:**
```php
// Force a diagnostic to beta even though it's in production path
add_filter( 'wpshadow_diagnostic_readiness_state', function( $state, $class, $path ) {
    if ( 'WPShadow\Diagnostics\Tests\New_Feature' === $class ) {
        return 'beta'; // Override to beta
    }
    return $state;
}, 10, 3 );
```

### Treatment Readiness Hooks

#### `wpshadow_include_beta_treatments`

**Type:** `apply_filters( 'wpshadow_include_beta_treatments', false )`

**Default:** `false`

**Returns:** Boolean

**Purpose:** Enable/disable inclusion of beta treatments in execution.

#### `wpshadow_include_planned_treatments`

**Type:** `apply_filters( 'wpshadow_include_planned_treatments', false )`

**Default:** `false`

**Returns:** Boolean

**Purpose:** Enable/disable inclusion of planned treatments in execution.

#### `wpshadow_allowed_treatment_readiness_states`

**Type:** `apply_filters( 'wpshadow_allowed_treatment_readiness_states', ['production'], ... )`

**Default:** `['production']`

**Returns:** Array of allowed states

**Purpose:** Override the complete list of allowed readiness states for treatments.

**Example:**
```php
// Allow beta treatments on staging
add_filter( 'wpshadow_allowed_treatment_readiness_states', function( $default ) {
    if ( get_option( 'staging_mode' ) ) {
        return array( 'production', 'beta' );
    }
    return $default;
} );
```

#### `wpshadow_treatment_readiness_state`

**Type:** `apply_filters( 'wpshadow_treatment_readiness_state', $state, $class_name )`

**Parameters:**
- `$state` (string): Computed state from reflection-based classification
- `$class_name` (string): Treatment class name

**Returns:** Resolved state

**Purpose:** Override readiness state for specific treatments.

#### `wpshadow_treatment_ready` (Legacy Compatibility)

**Type:** `apply_filters( 'wpshadow_treatment_ready', true, $class_name )`

**Default:** `true`

**Returns:** Boolean

**Purpose:** Final override for treatment executability (maintained for backward compatibility).

## AJAX Endpoints

### `wpshadow_readiness_inventory`

**Action:** `wp_ajax_wpshadow_readiness_inventory`

**Required Capability:** `manage_options`

**Nonce:** `wpshadow_scan_settings`

**Response:**
```json
{
    "success": true,
    "data": {
        "summary": {
            "diagnostics": {
                "production": 45,
                "beta": 3,
                "planned": 1,
                "total": 49
            },
            "treatments": {
                "production": 28,
                "beta": 0,
                "planned": 0,
                "total": 28
            }
        },
        "inventory": {
            "generated_at": 1712145600,
            "diagnostics": [...],
            "treatments": [...]
        }
    }
}
```

### `wpshadow_export_readiness_inventory`

**Action:** `wp_ajax_wpshadow_export_readiness_inventory`

**Required Capability:** `manage_options`

**Nonce:** `wpshadow_scan_settings`

**Parameters:**
- `format` (string, optional): Export format (`json` or `csv`, default: `json`)

**Response:** Binary download with content-type header

**Example Usage:**
```php
// Export as JSON
fetch(ajaxurl, {
    method: 'POST',
    body: new FormData(Object.assign(document.createElement('form'), {
        elements: [
            { name: 'action', value: 'wpshadow_export_readiness_inventory' },
            { name: 'format', value: 'json' },
            { name: 'nonce', value: wpshadowDashboardData.scan_settings_nonce }
        ]
    }))
}).then(r => r.blob()).then(blob => {
    // Download blob
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'readiness-inventory.json';
    link.click();
});
```

## Usage Examples

### Example 1: Enable Beta Diagnostics in Development

```php
// In a custom plugin or mu-plugin
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    add_filter( 'wpshadow_include_beta_diagnostics', '__return_true' );
}
```

### Example 2: Custom Per-Environment Policy

```php
add_filter( 'wpshadow_allowed_diagnostic_readiness_states', function( $default ) {
    $env = wp_get_environment_type();
    
    switch ( $env ) {
        case 'production':
            return [ 'production' ];
        case 'staging':
            return [ 'production', 'beta' ];
        case 'local':
            return [ 'production', 'beta', 'planned' ];
        default:
            return $default;
    }
}, 10, 1 );
```

### Example 3: Override Specific Diagnostic State

```php
add_filter( 'wpshadow_diagnostic_readiness_state', function( $state, $class, $path ) {
    $overrides = array(
        'WPShadow\Diagnostics\Tests\New_Feature' => 'beta',
        'WPShadow\Diagnostics\Tests\Experimental' => 'planned',
    );
    
    return $overrides[ $class ] ?? $state;
}, 10, 3 );
```

### Example 4: Read Readiness Inventory Programmatically

```php
// Get inventory for custom reporting
$inventory = \WPShadow\Core\Readiness_Registry::get_inventory();

// Count by state
$prod_count = count( array_filter( $inventory['diagnostics'], fn( $d ) => 'production' === ( $d['state'] ?? 'production' ) ) );
$beta_count = count( array_filter( $inventory['diagnostics'], fn( $d ) => 'beta' === $d['state'] ) );
$planned_count = count( array_filter( $inventory['diagnostics'], fn( $d ) => 'planned' === $d['state'] ) );

echo "Diagnostics: {$prod_count} production, {$beta_count} beta, {$planned_count} planned";
```

### Example 5: Implement Custom Governance Policy

```php
/**
 * Custom governance policy: 
 * - Production: All users, all environments
 * - Beta: Only admins on staging/local
 * - Planned: Only on local with WP_DEBUG
 */
add_filter( 'wpshadow_allowed_diagnostic_readiness_states', function( $default ) {
    $states = [ 'production' ];
    
    $env = wp_get_environment_type();
    $is_admin = current_user_can( 'manage_options' );
    $is_debug = defined( 'WP_DEBUG' ) && WP_DEBUG;
    
    if ( $is_admin && in_array( $env, [ 'staging', 'local' ], true ) ) {
        $states[] = 'beta';
    }
    
    if ( $is_debug && 'local' === $env ) {
        $states[] = 'planned';
    }
    
    return $states;
}, 10, 1 );
```

## Audit & Compliance

### JSON Export for Audit Trail

The governance report in Settings > Diagnostics allows exporting a complete readiness inventory as JSON:

```json
{
  "generated_at": 1712145600,
  "diagnostics": [
    {
      "class": "WPShadow\\Diagnostics\\Tests\\Db_Prefix_Intentional",
      "file": "includes/diagnostics/tests/class-db-prefix-intentional.php",
      "state": "production",
      "enabled": true
    }
  ],
  "treatments": [...]
}
```

**Use Cases:**
- Compliance snapshots before/after applying treatments
- Audit trails for security reviews
- Version control of lifecycle states
- Automated policy enforcement checks

### CSV Export for Analysis

The governance report also supports CSV export with columns:
- **Type**: `Diagnostic` or `Treatment`
- **Name/Class**: Full class name
- **Readiness**: `production`, `beta`, or `planned`
- **Enabled/Executable**: `Yes` or `No`
- **File/Path**: Source file path

**Use Cases:**
- Spreadsheet analysis and reporting
- Integration with external audit tools
- Compliance documentation
- Change tracking in version control

## Testing

The readiness system includes comprehensive diagnostic tests:

1. **Readiness_Registry_Test** — Validates state resolution (path-based and reflection-based)
2. **Readiness_Filtering_Test** — Validates filtering in discovery endpoints
3. **Readiness_Export_Format_Test** — Validates JSON and CSV export integrity

**Access:** Settings > Diagnostics tab → Run All Tests (under "Governance" family)

## Architecture Notes

- **Centralized:** All readiness state resolution goes through `Readiness_Registry`
- **Filtered:** Discovery endpoints automatically apply readiness filtering (no manual checks needed)
- **Hookable:** All classification logic can be overridden via filter hooks
- **Non-Breaking:** Existing code continues to work; readiness is additive, not subtractive
- **Audit-Ready:** Full inventory exportable as JSON/CSV with timestamps

## Troubleshooting

### Q: Beta diagnostics are missing from list
**A:** Beta diagnostics are hidden by default. Enable via filter:
```php
add_filter( 'wpshadow_include_beta_diagnostics', '__return_true' );
```

### Q: A diagnostic is marked as planned but I want production
**A:** Override via filter:
```php
add_filter( 'wpshadow_diagnostic_readiness_state', function( $state, $class ) {
    if ( 'MyPlugin\MyDiagnostic' === $class ) {
        return 'production';
    }
    return $state;
}, 10, 2 );
```

### Q: How do I check readiness programmatically?
**A:** Use the registry directly:
```php
$state = \WPShadow\Core\Readiness_Registry::get_diagnostic_state(
    'MyClass',
    'path/to/file.php'
);
```

### Q: Can I fetch inventory via AJAX?
**A:** Yes, use the `wpshadow_readiness_inventory` action with `manage_options` cap and nonce.
