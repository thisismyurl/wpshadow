# Debug Mode Toggles Feature

## Overview

The Debug Mode Toggles feature provides a user-friendly interface for enabling WordPress debug features without manually editing `wp-config.php`. It includes both backend logging (safe for production) and frontend display options (admin-only).

## Features

### Backend Logging (Safe for Production)
- **WP_DEBUG**: Enable error logging
- **WP_DEBUG_LOG**: Write errors to `debug.log`
- **SCRIPT_DEBUG**: Use unminified JavaScript and CSS
- **SAVEQUERIES**: Log database queries for analysis

### Frontend Display (Admin Only)
- **WP_DEBUG_DISPLAY**: Show errors on screen (admin only via cookie)
- **Query Information**: Display query count and execution time
- **Memory Usage**: Show peak memory usage
- **Floating Debug Bar**: Always-visible debug information bar at top of viewport

### Safety Features
- **Auto-disable**: All backend logging automatically disables after 1 hour
- **Cookie-based Access**: Frontend display only for administrators with valid cookie
- **Activity Logging**: All toggle changes are logged for audit trail
- **Countdown Timer**: Visual countdown showing time until auto-disable

### Error Log Management
- **Real-time Viewer**: View last 100 lines of `debug.log` in admin interface
- **Refresh**: Reload error log on demand
- **Clear Log**: One-click log file clearing with confirmation
- **File Size Display**: Shows current log file size

## Technical Implementation

### Configuration Management
- Debug settings stored in `wp_options` table (`wps_debug_settings`)
- Configuration file written to `wp-content/wps-debug-config.php`
- Auto-loader created at `wp-content/mu-plugins/wps-debug-loader.php`
- Constants set before other plugins load (via mu-plugin)

### Security
- All AJAX handlers protected by nonce verification
- Capability checks: `manage_options` required for all operations
- Cookie values use WordPress nonces for security
- File operations use phpcs-approved methods
- All output properly escaped (esc_html, esc_attr, esc_js, esc_url)

### User Interface
- Located at: **Support → Debug Tools**
- Modern toggle switches for all options
- Real-time updates via AJAX
- Visual feedback for all actions
- Responsive design for mobile devices

## File Structure

```
includes/
  class-wps-debug-mode.php        # Main debug mode manager class
  views/
    debug-tools.php               # Admin page template
  admin/
    assets.php                    # Asset registration (updated)

assets/
  css/
    debug-tools.css               # Additional styles
  js/
    debug-tools.js                # Additional scripts

wp-support-thisismyurl.php        # Main plugin file (updated to load debug mode)
```

## Usage

### Enabling Debug Mode

1. Navigate to **Support → Debug Tools** in WordPress admin
2. Toggle desired backend logging options
3. Optionally enable frontend display options
4. Configuration is applied immediately

### Viewing Error Log

1. Navigate to **Support → Debug Tools**
2. Scroll to "Error Log" section
3. Click "Refresh Log" to reload
4. Click "Clear Log" to empty the log file (with confirmation)

### Disabling Debug Mode

1. Manually toggle off all options, OR
2. Wait for 1-hour auto-disable timeout

## API Reference

### Class: WPS_Debug_Mode

#### Methods

##### `init(): void`
Initialize the debug mode manager. Hooks into WordPress actions.

##### `get_settings(): array`
Get current debug settings.

Returns:
```php
[
    'wp_debug' => bool,
    'wp_debug_log' => bool,
    'script_debug' => bool,
    'savequeries' => bool,
    'debug_display' => bool,
    'query_info' => bool,
    'memory_usage' => bool,
]
```

##### `update_settings(array $settings): bool`
Update debug settings and regenerate configuration file.

##### `check_auto_disable(): void`
Check for auto-disable timeout and disable if needed.

##### `render_error_bar(): void`
Render floating debug bar for admin users (frontend and backend).

### AJAX Actions

#### `wps_toggle_debug`
Toggle a specific debug setting.

**Parameters:**
- `nonce` (string): Security nonce
- `setting` (string): Setting key to toggle
- `value` (bool): New value

**Response:**
```json
{
    "success": true,
    "data": {
        "message": "Debug setting updated",
        "settings": { /* all settings */ }
    }
}
```

#### `wps_get_error_log`
Get last 100 lines of error log.

**Parameters:**
- `nonce` (string): Security nonce

**Response:**
```json
{
    "success": true,
    "data": {
        "content": "... log content ...",
        "size": "2.5 MB"
    }
}
```

#### `wps_clear_error_log`
Clear the error log file.

**Parameters:**
- `nonce` (string): Security nonce

**Response:**
```json
{
    "success": true,
    "data": {
        "message": "Error log cleared"
    }
}
```

## Testing

### Manual Testing Checklist

1. ✓ Install plugin and activate
2. ✓ Navigate to Support → Debug Tools
3. ✓ Toggle each backend logging option
4. ✓ Verify config file created at `wp-content/wps-debug-config.php`
5. ✓ Verify mu-plugin loader at `wp-content/mu-plugins/wps-debug-loader.php`
6. ✓ Toggle frontend display options
7. ✓ Verify floating debug bar appears
8. ✓ Create a test error and verify it appears in log viewer
9. ✓ Test refresh and clear log functionality
10. ✓ Verify countdown timer and auto-disable

### Code Quality

- ✓ All PHP files pass syntax check (`php -l`)
- ✓ All output properly escaped
- ✓ All AJAX handlers have nonce verification
- ✓ All actions require `manage_options` capability
- ✓ File operations use phpcs-approved methods
- ✓ Activity logging integration working

## Support

For issues or questions:
- GitHub Issues: https://github.com/thisismyurl/plugin-wp-support-thisismyurl/issues
- Documentation: https://thisismyurl.com/plugin-wp-support-thisismyurl/

## Changelog

### 1.2601.73002 - 2026-01-13
- Initial release of Debug Mode Toggles feature
- Backend logging toggles for WP_DEBUG, WP_DEBUG_LOG, SCRIPT_DEBUG, SAVEQUERIES
- Frontend display toggles for errors, query info, and memory usage
- Floating debug bar for admin users
- Error log viewer with refresh and clear functionality
- Auto-disable after 1 hour timeout
- Activity logging integration
- Cookie-based admin detection for display
- Configuration file management via mu-plugin
