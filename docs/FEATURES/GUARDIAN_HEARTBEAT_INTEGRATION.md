# Guardian Heartbeat Integration Plan

## Overview

Wire the WPShadow Guardian system to run diagnostics automatically via WordPress Heartbeat API, with intelligent classification of diagnostics into Quick Scan (background) and Deep Scan (off-hours) suites.

## Architecture

### Current State
- ✅ `Diagnostic_Scheduler` exists with heartbeat hooks
- ✅ `Performance_Impact_Classifier` exists with impact predictions
- ✅ Diagnostic base class with execute() method
- ✅ Diagnostic Registry with auto-discovery
- ⏳ Heartbeat integration exists but not fully wired to Guardian execution
- ⏳ No automatic classification of quick vs deep scan diagnostics

### Target State
- Guardian runs automatically via WordPress Heartbeat
- Quick Scan diagnostics run in background without user notice
- Deep Scan diagnostics scheduled for off-hours only
- Low-traffic sites fall back to WP-Cron with option for remote triggers
- All diagnostic execution respects performance impact classification

## Diagnostic Classification

### Quick Scan Suite (Background Safe)
**Criteria:**
- `guardian` classification: `GUARDIAN_ANYTIME` or `GUARDIAN_BACKGROUND`
- Impact level: `IMPACT_NONE`, `IMPACT_MINIMAL`, `IMPACT_LOW`, or `IMPACT_MEDIUM`
- Execution time: < 500ms
- No external HTTP calls (or very few, cached)
- Can run during user sessions without performance impact

**Examples:**
- admin-email (5ms)
- admin-username (5ms)
- https-everywhere (25ms)
- database-health (100ms)
- backup check (50ms)

**Run frequency:** Every heartbeat cycle when due (typically every 15-60 seconds when user active)

### Deep Scan Suite (Off-Hours Only)
**Criteria:**
- `guardian` classification: `GUARDIAN_SCHEDULED` or `GUARDIAN_MANUAL`
- Impact level: `IMPACT_HIGH`, `IMPACT_VERY_HIGH`, or `IMPACT_EXTREME`
- Execution time: > 500ms
- May include external HTTP calls, file system scans, content scans
- Could impact server performance if run during peak hours

**Examples:**
- outdated-plugins (2s+, multiple API calls)
- abandoned-plugins (5s+, complex analysis)
- broken-links (extreme, HTTP requests per link)
- pub-alt-text-coverage (5s+, loops all posts)
- database-malware-scanning (varies, file system + DB)

**Run frequency:** Scheduled via WP-Cron for off-peak hours (default: 2-4 AM local time)

## Implementation Plan

### Phase 1: Wire Heartbeat to Guardian Executor (This Session)

#### Step 1.1: Create Guardian Executor Class
**File:** `includes/core/class-guardian-executor.php`

**Purpose:** Central execution engine that respects performance impact and scheduling

**Responsibilities:**
- Execute diagnostics based on impact classification
- Enforce timing rules (off-hours for deep scans)
- Batch execution to avoid overwhelming heartbeat
- Track execution state across heartbeat cycles
- Report progress to dashboard

**Key Methods:**
```php
public static function execute_background_diagnostics(): array
public static function execute_scheduled_diagnostics(): array
public static function should_run_diagnostic( string $slug ): bool
public static function get_off_peak_hours(): array
public static function is_off_peak_time(): bool
public static function batch_execute( array $diagnostic_slugs, int $max_time_ms ): array
```

#### Step 1.2: Enhance Diagnostic_Scheduler
**File:** `includes/utils/class-diagnostic-scheduler.php`

**Changes:**
- Integrate with `Performance_Impact_Classifier`
- Add methods to get background-safe vs scheduled diagnostics
- Enhance `process_heartbeat()` to call Guardian_Executor
- Add off-hours detection

#### Step 1.3: Update Diagnostic_Base
**File:** `includes/core/class-diagnostic-base.php`

**Changes:**
- Add methods to expose guardian classification
- Add execution time tracking hooks
- Integrate with Activity_Logger for execution tracking

#### Step 1.4: Create Guardian Dashboard Widget
**File:** `includes/admin/widgets/class-guardian-status-widget.php`

**Purpose:** Show Guardian status and pending diagnostics in dashboard

