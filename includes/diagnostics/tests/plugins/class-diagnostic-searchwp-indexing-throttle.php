<?php
/**
 * SearchWP Indexing Throttle Diagnostic
 *
 * SearchWP indexing throttle misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.407.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SearchWP Indexing Throttle Diagnostic Class
 *
 * @since 1.407.0000
 */
class Diagnostic_SearchwpIndexingThrottle extends Diagnostic_Base {

	protected static $slug = 'searchwp-indexing-throttle';
	protected static $title = 'SearchWP Indexing Throttle';
	protected static $description = 'SearchWP indexing throttle misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'SearchWP' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Indexing throttle setting
		$throttle = get_option( 'searchwp_indexer_throttle', 0 );
		if ( empty( $throttle ) || $throttle < 1 ) {
			$issues[] = 'no indexing throttle set (may overload server)';
		}
		
		// Check 2: Aggressive indexing detection
		if ( ! empty( $throttle ) && $throttle > 10 ) {
			$issues[] = "aggressive indexing throttle ({$throttle} posts/batch, may slow site)";
		}
		
		// Check 3: Index processing during peak hours
		$index_schedule = get_option( 'searchwp_indexer_schedule', 'always' );
		if ( 'always' === $index_schedule ) {
			$issues[] = 'indexing runs continuously (consider off-peak scheduling)';
		}
		
		// Check 4: Index status and backlog
		global $wpdb;
		$pending_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}searchwp_index WHERE indexed = 0"
		);
		if ( $pending_count > 1000 ) {
			$issues[] = "large indexing backlog ({$pending_count} posts pending)";
		}
		
		// Check 5: Memory limit for indexing
		$memory_limit = get_option( 'searchwp_indexer_memory_limit', WP_MEMORY_LIMIT );
		$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );
		if ( $memory_bytes < 134217728 ) { // 128MB
			$memory_mb = round( $memory_bytes / 1048576 );
			$issues[] = "low indexing memory limit ({$memory_mb}MB, recommend 128MB+)";
		}
		
		// Check 6: Indexing errors in log
		$error_log = get_option( 'searchwp_indexer_errors', array() );
		if ( ! empty( $error_log ) && is_array( $error_log ) ) {
			$error_count = count( $error_log );
			if ( $error_count > 10 ) {
				$issues[] = "{$error_count} indexing errors logged (review configuration)";
			}
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'SearchWP indexing performance issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/searchwp-indexing-throttle',
			);
		}
		
		return null;
	}
}
