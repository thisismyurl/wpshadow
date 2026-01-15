# WP Support Site Health Integration

## Overview

WP Support integrates deeply with WordPress Site Health to provide comprehensive health scoring, metrics tracking, and actionable recommendations. The system follows a **three-tier data flow architecture**:

```
Feature → WPS Dashboard → WordPress Core Site Health
```

## Architecture

### Data Flow

1. **Feature Level**: Each feature reports its own health metrics
2. **WPS Dashboard Level**: Aggregates feature scores into category scores (Security, Performance)
3. **WordPress Core Level**: Integrates with WordPress Site Health system via hooks

### Scoring System

#### Overall Health Score (0-100)
- **80-100**: Good (Green)
- **60-79**: Needs Improvement (Yellow)
- **0-59**: Critical (Red)

Formula: `(Security × 0.6) + (Performance × 0.4)`

### Feature Weight Categories

Features are weighted by their impact on overall site health:

| Weight | Multiplier | Use Case | Examples |
|--------|-----------|----------|----------|
| **Critical** | 1.0 | Core security, caching | Firewall, Page Cache, Core Integrity |
| **High** | 0.75 | Performance optimization | CDN, Image Optimizer, Script Deferral |
| **Medium** | 0.5 | Database/code cleanup | Database Cleanup, Asset Minification |
| **Low** | 0.25 | Minor tweaks | Head Cleanup, jQuery Cleanup |

## Security Score Calculation

**Maximum: 100 points**

### Security Features

| Feature | Weight | Base Points | Sub-Features |
|---------|--------|-------------|--------------|
| **Hardening** | Critical | 20 | All-or-nothing |
| **Firewall** | Critical | 20 | IP Blocking (30%), Rate Limiting (30%), Attack Detection (40%) |
| **Malware Scanner** | Critical | 15 | Pattern Detection (40%), Real-time Scanning (30%), Quarantine (30%) |
| **Core Integrity** | Critical | 15 | Checksum Verification (50%), Auto-Repair (50%) |
| **Traffic Monitor** | High | 10 | All-or-nothing |
| **Conflict Sandbox** | Medium | 10 | All-or-nothing |
| **Visual Regression** | Medium | 10 | All-or-nothing |

### Calculation Logic

```php
security_score = 0;

foreach (security_features as feature) {
    if (feature.enabled) {
        feature_score = calculate_feature_score(feature);
        weighted_score = feature_score * WEIGHT_MULTIPLIER;
        contribution = (weighted_score / 100) * feature.base_points;
        security_score += contribution;
    }
}

return min(100, security_score);
```

### Example: Firewall Score

```php
// Base score for being enabled
score = 40;

// Bonus for active blocklist
if (blocked_ips > 0) {
    score += 30;
}

// Bonus for rate limiting configuration
if (rate_limit < 100) {
    score += 30;
}

// Sub-feature breakdown:
// - IP Blocking: 30 points (if has blocked IPs)
// - Rate Limiting: 30 points (if configured)
// - Attack Detection: 40 points (always active when enabled)

return min(100, score);
```

## Performance Score Calculation

**Maximum: 100 points**

### Performance Features

| Feature | Weight | Base Points | Sub-Features |
|---------|--------|-------------|--------------|
| **Page Cache** | Critical | 15 | HTML Caching (50%), Device Detection (20%), Auto-Invalidation (30%) |
| **CDN Integration** | High | 12 | URL Rewriting (60%), API Integration (40%) |
| **Image Optimizer** | High | 10 | Compression (60%), Auto-Optimization (40%) |
| **Script Deferral** | High | 8 | All-or-nothing |
| **Critical CSS** | High | 8 | All-or-nothing |
| **Asset Minification** | Medium | 7 | All-or-nothing |
| **Database Cleanup** | Medium | 7 | 6 sub-features (see below) |
| **Image Lazy Loading** | Medium | 6 | All-or-nothing |
| **Script Optimizer** | Medium | 6 | All-or-nothing |
| **Conditional Loading** | Medium | 5 | All-or-nothing |
| **Head Cleanup** | Low | 4 | 9 sub-features (see below) |
| **Resource Hints** | Low | 3 | All-or-nothing |
| **Embed Disable** | Low | 3 | All-or-nothing |
| **jQuery Cleanup** | Low | 2 | All-or-nothing |
| **Block CSS Cleanup** | Low | 2 | All-or-nothing |
| **Google Fonts Disabler** | Low | 2 | All-or-nothing |

### Sub-Feature Scoring

#### Head Cleanup (100 points total)

Each sub-feature contributes specific points when enabled:

