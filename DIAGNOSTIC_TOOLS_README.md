# WPShadow Diagnostic Tools - Usage Guide

Quick reference for diagnostic system tools and maintenance scripts.

## Tools Directory

All maintenance and utility scripts are located in `/tools/`:

### 1. Batch Diagnostic Fixer
**File:** `tools/batch-diagnostic-fixer.php`

Fixes all 2,500+ diagnostic stub files for consistency.

**Usage:**
```bash
cd /workspaces/wpshadow
php tools/batch-diagnostic-fixer.php
```

**What It Does:**
- Adds `declare(strict_types=1);` to all files
- Standardizes namespace to `WPShadow\Diagnostics`
- Adds `use WPShadow\Core\Diagnostic_Base;` statements
- Fixes class inheritance to extend `Diagnostic_Base`
- Corrects method signatures to `public static function check(): ?array`
- Removes duplicate inheritance declarations
- Normalizes whitespace

**Output:**
- Progress indicator (dot per file)
- Summary of files fixed/errors/skipped
- Useful when re-running after adding new diagnostics

---

### 2. Quality Auditor
**File:** `tools/audit-diagnostic-quality.php`

Validates all diagnostic files meet quality standards.

**Usage:**
```bash
php tools/audit-diagnostic-quality.php
```

**Checks Performed:**
- ✓ Proper PHP opening tag
- ✓ `declare(strict_types=1);` present
- ✓ Correct namespace format
- ✓ Diagnostic_Base use statement
- ✓ Class extends Diagnostic_Base
- ✓ Proper `check(): ?array` signature
- ✓ Return statement exists
- ✓ Required return fields present
- ✓ No duplicate extends clauses

**Output:**
- File-by-file pass/fail status
- Summary statistics
- List of issues found
- Pass rate percentage

**Useful When:**
- Validating new diagnostics
- Before deploying changes
- Troubleshooting diagnostic issues
- Adding new bulk diagnostics

---

### 3. Single File Fixer (Manual Use)
**File:** `tools/fix-diagnostic-files.php`

Analyzes and fixes individual diagnostic files.

**Usage:**
```bash
# From WordPress admin or CLI
wp eval-file wpshadow/tools/fix-diagnostic-files.php
```

**When to Use:**
- Fixing individual problematic diagnostics
- Detailed issue reporting needed
- Manual inspection of specific files

---

## Diagnostic Scheduler

**Main Class:** `includes/core/class-diagnostic-scheduler.php`

### Initialization (in wpshadow.php)
```php
use WPShadow\Core\Diagnostic_Scheduler;

if (is_admin()) {
    Diagnostic_Scheduler::init();
}
```

### Key Methods

#### Check if Diagnostic Should Run
```php
if (Diagnostic_Scheduler::should_run('ssl')) {
    $result = run_diagnostic();
    Diagnostic_Scheduler::record_run('ssl');
}
```

#### Get Schedule Configuration
```php
$schedule = Diagnostic_Scheduler::get_schedule('ssl');
// Returns: [
//   'frequency' => 86400,
//   'triggers' => [],
//   'priority' => 'critical',
//   'background' => true
// ]
```

#### Get Next Run Time
```php
$timestamp = Diagnostic_Scheduler::get_next_run_time('ssl');
echo 'Next run: ' . date('Y-m-d H:i:s', $timestamp);
```

#### Get Diagnostics by Priority
```php
$critical = Diagnostic_Scheduler::get_by_priority('critical');
$all = Diagnostic_Scheduler::get_by_priority();
```

#### Get Background-Safe Diagnostics
```php
$bg_safe = Diagnostic_Scheduler::get_background_safe();
// Safe to run during Heartbeat
```

---

## Maintenance Tasks

### After Adding New Diagnostics
```bash
# 1. Validate syntax
php -l includes/diagnostics/class-diagnostic-new-check.php

# 2. Run batch fixer (if bulk added)
php tools/batch-diagnostic-fixer.php

# 3. Run quality audit
php tools/audit-diagnostic-quality.php
```

### Before Deployment
```bash
# Full validation check
php tools/audit-diagnostic-quality.php

# Verify no syntax errors
find includes/diagnostics -name "*.php" | xargs php -l
```

### Troubleshooting Failed Diagnostics
1. Check scheduler status: `get_option('wpshadow_last_run_slug')`
2. Run quality audit on specific file
3. Check method signature matches pattern
4. Verify return array has required fields

---

## Frequency Reference

Use these constants when customizing schedules:

```php
use WPShadow\Core\Diagnostic_Scheduler;

Diagnostic_Scheduler::FREQUENCY_EVERY_REQUEST;  // 0
Diagnostic_Scheduler::FREQUENCY_HOURLY;         // 3600
Diagnostic_Scheduler::FREQUENCY_6_HOURS;        // 21600
Diagnostic_Scheduler::FREQUENCY_DAILY;          // 86400
Diagnostic_Scheduler::FREQUENCY_WEEKLY;         // 604800
Diagnostic_Scheduler::FREQUENCY_MONTHLY;        // 2592000
Diagnostic_Scheduler::FREQUENCY_QUARTERLY;      // 7776000
```

