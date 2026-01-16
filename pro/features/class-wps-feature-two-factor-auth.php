<?php
/**
 * Feature: Two-Factor Authentication (2FA)
 *
 * TODO: Implement two-factor authentication system
 *
 * MISSING CAPABILITIES (vs Wordfence):
 * - TOTP (Time-based One-Time Password) support
 * - Authenticator app integration (Google Authenticator, Authy, etc.)
 * - Backup codes generation
 * - SMS-based 2FA (optional)
 * - Email-based 2FA (fallback)
 * - Recovery codes for account recovery
 * - Per-user 2FA enforcement
 * - Role-based 2FA requirements
 * - 2FA setup wizard
 * - QR code generation for easy setup
 * - Trusted devices management
 * - Grace period for 2FA enrollment
 *
 * IMPLEMENTATION NOTES:
 * - Use standard TOTP algorithm (RFC 6238)
 * - Generate secret keys per user
 * - Store 2FA settings in user meta
 * - Add login screen 2FA prompt
 * - Provide fallback authentication methods
 * - Allow trusted device cookies
 *
 * AUTHENTICATION METHODS:
 * 1. TOTP via authenticator app (primary)
 * 2. Backup codes (10 one-time codes)
 * 3. Email verification (fallback)
 * 4. SMS verification (optional, requires gateway)
 *
 * USER FLOW:
 * 1. User enables 2FA in profile
 * 2. Generate secret key and QR code
 * 3. User scans QR code with authenticator app
 * 4. User enters verification code to confirm
 * 5. Generate and display backup codes
 * 6. On next login, prompt for 2FA code
 * 7. Remember trusted devices (optional)
 *
 * INTEGRATION POINTS:
 * - Hook into wp_authenticate filter
 * - Add profile settings for 2FA
 * - Extend login form UI
 * - Add to security dashboard
 * - Log 2FA events (success, failure, bypass)
 *
 * SECURITY CONSIDERATIONS:
 * - Rate limit verification attempts
 * - Invalidate codes after single use (backup codes)
 * - Encrypt secret keys in database
 * - Clear trusted device cookies on logout
 * - Enforce 2FA for specific roles (admin)
 *
 * PERFORMANCE CONSIDERATIONS:
 * - Cache TOTP validation (prevent replay attacks)
 * - Minimal login overhead
 * - Lazy load QR code generation
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Feature_Two_Factor_Auth
 *
 * Two-factor authentication for enhanced account security.
 *
 * @todo Implement all 2FA features
 */
final class WPSHADOW_Feature_Two_Factor_Auth extends WPSHADOW_Abstract_Feature {

	/**
	 * User meta key for 2FA secret.
	 */
	private const SECRET_META_KEY = 'wpshadow_2fa_secret';

	/**
	 * User meta key for backup codes.
	 */
	private const BACKUP_CODES_KEY = 'wpshadow_2fa_backup_codes';

	/**
	 * User meta key for trusted devices.
	 */
	private const TRUSTED_DEVICES_KEY = 'wpshadow_2fa_trusted_devices';

	/**
	 * TOTP time step (30 seconds).
	 */
	private const TOTP_TIME_STEP = 30;

	/**
	 * TOTP code length.
	 */
	private const TOTP_CODE_LENGTH = 6;

	/**
	 * Number of backup codes to generate.
	 */
	private const BACKUP_CODE_COUNT = 10;

	/**
	 * Trusted device cookie name.
	 */
	private const TRUSTED_DEVICE_COOKIE = 'wpshadow_2fa_trusted';

