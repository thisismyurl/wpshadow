# WPShadow Security Checklist

**Quick Reference for Security Best Practices**

---

## 🔐 Before Every Code Commit

### Nonce & Capability
- [ ] AJAX handler has `check_ajax_referer()`
- [ ] Admin actions have `check_admin_referer()`
- [ ] `current_user_can('manage_options')` verified
- [ ] Multisite? Use `manage_network` instead of `manage_options`

### Input Sanitization
- [ ] All `$_POST` values sanitized with `sanitize_*()`
- [ ] All `$_GET` values sanitized with `sanitize_*()`
- [ ] Used `wp_unslash()` before sanitizing
- [ ] Correct sanitization function chosen (text, email, key, etc.)

### Output Escaping
- [ ] All dynamic HTML escaped with `esc_html()`
- [ ] All URLs escaped with `esc_url()`
- [ ] All attributes escaped with `esc_attr()`
- [ ] All JavaScript strings escaped with `esc_js()`
- [ ] User-provided HTML escaped with `wp_kses_post()`

### Database Queries
- [ ] All queries use `$wpdb->prepare()` with placeholders
- [ ] No concatenation of variables into SQL
- [ ] Custom table names use `$wpdb->prefix`
- [ ] Considered WordPress API first (get_posts, get_comments, etc.)

### File Operations
- [ ] File paths validated with `realpath()`
- [ ] Path in allowed directory (boundary check)
- [ ] Used `WP_Filesystem` API when possible
- [ ] No `eval()`, `exec()`, or `system()` without necessity
- [ ] If shell command needed, no user input in command

### Error Handling
- [ ] Generic error messages to users
- [ ] Detailed errors logged internally
- [ ] No stack traces shown to users
- [ ] Exceptions caught and handled gracefully

### API Keys & Secrets
- [ ] Not stored in plugin code (hardcoded)
- [ ] Not in version control (.gitignore)
- [ ] Stored encrypted or in environment variables
- [ ] Access logged for audit trail

---

## 🛡️ Security Code Patterns

### Pattern: Secure AJAX Handler
```php
class MyAjaxHandler extends AJAX_Handler_Base {
    public static function handle() {
        // 1. Security check
        self::verify_request('my_action', 'manage_options');

        // 2. Get & sanitize input
        $param = self::get_post_param('param_name', 'text', '', true);

        // 3. Try operation
        try {
            $result = self::do_operation($param);
            self::send_success(['data' => $result]);
        } catch (Exception $e) {
            Error_Handler::log_error($e->getMessage(), $e);
            self::send_error(__('Operation failed', 'wpshadow'));
        }
    }
}
```

### Pattern: Safe File Reading
```php
$validated_path = Security_Validator::validate_file_path($user_file);
if (!$validated_path) {
    wp_die(__('Invalid file path', 'wpshadow'));
}

// Use WP Filesystem
require_once ABSPATH . 'wp-admin/includes/file.php';
WP_Filesystem();
global $wp_filesystem;
$contents = $wp_filesystem->get_contents($validated_path);
```

### Pattern: Database Query
```php
global $wpdb;
$result = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->posts} WHERE post_author = %d AND post_status = %s",
        $user_id,
        'publish'
    )
);
// Better: Use get_posts() instead
$posts = get_posts([
    'author' => $user_id,
    'post_status' => 'publish',
]);
```

### Pattern: API Key Storage
```php
// Store (encrypted)
$key = self::get_post_param('api_key', 'text', '', true);
Secret_Manager::store('my_service_key', $key);
Secret_Audit_Log::log_access('my_service_key', 'stored');

// Retrieve (decrypted)
$key = Secret_Manager::retrieve('my_service_key');
Secret_Audit_Log::log_access('my_service_key', 'retrieved');
```

### Pattern: External API Call
```php
$response = wp_remote_get($url, [
    'timeout'    => 10,
    'sslverify'  => true,
    'user-agent' => 'WPShadow/' . WPSHADOW_VERSION,
]);

if (is_wp_error($response)) {
    Error_Handler::log_error($response->get_error_message());
    return null;
}

$code = wp_remote_retrieve_response_code($response);
if ($code !== 200) {
    Error_Handler::log_error('HTTP ' . $code);
    return null;
}

$body = wp_remote_retrieve_body($response);
$body = wp_kses_post($body);  // Sanitize if displaying
```

---

## 🚨 Red Flags - NEVER Do This

