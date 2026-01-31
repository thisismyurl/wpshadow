<?php
/**
 * Authentication Salts Uniqueness Validation Diagnostic
 *
 * Ensures 8 authentication keys/salts are unique, not default install values.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Authentication Salts Uniqueness Validation Class
 *
 * Tests authentication salts security.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Authentication_Salts_Uniqueness_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'authentication-salts-uniqueness-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Authentication Salts Uniqueness Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures 8 authentication keys/salts are unique, not default install values';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$salts_check = self::check_authentication_salts();
		
		if ( $salts_check['has_vulnerabilities'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $salts_check['vulnerabilities'] ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/authentication-salts-uniqueness-validation',
				'meta'         => array(
					'weak_salts_count' => $salts_check['weak_salts_count'],
					'default_detected' => $salts_check['default_detected'],
				),
			);
		}

		return null;
	}

	/**
	 * Check authentication salts security.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_authentication_salts() {
		$check = array(
			'has_vulnerabilities' => false,
			'vulnerabilities'     => array(),
			'weak_salts_count'    => 0,
			'default_detected'    => false,
		);

		// Required salt constants.
		$required_salts = array(
			'AUTH_KEY',
			'SECURE_AUTH_KEY',
			'LOGGED_IN_KEY',
			'NONCE_KEY',
			'AUTH_SALT',
			'SECURE_AUTH_SALT',
			'LOGGED_IN_SALT',
			'NONCE_SALT',
		);

		$weak_salts = 0;

		foreach ( $required_salts as $salt_name ) {
			if ( ! defined( $salt_name ) ) {
				$check['has_vulnerabilities'] = true;
				$check['vulnerabilities'][] = sprintf(
					/* translators: %s: constant name */
					__( '%s not defined (critical security vulnerability)', 'wpshadow' ),
					$salt_name
				);
				++$weak_salts;
				continue;
			}

			$salt_value = constant( $salt_name );

			// Check for default placeholder.
			if ( str_contains( strtolower( $salt_value ), 'put your unique phrase here' ) ) {
				$check['has_vulnerabilities'] = true;
				$check['default_detected'] = true;
				$check['vulnerabilities'][] = sprintf(
					/* translators: %s: constant name */
					__( '%s contains default placeholder (allows session hijacking)', 'wpshadow' ),
					$salt_name
				);
				++$weak_salts;
				continue;
			}

			// Check for empty value.
			if ( empty( $salt_value ) ) {
				$check['has_vulnerabilities'] = true;
				$check['vulnerabilities'][] = sprintf(
					/* translators: %s: constant name */
					__( '%s is empty (no authentication security)', 'wpshadow' ),
					$salt_name
				);
				++$weak_salts;
				continue;
			}

			// Check length (should be 64 characters for strong security).
			if ( strlen( $salt_value ) < 64 ) {
				$check['has_vulnerabilities'] = true;
				$check['vulnerabilities'][] = sprintf(
					/* translators: 1: constant name, 2: length */
					__( '%1$s is too short (%2$d characters, recommend 64+)', 'wpshadow' ),
					$salt_name,
					strlen( $salt_value )
				);
				++$weak_salts;
			}
		}

		$check['weak_salts_count'] = $weak_salts;

		return $check;
	}
}
