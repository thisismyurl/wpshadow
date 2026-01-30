# WPShadow Gamification: Quick Reference Guide

**For:** Developers integrating with the earn/spend system  
**Last Updated:** 1.2604.0400

---

## TL;DR - The 30-Second Version

**Users earn points by:**
- ✅ Enabling features (Guardian, Backups, Cloud) - **Auto-awards, zero clicks**
- ✅ Sharing on social media - **75 pts each, honor system**
- ✅ Reviewing on WordPress.org - **200 pts, needs 7 days + 3 treatments**
- ✅ Reading KB articles - **25 pts each, hooks built-in**
- ✅ Completing training - **50 pts per video, hooks built-in**

**Users spend points on:**
- Guardian AI tokens (100 credits = 1000 pts)
- Vault storage (5GB = 2000 pts)
- Pro subscription (1 month = 3000 pts)

---

## Key Classes & Methods

### Earn_Actions (Claiming Points)

```php
use WPShadow\Gamification\Earn_Actions;

// Get all available earn actions
$actions = Earn_Actions::get_actions();
// Returns: 8 actions with points, requirements, categories

// Check if user is eligible and status
$status = Earn_Actions::get_user_status($user_id);
// Returns: array of each action with 'claimed', 'eligible', 'completed' status

// Claim an action (manual - reviews, shares)
$result = Earn_Actions::claim($user_id, 'share_x');
// Returns: ['success' => true/false, 'points' => 75, 'message' => '...']

// Check if already claimed
$is_claimed = Earn_Actions::is_claimed($user_id, 'review_wordpress');
// Returns: true/false

// Check if auto-award is completed
$is_done = Earn_Actions::is_auto_completed(['setting' => 'wpshadow_guardian_enabled']);
// Returns: true/false

// Get eligibility details
$eligibility = Earn_Actions::get_eligibility($user_id, $action);
// Returns: ['eligible' => true/false, 'message' => 'Requires 7 days active']
```

### Reward_System (Redeeming Points)

```php
use WPShadow\Gamification\Reward_System;

// Get all rewards
$rewards = Reward_System::get_rewards();
// Returns: array of rewards with title, cost, type

// Get single reward
$reward = Reward_System::get_reward('guardian_credits_100');
// Returns: ['id' => '...', 'title' => '100 Guardian Credits', 'cost' => 1000, 'type' => 'guardian_credits']

// Redeem points for reward
$result = Reward_System::redeem($user_id, 'guardian_credits_100');
// Returns: ['success' => true/false, 'message' => '...', 'reward' => [...]]

// Check if user has sufficient points
$balance = Points_System::get_balance($user_id);
$can_redeem = $balance >= $reward['cost'];
```

### Points_System (Tracking Points)

```php
use WPShadow\Gamification\Points_System;

// Get current balance
$balance = Points_System::get_balance($user_id);
// Returns: int (total points available)

// Award points
Points_System::award_points($user_id, 150, 'guardian_enabled', [
    'setting' => 'wpshadow_guardian_enabled',
    'feature' => 'Guardian Monitoring'
]);

// Spend points
Points_System::spend_points($user_id, 1000, 'redeem_reward', [
    'reward_id' => 'guardian_credits_100',
    'delivery' => 'sent'
]);

// Get transaction history
$history = Points_System::get_history($user_id, $limit = 50);
// Returns: array of transactions with amounts, reasons, dates

// Count specific actions
$share_count = Points_System::get_action_count($user_id, 'social_share');
// Returns: 3 (user shared 3 times)

$x_shares = Points_System::get_action_count($user_id, 'social_share', 'network', 'share_x');
// Returns: 2 (user shared on X twice)
```

### Achievement_Registry (Unlocking Achievements)

