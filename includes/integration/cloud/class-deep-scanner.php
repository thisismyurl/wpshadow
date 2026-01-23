<?php
declare(strict_types=1);

namespace WPShadow\Cloud;

use WPShadow\Diagnostics\Diagnostic_Registry;
use WPShadow\Core\KPI_Tracker;

/**
 * Cloud Deep Scanner
 *
 * Executes comprehensive cloud-based health scans.
 * Sends local diagnostics to cloud for enhanced analysis.
 *
 * Features:
 * - Quota checking (free: 100/month, pro: unlimited)
 * - Local findings gathering
 * - Cloud API submission
 * - Scan result caching
 * - AI-powered analysis from cloud
 *
 * Philosophy: Free tier enables cloud scans (generous quota).
 * Pro tier removes limits. Register-not-pay model.
 */
class Deep_Scanner {

	/**
	 * Initiate cloud deep scan
	 *
	 * Local diagnostics are sent to cloud for enhanced analysis.
	 * Returns scan_id for tracking progress.
	 *
	 * @return array { success: bool, scan_id?: string, error?: string }
	 */
	public static function initiate_scan(): array {
		// Check registration
		if ( ! Registration_Manager::is_registered() ) {
			return array( 'error' => __( 'Site not registered. Register for free to enable cloud scans.', 'wpshadow' ) );
		}

		// Check quota
		$status = Registration_Manager::get_registration_status();
		if ( $status['scans_remaining'] <= 0 ) {
			return array(
				'error' => sprintf(
					__( 'Scan quota exceeded (%1$d used this month). %2$s to continue.', 'wpshadow' ),
					$status['scans_used'],
					$status['tier'] === 'free' ? '<a href="' . esc_url( Registration_Manager::get_upgrade_url() ) . '">Upgrade to Pro</a>' : 'Contact support'
				),
			);
		}

		// Gather local findings
		$local_findings = self::gather_local_findings();

		// Prepare submission
		$submission = array(
			'findings'     => $local_findings,
			'site_url'     => sanitize_url( get_site_url() ),
			'wp_version'   => get_bloginfo( 'version' ),
			'php_version'  => phpversion(),
			'plugin_count' => count( get_plugins() ),
			'theme'        => get_stylesheet(),
		);

		// Submit to cloud API
		$response = Cloud_Client::request( 'POST', '/scans', $submission );

		if ( isset( $response['error'] ) ) {
			return $response;
		}

		// Extract scan ID
		$scan_id = $response['scan_id'] ?? '';
		if ( empty( $scan_id ) ) {
			return array( 'error' => __( 'Failed to create scan (no ID returned).', 'wpshadow' ) );
		}

		// Store scan metadata locally
		$scan_data = array(
			'scan_id'        => $scan_id,
			'timestamp'      => current_time( 'mysql' ),
			'status'         => 'processing',
			'findings_count' => count( $local_findings ),
			'findings_sent'  => true,
		);
		update_option( "wpshadow_cloud_scan_{$scan_id}", $scan_data );

		// Track KPI
		KPI_Tracker::record_action( 'cloud_scan_initiated', 1 );

		// Set transient to track scan status
		set_transient( "wpshadow_scan_status_{$scan_id}", 'processing', 3600 );

		return array(
			'success' => true,
			'scan_id' => $scan_id,
			'message' => __( 'Cloud scan initiated. Results will be available shortly.', 'wpshadow' ),
		);
	}

	/**
	 * Get scan results
	 *
	 * Checks cloud service for completed scan results.
	 * Caches locally with 1-hour TTL.
	 *
	 * @param string $scan_id Scan to retrieve
	 *
	 * @return array Scan results with findings and analysis
	 */
	public static function get_scan_results( string $scan_id ): array {
		$scan_id = sanitize_key( $scan_id );

		// Check local cache first
		$cached = get_option( "wpshadow_cloud_scan_{$scan_id}" );

		// If already completed, return cached
		if ( $cached && $cached['status'] === 'completed' ) {
			return $cached;
		}

		// Fetch from API
		$response = Cloud_Client::request( 'GET', "/scans/{$scan_id}" );

		if ( isset( $response['error'] ) ) {
			return $response;
		}

		// Update cache
		$response['retrieved_at'] = current_time( 'mysql' );
		update_option( "wpshadow_cloud_scan_{$scan_id}", $response );

		// If completed, update transient
		if ( $response['status'] === 'completed' ) {
			delete_transient( "wpshadow_scan_status_{$scan_id}" );
		}

		return $response;
	}

