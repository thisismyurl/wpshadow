# SameSite Cookie Attribute Implementation

## Overview

This document describes the implementation of SameSite cookie attributes in the WPShadow plugin to prevent Cross-Site Request Forgery (CSRF) attacks.

## Security Issue

Without the SameSite attribute, browsers will send cookies along with cross-site requests, making the application vulnerable to CSRF attacks. An attacker could potentially:

- Create a malicious website that makes requests to your WordPress site
- If a logged-in user visits the malicious site, their cookies would be sent with those requests
- The malicious site could perform actions on behalf of the user without their knowledge

## Solution

All session cookies now include the `SameSite=Lax` attribute, which instructs browsers to:

1. **Block cookies on cross-site POST requests** - Prevents CSRF attacks
2. **Allow cookies on same-site requests** - Normal functionality works as expected
3. **Allow cookies on top-level navigation** - Users can still click links to navigate

## Implementation Details

### Modified Files

1. **includes/features/class-wps-feature-troubleshooting-mode.php**
   - Cookie: `wpshadow_troubleshooting_mode`
   - Purpose: Identifies users in troubleshooting mode
   - Updated in methods: `ajax_start_troubleshooting()`, `ajax_stop_troubleshooting()`

2. **includes/features/class-wps-feature-conflict-sandbox.php**
   - Cookie: `wpshadow_conflict_sandbox`
   - Purpose: Tracks sandbox session for conflict isolation
   - Updated in methods: `set_sandbox_cookie()`, `clear_sandbox_cookie()`

3. **includes/class-wps-debug-mode.php**
   - Cookie: `wpshadow_debug_display`
   - Purpose: Controls debug information display for admins
   - Updated in methods: `set_display_cookie()`, `clear_display_cookie()`

### Cookie Configuration

All cookies now use the following security configuration:

```php
setcookie(
    $name,
    $value,
    array(
        'expires'  => $expiration_time,
        'path'     => COOKIEPATH,         // WordPress cookie path
        'domain'   => COOKIE_DOMAIN,       // WordPress cookie domain
        'secure'   => is_ssl(),            // HTTPS only when SSL is active
        'httponly' => true,                // Prevents JavaScript access
        'samesite' => 'Lax',              // NEW: CSRF protection
    )
);
```

### Why SameSite=Lax?

We chose `Lax` over `Strict` because:

- **Lax** allows cookies during normal navigation (clicking links from external sites)
- **Lax** blocks cookies during cross-site POST requests (CSRF protection)
- **Lax** provides the right balance for WordPress admin functionality
- **Strict** would break legitimate workflows (e.g., clicking admin links from emails)

## Browser Support

The SameSite attribute is supported by all modern browsers:

- Chrome 51+ (2016)
- Firefox 60+ (2018)
- Safari 12+ (2018)
- Edge 16+ (2017)

Older browsers that don't support SameSite will simply ignore the attribute and behave as before.

## Testing

To verify the implementation:

1. **Open browser developer tools** (F12)
2. **Navigate to the Application/Storage tab**
3. **View Cookies** for your WordPress site
4. **Verify SameSite column** shows "Lax" for the following cookies:
   - `wpshadow_troubleshooting_mode` (when in troubleshooting mode)
   - `wpshadow_conflict_sandbox` (when using conflict sandbox)
   - `wpshadow_debug_display` (when debug mode is enabled)

## Security Impact

### Before
- Cookies sent with all requests, including cross-site POST requests
- Vulnerable to CSRF attacks
- Attacker could perform actions as authenticated user

### After
- Cookies only sent with same-site requests and top-level navigation
- CSRF attacks prevented by default
- Maintains full legitimate functionality

## Compliance

This implementation aligns with:

- **OWASP Top 10** - A01:2021 Broken Access Control
- **OWASP Cookie Security Guidance** - SameSite attribute recommendations
- **Modern browser security standards** - Chrome, Firefox, Safari defaults

## References

- [OWASP SameSite Cookie Attribute](https://owasp.org/www-community/SameSite)
- [MDN Web Docs: SameSite cookies](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite)
- [RFC 6265bis: SameSite Cookies](https://datatracker.ietf.org/doc/html/draft-ietf-httpbis-rfc6265bis-09)

## Version History

- **v1.2601.75000+** - SameSite=Lax attribute added to all session cookies
