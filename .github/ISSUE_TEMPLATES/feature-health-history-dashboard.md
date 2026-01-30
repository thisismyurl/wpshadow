# [Feature] Health History Dashboard - Visual Proof of Value

**Labels:** `feature`, `dashboard`, `analytics`, `visualization`
**Assignee:** TBD
**Milestone:** v1.3
**Priority:** HIGH

## Problem Statement
**The Genius Opportunity We're Missing:**

WPShadow tracks everything (activity log, findings, treatments, KPIs) but we're not **visualizing** the improvement over time.

**What Users Need To See:**
- "My site health went from 68% to 94% in 30 days"
- "WPShadow fixed 47 issues and saved me 12.3 hours"
- "Security improved 35%, Performance improved 22%"

**Current State:** We have all the data but it's buried in tables and logs. No graphs, no trends, no visual story.

**The Opportunity:** This would be the MOST SHAREABLE feature. Users would screenshot graphs showing improvement and post them everywhere.

## Proposed Solution
Add a **Health History Dashboard** that shows site health trends over time with beautiful, informative charts.

### Key Features

#### 1. Overall Health Trend Line Graph (Hero Visual)
```
Overall Site Health (Last 90 Days)

100% ┼╮
     │ ╰─╮
 75% ┤    ╰─╮
     │       ╰──────╮
 50% ┤              ╰─────
     │
 25% ┤
     │
  0% ┼────────────────────────────
     Jan  Feb  Mar  Apr  May

Current: 94% (+26 points since Jan 1)
```

#### 2. Category Breakdown Gauges (Stacked Over Time)
Show trend for each category (Security, Performance, Quality, etc.)

```
Security:   95% ↑ +15 pts  [▓▓▓▓▓▓▓▓▓▓░]
Performance: 78% ↑ +12 pts  [▓▓▓▓▓▓▓▓░░░]
Quality:     92% ↑ +8 pts   [▓▓▓▓▓▓▓▓▓░░]
```

#### 3. Fixes Applied Timeline
Visual timeline of treatments with impact markers

```
May 2026
├─ 5/30: SSL Redirect Enabled (+15 security points)
├─ 5/28: Expired Transients Deleted (freed 45 MB)
└─ 5/25: File Permissions Fixed (critical issue)

April 2026
├─ 4/20: Memory Limit Increased (+8 performance)
└─ 4/15: Debug Mode Disabled (+10 security)
```

#### 4. Value Delivered Metrics
```
┌────────────────────────────────┐
│ VALUE DELIVERED (LAST 30 DAYS) │
├────────────────────────────────┤
│ ⏱️  Time Saved: 12.3 hours      │
│ 🛡️  Issues Fixed: 47           │
│ 📈 Health Improved: +26 points  │
│ 💰 Estimated Value: $615        │
└────────────────────────────────┘
```

## Implementation Checklist

### Phase 1: Data Collection & Storage
- [ ] Create `includes/analytics/class-health-history.php`
- [ ] Record daily health snapshots in new option: `wpshadow_health_history`
- [ ] Store: `{ date, overall_health, security, performance, quality, ... }`
- [ ] Limit to last 90 days of data (trim old entries)
- [ ] Hook into scan completion to record snapshot
- [ ] Backfill historical data from activity log (if possible)

### Phase 2: Chart Library Integration
- [ ] Add Chart.js via CDN or bundle (lightweight, MIT license)
- [ ] Create `assets/js/health-history-charts.js`
- [ ] Create reusable chart components
- [ ] Responsive design (mobile-friendly)
- [ ] Dark mode support

### Phase 3: Dashboard Page
- [ ] Create `includes/views/health-history.php`
- [ ] Add menu item: WPShadow → Health History
- [ ] Layout: Hero trend chart + category gauges + timeline + metrics
- [ ] Date range selector (7/30/60/90 days)
- [ ] "Export Image" button (save chart as PNG)
- [ ] "Share" button (pre-filled social media text)

