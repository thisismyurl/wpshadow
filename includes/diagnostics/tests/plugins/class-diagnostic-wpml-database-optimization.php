<?php
/**
 * WPML Database Optimization Diagnostic
 *
 * WPML tables causing performance issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.302.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPML Database Optimization Diagnostic Class
 *
 * @since 1.302.0000
 */
class Diagnostic_WpmlDatabaseOptimization extends Diagnostic_Base {

	protected static $slug = 'wpml-database-optimization';
	protected static $title = 'WPML Database Optimization';
	protected static $description = 'WPML tables causing performance issues';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Database indexing
		$indexing = get_option( 'wpml_database_indexing_enabled', 0 );
		if ( ! $indexing ) {
			$issues[] = 'Database indexing not enabled';
		}
		
		// Check 2: Query optimization
		$opt = get_option( 'wpml_query_optimization_enabled', 0 );
		if ( ! $opt ) {
			$issues[] = 'Query optimization not enabled';
		}
		
		// Check 3: Table cleanup
		$cleanup = get_option( 'wpml_table_cleanup_enabled', 0 );
		if ( ! $cleanup ) {
			$issues[] = 'Database table cleanup not enabled';
		}
		
		// Check 4: Orphaned content cleanup
		$orphaned = get_option( 'wpml_orphaned_content_cleanup_enabled', 0 );
		if ( ! $orphaned ) {
			$issues[] = 'Orphaned content cleanup not enabled';
		}
		
		// Check 5: Cache clearing
		$cache = get_option( 'wpml_translation_cache_clearing_enabled', 0 );
		if ( ! $cache ) {
			$issues[] = 'Translation cache clearing not configured';
		}
		
		// Check 6: Scheduled optimization
		$schedule = get_option( 'wpml_scheduled_optimization_enabled', 0 );
		if ( ! $schedule ) {
			$issues[] = 'Scheduled optimization not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d database optimization issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wpml-database-optimization',
			);
		}
		
		return null;
	}
}
