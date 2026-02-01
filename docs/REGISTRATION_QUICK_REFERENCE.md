# WPShadow Registration System - Quick Reference

**For Developers:** Fast reference for integrating with the unified registration system.

---

## Quick Check: Is User Registered?

```php
use WPShadow\Core\WPShadow_Account_API;

if ( WPShadow_Account_API::is_registered() ) {
    // User has an account
} else {
    // Show registration prompt
}
```

---

## Get Account Information

```php
$account_info = WPShadow_Account_API::get_account_info();

if ( ! is_wp_error( $account_info ) ) {
    $email = $account_info['email'];
    $member_since = $account_info['member_since'];
}
```

---

## Get Service Status (Free Tier Limits)

```php
$services = WPShadow_Account_API::get_services_status();

// Guardian
$guardian_tokens = $services['guardian']['tokens_current'];
$guardian_max = $services['guardian']['tokens_per_month'];

// Vault
$vault_backups = $services['vault']['max_backups'];
$vault_retention = $services['vault']['retention_days'];
$vault_storage = $services['vault']['storage_used'];

// Cloud
$cloud_checks = $services['cloud']['uptime_checks'];
```

---

## Manual Registration (Programmatic)

```php
$result = WPShadow_Account_API::register( 'user@example.com', 'password123' );

if ( $result['success'] ) {
    $api_key = $result['api_key'];
    // Services are automatically synced
}
```

---

## Connect Existing Account

```php
$result = WPShadow_Account_API::connect( 'wps_xxxxxxxxxxxxxxxx' );

if ( $result['success'] ) {
    // Account connected and services synced
}
```

---

## Sync Services Manually

```php
// After tier upgrade or account changes
WPShadow_Account_API::sync_services();

// This updates:
// - Guardian_API_Client::set_api_key()
// - Settings_Registry::set('vault_api_key')
// - update_option('wpshadow_cloud_api_key')
```

---

## Make Authenticated API Calls

```php
$response = WPShadow_Account_API::api_request( '/custom-endpoint', array(
    'method' => 'POST',
    'body'   => wp_json_encode( array(
        'data' => 'value',
    ) ),
) );

if ( ! is_wp_error( $response ) ) {
    // Handle response
}
```

---

## AJAX Registration (JavaScript)

```javascript
$.ajax({
    url: wpShadowAccount.ajax_url,
    type: 'POST',
    data: {
        action: 'wpshadow_account_register',
        nonce: wpShadowAccount.nonces.register,
        email: $('#email').val(),
        password: $('#password').val()
    },
    success: function(response) {
        if (response.success) {
            location.reload();
        }
    }
});
```

---

## Check Service Availability

```php
if ( WPShadow_Account_API::is_available() ) {
    // Account service is reachable
} else {
    // Service offline or network issue
}
```

---

## Validate API Key

```php
$is_valid = WPShadow_Account_API::validate_api_key( 'wps_xxxxxxxxxxxxxxxx' );
// Returns boolean, cached for 1 hour
```

---

## Get Default Free Tier Limits

```php
// If not registered, get default limits
$defaults = WPShadow_Account_API::get_default_service_limits();
// Returns array with guardian, vault, cloud limits
```

---

## Settings Access

```php
use WPShadow\Core\Settings_Registry;

// Get API key
$api_key = Settings_Registry::get( 'wpshadow_account_api_key', '' );

// Get email
$email = Settings_Registry::get( 'wpshadow_account_email', '' );

// Get registration timestamp
$registered_at = Settings_Registry::get( 'wpshadow_account_registered_at', 0 );

// Get services config
$services = Settings_Registry::get( 'wpshadow_account_services', array() );
```

---

## Cache Management

```php
use WPShadow\Core\Cache_Manager;

// Clear service status cache (5 min)
Cache_Manager::delete( 'account_service_status', 'wpshadow_account' );

// Clear account info cache (15 min)
Cache_Manager::delete( 'account_info', 'wpshadow_account' );

// Clear API key validation cache (1 hour)
Cache_Manager::delete( 'account_key_valid_' . md5( $api_key ), 'wpshadow_account' );
```

---

## Activity Logging

```php
use WPShadow\Core\Activity_Logger;

// Registration is automatically logged
// 'wpshadow_account_registered'
// 'wpshadow_account_connected'
// 'wpshadow_account_disconnected'
// 'wpshadow_services_synced'

// Query logs
$logs = Activity_Logger::get_logs( array(
    'action' => 'wpshadow_account_registered',
    'limit'  => 10,
) );
```

---

## Hooks

```php
// When account is registered
add_action( 'wpshadow_account_registered', function( $email, $services ) {
    // Custom action after registration
}, 10, 2 );

// When services are synced
add_action( 'wpshadow_services_synced', function( $services ) {
    // Custom action after sync
}, 10, 1 );

// When account is disconnected
add_action( 'wpshadow_account_disconnected', function() {
    // Custom cleanup
} );
```

---

## Free Tier Constants

