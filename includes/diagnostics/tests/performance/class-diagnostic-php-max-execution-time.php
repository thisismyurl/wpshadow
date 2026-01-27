<?php
/**
 * Diagnostic: PHP max_execution_time
 *
 * Checks if PHP max_execution_time is adequate for WordPress operations.
 * Too low can cause timeouts during imports, backups, or plugin operations.
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
 * Class Diagnostic_Php_Max_Execution_Time
 *
 * Tests PHP max_execution_time configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Max_Execution_Time extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-max-execution-time';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP max_execution_time';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP max_execution_time is adequate';

	/**
	 * Check PHP max_execution_time setting.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Get max_execution_time setting.
		$max_execution_time = ini_get( 'max_execution_time' );

		// Convert to integer.
		$max_execution_time = (int) $max_execution_time;

		// 0 means unlimited (allowed but warn about it).
		if ( 0 === $max_execution_time ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP max_execution_time is set to 0 (unlimited). While this prevents timeouts, it may allow runaway scripts to consume server resources indefinitely. Consider setting a reasonable limit like 300 seconds.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_max_execution_time',
				'meta'        => array(
					'max_execution_time' => 0,
				),
			);
		}

		// Recommended minimum for WordPress.
		$recommended_min = 60;

		// Warn if too low.
		if ( $max_execution_time < $recommended_min ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: Current value in seconds, 2: Recommended minimum in seconds */
					__( 'PHP max_execution_time is set to %1$d seconds, which is below the recommended minimum of %2$d seconds for WordPress. This may cause timeouts during imports, backups, or plugin operations.', 'wpshadow' ),
					$max_execution_time,
					$recommended_min
				),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_max_execution_time',
				'meta'        => array(
					'max_execution_time' => $max_execution_time,
					'recommended_min'    => $recommended_min,
				),
			);
		}

		// Warn if very high (potential security risk).
		$maximum_safe = 600; // 10 minutes.

		if ( $max_execution_time > $maximum_safe ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Current value in seconds */
					__( 'PHP max_execution_time is set to %d seconds, which is very high. While this prevents timeouts, it may allow resource-intensive scripts to run too long. Consider using 300 seconds (5 minutes) unless specific operations require longer.', 'wpshadow' ),
					$max_execution_time
				),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_max_execution_time',
				'meta'        => array(
					'max_execution_time' => $max_execution_time,
				),
			);
		}

		// PHP max_execution_time is properly configured.
		return null;
	}
}
