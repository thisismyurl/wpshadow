# WPShadow Workflow Triggers Reference

This document outlines all available triggers for WPShadow workflows, including the 5 newly added triggers.

## Existing Triggers

### 1. Time Trigger
- **Label:** Time Trigger
- **Description:** Run when clock reaches specific time
- **Configuration:**
  - `time` (time): 24-hour format time (e.g., "02:00")
  - `days` (checkbox_group): Days to run the trigger

### 2. Condition Trigger
- **Label:** Condition Trigger
- **Description:** Run when condition is met
- **Configuration:**
  - `condition_type` (select): Memory usage, plugins outdated, disk space, SSL, backup, debug mode, custom PHP
  - `threshold` (number): Threshold value for comparison
  - `custom_condition` (textarea): Custom PHP condition code

### 3. Event Trigger
- **Label:** Event Trigger
- **Description:** Run when specific event happens
- **Configuration:**
  - `event_type` (select): Plugin activated/deactivated, theme changed, user registered, post published/deleted, comment posted

### 4. Page Load Trigger
- **Label:** Page Load Trigger
- **Description:** Run on every page load
- **Configuration:**
  - `page_context` (select): Frontend/admin contexts (all pages, specific post types, archives, home, etc.)

## New Triggers

### 5. Plugin/Theme Update Trigger
- **Label:** Plugin/Theme Update Available
- **Description:** Run when plugin or theme updates are detected
- **Configuration:**
  - `target_type` (select): 
    - `any` - Any plugin or theme
    - `plugins` - Plugins only
    - `themes` - Themes only
    - `specific` - Specific plugin/theme
  - `specific_slug` (text): Plugin/theme slug (when target_type is "specific")

**Example Use Case:** Automatically run diagnostics when updates are available.

### 6. Backup Completion Trigger
- **Label:** Backup Completion
- **Description:** Run when backup completes (success or failure)
- **Configuration:**
  - `backup_status` (select):
    - `any` - Any backup event
    - `success` - Successful backup only
    - `failure` - Failed backup only

**Example Use Case:** Send notification when backup completes or fails.

### 7. Database Issues Trigger
- **Label:** Database Issues
- **Description:** Run when database problems are detected
- **Configuration:**
  - `database_issue` (select):
    - `size_threshold` - Database size exceeds threshold
    - `corruption` - Corruption detected
    - `tables_missing` - Tables missing
    - `slow_query` - Slow queries detected
  - `size_mb` (number): Size threshold in MB (default: 500)

**Example Use Case:** Run optimization treatment when database exceeds 1000MB.

### 8. Error Log Activity Trigger
- **Label:** Error Log Activity
- **Description:** Run when errors are logged
- **Configuration:**
  - `error_level` (select):
    - `any` - Any error
    - `warning` - Warnings and higher
    - `error` - Errors and higher
    - `critical` - Critical errors only
  - `frequency` (number): Minimum occurrences (default: 1)

**Example Use Case:** Alert admins when critical errors occur.

### 9. Manual / External CRON Trigger ⭐
- **Label:** Manual / External CRON
- **Description:** Trigger via query string (external CRON or manual button)
- **Configuration:**
  - `trigger_key` (text): Query parameter name (default: "run_workflow")
  - `require_auth` (checkbox): Require authentication (default: true)
  - `allowed_ips` (textarea): Comma-separated list of allowed IPs

**Usage:**
```
https://example.com/?run_workflow=workflow_id_here
```

Or with custom parameter:
```
https://example.com/?custom_param=workflow_123
```

**Features:**
- Query parameter matches workflow ID
- Optional authentication requirement
- IP whitelist support
- Compatible with external CRON services
- Sets nocache headers to prevent caching of CRON requests

**Example Use Case:** 
- External monitoring service triggers workflow via HTTP request
- Scheduled CRON job on another server calls the workflow
- Admin clicks "Run Now" button that generates the query string

**Security Considerations:**
- Enable `require_auth` to prevent unauthorized triggers
- Use IP whitelist if calling from specific CRON service
- Consider using HTTPS for external triggers
- Regularly review workflow execution logs

## Trigger Matching Logic

Each trigger type has specific matching logic in `trigger_matches_context()`:

- **page_load_trigger**: Matches based on page context (frontend/admin/post type/archive)
- **event_trigger**: Matches exact event type
- **condition_trigger**: Evaluates system conditions at runtime
- **plugin_update_trigger**: Checks available updates via transient
- **backup_completion_trigger**: Matches backup status
- **database_trigger**: Checks database size and corruption status
- **error_log_trigger**: Matches error severity level
- **manual_cron_trigger**: Validated in query string handler (always returns true in context matching)
- **time_trigger**: Evaluated during cron execution

## Workflow Integration

### Adding a new trigger to a workflow:

1. Open workflow builder
2. Click "Add Trigger" block
3. Select trigger type from registry
4. Configure trigger-specific options
5. Save workflow

### Actions available after trigger fires:

- Run Diagnostic
- Apply Treatment
- Send Email
- Log Action
- Send Notification

## Hook Points

Workflows hook into WordPress at these points:

- `wp` - Frontend page load (priority 1)
- `admin_init` - Admin page load (priority 1)
- `activated_plugin` - Plugin activation
- `deactivated_plugin` - Plugin deactivation
- `switch_theme` - Theme change
- `user_register` - User registration
- `publish_post` - Post publication
- `delete_post` - Post deletion
- `comment_post` - Comment posting
- `load-update.php|plugins.php|themes.php` - Update checks
- `wpshadow_backup_completed` - Backup completion
- `wpshadow_database_check` - Database health check
- `wpshadow_error_log_entry` - Error logging
- `wpshadow_workflow_cron` - Hourly cron execution

## Example Workflows

### Example 1: Monitor Plugin Updates
```
Trigger: Plugin/Theme Update Available (specific_slug: "wpshadow")
Action: Send Email (to admin with list of updates)
```

### Example 2: Daily Health Check
```
Trigger: Time Trigger (time: "02:00", days: [all])
Action: Run Diagnostic (diagnostic_type: "full")
Action: Send Email (with results)
```

### Example 3: External Monitoring Integration
```
Trigger: Manual / External CRON (trigger_key: "monitor", require_auth: true)
Action: Run Diagnostic (all checks)
Action: Apply Treatment (if needed)
Action: Log Action (for audit trail)
```

### Example 4: Database Maintenance
```
Trigger: Database Issues (database_issue: "size_threshold", size_mb: 1000)
Action: Run Diagnostic (diagnostic_type: "database")
Action: Apply Treatment (optimize database)
Action: Send Email (notification)
```
