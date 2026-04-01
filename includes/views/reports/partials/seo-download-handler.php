<?php
/**
 * SEO Report Download Handler
 *
 * Handles PDF/JSON downloads before admin output is sent.
 *
 * @package WPShadow
 * @subpackage Reports
 */

declare(strict_types=1);

use WPShadow\Core\Form_Param_Helper;
use WPShadow\Reporting\Report_Snapshot_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpshadow_handle_seo_report_download' ) ) {
	/**
	 * Handle SEO report downloads.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	function wpshadow_handle_seo_report_download() {
		$download_type = Form_Param_Helper::get( 'download', 'text', '' );
		$snapshot_id   = (int) Form_Param_Helper::get( 'snapshot_id', 'int', 0 );

		if ( ! $download_type || ! $snapshot_id ) {
			return;
		}

		check_admin_referer( 'wpshadow_download_seo_report', 'nonce' );

		while ( ob_get_level() ) {
			ob_end_clean();
		}

		$snapshot = Report_Snapshot_Manager::get_snapshot_by_id( $snapshot_id );

		if ( ! is_array( $snapshot ) || 'seo-report' !== $snapshot['report_id'] ) {
			wp_die( esc_html__( 'SEO report not found.', 'wpshadow' ) );
		}

		$snapshot_data     = $snapshot['data'] ?? array();
		$snapshot_metadata = $snapshot['metadata'] ?? array();

		$download_payload = array(
			'report'       => 'seo-report',
			'generated_at' => $snapshot['created_at'],
			'data'         => $snapshot_data,
			'metadata'     => $snapshot_metadata,
		);

		if ( 'pdf' === $download_type ) {
			$summary        = isset( $snapshot_data['summary'] ) ? $snapshot_data['summary'] : array();
			$requester_id   = isset( $snapshot_metadata['requested_by'] ) ? (int) $snapshot_metadata['requested_by'] : 0;
			$requester      = $requester_id ? get_user_by( 'id', $requester_id ) : null;
			$requester_name = $requester ? $requester->display_name : '';
			$pdf            = '';

			$pdf_html = '<h1>' . esc_html__( 'WPShadow SEO Report', 'wpshadow' ) . '</h1>';
			$pdf_html .= '<p><strong>' . esc_html__( 'Generated', 'wpshadow' ) . ':</strong> ' . esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $snapshot['created_at'] ) ) ) . '</p>';
			$pdf_html .= '<p><strong>' . esc_html__( 'Requested by', 'wpshadow' ) . ':</strong> ' . esc_html( $requester_name ? $requester_name : __( 'WPShadow', 'wpshadow' ) ) . '</p>';
			$pdf_html .= '<ul>';
			$pdf_html .= '<li>' . esc_html__( 'SEO score', 'wpshadow' ) . ': ' . esc_html( (string) ( $summary['seo_score'] ?? 0 ) ) . '</li>';
			$pdf_html .= '<li>' . esc_html__( 'Issues found', 'wpshadow' ) . ': ' . esc_html( (string) ( $summary['seo_issues_count'] ?? 0 ) ) . '</li>';
			$pdf_html .= '<li>' . esc_html__( 'Diagnostics count', 'wpshadow' ) . ': ' . esc_html( (string) ( $summary['diagnostics_count'] ?? 0 ) ) . '</li>';
			$pdf_html .= '<li>' . esc_html__( 'Sitemap detected', 'wpshadow' ) . ': ' . esc_html( ! empty( $summary['has_sitemap'] ) ? __( 'Yes', 'wpshadow' ) : __( 'No', 'wpshadow' ) ) . '</li>';
			$pdf_html .= '</ul>';

			if ( class_exists( 'Dompdf\\Dompdf' ) ) {
				$dompdf = new Dompdf\Dompdf();
				$dompdf->loadHtml( $pdf_html );
				$dompdf->setPaper( 'letter', 'portrait' );
				$dompdf->render();
				$pdf = $dompdf->output();
			} elseif ( class_exists( 'Mpdf\\Mpdf' ) ) {
				$mpdf = new Mpdf\Mpdf();
				$mpdf->WriteHTML( $pdf_html );
				$pdf = $mpdf->Output( '', 'S' );
			}

			if ( '' === $pdf ) {
				$pdf_lines = array(
					'WPShadow SEO Report',
					'Generated: ' . date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $snapshot['created_at'] ) ),
					'Requested by: ' . ( $requester_name ? $requester_name : __( 'WPShadow', 'wpshadow' ) ),
					'SEO score: ' . (string) ( $summary['seo_score'] ?? 0 ),
					'Issues found: ' . (string) ( $summary['seo_issues_count'] ?? 0 ),
					'Diagnostics count: ' . (string) ( $summary['diagnostics_count'] ?? 0 ),
					'Sitemap detected: ' . ( ! empty( $summary['has_sitemap'] ) ? __( 'Yes', 'wpshadow' ) : __( 'No', 'wpshadow' ) ),
				);

				$escape_pdf_text = function ( $text ) {
					$escaped = str_replace( array( '\\', '(', ')' ), array( '\\\\', '\\(', '\\)' ), $text );
					return preg_replace( '/[^\x20-\x7E]/', '', $escaped );
				};

				$content = "BT\n/F1 12 Tf\n14 TL\n72 720 Td\n";
				foreach ( $pdf_lines as $line ) {
					$content .= '(' . $escape_pdf_text( $line ) . ") Tj\nT*\n";
				}
				$content .= "ET\n";

				$objects = array(
					'<< /Type /Catalog /Pages 2 0 R >>',
					'<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
					'<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
					'<< /Length ' . strlen( $content ) . " >>\nstream\n" . $content . "endstream",
					'<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
				);

				$pdf = "%PDF-1.4\n";
				$offsets = array( 0 );
				foreach ( $objects as $index => $object ) {
					$offsets[] = strlen( $pdf );
					$object_number = $index + 1;
					$pdf .= $object_number . " 0 obj\n" . $object . "\nendobj\n";
				}

				$start_xref = strlen( $pdf );
				$pdf .= "xref\n0 " . ( count( $objects ) + 1 ) . "\n";
				$pdf .= "0000000000 65535 f \n";
				foreach ( $offsets as $offset_index => $offset ) {
					if ( 0 === $offset_index ) {
						continue;
					}
					$pdf .= sprintf( "%010d 00000 n \n", $offset );
				}
				$pdf .= "trailer\n<< /Size " . ( count( $objects ) + 1 ) . " /Root 1 0 R >>\n";
				$pdf .= "startxref\n" . $start_xref . "\n%%EOF";
			}

			nocache_headers();
			header( 'Content-Type: application/pdf' );
			header( 'Content-Disposition: attachment; filename="wpshadow-seo-report-' . date( 'Y-m-d' ) . '.pdf"' );
			header( 'Content-Length: ' . strlen( $pdf ) );
			header( 'X-Content-Type-Options: nosniff' );

			echo $pdf; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			exit;
		}

		$json = wp_json_encode( $download_payload, JSON_PRETTY_PRINT );

		nocache_headers();
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="wpshadow-seo-report-' . date( 'Y-m-d' ) . '.json"' );
		header( 'Content-Length: ' . strlen( $json ) );
		header( 'X-Content-Type-Options: nosniff' );

		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}
}