### Phase 4: Dashboard Widget
- [ ] Create compact version for main dashboard
- [ ] Show mini trend spark line
- [ ] Current health score + change indicator (↑ +12)
- [ ] Link to full Health History page

### Phase 5: Visualization Components

#### Line Chart (Overall Health Over Time)
```javascript
// Chart.js configuration
{
    type: 'line',
    data: {
        labels: ['Jan 1', 'Jan 8', 'Jan 15', ...],
        datasets: [{
            label: 'Overall Health',
            data: [68, 71, 74, 79, 82, 87, 94],
            borderColor: '#00a32a',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        scales: {
            y: { min: 0, max: 100, title: { text: 'Health Score' } }
        }
    }
}
```

#### Multi-Line Chart (Categories)
```javascript
// Show all categories on one chart
datasets: [
    { label: 'Security', data: [...], borderColor: '#d63638' },
    { label: 'Performance', data: [...], borderColor: '#f0b849' },
    { label: 'Quality', data: [...], borderColor: '#00a32a' }
]
```

#### Bar Chart (Fixes Per Week)
```javascript
{
    type: 'bar',
    data: {
        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
        datasets: [{
            label: 'Fixes Applied',
            data: [12, 8, 15, 12],
            backgroundColor: '#2271b1'
        }]
    }
}
```

### Phase 6: Data API
- [ ] AJAX handler: `get-health-history-handler.php`
- [ ] Endpoint: `wp_ajax_wpshadow_get_health_history`
- [ ] Parameters: `date_range` (7, 30, 60, 90 days)
- [ ] Returns: JSON with dates, scores, events
- [ ] Cache results (transient, 1 hour TTL)

## Technical Implementation

### Data Structure
```php
// Option: wpshadow_health_history
[
    'snapshots' => [
        [
            'date' => '2026-05-30',
            'timestamp' => 1748617200,
            'overall' => 94,
            'security' => 95,
            'performance' => 78,
            'quality' => 92,
            'seo' => 88,
            'accessibility' => 90,
            'findings_count' => 3,
            'critical_count' => 0
        ],
        // ... more daily snapshots
    ],
    'milestones' => [
        [
            'date' => '2026-05-30',
            'event' => 'SSL Redirect Enabled',
            'impact' => '+15 security points',
            'type' => 'treatment'
        ],
        // ... significant events
    ]
]
```

### Daily Snapshot Recording
```php
// Hook into scheduled scan completion
add_action( 'wpshadow_scheduled_scan_completed', 'wpshadow_record_health_snapshot' );

function wpshadow_record_health_snapshot() {
    $history = get_option( 'wpshadow_health_history', array(
        'snapshots' => array(),
        'milestones' => array()
    ) );

    // Get current health scores
    $health = wpshadow_get_health_status();

    // Add new snapshot
    $history['snapshots'][] = array(
        'date' => current_time( 'Y-m-d' ),
        'timestamp' => current_time( 'timestamp' ),
        'overall' => $health['overall'],
        'security' => $health['security'],
        'performance' => $health['performance'],
        // ... other categories
    );

    // Keep only last 90 days
    $cutoff = strtotime( '-90 days' );
    $history['snapshots'] = array_filter(
        $history['snapshots'],
        fn($s) => $s['timestamp'] >= $cutoff
    );

    update_option( 'wpshadow_health_history', $history );
}
```

### Chart Rendering
```php
// includes/views/health-history.php
<div class="wpshadow-health-history">
    <h1><?php esc_html_e( 'Health History', 'wpshadow' ); ?></h1>

    <div class="date-range-selector">
        <button data-range="7"><?php esc_html_e( '7 Days', 'wpshadow' ); ?></button>
        <button data-range="30" class="active"><?php esc_html_e( '30 Days', 'wpshadow' ); ?></button>
        <button data-range="60"><?php esc_html_e( '60 Days', 'wpshadow' ); ?></button>
        <button data-range="90"><?php esc_html_e( '90 Days', 'wpshadow' ); ?></button>
    </div>

    <div class="chart-container">
        <canvas id="overallHealthChart"></canvas>
    </div>

    <div class="category-trends">
        <canvas id="categoryTrendsChart"></canvas>
    </div>

    <div class="fixes-timeline">
        <h2><?php esc_html_e( 'Improvements Made', 'wpshadow' ); ?></h2>
        <?php // Timeline of treatments with impact ?>
    </div>

    <div class="value-metrics">
        <h2><?php esc_html_e( 'Value Delivered', 'wpshadow' ); ?></h2>
        <?php // Time saved, issues fixed, etc. ?>
    </div>
</div>
```

