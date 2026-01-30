<?php
/**
 * Rank Math Redirects Database Diagnostic
 *
 * Rank Math Redirects Database configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.695.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rank Math Redirects Database Diagnostic Class
 *
 * @since 1.695.0000
 */
class Diagnostic_RankMathRedirectsDatabase extends Diagnostic_Base {

	protected static $slug = 'rank-math-redirects-database';
	protected static $title = 'Rank Math Redirects Database';
	protected static $description = 'Rank Math Redirects Database configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'RANK_MATH_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Redirects table exists
		$table_name = $wpdb->prefix . 'rank_math_redirections';
		$table_exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
				DB_NAME,
				$table_name
			)
		);
		
		if ( ! $table_exists ) {
			return null;
		}
		
		// Check 2: Total redirects count
		$redirect_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
		
		if ( $redirect_count > 1000 ) {
			$issues[] = sprintf( __( '%d redirects in database (performance impact)', 'wpshadow' ), $redirect_count );
		}
		
		// Check 3: Regex redirects (expensive)
		$regex_redirects = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE url_to LIKE %s OR url_to LIKE %s",
				'%*%',
				'%regex%'
			)
		);
		
		if ( $regex_redirects > 50 ) {
			$issues[] = sprintf( __( '%d regex redirects (slow pattern matching)', 'wpshadow' ), $regex_redirects );
		}
		
		// Check 4: Redirect chains
		$chains = $wpdb->get_var(
			"SELECT COUNT(DISTINCT r1.id) FROM {$table_name} r1
			 INNER JOIN {$table_name} r2 ON r1.url_to = r2.sources"
		);
		
		if ( $chains > 10 ) {
			$issues[] = sprintf( __( '%d redirect chains detected (SEO/performance issue)', 'wpshadow' ), $chains );
		}
		
		// Check 5: 404 monitoring enabled
		$monitor_404 = get_option( 'rank_math_404_monitor', true );
		if ( $monitor_404 ) {
			$log_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}rank_math_404_logs" );
			if ( $log_count > 5000 ) {
				$issues[] = sprintf( __( '%d 404 logs (database bloat)', 'wpshadow' ), $log_count );
			}
		}
		
		// Check 6: Redirect caching
		$cache_enabled = get_option( 'rank_math_redirections_cache', false );
		if ( ! $cache_enabled && $redirect_count > 100 ) {
			$issues[] = __( 'Redirect caching not enabled (query overhead)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of redirect database issues */
				__( 'Rank Math redirects database has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/rank-math-redirects-database',
		);
	}
}
