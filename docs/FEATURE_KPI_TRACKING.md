# Feature KPI Tracking System

**Status:** Planned - GitHub Issue #511  
**Priority:** Medium - High Value Feature  
**Timeline:** Phase 2 (Guardian weeks 5-8) or standalone implementation  
**Estimated Hours:** 18 hours total (6+8+4)  

---

## Overview

Every WPShadow feature should be able to report **measurable benefits** to demonstrate real value to site administrators. The KPI (Key Performance Indicator) tracking system allows features to log events and display aggregated metrics showing tangible impact.

**User-Facing Goal:**
> "External Fonts Disabler saved you 3.5 hours and 45 MB bandwidth this month"

---

## Core Concept

### What Gets Tracked

Each feature can track multiple types of KPIs:

#### Performance Metrics
- **Time saved** (manual work automated)
- **Storage saved** (disk space freed)
- **Bandwidth saved** (data transfer reduced)
- **Page load improvements** (speed increases)
- **Database queries reduced** (optimization impact)

#### Security Metrics
- **Threats blocked** (security issues prevented)
- **Vulnerabilities fixed** (issues auto-resolved)
- **Failed logins blocked** (brute force prevention)

#### Environmental Metrics
- **Carbon footprint** (CO2 saved through efficiency)
- **Energy saved** (estimated power consumption reduction)

#### Content Metrics
- **Images optimized** (files compressed)
- **Posts improved** (content enhanced)
- **Broken links fixed** (automatically corrected)

---

## Architecture

### Core Classes

```
includes/core/
├── class-wps-kpi-tracker.php
│   └── log_event()          # Log a KPI event
│   └── get_metrics()        # Retrieve aggregated metrics
│   └── calculate_savings()  # Calculate totals
│
└── class-wps-kpi-calculator.php
    └── calculate_time_saved()
    └── calculate_storage_saved()
    └── calculate_environmental_impact()
    └── calculate_monetary_value()
```

### Storage Schema

**Events Storage** (rolling 90 days):
```php
wpshadow_kpi_events = [
    [
        'timestamp' => 1705699200,
        'feature_id' => 'external_fonts_disabler',
        'event_type' => 'bandwidth_saved',
        'value' => 150,
        'unit' => 'kb',
        'description' => 'Prevented external font loading',
    ],
]
```

**Summary Storage** (monthly aggregates):
```php
wpshadow_kpi_summary = [
    '2026-01' => [
        'total_time_saved' => 480, // minutes
        'total_storage_saved' => 152000, // KB
        'total_bandwidth_saved' => 45000, // KB
        'total_carbon_reduced' => 900, // g CO2
        'by_feature' => [
            'external_fonts_disabler' => [...],
            'guardian' => [...],
        ],
    ],
]
```

---

## Integration Guide

### Extending Abstract Feature

All features inherit from `WPSHADOW_Abstract_Feature`. New KPI methods will be added:

```php
abstract class WPSHADOW_Abstract_Feature {
    
    /**
     * Define which KPIs this feature tracks
     * @return array
     */
    protected function define_kpis() {
        return [
            'bandwidth_saved',
            'time_saved',
            'carbon_reduction',
        ];
    }
    
    /**
     * Log a KPI event
     * @param array $event
     */
    protected function log_kpi_event( $event ) {
        WPSHADOW_KPI_Tracker::log_event(
            $this->get_id(),
            $event
        );
    }
    
    /**
     * Get this feature's KPI summary
     * @param string $period 'week', 'month', 'year'
     * @return array
     */
    protected function get_kpi_summary( $period = 'month' ) {
        return WPSHADOW_KPI_Tracker::get_metrics(
            $this->get_id(),
            $period
        );
    }
}
```

### Example Implementations

#### External Fonts Disabler

```php
class WPS_Feature_External_Fonts_Disabler extends WPSHADOW_Abstract_Feature {
    
    protected function define_kpis() {
        return ['bandwidth_saved', 'carbon_reduction'];
    }
    
    public function block_external_fonts() {
        // Block Google Fonts, etc.
        
        // Log KPI events
        $this->log_kpi_event([
            'type' => 'bandwidth_saved',
            'value' => 150, // KB per page load
            'unit' => 'kb',
            'description' => 'Prevented external font loading',
        ]);
        
        $this->log_kpi_event([
            'type' => 'carbon_reduction',
            'value' => 3, // g CO2
            'unit' => 'g_co2',
            'description' => 'Reduced carbon footprint',
        ]);
    }
}
```

#### Guardian System (Auto-Fix)