---

## Trigger Reference

Events that can trigger immediate diagnostic runs:

```php
Diagnostic_Scheduler::TRIGGER_PLUGIN_CHANGE;      // Plugin activate/deactivate/update
Diagnostic_Scheduler::TRIGGER_THEME_CHANGE;       // Theme activate/change/update
Diagnostic_Scheduler::TRIGGER_CORE_UPDATE;        // WordPress core update
Diagnostic_Scheduler::TRIGGER_SETTING_CHANGE;     // Important settings changed
Diagnostic_Scheduler::TRIGGER_HEARTBEAT;          // Via Heartbeat API
Diagnostic_Scheduler::TRIGGER_SCHEDULED;          // Via WordPress cron
Diagnostic_Scheduler::TRIGGER_MANUAL;             // Manual admin trigger
```

---

## Default Schedules

| Diagnostic | Frequency | Triggers | Priority |
|---|---|---|---|
| admin-email | Weekly | settings_change | critical |
| ssl | Daily | none | critical |
| backup | Daily | none | critical |
| outdated-plugins | Daily | plugin_change | high |
| abandoned-plugins | Weekly | plugin_change | high |
| database-health | Daily | none | high |
| database-revisions | Daily | none | medium |
| broken-links | Weekly | none | medium |
| malware-scanning | Daily | plugin_change, core_update | critical |
| seo-missing-meta | Weekly | none | low |

See `includes/core/class-diagnostic-scheduler.php` for complete list.

---

## File Structure

```
wpshadow/
├── includes/
│   ├── core/
│   │   └── class-diagnostic-scheduler.php      # Main scheduler
│   └── diagnostics/
│       ├── class-diagnostic-admin-email.php
│       ├── class-diagnostic-ssl.php
│       └── ...2,508 more files...
├── tools/
│   ├── batch-diagnostic-fixer.php              # Batch fixer
│   ├── audit-diagnostic-quality.php            # Quality checker
│   └── fix-diagnostic-files.php                # Single-file fixer
└── docs/
    ├── DIAGNOSTIC_SCHEDULER_GUIDE.md           # Full guide
    └── DIAGNOSTIC_SCHEDULER_COMPLETION_REPORT.md
```

---

## Common Issues & Solutions

### Issue: Diagnostics Not Running
**Check:**
1. Scheduler initialized: `is_admin()` hook present?
2. Last run recorded: `get_option('wpshadow_last_run_ssl')`
3. Frequency correct: `get_schedule('ssl')['frequency']`
4. Current time >= last_run + frequency

### Issue: Heartbeat Not Triggering
**Check:**
1. Heartbeat enabled: `wp option get heartbeat-interval`
2. Background flag: `get_schedule(slug)['background']`
3. Browser console for errors
4. User capability: `current_user_can('manage_options')`

### Issue: Too Many Duplicate Runs
**Solution:**
1. Verify `record_run()` being called
2. Check for multiple initialization calls
3. Verify last_run timestamp is updating
4. Check WordPress cron (may be interfering)

---

## Development Notes

### Adding Custom Schedule
```php
add_filter('wpshadow_diagnostic_schedules', function($schedules) {
    $schedules['custom-diagnostic'] = [
        'frequency'  => 86400,  // Daily
        'triggers'   => ['plugin_change'],
        'priority'   => 'high',
        'background' => true,
    ];
    return $schedules;
});
```

### Extending Diagnostic_Base
```php
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;

class Diagnostic_Custom extends Diagnostic_Base {
    protected static $slug = 'custom';
    protected static $title = 'Custom Check';
    
    public static function check(): ?array {
        // Return array if issue found, null otherwise
        return null;  // No issues
    }
}
```

---

## Performance Tips

1. **Use Background Flag** - Mark heartbeat-safe diagnostics with `background => true`
2. **Set Realistic Frequencies** - Don't set all to hourly
3. **Batch Related Checks** - Group checks by category to share queries
4. **Cache Results** - Store diagnostic results in transients
5. **Monitor Execution** - Track which diagnostics take longest

---

## Related Documentation

- **Full Guide:** [docs/DIAGNOSTIC_SCHEDULER_GUIDE.md](../docs/DIAGNOSTIC_SCHEDULER_GUIDE.md)
- **Completion Report:** [docs/DIAGNOSTIC_SCHEDULER_COMPLETION_REPORT.md](../docs/DIAGNOSTIC_SCHEDULER_COMPLETION_REPORT.md)
- **Architecture:** [docs/ARCHITECTURE.md](../docs/ARCHITECTURE.md)
- **Coding Standards:** [docs/CODING_STANDARDS.md](../docs/CODING_STANDARDS.md)

---

## Support

For issues with the diagnostic system:
1. Run `php tools/audit-diagnostic-quality.php`
2. Check `includes/core/class-diagnostic-scheduler.php` logs
3. Review `docs/DIAGNOSTIC_SCHEDULER_GUIDE.md` troubleshooting section
4. Check WordPress Heartbeat status

