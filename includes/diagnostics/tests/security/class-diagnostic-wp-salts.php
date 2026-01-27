<?php
/**
 * Diagnostic: WordPress Security Keys and Salts
 *
 * Checks if WordPress security keys and salts are properly configured.
 * Missing or default salts make session hijacking trivial for attackers.
 *
 * Philosophy: Security first (#1), educate users (#6), show value (#9)
 * KB Link: https://wpshadow.com/kb/security-wp-salts
 * Training: https://wpshadow.com/training/security-wp-salts
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
 * WP Salts Diagnostic Class
 *
 * Validates WordPress security keys and salts are unique and properly set.
 */
class Diagnostic_WP_Salts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wp-salts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Security Keys Missing or Weak';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'WordPress security keys and salts are not properly configured, allowing session hijacking.';

	/**
	 * Required salt constants
	 *
	 * @var array
	 */
	private static $required_salts = array(
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
	 * Run the diagnostic check
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$missing_salts = array();
		$weak_salts    = array();
		$default_salts = array();

		foreach ( self::$required_salts as $salt ) {
			// Check if constant is defined
			if ( ! defined( $salt ) ) {
				$missing_salts[] = $salt;
				continue;
			}

			$value = constant( $salt );

			// Check if it's the default 'put your unique phrase here'
			if ( empty( $value ) || 'put your unique phrase here' === $value ) {
				$default_salts[] = $salt;
				continue;
			}

			// Check if it's too short (weak)
			if ( strlen( $value ) < 64 ) {
				$weak_salts[] = $salt;
			}
		}

		// If everything is fine
		if ( empty( $missing_salts ) && empty( $weak_salts ) && empty( $default_salts ) ) {
			return null;
		}

		// Calculate threat level
		$threat_level = 60; // Base threat

		if ( ! empty( $missing_salts ) ) {
			$threat_level += 30; // Critical - completely missing
		}

		if ( ! empty( $default_salts ) ) {
			$threat_level += 20; // Very high - using defaults
		}

		if ( ! empty( $weak_salts ) ) {
			$threat_level += 10; // High - weak but present
		}

		// Build descriptive message
		$issues = array();

		if ( ! empty( $missing_salts ) ) {
			$issues[] = sprintf(
				/* translators: 1: count of missing salts */
				_n( '%d security key is completely missing', '%d security keys are completely missing', count( $missing_salts ), 'wpshadow' ),
				count( $missing_salts )
			);
		}

		if ( ! empty( $default_salts ) ) {
			$issues[] = sprintf(
				/* translators: 1: count of default salts */
				_n( '%d security key is using the default value', '%d security keys are using default values', count( $default_salts ), 'wpshadow' ),
				count( $default_salts )
			);
		}

		if ( ! empty( $weak_salts ) ) {
			$issues[] = sprintf(
				/* translators: 1: count of weak salts */
				_n( '%d security key is too short (weak)', '%d security keys are too short (weak)', count( $weak_salts ), 'wpshadow' ),
				count( $weak_salts )
			);
		}

		$message = sprintf(
			/* translators: 1: list of issues */
			__( 'Your WordPress security keys have problems: %s. These keys protect your login sessions. Without strong, unique keys, attackers can hijack user sessions and gain unauthorized access.', 'wpshadow' ),
			implode( '; ', $issues )
		);

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $message,
			'severity'    => 'critical',
			'threat_level' => min( $threat_level, 100 ),
			'auto_fixable' => false, // Requires manual wp-config.php update
			'kb_link'     => 'https://wpshadow.com/kb/security-wp-salts',
			'training_link' => 'https://wpshadow.com/training/security-wp-salts',
			'manual_steps' => array(
				sprintf(
					/* translators: 1: URL to WordPress.org API */
					__( 'Visit %s to generate new security keys', 'wpshadow' ),
					'https://api.wordpress.org/secret-key/1.1/salt/'
				),
				__( 'Copy the generated keys', 'wpshadow' ),
				__( 'Open wp-config.php in a text editor', 'wpshadow' ),
				__( 'Replace the existing keys with the new ones', 'wpshadow' ),
				__( 'Save the file', 'wpshadow' ),
				__( 'All users will be logged out and need to log in again', 'wpshadow' ),
			),
			'impact'      => array(
				'security' => __( 'Session hijacking vulnerability - attackers can steal user sessions', 'wpshadow' ),
				'users'    => __( 'Users will be logged out after fixing (they must log in again)', 'wpshadow' ),
			),
			'evidence'    => array(
				'missing_count' => count( $missing_salts ),
				'default_count' => count( $default_salts ),
				'weak_count'    => count( $weak_salts ),
				'total_required' => count( self::$required_salts ),
			),
		);
	}
}
