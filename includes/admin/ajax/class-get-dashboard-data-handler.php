<?php
declare( strict_types=1 );

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

/**
 * AJAX Handler: Get updated dashboard data
 * 
 * Action: wp_ajax_wpshadow_get_dashboard_data
 * Nonce: wpshadow_dashboard_nonce
 * Capability: read
 * 
 * Returns: Updated gauges, findings, kanban data for real-time refresh
 * 
 * @package WPShadow
 */
class Get_Dashboard_Data_Handler extends AJAX_Handler_Base {
	
	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_get_dashboard_data', array( __CLASS__, 'handle' ) );
	}
	
	/**
	 * Handle dashboard data request
	 */
	public static function handle(): void {
		try {
			// Verify security (read-only, minimal capability)
			self::verify_request( 'wpshadow_dashboard_nonce', 'read' );
			
			// Get fresh findings
			$findings = \wpshadow_get_site_findings();
			$dismissed = get_option( 'wpshadow_dismissed_findings', array() );
			
			// Filter out dismissed
			$findings = array_filter( $findings, function( $f ) use ( $dismissed ) {
				return ! isset( $f['id'] ) || ! isset( $dismissed[ $f['id'] ] );
			} );
			
			// Group by category
			$by_category = array();
			foreach ( $findings as $finding ) {
				$cat = $finding['category'] ?? 'uncategorized';
				if ( ! isset( $by_category[ $cat ] ) ) {
					$by_category[ $cat ] = array();
				}
				$by_category[ $cat ][] = $finding;
			}
			
			// Calculate gauge percentages
			$category_meta = array(
				'security' => array( 'label' => __( 'Security', 'wpshadow' ), 'icon' => 'dashicons-shield-alt', 'color' => '#d32f2f' ),
				'performance' => array( 'label' => __( 'Performance', 'wpshadow' ), 'icon' => 'dashicons-chart-line', 'color' => '#f57c00' ),
				'code_quality' => array( 'label' => __( 'Code Quality', 'wpshadow' ), 'icon' => 'dashicons-code-standards', 'color' => '#1976d2' ),
				'configuration' => array( 'label' => __( 'Configuration', 'wpshadow' ), 'icon' => 'dashicons-admin-generic', 'color' => '#388e3c' ),
			);
			
			$gauges = array();
			$total_findings = 0;
			$critical_count = 0;
			
			foreach ( $category_meta as $key => $meta ) {
				$cat_findings = $by_category[ $key ] ?? array();
				$total = count( $cat_findings );
				$total_findings += $total;
				
				// Calculate threat level
				$threat_total = 0;
				foreach ( $cat_findings as $finding ) {
					$threat_total += $finding['threat_level'] ?? 50;
					if ( $finding['severity'] === 'critical' ) {
						$critical_count++;
					}
				}
				
				$percent = $total > 0 ? min( 100, ( $threat_total / $total ) / 100 * 100 ) : 100;
				$percent = 100 - $percent; // Higher is better
				
				$gauges[ $key ] = array(
					'label' => $meta['label'],
					'percent' => $percent,
					'findings_count' => $total,
					'color' => $meta['color'],
				);
			}
			
			// Overall health
			$overall = 0;
			if ( $total_findings > 0 ) {
				$overall = ( ( $total_findings - $critical_count ) / $total_findings ) * 100;
			} else {
				$overall = 100;
			}
			
			self::send_success( array(
				'overall_health' => round( $overall ),
				'total_findings' => $total_findings,
				'critical_count' => $critical_count,
				'gauges' => $gauges,
				'findings' => $findings,
				'by_category' => $by_category,
				'timestamp' => time(),
			) );
		} catch ( \Exception $e ) {
			error_log( 'Dashboard Data Error: ' . $e->getMessage() );
			self::send_error( __( 'Failed to retrieve dashboard data', 'wpshadow' ) );
		}
	}
}
