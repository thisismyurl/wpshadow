# Phase 7 & 8: Cloud Features & Guardian Automation
## Comprehensive Implementation Plan

**Date:** January 21, 2026  
**Status:** Planning Phase  
**Philosophy Compliance:** ✅ All features pass Commandment #2 (Free as Possible) and #10 (Register Not Pay)

---

## Executive Summary

Phase 7 and 8 introduce two major capabilities that scale WPShadow beyond local diagnostics:

### Phase 7: Cloud Features & SaaS Integration ☁️
- **Goal:** Enable cloud-based services with generous free tier (registration-based, not payment-gated)
- **Free Tier:** 100 cloud scans/month, 50 email notifications/month, 3-site dashboard, 30-day analytics
- **Pro Tier:** Unlimited scans/notifications, unlimited sites, 365-day analytics, priority support
- **Philosophy:** Register (free) unlocks cloud features; Pay (optional) removes limits

### Phase 8: Guardian & Automation 🤖
- **Goal:** Proactive site health management with background job execution
- **Features:** Scheduled health checks, auto-fix of safe issues, email reports, anomaly detection
- **Philosophy:** Automation with oversight—users stay in control

---

## Phase 7: Cloud Features & SaaS Integration

### 7.1 Registration System

**Classes to Create:**

#### `includes/cloud/class-registration-manager.php` (200 LOC)
Manages user registration and cloud account linking.

```php
class Registration_Manager {
    
    /**
     * Register user with cloud service
     * Returns registration token for cloud dashboard
     */
    public static function register_user(
        string $email,
        string $site_url,
        array $preferences = []
    ): array {
        // Validate email & site
        // Call cloud API: POST /api/v1/register
        // Store cloud_token + registration_date in options
        // Return: { success: bool, token: string, cloud_dashboard_url: string }
    }
    
    /**
     * Check if site is registered
     */
    public static function is_registered(): bool {
        return ! empty( get_option( 'wpshadow_cloud_token' ) );
    }
    
    /**
     * Get registration status (free/pro tier)
     */
    public static function get_registration_status(): array {
        // Returns: { registered: bool, tier: 'free'|'pro', scans_remaining: int, expires: string }
    }
    
    /**
     * Get cloud dashboard URL
     */
    public static function get_dashboard_url(): string {
        return 'https://dashboard.wpshadow.com/sites/' . get_option( 'wpshadow_site_id' );
    }
    
    /**
     * Unregister site from cloud
     */
    public static function unregister(): bool {
        // Call cloud API: DELETE /api/v1/sites/{site_id}
        // Delete local cloud data
    }
}
```

**Data Storage (wp_options):**
- `wpshadow_cloud_token` - API token for authentication
- `wpshadow_site_id` - Cloud service site identifier
- `wpshadow_registration_date` - When registered
- `wpshadow_subscription_tier` - 'free' or 'pro'
- `wpshadow_subscription_expires` - Expiration date for pro tier

---

#### `includes/cloud/class-cloud-client.php` (250 LOC)
Low-level HTTP client for cloud API communication.

```php
class Cloud_Client {
    
    private const API_BASE = 'https://api.wpshadow.com/v1';
    
    /**
     * Send authenticated request to cloud API
     */
    public static function request(
        string $method,
        string $endpoint,
        array $data = [],
        array $headers = []
    ): array {
        // Verify API token
        $token = get_option( 'wpshadow_cloud_token' );
        if ( ! $token ) {
            return [ 'error' => 'Not registered' ];
        }
        
        // Add auth header
        $headers['Authorization'] = 'Bearer ' . $token;
        $headers['X-Site-ID'] = get_option( 'wpshadow_site_id' );
        
        // Build URL
        $url = self::API_BASE . $endpoint;
        
        // Send request with retries & timeouts
        $response = wp_remote_request( $url, [
            'method'  => $method,
            'headers' => $headers,
            'body'    => wp_json_encode( $data ),
            'timeout' => 10,
        ] );
        
        if ( is_wp_error( $response ) ) {
            return [ 'error' => $response->get_error_message() ];
        }
        
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        return $body ?? [];
    }
    
    /**
     * Retry logic with exponential backoff
     */
    private static function send_with_retry(): array {
        // Max 3 retries with exponential backoff
        // Return failed response after final attempt
    }
}
```

