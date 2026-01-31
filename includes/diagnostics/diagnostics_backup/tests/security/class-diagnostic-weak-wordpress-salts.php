<?php
/**
 * Weak WordPress Salts Diagnostic
 *
 * Detects if wp-config.php has default WordPress.org salt values
 * instead of unique cryptographic keys.
 *
 * @since   1.2802.1500
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Weak_WordPress_Salts Class
 *
 * Checks if WordPress security keys/salts are default or weak values.
 * Critical for session security and authentication tokens.
 *
 * @since 1.2802.1500
 */
class Diagnostic_Weak_WordPress_Salts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'weak-wordpress-salts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Weak WordPress Salt/Security Keys';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects default or weak security keys in wp-config.php';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * WordPress security constant names
	 *
	 * @var array
	 */
	const SECURITY_KEYS = array(
		'AUTH_KEY',
		'SECURE_AUTH_KEY',
		'LOGGED_IN_KEY',
		'NONCE_KEY',
		'AUTH_SALT',
		'SECURE_AUTH_SALT',
		'LOGGED_IN_SALT',
		'NONCE_SALT',
	);

	/**
	 * Known weak/default values
	 *
	 * @var array
	 */
	const WEAK_VALUES = array(
		'put your unique phrase here',
		'',
		' ',
		'changeme',
		'password',
		'secret',
		'default',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2802.1500
	 * @return array|null Finding array if weak keys found, null otherwise.
	 */
	public static function check() {
		// Step 1: Early bailout
		if ( ! self::should_run_check() ) {
			return null;
		}

		// Step 2: Analyze security keys
		$analysis = self::analyze_security_keys();

		// Step 3: If all keys are strong, return null
		if ( empty( $analysis['weak_keys'] ) ) {
			return null;
		}

		// Step 4: Return comprehensive finding
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of weak keys */
				__( '%d security key(s) are weak or default. Default keys = shared across millions of WordPress sites = compromised session security.', 'wpshadow' ),
				count( $analysis['weak_keys'] )
			),
			'severity'     => 'critical',
			'threat_level' => 85,
			'auto_fixable' => true, // Can regenerate with backup
			'kb_link'      => 'https://wpshadow.com/kb/security-weak-wordpress-salts',
			'family'       => self::$family,
			'meta'         => array(
				'weak_keys_count'   => count( $analysis['weak_keys'] ),
				'total_keys_count'  => count( self::SECURITY_KEYS ),
				'weak_keys'         => implode( ', ', $analysis['weak_keys'] ),
			),
			'details'      => array(
				'why_salts_matter'         => array(
					__( 'Salts protect cookies/sessions from brute force attacks', 'wpshadow' ),
					__( 'Default "put your unique phrase here" = shared across millions of sites', 'wpshadow' ),
					__( 'Attackers can crack cookies generated with default salts', 'wpshadow' ),
					__( 'Compromised salts = session hijacking = account takeover', 'wpshadow' ),
					__( 'Each WordPress site needs unique, random 64-character keys', 'wpshadow' ),
				),
				'weak_keys_detail'         => $analysis['weak_keys'],
				'security_impact'          => array(
					'Session Hijacking' => __( 'Attacker steals active session cookies, gains full admin access without password', 'wpshadow' ),
					'Cookie Forgery'    => __( 'Attacker generates valid authentication cookies for any user', 'wpshadow' ),
					'Password Reset'    => __( 'Attacker forges password reset tokens', 'wpshadow' ),
				),
				'remediation_options'      => array(
					'Option 1: Auto-Generate (Recommended)' => array(
						'description' => __( 'WPShadow can auto-generate new keys safely', 'wpshadow' ),
						'time'        => __( '30 seconds', 'wpshadow' ),
						'safety'      => __( 'Creates backup first, logs out all users', 'wpshadow' ),
						'note'        => __( 'All users must re-login after regeneration', 'wpshadow' ),
					),
					'Option 2: WordPress.org API' => array(
						'description' => __( 'Generate new keys via WordPress.org', 'wpshadow' ),
						'url'         => 'https://api.wordpress.org/secret-key/1.1/salt/',
						'time'        => __( '5 minutes', 'wpshadow' ),
						'steps'       => array(
							'1. Visit https://api.wordpress.org/secret-key/1.1/salt/',
							'2. Copy generated keys',
							'3. Edit wp-config.php',
							'4. Replace existing define() statements',
							'5. Save and test',
						),
					),
					'Option 3: Manual Generation' => array(
						'description' => __( 'Generate via command line', 'wpshadow' ),
						'command'     => 'openssl rand -base64 64',
						'time'        => __( '10 minutes', 'wpshadow' ),
						'difficulty'  => __( 'Advanced (requires SSH access)', 'wpshadow' ),
					),
				),
				'post_regeneration_impact' => array(
					__( 'All users logged out immediately (must re-login)', 'wpshadow' ),
					__( 'Active sessions invalidated (prevents session hijacking)', 'wpshadow' ),
					__( 'Password reset links expire (must request new ones)', 'wpshadow' ),
					__( 'Auth cookies become invalid (increases security)', 'wpshadow' ),
					__( 'No data loss (only affects active sessions)', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Check if diagnostic should run.
	 *
	 * @since  1.2802.1500
	 * @return bool True if check should run, false otherwise.
	 */
	private static function should_run_check() {
		// Always run (salts are critical)
		return true;
	}

	/**
	 * Analyze security keys for weakness.
	 *
	 * @since  1.2802.1500
	 * @return array {
	 *     Analysis results.
	 *
	 *     @type array $weak_keys Weak key constant names.
	 *     @type array $strong_keys Strong key constant names.
	 * }
	 */
	private static function analyze_security_keys() {
		$weak_keys   = array();
		$strong_keys = array();

		foreach ( self::SECURITY_KEYS as $key ) {
			if ( ! defined( $key ) ) {
				// Key not defined = weak
				$weak_keys[] = $key;
				continue;
			}

			$value = constant( $key );

			if ( self::is_weak_key( $value ) ) {
				$weak_keys[] = $key;
			} else {
				$strong_keys[] = $key;
			}
		}

		return array(
			'weak_keys'   => $weak_keys,
			'strong_keys' => $strong_keys,
		);
	}

	/**
	 * Check if a key value is weak.
	 *
	 * @since  1.2802.1500
	 * @param  string $value Key value to check.
	 * @return bool True if weak, false if strong.
	 */
	private static function is_weak_key( $value ) {
		// Check if value matches known weak values
		foreach ( self::WEAK_VALUES as $weak ) {
			if ( strtolower( trim( $value ) ) === strtolower( $weak ) ) {
				return true;
			}
		}

		// Check if value is too short (less than 32 characters)
		if ( strlen( $value ) < 32 ) {
			return true;
		}

		// Check if value lacks complexity (all same character, etc.)
		if ( strlen( str_replace( $value[0], '', $value ) ) === 0 ) {
			return true; // All same character
		}

		// Check for common patterns
		if ( preg_match( '/^(123|abc|aaa|password|secret)/i', $value ) ) {
			return true;
		}

		return false; // Appears strong
	}

	/**
	 * Set test keys for testing purposes.
	 *
	 * @since  1.2802.1500
	 * @param  array $keys Mock security keys.
	 * @return void
	 */
	public static function set_test_keys( $keys ) {
		foreach ( $keys as $name => $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}
	}
}
