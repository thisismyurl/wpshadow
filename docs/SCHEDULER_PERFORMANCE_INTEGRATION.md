# Diagnostic Scheduler + Performance Impact Integration

## Overview

The `Performance_Impact_Classifier` works alongside `Diagnostic_Scheduler` to make intelligent decisions about WHEN diagnostics run based on their server impact.

**Key Question Answered:** "How much will this diagnostic impact the server, and when should it run?"

---

## Quick Integration Pattern

### 1. In Diagnostic_Scheduler, Add Impact Metadata

```php
protected static $schedule_definitions = [
    'admin-email' => [
        'frequency'   => self::FREQUENCY_WEEKLY,
        'triggers'    => [ self::TRIGGER_SETTING_CHANGE ],
        'priority'    => 'critical',
        'background'  => false,
        'impact'      => Performance_Impact_Classifier::IMPACT_MINIMAL,      // ← NEW
        'guardian'    => Performance_Impact_Classifier::GUARDIAN_ANYTIME,    // ← NEW
    ],
    'outdated-plugins' => [
        'frequency'   => self::FREQUENCY_DAILY,
        'triggers'    => [ self::TRIGGER_PLUGIN_CHANGE ],
        'priority'    => 'high',
        'background'  => true,
        'impact'      => Performance_Impact_Classifier::IMPACT_HIGH,         // ← NEW
        'guardian'    => Performance_Impact_Classifier::GUARDIAN_SCHEDULED,  // ← NEW
    ],
];
```

### 2. Update should_run() Method to Check Impact + Time

```php
public static function should_run( string $slug, int $now = 0 ): bool {
    if ( empty( $now ) ) {
        $now = time();
    }

    $definition = self::$schedule_definitions[ $slug ] ?? null;
    if ( ! $definition ) {
        return false;
    }

    // EXISTING: Check if enough time has passed since last run
    $last_run = self::get_last_run( $slug );
    if ( $now - $last_run < $definition['frequency'] ) {
        return false;
    }

    // ← NEW: Check if now is optimal time based on impact
    $impact = $definition['impact'] ?? null;
    $guardian = $definition['guardian'] ?? null;

    if ( $impact && $guardian ) {
        if ( ! self::is_optimal_time_to_run( $guardian, $impact ) ) {
            return false;  // Defer to better time
        }
    }

    return true;
}

/**
 * Determine if now is good time to run diagnostic based on impact
 */
protected static function is_optimal_time_to_run( string $guardian, string $impact ): bool {
    // These diagnostics are always safe
    if ( $guardian === Performance_Impact_Classifier::GUARDIAN_ANYTIME ) {
        return true;
    }

    // Background jobs OK if not in critical request path
    if ( $guardian === Performance_Impact_Classifier::GUARDIAN_BACKGROUND ) {
        // Only run if this is a background task (not direct user request)
        return defined( 'DOING_AJAX' ) && DOING_AJAX || 
               defined( 'DOING_CRON' ) && DOING_CRON ||
               did_action( 'wp_loaded' ) && ! did_action( 'admin_init' );
    }

    // Scheduled diagnostics - only during low traffic windows
    if ( $guardian === Performance_Impact_Classifier::GUARDIAN_SCHEDULED ) {
        $hour = intval( gmdate( 'H' ) );
        // Run during 2-6 AM UTC (typical low traffic)
        return $hour >= 2 && $hour < 6;
    }

    // Manual only - never auto-run
    if ( $guardian === Performance_Impact_Classifier::GUARDIAN_MANUAL ) {
        return false;
    }

    return false;
}

/**
 * Get human-readable explanation for why diagnostic should/shouldn't run now
 */
public static function get_run_decision_reason( string $slug ): string {
    $definition = self::$schedule_definitions[ $slug ] ?? null;
    if ( ! $definition ) {
        return 'Diagnostic not found';
    }

    $impact = $definition['impact'] ?? null;
    $guardian = $definition['guardian'] ?? null;

    if ( ! $impact || ! $guardian ) {
        return 'No impact metadata configured';
    }

    $last_run = self::get_last_run( $slug );
    $freq = $definition['frequency'] ?? 0;
    $can_run_freq = time() - $last_run >= $freq;

    $explanation = Performance_Impact_Classifier::get_guardian_explanation( $guardian );

    if ( ! $can_run_freq ) {
        $wait = $freq - ( time() - $last_run );
        return sprintf(
            '%s. Will run in %s seconds.',
            $explanation,
            $wait
        );
    }

    $will_run = self::is_optimal_time_to_run( $guardian, $impact );

    if ( $will_run ) {
        return sprintf(
            '%s. Ready to run now (optimal timing).',
            $explanation
        );
    } else {
        $hour = intval( gmdate( 'H' ) );
        $next_window = '';

        if ( $guardian === Performance_Impact_Classifier::GUARDIAN_SCHEDULED ) {
            $next_window = sprintf( 'Next run: 2-6 AM (UTC). Current: %dh', $hour );
        } elseif ( $guardian === Performance_Impact_Classifier::GUARDIAN_BACKGROUND ) {
            $next_window = 'Next background job cycle';
        } elseif ( $guardian === Performance_Impact_Classifier::GUARDIAN_MANUAL ) {
            $next_window = 'Manual trigger only (high impact test)';
        }

        return sprintf(
            '%s. Deferred: %s',
            $explanation,
            $next_window
        );
    }
}
```

