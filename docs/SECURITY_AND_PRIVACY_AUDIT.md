# WPShadow Security & Privacy Audit

**Date:** February 1, 2026
**Scope:** Full plugin security hardening and privacy compliance review
**Status:** Active - Comprehensive analysis in progress

---

## Executive Summary

This document contains a detailed security and privacy audit of the WPShadow plugin. The goal is to ensure our users are protected from external attacks and internal vulnerabilities while maintaining full GDPR compliance and privacy-first principles.

**Key Findings:**
- ✅ **Strong Foundation:** Core security patterns well-implemented (nonce verification, capability checks, input sanitization)
- ⚠️ **Areas of Concern:** Code execution via git, secret storage, API key handling, webhook security
- 🔍 **Needs Review:** File operations, error logging, data exposure, third-party integrations

---

## 1. Authentication & Authorization

### 1.1 Current Implementation ✅

**Strengths:**
- ✅ `Security_Validator` class provides centralized auth patterns
- ✅ `AJAX_Handler_Base` enforces nonce + capability on all AJAX requests
- ✅ `verify_request()` method combines nonce and capability checks
- ✅ Multisite-aware capability checking
- ✅ Proper use of `current_user_can()` for permission checks
- ✅ `check_ajax_referer()` and `check_admin_referer()` implemented

**Evidence:**
```php
// AJAX_Handler_Base enforces both checks
protected static function verify_request($nonce_action, $capability = 'manage_options', $nonce_field = 'nonce') {
    check_ajax_referer($nonce_action, $nonce_field);
    if (!current_user_can($capability)) {
        wp_send_json_error(['message' => __('Insufficient permissions.', 'wpshadow')]);
    }
}
```

### 1.2 Vulnerabilities & Risks ⚠️

#### 🔴 CRITICAL: Code Execution via Git Command

**File:** `includes/admin/class-auto-deploy.php`
**Lines:** 153-170
**Risk Level:** CRITICAL
**Issue:** Unauthenticated git command execution

```php
// DANGEROUS: No input validation on ref parameter
exec('git fetch origin main 2>&1', $output, $return_var);  // Lines 156
exec('git pull origin main 2>&1', $output, $return_var);   // Lines 163
```

**Problems:**
1. ❌ GitHub signature verification exists BUT webhook is accessible from anywhere
2. ❌ `exec()` function used for shell commands (security risk even with validation)
3. ❌ No rate limiting on webhook calls
4. ❌ No logging of who accessed webhook, when, or why
5. ❌ Hard-coded `main` branch - could be exploited if webhook is spoofed

**Attack Vector:**
```
1. Attacker intercepts GitHub webhook data
2. Replays or forges webhook with malicious ref
3. Code execution as web server user
```

