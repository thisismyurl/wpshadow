<?php
declare(strict_types=1);

namespace WPShadow\Workflow\Commands;

use WPShadow\Workflow\Command;
use WPShadow\Cloud\Deep_Scanner;

/**
 * Get Scan Results Command
 * 
 * AJAX endpoint to retrieve cloud scan results.
 * Polls scan status and returns completed results.
 * 
 * Parameters:
 * - scan_id: string (required) - Scan identifier
 * 
 * Response: { success: bool, status: 'processing'|'completed', results?: {...} }
 */
class Get_Scan_Results_Command extends Command {
	
	/**
	 * Get command name
	 * 
	 * @return string
	 */
	public static function get_name(): string {
		return 'get_scan_results';
	}
	
	/**
	 * Get command description
	 * 
	 * @return string
	 */
	public static function get_description(): string {
		return __( 'Retrieve results from a cloud deep scan', 'wpshadow' );
	}
	
	/**
	 * Execute the command (AJAX handler)
	 * 
	 * @return void Issues JSON response
	 */
	public function execute(): void {
		// Verify request security
		if ( ! $this->verify_request( 'manage_options' ) ) {
			return;
		}
		
		// Get scan_id parameter
		$scan_id = $this->get_post_var( 'scan_id' );
		if ( empty( $scan_id ) ) {
			$this->error( __( 'Scan ID is required', 'wpshadow' ) );
			return;
		}
		
		// First, check status
		$status = Deep_Scanner::get_scan_status( $scan_id );
		
		// If still processing, return status
		if ( $status['status'] !== 'completed' ) {
			$this->success( [
				'scan_id' => $scan_id,
				'status'  => $status['status'],
				'message' => __( 'Scan still processing...', 'wpshadow' ),
			] );
			return;
		}
		
		// Get full results
		$results = Deep_Scanner::get_scan_results( $scan_id );
		
		if ( isset( $results['error'] ) ) {
			$this->error( $results['error'] );
			return;
		}
		
		// Success
		$this->success( [
			'scan_id'   => $scan_id,
			'status'    => 'completed',
			'findings'  => $results['findings'] ?? [],
			'insights'  => $results['insights'] ?? null,
			'timestamp' => $results['retrieved_at'] ?? current_time( 'mysql' ),
		] );
	}
	
	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		$hook = 'wp_ajax_wpshadow_' . static::get_name();
		add_action( $hook, [ new static(), 'execute' ] );
	}
}
