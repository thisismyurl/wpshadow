# External CRON Integration Guide

This guide explains how to set up external CRON services or monitoring tools to trigger WPShadow workflows via HTTP requests.

## Overview

The **Manual / External CRON** trigger allows workflows to be initiated via URL query strings, making it perfect for:

- External monitoring services (Uptime Robot, Pingdom, etc.)
- Scheduled tasks from external servers
- CI/CD pipeline integrations
- Third-party automation platforms (Zapier, IFTTT, etc.)
- Custom scheduled scripts

## Basic Setup

### Step 1: Create a Workflow with Manual/External CRON Trigger

1. Navigate to WPShadow → Workflow Builder
2. Create a new workflow or edit existing
3. Add a **Manual / External CRON** trigger block
4. Configure:
   - **Query Parameter Name:** e.g., `run_workflow` (default)
   - **Require Authentication:** Check if you want to require login
   - **Allowed IPs:** (Optional) Whitelist specific IPs

### Step 2: Note Your Workflow ID

The workflow ID is displayed in the workflow list and editor. You'll need this for the trigger URL.

### Step 3: Construct Your Trigger URL

**Basic URL:**
```
https://yoursite.com/?run_workflow=WORKFLOW_ID
```

**With custom parameter name:**
```
https://yoursite.com/?trigger=WORKFLOW_ID
```

**With authentication (if required):**
```
https://yoursite.com/?run_workflow=WORKFLOW_ID&logged_in_user=your_username
```

## Security Configuration

### Option 1: Authentication Required (Recommended)

When `require_auth` is enabled, the request must come from a logged-in user or include valid credentials.

**Using Basic Auth:**
```
curl -u "username:password" "https://yoursite.com/?run_workflow=WORKFLOW_ID"
```

**Using Cookie (if already authenticated):**
```
curl -b "wordpress_logged_in=YOUR_COOKIE" "https://yoursite.com/?run_workflow=WORKFLOW_ID"
```

### Option 2: IP Whitelist

Restrict triggers to specific IP addresses:

```
Allowed IPs: 192.168.1.100, 10.0.0.5, 203.0.113.42
```

Multiple IPs are comma-separated. Only requests from these IPs will trigger the workflow.

### Option 3: HTTPS Only

Always use HTTPS URLs to prevent credentials/workflow IDs from being intercepted.

## Integration Examples

### Uptime Robot

Uptime Robot can trigger a workflow when your site goes down or comes back up:

1. Log in to Uptime Robot
2. Select your website monitor
3. Edit → Alert Settings
4. Add webhook:
   - **Webhook URL:** `https://yoursite.com/?run_workflow=WORKFLOW_ID&incident={status}`
   - **HTTP Method:** GET or POST
5. Save

Your workflow will execute when the status changes.

### Pingdom

To integrate with Pingdom:

1. Go to Integrations
2. Add Webhook
3. **URL:** `https://yoursite.com/?run_workflow=WORKFLOW_ID`
4. Set webhook to trigger on uptime/downtime events

### Zapier

Use Zapier to trigger workflows from hundreds of services:

1. Create new Zap
2. Trigger: Select any Zapier app (e.g., Schedule, Gmail, Slack)
3. Action: Use "Webhooks by Zapier" → Make a GET request
4. **URL:** `https://yoursite.com/?run_workflow=WORKFLOW_ID`
5. Configure frequency/conditions

### External Cron.php Script

Run a PHP script on a separate server that triggers your workflow:

```php
<?php
// external-cron.php
// Schedule this to run via your hosting cron

$workflow_id = 'your-workflow-id-here';
$site_url = 'https://yoursite.com';
$trigger_url = $site_url . '?run_workflow=' . $workflow_id;

// Option 1: Simple GET request
file_get_contents($trigger_url);

// Option 2: Using cURL (better for monitoring responses)
$ch = curl_init($trigger_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "Workflow triggered successfully\n";
} else {
    echo "Failed to trigger workflow: HTTP $http_code\n";
}
?>
```

### Linux Crontab

Schedule the workflow to run daily:

```bash
# Run workflow every day at 2 AM
0 2 * * * curl -s "https://yoursite.com/?run_workflow=WORKFLOW_ID" > /dev/null 2>&1

# Run every 6 hours
0 */6 * * * curl -s "https://yoursite.com/?run_workflow=WORKFLOW_ID" > /dev/null 2>&1

# Run every 30 minutes
*/30 * * * * curl -s "https://yoursite.com/?run_workflow=WORKFLOW_ID" > /dev/null 2>&1
```

