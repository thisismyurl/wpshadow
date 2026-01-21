<?php
declare( strict_types=1 );

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Diagnostics\Diagnostic_Registry;
use WPShadow\Core\Activity_Logger;

/**
 * AJAX Handler: Deep Scan
 * 
 * Action: wp_ajax_wpshadow_deep_scan
 * Nonce: wpshadow_scan_nonce
 * Capability: manage_options
 * 
 * Philosophy: Show value (#9) - Comprehensive deep scan with server load awareness
 * 
 * @package WPShadow
 */
class Deep_Scan_Handler extends AJAX_Handler_Base {
	
	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_deep_scan', array( __CLASS__, 'handle' ) );
	}
	
	/**
	 * Handle deep scan AJAX request
	 */
	public static function handle(): void {
		try {
			// Verify security
			self::verify_request( 'wpshadow_scan_nonce', 'manage_options' );
			
			// Get scan mode: 'now' or 'schedule'
			$mode = self::get_post_param( 'mode', 'text', 'now', true );
			
			if ( $mode === 'schedule' ) {
				// Schedule deep scan for off-peak hours
				$result = self::schedule_deep_scan();
			} else {
				// Run immediately (with warning already acknowledged)
				$result = self::run_deep_scan();
			}
			
			self::send_success( $result );
			wp_die();
			
		} catch ( \Exception $e ) {
			error_log( 'Deep Scan Handler Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
			self::send_error( $e->getMessage() );
			wp_die();
		}
	}
	
	/**
	 * Run deep scan immediately
	 * 
	 * @return array Result data
	 */
	private static function run_deep_scan(): array {
		// Record scan start time
		update_option( 'wpshadow_last_deep_scan', time() );
		
		// Run all deep scan checks (quick + deep diagnostics)
		$findings = Diagnostic_Registry::run_deepscan_checks();
		
		// Get quick diagnostics to count total diagnostics run
		$quick_count = count( Diagnostic_Registry::get_diagnostics() );
		$deep_extras = apply_filters( 'wpshadow_deep_scan_diagnostics', array() );
		$total = $quick_count + count( $deep_extras );
		$completed = $total;
		
		// Build progress by category and findings breakdown
		$progress_by_category = array();
		$findings_by_category = array();
		$skipped = 0;
		
		foreach ( $findings as $finding ) {
			$category = isset( $finding['category'] ) ? $finding['category'] : 'other';
			
			// Log individual diagnostic finding
			if ( class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
				Activity_Logger::log(
					'diagnostic_finding',
					sprintf( 'Found issue: %s', $finding['title'] ?? $finding['id'] ?? 'Unknown' ),
					$category,
					array( 'finding_id' => $finding['id'] ?? '', 'scan_type' => 'deep' )
				);
			}
			
			if ( ! isset( $progress_by_category[ $category ] ) ) {
				$progress_by_category[ $category ] = array( 'completed' => 0, 'total' => 0, 'findings' => 0 );
				$findings_by_category[ $category ] = array();
			}
			$progress_by_category[ $category ]['findings']++;
			$findings_by_category[ $category ][] = $finding['title'] ?? $finding['id'] ?? 'Unknown';
		}
		
		// Estimate skipped if any
		if ( $completed > 0 && count( $findings ) === 0 ) {
			// Add clean category to show diagnostics ran but found nothing
			$progress_by_category['clean'] = array( 'completed' => $completed, 'total' => $total, 'findings' => 0 );
		}
		
		// Clean up resolved findings from Kanban
		$current_finding_ids = array_map( function( $f ) { return $f['id'] ?? ''; }, $findings );
		$current_finding_ids = array_filter( $current_finding_ids );
		$stored_findings = get_option( 'wpshadow_site_findings', array() );
		$resolved_count = 0;
		
		foreach ( $stored_findings as $stored_id => $stored_finding ) {
			if ( ! in_array( $stored_id, $current_finding_ids, true ) ) {
				// Finding no longer present - it's resolved
				unset( $stored_findings[ $stored_id ] );
				$resolved_count++;
				
				// Log resolved finding
				if ( class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
					Activity_Logger::log(
						'finding_resolved',
						sprintf( 'Issue resolved: %s', $stored_finding['title'] ?? $stored_id ),
						$stored_finding['category'] ?? 'other',
						array( 'finding_id' => $stored_id )
					);
				}
			}
		}
		
		if ( $resolved_count > 0 ) {
			update_option( 'wpshadow_site_findings', $stored_findings );
		}
		
		// Log comprehensive activity
		if ( class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
			$activity_details = sprintf(
				'Deep Scan: Ran %d diagnostics, found %d issues, resolved %d, %d categories affected',
				$completed,
				count( $findings ),
				$resolved_count,
				count( $findings_by_category )
			);
			
			Activity_Logger::log(
				'scan_completed',
				$activity_details,
				'security',
				array(
					'scan_type' => 'deep_scan',
					'warning_acknowledged' => true,
					'total_diagnostics' => $total,
					'completed' => $completed,
					'skipped' => $skipped,
					'findings_count' => count( $findings ),
					'resolved_count' => $resolved_count,
					'findings_by_category' => $findings_by_category,
					'categories_affected' => array_keys( $findings_by_category ),
					'run_by_user' => wp_get_current_user()->display_name,
				)
			);
		}
		
		return array(
			'mode' => 'now',
			'completed' => $completed,
			'total' => $total,
			'skipped' => $skipped,
			'findings_count' => count( $findings ),
			'progress_by_category' => $progress_by_category,
			'findings_by_category' => $findings_by_category,
			'message' => sprintf(
				__( 'Deep Scan completed. Found %d findings from %d diagnostics (%d categories affected).', 'wpshadow' ),
				count( $findings ),
				$completed,
				count( $findings_by_category )
			)
		);
	}
	
	/**
	 * Schedule deep scan for off-peak hours
	 * 
	 * @return array Result data
	 */
	private static function schedule_deep_scan(): array {
		// Check if already scheduled
		$timestamp = wp_next_scheduled( 'wpshadow_scheduled_deep_scan' );
		
		if ( ! $timestamp ) {
			// Schedule weekly deep scan on Sunday at 2 AM
			wp_schedule_event( strtotime( 'next Sunday 2:00 AM' ), 'weekly', 'wpshadow_scheduled_deep_scan' );
		}
		
		// Log the activity
		if ( class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
			Activity_Logger::log(
				'scan_scheduled',
				'Deep Scan scheduled for off-peak execution',
				'automation',
				array( 'scan_type' => 'deep_scan', 'frequency' => 'weekly', 'time' => '2:00 AM Sunday' )
			);
		}
		
		return array(
			'mode' => 'schedule',
			'scheduled' => true,
			'next_run' => wp_next_scheduled( 'wpshadow_scheduled_deep_scan' ),
			'message' => __( 'Deep Scan scheduled to run weekly on Sundays at 2:00 AM (off-peak hours).', 'wpshadow' )
		);
	}
}
