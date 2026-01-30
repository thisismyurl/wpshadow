<?php
/**
 * Diagnostic: PHP error_reporting Level
 *
 * Checks if PHP error_reporting is properly configured.
 * Production sites should have minimal error reporting; development sites should have full reporting.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Php_Error_Reporting
 *
 * Tests PHP error_reporting configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Error_Reporting extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-error-reporting';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP error_reporting Level';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP error_reporting is properly configured';

	/**
	 * Check PHP error_reporting level.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Get current error_reporting level.
		$error_reporting = error_reporting();

		// Check if WP_DEBUG is enabled (development mode).
		$is_debug_mode = defined( 'WP_DEBUG' ) && WP_DEBUG;

		// Production mode: Should have minimal error reporting.
		if ( ! $is_debug_mode ) {
			// Check if E_ALL is set (too verbose for production).
			if ( ( $error_reporting & E_ALL ) === E_ALL ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => __( 'PHP error_reporting is set to E_ALL in production mode. This may expose sensitive information in error messages. Consider setting it to E_ERROR | E_WARNING | E_PARSE.', 'wpshadow' ),
					'severity'    => 'low',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/php_error_reporting',
					'meta'        => array(
						'error_reporting' => $error_reporting,
						'wp_debug'        => false,
					),
				);
			}

			// Check if E_NOTICE is enabled (can be noisy in production).
			if ( ( $error_reporting & E_NOTICE ) === E_NOTICE ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => __( 'PHP error_reporting includes E_NOTICE in production mode. This may cause excessive error logging. Consider disabling notices in production.', 'wpshadow' ),
					'severity'    => 'info',
					'threat_level' => 20,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/php_error_reporting',
					'meta'        => array(
						'error_reporting' => $error_reporting,
						'wp_debug'        => false,
					),
				);
			}
		}

		// Development mode: Should have full error reporting.
		if ( $is_debug_mode ) {
			// Check if error reporting is too restrictive.
			if ( 0 === $error_reporting ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => __( 'PHP error_reporting is disabled (0) but WP_DEBUG is enabled. Enable error reporting to see PHP errors during development.', 'wpshadow' ),
					'severity'    => 'info',
					'threat_level' => 25,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/php_error_reporting',
					'meta'        => array(
						'error_reporting' => $error_reporting,
						'wp_debug'        => true,
					),
				);
			}

			// Check if E_ALL is not set (should be in development).
			if ( ( $error_reporting & E_ALL ) !== E_ALL ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => __( 'PHP error_reporting should be set to E_ALL in development mode (WP_DEBUG is enabled). This helps identify all PHP issues during development.', 'wpshadow' ),
					'severity'    => 'info',
					'threat_level' => 20,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/php_error_reporting',
					'meta'        => array(
						'error_reporting' => $error_reporting,
						'wp_debug'        => true,
					),
				);
			}
		}

		// PHP error_reporting is properly configured for the environment.
		return null;
	}
}
