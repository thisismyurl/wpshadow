# Feature Log Widget

A VS Code Source Control inspired activity timeline that tracks feature changes and actions.

## Features

- **VS Code SCM-Style Timeline**: Clean, vertical timeline with colored dots and connecting lines
- **Action-Specific Colors**: Different colors for enabled (green), disabled (red), settings updates (blue), errors (red pulsing)
- **Automatic Logging**: Tracks feature enables/disables, sub-feature toggles, and settings updates
- **User Attribution**: Shows which user performed each action
- **Relative Timestamps**: Human-readable "5 minutes ago" with full timestamp on hover
- **Load More Pagination**: Loads 10 entries at a time with AJAX pagination
- **Hover Effects**: Subtle background highlight on hover
- **Responsive Design**: Adapts to sidebar width

## Location

The Feature Log widget appears on the right sidebar of individual feature detail pages:
- Navigate to: **WPShadow → Features → [Any Feature Name]**
- Widget appears under "Feature Information"

## Logged Actions

The following actions are automatically logged:

- `enabled` - Feature enabled (green dot)
- `disabled` - Feature disabled (red dot)
- `settings_updated` - Feature settings modified (blue dot)
- `sub_feature_enabled` - Sub-feature toggled on (blue dot)
- `sub_feature_disabled` - Sub-feature toggled off (blue dot)
- `error` - Error occurred (pulsing red dot)
- `action_performed` - Custom action (gray dot)

## Implementation

### Logging Function

Use the `wpshadow_log_feature_activity()` function to add log entries:

```php
\WPShadow\CoreSupport\wpshadow_log_feature_activity(
    'feature-id',           // Feature ID
    'enabled',              // Action type
    'Optional message',     // Description (optional)
    array()                 // Additional metadata (optional)
);
```

### Already Integrated

Logging is automatically integrated for:
- ✓ Feature enable/disable toggles
- ✓ Sub-feature toggles
- ✓ External Fonts Disabler settings updates

### Adding to Your Feature

To add logging to your own feature's AJAX handlers:

```php
public function ajax_save_settings(): void {
    // ... your save logic ...
    
    // Log the activity
    if ( function_exists( '\WPShadow\CoreSupport\wpshadow_log_feature_activity' ) ) {
        \WPShadow\CoreSupport\wpshadow_log_feature_activity(
            'your-feature-id',
            'settings_updated',
            'Settings saved successfully'
        );
    }
    
    wp_send_json_success();
}
```

## Testing

### Generate Sample Logs

To test the widget with sample data:

1. **Using the direct PHP script**:
   ```php
   // Temporarily add to your theme's functions.php:
   include_once( ABSPATH . '../wpshadow/generate-sample-logs-direct.php' );
   ```

2. **Using WP-CLI**:
   ```bash
   cd /workspaces/wpshadow
   wp eval-file test-feature-logs.php
   ```

3. **Using the bash script**:
   ```bash
   cd /workspaces/wpshadow
   ./generate-test-logs.sh
   ```

### View Generated Logs

After generating sample data, view the logs at:
```
/wp-admin/admin.php?page=wpshadow&wpshadow_tab=features&feature=external-fonts-disabler
```

## Data Storage

- **Option Name**: `wpshadow_feature_logs`
- **Structure**: Multi-dimensional array indexed by feature ID
- **Retention**: Last 100 entries per feature (automatic cleanup)
- **Format**:
  ```php
  array(
      'feature-id' => array(
          array(
              'timestamp' => 1234567890,
              'action' => 'enabled',
              'message' => 'Optional description',
              'user' => 'username',
              'user_id' => 1,
              'metadata' => array(),
          ),
          // ... more entries
      ),
  )
  ```

## Styling

The timeline uses VS Code's Source Control graph aesthetic:

- **Timeline Line**: 2px gray (#dcdcde) connecting dots
- **Action Dots**: 10px circles with action-specific colors
  - Enabled: `#00a32a` (green)
  - Disabled: `#d63638` (red)
  - Settings: `#2271b1` (blue)
  - Error: `#d63638` with pulse animation
  - Default: `#646970` (gray)
- **Typography**: 13px/12px sizes with WordPress admin colors
- **Spacing**: 30px left padding, 20px between entries

Custom CSS classes:
- `.wpshadow-feature-log-timeline` - Container
- `.wpshadow-log-entry` - Individual entry wrapper
- `.wpshadow-log-dot` - Colored dot indicator
- `.wpshadow-log-line` - Connecting line
- `.wpshadow-log-content` - Text content
- `.wpshadow-log-action` - Action label
- `.wpshadow-log-time` - Timestamp
- `.wpshadow-log-message` - Optional message
- `.wpshadow-log-user` - User attribution

## AJAX Endpoints

### Load More Logs

- **Action**: `wpshadow_load_more_logs`
- **Parameters**:
  - `feature_id` (string): Feature identifier
  - `offset` (int): Pagination offset
  - `nonce` (string): Security nonce
- **Returns**: HTML for additional log entries

## Future Enhancements

Potential improvements:
- Export logs to CSV/JSON
- Filter by action type
- Search within messages
- Clear all logs button
- Configurable retention period
- Email notifications for errors
- Log statistics/charts
