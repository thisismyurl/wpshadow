# WPShadow Performance Impact Quick Reference

## TL;DR

**Question:** How much will a diagnostic impact the server, and when should it run?

**Answer:** Use `Performance_Impact_Classifier` to predict impact in milliseconds and determine optimal execution time.

---

## Quick Stats

```
Total Diagnostics: 69
├─ Anytime Safe:    12 tests (~50-100ms total)
├─ Background:      15 tests (~3-5s per batch)
├─ Off-Peak Only:   38 tests (~30-60s per batch)  
└─ Manual Only:      4 tests (5-45s each)

Distribution by Impact:
├─ Negligible:   2  (0-5ms)
├─ Minimal:      3  (5-25ms)
├─ Low:          7  (25-100ms)
├─ Medium:      15  (100-500ms)
├─ High:        20  (500ms-2s)
├─ Very High:   18  (2-5s)
└─ Extreme:      4  (5s+)
```

---

## The 7 Impact Levels

| Level | Time | Context | Run When | Color |
|-------|------|---------|----------|-------|
| Negligible | 0-5ms | ✓ ANYTIME | Every request | 🟢 |
| Minimal | 5-25ms | ✓ ANYTIME | Every request | 🟢 |
| Low | 25-100ms | ✓ ANYTIME | Every request | 🟢 |
| Medium | 100-500ms | ⚡ BACKGROUND | Job queue | 🟡 |
| High | 500ms-2s | ⏰ SCHEDULED | 2-6 AM only | 🟠 |
| Very High | 2-5s | ⏰ SCHEDULED | 2-6 AM only | 🔴 |
| Extreme | 5s+ | 🔒 MANUAL | User confirms | 🔴 |

---

## API Quick Reference

### Get Impact Prediction
```php
use WPShadow\Core\Performance_Impact_Classifier;

$prediction = Performance_Impact_Classifier::predict('ssl');
// Returns:
// [
//   'impact_level' => 'medium',
//   'estimated_ms' => 300,
//   'guardian_suitable' => 'background',
//   'description' => 'Remote SSL certificate check'
// ]

echo "This test takes ~" . $prediction['estimated_ms'] . "ms";
```

### Get All Tests for Guardian Context
```php
// Tests that can run during peak hours
$anytime = Performance_Impact_Classifier::get_guardian_suitable('anytime');
// Returns: [admin-email, admin-username, https-everywhere, ...]

// Tests suitable for background queue
$background = Performance_Impact_Classifier::get_guardian_suitable('background');
// Returns: [ssl, database-health, core-backups-recent, ...]

// Tests for off-peak scheduling (2-6 AM)
$off_peak = Performance_Impact_Classifier::get_off_peak_suitable();
// Returns: [outdated-plugins, abandoned-plugins, backup, broken-links, ...]
```

### Filter by Impact Level
```php
$high_impact = Performance_Impact_Classifier::get_by_impact('high');
// Returns all diagnostics that take 500ms-2s

$extreme = Performance_Impact_Classifier::get_by_impact('extreme');
// Returns: [backup, broken-links, deep-malware-scan, ...]
```

### Get Statistics
```php
$stats = Performance_Impact_Classifier::get_stats();
// Returns:
// [
//   'total' => 69,
//   'by_impact' => [
//       'minimal' => 3,
//       'low' => 7,
//       'medium' => 15,
//       ...
//   ],
//   'by_guardian' => [
//       'anytime' => 12,
//       'background' => 15,
//       'scheduled' => 38,
//       'manual' => 4,
//   ],
//   'avg_ms' => 312.5,
//   'total_ms' => 21581.3,
// ]
```

### Get Display Label
```php
$label = Performance_Impact_Classifier::get_impact_label('high');
// Returns:
// [
//   'label' => 'High',
//   'color' => 'orange',
//   'emoji' => '⚠⚠',
//   'ms_max' => 2000,
// ]

echo $label['emoji'] . ' ' . $label['label'];  // ⚠⚠ High
```

---

## Scheduling Strategy

### Peak Hours (9 AM - 5 PM)
```
Run ONLY: Anytime tests (12 tests, ~75ms total)
Result: User sees latest data with no slowdown
```

### Evening (6 PM - 12 AM)
```
Run: Background queue (15 tests, ~3-5s per batch)
Timing: Stagger every few hours
Result: Moderate work during moderate traffic
```