**API Endpoints Used:**
- `POST /register` - Register new site
- `GET /sites/{id}` - Get site registration status
- `POST /scans` - Submit deep scan request
- `GET /scans/{id}` - Get scan results
- `POST /notifications` - Configure email notifications
- `GET /usage` - Get quota usage (free vs pro)
- `DELETE /sites/{id}` - Unregister site

---

### 7.2 Deep Scanning System

**Classes to Create:**

#### `includes/cloud/class-deep-scanner.php` (300 LOC)
Executes comprehensive cloud-based health scans.

```php
class Deep_Scanner {
    
    /**
     * Initiate cloud deep scan
     * Local diagnostics are sent to cloud for enhanced analysis
     */
    public static function initiate_scan(): array {
        // Check quota
        $status = Registration_Manager::get_registration_status();
        if ( $status['scans_remaining'] <= 0 ) {
            return [ 'error' => 'Scan quota exceeded. Upgrade to Pro.' ];
        }
        
        // Gather local diagnostic data
        $local_findings = self::gather_local_findings();
        
        // Send to cloud API
        $cloud_response = Cloud_Client::request( 'POST', '/scans', [
            'findings'      => $local_findings,
            'site_url'      => get_site_url(),
            'wp_version'    => get_bloginfo( 'version' ),
            'php_version'   => phpversion(),
        ] );
        
        if ( isset( $cloud_response['error'] ) ) {
            return $cloud_response;
        }
        
        // Store scan result locally
        $scan_id = $cloud_response['scan_id'];
        update_option( "wpshadow_cloud_scan_{$scan_id}", [
            'scan_id'       => $scan_id,
            'timestamp'     => current_time( 'mysql' ),
            'status'        => 'processing',
            'findings_count' => count( $local_findings ),
        ] );
        
        return [ 'success' => true, 'scan_id' => $scan_id ];
    }
    
    /**
     * Get scan results
     */
    public static function get_scan_results( string $scan_id ): array {
        // Check if cached
        $cached = get_option( "wpshadow_cloud_scan_{$scan_id}" );
        
        // If not completed, fetch from API
        if ( $cached['status'] !== 'completed' ) {
            $response = Cloud_Client::request( 'GET', "/scans/{$scan_id}" );
            if ( isset( $response['error'] ) ) {
                return $response;
            }
            
            // Update cache
            $cached = $response;
            update_option( "wpshadow_cloud_scan_{$scan_id}", $cached );
        }
        
        return $cached;
    }
    
    /**
     * Gather local findings for cloud analysis
     */
    private static function gather_local_findings(): array {
        $registry = \WPShadow\Diagnostics\Diagnostic_Registry::get_all();
        $findings = [];
        
        foreach ( $registry as $diagnostic ) {
            if ( $diagnostic->has_issues() ) {
                $findings[] = [
                    'id'       => $diagnostic::get_id(),
                    'severity' => $diagnostic::get_severity(),
                    'message'  => $diagnostic::get_finding_text(),
                ];
            }
        }
        
        return $findings;
    }
}
```

**Enhancement:** Cloud service returns:
- AI-powered analysis of findings
- Risk scoring (0-100)
- Correlation with other WordPress sites
- Predictive alerts ("similar issues led to hacks in X% of sites")
- Performance recommendations based on similar sites
- Security advisories matching site configuration

---

### 7.3 Notification System

**Classes to Create:**

#### `includes/cloud/class-notification-manager.php` (200 LOC)
Manages email and webhook notifications.

