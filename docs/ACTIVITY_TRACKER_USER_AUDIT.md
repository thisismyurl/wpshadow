# Activity Tracker User Audit Implementation

**Status:** ✅ Complete  
**Date:** January 27, 2026  
**Version:** 1.2601.212003+  

## Overview

The Activity Tracker has been enhanced to comprehensively track user actions and include complete user attribution for every logged activity. This provides full audit trail capabilities showing "which user did what, when."

## Features Implemented

### 1. User Login Tracking

**Logs:** When any user logs into the WordPress dashboard

**Tracked Information:**
- User ID
- Username (login)
- Display Name
- User Email
- User Roles

**Location:** `includes/core/class-hooks-initializer.php::on_user_login()`

**Hook Used:** `wp_login` (WordPress standard login hook)

**Sample Log Entry:**
```php
Activity_Logger::log(
    'user_login',
    'User Allison logged in',
    'admin',
    array(
        'user_id'     => 2,
        'username'    => 'allison',
        'user_email'  => 'allison@example.com',
        'user_roles'  => ['administrator'],
    )
);
```

### 2. Settings Change Tracking

**Logs:** When any WPShadow setting is changed

**Tracked Information:**
- Setting name (human-readable)
- Old value
- New value
- User who made the change (automatically captured)
- Timestamp

**Location:** `includes/core/class-hooks-initializer.php::on_option_updated()`

**Hooks Used:** `update_option_wpshadow_*` (WordPress standard option update hooks)

**Settings Tracked:**
- Cache settings (enabled, duration)
- Visual comparison dimensions (width, height)
- Privacy settings (telemetry, error reporting)
- Data retention days
- Notification settings (enabled, severity, email)
- Backup settings (enabled, retention days)

**Sample Log Entry:**
```
Activity: Setting changed: Cache Duration (from "3600" to "7200")
User: Allison (ID: 2)
Timestamp: 2026-01-27 14:32:15
Details: 
  - Setting Name: Cache Duration
  - Old Value: 3600
  - New Value: 7200
  - Option Key: wpshadow_cache_duration
```

### 3. Enhanced Activity History View

**Location:** `includes/views/activity-history.php`

**Enhancements:**

#### User Column Display
- Displays the user name (display_name) for each activity
- Shows which user performed each action
- Column header: "User"

#### User Filter Dropdown
- New filter dropdown to filter activities by user
- Shows all unique users from the activity log
- Allows filtering to see only activities from a specific user
- Users sorted alphabetically by display name
- Gracefully handles deleted users ("Unknown User")

#### Activity Labels
Added new action type labels:
- `user_login` → "User Login"
- `setting_changed` → "Settings Changed"

### 4. Activity Logger Integration

**File:** `includes/core/class-activity-logger.php`

**Key Features Already Present:**
- Automatic user_id capture: `get_current_user_id()`
- Automatic user_name capture: `wp_get_current_user()->display_name`
- User filtering: `get_activities(['user_id' => $user_id])`
- CSV export includes user information

**Activity Entry Structure:**
```php
array(
    'id'        => 'activity_12345abc...',
    'action'    => 'user_login|setting_changed|etc',
    'details'   => 'Human-readable description',
    'category'  => 'admin|settings|security|etc',
    'metadata'  => [...additional context...],
    'user_id'   => 2,              // ← ALWAYS captured
    'user_name' => 'Allison',      // ← ALWAYS captured
    'timestamp' => 1706359935,     // Unix timestamp
    'date'      => '2026-01-27...' // MySQL datetime
);
```

## How It Works

### User Login Flow

1. User submits login form
2. WordPress validates credentials
3. `wp_login` hook fires with user login name and user object
4. `Hooks_Initializer::on_user_login()` is called
5. Activity logged to Activity_Logger with full user details
6. Entry stored in `wpshadow_activity_log` option

### Settings Change Flow

