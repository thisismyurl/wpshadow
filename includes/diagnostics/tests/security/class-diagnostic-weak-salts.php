<?php
/**
 * Weak WordPress Salt/Security Keys Diagnostic
 *
 * Detects if wp-config.php has default WordPress.org salt values.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Weak WordPress Salts Class
 *
 * Validates security keys are unique, not defaults.
 * Default keys compromise sessions across millions of sites.
 *
 * @since 1.5029.1200
 */
class Diagnostic_Weak_Salts extends Diagnostic_Base {

	protected static $slug        = 'weak-wordpress-salts';
	protected static $title       = 'Weak WordPress Salt/Security Keys';
	protected static $description = 'Validates security keys are unique';
	protected static $family      = 'security';

	public static function check() {
		$cache_key = 'wpshadow_weak_salts_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Check security constants (NO $wpdb usage - these are PHP constants).
		$salt_constants = array(
			'AUTH_KEY',
			'SECURE_AUTH_KEY',
			'LOGGED_IN_KEY',
			'NONCE_KEY',
			'AUTH_SALT',
			'SECURE_AUTH_SALT',
			'LOGGED_IN_SALT',
			'NONCE_SALT',
		);

		$weak_keys = array();
		$default_patterns = array( 'put your unique phrase here', 'unique phrase' );

		foreach ( $salt_constants as $constant ) {
			if ( defined( $constant ) ) {
				$value = constant( $constant );

				// Check if it's a default value.
				foreach ( $default_patterns as $pattern ) {
					if ( stripos( $value, $pattern ) !== false ) {
						$weak_keys[] = $constant;
						break;
					}
				}

				// Check if it's too short (< 20 chars).
				if ( strlen( $value ) < 20 ) {
					$weak_keys[] = $constant;
				}
			} else {
				$weak_keys[] = $constant . ' (not defined)';
			}
		}

		if ( ! empty( $weak_keys ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of weak keys */
					__( '%d security keys are weak or default. Regenerate immediately for session security.', 'wpshadow' ),
					count( $weak_keys )
				),
				'severity'     => 'critical',
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/security-weak-wordpress-salts',
				'data'         => array(
					'weak_keys'         => $weak_keys,
					'total_keys'        => count( $salt_constants ),
					'regeneration_url'  => 'https://api.wordpress.org/secret-key/1.1/salt/',
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