```php
class Notification_Manager {
    
    /**
     * Get current notification preferences
     */
    public static function get_preferences(): array {
        return get_option( 'wpshadow_notification_preferences', [
            'email_on_critical'     => true,  // Always on for free
            'email_on_findings'     => false, // Pro feature
            'daily_digest'          => false, // Pro feature
            'weekly_summary'        => true,  // Free
            'scan_completion'       => true,  // Free
            'anomaly_alerts'        => false, // Pro feature
        ] );
    }
    
    /**
     * Update notification preferences
     */
    public static function set_preferences( array $preferences ): bool {
        // Validate tier
        $status = Registration_Manager::get_registration_status();
        
        // Block pro features if free tier
        if ( $status['tier'] === 'free' ) {
            $preferences['email_on_findings'] = false;
            $preferences['daily_digest']      = false;
            $preferences['anomaly_alerts']    = false;
        }
        
        return update_option( 'wpshadow_notification_preferences', $preferences );
    }
    
    /**
     * Send notification via cloud service
     * Cloud service handles email delivery (more reliable than wp_mail)
     */
    public static function send_notification(
        string $type,
        array $data
    ): bool {
        $prefs = self::get_preferences();
        
        // Check if notification type is enabled
        $key = "email_on_{$type}";
        if ( ! isset( $prefs[ $key ] ) || ! $prefs[ $key ] ) {
            return false;
        }
        
        // Send via cloud API
        $response = Cloud_Client::request( 'POST', '/notifications', [
            'type' => $type,
            'data' => $data,
        ] );
        
        return ! isset( $response['error'] );
    }
}
```

**Notification Types:**
- `critical` - Security findings, immediate attention needed
- `findings` - New findings detected (pro feature)
- `scan_completion` - Deep scan completed with results
- `weekly_summary` - Weekly digest of site health
- `anomaly` - Unusual activity detected (pro feature)

---

### 7.4 Multi-Site Dashboard

**Classes to Create:**

#### `includes/cloud/class-multisite-dashboard.php` (250 LOC)
Centralized dashboard for managing multiple WordPress sites.

```php
class Multisite_Dashboard {
    
    /**
     * Get all registered sites (from cloud account)
     * Returns sites the logged-in cloud user manages
     */
    public static function get_registered_sites(): array {
        // Check cache (1 hour TTL)
        $cached = get_transient( 'wpshadow_registered_sites_list' );
        if ( $cached ) {
            return $cached;
        }
        
        // Fetch from API
        $response = Cloud_Client::request( 'GET', '/sites' );
        if ( isset( $response['error'] ) ) {
            return [];
        }
        
        // Cache for 1 hour
        set_transient( 'wpshadow_registered_sites_list', $response['sites'], 3600 );
        
        return $response['sites'];
    }
    
    /**
     * Get current site from cloud account perspective
     */
    public static function get_current_site_status(): array {
        $response = Cloud_Client::request(
            'GET',
            '/sites/' . get_option( 'wpshadow_site_id' )
        );
        
        return $response ?? [];
    }
    
    /**
     * Aggregate health across all sites (pro feature)
     */
    public static function get_network_health(): array {
        $sites = self::get_registered_sites();
        
        return [
            'total_sites'       => count( $sites ),
            'critical_findings' => array_sum( array_column( $sites, 'critical_count' ) ),
            'average_health'    => array_sum( array_column( $sites, 'health_score' ) ) / count( $sites ),
        ];
    }
}
```

**Free Tier Limit:** 3 sites  
**Pro Tier Limit:** Unlimited sites  
**Dashboard URL:** https://dashboard.wpshadow.com

---

### 7.5 Usage Tracking & Quota

**Classes to Create:**

#### `includes/cloud/class-usage-tracker.php` (150 LOC)
Tracks API usage against free/pro quotas.

