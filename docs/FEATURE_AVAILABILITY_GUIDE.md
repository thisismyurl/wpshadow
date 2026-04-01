# Feature Availability Checking - Implementation Guide

## Overview

The `Version_Checker` class provides a way to conditionally display features, cards, menu items, and links based on the plugin's current version. This prevents "coming soon" features from being displayed before their release.

**Current Version:** `0.6035.2150`
**Version Format:** `1.YDDD.HHMM` (major.yearday.hourmiunute)

## Quick Examples

### Example 1: Check If Feature Is Live (PHP)

```php
use WPShadow\Core\Version_Checker;

if ( Version_Checker::is_feature_live( 'WPShadow\Diagnostics\Diagnostic_Example' ) ) {
    echo 'This feature is available!';
}
```

### Example 2: Render Card Only If Live (Template)

```php
<?php
wpshadow_render_feature_card_if_live(
    'WPShadow\Features\Advanced_Analytics',
    'Advanced Analytics',
    'Detailed performance metrics and trends',
    'dashicons-chart-line',
    'admin.php?page=wpshadow-analytics',
    'View Analytics'
);
?>
```

### Example 3: Conditional Menu Links

```php
<?php
wpshadow_render_menu_link_if_live(
    'WPShadow\Admin\Advanced_Reports_Page',
    'Advanced Reports',
    'admin.php?page=wpshadow-advanced-reports',
    '<span class="dashicons dashicons-chart-bar"></span>',
    true  // Show "coming soon" if not live
);
?>
```

### Example 4: Filter Array of Features

```php
use WPShadow\Core\Version_Checker;

$all_features = [
    'WPShadow\Diagnostics\Diagnostic_Example',
    'WPShadow\Features\Future_Feature',
    'WPShadow\Treatment\Example_Treatment',
];

$live_features = Version_Checker::filter_live_features( $all_features );
// Only live features in current version
```

## API Reference

### Version_Checker Class

#### `is_feature_live( string $class_name ): bool`

Checks if a feature is available in the current version.

**Parameters:**
- `$class_name` (string): Full class name (e.g., `'WPShadow\Diagnostics\Diagnostic_Example'`)

**Returns:** `true` if available, `false` if coming soon

**Example:**
```php
if ( Version_Checker::is_feature_live( 'WPShadow\Treatments\Treatment_Advanced_Backup' ) ) {
    // Display the feature
}
```

#### `get_feature_since( string $class_name ): string`

Gets the `@since` version tag from a feature's docblock.

**Parameters:**
- `$class_name` (string): Full class name

**Returns:** Version string (e.g., `'0.6040.1500'`) or empty string

**Example:**
```php
$since = Version_Checker::get_feature_since( 'WPShadow\Feature' );
echo "Available since v" . $since;
```

#### `are_all_features_live( array $class_names ): bool`

Checks if multiple features are all currently available.

**Parameters:**
- `$class_names` (array): Array of class names

**Returns:** `true` only if ALL features are live

**Example:**
```php
if ( Version_Checker::are_all_features_live( [
    'WPShadow\Feature1',
    'WPShadow\Feature2',
] ) ) {
    // All features available
}
```

#### `filter_live_features( array $class_names ): array`

Returns only the currently available features from an array.

**Parameters:**
- `$class_names` (array): Array of class names

**Returns:** Filtered array of only live features

**Example:**
```php
$available = Version_Checker::filter_live_features( $all_features );
foreach ( $available as $feature ) {
    // Render available feature
}
```

### Template Helper Functions

#### `wpshadow_render_feature_card_if_live()`

Renders a feature card only if the feature is live. Shows disabled placeholder if coming soon.

**Parameters:**
- `$class_name` (string): Class name to check
- `$title` (string): Card title
- `$description` (string): Card description
- `$icon` (string): Icon class (e.g., `'dashicons-admin-tools'`)
- `$button_url` (string, optional): Button link URL
- `$button_text` (string, optional): Button text (default: "Learn More")
- `$content_callback` (callable, optional): Callback to render custom card body

**Example:**
```php
wpshadow_render_feature_card_if_live(
    'WPShadow\Features\ML_Optimization',
    'ML-Powered Optimization',
    'AI-powered performance recommendations',
    'dashicons-lightbulb',
    'https://wpshadow.com/ml-optimizer',
    'Learn More'
);
```

#### `wpshadow_render_coming_soon_card()`

Renders a "coming soon" placeholder card for future features.

**Parameters:**
- `$title` (string): Card title
- `$class_name` (string, optional): Class name (to show version)

**Example:**
```php
wpshadow_render_coming_soon_card( 'Advanced Analytics', 'WPShadow\Features\Analytics' );
```

#### `wpshadow_render_menu_link_if_live()`

Renders a menu link only if feature is live.

**Parameters:**
- `$class_name` (string): Class to check
- `$label` (string): Menu label
- `$url` (string, optional): Menu URL
- `$icon` (string, optional): Icon HTML
- `$show_coming_soon` (bool, optional): Show grayed-out link if not live

