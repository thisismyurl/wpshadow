<?php
/**
 * Operation Locking Diagnostic
 *
 * Checks whether long operations prevent concurrent runs.
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
 * Operation Locking Diagnostic Class
 *
 * Verifies that long tasks use locking or job queues.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Operation_Locking extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'operation-locking';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Concurrent Operations Not Prevented';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if long-running tasks prevent double execution';

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

		$has_queue = class_exists( 'ActionScheduler' ) || class_exists( 'ActionScheduler_QueueRunner' );
		$has_object_cache = function_exists( 'wp_using_ext_object_cache' ) && wp_using_ext_object_cache();

		$stats['action_scheduler'] = $has_queue ? 'enabled' : 'disabled';
		$stats['object_cache']     = $has_object_cache ? 'enabled' : 'disabled';

		if ( ! $has_queue && ! $has_object_cache ) {
			$issues[] = __( 'No clear locking or queue system detected for long-running tasks', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'When two copies of the same task run at once, they can overwrite each other or create duplicates. Locking and job queues keep long tasks safe.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/operation-locking?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
