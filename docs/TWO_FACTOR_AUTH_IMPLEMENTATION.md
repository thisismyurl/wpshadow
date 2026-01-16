# Two-Factor Authentication (2FA) Implementation

**Status**: ✅ **COMPLETE**  
**Date**: January 2026  
**Feature Class**: `WPSHADOW_Feature_Two_Factor_Auth`  
**File**: `includes/features/class-wps-feature-two-factor-auth.php`  
**Lines**: 1,026

## Overview

Complete Two-Factor Authentication implementation using Time-based One-Time Passwords (TOTP) following RFC 6238. Provides enterprise-grade security with QR code setup, backup codes, trusted devices, and email fallback.

## Features Implemented

### Core Security Features

1. **TOTP Algorithm (RFC 6238)**
   - HMAC-SHA1 with dynamic truncation
   - 30-second time windows
   - 6-digit codes
   - Clock drift tolerance (±30 seconds)
   - Replay attack prevention via transients

2. **Base32 Encoding**
   - Custom implementation following RFC 4648
   - Encode/decode for secret keys
   - 160-bit security (20 random bytes)

3. **QR Code Generation**
   - Google Charts API integration
   - `otpauth://totp/` URI format
   - Compatible with Google Authenticator, Authy, Microsoft Authenticator

4. **Backup Codes**
   - 10 random 8-character codes
   - Hashed with `wp_hash_password()`
   - Single-use enforcement
   - Regeneration capability

5. **Trusted Devices**
   - 30-day secure cookies
   - Device ID tracking
   - IP address and user agent logging
   - Automatic expiry cleanup
   - Manual device removal

6. **Email Fallback**
   - 6-digit random codes
   - 5-minute expiry via transients
   - Translated email messages
   - `wp_mail()` integration

### User Interface

1. **Login Form Modifications**
   - 2FA code input field (numeric, 6 digits)
   - "Trust this device" checkbox
   - JavaScript show/hide logic
   - CSS styling for input

2. **Profile Settings**
   - QR code display for setup
   - Secret key display
   - Statistics dashboard:
     * Successful logins
     * Failed attempts
     * Backup codes remaining
     * Trusted devices count
     * Last login timestamp
   - Enable/disable toggle
   - Backup code regeneration
   - Device management

3. **Admin Notices**
   - Required but not enabled warning
   - Setup success confirmation
   - Low backup codes alert

### AJAX Handlers

1. **Generate Secret** (`WPSHADOW_generate_2fa_secret`)
   - Creates new secret key
   - Generates QR code
   - Stores in 15-minute transient

2. **Verify Setup** (`WPSHADOW_verify_2fa_setup`)
   - Validates TOTP code during initial setup
   - Saves secret to user meta
   - Generates backup codes
   - Returns codes for user to save

3. **Generate Backup Codes** (`WPSHADOW_generate_backup_codes`)
   - Regenerates 10 new backup codes
   - Invalidates existing codes
   - Returns new codes

4. **Disable 2FA** (`WPSHADOW_disable_2fa`)
   - Removes secret, backup codes, trusted devices
   - Requires confirmation dialog

5. **Remove Trusted Device** (`WPSHADOW_remove_trusted_device`)
   - Removes specific device by ID
   - Updates user meta

### Authentication Flow

1. **Standard Login**
   - User enters username/password
   - WordPress authenticates credentials
   - 2FA filter intercepts (`authenticate_2fa`)
   - Checks if 2FA enabled for user
   - If disabled but required → Error
   - If trusted device → Allow
   - If no code provided → Prompt for code
   - Verify TOTP or backup code
   - Update statistics (success/failure)
   - Mark device as trusted (if requested)
   - Complete authentication

2. **Post-Login**
   - Clean up session variables
   - Update last login timestamp
   - Increment successful login counter

### Security Features

1. **Replay Attack Prevention**
   - Used codes tracked in transients
   - 2× time step expiry (60 seconds)
   - MD5 hash of code + timestamp as key

2. **Timing-Safe Comparison**
   - Uses `hash_equals()` for code verification
   - Prevents timing attacks

3. **Secure Cookie Management**
   - httponly flag
   - Secure flag (HTTPS)
   - Proper domain/path settings
   - 30-day expiry

4. **Role-Based Enforcement**
   - Option: `force_admin_2fa`
   - Requires all administrators to enable 2FA
   - Blocks login until setup complete

5. **Failed Attempt Tracking**
   - Increments counter on failed verification
   - Stores in user meta
   - No lockout (WordPress handles this separately)

## Configuration Options

All options use the `wpshadow_two-factor-auth_` prefix:

1. **force_admin_2fa** (boolean)
   - Default: `false`
   - Description: Require all administrators to enable 2FA

2. **backup_codes** (boolean)
   - Default: `true`
   - Description: Enable backup code authentication

3. **trusted_devices** (boolean)
   - Default: `false`
   - Description: Allow users to trust devices for 30 days

4. **email_fallback** (boolean)
   - Default: `false`
   - Description: Send codes via email as fallback

