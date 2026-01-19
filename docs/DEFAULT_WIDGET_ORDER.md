# Default Widget Order Configuration

## Overview
This document describes the default widget order for the WPShadow dashboard tabs, specifically the Features tab.

## Features Tab Default Order

When users haven't customized the widget layout, the Features tab displays widgets in the following default order:

### Left Column (Normal Context)
1. **Available Features** - Browse and search all available features
2. **Feature Settings** - Configure selected feature options (when a feature is selected)
3. **Scheduled Activity** - View scheduled tasks and automated actions
4. **Activity History** - Track all feature activity and changes

### Right Column (Side Context)
1. **Quick Links** - Quick access to common actions and navigation
2. **Feature Information** - Details about the selected feature
3. **System Health** - WordPress system health status

## Widget IDs

### Left Column Widgets
- `wpshadow_features_list` - Available Features
- `wpshadow_feature_settings` - Feature Settings
- `wpshadow_features_scheduled_activity` - Scheduled Activity
- `wpshadow_features_activity_history` - Activity History

### Right Column Widgets
- `wpshadow_features_quick_links` - Quick Links
- `wpshadow_features_info` - Feature Information
- `wpshadow_features_system_health` - System Health

## Implementation

### Layout Definition
The default order is defined in `includes/admin/class-wps-dashboard-layout.php`:
```php
public static function get_features_default_order(): array {
    return array(
        'normal' => array(  // Left column
            'wpshadow_features_list',
            'wpshadow_feature_settings',
            'wpshadow_features_scheduled_activity',
            'wpshadow_features_activity_history',
        ),
        'side'   => array(  // Right column
            'wpshadow_features_quick_links',
            'wpshadow_features_info',
            'wpshadow_features_system_health',
        ),
    );
}
```

### Widget Registration
Widgets are registered in `includes/views/dashboard-renderer.php` in the `wpshadow_register_features_metaboxes()` function.

## User Customization

Users can customize the widget order by:
1. Dragging widgets to reorder them
2. Hiding/showing widgets via screen options
3. Using WordPress meta box preferences

The customized layout is saved to WordPress options and persists across sessions.

## Related Files
- `includes/admin/class-wps-dashboard-layout.php` - Layout management
- `includes/views/dashboard-renderer.php` - Widget registration
- `includes/admin/screens.php` - Screen setup
