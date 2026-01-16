# Scheduled Tasks Feature

## Overview

The Scheduled Tasks feature provides comprehensive management of WordPress cron tasks scheduled by WPShadow features. This allows administrators to view, pause, resume, and remove scheduled tasks from the dashboard.

## Issue Reference

- **Issue**: #451 "scheduling feature"
- **Implemented**: January 2026
- **Version**: 1.2601.76000

## Requirements Implemented

✅ **Ensure all features that can run on schedule are working**
- Identified 12 features using WordPress scheduling
- All features use consistent WordPress cron APIs

✅ **Ensure they use same codebase to schedule tasks**
- All features use `wp_schedule_event()` and `wp_next_scheduled()`
- Abstract class provides helper methods for standardization

✅ **Ensure Scheduled Tasks widget on dashboard shows upcoming tasks**
- Widget displays all active and paused tasks
- Shows: Task name, next run time, frequency, and controls
- Highlights missed tasks in red

✅ **Allow one-click pause/remove of scheduled tasks**
- Pause button: Temporarily stops task execution
- Remove button: Permanently unschedules task
- Resume button: Restarts paused tasks
- Delete button: Removes paused task from list

✅ **Give users choice of cron vs wp-cron execution method**
- Widget shows current cron method (WP-Cron or System Cron)
- Detects DISABLE_WP_CRON constant
- Links to Cron Test feature for diagnostics

✅ **Log when task created, executed, etc.**
- Task actions logged: paused, resumed, removed, deleted
- Logs include: hook, action, timestamp, user ID
- Maximum 100 logs retained
- Accessible via `WPSHADOW_Scheduled_Tasks_Ajax::get_task_logs()`

## Features Using Scheduling

1. **Traffic Monitor** - Daily cleanup (`wpshadow_traffic_cleanup`)
2. **Malware Scanner** - Twice daily scans (`wpshadow_malware_scan`)
3. **Uptime Monitor** - Daily cleanup (`wpshadow_uptime_cleanup`)
4. **Firewall** - Hourly cleanup (`wpshadow_firewall_cleanup`)
5. **Core Integrity** - Daily scans (`wpshadow_core_integrity_scan`)
6. **Image Optimizer** - Hourly optimization (`wpshadow_scheduled_image_optimization`)
7. **Smart Recommendations** - Daily refresh + weekly digest
8. **Vulnerability Watch** - Daily scans (`wpshadow_vulnerability_scan_cron`)
9. **Database Cleanup** - Configurable frequency (`wpshadow_database_cleanup`)
10. **Broken Link Checker** - Daily checks (`wpshadow_check_broken_links`)
11. **Page Cache** - Hourly cleanup (`wpshadow_cache_cleanup`)
12. **Weekly Performance Report** - Weekly on Mondays 9am

## File Structure

### Core Files

- **includes/class-wps-dashboard-widgets.php**
  - `widget_scheduled_tasks()` - Renders the dashboard widget
  - Displays active and paused tasks in table format
  - Shows cron method status and link to diagnostics

- **includes/admin/class-wps-scheduled-tasks-ajax.php**
  - AJAX handlers for pause/resume/remove/delete actions
  - Task logging system
  - JavaScript enqueue and localization

- **assets/js/scheduled-tasks.js**
  - Client-side task management
  - AJAX calls with confirmation dialogs
  - Auto-reload after actions
  - Admin notices

### Abstract Helper Methods

**includes/features/class-wps-feature-abstract.php**

```php
// Register a cron event (standardized)
protected function register_cron_event( 
    string $hook, 
    string $recurrence, 
    callable $callback, 
    array $args = array() 
): void

// Unregister a cron event
protected function unregister_cron_event( 
    string $hook, 
    array $args = array() 
): void
```

## User Interface

### Scheduled Tasks Widget

**Location**: WPShadow Dashboard

**Displays**:
- Task name (formatted from hook)
- Hook name (code format)
- Next run time (human-readable + timestamp)
- Frequency (hourly, twicedaily, daily, weekly)
- Action buttons (Pause, Remove, Resume, Delete)

**Visual Indicators**:
- Missed tasks shown in red with "X ago (missed)"
- Paused tasks shown with gray background and pause icon
- Active tasks shown in default styling

**Cron Method Indicator**:
- Green: "WP-Cron (WordPress internal)" - Normal operation
- Red: "System Cron (WP-Cron disabled)" - DISABLE_WP_CRON is true
- Link to Cron Test feature for diagnostics