```php
class Usage_Tracker {
    
    /**
     * Get usage statistics for current billing period
     */
    public static function get_usage_stats(): array {
        $response = Cloud_Client::request( 'GET', '/usage' );
        
        return $response ?? [
            'scans_used'     => 0,
            'scans_limit'    => 100,
            'emails_used'    => 0,
            'emails_limit'   => 50,
            'sites_used'     => 1,
            'sites_limit'    => 3,
            'period_start'   => date( 'Y-m-d' ),
            'period_end'     => date( 'Y-m-d', strtotime( '+30 days' ) ),
        ];
    }
    
    /**
     * Check if quota allows action
     */
    public static function can_perform_action( string $action ): bool {
        $usage = self::get_usage_stats();
        
        $limits = [
            'scan'  => [ 'used' => 'scans_used', 'limit' => 'scans_limit' ],
            'email' => [ 'used' => 'emails_used', 'limit' => 'emails_limit' ],
            'site'  => [ 'used' => 'sites_used', 'limit' => 'sites_limit' ],
        ];
        
        if ( ! isset( $limits[ $action ] ) ) {
            return true;
        }
        
        $limit_info = $limits[ $action ];
        return $usage[ $limit_info['used'] ] < $usage[ $limit_info['limit'] ];
    }
    
    /**
     * Display quota usage in UI
     */
    public static function render_quota_widget(): string {
        $usage = self::get_usage_stats();
        $tier = Registration_Manager::get_registration_status()['tier'];
        
        return sprintf(
            __( 'Scans: %d/%d | Emails: %d/%d | Tier: %s', 'wpshadow' ),
            $usage['scans_used'],
            $usage['scans_limit'],
            $usage['emails_used'],
            $usage['emails_limit'],
            ucfirst( $tier )
        );
    }
}
```

---

### 7.6 AJAX Commands for Cloud Integration

**Files to Create:**

#### `includes/workflow/commands/class-initiate-cloud-scan-command.php` (50 LOC)
AJAX endpoint to start cloud deep scan.

#### `includes/workflow/commands/class-get-scan-results-command.php` (40 LOC)
AJAX endpoint to retrieve completed scan results.

#### `includes/workflow/commands/class-update-notification-preferences-command.php` (50 LOC)
AJAX endpoint to save notification settings.

---

## Phase 8: Guardian & Automation

### 8.1 Scheduled Health Check System

**Classes to Create:**

#### `includes/guardian/class-guardian-manager.php` (200 LOC)
Central Guardian orchestration system.

```php
class Guardian_Manager {
    
    /**
     * Initialize Guardian system
     */
    public static function init(): void {
        // Schedule recurring checks
        if ( ! wp_next_scheduled( 'wpshadow_guardian_health_check' ) ) {
            wp_schedule_event( time(), 'hourly', 'wpshadow_guardian_health_check' );
        }
        
        if ( ! wp_next_scheduled( 'wpshadow_guardian_auto_fix' ) ) {
            wp_schedule_event( time(), 'daily', 'wpshadow_guardian_auto_fix' );
        }
    }
    
    /**
     * Run scheduled health check (cron job)
     */
    public static function run_health_check(): void {
        // Skip if disabled by user
        if ( ! get_option( 'wpshadow_guardian_enabled' ) ) {
            return;
        }
        
        // Run all diagnostics
        $registry = \WPShadow\Diagnostics\Diagnostic_Registry::get_all();
        $findings = [];
        
        foreach ( $registry as $diagnostic ) {
            if ( $diagnostic->has_issues() ) {
                $findings[] = [
                    'id'       => $diagnostic::get_id(),
                    'severity' => $diagnostic::get_severity(),
                ];
            }
        }
        
        // Log findings
        self::log_check_results( $findings );
        
        // Store for anomaly detection
        self::update_baseline( $findings );
        
        // Send notification if critical issues
        if ( self::has_critical_findings( $findings ) ) {
            Notification_Manager::send_notification( 'critical', [
                'findings' => $findings,
            ] );
        }
    }
    
    /**
     * Run scheduled auto-fixes (nightly)
     */
    public static function run_auto_fixes(): void {
        // Skip if auto-fix disabled
        if ( ! get_option( 'wpshadow_guardian_auto_fix_enabled' ) ) {
            return;
        }
        
        // Get safe fixes approved by user
        $safe_fixes = get_option( 'wpshadow_guardian_safe_fixes', [] );
        
        if ( empty( $safe_fixes ) ) {
            return;
        }
        
        foreach ( $safe_fixes as $treatment_id ) {
            // Check if treatment's diagnostic has issues
            $treatment = \WPShadow\Treatments\Treatment_Registry::get( $treatment_id );
            
            if ( $treatment && $treatment::can_apply() ) {
                // Create backup before fix
                $backup_id = wpshadow_create_backup( $treatment_id );
                
                // Apply treatment
                $result = $treatment::apply();
                
                if ( $result ) {
                    // Log fix
                    KPI_Tracker::record_treatment_applied( $treatment_id, 0 );
                    
                    // Send email report
                    self::send_fix_report( [
                        'treatment' => $treatment_id,
                        'success'   => true,
                        'backup_id' => $backup_id,
                    ] );
                } else {
                    // Log failure
                    self::log_fix_failure( $treatment_id );
                    
                    // Send error report
                    self::send_fix_report( [
                        'treatment' => $treatment_id,
                        'success'   => false,
                    ] );
                }
            }
        }
    }
    
    /**
     * Get Guardian settings
     */
    public static function get_settings(): array {
        return [
            'enabled'               => get_option( 'wpshadow_guardian_enabled', false ),
            'auto_fix_enabled'      => get_option( 'wpshadow_guardian_auto_fix_enabled', false ),
            'check_interval'        => get_option( 'wpshadow_guardian_check_interval', 'hourly' ),
            'auto_fix_time'         => get_option( 'wpshadow_guardian_auto_fix_time', '02:00' ),
            'safe_fixes'            => get_option( 'wpshadow_guardian_safe_fixes', [] ),
            'notification_enabled'  => get_option( 'wpshadow_guardian_notification_enabled', true ),
        ];
    }
    
    /**
     * Update Guardian settings
     */
    public static function update_settings( array $settings ): bool {
        update_option( 'wpshadow_guardian_enabled', (bool) $settings['enabled'] );
        update_option( 'wpshadow_guardian_auto_fix_enabled', (bool) $settings['auto_fix_enabled'] );
        update_option( 'wpshadow_guardian_check_interval', $settings['check_interval'] );
        update_option( 'wpshadow_guardian_auto_fix_time', $settings['auto_fix_time'] );
        update_option( 'wpshadow_guardian_safe_fixes', $settings['safe_fixes'] ?? [] );
        update_option( 'wpshadow_guardian_notification_enabled', (bool) $settings['notification_enabled'] );
        
        return true;
    }
}
```

