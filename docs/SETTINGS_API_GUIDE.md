# WPShadow Settings API Developer Guide

## Overview

This guide documents how WPShadow uses the WordPress Settings API for configuration management.

**Philosophy Alignment:**
- **Commandment #8** (Inspire Confidence): Using standard WordPress APIs instills confidence
- **Commandment #10** (Beyond Pure): Settings properly registered and transparent
- **Commandment #2** (Free Forever): All settings persist locally, nothing paywalled

---

## Architecture

### Settings Registration

All WPShadow settings are registered in a single location for maintainability:

**File:** `includes/core/class-settings-registry.php`

This class uses the WordPress Settings API to register settings with:
- Type validation
- Sanitization callbacks
- Default values
- REST API exposure control

### How Registration Works

1. Settings_Registry calls `register_setting()` on `admin_init` hook
2. Each setting specifies sanitization via callback
3. Settings are stored in `wp_options` table with `autoload=false`
4. REST API access controlled per-setting

### Benefits

✅ **Proper WordPress Integration**
- Respects WordPress architecture
- Integrates with Settings API UI
- Available in REST API (with proper exposure control)

✅ **Security**
- Automatic sanitization via registered callbacks
- Type validation enforced
- Nonce protection included

✅ **Maintainability**
- Single source of truth for all settings
- Consistent defaults
- Easy to audit and modify

---

## Registered Settings

### Group: `wpshadow_guardian` (6 settings)

Controls Guardian automated health monitoring system.

#### `wpshadow_guardian_enabled`
**Type:** Boolean
**Default:** `false` (opt-in philosophy)
**Description:** Master switch for Guardian system
**REST API:** Exposed as `wpshadow_guardian_enabled`
**Sanitization:** Boolean sanitizer
**Usage:**
```php
$enabled = get_option('wpshadow_guardian_enabled', false);
if ($enabled) {
    Guardian_Manager::run_scan();
}
```

#### `wpshadow_guardian_safety_mode`
**Type:** Boolean
**Default:** `true` (conservative default)
**Description:** Enable safety mode (dry-run fixes, no apply)
**REST API:** Exposed as `wpshadow_guardian_safety_mode`
**Sanitization:** Boolean sanitizer
**Usage:**
```php
$safety_mode = get_option('wpshadow_guardian_safety_mode', true);
if ($safety_mode) {
    // Dry-run only
    $preview = Guardian_Manager::preview_fixes($issues);
} else {
    // Apply fixes
    Guardian_Manager::apply_fixes($issues);
}
```

#### `wpshadow_guardian_activity_logging`
**Type:** Boolean
**Default:** `true` (transparency/privacy)
**Description:** Enable activity logging (privacy-first)
**REST API:** Hidden from REST API (not exposed)
**Sanitization:** Boolean sanitizer
**Notes:** Logged to `wp_options` table under `wpshadow_guardian_activity`
**Usage:**
```php
$logging_enabled = get_option('wpshadow_guardian_activity_logging', true);
if ($logging_enabled) {
    Guardian_Activity_Logger::log('fix_applied', $fix_data);
}
```

#### `wpshadow_guardian_check_frequency`
**Type:** String
**Default:** `'hourly'` (reasonable default)
**Valid Values:** `'hourly'`, `'twicedaily'`, `'daily'`
**Description:** How often Guardian scans run
**REST API:** Exposed as `wpshadow_guardian_check_frequency`
**Sanitization:** Enum sanitizer (only allows valid values)
**Usage:**
```php
$frequency = get_option('wpshadow_guardian_check_frequency', 'hourly');
wp_schedule_event(time(), $frequency, 'wpshadow_guardian_scan');
```

#### `wpshadow_guardian_max_treatments`
**Type:** Integer
**Default:** `5` (safety limit)
**Description:** Maximum fixes Guardian can apply in one scan
**REST API:** Exposed as `wpshadow_guardian_max_treatments`
**Sanitization:** Integer sanitizer with min=1, max=100
**Usage:**
```php
$max = (int) get_option('wpshadow_guardian_max_treatments', 5);
$treatments = array_slice($available_treatments, 0, $max);
```

#### `wpshadow_guardian_auto_fix_whitelist`
**Type:** Array (serialized)
**Default:** `['ssl_certificate', 'cache_clear']`
**Description:** Which treatments Guardian can auto-apply
**REST API:** Exposed as `wpshadow_guardian_auto_fix_whitelist`
**Sanitization:** Array sanitizer (text array)
**Usage:**
```php
$whitelist = get_option('wpshadow_guardian_auto_fix_whitelist', []);
if (in_array($treatment_id, $whitelist, true)) {
    Guardian_Manager::apply_treatment($treatment_id);
}
```