```php
class WPS_Feature_Guardian extends WPSHADOW_Abstract_Feature {
    
    protected function define_kpis() {
        return ['time_saved', 'issues_prevented'];
    }
    
    public function auto_fix_permalinks() {
        // Fix permalink structure
        
        $this->log_kpi_event([
            'type' => 'time_saved',
            'value' => 15, // minutes
            'unit' => 'minutes',
            'description' => 'Auto-fixed permalink structure',
        ]);
        
        $this->log_kpi_event([
            'type' => 'issues_prevented',
            'value' => 1,
            'unit' => 'count',
            'description' => 'Prevented SEO issue',
        ]);
    }
}
```

#### Database Optimizer (future example)

```php
class WPS_Feature_Database_Optimizer extends WPSHADOW_Abstract_Feature {
    
    protected function define_kpis() {
        return ['storage_saved', 'time_saved', 'queries_reduced'];
    }
    
    public function clean_transients() {
        $deleted_count = 142;
        $size_freed = 2500; // KB
        
        $this->log_kpi_event([
            'type' => 'storage_saved',
            'value' => $size_freed,
            'unit' => 'kb',
            'description' => "Removed {$deleted_count} expired transients",
        ]);
        
        $this->log_kpi_event([
            'type' => 'time_saved',
            'value' => 5,
            'unit' => 'minutes',
            'description' => 'Automated database cleanup',
        ]);
    }
}
```

---

## User Interface

### Dashboard Widget

Main dashboard displays overall impact:

```
┌─────────────────────────────────────────┐
│ 💡 Your WPShadow Impact                 │
├─────────────────────────────────────────┤
│ 📊 This Month's Savings                 │
│   ⏱️  8 hours saved (~$400 value)      │
│   💾 152 MB storage freed               │
│   🌍 900g CO2 prevented                 │
│                                         │
│ 🏆 Top Features                         │
│   1. External Fonts - 3.5h, 45 MB      │
│   2. Guardian - 2.5h                   │
│   3. DB Optimizer - 2h, 85 MB          │
│                                         │
│ [View Full Report]                      │
└─────────────────────────────────────────┘
```

### Impact Tab (New Dashboard Tab)

Full reporting interface:
- Total savings across all features
- Breakdown by feature
- Breakdown by metric type
- Date range filtering (week/month/year/custom)
- Export to PDF/CSV
- Historical trend charts

### Feature-Specific Display

Each feature's settings page shows its specific impact:

```
External Fonts Disabler has saved you:
  ⏱️  3.5 hours this month
  💾 45 MB bandwidth
  🌍 900g CO2 prevented
  
That's equivalent to:
  💰 $175 in admin time (at $50/hour)
  🌲 Planting 0.5 trees
```

### Email Digest Integration

Weekly email includes KPI summary:

```
Your WPShadow Impact This Week
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Total Savings:
• Time: 2 hours ($100 value)
• Storage: 38 MB
• Carbon: 225g CO2

Top Features:
1. External Fonts: 1.2h, 12 MB
2. Guardian: 0.8h
3. DB Optimizer: 26 MB

[View Full Report →]
```

---

## Conversion Tables

### Time Value Conversions

```php
TIME_VALUES = [
    'simple_task'    => 5,   // minutes
    'medium_task'    => 15,  // minutes
    'complex_task'   => 30,  // minutes
    'developer_task' => 60,  // minutes
];
```

### Cost Conversions (User-Configurable)

```php
DEFAULT_RATES = [
    'admin_hourly'     => 50,  // USD
    'developer_hourly' => 100, // USD
];
```

### Environmental Impact

```php
ENVIRONMENTAL_FACTORS = [
    'kb_data_transfer' => 0.02,  // grams CO2 per KB
    'mb_storage_year'  => 0.5,   // grams CO2 per MB/year
];
```

---

## Implementation Phases

### Phase 1 (6 hours) - Core System
- [ ] Create KPI tracker core class
- [ ] Create KPI calculator class
- [ ] Extend abstract feature with KPI methods
- [ ] Add basic Impact tab to dashboard
- [ ] Update 3-5 existing features to log KPIs

**Priority Features for Phase 1:**
1. External Fonts Disabler (bandwidth, carbon)
2. Guardian Auto-Fix (time, issues prevented)
3. Dark Mode (energy saved)
4. Hotlink Protection (bandwidth)
5. Core Diagnostics (time saved)

### Phase 2 (8 hours) - UI & Reporting
- [ ] Add KPI widget to main dashboard
- [ ] Add feature-specific KPI display on settings pages
- [ ] Implement email digest KPI summary
- [ ] Add date range filtering
- [ ] Create PDF/CSV export functionality
- [ ] Add historical trend charts