---

#### `includes/guardian/class-baseline-manager.php` (150 LOC)
Maintains site baseline for anomaly detection.

```php
class Baseline_Manager {
    
    /**
     * Initialize baseline (first run)
     */
    public static function create_baseline(): void {
        $baseline = [
            'created_at'        => current_time( 'mysql' ),
            'security_findings' => 0,
            'performance_findings' => 0,
            'plugin_count'      => count( get_plugins() ),
            'active_plugins'    => count( get_option( 'active_plugins', [] ) ),
            'theme'             => wp_get_theme()->get( 'Name' ),
            'php_version'       => phpversion(),
            'wp_version'        => get_bloginfo( 'version' ),
        ];
        
        update_option( 'wpshadow_site_baseline', $baseline );
    }
    
    /**
     * Check for anomalies
     * Returns array of detected changes from baseline
     */
    public static function detect_anomalies(): array {
        $baseline = get_option( 'wpshadow_site_baseline' );
        if ( ! $baseline ) {
            self::create_baseline();
            return [];
        }
        
        $current = [
            'plugin_count' => count( get_plugins() ),
            'active_plugins' => count( get_option( 'active_plugins', [] ) ),
        ];
        
        $anomalies = [];
        
        // Detect plugin count changes
        if ( abs( $current['plugin_count'] - $baseline['plugin_count'] ) > 2 ) {
            $anomalies[] = [
                'type' => 'plugin_count_change',
                'message' => sprintf(
                    __( 'Plugin count changed from %d to %d', 'wpshadow' ),
                    $baseline['plugin_count'],
                    $current['plugin_count']
                ),
            ];
        }
        
        return $anomalies;
    }
}
```

---

### 8.2 Backup & Rollback System

**Classes to Create:**

#### `includes/guardian/class-backup-manager.php` (200 LOC)
Manages automatic backups before auto-fixes.

