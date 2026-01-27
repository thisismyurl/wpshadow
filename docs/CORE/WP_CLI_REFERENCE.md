# WPShadow WP-CLI Commands Reference

**Version:** 1.2601.2148  
**Last Updated:** January 25, 2026

This document provides a comprehensive reference for all WP-CLI commands available in WPShadow. These commands allow you to manage WPShadow from the command line.

---

## Table of Contents

- [Installation & Setup](#installation--setup)
- [Activity Commands](#activity-commands)
- [Treatment Commands](#treatment-commands)
- [Diagnostic Commands](#diagnostic-commands)
- [Workflow Commands](#workflow-commands)
- [KPI Commands](#kpi-commands)
- [Consent Commands](#consent-commands)
- [Settings Commands](#settings-commands)
- [Usage Examples](#usage-examples)

---

## Installation & Setup

WP-CLI commands are automatically available when WPShadow is installed and WP-CLI is present on your system.

### Verify Installation

```bash
wp wpshadow --info
```

### Check Available Commands

```bash
wp help wpshadow
```

---

## Activity Commands

### `wp wpshadow activity list`

List recent activity entries from the WPShadow activity log.

**Options:**
- `--category=<category>` - Filter by category slug (security, performance, etc.)
- `--action=<action>` - Filter by action key
- `--limit=<number>` - Number of entries to return (default: 20)
- `--format=<format>` - Output format: table, json, csv, yaml (default: table)

**Examples:**

```bash
# List last 10 activities
wp wpshadow activity list --limit=10

# List security-related activities
wp wpshadow activity list --category=security

# Export to JSON
wp wpshadow activity list --format=json

# List specific action
wp wpshadow activity list --action=treatment_applied
```

**Output:**
```
+---------------------+-------------------+----------+-------------------------+-------+
| date                | action            | category | details                 | user  |
+---------------------+-------------------+----------+-------------------------+-------+
| 2026-01-25 19:00:00 | treatment_applied | security | SSL enabled             | admin |
| 2026-01-25 18:55:00 | diagnostic_run    | security | Security scan completed | admin |
+---------------------+-------------------+----------+-------------------------+-------+
```

### `wp wpshadow activity export`

Export activity log to CSV format.

**Options:**
- `--category=<category>` - Filter by category slug
- `--action=<action>` - Filter by action key

**Examples:**

```bash
# Export all activity
wp wpshadow activity export > activity.csv

# Export security activity
wp wpshadow activity export --category=security > security-activity.csv
```

---

## Treatment Commands

### `wp wpshadow treatment list`

List all available treatments.

**Options:**
- `--format=<format>` - Output format: table, json, csv, yaml (default: table)

**Examples:**

```bash
# List all treatments
wp wpshadow treatment list

# Export to JSON
wp wpshadow treatment list --format=json
```

**Output:**
```
+----------------------------------+---------------------+----------------------------------+
| class                            | name                | description                      |
+----------------------------------+---------------------+----------------------------------+
| Treatment_SSL                    | Enable SSL          | Enables HTTPS for your site      |
| Treatment_Debug_Mode             | Disable Debug Mode  | Turns off WordPress debug mode   |
+----------------------------------+---------------------+----------------------------------+
```

### `wp wpshadow treatment apply <finding_id>`

Apply a treatment to fix a specific finding.

**Arguments:**
- `<finding_id>` - The finding ID to apply treatment to (e.g., 'ssl-check', 'debug-mode')

**Options:**
- `--dry-run` - Show what would be done without applying changes

**Examples:**

```bash
# Apply SSL treatment
wp wpshadow treatment apply ssl-check

# Dry run to see what would happen
wp wpshadow treatment apply ssl-check --dry-run

# Apply debug mode treatment
wp wpshadow treatment apply debug-mode
```

**Output:**
```
Success: Treatment applied: Enable SSL
```

### `wp wpshadow treatment undo <finding_id>`

Undo a previously applied treatment.

**Arguments:**
- `<finding_id>` - The finding ID to undo treatment for

**Examples:**

```bash
# Undo SSL treatment
wp wpshadow treatment undo ssl-check

# Undo debug mode treatment
wp wpshadow treatment undo debug-mode
```

**Output:**
```
Success: Treatment undone: Enable SSL
```

---

## Diagnostic Commands

### `wp wpshadow diagnostic list`

List all available diagnostic checks.

**Options:**
- `--format=<format>` - Output format: table, json, csv, yaml (default: table)

**Examples:**

```bash
# List all diagnostics
wp wpshadow diagnostic list

# Export to JSON
wp wpshadow diagnostic list --format=json
```

**Output:**
```
+----------------------------------+-------------------------+
| class                            | name                    |
+----------------------------------+-------------------------+
| Diagnostic_SSL                   | SSL Certificate Check   |
| Diagnostic_Debug_Mode            | Debug Mode Check        |
| Diagnostic_PHP_Version           | PHP Version Check       |
+----------------------------------+-------------------------+
```

### `wp wpshadow diagnostic run [<diagnostic_class>]`

Run diagnostic checks. If no diagnostic is specified, runs all diagnostics.

**Arguments:**
- `[<diagnostic_class>]` - Optional. Specific diagnostic class to run (can be partial match)

**Options:**
- `--format=<format>` - Output format: table, json, yaml (default: table)

**Examples:**

```bash
# Run all diagnostics
wp wpshadow diagnostic run

# Run specific diagnostic
wp wpshadow diagnostic run Diagnostic_SSL

# Run diagnostics matching "security"
wp wpshadow diagnostic run security

# Export results to JSON
wp wpshadow diagnostic run --format=json
```

**Output:**
```
+-----------------------+--------+----------+----------------------------------+
| diagnostic            | status | severity | message                          |
+-----------------------+--------+----------+----------------------------------+
| SSL Certificate Check | fail   | high     | SSL certificate is not enabled   |
| Debug Mode Check      | fail   | medium   | Debug mode is enabled            |
+-----------------------+--------+----------+----------------------------------+
Success: All diagnostics completed.
```

---

## Workflow Commands

### `wp wpshadow workflow list`

List all workflows.

**Options:**
- `--format=<format>` - Output format: table, json, csv, yaml (default: table)

**Examples:**

```bash
# List workflows
wp wpshadow workflow list

# Export to JSON
wp wpshadow workflow list --format=json
```

**Output:**
```
+------------------+------------------------+---------+
| id               | name                   | enabled |
+------------------+------------------------+---------+
| security-scan    | Security Scan Workflow | yes     |
| backup-routine   | Backup Routine         | no      |
+------------------+------------------------+---------+
```

### `wp wpshadow workflow toggle <id>`

Enable or disable a workflow.

**Arguments:**
- `<id>` - Workflow ID

**Options:**
- `--enable` - Enable the workflow
- `--disable` - Disable the workflow

**Examples:**

```bash
# Enable workflow
wp wpshadow workflow toggle security-scan --enable

# Disable workflow
wp wpshadow workflow toggle backup-routine --disable
```

**Output:**
```
Success: Workflow enabled: security-scan
```

---

## KPI Commands

### `wp wpshadow kpi summary`

Show KPI summary statistics.

**Options:**
- `--format=<format>` - Output format: table, json, yaml (default: table)

**Examples:**

```bash
# Show KPI summary
wp wpshadow kpi summary

# Export to JSON
wp wpshadow kpi summary --format=json

# Export to YAML
wp wpshadow kpi summary --format=yaml
```

**Output:**
```
+----------------------+-------+
| metric               | value |
+----------------------+-------+
| findings_detected    | 15    |
| findings_resolved    | 12    |
| treatments_applied   | 10    |
| time_saved_minutes   | 240   |
| success_rate         | 95%   |
+----------------------+-------+
```

---

## Consent Commands

### `wp wpshadow consent get`

Get consent preferences for a user.

**Options:**
- `--user=<user_id>` - User ID (default: 1)
- `--format=<format>` - Output format: table, json, yaml (default: table)

**Examples:**

```bash
# Get consent for user 1
wp wpshadow consent get

# Get consent for specific user
wp wpshadow consent get --user=2

# Export to JSON
wp wpshadow consent get --format=json
```

**Output:**
```
+------------------+-------+
| preference       | value |
+------------------+-------+
| telemetry        | no    |
| error_reporting  | yes   |
| newsletter       | no    |
+------------------+-------+
```

---

## Settings Commands

### `wp wpshadow setting [<setting_name>] [<setting_value>]`

Get or set WPShadow settings.

**Arguments:**
- `[<setting_name>]` - Setting name to get or set (optional - lists all if omitted)
- `[<setting_value>]` - Setting value to set (optional - gets current value if omitted)

**Options:**
- `--format=<format>` - Output format: table, json, yaml (default: table)

**Examples:**

```bash
# List all WPShadow settings
wp wpshadow setting

# Get specific setting
wp wpshadow setting wpshadow_debug_mode

# Set a setting
wp wpshadow setting wpshadow_debug_mode 1

# List settings in JSON
wp wpshadow setting --format=json
```

**List Output:**
```
+----------------------------------+--------+
| name                             | value  |
+----------------------------------+--------+
| wpshadow_debug_mode              | 0      |
| wpshadow_cache_enabled           | 1      |
| wpshadow_guardian_enabled        | 0      |
+----------------------------------+--------+
```

**Get Output:**
```
wpshadow_debug_mode: 0
```

**Set Output:**
```
Success: Setting updated: wpshadow_debug_mode = 1
```

---

## Usage Examples

### Example 1: Daily Health Check Script

```bash
#!/bin/bash
# daily-health-check.sh
# Run this via cron: 0 2 * * * /path/to/daily-health-check.sh

# Run all diagnostics
wp wpshadow diagnostic run --format=json > /tmp/diagnostics.json

# Check for critical findings
CRITICAL=$(cat /tmp/diagnostics.json | grep -c '"severity":"critical"')

if [ $CRITICAL -gt 0 ]; then
    # Send alert
    echo "Critical findings detected" | mail -s "WPShadow Alert" admin@example.com
    
    # Export activity log for investigation
    wp wpshadow activity export > /var/log/wpshadow-activity.csv
fi

# Clean up
rm /tmp/diagnostics.json
```

### Example 2: Automated Treatment Application

```bash
#!/bin/bash
# auto-treat.sh
# Automatically apply safe treatments

# Define safe treatments
SAFE_TREATMENTS=(
    "debug-mode"
    "query-strings"
    "emoji-scripts"
)

# Apply each treatment
for TREATMENT in "${SAFE_TREATMENTS[@]}"; do
    echo "Applying treatment: $TREATMENT"
    wp wpshadow treatment apply "$TREATMENT"
done

# Generate report
wp wpshadow kpi summary --format=json > /tmp/kpi-report.json
```

### Example 3: Backup Before Major Changes

```bash
#!/bin/bash
# safe-apply-treatment.sh <finding_id>
# Apply treatment with backup

FINDING_ID=$1

if [ -z "$FINDING_ID" ]; then
    echo "Usage: $0 <finding_id>"
    exit 1
fi

# Create backup (using your backup plugin)
echo "Creating backup..."
# wp backup create --plugin=backup-plugin

# Apply treatment
echo "Applying treatment: $FINDING_ID"
if wp wpshadow treatment apply "$FINDING_ID"; then
    echo "Treatment applied successfully"
    
    # Log activity
    wp wpshadow activity list --limit=1 --format=table
else
    echo "Treatment failed, consider restoring backup"
    exit 1
fi
```

### Example 4: Monitor Settings Changes

```bash
#!/bin/bash
# monitor-settings.sh
# Track settings changes over time

# Export current settings
DATE=$(date +%Y%m%d)
wp wpshadow setting --format=json > "/var/log/wpshadow-settings-$DATE.json"

# Compare with yesterday
YESTERDAY=$(date -d "yesterday" +%Y%m%d)
if [ -f "/var/log/wpshadow-settings-$YESTERDAY.json" ]; then
    diff "/var/log/wpshadow-settings-$YESTERDAY.json" \
         "/var/log/wpshadow-settings-$DATE.json" > \
         "/var/log/wpshadow-settings-changes-$DATE.txt"
    
    if [ -s "/var/log/wpshadow-settings-changes-$DATE.txt" ]; then
        echo "Settings changed" | \
            mail -s "WPShadow Settings Changed" admin@example.com
    fi
fi
```

### Example 5: Weekly Security Audit

```bash
#!/bin/bash
# weekly-security-audit.sh

echo "=== WPShadow Weekly Security Audit ===" > /tmp/audit.txt
echo "Date: $(date)" >> /tmp/audit.txt
echo "" >> /tmp/audit.txt

# Run security diagnostics
echo "Security Findings:" >> /tmp/audit.txt
wp wpshadow diagnostic run security --format=yaml >> /tmp/audit.txt
echo "" >> /tmp/audit.txt

# Get activity summary
echo "Recent Security Activity:" >> /tmp/audit.txt
wp wpshadow activity list --category=security --limit=20 >> /tmp/audit.txt
echo "" >> /tmp/audit.txt

# Get KPIs
echo "Security KPIs:" >> /tmp/audit.txt
wp wpshadow kpi summary --format=yaml >> /tmp/audit.txt

# Send report
mail -s "Weekly Security Audit" security@example.com < /tmp/audit.txt

# Clean up
rm /tmp/audit.txt
```

### Example 6: Continuous Integration Testing

```bash
#!/bin/bash
# ci-wpshadow-test.sh
# Run in CI pipeline to catch issues

set -e

# Run all diagnostics
echo "Running WPShadow diagnostics..."
RESULTS=$(wp wpshadow diagnostic run --format=json)

# Check for critical findings
CRITICAL=$(echo "$RESULTS" | grep -c '"severity":"critical"' || true)

if [ $CRITICAL -gt 0 ]; then
    echo "FAIL: Critical findings detected"
    echo "$RESULTS"
    exit 1
fi

# Check for high severity
HIGH=$(echo "$RESULTS" | grep -c '"severity":"high"' || true)

if [ $HIGH -gt 0 ]; then
    echo "WARNING: High severity findings detected"
    echo "$RESULTS"
    # Don't fail, but warn
fi

echo "PASS: All critical checks passed"
exit 0
```

---

## Integration with CI/CD

### GitHub Actions Example

```yaml
name: WPShadow Security Check

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]
  schedule:
    - cron: '0 2 * * *'  # Daily at 2 AM

jobs:
  security-check:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup WordPress
        run: |
          # Setup WordPress environment
          
      - name: Install WP-CLI
        run: |
          curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
          chmod +x wp-cli.phar
          sudo mv wp-cli.phar /usr/local/bin/wp
          
      - name: Install WPShadow
        run: |
          wp plugin install wpshadow --activate
          
      - name: Run Diagnostics
        run: |
          wp wpshadow diagnostic run --format=json > diagnostics.json
          
      - name: Check for Critical Issues
        run: |
          CRITICAL=$(cat diagnostics.json | grep -c '"severity":"critical"' || true)
          if [ $CRITICAL -gt 0 ]; then
            echo "Critical security issues found!"
            cat diagnostics.json
            exit 1
          fi
          
      - name: Upload Results
        uses: actions/upload-artifact@v2
        with:
          name: wpshadow-diagnostics
          path: diagnostics.json
```

---

## Best Practices

### Command Line Usage
1. **Use JSON format for scripting** - Easier to parse in bash/python scripts
2. **Set appropriate limits** - Don't export huge logs unnecessarily
3. **Combine with other tools** - Use grep, jq, awk for filtering
4. **Use dry-run first** - Always test with --dry-run before applying treatments

### Automation
1. **Schedule regular checks** - Use cron for periodic diagnostics
2. **Log all actions** - Keep audit trail of automated changes
3. **Alert on failures** - Email/Slack notifications for critical issues
4. **Backup before treatments** - Always create backups in automated scripts

### CI/CD Integration
1. **Fail on critical findings** - Block deployments with critical issues
2. **Warn on high severity** - Flag but don't block for high severity
3. **Track over time** - Save diagnostic results as artifacts
4. **Compare environments** - Ensure staging matches production expectations

---

## Troubleshooting

### Command Not Found

```bash
# Verify WP-CLI is installed
wp --version

# Verify WPShadow is active
wp plugin list | grep wpshadow

# Check if commands are registered
wp cli command-list | grep wpshadow
```

### Permission Errors

```bash
# Run as correct user (usually www-data or nginx)
sudo -u www-data wp wpshadow diagnostic run

# Or fix permissions
chown -R www-data:www-data /var/www/html
```

### No Output

```bash
# Add --debug flag
wp wpshadow diagnostic run --debug

# Check WP_CLI logs
wp --info
```

---

## Support

For questions about WP-CLI commands:
- **GitHub Issues:** https://github.com/thisismyurl/wpshadow/issues
- **Documentation:** https://wpshadow.com/docs/wp-cli
- **Community Forum:** https://wpshadow.com/community

---

**Philosophy Alignment:**
- Commandment #7: Ridiculously Good - Comprehensive CLI for automation
- Commandment #8: Inspire Confidence - Clear commands with examples
- Accessibility First: All commands documented with usage examples
