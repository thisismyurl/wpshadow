# Guardian Heartbeat System - Quick Reference

## 📊 System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    WordPress Site                            │
│                                                              │
│  ┌────────────────────────────────────────────────────┐    │
│  │           WordPress Heartbeat API                   │    │
│  │        (fires every 15-60 seconds)                  │    │
│  └────────────────────┬───────────────────────────────┘    │
│                       │                                      │
│                       ▼                                      │
│  ┌────────────────────────────────────────────────────┐    │
│  │        Diagnostic_Scheduler                         │    │
│  │     process_heartbeat($response, $data)             │    │
│  └────────────────────┬───────────────────────────────┘    │
│                       │                                      │
│                       ▼                                      │
│  ┌────────────────────────────────────────────────────┐    │
│  │        Guardian_Executor                            │    │
│  │  execute_background_diagnostics()                   │    │
│  │                                                      │    │
│  │  ┌────────────────────────────────────────┐        │    │
│  │  │ 1. Check: Enabled? Server load OK?     │        │    │
│  │  └────────────────────────────────────────┘        │    │
│  │  ┌────────────────────────────────────────┐        │    │
│  │  │ 2. Get background-safe diagnostics     │        │    │
│  │  │    (GUARDIAN_ANYTIME/BACKGROUND)       │        │    │
│  │  └────────────────────────────────────────┘        │    │
│  │  ┌────────────────────────────────────────┐        │    │
│  │  │ 3. Execute max 3, max 100ms            │        │    │
│  │  └────────────────────────────────────────┘        │    │
│  │  ┌────────────────────────────────────────┐        │    │
│  │  │ 4. Log to Activity Logger              │        │    │
│  │  └────────────────────────────────────────┘        │    │
│  └────────────────────┬───────────────────────────────┘    │
│                       │                                      │
│                       ▼                                      │
│  ┌────────────────────────────────────────────────────┐    │
│  │     Return results in heartbeat response            │    │
│  │  {executed: 2, findings: 1, time: 87ms}            │    │
│  └────────────────────────────────────────────────────┘    │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

## 🔄 Scheduled Deep Scan Flow

```
┌─────────────────────────────────────────────────────────────┐
│                  2:00 AM Local Time                          │
│                                                              │
│  ┌────────────────────────────────────────────────────┐    │
│  │           WP-Cron Trigger                           │    │
│  │   'wpshadow_guardian_deep_scan' hook                │    │
│  └────────────────────┬───────────────────────────────┘    │
│                       │                                      │
│                       ▼                                      │
│  ┌────────────────────────────────────────────────────┐    │
│  │        Guardian_Executor                            │    │
│  │   execute_scheduled_diagnostics()                   │    │
│  │                                                      │    │
│  │  ┌────────────────────────────────────────┐        │    │
│  │  │ 1. Verify off-peak time (2-6 AM)       │        │    │
│  │  └────────────────────────────────────────┘        │    │
│  │  ┌────────────────────────────────────────┐        │    │
│  │  │ 2. Get scheduled diagnostics           │        │    │
│  │  │    (GUARDIAN_SCHEDULED/MANUAL)         │        │    │
│  │  └────────────────────────────────────────┘        │    │
│  │  ┌────────────────────────────────────────┐        │    │
│  │  │ 3. Execute all, max 300 seconds        │        │    │
│  │  └────────────────────────────────────────┘        │    │
│  │  ┌────────────────────────────────────────┐        │    │
│  │  │ 4. Store findings, log activity        │        │    │
│  │  └────────────────────────────────────────┘        │    │
│  │  ┌────────────────────────────────────────┐        │    │
│  │  │ 5. Email report (if enabled)           │        │    │
│  │  └────────────────────────────────────────┘        │    │
│  └────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
```

## 🎯 Diagnostic Classification

