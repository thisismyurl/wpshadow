<?php
/**
 * Wordpress Table Optimization Diagnostic
 *
 * Wordpress Table Optimization issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1278.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Table Optimization Diagnostic Class
 *
 * @since 1.1278.0000
 */
class Diagnostic_WordpressTableOptimization extends Diagnostic_Base {

	protected static $slug = 'wordpress-table-optimization';
	protected static $title = 'Wordpress Table Optimization';
	protected static $description = 'Wordpress Table Optimization issue detected';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();
		global $wpdb;

		// Check 1: Automatic optimization enabled
		$auto_optimize = get_option( 'db_auto_optimize', 0 );
		if ( ! $auto_optimize ) {
			$issues[] = 'Automatic table optimization not enabled';
		}

		// Check 2: Optimization schedule configured
		$optimize_schedule = get_option( 'db_optimize_schedule', '' );
		if ( empty( $optimize_schedule ) ) {
			$issues[] = 'Optimization schedule not configured';
		}

		// Check 3: Fragmentation threshold set
		$frag_threshold = absint( get_option( 'db_fragmentation_threshold', 0 ) );
		if ( $frag_threshold <= 0 ) {
			$issues[] = 'Fragmentation threshold not configured';
		}

		// Check 4: Table analysis enabled
		$table_analysis = get_option( 'db_table_analysis', 0 );
		if ( ! $table_analysis ) {
			$issues[] = 'Table analysis not enabled';
		}

		// Check 5: Repair on error enabled
		$repair_error = get_option( 'db_repair_on_error', 0 );
		if ( ! $repair_error ) {
			$issues[] = 'Auto-repair on error not enabled';
		}

		// Check 6: Optimization logging
		$opt_logging = get_option( 'db_optimize_logging', 0 );
		if ( ! $opt_logging ) {
			$issues[] = 'Optimization logging not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d table optimization issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wordpress-table-optimization',
			);
		}

		return null;
	}
}
