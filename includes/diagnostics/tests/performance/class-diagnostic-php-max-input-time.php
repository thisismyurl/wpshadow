<?php
/**
 * Diagnostic: PHP max_input_time
 *
 * Checks if PHP max_input_time is adequate for handling large uploads.
 * Too low can cause timeouts during file uploads or form submissions.
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
 * Class Diagnostic_Php_Max_Input_Time
 *
 * Tests PHP max_input_time configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Max_Input_Time extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-max-input-time';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP max_input_time';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP max_input_time is adequate';

	/**
	 * Check PHP max_input_time setting.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Get max_input_time setting.
		$max_input_time = ini_get( 'max_input_time' );

		// Convert to integer.
		$max_input_time = (int) $max_input_time;

		// -1 means unlimited (use max_execution_time instead).
		if ( -1 === $max_input_time ) {
			$max_execution_time = (int) ini_get( 'max_execution_time' );

			if ( 0 === $max_execution_time ) {
				return null; // Both unlimited, nothing to warn about.
			}

			if ( $max_execution_time < 60 ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => sprintf(
						/* translators: %d: max_execution_time in seconds */
						__( 'PHP max_input_time is set to -1 (uses max_execution_time), but max_execution_time is only %d seconds. This may be too low for large file uploads.', 'wpshadow' ),
						$max_execution_time
					),
					'severity'    => 'info',
					'threat_level' => 25,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/php_max_input_time',
					'meta'        => array(
						'max_input_time'     => -1,
						'max_execution_time' => $max_execution_time,
					),
				);
			}

			return null;
		}

		// 0 means unlimited.
		if ( 0 === $max_input_time ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP max_input_time is set to 0 (unlimited). While this prevents upload timeouts, it may allow slow clients to tie up server resources. Consider setting a reasonable limit like 300 seconds.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_max_input_time',
				'meta'        => array(
					'max_input_time' => 0,
				),
			);
		}

		// Recommended minimum for WordPress.
		$recommended_min = 60;

		// Warn if too low.
		if ( $max_input_time < $recommended_min ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: Current value in seconds, 2: Recommended minimum in seconds */
					__( 'PHP max_input_time is set to %1$d seconds, which is below the recommended minimum of %2$d seconds. This may cause timeouts during file uploads or large form submissions.', 'wpshadow' ),
					$max_input_time,
					$recommended_min
				),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_max_input_time',
				'meta'        => array(
					'max_input_time'  => $max_input_time,
					'recommended_min' => $recommended_min,
				),
			);
		}

		// Check if max_input_time is significantly lower than max_execution_time.
		$max_execution_time = (int) ini_get( 'max_execution_time' );

		if ( $max_execution_time > 0 && $max_input_time < ( $max_execution_time / 2 ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: max_input_time, 2: max_execution_time */
					__( 'PHP max_input_time (%1$d seconds) is significantly lower than max_execution_time (%2$d seconds). Consider setting them to similar values for consistency.', 'wpshadow' ),
					$max_input_time,
					$max_execution_time
				),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_max_input_time',
				'meta'        => array(
					'max_input_time'     => $max_input_time,
					'max_execution_time' => $max_execution_time,
				),
			);
		}

		// PHP max_input_time is properly configured.
		return null;
	}
}
