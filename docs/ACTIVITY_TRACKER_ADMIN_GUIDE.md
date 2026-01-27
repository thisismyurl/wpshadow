# Activity Tracker Admin Guide

## Accessing the Activity Log

### Location
- **Menu**: WPShadow → Dashboard → Activity Log
- **Direct URL**: `/wp-admin/admin.php?page=wpshadow-dashboard#activity`

### Features

The Activity Log provides a complete audit trail of everything happening on your WordPress site within the admin area.

## What You Can See

### Log Entry Details

Each log entry shows:

1. **Timestamp** - When the action occurred
2. **User** - Who performed the action
3. **Action** - What was done
4. **Category** - Type of activity (Content, User Management, Settings, etc.)
5. **Details** - Human-readable description with specific information

### Example Entries

**User Login:**
```
Allison Smith logged in
Today at 10:45 AM
IP: 192.168.1.100
User Agent: Mozilla/5.0...
```

**Post Updated:**
```
Post updated: "New Feature" (ID: 789) - 2 changes: Title, Content
Today at 2:30 PM
By: Allison Smith
Changes: Title, Content updated
```

**User Created:**
```
New user created: John Doe (johndoe) by Allison Smith
Today at 1:15 PM
Role: Editor
```

**Settings Changed:**
```
Setting updated: wpshadow_cache_duration - Changed by: Allison Smith
Today at 11:00 AM
Changed from: 3600 to 7200
```

## Filtering Activities

### Filter by User

Click on any user name to see all activities by that user.

### Filter by Category

Categories available:
- **User Authentication** - Logins, logouts, failed attempts
- **User Management** - User account creation, updates, deletion
- **Content Management** - Posts, pages, custom post types
- **Comment Management** - Comments, approvals, spam
- **Settings Management** - WordPress settings and options
- **Plugin Management** - Plugin activation/deactivation
- **Theme Management** - Theme switches
- **Site Management** - Menus and site-wide changes

### Search

Use the search box to find specific activities:
- Search by user name
- Search by post/page title
- Search by action type
- Search by any text in the details

### Date Range

Filter activities by date:
- Last 24 hours
- Last 7 days
- Last 30 days
- Custom date range

## Using Activity Log for Auditing

### Track User Actions

See exactly what each user has done:
- Posts they created/updated
- Comments they approved
- Settings they changed
- When they logged in/out

### Investigate Content Changes

Find out who changed what:
- Click on a post title to see all changes to that post
- See the user who made each change and when
- View the specific fields that were modified

### Monitor Security

Keep an eye on user account management:
- New user registrations
- Role changes
- User deletions
- Failed login attempts

### Verify Plugin/Theme Changes

See who activated/deactivated plugins or switched themes:
- Track plugin activation history
- See who made theme changes
- Monitor plugin updates

## Exporting Activity Reports

### Export to CSV

You can export activity logs to CSV for reporting or compliance:

```
Timestamp, User, Action, Category, Details
2024-01-19 10:45:00, Allison Smith, user_login, user_authentication, User logged in
2024-01-19 10:50:30, Allison Smith, post_updated, content_management, Post updated
```

### Uses

- Compliance reporting (SOC 2, HIPAA, etc.)
- Content change audits
- User activity analysis
- Historical record keeping

## Common Scenarios

### Finding Who Changed a Post

1. Go to Activity Log
2. Filter by Category: "Content Management"
3. Search for the post title
4. See all changes with user and timestamp
5. Click on a change to see details

### Tracking User Account Management

1. Go to Activity Log
2. Filter by Category: "User Management"
3. See new users created, updates, deletions
4. See who performed each action

### Reviewing Login Activity

1. Go to Activity Log
2. Filter by Category: "User Authentication"
3. See login times and failed attempts
4. See IP addresses for security review

### Finding Settings Changes

1. Go to Activity Log
2. Filter by Category: "Settings Management"
3. Search for the setting name
4. See before/after values
5. See who changed it and when

### Monitoring Comments

1. Go to Activity Log
2. Filter by Category: "Comment Management"
3. See all comment approvals, deletions, spam marking
4. See which user performed each action

## Admin Responsibilities

### Regular Reviews

- Review activity logs weekly or monthly
- Look for unusual activity patterns
- Check for unauthorized changes

### Security Monitoring

- Monitor failed login attempts
- Review user account creation
- Watch for role changes
- Check plugin activation/deactivation

### Content Auditing

- Verify who has been editing content
- Check post publication history
- Review comment moderation actions

### Compliance

- Keep activity logs for compliance purposes
- Export logs when needed for audits
- Document activity for regulatory requirements

## Retention Policy

The activity log automatically maintains:
- **Last 500 entries** - Most recent activities are kept
- **Configurable retention** - By default keeps 90 days of activity
- **Automatic pruning** - Old entries removed automatically

### Exporting for Long-term Storage

If you need to keep activity logs longer:
1. Export to CSV regularly
2. Store exports in your backup system
3. Document the retention schedule

## Privacy & Security

### Who Can See Activity Logs

Only users with `manage_options` capability (administrators):
- Can view the activity log
- Can filter and search
- Can export activities

### What's Logged

Only admin actions are logged:
- No front-end user activity
- No customer purchases (unless in admin)
- Only WordPress admin area actions

### IP Addresses

IP addresses are logged for:
- User logins (security tracking)
- Failed login attempts (security monitoring)

No other admin actions collect IP data.

## Troubleshooting

### Activity log is empty

- Log has limit of 500 entries, oldest are pruned
- Check if you're viewing the correct date range
- Try clearing filters

### Not seeing a specific action

- Some actions are excluded to reduce noise:
  - Auto-saves
  - Post revisions
  - Transient options
  - Cache updates
- The action might have occurred outside the date range

### Need more details

- Click on a log entry to see all metadata
- Export to CSV for detailed analysis
- Check the WPShadow system settings for logging level

## Settings for Activity Log

### In WPShadow Settings → Advanced

- **Enable Activity Logging**: Turn activity tracking on/off
- **Log Retention Days**: How many days to keep activities (default: 90)
- **Export Format**: Choose CSV or JSON for exports

## Tips & Best Practices

✅ **DO:**
- Review activity logs regularly
- Export logs for compliance records
- Use filters to find specific activities
- Share exported logs with auditors as needed
- Document unusual activity

❌ **DON'T:**
- Ignore repeated failed login attempts (security risk)
- Assume all changes are documented elsewhere
- Delete activity logs before exporting if needed for compliance
- Give admin access to users who don't need it

## Integration with Other Tools

The activity log works seamlessly with:
- **WordPress Site Health** - Includes security checks
- **WPShadow Dashboard** - Shows recent activity widget
- **Diagnostic Tools** - Context for finding issues
- **Treatment Tools** - See who applied what fixes

---

**Need Help?**
- Visit WPShadow Knowledge Base: [wpshadow.com/kb/activity-logging](https://wpshadow.com/kb/activity-logging)
- Contact WPShadow Support for questions