### Group: `wpshadow_workflow` (2 settings)

Controls workflow automation and email configuration.

#### `wpshadow_workflow_approved_recipients`
**Type:** Array (serialized)
**Default:** `[]` (empty - no emails by default)
**Description:** Email addresses approved for workflow notifications
**REST API:** Hidden from REST API (privacy)
**Sanitization:** Email array sanitizer
**Usage:**
```php
$recipients = get_option('wpshadow_workflow_approved_recipients', []);
foreach ($recipients as $email) {
    wp_mail($email, $subject, $message);
}
```

#### `wpshadow_workflow_email_verification_tokens`
**Type:** Array (serialized)
**Default:** `[]`
**Description:** Email verification tokens (internal use)
**REST API:** Hidden from REST API (security)
**Sanitization:** Text array sanitizer
**Usage:** Internal to email verification system

### Group: `wpshadow_privacy` (3 settings)

Controls data collection and privacy preferences.

#### `wpshadow_privacy_telemetry_enabled`
**Type:** Boolean
**Default:** `false` (opt-in, privacy-first)
**Description:** Whether to send anonymized telemetry
**REST API:** Exposed as `wpshadow_privacy_telemetry_enabled`
**Sanitization:** Boolean sanitizer
**Usage:**
```php
$telemetry = get_option('wpshadow_privacy_telemetry_enabled', false);
if ($telemetry) {
    Analytics_Service::send_metrics($data);
}
```

#### `wpshadow_privacy_telemetry_consent_date`
**Type:** String (ISO 8601 datetime)
**Default:** `''` (empty string)
**Description:** When user consented to telemetry
**REST API:** Hidden from REST API
**Sanitization:** ISO datetime sanitizer
**Usage:**
```php
$consent_date = get_option('wpshadow_privacy_telemetry_consent_date', '');
if ($consent_date) {
    $days_ago = floor((time() - strtotime($consent_date)) / DAY_IN_SECONDS);
}
```

#### `wpshadow_privacy_error_reporting`
**Type:** Boolean
**Default:** `false` (opt-in)
**Description:** Send error logs for debugging (privacy-first)
**REST API:** Hidden from REST API
**Sanitization:** Boolean sanitizer
**Usage:**
```php
$error_reporting = get_option('wpshadow_privacy_error_reporting', false);
if ($error_reporting) {
    Error_Reporter::send_to_service($error_log);
}
```

### Group: `wpshadow_general` (5 settings)

Core functionality settings.

#### `wpshadow_cache_enabled`
**Type:** Boolean
**Default:** `true` (better performance)
**Description:** Enable internal caching
**REST API:** Exposed
**Sanitization:** Boolean sanitizer

#### `wpshadow_cache_duration`
**Type:** Integer
**Default:** `3600` (1 hour in seconds)
**Valid Range:** 60-86400 (1 min to 24 hours)
**Description:** Cache TTL in seconds
**REST API:** Exposed
**Sanitization:** Integer sanitizer with min=60, max=86400

#### `wpshadow_debug_mode`
**Type:** Boolean
**Default:** `false` (disabled by default)
**Description:** Enable debug logging (dev only)
**REST API:** Hidden
**Sanitization:** Boolean sanitizer
**Usage:**
```php
if (get_option('wpshadow_debug_mode', false)) {
    error_log('Debug: ' . print_r($data, true));
}
```

