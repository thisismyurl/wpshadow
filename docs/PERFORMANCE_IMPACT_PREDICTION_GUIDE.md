# Performance Impact Prediction & Guardian Integration Guide

## Overview

Before running any diagnostic, we can now **predict its performance impact** and automatically categorize it for:
- **Anytime execution** (Guardian can run any time)
- **Background execution** (Scheduled background jobs)
- **Off-peak only** (Run during low-traffic periods)
- **Manual only** (High-risk operations requiring user confirmation)

## Impact Classification System

### 7 Impact Levels

```
IMPACT_NONE (0-5ms)         ✓ Negligible - run anytime
IMPACT_MINIMAL (5-25ms)     ✓ Minimal - run anytime
IMPACT_LOW (25-100ms)       ✓ Low - run anytime
IMPACT_MEDIUM (100-500ms)   ⚠ Medium - batch acceptable
IMPACT_HIGH (500ms-2s)      ⚠ High - off-peak preferred
IMPACT_VERY_HIGH (2-5s)     ⚠ Very High - off-peak strongly recommended
IMPACT_EXTREME (5s+)        🔴 Extreme - manual/scheduled only
```

### Guardian Suitability Contexts

```
GUARDIAN_ANYTIME       → Can execute during any request/time
GUARDIAN_BACKGROUND    → Should use background job queue
GUARDIAN_SCHEDULED     → Scheduled job (daily/weekly)
GUARDIAN_MANUAL        → User-triggered or one-time only
```

## Usage Examples

### Predict Impact Before Running

```php
use WPShadow\Core\Performance_Impact_Classifier;

// Predict impact for a specific diagnostic
$prediction = Performance_Impact_Classifier::predict('ssl');

// Returns:
[
    'slug'              => 'ssl',
    'impact_level'      => 'medium',           // IMPACT_MEDIUM
    'estimated_ms'      => 300.0,              // Estimated milliseconds
    'guardian_suitable' => 'background',       // GUARDIAN_BACKGROUND
    'description'       => 'Remote SSL certificate check',
    'factors' => [
        'ssl_cert_check' => 1,
        'http_get_external' => 1,
    ]
]
```

### Filter Diagnostics by Impact

```php
// Get all low-impact diagnostics (anytime safe)
$low_impact = Performance_Impact_Classifier::get_by_impact('low');

// Get all medium-impact diagnostics
$medium_impact = Performance_Impact_Classifier::get_by_impact('medium');

// Get all diagnostics (organized by impact level)
$all = Performance_Impact_Classifier::get_by_impact();
```

### Get Guardian-Suitable Diagnostics

```php
// Get diagnostics Guardian can run anytime
$anytime = Performance_Impact_Classifier::get_guardian_suitable('anytime');

// Get diagnostics suitable for background jobs
$background = Performance_Impact_Classifier::get_guardian_suitable('background');

// Get diagnostics suitable for scheduled jobs only
$scheduled = Performance_Impact_Classifier::get_guardian_suitable('scheduled');

// Get all Guardian-suitable diagnostics (anytime + background)
$all_guardian = Performance_Impact_Classifier::get_guardian_suitable();
```

### Get Off-Peak Only Diagnostics

```php
// Get high/very-high impact diagnostics (off-peak recommended)
$off_peak = Performance_Impact_Classifier::get_off_peak_suitable();

// These should only be scheduled for:
// - 2-6 AM (lowest traffic)
// - Weekends during low-traffic hours
// - One-time manual execution
```

### Get Statistics

```php
$stats = Performance_Impact_Classifier::get_stats();

// Returns:
[
    'total' => 87,                           // Total diagnostics classified
    'by_impact' => [
        'minimal' => 5,
        'low'     => 12,
        'medium'  => 23,
        'high'    => 25,
        'very_high' => 18,
        'extreme' => 4,
    ],
    'by_guardian' => [
        'anytime'   => 17,                   // Safe for any request
        'background' => 35,                  // Use background queue
        'scheduled' => 28,                   // Off-peak jobs
        'manual'    => 7,                    // Manual/one-time only
    ],
    'avg_ms'   => 487.5,                     // Average impact across all
    'total_ms' => 42412.5,                   // If all ran in sequence
]
```