```
┌──────────────────────────────────────────────────────────────┐
│                 All Diagnostics                               │
│                        │                                       │
│           ┌────────────┴────────────┐                         │
│           │                         │                         │
│           ▼                         ▼                         │
│  ┌─────────────────┐      ┌─────────────────┐               │
│  │  Quick Scan     │      │  Deep Scan      │               │
│  │  (Background)   │      │  (Scheduled)    │               │
│  └─────────────────┘      └─────────────────┘               │
│                                                                │
│  Impact: NONE/MINIMAL    Impact: HIGH/EXTREME                 │
│  Guardian: ANYTIME       Guardian: SCHEDULED                  │
│  Time: < 500ms           Time: > 500ms                        │
│  External: None/Few      External: Many                       │
│                                                                │
│  Examples:               Examples:                            │
│  • admin-email (5ms)     • broken-links (30s+)               │
│  • ssl (300ms)           • pub-alt-text (10s+)               │
│  • backup (50ms)         • outdated-plugins (2s+)            │
│  • database-health       • malware-scanning                   │
│                                                                │
│  Frequency:              Frequency:                           │
│  Every heartbeat cycle   Daily at 2 AM                        │
│  (when due)              (off-peak only)                      │
└──────────────────────────────────────────────────────────────┘
```

## ⚙️ Configuration Matrix

```
┌────────────────────────────────────────────────────────────────┐
│  Setting                        Default    Range/Options        │
├────────────────────────────────────────────────────────────────┤
│  guardian_enabled               true       true/false           │
│  guardian_heartbeat_enabled     true       true/false           │
│  guardian_deep_scan_enabled     true       true/false           │
│  guardian_max_heartbeat_ms      100        50-500 ms            │
│  guardian_off_peak_hours        [2-6]      Array of hours (0-23)│
│  guardian_email_reports         false      true/false           │
│  guardian_email_address         admin@     Valid email          │
└────────────────────────────────────────────────────────────────┘
```

## 🛡️ Performance Safeguards

```
┌────────────────────────────────────────────────────────────────┐
│                    Heartbeat Execution                          │
├────────────────────────────────────────────────────────────────┤
│  Max time per cycle:           100ms (configurable 50-500ms)   │
│  Max diagnostics per cycle:    3 diagnostics                   │
│  Server load threshold:        Skip if > 70% CPU               │
│  Capability required:          manage_options                  │
│  Skip during:                  Other admin-ajax requests       │
└────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────┐
│                    Scheduled Execution                          │
├────────────────────────────────────────────────────────────────┤
│  Max execution time:           300 seconds                     │
│  Only during:                  Off-peak hours (2-6 AM)         │
│  Skip if:                      Core/plugin update running      │
│  Skip if:                      Backup in progress              │
│  Locking:                      WP-Cron prevents overlaps       │
└────────────────────────────────────────────────────────────────┘
```

## 📝 Activity Logging

All Guardian activity is logged to Activity Logger:

```php
// Background execution
Activity_Logger::log(
    'guardian_execution',
    'Guardian executed 2 background diagnostics',
    'guardian',
    [
        'diagnostics_run' => ['admin-email', 'ssl'],
        'execution_time_ms' => 87,
        'findings_count' => 1,
        'trigger' => 'heartbeat'
    ]
);

// Finding detection
Activity_Logger::log(
    'diagnostic_finding',
    'Found issue: SSL Certificate Expiring',
    'security',
    [
        'finding_id' => 'ssl',
        'severity' => 'high',
        'source' => 'guardian_auto'
    ]
);

// Deep scan
Activity_Logger::log(
    'guardian_deep_scan',
    'Guardian executed 15 scheduled diagnostics',
    'guardian',
    [
        'diagnostics_run' => [...],
        'execution_time_ms' => 12453,
        'findings_count' => 3,
        'trigger' => 'scheduled'
    ]
);
```

## 🎯 Decision Tree