### 3. Add Impact-Aware Queue Management

```php
/**
 * Get next diagnostic to run, considering impact
 */
public static function get_next_diagnostic_to_run( bool $respect_impact = true ): ?string {
    $diagnostics = array_keys( self::$schedule_definitions );
    $now = time();

    foreach ( $diagnostics as $slug ) {
        if ( ! $respect_impact ) {
            // Original behavior - just check frequency
            if ( self::should_run( $slug, $now ) ) {
                return $slug;
            }
            continue;
        }

        // Smart behavior - also check impact
        $definition = self::$schedule_definitions[ $slug ] ?? null;
        if ( ! $definition ) {
            continue;
        }

        $last_run = self::get_last_run( $slug );
        $freq = $definition['frequency'] ?? 0;

        // Check if frequency allows it
        if ( $now - $last_run < $freq ) {
            continue;
        }

        // Check if timing is optimal
        $impact = $definition['impact'] ?? null;
        $guardian = $definition['guardian'] ?? null;

        if ( $impact && $guardian ) {
            if ( ! self::is_optimal_time_to_run( $guardian, $impact ) ) {
                continue;  // Skip to next - this one can wait
            }
        }

        // This diagnostic is ready
        return $slug;
    }

    return null;
}

/**
 * Get batch of diagnostics suitable for current context
 */
public static function get_suitable_batch_now( int $count = 5 ): array {
    $impact_data = Performance_Impact_Classifier::get_stats();
    $suitable = [];

    // Determine current context
    $hour = intval( gmdate( 'H' ) );
    $is_peak_hours = $hour >= 9 && $hour < 17;  // 9 AM - 5 PM
    $is_off_peak = $hour >= 2 && $hour < 6;      // 2-6 AM
    $is_background = defined( 'DOING_CRON' ) && DOING_CRON;

    foreach ( self::$schedule_definitions as $slug => $definition ) {
        if ( count( $suitable ) >= $count ) {
            break;
        }

        $last_run = self::get_last_run( $slug );
        $freq = $definition['frequency'] ?? 0;

        // Skip if not enough time passed
        if ( time() - $last_run < $freq ) {
            continue;
        }

        $impact = $definition['impact'] ?? null;
        $guardian = $definition['guardian'] ?? null;

        if ( ! $impact || ! $guardian ) {
            continue;  // Skip unclassified diagnostics
        }

        // Filter by current context
        if ( $is_peak_hours ) {
            // Only anytime diagnostics during peak
            if ( $guardian !== Performance_Impact_Classifier::GUARDIAN_ANYTIME ) {
                continue;
            }
        } elseif ( $is_background ) {
            // Background jobs can run medium-impact diagnostics
            if ( ! in_array(
                $guardian,
                [
                    Performance_Impact_Classifier::GUARDIAN_ANYTIME,
                    Performance_Impact_Classifier::GUARDIAN_BACKGROUND,
                ],
                true
            ) ) {
                continue;
            }
        } elseif ( $is_off_peak ) {
            // Off-peak can run anything except manual
            if ( $guardian === Performance_Impact_Classifier::GUARDIAN_MANUAL ) {
                continue;
            }
        }

        $suitable[] = $slug;
    }

    return $suitable;
}
```

### 4. Add to Dashboard to Show Users Impact

