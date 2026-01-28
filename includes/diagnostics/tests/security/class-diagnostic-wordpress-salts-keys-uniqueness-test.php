<?php
/**
 * WordPress Salts & Keys Uniqueness Test Diagnostic
 *
 * Verifies authentication salts in wp-config.php are unique, not defaults.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress Salts & Keys Uniqueness Test Class
 *
 * Tests authentication salt security.
 *
 * @since 1.26028.1905
 */
class Diagnostic_WordPress_Salts_Keys_Uniqueness_Test extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-salts-keys-uniqueness-test';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Salts & Keys Uniqueness Test';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies authentication salts in wp-config.php are unique, not defaults';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$salt_check = self::check_salt_uniqueness();
		
		if ( ! $salt_check['is_unique'] ) {
			$issues = array();
			
			if ( $salt_check['has_defaults'] ) {
				$issues[] = sprintf(
					/* translators: %d: number of default salts */
					__( '%d salt constants contain default values', 'wpshadow' ),
					$salt_check['default_count']
				);
			}

			if ( $salt_check['has_short_salts'] ) {
				$issues[] = sprintf(
					/* translators: %d: number of short salts */
					__( '%d salts <64 characters (weak)', 'wpshadow' ),
					$salt_check['short_count']
				);
			}

			if ( $salt_check['has_empty_salts'] ) {
				$issues[] = sprintf(
					/* translators: %d: number of empty salts */
					__( '%d salts are empty or undefined', 'wpshadow' ),
					$salt_check['empty_count']
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'critical',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-salts-keys-uniqueness-test',
				'meta'         => array(
					'has_defaults'    => $salt_check['has_defaults'],
					'default_count'   => $salt_check['default_count'],
					'has_short_salts' => $salt_check['has_short_salts'],
					'short_count'     => $salt_check['short_count'],
					'has_empty_salts' => $salt_check['has_empty_salts'],
					'empty_count'     => $salt_check['empty_count'],
				),
			);
		}

		return null;
	}

	/**
	 * Check salt uniqueness.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_salt_uniqueness() {
		$check = array(
			'is_unique'        => true,
			'has_defaults'     => false,
			'default_count'    => 0,
			'has_short_salts'  => false,
			'short_count'      => 0,
			'has_empty_salts'  => false,
			'empty_count'      => 0,
		);

		$salt_keys = array(
			'AUTH_KEY',
			'SECURE_AUTH_KEY',
			'LOGGED_IN_KEY',
			'NONCE_KEY',
			'AUTH_SALT',
			'SECURE_AUTH_SALT',
			'LOGGED_IN_SALT',
			'NONCE_SALT',
		);

		foreach ( $salt_keys as $key ) {
			if ( ! defined( $key ) ) {
				++$check['empty_count'];
				$check['has_empty_salts'] = true;
				$check['is_unique'] = false;
				continue;
			}

			$value = constant( $key );

			// Check for empty values.
			if ( empty( $value ) ) {
				++$check['empty_count'];
				$check['has_empty_salts'] = true;
				$check['is_unique'] = false;
				continue;
			}

			// Check for default placeholder text.
			if ( false !== strpos( strtolower( $value ), 'put your unique phrase here' ) ) {
				++$check['default_count'];
				$check['has_defaults'] = true;
				$check['is_unique'] = false;
			}

			// Check length (should be 64 characters).
			if ( strlen( $value ) < 64 ) {
				++$check['short_count'];
				$check['has_short_salts'] = true;
				$check['is_unique'] = false;
			}
		}

		return $check;
	}
}
