<?php
/**
 * User Privacy Report Download Handler
 *
 * Handles file downloads for the User Privacy Report via admin-post.
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\Hook_Subscriber_Base;
use WPShadow\Reporting\Report_Snapshot_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User_Privacy_Report_Download Class
 *
 * Provides a dedicated download endpoint for report exports.
 *
 * @since 1.6093.1200
 */
class User_Privacy_Report_Download extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since 1.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'admin_post_wpshadow_download_user_privacy_report' => 'handle_download',
		);
	}

	/**
	 * Initialize the handler.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init(): void {
		self::subscribe();
	}

	/**
	 * Handle user privacy report downloads.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function handle_download(): void {
		check_admin_referer( 'wpshadow_download_user_privacy_report', 'nonce' );

		$download_type = isset( $_GET['download'] ) ? sanitize_key( wp_unslash( $_GET['download'] ) ) : '';
		$snapshot_id   = isset( $_GET['snapshot_id'] ) ? absint( wp_unslash( $_GET['snapshot_id'] ) ) : 0;

		if ( empty( $download_type ) || empty( $snapshot_id ) ) {
			wp_die( esc_html__( 'Missing download details.', 'wpshadow' ) );
		}

		if ( 'pdf' !== $download_type && 'json' !== $download_type ) {
			wp_die( esc_html__( 'Unsupported download format.', 'wpshadow' ) );
		}

		$current_user_id = get_current_user_id();
		$can_view_others = current_user_can( 'list_users' );

		$snapshot = Report_Snapshot_Manager::get_snapshot_by_id( $snapshot_id );

		if ( ! is_array( $snapshot ) || 'user-privacy-report' !== $snapshot['report_id'] ) {
			wp_die( esc_html__( 'Privacy report not found.', 'wpshadow' ) );
		}

		$snapshot_data     = $snapshot['data'] ?? array();
		$snapshot_metadata = $snapshot['metadata'] ?? array();
		$target_user_id    = isset( $snapshot_metadata['user_id'] ) ? (int) $snapshot_metadata['user_id'] : 0;

		if ( ! $can_view_others && $target_user_id !== $current_user_id ) {
			wp_die( esc_html__( 'You do not have permission to download this report.', 'wpshadow' ) );
		}

		// Fetch ALL activity logs for this user (no limit)
		if ( class_exists( 'WPShadow\\Core\\Activity_Logger' ) ) {
			$result = Activity_Logger::get_activities( array( 'user_id' => $target_user_id ), 10000, 0 );
			$snapshot_data['activity_logs'] = isset( $result['activities'] ) ? $result['activities'] : array();
		}

		$download_payload = array(
			'report'       => 'user-privacy-report',
			'generated_at' => $snapshot['created_at'],
			'user_id'      => $target_user_id,
			'data'         => $snapshot_data,
			'metadata'     => $snapshot_metadata,
		);

		if ( 'pdf' === $download_type ) {
			self::send_pdf( $snapshot, $snapshot_data, $snapshot_metadata, $target_user_id );
		}

		self::send_json( $download_payload );
	}

	/**
	 * Send the PDF download.
	 *
	 * @since 1.6093.1200
	 * @param  array $snapshot Snapshot row.
	 * @param  array $snapshot_data Snapshot data.
	 * @param  array $snapshot_metadata Snapshot metadata.
	 * @param  int   $target_user_id Target user ID.
	 * @return void
	 */
	private static function send_pdf( array $snapshot, array $snapshot_data, array $snapshot_metadata, int $target_user_id ): void {
		$summary = isset( $snapshot_data['summary'] ) ? $snapshot_data['summary'] : array();
		$consent = isset( $snapshot_data['consent'] ) ? $snapshot_data['consent'] : array();
		$settings = isset( $snapshot_data['settings'] ) ? $snapshot_data['settings'] : array();
		$user_meta = isset( $snapshot_data['user_meta'] ) ? $snapshot_data['user_meta'] : array();
		$activity_logs = isset( $snapshot_data['activity_logs'] ) ? $snapshot_data['activity_logs'] : array();
		$findings = isset( $snapshot_data['findings'] ) ? $snapshot_data['findings'] : array();
		$requester_id = isset( $snapshot_metadata['requested_by'] ) ? (int) $snapshot_metadata['requested_by'] : 0;
		$requester    = $requester_id ? get_user_by( 'id', $requester_id ) : null;
		$requester_name = $requester ? $requester->display_name : '';

		if ( empty( $findings ) ) {
			$findings = function_exists( 'wpshadow_get_cached_findings' ) ? wpshadow_get_cached_findings() : get_option( 'wpshadow_site_findings', array() );
		}

		if ( ! is_array( $findings ) ) {
			$findings = array();
		}

		$format_value = static function ( $value ): string {
			if ( is_bool( $value ) ) {
				return $value ? __( 'Yes', 'wpshadow' ) : __( 'No', 'wpshadow' );
			}
			if ( is_array( $value ) || is_object( $value ) ) {
				return wp_json_encode( $value, JSON_PRETTY_PRINT );
			}
			if ( '' === $value || null === $value ) {
				return __( 'None', 'wpshadow' );
			}
			return (string) $value;
		};

		$wrap_text = static function ( string $text, int $width = 96 ): array {
			$wrapped = wordwrap( $text, $width, "\n", true );
			return explode( "\n", $wrapped );
		};

		$add_section = static function ( array &$lines, string $title ) {
			$lines[] = '';
			$lines[] = $title;
			$lines[] = str_repeat( '-', strlen( $title ) );
		};

		$add_wrapped = static function ( array &$lines, string $text ) use ( $wrap_text ) {
			foreach ( $wrap_text( $text ) as $line ) {
				$lines[] = $line;
			}
		};

		$pdf_lines = array(
			'WPShadow User Privacy Report',
			'Generated: ' . date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $snapshot['created_at'] ) ),
			'Requested by: ' . ( $requester_name ? $requester_name : __( 'WPShadow', 'wpshadow' ) ),
			'User ID: ' . (string) $target_user_id,
			'User Label: ' . (string) ( $summary['user_label'] ?? '' ),
			'Settings count: ' . (string) ( $summary['settings_count'] ?? 0 ),
			'User meta count: ' . (string) ( $summary['user_meta_count'] ?? 0 ),
			'Activity log count: ' . (string) ( $summary['activity_log_count'] ?? 0 ),
			'Consent: ' . ( ! empty( $consent['anonymized_telemetry'] ) ? __( 'Anonymous usage data enabled', 'wpshadow' ) : __( 'Anonymous usage data disabled', 'wpshadow' ) ),
		);

		$add_section( $pdf_lines, __( 'Findings', 'wpshadow' ) );
		if ( empty( $findings ) ) {
			$pdf_lines[] = __( 'No findings are currently recorded.', 'wpshadow' );
		} else {
			foreach ( $findings as $finding ) {
				$title = $finding['title'] ?? $finding['id'] ?? __( 'Finding', 'wpshadow' );
				$add_wrapped( $pdf_lines, sprintf( __( 'Finding: %s', 'wpshadow' ), (string) $title ) );
				if ( ! empty( $finding['id'] ) ) {
					$pdf_lines[] = 'ID: ' . (string) $finding['id'];
				}
				if ( ! empty( $finding['severity'] ) ) {
					$pdf_lines[] = 'Severity: ' . (string) $finding['severity'];
				}
				if ( ! empty( $finding['category'] ) ) {
					$pdf_lines[] = 'Category: ' . (string) $finding['category'];
				}
				if ( isset( $finding['threat_level'] ) ) {
					$pdf_lines[] = 'Threat level: ' . (string) $finding['threat_level'];
				}
				if ( isset( $finding['auto_fixable'] ) ) {
					$pdf_lines[] = 'Auto-fixable: ' . ( $finding['auto_fixable'] ? __( 'Yes', 'wpshadow' ) : __( 'No', 'wpshadow' ) );
				}
				if ( ! empty( $finding['description'] ) ) {
					$add_wrapped( $pdf_lines, 'Description: ' . (string) $finding['description'] );
				}
				if ( ! empty( $finding['kb_link'] ) ) {
					$add_wrapped( $pdf_lines, 'Learn more: ' . (string) $finding['kb_link'] );
				}
				$pdf_lines[] = '';
			}
		}

		$add_section( $pdf_lines, __( 'Site Settings', 'wpshadow' ) );
		if ( empty( $settings ) ) {
			$pdf_lines[] = __( 'No WPShadow settings saved yet.', 'wpshadow' );
		} else {
			foreach ( $settings as $setting_key => $setting_value ) {
				$add_wrapped( $pdf_lines, (string) $setting_key . ': ' . $format_value( $setting_value ) );
			}
		}

		$add_section( $pdf_lines, __( 'User Meta', 'wpshadow' ) );
		if ( empty( $user_meta ) ) {
			$pdf_lines[] = __( 'No WPShadow user preferences saved yet.', 'wpshadow' );
		} else {
			foreach ( $user_meta as $meta_key => $meta_value ) {
				$value = is_array( $meta_value ) ? ( $meta_value[0] ?? '' ) : $meta_value;
				$add_wrapped( $pdf_lines, (string) $meta_key . ': ' . $format_value( $value ) );
			}
		}

		$add_section( $pdf_lines, __( 'Activity Logs', 'wpshadow' ) );
		if ( empty( $activity_logs ) ) {
			$pdf_lines[] = __( 'No recent activity logged for this user yet.', 'wpshadow' );
		} else {
			foreach ( $activity_logs as $activity ) {
				$date = isset( $activity['timestamp'] ) ? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $activity['timestamp'] ) : '';
				$action = $activity['action'] ?? '';
				$details = $activity['details'] ?? '';
				$add_wrapped( $pdf_lines, trim( $date . ' - ' . $action . ' - ' . $details, ' -' ) );
			}
		}

		$escape_pdf_text = function ( string $text ): string {
			$escaped = str_replace( array( '\\', '(', ')' ), array( '\\\\', '\\(', '\\)' ), $text );
			return preg_replace( '/[^\x20-\x7E]/', '', $escaped );
		};

		$lines_per_page = 46;
		$pages = array_chunk( $pdf_lines, $lines_per_page );
		$page_count = count( $pages );
		$font_object_number = 3 + ( $page_count * 2 );

		$kids = array();
		for ( $i = 0; $i < $page_count; $i++ ) {
			$kids[] = ( 3 + ( $i * 2 ) ) . ' 0 R';
		}

		$objects = array(
			'<< /Type /Catalog /Pages 2 0 R >>',
			'<< /Type /Pages /Kids [' . implode( ' ', $kids ) . '] /Count ' . $page_count . ' >>',
		);

		foreach ( $pages as $index => $page_lines ) {
			$page_object_number = 3 + ( $index * 2 );
			$content_object_number = $page_object_number + 1;

			$content = "BT\n/F1 11 Tf\n14 TL\n72 720 Td\n";
			foreach ( $page_lines as $line ) {
				$content .= '(' . $escape_pdf_text( $line ) . ") Tj\nT*\n";
			}
			$content .= "ET\n";

			$objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents ' . $content_object_number . ' 0 R /Resources << /Font << /F1 ' . $font_object_number . ' 0 R >> >> >>';
			$objects[] = '<< /Length ' . strlen( $content ) . " >>\nstream\n" . $content . "endstream";
		}

		$objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

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

		nocache_headers();
		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: attachment; filename="wpshadow-user-privacy-report-' . date( 'Y-m-d' ) . '.pdf"' );
		header( 'Content-Length: ' . strlen( $pdf ) );
		header( 'X-Content-Type-Options: nosniff' );

		echo $pdf; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Send the JSON download.
	 *
	 * @since 1.6093.1200
	 * @param  array $download_payload Download payload.
	 * @return void
	 */
	private static function send_json( array $download_payload ): void {
		$json = wp_json_encode( $download_payload, JSON_PRETTY_PRINT );

		nocache_headers();
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="wpshadow-user-privacy-report-' . date( 'Y-m-d' ) . '.json"' );
		header( 'Content-Length: ' . strlen( $json ) );
		header( 'X-Content-Type-Options: nosniff' );

		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}
}
