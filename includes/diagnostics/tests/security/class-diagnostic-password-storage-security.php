<?php
/**
 * Password Storage Security Diagnostic
 *
 * Detects insecure password hashing methods and verifies
 * that WordPress is using modern, secure password storage.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Password Storage Security Diagnostic Class
 *
 * Checks for:
 * - Weak password hashing algorithms (MD5, SHA1)
 * - Proper use of bcrypt/Argon2 via wp_hash_password()
 * - Custom authentication plugins bypassing WordPress hashing
 * - Plaintext passwords in database
 *
 * According to Verizon's 2024 Data Breach Investigations Report,
 * 81% of breaches involve stolen or weak credentials. Weak
 * password hashing allows attackers to crack passwords in seconds
 * rather than years, making it one of the most critical security
 * vulnerabilities.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Password_Storage_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'password-storage-security';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Password Storage Security';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Verifies passwords are hashed using modern, secure algorithms';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Performs comprehensive password storage security analysis:
	 * 1. Checks for weak hashing algorithms (MD5, SHA1)
	 * 2. Verifies bcrypt/Argon2 usage
	 * 3. Detects plaintext passwords
	 * 4. Identifies custom auth plugins that may bypass secure hashing
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();
		$hash_types_seen = array();

		// Check 1: Verify WordPress is using secure password hashing.
		if ( ! function_exists( 'wp_hash_password' ) ) {
			$issues[] = __( 'WordPress password hashing function not available', 'wpshadow' );
		}

		// Check 2: Look for weak or plaintext password hashes in the users table.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$password_hashes = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, user_pass FROM {$wpdb->users} LIMIT %d",
				20
			),
			ARRAY_A
		);

		if ( $password_hashes ) {
			foreach ( $password_hashes as $user ) {
				$hash = $user['user_pass'];
				$hash_type = self::get_hash_type( $hash );
				$hash_types_seen[ $hash_type ] = true;

				switch ( $hash_type ) {
					case 'md5':
						$issues[] = sprintf(
							/* translators: %d: user ID */
							__( 'User ID %d appears to use MD5 password hashing (insecure and fast to crack)', 'wpshadow' ),
							$user['ID']
						);
						break;
					case 'sha1':
						$issues[] = sprintf(
							/* translators: %d: user ID */
							__( 'User ID %d appears to use SHA1 password hashing (insecure and fast to crack)', 'wpshadow' ),
							$user['ID']
						);
						break;
					case 'plaintext':
						$issues[] = sprintf(
							/* translators: %d: user ID */
							__( 'User ID %d may have a plaintext or unsafely stored password', 'wpshadow' ),
							$user['ID']
						);
						break;
				}
			}
		}

		// Check 3: Detect custom overrides of wp_hash_password() or wp_check_password().
		$hash_password_file = self::get_function_file( 'wp_hash_password' );
		$check_password_file = self::get_function_file( 'wp_check_password' );
		$core_pluggable_path = wp_normalize_path( ABSPATH . WPINC . '/pluggable.php' );

		if ( $hash_password_file && false === strpos( $hash_password_file, $core_pluggable_path ) ) {
			$issues[] = __( 'wp_hash_password() is overridden outside of WordPress core (custom authentication detected)', 'wpshadow' );
		}

		if ( $check_password_file && false === strpos( $check_password_file, $core_pluggable_path ) ) {
			$issues[] = __( 'wp_check_password() is overridden outside of WordPress core (custom authentication detected)', 'wpshadow' );
		}

		// Check 4: Verify wp_hash_password() uses modern hashing (bcrypt/Argon2).
		if ( function_exists( 'wp_hash_password' ) ) {
			$test_hash = wp_hash_password( 'test_password_' . wp_rand( 1000, 9999 ) );
			$hash_type = self::get_hash_type( $test_hash );
			$hash_types_seen[ $hash_type ] = true;

			if ( 'bcrypt' !== $hash_type && 'argon2' !== $hash_type ) {
				$issues[] = sprintf(
					/* translators: %s: hashing algorithm */
					__( 'WordPress password hashing is using %s rather than bcrypt/Argon2', 'wpshadow' ),
					$hash_type
				);
			}
		}

		// Check 5: Look for password storage in wp_usermeta (often insecure).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$meta_passwords = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id, meta_key, meta_value FROM {$wpdb->usermeta}
				WHERE meta_key LIKE %s
				AND meta_key NOT LIKE %s
				AND meta_key NOT LIKE %s
				LIMIT %d",
				'%password%','%_password_%_expires','%_password_reset_%',
				20
			),
			ARRAY_A
		);

		if ( $meta_passwords ) {
			foreach ( $meta_passwords as $meta_row ) {
				$meta_value = (string) $meta_row['meta_value'];
				if ( '' === $meta_value ) {
					continue;
				}

				$meta_hash_type = self::get_hash_type( $meta_value );
				if ( 'plaintext' === $meta_hash_type || 'unknown' === $meta_hash_type ) {
					$issues[] = sprintf(
						/* translators: 1: meta key, 2: user ID */
						__( 'Usermeta key "%1$s" for user ID %2$d appears to store a password in an unsafe format', 'wpshadow' ),
						$meta_row['meta_key'],
						(int) $meta_row['user_id']
					);
				}
			}
		}

		// If we found any issues, return a finding.
		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d password storage issue detected',
						'%d password storage issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/password-storage-security',
				'context'      => array(
					'issues' => $issues,
					'stats'  => array(
						'hash_types_seen' => array_keys( $hash_types_seen ),
					),
					'why'    => __(
						'Weak password hashing is among the most critical security vulnerabilities. Verizon\'s 2024 DBIR reports 81% of breaches involve stolen credentials. MD5 (once prevalent in WordPress) can be cracked in <1 second using modern GPUs ($200 worth). SHA1 takes ~10 seconds. In contrast, bcrypt with cost factor 12 takes 100+ milliseconds per attempt, making 1 million password attempts take 27+ hours instead of seconds. This difference is existential: compromised password database + weak hashing = immediate account takeover. NIST SP 800-63B mandates PBKDF2, bcrypt, scrypt, or Argon2. OWASP Top 10 2023 lists password storage failures as a top 10 risk (#02-Cryptographic Failures). PCI-DSS requires passwords hashed with "strong cryptography" (bcrypt qualifies, MD5/SHA1 do not). If plaintext passwords stored in database, any SQL injection = account compromise. Custom authentication bypassing wp_hash_password() is particularly dangerous because custom implementations often lack security review.',
						'wpshadow'
					),
					'recommendation' => __(
						'1. Verify WordPress is using bcrypt: Check database for password hashes starting with $2a$ or $2b$ (bcrypt indicator). MD5 hashes are 32 hex chars. SHA1 hashes are 40 hex chars. Anything else indicates potential issue.
2. Update WordPress to 6.4+: Recent WordPress versions specifically upgraded to bcrypt/Argon2. If on older version, upgrade immediately.
3. Force password reset for MD5/SHA1 hashes: Use wp-cli: `wp user list --format=csv | tail -n +2 | awk -F\',\' \'{print $1}\' | xargs -I {} wp user update {} --prompt=user_pass` to migrate all users to bcrypt.
4. Audit custom authentication plugins: Search database for tables storing passwords outside wp_users. Delete or migrate to wp_hash_password().
5. Search usermeta for password storage: Run query: `SELECT user_id, meta_key, meta_value FROM wp_usermeta WHERE meta_value LIKE \'$1$%\' OR meta_value LIKE \'$2%\' LIMIT 100`. These are secondary password stores (dangerous).
6. Check for plaintext passwords: Query: `SELECT COUNT(*) FROM wp_users WHERE CHAR_LENGTH(user_pass) < 20 OR user_pass NOT LIKE \'$%\'`. Result >0 indicates potential plaintext storage.
7. Disable password storage plugins: Remove plugins like "Password Protect WordPress" that store extra password copies. Use WordPress native passwords only.
8. Enable password strength enforcement: Install "WP Force Strong Passwords" to ensure new passwords are strong (mitigates if cracked).
9. Monitor password changes: Log all password updates to activity log. Alert admin on unusual patterns (many password resets = account compromises).
10. Implement "Secure Passwords" section in admin: Display recommended password policies. Link to password managers (Bitwarden, 1Password) for secure generation.',
						'wpshadow'
					),
				),
			);

			// Add upgrade path for WPShadow Pro Security (when available).
			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'password-storage-hardening',
				'password-storage-security'
			);

			return $finding;
		}

		return null;
	}

	/**
	 * Determine the password hash type.
	 *
	 * @since 1.6093.1200
	 * @param  string $hash Password hash value.
	 * @return string Hash type identifier.
	 */
	private static function get_hash_type( $hash ) {
		if ( preg_match( '/^[a-f0-9]{32}$/i', $hash ) ) {
			return 'md5';
		}

		if ( preg_match( '/^[a-f0-9]{40}$/i', $hash ) ) {
			return 'sha1';
		}

		if ( str_starts_with( $hash, '$argon2' ) ) {
			return 'argon2';
		}

		if ( preg_match( '/^\$2[abxy]\$/', $hash ) ) {
			return 'bcrypt';
		}

		if ( str_starts_with( $hash, '$P$' ) || str_starts_with( $hash, '$H$' ) ) {
			return 'phpass';
		}

		if ( ! str_starts_with( $hash, '$' ) && ! preg_match( '/^[a-f0-9]+$/i', $hash ) && strlen( $hash ) < 60 ) {
			return 'plaintext';
		}

		return 'unknown';
	}

	/**
	 * Get the normalized source file for a function.
	 *
	 * @since 1.6093.1200
	 * @param  string $function_name Function name.
	 * @return string Normalized file path or empty string.
	 */
	private static function get_function_file( $function_name ) {
		if ( ! function_exists( $function_name ) ) {
			return '';
		}

		try {
			$reflection = new \ReflectionFunction( $function_name );
			$filename   = $reflection->getFileName();
			if ( $filename ) {
				return wp_normalize_path( $filename );
			}
		} catch ( \ReflectionException $exception ) {
			return '';
		}

		return '';
	}
}
