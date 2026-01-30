<?php
/**
 * Broken Link Checker Scan Performance Diagnostic
 *
 * Broken Link Checker Scan Performance issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1421.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Broken Link Checker Scan Performance Diagnostic Class
 *
 * @since 1.1421.0000
 */
class Diagnostic_BrokenLinkCheckerScanPerformance extends Diagnostic_Base {

	protected static $slug = 'broken-link-checker-scan-performance';
	protected static $title = 'Broken Link Checker Scan Performance';
	protected static $description = 'Broken Link Checker Scan Performance issue found';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'BLC_ACTIVE' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check number of links being monitored
		global $wpdb;
		$link_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}blc_links" );
		
		if ( $link_count > 5000 ) {
			$issues[] = "large link database ({$link_count} links monitored)";
		}
		
		// Check scan frequency
		$check_threshold = get_option( 'blc_check_threshold', 72 );
		if ( $check_threshold < 24 && $link_count > 1000 ) {
			$issues[] = "frequent scanning ({$check_threshold} hours) with large link database";
		}
		
		// Check for broken links queue
		$broken_links = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}blc_links WHERE broken = %d",
				1
			)
		);
		
		if ( $broken_links > 100 ) {
			$issues[] = "many unresolved broken links ({$broken_links} found)";
		}
		
		// Check timeout settings
		$timeout = get_option( 'blc_timeout', 30 );
		if ( $timeout > 60 ) {
			$issues[] = "high timeout setting ({$timeout}s, slows scanning)";
		}
		
		// Check execution mode
		$execution_mode = get_option( 'blc_execution_mode', 'cron' );
		if ( 'always' === $execution_mode ) {
			$issues[] = 'scanning on every page load (impacts site performance)';
		}
		
		// Check for old scan data
		$last_check = get_option( 'blc_last_check_time', 0 );
		if ( $last_check > 0 && ( time() - $last_check ) > ( 30 * DAY_IN_SECONDS ) ) {
			$days = round( ( time() - $last_check ) / DAY_IN_SECONDS );
			$issues[] = "scan data outdated (last check {$days} days ago)";
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Broken Link Checker performance issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/broken-link-checker-scan-performance',
			);
		}
		
		return null;
	}
}
