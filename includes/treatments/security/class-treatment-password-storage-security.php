<?php
/**
 * Password Storage Security Treatment
 *
 * Detects insecure password hashing methods and verifies
 * that WordPress is using modern, secure password storage.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.2033.2102
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Password Storage Security Treatment Class
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
 * @since 1.2033.2102
 */
class Treatment_Password_Storage_Security extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $slug = 'password-storage-security';

	/**
	 * The treatment title
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $title = 'Password Storage Security';

	/**
	 * The treatment description
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $description = 'Verifies passwords are hashed using modern, secure algorithms';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Performs comprehensive password storage security analysis:
	 * 1. Checks for weak hashing algorithms (MD5, SHA1)
	 * 2. Verifies bcrypt/Argon2 usage
	 * 3. Detects plaintext passwords
	 * 4. Identifies custom auth plugins that may bypass secure hashing
	 *
	 * @since  1.2033.2102
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Password_Storage_Security' );
	}

	/**
	 * Determine the password hash type.
	 *
	 * @since  1.6034.1200
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
	 * @since  1.6034.1200
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
