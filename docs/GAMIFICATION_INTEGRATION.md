# Gamification Integration Guide

## Overview

The gamification system integrates with multiple WPShadow features to provide automatic achievement tracking and badge rewards. This document shows how achievements are awarded through existing features.

## Integration Map

### 1. Perfect Health Guardian Badge 🏥
**Integration:** Core Diagnostics Feature
**Trigger:** Site maintains 100% health for 7 consecutive days

```
└── Core Diagnostics Feature
    ├── Checks site health score daily
    └── Gamification watches: wpshadow_perfect_health_days
        └── Increments each day with 0 critical + 0 recommended issues
            └── Resets on any issues detected
                └── Award badge when streak reaches 7 days
```

**How it works:**
1. Core Diagnostics monitors WordPress health status
2. Each day, gamification checks if health is perfect (line: `check_site_health_achievement()`)
3. Streak counter increments: `wpshadow_perfect_health_days`
4. On 7-day streak: Award `perfect_health_week` badge

### 2. Accessibility Champion Badge ♿
**Integration:** a11y Audit Feature
**Trigger:** Site passes accessibility audit with 90%+ score

```
└── a11y Audit Feature
    ├── Runs accessibility scans
    └── Stores score in: wpshadow_a11y_latest_score
        └── Gamification watches this option daily
            └── If score >= 90: Award a11y_champion badge
```

**How it works:**
1. a11y-audit feature runs accessibility checks
2. Stores latest score in `wpshadow_a11y_latest_score` option
3. Gamification checks this daily (line: `check_a11y_achievement()`)
4. Badge awarded when score >= 90

### 3. Performance Optimizer Badge ⚡
**Integration:** Plugin Audit Feature
**Trigger:** Achieve 90%+ performance score across all pages

```
└── Plugin Audit Feature
    ├── Profiles page performance
    └── Stores score in: wpshadow_performance_latest_score
        └── Gamification watches this option daily
            └── If score >= 90: Award performance_optimizer badge
```

**How it works:**
1. Plugin audit feature measures performance metrics
2. Stores latest performance score in `wpshadow_performance_latest_score`
3. Gamification checks this daily (line: `check_performance_achievement()`)
4. Badge awarded when score >= 90

### 4. Security Hardened Badge 🔒
**Integration:** Multiple Security Features
**Trigger:** All recommended security features enabled

```
└── Security Features
    ├── iframe-busting (enabled by user)
    ├── hotlink-protection (enabled by user)
    └── external-fonts-disabler (enabled by user)
        └── Gamification checks all three daily
            └── If all enabled: Award security_hardened badge
```

**How it works:**
1. User enables security features through WPShadow admin
2. Each feature stores enabled status in options
3. Gamification checks all three daily (line: `check_security_achievement()`)
4. Badge awarded when all three are enabled
5. Can be earned multiple times if disabled and re-enabled

### 5. Feature Usage Badges 👣 🔭 🎓
**Integration:** Feature Registry
**Trigger:** Enabling specific numbers of features

```
└── Feature Registry
    ├── Tracks enabled features
    └── Stores count in: wpshadow_enabled_features
        └── Gamification checks this daily
            └── Milestone badges:
                ├── 1+ enabled → First Step badge
                ├── 5+ enabled → Feature Explorer badge
                ├── 10+ enabled → Feature Master badge
                └── 50+ enabled → Cleanup Champion badge (issue fixes tracked)
```

**How it works:**
1. Feature Registry maintains list of enabled features
2. Stores in `wpshadow_enabled_features` option
3. Gamification counts enabled features (line: `check_feature_usage_achievements()`)
4. Badges awarded at thresholds: 1, 5, 10, 50

### 6. Log Keeper Badge 📋
**Integration:** Core Diagnostics (Data Retention Sub-Feature)
**Trigger:** Zero errors for 7 consecutive days

```
└── Core Diagnostics Feature
    ├── Activity Log Cleanup (sub-feature)
    ├── Error Log Cleanup (sub-feature)
    └── Tracks daily error count: wpshadow_error_count_today
        └── Gamification watches this daily
            └── If count = 0:
                ├── Increment wpshadow_clean_log_days
                └── On 7-day streak: Award clean_logs badge
            └── If count > 0: Reset streak
```

**How it works:**
1. Core Diagnostics monitors error logs daily
2. Stores count of new errors in `wpshadow_error_count_today`
3. Gamification checks this daily (line: `check_error_log_achievement()`)
4. Streak counter increments on zero errors
5. Badge awarded when streak reaches 7 days
6. Resets if any errors detected

### 7. HTTPS Champion Badge 🔐
**Integration:** Core Site Configuration
**Trigger:** Proper SSL/HTTPS configuration

```
└── WordPress Site Configuration
    ├── is_ssl() function
    └── siteurl option
        └── Gamification checks this daily
            └── If HTTPS detected: Award ssl_champion badge
```

