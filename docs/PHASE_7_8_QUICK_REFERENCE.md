# Phase 7-8 Quick Reference Guide

**Last Updated:** January 21, 2026

---

## Cloud Registration Usage

### Check if site is registered:

```php
use WPShadow\Cloud\Registration_Manager;

if ( Registration_Manager::is_registered() ) {
    // Site has cloud account
    $status = Registration_Manager::get_registration_status();
    echo "Tier: " . $status['tier']; // 'free' or 'pro'
}
```

### Register a new site:

```php
$result = Registration_Manager::register_user(
    'admin@example.com',
    [
        'email_on_critical' => true,
        'weekly_summary'    => true,
    ]
);

if ( $result['success'] ) {
    $dashboard_url = $result['cloud_dashboard_url'];
    // Redirect or show link
} else {
    echo "Registration failed: " . $result['error'];
}
```

### Get registration status for display:

```php
$status = Registration_Manager::get_registration_status();

// Returns:
// {
//     'registered': bool,
//     'tier': 'free'|'pro',
//     'scans_remaining': int,
//     'emails_remaining': int,
//     'expires': string|null,
//     'is_expiring_soon': bool
// }

if ( $status['registered'] ) {
    echo "You have {$status['scans_remaining']} scans remaining";
    
    if ( $status['is_expiring_soon'] ) {
        echo "Pro tier expiring soon!";
    }
}
```

### Check if action is allowed by quota:

```php
if ( Registration_Manager::can_perform_action( 'scan' ) ) {
    // Can perform scan
} else {
    // Out of quota - show upgrade link
    $upgrade_url = Registration_Manager::get_upgrade_url();
    echo "<a href='$upgrade_url'>Upgrade to Pro</a>";
}
```

### Unregister from cloud:

```php
$result = Registration_Manager::unregister();
if ( $result['success'] ) {
    // All cloud data removed locally and from cloud
}
```

---

## Notification Management

### Get current notification preferences:

```php
use WPShadow\Cloud\Notification_Manager;

$prefs = Notification_Manager::get_preferences();

// Returns:
// {
//     'email_on_critical': true,      // Always on for free
//     'email_on_findings': false,     // Pro feature
//     'daily_digest': false,          // Pro feature
//     'weekly_summary': true,         // Free
//     'scan_completion': true,        // Free
//     'anomaly_alerts': false,        // Pro feature
//     'webhook_enabled': false,       // Pro feature
//     'webhook_url': '',              // Pro feature
// }

if ( $prefs['email_on_critical'] ) {
    echo "Critical alerts enabled";
}
```

### Update notification preferences:

```php
$result = Notification_Manager::set_preferences( [
    'email_on_critical' => true,    // Always allowed
    'weekly_summary'    => false,   // Opt-out
    'daily_digest'      => true,    // Only works if pro tier
] );

if ( $result ) {
    echo "Preferences saved";
}
```

### Send a notification:

```php
$sent = Notification_Manager::send_notification(
    'critical',  // Type: critical|findings|scan_complete|etc
    [
        'findings' => [
            [
                'id'      => 'ssl-missing',
                'message' => 'SSL certificate not installed',
            ]
        ]
    ],
    'ssl-missing' // Optional: unique context for rate limiting
);

if ( $sent ) {
    echo "Notification sent";
} else {
    // Not sent: user disabled it, or rate limited
}
```

### Get notification statistics:

```php
$stats = Notification_Manager::get_statistics();

// Returns:
// {
//     'total_sent': 42,
//     'by_type': {
//         'critical': 5,
//         'weekly_summary': 8,
//         'scan_completion': 29
//     }
// }
```

---

## Cloud API Communication

### Making API requests (low-level):

```php
use WPShadow\Cloud\Cloud_Client;

// The client handles authentication automatically
$response = Cloud_Client::request(
    'POST',                 // Method: GET|POST|PUT|DELETE
    '/scans',              // Endpoint
    [                      // Data (optional for GET)
        'findings' => [
            ['id' => 'ssl-missing', 'severity' => 'critical'],
        ]
    ],
    []                     // Additional headers (optional)
);

if ( isset( $response['error'] ) ) {
    echo "API error: " . $response['error'];
} else {
    // Success - use response data
    $scan_id = $response['scan_id'];
}
```

### Health check (verify API connectivity):

```php
if ( Cloud_Client::health_check() ) {
    echo "API is reachable";
} else {
    echo "API is down";
}
```

### Error handling (automatic retry):

```php
// Cloud_Client automatically:
// - Retries up to 3 times on network errors
// - Uses exponential backoff: 2s, 4s delays
// - Falls back gracefully if cloud service down
// - Never exposes sensitive data in logs

$response = Cloud_Client::request( 'GET', '/status' );

// Response format:
// Success: { 'tier': 'pro', 'scans_remaining': 50, ... }
// Error:   { 'error': 'Human-readable error message' }
```

---

## AJAX Commands

### Registration AJAX Command

**Endpoint:** `wp_ajax_wpshadow_register_cloud`  
**Method:** POST  
**Nonce:** `wpshadow_register_nonce`  
**Capability:** `manage_options`

**JavaScript Usage:**

```javascript
wp.ajax.post( 'wpshadow_register_cloud', {
    nonce: wpshadow.registerNonce,
    email: 'admin@example.com'  // Optional, defaults to admin email
}).done( function( response ) {
    console.log( 'Registration successful!' );
    console.log( 'Dashboard: ' + response.cloud_dashboard_url );
    window.location = response.cloud_dashboard_url;
}).fail( function( error ) {
    console.error( 'Registration failed: ' + error );
});
```