```php
class Backup_Manager {
    
    /**
     * Create automated backup
     * Stores option snapshots in transients for quick rollback
     */
    public static function create_automated_backup( string $reason = 'auto_fix' ): string {
        $backup_id = 'backup_' . time() . '_' . wp_generate_password( 8, false );
        
        $backup = [
            'id'        => $backup_id,
            'timestamp' => current_time( 'mysql' ),
            'reason'    => $reason,
            'options'   => self::get_critical_options(),
        ];
        
        // Store in option (4-week expiration via transient)
        set_transient( "wpshadow_{$backup_id}", $backup, WEEK_IN_SECONDS * 4 );
        
        // Also log in backup manifest
        $manifest = get_option( 'wpshadow_backup_manifest', [] );
        $manifest[] = [
            'id'        => $backup_id,
            'timestamp' => current_time( 'mysql' ),
            'reason'    => $reason,
        ];
        update_option( 'wpshadow_backup_manifest', array_slice( $manifest, -50 ) ); // Keep last 50
        
        return $backup_id;
    }
    
    /**
     * Restore from backup
     */
    public static function restore_backup( string $backup_id ): bool {
        $backup = get_transient( "wpshadow_{$backup_id}" );
        
        if ( ! $backup ) {
            return false;
        }
        
        foreach ( $backup['options'] as $option_name => $option_value ) {
            update_option( $option_name, $option_value );
        }
        
        return true;
    }
    
    /**
     * Get critical options for backup (settings that treatment might change)
     */
    private static function get_critical_options(): array {
        return [
            'siteurl'        => get_option( 'siteurl' ),
            'home'           => get_option( 'home' ),
            'permalink_structure' => get_option( 'permalink_structure' ),
            'blogname'       => get_option( 'blogname' ),
            'blogdescription' => get_option( 'blogdescription' ),
            'active_plugins' => get_option( 'active_plugins' ),
        ];
    }
}
```

---

### 8.3 Reporting & Activity Logging

**Classes to Create:**

#### `includes/guardian/class-guardian-activity-logger.php` (150 LOC)
Comprehensive logging of all Guardian actions.

```php
class Guardian_Activity_Logger {
    
    /**
     * Log health check run
     */
    public static function log_health_check( array $findings ): void {
        $log = [
            'timestamp'     => current_time( 'mysql' ),
            'type'          => 'health_check',
            'findings_count' => count( $findings ),
            'critical_count' => count( array_filter( 
                $findings,
                fn( $f ) => $f['severity'] === 'critical'
            ) ),
            'findings'      => $findings,
        ];
        
        self::append_activity_log( $log );
    }
    
    /**
     * Log treatment auto-fix
     */
    public static function log_auto_fix( string $treatment_id, bool $success ): void {
        $log = [
            'timestamp'     => current_time( 'mysql' ),
            'type'          => 'auto_fix',
            'treatment'     => $treatment_id,
            'success'       => $success,
        ];
        
        self::append_activity_log( $log );
    }
    
    /**
     * Get activity log for dashboard display
     */
    public static function get_activity_log( int $limit = 50 ): array {
        $logs = get_option( 'wpshadow_guardian_activity_log', [] );
        return array_slice( $logs, -$limit );
    }
    
    /**
     * Append to activity log (keep only 500 most recent)
     */
    private static function append_activity_log( array $entry ): void {
        $logs = get_option( 'wpshadow_guardian_activity_log', [] );
        $logs[] = $entry;
        $logs = array_slice( $logs, -500 );
        update_option( 'wpshadow_guardian_activity_log', $logs );
    }
}
```

---

### 8.4 Email Reporting

**Classes to Create:**

#### `includes/guardian/class-guardian-report-generator.php` (200 LOC)
Generates comprehensive email reports of Guardian actions.

