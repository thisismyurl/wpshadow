<?php
/**
 * Multisite Database Optimization Diagnostic
 *
 * Multisite Database Optimization misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.976.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Database Optimization Diagnostic Class
 *
 * @since 1.976.0000
 */
class Diagnostic_MultisiteDatabaseOptimization extends Diagnostic_Base {

	protected static $slug = 'multisite-database-optimization';
	protected static $title = 'Multisite Database Optimization';
	protected static $description = 'Multisite Database Optimization misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}

		$issues = array();

		// Check 1: Network-wide optimization
		$network_opt = get_site_option( 'multisite_db_optimization', 0 );
		if ( ! $network_opt ) {
			$issues[] = 'Network-wide optimization not enabled';
		}

		// Check 2: Per-site optimization
		$site_opt = get_option( 'multisite_per_site_optimization', 0 );
		if ( ! $site_opt ) {
			$issues[] = 'Per-site optimization not enabled';
		}

		// Check 3: Cross-site cleanup
		$cross_cleanup = get_site_option( 'multisite_cross_site_cleanup', 0 );
		if ( ! $cross_cleanup ) {
			$issues[] = 'Cross-site cleanup not enabled';
		}

		// Check 4: Blog/site table optimization
		$table_opt = get_option( 'multisite_table_optimization', 0 );
		if ( ! $table_opt ) {
			$issues[] = 'Table optimization not configured';
		}

		// Check 5: Network cache clearing
		$cache_clear = get_option( 'multisite_network_cache_clear', 0 );
		if ( ! $cache_clear ) {
			$issues[] = 'Network cache clearing not enabled';
		}

		// Check 6: Scheduled optimization
		$scheduled = get_site_option( 'multisite_scheduled_optimization', 0 );
		if ( ! $scheduled ) {
			$issues[] = 'Scheduled optimization not configured';
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
					'Found %d multisite DB optimization issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/multisite-database-optimization',
			);
		}

		return null;
	}
}
