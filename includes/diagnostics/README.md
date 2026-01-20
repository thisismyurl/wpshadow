# WPShadow Diagnostics

This folder contains individual diagnostic check classes for the WPShadow health monitoring system.

## Structure

Each diagnostic is a separate class that follows a consistent pattern:

```php
namespace WPShadow\Diagnostics;

class Diagnostic_Example {
    public static function check() {
        // Return array with finding data, or null if no issue found
        return array(
            'id'           => 'unique-id',
            'title'        => 'Finding Title',
            'description'  => 'Detailed description...',
            'color'        => '#hex',
            'bg_color'     => '#hex',
            'kb_link'      => 'https://...',
            'auto_fixable' => true/false,
            'threat_level' => 0-100,
        );
    }
}
```

## Current Diagnostics

1. **Diagnostic_Memory_Limit** - Checks PHP memory configuration
2. **Diagnostic_Backup** - Detects backup plugin presence
3. **Diagnostic_Permalinks** - Validates permalink structure
4. **Diagnostic_Tagline** - Checks for site tagline/description
5. **Diagnostic_SSL** - Verifies HTTPS/SSL configuration
6. **Diagnostic_Outdated_Plugins** - Finds plugins needing updates
7. **Diagnostic_Debug_Mode** - Checks if debug mode is enabled
8. **Diagnostic_WordPress_Version** - Detects outdated WordPress core
9. **Diagnostic_Plugin_Count** - Monitors excessive plugin count

## Registry System

The `Diagnostic_Registry` class manages all diagnostics:

- **init()** - Loads all diagnostic classes
- **run_all_checks()** - Executes all diagnostics and returns findings
- **register()** - Add custom diagnostic
- **unregister()** - Remove diagnostic

## Adding New Diagnostics

1. Create new file: `class-diagnostic-your-check.php`
2. Implement `check()` method returning array or null
3. Register in `Diagnostic_Registry::$diagnostics` array

Example:

```php
<?php
namespace WPShadow\Diagnostics;

class Diagnostic_Custom_Check {
    public static function check() {
        if ( /* some condition */ ) {
            return array(
                'id'           => 'custom-check',
                'title'        => 'Custom Issue Detected',
                'description'  => 'Description of the issue',
                'color'        => '#2196f3',
                'bg_color'     => '#e3f2fd',
                'kb_link'      => 'https://wpshadow.com/kb/custom/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=custom',
                'auto_fixable' => false,
                'threat_level' => 50,
            );
        }
        return null;
    }
}
```

Then add `'Diagnostic_Custom_Check'` to the `$diagnostics` array in the registry.

## Field Reference

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `id` | string | Yes | Unique identifier for the finding |
| `title` | string | Yes | Display title for the issue |
| `description` | string | Yes | Detailed explanation |
| `color` | string | No | Text color (hex) |
| `bg_color` | string | No | Background color (hex) |
| `kb_link` | string | No | Knowledge base article URL |
| `action_link` | string | No | Direct action URL (admin page) |
| `action_text` | string | No | Action button text |
| `secondary_action_link` | string | No | Second button URL |
| `secondary_action_text` | string | No | Second button text |
| `modal_trigger` | string | No | Modal ID to open on click |
| `auto_fixable` | bool | No | Can this be auto-fixed? |
| `threat_level` | int | Yes | 0-100 threat severity |

## Threat Level Guidelines

- **90-100**: Critical security/functionality issues (SSL, major vulnerabilities)
- **70-89**: High priority (outdated software, backup missing)
- **50-69**: Medium priority (memory limits, debug mode)
- **30-49**: Low priority (plugin count, performance optimization)
- **0-29**: Informational (permalinks, tagline)

## Best Practices

1. Keep diagnostics focused on single checks
2. Return `null` when no issue is found (not empty array)
3. Include UTM parameters in all KB links
4. Set realistic threat levels
5. Only mark as `auto_fixable` if truly safe to auto-fix
6. Write clear, actionable descriptions
7. Use consistent color coding