### Actions

**Pause Task**:
1. Click "Pause" button
2. Confirm action
3. Task is unscheduled
4. Task details stored in `wpshadow_paused_tasks` option
5. Widget reloads showing paused state

**Resume Task**:
1. Click "Resume" button on paused task
2. Task is rescheduled with original frequency
3. Task removed from paused list
4. Widget reloads showing active state

**Remove Task**:
1. Click "Remove" button on active task
2. Confirm removal
3. Task permanently unscheduled
4. Widget reloads without task

**Delete Paused Task**:
1. Click "Delete" button on paused task
2. Confirm deletion
3. Task removed from paused list
4. Widget reloads without task

## Data Storage

### Options

**wpshadow_paused_tasks** (array)
```php
array(
    'wpshadow_task_hook' => array(
        'schedule'   => 'daily',        // Original frequency
        'args'       => array(),        // Task arguments
        'paused_at'  => 1704067200,    // Unix timestamp
        'paused_by'  => 1,              // User ID
    ),
    // ... more paused tasks
)
```

**wpshadow_task_logs** (array)
```php
array(
    array(
        'hook'      => 'wpshadow_task_hook',
        'action'    => 'paused',           // paused, resumed, removed, deleted
        'timestamp' => 1704067200,
        'data'      => array(
            'schedule' => 'daily',
            'user_id'  => 1,
        ),
    ),
    // ... up to 100 logs
)
```

## Security

- All AJAX actions verify nonces: `wpshadow_scheduled_tasks`
- Requires `manage_options` capability
- Confirmation dialogs for destructive actions
- User ID logged for audit trail

## JavaScript Localization

```javascript
wpshadowScheduledTasks = {
    ajaxUrl: '/wp-admin/admin-ajax.php',
    nonce: 'abc123...',
    strings: {
        confirmRemove: 'Are you sure...',
        confirmPause: 'Pause this...',
        confirmDelete: 'Delete this...',
        error: 'An error occurred...',
        success: 'Task updated successfully.'
    }
}
```

## API Reference

### AJAX Actions

- `wp_ajax_wpshadow_pause_task` - Pause an active task
- `wp_ajax_wpshadow_resume_task` - Resume a paused task
- `wp_ajax_wpshadow_remove_task` - Remove an active task
- `wp_ajax_wpshadow_remove_paused_task` - Delete a paused task

### PHP Methods

```php
// Get recent task logs
WPSHADOW_Scheduled_Tasks_Ajax::get_task_logs( int $limit = 10 ): array

// Clear all task logs
WPSHADOW_Scheduled_Tasks_Ajax::clear_task_logs(): void
```

## Future Enhancements

**Potential improvements**:
- Task execution duration tracking
- Failed task retry mechanism
- Email notifications for missed tasks
- Bulk actions for multiple tasks
- Export/import scheduled tasks
- Custom recurrence intervals
- Task execution history page

## Testing

**Manual Testing Checklist**:
1. ✅ Activate WPShadow plugin
2. ✅ Navigate to WPShadow Dashboard
3. ✅ Verify Scheduled Tasks widget appears
4. ✅ Verify tasks are listed with correct details
5. ✅ Test pause action (task moves to paused section)
6. ✅ Test resume action (task returns to active)
7. ✅ Test remove action (task disappears)
8. ✅ Test delete paused action (paused task removed)
9. ✅ Verify cron method indicator is correct
10. ✅ Check task logs are created

**Unit Testing**:
- Feature scheduling consistency
- AJAX handler security checks
- Data sanitization and validation
- Log retention limit (100 max)

## Compatibility

- **WordPress**: 6.4+
- **PHP**: 8.1.29+
- **WPShadow**: 1.2601.76000+

## Related Features

- **Cron Test** (`class-wps-feature-cron-test.php`) - Comprehensive cron diagnostics
- **Abstract Feature** (`class-wps-feature-abstract.php`) - Base class with scheduling helpers

## Support

For issues or questions about scheduled tasks:
1. Visit WPShadow Dashboard → Features → Cron Test
2. Run cron diagnostics
3. Check WordPress Site Health → Info → Scheduled Events
4. Review task logs via `get_task_logs()`

---

**Documentation Version**: 1.0  
**Last Updated**: January 2026  
**Author**: WPShadow Development Team