## User Meta Keys

1. **wpshadow_2fa_secret** - Base32-encoded secret key (20 bytes)
2. **wpshadow_2fa_backup_codes** - Array of hashed backup codes
3. **wpshadow_2fa_trusted_devices** - Array of device data:
   ```php
   [
       'device_id' => [
           'created'    => 1234567890,
           'expires'    => 1237246290,
           'ip'         => '192.168.1.1',
           'user_agent' => 'Mozilla/5.0...'
       ]
   ]
   ```
4. **wpshadow_2fa_successful_logins** - Counter
5. **wpshadow_2fa_failed_attempts** - Counter
6. **wpshadow_2fa_last_login** - Timestamp

## Transients

1. **wpshadow_2fa_setup_{user_id}** - Temporary secret during setup (15 minutes)
2. **wpshadow_2fa_used_{hash}** - Used TOTP code tracking (60 seconds)
3. **wpshadow_2fa_email_code_{user_id}** - Email fallback code (5 minutes)

## Cookies

1. **wpshadow_2fa_trusted_device** - Device ID for trusted device (30 days)

## WordPress Hooks

### Actions

- `authenticate` (priority 30) - `authenticate_2fa()` - Main authentication filter
- `wp_login` (priority 10) - `handle_successful_login()` - Post-login cleanup
- `login_form` - `add_2fa_login_field()` - Add 2FA input to login form
- `login_enqueue_scripts` - `enqueue_login_assets()` - CSS/JS for login page
- `show_user_profile` - `show_2fa_settings()` - Profile settings UI
- `edit_user_profile` - `show_2fa_settings()` - Profile settings UI (admin)
- `personal_options_update` - `save_2fa_settings()` - Save profile settings
- `edit_user_profile_update` - `save_2fa_settings()` - Save profile settings (admin)
- `admin_notices` - `show_2fa_notices()` - Admin notification system

### AJAX Actions

- `wp_ajax_WPSHADOW_generate_2fa_secret`
- `wp_ajax_WPSHADOW_verify_2fa_setup`
- `wp_ajax_WPSHADOW_generate_backup_codes`
- `wp_ajax_WPSHADOW_disable_2fa`
- `wp_ajax_WPSHADOW_remove_trusted_device`

## Technical Implementation Details

### TOTP Calculation

```php
// Time counter (30-second windows)
$time_counter = floor( time() / 30 );

// Pack as 8-byte big-endian
$time_bytes = pack( 'N*', 0 ) . pack( 'N*', $time_counter );

// HMAC-SHA1
$hash = hash_hmac( 'sha1', $time_bytes, $decoded_secret, true );

// Dynamic truncation (RFC 4226)
$offset = ord( $hash[19] ) & 0x0F;
$truncated = (
    ( ( ord( $hash[$offset] ) & 0x7F ) << 24 ) |
    ( ( ord( $hash[$offset + 1] ) & 0xFF ) << 16 ) |
    ( ( ord( $hash[$offset + 2] ) & 0xFF ) << 8 ) |
    ( ord( $hash[$offset + 3] ) & 0xFF )
);

// 6-digit code
$code = $truncated % 1000000;
```

### Base32 Encoding

```php
$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
$encoded = '';
$n = 0;
$bits_remaining = 0;

for ( $i = 0; $i < strlen( $data ); ++$i ) {
    $n = ( $n << 8 ) | ord( $data[$i] );
    $bits_remaining += 8;
    
    while ( $bits_remaining >= 5 ) {
        $bits_remaining -= 5;
        $encoded .= $alphabet[ ( $n >> $bits_remaining ) & 0x1F ];
    }
}

if ( $bits_remaining > 0 ) {
    $n <<= ( 5 - $bits_remaining );
    $encoded .= $alphabet[ $n & 0x1F ];
}
```

### QR Code Generation

```php
$issuer = get_bloginfo( 'name' );
$account = $user->user_login;
$uri = sprintf(
    'otpauth://totp/%s:%s?secret=%s&issuer=%s',
    rawurlencode( $issuer ),
    rawurlencode( $account ),
    $secret,
    rawurlencode( $issuer )
);

$qr_code_url = sprintf(
    'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=%s',
    rawurlencode( $uri )
);
```

## Testing Checklist

### Manual Testing

- [ ] **Setup Flow**
  - [ ] Generate QR code
  - [ ] Scan with authenticator app
  - [ ] Verify TOTP code
  - [ ] Receive backup codes
  - [ ] 2FA enabled in profile

- [ ] **Login Flow**
  - [ ] Login with username/password
  - [ ] Prompted for 2FA code
  - [ ] Enter valid TOTP code
  - [ ] Successfully authenticated
  - [ ] Statistics updated

- [ ] **Backup Codes**
  - [ ] Use backup code to login
  - [ ] Code removed after use
  - [ ] Cannot reuse same code
  - [ ] Regenerate backup codes
  - [ ] Old codes invalidated

