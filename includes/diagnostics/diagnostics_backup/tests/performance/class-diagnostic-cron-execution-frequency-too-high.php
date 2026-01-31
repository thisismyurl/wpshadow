<?php
/**
 * Cron Execution Frequency Too High Diagnostic
 *
 * Checks if cron execution frequency is reasonable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cron Execution Frequency Too High Diagnostic Class
 *
 * Detects excessive cron frequency.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Cron_Execution_Frequency_Too_High extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cron-execution-frequency-too-high';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cron Execution Frequency Too High';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if cron frequency is excessive';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_filter;

		// Count scheduled cron events
		$crons = _get_cron_array();
		$cron_count = 0;

		if ( is_array( $crons ) ) {
			foreach ( $crons as $event ) {
				if ( is_array( $event ) ) {
					$cron_count += count( $event );
				}
			}
		}

		if ( $cron_count > 50 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d scheduled cron events registered. Many cron jobs may impact performance. Review and remove unnecessary schedules.', 'wpshadow' ),
					absint( $cron_count )
				),
				'severity'      => 'low',
				'threat_level'  => 25,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/cron-execution-frequency-too-high',
			);
		}

		return null;
	}
}