### Off-Peak (2-6 AM)
```
Run: All scheduled tests (38 tests, ~30-60s per batch)
Timing: Daily runs, weekly full scans
Result: Complete diagnostics, no user impact
```

### Manual Only
```
Run: Extreme tests (4 tests, 5-45s each)
When: User clicks button + confirms dialog
Example: Full backup creation
```

---

## Real Diagnostic Examples

### Quick & Safe (Run Anytime)
- `admin-email` (MINIMAL, 5ms)
- `admin-username` (MINIMAL, 5ms)
- `https-everywhere` (LOW, 10ms)
- `head-cleanup` (LOW, 25ms)
- `database-revisions` (LOW, 10ms)
- `autoloaded-options` (LOW, 50ms)

### Moderate (Background Queue)
- `ssl` (MEDIUM, 300ms)
- `plugin-conflicts` (MEDIUM, 250ms)
- `database-health` (MEDIUM, 150ms)
- `database-post-revisions` (MEDIUM, 100ms)
- `seo-missing-meta` (MEDIUM, 350ms)

### Heavy (Off-Peak Only)
- `outdated-plugins` (HIGH, 800ms)
- `core-response-time` (HIGH, 1000ms)
- `seo-missing-h1` (HIGH, 1200ms)
- `abandoned-plugins` (VERY HIGH, 2500ms)
- `alt-text-coverage` (VERY HIGH, 3000ms)
- `malware-scan` (VERY HIGH, 3000ms)

### Extreme (Manual Only)
- `backup` (EXTREME, 30-120s)
- `broken-links` (EXTREME, 45000ms+)

---

## Common Scenarios

### Scenario 1: User visits admin at 2 PM
```
Guardian checks: Should I run outdated-plugins?
Step 1: Is it time? → Yes (daily frequency satisfied)
Step 2: What's the impact? → HIGH (800ms)
Step 3: Is now optimal? → No (2 PM is peak hours)
Decision: DEFER until 3 AM
Display: "Last checked 6 hours ago. Next check scheduled for tonight at 3 AM"
```

### Scenario 2: Nightly 3 AM cron runs
```
Guardian checks: What can I run safely now?
Current time: 3:00 AM (low traffic)
Impact allowed: HIGH, VERY HIGH, EXTREME (not MANUAL)

Returns:
✓ outdated-plugins (HIGH)
✓ abandoned-plugins (VERY HIGH)
✓ alt-text-coverage (VERY HIGH)
✓ malware-scan (VERY HIGH)
✗ backup (EXTREME - manual only)

Total time: ~45 seconds
Result: Dashboard fully updated by 3:01 AM
```

### Scenario 3: User clicks "Run Full Backup"
```
Guardian checks: Should I run backup?
Impact: EXTREME (120+ seconds)
Guardian: MANUAL (user confirmation required)

Shows dialog:
"This backup will take 2-3 minutes and use high server resources.
Ready to proceed? [Cancel] [Backup Now]"

User clicks: [Backup Now]
Result: Full backup created while user watches progress
```

---

## Guardian Cloud vs. Local Server

### On Guardian Cloud (Remote)
- ✓ Can run ANY diagnostic anytime
- ✓ No peak/off-peak constraints
- ✓ Multiple diagnostics in parallel
- ✓ Best for extreme-impact tests

Example: Backup creation runs immediately, never deferred

### On Local Server (WordPress host)
- ⚠ Must respect peak/off-peak
- ⚠ Batch high-impact tests
- ⚠ Serialize execution
- ⚠ Reserved 2-6 AM for heavy work

Example: Backup deferred to 3 AM to avoid user slowdown

---

## Integration Checklist

- [ ] Add impact/guardian fields to Diagnostic_Scheduler
- [ ] Update should_run() to check impact + time
- [ ] Add is_optimal_time_to_run() method
- [ ] Create get_suitable_batch_now() method
- [ ] Add dashboard widget showing pending tests
- [ ] Update Guardian API to use classifier
- [ ] Test peak vs. off-peak scheduling
- [ ] Monitor actual vs. predicted times
- [ ] Tune impact factors based on real data
- [ ] Document for end users

---

## Performance Factors Used

The classifier estimates impact using 20+ granular factors:

| Factor | Estimate |
|--------|----------|
| Simple DB query | 5ms |
| Complex DB query | 50ms |
| Full table scan | 200ms |
| HTTP GET request | 500ms |
| HTTP POST request | 1000ms |
| SSL cert check | 300ms |
| DNS lookup | 100ms |
| File hash | 20ms |
| Plugin scan | 100ms |
| Image metadata | 100ms |
| Regex compile | 5-50ms |
| XML parsing | 50ms |
| Serialize array | 25ms |

Multiple factors combine:
```
Outdated Plugins = 
  HTTP GET (1×) + 
  DB query (2×) + 
  Array operations (1000 plugins) +
  ≈ 800ms
```

---

## Dashboard Display Examples

### Impact Badge
```
MINIMAL ✓  (0-5ms)   green
MINIMAL ✓✓ (5-25ms)  green
LOW ✓✓✓    (25-100ms) green
MEDIUM ⚠   (100-500ms) yellow
HIGH ⚠⚠    (500ms-2s)  orange
VERY HIGH ⚠⚠⚠ (2-5s)   red
EXTREME 🔴  (5s+)      red
```

### Next Run Info
```
📅 admin-email
   Last: 2 hours ago
   Next: in 5 days (weekly)
   Impact: MINIMAL (5ms) - Can run anytime
   Status: ✅ Ready to run

⚡ outdated-plugins
   Last: 1 day ago
   Next: Tomorrow at 3:00 AM
   Impact: HIGH (800ms) - Off-peak only
   Status: ⏱️ Deferred (Peak hours, will run tonight)
```

---

## Debugging

### Check if diagnostic has impact metadata
```bash
$ cd /workspaces/wpshadow
$ php -c tools/show-impact-reference.php | grep -i "outdated-plugins"
```

### Verify classifier is loaded
```php
$test = Performance_Impact_Classifier::predict( 'ssl' );
if ( $test['impact_level'] ) {
    echo "Classifier working: SSL = " . $test['impact_level'];
}
```

### Show all anytime-safe tests
```php
$safe = Performance_Impact_Classifier::get_guardian_suitable( 'anytime' );
echo count( $safe ) . " tests safe for peak hours";
```

---

## Common Questions

**Q: Why is outdated-plugins HIGH impact but ssl only MEDIUM?**  
A: outdated-plugins makes multiple WordPress.org API calls and analyzes 1000+ plugins. SSL just checks certificate validity. Both are external calls but outdated-plugins has more overhead.

**Q: Can I run backup during peak hours?**  
A: No. Backup is EXTREME impact (120s+). Even on Guardian Cloud, it's deferred to allow other operations to complete. MANUAL only ensures user confirmation.

**Q: What if my server is very fast?**  
A: Use `Performance_Impact_Classifier::set_impact_override()` to customize classifications. Or ensure Guardian Cloud handles the heavy tests.

**Q: How do I know if predictions are accurate?**  
A: Dashboard tracks actual vs. predicted time. After 100 runs, adjust factors based on real data for your server hardware.

**Q: Can I force a test to run now, ignoring impact?**  
A: Yes, for testing: `Diagnostic_Scheduler::should_run( $slug, time(), false )`. Never use in production (users will notice slowdown).

---

## Files

- **[class-performance-impact-classifier.php](../includes/core/class-performance-impact-classifier.php)** - Main classifier (475 lines)
- **[SCHEDULER_PERFORMANCE_INTEGRATION.md](./SCHEDULER_PERFORMANCE_INTEGRATION.md)** - Integration guide (600+ lines)
- **[SCHEDULER_INTEGRATION_CODE_EXAMPLES.php](./SCHEDULER_INTEGRATION_CODE_EXAMPLES.php)** - Copy-paste ready code
- **[PERFORMANCE_IMPACT_PREDICTION_GUIDE.md](./PERFORMANCE_IMPACT_PREDICTION_GUIDE.md)** - Comprehensive reference
- **[tools/show-impact-reference.php](../tools/show-impact-reference.php)** - Visual reference tool

---

## Next Steps

1. **Integrate into Scheduler** - Add impact fields to schedule_definitions
2. **Update should_run()** - Check impact + time before execution
3. **Build Dashboard** - Show pending diagnostics with impact badges
4. **Test Timing** - Verify peak/off-peak scheduling works
5. **Monitor Real Data** - Track actual vs. predicted times
6. **Tune Factors** - Adjust millisecond estimates based on your server

---

**Philosophy:** Shows value (#9) by proving impact and making intelligent scheduling decisions that balance thoroughness with user experience.