```php
class Guardian_Report_Generator {
    
    /**
     * Generate daily report email
     */
    public static function generate_daily_report(): string {
        $report = [
            'period' => date( 'Y-m-d', strtotime( 'yesterday' ) ),
            'checks_run' => count( array_filter(
                Guardian_Activity_Logger::get_activity_log(),
                fn( $l ) => $l['type'] === 'health_check'
            ) ),
            'auto_fixes_applied' => count( array_filter(
                Guardian_Activity_Logger::get_activity_log(),
                fn( $l ) => $l['type'] === 'auto_fix' && $l['success']
            ) ),
            'findings_detected' => 0, // Count critical findings
        ];
        
        return self::render_email_template( 'daily_report', $report );
    }
    
    /**
     * Generate critical alert email
     */
    public static function generate_critical_alert( array $findings ): string {
        return self::render_email_template( 'critical_alert', [
            'findings' => $findings,
            'urgent'   => true,
        ] );
    }
    
    /**
     * Send report email
     */
    public static function send_report( string $type, array $data ): bool {
        $admin_email = get_option( 'admin_email' );
        $subject = "WPShadow Guardian Report: {$type}";
        $message = self::render_email_template( $type, $data );
        
        return wp_mail(
            $admin_email,
            $subject,
            $message,
            [ 'Content-Type: text/html; charset=UTF-8' ]
        );
    }
    
    /**
     * Render email template
     */
    private static function render_email_template( string $template, array $data ): string {
        // Load template from includes/guardian/templates/email-{$template}.php
        // Render with data
        // Return HTML
    }
}
```

---

### 8.5 AJAX Commands for Guardian

**Files to Create:**

#### `includes/workflow/commands/class-enable-guardian-command.php` (40 LOC)
AJAX to enable/disable Guardian system.

#### `includes/workflow/commands/class-configure-guardian-command.php` (50 LOC)
AJAX to update Guardian settings (check interval, auto-fix toggle, safe fixes list).

#### `includes/workflow/commands/class-restore-backup-command.php` (40 LOC)
AJAX to restore from automated backup.

---

## Implementation Sequence & Priority

### Priority 1: Foundation (Week 1-2)
1. **Registration System** (8 hours)
   - Registration_Manager
   - Cloud_Client
   - Bootstrap integration + AJAX commands
   - Test registration flow end-to-end

2. **Guardian Core** (6 hours)
   - Guardian_Manager
   - Cron job scheduling
   - Bootstrap integration

### Priority 2: Cloud Features (Week 2-3)
3. **Deep Scanning** (6 hours)
   - Deep_Scanner
   - Test scan flow

4. **Notifications** (4 hours)
   - Notification_Manager
   - Test email delivery

### Priority 3: Guardian Features (Week 3-4)
5. **Auto-Fix System** (6 hours)
   - Backup_Manager
   - Guardian auto-fix in Guardian_Manager::run_auto_fixes()
   - Test backup/restore

6. **Reporting & Logging** (4 hours)
   - Guardian_Activity_Logger
   - Guardian_Report_Generator
   - Test report generation

### Priority 4: UI Integration (Week 4-5)
7. **Dashboard Widgets** (8 hours)
   - Cloud registration prompt
   - Guardian control panel
   - Activity log display
   - Usage quota display

8. **Settings Pages** (6 hours)
   - Cloud settings page
   - Guardian configuration page
   - Backup restore page

---

## Data Storage Strategy

### wp_options for Cloud:
- `wpshadow_cloud_token` - API authentication
- `wpshadow_site_id` - Cloud service identifier
- `wpshadow_subscription_tier` - free/pro
- `wpshadow_notification_preferences` - Email/webhook settings
- `wpshadow_registered_sites_list` - Cached site list (transient, 1h)
- `wpshadow_cloud_scan_{scan_id}` - Scan results cache

### wp_options for Guardian:
- `wpshadow_guardian_enabled` - Master toggle
- `wpshadow_guardian_auto_fix_enabled` - Auto-fix toggle
- `wpshadow_guardian_check_interval` - Cron frequency
- `wpshadow_guardian_auto_fix_time` - Scheduled fix time
- `wpshadow_guardian_safe_fixes` - Array of treatment IDs to auto-apply
- `wpshadow_guardian_notification_enabled` - Email alerts toggle
- `wpshadow_site_baseline` - Site baseline snapshot
- `wpshadow_backup_manifest` - List of available backups (keep 50)
- `wpshadow_guardian_activity_log` - Activity history (keep 500 entries)

### Transients for Cloud:
- `wpshadow_backup_{id}` - Backup snapshots (4-week expiration)
- `wpshadow_registered_sites_list` - Site list cache (1-hour)

---

## Security & Privacy Considerations

