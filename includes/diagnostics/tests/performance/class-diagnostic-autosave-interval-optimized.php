<?php
/**
 * Autosave Interval Optimized Diagnostic
 *
 * Checks whether the WordPress AUTOSAVE_INTERVAL constant is set to a
 * healthy range between 30 and 300 seconds.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_Server_Environment_Helper as Server_Env;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Autosave_Interval_Optimized Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Autosave_Interval_Optimized extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'autosave-interval-optimized';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Autosave Interval Optimized';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the AUTOSAVE_INTERVAL constant is configured optimally — too short increases database write load, while too long risks data loss on crashes.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Reads AUTOSAVE_INTERVAL via the Server_Env helper. Flags intervals below
	 * 30 s (too aggressive on the DB) or above 300 s (data-loss risk).
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$interval = Server_Env::get_autosave_interval();

		// An interval of 30–300 seconds is a healthy range.
		if ( $interval >= 30 && $interval <= 300 ) {
			return null;
		}

		if ( $interval < 30 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: autosave interval in seconds */
					__( 'AUTOSAVE_INTERVAL is set to %d seconds — very aggressively. WordPress triggers a database write on every autosave. An interval this short on busy sites can significantly increase database load, especially with many concurrent editors.', 'wpshadow' ),
					$interval
				),
				'severity'     => 'low',
				'threat_level' => 20,
				'details'      => array(
					'autosave_interval_seconds' => $interval,
					'recommended_minimum'        => 60,
				),
			);
		}

		// Interval > 300 seconds: risk of data loss if browser crashes.
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: autosave interval in seconds */
				__( 'AUTOSAVE_INTERVAL is set to %d seconds — longer than recommended. Authors may lose up to %d seconds of unsaved edits if their browser crashes or the connection drops.', 'wpshadow' ),
				$interval,
				$interval
			),
			'severity'     => 'low',
			'threat_level' => 15,
			'details'      => array(
				'autosave_interval_seconds' => $interval,
				'recommended_maximum'        => 300,
			),
		);
	}
}