**Shows:**
- Last background execution time
- Next scheduled deep scan time
- Pending diagnostics count
- Recent findings from auto-scans
- Manual trigger buttons

### Phase 2: WP-Cron Integration (Future Session)

For low-traffic sites where heartbeat doesn't fire frequently:

#### Step 2.1: Register WP-Cron Events
```php
wp_schedule_event( strtotime('today 2:00 AM'), 'daily', 'wpshadow_guardian_deep_scan' );
wp_schedule_event( time(), 'hourly', 'wpshadow_guardian_quick_scan_fallback' );
```

#### Step 2.2: Detect Low-Traffic Sites
- Track heartbeat fire frequency
- If < 10 heartbeats/hour, enable cron fallback
- Notify user of cron-based execution

### Phase 3: Remote Trigger Support (Future Session)

For sites with unreliable cron or extremely low traffic:

#### Step 3.1: API Endpoint for Remote Triggers
**File:** `includes/api/class-guardian-trigger-endpoint.php`

**Features:**
- REST API endpoint: `/wp-json/wpshadow/v1/guardian/trigger`
- Authentication via API key (generated per-site)
- Rate limiting (max 1 trigger per 5 minutes)
- Execute diagnostics and return results

#### Step 3.2: Cloud Service Integration
- WPShadow cloud service pings registered sites
- Triggers Guardian execution on schedule
- Receives diagnostic results for cloud analysis
- Free tier: 100 triggers/month
- Pro tier: Unlimited triggers

## Execution Flow

### Background Execution (Heartbeat)

```
User Active in Admin
    ↓
WordPress Heartbeat Fires
    ↓
Diagnostic_Scheduler::process_heartbeat()
    ↓
Get diagnostics due for execution (via should_run())
    ↓
Filter to GUARDIAN_ANYTIME/GUARDIAN_BACKGROUND only
    ↓
Guardian_Executor::execute_background_diagnostics()
    ↓
Batch execute (max 100ms total per heartbeat)
    ↓
Record results → Activity_Logger
    ↓
Update dashboard widget data
    ↓
Return updated stats in heartbeat response
```

### Scheduled Execution (Deep Scan)

```
WP-Cron Triggers (2 AM daily)
    ↓
wpshadow_guardian_deep_scan hook fires
    ↓
Guardian_Executor::execute_scheduled_diagnostics()
    ↓
Check is_off_peak_time() → TRUE
    ↓
Get all GUARDIAN_SCHEDULED diagnostics
    ↓
Execute with generous time limits (300s PHP timeout)
    ↓
Record results → Activity_Logger
    ↓
Store findings → wpshadow_site_findings option
    ↓
Email report (if enabled)
```

## Configuration Options

### User-Configurable Settings
**Location:** WPShadow → Settings → Guardian

```php
'guardian_enabled'              => true/false,  // Master switch
'guardian_heartbeat_enabled'    => true/false,  // Background execution
'guardian_deep_scan_enabled'    => true/false,  // Scheduled deep scans
'guardian_deep_scan_time'       => '02:00',     // Off-peak time
'guardian_max_heartbeat_ms'     => 100,         // Max time per heartbeat
'guardian_email_reports'        => true/false,  // Email findings
'guardian_email_address'        => '',          // Report recipient
'guardian_low_traffic_fallback' => 'auto',      // auto/cron/remote
```

### Off-Peak Hours
Default: 2 AM - 6 AM local server time
Configurable in settings

## Performance Safeguards

### Heartbeat Execution Limits
- Maximum 100ms execution time per heartbeat cycle
- Maximum 3 diagnostics per heartbeat cycle
- Skip if server load > 70%
- Skip if WordPress is_admin_ajax() request active

### Deep Scan Limits
- Only during off-peak hours
- Maximum 300s PHP execution time
- Skip if backup is running
- Skip if WordPress core/plugin update in progress

### Throttling
- If diagnostic fails 3 times consecutively, disable until manual trigger
- If total execution time exceeds limits, pause Guardian for 1 hour
- Exponential backoff for failing diagnostics

## Monitoring & Logging

### Activity Logging
All Guardian executions logged via Activity_Logger:

```php
Activity_Logger::log(
    'guardian_execution',
    'Background diagnostics executed via heartbeat',
    'guardian',
    [
        'diagnostics_run' => ['admin-email', 'ssl', 'database-health'],
        'execution_time_ms' => 87,
        'findings_count' => 2,
        'trigger' => 'heartbeat'
    ]
);
```

