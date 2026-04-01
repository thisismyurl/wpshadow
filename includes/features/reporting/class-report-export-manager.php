<?php
/**
 * Report Export Manager
 *
 * Handles PDF, CSV, and Excel export generation for all reports.
 *
 * @package    WPShadow
 * @subpackage Reporting
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Reporting;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Report_Export_Manager Class
 *
 * Manages report export in multiple formats.
 *
 * @since 0.6093.1200
 */
class Report_Export_Manager {

	/**
	 * Export report as PDF
	 *
	 * @since 0.6093.1200
	 * @param  string $report_id Report identifier.
	 * @param  array  $data      Report data.
	 * @return string|false PDF file path or false on failure.
	 */
	public static function export_pdf( $report_id, $data ) {
		// Generate HTML for PDF
		$html = self::generate_report_html( $report_id, $data );

		// Use WordPress file system
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		$upload_dir = wp_upload_dir();
		$pdf_dir = $upload_dir['basedir'] . '/wpshadow-reports/';

		if ( ! $wp_filesystem->is_dir( $pdf_dir ) ) {
			wp_mkdir_p( $pdf_dir );
		}

		$filename = sanitize_file_name( $report_id . '-' . gmdate( 'Y-m-d-His' ) . '.pdf' );
		$filepath = $pdf_dir . $filename;

		// For now, save HTML version (would integrate PDF library in production)
		$wp_filesystem->put_contents( $filepath . '.html', $html, FS_CHMOD_FILE );

		/**
		 * Fires after PDF export is generated.
		 *
		 * @since 0.6093.1200
		 *
		 * @param string $filepath PDF file path.
		 * @param string $report_id Report ID.
		 * @param array  $data Report data.
		 */
		do_action( 'wpshadow_after_pdf_export', $filepath, $report_id, $data );

		return $filepath . '.html';
	}

	/**
	 * Export report as CSV
	 *
	 * @since 0.6093.1200
	 * @param  string $report_id Report identifier.
	 * @param  array  $data      Report data.
	 * @return string|false CSV file path or false on failure.
	 */
	public static function export_csv( $report_id, $data ) {
		$upload_dir = wp_upload_dir();
		$csv_dir = $upload_dir['basedir'] . '/wpshadow-reports/';

		if ( ! file_exists( $csv_dir ) ) {
			wp_mkdir_p( $csv_dir );
		}

		$filename = sanitize_file_name( $report_id . '-' . gmdate( 'Y-m-d-His' ) . '.csv' );
		$filepath = $csv_dir . $filename;

		$fp = fopen( $filepath, 'w' );

		if ( ! $fp ) {
			return false;
		}

		// Write CSV header
		fputcsv( $fp, array( 'ID', 'Title', 'Severity', 'Category', 'Description', 'Auto-Fixable' ) );

		// Write findings
		if ( isset( $data['findings'] ) && is_array( $data['findings'] ) ) {
			foreach ( $data['findings'] as $finding ) {
				fputcsv( $fp, array(
					$finding['id'] ?? '',
					$finding['title'] ?? '',
					$finding['severity'] ?? '',
					$finding['family'] ?? '',
					wp_strip_all_tags( $finding['description'] ?? '' ),
					$finding['auto_fixable'] ? 'Yes' : 'No',
				) );
			}
		}

		fclose( $fp );

		/**
		 * Fires after CSV export is generated.
		 *
		 * @since 0.6093.1200
		 *
		 * @param string $filepath CSV file path.
		 * @param string $report_id Report ID.
		 * @param array  $data Report data.
		 */
		do_action( 'wpshadow_after_csv_export', $filepath, $report_id, $data );

		return $filepath;
	}

	/**
	 * Generate HTML for report
	 *
	 * @since 0.6093.1200
	 * @param  string $report_id Report identifier.
	 * @param  array  $data      Report data.
	 * @return string HTML content.
	 */
	private static function generate_report_html( $report_id, $data ) {
		$site_name = get_bloginfo( 'name' );
		$date = gmdate( 'F j, Y g:i a' );

		$html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
		$html .= '<title>' . esc_html( $site_name ) . ' - ' . esc_html( $report_id ) . '</title>';
		$html .= '<style>body{font-family:Arial,sans-serif;margin:40px;} h1{color:#2271b1;} table{border-collapse:collapse;width:100%;margin-top:20px;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background-color:#f0f0f1;} .severity-high{color:#d63638;} .severity-medium{color:#dba617;} .severity-low{color:#00a32a;}</style>';
		$html .= '</head><body>';
		$html .= '<h1>' . esc_html( $site_name ) . '</h1>';
		$html .= '<h2>' . esc_html( ucwords( str_replace( '-', ' ', $report_id ) ) ) . ' Report</h2>';
		$html .= '<p><strong>Generated:</strong> ' . esc_html( $date ) . '</p>';

		if ( isset( $data['findings'] ) && is_array( $data['findings'] ) && count( $data['findings'] ) > 0 ) {
			$html .= '<h3>Findings (' . count( $data['findings'] ) . ')</h3>';
			$html .= '<table><thead><tr><th>Severity</th><th>Title</th><th>Description</th><th>Auto-Fix</th></tr></thead><tbody>';

			foreach ( $data['findings'] as $finding ) {
				$severity_class = 'severity-' . ( $finding['severity'] ?? 'low' );
				$html .= '<tr>';
				$html .= '<td class="' . esc_attr( $severity_class ) . '">' . esc_html( strtoupper( $finding['severity'] ?? 'low' ) ) . '</td>';
				$html .= '<td>' . esc_html( $finding['title'] ?? '' ) . '</td>';
				$html .= '<td>' . esc_html( wp_strip_all_tags( $finding['description'] ?? '' ) ) . '</td>';
				$html .= '<td>' . ( $finding['auto_fixable'] ? '✓' : '✗' ) . '</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody></table>';
		} else {
			$html .= '<p><strong>No issues found.</strong> Your site is healthy!</p>';
		}

		$html .= '<hr style="margin-top:40px;"><p style="color:#666;font-size:12px;">Generated by WPShadow - WordPress Health & Security Plugin</p>';
		$html .= '</body></html>';

		return $html;
	}

	/**
	 * Get download URL for exported file
	 *
	 * @since 0.6093.1200
	 * @param  string $filepath File path.
	 * @return string Download URL.
	 */
	public static function get_download_url( $filepath ) {
		$upload_dir = wp_upload_dir();
		$file_url = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $filepath );
		return $file_url;
	}

	/**
	 * Clean up old export files
	 *
	 * @since 0.6093.1200
	 * @param  int $days Days to keep files.
	 * @return int Number of files deleted.
	 */
	public static function cleanup_old_exports( $days = 7 ) {
		$upload_dir = wp_upload_dir();
		$export_dir = $upload_dir['basedir'] . '/wpshadow-reports/';

		if ( ! file_exists( $export_dir ) ) {
			return 0;
		}

		$files = glob( $export_dir . '*' );
		$count = 0;
		$cutoff = time() - ( $days * DAY_IN_SECONDS );

		foreach ( $files as $file ) {
			if ( is_file( $file ) && filemtime( $file ) < $cutoff ) {
				wp_delete_file( $file );
				$count++;
			}
		}

		return $count;
	}
}