```php
/**
 * Get diagnostic details for dashboard display
 */
public static function get_diagnostic_with_impact( string $slug ): array {
    $definition = self::$schedule_definitions[ $slug ] ?? null;
    if ( ! $definition ) {
        return [];
    }

    $prediction = Performance_Impact_Classifier::predict( $slug );
    $last_run = self::get_last_run( $slug );
    $freq = $definition['frequency'] ?? 0;
    $next_run = $last_run + $freq;

    return [
        'slug'                => $slug,
        'name'                => $definition['name'] ?? ucwords( str_replace( '-', ' ', $slug ) ),
        'frequency_seconds'   => $freq,
        'last_run_timestamp'  => $last_run,
        'next_run_timestamp'  => $next_run,
        'next_run_readable'   => self::format_timestamp_relative( $next_run ),
        'impact_level'        => $prediction['impact_level'] ?? 'unknown',
        'estimated_ms'        => $prediction['estimated_ms'] ?? 0,
        'guardian_context'    => $prediction['guardian_suitable'] ?? 'unknown',
        'impact_label'        => Performance_Impact_Classifier::get_impact_label(
            $prediction['impact_level'] ?? 'medium'
        ),
        'run_reason'          => self::get_run_decision_reason( $slug ),
        'can_run_now'         => self::should_run( $slug ),
        'priority'            => $definition['priority'] ?? 'medium',
    ];
}
```

---

## Real-World Examples

### Example 1: Admin Makes Request (2 PM Peak Hours)

```
Time: 14:00 (2 PM)
Admin visits: /wp-admin/admin.php?page=wpshadow

Which diagnostics run?
✓ admin-email (MINIMAL impact, ANYTIME) → runs immediately
✓ admin-username (MINIMAL impact, ANYTIME) → runs immediately
✗ ssl (MEDIUM impact, BACKGROUND) → deferred, queues for background
✗ outdated-plugins (HIGH impact, SCHEDULED) → deferred, waits for 2-6 AM
✗ backup (EXTREME impact, MANUAL) → never auto-runs

Dashboard shows: "Latest checks from 1 hour ago. 
2 new checks just now. 2 checks pending (will run tonight at 3 AM)."
```

### Example 2: Nightly Background Job (3 AM)

```
Time: 03:00 (3 AM)
Background cron job starts

Which diagnostics run in this batch?
✓ ssl (MEDIUM impact, BACKGROUND) → safe during off-peak
✓ plugin-conflicts (MEDIUM impact, BACKGROUND) → safe during off-peak
✓ database-health (MEDIUM impact, BACKGROUND) → safe during off-peak
✓ outdated-plugins (HIGH impact, SCHEDULED) → optimal time
✓ core-homepage-load-time (HIGH impact, SCHEDULED) → optimal time
✗ backup (EXTREME impact, MANUAL) → not in automatic batch

Total estimated time: ~45 seconds
Result: Dashboard updates with fresh data at 3:05 AM

User sees at 8 AM: "All diagnostics current as of 3:05 AM (5 hours ago)"
```

### Example 3: User Manually Requests Backup

```
Time: 14:30 (2:30 PM)
User clicks: "Run Full Backup"

What happens?
1. System predicts: EXTREME impact (~120 seconds)
2. Shows confirmation: "This backup will take ~2 minutes and use high resources"
3. User confirms
4. Backup runs (if scheduling allows, or queue for later)
5. User sees progress bar

Comparison:
- If Guardian cloud: Runs immediately (unlimited resources)
- If local server: May defer to tonight 3 AM (less disruptive)
```

---

## Guardian Integration

### Guardian Cloud (Remote Execution)

Guardian Cloud has no resource constraints, so it can:
- Run ANY diagnostic anytime
- Ignore GUARDIAN_SCHEDULED context
- Ignore time-of-day considerations
- Run multiple diagnostics in parallel

```php
/**
 * Remote Guardian doesn't need timing constraints
 */
public static function should_run_on_guardian_cloud( string $slug ): bool {
    // On Guardian cloud, everything runs (if it's time)
    $definition = self::$schedule_definitions[ $slug ] ?? null;
    if ( ! $definition ) {
        return false;
    }

    $last_run = self::get_last_run( $slug );
    $freq = $definition['frequency'] ?? 0;

    // Only check frequency, not timing
    return time() - $last_run >= $freq;
}
```

### Local Server (Respects Impact)

Local servers need careful scheduling:

```php
/**
 * Local server respects impact levels strictly
 */
public static function should_run_on_local_server( string $slug ): bool {
    // Use the full impact-aware logic
    return self::should_run( $slug );
}

/**
 * Detect execution context
 */
public static function is_guardian_cloud_execution(): bool {
    // Check if running on Guardian cloud infrastructure
    // Could be based on environment variable, DNS name, etc.
    return ! empty( getenv( 'WPSHADOW_GUARDIAN_CLOUD' ) );
}
```