	/**
	 * Trusted device duration (30 days).
	 */
	private const TRUSTED_DEVICE_DURATION = 30 * DAY_IN_SECONDS;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'two-factor-auth',
				'name'               => __( 'Two-Factor Authentication', 'plugin-wpshadow' ),
				'description'        => __( 'Stop hackers from stealing login passwords - add an extra verification step.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'security',
				'license_level'      => 5, 
				'minimum_capability' => 'read',
				'icon'               => 'dashicons-lock',
				'category'           => 'security',
				'priority'           => 15,

			)
		);
		
		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'totp_authentication' => __( 'TOTP Authenticator App', 'plugin-wpshadow' ),
					'backup_codes'        => __( 'Backup Codes', 'plugin-wpshadow' ),
					'email_2fa'           => __( 'Email Verification Fallback', 'plugin-wpshadow' ),
					'trusted_devices'     => __( 'Remember Trusted Devices', 'plugin-wpshadow' ),
					'force_admin_2fa'     => __( 'Require 2FA for Administrators', 'plugin-wpshadow' ),
				)
			);
			if ( method_exists( $this, 'set_default_sub_features' ) ) {
				$this->set_default_sub_features(
					array(
						'totp_authentication' => true,
						'backup_codes'        => true,
						'email_2fa'           => true,
						'trusted_devices'     => false,
						'force_admin_2fa'     => false,
					)
				);
			}
		}
		
		$this->log_activity( 'feature_initialized', 'Two Factor Auth feature initialized', 'info' );
	}

	/**
	 * Indicate this feature has a details page.
	 *
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Authentication hooks.
		add_filter( 'authenticate', array( $this, 'authenticate_2fa' ), 30, 3 );
		add_action( 'wp_login', array( $this, 'handle_successful_login' ), 10, 2 );

		// Login form modifications.
		add_action( 'login_form', array( $this, 'add_2fa_login_field' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'enqueue_login_assets' ) );

		// Profile settings.
		add_action( 'show_user_profile', array( $this, 'show_2fa_settings' ) );
		add_action( 'edit_user_profile', array( $this, 'show_2fa_settings' ) );
		add_action( 'personal_options_update', array( $this, 'save_2fa_settings' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_2fa_settings' ) );

		// AJAX handlers.
		add_action( 'wp_ajax_WPSHADOW_generate_2fa_secret', array( $this, 'ajax_generate_secret' ) );
		add_action( 'wp_ajax_WPSHADOW_verify_2fa_setup', array( $this, 'ajax_verify_setup' ) );
		add_action( 'wp_ajax_WPSHADOW_generate_backup_codes', array( $this, 'ajax_generate_backup_codes' ) );
		add_action( 'wp_ajax_WPSHADOW_disable_2fa', array( $this, 'ajax_disable_2fa' ) );
		add_action( 'wp_ajax_WPSHADOW_remove_trusted_device', array( $this, 'ajax_remove_trusted_device' ) );

		// Admin notices.
		add_action( 'admin_notices', array( $this, 'show_2fa_notices' ) );
	}

	/**
	 * Generate a secret key for TOTP.
	 *
	 * @return string Base32-encoded secret key.
	 */
	private function generate_secret(): string {
		// Generate 20 random bytes (160 bits for strong security).
		$random_bytes = random_bytes( 20 );
		
		// Base32 encode the secret.
		return $this->base32_encode( $random_bytes );
	}

	/**
	 * Base32 encode a string.
	 *
	 * @param string $data Data to encode.
	 * @return string Base32-encoded string.
	 */
	private function base32_encode( string $data ): string {
		$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$encoded = '';
		$n = 0;
		$bits_remaining = 0;
		
		for ( $i = 0, $len = strlen( $data ); $i < $len; ++$i ) {
			$n = ( $n << 8 ) | ord( $data[ $i ] );
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
		
		return $encoded;
	}

	/**
	 * Base32 decode a string.
	 *
	 * @param string $data Base32-encoded data.
	 * @return string Decoded binary data.
	 */
	private function base32_decode( string $data ): string {
		$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$decoded = '';
		$n = 0;
		$bits_remaining = 0;
		
		$data = strtoupper( $data );
		
		for ( $i = 0, $len = strlen( $data ); $i < $len; ++$i ) {
			$char = $data[ $i ];
			$pos = strpos( $alphabet, $char );
			
			if ( $pos === false ) {
				continue;
			}
			
			$n = ( $n << 5 ) | $pos;
			$bits_remaining += 5;
			
			if ( $bits_remaining >= 8 ) {
				$bits_remaining -= 8;
				$decoded .= chr( ( $n >> $bits_remaining ) & 0xFF );
			}
		}
		
		return $decoded;
	}

	/**
	 * Generate QR code for authenticator app setup.
	 *
	 * @param int    $user_id User ID.
	 * @param string $secret  Secret key.
	 * @return string QR code data URL.
	 */
	private function generate_qr_code( int $user_id, string $secret ): string {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return '';
		}
		
		$site_name = get_bloginfo( 'name' );
		$email = $user->user_email;
		
		// Build TOTP URI (RFC 6238).
		$uri = sprintf(
			'otpauth://totp/%s:%s?secret=%s&issuer=%s',
			rawurlencode( $site_name ),
			rawurlencode( $email ),
			$secret,
			rawurlencode( $site_name )
		);
		
		// Generate QR code using Google Charts API.
		$qr_url = add_query_arg(
			array(
				'chs' => '200x200',
				'cht' => 'qr',
				'chl' => $uri,
				'choe' => 'UTF-8',
			),
			'https://chart.googleapis.com/chart'
		);
		
		return $qr_url;
	}

	/**
	 * Calculate TOTP value.
	 *
	 * @param string $secret    Secret key.
	 * @param int    $timestamp Timestamp.
	 * @return string 6-digit TOTP code.
	 */
	private function calculate_totp( string $secret, int $timestamp ): string {
		// Calculate time counter (30-second window).
		$time_counter = floor( $timestamp / self::TOTP_TIME_STEP );
		
		// Base32 decode secret.
		$decoded_secret = $this->base32_decode( $secret );
		
		// Pack time counter as 8-byte big-endian.
		$time_bytes = pack( 'N*', 0 ) . pack( 'N*', $time_counter );
		
		// Calculate HMAC-SHA1.
		$hash = hash_hmac( 'sha1', $time_bytes, $decoded_secret, true );
		
		// Dynamic truncation (RFC 4226).
		$offset = ord( $hash[ strlen( $hash ) - 1 ] ) & 0x0F;
		$truncated = (
			( ( ord( $hash[ $offset ] ) & 0x7F ) << 24 ) |
			( ( ord( $hash[ $offset + 1 ] ) & 0xFF ) << 16 ) |
			( ( ord( $hash[ $offset + 2 ] ) & 0xFF ) << 8 ) |
			( ord( $hash[ $offset + 3 ] ) & 0xFF )
		);
		
		// Generate 6-digit code.
		$code = $truncated % pow( 10, self::TOTP_CODE_LENGTH );
		
		return str_pad( (string) $code, self::TOTP_CODE_LENGTH, '0', STR_PAD_LEFT );
	}

	/**
	 * Verify TOTP code.
	 *
	 * @param string $code   6-digit code.
	 * @param string $secret Secret key.
	 * @return bool True if valid, false otherwise.
	 */
	private function verify_totp( string $code, string $secret ): bool {
		$timestamp = time();
		
		// Check current, previous, and next time windows (handle clock drift).
		for ( $i = -1; $i <= 1; ++$i ) {
			$window_time = $timestamp + ( $i * self::TOTP_TIME_STEP );
			$expected_code = $this->calculate_totp( $secret, $window_time );
			
			if ( hash_equals( $expected_code, $code ) ) {
				// Check if code was already used (replay prevention).
				$used_key = 'wpshadow_2fa_used_' . md5( $code . $window_time );
				if ( get_transient( $used_key ) ) {
					return false;
				}
				
				// Mark code as used for this time window.
				set_transient( $used_key, true, self::TOTP_TIME_STEP * 2 );
				
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Generate backup codes.
	 *
	 * @return array Array of backup codes.
	 */
	private function generate_backup_codes(): array {
		$codes = array();
		
		// Generate 10 random backup codes.
		for ( $i = 0; $i < self::BACKUP_CODE_COUNT; ++$i ) {
			// Generate 8-character alphanumeric code.
			$code = strtoupper( substr( bin2hex( random_bytes( 4 ) ), 0, 8 ) );
			$codes[] = $code;
		}
		
		return $codes;
	}

	/**
	 * Verify backup code.
	 *
	 * @param int    $user_id User ID.
	 * @param string $code    Backup code.
	 * @return bool True if valid, false otherwise.
	 */
	private function verify_backup_code( int $user_id, string $code ): bool {
		// Get user's backup codes (stored as hashes).
		$backup_codes = get_user_meta( $user_id, self::BACKUP_CODES_KEY, true );
		
		if ( ! is_array( $backup_codes ) || empty( $backup_codes ) ) {
			return false;
		}
		
		$code_hash = wp_hash_password( strtoupper( $code ) );
		
		// Check if code matches any stored hash.
		foreach ( $backup_codes as $index => $stored_hash ) {
			if ( wp_check_password( strtoupper( $code ), $stored_hash ) ) {
				// Remove used code.
				unset( $backup_codes[ $index ] );
				update_user_meta( $user_id, self::BACKUP_CODES_KEY, array_values( $backup_codes ) );
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Check if 2FA is enabled for user.
	 *
	 * @param int $user_id User ID.
	 * @return bool True if enabled, false otherwise.
	 */
	private function is_2fa_enabled( int $user_id ): bool {
		$secret = get_user_meta( $user_id, self::SECRET_META_KEY, true );
		return ! empty( $secret );
	}

	/**
	 * Check if 2FA is required for user's role.
	 *
	 * @param int $user_id User ID.
	 * @return bool True if required, false otherwise.
	 */
	private function is_2fa_required( int $user_id ): bool {
		$user = get_userdata( $user_id );
		
		if ( ! $user ) {
			return false;
		}
		
		// Check if force_admin_2fa is enabled and user is admin.
		if ( get_option( 'wpshadow_two-factor-auth_force_admin_2fa', false ) ) {
			if ( in_array( 'administrator', $user->roles, true ) ) {
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Check if device is trusted.
	 *
	 * @param int $user_id User ID.
	 * @return bool True if trusted, false otherwise.
	 */
	private function is_trusted_device( int $user_id ): bool {
		if ( ! get_option( 'wpshadow_two-factor-auth_trusted_devices', false ) ) {
			return false;
		}
		
		// Check for trusted device cookie.
		if ( ! isset( $_COOKIE[ self::TRUSTED_DEVICE_COOKIE ] ) ) {
			return false;
		}
		
		$device_id = sanitize_text_field( wp_unslash( $_COOKIE[ self::TRUSTED_DEVICE_COOKIE ] ) );
		
		// Get user's trusted devices.
		$trusted_devices = get_user_meta( $user_id, self::TRUSTED_DEVICES_KEY, true );
		
		if ( ! is_array( $trusted_devices ) ) {
			return false;
		}
		
		// Check if device ID exists and is not expired.
		if ( isset( $trusted_devices[ $device_id ] ) ) {
			$expiry = $trusted_devices[ $device_id ]['expiry'] ?? 0;
			if ( $expiry > time() ) {
				return true;
			}
			// Remove expired device.
			unset( $trusted_devices[ $device_id ] );
			update_user_meta( $user_id, self::TRUSTED_DEVICES_KEY, $trusted_devices );
		}
		
		return false;
	}

	/**
	 * Mark device as trusted.
	 *
	 * @param int $user_id User ID.
	 * @return bool True on success, false on failure.
	 */
	private function mark_device_trusted( int $user_id ): bool {
		// Generate unique device ID.
		$device_id = wp_generate_password( 32, false );
		
		// Get current trusted devices.
		$trusted_devices = get_user_meta( $user_id, self::TRUSTED_DEVICES_KEY, true );
		
		if ( ! is_array( $trusted_devices ) ) {
			$trusted_devices = array();
		}
		
		// Add new device.
		$trusted_devices[ $device_id ] = array(
			'created' => time(),
			'expiry'  => time() + self::TRUSTED_DEVICE_DURATION,
			'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
		);
		
		update_user_meta( $user_id, self::TRUSTED_DEVICES_KEY, $trusted_devices );
		
		// Set secure cookie.
		setcookie(
			self::TRUSTED_DEVICE_COOKIE,
			$device_id,
			time() + self::TRUSTED_DEVICE_DURATION,
			COOKIEPATH,
			COOKIE_DOMAIN,
			is_ssl(),
			true
		);
		
		return true;
	}

	/**
	 * Remove trusted device.
	 *
	 * @param int    $user_id   User ID.
	 * @param string $device_id Device ID.
	 * @return bool True on success, false on failure.
	 */
	private function remove_trusted_device( int $user_id, string $device_id ): bool {
		$trusted_devices = get_user_meta( $user_id, self::TRUSTED_DEVICES_KEY, true );
		
		if ( ! is_array( $trusted_devices ) || ! isset( $trusted_devices[ $device_id ] ) ) {
			return false;
		}
		
		unset( $trusted_devices[ $device_id ] );
		update_user_meta( $user_id, self::TRUSTED_DEVICES_KEY, $trusted_devices );
		
		return true;
	}

	/**
	 * Send 2FA code via email (fallback method).
	 *
	 * @param int $user_id User ID.
	 * @return bool True on success, false on failure.
	 */
	private function send_email_code( int $user_id ): bool {
		$user = get_userdata( $user_id );
		
		if ( ! $user ) {
			return false;
		}
		
		// Generate 6-digit code.
		$code = str_pad( (string) wp_rand( 100000, 999999 ), 6, '0', STR_PAD_LEFT );
		
		// Store code in transient (5 minutes).
		set_transient( 'wpshadow_2fa_email_' . $user_id, wp_hash_password( $code ), 5 * MINUTE_IN_SECONDS );
		
		// Send email.
		$subject = sprintf(
			/* translators: %s: Site name */
			__( 'Two-Factor Authentication Code for %s', 'plugin-wpshadow' ),
			get_bloginfo( 'name' )
		);
		
		$message = sprintf(
			/* translators: %s: 6-digit code */
			__( 'Your two-factor authentication code is: %s\n\nThis code will expire in 5 minutes.\n\nIf you did not request this code, please contact the site administrator.', 'plugin-wpshadow' ),
			$code
		);
		
		return wp_mail( $user->user_email, $subject, $message );
	}

	/**
	 * Get 2FA statistics for user.
	 *
	 * @param int $user_id User ID.
	 * @return array 2FA statistics.
	 */
	private function get_user_statistics( int $user_id ): array {
		$backup_codes = get_user_meta( $user_id, self::BACKUP_CODES_KEY, true );
		$trusted_devices = get_user_meta( $user_id, self::TRUSTED_DEVICES_KEY, true );
		
		return array(
			'enabled'                => $this->is_2fa_enabled( $user_id ),
			'successful_logins'      => (int) get_user_meta( $user_id, 'wpshadow_2fa_successful_logins', true ),
			'failed_attempts'        => (int) get_user_meta( $user_id, 'wpshadow_2fa_failed_attempts', true ),
			'remaining_backup_codes' => is_array( $backup_codes ) ? count( $backup_codes ) : 0,
			'trusted_devices'        => is_array( $trusted_devices ) ? count( $trusted_devices ) : 0,
			'last_login'             => get_user_meta( $user_id, 'wpshadow_2fa_last_login', true ),
		);
	}

	/**
	 * Authentication filter for 2FA.
	 *
	 * @param \WP_User|\WP_Error|null $user     User object or error.
	 * @param string                  $username Username.
	 * @param string                  $password Password.
	 * @return \WP_User|\WP_Error User object or error.
	 */
	public function authenticate_2fa( $user, string $username, string $password ) {
		// Only proceed if authentication succeeded.
		if ( ! ( $user instanceof \WP_User ) ) {
			return $user;
		}

		// Skip if 2FA not enabled for this user.
		if ( ! $this->is_2fa_enabled( $user->ID ) ) {
			// Check if 2FA is required.
			if ( $this->is_2fa_required( $user->ID ) ) {
				return new \WP_Error(
					'2fa_required',
						__( 'Your account needs an extra security step. Let\'s set that up now in your profile.', 'plugin-wpshadow' )
		// Check if device is trusted.
		if ( $this->is_trusted_device( $user->ID ) ) {
			return $user;
		}

		// Check if 2FA code was provided.
		$code = isset( $_POST['wpshadow_2fa_code'] ) ? sanitize_text_field( wp_unslash( $_POST['wpshadow_2fa_code'] ) ) : '';

		if ( empty( $code ) ) {
			// Store user ID in session for 2FA prompt.
			$_SESSION['wpshadow_2fa_user_id'] = $user->ID;
			return new \WP_Error(
				'2fa_required',
					__( 'Please enter your security code from your authenticator app.', 'plugin-wpshadow' )
		if ( $this->verify_totp( $code, $secret ) ) {
			// Success - mark device as trusted if requested.
			if ( isset( $_POST['wpshadow_2fa_trust'] ) && get_option( 'wpshadow_two-factor-auth_trusted_devices', false ) ) {
				$this->mark_device_trusted( $user->ID );
			}

			// Update statistics.
			$successful = (int) get_user_meta( $user->ID, 'wpshadow_2fa_successful_logins', true );
			update_user_meta( $user->ID, 'wpshadow_2fa_successful_logins', $successful + 1 );
			update_user_meta( $user->ID, 'wpshadow_2fa_last_login', time() );

			return $user;
		}

		// Try backup code if TOTP failed.
		if ( get_option( 'wpshadow_two-factor-auth_backup_codes', true ) ) {
			if ( $this->verify_backup_code( $user->ID, $code ) ) {
				// Success with backup code.
				$successful = (int) get_user_meta( $user->ID, 'wpshadow_2fa_successful_logins', true );
				update_user_meta( $user->ID, 'wpshadow_2fa_successful_logins', $successful + 1 );
				update_user_meta( $user->ID, 'wpshadow_2fa_last_login', time() );

				return $user;
			}
		}

		// Failed - update statistics.
		$failed = (int) get_user_meta( $user->ID, 'wpshadow_2fa_failed_attempts', true );
		update_user_meta( $user->ID, 'wpshadow_2fa_failed_attempts', $failed + 1 );

		return new \WP_Error(
			'invalid_2fa_code',
			__( 'That code didn\'t work. Let\'s try that again.', 'plugin-wpshadow' )
		);
	}

	/**
	 * Handle successful login.
	 *
	 * @param string   $username Username.
	 * @param \WP_User $user     User object.
	 * @return void
	 */
	public function handle_successful_login( string $username, \WP_User $user ): void {
		// Clean up session.
		unset( $_SESSION['wpshadow_2fa_user_id'] );
	}

	/**
	 * Add 2FA field to login form.
	 *
	 * @return void
	 */
	public function add_2fa_login_field(): void {
		?>
		<p class="wpshadow-2fa-field" style="display: none;">
			<label for="wpshadow_2fa_code"><?php esc_html_e( 'Security Code', 'plugin-wpshadow' ); ?></label>
			<input type="text" name="wpshadow_2fa_code" id="wpshadow_2fa_code" class="input" size="20" autocomplete="off" pattern="[0-9]{6}" inputmode="numeric" />
		</p>
		<?php if ( get_option( 'wpshadow_two-factor-auth_trusted_devices', false ) ) : ?>
		<p class="wpshadow-2fa-trust" style="display: none;">
			<label>
				<input type="checkbox" name="wpshadow_2fa_trust" value="1" />
				<?php esc_html_e( 'Remember this device for 30 days', 'plugin-wpshadow' ); ?>
			</label>
		</p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Enqueue login assets.
	 *
	 * @return void
	 */
	public function enqueue_login_assets(): void {
		?>
		<style>
			.wpshadow-2fa-field input { letter-spacing: 0.3em; font-size: 18px; }
		</style>
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			var errorMsg = document.querySelector('.login .message');
			if (errorMsg && (errorMsg.textContent.indexOf('two-factor') > -1 || errorMsg.textContent.indexOf('2FA') > -1)) {
				document.querySelector('.wpshadow-2fa-field').style.display = 'block';
				var trustField = document.querySelector('.wpshadow-2fa-trust');
				if (trustField) trustField.style.display = 'block';
			}
		});
		</script>
		<?php
	}

	/**
	 * Show 2FA settings in user profile.
	 *
	 * @param \WP_User $user User object.
	 * @return void
	 */
	public function show_2fa_settings( \WP_User $user ): void {
		$is_enabled = $this->is_2fa_enabled( $user->ID );
		$stats = $this->get_user_statistics( $user->ID );
		?>
		<h2><?php esc_html_e( 'Login Security', 'plugin-wpshadow' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'Status', 'plugin-wpshadow' ); ?></th>
				<td>
					<?php if ( $is_enabled ) : ?>
						<span class="dashicons dashicons-yes" style="color: #00a32a;"></span>
						<?php esc_html_e( 'Enabled', 'plugin-wpshadow' ); ?>
						<button type="button" class="button" id="wpshadow-disable-2fa"><?php esc_html_e( 'Disable 2FA', 'plugin-wpshadow' ); ?></button>
					<?php else : ?>
						<span class="dashicons dashicons-no" style="color: #d63638;"></span>
						<?php esc_html_e( 'Disabled', 'plugin-wpshadow' ); ?>
						<button type="button" class="button button-primary" id="wpshadow-setup-2fa"><?php esc_html_e( 'Setup 2FA', 'plugin-wpshadow' ); ?></button>
					<?php endif; ?>
				</td>
			</tr>
			<?php if ( $is_enabled ) : ?>
			<tr>
				<th><?php esc_html_e( 'Activity', 'plugin-wpshadow' ); ?></th>
				<td>
					<p><?php echo esc_html( sprintf( __( 'Times you\'ve logged in: %d', 'plugin-wpshadow' ), $stats['successful_logins'] ) ); ?></p>
					<p><?php echo esc_html( sprintf( __( 'Emergency codes you have left: %d', 'plugin-wpshadow' ), $stats['remaining_backup_codes'] ) ); ?></p>
					<p><?php echo esc_html( sprintf( __( 'Remembered devices: %d', 'plugin-wpshadow' ), $stats['trusted_devices'] ) ); ?></p>
					<?php if ( $stats['last_login'] ) : ?>
					<p><?php echo esc_html( sprintf( __( 'Last time you logged in: %s', 'plugin-wpshadow' ), wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $stats['last_login'] ) ) ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Emergency Codes', 'plugin-wpshadow' ); ?></th>
				<td>
					<button type="button" class="button" id="wpshadow-regenerate-backup-codes"><?php esc_html_e( 'Get New Emergency Codes', 'plugin-wpshadow' ); ?></button>
					<p class="description"><?php esc_html_e( 'Create a fresh set of emergency codes. Your old ones will stop working.', 'plugin-wpshadow' ); ?></p>
				</td>
			</tr>
			<?php endif; ?>
		</table>
		
		<div id="wpshadow-2fa-setup-modal" style="display: none;">
			<h3><?php esc_html_e( 'Add Extra Security to Your Account', 'plugin-wpshadow' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Get an authenticator app on your phone (like Google Authenticator or Authy)', 'plugin-wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Scan this code with your app:', 'plugin-wpshadow' ); ?></li>
			</ol>
			<div id="wpshadow-qr-code"></div>
			<p><strong><?php esc_html_e( 'Or enter this code manually:', 'plugin-wpshadow' ); ?></strong> <code id="wpshadow-secret-key"></code></p>
			<p>
				<label for="wpshadow-verify-code"><?php esc_html_e( 'Now enter the 6-digit code from your app:', 'plugin-wpshadow' ); ?></label>
				<input type="text" id="wpshadow-verify-code" pattern="[0-9]{6}" maxlength="6" />
				<button type="button" class="button button-primary" id="wpshadow-verify-2fa"><?php esc_html_e( 'Confirm', 'plugin-wpshadow' ); ?></button>
			</p>
			<div id="wpshadow-backup-codes-display" style="display: none;">
				<h4><?php esc_html_e( 'Your Emergency Codes', 'plugin-wpshadow' ); ?></h4>
				<p><?php esc_html_e( 'Save these somewhere safe. Each one works only once if you can\'t access your authenticator app.', 'plugin-wpshadow' ); ?></p>
				<pre id="wpshadow-backup-codes"></pre>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('#wpshadow-setup-2fa').on('click', function() {
				$.post(ajaxurl, { action: 'WPSHADOW_generate_2fa_secret', _wpnonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_2fa' ) ); ?>' }, function(response) {
					if (response.success) {
						$('#wpshadow-qr-code').html('<img src="' + response.data.qr_code + '" alt="QR Code" />');
						$('#wpshadow-secret-key').text(response.data.secret);
						$('#wpshadow-2fa-setup-modal').show();
					}
				});
			});

			$('#wpshadow-verify-2fa').on('click', function() {
				var code = $('#wpshadow-verify-code').val();
				$.post(ajaxurl, { action: 'WPSHADOW_verify_2fa_setup', code: code, _wpnonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_2fa' ) ); ?>' }, function(response) {
					if (response.success) {
						$('#wpshadow-backup-codes').text(response.data.backup_codes.join('\\n'));
						$('#wpshadow-backup-codes-display').show();
						alert('Great! Your account is now more secure.');
						location.reload();
					} else {
						alert('That code didn\'t work. Let\'s try again.');
					}
				});
			});

			$('#wpshadow-disable-2fa').on('click', function() {
				if (confirm('Are you sure you want to turn this off?')) {
					$.post(ajaxurl, { action: 'WPSHADOW_disable_2fa', _wpnonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_2fa' ) ); ?>' }, function(response) {
						if (response.success) {
							location.reload();
						}
					});
				}
			});

			$('#wpshadow-regenerate-backup-codes').on('click', function() {
				if (confirm('Your old codes will stop working. Get new ones?')) {
					$.post(ajaxurl, { action: 'WPSHADOW_generate_backup_codes', _wpnonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_2fa' ) ); ?>' }, function(response) {
						if (response.success) {
							alert('Backup codes:\\n\\n' + response.data.codes.join('\\n'));
						}
					});
				}
			});
		});
		</script>
		<?php
	}

	/**
	 * Save 2FA settings.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public function save_2fa_settings( int $user_id ): void {
		// Settings are saved via AJAX.
	}

	/**
	 * AJAX: Generate 2FA secret.
	 *
	 * @return void
	 */
	public function ajax_generate_secret(): void {
		check_ajax_referer( 'wpshadow_2fa' );

		$user_id = get_current_user_id();
		$secret = $this->generate_secret();

		// Store temporarily in transient.
		set_transient( 'wpshadow_2fa_setup_' . $user_id, $secret, 15 * MINUTE_IN_SECONDS );

		wp_send_json_success(
			array(
				'secret'  => $secret,
				'qr_code' => $this->generate_qr_code( $user_id, $secret ),
			)
		);
	}

	/**
	 * AJAX: Verify 2FA setup.
	 *
	 * @return void
	 */
	public function ajax_verify_setup(): void {
		check_ajax_referer( 'wpshadow_2fa' );

		$user_id = get_current_user_id();
		$code = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
		$secret = get_transient( 'wpshadow_2fa_setup_' . $user_id );

		if ( ! $secret || ! $this->verify_totp( $code, $secret ) ) {
			wp_send_json_error( array( 'message' => __( 'That code doesn\'t work', 'plugin-wpshadow' ) ) );
		}

		// Save secret.
		update_user_meta( $user_id, self::SECRET_META_KEY, $secret );
		delete_transient( 'wpshadow_2fa_setup_' . $user_id );

		// Generate backup codes.
		$backup_codes = $this->generate_backup_codes();
		$hashed_codes = array_map( 'wp_hash_password', $backup_codes );
		update_user_meta( $user_id, self::BACKUP_CODES_KEY, $hashed_codes );

		wp_send_json_success( array( 'backup_codes' => $backup_codes ) );
	}

	/**
	 * AJAX: Generate backup codes.
	 *
	 * @return void
	 */
	public function ajax_generate_backup_codes(): void {
		check_ajax_referer( 'wpshadow_2fa' );

		$user_id = get_current_user_id();
		$backup_codes = $this->generate_backup_codes();
		$hashed_codes = array_map( 'wp_hash_password', $backup_codes );
		update_user_meta( $user_id, self::BACKUP_CODES_KEY, $hashed_codes );

		wp_send_json_success( array( 'codes' => $backup_codes ) );
	}

	/**
	 * AJAX: Disable 2FA.
	 *
	 * @return void
	 */
	public function ajax_disable_2fa(): void {
		check_ajax_referer( 'wpshadow_2fa' );

		$user_id = get_current_user_id();
		delete_user_meta( $user_id, self::SECRET_META_KEY );
		delete_user_meta( $user_id, self::BACKUP_CODES_KEY );
		delete_user_meta( $user_id, self::TRUSTED_DEVICES_KEY );

		wp_send_json_success();
	}

	/**
	 * AJAX: Remove trusted device.
	 *
	 * @return void
	 */
	public function ajax_remove_trusted_device(): void {
		check_ajax_referer( 'wpshadow_2fa' );

		$user_id = get_current_user_id();
		$device_id = isset( $_POST['device_id'] ) ? sanitize_text_field( wp_unslash( $_POST['device_id'] ) ) : '';

		if ( $this->remove_trusted_device( $user_id, $device_id ) ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * Show admin notices for 2FA.
	 *
	 * @return void
	 */
	public function show_2fa_notices(): void {
		$user_id = get_current_user_id();

		if ( $this->is_2fa_required( $user_id ) && ! $this->is_2fa_enabled( $user_id ) ) {
			?>
			<div class="notice notice-warning">
				<p>
					<?php esc_html_e( 'Two-factor authentication is required for your account.', 'plugin-wpshadow' ); ?>
					<a href="<?php echo esc_url( admin_url( 'profile.php#wpshadow-2fa' ) ); ?>"><?php esc_html_e( 'Set it up now', 'plugin-wpshadow' ); ?></a>
				</p>
			</div>
			<?php
		}
	}
}
