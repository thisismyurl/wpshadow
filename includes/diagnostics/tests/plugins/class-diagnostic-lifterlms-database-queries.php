<?php
/**
 * LifterLMS Database Queries Diagnostic
 *
 * LifterLMS database not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.370.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LifterLMS Database Queries Diagnostic Class
 *
 * @since 1.370.0000
 */
class Diagnostic_LifterlmsDatabaseQueries extends Diagnostic_Base {

	protected static $slug = 'lifterlms-database-queries';
	protected static $title = 'LifterLMS Database Queries';
	protected static $description = 'LifterLMS database not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'LLMS' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Query optimization enabled
		$opt = get_option( 'llms_query_optimization_enabled', 0 );
		if ( ! $opt ) {
			$issues[] = 'Query optimization not enabled';
		}

		// Check 2: Database indexing
		$indexing = get_option( 'llms_database_indexing_enabled', 0 );
		if ( ! $indexing ) {
			$issues[] = 'Database indexing not enabled';
		}

		// Check 3: Post meta optimization
		$meta = get_option( 'llms_post_meta_optimization_enabled', 0 );
		if ( ! $meta ) {
			$issues[] = 'Post meta optimization not enabled';
		}

		// Check 4: Query caching
		$cache = get_option( 'llms_query_caching_enabled', 0 );
		if ( ! $cache ) {
			$issues[] = 'Query caching not enabled';
		}

		// Check 5: User meta optimization
		$user_meta = get_option( 'llms_user_meta_optimization_enabled', 0 );
		if ( ! $user_meta ) {
			$issues[] = 'User meta optimization not enabled';
		}

		// Check 6: Archive old data
		$archive = get_option( 'llms_data_archiving_enabled', 0 );
		if ( ! $archive ) {
			$issues[] = 'Data archiving not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 35;
			$threat_multiplier = 6;
			$max_threat = 65;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d database query issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/lifterlms-database-queries',
			);
		}

		return null;
	}
}
