# Feature Details System

## Overview

The Feature Details System replaces individual feature admin submenus with a unified, centralized feature management interface. Instead of each feature creating its own submenu page, features now register themselves in the **Quick Links** section of the dashboard widget, which directs users to a dedicated feature details page.

## Architecture

### Components

1. **WPSHADOW_Feature_Details_Page** (`includes/class-wps-feature-details-page.php`)
   - Central feature details page manager
   - Hidden admin page accessible via query parameters
   - Handles AJAX requests for toggling features and settings
   - Activity logging system

2. **Feature Abstract Class Extensions** (`includes/features/class-wps-feature-abstract.php`)
   - `has_details_page()` - Override to enable details page for feature
   - `log_activity()` - Log feature activity
   - `get_details_url()` - Get feature details page URL

3. **Dashboard Quick Actions Widget** (`includes/class-wps-dashboard-widgets.php`)
   - Updated to display Feature Quick Links section
   - Automatically lists all features with `has_details_page() === true`
   - Links directly to feature details pages

4. **Frontend Assets**
   - `assets/css/feature-details.css` - Styling for feature details page
   - `assets/js/feature-details.js` - Interactive toggle controls and AJAX handlers

## Feature Details Page Structure

The feature details page displays:

### 1. Feature Information Widget
- **Title and Icon** - Feature name with dashicon
- **Description** - Full feature description
- **Metadata Table** - Version, category, scope, license level
- **Main Toggle** - Enable/disable the entire feature
- **Sub-Features Section** - Individual settings with toggle switches (if applicable)

### 2. Activity Log Widget
- **Real-time Log** - Last 50 activity entries
- **Filterable Columns** - Timestamp, action, details
- **Log Levels** - Info, success, warning, error
- **Refresh Button** - Manual log refresh

## Usage Guide

### For Feature Developers

#### Step 1: Enable Details Page

Override `has_details_page()` in your feature class:

```php
class WPSHADOW_Feature_YourFeature extends WPSHADOW_Abstract_Feature {
    
    /**
     * Enable details page for this feature.
     */
    public function has_details_page(): bool {
        return true;
    }
}
```

#### Step 2: Remove Old Admin Menu Code

**Remove** any `add_submenu_page()` calls:

```php
// ❌ DELETE THIS:
public function add_admin_menu(): void {
    add_submenu_page(
        'wpshadow',
        __( 'My Feature', 'plugin-wpshadow' ),
        __( 'My Feature', 'plugin-wpshadow' ),
        'manage_options',
        'wpshadow-my-feature',
        array( $this, 'render_page' )
    );
}
```

**And** remove the hook registration:

```php
// ❌ DELETE THIS:
add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
```

#### Step 3: Define Sub-Features (Optional)

Add sub-features/settings in your constructor config:

```php
parent::__construct(
    array(
        'id'           => 'my-feature',
        'name'         => __( 'My Feature', 'plugin-wpshadow' ),
        'description'  => __( 'Does awesome things', 'plugin-wpshadow' ),
        // ... other config ...
        'sub_features' => array(
            'option_one'   => __( 'Enable Option One', 'plugin-wpshadow' ),
            'option_two'   => __( 'Enable Option Two', 'plugin-wpshadow' ),
            'advanced_mode' => __( 'Advanced Mode', 'plugin-wpshadow' ),
        ),
    )
);
```

These will automatically appear as toggle switches in the Feature Settings section.

#### Step 4: Log Activity

Use the new `log_activity()` method to track feature actions:

```php
// Log when feature performs an action
$this->log_activity(
    'cache_cleared',
    'Cache cleared successfully',
    'success'
);

// Log warnings
$this->log_activity(
    'api_timeout',
    'API request timed out after 30 seconds',
    'warning'
);

// Log errors
$this->log_activity(
    'database_error',
    'Failed to update database: ' . $error_message,
    'error'
);

// Log info
$this->log_activity(
    'cron_executed',
    'Scheduled task executed successfully',
    'info'
);
```

#### Step 5: Access Settings

Read sub-feature settings using standard methods:

```php
// Check if sub-feature is enabled
$option_enabled = get_option( 'wpshadow_my-feature_option_one', false );

// Or use the helper method
$option_enabled = $this->get_setting( 'option_one', false );
```

## Migration Checklist

For each feature that currently has an admin submenu:

- [ ] Override `has_details_page()` to return `true`
- [ ] Remove `add_admin_menu()` method
- [ ] Remove `add_action( 'admin_menu', ... )` hook registration
- [ ] Define `sub_features` array in constructor (if applicable)
- [ ] Replace custom admin page rendering with feature details page
- [ ] Add `log_activity()` calls throughout feature code
- [ ] Test feature toggle on/off functionality
- [ ] Test sub-feature toggle switches
- [ ] Verify activity log records actions correctly

## Features Currently Using Submenus

The following features need migration:

