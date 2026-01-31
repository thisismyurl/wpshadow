<?php
declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Reports\Report_Engine;
use WPShadow\Reports\Report_Renderer;

/**
 * AJAX Handler: Generate Report
 *
 * Action: wp_ajax_wpshadow_generate_report
 * Nonce: wpshadow_report_builder
 * Capability: manage_options
 */
class Generate_Report_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_generate_report', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request
	 */
	public static function handle(): void {
		// Centralized security check
		self::verify_request( 'wpshadow_report_builder', 'manage_options' );

		// Get and sanitize parameters
		$date_from = self::get_post_param( 'date_from', 'text', '', true );
		$date_to   = self::get_post_param( 'date_to', 'text', '', true );
		$category  = self::get_post_param( 'category', 'text', '', false );
		$type      = self::get_post_param( 'type', 'text', 'summary', false );
		$format    = self::get_post_param( 'format', 'text', 'html', false );

		// Validate date format
		if ( ! self::is_valid_date( $date_from ) || ! self::is_valid_date( $date_to ) ) {
			self::send_error( __( 'Invalid date format. Please use YYYY-MM-DD.', 'wpshadow' ) );
		}

		// Validate report type
		if ( ! in_array( $type, array( 'summary', 'detailed', 'executive' ), true ) ) {
			self::send_error( __( 'Invalid report type.', 'wpshadow' ) );
		}

		// Validate format
		if ( ! in_array( $format, array( 'html', 'csv', 'json' ), true ) ) {
			self::send_error( __( 'Invalid export format.', 'wpshadow' ) );
		}

		try {
			// Generate report
			$report = Report_Engine::generate(
				array(
					'date_from' => $date_from,
					'date_to'   => $date_to,
					'category'  => $category,
					'type'      => $type,
				)
			);

			// Render based on format
			if ( $format === 'html' ) {
				$content = Report_Renderer::render_html( $report );
			} elseif ( $format === 'csv' ) {
				$content = '<pre>' . esc_html( Report_Renderer::render_csv( $report ) ) . '</pre>';
			} else {
				$content = '<pre>' . esc_html( Report_Renderer::render_json( $report ) ) . '</pre>';
			}

			// Add download button
			$download_url = wp_nonce_url(
				add_query_arg(
					array(
						'action'    => 'wpshadow_download_report',
						'date_from' => $date_from,
						'date_to'   => $date_to,
						'category'  => $category,
						'type'      => $type,
						'format'    => $format,
					),
					admin_url( 'admin-ajax.php' )
				),
				'wpshadow_download_report'
			);

			$content = sprintf(
				'<div style="margin-bottom: 20px;"><a href="%s" class="button button-primary">%s</a></div>%s',
				esc_url( $download_url ),
				esc_html__( 'Download Report', 'wpshadow' ),
				$content
			);

			self::send_success(
				array(
					'message' => __( 'Report generated successfully', 'wpshadow' ),
					'html'    => $content,
				)
			);

		} catch ( \Exception $e ) {
			self::send_error(
				sprintf(
					__( 'Error generating report: %s', 'wpshadow' ),
					$e->getMessage()
				)
			);
		}
	}

	/**
	 * Validate date format
	 *
	 * @param string $date Date string to validate
	 * @return bool True if valid YYYY-MM-DD format
	 */
	private static function is_valid_date( string $date ): bool {
		$date_obj = \DateTime::createFromFormat( 'Y-m-d', $date );
		return $date_obj && $date_obj->format( 'Y-m-d' ) === $date;
	}
}