### Display Impact Information

```php
// Get human-readable impact label
$label = Performance_Impact_Classifier::get_impact_label('high');

// Returns:
[
    'label'  => 'High',
    'color'  => 'orange',
    'emoji'  => '⚠⚠',
    'ms_max' => 2000,
]

// Use in UI:
echo sprintf(
    '⚠ <span style="color:%s">%s Impact</span> (~%dms)',
    $label['color'],
    $label['label'],
    $label['ms_max']
);
```

## Pre-Classified Diagnostics

### Low Impact (Anytime Safe)

```
✓ admin-email (5ms)          - Get option, validate email
✓ admin-username (5ms)       - Query default usernames
✓ https-everywhere (10ms)    - Check HTTPS options
✓ head-cleanup (25ms)        - Check header filters
✓ database-revisions (10ms)  - Count revision count
✓ autoloaded-options (50ms)  - Aggregation query
```

### Medium Impact (Background/Batch)

```
⚠ ssl (300ms)                  - SSL certificate check
⚠ plugin-conflicts (250ms)     - Analyze interdependencies
⚠ database-health (150ms)      - Multiple health checks
⚠ core-backups-recent (75ms)   - Check recent backups exist
⚠ seo-missing-meta (400ms)     - Query posts without meta
```

### High Impact (Off-Peak)

```
⚠⚠ outdated-plugins (800ms)          - WordPress.org API calls
⚠⚠ core-homepage-load-time (1000ms)  - HTTP request to homepage
⚠⚠ seo-missing-h1 (1200ms)           - Fetch post content
⚠⚠ core-response-time (1500ms)       - Multiple page requests
```

### Very High Impact (Off-Peak Strongly)

```
⚠⚠⚠ abandoned-plugins (2500ms)      - Multiple API calls + analysis
⚠⚠⚠ pub-alt-text-coverage (3000ms)  - Scan 5K+ posts/images
⚠⚠⚠ database-malware (3000ms)       - Full database scan
```

### Extreme Impact (Manual Only)

```
🔴 backup (30000ms+)              - Full backup creation
🔴 broken-links (45000ms+)        - HTTP check every link
🔴 database-malware-deep (50000ms) - Deep pattern matching
```

## Guardian Integration Strategy

### 1. Anytime Execution (Guardian Continuous)
Run during **every Heartbeat** or **every user request**:
```php
$always_safe = Performance_Impact_Classifier::get_guardian_suitable('anytime');
// → 5-10 very low impact diagnostics
// Total impact: ~50-100ms combined
```

### 2. Background Queue (Guardian Jobs)
Run as **background jobs** when queue available:
```php
$background = Performance_Impact_Classifier::get_guardian_suitable('background');
// → 15-20 medium-impact diagnostics
// Schedule: Multiple per hour, stagger execution
// Total time: ~6-8 hours per full cycle
```

### 3. Scheduled Jobs (Guardian Cron)
Run on **fixed schedule**:
```php
$scheduled = Performance_Impact_Classifier::get_off_peak_suitable();
// → 25-30 high/very-high impact diagnostics

// Examples:
// Hourly (2-6 AM):     outdated-plugins, response-time
// Daily (3 AM):        abandoned-plugins, alt-text-coverage
// Weekly (Sunday 2AM): malware-scan, broken-links check
```

### 4. Manual Only (Guardian Admin)
**User-triggered** from admin interface:
```php
$manual_only = array_filter(
    Performance_Impact_Classifier::get_off_peak_suitable(),
    fn($config) => $config['guardian'] === 'manual'
);
// → Backup creation, deep malware scans, link audits
// Requires explicit user request + confirmation
```

