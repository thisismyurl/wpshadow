<?php
/**
 * Personal Data Export Performance Diagnostic
 *
 * Personal Data Export Performance not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1127.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Personal Data Export Performance Diagnostic Class
 *
 * @since 1.1127.0000
 */
class Diagnostic_PersonalDataExportPerformance extends Diagnostic_Base {

	protected static $slug = 'personal-data-export-performance';
	protected static $title = 'Personal Data Export Performance';
	protected static $description = 'Personal Data Export Performance not compliant';
	protected static $family = 'performance';

	public static function check() {
		global $wpdb;
		$issues = array();
		
		// Check 1: Pending export requests
		$pending_exports = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments} 
				WHERE comment_type = %s AND comment_approved = %s",
				'export_personal_data',
				'request-pending'
			)
		);
		
		if ( $pending_exports > 10 ) {
			$issues[] = sprintf( __( '%d pending export requests (processing backlog)', 'wpshadow' ), $pending_exports );
		}
		
		// Check 2: Export timeout setting
		$timeout = get_option( 'wp_privacy_export_timeout', 30 );
		if ( $timeout < 60 ) {
			$issues[] = sprintf( __( 'Export timeout: %d seconds (may fail for large sites)', 'wpshadow' ), $timeout );
		}
		
		// Check 3: Batch size
		$batch_size = get_option( 'wp_privacy_export_batch_size', 100 );
		if ( $batch_size > 500 ) {
			$issues[] = sprintf( __( 'Export batch size: %d (memory intensive)', 'wpshadow' ), $batch_size );
		}
		
		// Check 4: Export file cleanup
		$upload_dir = wp_upload_dir();
		$export_dir = $upload_dir['basedir'] . '/wp-personal-data-exports/';
		
		if ( is_dir( $export_dir ) ) {
			$old_files = 0;
			$files = glob( $export_dir . '*.html' );
			
			foreach ( $files as $file ) {
				if ( ( time() - filemtime( $file ) ) > ( 7 * DAY_IN_SECONDS ) ) {
					$old_files++;
				}
			}
			
			if ( $old_files > 5 ) {
				$issues[] = sprintf( __( '%d export files >7 days old (not auto-deleted)', 'wpshadow' ), $old_files );
			}
		}
		
		// Check 5: Export rate limiting
		$rate_limit = get_option( 'wp_privacy_export_rate_limit', 1 );
		if ( $rate_limit > 5 ) {
			$issues[] = sprintf( __( 'Export rate limit: %d per hour (abuse risk)', 'wpshadow' ), $rate_limit );
		}
		
		
		// Check 6: Cache status
		if ( ! (defined( "WP_CACHE" ) && WP_CACHE) ) {
			$issues[] = __( 'Cache status', 'wpshadow' );
		}

		// Check 7: Database optimization
		if ( ! (! is_option_empty( "db_optimized" )) ) {
			$issues[] = __( 'Database optimization', 'wpshadow' );
		}

		// Check 8: Asset minification
		if ( ! (function_exists( "wp_enqueue_script" )) ) {
			$issues[] = __( 'Asset minification', 'wpshadow' );
		}
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = (40 + min(35, count($issues) * 8));
		if ( count( $issues ) >= 4 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of export performance issues */
				__( 'Personal data export has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/personal-data-export-performance',
		);
	}
}
