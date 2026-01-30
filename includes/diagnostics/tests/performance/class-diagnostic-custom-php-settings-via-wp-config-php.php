<?php
/**
 * Custom PHP Settings via wp-config.php Diagnostic
 *
 * Validates PHP configuration overrides set in wp-config.php.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26030.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom PHP Settings via wp-config.php Class
 *
 * Tests PHP settings configuration.
 *
 * @since 1.26030.0000
 */
class Diagnostic_Custom_Php_Settings_Via_Wp_Config_Php extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-php-settings-via-wp-config-php';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom PHP Settings via wp-config.php';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates PHP configuration overrides set in wp-config.php';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26030.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$php_check = self::check_php_settings();
		
		if ( $php_check['has_concerns'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $php_check['concerns'] ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/custom-php-settings-via-wp-config-php',
				'meta'         => array(
					'display_errors'      => $php_check['display_errors'],
					'max_execution_time'  => $php_check['max_execution_time'],
					'recommendations'     => $php_check['recommendations'],
				),
			);
		}

		return null;
	}

	/**
	 * Check PHP settings in wp-config.php.
	 *
	 * @since  1.26030.0000
	 * @return array Check results.
	 */
	private static function check_php_settings() {
		$check = array(
			'has_concerns'       => false,
			'concerns'           => array(),
			'display_errors'     => ini_get( 'display_errors' ),
			'max_execution_time' => ini_get( 'max_execution_time' ),
			'recommendations'    => array(),
		);

		// Check if display_errors is On (bad for production).
		if ( '1' === $check['display_errors'] || 'On' === $check['display_errors'] ) {
			if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
				$check['has_concerns'] = true;
				$check['concerns'][] = __( 'display_errors is On in production (exposes sensitive information)', 'wpshadow' );
				$check['recommendations'][] = __( 'Set display_errors Off in php.ini for production environments', 'wpshadow' );
			}
		}

		// Check max_execution_time.
		if ( '0' === $check['max_execution_time'] || (int) $check['max_execution_time'] > 300 ) {
			$check['has_concerns'] = true;
			$check['concerns'][] = sprintf(
				/* translators: %s: max_execution_time value */
				__( 'max_execution_time set to %s (too high, can hide performance issues)', 'wpshadow' ),
				'0' === $check['max_execution_time'] ? 'unlimited' : $check['max_execution_time'] . 's'
			);
			$check['recommendations'][] = __( 'Set max_execution_time to 60-120 seconds to catch performance problems', 'wpshadow' );
		}

		// Check memory_limit.
		$memory_limit = ini_get( 'memory_limit' );
		
		if ( false !== $memory_limit ) {
			$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );
			
			// Flag if very low or very high.
			if ( $memory_bytes < 134217728 ) { // Less than 128MB.
				$check['has_concerns'] = true;
				$check['concerns'][] = sprintf(
					/* translators: %s: memory limit */
					__( 'PHP memory_limit is %s (too low for WordPress - should be 256M+)', 'wpshadow' ),
					$memory_limit
				);
				$check['recommendations'][] = __( 'Increase memory_limit in php.ini to 256M for optimal WordPress performance', 'wpshadow' );
			} elseif ( $memory_bytes > 536870912 ) { // More than 512MB.
				$check['has_concerns'] = true;
				$check['concerns'][] = sprintf(
					/* translators: %s: memory limit */
					__( 'PHP memory_limit is %s (unusually high - may hide memory leaks)', 'wpshadow' ),
					$memory_limit
				);
				$check['recommendations'][] = __( 'Review why memory limit is so high - fix underlying issues instead', 'wpshadow' );
			}
		}

		return $check;
	}
}
