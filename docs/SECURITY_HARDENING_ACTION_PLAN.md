# Security Hardening Action Plan

**Created:** February 1, 2026
**Priority:** CRITICAL - User Safety Depends on These Fixes

---

## 🔴 CRITICAL Issues (Fix This Week)

### 1. Code Execution via Git Webhook
**File:** `includes/admin/class-auto-deploy.php`
**Risk:** Remote Code Execution (RCE) if webhook is spoofed
**Effort:** 3-4 hours
**Status:** Not Started

**Changes Required:**
1. Add webhook IP whitelist (GitHub IP ranges)
2. Add rate limiting (max 10 deployments/hour)
3. Implement comprehensive webhook logging
4. Replace `exec()` with safer alternative
5. Add request timeout handling

**Acceptance Criteria:**
- [ ] Webhook logging shows all attempts
- [ ] Requests from non-GitHub IPs rejected
- [ ] Rate limiting enforced
- [ ] No code execution without verification
- [ ] Tests pass

**PR Template:**
```
Title: Security: Harden webhook auto-deploy endpoint

## Problem
Auto-deploy webhook vulnerable to code execution attacks.

## Solution
- Add GitHub IP whitelist
- Implement rate limiting
- Add comprehensive logging
- Replace exec() with proc_open()

## Security Impact
Prevents unauthorized code execution via webhook spoofing.

## Testing
- [x] Rate limiting blocks excess requests
- [x] Non-GitHub IPs rejected
- [x] Valid GitHub webhooks work
- [x] All attempts logged
```

---

### 2. API Keys Stored in Plaintext
**Files:**
- `includes/admin/class-auto-deploy.php` (webhook secret)
- `includes/vault/class-vault-registration.php` (Vault key)
- `includes/guardian/class-guardian-api-client.php` (Guardian key)

**Risk:** Database compromise = full API key exposure
**Effort:** 4-5 hours
**Status:** Not Started

**Changes Required:**
1. Create `Secret_Manager` class with encryption
2. Create `Secret_Audit_Log` for access tracking
3. Migrate existing secrets from plaintext
4. Add encryption to new key storage
5. Update documentation

**Implementation Steps:**

**Step 1: Create Secret_Manager Class**
```php
<?php
// includes/core/class-secret-manager.php

namespace WPShadow\Core;

class Secret_Manager {
    /**
     * Store encrypted secret
     */
    public static function store($key, $secret) {
        // Don't store empty secrets
        if (empty($secret)) {
            delete_option('_secret_' . $key);
            return true;
        }

        $encryption_key = self::get_encryption_key();
        $encrypted = self::encrypt($secret, $encryption_key);

        update_option('_secret_' . $key, $encrypted);
        return true;
    }

    /**
     * Retrieve and decrypt secret
     */
    public static function retrieve($key) {
        $encrypted = get_option('_secret_' . $key);
        if (empty($encrypted)) {
            return null;
        }

        $encryption_key = self::get_encryption_key();
        return self::decrypt($encrypted, $encryption_key);
    }

    private static function encrypt($data, $key) {
        // Implementation using openssl_encrypt
    }

    private static function decrypt($data, $key) {
        // Implementation using openssl_decrypt
    }

    private static function get_encryption_key() {
        // Derive from WordPress salts
        return AUTH_KEY . SECURE_AUTH_KEY . LOGGED_IN_KEY;
    }
}
```

**Step 2: Create Secret_Audit_Log**
```php
<?php
// includes/core/class-secret-audit-log.php

namespace WPShadow\Core;

class Secret_Audit_Log {
    public static function log_access($key_name, $action) {
        Activity_Logger::log(
            'secret_access',
            array(
                'key_name'   => $key_name,
                'action'     => $action,  // created|retrieved|updated|deleted
                'user_id'    => get_current_user_id(),
                'ip_address' => self::get_client_ip(),
            )
        );
    }

    private static function get_client_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return sanitize_text_field(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        } else {
            return sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
        }
    }
}
```

**Step 3: Update Auto_Deploy to Use Encryption**
```php
// In includes/admin/class-auto-deploy.php

// OLD:
// $secret = sanitize_text_field(wp_unslash($_POST['wpshadow_webhook_secret']));
// update_option('wpshadow_webhook_secret', $secret);

// NEW:
$secret = sanitize_text_field(wp_unslash($_POST['wpshadow_webhook_secret']));
Secret_Manager::store('webhook_secret', $secret);
Secret_Audit_Log::log_access('webhook_secret', 'updated');

// And for retrieval:
// OLD:
// $secret = get_option('wpshadow_webhook_secret', '');

// NEW:
$secret = Secret_Manager::retrieve('webhook_secret');
```

**Acceptance Criteria:**
- [ ] Secret_Manager class created and tested
- [ ] All API keys encrypted on storage
- [ ] All API keys decrypted on retrieval
- [ ] Audit log captures all key access
- [ ] Existing plaintext keys migrated
- [ ] No database exports contain plaintext keys
- [ ] Documentation updated

