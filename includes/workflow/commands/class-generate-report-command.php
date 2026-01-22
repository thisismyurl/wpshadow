<?php
declare(strict_types=1);

namespace WPShadow\Workflow\Commands;

use WPShadow\Core\Command_Base;
use WPShadow\Reporting\Report_Generator;
use WPShadow\Reporting\Notification_Manager;
use WPShadow\Core\KPI_Tracker;

/**
 * Workflow Command: Generate Report
 *
 * Generate and export reports for specific date range.
 * Supports HTML, JSON, CSV formats.
 *
 * POST Parameters:
 * - start_date (required): Start date YYYY-MM-DD
 * - end_date (required): End date YYYY-MM-DD
 * - type (optional): Report type (summary, detailed, executive)
 * - format (optional): Export format (html, json, csv)
 */
class Generate_Report_Command extends Command_Base {

	/**
	 * Execute the command
	 *
	 * @return array Result
	 */
	protected function execute(): array {
		$start_date = sanitize_text_field( $this->get_param( 'start_date' ) );
		$end_date   = sanitize_text_field( $this->get_param( 'end_date' ) );
		$type       = sanitize_key( $this->get_param( 'type' ) ) ?: 'summary';
		$format     = sanitize_key( $this->get_param( 'format' ) ) ?: 'html';

		// Validate dates
		if ( ! self::validate_date( $start_date ) || ! self::validate_date( $end_date ) ) {
			return $this->error( 'Invalid date format' );
		}

		if ( strtotime( $start_date ) > strtotime( $end_date ) ) {
			return $this->error( 'Start date must be before end date' );
		}

		// Validate type and format
		if ( ! in_array( $type, array( 'summary', 'detailed', 'executive' ), true ) ) {
			$type = 'summary';
		}

		if ( ! in_array( $format, array( 'html', 'json', 'csv' ), true ) ) {
			$format = 'html';
		}

		try {
			// Generate report
			$report = Report_Generator::generate_report( $start_date, $end_date, $type );

			// Export in requested format
			$exported = match ( $format ) {
				'html' => Report_Generator::export_html( $report ),
				'json' => Report_Generator::export_json( $report ),
				'csv' => Report_Generator::export_csv( $report ),
				default => Report_Generator::export_html( $report ),
			};

			KPI_Tracker::record_action( 'report_generated', 1 );

			return $this->success(
				array(
					'report'     => $exported,
					'format'     => $format,
					'date_range' => "$start_date to $end_date",
					'filename'   => "wpshadow-report-{$start_date}-to-{$end_date}.{$format}",
				)
			);
		} catch ( \Exception $e ) {
			return $this->error( 'Report generation failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Validate date format
	 *
	 * @param string $date Date string
	 *
	 * @return bool Valid
	 */
	private static function validate_date( string $date ): bool {
		$d = \DateTime::createFromFormat( 'Y-m-d', $date );
		return $d && $d->format( 'Y-m-d' ) === $date;
	}

	/**
	 * Get command name
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'generate_report';
	}

	/**
	 * Get command description
	 *
	 * @return string
	 */
	public function get_description(): string {
		return 'Generate and export performance reports';
	}
}
