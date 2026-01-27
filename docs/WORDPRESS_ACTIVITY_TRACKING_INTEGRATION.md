# WordPress Activity Tracking Integration

## Overview

WPShadow now includes comprehensive activity tracking for all WordPress admin actions. Every user action is logged with full user identification, timestamp, and relevant metadata, providing administrators with a complete audit trail of who did what and when.

## Features

### User Authentication Tracking
- ✅ User logins (with IP address and user agent)
- ✅ User logouts
- ✅ Failed login attempts
- ✅ User role information captured

### User Account Management
- ✅ User creation (captures creator)
- ✅ User profile updates (tracks changes: email, display name, website, etc.)
- ✅ User deletion (captures who deleted)
- ✅ User role changes (tracks old roles → new role)

### Content Management
- ✅ Post/Page/CPT creation
- ✅ Post/Page/CPT updates (tracks Title, Content, Excerpt changes)
- ✅ Post/Page/CPT publication
- ✅ Post/Page/CPT status changes (draft → published, etc.)
- ✅ Post/Page/CPT moved to trash
- ✅ Post/Page/CPT restoration from trash
- ✅ Post/Page/CPT permanent deletion
- ✅ Excludes auto-saves and revisions (no noise)

### Comment Management
- ✅ Comment creation (tracks author, status)
- ✅ Comment updates
- ✅ Comment deletion
- ✅ Comment approval/unapproval
- ✅ Comment spam marking
- ✅ Comment unspamming

### Settings Management
- ✅ All WordPress option/setting changes
- ✅ Before/after values captured
- ✅ Excludes noisy options (transients, cron, cache)
- ✅ WPShadow settings changes tracked

### Plugin & Theme Management
- ✅ Plugin activation
- ✅ Plugin deactivation
- ✅ Plugin updates
- ✅ Theme switching

### Menu Management
- ✅ Menu creation
- ✅ Menu updates
- ✅ Menu deletion

## Data Structure

Every activity log entry contains:

```php
array(
    'id'               => 'activity_uuid',           // Unique identifier
    'user_id'          => 123,                       // WordPress user ID
    'user_name'        => 'Allison Smith',          // Display name
    'action'           => 'post_updated',           // Action type
    'details'          => 'Post updated: "...",     // Human-readable description
    'category'         => 'content_management',     // Category for filtering
    'timestamp'        => 1674123456,               // Unix timestamp
    'date'             => '2023-01-19 10:30:45',    // MySQL datetime
    'metadata'         => [                         // Action-specific data
        'post_id'      => 456,
        'post_type'    => 'post',
        'changes'      => ['Title', 'Content'],
        'old_value'    => 'previous',
        'new_value'    => 'current',
        // ... more depending on action
    ]
)
```

## Usage Examples

### Log a User Login

The system automatically captures this:

```
Log Entry:
- User: Allison (user_id: 2)
- Action: user_login
- Details: User logged in: Allison Smith (allison)
- Metadata:
  - IP: 192.168.1.100
  - User Agent: Mozilla/5.0...
  - Role: administrator
```

### Log a Settings Change

When Allison changes a cache setting:

```
Log Entry:
- User: Allison (user_id: 2)
- Action: option_updated
- Details: Setting updated: wpshadow_cache_duration - Changed by: Allison Smith
- Metadata:
  - option: wpshadow_cache_duration
  - old_value: "3600"
  - new_value: "7200"
```

### Log a Post Update

When Allison updates a post:

```
Log Entry:
- User: Allison (user_id: 2)
- Action: post_updated
- Details: Post updated: "New Feature" (ID: 789) - 2 changes: Title, Content
- Metadata:
  - post_id: 789
  - post_type: post
  - changes: ['Title', 'Content']
  - updated_by: Allison Smith
```

## Retrieving Activity Logs

The Activity_Logger class provides methods for retrieving and filtering activities:

```php
use WPShadow\Core\Activity_Logger;

// Get all activities
$result = Activity_Logger::get_activities();

// Get activities with filters
$result = Activity_Logger::get_activities(
    array(
        'user_id' => 2,                    // Filter by specific user
        'action'  => 'post_updated',       // Filter by action type
        'category' => 'content_management', // Filter by category
        'search'  => 'post title',         // Full-text search in details
        'date_from' => '2024-01-15',       // Date range
        'date_to'   => '2024-01-20',
    ),
    $limit = 50,   // Pagination limit
    $offset = 0    // Pagination offset
);

// Get recent activities
$recent = Activity_Logger::get_recent(20);

// Get action counts
$counts = Activity_Logger::get_action_counts();
// Result: ['post_updated' => 45, 'user_login' => 28, ...]

// Get category counts
$categories = Activity_Logger::get_category_counts();
// Result: ['content_management' => 60, 'user_authentication' => 30, ...]

// Export to CSV
$csv = Activity_Logger::export_csv(array('user_id' => 2));
```

## Available Action Types

### User Authentication
- `user_login` - User login
- `user_logout` - User logout
- `login_failed` - Failed login attempt

### User Management
- `user_created` - New user account created
- `user_updated` - User profile/settings updated
- `user_deleted` - User account deleted
- `user_role_changed` - User role changed

### Content Management
- `post_updated` - Post/page/CPT updated
- `post_published` - Post/page/CPT published
- `post_trashed` - Post/page/CPT moved to trash
- `post_restored` - Post/page/CPT restored from trash
- `post_deleted` - Post/page/CPT permanently deleted
- `post_status_changed` - Post/page/CPT status changed

### Comment Management
- `comment_created` - Comment created
- `comment_updated` - Comment edited
- `comment_deleted` - Comment deleted
- `comment_status_changed` - Comment status changed
- `comment_spammed` - Comment marked as spam
- `comment_unspammed` - Comment unmarked as spam

### Settings Management
- `option_updated` - WordPress setting/option changed

### Plugin Management
- `plugin_activated` - Plugin activated
- `plugin_deactivated` - Plugin deactivated
- `plugin_updated` - Plugin updated

### Theme Management
- `theme_switched` - Theme changed

### Site Management
- `menu_created` - Menu created
- `menu_updated` - Menu updated
- `menu_deleted` - Menu deleted

## Categories for Filtering

- `user_authentication` - Logins, logouts, failed attempts
- `user_management` - User CRUD operations
- `content_management` - Post/page/CPT operations
- `comment_management` - Comment operations
- `settings_management` - Option/setting changes
- `plugin_management` - Plugin activation/deactivation
- `theme_management` - Theme switches
- `site_management` - Menus and other site-wide changes

## Performance Considerations

### Efficiency Features
1. **Automatic Noise Filtering**: Auto-saves, revisions, and transient options are excluded
2. **Storage Limit**: Keeps last 500 entries (configurable via `MAX_ACTIVITIES` constant)
3. **Selective Logging**: Only meaningful changes are logged (duplicate values skipped)
4. **Efficient Metadata**: Stores only relevant data for each action type
5. **No External Calls**: Everything stored locally in WordPress options

### Activity Log Pruning

```php
use WPShadow\Core\Activity_Logger;

// Keep only last 90 days of activities
$removed = Activity_Logger::prune(90);

// Delete specific old entries
$removed = Activity_Logger::delete_old_entries('30 days ago');
```

## Integration with Activity Tracker UI

The WordPress_Hooks_Tracker is automatically initialized on `plugins_loaded` hook, so no additional setup is required. Activities are immediately available for viewing in the WPShadow Dashboard → Activity Log.

## Filtering Examples

### Show all actions by specific user
```php
$activities = Activity_Logger::get_activities(array('user_id' => 2));
```

### Show all post updates
```php
$activities = Activity_Logger::get_activities(array('action' => 'post_updated'));
```

### Show content management activities
```php
$activities = Activity_Logger::get_activities(array('category' => 'content_management'));
```

### Show login activity
```php
$activities = Activity_Logger::get_activities(array('action' => 'user_login'));
```