#### `wpshadow_kb_link_enabled`
**Type:** Boolean
**Default:** `true` (philosophy #5)
**Description:** Show Knowledge Base links
**REST API:** Hidden
**Sanitization:** Boolean sanitizer

#### `wpshadow_training_link_enabled`
**Type:** Boolean
**Default:** `true` (philosophy #6)
**Description:** Show training video links
**REST API:** Hidden
**Sanitization:** Boolean sanitizer

### Group: `wpshadow_performance` (1 setting)

Performance optimization settings.

#### `wpshadow_heartbeat_frequency`
**Type:** String
**Default:** `'standard'` (default WP heartbeat)
**Valid Values:** `'disabled'`, `'half'`, `'standard'`
**Description:** Adjust WordPress heartbeat frequency
**REST API:** Hidden
**Sanitization:** Enum sanitizer

---

## Working with Settings

### Get a Setting

```php
// Get with default
$value = get_option('wpshadow_guardian_enabled', false);

// Type-safe using Options_Manager utility
use WPShadow\Core\Options_Manager;

$enabled = Options_Manager::get_bool('wpshadow_guardian_enabled', false);
$max_treatments = Options_Manager::get_int('wpshadow_guardian_max_treatments', 5);
$recipients = Options_Manager::get_array('wpshadow_workflow_approved_recipients', []);
```

### Update a Setting

```php
// Direct update
update_option('wpshadow_guardian_enabled', true);

// Update without autoload (for rarely-used settings)
update_option('wpshadow_guardian_activity', $activity_data, false);

// Using Options_Manager with transient caching
Options_Manager::set('wpshadow_cache_duration', 3600);
```

### In REST API

Settings exposed in REST API can be accessed via:

```javascript
// GET
fetch('/wp-json/wp/v2/settings')
    .then(r => r.json())
    .then(data => console.log(data.wpshadow_guardian_enabled));

// POST (requires authentication + capability)
fetch('/wp-json/wp/v2/settings', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ wpshadow_guardian_enabled: true })
})
```

### In Admin Settings Page

Settings are automatically integrated into WordPress Settings pages:

```php
// In your settings form
<form method="post" action="options.php">
    <?php settings_fields('wpshadow_guardian'); ?>

    <input type="checkbox"
        name="wpshadow_guardian_enabled"
        value="1"
        <?php checked(get_option('wpshadow_guardian_enabled', false)); ?> />

    <?php submit_button(); ?>
</form>
```

---

## Adding New Settings

### Step 1: Add to Settings_Registry

Edit `includes/core/class-settings-registry.php`:

```php
register_setting(
    'wpshadow_mygroup',          // Setting group
    'wpshadow_my_setting',        // Setting name
    [
        'sanitize_callback' => 'sanitize_text_field',
        'default'          => 'default_value',
        'type'             => 'string',
        'show_in_rest'     => true,  // Expose in REST API
    ]
);
```

### Step 2: Sanitization Callbacks

Use appropriate sanitizers for security:

```php
// Boolean
'sanitize_callback' => function($value) {
    return (bool) $value;
}

// Integer with range validation
'sanitize_callback' => function($value) {
    $int = (int) $value;
    return max(1, min(100, $int));  // Clamp between 1-100
}

// Email array
'sanitize_callback' => function($value) {
    $emails = (array) $value;
    return array_filter($emails, 'is_email');
}

// Enum (only specific values allowed)
'sanitize_callback' => function($value) {
    $allowed = ['option1', 'option2', 'option3'];
    return in_array($value, $allowed, true) ? $value : 'option1';
}
```

### Step 3: Use in Code

```php
$value = get_option('wpshadow_my_setting', 'default_value');
update_option('wpshadow_my_setting', $new_value);
```

---

## Best Practices

### ✅ DO

- ✅ Register all settings in Settings_Registry
- ✅ Use appropriate sanitization callbacks
- ✅ Provide sensible defaults
- ✅ Use `autoload=false` for rarely-accessed settings
- ✅ Document each setting's purpose and valid values
- ✅ Use Options_Manager for type-safe access
- ✅ Mark privacy-sensitive settings as hidden from REST API
- ✅ Validate user input before storing

### ❌ DON'T

- ❌ Use `get_option()` directly without registered setting
- ❌ Store arrays without serialization support
- ❌ Skip sanitization callbacks
- ❌ Store sensitive data in plain text (encrypt first)
- ❌ Autoload large options
- ❌ Mix old `define()` constants with Settings API

---

## Troubleshooting

### Settings not appearing in WordPress Settings

**Issue:** Settings registered but not visible
**Solution:** Ensure Settings_Registry::register() is called on `admin_init` hook

### PHP warnings about undefined settings

**Issue:** Notice: Undefined array key 'wpshadow_my_setting'
**Solution:** Always provide defaults: `get_option('key', 'default')`

### Settings not persisting

**Issue:** Updated option disappears
**Solution:** Check sanitization callback isn't filtering out value too aggressively

### REST API returns empty

**Issue:** Settings not showing in `/wp-json/wp/v2/settings`
**Solution:** Verify `'show_in_rest' => true` in registration

---

## Related Files

- **Registration:** [includes/core/class-settings-registry.php](../../includes/core/class-settings-registry.php)
- **Usage Utility:** [includes/core/class-options-manager.php](../../includes/core/class-options-manager.php)
- **Examples:** See any treatment class or admin handler for usage patterns

---

**Philosophy Reference:**
- #2 (Free as Possible) - All settings control local features only
- #5 (Drive to KB) - Link to KB from all settings
- #8 (Inspire Confidence) - Using WordPress Settings API standard
- #10 (Beyond Pure) - Settings transparent and privacy-first