```php
// ❌ NEVER: Concatenate user input into SQL
$wpdb->query("SELECT * FROM posts WHERE ID = $post_id");

// ✅ DO: Use prepared statements
$wpdb->query($wpdb->prepare("SELECT * FROM posts WHERE ID = %d", $post_id));

// ❌ NEVER: Execute user input
eval($_POST['code']);
exec($_GET['command']);

// ✅ DO: Validate and use WordPress APIs
$action = sanitize_text_field($_POST['action']);
if (in_array($action, ['option1', 'option2'])) {
    // Safe operation
}

// ❌ NEVER: Show raw errors
try {
    operation();
} catch (Exception $e) {
    wp_send_json_error(['message' => $e->getMessage()]);
}

// ✅ DO: Log errors, show generic message
try {
    operation();
} catch (Exception $e) {
    Error_Handler::log_error($e->getMessage(), $e);
    wp_send_json_error(['message' => __('Error occurred', 'wpshadow')]);
}

// ❌ NEVER: Store secrets plaintext
update_option('my_api_key', $key);

// ✅ DO: Encrypt secrets
Secret_Manager::store('my_api_key', $key);

// ❌ NEVER: Trust file paths
$file = file_get_contents($_GET['file']);

// ✅ DO: Validate paths
$validated = Security_Validator::validate_file_path($_GET['file']);
if (!$validated) wp_die();
$file = file_get_contents($validated);

// ❌ NEVER: Skip nonce checks
if ($_POST['action'] === 'update') {
    update_option('setting', $_POST['value']);
}

// ✅ DO: Verify nonce and capability
check_admin_referer('my_action', 'nonce_field');
if (!current_user_can('manage_options')) wp_die();
update_option('setting', sanitize_text_field($_POST['value']));
```

---

## 🔍 Security Review Questions

Before marking code as "done", answer YES to all:

1. **Authentication:**
   - [ ] Nonce verified for POST/admin requests?
   - [ ] Capability checked (`manage_options`)?
   - [ ] Multisite supported?

2. **Input:**
   - [ ] All `$_POST` sanitized?
   - [ ] All `$_GET` sanitized?
   - [ ] `wp_unslash()` called before sanitizing?
   - [ ] Correct sanitization function used?

3. **Output:**
   - [ ] All dynamic output escaped?
   - [ ] HTML content uses `esc_html()`?
   - [ ] URLs use `esc_url()`?
   - [ ] Attributes use `esc_attr()`?
   - [ ] User HTML uses `wp_kses_post()`?

4. **Database:**
   - [ ] All queries use prepared statements?
   - [ ] No variable concatenation into SQL?
   - [ ] Used WordPress APIs instead of SQL?
   - [ ] Custom tables use prefix?

5. **Files:**
   - [ ] File paths validated?
   - [ ] Path boundary checked?
   - [ ] Used WP_Filesystem?
   - [ ] No code execution from user input?

6. **Errors:**
   - [ ] Errors logged internally?
   - [ ] Generic messages shown to users?
   - [ ] No sensitive data in error messages?
   - [ ] Exceptions handled gracefully?

7. **Secrets:**
   - [ ] Secrets not in code?
   - [ ] Secrets not in git history?
   - [ ] Secrets encrypted if stored?
   - [ ] API access logged?

8. **Privacy:**
   - [ ] Data collection disclosed?
   - [ ] User consent obtained?
   - [ ] Data retention policy stated?
   - [ ] User export/deletion available?

---

## 🧪 Testing Checklist

For each security feature:

```bash
# Unit tests
✅ Nonce verification works
✅ Invalid nonce rejected
✅ Capability check enforced
✅ Unauthorized users blocked
✅ Input sanitization correct
✅ Output escaping present
✅ SQL injection prevented
✅ Path traversal blocked
✅ Secrets encrypted
✅ Errors handled safely

# Integration tests
✅ Full workflow secure
✅ Permissions enforced
✅ Database secure
✅ Files protected
✅ Audit logs generated

# Manual tests
✅ Try SQL injection payloads
✅ Try path traversal paths
✅ Try XSS scripts
✅ Try CSRF attacks
✅ Check audit logs
✅ Verify error handling
```

---

## 📋 Security Scan Tools

### PHP CodeSniffer (WordPress Standards)
```bash
composer phpcs  # Check code
composer phpcbf # Auto-fix
```

### Manual Testing
```bash
# SQL Injection
?id=1' OR '1'='1
?id=1 UNION SELECT...

# Path Traversal
?file=../../etc/passwd
?file=../../../wp-config.php

# XSS
?search=<script>alert('xss')</script>
?message=<img src=x onerror=alert('xss')>

# CSRF
Modify requests, remove nonce

# Authentication bypass
Try accessing without capability check
```

---

## 📞 When to Escalate to Security Team

1. **Code Execution:** Any use of `eval()`, `exec()`, `system()`
2. **SQL Injection:** Any variable in SQL without `prepare()`
3. **Path Traversal:** File operations without validation
4. **Secrets Exposed:** API keys in code or git history
5. **GDPR Issues:** Data handling without consent
6. **Authentication Bypass:** Permissions not checked
7. **Crypto:** Custom encryption implementation
8. **Compliance:** PCI, HIPAA, SOC 2 requirements

---

## 🔗 Useful Links

- [WordPress Security Handbook](https://developer.wordpress.org/plugins/security/)
- [OWASP Cheat Sheet](https://cheatsheetseries.owasp.org/)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [WPShadow Security Audit](./SECURITY_AND_PRIVACY_AUDIT.md)
- [Action Plan](./SECURITY_HARDENING_ACTION_PLAN.md)

---

## 👥 Security Contacts

- **Lead:** Security Team
- **On-Call:** [Team Contact]
- **Urgent:** security@wpshadow.com

---

**Last Updated:** February 1, 2026