---

## Configuration & Customization

### How to Add Impact to New Diagnostic

```php
// In get_default_schedules():
'my-custom-diagnostic' => [
    'frequency'   => self::FREQUENCY_DAILY,
    'triggers'    => [],
    'priority'    => 'medium',
    'background'  => true,
    
    // NEW: Add these two lines
    'impact'      => Performance_Impact_Classifier::IMPACT_MEDIUM,
    'guardian'    => Performance_Impact_Classifier::GUARDIAN_BACKGROUND,
],
```

### How to Override Impact for Specific Cases

```php
/**
 * Allow customization of impact per server
 */
public static function get_impact_override( string $slug ): ?string {
    $overrides = get_option( 'wpshadow_impact_overrides', [] );
    return $overrides[ $slug ] ?? null;
}

/**
 * Admin can adjust impact classification
 */
public static function set_impact_override( string $slug, string $impact_level ): bool {
    // Validate impact level
    $valid_levels = [
        Performance_Impact_Classifier::IMPACT_MINIMAL,
        Performance_Impact_Classifier::IMPACT_LOW,
        Performance_Impact_Classifier::IMPACT_MEDIUM,
        Performance_Impact_Classifier::IMPACT_HIGH,
        Performance_Impact_Classifier::IMPACT_VERY_HIGH,
        Performance_Impact_Classifier::IMPACT_EXTREME,
    ];

    if ( ! in_array( $impact_level, $valid_levels, true ) ) {
        return false;
    }

    $overrides = get_option( 'wpshadow_impact_overrides', [] );
    $overrides[ $slug ] = $impact_level;

    return update_option( 'wpshadow_impact_overrides', $overrides );
}
```

---

## Testing the Integration

### Quick Test: Check Impact Classification

```bash
$ cd /workspaces/wpshadow
$ php tools/show-impact-reference.php
```

### Dashboard Test: Inspect Diagnostic Metadata

Add to dashboard template:

```php
<?php
$diagnostic = Diagnostic_Scheduler::get_diagnostic_with_impact( 'outdated-plugins' );
echo sprintf(
    '<div class="diagnostic-info">%s - %s (%d ms) - Next: %s</div>',
    $diagnostic['slug'],
    $diagnostic['impact_label']['label'],
    $diagnostic['estimated_ms'],
    $diagnostic['next_run_readable']
);
?>
```

Output:
```
outdated-plugins - High (742 ms) - Next: in 5 hours 23 minutes
```

---

## Performance Impact Metrics Tracked

Once integrated, the system tracks:

1. **Prediction Accuracy** - Actual vs. estimated time
2. **Server Load During Runs** - Peak CPU/memory usage
3. **User Experience Impact** - Page load time during diagnostics
4. **Scheduling Efficiency** - How many diagnostics deferred vs. executed
5. **Queue Depth** - How many pending diagnostics waiting

Display these on Guardian admin page to show value (Philosophy #9).

---

## Next Steps

1. **Integrate into Diagnostic_Scheduler** (2-3 hours)
   - Add impact/guardian fields to schedule_definitions
   - Update should_run() logic
   - Add impact-aware batch querying

2. **Build Dashboard Widgets** (4-5 hours)
   - Show next scheduled diagnostics with impact
   - Display predicted vs. actual execution time
   - Show load distribution graph

3. **Create Guardian API** (6-8 hours)
   - `/api/diagnostic/should-run/{slug}`
   - `/api/diagnostic/next-batch`
   - `/api/diagnostic/queue-for-later/{slug}`

4. **Add Off-Peak Schedule Manager** (3-4 hours)
   - Optimize 2-6 AM slots
   - Prevent server spikes
   - Distribute heavy diagnostics

5. **Monitor & Calibrate** (ongoing)
   - Track actual vs. predicted times
   - Adjust factors based on real data
   - Profile server performance

---

## Questions?

**Q: What if my server is fast and can handle high-impact diagnostics anytime?**  
A: Use `set_impact_override()` to adjust the classification, or ensure Guardian Cloud is handling the high-impact tests.

**Q: Can I run all diagnostics right now?**  
A: Yes, use `should_run( $slug, $now, $respect_impact = false )` to ignore timing constraints. For testing only.

**Q: How do I see what diagnostics are queued?**  
A: Dashboard widget shows pending diagnostics with their impact and estimated next run time.

