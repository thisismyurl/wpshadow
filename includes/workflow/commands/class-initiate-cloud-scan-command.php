<?php
declare(strict_types=1);

namespace WPShadow\Workflow\Commands;

use WPShadow\Workflow\Command;
use WPShadow\Cloud\Deep_Scanner;
use WPShadow\Cloud\Registration_Manager;

/**
 * Initiate Cloud Scan Command
 *
 * AJAX endpoint to start a cloud deep scan.
 * Checks registration and quota before submission.
 *
 * Parameters: none (uses current site)
 *
 * Response: { success: bool, scan_id?: string, error?: string }
 */
class Initiate_Cloud_Scan_Command extends Command {

	/**
	 * Get command name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'initiate_cloud_scan';
	}

	/**
	 * Get command description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return __( 'Start a cloud-based deep health scan', 'wpshadow' );
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

		// Check registration
		if ( ! Registration_Manager::is_registered() ) {
			$this->error( __( 'Site must be registered to use cloud scans. Register for free.', 'wpshadow' ) );
			return;
		}

		// Initiate scan
		$result = Deep_Scanner::initiate_scan();

		if ( isset( $result['error'] ) ) {
			$this->error( $result['error'] );
			return;
		}

		// Success
		$this->success(
			array(
				'scan_id' => $result['scan_id'],
				'message' => $result['message'] ?? __( 'Cloud scan initiated', 'wpshadow' ),
			)
		);
	}

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		$hook = 'wp_ajax_wpshadow_' . static::get_name();
		add_action( $hook, array( new static(), 'execute' ) );
	}
}
