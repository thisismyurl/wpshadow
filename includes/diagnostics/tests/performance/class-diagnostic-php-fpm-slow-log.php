<?php
/**
 * Diagnostic: PHP-FPM Slow Log
 *
 * Checks if PHP-FPM slow log is enabled to track slow requests.
 * Slow logs help identify performance bottlenecks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Php_Fpm_Slow_Log
 *
 * Tests PHP-FPM slow log configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Fpm_Slow_Log extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-fpm-slow-log';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP-FPM Slow Log';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP-FPM slow log is enabled';

	/**
	 * Check PHP-FPM slow log configuration.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if running PHP-FPM.
		$is_php_fpm = false;

		if ( function_exists( 'php_sapi_name' ) ) {
			$sapi = php_sapi_name();
			if ( strpos( $sapi, 'fpm' ) !== false ) {
				$is_php_fpm = true;
			}
		}

		if ( ! $is_php_fpm ) {
			return null; // Not applicable if not using PHP-FPM.
		}

		// Check for FPM_SLOW_LOG_TIMEOUT environment variable.
		$slow_log_timeout = getenv( 'FPM_SLOW_LOG_TIMEOUT' );

		// Check for FPM_SLOW_LOG_PATH environment variable.
		$slow_log_path = getenv( 'FPM_SLOW_LOG_PATH' );

		// If slow log is not configured.
		if ( false === $slow_log_timeout && false === $slow_log_path ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP-FPM slow log is not configured. Enabling it can help identify performance bottlenecks by logging requests that exceed a specified execution time.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_fpm_slow_log',
				'meta'        => array(
					'is_php_fpm'       => true,
					'slow_log_enabled' => false,
				),
			);
		}

		// Check if slow log timeout is reasonable (5-10 seconds typical).
		if ( false !== $slow_log_timeout ) {
			$timeout = (int) $slow_log_timeout;

			if ( $timeout < 3 ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => sprintf(
						/* translators: %d: Timeout in seconds */
						__( 'PHP-FPM slow log timeout is set to %d seconds, which is very aggressive. Consider increasing it to 5-10 seconds to reduce log noise.', 'wpshadow' ),
						$timeout
					),
					'severity'    => 'info',
					'threat_level' => 20,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/php_fpm_slow_log',
					'meta'        => array(
						'is_php_fpm'       => true,
						'slow_log_enabled' => true,
						'timeout'          => $timeout,
					),
				);
			}
		}

		// PHP-FPM slow log is properly configured.
		return null;
	}
}