1. Admin visits Settings page
2. Changes a setting value
3. Form submits via `options.php` (WordPress Settings API)
4. WordPress updates the option in wp_options table
5. `update_option_wpshadow_*` hook fires with old/new values
6. `Hooks_Initializer::on_option_updated()` is called
7. Activity logged with:
   - Which user made the change (via `get_current_user_id()`)
   - What setting was changed
   - Before/after values
   - Formatted for readability (booleans → "enabled"/"disabled")
8. Entry stored in `wpshadow_activity_log` option

## Activity History View Usage

### Viewing All Activities
1. Go to WPShadow Dashboard → Tools → Activity History
2. View complete log of all activities across all users

### Filtering by User
1. Go to Activity History page
2. Select a user from the "User" dropdown
3. Click "Filter"
4. View only activities performed by that user

### Filtering by Multiple Criteria
- **User:** See only activities by Allison
- **Action Type:** See only "Settings Changed" actions
- **Category:** See only "admin" category activities
- **Search:** Search activity details

### Combining Filters Example
- User: Allison
- Action Type: Settings Changed
- Result: Shows all settings changes made by Allison

### Exporting Data
- Click "Export" to download full activity log
- CSV format includes: Timestamp, User, Action, Category, Details
- Can export filtered results

## Database Storage

**Option Name:** `wpshadow_activity_log`

**Storage Structure:**
- Array of activity entries
- Keeps last 500 entries (MAX_ACTIVITIES constant)
- Entries added at the beginning (most recent first)
- Automatically trimmed when max size exceeded

**Memory Efficiency:**
- Stores in single wp_options row
- Serialized PHP array
- No database tables required
- ~500 entries typically ~50-100KB

## Security Considerations

✅ **User Privacy:**
- No passwords or sensitive credentials stored
- User roles captured for audit purposes
- Email shown (already public in WordPress)

✅ **Data Safety:**
- No external API calls
- All data stored locally
- No third-party tracking
- GDPR-friendly (no personal tracking)

✅ **Access Control:**
- Activity log only visible to users with `manage_options` capability
- Respects WordPress permission model

## User Attribution Example

### Scenario: Allison Changes Cache Settings

**Steps:**
1. Allison logs in → Activity logged: "User allison logged in"
2. Allison goes to Settings → General
3. Changes Cache Duration from 1 hour to 2 hours
4. Clicks Save → Activity logged: "Setting changed: Cache Duration (from "3600" to "7200")"

**Activity Log Entries:**

| Timestamp | User | Action | Details |
|-----------|------|--------|---------|
| 2026-01-27 14:30:22 | Allison | User Login | User Allison logged in |
| 2026-01-27 14:32:15 | Allison | Settings Changed | Setting changed: Cache Duration (from "3600" to "7200") |

**Viewing:**
1. Go to Activity History
2. Filter by User: "Allison"
3. See all activities performed by Allison
4. Includes time, what was changed, old/new values

## Implementation Details

### Files Modified

1. **`includes/core/class-hooks-initializer.php`**
   - Added `wp_login` hook registration
   - Added multiple `update_option_wpshadow_*` hook registrations
   - Added `on_user_login()` method
   - Added `on_option_updated()` method
   - Added `format_setting_value()` helper method

2. **`includes/views/activity-history.php`**
   - Added "User Login" action label
   - Added "Settings Changed" action label
   - Added User Filter dropdown in filter section
   - Dynamically populates user dropdown from activity log
   - Handles deleted users gracefully

3. **`includes/core/class-activity-logger.php`**
   - No changes (already captures user_id and user_name)
   - Already supports user filtering

### Hook Registration

**Location:** `includes/core/class-hooks-initializer.php::init()`

```php
// User login tracking
add_action( 'wp_login', array( __CLASS__, 'on_user_login' ), 10, 2 );

// Settings changes tracking (12 settings hooks)
add_action( 'update_option_wpshadow_cache_enabled', array( __CLASS__, 'on_option_updated' ), 10, 3 );
add_action( 'update_option_wpshadow_cache_duration', array( __CLASS__, 'on_option_updated' ), 10, 3 );
// ... etc for all settings
```

