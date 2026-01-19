# WPShadow Gamification System

## Overview

The gamification system is now a **core component** of WPShadow (not an optional feature). It motivates site administrators by awarding badges for significant achievements and displays progress through a persistent dashboard widget.

**Location:** `/includes/core/class-wps-gamification.php`

## Badges System

### 10 Achievement Badges

#### Tier 1: Common (Early Unlocks)
1. **First Step** 👣
   - Unlock: Enable your first WPShadow feature
   - Color: #4CAF50 (Green)

#### Tier 2: Uncommon (Regular Achievements)
2. **Feature Explorer** 🔭
   - Unlock: Enable 5 different WPShadow features
   - Color: #2196F3 (Blue)

3. **Log Keeper** 📋
   - Unlock: Maintain clean error logs for 7 consecutive days
   - Color: #00bcd4 (Cyan)

#### Tier 3: Rare (Major Accomplishments)
4. **Perfect Health Guardian** 🏥
   - Unlock: Maintain 100% site health score for 7 consecutive days
   - Color: #46b450 (Health Green)
   - Criteria: Zero critical issues AND zero recommended issues

5. **Accessibility Champion** ♿
   - Unlock: Site passes accessibility audit with 90%+ score
   - Color: #0073aa (WordPress Blue)
   - Criteria: Integrated with a11y-audit feature

6. **Performance Optimizer** ⚡
   - Unlock: Achieve excellent performance score (90%+) across all pages
   - Color: #ffb81c (Warning Yellow)
   - Criteria: Integrated with plugin-audit feature

7. **HTTPS Champion** 🔐
   - Unlock: Proper SSL/HTTPS configuration detected
   - Color: #f44336 (Red)
   - Criteria: Site running on HTTPS

8. **Feature Master** 🎓
   - Unlock: Enable 10 different WPShadow features
   - Color: #9C27B0 (Purple)

9. **Cleanup Champion** 🧹
   - Unlock: Fixed 50+ issues across your site
   - Color: #9b51e0 (Lavender)
   - Criteria: Requires integration with issue tracking

#### Tier 4: Epic (Ultimate Achievements)
10. **Security Hardened** 🔒
    - Unlock: Enabled all recommended security features
    - Color: #d63638 (Red)
    - Criteria:
      - iframe-busting enabled
      - hotlink-protection enabled
      - external-fonts-disabler enabled

## Achievement Tracking

### Daily Achievement Checks

The system runs achievement checks once per day via the `init` hook. Each check looks for:

- **Site Health**: Tracks consecutive perfect health days (resets on any issues)
- **Error Logs**: Tracks consecutive days with zero errors
- **Feature Usage**: Counts enabled features for milestone badges
- **Security Status**: Checks if recommended security features are active
- **Performance**: Monitors performance scores from audits
- **Accessibility**: Monitors a11y compliance scores
- **SSL/HTTPS**: Verifies HTTPS is properly configured

### Data Storage

- **Earned Badges:** `wpshadow_earned_badges` (option)
  ```php
  [
    'perfect_health_week' => [
      'earned_at' => '2024-01-15 14:30:00',
      'timestamp' => 1705332600
    ],
    // ... more badges
  ]
  ```

- **Achievement Streaks:** Multiple options track consecutive day counts
  - `wpshadow_perfect_health_days`
  - `wpshadow_clean_log_days`
  - `wpshadow_last_achievement_check`

- **Gamification Stats:** `wpshadow_gamification_stats` (option)
  ```php
  [
    'total_badges' => 5,
    'common_badges' => 1,
    'uncommon_badges' => 2,
    'rare_badges' => 2,
    'epic_badges' => 0,
    'legendary_badges' => 0
  ]
  ```

## Dashboard Widget

### Features

- **Badges Earned Counter:** Shows total badges earned vs. available
- **Visual Badge Display:** Shows earned badges with icons, titles, and dates
- **Interactive Tooltips:** Hover over badges to see full descriptions
- **Encouragement Messages:** Displays tips for earning more badges
- **Recent Badges:** Shows last 5 badges in header

### Placement

- Primary: WordPress Dashboard (`wp_dashboard_setup` hook)
- Secondary: WPShadow admin pages via `wpshadow_admin_page_header` action

### Styling

- Responsive grid layout (auto-fill 80px cards)
- Gradient header (purple background)
- Stats boxes with progress
- Emoji icons for visual clarity

## Integration Points

### With Core Diagnostics
```php
// Gamification checks site health from core-diagnostics
$status = get_site_health_count();
if ( $status['recommended'] === 0 && $status['critical'] === 0 ) {
    // Increment perfect health streak
}
```

### With a11y Audit
```php
// Gets accessibility score from a11y-audit feature
$a11y_score = get_option( 'wpshadow_a11y_latest_score', 0 );
if ( $a11y_score >= 90 ) {
    self::award_badge( 'a11y_champion' );
}
```