```
                     User Active in Admin
                            │
                            ▼
                    Heartbeat Fires
                            │
                            ▼
                 Guardian Enabled? ──No──> Skip
                            │
                           Yes
                            ▼
              Heartbeat Execution Enabled? ──No──> Skip
                            │
                           Yes
                            ▼
                Server Load Acceptable? ──No──> Skip
                            │
                           Yes
                            ▼
              Any Diagnostics Due? ──No──> Skip
                            │
                           Yes
                            ▼
                Execute up to 3 diagnostics
                  (max 100ms total)
                            │
                            ▼
                  Log execution results
                            │
                            ▼
              Return data in heartbeat response
```

## 📊 KPI Tracking

Guardian automatically tracks:

```
Metrics Tracked:
├─ Diagnostics executed (per day/week/month)
├─ Findings detected (by severity)
├─ Execution time (average/total)
├─ Background vs scheduled ratio
└─ Issues prevented (value demonstration)

Dashboard Display:
├─ "Guardian has run 247 diagnostics this month"
├─ "15 issues detected and resolved automatically"
├─ "Last execution: 5 minutes ago"
└─ "Next deep scan: Tonight at 2:00 AM"
```

## 🔗 Integration Points

```
Guardian_Executor integrates with:

├─ Performance_Impact_Classifier
│  └─ Determines if diagnostic is background-safe
│
├─ Diagnostic_Scheduler
│  ├─ Checks if diagnostic should run (frequency)
│  └─ Records execution time
│
├─ Diagnostic_Registry
│  └─ Gets list of all diagnostics
│
├─ Activity_Logger
│  └─ Logs all executions and findings
│
├─ KPI_Tracker
│  └─ Tracks metrics for value demonstration
│
└─ WordPress Heartbeat API
   └─ Triggers background execution
```

## 🚀 Quick Start (Developer)

### Enable Guardian
```php
update_option('wpshadow_guardian_enabled', true);
```

### Trigger Manual Execution
```php
// Background diagnostics
Guardian_Executor::execute_background_diagnostics();

// Scheduled diagnostics
Guardian_Executor::execute_scheduled_diagnostics();
```

### Check if Diagnostic Should Run
```php
Diagnostic_Scheduler::should_run('ssl');
```

### Get Execution Status
```php
$last_run = get_option('wpshadow_last_run_ssl');
$next_run = Diagnostic_Scheduler::get_next_run_time('ssl');
```

### Configure Off-Peak Hours
```php
update_option('wpshadow_guardian_off_peak_hours', [2, 3, 4, 5]);
```

## 🐛 Troubleshooting

### Guardian Not Running?
1. Check: `get_option('wpshadow_guardian_enabled')`
2. Check: `get_option('wpshadow_guardian_heartbeat_enabled')`
3. Check Activity Log for execution events
4. Verify heartbeat is working: Check browser Network tab
5. Check server load: Guardian skips if > 70% CPU

### Diagnostics Not Executing?
1. Check if diagnostic is background-safe:
   ```php
   $impact = Performance_Impact_Classifier::predict('diagnostic-slug');
   ```
2. Check if diagnostic is due:
   ```php
   Diagnostic_Scheduler::should_run('diagnostic-slug');
   ```
3. Check last execution time:
   ```php
   get_option('wpshadow_last_run_diagnostic-slug');
   ```

### Deep Scans Not Running?
1. Verify it's off-peak time: `Guardian_Executor::is_off_peak_time()`
2. Check WP-Cron is enabled: `wp_get_schedules()`
3. Verify hook registered: `wp_next_scheduled('wpshadow_guardian_deep_scan')`

---

**Files:**
- Code: `includes/core/class-guardian-executor.php`
- Scheduler: `includes/utils/class-diagnostic-scheduler.php`
- Bootstrap: `includes/core/class-plugin-bootstrap.php`
- Docs: `docs/FEATURES/GUARDIAN_HEARTBEAT_INTEGRATION.md`
- Summary: `docs/FEATURES/GUARDIAN_IMPLEMENTATION_SUMMARY.md`