### Cloud Integration:
- ✅ API token stored encrypted in options (use wp_option with serialized array)
- ✅ HTTPS required for all cloud API calls
- ✅ Rate limiting on API endpoints
- ✅ Consent-first: User explicitly registers before cloud features
- ✅ Data minimization: Only send necessary findings to cloud
- ✅ User can unregister anytime, deleting cloud data

### Guardian Automation:
- ✅ Only auto-fixes marked as "safe" by user
- ✅ Always create backup before auto-fix
- ✅ Email report on every auto-fix action
- ✅ Undo available for 4 weeks after auto-fix
- ✅ Users can disable auto-fix anytime
- ✅ Activity log audit trail for transparency

---

## Testing Checklist

### Unit Tests (Per Class):
- [ ] Registration_Manager: register, unregister, get_status
- [ ] Cloud_Client: request, error handling, retries
- [ ] Deep_Scanner: initiate, gather findings, get results
- [ ] Notification_Manager: get/set preferences, send notification
- [ ] Guardian_Manager: health check, auto-fix, settings
- [ ] Baseline_Manager: create, detect anomalies
- [ ] Backup_Manager: create, restore backup

### Integration Tests:
- [ ] Complete registration flow (UI → cloud API → local storage)
- [ ] Complete deep scan flow (initiate → gather → send → receive)
- [ ] Complete auto-fix flow (backup → apply → report → restore)
- [ ] Cron job scheduling and execution
- [ ] Email notifications delivery
- [ ] Quota enforcement and limits

### Security Tests:
- [ ] API token not exposed in logs
- [ ] Nonce verification on all AJAX
- [ ] Capability checks enforced
- [ ] No SQL injection via sanitization
- [ ] No XSS in output escaping

### Performance Tests:
- [ ] Registration API call < 2s
- [ ] Deep scan initiation < 1s
- [ ] Cloud_Client retries backoff correctly
- [ ] Backup creation < 500ms
- [ ] Cron jobs don't block admin

---

## Philosophy Alignment

### Phase 7: Cloud Features
- **Commandment #2 (Free as Possible):** ✅ All local features remain free; cloud features have generous free tier
- **Commandment #3 (Register Not Pay):** ✅ Registration (free) enables cloud; payment optional for pro tier
- **Commandment #5 (Drive to KB):** ✅ Cloud results link to KB articles
- **Commandment #9 (Show Value):** ✅ KPI tracking for cloud-enhanced findings

### Phase 8: Guardian
- **Commandment #1 (Helpful Neighbor):** ✅ Proactive health management without pressure
- **Commandment #2 (Free as Possible):** ✅ Local auto-fixes always free; only cloud auto-fixes limited
- **Commandment #7 (Ridiculously Good):** ✅ Transparent automation, full audit trail
- **Commandment #9 (Show Value):** ✅ Email reports prove value delivered
- **Commandment #10 (Beyond Pure):** ✅ User controls automation, can revert anytime

---

## Success Metrics

### Phase 7:
- ✅ 50%+ of free users register within first month
- ✅ Average 2-3 cloud scans per registered user per month
- ✅ Email notification open rate > 30%
- ✅ 10% conversion from free to pro tier
- ✅ Zero data privacy complaints

### Phase 8:
- ✅ 25%+ of users enable Guardian
- ✅ Average 5-10 auto-fixes per month per user
- ✅ 95%+ success rate on auto-fixes
- ✅ Backup restore used < 1% (indicates high safety)
- ✅ Users cite "hands-off health management" as key feature

---

## Next Steps

1. **Create Cloud Base System** (4 hours)
   - Registration_Manager
   - Cloud_Client
   - Test with mock API

2. **Create Guardian Core** (3 hours)
   - Guardian_Manager
   - Schedule cron jobs

3. **Integrate AJAX Commands** (2 hours)
   - Register all new command classes
   - Update bootstrap

4. **Create Dashboard Widgets** (6 hours)
   - Registration prompt
   - Guardian control panel
   - Activity log

5. **Documentation & Testing** (4 hours)

---

*Ready to build Phase 7-8? Let's make WPShadow the most helpful WordPress guardian ever created.* 🚀