---

### 3. Path Traversal Vulnerabilities
**Files:**
- `includes/workflow/class-workflow-discovery.php` (line 111, 152)
- `includes/admin/ajax/generate-customization-audit-handler.php` (line 220)

**Risk:** Arbitrary file read (e.g., /etc/passwd)
**Effort:** 2-3 hours
**Status:** Not Started

**Changes Required:**
1. Add `validate_file_path()` to Security_Validator
2. Audit all `file_get_contents()` calls
3. Use `realpath()` and directory boundary checks
4. Update all file operations to use validation

**Implementation:**

```php
// Add to includes/core/class-security-validator.php

/**
 * Validate file path is within allowed directory
 *
 * @param string $file_path Path to validate
 * @param string $base_dir Optional base directory (default: WPSHADOW_PATH)
 * @return string|false Validated path or false if invalid
 */
public static function validate_file_path($file_path, $base_dir = null) {
    if (empty($file_path)) {
        return false;
    }

    if (null === $base_dir) {
        $base_dir = WPSHADOW_PATH;
    }

    // Resolve symlinks and relative paths
    $real_path = realpath($file_path);
    $real_base = realpath($base_dir);

    // Both must be valid
    if (!$real_path || !$real_base) {
        return false;
    }

    // File must be within base directory
    if (strpos($real_path, $real_base) !== 0) {
        return false;
    }

    return $real_path;
}

// Usage:
$file_path = Security_Validator::validate_file_path($_GET['file']);
if (!$file_path) {
    wp_die(__('Invalid file path', 'wpshadow'));
}

$contents = file_get_contents($file_path);
```

**Acceptance Criteria:**
- [ ] validate_file_path() implemented and tested
- [ ] All file_get_contents() calls validated
- [ ] Path traversal attempts blocked
- [ ] Tests confirm security
- [ ] Documentation updated

---

## 🟡 HIGH Priority Issues (Fix Next Sprint)

### 4. GDPR Compliance - User Data Export/Deletion
**Files:** New implementation required
**Risk:** Legal compliance
**Effort:** 6-8 hours
**Status:** Not Started

**Changes Required:**
1. Register WordPress privacy exporters
2. Register WordPress privacy erasers
3. Export all plugin data for user
4. Delete all plugin data for user
5. Document data practices

**Implementation:**

```php
<?php
// includes/core/class-gdpr-handler.php

namespace WPShadow\Core;

class GDPR_Handler {
    /**
     * Register privacy handlers on plugins_loaded
     */
    public static function init() {
        add_filter('wp_privacy_personal_data_exporters', array(__CLASS__, 'register_exporters'));
        add_filter('wp_privacy_personal_data_erasers', array(__CLASS__, 'register_erasers'));
    }

    /**
     * Register data exporters
     */
    public static function register_exporters($exporters) {
        $exporters['wpshadow'] = array(
            'exporter_friendly_name' => __('WPShadow Plugin Data', 'wpshadow'),
            'callback' => array(__CLASS__, 'export_data'),
        );
        return $exporters;
    }

    /**
     * Register data erasers
     */
    public static function register_erasers($erasers) {
        $erasers['wpshadow'] = array(
            'eraser_friendly_name' => __('WPShadow Plugin Data', 'wpshadow'),
            'callback' => array(__CLASS__, 'erase_data'),
        );
        return $erasers;
    }

    /**
     * Export user data
     */
    public static function export_data($email_address, $page = 1) {
        $user = get_user_by('email', $email_address);
        if (!$user) {
            return array('data' => array(), 'done' => true);
        }

        $data = array();

        // Export activity logs
        global $wpdb;
        $activities = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wpshadow_activity
             WHERE user_id = %d
             LIMIT %d, %d",
            $user->ID,
            ($page - 1) * 100,
            100
        ));

        foreach ($activities as $activity) {
            $data[] = array(
                'group_id'    => 'wpshadow_activities',
                'group_label' => __('WPShadow Activities', 'wpshadow'),
                'item_id'     => 'activity_' . $activity->id,
                'data'        => array(
                    array('name' => __('Action', 'wpshadow'), 'value' => $activity->action),
                    array('name' => __('Date', 'wpshadow'), 'value' => $activity->created_at),
                ),
            );
        }

        return array(
            'data' => $data,
            'done' => count($activities) < 100,
        );
    }

    /**
     * Erase user data
     */
    public static function erase_data($email_address, $page = 1) {
        $user = get_user_by('email', $email_address);
        if (!$user) {
            return array('items_removed' => 0, 'items_retained' => 0, 'messages' => array(), 'done' => true);
        }

        global $wpdb;

        // Delete activities
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}wpshadow_activity WHERE user_id = %d LIMIT 1000",
            $user->ID
        ));

        return array(
            'items_removed'  => $deleted,
            'items_retained' => 0,
            'messages'       => array(),
            'done'           => $deleted < 1000,
        );
    }
}

// Initialize
add_action('plugins_loaded', array('WPShadow\Core\GDPR_Handler', 'init'));
```

