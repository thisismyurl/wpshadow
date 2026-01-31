# WPShadow Efficiency Audit - Code Locations Reference

**Purpose:** Detailed file locations and specific code patterns identified during efficiency audit  
**Updated:** January 31, 2026  
**Use:** Quick reference for implementing Phase 1 and Phase 2 optimizations

---

## 🎯 Quick Reference Index

- [Asset Loading Issues](#asset-loading-issues)
- [N+1 Query Patterns](#n1-query-patterns)
- [Caching Opportunities](#caching-opportunities)
- [Database Index Targets](#database-index-targets)
- [API Call Consolidation](#api-call-consolidation)
- [System Initialization](#system-initialization)
- [Memory Optimization](#memory-optimization)

---

## Asset Loading Issues

### Primary Problem File
**File:** `/includes/core/class-hooks-initializer.php`

**Method:** `on_wp_enqueue_scripts()`  
**Lines:** 295-360

**Current Behavior:**
```php
// ALL of these enqueued on EVERY page
wp_enqueue_style( 'wpshadow-design-system' );      // 45KB
wp_enqueue_style( 'wpshadow-form-controls' );      // 28KB
wp_enqueue_style( 'wpshadow-gauges' );             // 32KB
wp_enqueue_style( 'wpshadow-utilities-consolidated' ); // 52KB
wp_enqueue_style( 'wpshadow-kanban-board-consolidated' ); // 52KB
wp_enqueue_style( 'wpshadow-dashboard-fullscreen' ); // 35KB
wp_enqueue_style( 'wpshadow-modal' );              // 18KB
wp_enqueue_style( 'wpshadow-guardian-dashboard-modern' ); // 42KB
wp_enqueue_style( 'wpshadow-gamification' );       // 35KB
wp_enqueue_style( 'wpshadow-vault-dashboard' );    // 28KB
wp_enqueue_style( 'wpshadow-phase4' );             // 38KB
wp_enqueue_script( 'wpshadow-admin' );             // 180KB
wp_enqueue_script( 'wpshadow-dashboard-realtime' ); // 65KB
```

**Total Damage:** 460KB+ per page load

### Related Files Enqueuing Assets

1. **File:** `/includes/admin/class-privacy-dashboard-page.php:74-75`
   - Enqueues: wpshadow-admin style and script
   - Should be conditional on page=wpshadow-privacy

2. **File:** `/includes/academy/class-academy-ui.php:625-632`
   - Enqueues: Academy-specific styles and scripts
   - Should only load on wpshadow-academy page

3. **File:** `/includes/onboarding/class-feature-tour.php:59-66`
   - Enqueues: Feature tour styles and scripts
   - Should only load when feature tour active

4. **File:** `/includes/core/class-hooks-initializer.php:298-360`
   - Multiple conditional enqueues based on page
   - Should ALL be behind hook check: `if ( false === strpos( $hook_suffix, 'wpshadow' ) ) return;`

### Solution Checklist

- [ ] Add hook check at start of `on_wp_enqueue_scripts()`
- [ ] Wrap each enqueue in appropriate condition
- [ ] Test all pages load CSS/JS correctly
- [ ] Monitor browser console for 404 errors
- [ ] Verify responsive design still works

---

## N+1 Query Patterns

### 🔴 Critical N+1 Issues

#### Issue 1: Exit Followup Handler
**File:** `/includes/admin/ajax/exit-followup-handlers.php`  
**Lines:** 80-115  
**Severity:** HIGH

```php
// PROBLEM: Fetches followups, then loops for individual queries
$followups = $wpdb->get_results(
    "SELECT * FROM {$wpdb->prefix}wpshadow_followups"
);

foreach ( $followups as $followup ) {
    // THIS CREATES A QUERY FOR EACH FOLLOWUP
    $data = $wpdb->get_row(
        "SELECT * FROM {$wpdb->prefix}wpshadow_followup_data WHERE followup_id = {$followup->id}"
    );
    // Process each followup individually
}
```

**Fix:** Use JOIN
```php
// SOLUTION: Single query with JOIN
$results = $wpdb->get_results(
    "SELECT f.*, d.* 
     FROM {$wpdb->prefix}wpshadow_followups f
     LEFT JOIN {$wpdb->prefix}wpshadow_followup_data d 
       ON f.id = d.followup_id"
);
```

---

#### Issue 2: Bulk Find-Replace Handler
**File:** `/includes/admin/ajax/bulk-find-replace-handler.php`  
**Lines:** 180-335  
**Severity:** MEDIUM

**Problem:** Multiple separate COUNT queries before execution

```php
// Query 1: Count matches
$matches = (int) $wpdb->get_var( $count_query );

// Query 2: Count again? (sometimes)
$matches = (int) $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE ..."
);

// Query 3: Execute replacement
$replaced = $wpdb->query( $replace_query );
```

**Fix:** Use SQL_CALC_FOUND_ROWS()
```php
// SOLUTION: Single count included with result
$wpdb->query(
    "SELECT SQL_CALC_FOUND_ROWS() ... WHERE condition LIMIT 1000"
);
$affected_rows = $wpdb->get_var( 'SELECT FOUND_ROWS()' );
```

---

#### Issue 3: Clone/Sync Operations
**Files:** 
- `/includes/admin/ajax/sync-clone-handler.php:186-199`
- `/includes/admin/ajax/create-clone-handler.php:258-283`

**Severity:** MEDIUM

**Problem:** Cloning large tables without optimization
```php
// Query 1
$wpdb->query( "DROP TABLE IF EXISTS `{$new_table}`" );

// Query 2
$wpdb->query( "CREATE TABLE `{$new_table}` LIKE `{$table}`" );

// Query 3 (runs without key optimization)
$wpdb->query( "INSERT INTO `{$new_table}` SELECT * FROM `{$table}`" );
```

**Fix:** Disable keys during bulk insert
```php
$wpdb->query( "ALTER TABLE {$new_table} DISABLE KEYS" );
$wpdb->query( "INSERT INTO {$new_table} SELECT * FROM {$table}" );
$wpdb->query( "ALTER TABLE {$new_table} ENABLE KEYS" );
```

---

### 🟡 Potential N+1 Patterns to Audit

Search for these patterns project-wide:

```bash
# Find loops with get_row/get_results inside
grep -rn "foreach" includes/ | grep -A 5 "get_row\|get_results"

# Find duplicate queries in same function
grep -n "wpdb->get_\|wpdb->query" includes/ | head -20
```

**Suspect Files:**
- `/includes/guardian/class-anomaly-detector.php`
- `/includes/recommendations/class-recommendation-engine.php`
- `/includes/dashboard/class-dashboard-page.php`
- `/includes/reporting/class-report-generator.php`

---

## Caching Opportunities

### Issue 1: Diagnostic Result Caching
**Files to Create:** `/includes/core/class-diagnostic-cache.php`

**Opportunity:** Cache diagnostic results for 1 hour

```php
// BEFORE: Re-runs diagnostic every time
$result = Diagnostic_Memory_Limit::execute();

// AFTER: Cache for 1 hour
$cache_key = 'wpshadow_diagnostic_' . $diagnostic_slug;
$result = Cache_Manager::get( $cache_key );

if ( false === $result ) {
    $result = Diagnostic_Memory_Limit::execute();
    Cache_Manager::set( $cache_key, $result, HOUR_IN_SECONDS );
}
```

**Impact:** 60-70% faster repeat scans

---

### Issue 2: Inconsistent Cache TTLs
**Main File:** `/includes/guardian/class-anomaly-detector.php`

**Line 135:** Cache set to 5 minutes (too short)
```php
set_transient( 'wpshadow_anomaly_baseline', $data, 300 );
```

**Line 200-209:** Cache set to 5 minutes
```php
set_transient( 'wpshadow_debug_log_size', $current_size, 300 );
```

**Recommendation:**
```php
// Real-time: 5 minutes (anomaly detection baseline)
set_transient( 'wpshadow_anomaly_baseline', $data, 5 * MINUTE_IN_SECONDS );

// Hourly: 1 hour (log size tracking)
set_transient( 'wpshadow_debug_log_size', $current_size, HOUR_IN_SECONDS );
```

---

### Issue 3: Missing Query Result Caching

**Files to Cache:**
1. Diagnostic catalog (`Diagnostic_Registry::get_all()`)
2. Treatment catalog (`Treatment_Registry::get_all()`)
3. Utilities catalog (`wpshadow_get_utilities_catalog()`)
4. Module registry lookups

**Example Location:** `/includes/admin/class-menu-manager.php`

```php
// Currently runs every page load
$menu_items = Menu_Manager::get_menus();

// Should be cached
$menu_items = Cache_Manager::get( 'wpshadow_menu_items' );
if ( false === $menu_items ) {
    $menu_items = Menu_Manager::get_menus();
    Cache_Manager::set( 'wpshadow_menu_items', $menu_items, 12 * HOUR_IN_SECONDS );
}
```

---

## Database Index Targets

### Tables Needing Indexes

**Table:** `{prefix}wpshadow_activities`

Current indexes: Likely none  
Recommended indexes:
```sql
ALTER TABLE {prefix}wpshadow_activities ADD INDEX idx_user_id (user_id);
ALTER TABLE {prefix}wpshadow_activities ADD INDEX idx_timestamp (timestamp);
ALTER TABLE {prefix}wpshadow_activities ADD INDEX idx_activity_type (activity_type);
ALTER TABLE {prefix}wpshadow_activities ADD INDEX idx_user_timestamp (user_id, timestamp);
```

**Table:** `{prefix}wpshadow_findings`

Recommended indexes:
```sql
ALTER TABLE {prefix}wpshadow_findings ADD INDEX idx_status (status);
ALTER TABLE {prefix}wpshadow_findings ADD INDEX idx_severity (severity);
ALTER TABLE {prefix}wpshadow_findings ADD INDEX idx_created_at (created_at);
ALTER TABLE {prefix}wpshadow_findings ADD INDEX idx_status_severity (status, severity);
```

**Table:** `{prefix}wpshadow_followups`

Recommended indexes:
```sql
ALTER TABLE {prefix}wpshadow_followups ADD INDEX idx_id (id);
ALTER TABLE {prefix}wpshadow_followups ADD INDEX idx_status (status);
```

**Table:** `{prefix}wpshadow_followup_data`

Recommended indexes:
```sql
ALTER TABLE {prefix}wpshadow_followup_data ADD INDEX idx_followup_id (followup_id);
```

**Implementation File Location:** `/includes/core/class-database-migrator.php`

Add index creation to activation/migration routine

---

## API Call Consolidation

### Outbound Request Files

1. **File:** `/includes/guardian/class-guardian-executor.php`
   - May make Guardian API calls
   - Should be batched

2. **File:** `/includes/core/class-phone-home.php`
   - Phone-home telemetry
   - Should batch with other calls

3. **File:** `/includes/integration/class-cloud-services.php`
   - Cloud connectivity checks
   - Should batch with other calls

4. **File:** `/includes/admin/class-license-manager.php`
   - License validation
   - Should batch with Guardian/Cloud calls

### Batching Strategy

**Create:** `/includes/core/class-api-batch-manager.php`

```php
class API_Batch_Manager {
    private static $batch_queue = array();
    
    public static function queue( $action, $url, $args = array() ) {
        self::$batch_queue[] = array(
            'action' => $action,
            'url' => $url,
            'args' => $args,
        );
    }
    
    public static function flush() {
        // Execute all queued requests together
        // Use curl_multi for parallel execution
    }
}
```

---

## System Initialization

### Over-Eager Init Pattern

**File:** `/includes/core/class-plugin-bootstrap.php`  
**Lines:** 30-80

**Current:** All 21 systems initialized immediately

```php
public static function init() {
    self::load_core_classes();        // Always
    Hooks_Initializer::init();        // Always
    Menu_Manager::init();             // Always
    self::load_dashboard_page();      // Always (admin-only?)
    self::load_workflow_module();     // Always (admin-only?)
    self::load_engage_system();       // Always (admin-only?)
    // ... 15 more systems ...
}
```

### Optimization Strategy

**Add conditional loading:**

```php
public static function init() {
    // Always load
    self::load_core_classes();
    Hooks_Initializer::init();
    
    // Only in admin
    if ( is_admin() ) {
        Menu_Manager::init();
        self::load_dashboard_page();
        self::load_workflow_module();
        // ... other admin systems ...
    }
    
    // Only if enabled
    if ( get_option( 'wpshadow_guardian_active' ) ) {
        self::load_guardian_system();
    }
    
    // Only on-demand
    if ( wp_doing_ajax() && 'wpshadow_action' === $_POST['action'] ) {
        self::load_ajax_handlers();
    }
}
```

---

## Memory Optimization

### Large Object Creation

**Files Creating Large Objects:**

1. **File:** `/includes/core/class-kpi-tracker.php`
   - Creates dashboard widget instances
   - Solution: Lazy-load on demand

2. **File:** `/includes/guardian/class-anomaly-detector.php`
   - Creates large analysis arrays
   - Solution: Process in chunks

3. **File:** `/includes/recommendations/class-recommendation-engine.php`
   - Loads all recommendations at once
   - Solution: Paginate/load on-demand

### Memory Check Pattern

**Add Before Large Operations:**

```php
// Check memory before bulk operation
$current = memory_get_usage( true );
$limit = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
$safety_margin = 5 * MB_IN_BYTES;

if ( $current + (50 * MB_IN_BYTES) > ($limit - $safety_margin) ) {
    return array(
        'error' => 'Insufficient memory for operation',
        'current' => size_format( $current ),
        'available' => size_format( $limit - $current ),
    );
}
```

**Files to Add Checks:**
- `/includes/admin/ajax/bulk-find-replace-handler.php`
- `/includes/admin/ajax/sync-clone-handler.php`
- `/includes/admin/ajax/create-clone-handler.php`

---

## Quick Implementation Commands

### Find All Transient Usage
```bash
grep -rn "get_transient\|set_transient\|delete_transient" includes/ | wc -l
```

### Find All Query Usage
```bash
grep -rn "wpdb->get_\|wpdb->query" includes/ | head -50
```

### Find All Enqueue Calls
```bash
grep -rn "wp_enqueue" includes/ | grep -v "add_action\|do_action"
```

### Find All Loops with Queries
```bash
grep -rn "foreach" includes/ -A 5 | grep "wpdb" | head -20
```

---

## Implementation Tracking

### Phase 1: Quick Wins

| Item | File(s) | Effort | Status |
|------|---------|--------|--------|
| Asset loading | class-hooks-initializer.php | 1-1.5h | ⏳ TODO |
| N+1 queries | exit-followup-handlers.php | 30-45m | ⏳ TODO |
| Database indexes | class-database-migrator.php | 30m | ⏳ TODO |
| Object cache | Cache_Manager (new) | 45-60m | ⏳ TODO |

### Phase 2: Medium Effort

| Item | File(s) | Effort | Status |
|------|---------|--------|--------|
| Diagnostic caching | class-diagnostic-cache.php (new) | 3-4h | ⏳ TODO |
| Cache tiers | Multiple | 2-3h | ⏳ TODO |
| API batching | class-api-batch-manager.php (new) | 2-3h | ⏳ TODO |

### Phase 3: Large Refactoring

| Item | File(s) | Effort | Status |
|------|---------|--------|--------|
| Lazy init | class-plugin-bootstrap.php | 8-12h | ⏳ TODO |
| Code splitting | assets/js/*.js | 6-8h | ⏳ TODO |
| Memory optimization | Multiple | 4-6h | ⏳ TODO |

---

**Last Updated:** January 31, 2026  
**Next Review:** After Phase 1 deployment