**Server Response:**

```php
// Success:
{
    'success': true,
    'message': 'Successfully registered with WPShadow Cloud!',
    'cloud_dashboard_url': 'https://dashboard.wpshadow.com/sites/...',
    'site_id': 'site_abc123...'
}

// Error:
{
    'error': 'Email already registered'
}
```

---

## Data Storage Reference

### WordPress Options (wp_options table)

| Option Name | Type | Purpose | Expires |
|---|---|---|---|
| `wpshadow_cloud_token` | string | API authentication token | Never |
| `wpshadow_site_id` | string | Cloud service identifier | Never |
| `wpshadow_registration_date` | datetime | When registered | Never |
| `wpshadow_subscription_tier` | string | 'free' or 'pro' | Never |
| `wpshadow_subscription_expires` | date | Pro expiration date | Never |
| `wpshadow_notification_preferences` | array | Notification settings | Never |
| `wpshadow_notification_log` | array | Last 500 notifications | Never |
| `wpshadow_guardian_enabled` | bool | Guardian master toggle | Never |
| `wpshadow_guardian_auto_fix_enabled` | bool | Auto-fix toggle | Never |
| `wpshadow_guardian_activity_log` | array | Last 500 activities | Never |

### WordPress Transients (temporary cache)

| Transient Name | TTL | Purpose |
|---|---|---|
| `wpshadow_registration_status_cache` | 24h | Cached registration status |
| `wpshadow_backup_{id}` | 28d | Automated backup snapshots |
| `wpshadow_notif_{type}_{context}` | 1h | Notification rate limiting |
| `wpshadow_registered_sites_list` | 1h | Multi-site dashboard cache |

---

## Security Reminders

### Always:
- ✅ Verify nonces on AJAX endpoints
- ✅ Check `current_user_can( 'manage_options' )`
- ✅ Sanitize inputs with `sanitize_text_field()`, `sanitize_email()`
- ✅ Escape output with `esc_html()`, `esc_attr()`, `esc_url()`
- ✅ Use `wp_json_encode()` for JSON encoding
- ✅ Use `wp_remote_request()` for HTTP calls (not curl directly)

### Never:
- ❌ Log API tokens or sensitive data
- ❌ Pass raw user input to database
- ❌ Use eval() or dynamic code execution
- ❌ Bypass nonce verification
- ❌ Trust $_POST/$_GET without sanitization
- ❌ Output user data without escaping

---

## Debugging & Troubleshooting

### Enable debug logging:

```php
// In wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );

// Cloud_Client will log errors to wp-content/debug.log
```

### Check registration status in database:

```bash
# Via WP CLI
wp option get wpshadow_cloud_token
wp option get wpshadow_site_id
wp option get wpshadow_subscription_tier

# Or in admin:
Settings → WPShadow → Cloud Status
```

### Verify API connectivity:

```php
use WPShadow\Cloud\Cloud_Client;

if ( Cloud_Client::health_check() ) {
    echo "✅ Cloud API is reachable";
} else {
    echo "❌ Cloud API is unreachable";
    echo "Check: Internet connection, firewall, API status";
}
```

### Clear registration cache:

```php
use WPShadow\Cloud\Registration_Manager;

// Force refresh of status from cloud API
Registration_Manager::clear_cache();

// Then fetch fresh:
$status = Registration_Manager::get_registration_status();
```

---

## Common Patterns

### Conditional Cloud Feature Display

```php
// Only show feature if user is registered AND on pro tier
if ( Registration_Manager::is_registered() ) {
    $status = Registration_Manager::get_registration_status();
    
    if ( $status['tier'] === 'pro' ) {
        // Show pro feature (daily digest)
    } else {
        // Show free feature (weekly summary)
        echo "<a href='" . Registration_Manager::get_upgrade_url() . "'>";
        echo "Upgrade to Pro for daily digests";
        echo "</a>";
    }
}
```

### Handle Quota Exceeded

```php
if ( ! Registration_Manager::can_perform_action( 'scan' ) ) {
    $status = Registration_Manager::get_registration_status();
    
    echo "You've used all {$status['scans_limit']} scans this month.";
    echo "<a href='" . Registration_Manager::get_upgrade_url() . "'>";
    echo "Upgrade to Pro for unlimited scans";
    echo "</a>";
    return;
}

// Proceed with scan
```

### Safe Notification Sending

```php
// Always wrapped in try-catch-like error checking
$sent = Notification_Manager::send_notification(
    'critical',
    $finding_data,
    $finding_id  // Unique context to prevent spam
);

if ( ! $sent ) {
    // Silently log, don't show error to user
    error_log( 'WPShadow: Notification failed for ' . $finding_id );
}
```

---

## Phase 8 (Guardian) Preview

Guardian system will use these same classes for:

```php
// Automated health checks
Guardian_Manager::run_health_check();

// Auto-apply safe fixes
Guardian_Manager::run_auto_fixes();

// Backup/restore
Backup_Manager::create_automated_backup( 'auto_fix_backup' );
Backup_Manager::restore_backup( $backup_id );

// Activity tracking
Guardian_Activity_Logger::log_auto_fix( 'Treatment_SSL', true );

// Email reports
$report = Guardian_Report_Generator::generate_daily_report();
Guardian_Report_Generator::send_report( 'daily_report', $data );
```

---

*Last Updated: January 21, 2026*  
*Phase 7-8 Core Implementation Complete*

