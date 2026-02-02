# WPShadow Security Best Practices - Quick Reference

## 🛡️ Security First Mindset

When writing code for WPShadow, **security is non-negotiable**. Follow these patterns religiously.

---

## 📝 Input Validation & Sanitization

### Always Sanitize User Input

```php
// ✅ CORRECT: Sanitize based on data type
$text  = sanitize_text_field( wp_unslash( $_POST['field'] ) );
$email = sanitize_email( wp_unslash( $_POST['email'] ) );
$url   = esc_url_raw( wp_unslash( $_POST['url'] ) );
$key   = sanitize_key( $_POST['key'] );
$int   = absint( $_POST['count'] );
$bool  = rest_sanitize_boolean( $_POST['enabled'] );

// ❌ WRONG: Never use raw input
$value = $_POST['field'];
```

### Use AJAX_Handler_Base Helper

```php
// ✅ CORRECT: Built-in sanitization
$value = self::get_post_param( 'field', 'text', '', true );
$count = self::get_post_param( 'count', 'int', 0 );
$flag  = self::get_post_param( 'flag', 'bool', false );

// Types: 'text', 'email', 'url', 'int', 'bool', 'array', 'textarea'
```

---

## 🗄️ SQL Security

### Always Use Prepared Statements

```php
// ✅ CORRECT: Use $wpdb->prepare()
$results = $wpdb->get_results( 
    $wpdb->prepare( 
        "SELECT * FROM {$wpdb->posts} WHERE post_status = %s AND post_date > %s",
        $status,
        $date
    )
);

// ❌ WRONG: Direct variable interpolation
$results = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_status = '{$status}'" );
```

### Table Name Validation

```php
// ✅ CORRECT: Validate and escape table names
use WPShadow\Core\Security_Hardening;

if ( ! Security_Hardening::is_valid_table_name( $table ) ) {
    return new \WP_Error( 'invalid_table', 'Invalid table name' );
}

$escaped_table = Security_Hardening::sanitize_table_name( $table );
$wpdb->query( "DROP TABLE IF EXISTS `{$escaped_table}`" );

// ❌ WRONG: Direct table name usage
$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" );
```

---

## 🔐 Authentication & Authorization

### Always Check Nonces

```php
// ✅ CORRECT: Verify nonce
if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'action_name' ) ) {
    wp_send_json_error( array( 'message' => __( 'Security check failed', 'wpshadow' ) ) );
}

// ✅ CORRECT: Use AJAX_Handler_Base
self::verify_request( 'wpshadow_action', 'manage_options' );

// ❌ WRONG: No nonce verification
$value = $_POST['value']; // Dangerous!
```

### Always Check Capabilities

```php
// ✅ CORRECT: Check proper capability
if ( ! current_user_can( 'manage_options' ) ) {
    wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
}

// ❌ WRONG: Using 'read' for admin operations
if ( ! current_user_can( 'read' ) ) { ... } // Too permissive!
```

### Capability Reference

| Operation | Capability | Who Has It |
|-----------|-----------|------------|
| View reports | `'read'` | All logged-in users |
| Admin settings | `'manage_options'` | Administrators |
| Execute workflows | `'manage_options'` | Administrators |
| Apply treatments | `'manage_options'` | Administrators |
| Network settings | `'manage_network_options'` | Network Admins |

---

## 🎨 Output Escaping

### Context-Aware Escaping

```php
// ✅ CORRECT: Escape for context
<div class="notice">
    <p><?php echo esc_html( $message ); ?></p>
    <a href="<?php echo esc_url( $link ); ?>" 
       class="button"
       data-value="<?php echo esc_attr( $value ); ?>">
        <?php echo esc_html( $button_text ); ?>
    </a>
</div>

// ✅ CORRECT: JavaScript values
<script>
const config = <?php echo wp_json_encode( $config ); ?>;
const enabled = <?php echo wp_json_encode( $is_enabled ); ?>;
</script>

// ❌ WRONG: No escaping
<p><?php echo $message; ?></p>
<script>const value = '<?php echo $value; ?>';</script>
```

### Escaping Function Reference

| Context | Function | Example |
|---------|----------|---------|
| HTML content | `esc_html()` | `<p><?php echo esc_html( $text ); ?></p>` |
| HTML attribute | `esc_attr()` | `<div class="<?php echo esc_attr( $class ); ?>">` |
| URL | `esc_url()` | `<a href="<?php echo esc_url( $link ); ?>">` |
| JavaScript | `wp_json_encode()` | `const x = <?php echo wp_json_encode( $val ); ?>;` |
| Rich HTML | `wp_kses_post()` | `<?php echo wp_kses_post( $html ); ?>` |