| Sub-Feature | Points | Impact |
|-------------|--------|--------|
| RSD Link | 5 | Minimal - legacy feature |
| WLWManifest Link | 5 | Minimal - Windows Live Writer |
| Shortlink | 5 | Minimal - rarely used |
| WP Generator Tag | 10 | Security - hides version |
| Feed Links | 10 | Medium - reduces HEAD size |
| REST API Link | 10 | Medium - reduces HEAD size |
| oEmbed Links | 15 | High - removes 3-4 tags |
| Emoji Scripts | 20 | High - removes scripts + styles |
| DNS Prefetch | 20 | High - removes external connections |

**Example**: If you enable "Emoji Scripts" (20 pts), "DNS Prefetch" (20 pts), and "oEmbed" (15 pts), your Head Cleanup score is 55/100.

#### Database Cleanup (100 points total)

| Sub-Feature | Points | Impact |
|-------------|--------|--------|
| Revisions | 25 | High - often largest table bloat |
| Auto-Drafts | 20 | Medium-high - accumulates quickly |
| Trashed Posts | 15 | Medium - depends on workflow |
| Spam Comments | 15 | Medium - depends on spam volume |
| Transients | 15 | Medium - can grow significantly |
| Optimize Tables | 10 | Low - maintenance task |

### Example: Database Cleanup Score

```php
score = 0;

if (cleanup_revisions_enabled) score += 25;
if (cleanup_autodrafts_enabled) score += 20;
if (cleanup_trash_enabled) score += 15;
if (cleanup_spam_enabled) score += 15;
if (cleanup_transients_enabled) score += 15;
if (optimize_tables_enabled) score += 10;

return min(100, score);
```

## WordPress Site Health Integration

### Test Registration

WP Support registers 4 tests with WordPress Site Health:

1. **WPS Security Score** (Direct Test)
   - Status: good/recommended/critical
   - Badge: "Security" (blue)
   - Action: Links to Security settings

2. **WPS Performance Score** (Direct Test)
   - Status: good/recommended/critical
   - Badge: "Performance" (orange)
   - Action: Links to Performance settings

3. **WPS Overall Health** (Direct Test)
   - Status: good/recommended/critical
   - Badge: "WP Support" (green)
   - Action: Links to WPS dashboard

4. **WPS Feature Status** (Async Test)
   - Status: good if features enabled, recommended if none
   - Badge: "Features" (purple)
   - Action: Links to feature management

### Debug Information

Added to Site Health Info tab under "WP Support":

- Plugin Version
- Overall Health Score
- Security Score
- Performance Score
- Enabled Features Count
- Active Features List

### Implementation

```php
// Register with WordPress
add_filter('site_status_tests', function($tests) {
    $tests['direct']['wps_security_score'] = [
        'label' => 'WP Support Security Score',
        'test'  => [WPS_Site_Health_Integration::class, 'test_security_score']
    ];
    return $tests;
});

// Add debug info
add_filter('debug_information', function($info) {
    $info['wp-support'] = [
        'label' => 'WP Support',
        'fields' => [
            'overall_health' => [
                'label' => 'Overall Health Score',
                'value' => '85/100'
            ]
        ]
    ];
    return $info;
});
```

## Dashboard Widget

### Location
- WordPress Dashboard (via `wp_add_dashboard_widget()`)
- Priority: High
- Position: Normal column

### Display Elements

1. **Overall Health Circle**
   - Large circular indicator (120px)
   - Color-coded: Green (80+), Yellow (60-79), Red (0-59)
   - Displays overall score

2. **Category Breakdown Bars**
   - Security bar (gradient fill, percentage width)
   - Performance bar (gradient fill, percentage width)
   - Labels and scores (X/100)

3. **Action Buttons**
   - "View Site Health" → WordPress Site Health page
   - "WP Support Dashboard" → WPS dashboard

4. **Recommendations Section**
   - Shown when scores < 60
   - Context-aware suggestions
   - Links to relevant settings

### AJAX Updates

Widget auto-refreshes every 5 minutes via AJAX:

```javascript
setInterval(function() {
    $.post(ajaxurl, {
        action: 'wps_get_health_score',
        nonce: wpsHealth.nonce
    }, function(response) {
        updateScores(response.data);
    });
}, 300000); // 5 minutes
```

## API Endpoints

### Get Health Score (AJAX)

**Endpoint**: `wp_ajax_wps_get_health_score`

**Request**:
```javascript
{
    action: 'wps_get_health_score',
    nonce: 'abc123'
}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "overall": 85,
        "security": 90,
        "performance": 78,
        "features": {
            "firewall": {
                "enabled": true,
                "score": 100,
                "sub_features": {
                    "ip_blocking": {"enabled": true, "points": 30},
                    "rate_limiting": {"enabled": true, "points": 30},
                    "attack_detection": {"enabled": true, "points": 40}
                }
            }
        }
    }
}
```

