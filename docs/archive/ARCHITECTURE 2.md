# WPShadow Architecture

## Folder Structure

```
includes/
├── diagnostics/          # Detection/problem identification
│   ├── class-diagnostic-*.php    # Individual diagnostic checks
│   └── class-diagnostic-registry.php
│
├── treatments/           # Fixes/solutions for detected problems
│   ├── class-treatment-*.php     # Individual treatment/fix implementations
│   ├── interface-treatment.php   # Treatment contract
│   └── class-treatment-registry.php
│
├── core/                 # Core utilities and tracking
│   ├── class-kpi-tracker.php           # KPI/metrics tracking
│   └── class-finding-status-manager.php # Kanban status management
│
└── ... (other features)
```

## Philosophy: Diagnostics vs Treatments

### Diagnostics (Detection Layer)
- **Purpose:** Find and identify problems
- **Responsibility:** Report issues accurately with severity data
- **Output:** Finding objects with ID, title, description, threat level
- **Location:** `includes/diagnostics/`
- **Example:** `Diagnostic_Backup` detects if backup plugin is missing

### Treatments (Solution Layer)
- **Purpose:** Fix identified problems
- **Responsibility:** Apply fixes safely with undo capability
- **Output:** Success/failure result with user-friendly message
- **Location:** `includes/treatments/`
- **Example:** `Treatment_Permalinks` applies SEO-friendly permalink structure

### Separation of Concerns
- **Diagnostics** should be fast, lightweight, read-only
- **Treatments** handle file writes, configuration changes, side effects
- One diagnostic can have multiple treatments (choose how to fix)
- One treatment can address one finding

## Core Systems

### KPI Tracker (`class-kpi-tracker.php`)
Tracks key metrics to prove value:
- **Findings detected** - How many issues were found
- **Fixes applied** - How many were resolved
- **Fixes percentage** - % of findings that were fixed
- **Time saved** - Estimated time saved (15 min per fix)
- **Data retention** - Last 90 days

**Usage:**
```php
KPI_Tracker::log_finding_detected( 'ssl-missing', 'critical' );
KPI_Tracker::log_fix_applied( 'ssl-missing', 'auto' );
$summary = KPI_Tracker::get_kpi_summary();
```

### Finding Status Manager (`class-finding-status-manager.php`)
GitHub project-style Kanban interface for managing findings:

**Statuses:**
- `detected` - New findings (left column)
- `ignored` - Won't deal with this
- `manual` - User will fix themselves
- `automated` - Guardian should auto-fix
- `fixed` - Already resolved

**Usage:**
```php
Finding_Status_Manager::set_finding_status( 'ssl-missing', 'automated' );
$by_status = Finding_Status_Manager::get_by_status( 'automated' );
$stats = Finding_Status_Manager::get_stats();
```

## Adding New Diagnostics

1. Create `includes/diagnostics/class-diagnostic-YOUR-CHECK.php`
2. Return finding array or null from `check()` method
3. Register in `Diagnostic_Registry::$diagnostics`

Example:
```php
class Diagnostic_Custom_Check {
    public static function check() {
        if ( $problem_exists ) {
            return array(
                'id'           => 'unique-id',
                'title'        => 'Problem Title',
                'description'  => 'What to do',
                'threat_level' => 50,
                'auto_fixable' => true,
            );
        }
        return null;
    }
}
```

## Adding New Treatments

1. Create `includes/treatments/class-treatment-YOUR-FIX.php`
2. Implement `Treatment_Interface`
3. Return result from `apply()` and `undo()` methods
4. Register in `Treatment_Registry::$treatments`

Example:
```php
class Treatment_Custom_Fix implements Treatment_Interface {
    public static function get_finding_id() {
        return 'unique-id';
    }
    
    public static function can_apply() {
        return /* check prerequisites */;
    }
    
    public static function apply() {
        /* do fix */
        KPI_Tracker::log_fix_applied( 'unique-id', 'auto' );
        return array( 'success' => true, 'message' => '...' );
    }
    
    public static function undo() {
        /* revert changes */
        return array( 'success' => true, 'message' => '...' );
    }
}
```

## Frontend: Kanban Board

The UI shows findings organized by status:

```
┌────────────────────────────────────────────────────────────┐
│ WPShadow Findings Board                                    │
├────────────┬────────────┬────────────┬────────────┬────────┤
│ Detected   │ Ignore     │ Manual     │ Automated  │ Fixed  │
│ (5)        │ (2)        │ (1)        │ (3)        │ (2)    │
├────────────┼────────────┼────────────┼────────────┼────────┤
│ • SSL      │ • Old TLS  │ • Backup   │ • Memory   │ • Perf │
│ • Backup   │ • Staging  │            │ • Debug    │ • Tags │
│ • Debug    │            │            │ • Plugins  │        │
│ • Tagline  │            │            │            │        │
│ • Plugins  │            │            │            │        │
└────────────┴────────────┴────────────┴────────────┴────────┘
```

Users drag findings between columns to:
- **Ignore** - Don't fix this
- **Manual** - I'll fix it manually
- **Automated** - Guardian should auto-fix
- **Fixed** - Manually confirm resolved

## KPI Display

Dashboard shows value delivered:
```
┌──────────────────────────────────┐
│ Your Site Health Dashboard       │
├──────────────────────────────────┤
│ Findings Detected: 13            │
│ Fixes Applied: 8 (62%)           │
│ Time Saved: 2h 0m (est.)         │
│ Status: 3 auto-fixes pending     │
└──────────────────────────────────┘
```

## Backward Compatibility

Old helper functions in `wpshadow.php` are maintained but should be phased out:
- `wpshadow_get_memory_limit_mb()` → Use `Diagnostic_Memory_Limit`
- `wpshadow_has_backup_plugin()` → Use `Diagnostic_Backup`
- `wpshadow_attempt_autofix()` → Use `Treatment_Registry::apply_treatment()`

## Next Steps

1. Port remaining diagnostics from `.archive/includes.bak/detectors/`
2. Implement treatments for auto-fixable diagnostics
3. Build Kanban board UI component
4. Add KPI dashboard widgets
5. Implement Guardian background job for auto-fixes