---

## 📁 File Operations

### Use WordPress Filesystem API

```php
// ✅ CORRECT: WordPress Filesystem API
require_once ABSPATH . 'wp-admin/includes/file.php';
WP_Filesystem();
global $wp_filesystem;

$wp_filesystem->put_contents( 
    $file_path, 
    $content, 
    FS_CHMOD_FILE 
);

// ❌ WRONG: Direct file operations
file_put_contents( $file_path, $content );
```

### Validate File Paths

```php
// ✅ CORRECT: Path validation
use WPShadow\Core\Security_Hardening;

$upload_dir = wp_upload_dir();
if ( ! Security_Hardening::is_path_within_directory( $path, $upload_dir['basedir'] ) ) {
    return new \WP_Error( 'invalid_path', 'Path outside allowed directory' );
}

// ❌ WRONG: No validation
$file = $base_dir . '/' . $user_input; // Directory traversal risk!
```

---

## 🚦 Rate Limiting

### Protect Sensitive Operations

```php
// ✅ CORRECT: Rate limiting
use WPShadow\Core\Security_Hardening;

if ( ! Security_Hardening::check_rate_limit( 'wpshadow_login', 5, 300 ) ) {
    wp_send_json_error( array( 'message' => __( 'Too many attempts. Try again later.', 'wpshadow' ) ) );
}

// Parameters: ($action, $max_attempts, $period_seconds)
```

---

## 🔑 Token & Secret Management

### Hash Sensitive Tokens

```php
// ✅ CORRECT: Hash tokens before storage
use WPShadow\Core\Security_Hardening;

$token = bin2hex( random_bytes( 32 ) );
$hash  = Security_Hardening::hash_token( $token );

// Store only the hash
update_option( 'my_token_hash', $hash );

// Later: Verify token
if ( Security_Hardening::verify_token( $user_token, $stored_hash ) ) {
    // Valid
}

// ❌ WRONG: Store tokens in plain text
update_option( 'my_token', $token ); // Vulnerable if DB compromised
```

### Use Secret_Manager for Sensitive Data

```php
// ✅ CORRECT: Encrypted storage
\WPShadow\Core\Secret_Manager::store( 'api_key', $api_key );
$api_key = \WPShadow\Core\Secret_Manager::retrieve( 'api_key' );

// ❌ WRONG: Plain text storage
update_option( 'api_key', $api_key ); // Visible in database
```

---

## 🛡️ PHP Code Validation

### Use token_get_all() Not exec()

```php
// ✅ CORRECT: Safe syntax validation
$tokens = @token_get_all( $code );
if ( false === $tokens ) {
    return array( 'valid' => false, 'error' => 'Syntax error' );
}

// Check for dangerous functions
use WPShadow\Core\Security_Hardening;
$dangerous = Security_Hardening::scan_for_dangerous_functions( $code );
if ( ! empty( $dangerous ) ) {
    return array( 'valid' => false, 'error' => 'Dangerous functions detected' );
}

// ❌ WRONG: Shell execution
exec( 'php -l ' . escapeshellarg( $temp_file ), $output, $return_var );
```

---

## 🌐 HTTP Request Validation

### Validate Request Source

```php
// ✅ CORRECT: Check request safety
use WPShadow\Core\Security_Hardening;

if ( ! Security_Hardening::is_safe_request() ) {
    // Log suspicious activity
    Error_Handler::log_warning( 'Suspicious request detected' );
}

// Validate GitHub webhook IPs
$ip = Security_Hardening::get_client_ip();
if ( ! Security_Hardening::is_github_ip( $ip ) ) {
    wp_die( 'Forbidden', 403 );
}
```

---

## 🔒 Security Headers

### Add Protection Headers

```php
// ✅ CORRECT: Security headers
use WPShadow\Core\Security_Hardening;

add_action( 'send_headers', function() {
    Security_Hardening::add_security_headers();
} );

// Sets:
// - X-Frame-Options: SAMEORIGIN
// - X-Content-Type-Options: nosniff
// - X-XSS-Protection: 1; mode=block
// - Referrer-Policy: strict-origin-when-cross-origin
// - Content-Security-Policy: frame-ancestors 'self'
```