- [ ] **Trusted Devices**
  - [ ] Enable trusted devices option
  - [ ] Check "trust this device"
  - [ ] Cookie set properly
  - [ ] No 2FA prompt on subsequent logins
  - [ ] Device appears in profile
  - [ ] Remove trusted device
  - [ ] 2FA prompt returns

- [ ] **Role Enforcement**
  - [ ] Enable force_admin_2fa
  - [ ] Admin without 2FA blocked
  - [ ] Setup link provided
  - [ ] After setup, login allowed

- [ ] **Edge Cases**
  - [ ] Clock drift (±30 seconds)
  - [ ] Expired codes rejected
  - [ ] Invalid codes rejected
  - [ ] Replay attack prevention
  - [ ] Multiple failed attempts tracked

### Security Testing

- [ ] **Replay Attacks**
  - [ ] Use same code twice
  - [ ] Verify rejection

- [ ] **Timing Attacks**
  - [ ] Verify hash_equals() usage
  - [ ] Constant-time comparison

- [ ] **Brute Force**
  - [ ] Failed attempt tracking
  - [ ] WordPress lockout integration

- [ ] **Cookie Security**
  - [ ] httponly flag set
  - [ ] Secure flag (HTTPS)
  - [ ] Proper expiry

- [ ] **Transient Cleanup**
  - [ ] Used codes expire
  - [ ] Setup secrets expire
  - [ ] Email codes expire

## Compatibility

- **WordPress**: 6.4+
- **PHP**: 8.1.29+
- **Authenticator Apps**:
  - Google Authenticator
  - Authy
  - Microsoft Authenticator
  - FreeOTP
  - Any RFC 6238 compatible app

## Performance Considerations

1. **HMAC-SHA1 Calculation**
   - Native PHP `hash_hmac()` function
   - Minimal overhead (<1ms)

2. **Base32 Operations**
   - Pure PHP implementation
   - Called only during setup and verification
   - Negligible impact

3. **Transient Storage**
   - WordPress object cache integration
   - Automatic expiry cleanup
   - No database bloat

4. **Cookie Management**
   - Single cookie per device
   - 30-day expiry
   - Automatic cleanup on removal

## Future Enhancements

### Potential Additions

1. **WebAuthn/FIDO2 Support**
   - Hardware key authentication
   - Biometric authentication
   - Passwordless login

2. **Recovery Codes**
   - Different from backup codes
   - One-time use for account recovery
   - Admin-generated for user assistance

3. **SMS Fallback**
   - Send codes via SMS
   - Requires SMS gateway integration
   - Third-party service dependency

4. **Rate Limiting**
   - Limit failed attempts per user
   - Temporary lockout after X failures
   - Integration with existing WordPress security

5. **Audit Logging**
   - Detailed 2FA event logs
   - Failed attempt details
   - Device trust changes
   - Integration with activity logger

6. **Multi-Method Support**
   - Allow users to enable multiple methods
   - Fallback priority order
   - Method selection during login

7. **Session Management**
   - Force re-authentication for sensitive actions
   - Session timeout settings
   - Active session monitoring

8. **API Integration**
   - REST API endpoint protection
   - Application passwords with 2FA
   - OAuth integration

## References

- **RFC 6238**: TOTP: Time-Based One-Time Password Algorithm
  - https://datatracker.ietf.org/doc/html/rfc6238

- **RFC 4226**: HOTP: An HMAC-Based One-Time Password Algorithm
  - https://datatracker.ietf.org/doc/html/rfc4226

- **RFC 4648**: The Base16, Base32, and Base64 Data Encodings
  - https://datatracker.ietf.org/doc/html/rfc4648

- **Google Authenticator Key URI Format**
  - https://github.com/google/google-authenticator/wiki/Key-Uri-Format

## Code Statistics

- **Total Lines**: 1,026
- **Methods**: 27
  - Public: 12
  - Private: 15
- **Constants**: 7
- **Hooks**: 14 (9 actions + 5 AJAX)
- **AJAX Handlers**: 5
- **User Meta Keys**: 6
- **Transients**: 3
- **Cookies**: 1

## Completion Summary

✅ **All Features Implemented**
- TOTP algorithm (RFC 6238)
- Base32 encoding/decoding
- QR code generation
- Backup codes with hashing
- Trusted device management
- Email fallback
- Statistics tracking
- Authentication flow
- Login form modifications
- Profile settings UI
- AJAX handlers
- Admin notices
- Role-based enforcement
- Security features (replay prevention, timing-safe comparison)

✅ **Integration Complete**
- Feature registered in wpshadow.php (line 341)
- File required in wpshadow.php (line 736)
- All hooks registered
- All AJAX handlers registered

✅ **Validation Complete**
- PHP syntax: ✅ No errors
- Feature count: 67 features total
- Stub file: ✅ Deleted

## Support

For issues or questions:
- GitHub Issues: https://github.com/thisismyurl/plugin-wpshadow
- Documentation: /docs/
- Support Contact: support@wpshadow.com
