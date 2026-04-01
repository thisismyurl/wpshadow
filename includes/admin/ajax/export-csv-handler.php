<?php
/**
 * Export CSV AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\KPI_Advanced_Features;

/**
 * AJAX Handler: Export Resolution History CSV
 *
 * Action: wp_ajax_wpshadow_export_csv
 * Nonce: wpshadow_export (URL param)
 * Capability: manage_options
 */
class Export_CSV_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_export_csv', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function handle(): void {
		// Verify nonce and capability using base class
		self::verify_admin_request( 'wpshadow_export', 'manage_options', 'nonce' );

		// Generate CSV
		$csv_path = KPI_Advanced_Features::export_resolution_history_csv( 30 );

		if ( empty( $csv_path ) || ! file_exists( $csv_path ) ) {
			wp_die( __( 'No resolution history available for export', 'wpshadow' ) );
		}

		// Set headers for download
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . basename( $csv_path ) );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// Output CSV
		readfile( $csv_path );

		// Clean up
		unlink( $csv_path );

		exit;
	}
}