## Developer Hooks

### Filters

#### `wps_health_security_score`
Modify security score calculation.

```php
add_filter('wps_health_security_score', function($score, $features) {
    // Custom logic
    return $score;
}, 10, 2);
```

#### `wps_health_performance_score`
Modify performance score calculation.

```php
add_filter('wps_health_performance_score', function($score, $features) {
    // Custom logic
    return $score;
}, 10, 2);
```

#### `wps_health_feature_weight`
Modify individual feature weight.

```php
add_filter('wps_health_feature_weight', function($weight, $feature_id) {
    if ($feature_id === 'custom-feature') {
        return 'critical';
    }
    return $weight;
}, 10, 2);
```

### Actions

#### `wps_health_score_calculated`
Triggered after score calculation.

```php
add_action('wps_health_score_calculated', function($overall, $security, $performance) {
    // Log or process scores
}, 10, 3);
```

## Best Practices

### 1. Enable High-Impact Features First
Start with Critical weight features:
- Page Cache (15 points)
- Firewall (20 points)
- Hardening (20 points)

### 2. Progressive Enhancement
Enable features progressively, testing after each:
1. Security features (Firewall, Hardening)
2. Caching (Page Cache, CDN)
3. Optimization (Image Optimizer, Script Deferral)
4. Cleanup (Head Cleanup, Database Cleanup)

### 3. Monitor Score Changes
After enabling a feature:
- Check WordPress Site Health
- Review WPS dashboard widget
- Monitor actual performance metrics

### 4. Sub-Feature Granularity
For features like Head Cleanup:
- Enable high-point items first (Emoji Scripts, DNS Prefetch)
- Test compatibility
- Enable lower-point items incrementally

## Scoring Examples

### Scenario 1: Security-Focused Site

**Enabled Features**:
- Hardening (Critical): 20 × 1.0 = 20 points
- Firewall (Critical): 20 × 1.0 = 20 points
- Malware Scanner (Critical): 15 × 1.0 = 15 points
- Core Integrity (Critical): 15 × 1.0 = 15 points

**Security Score**: 70/100
**Overall Health**: `(70 × 0.6) + (0 × 0.4) = 42/100` (Critical)

*Recommendation*: Enable performance features to balance score.

### Scenario 2: Performance-Focused Site

**Enabled Features**:
- Page Cache (Critical): 15 × 1.0 = 15 points
- CDN Integration (High): 12 × 0.75 = 9 points
- Image Optimizer (High): 10 × 0.75 = 7.5 points
- Script Deferral (High): 8 × 0.75 = 6 points

**Performance Score**: 37.5/100
**Overall Health**: `(0 × 0.6) + (37.5 × 0.4) = 15/100` (Critical)

*Recommendation*: Enable security features to balance score.

### Scenario 3: Balanced Configuration

**Enabled Features**:
- Hardening (Critical): 20 points
- Firewall (Critical): 20 points
- Core Integrity (Critical): 15 points
- Page Cache (Critical): 15 points
- CDN Integration (High): 9 points
- Image Optimizer (High): 7.5 points

**Security Score**: 55/100
**Performance Score**: 31.5/100
**Overall Health**: `(55 × 0.6) + (31.5 × 0.4) = 45.6/100` (Critical)

*Recommendation*: Enable more features in both categories for "Good" status (80+).

### Scenario 4: Optimal Configuration

**All Security Features Enabled**: 90/100
**All Performance Features Enabled**: 95/100
**Overall Health**: `(90 × 0.6) + (95 × 0.4) = 92/100` (Good)

## Troubleshooting

### Low Scores Despite Enabled Features

**Check**:
1. Feature actually active (not just enabled)
2. Sub-features configured correctly
3. No conflicts preventing feature operation
4. Cache cleared after enabling features

### WordPress Site Health Not Showing WPS Tests

**Solutions**:
1. Clear WordPress transients
2. Deactivate/reactivate WP Support plugin
3. Check PHP error logs for hook registration failures
4. Verify `site_status_tests` filter not being overridden

### Dashboard Widget Not Appearing

**Solutions**:
1. Check user has `manage_options` capability
2. Ensure widget not manually hidden via Screen Options
3. Verify JavaScript assets loading correctly
4. Check for JavaScript console errors

## Future Enhancements

- Historical score tracking (trends over time)
- Email notifications for score drops
- Comparative scoring (vs. similar sites)
- Automated recommendations engine
- Integration with external monitoring services
- Per-feature impact visualization
- Custom scoring profiles (security-heavy, performance-heavy, balanced)

---

**Last Updated**: January 15, 2026
**Version**: 1.0.0
**Maintained by**: thisismyurl