```php
use WPShadow\Gamification\Achievement_Registry;

// Register an achievement (auto-called on startup)
Achievement_Registry::register('social_supporter', [
    'title' => 'Social Supporter',
    'description' => 'Share WPShadow 3 times',
    'points' => 150,
    'icon' => '📣',
    'achievement_id' => 'share_x', // Links to earn action
]);

// Get achievement
$achievement = Achievement_Registry::get('social_supporter');
// Returns: array with title, description, points, icon

// Get all achievements
$all = Achievement_Registry::get_all();
// Returns: array of all 20+ achievements

// Check if user has achievement
$has_it = Achievement_Registry::user_has($user_id, 'social_supporter');
// Returns: true/false

// Unlock achievement
Achievement_Registry::unlock($user_id, 'social_supporter');
// Awards points + creates notification
```

---

## AJAX Endpoints

### Claim Earn Action

**Endpoint:** `wp_ajax_wpshadow_claim_earn_action`  
**Method:** POST  
**Nonce:** `wpshadow_gamification`

**Request:**
```javascript
$.post(ajaxurl, {
    action: 'wpshadow_claim_earn_action',
    nonce: wpShadowGamification.nonce,
    action_id: 'share_x'
}, function(response) {
    if (response.success) {
        console.log('Earned', response.data.points, 'points');
    } else {
        console.error(response.data.message);
    }
});
```

**Response Success:**
```json
{
    "success": true,
    "data": {
        "message": "You earned 75 points!",
        "points": 75
    }
}
```

**Response Error:**
```json
{
    "success": false,
    "data": {
        "message": "You must wait 7 days before claiming this reward"
    }
}
```

### Redeem Reward

**Endpoint:** `wp_ajax_wpshadow_redeem_reward`  
**Method:** POST  
**Nonce:** `wpshadow_gamification`

**Request:**
```javascript
$.post(ajaxurl, {
    action: 'wpshadow_redeem_reward',
    nonce: wpShadowGamification.nonce,
    reward_id: 'guardian_credits_100'
}, function(response) {
    if (response.success) {
        console.log('Redeemed reward:', response.data.reward.title);
    } else {
        console.error(response.data.message);
    }
});
```

**Response Success:**
```json
{
    "success": true,
    "data": {
        "message": "You received 100 Guardian Credits!",
        "reward": {
            "id": "guardian_credits_100",
            "title": "100 Guardian Credits",
            "cost": 1000
        }
    }
}
```

---

## Hooks & Filters

### Actions (Fire These to Award Points)

```php
// When user enables Guardian
do_action('wpshadow_setting_updated', 'wpshadow_guardian_enabled', false, true);
// Auto-awards 150 points + guardian_enabled achievement

// When user reads KB article
do_action('wpshadow_kb_article_viewed', $user_id, $article_id);
// Auto-awards 25 points (if hook is wired)

// When user completes training
do_action('wpshadow_training_video_completed', $user_id, $video_id);
// Auto-awards 50 points (if hook is wired)
```

### Filters (Modify Behavior)

```php
// Change points required for a reward
$cost = apply_filters('wpshadow_reward_cost', 1000, 'guardian_credits_100');

// Change earn action definitions
$actions = apply_filters('wpshadow_earn_actions', $actions);

// Change eligibility requirements
$eligible = apply_filters('wpshadow_earn_action_eligible', $eligible, $user_id, $action);
```

---

## Common Integration Patterns

### Pattern 1: Award Points After Custom Action

```php
// In your custom code, after user does something:
\WPShadow\Gamification\Points_System::award_points(
    $user_id,
    100,
    'custom_action',
    ['action_name' => 'imported_data', 'item_count' => 42]
);

// Creates achievement trigger:
do_action('wpshadow_custom_action_completed', $user_id);
```

### Pattern 2: Gate Feature Behind Point Requirement

```php
$user_id = get_current_user_id();
$balance = \WPShadow\Gamification\Points_System::get_balance($user_id);
$required = 5000;

if ($balance < $required) {
    wp_die(sprintf(
        esc_html__('You need %d more points to unlock this feature', 'wpshadow'),
        $required - $balance
    ));
}

// User has enough points, continue...
```

### Pattern 3: Show User How Close to Achievement

```php
$user_id = get_current_user_id();
$share_count = \WPShadow\Gamification\Points_System::get_action_count(
    $user_id,
    'social_share'
);

$message = sprintf(
    esc_html__('Share %d more times to unlock "Social Supporter" (%d points bonus)', 'wpshadow'),
    3 - $share_count,
    150
);
echo wp_kses_post($message);
```

