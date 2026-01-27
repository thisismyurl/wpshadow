# WPShadow Hooks Reference

**Version:** 1.2601.2148  
**Last Updated:** January 25, 2026

This document provides a comprehensive reference for all developer hooks (actions and filters) available in WPShadow. These hooks allow developers to extend and customize WPShadow's functionality.

---

## Table of Contents

- [Core Lifecycle Hooks](#core-lifecycle-hooks)
- [Treatment Hooks](#treatment-hooks)
- [Diagnostic Hooks](#diagnostic-hooks)
- [Finding Management Hooks](#finding-management-hooks)
- [Settings Hooks](#settings-hooks)
- [Activity & KPI Hooks](#activity--kpi-hooks)
- [Guardian Hooks](#guardian-hooks)
- [Workflow Hooks](#workflow-hooks)
- [Privacy & Consent Hooks](#privacy--consent-hooks)
- [Backup & Recovery Hooks](#backup--recovery-hooks)
- [Admin UI Hooks](#admin-ui-hooks)
- [Usage Examples](#usage-examples)

---

## Core Lifecycle Hooks

### `wpshadow_core_loaded`
**Type:** Action  
**Description:** Fires when the core WPShadow system has been loaded, before other systems initialize.  
**Parameters:** None  
**Example:**
```php
add_action( 'wpshadow_core_loaded', function() {
    // Custom initialization code
    error_log( 'WPShadow core loaded' );
} );
```

### `wpshadow_core_initialized`
**Type:** Action  
**Description:** Fires after all WPShadow systems are fully initialized.  
**Parameters:** None  
**Example:**
```php
add_action( 'wpshadow_core_initialized', function() {
    // All systems ready, safe to interact with WPShadow
    $findings = \WPShadow\Diagnostics\Diagnostic_Registry::get_all();
} );
```

### `wpshadow_load_pro_features`
**Type:** Action  
**Description:** Fires when pro features should be loaded. Use this to load premium extensions.  
**Parameters:** None  
**Example:**
```php
add_action( 'wpshadow_load_pro_features', function() {
    require_once MY_PLUGIN_PATH . '/pro-features.php';
} );
```

---

## Treatment Hooks

### `wpshadow_before_treatment_apply`
**Type:** Action  
**Description:** Fires before a treatment is applied.  
**Parameters:**
- `string $class` - Treatment class name
- `string $finding_id` - Finding identifier
- `bool $dry_run` - Whether this is a dry run (no changes made)

**Example:**
```php
add_action( 'wpshadow_before_treatment_apply', function( $class, $finding_id, $dry_run ) {
    error_log( "Applying treatment {$class} for finding {$finding_id}" );
    
    // Take a backup before treatment
    if ( ! $dry_run ) {
        do_backup();
    }
}, 10, 3 );
```

### `wpshadow_after_treatment_apply`
**Type:** Action  
**Description:** Fires after a treatment has been applied.  
**Parameters:**
- `string $class` - Treatment class name
- `string $finding_id` - Finding identifier
- `array $result` - Treatment result with 'success' and 'message' keys

**Example:**
```php
add_action( 'wpshadow_after_treatment_apply', function( $class, $finding_id, $result ) {
    if ( $result['success'] ) {
        // Send notification on successful treatment
        wp_mail( get_option( 'admin_email' ), 
            'Treatment Applied', 
            "Treatment {$class} successfully applied." 
        );
    }
}, 10, 3 );
```

### `wpshadow_treatment_result`
**Type:** Filter  
**Description:** Filters the treatment result before it's returned.  
**Parameters:**
- `array $result` - Result array with 'success' and 'message' keys
- `string $class` - Treatment class name
- `string $finding_id` - Finding identifier

**Example:**
```php
add_filter( 'wpshadow_treatment_result', function( $result, $class, $finding_id ) {
    // Add custom data to result
    $result['custom_field'] = 'custom value';
    return $result;
}, 10, 3 );
```

### `wpshadow_before_treatment_undo`
**Type:** Action  
**Description:** Fires before a treatment is undone.  
**Parameters:**
- `string $class` - Treatment class name
- `string $finding_id` - Finding identifier

**Example:**
```php
add_action( 'wpshadow_before_treatment_undo', function( $class, $finding_id ) {
    error_log( "Undoing treatment {$class}" );
}, 10, 2 );
```

### `wpshadow_after_treatment_undo`
**Type:** Action  
**Description:** Fires after a treatment has been undone.  
**Parameters:**
- `string $class` - Treatment class name
- `string $finding_id` - Finding identifier
- `array $result` - Undo result with 'success' and 'message' keys

**Example:**
```php
add_action( 'wpshadow_after_treatment_undo', function( $class, $finding_id, $result ) {
    if ( $result['success'] ) {
        wp_cache_flush(); // Clear all caches after undo
    }
}, 10, 3 );
```

### `wpshadow_treatment_undo_result`
**Type:** Filter  
**Description:** Filters the treatment undo result before it's returned.  
**Parameters:**
- `array $result` - Result array
- `string $class` - Treatment class name
- `string $finding_id` - Finding identifier

---

## Diagnostic Hooks

### `wpshadow_before_diagnostic_check`
**Type:** Action  
**Description:** Fires before a diagnostic check is run.  
**Parameters:**
- `string $class` - Diagnostic class name
- `string $slug` - Diagnostic slug/identifier

**Example:**
```php
add_action( 'wpshadow_before_diagnostic_check', function( $class, $slug ) {
    // Prepare environment for diagnostic
    wp_cache_flush();
}, 10, 2 );
```

### `wpshadow_after_diagnostic_check`
**Type:** Action  
**Description:** Fires after a diagnostic check has run.  
**Parameters:**
- `string $class` - Diagnostic class name
- `string $slug` - Diagnostic slug/identifier
- `array|null $finding` - Finding result (null if no issues found)

**Example:**
```php
add_action( 'wpshadow_after_diagnostic_check', function( $class, $slug, $finding ) {
    if ( $finding && $finding['severity'] === 'critical' ) {
        // Alert on critical findings
        wp_mail( get_option( 'admin_email' ), 
            'Critical Issue Detected', 
            "Diagnostic {$slug} found a critical issue." 
        );
    }
}, 10, 3 );
```

### `wpshadow_diagnostic_result`
**Type:** Filter  
**Description:** Filters diagnostic check results before they're stored/displayed.  
**Parameters:**
- `array|null $finding` - Finding result (null if no issues)
- `string $class` - Diagnostic class name
- `string $slug` - Diagnostic slug/identifier

**Example:**
```php
add_filter( 'wpshadow_diagnostic_result', function( $finding, $class, $slug ) {
    // Suppress certain findings in development
    if ( WP_DEBUG && $slug === 'debug-mode' ) {
        return null; // Suppress finding
    }
    return $finding;
}, 10, 3 );
```

### `wpshadow_diagnostic_executed`
**Type:** Action  
**Description:** Legacy hook fired when a diagnostic is executed (deprecated - use wpshadow_after_diagnostic_check).  
**Parameters:**
- Mixed parameters

---

## Finding Management Hooks

### `wpshadow_finding_status_changed`
**Type:** Action  
**Description:** Fires when a finding's status is changed.  
**Parameters:**
- `string $finding_id` - Finding identifier
- `string $status` - New status (detected, ignored, manual, automated, fixed)
- `string|null $old_status` - Previous status (null if first time)

**Example:**
```php
add_action( 'wpshadow_finding_status_changed', function( $finding_id, $status, $old_status ) {
    // Track status changes in external system
    if ( $status === 'fixed' ) {
        error_log( "Finding {$finding_id} was fixed!" );
    }
}, 10, 3 );
```

### `wpshadow_finding_detected`
**Type:** Action  
**Description:** Fires when a new finding is detected.  
**Parameters:**
- `string $finding_id` - Finding identifier
- `string $severity` - Severity level (critical, high, medium, low, info)

**Example:**
```php
add_action( 'wpshadow_finding_detected', function( $finding_id, $severity ) {
    // Send Slack notification for critical findings
    if ( $severity === 'critical' ) {
        send_slack_alert( "Critical finding detected: {$finding_id}" );
    }
}, 10, 2 );
```

### `wpshadow_finding_resolved`
**Type:** Action  
**Description:** Fires when a finding is resolved.  
**Parameters:**
- `string $finding_id` - Finding identifier
- `string $resolution_type` - How it was resolved (automated, manual, ignored)

**Example:**
```php
add_action( 'wpshadow_finding_resolved', function( $finding_id, $resolution_type ) {
    // Update dashboard metrics
    update_option( 'my_resolved_count', get_option( 'my_resolved_count', 0 ) + 1 );
}, 10, 2 );
```

---

## Settings Hooks

### `wpshadow_setting_updated`
**Type:** Action  
**Description:** Fires when any WPShadow setting is updated.  
**Parameters:**
- `string $option` - Setting name
- `mixed $old_value` - Previous value
- `mixed $value` - New value

**Example:**
```php
add_action( 'wpshadow_setting_updated', function( $option, $old_value, $value ) {
    error_log( "Setting {$option} changed from {$old_value} to {$value}" );
}, 10, 3 );
```

### `wpshadow_setting_updated_{$option}`
**Type:** Action  
**Description:** Fires when a specific WPShadow setting is updated. Dynamic hook name based on setting name.  
**Parameters:**
- `mixed $old_value` - Previous value
- `mixed $value` - New value

**Example:**
```php
// Monitor debug mode changes
add_action( 'wpshadow_setting_updated_wpshadow_debug_mode', function( $old_value, $value ) {
    if ( $value ) {
        error_log( 'WPShadow debug mode enabled' );
    }
}, 10, 2 );
```

### `wpshadow_setting_added`
**Type:** Action  
**Description:** Fires when a WPShadow setting is added (first time).  
**Parameters:**
- `string $option` - Setting name
- `mixed $value` - Setting value

**Example:**
```php
add_action( 'wpshadow_setting_added', function( $option, $value ) {
    // Track new settings
    error_log( "New setting added: {$option}" );
}, 10, 2 );
```

### `wpshadow_setting_added_{$option}`
**Type:** Action  
**Description:** Fires when a specific WPShadow setting is added. Dynamic hook name.  
**Parameters:**
- `mixed $value` - Setting value

---

## Activity & KPI Hooks

### `wpshadow_activity_logged`
**Type:** Action  
**Description:** Fires after an activity entry is logged.  
**Parameters:**
- `array $activity` - Activity log entry

**Example:**
```php
add_action( 'wpshadow_activity_logged', function( $activity ) {
    // Send activity to external logging service
    send_to_splunk( $activity );
} );
```

### `wpshadow_activity_entry`
**Type:** Filter  
**Description:** Filters an activity log entry before it's saved.  
**Parameters:**
- `array $activity` - Activity entry
- `string $action` - Action type
- `string $details` - Action details
- `string $category` - Activity category
- `array $metadata` - Additional metadata

**Example:**
```php
add_filter( 'wpshadow_activity_entry', function( $activity, $action, $details, $category, $metadata ) {
    // Add custom metadata
    $activity['custom_field'] = 'custom value';
    return $activity;
}, 10, 5 );
```

### `wpshadow_treatment_kpi_recorded`
**Type:** Action  
**Description:** Fires when a treatment KPI is recorded.  
**Parameters:**
- `string $treatment_id` - Treatment identifier
- `int $time_saved_minutes` - Time saved in minutes

### `wpshadow_diagnostic_kpi_recorded`
**Type:** Action  
**Description:** Fires when a diagnostic KPI is recorded.  
**Parameters:**
- `string $diagnostic_id` - Diagnostic identifier
- `bool $success` - Whether diagnostic was successful

---

## Guardian Hooks

### `wpshadow_guardian_settings_updated`
**Type:** Action  
**Description:** Fires when Guardian settings are updated.  
**Parameters:**
- `array $settings` - New Guardian settings

### `wpshadow_guardian_health_check_complete`
**Type:** Action  
**Description:** Fires when Guardian completes a health check.  
**Parameters:**
- `array $findings` - Array of findings detected

### `wpshadow_guardian_auto_fix_complete`
**Type:** Action  
**Description:** Fires when Guardian completes automated fixes.  
**Parameters:**
- `array $results` - Results of automated fixes

### `wpshadow_guardian_disabled`
**Type:** Action  
**Description:** Fires when Guardian is disabled.  
**Parameters:** None

### `wpshadow_guardian_background_analyzers`
**Type:** Action  
**Description:** Fires when Guardian runs background analyzers.  
**Parameters:** None

### `wpshadow_baseline_reset`
**Type:** Action  
**Description:** Fires when the Guardian baseline is reset.  
**Parameters:** None

---

## Workflow Hooks

### `wpshadow_before_workflow_delete`
**Type:** Action  
**Description:** Fires before a workflow is deleted.  
**Parameters:**
- `string $workflow_id` - Workflow identifier
- `array $workflow` - Workflow data

### `wpshadow_after_workflow_delete`
**Type:** Action  
**Description:** Fires after a workflow is deleted.  
**Parameters:**
- `string $workflow_id` - Workflow identifier
- `array $workflow` - Workflow data

### `wpshadow_workflow_suggestions`
**Type:** Filter  
**Description:** Filters workflow suggestions.  
**Parameters:**
- `array $suggestions` - Workflow suggestions

### `wpshadow_refresh_workflow_discovery`
**Type:** Action  
**Description:** Fires to trigger workflow discovery refresh.  
**Parameters:** None

---

## Privacy & Consent Hooks

### `wpshadow_onboarding_completed`
**Type:** Action  
**Description:** Fires when onboarding is completed.  
**Parameters:**
- `int $user_id` - User ID
- `string $platform` - Platform type
- `string $comfort_level` - User comfort level
- `array $config_data` - Configuration data
- `array $privacy_data` - Privacy preferences

### `wpshadow_newsletter_subscribe`
**Type:** Action  
**Description:** Fires when a user subscribes to the newsletter.  
**Parameters:**
- `string $email` - Email address
- `array $data` - Additional subscription data

### `wpshadow_registered`
**Type:** Action  
**Description:** Fires when the plugin is registered with WPShadow cloud.  
**Parameters:** None

### `wpshadow_unregistered`
**Type:** Action  
**Description:** Fires when the plugin is unregistered from WPShadow cloud.  
**Parameters:** None

---

## Backup & Recovery Hooks

### `wpshadow_backup_created`
**Type:** Action  
**Description:** Fires when a backup is created.  
**Parameters:**
- `string $backup_id` - Backup identifier
- `string $reason` - Reason for backup

### `wpshadow_backup_restored`
**Type:** Action  
**Description:** Fires when a backup is restored.  
**Parameters:**
- `string $backup_id` - Backup identifier

### `wpshadow_backup_deleted`
**Type:** Action  
**Description:** Fires when a backup is deleted.  
**Parameters:**
- `string $backup_id` - Backup identifier

---

## Admin UI Hooks

### `wpshadow_admin_notices`
**Type:** Action  
**Description:** Fires when admin notices should be displayed.  
**Parameters:** None

### `wpshadow_dashboard_activity`
**Type:** Action  
**Description:** Fires in the dashboard activity section.  
**Parameters:**
- `string $category_filter` - Current category filter

### `wpshadow_dashboard_after_content`
**Type:** Action  
**Description:** Fires after the main dashboard content.  
**Parameters:**
- `string $category_filter` - Current category filter

### `wpshadow_dashboard_gauges`
**Type:** Action  
**Description:** Fires in the dashboard gauges section.  
**Parameters:**
- `string $category_filter` - Current category filter

### `wpshadow_onboarding_wizard_assets`
**Type:** Action  
**Description:** Fires when onboarding wizard assets should be enqueued.  
**Parameters:** None

---

## Usage Examples

### Example 1: Send Notifications on Critical Findings

```php
/**
 * Send email notifications when critical findings are detected.
 */
add_action( 'wpshadow_finding_detected', function( $finding_id, $severity ) {
    if ( $severity === 'critical' ) {
        $admin_email = get_option( 'admin_email' );
        $subject = sprintf( __( 'Critical Security Issue Detected: %s', 'my-plugin' ), $finding_id );
        $message = sprintf( 
            __( 'WPShadow has detected a critical security issue: %s. Please log in to review and fix this issue immediately.', 'my-plugin' ),
            $finding_id 
        );
        
        wp_mail( $admin_email, $subject, $message );
    }
}, 10, 2 );
```

### Example 2: Track Treatment Success Rate

```php
/**
 * Track treatment success rate in custom database table.
 */
add_action( 'wpshadow_after_treatment_apply', function( $class, $finding_id, $result ) {
    global $wpdb;
    
    $wpdb->insert(
        $wpdb->prefix . 'my_treatment_log',
        array(
            'treatment_class' => $class,
            'finding_id'      => $finding_id,
            'success'         => $result['success'] ? 1 : 0,
            'timestamp'       => current_time( 'mysql' ),
        ),
        array( '%s', '%s', '%d', '%s' )
    );
}, 10, 3 );
```

### Example 3: Custom Diagnostic Result Filtering

```php
/**
 * Suppress certain diagnostics in specific environments.
 */
add_filter( 'wpshadow_diagnostic_result', function( $finding, $class, $slug ) {
    // Don't show debug mode warnings in development
    if ( wp_get_environment_type() === 'development' && $slug === 'debug-mode' ) {
        return null;
    }
    
    // Downgrade severity for staging
    if ( wp_get_environment_type() === 'staging' && $finding ) {
        if ( $finding['severity'] === 'critical' ) {
            $finding['severity'] = 'high';
        }
    }
    
    return $finding;
}, 10, 3 );
```

### Example 4: Integrate with Third-Party Services

```php
/**
 * Send activity log to external monitoring service.
 */
add_action( 'wpshadow_activity_logged', function( $activity ) {
    // Send to Datadog
    if ( function_exists( 'dd_trace_function' ) ) {
        \datadog\trace\DDTrace::log(
            'wpshadow.activity',
            $activity,
            array( 'source' => 'wpshadow' )
        );
    }
    
    // Send to New Relic
    if ( extension_loaded( 'newrelic' ) ) {
        newrelic_record_custom_event( 'WPShadowActivity', $activity );
    }
} );
```

### Example 5: Auto-Backup Before Treatments

```php
/**
 * Automatically create a backup before applying treatments.
 */
add_action( 'wpshadow_before_treatment_apply', function( $class, $finding_id, $dry_run ) {
    // Skip backups in dry run mode
    if ( $dry_run ) {
        return;
    }
    
    // Only backup for high-risk treatments
    $high_risk = array( 'ssl', 'database', 'file-permissions' );
    if ( in_array( $finding_id, $high_risk, true ) ) {
        if ( class_exists( '\\WPShadow\\Monitoring\\Recovery\\Backup_Manager' ) ) {
            \WPShadow\Monitoring\Recovery\Backup_Manager::create_backup(
                "before-treatment-{$finding_id}"
            );
        }
    }
}, 5, 3 ); // Priority 5 to run before other hooks
```

---

## Best Practices

### Hook Priority
- Use lower priority numbers (1-5) for hooks that must run early
- Use default priority (10) for most hooks
- Use higher priority numbers (15-20) for hooks that should run late

### Performance
- Keep hook callbacks lightweight
- Avoid expensive operations in hooks that fire frequently
- Use caching when appropriate
- Consider async processing for heavy tasks

### Error Handling
- Always validate hook parameters
- Use try-catch blocks for operations that might fail
- Log errors appropriately
- Don't let hook failures break the main flow

### Naming Conventions
- All WPShadow hooks are prefixed with `wpshadow_`
- Actions describe events: `wpshadow_treatment_applied`
- Filters describe what's being filtered: `wpshadow_treatment_result`

---

## Support

For questions about hooks or to request new hooks:
- **GitHub Issues:** https://github.com/thisismyurl/wpshadow/issues
- **Documentation:** https://wpshadow.com/docs/hooks
- **Community Forum:** https://wpshadow.com/community

---

**Philosophy Alignment:**
- Commandment #7: Ridiculously Good - Comprehensive hook system for extensibility
- Commandment #8: Inspire Confidence - Well-documented, predictable hooks
- Accessibility First: All hooks documented with clear examples
