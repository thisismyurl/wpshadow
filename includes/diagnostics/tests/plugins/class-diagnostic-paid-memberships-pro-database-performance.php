<?php
/**
 * Paid Memberships Pro Database Performance Diagnostic
 *
 * PMPro database queries slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.338.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paid Memberships Pro Database Performance Diagnostic Class
 *
 * @since 1.338.0000
 */
class Diagnostic_PaidMembershipsProDatabasePerformance extends Diagnostic_Base {

	protected static $slug = 'paid-memberships-pro-database-performance';
	protected static $title = 'Paid Memberships Pro Database Performance';
	protected static $description = 'PMPro database queries slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'PMPRO_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Query optimization
		$query = get_option( 'pmpro_query_optimization_enabled', 0 );
		if ( ! $query ) {
			$issues[] = 'Query optimization not enabled';
		}
		
		// Check 2: Membership level caching
		$cache = get_option( 'pmpro_membership_level_caching_enabled', 0 );
		if ( ! $cache ) {
			$issues[] = 'Membership level caching not enabled';
		}
		
		// Check 3: User meta optimization
		$meta = get_option( 'pmpro_user_meta_optimization_enabled', 0 );
		if ( ! $meta ) {
			$issues[] = 'User meta optimization not enabled';
		}
		
		// Check 4: Transaction logging
		$logging = get_option( 'pmpro_transaction_logging_optimized', 0 );
		if ( ! $logging ) {
			$issues[] = 'Transaction logging not optimized';
		}
		
		// Check 5: Relationship indexing
		$indexing = get_option( 'pmpro_table_indexing_enabled', 0 );
		if ( ! $indexing ) {
			$issues[] = 'Database indexing not enabled';
		}
		
		// Check 6: Archive old data
		$archive = get_option( 'pmpro_data_archiving_enabled', 0 );
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
					'Found %d database performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/paid-memberships-pro-database-performance',
			);
		}
		
		return null;
	}
}
