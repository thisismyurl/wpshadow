<?php
/**
 * Database Write Verification Diagnostic
 *
 * Checks whether database write failures are detected and surfaced.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Reliability
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Write Verification Diagnostic Class
 *
 * Helps confirm that database write failures are visible and can be handled.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Database_Write_Verification extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'database-write-verification';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Database Writes Don\'t Verify Success';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database write failures are visible and handled';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$debug_enabled = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$debug_log     = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
		$save_queries  = defined( 'SAVEQUERIES' ) && SAVEQUERIES;

		$stats['wp_debug']     = $debug_enabled ? 'enabled' : 'disabled';
		$stats['wp_debug_log'] = $debug_log ? 'enabled' : 'disabled';
		$stats['savequeries']  = $save_queries ? 'enabled' : 'disabled';

		if ( ! $debug_log && ! $save_queries ) {
			$issues[] = __( 'Database write failures are not logged, so silent saves are harder to catch', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'When a database save fails, visitors should not be told everything worked. Logging and error checks make sure you can detect failures and respond with a clear message.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-write-verification?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
