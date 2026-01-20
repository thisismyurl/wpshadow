# Quick Start: New Workflow Triggers

## What Was Added

5 new powerful triggers for WPShadow workflows:

1. ✅ **Plugin/Theme Update Trigger** - Detect when updates are available
2. ✅ **Backup Completion Trigger** - React to backup success/failure
3. ✅ **Database Issues Trigger** - Monitor database size and health
4. ✅ **Error Log Activity Trigger** - Alert on errors
5. ✅ **Manual / External CRON Trigger** - Trigger workflows via URL or external services

## How to Use

### From the UI
1. WPShadow → Workflow Builder
2. Create new workflow
3. Add trigger block
4. Select from new triggers in the dropdown
5. Configure options
6. Save

### Manual/CRON Trigger - Most Powerful Feature

**Create External CRON Trigger:**
1. Add "Manual / External CRON" trigger to workflow
2. Set Query Parameter Name (e.g., `run_workflow`)
3. Optional: Enable authentication, add IP whitelist
4. Save workflow and note the **Workflow ID**

**Call from External Service:**
```
https://yoursite.com/?run_workflow=WORKFLOW_ID
```

**Call from Linux Cron:**
```bash
0 2 * * * curl https://yoursite.com/?run_workflow=WORKFLOW_ID
```

**Call from Uptime Robot/Pingdom/etc:**
Use the above URL as webhook URL.

## Configuration Reference

### Plugin/Theme Update Trigger
- **Target Type:** Any, Plugins only, Themes only, Specific plugin
- **Specific Slug:** Leave empty unless selecting "Specific"
- **Example:** Trigger when WooCommerce has an update available

### Backup Completion Trigger
- **Status:** Any, Success only, Failure only
- **Example:** Send notification when daily backup completes

### Database Issues Trigger
- **Issue Type:** Size exceeded, Corruption, Tables missing, Slow queries
- **Size Threshold (MB):** Default 500
- **Example:** Alert when database grows over 1000MB

### Error Log Activity Trigger
- **Error Level:** Any, Warnings+, Errors+, Critical only
- **Frequency:** Minimum occurrences to trigger
- **Example:** Alert when 5+ critical errors logged

### Manual / External CRON Trigger
- **Query Parameter:** Name of URL parameter (default: run_workflow)
- **Require Authentication:** Enable for security
- **Allowed IPs:** Comma-separated whitelist (optional)
- **Example:** `?run_workflow=workflow_123&param2=value`

## Real-World Examples

### Example 1: Monitor Plugin Updates
```
Workflow Name: Weekly Update Check
Trigger: Plugin/Theme Update Available
  - Target Type: Plugins only
Action: Send Email
  - To: admin@example.com
  - Subject: Plugin updates available
```

### Example 2: External Uptime Monitoring
```
Workflow Name: Uptime Robot Response
Trigger: Manual / External CRON
  - Query Parameter: uptime_robot
  - Require Auth: false
  - Allowed IPs: 192.168.1.50 (your server IP)
Action: Run Diagnostic (full health check)
Action: Send Email (if issues found)

Uptime Robot Webhook: https://yoursite.com/?uptime_robot=WORKFLOW_ID
```

### Example 3: Daily Scheduled Maintenance
```
Workflow Name: Nightly Maintenance
Trigger: Manual / External CRON
  - Query Parameter: maintenance
  - Require Auth: true
Action: Run Diagnostic (database check)
Action: Apply Treatment (optimize)
Action: Send Email (report)

Linux Cron: 0 2 * * * curl -u user:pass https://yoursite.com/?maintenance=WORKFLOW_ID
```

### Example 4: Database Size Alert
```
Workflow Name: Database Size Monitor
Trigger: Database Issues
  - Issue Type: Size threshold exceeded
  - Size MB: 1000
Action: Run Diagnostic (database)
Action: Send Email (alert)
Action: Log Action (audit trail)
```

## Security Best Practices

1. **Always use HTTPS** for external CRON triggers
2. **Enable authentication** unless calling from trusted IPs
3. **Use IP whitelist** for external monitoring services
4. **Rotate allowed IPs** when monitoring service IPs change
5. **Review workflow logs** regularly for unauthorized triggers
6. **Set rate limits** if necessary (manually in action code)

## Documentation

For more details, see:
- [WORKFLOW_TRIGGERS_REFERENCE.md](WORKFLOW_TRIGGERS_REFERENCE.md) - Complete trigger documentation
- [EXTERNAL_CRON_INTEGRATION_GUIDE.md](EXTERNAL_CRON_INTEGRATION_GUIDE.md) - Integration tutorials
- [IMPLEMENTATION_SUMMARY_NEW_TRIGGERS.md](IMPLEMENTATION_SUMMARY_NEW_TRIGGERS.md) - Technical details

## Common Tasks

### Generate a trigger URL
```
Base: https://yoursite.com/
Parameter: run_workflow
Workflow ID: my-daily-check
Result: https://yoursite.com/?run_workflow=my-daily-check
```

### Test a trigger URL
```bash
# Simple test
curl "https://yoursite.com/?run_workflow=my-daily-check"

# With authentication
curl -u "username:password" "https://yoursite.com/?run_workflow=my-daily-check"

# With timeout and verbose output
curl -v -m 30 "https://yoursite.com/?run_workflow=my-daily-check"
```

### View workflow execution logs
1. WPShadow → Dashboard
2. Look for "Workflow Executions" widget
3. Filter by trigger type: "Manual/CRON"
4. Check timestamp, status, and action results

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Workflow won't trigger | Check workflow ID is correct, verify authentication settings, check IP whitelist |
| 403 Forbidden | Enable guest access (disable require_auth) or add your IP to whitelist |
| 404 Not Found | Try: `https://yoursite.com/index.php?run_workflow=ID` |
| No update detection | Clear WordPress update transients: `delete_site_transient('update_plugins')` |
| Database check not working | Ensure database user has SELECT on information_schema |

## Technical Details

**Files Modified:**
- `includes/workflow/class-block-registry.php` - Trigger definitions
- `includes/workflow/class-workflow-executor.php` - Trigger handlers and logic

**Hooks Added:**
- `wpshadow_backup_completed` - For backup monitoring
- `wpshadow_database_check` - For database health checks
- `wpshadow_error_log_entry` - For error logging

**Query String Validation:**
- Parameter name sanitization
- Workflow ID exact matching
- IP whitelist checking
- Authentication verification
- Nocache headers set automatically

## Next Steps

1. **Create Your First Workflow** - Use Plugin/Theme Update trigger
2. **Test with Manual Trigger** - Create external CRON workflow and test URL
3. **Integrate with External Service** - Hook up Uptime Robot or monitoring tool
4. **Set Up Notifications** - Configure email/notifications in workflow actions
5. **Monitor Logs** - Regular review of execution logs

## Support

Check documentation files first:
1. [WORKFLOW_TRIGGERS_REFERENCE.md](WORKFLOW_TRIGGERS_REFERENCE.md) - How each trigger works
2. [EXTERNAL_CRON_INTEGRATION_GUIDE.md](EXTERNAL_CRON_INTEGRATION_GUIDE.md) - External service integration
3. WPShadow Dashboard → Help → Workflows

Questions? Review the workflow execution logs in the dashboard.