### With Plugin Audit
```php
// Gets performance score from plugin-audit feature
$performance_score = get_option( 'wpshadow_performance_latest_score', 0 );
if ( $performance_score >= 90 ) {
    self::award_badge( 'performance_optimizer' );
}
```

### With Feature Registry
```php
// Tracks enabled features for milestones
$enabled_features = get_option( 'wpshadow_enabled_features', array() );
$count = count( $enabled_features );
// Awards first_feature, five_features, ten_features badges
```

## API Methods

### Public Static Methods

#### `init()`
Initializes the gamification system.
```php
WPShadow_Gamification::init();
```

#### `award_badge(string $badge_id): bool`
Awards a badge to the site (only if not already earned).
```php
$awarded = WPShadow_Gamification::award_badge( 'perfect_health_week' );
```

#### `get_badges(): array`
Returns all earned badges.
```php
$earned = WPShadow_Gamification::get_badges();
```

#### `get_badge(string $badge_id): ?array`
Returns a specific badge definition.
```php
$badge = WPShadow_Gamification::get_badge( 'perfect_health_week' );
// Returns: [
//   'title' => 'Perfect Health Guardian',
//   'description' => '...',
//   'icon' => '🏥',
//   'color' => '#46b450',
//   'rarity' => 'rare'
// ]
```

#### `get_all_badges(): array`
Returns all available badge definitions.
```php
$all_badges = WPShadow_Gamification::get_all_badges();
```

#### `check_achievements()`
Manually trigger achievement checks (normally runs daily).
```php
WPShadow_Gamification::check_achievements();
```

## Extending Gamification

### Adding New Badges

To add new badges, modify the `BADGES` constant:

```php
private const BADGES = array(
    'my_new_badge' => array(
        'title'       => 'Badge Title',
        'description' => 'What unlocks this badge',
        'icon'        => '🎯',
        'color'       => '#2271b1',
        'rarity'      => 'rare',  // common, uncommon, rare, epic, legendary
    ),
);
```

### Adding New Achievement Checks

Add new private methods following the pattern:

```php
private static function check_my_achievement(): void {
    // Check some condition
    if ( condition_met() ) {
        self::award_badge( 'my_new_badge' );
    }
}
```

Then call it from `check_achievements()`:

```php
public static function check_achievements(): void {
    // ... existing checks ...
    self::check_my_achievement();
}
```

### Integration from Features

Features can trigger badge awards by calling:

```php
\WPShadow\CoreSupport\WPShadow_Gamification::award_badge( 'badge_id' );
```

## Future Enhancement Ideas

1. **Badge Rarity Tiers:** Display rarity to encourage collection
2. **Achievement Streaks:** Show current streaks with progress bars
3. **Leaderboards:** Compare stats across multisite installations
4. **Badge Notifications:** Email/admin notice when badges earned
5. **Anniversary Badges:** Special badges on plugin anniversary
6. **Seasonal Badges:** Limited-time achievements
7. **Community Badges:** Share achievements on WordPress.org
8. **Custom Goals:** Site admins can set custom achievement targets

## Hook Integration

The gamification system uses WordPress standard hooks:

- `init` - Daily achievement checks
- `wp_dashboard_setup` - Widget registration
- `admin_enqueue_scripts` - Asset loading
- `wpshadow_admin_page_header` - Header display on WPShadow pages

## Performance Considerations

- Achievement checks run **once per day** (checked on first `init` of each day)
- Data stored in options (WordPress standard storage)
- No database queries beyond standard options API
- Dashboard widget uses minimal inline CSS (no external file load)
- Scales well: No N+1 queries or loops over large datasets

## Migration from Feature

Previously, achievement-badges was:
- Location: `/includes/_features_disabled/class-wps-achievement-badges.php`
- Type: Optional feature (users could disable)
- Status: Inconsistently available

Now it is:
- Location: `/includes/core/class-wps-gamification.php`
- Type: Always-on core component
- Status: Guaranteed available, initialized automatically
- Visibility: Dashboard widget prominent on all admin pages

## Testing Checklist

- [ ] Dashboard widget displays on WordPress dashboard
- [ ] Badges display correctly with icons and dates
- [ ] Achievement checks run daily
- [ ] Perfect health badge awarded after 7-day streak
- [ ] a11y badge awarded on 90%+ score
- [ ] Performance badge awarded on 90%+ score
- [ ] Security badge awarded when all features enabled
- [ ] Badge counts update in stats
- [ ] Widget shows on all WPShadow admin pages
- [ ] Gamification header appears with recent badges

## Configuration

No configuration needed - gamification works out of the box. All achievements and badges are automatically:
- Tracked
- Evaluated daily
- Awarded when criteria met
- Displayed on dashboard

Users cannot disable individual achievements or the gamification system entirely.
