<?php
/**
 * Multisite Network Admin Performance Diagnostic
 *
 * Multisite Network Admin Performance misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.938.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Network Admin Performance Diagnostic Class
 *
 * @since 1.938.0000
 */
class Diagnostic_MultisiteNetworkAdminPerformance extends Diagnostic_Base {

	protected static $slug = 'multisite-network-admin-performance';
	protected static $title = 'Multisite Network Admin Performance';
	protected static $description = 'Multisite Network Admin Performance misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Network admin dashboard widgets
		global $wp_meta_boxes;
		$widget_count = isset( $wp_meta_boxes['dashboard-network'] ) ? count( $wp_meta_boxes['dashboard-network']['normal']['core'] ) : 0;
		
		if ( $widget_count > 10 ) {
			$issues[] = sprintf( __( '%d dashboard widgets (slow load)', 'wpshadow' ), $widget_count );
		}
		
		// Check 2: Site count
		$site_count = get_blog_count();
		if ( $site_count > 100 ) {
			$issues[] = sprintf( __( '%d sites (pagination needed)', 'wpshadow' ), $site_count );
		}
		
		// Check 3: Site lookup optimization
		$optimize_lookups = get_site_option( 'network_optimize_site_lookups', 'yes' );
		if ( 'no' === $optimize_lookups ) {
			$issues[] = __( 'Site lookups not optimized (slow queries)', 'wpshadow' );
		}
		
		// Check 4: Plugin checks
		$check_all_sites = get_site_option( 'network_check_plugins_all_sites', 'yes' );
		if ( 'yes' === $check_all_sites && $site_count > 50 ) {
			$issues[] = __( 'Checking plugins on all sites (timeout risk)', 'wpshadow' );
		}
		
		// Check 5: User queries
		$cache_user_queries = get_site_option( 'network_cache_user_queries', 'yes' );
		if ( 'no' === $cache_user_queries ) {
			$issues[] = __( 'User queries not cached (repeated lookups)', 'wpshadow' );
		}
		
		// Check 6: Batch operations
		$batch_limit = get_site_option( 'network_batch_operation_limit', 10 );
		if ( $batch_limit > 20 ) {
			$issues[] = sprintf( __( '%d site batch limit (memory issues)', 'wpshadow' ), $batch_limit );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 68;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 62;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of network admin performance issues */
				__( 'Multisite network admin has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/multisite-network-admin-performance',
		);
	}
}