### Search for specific changes
```php
$activities = Activity_Logger::get_activities(array('search' => 'Allison'));
```

### Show activities from last 7 days
```php
$activities = Activity_Logger::get_activities(array(
    'date_from' => '7 days ago',
    'date_to'   => 'now'
));
```

## What Gets Logged (Complete Checklist)

✅ Every time a user logs in
✅ Every time a user logs out
✅ Failed login attempts
✅ New user accounts created (and who created them)
✅ User profile updates (what changed)
✅ User deletion (and who deleted them)
✅ User role changes (who changed them and what roles changed)
✅ Post/page/custom post type creation
✅ Post/page/CPT updates (what changed)
✅ Post/page/CPT publication
✅ Post/page/CPT status changes (draft→published, etc)
✅ Post/page/CPT moved to trash
✅ Post/page/CPT restored from trash
✅ Post/page/CPT permanent deletion
✅ Comments created (by whom)
✅ Comments edited
✅ Comments deleted
✅ Comments approved/unapproved
✅ Comments marked as spam
✅ Comments unmarked as spam
✅ All WordPress settings changes
✅ All WPShadow settings changes
✅ Plugin activation
✅ Plugin deactivation
✅ Plugin updates
✅ Theme switches
✅ Menu creation
✅ Menu updates
✅ Menu deletion

## What Doesn't Get Logged (By Design)

❌ Auto-saves (noise reduction)
❌ Post revisions (noise reduction)
❌ Transient options (too frequent)
❌ Cache updates
❌ Cron job metadata
❌ Database schema versions
❌ Update check results
❌ Duplicate updates (same old/new value)

## Admins Can Review

The activity log provides complete transparency:

- **Who**: User name and ID
- **What**: Specific action taken
- **When**: Exact timestamp
- **Where**: IP address (for logins)
- **Why**: What changed (before/after values)

This enables administrators to:
- ✅ Audit who made changes
- ✅ Investigate content modifications
- ✅ Track user account management
- ✅ Monitor security events (logins, failed attempts)
- ✅ Verify comment management
- ✅ Review settings changes
- ✅ Understand site activity over time
- ✅ Export activity reports for compliance

## Under the Hood

### Hooks Used

The WordPress_Hooks_Tracker registers hooks for:

1. **User Authentication**
   - `wp_login` - Successful login
   - `wp_logout` - User logout
   - `wp_login_failed` - Failed login

2. **User Operations**
   - `user_register` - New user
   - `profile_update` - Profile changes
   - `delete_user` - User deletion
   - `set_user_role` - Role changes

3. **Content Operations**
   - `post_updated` - Post/page/CPT updates
   - `publish_post` - Publishing
   - `wp_trash_post` - Move to trash
   - `restore_post` - Restore from trash
   - `delete_post` - Permanent deletion
   - `transition_post_status` - Status changes

4. **Comment Operations**
   - `comment_post` - Comment creation
   - `edit_comment` - Comment editing
   - `delete_comment` - Comment deletion
   - `wp_set_comment_status` - Status changes
   - `spam_comment` / `unspam_comment` - Spam marking

5. **Settings**
   - `update_option` - Option/setting changes

6. **Plugins & Themes**
   - `activated_plugin` - Plugin activation
   - `deactivated_plugin` - Plugin deactivation
   - `upgrader_process_complete` - Plugin/theme updates
   - `switch_theme` - Theme switching

7. **Menus**
   - `wp_create_nav_menu` - Menu creation
   - `wp_update_nav_menu` - Menu updates
   - `wp_delete_nav_menu` - Menu deletion

## Storage

All activities are stored in the WordPress options table:
- Option name: `wpshadow_activity_log`
- Storage format: Serialized PHP array
- Limit: 500 most recent entries (oldest pruned automatically)
- No external database tables needed

## Version Information

- **Added**: WPShadow 1.2601.212003
- **Class**: `WPShadow\Monitoring\WordPress_Hooks_Tracker`
- **Requires**: WordPress 5.0+, PHP 8.1+