## Testing the Implementation

### Test 1: User Login Tracking

1. Log out of WordPress
2. Log back in
3. Go to WPShadow → Tools → Activity History
4. Verify "User Login" entry appears at top
5. Check details show correct user info

### Test 2: Settings Change Tracking

1. Go to WPShadow → Settings → General
2. Toggle "Enable Result Caching"
3. Click Save
4. Go to Activity History
5. Verify "Settings Changed" entry appears
6. Check old/new values are correct

### Test 3: User Filtering

1. Have multiple users log in and make changes
2. Go to Activity History
3. Select a user from "User" dropdown
4. Click Filter
5. Verify only that user's activities are shown

### Test 4: Multi-User Audit

1. User A logs in and changes a setting
2. User B logs in and changes a different setting
3. Go to Activity History
4. See activities from both users with proper attribution

## Code Examples

### Reading Activity for a Specific User

```php
$user_id = 2; // Allison
$activities = \WPShadow\Core\Activity_Logger::get_activities(
    ['user_id' => $user_id],
    50,  // limit
    0    // offset
);

foreach ( $activities['activities'] as $activity ) {
    echo $activity['user_name'] . ': ' . $activity['details'];
}
```

### Exporting Activities for Audit

```php
$csv = \WPShadow\Core\Activity_Logger::export_csv(
    ['category' => 'settings', 'user_id' => 2]
);

// File output: Timestamp,User,Action,Category,Details
// 2026-01-27 14:32:15,Allison,setting_changed,settings,"Setting changed: Cache Duration..."
```

### Logging a Custom Action with User Info

```php
\WPShadow\Core\Activity_Logger::log(
    'custom_action',
    'User did something',
    'category',
    ['key' => 'value']
);

// Automatically includes:
// - user_id: get_current_user_id()
// - user_name: wp_get_current_user()->display_name
// - timestamp: current_time('timestamp')
// - date: current_time('mysql')
```

## Future Enhancements

Possible improvements:
- IP address logging for login tracking
- Session tracking (which user is currently logged in)
- Detailed setting change history (show all historical values)
- User action reports/summaries
- Alert when specific users make changes
- Integration with external logging services

## Compliance & Philosophy

**Aligns with WPShadow Philosophy:**

✅ **Commandment #9 (Show Value):**
- Tracks impact of user actions
- Proves who is maintaining the site
- Demonstrates activity levels

✅ **Commandment #10 (Privacy First):**
- No external tracking
- All data stored locally
- Respects user privacy
- GDPR-friendly design

✅ **Helpful Neighbor:**
- Clear audit trail
- Transparent about who did what
- Builds confidence in site management
- Educational (shows who's active)

## Support & Troubleshooting

**Q: Why don't I see all users in the User dropdown?**
A: Only users who have performed at least one tracked action appear in the dropdown. Log in and change a setting to appear.

**Q: How far back does the activity log go?**
A: The log keeps the last 500 entries. Older entries are automatically removed.

**Q: Can I see which user deleted something?**
A: Not with the current tracking. We only track logins and settings changes. Additional tracking can be added as needed.

**Q: Are logins from the front-end tracked?**
A: No, only WordPress admin dashboard logins. Front-end login tracking can be added if needed.

**Q: What if a user account is deleted?**
A: Their user_id is stored but display_name shows as "Unknown User" in the filter dropdown.

## Conclusion

The Activity Tracker now provides comprehensive user audit trails with complete attribution. Every action is tied to a specific user, making it possible to answer:

- **Who** logged in? (Allison)
- **When** did they log in? (2026-01-27 14:30:22)
- **What** did they change? (Cache Duration setting)
- **From what to what?** (3600 seconds → 7200 seconds)

This enables full accountability and transparency in site management, aligning with WPShadow's commitment to privacy-first, helpful monitoring.