### Pattern 4: Bulk Award (For Migrations)

```php
// When migrating users or awarding legacy points:
$users = get_users(['fields' => 'ID']);

foreach ($users as $user_id) {
    if (!wp_cache_get("awarded_legacy_$user_id", 'wpshadow')) {
        \WPShadow\Gamification\Points_System::award_points(
            $user_id,
            500,
            'legacy_migration',
            ['source' => 'v1_users']
        );
        
        wp_cache_set("awarded_legacy_$user_id", true, 'wpshadow', 86400);
    }
}
```

---

## Database Tables

### `wp_postmeta` (Points Transactions)

```
post_id: User ID
meta_key: wpshadow_points_transaction_{timestamp}
meta_value: {
    'type': 'earn'|'spend',
    'amount': 150,
    'reason': 'guardian_enabled',
    'meta': {
        'setting': 'wpshadow_guardian_enabled',
        'feature': 'Guardian Monitoring'
    },
    'timestamp': 1234567890
}
```

### `wp_usermeta` (Earn Claims)

```
user_id: User ID
meta_key: wpshadow_earn_claims
meta_value: {
    'share_x': 1234567890,           // Timestamp claimed
    'share_linkedin': 1234567890,
    'review_wordpress': null,         // Not yet claimed
    'guardian_enabled': 1234567890
}
```

### `wp_usermeta` (Points Balance)

```
user_id: User ID
meta_key: wpshadow_points_balance
meta_value: 3450  // Total points
```

---

## Debugging

### Check User Points Balance

```php
$user_id = get_current_user_id();
$balance = \WPShadow\Gamification\Points_System::get_balance($user_id);
echo "User {$user_id} has {$balance} points";

// View transaction history
$history = \WPShadow\Gamification\Points_System::get_history($user_id, 100);
foreach ($history as $tx) {
    echo "{$tx['date']}: {$tx['reason']} = {$tx['amount']} pts\n";
}
```

### Check Earn Action Status

```php
$user_id = get_current_user_id();
$status = \WPShadow\Gamification\Earn_Actions::get_user_status($user_id);

foreach ($status as $action_id => $state) {
    echo "{$action_id}: ";
    echo $state['claimed'] ? "CLAIMED" : "";
    echo $state['eligible'] ? "ELIGIBLE" : "INELIGIBLE";
    echo $state['completed'] ? "COMPLETED" : "";
    echo "\n";
}
```

### Verify Achievement Unlocked

```php
$user_id = get_current_user_id();
if (\WPShadow\Gamification\Achievement_Registry::user_has($user_id, 'social_supporter')) {
    echo "User has Social Supporter achievement";
} else {
    echo "User needs to share 3x to unlock";
}
```

---

## Performance Notes

**✅ Optimized:**
- Points balance cached in user meta (1 query to get)
- Transaction history limited to recent 50 (pagination)
- Action counting uses filtered meta queries
- Eligibility checks use timestamps (no full scan)

**⚠️ Watch For:**
- Avoid bulk point awards to all users (background task recommended)
- Don't call get_history() repeatedly in a loop (use cache)
- Achievement unlocks trigger emails (async recommended)

---

## Security Checklist

- [x] All AJAX endpoints verify nonce
- [x] All AJAX endpoints check capability
- [x] All inputs sanitized (action_id = sanitize_key)
- [x] All output escaped
- [x] Transaction history append-only (no modifications)
- [x] Duplicate claims prevented via is_claimed()
- [x] Eligibility verified before awarding
- [x] Points balance verified before redeeming
- [x] All database writes use $wpdb->prepare()
- [x] No raw user input in SQL queries

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.2604.0400 | Jan 26, 2026 | Initial launch: 8 earn actions, AJAX handlers, auto-awards |
| Future | TBD | Social verification, KB/training wiring, email notifications |

---

**Need help? Check the integration test at `/tests/gamification-integration-test.php` for working examples.**