	/**
	 * Get scan status without full results
	 *
	 * Lightweight check for polling scan progress.
	 *
	 * @param string $scan_id Scan to check
	 *
	 * @return array { scan_id: string, status: 'processing'|'completed'|'failed', progress?: int }
	 */
	public static function get_scan_status( string $scan_id ): array {
		$scan_id = sanitize_key( $scan_id );

		// Check transient first (fast path)
		$status = get_transient( "wpshadow_scan_status_{$scan_id}" );
		if ( $status ) {
			return array(
				'scan_id' => $scan_id,
				'status'  => $status,
			);
		}

		// Check cache
		$cached = get_option( "wpshadow_cloud_scan_{$scan_id}" );
		if ( $cached ) {
			return array(
				'scan_id' => $scan_id,
				'status'  => $cached['status'] ?? 'unknown',
			);
		}

		// Query API
		$response = Cloud_Client::request( 'GET', "/scans/{$scan_id}/status" );

		return $response ?? array(
			'scan_id' => $scan_id,
			'status'  => 'unknown',
		);
	}

	/**
	 * Cancel in-progress scan
	 *
	 * @param string $scan_id Scan to cancel
	 *
	 * @return bool Success
	 */
	public static function cancel_scan( string $scan_id ): bool {
		$scan_id = sanitize_key( $scan_id );

		$response = Cloud_Client::request( 'DELETE', "/scans/{$scan_id}" );

		if ( isset( $response['error'] ) ) {
			return false;
		}

		// Clear local cache
		delete_option( "wpshadow_cloud_scan_{$scan_id}" );
		delete_transient( "wpshadow_scan_status_{$scan_id}" );

		return true;
	}

	/**
	 * Get recent scans list
	 *
	 * @param int $limit Number of recent scans
	 *
	 * @return array List of recent scans
	 */
	public static function get_recent_scans( int $limit = 10 ): array {
		$response = Cloud_Client::request( 'GET', '/scans/recent?limit=' . intval( $limit ) );

		return $response['scans'] ?? array();
	}

	/**
	 * Gather local findings for cloud analysis
	 *
	 * Runs all diagnostics and collects findings.
	 * Data sent to cloud for enhanced analysis.
	 *
	 * @return array Findings array
	 */
	private static function gather_local_findings(): array {
		$findings = array();

		try {
			// Get all registered diagnostics
			$diagnostics = Diagnostic_Registry::get_all();

			foreach ( $diagnostics as $diagnostic ) {
				// Only include diagnostics with issues
				if ( ! method_exists( $diagnostic, 'has_issues' ) || ! $diagnostic::has_issues() ) {
					continue;
				}

				$findings[] = array(
					'diagnostic_id' => $diagnostic::get_id(),
					'name'          => $diagnostic::get_name(),
					'severity'      => $diagnostic::get_severity(),
					'finding_text'  => $diagnostic::get_finding_text(),
					'timestamp'     => current_time( 'mysql' ),
				);
			}
		} catch ( \Exception $e ) {
			// Gracefully handle registry errors
			error_log( 'WPShadow Deep Scanner error: ' . $e->getMessage() );
		}

		return $findings;
	}

	/**
	 * Get scan history from cloud
	 *
	 * Retrieves historical scans for trend analysis.
	 *
	 * @param string $period 'week'|'month'|'all'
	 *
	 * @return array Historical scan data
	 */
	public static function get_scan_history( string $period = 'month' ): array {
		$period = sanitize_key( $period );

		// Check cache
		$cached = get_transient( "wpshadow_scan_history_{$period}" );
		if ( $cached ) {
			return $cached;
		}

		// Fetch from API
		$response = Cloud_Client::request( 'GET', "/scans/history?period={$period}" );

		if ( isset( $response['error'] ) ) {
			return array();
		}

		// Cache for 6 hours
		set_transient( "wpshadow_scan_history_{$period}", $response, 21600 );

		return $response;
	}

	/**
	 * Get scan insights and recommendations
	 *
	 * Cloud-powered analysis and actionable recommendations.
	 *
	 * @param string $scan_id Scan to analyze
	 *
	 * @return array Insights and recommendations
	 */
	public static function get_insights( string $scan_id ): array {
		$scan_id = sanitize_key( $scan_id );

		$response = Cloud_Client::request( 'GET', "/scans/{$scan_id}/insights" );

		return $response ?? array(
			'risk_score'      => 0,
			'recommendations' => array(),
			'comparison'      => null,
		);
	}
}