## Philosophy Alignment
✅ **Philosophy #2**: Free forever (uses Chart.js MIT license)
✅ **Philosophy #8**: Inspire confidence (visual proof of improvement)
✅ **Philosophy #9**: Show value (literally visualizes value delivered)
✅ **Philosophy #11**: Talk-about-worthy (users will share their graphs)

## User Stories
1. **Site Owner**: "I want to show my boss we're making the site better"
2. **Agency**: "I need proof of value to justify my monthly retainer"
3. **Developer**: "I want to see if my optimizations are working"
4. **Marketer**: "I need a compelling graph for my case study"

## Success Metrics
- % of users who visit Health History page (target: 40%+)
- Social shares of health graphs (track via UTM)
- Upgrade conversions after viewing history (measure funnel)
- User retention improvement (engaged users see their progress)

## Mockup

### Desktop View
```
┌─────────────────────────────────────────────────────────┐
│ WPShadow - Health History                    [Export]   │
├─────────────────────────────────────────────────────────┤
│                                                         │
│ Overall Site Health (Last 30 Days)                     │
│ ┌─────────────────────────────────────────────────┐   │
│ │100%  ╱────────────                              │   │
│ │ 75% ╱                                           │   │
│ │ 50%╱                                            │   │
│ │ 25%                                             │   │
│ │  0%─────────────────────────────────────────────│   │
│ │    May 1      May 15      May 30               │   │
│ └─────────────────────────────────────────────────┘   │
│ Current: 94% (+26 points since May 1)                  │
│                                                         │
│ Category Breakdown                                      │
│ ┌──────────────┬──────────────┬──────────────┐        │
│ │  Security    │ Performance  │   Quality     │        │
│ │     95%      │     78%      │     92%       │        │
│ │   ↑ +15      │   ↑ +12      │   ↑ +8        │        │
│ └──────────────┴──────────────┴──────────────┘        │
│                                                         │
│ Improvements Made This Month                            │
│ • May 30: SSL Redirect Enabled (+15 security)          │
│ • May 28: Expired Transients Deleted (freed 45 MB)     │
│ • May 25: File Permissions Fixed (critical)            │
│                                                         │
│ Value Delivered (30 Days)                               │
│ ⏱️  Time Saved: 12.3 hours | 🛡️  Issues Fixed: 47      │
└─────────────────────────────────────────────────────────┘
```

## Future Enhancements (Pro)
- **Predictive Trends**: "At this rate, you'll reach 98% health in 2 weeks"
- **Comparison Mode**: Compare multiple sites side-by-side
- **Custom Reports**: Schedule PDF reports with health trends
- **Annotations**: Add notes to timeline ("Redesign launch", "Black Friday")
- **Goal Setting**: "I want to reach 95% security by June 1"
- **Benchmarking**: "Your site improved faster than 78% of similar sites"

## Related Files
- `includes/core/class-activity-logger.php` - Source of historical data
- `includes/dashboard/` - Dashboard integration
- `includes/core/functions-category-metadata.php` - Health score calculations
- `includes/reporting/class-kpi-advanced-features.php` - KPI data

## Dependencies
- Chart.js (MIT license, ~100KB minified)
- Or use native CSS for simple bar/line charts

## Notes
**This is THE killer feature.** Visual proof of value is incredibly powerful for:
- User retention (seeing progress encourages continued use)
- Word of mouth (shareable graphs)
- Upgrades (users who see value are more likely to pay)
- Agencies (client presentations need visual proof)

We have all the data already. We just need to make it beautiful and visible.