### Phase 3 (4 hours) - Advanced Features
- [ ] Integrate with Gamification system
  - Badge: "High Impact Admin" (>20h saved/month)
  - Badge: "Eco Warrior" (>1kg CO2 prevented/month)
- [ ] Add environmental impact visualizations
- [ ] Add monetary value calculations
- [ ] Create monthly impact report email
- [ ] User-configurable hourly rates

---

## Dependencies

### Existing Systems
- `WPSHADOW_Abstract_Feature` - Base class for all features
- `WPSHADOW_Dashboard_Registry` - Dashboard tab system
- `WPSHADOW_Achievement_Badges` - Gamification system
- `WPSHADOW_Feature_Guardian` - Email reporting (Issue #491)

### New Systems (Built in Phase 1)
- `WPSHADOW_KPI_Tracker` - Event logging
- `WPSHADOW_KPI_Calculator` - Metric calculations
- KPI dashboard tab
- KPI storage schema

---

## Integration Timeline

### Option 1: Standalone Implementation
- Implement independently of Guardian
- Timeline: 3-4 weeks
- Benefits: Available sooner, applicable to all features immediately

### Option 2: Guardian Phase 2 Integration (Recommended)
- Implement during Guardian Phase 2 (weeks 5-8)
- Benefits: 
  - Guardian can log KPIs from day 1 of Phase 2
  - Predictive analytics can include KPI predictions
  - Unified planning and testing
  - Better integration with Guardian reports

### Suggested Approach
**Week 5-6:** Implement KPI Phase 1 (core system)  
**Week 7:** Implement KPI Phase 2 (UI)  
**Week 8:** Implement KPI Phase 3 (advanced features)  

This aligns perfectly with Guardian Phase 2 timeline.

---

## Success Metrics

### Technical Metrics
- [ ] 100% of active features define at least one KPI
- [ ] 95%+ of features actively log KPI events
- [ ] Dashboard load time <2 seconds with KPI data
- [ ] Export functionality works for datasets up to 10,000 events
- [ ] Calculations are accurate to within 5% margin

### User Metrics
- [ ] Impact dashboard shows meaningful data within 1 week of use
- [ ] Users report understanding plugin value better (survey)
- [ ] Reduced plugin uninstall rate by 10%+
- [ ] Increased Pro conversion rate by 5%+
- [ ] 70%+ of users view Impact tab monthly

### Business Metrics
- [ ] KPI data used in marketing materials
- [ ] Average reported savings: >10 hours/month per user
- [ ] Environmental impact: >1kg CO2/month per user
- [ ] Feature adoption increases after adding KPIs

---

## Example Use Cases

### Use Case 1: Site Admin Justifying Plugin to Client
**Scenario:** Freelancer needs to justify WPShadow cost to client  
**KPI Display:**
```
WPShadow saved your site:
• 12 hours of admin work ($600 value)
• 250 MB hosting storage
• 1.2 kg CO2 emissions
• Prevented 3 security issues

Total estimated value: $750/month
WPShadow cost: $0 (or $29 Pro)
ROI: Priceless / 2,500%
```

### Use Case 2: Marketing Material
**Testimonial Generation:**
> "WPShadow has saved me over 40 hours this year - that's an entire work week! The environmental impact dashboard shows I've prevented 15kg of CO2 emissions just by using External Fonts Disabler. These real numbers prove the value."

### Use Case 3: Feature Prioritization
**Internal Decision Making:**
- Which features provide the most value?
- What should we optimize next?
- What features should be highlighted in tutorials?

Data from KPI system answers these questions objectively.

---

## Related Issues

- **#511** - [ENHANCEMENT] Implement feature KPI tracking (this feature)
- **#487-498** - Guardian System (will log KPIs for issue prevention)
- **#502** - Tips Coach Integration (can show KPI benefits of tips)
- **#508** - Gamification Integration (badges for high KPI users)

---

## Documentation References

- [Guardian Implementation Roadmap](GUARDIAN_IMPLEMENTATION_ROADMAP.md) - Phase 2 integration plan
- [Architecture Review](ARCHITECTURE_REVIEW_GUARDIAN_SYSTEM.md) - System architecture
- Abstract Feature pattern - `includes/core/class-wpshadow-abstract-feature.php`

---

**KPI Tracking System - Ready for Implementation**

GitHub Issue: https://github.com/thisismyurl/wpshadow/issues/511
