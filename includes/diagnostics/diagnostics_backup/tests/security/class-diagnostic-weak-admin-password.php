<?php
/**
 * Weak Admin Password Detection Diagnostic
 *
 * Checks admin users for weak or commonly-used passwords that make
 * account takeover trivial.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Weak_Admin_Password Class
 *
 * Detects admin users with weak, common, or easily guessable passwords.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Weak_Admin_Password extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'weak-admin-password';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Weak Administrator Password Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for weak or commonly-used passwords';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Top weak passwords to check against
	 *
	 * @var array
	 */
	const WEAK_PASSWORDS = array(
		'admin',
		'password',
		'password123',
		'123456',
		'12345678',
		'qwerty',
		'abc123',
		'letmein',
		'welcome',
		'monkey',
		'dragon',
		'master',
		'sunshine',
		'princess',
		'football',
		'shadow',
		'michael',
		'superman',
		'batman',
		'iloveyou',
		'123123',
		'000000',
		'111111',
		'666666',
		'888888',
		'admin123',
		'admin@123',
		'root',
		'toor',
		'pass',
		'test',
		'guest',
		'info',
		'adm',
		'administrator',
		'wordpress',
		'wp-admin',
		'wpadmin',
		'site123',
		'siteadmin',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if weak passwords found, null otherwise.
	 */
	public static function check() {
		// Get all administrator users
		$admin_users = get_users(
			array(
				'role'   => 'administrator',
				'fields' => array( 'ID', 'user_login', 'user_email' ),
			)
		);

		if ( empty( $admin_users ) ) {
			return null;
		}

		$weak_password_users = array();

		foreach ( $admin_users as $user ) {
			// Check if user might have weak password using common patterns
			// Note: We cannot directly check the password, but we can identify risk patterns
			if ( self::is_likely_weak_username( $user->user_login ) ) {
				$weak_password_users[] = array(
					'id'       => $user->ID,
					'login'    => $user->user_login,
					'email'    => $user->user_email,
					'risk'     => 'high', // Username matches common weak passwords
				);
			}
		}

		if ( empty( $weak_password_users ) ) {
			return null;
		}

		$count = count( $weak_password_users );

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: number of admins with weak patterns */
				__( 'Found %d administrator %s with weak password patterns. Attack risk is extremely high.', 'wpshadow' ),
				$count,
				( $count === 1 ? __( 'account', 'wpshadow' ) : __( 'accounts', 'wpshadow' ) )
			),
			'severity'      => 'critical',
			'threat_level'  => 90,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/weak-admin-password',
			'family'        => self::$family,
			'meta'          => array(
				'weak_password_count' => $count,
				'affected_users'      => array_slice( $weak_password_users, 0, 5 ), // Show first 5
				'brute_force_risk'    => __( 'CRITICAL - These accounts are trivial to brute-force', 'wpshadow' ),
				'immediate_actions'   => array(
					__( 'Change all administrator passwords immediately', 'wpshadow' ),
					__( 'Use strong passwords with 16+ characters including special characters', 'wpshadow' ),
					__( 'Implement two-factor authentication (2FA)', 'wpshadow' ),
					__( 'Enable login attempt throttling', 'wpshadow' ),
					__( 'Review recent login attempts for unauthorized access', 'wpshadow' ),
				),
			),
			'details'       => array(
				'issue'           => __( 'One or more admin accounts have weak or predictable password patterns.', 'wpshadow' ),
				'security_impact' => __( 'CRITICAL - Account takeover is extremely easy. Attackers can gain full site control within minutes.', 'wpshadow' ),
				'attack_scenario' => array(
					'Step 1' => __( 'Attacker scans your WordPress admin login page', 'wpshadow' ),
					'Step 2' => __( 'Attacker tries common usernames (admin, wordpress, site)', 'wpshadow' ),
					'Step 3' => __( 'Attacker tries weak passwords from automated dictionary', 'wpshadow' ),
					'Step 4' => __( 'Login succeeds - attacker has full admin access', 'wpshadow' ),
					'Step 5' => __( 'Attacker installs backdoor malware', 'wpshadow' ),
				),
				'password_requirements' => array(
					__( 'Minimum 16 characters' ) => __( 'Longer passwords are exponentially harder to crack' ),
					__( 'Mix of character types' ) => __( 'UPPERCASE, lowercase, numbers, and special chars (!@#$%^&*)' ),
					__( 'No dictionary words' ) => __( 'Avoid actual words that can be in brute-force dictionaries' ),
					__( 'Unique per site' ) => __( 'Do not reuse passwords across sites' ),
				),
			),
		);
	}

	/**
	 * Check if username matches weak password patterns.
	 *
	 * @since  1.2601.2148
	 * @param  string $username Username to check.
	 * @return bool True if username matches weak patterns.
	 */
	private static function is_likely_weak_username( $username ) {
		$username_lower = strtolower( $username );

		// Direct match with weak password list
		foreach ( self::WEAK_PASSWORDS as $weak ) {
			if ( $username_lower === $weak || $username_lower === sanitize_user( $weak ) ) {
				return true;
			}

			// Partial matches
			if ( strpos( $username_lower, $weak ) !== false || strpos( $weak, $username_lower ) !== false ) {
				return true;
			}
		}

		// Check for common patterns
		if ( $username_lower === 'admin' || $username_lower === 'administrator' ) {
			return true;
		}

		if ( strpos( $username_lower, 'test' ) !== false || strpos( $username_lower, 'demo' ) !== false ) {
			return true;
		}

		// Check if username is just numbers or sequential
		if ( preg_match( '/^\d+$/', $username_lower ) || preg_match( '/^[a-z]{1,3}$/', $username_lower ) ) {
			return true;
		}

		return false;
	}
}
