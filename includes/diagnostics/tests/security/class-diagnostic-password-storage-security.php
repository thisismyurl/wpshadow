<?php
/**
 * Password Storage Security Diagnostic
 *
 * Detects insecure password hashing methods and verifies
 * that WordPress is using modern, secure password storage.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2033.2102
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

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
 * @since 1.2033.2102
 */
class Diagnostic_Password_Storage_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $slug = 'password-storage-security';

	/**
	 * The diagnostic title
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $title = 'Password Storage Security';

	/**
	 * The diagnostic description
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $description = 'Verifies passwords are hashed using modern, secure algorithms';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.2033.2102
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
	 * @since  1.2033.2102
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check 1: Verify WordPress is using secure password hashing.
		if ( ! function_exists( 'wp_hash_password' ) ) {
			$issues[] = __( 'WordPress password hashing function not available', 'wpshadow' );
		}

		// Check 2: Look for custom password columns that might use weak hashing.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$password_hashes = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, user_pass FROM {$wpdb->users} LIMIT %d",
				5
			),
			ARRAY_A
		);

		if ( $password_hashes ) {
			foreach ( $password_hashes as $user ) {
				$hash = $user['user_pass'];

				// Check if hash looks like MD5 (32 chars, hex).
				if ( preg_match( '/^[a-f0-9]{32}$/i', $hash ) ) {
					$issues[] = sprintf(
						/* translators: %d: user ID */
						__( 'User ID %d appears to use MD5 password hashing (insecure)', 'wpshadow' ),
						$user['ID']
					);
				}

				// Check if hash looks like SHA1 (40 chars, hex).
				if ( preg_match( '/^[a-f0-9]{40}$/i', $hash ) ) {
					$issues[] = sprintf(
						/* translators: %d: user ID */
						__( 'User ID %d appears to use SHA1 password hashing (insecure)', 'wpshadow' ),
						$user['ID']
					);
				}

				// Check if hash looks like plaintext (no $ prefix, not hex pattern).
				if ( ! str_starts_with( $hash, '$' ) && ! preg_match( '/^[a-f0-9]+$/i', $hash ) && strlen( $hash ) < 60 ) {
					$issues[] = sprintf(
						/* translators: %d: user ID */
						__( 'User ID %d may have a plaintext or weakly hashed password', 'wpshadow' ),
						$user['ID']
					);
				}
			}
		}

		// Check 3: Look for custom authentication plugins that might bypass wp_hash_password().
		$active_plugins = get_option( 'active_plugins', array() );
		$suspicious_auth_plugins = array(
			'custom-auth',
			'legacy-auth',
			'simple-password',
			'md5-auth',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $suspicious_auth_plugins as $suspicious ) {
				if ( str_contains( strtolower( $plugin ), $suspicious ) ) {
					$issues[] = sprintf(
						/* translators: %s: plugin name */
						__( 'Plugin "%s" may implement custom authentication that bypasses secure password hashing', 'wpshadow' ),
						$plugin
					);
				}
			}
		}

		// Check 4: Verify wp_hash_password() uses bcrypt (contains $2 or $argon2).
		if ( function_exists( 'wp_hash_password' ) ) {
			$test_hash = wp_hash_password( 'test_password_' . wp_rand( 1000, 9999 ) );
			
			// bcrypt starts with $2a$, $2b$, $2x$, $2y$; Argon2 starts with $argon2.
			$is_secure = str_starts_with( $test_hash, '$2' ) || str_starts_with( $test_hash, '$argon2' );
			
			if ( ! $is_secure ) {
				$issues[] = __( 'WordPress password hashing does not appear to use bcrypt or Argon2', 'wpshadow' );
			}
		}

		// Check 5: Look for password storage in wp_usermeta (security plugins sometimes do this insecurely).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$meta_passwords = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} 
			WHERE meta_key LIKE '%password%' 
			AND meta_key NOT LIKE '%_password_%_expires'
			AND meta_key NOT LIKE '%_password_reset_%'"
		);

		if ( $meta_passwords > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of usermeta rows */
				__( 'Found %d usermeta entries with "password" in the key, which may indicate insecure password storage', 'wpshadow' ),
				$meta_passwords
			);
		}

		// If we found any issues, return a finding.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					__( 'Password storage security issues detected: %s', 'wpshadow' ),
					implode( '; ', $issues )
				),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/password-storage-security',
				'context'      => array(
					'issues' => $issues,
					'why'    => __(
						'Passwords are the #1 target in cyberattacks. According to Verizon\'s 2024 DBIR, 81% of breaches involve stolen or weak credentials. ' .
						'Weak hashing algorithms like MD5 and SHA1 can be cracked in seconds using modern GPUs. ' .
						'WordPress uses bcrypt by default, which is designed to be slow (making brute-force attacks impractical). ' .
						'If your site uses custom authentication or legacy password storage, attackers can compromise accounts rapidly.',
						'wpshadow'
					),
				),
			);
		}

		return null;
	}
}