### Windows Task Scheduler

Create a scheduled task that calls your workflow:

```batch
@echo off
REM Run workflow daily at 2 AM
curl -s "https://yoursite.com/?run_workflow=WORKFLOW_ID" > nul
```

Configure this as a scheduled task in Windows Task Scheduler.

## Advanced Configuration

### Custom Query Parameter Names

Create multiple workflows with different parameter names for different triggers:

**Workflow 1:** `?daily_health_check=workflow_123`
**Workflow 2:** `?emergency_maintenance=workflow_456`

### Multi-Step Workflows

Chain multiple workflows using the same trigger:

```
1. Health Check Workflow triggered
   └─ Runs diagnostics
   └─ Stores results in custom post type
   
2. Maintenance Workflow triggered separately
   └─ Reads previous results
   └─ Applies treatments if needed
   └─ Notifies admin
```

### Conditional Triggering

Use the same workflow ID for multiple external triggers, but configure different actions based on query parameters:

```
https://yoursite.com/?run_workflow=workflow_id&action=backup
https://yoursite.com/?run_workflow=workflow_id&action=optimize
```

Then check `$_GET['action']` within action logic.

## Monitoring & Logging

### View Execution Logs

1. WPShadow → Dashboard
2. Look for "Workflow Executions" widget
3. Filter by trigger type: "Manual/CRON"

### Debug Mode

Enable debugging in `wp-config.php`:

```php
define( 'WPSHADOW_DEBUG_WORKFLOWS', true );
```

This will log all workflow executions to `wp-content/wpshadow-workflows.log`.

### Server Logs

Check your web server logs for requests matching your trigger URL:

```bash
# Apache
tail -f /var/log/apache2/access.log | grep "run_workflow"

# Nginx
tail -f /var/log/nginx/access.log | grep "run_workflow"
```

## Troubleshooting

### Workflow Not Triggering

1. **Verify workflow ID is correct:**
   - Check WPShadow → Workflows list
   - Copy exact ID (including any hyphens)

2. **Check authentication:**
   - If `require_auth` is enabled, ensure you're logged in
   - Or provide valid credentials in the URL

3. **Verify IP whitelist:**
   - Check that the calling IP is in the allowed list
   - View your server's remote IP: Check logs or use `echo $_SERVER['REMOTE_ADDR'];`

4. **Check firewall/WAF rules:**
   - Some security plugins may block URLs with query parameters
   - Whitelist the query parameter in your security plugin

### 403 Forbidden Error

- **Cause:** Authentication failed or IP not whitelisted
- **Solution:** 
  - Enable guest access (disable `require_auth`)
  - Or add your IP to whitelist
  - Or provide proper authentication

### 404 Not Found Error

- **Cause:** Site is using pretty permalinks and query string not working
- **Solution:**
  - Disable pretty permalinks temporarily
  - Or use: `https://yoursite.com/index.php?run_workflow=WORKFLOW_ID`

### Workflow Triggers But Doesn't Execute Actions

- **Cause:** Actions may have failed or require additional configuration
- **Solution:**
  - Check workflow execution logs
  - Verify action configurations (email addresses, diagnostic types, etc.)
  - Enable debug mode for detailed logging

## Best Practices

### 1. Use HTTPS
Always use HTTPS URLs to protect your workflow IDs and any sensitive data.

### 2. Rotate IPs
Regularly update your IP whitelist to match your monitoring service's current IPs.

### 3. Log Everything
Configure workflows to log actions so you can audit external triggers.

### 4. Rate Limiting
Consider adding rate limiting to prevent workflow abuse:

```php
// In your custom action
$rate_limit_key = 'workflow_' . $workflow_id . '_last_run';
$last_run = get_option($rate_limit_key);

if ($last_run && (time() - $last_run) < 60) {
    return; // Skip if triggered within 60 seconds
}

update_option($rate_limit_key, time());
```

### 5. Timeout Handling
Set appropriate timeouts for your external requests (30 seconds is usually safe).

### 6. Error Notifications
Configure workflows to send error notifications:

```
External Service calls workflow
  ↓
Run diagnostics
  ↓
If errors found → Send email to admin
```

## API Alternative

For programmatic access, consider also implementing:

```php
// Direct PHP API call
do_action('wpshadow_trigger_workflow', $workflow_id, $context);
```

This allows internal plugins to trigger workflows without HTTP requests.

## Support

For issues or questions:
1. Check workflow execution logs
2. Enable debug mode
3. Review security settings (auth, IPs)
4. Contact WPShadow support with logs