### KPI Tracking
```php
KPI_Tracker::log_guardian_execution( $diagnostic_count, $execution_time_ms );
KPI_Tracker::log_finding_detected( $finding_id, $severity, 'guardian_auto' );
```

## Testing Strategy

### Unit Tests
- Guardian_Executor::should_run_diagnostic()
- Guardian_Executor::is_off_peak_time()
- Guardian_Executor::batch_execute()
- Diagnostic classification helpers

### Integration Tests
- Full heartbeat cycle with mock diagnostics
- Scheduled execution via WP-Cron
- Performance impact validation
- Dashboard widget updates

### Manual Testing
- Enable Guardian, observe background execution in Activity Log
- Wait for off-peak time, verify deep scan runs
- Disable heartbeat, verify cron fallback
- Low-traffic simulation

## Rollout Plan

### Stage 1: Development (This Session)
- Create Guardian_Executor class
- Wire heartbeat integration
- Add dashboard widget
- Test with 5-10 diagnostics

### Stage 2: Internal Testing (1-2 days)
- Deploy to staging sites
- Monitor performance impact
- Validate scheduling logic
- Fix bugs

### Stage 3: Beta Release (1 week)
- Enable for opted-in users
- Collect feedback
- Monitor server impact
- Iterate on thresholds

### Stage 4: General Availability (2 weeks)
- Enable by default for new installs
- Opt-in for existing installs
- Announce feature
- Documentation and training

## Edge Cases & Considerations

### Multisite
- Network-level Guardian settings
- Per-site Guardian state tracking
- Avoid overlapping execution across sites
- Network admin dashboard widget

### High-Traffic Sites
- Heartbeat fires constantly
- Throttle to max 1 Guardian execution per minute
- Batch more aggressively (5-10 diagnostics per cycle)

### Low-Traffic Sites
- Heartbeat may never fire
- WP-Cron fallback essential
- Consider remote trigger option

### Resource-Constrained Hosting
- Shared hosting with strict limits
- Reduce max execution time to 50ms
- Increase diagnostic frequency intervals
- Prioritize critical diagnostics only

### Plugin Conflicts
- Heartbeat disabled by caching plugins
- Cron disabled by some hosts
- Graceful degradation to manual scans only
- Clear user notification

## Success Metrics

### Performance
- Background execution < 100ms per cycle
- Zero user-reported slowdowns
- Server load < 5% increase
- Memory usage < 10MB increase per cycle

### Effectiveness
- 90% of diagnostics run automatically within scheduled interval
- Findings detected within 24 hours of issue appearing
- 50% reduction in manual scan triggers

### User Experience
- Guardian "just works" without user intervention
- Dashboard shows current status at a glance
- Email reports are timely and actionable
- Settings are clear and well-documented

## Documentation Required

### User Documentation
- **Guardian Guide:** Explain what Guardian does, how it works
- **Configuration Guide:** All settings with examples
- **Troubleshooting Guide:** Common issues and solutions
- **FAQ:** "Is Guardian running?", "Why no findings?", etc.

### Developer Documentation
- **Architecture Diagram:** Guardian components and flow
- **API Reference:** Guardian_Executor methods
- **Extension Guide:** How pro modules add diagnostics
- **Performance Guide:** Impact classification and optimization

## Next Steps

1. ✅ Read this plan
2. Create `Guardian_Executor` class skeleton
3. Wire heartbeat to execute background diagnostics
4. Add dashboard widget showing Guardian status
5. Test with 5 existing diagnostics
6. Deploy and monitor
7. Iterate based on real-world data

---

**Philosophy Alignment:**
- **Helpful Neighbor (#1):** Guardian works quietly in background, alerts when needed
- **Free as Possible (#2):** All Guardian functionality free, no artificial limits
- **Inspire Confidence (#8):** Users see Guardian is watching, feel protected
- **Show Value (#9):** KPI tracking shows time saved, issues prevented

**CANON Alignment:**
- **Accessibility:** Dashboard widget keyboard navigable, screen reader friendly
- **Performance:** Execution limits ensure no user-facing impact
- **Privacy:** All processing local, no external calls without consent