**How it works:**
1. Gamification checks WordPress SSL status daily (line: `check_ssl_achievement()`)
2. Uses native WordPress `is_ssl()` function
3. Verifies siteurl is HTTPS
4. Badge awarded when HTTPS is active

## Data Structures for Feature Integration

### How Features Should Update Gamification

If a feature needs to update gamification tracking:

```php
// Feature updates its score
update_option( 'wpshadow_performance_latest_score', 92.5 );
// Gamification picks it up on next daily check

// Or manually trigger achievements
\WPShadow\CoreSupport\WPShadow_Gamification::award_badge( 'badge_id' );
```

### Expected Options Pattern

Features should follow this pattern for gamification integration:

```php
// Store latest score
update_option( 'wpshadow_{feature_name}_latest_score', $score );

// Store daily counts
update_option( 'wpshadow_{feature_name}_count_today', $count );

// Store enabled features list
update_option( 'wpshadow_enabled_features', $enabled_array );
```

## Achievement Check Flow

```
┌─────────────────────────────────────┐
│     WordPress init Hook             │
│    (runs on every page load)         │
└────────────────┬────────────────────┘
                 │
                 ▼
    ┌────────────────────────────┐
    │ check_achievements()       │
    │                            │
    │ Is today different from    │
    │ wpshadow_last_achievement  │
    │ _check?                    │
    └────────────┬───────────────┘
                 │
        ┌────────┴────────┐
        │                 │
       YES               NO
        │                 │
        ▼                 │
    ┌─────────────────────────────────────┐
    │ Run all 7 daily checks:             │
    │ ├─ check_site_health_achievement    │
    │ ├─ check_a11y_achievement          │
    │ ├─ check_performance_achievement   │
    │ ├─ check_security_achievement      │
    │ ├─ check_error_log_achievement     │
    │ ├─ check_ssl_achievement           │
    │ └─ check_feature_usage_achievements│
    │                                     │
    │ Then: Update wpshadow_              │
    │ last_achievement_check = time()     │
    └─────────────────────────────────────┘
                 │
        ┌────────┴────────┐
        │                 │
        └─────────────────┘
                 │
                 ▼
    ┌────────────────────────────────────┐
    │ Each check:                        │
    │ 1. Get current streak/score        │
    │ 2. Check if criteria met           │
    │ 3. Increment or award badge        │
    │ 4. Update option                   │
    └────────────────────────────────────┘
                 │
                 ▼
    ┌────────────────────────────────────┐
    │ update_stats()                     │
    │ Recalculate total badges &         │
    │ rarity distribution                │
    └────────────────────────────────────┘
```

## Feature Integration Checklist

For each WPShadow feature to integrate with gamification:

- [ ] Feature stores metrics in `wpshadow_{feature}_latest_score` option
- [ ] Score is numeric (0-100 scale recommended)
- [ ] Updated daily or after scan/check
- [ ] Gamification can call `get_option()` to retrieve
- [ ] Feature enables/disables status tracked in registry
- [ ] Feature follows naming convention in enabled features list
- [ ] Custom achievement logic added to gamification if needed

## Testing Gamification Integration

```php
// Test manually awarding badge
\WPShadow\CoreSupport\WPShadow_Gamification::award_badge( 'perfect_health_week' );

// Check earned badges
$badges = \WPShadow\CoreSupport\WPShadow_Gamification::get_badges();
echo count( $badges ); // Should be >= 1

// Check all available badges
$all = \WPShadow\CoreSupport\WPShadow_Gamification::get_all_badges();
echo count( $all ); // Should be 10

// Manually trigger checks
\WPShadow\CoreSupport\WPShadow_Gamification::check_achievements();

// View stats
$stats = get_option( 'wpshadow_gamification_stats', array() );
print_r( $stats );
```

## Real-World Example: a11y Integration

Here's how a11y-audit feature would integrate:

```php
// In a11y-audit feature after running audit:
class WPS_Feature_A11y_Audit {
    
    public function run_audit() {
        // ... audit code ...
        $score = $this->calculate_score(); // Returns 0-100
        
        // Store for gamification
        update_option( 'wpshadow_a11y_latest_score', $score );
        
        // (Optional) Direct badge award
        if ( $score >= 90 ) {
            \WPShadow\CoreSupport\WPShadow_Gamification::award_badge( 'a11y_champion' );
        }
    }
}
```

The gamification system will automatically:
1. Check this option daily
2. Award badge when score >= 90
3. Display badge in dashboard widget
4. Track in statistics

## Performance Notes

- All gamification checks run **once per day** (regardless of page views)
- Uses WordPress options API (cached by default)
- No custom database queries
- Minimal performance impact: < 1ms per check
- Scales to thousands of badges without degradation