**Example:**
```php
wpshadow_render_menu_link_if_live(
    'WPShadow\Admin\AdvancedSettings',
    'Advanced Settings',
    'admin.php?page=wpshadow-advanced-settings',
    '<span class="dashicons dashicons-admin-tools"></span>',
    true
);
```

#### `wpshadow_is_feature_live()`

Simple boolean check in templates (wrapper for PHP code).

**Parameters:**
- `$class_name` (string): Class to check

**Returns:** `true/false`

**Example:**
```php
<?php if ( wpshadow_is_feature_live( 'WPShadow\Feature' ) ) : ?>
    <p>Feature is available!</p>
<?php endif; ?>
```

## Practical Examples

### Dashboard Card Grid

```php
<div class="wps-grid wps-grid-auto-320">
    <?php
    wpshadow_render_feature_card_if_live(
        'WPShadow\Diagnostics\Security_Audit',
        'Security Audit',
        'Comprehensive security analysis',
        'dashicons-shield-alt',
        'admin.php?page=wpshadow-security'
    );

    wpshadow_render_feature_card_if_live(
        'WPShadow\Diagnostics\Performance_Profile',
        'Performance Profiler',
        'Identify performance bottlenecks',
        'dashicons-chart-line',
        'admin.php?page=wpshadow-performance'
    );
    ?>
</div>
```

### Admin Menu

```php
<?php if ( wpshadow_is_feature_live( 'WPShadow\Admin\AdvancedMenu' ) ) : ?>
    <li>
        <a href="admin.php?page=wpshadow-advanced">
            Advanced Tools
        </a>
    </li>
<?php else : ?>
    <li class="disabled">
        Advanced Tools <small>(v1.6050.0000)</small>
    </li>
<?php endif; ?>
```

### Settings Page with Feature Flags

```php
<?php
$features = [
    'WPShadow\Features\CloudSync',
    'WPShadow\Features\AIOptimization',
    'WPShadow\Features\AutoHealing',
];

$available = Version_Checker::filter_live_features( $features );

foreach ( $available as $feature ) : ?>
    <div class="setting">
        <!-- Render setting for available feature -->
    </div>
<?php endforeach; ?>
```

### Conditional Content

```php
<?php
if ( Version_Checker::is_feature_live( 'WPShadow\Reports\AdvancedAnalytics' ) ) :
    // Show advanced reports UI
else :
    // Show basic reports UI
endif;
?>
```

## Adding @since Tags

When creating new features, always add an `@since` tag to the class docblock:

```php
<?php
/**
 * Advanced Analytics Feature
 *
 * Provides detailed performance metrics and trend analysis.
 *
 * @package    WPShadow
 * @subpackage Features
 * @since      0.6050.1200  // Future version - won't display until released
 */
class Advanced_Analytics {
    // Implementation
}
```

## Version Format Explained

WPShadow uses this semantic version format:

```
1.YDDD.HHMM
```

- **1** = Major version (always 1)
- **YDDD** = Last digit of year + Julian day
  - `6035` = 2026, day 35 (early February)
  - `6050` = 2026, day 50 (mid-February)
- **HHMM** = Hour and minute in 24-hour format
  - `2150` = 21:50 (9:50 PM)
  - `0000` = 00:00 (Midnight)

Examples:
- `0.6030.0000` = Early 2026, midnight
- `0.6050.1200` = Mid 2026, noon
- `0.6100.2359` = Late 2026, 11:59 PM

## Testing

```php
// Enable/disable a feature for testing
\WPShadow\Core\Version_Checker::clear_cache();

// Mock a future version
$reflection = new ReflectionProperty( '\WPShadow\Core\Version_Checker', 'current_version' );
$reflection->setAccessible( true );
$reflection->setValue( null, '0.6000.0000' );
```

## Best Practices

1. **Always check before rendering** - Don't display cards or links for unavailable features
2. **Use @since tags consistently** - Every new class should have a `@since` tag
3. **Cache checks** - Version_Checker internally caches results for performance
4. **Provide feedback** - Show "coming soon" badges when appropriate
5. **Test during development** - Test your features with different version numbers

## Common Mistakes

❌ Don't forget to add `@since` tags
```php
// BAD - no @since tag
class My_Feature {
```

✅ Always add @since tags
```php
// GOOD
/**
 * My Feature
 * @since 0.6050.1500
 */
class My_Feature {
```

❌ Don't hardcode version checks
```php
// BAD
if ( WPSHADOW_VERSION === '0.6035.2150' ) { /* ... */ }
```

✅ Use Version_Checker
```php
// GOOD
if ( Version_Checker::is_feature_live( 'WPShadow\Feature' ) ) { /* ... */ }
```

## Integration Points

The Version_Checker integrates with:

- **Guardian Dashboard** - Conditionally show diagnostic cards
- **Settings Pages** - Show/hide setting options
- **Academy UI** - Display available training materials
- **Admin Menu** - Conditionally register menu items
- **Reports Page** - Show available report types
- **Treatment Cards** - Hide treatments not yet released

---

**Documentation Generated:** February 6, 2026
**For Version:** 0.6035.2150 and later
