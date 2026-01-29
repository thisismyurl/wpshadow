<?php
/**
 * Wordfence Performance Impact
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Wordfence_Performance extends Diagnostic_Base {

	protected static $slug        = 'wordfence-performance';
	protected static $title       = 'Wordfence Performance Impact';
	protected static $description = 'Checks Wordfence scan scheduling';
	protected static $family      = 'performance';

	public static function check() {
		$cache_key = 'wpshadow_wordfence_performance';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		if ( ! class_exists( 'wordfence' ) ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$issues = array();

		// Check scan scheduling.
		$scan_schedule = get_option( 'wordfence_scanSchedule', 'daily' );
		
		if ( 'realtime' === $scan_schedule ) {
			$issues[] = 'Real-time scanning enabled (high performance impact)';
		}

		// Check live traffic monitoring.
		$live_traffic = get_option( 'wordfence_liveTrafficEnabled', '1' );
		
		if ( '1' === $live_traffic ) {
			$issues[] = 'Live traffic monitoring enabled';
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Wordfence settings may impact performance. Optimize scan schedule.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/wordfence-performance',
				'data'         => array(
					'issues' => $issues,
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