**Acceptance Criteria:**
- [ ] Data exporters registered
- [ ] Data erasers registered
- [ ] User can request export
- [ ] User can request deletion
- [ ] Privacy policy provided
- [ ] Tests confirm functionality

---

### 5. Remove Plain Functions from Settings Registry
**File:** `includes/core/class-settings-registry.php`
**Risk:** Secrets visible to any code calling `Settings_Registry::get_all()`
**Effort:** 1-2 hours
**Status:** Not Started

**Recommendation:**
- Filter out `_secret_*` keys from `get_all()`
- Never return encrypted secret values
- Add `retrieve_secret()` method instead

---

## 🟢 Medium Priority Issues (Next Month)

### 6. Error Message Sanitization
**Files:** All error handlers
**Issue:** Error messages may expose system information
**Effort:** 2-3 hours

**Pattern:**
```php
// CURRENT (RISKY):
catch (Exception $e) {
    wp_send_json_error(array('message' => $e->getMessage()));
}

// FIXED:
catch (Exception $e) {
    Error_Handler::log_error($e->getMessage(), $e, 'operation_name');
    wp_send_json_error(array('message' => __('Operation failed', 'wpshadow')));
}
```

---

### 7. Webhook Rate Limiting & Logging
**File:** `includes/admin/class-auto-deploy.php`
**Issue:** No rate limiting, minimal logging
**Effort:** 2-3 hours

**Implementation:**
```php
private static function check_rate_limit() {
    $key = 'wpshadow_webhook_rate_' . date('Y-m-d-H');
    $count = get_transient($key);

    if ($count === false) {
        $count = 0;
    }

    if ($count >= 10) {  // Max 10 per hour
        self::log_webhook('rate_limit_exceeded');
        self::send_response(429, 'Rate limit exceeded');
    }

    set_transient($key, $count + 1, 3600);
}

private static function log_webhook($status, $data = array()) {
    Activity_Logger::log('webhook_access', array(
        'status'     => $status,
        'ip_address' => self::get_client_ip(),
        'signature'  => !empty($_SERVER['HTTP_X_HUB_SIGNATURE_256']) ? 'present' : 'missing',
        'event'      => $_SERVER['HTTP_X_GITHUB_EVENT'] ?? 'unknown',
        'data'       => $data,
    ));
}
```

---

### 8. Validate External API Responses
**File:** `includes/helpers/html-fetcher-helpers.php`
**Issue:** No validation of remote content
**Effort:** 1-2 hours

**Implementation:**
```php
public static function fetch_and_cache($url) {
    $response = wp_remote_get($url, array(
        'timeout'    => 10,
        'sslverify'  => true,
        'user-agent' => 'WPShadow/' . WPSHADOW_VERSION,
    ));

    if (is_wp_error($response)) {
        Error_Handler::log_error('HTTP Error: ' . $response->get_error_message());
        return null;
    }

    $code = wp_remote_retrieve_response_code($response);
    if ($code < 200 || $code >= 300) {
        Error_Handler::log_error('HTTP ' . $code . ' from ' . $url);
        return null;
    }

    $body = wp_remote_retrieve_body($response);
    if (empty($body) || strlen($body) > 1000000) {  // 1MB limit
        Error_Handler::log_error('Invalid response size from ' . $url);
        return null;
    }

    // Sanitize before caching
    $body = wp_kses_post($body);

    set_transient('wpshadow_' . md5($url), $body, 24 * HOUR_IN_SECONDS);
    return $body;
}
```

---

## Implementation Timeline

### Week 1 (Feb 1-7)
- [ ] Fix code execution via webhook (Issues #1)
- [ ] Implement Secret_Manager (#2)
- [ ] Implement validate_file_path (#3)

### Week 2 (Feb 8-14)
- [ ] Test all security fixes
- [ ] GDPR implementation (#4)
- [ ] Deploy to production

### Week 3+ (Feb 15+)
- [ ] Error message sanitization (#6)
- [ ] Webhook logging & rate limiting (#7)
- [ ] External API validation (#8)
- [ ] Code review and penetration testing

---

## Testing Plan

### Unit Tests
```bash
composer test -- tests/unit/Security/
```

### Integration Tests
```bash
composer test -- tests/integration/Security/
```

### Security Tests
```bash
# OWASP ZAP scanning
# SQL injection tests
# Path traversal tests
# XSS tests
```

### Manual Testing
- [ ] Deploy locally with debug enabled
- [ ] Test webhook with spoofed requests
- [ ] Test API key access
- [ ] Test file path validation
- [ ] Test GDPR export/deletion

---

## Success Criteria

✅ All CRITICAL issues resolved
✅ No new vulnerabilities introduced
✅ All tests passing
✅ Code review approved
✅ Security scan clean
✅ GDPR compliant
✅ Documentation updated

---

## References

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [WordPress Security](https://developer.wordpress.org/plugins/security/)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [GDPR Compliance](https://gdpr.eu/compliance-checklist/)
