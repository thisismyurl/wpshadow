# Workflow Discovery System

## Overview

The Workflow Builder includes an automatic discovery system that scans the `includes/diagnostics/` and `includes/treatments/` directories to dynamically register new triggers and actions without manual configuration.

This allows new diagnostic checks and treatment fixes to be automatically added to the Workflow Builder when their class files are created.

## How It Works

### Automatic Scanning

The `Workflow_Discovery` class scans for:

- **Diagnostics** (triggers): All `class-diagnostic-*.php` files in `includes/diagnostics/`
- **Treatments** (actions): All `class-treatment-*.php` files in `includes/treatments/`

### File Structure Requirements

For diagnostics to be discovered, the class must:
1. Extend `WPShadow\Core\Diagnostic_Base`
2. Have static properties: `$slug`, `$title`, `$description`
3. Implement the `check()` method

Example:
```php
<?php
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;

class Diagnostic_My_Check extends Diagnostic_Base {
    protected static $slug = 'my-check';
    protected static $title = 'My Check Title';
    protected static $description = 'Description of what this checks';
    
    public static function check(): ?array {
        // Your diagnostic logic here
    }
}
```

For treatments to be discovered, the class must:
1. Implement `WPShadow\Core\Treatment_Interface`
2. Have a `get_finding_id()` method that returns a string
3. Implement `apply()` and `undo()` methods

Example:
```php
<?php
namespace WPShadow\Treatments;
use WPShadow\Core\Treatment_Interface;

class Treatment_My_Fix implements Treatment_Interface {
    public static function get_finding_id(): string {
        return 'my-fix';
    }
    
    public static function apply(): array {
        // Your treatment logic here
    }
    
    public static function undo(): array {
        // Undo logic here
    }
}
```

## Usage

### Get All Discovered Diagnostics
```php
$diagnostics = Workflow_Discovery::discover_diagnostics();
// Returns array keyed by slug with metadata for each diagnostic
```

### Get All Discovered Treatments
```php
$treatments = Workflow_Discovery::discover_treatments();
// Returns array keyed by slug with metadata for each treatment
```

### Get Specific Item
```php
$diagnostic = Workflow_Discovery::get_diagnostic( 'my-check' );
$treatment = Workflow_Discovery::get_treatment( 'my-fix' );
```

## Cache Management

The discovery system uses caching to avoid repeated file scanning.

### Clear Cache Programmatically
```php
// When new files are added
Workflow_Discovery::clear_cache();
Workflow_Wizard::refresh_discovery_cache();
```

### Refresh via Hook (for external systems)
```php
// Call this when files are synced/updated
do_action( 'wpshadow_refresh_workflow_discovery' );
```

## Integration Points

### For External File Sync Bots

When your bot adds new diagnostic or treatment files:

```php
// In your bot's code
do_action( 'wpshadow_refresh_workflow_discovery' );
```

This clears the cache and re-scans the directories.

### Getting Discovery Status

```php
use WPShadow\Workflow\Workflow_Discovery_Hooks;

$status = Workflow_Discovery_Hooks::get_discovery_status();
// Returns:
// [
//     'diagnostics_count' => 15,
//     'treatments_count' => 12,
//     'last_refreshed' => 1234567890
// ]
```

### Listening to Discovery Events

```php
add_action( 'wpshadow_discovery_refreshed', function( $data ) {
    error_log( 'Discovery refreshed: ' . count( $data['diagnostics'] ) . ' diagnostics' );
    // Pro version can react here
}, 10, 1 );
```

## Extensibility (Pro Version)

The discovery system is designed to support the Pro version's extension of single actions to multiple actions per trigger:

### Current Behavior (Free)
- One action per trigger (enforced in action-selection.php)
- All discovered treatments shown as selectable actions

### Pro Behavior (Future)
- Multiple actions per trigger
- Conditional logic between actions
- Advanced scheduling
- Custom treatment sequences

The discovery system will automatically include Pro treatments alongside free treatments when both are present.

## Performance Considerations

- **Caching**: Discovery results are cached during request to avoid repeated scanning
- **File System**: Scans only happen on explicit calls or hourly auto-check
- **Memory**: Loaded only when workflow builder is accessed
- **Transients**: Uses WordPress transients for persistent cross-request caching

## Troubleshooting

### New treatments don't appear in workflow builder

1. Check file naming: Must be `class-treatment-*.php` or `class-diagnostic-*.php`
2. Verify class namespace and structure match requirements above
3. Clear cache: `do_action( 'wpshadow_refresh_workflow_discovery' );`
4. Check error logs for parsing errors

### Cache not clearing

Verify the `wpshadow_refresh_workflow_discovery` hook is being called by your bot. Check WordPress debug log for confirmation.

## Development

When adding new discoveries system features, keep in mind:

1. **Metadata Extraction**: Update `extract_diagnostic_data()` and `extract_treatment_data()` if class structure changes
2. **New Properties**: Add new static properties to extraction regex patterns
3. **Pro Integration**: Use hooks to allow Pro modules to extend discovery
4. **Backward Compatibility**: New fields should be optional with sensible defaults
