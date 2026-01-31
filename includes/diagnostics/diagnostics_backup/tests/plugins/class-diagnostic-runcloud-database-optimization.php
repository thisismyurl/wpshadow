<?php
/**
 * Runcloud Database Optimization Diagnostic
 *
 * Runcloud Database Optimization needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1026.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Runcloud Database Optimization Diagnostic Class
 *
 * @since 1.1026.0000
 */
class Diagnostic_RuncloudDatabaseOptimization extends Diagnostic_Base {

	protected static $slug = 'runcloud-database-optimization';
	protected static $title = 'Runcloud Database Optimization';
	protected static $description = 'Runcloud Database Optimization needs attention';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();

		// Check 1: Optimization schedule configured
		$schedule = get_option( 'runcloud_db_optimize_schedule', '' );
		if ( empty( $schedule ) ) {
			$issues[] = 'Optimization schedule not configured';
		}

		// Check 2: Table optimization enabled
		$table_opt = get_option( 'runcloud_table_optimization', false );
		if ( ! $table_opt ) {
			$issues[] = 'Table optimization disabled';
		}

		// Check 3: Index optimization enabled
		$index_opt = get_option( 'runcloud_index_optimization', false );
		if ( ! $index_opt ) {
			$issues[] = 'Index optimization disabled';
		}

		// Check 4: Auto-repair enabled
		$auto_repair = get_option( 'runcloud_auto_repair', false );
		if ( ! $auto_repair ) {
			$issues[] = 'Auto-repair disabled';
		}

		// Check 5: Query cache optimization
		$query_cache = get_option( 'runcloud_query_cache_enabled', false );
		if ( ! $query_cache ) {
			$issues[] = 'Query cache disabled';
		}

		// Check 6: Database cleanup enabled
		$cleanup = get_option( 'runcloud_db_cleanup_enabled', false );
		if ( ! $cleanup ) {
			$issues[] = 'Database cleanup disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'RunCloud database optimization issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/runcloud-database-optimization',
			);
		}

		return null;
	}
}