---

## 📋 Security Checklist for New Features

Before submitting code, verify:

### Input
- [ ] All `$_POST` / `$_GET` / `$_REQUEST` values sanitized
- [ ] `wp_unslash()` called before sanitization
- [ ] Type-specific sanitization used
- [ ] AJAX handlers use `get_post_param()` helper

### SQL
- [ ] All queries use `$wpdb->prepare()`
- [ ] Table names validated and escaped
- [ ] No direct variable interpolation in queries
- [ ] PHPDoc comments explain why prepare() not used (if applicable)

### Authentication
- [ ] Nonces verified on all requests
- [ ] Capabilities checked appropriately
- [ ] `manage_options` used for admin operations
- [ ] No `nopriv` hooks without justification

### Output
- [ ] HTML content escaped with `esc_html()`
- [ ] Attributes escaped with `esc_attr()`
- [ ] URLs escaped with `esc_url()`
- [ ] JavaScript values use `wp_json_encode()`

### Files
- [ ] WordPress Filesystem API used
- [ ] Paths validated within allowed directories
- [ ] `wp_normalize_path()` used
- [ ] No direct `file_get_contents()` / `file_put_contents()`

### Tokens
- [ ] Sensitive tokens hashed before storage
- [ ] `Security_Hardening::hash_token()` used
- [ ] Comparison uses `hash_equals()`

### Rate Limiting
- [ ] Sensitive endpoints have rate limits
- [ ] `Security_Hardening::check_rate_limit()` used
- [ ] Violations logged

---

## 🔍 Common Vulnerabilities to Avoid

### SQL Injection
```php
// ❌ Vulnerable
$wpdb->query( "SELECT * FROM {$table} WHERE id = {$id}" );

// ✅ Secure
$wpdb->query( $wpdb->prepare( "SELECT * FROM %i WHERE id = %d", $table, $id ) );
```

### XSS (Cross-Site Scripting)
```php
// ❌ Vulnerable
echo "<div>" . $user_input . "</div>";

// ✅ Secure
echo '<div>' . esc_html( $user_input ) . '</div>';
```

### CSRF (Cross-Site Request Forgery)
```php
// ❌ Vulnerable (no nonce)
if ( isset( $_POST['action'] ) ) { do_something(); }

// ✅ Secure (with nonce)
if ( wp_verify_nonce( $_POST['nonce'], 'action_name' ) ) { do_something(); }
```

### Path Traversal
```php
// ❌ Vulnerable
$file = $base_dir . '/' . $_GET['file'];

// ✅ Secure
$file = $base_dir . '/' . basename( $_GET['file'] );
if ( ! Security_Hardening::is_path_within_directory( $file, $base_dir ) ) {
    wp_die( 'Invalid path' );
}
```

### Command Injection
```php
// ❌ Vulnerable
exec( 'php -l ' . $file );

// ✅ Secure
$tokens = token_get_all( file_get_contents( $file ) );
```

---

## 📚 Additional Resources

**WordPress Security:**
- [WordPress Security White Paper](https://wordpress.org/about/security/)
- [Plugin Security Best Practices](https://developer.wordpress.org/plugins/security/)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)

**WPShadow Documentation:**
- [Security Audit Report](../SECURITY_AUDIT_2026-02-02.md)
- [Security Hardening Class](../../includes/core/class-security-hardening.php)
- [Coding Standards](../CODING_STANDARDS.md)

---

## ⚡ Quick Reference Card

```php
// Input Validation
$text = sanitize_text_field( wp_unslash( $_POST['field'] ) );
$int  = absint( $_POST['count'] );

// SQL Security
$results = $wpdb->get_results( $wpdb->prepare( "SELECT * WHERE id = %d", $id ) );

// Authentication
wp_verify_nonce( $_POST['nonce'], 'action' );
current_user_can( 'manage_options' );

// Output Escaping
echo esc_html( $text );
echo esc_url( $url );
echo wp_json_encode( $data );

// File Operations
WP_Filesystem();
$wp_filesystem->put_contents( $path, $content );

// Security Utilities
Security_Hardening::check_rate_limit( 'action', 10, 60 );
Security_Hardening::is_path_within_directory( $path, $base );
Security_Hardening::hash_token( $token );
```

---

**Last Updated:** February 2, 2026  
**Security Score:** 9.5/10  
**Status:** Production-Ready ✅