```php
// Guardian
const GUARDIAN_FREE_TOKENS_PER_MONTH = 100;

// Vault
const VAULT_FREE_MAX_BACKUPS = 3;
const VAULT_FREE_RETENTION_DAYS = 7;
const VAULT_FREE_STORAGE_GB = 1;

// Cloud
const CLOUD_FREE_UPTIME_CHECKS = 100;
const CLOUD_FREE_AI_SCANS = 50;
```

---

## Common Use Cases

### Show Registration Prompt

```php
if ( ! WPShadow_Account_API::is_registered() ) {
    ?>
    <div class="notice notice-info">
        <p>
            <?php esc_html_e( 'Register for free to unlock Guardian AI scanning, Vault backups, and Cloud Services.', 'wpshadow' ); ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-account' ) ); ?>" class="button button-primary">
                <?php esc_html_e( 'Register Free', 'wpshadow' ); ?>
            </a>
        </p>
    </div>
    <?php
}
```

### Check Guardian Token Balance

```php
$services = WPShadow_Account_API::get_services_status();
$tokens = $services['guardian']['tokens_current'] ?? 0;

if ( $tokens < 10 ) {
    echo 'Low on tokens. Consider upgrading.';
}
```

### Display Service Usage

```php
$services = WPShadow_Account_API::get_services_status();

foreach ( $services as $service_id => $service_data ) {
    $tier = $service_data['tier'] ?? 'free';
    echo "<h3>{$service_id}: {$tier}</h3>";

    if ( 'guardian' === $service_id ) {
        $current = $service_data['tokens_current'] ?? 0;
        $max = $service_data['tokens_per_month'] ?? 100;
        echo "<p>Tokens: {$current} / {$max}</p>";
    }
}
```

---

## Error Handling

```php
$account_info = WPShadow_Account_API::get_account_info();

if ( is_wp_error( $account_info ) ) {
    $error_message = $account_info->get_error_message();
    $error_code = $account_info->get_error_code();

    // Handle specific errors
    switch ( $error_code ) {
        case 'no_api_key':
            // User not registered
            break;
        case 'invalid_response':
            // API error
            break;
    }
}
```

---

## Admin Page Integration

### Add Link to Menu

```php
add_menu_page(
    __( 'My Feature', 'wpshadow' ),
    __( 'My Feature', 'wpshadow' ),
    'manage_options',
    'wpshadow-my-feature',
    'my_render_callback',
    'dashicons-shield',
    25
);

function my_render_callback() {
    if ( ! WPShadow_Account_API::is_registered() ) {
        echo '<p>Please register at <a href="' . admin_url( 'admin.php?page=wpshadow-account' ) . '">WPShadow Account</a></p>';
        return;
    }

    // Render feature UI
}
```

---

## REST API Requests (from Plugin to Account API)

```php
// Example: Custom endpoint call
$response = WPShadow_Account_API::api_request( '/my-custom-endpoint', array(
    'method'  => 'POST',
    'timeout' => 30,
    'body'    => wp_json_encode( array(
        'site_id' => get_option( 'siteurl' ),
        'action'  => 'custom_action',
    ) ),
) );

if ( ! is_wp_error( $response ) && isset( $response['success'] ) ) {
    // Handle response
}
```

---

## Troubleshooting

### Clear All Caches

```php
Cache_Manager::delete( 'account_service_status', 'wpshadow_account' );
Cache_Manager::delete( 'account_info', 'wpshadow_account' );

$api_key = WPShadow_Account_API::get_api_key();
if ( $api_key ) {
    Cache_Manager::delete( 'account_key_valid_' . md5( $api_key ), 'wpshadow_account' );
}
```

### Force Refresh Account Info

```php
$account_info = WPShadow_Account_API::get_account_info( true ); // true = force refresh
```

### Re-sync Services

```php
WPShadow_Account_API::sync_services();
```

### Check Service Availability

```php
if ( ! WPShadow_Account_API::is_available() ) {
    error_log( 'WPShadow Account service is unavailable' );
}
```

---

## Pro Tips

1. **Always check `is_registered()` before calling account APIs**
2. **Use cached methods** - don't force refresh unless necessary
3. **Handle `WP_Error` responses** - API calls can fail
4. **Sync services after tier upgrades** - ensures consistent state
5. **Check service availability** before showing registration prompts
6. **Use Settings_Registry** for direct settings access (faster)
7. **Log important account events** using Activity_Logger

---

## File Locations

- **API Client:** `/includes/core/class-wpshadow-account-api.php`
- **AJAX Handlers:** `/includes/admin/ajax/class-account-registration-handler.php`
- **Admin Page:** `/includes/admin/class-account-registration-page.php`
- **Settings:** `/includes/core/class-settings-registry.php`
- **JavaScript:** `/assets/js/account.js`
- **CSS:** `/assets/css/account.css`

---

**Need Help?** See [UNIFIED_REGISTRATION_SYSTEM.md](/workspaces/wpshadow/docs/UNIFIED_REGISTRATION_SYSTEM.md) for full documentation.