1. ✅ Accessibility Audit (`class-wps-feature-a11y-audit.php`)
2. ✅ Weekly Performance Report (`class-wps-feature-weekly-performance-report.php`)
3. ✅ Open Graph Previewer (`class-wps-feature-open-graph-previewer.php`)
4. ✅ Email Test (`class-wps-feature-email-test.php`)
5. ✅ Mobile Friendliness (`class-wps-feature-mobile-friendliness.php`)
6. ✅ PHP Info (`class-wps-feature-php-info.php`)
7. ✅ Cron Test (`class-wps-feature-cron-test.php`)
8. ✅ Color Contrast Checker (`class-wps-feature-color-contrast-checker.php`)
9. ✅ MySQL Diagnostics (`class-wps-feature-mysql-diagnostics.php`)
10. ✅ Brute Force Protection (`class-wps-feature-brute-force-protection.php`)
11. ✅ Smart Recommendations (`class-wps-feature-smart-recommendations.php`)
12. ✅ Troubleshooting Mode (`class-wps-feature-troubleshooting-mode.php`)
13. ✅ Loopback Test (`class-wps-feature-loopback-test.php`)
14. ✅ Broken Link Checker (`class-wps-feature-broken-link-checker.php`)
15. ✅ Conflict Sandbox (`class-wps-feature-conflict-sandbox.php`)

## API Reference

### WPSHADOW_Feature_Details_Page

#### Static Methods

```php
// Initialize system
WPSHADOW_Feature_Details_Page::init();

// Get feature details URL
$url = WPSHADOW_Feature_Details_Page::get_feature_url( 'my-feature' );

// Log activity
WPSHADOW_Feature_Details_Page::log_feature_activity(
    'my-feature',
    'action',
    'Message',
    'level'
);

// Get activity log
$log = WPSHADOW_Feature_Details_Page::get_feature_activity_log( 'my-feature', 50 );
```

### WPSHADOW_Abstract_Feature

#### New Methods

```php
// Check if feature has details page
public function has_details_page(): bool

// Log activity
protected function log_activity( string $action, string $message, string $level = 'info' ): bool

// Get feature details URL
public function get_details_url(): string
```

## AJAX Endpoints

### Toggle Feature

**Endpoint:** `wp_ajax_wpshadow_toggle_feature`

**Parameters:**
- `nonce` - Security nonce
- `feature_id` - Feature identifier
- `enabled` - Boolean ('true'/'false')

### Toggle Feature Setting

**Endpoint:** `wp_ajax_wpshadow_toggle_feature_setting`

**Parameters:**
- `nonce` - Security nonce
- `feature_id` - Feature identifier
- `setting_key` - Setting key
- `enabled` - Boolean ('true'/'false')

### Get Feature Log

**Endpoint:** `wp_ajax_wpshadow_get_feature_log`

**Parameters:**
- `nonce` - Security nonce
- `feature_id` - Feature identifier

## Benefits

### For Users
- ✅ **Centralized Management** - All feature controls in one place
- ✅ **Consistent Interface** - Same UI for all features
- ✅ **Quick Access** - Quick Links in dashboard widget
- ✅ **Activity Tracking** - See what features are doing
- ✅ **Granular Control** - Toggle individual settings per feature

### For Developers
- ✅ **Less Boilerplate** - No custom admin pages needed
- ✅ **Automatic UI** - Toggle switches generated automatically
- ✅ **Built-in Logging** - Activity logging out of the box
- ✅ **Easy Maintenance** - One system to update, not 66 features
- ✅ **Better UX** - Consistent patterns across all features

## Example Implementation

See `class-wps-feature-uptime-monitor.php` for a complete example:

```php
final class WPSHADOW_Feature_Uptime_Monitor extends WPSHADOW_Abstract_Feature {
    
    public function __construct() {
        parent::__construct(
            array(
                'id'           => 'uptime-monitor',
                'name'         => __( 'Uptime Monitoring', 'plugin-wpshadow' ),
                'description'  => __( 'Monitor site uptime...', 'plugin-wpshadow' ),
                'sub_features' => array(
                    'email_alerts' => __( 'Email Alerts', 'plugin-wpshadow' ),
                    'sms_alerts'   => __( 'SMS Alerts', 'plugin-wpshadow' ),
                    'daily_report' => __( 'Daily Reports', 'plugin-wpshadow' ),
                ),
            )
        );
    }
    
    public function has_details_page(): bool {
        return true;
    }
    
    public function check_uptime(): void {
        // ... check logic ...
        
        if ( $is_down ) {
            $this->log_activity(
                'site_down',
                'Site is unreachable',
                'error'
            );
        } else {
            $this->log_activity(
                'uptime_check',
                'Site is online',
                'success'
            );
        }
    }
}
```

## Troubleshooting

### Feature not appearing in Quick Links
- Ensure `has_details_page()` returns `true`
- Check that feature is registered in feature registry
- Verify feature class is loaded properly

### Toggles not working
- Check browser console for JavaScript errors
- Verify nonce is valid
- Confirm AJAX endpoint is reachable
- Check user has `manage_options` capability

### Activity log not updating
- Ensure `log_activity()` is called correctly
- Check log level is valid (info, success, warning, error)
- Verify option table has space (check database size limits)

## Future Enhancements

- [ ] Export activity logs to CSV
- [ ] Filter logs by date range
- [ ] Search functionality in logs
- [ ] Feature presets/profiles
- [ ] Bulk feature enable/disable
- [ ] Feature dependencies visualization
- [ ] Performance metrics per feature
- [ ] Email digest of feature activities

---

**Version:** 1.0.0  
**Last Updated:** January 16, 2026  
**Maintained by:** WPShadow Development Team
