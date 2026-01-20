# WPShadow Implementation Roadmap

## Phase 1: Foundation (COMPLETED)
✅ Diagnostic registry system
✅ Individual diagnostic checks (9 checks)
✅ Treatment interface and registry
✅ KPI tracking system
✅ Finding status manager (Kanban board backend)
✅ Initial treatments (Permalinks, Memory Limit)

## Phase 2: Core Diagnostics (IN PROGRESS)

### From Old Codebase (`.archive/includes.bak/detectors/`)
- [x] Memory Limit
- [x] Backup Plugin  
- [x] Permalinks
- [x] Site Description/Tagline
- [x] SSL Configuration
- [ ] Custom Field Validation
- [ ] Image Optimization
- [ ] Database Optimization
- [ ] Performance Monitoring

### New Diagnostics to Add
- [ ] Plugin Security Audit
- [ ] Theme Security Check
- [ ] Database Health
- [ ] File Integrity
- [ ] API Health Check
- [ ] Cache Configuration
- [ ] CDN Status
- [ ] Email Deliverability

## Phase 3: Treatments (NEXT)

### Priority Treatments (Auto-fixable)
- [ ] Treatment_Debug_Mode - Disable debug mode in wp-config
- [ ] Treatment_Tagline - Add site tagline
- [ ] Treatment_Cache_Control - Enable caching headers
- [ ] Treatment_GZIP_Compression - Enable GZIP
- [ ] Treatment_Image_Optimization - Bulk optimize images

### Medium Priority (Requires Caution)
- [ ] Treatment_Plugin_Update - Update plugins safely
- [ ] Treatment_WordPress_Update - Update WP core
- [ ] Treatment_Disable_Unused_Plugins - Disable unused plugins

### Low Priority (Manual)
- [ ] Treatment_SSL_Certificate - Guide for SSL setup
- [ ] Treatment_Backup_Plugin_Install - Guide for backup setup
- [ ] Treatment_Performance_Tuning - Performance recommendations

## Phase 4: UI/UX (PLANNED)

### Kanban Board Interface
```
┌─────────────┬──────────┬──────────┬───────────┬──────────┐
│  Detected   │  Ignore  │  Manual  │ Automated │  Fixed   │
│    (5)      │   (2)    │   (1)    │    (3)    │   (2)    │
├─────────────┼──────────┼──────────┼───────────┼──────────┤
│ • Finding 1 │ Finding  │ Finding  │ Finding   │ Finding  │
│ • Finding 2 │   X      │   Y      │    Z      │    W     │
│ • Finding 3 │          │          │           │          │
│ • Finding 4 │          │          │           │          │
│ • Finding 5 │          │          │           │          │
└─────────────┴──────────┴──────────┴───────────┴──────────┘
```

### KPI Dashboard Widget
```
┌──────────────────────────────────────┐
│ Your Site Health Value Delivered     │
├──────────────────────────────────────┤
│ Issues Found: 13                     │
│ Issues Fixed: 8 (62%)                │
│ Time Saved: 2 hours 0 minutes        │
│ Auto-Fixes This Month: 3             │
│ Money Saved (est.): $240*             │
│                                      │
│ * Based on $30/hr consultant rate   │
└──────────────────────────────────────┘
```

### Findings Detail View
- Finding card with:
  - Threat level gauge
  - Description
  - Action buttons (auto-fix, manual guide, ignore)
  - Related KB articles
  - Notes section
  - Status history

## Phase 5: Guardian (FUTURE)

### Background Job System
- Scheduled health checks (hourly/daily)
- Auto-apply fixes based on finding status
- Email reports on findings/fixes
- Slack notifications (future)

### Smart Features
- Machine learning threat level prediction
- Proactive issue prevention
- Performance baseline tracking
- Anomaly detection

## Data Structures

### Finding Status Map (in options)
```php
'wpshadow_finding_status_map' => array(
    'detected' => array(
        array( 'id' => 'ssl-missing', 'timestamp' => 1234567890, 'notes' => '' ),
    ),
    'ignored' => array(),
    'manual' => array(
        array( 'id' => 'backup-missing', 'timestamp' => 1234567890, 'notes' => '' ),
    ),
    'automated' => array(
        array( 'id' => 'memory-limit-low', 'timestamp' => 1234567890, 'notes' => '' ),
    ),
    'fixed' => array(),
)
```

### KPI Tracking (in options)
```php
'wpshadow_kpi_tracking' => array(
    'findings_detected' => array(
        'ssl-missing_2026-01-20' => array(
            'finding_id' => 'ssl-missing',
            'severity' => 'critical',
            'date' => '2026-01-20 14:30:00',
            'count' => 1,
        ),
    ),
    'fixes_applied' => array(
        array(
            'finding_id' => 'memory-limit-low',
            'method' => 'auto',
            'date' => '2026-01-20 14:31:00',
        ),
    ),
    'findings_dismissed' => array(),
)
```

## File Organization

```
includes/
├── diagnostics/              # Problem detection
│   ├── class-diagnostic-memory-limit.php
│   ├── class-diagnostic-backup.php
│   ├── class-diagnostic-permalinks.php
│   ├── class-diagnostic-tagline.php
│   ├── class-diagnostic-ssl.php
│   ├── class-diagnostic-outdated-plugins.php
│   ├── class-diagnostic-debug-mode.php
│   ├── class-diagnostic-wordpress-version.php
│   ├── class-diagnostic-plugin-count.php
│   ├── class-diagnostic-registry.php
│   └── README.md
│
├── treatments/              # Problem solutions
│   ├── interface-treatment.php
│   ├── class-treatment-permalinks.php
│   ├── class-treatment-memory-limit.php
│   ├── class-treatment-debug-mode.php        # TO ADD
│   ├── class-treatment-registry.php
│   └── README.md
│
├── core/                    # Utilities
│   ├── class-kpi-tracker.php
│   ├── class-finding-status-manager.php
│   └── (other core utilities)
│
└── ARCHITECTURE.md          # This file
```

## Integration Points

### Main Plugin File (wpshadow.php)
- Load Diagnostic_Registry on `plugins_loaded`
- Load Treatment_Registry on `plugins_loaded`
- AJAX endpoints for status changes
- AJAX endpoints for treatment application

### Dashboard UI
- Display findings by status
- Kanban board for organizing
- KPI metrics display
- Treatment application buttons

### Admin Menu
- Main dashboard
- Findings board (Kanban)
- KPI/History report
- Settings
- Help/KB articles

## Success Metrics

By end of Phase 4, we should have:
- ✅ 20+ diagnostics detecting issues
- ✅ 10+ treatments auto-fixing problems
- ✅ Kanban board UI for organizing findings
- ✅ KPI dashboard showing value delivered
- ✅ Ability to track issues and fixes over time
- ✅ Data proving "X hours saved" and "Y issues prevented"

## Notes

- Keep treatments safe by default (file backups, validation)
- All fixes tracked via KPI system for proof of value
- Status manager enables user control (Kanban board)
- Separate diagnostics from treatments for flexibility
- Scalable design allows easy addition of new checks/fixes
