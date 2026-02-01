<?php
/**
 * Personal Data Export Functionality Diagnostic
 *
 * Verifies that WordPress's personal data export feature is properly
 * configured and functional, as required by GDPR Article 20.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1600
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
 * Ensures the GDPR data export functionality is operational and properly configured.
 *
 * @since 1.26032.1600
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
	protected static $description = 'Verifies GDPR data export feature is functional';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Export feature is accessible
	 * - Email notifications are configured
	 * - Export includes custom data if registered
	 * - Cron jobs for processing are working
	 *
	 * @since  1.26032.1600
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if admin email is set (required for export requests).
		$admin_email = get_option( 'admin_email' );
		if ( empty( $admin_email ) || ! is_email( $admin_email ) ) {
			$issues[] = __( 'Admin email is not properly configured; data export requests cannot be processed', 'wpshadow' );
		}

		// Check if wp_privacy_personal_data_exporters filter has exporters registered.
		global $wp_filter;
		$has_exporters = isset( $wp_filter['wp_privacy_personal_data_exporters'] ) &&
						! empty( $wp_filter['wp_privacy_personal_data_exporters']->callbacks );

		if ( ! $has_exporters ) {
			$issues[] = __( 'No personal data exporters are registered; data exports may be incomplete', 'wpshadow' );
		}

		// Check for required capabilities.
		$admin_role = get_role( 'administrator' );
		if ( $admin_role && ! $admin_role->has_cap( 'export_others_personal_data' ) ) {
			$issues[] = __( 'Administrator role lacks export_others_personal_data capability', 'wpshadow' );
		}

		// Check if cron is working (required for processing exports).
		$cron_disabled = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
		if ( $cron_disabled ) {
			$cron_array = get_option( 'cron' );
			if ( ! empty( $cron_array ) ) {
				// Check if there are any privacy-related cron jobs.
				foreach ( $cron_array as $timestamp => $cron ) {
					if ( isset( $cron['wp_privacy_delete_old_export_files'] ) ) {
						$issues[] = __( 'WP-Cron is disabled; personal data export file cleanup will not run automatically', 'wpshadow' );
						break;
					}
				}
			}
		}

		// Check export directory permissions.
		$upload_dir = wp_upload_dir();
		$export_dir = trailingslashit( $upload_dir['basedir'] ) . 'exports';

		if ( ! file_exists( $export_dir ) ) {
			// Directory doesn't exist yet (will be created on first export).
			// Check if parent directory is writable.
			if ( ! is_writable( $upload_dir['basedir'] ) ) {
				$issues[] = __( 'Uploads directory is not writable; personal data exports cannot be created', 'wpshadow' );
			}
		} elseif ( ! is_writable( $export_dir ) ) {
			$issues[] = __( 'Personal data exports directory exists but is not writable', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/personal-data-export-functionality',
			);
		}

		return null;
	}
}
