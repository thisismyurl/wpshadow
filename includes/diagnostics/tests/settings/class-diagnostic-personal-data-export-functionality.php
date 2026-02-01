<?php
/**
 * Personal Data Export Functionality Diagnostic
 *
 * Tests personal data export tools are functional.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.1531
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Personal Data Export Functionality Diagnostic Class
 *
 * Validates that personal data export tools are properly configured and functional.
 *
 * @since 1.2601.1531
 */
class Diagnostic_Personal_Data_Export_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'personal-data-export-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Personal Data Export Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests personal data export tools are functional';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * The family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.1531
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if privacy policy page is configured.
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );
		if ( 0 === $privacy_page_id ) {
			$issues[] = __( 'Privacy policy page is not configured', 'wpshadow' );
		} else {
			$page = get_post( $privacy_page_id );
			if ( ! $page || 'publish' !== $page->post_status ) {
				$issues[] = __( 'Privacy policy page is not published', 'wpshadow' );
			}
		}

		// Check if exporters are registered.
		$has_exporters = has_filter( 'wp_privacy_personal_data_exporters' );
		if ( ! $has_exporters ) {
			$issues[] = __( 'No personal data exporters are registered', 'wpshadow' );
		} else {
			// Get registered exporters to verify functionality.
			$exporters = apply_filters( 'wp_privacy_personal_data_exporters', array() );
			if ( empty( $exporters ) ) {
				$issues[] = __( 'Personal data exporters filter registered but returns empty', 'wpshadow' );
			}
		}

		// Check if export directory is writable.
		$upload_dir  = wp_upload_dir();
		$exports_dir = trailingslashit( $upload_dir['basedir'] ) . 'wp-personal-data-exports/';
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable -- Diagnostic check only, no file modifications.
		if ( file_exists( $exports_dir ) && ! is_writable( $exports_dir ) ) {
			$issues[] = __( 'Personal data exports directory is not writable', 'wpshadow' );
		}

		// Check if cron job for cleaning old exports exists.
		$cleanup_cron = wp_next_scheduled( 'wp_privacy_delete_old_export_files' );
		if ( false === $cleanup_cron ) {
			$issues[] = __( 'Cron job for cleaning old export files is not scheduled', 'wpshadow' );
		}

		// Check if email notification functions exist.
		if ( ! function_exists( 'wp_privacy_send_personal_data_export_email' ) ) {
			$issues[] = __( 'Personal data export email notification function is not available', 'wpshadow' );
		}

		// Check for pending export requests that might be stuck.
		global $wpdb;
		$stuck_requests = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}posts 
				WHERE post_type = %s 
				AND post_status = %s 
				AND post_modified < DATE_SUB(NOW(), INTERVAL 7 DAY)",
				'user_request',
				'request-pending'
			)
		);

		if ( $stuck_requests > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of stuck requests */
				__( 'Found %d pending export requests older than 7 days', 'wpshadow' ),
				$stuck_requests
			);
		}

		// If no issues found, return null.
		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'                 => self::$slug,
			'title'              => self::$title,
			'description'        => sprintf(
				/* translators: %d: number of issues */
				__( 'Found %d personal data export functionality issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'           => 'high',
			'threat_level'       => 65,
			'site_health_status' => 'critical',
			'auto_fixable'       => false,
			'kb_link'            => 'https://wpshadow.com/kb/personal-data-export-functionality',
			'family'             => self::$family,
			'details'            => array(
				'issues'          => $issues,
				'privacy_page_id' => $privacy_page_id,
				'has_exporters'   => $has_exporters,
				'exports_dir'     => $exports_dir,
				'stuck_requests'  => $stuck_requests,
			),
		);
	}
}
