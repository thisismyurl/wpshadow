<?php
/**
 * Diagnostic: WordPress Security Keys and Salts Configuration
 *
 * Checks if WordPress security keys and salts are properly configured with unique values.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_WP_Salts Class
 *
 * Detects if WordPress security keys and salts are properly configured.
 * These 8 constants form the foundation of WordPress's security:
 *
 * - AUTH_KEY, SECURE_AUTH_KEY, LOGGED_IN_KEY, NONCE_KEY
 * - AUTH_SALT, SECURE_AUTH_SALT, LOGGED_IN_SALT, NONCE_SALT
 *
 * Each must be unique, at least 64 characters, and randomly generated.
 * Weak or default values allow attackers to forge authentication cookies,
 * hijack sessions, and impersonate users.
 *
 * This is one of WordPress's most critical security features. Sites with
 * weak salts are vulnerable to session hijacking and cookie forgery attacks.
 *
 * Returns critical threat level if any constant is missing, too short,
 * default, or duplicated.
 *
 * @since 1.2601.2200
 */
class Diagnostic_WP_Salts extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'wp-salts';

	/**
	 * Diagnostic title (user-facing)
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Security Keys and Salts';

	/**
	 * Diagnostic description (plain language)
	 *
	 * @var string
	 */
	protected static $description = 'Verifies all 8 security keys and salts are properly configured with unique values';

	/**
	 * Family grouping for batch operations
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Family label (human-readable)
	 *
	 * @var string
	 */
	protected static $family_label = 'Security';

	/**
	 * List of all required security constants
	 *
	 * @var array
	 */
	private static $required_constants = array(
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
	 * Default/weak values that should be replaced
	 *
	 * @var array
	 */
	private static $default_values = array(
		'put your unique phrase here',
		'@()&*#()_',
		'changeme',
		'default',
		'password',
		'secret',
		'undefined',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Verifies all 8 security constants are defined, have unique values,
	 * are at least 64 characters long, and don't contain common weak values.
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if issues detected, null if all good.
	 */
	public static function check() {
		$issues        = array();
		$defined_const = array();
		$values        = array();

		// Check each required constant
		foreach ( self::$required_constants as $const ) {
			if ( ! defined( $const ) ) {
				$issues[] = sprintf(
					/* translators: %s: constant name */
					esc_html__( '%s is not defined', 'wpshadow' ),
					$const
				);
				continue;
			}

			$value = constant( $const );
			$defined_const[] = $const;

			// Check if value is weak or default
			$value_lower = strtolower( $value );
			foreach ( self::$default_values as $weak_value ) {
				if ( false !== stripos( $value, $weak_value ) ) {
					$issues[] = sprintf(
						/* translators: %s: constant name */
						esc_html__( '%s appears to have a weak or default value', 'wpshadow' ),
						$const
					);
					break;
				}
			}

			// Check minimum length
			if ( strlen( $value ) < 64 ) {
				$issues[] = sprintf(
					/* translators: 1: constant name, 2: actual length, 3: minimum length */
					esc_html__( '%1$s is only %2$d characters (minimum recommended: %3$d)', 'wpshadow' ),
					$const,
					strlen( $value ),
					64
				);
			}

			$values[ $const ] = $value;
		}

		// Check for duplicate values
		$unique_values = array_unique( $values );
		if ( count( $unique_values ) !== count( $values ) ) {
			$issues[] = esc_html__( 'Some security constants have duplicate values. Each constant should be unique.', 'wpshadow' );
		}

		// If any issues found, return critical finding
		if ( ! empty( $issues ) ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: number of issues, 2: list of issues */
					esc_html__( 'Found %1$d security issues with WordPress keys and salts: %2$s', 'wpshadow' ),
					count( $issues ),
					implode( ', ', $issues )
				),
				'severity'           => 'critical',
				'threat_level'       => 90,
				'site_health_status' => 'critical',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/security-wp-salts',
				'family'             => self::$family,
				'details'            => array(
					'defined_constants' => $defined_const,
					'issues_found'      => $issues,
					'recommendation'    => 'Visit https://api.wordpress.org/secret-key/1.1/salt/ to generate new keys',
					'docs_link'         => 'https://wpshadow.com/kb/security-wp-salts',
				),
			);
		}

		// All security constants are properly configured
		return null;
	}
}