## Dashboard Widget Display

```
WPShadow Guardian Status
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✓ Anytime Diagnostics (17)
  └─ Running: ssl-check, admin-email, https-everywhere
  └─ Next batch: 12 diagnostics in 2 hours

⚡ Background Queue (35)
  └─ Pending: 8 diagnostics
  └─ Processing: outdated-plugins (45s remaining)
  └─ Completed today: 24

⏰ Off-Peak Schedule (28)
  └─ Next scheduled: 2026-01-23 02:00 (abandoned-plugins)
  └─ Running weekly: malware-scan, link-audit
  └─ Completed this week: 18

🔒 Manual/Sensitive (7)
  └─ Full Backup:        [Create Now]
  └─ Deep Malware Scan:  [Run Manually]
  └─ Last run: 2 weeks ago

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Server Health: ✓ 92%
Last full cycle: 2 days ago
```

## Implementation in Diagnostic_Scheduler

Integrate impact predictions into the scheduler:

```php
// In wpshadow.php or diagnostic registry

use WPShadow\Core\Performance_Impact_Classifier;
use WPShadow\Core\Diagnostic_Scheduler;

// Enhance scheduler with impact data
$diagnostic_config = array_map(function($config) use ($slug) {
    $impact = Performance_Impact_Classifier::predict($slug);
    return array_merge($config, [
        'impact_ms'      => $impact['estimated_ms'],
        'guardian_mode'  => $impact['guardian_suitable'],
        'impact_level'   => $impact['impact_level'],
    ]);
}, Diagnostic_Scheduler::get_default_schedules());
```

## Smart Execution Rules

Based on impact predictions:

```php
// Never run high-impact during user requests
if ($impact_ms > 500 && is_user_request()) {
    queue_for_background();
}

// Very high impact? Only off-peak
if ($impact_ms > 2000) {
    if (!is_off_peak_hours()) {
        schedule_for_next_off_peak();
        return;
    }
}

// Batch similar impacts together
$current_batch_impact = 0;
foreach ($diagnostics as $slug => $config) {
    if ($current_batch_impact + $config['impact_ms'] < 5000) {
        add_to_batch($slug);
        $current_batch_impact += $config['impact_ms'];
    } else {
        execute_batch();
        start_new_batch($slug);
    }
}
```

## API for Adding Custom Impacts

When creating new diagnostics, specify impact:

```php
// In diagnostic class doc comment
/**
 * Diagnostic: Complex Security Scan
 * 
 * Impact: High (~800ms)
 * Guardian Mode: Background only
 * Factors:
 *   - 5 database full scans
 *   - 1 external API call
 *   - Complex regex patterns on 10K items
 */
class Diagnostic_Complex_Security extends Diagnostic_Base {
    
    public static function get_impact(): array {
        return [
            'level'     => Performance_Impact_Classifier::IMPACT_HIGH,
            'ms'        => 800,
            'guardian'  => Performance_Impact_Classifier::GUARDIAN_BACKGROUND,
            'factors'   => [
                'db_query_full_scan' => 5,
                'http_get_external' => 1,
                'regex_complex' => 1,
                'array_operations_10k' => 1,
            ]
        ];
    }
}
```

## Benefits

✅ **Predictive** - Know impact before running
✅ **Guardian-Aware** - Designates which tests Guardian can execute
✅ **Intelligent Batching** - Combines low-impact tests
✅ **Off-Peak Protection** - Prevents server slowdowns during traffic
✅ **User Confidence** - Shows why tests take time (transparency)
✅ **Automated Scheduling** - System auto-categorizes new diagnostics
✅ **Performance Monitoring** - Track actual vs predicted impact

## Next Steps

1. Integrate into Diagnostic_Scheduler
2. Add impact display to dashboard
3. Create Guardian API with impact-aware execution
4. Monitor actual vs predicted times
5. Calibrate factors based on real server profiles
6. Create off-peak schedule manager

