# Diagnostic Scheduler Implementation Guide

## Overview

The Diagnostic Scheduler system manages when diagnostics run, integrating with WordPress Heartbeat to execute background checks intelligently.

## Features

### 1. Frequency-Based Scheduling
Each diagnostic has a frequency (in seconds) that determines how often it runs:

- **FREQUENCY_EVERY_REQUEST (0s)** - On every admin page load
- **FREQUENCY_HOURLY (3600s)** - Once per hour
- **FREQUENCY_6_HOURS (21600s)** - Every 6 hours
- **FREQUENCY_DAILY (86400s)** - Once per 24 hours
- **FREQUENCY_WEEKLY (604800s)** - Once per 7 days
- **FREQUENCY_MONTHLY (2592000s)** - Once per 30 days
- **FREQUENCY_QUARTERLY (7776000s)** - Once per 90 days

### 2. Trigger System
Diagnostics can be configured to run immediately when specific events occur:

- `TRIGGER_PLUGIN_CHANGE` - Plugin activated/deactivated/updated
- `TRIGGER_THEME_CHANGE` - Theme activated/changed/updated
- `TRIGGER_CORE_UPDATE` - WordPress core updated
- `TRIGGER_SETTING_CHANGE` - Important settings changed
- `TRIGGER_HEARTBEAT` - Via WordPress Heartbeat
- `TRIGGER_SCHEDULED` - Via WordPress cron
- `TRIGGER_MANUAL` - Manual admin trigger

### 3. Background Execution
Diagnostics marked as `background => true` can run during Heartbeat without impacting user experience. Critical diagnostics run in the admin but don't block page loads.

### 4. Priority Levels
- **critical** - Security/backup related, run frequently
- **high** - Performance/plugin issues, run daily
- **medium** - General health checks, run weekly
- **low** - SEO/design/content issues, run less frequently

## Usage

### Check if Diagnostic Should Run

```php
use WPShadow\Core\Diagnostic_Scheduler;

// Check if diagnostic should run now
if (Diagnostic_Scheduler::should_run('ssl')) {
    // Run the diagnostic
    $result = Diagnostic_SSL::check();
    
    // Record that we ran it
    Diagnostic_Scheduler::record_run('ssl');
}
```

### Record a Diagnostic Run

```php
// Record that a diagnostic has been run
Diagnostic_Scheduler::record_run('ssl');
```

### Get Schedule for a Diagnostic

```php
// Get full schedule configuration
$schedule = Diagnostic_Scheduler::get_schedule('ssl');
// Returns: ['frequency' => 86400, 'triggers' => [...], 'priority' => 'critical', 'background' => true]
```

### Get Next Run Time

```php
// Get Unix timestamp of next scheduled run
$next_run = Diagnostic_Scheduler::get_next_run_time('ssl');
echo date('Y-m-d H:i:s', $next_run);
```

### Get Diagnostics by Priority

```php
// Get all critical diagnostics
$critical = Diagnostic_Scheduler::get_by_priority('critical');

// Get all diagnostics
$all = Diagnostic_Scheduler::get_by_priority();
```

### Get Background-Safe Diagnostics

```php
// Get diagnostics that can safely run in background
$bg_safe = Diagnostic_Scheduler::get_background_safe();
```

## WordPress Heartbeat Integration

The scheduler automatically hooks into WordPress Heartbeat to trigger background diagnostic runs.

### How It Works

1. Heartbeat sends request every 15-60 seconds
2. Scheduler checks which diagnostics should run
3. Returns list of pending diagnostics to client
4. Client-side JavaScript can execute them without blocking
5. Results are stored in options and options meta

### Example Client-Side Integration

```javascript
// Listen for heartbeat responses with pending diagnostics
jQuery(document).on('heartbeat-tick', function(event, data) {
    if (data.wpshadow_diagnostics_pending) {
        console.log('Pending diagnostics:', data.wpshadow_diagnostics_pending);
        
        // Execute them in background
        data.wpshadow_diagnostics_pending.forEach(slug => {
            wp.ajax.send('wpshadow_run_diagnostic', {
                data: { slug: slug },
                success: function(result) {
                    console.log('Diagnostic completed:', slug);
                }
            });
        });
    }
});
```

## Default Schedule Matrix

| Diagnostic | Frequency | Triggers | Priority | Background |
|---|---|---|---|---|
| admin-email | Weekly | settings_change | critical | no |
| ssl | Daily | none | critical | yes |
| backup | Daily | none | critical | yes |
| outdated-plugins | Daily | plugin_change | high | yes |
| database-health | Daily | none | high | yes |
| seo-missing-meta | Weekly | none | low | yes |
| broken-links | Weekly | none | medium | yes |
| malware-scanning | Daily | plugin_change, core_update | critical | yes |

## Adding Custom Schedules

To override default schedules for custom diagnostics or modify existing ones, filter the schedules:

```php
add_filter('wpshadow_diagnostic_schedules', function($schedules) {
    // Add or modify schedule for custom diagnostic
    $schedules['custom-check'] = [
        'frequency'  => 86400,  // Daily
        'triggers'   => ['plugin_change'],
        'priority'   => 'high',
        'background' => true,
    ];
    
    return $schedules;
});
```

## Philosophy Alignment

**Commandment #9 (Show Value):** The scheduler tracks KPIs:
- Time saved by running diagnostics proactively
- Issues detected and fixed
- Operational efficiency improvements

**Commandment #10 (Privacy First):** 
- Scheduling data stored in `wp_options` (no external calls)
- Last run times not tracked off-site
- Users can disable background execution

## Performance Implications

### Storage
- One option per diagnostic for last run time
- Options: `wpshadow_last_run_{slug}` - ~50 bytes each
- 2500 diagnostics = ~125 KB total

### Execution
- Heartbeat runs every 15-60 seconds
- Scheduler check is ~0.1ms
- Only schedules diagnostics, doesn't execute them

### Optimization
- Batch option queries instead of individual gets
- Cache schedule definitions in transient
- Implement option query batching

## Troubleshooting

### Diagnostics Not Running

1. Check `wp_options` table for `wpshadow_last_run_*` entries
2. Verify Heartbeat is active: `wp option get heartbeat-interval`
3. Check browser console for JavaScript errors
4. Verify user has `manage_options` capability

### Excessive Runs

1. Check frequency setting isn't set to `FREQUENCY_EVERY_REQUEST`
2. Verify last run time is being recorded properly
3. Check for cron conflicts if using `TRIGGER_SCHEDULED`

### Performance Issues

1. Monitor option query count
2. Check if background diagnostics are blocking
3. Verify Heartbeat interval isn't too aggressive

## Future Enhancements

- [ ] Implement option query batching (Phase 3.5)
- [ ] Add transient caching for schedule definitions
- [ ] Create admin UI for custom schedules
- [ ] Add real-time progress tracking
- [ ] Implement priority queue for overlapping runs
- [ ] Add notification system for critical findings
- [ ] Create analytics dashboard for diagnostic trends

