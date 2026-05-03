<?php
/**
 * Readiness Inventory Export Handler
 *
 * Exports complete readiness inventory (diagnostics + treatments) as JSON or CSV
 * for audit and compliance documentation.
 *
 * **Parameters:**
 * - `format`: Export format (default: json, options: json, csv)
 *
 * **Response:** Streamed download with appropriate content-type headers
 *
 * @package ThisIsMyURL\Shadow
 * @since 0.7055
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Admin\Ajax;

use ThisIsMyURL\Shadow\Core\AJAX_Handler_Base;
use ThisIsMyURL\Shadow\Core\Readiness_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Export readiness inventory as JSON or CSV.
 */
class AJAX_Readiness_Export extends AJAX_Handler_Base {
	/**
	 * Handle the export request.
	 *
	 * @since 0.7055
	 * @return void
	 */
	public static function handle(): void {
		self::verify_manage_options_request( 'thisismyurl_shadow_scan_settings' );

		$format = self::get_post_param( 'format', 'text', 'json' );
		if ( ! in_array( $format, array( 'json', 'csv' ), true ) ) {
			$format = 'json';
		}

		$inventory = Readiness_Registry::get_inventory();

		if ( 'csv' === $format ) {
			self::export_as_csv( $inventory );
		} else {
			self::export_as_json( $inventory );
		}
	}

	/**
	 * Export inventory as JSON.
	 *
	 * @param array<string, mixed> $inventory Inventory data.
	 * @return void
	 */
	private static function export_as_json( array $inventory ): void {
		$timestamp = wp_date( 'Y-m-d\TH:i:s', null, new \DateTimeZone( 'UTC' ) );
		$filename  = sanitize_file_name( sprintf( 'thisismyurl-shadow-readiness-inventory-%s.json', $timestamp ) );
		$json      = wp_json_encode( $inventory, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

		if ( false === $json ) {
			wp_die( esc_html__( 'Unable to generate the readiness export.', 'thisismyurl-shadow' ) );
		}

		header( 'Content-Type: application/json; charset=utf-8' );
		header( sprintf( 'Content-Disposition: attachment; filename="%1$s"; filename*=UTF-8\'\'%2$s', $filename, rawurlencode( $filename ) ) );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'X-Content-Type-Options: nosniff' );

		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON payload for file download.
		wp_die();
	}

	/**
	 * Export inventory as CSV.
	 *
	 * @param array<string, mixed> $inventory Inventory data.
	 * @return void
	 */
	private static function export_as_csv( array $inventory ): void {
		$timestamp = wp_date( 'Y-m-d_H-i-s', null, new \DateTimeZone( 'UTC' ) );
		$filename  = sanitize_file_name( sprintf( 'thisismyurl-shadow-readiness-inventory-%s.csv', $timestamp ) );

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( sprintf( 'Content-Disposition: attachment; filename="%1$s"; filename*=UTF-8\'\'%2$s', $filename, rawurlencode( $filename ) ) );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'X-Content-Type-Options: nosniff' );

		$output = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( false === $output ) {
			wp_die( 'Error opening output stream' );
		}

		// Write CSV header.
		fputcsv( $output, array( 'Type', 'Name/Class', 'Readiness', 'Enabled/Executable', 'File/Path' ) );

		// Write diagnostics.
		if ( ! empty( $inventory['diagnostics'] ) ) {
			foreach ( (array) $inventory['diagnostics'] as $diagnostic ) {
				fputcsv(
					$output,
					array(
						'Diagnostic',
						$diagnostic['class'] ?? '',
						$diagnostic['state'] ?? 'production',
						! empty( $diagnostic['enabled'] ) ? 'Yes' : 'No',
						$diagnostic['file'] ?? '',
					)
				);
			}
		}

		// Write treatments.
		if ( ! empty( $inventory['treatments'] ) ) {
			foreach ( (array) $inventory['treatments'] as $treatment ) {
				fputcsv(
					$output,
					array(
						'Treatment',
						$treatment['class'] ?? '',
						$treatment['state'] ?? 'production',
						! empty( $treatment['executable'] ) ? 'Yes' : 'No',
						$treatment['file'] ?? '',
					)
				);
			}
		}

		fclose( $output ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		wp_die();
	}
}

// Register AJAX action.
\add_action( 'wp_ajax_thisismyurl_shadow_export_readiness_inventory', array( '\\ThisIsMyURL\\Shadow\\Admin\\Ajax\\AJAX_Readiness_Export', 'handle' ) );