**Recommended Fixes:**
- [ ] Implement webhook IP whitelist (GitHub's IP ranges only)
- [ ] Add rate limiting (max 10 deployments per hour)
- [ ] Log all webhook attempts with timestamp, source IP, signature status
- [ ] Consider replacing `exec()` with `proc_open()` or safer alternative
- [ ] Add request timeout handling
- [ ] Verify webhook secret matches exactly (timing attack protection exists)

---

#### 🟡 HIGH: Sensitive Data Exposure - API Keys

**Files:**
- `includes/admin/class-auto-deploy.php` - Webhook secret
- `includes/vault/class-vault-registration.php` - Vault API key
- `includes/guardian/class-guardian-api-client.php` - Guardian API key

**Issue:** API keys stored in WordPress options (plain text)

**Current Storage:**
```php
// LINE 261: Auto_Deploy.php
$secret = sanitize_text_field(wp_unslash($_POST['wpshadow_webhook_secret']));
// Stored directly in options (unencrypted)
update_option('wpshadow_webhook_secret', $secret);

// VAULT_Registration.php line 144
Settings_Registry::set('vault_api_key', $api_key);

// Guardian_API_Client.php line 128
update_option('wpshadow_guardian_api_key', sanitize_text_field($api_key));
```

**Problems:**
1. ❌ Keys stored unencrypted in `wp_options` table
2. ❌ Database backups expose keys
3. ❌ WordPress database export includes keys
4. ❌ All admin users have access to these keys via option inspection
5. ❌ No audit trail of key access

**Attack Vector:**
```
1. Attacker gains database access (SQL injection, backup leak)
2. Extracts all API keys from wp_options
3. Uses keys to impersonate site or access external services
```

**Privacy Implication:**
- Vault could have access to site data without explicit user knowledge
- API usage could be logged on external services
- Key rotation difficult (manual process)

**Recommended Fixes:**
- [ ] Encrypt keys using `wp_encrypt()` before storage
- [ ] Use environment variables for production (wp-config.php constants)
- [ ] Add `_private_` prefix to option names to prevent casual inspection
- [ ] Implement key rotation with old key support
- [ ] Add audit log for API key access/changes
- [ ] Consider AWS Secrets Manager or similar for production

---

#### 🟡 HIGH: Webhook Validation & CORS

**File:** `includes/admin/class-auto-deploy.php`
**Lines:** 75-90
**Issue:** Webhook endpoint not protected with proper CORS

**Current Implementation:**
```php
// Line 74-75: No CORS headers, open to any origin
$payload = file_get_contents('php://input');

// Line 84: GitHub signature check exists
if (!self::verify_github_signature($payload)) {
    self::send_response(401, 'Invalid signature');
}
```

**Problems:**
1. ⚠️ Webhook responds to requests from any origin
2. ⚠️ `file_get_contents('php://input')` can be bypassed by body readers
3. ⚠️ No origin validation even though GitHub sends `X-GitHub-Delivery` header
4. ⚠️ No request timeout (infinite wait possible)

**Recommended Fixes:**
- [ ] Validate `X-GitHub-Delivery` UUID header
- [ ] Validate `X-GitHub-Event` is 'push'
- [ ] Add response timeout
- [ ] Log failed signature attempts
- [ ] Return generic 404 instead of 401 on auth failure (prevents scanning)

---

### 1.3 Recommendations

**Priority 1 (Immediate):**
- [ ] Disable `WPSHADOW_AUTO_DEPLOY` by default
- [ ] Add comprehensive logging to webhook attempts
- [ ] Implement webhook IP whitelist
- [ ] Encrypt stored API keys

**Priority 2 (This Sprint):**
- [ ] Add rate limiting to webhook endpoint
- [ ] Implement API key rotation mechanism
- [ ] Add security audit log for sensitive operations
- [ ] Use environment variables for all secrets in production

**Priority 3 (Next Sprint):**
- [ ] Consider `proc_open()` instead of `exec()`
- [ ] Implement key rotation automation
- [ ] Add webhook replay protection (nonce + timestamp)

---

## 2. Input Sanitization & Output Escaping

### 2.1 Current Implementation ✅

**Strong Patterns Observed:**

**Sanitization (Input):**
```php
// AJAX_Handler_Base properly sanitizes all inputs
$text    = sanitize_text_field($value);      // Text fields
$email   = sanitize_email($value);           // Email
$key     = sanitize_key($value);             // Option/meta keys
$textarea = sanitize_textarea_field($value); // Text areas
$int     = intval($value);                   // Integers
$bool    = rest_sanitize_boolean($value);    // Booleans
$url     = esc_url_raw($value);              // URLs
```

**Escaping (Output):**
```php
// Auto_Deploy.php - proper escaping
echo esc_html($webhook_url);        // HTML content
echo esc_attr($attribute_value);    // HTML attributes
echo esc_url($link);                // URLs
echo wp_kses_post($html_content);   // Safe HTML
```

### 2.2 Vulnerabilities Found ⚠️

#### 🟡 HIGH: Improper JSON Escaping

**File:** `includes/admin/class-auto-deploy.php`
**Lines:** 225-233
**Issue:** JSON response not properly escaped for HTML context

```php
// CURRENT (Line 228-232)
echo wp_json_encode(array(
    'status'  => $status_code,
    'message' => $message,
    'data'    => $data,
));
exit;
```

**Problem:**
- JSON is written directly to HTML without escaping
- If error message contains user input or special chars, could break JSON parsing
- No `Content-Type` header set to `application/json`

**Recommended Fix:**
```php
// Add header first
header('Content-Type: application/json; charset=UTF-8');

// Then encode (already done correctly)
echo wp_json_encode(array(...));
exit;
```

---

#### 🟡 MEDIUM: File Operations Without Proper Path Validation

**File:** `includes/workflow/class-workflow-discovery.php`
**Lines:** 111, 152
**Issue:** `file_get_contents()` without path validation

```php
// LINE 111: Path comes from method parameter
$file_contents = file_get_contents($file_path);

// No validation that $file_path is within allowed directory
```

**Problem:**
- Path traversal possible if `$file_path` not properly validated
- No check for symlinks
- No check for allowed directories

**Attack Vector:**
```
1. Attacker provides: ../../../../../../etc/passwd
2. File contents exposed if readable
```

**Recommended Fix:**
```php
// Always validate paths
$allowed_base = realpath(WPSHADOW_PATH);
$real_path = realpath($file_path);

if (!$real_path || strpos($real_path, $allowed_base) !== 0) {
    wp_die(__('Invalid file path', 'wpshadow'));
}

$file_contents = file_get_contents($real_path);
```

---

#### 🟡 MEDIUM: HTML Fetcher Cache Without Validation

**File:** `includes/helpers/html-fetcher-helpers.php`
**Lines:** 54-75
**Issue:** External HTML cached without sanitization

```php
// LINE 55: wp_remote_get() response not validated
$response = wp_remote_get(/* ... */);

// Cached and later output (potentially with malicious HTML)
// If admin later views cached HTML, could execute scripts
```

**Problems:**
1. HTML from external sites not sanitized before caching
2. Could cache and replay XSS payloads
3. No cache expiration validation

**Recommended Fix:**
```php
// Sanitize HTML before caching
if (is_array($response)) {
    $body = wp_remote_retrieve_body($response);
    // Sanitize against XSS
    $body = wp_kses_post($body);
    set_transient(..., $body, ...);
}
```

---

### 2.3 Recommendations

**Priority 1:**
- [ ] Add JSON `Content-Type` header in all AJAX responses
- [ ] Add path validation to all `file_get_contents()` calls
- [ ] Sanitize external HTML before caching

**Priority 2:**
- [ ] Audit all `sanitize_*` functions for correct type
- [ ] Add escaping to all error/warning messages
- [ ] Test with special characters and unicode

---

## 3. Database Security

### 3.1 Current Implementation ✅

**Strengths:**
- ✅ All database queries use `$wpdb->prepare()`
- ✅ Phase 4 refactoring replaced complex SQL with WordPress APIs
- ✅ Custom tables properly prefixed with `$wpdb->prefix`

### 3.2 Concerns Found ⚠️

#### 🟡 MEDIUM: Direct SQL for Meta Queries

**File:** `includes/admin/class-auto-deploy.php`
**Lines:** 206-209
**Issue:** While using `$wpdb->prepare()`, better approach exists

```php
// CURRENT: Direct query
$failed_requests = $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE 'activecampaign_api_error_%'"
);

// BETTER: Use get_option() with pattern
$options = wp_load_alloptions();
$failed = count(array_filter($options, function($k) {
    return strpos($k, 'activecampaign_api_error_') === 0;
}, ARRAY_FILTER_USE_KEY));
```

---

### 3.3 Recommendations

- [ ] Audit all custom table queries for prepared statements
- [ ] Replace direct SQL with WordPress APIs where possible
- [ ] Add database query logging for security events
- [ ] Implement row-level permission checks (user_id ownership)

---

## 4. File Operations & Filesystem

### 4.1 Vulnerabilities Found ⚠️

#### 🔴 CRITICAL: Path Traversal Risk

**Files:**
- `includes/workflow/class-workflow-discovery.php`
- `includes/admin/ajax/generate-customization-audit-handler.php`
- `includes/core/class-performance-impact-classifier.php`

**Issue:** File paths from user input not properly validated

```php
// workflow-discovery.php line 111
$file_contents = file_get_contents($file_path);  // $file_path from parameter

// No realpath() check, no directory boundary check
```

**Attack Vector:**
```
GET /wp-admin/admin-ajax.php?action=discover_workflow&path=../../../../../../etc/passwd
```

**Recommended Fixes:**
- [ ] Always use `realpath()` to resolve symlinks
- [ ] Verify path is within allowed directory
- [ ] Whitelist allowed file extensions
- [ ] Use `WP_Filesystem` API when possible

**Pattern to Use:**
```php
protected static function validate_file_path($file_path) {
    $allowed_dirs = array(
        realpath(WPSHADOW_PATH . 'includes'),
        realpath(WP_CONTENT_DIR),
    );

    $real_path = realpath($file_path);

    if (!$real_path) {
        return false;
    }

    foreach ($allowed_dirs as $allowed) {
        if (strpos($real_path, $allowed) === 0) {
            return $real_path;
        }
    }

    return false;
}
```

---

#### 🟡 HIGH: Insecure Temp File Handling

**Potential Issues:**
- ❌ No evidence of `wp_tempnam()` usage (using PHP `tempnam()` instead)
- ❌ Temp files may be world-readable
- ❌ No cleanup of old temp files

**Recommended Pattern:**
```php
// Use WordPress temp functions
$temp_file = wp_tempnam('wpshadow_');
if (!$temp_file) {
    wp_die(__('Cannot create temp file', 'wpshadow'));
}

// Use WP_Filesystem
require_once ABSPATH . 'wp-admin/includes/file.php';
WP_Filesystem();
global $wp_filesystem;

$wp_filesystem->put_contents($temp_file, $content, FS_CHMOD_FILE);
```

---

### 4.2 Recommendations

**Priority 1 (Critical):**
- [ ] Add `validate_file_path()` to Security_Validator
- [ ] Audit all file operations for path traversal
- [ ] Use realpath() before any file access
- [ ] Implement whitelist of allowed directories

**Priority 2:**
- [ ] Implement temp file cleanup (cron)
- [ ] Use WP_Filesystem for all file operations
- [ ] Add file operation logging

---

## 5. API Key & Secret Management

### 5.1 Current Issues ⚠️

#### 🔴 CRITICAL: Plaintext Secret Storage

**Files:**
- `includes/admin/class-auto-deploy.php` - webhook secret
- `includes/vault/class-vault-registration.php` - Vault API key
- `includes/guardian/class-guardian-api-client.php` - Guardian API key

**Problem:**
```php
// Stored unencrypted in options table
update_option('wpshadow_webhook_secret', $secret);
Settings_Registry::set('vault_api_key', $api_key);
```

**Data Exposure Paths:**
1. Database backups
2. WordPress database export
3. Admin inspection via REST API
4. Database query logs
5. System monitoring tools with database access

**Recommended Solution:**

**Option A: Environment Variables (Recommended for Production)**
```php
// .env or wp-config.php
define('WPSHADOW_WEBHOOK_SECRET', getenv('WPSHADOW_WEBHOOK_SECRET'));
define('WPSHADOW_VAULT_API_KEY', getenv('WPSHADOW_VAULT_API_KEY'));
```

**Option B: Encrypted Storage**
```php
class Secret_Manager {
    public static function store($key, $secret) {
        // Generate encryption key from site URL + WordPress salt
        $encryption_key = self::get_encryption_key();

        // Encrypt before storage
        $encrypted = self::encrypt($secret, $encryption_key);

        update_option('_encrypted_' . $key, $encrypted);
    }

    public static function retrieve($key) {
        $encrypted = get_option('_encrypted_' . $key);
        if (!$encrypted) return null;

        $encryption_key = self::get_encryption_key();
        return self::decrypt($encrypted, $encryption_key);
    }

    private static function get_encryption_key() {
        // Use WordPress auth salts
        return AUTH_KEY . SECURE_AUTH_KEY;
    }
}
```

**Option C: AWS Secrets Manager (Enterprise)**
```php
// For large deployments
$client = new SecretsManagerClient([
    'region'  => 'us-east-1',
    'version' => 'latest',
]);

$result = $client->getSecretValue([
    'SecretId' => 'wpshadow/vault-api-key',
]);

$secret = json_decode($result['SecretString'], true)['api_key'];
```

---

### 5.2 Key Access Audit Trail

**Current Issue:** No logging of API key access or changes

**Recommended Implementation:**
```php
class Secret_Audit_Log {
    public static function log_access($key_name, $action) {
        Activity_Logger::log(
            'secret_access',
            array(
                'key_name'  => $key_name,
                'action'    => $action,  // 'created'|'retrieved'|'rotated'|'deleted'
                'user_id'   => get_current_user_id(),
                'timestamp' => current_time('mysql'),
                'ip_address' => sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? ''),
            )
        );
    }
}

// Usage
Secret_Audit_Log::log_access('vault_api_key', 'created');
```

---

### 5.3 Recommendations

**Priority 1:**
- [ ] Implement `Secret_Manager` class with encryption
- [ ] Add `Secret_Audit_Log` for all key access
- [ ] Migrate existing plaintext keys
- [ ] Disable storing secrets in options for new keys

**Priority 2:**
- [ ] Implement key rotation mechanism
- [ ] Add key expiration dates
- [ ] Support for AWS Secrets Manager

**Priority 3:**
- [ ] Dashboard warning if secrets stored plaintext
- [ ] Diagnostic for detecting plaintext secrets
- [ ] Key strength validation

---

## 6. Error Handling & Logging

### 6.1 Current Implementation ✅

**Strengths:**
- ✅ `Error_Handler` class for centralized error management
- ✅ Activity logging for important actions
- ✅ WP_DEBUG aware

### 6.2 Vulnerabilities Found ⚠️

#### 🟡 HIGH: Information Disclosure Through Error Messages

**Files:**
- All AJAX handlers
- Error messages exposed in HTTP responses

**Issue:** Error messages may reveal system information

```php
// EXAMPLE: If exception occurs
catch (Exception $e) {
    wp_send_json_error(array(
        'message' => $e->getMessage(),  // May expose paths, database info
    ));
}
```

**Attack Vector:**
```
1. Attacker triggers error condition
2. Error message reveals database structure, file paths
3. Information used for subsequent attacks
```

**Recommended Fix:**
```php
try {
    // risky operation
} catch (Exception $e) {
    // Log detailed error internally
    Error_Handler::log_error($e->getMessage(), $e, 'context_name');

    // Send generic message to user
    wp_send_json_error(array(
        'message' => __('An error occurred. Please try again.', 'wpshadow'),
        // Don't include: $e->getMessage(), file, line, trace
    ));
}
```

---

#### 🟡 MEDIUM: Debug Log Information Disclosure

**File:** `includes/diagnostics/diagnostics_backup/tests/developer/class-diagnostic-wp-debug-status.php`
**Line:** 146
**Issue:** Debug log contents may be displayed to authenticated users

```php
// LINE 146: Debug log exposed
'Via WP-CLI' => 'wp eval "echo file_get_contents(WP_CONTENT_DIR . \'/debug.log\');"',
```

**Problem:**
- Debug logs can contain sensitive info (database queries, API keys, user data)
- Even limited to admins, still a risk

**Recommended Fix:**
```php
// Only show debug log if WP_DEBUG enabled AND WP_DEBUG_LOG disabled
if (defined('WP_DEBUG') && WP_DEBUG && !defined('WP_DEBUG_LOG')) {
    // Show warning
} else {
    // Don't show debug log contents
}

// Never read file directly, use WordPress functions
if (file_exists(WP_CONTENT_DIR . '/debug.log')) {
    $can_read = wp_filesystem->exists(WP_CONTENT_DIR . '/debug.log');
}
```

---

### 6.3 Recommendations

**Priority 1:**
- [ ] Generic error messages to users, detailed logging internally
- [ ] Implement structured logging (Sentry integration optional)
- [ ] Add error message sanitization

**Priority 2:**
- [ ] Audit Activity_Logger for sensitive data leakage
- [ ] Implement log rotation and cleanup
- [ ] Add privacy-aware logging

---

## 7. Third-Party Integration Security

### 7.1 Current Issues ⚠️

#### 🟡 HIGH: Guardian API Integration - Data Sent to External Service

**File:** `includes/guardian/class-guardian-api-client.php`
**Lines:** Site data sent to Guardian servers

**Issue:** User data sent to external service without explicit consent

```php
// What data is sent?
$site_data = self::prepare_site_data();  // Line not shown

// Where is it sent?
const API_BASE_URL = 'https://vault.wpshadow.com/api/v1';
```

**Questions:**
1. ❓ What exact data is sent to Guardian?
2. ❓ Is consent captured from user?
3. ❓ Is data encrypted in transit?
4. ❓ What is Guardian's data retention policy?
5. ❓ Is user data removed if key revoked?

**Recommended Audit:**
- [ ] Document `prepare_site_data()` - exactly what is sent
- [ ] Add explicit consent prompt before first scan
- [ ] Require HTTPS for all API calls
- [ ] Add API response validation and sanitization
- [ ] Implement request signing (prevent MITM attacks)

---

#### 🟡 HIGH: Vault Integration - User Data Backup Storage

**File:** `includes/vault/class-vault-registration.php`

**Security Questions:**
1. ❓ Where is backup data stored?
2. ❓ Is it encrypted at rest?
3. ❓ Is it encrypted in transit?
4. ❓ What happens if API key is compromised?
5. ❓ Can Vault access backups without API key?
6. ❓ What is data retention policy if account deleted?

**Recommended Audit:**
- [ ] Document Vault data handling
- [ ] Verify end-to-end encryption
- [ ] Add encryption key derivation from site (user-specific)
- [ ] Implement backup integrity verification
- [ ] Add backup download/restore security

---

#### 🟡 MEDIUM: wp_remote_get() Without Validation

**File:** `includes/helpers/html-fetcher-helpers.php`
**Lines:** 54-75

**Issues:**
1. ❌ No timeout set (default 5 seconds may not be enough)
2. ❌ No SSL verification by default
3. ❌ Response not validated before caching
4. ❌ No error handling for connection failures

**Recommended Fix:**
```php
$response = wp_remote_get(
    $url,
    array(
        'timeout'    => 10,                    // Explicit timeout
        'sslverify'  => true,                  // Verify SSL
        'user-agent' => 'WPShadow/' . WPSHADOW_VERSION,
    )
);

if (is_wp_error($response)) {
    // Log error
    Error_Handler::log_error($response->get_error_message());
    return null;
}

$code = wp_remote_retrieve_response_code($response);
if ($code < 200 || $code >= 300) {
    // Not success
    return null;
}

$body = wp_remote_retrieve_body($response);
if (empty($body)) {
    return null;
}

// Sanitize before caching
$body = wp_kses_post($body);
```

---

### 7.2 Recommendations

**Priority 1:**
- [ ] Document all external API integrations
- [ ] Create External_API_Audit table in docs
- [ ] Add user consent dialogs for data sharing
- [ ] Verify HTTPS for all external calls

**Priority 2:**
- [ ] Implement API response signing/verification
- [ ] Add data minimization (send only necessary fields)
- [ ] Implement user opt-out for external services

---

## 8. Privacy & GDPR Compliance

### 8.1 Current Implementation ✅

**Good Patterns:**
- ✅ Activity logging exists
- ✅ Options storage (not random custom tables)
- ✅ Some diagnostic checks for privacy

### 8.2 Gaps Found ⚠️

#### 🟡 HIGH: No Privacy Policy References

**Issue:** Plugin collects data but no privacy policy links in admin UI

**Missing:**
- [ ] Privacy policy link in plugin description
- [ ] "Why we collect this" explanations
- [ ] Data retention policies displayed
- [ ] User data export functionality
- [ ] User data deletion functionality

#### 🟡 MEDIUM: Activity Logger - Data Retention Not Defined

**File:** `includes/core/class-activity-logger.php`

**Issues:**
1. ❓ How long is activity data retained?
2. ❓ Is personal data logged (IP addresses, etc.)?
3. ❓ Can users request deletion?

**Recommended Implementation:**
```php
class Activity_Logger {
    const DEFAULT_RETENTION_DAYS = 90;  // GDPR-friendly default

    public static function cleanup_old_entries() {
        $cutoff_date = date('Y-m-d H:i:s', strtotime('-' . self::DEFAULT_RETENTION_DAYS . ' days'));

        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}wpshadow_activities WHERE created_at < %s",
            $cutoff_date
        ));
    }
}

// Schedule cleanup
add_action('wpshadow_cleanup_activities', 'WPShadow\Core\Activity_Logger::cleanup_old_entries');

// One-time schedule
if (!wp_next_scheduled('wpshadow_cleanup_activities')) {
    wp_schedule_event(time(), 'daily', 'wpshadow_cleanup_activities');
}
```

---

#### 🟡 MEDIUM: Backup Data Privacy

**Files:**
- `includes/monitoring/recovery/class-backup-manager.php`
- `includes/vault/class-vault-registration.php`

**Questions:**
1. ❓ Does backup include user data (posts, comments)?
2. ❓ Is backup encrypted?
3. ❓ Who has access to backups?
4. ❓ Are PII fields masked in backups?

**Recommended:**
- [ ] Document what data is backed up
- [ ] Implement opt-out for sensitive data
- [ ] Encrypt backups with user-specific key
- [ ] Add backup data manifest (what's included)

---

### 8.3 GDPR Data Requests Implementation

**Missing:** User data export/deletion (required by GDPR)

```php
class GDPR_Handler {
    /**
     * Export all plugin data for a user
     */
    public static function export_user_data($user_id) {
        $export = array();

        // Activity logs
        $export['activities'] = Activity_Logger::get_for_user($user_id);

        // Settings
        $export['settings'] = Settings_Registry::get_all();

        // Backups
        $export['backups'] = Backup_Manager::get_for_user($user_id);

        return $export;
    }

    /**
     * Delete all plugin data for a user
     */
    public static function delete_user_data($user_id) {
        Activity_Logger::delete_for_user($user_id);
        Settings_Registry::delete_for_user($user_id);
        Backup_Manager::delete_for_user($user_id);
    }
}

// Register with WordPress
register_rest_route('wpshadow/v1', '/gdpr/export', array(
    'methods' => 'POST',
    'callback' => 'WPShadow\Core\GDPR_Handler::export_user_data',
    'permission_callback' => function() {
        return current_user_can('manage_options');
    },
));
```

---

### 8.4 Recommendations

**Priority 1 (Required for Legal Compliance):**
- [ ] Add privacy policy link to plugin
- [ ] Implement user data export (`wp_privacy_personal_data_exporters`)
- [ ] Implement user data deletion (`wp_privacy_personal_data_erasers`)
- [ ] Document data collection practices
- [ ] Add consent for external API integrations

**Priority 2:**
- [ ] Define and implement data retention policies
- [ ] Add automatic cleanup of old activity logs
- [ ] Implement right to be forgotten (anonymization)
- [ ] Add privacy-aware diagnostic checks

---

## 9. Security Hardening Checklist

### Immediate (This Week)
- [ ] Disable `WPSHADOW_AUTO_DEPLOY` by default
- [ ] Add webhook logging and rate limiting
- [ ] Implement `Secret_Manager` for API key encryption
- [ ] Add `validate_file_path()` to Security_Validator
- [ ] Add privacy policy links to plugin

### Short-term (This Sprint)
- [ ] Encrypt stored secrets
- [ ] Implement GDPR data export/deletion
- [ ] Add comprehensive error message sanitization
- [ ] Audit all external API calls
- [ ] Implement webhook IP whitelist

### Medium-term (This Month)
- [ ] Implement API key rotation
- [ ] Add security diagnostic checks
- [ ] Implement Secret_Audit_Log
- [ ] Add OWASP security headers
- [ ] Penetration test critical flows

### Long-term (Q2 2026)
- [ ] Support AWS Secrets Manager
- [ ] Implement WAF integration
- [ ] Add advanced threat detection
- [ ] Security audit by external firm

---

## 10. Security & Privacy by Feature

### 10.1 Diagnostics
**Status:** ✅ Generally secure
**Concern:** Some diagnostics read sensitive files (debug logs, config)
**Fix:** Add explicit user consent, mask sensitive data

### 10.2 Treatments
**Status:** ⚠️ File modifications risky
**Concern:** Could modify system files, .htaccess, wp-config
**Fix:** Backup before modification, implement dry-run, add undo capability

### 10.3 AJAX Handlers
**Status:** ✅ Strong nonce + capability checks
**Concern:** Some handlers process file uploads, external URLs
**Fix:** Validate all file types, restrict URLs, rate limit

### 10.4 Dashboard
**Status:** ⚠️ May display sensitive data
**Concern:** Activity logs show operations, settings show API keys
**Fix:** Mask API keys, add privacy controls

### 10.5 Settings
**Status:** 🔴 Store secrets plaintext
**Concern:** API keys, webhook secrets unencrypted
**Fix:** Implement `Secret_Manager` with encryption

### 10.6 Backups & Recovery
**Status:** ⚠️ Unclear data handling
**Concern:** What data is included, where stored
**Fix:** Document, audit, implement encryption

---

## 11. Useful Security Patterns to Implement

### Pattern 1: Secure AJAX Handler
```php
class Secure_AJAX_Handler extends AJAX_Handler_Base {
    public static function handle() {
        // 1. Verify security
        self::verify_request('action_name', 'manage_options');

        // 2. Sanitize inputs
        $param = self::get_post_param('param', 'text', '', true);

        // 3. Log action
        Activity_Logger::log('action_performed', array(
            'param' => $param,
            'user_id' => get_current_user_id(),
        ));

        // 4. Try operation
        try {
            $result = self::do_something($param);
            self::send_success(array('data' => $result));
        } catch (Exception $e) {
            Error_Handler::log_error($e->getMessage(), $e);
            self::send_error(__('Operation failed', 'wpshadow'));
        }
    }
}
```

### Pattern 2: Safe File Operations
```php
public static function safe_file_read($file_path) {
    // Validate path
    $validated_path = Security_Validator::validate_file_path($file_path);
    if (!$validated_path) {
        return wp_error('invalid_path', __('Invalid file path', 'wpshadow'));
    }

    // Use WP Filesystem
    require_once ABSPATH . 'wp-admin/includes/file.php';
    WP_Filesystem();
    global $wp_filesystem;

    if (!$wp_filesystem->exists($validated_path)) {
        return wp_error('not_found', __('File not found', 'wpshadow'));
    }

    return $wp_filesystem->get_contents($validated_path);
}
```

### Pattern 3: Secret Management
```php
public static function store_secret($key, $secret) {
    $encrypted = Secret_Manager::encrypt($secret);
    update_option('_encrypted_' . $key, $encrypted);
    Secret_Audit_Log::log_access($key, 'stored');
}

public static function get_secret($key) {
    $encrypted = get_option('_encrypted_' . $key);
    if (!$encrypted) return null;

    Secret_Audit_Log::log_access($key, 'retrieved');
    return Secret_Manager::decrypt($encrypted);
}
```

---

## 12. References

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [WordPress Security Documentation](https://developer.wordpress.org/plugins/security/)
- [GDPR Compliance](https://gdpr.eu/)
- [WordPress Nonces](https://developer.wordpress.org/plugins/security/nonces/)
- [Prepared Statements](https://developer.wordpress.org/plugins/security/securing-output/)

---

## Next Steps

1. **Review Phase:** Team reviews findings
2. **Prioritization:** Assign risk levels to each finding
3. **Implementation:** Fix critical issues first
4. **Testing:** Security testing after fixes
5. **Documentation:** Update security documentation

---

**Document Created:** February 1, 2026
**Last Updated:** February 1, 2026
**Status:** Draft - Awaiting Review
