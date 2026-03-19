<?php
/**
 * Export Files Not Deleted After Download Diagnostic
 *
 * Detects when exported personal data or site exports remain on server after download.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_Request_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Export_Files_Not_Deleted_After_Download Class
 *
 * Verifies that export files are properly cleaned up.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Export_Files_Not_Deleted_After_Download extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'export-files-not-deleted-after-download';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Export File Cleanup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if exported files are removed after download to prevent data exposure';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check for GDPR export files.
		$upload_dir  = wp_upload_dir();
		$export_dir  = $upload_dir['basedir'] . '/wp-personal-data-exports/';
		
		if ( file_exists( $export_dir ) && is_dir( $export_dir ) ) {
			$export_files = glob( $export_dir . '*.zip' );
			
			if ( ! empty( $export_files ) ) {
				$old_files = 0;
				$now       = time();
				
				foreach ( $export_files as $file ) {
					$age = $now - filemtime( $file );
					
					// Files older than 3 days (default expiration).
					if ( $age > ( 3 * DAY_IN_SECONDS ) ) {
						$old_files++;
					}
				}

				if ( $old_files > 0 ) {
					$issues[] = sprintf(
						/* translators: %d: number of files */
						_n(
							'%d old export file found in uploads directory',
							'%d old export files found in uploads directory',
							$old_files,
							'wpshadow'
						),
						$old_files
					);
				}

				// Check total file count.
				if ( count( $export_files ) > 10 ) {
					$issues[] = sprintf(
						/* translators: %d: number of files */
						__( '%d export files accumulated - cleanup may not be working', 'wpshadow' ),
						count( $export_files )
					);
				}
			}

			// 2. Check directory permissions.
			if ( is_readable( $export_dir ) && ! file_exists( $export_dir . '.htaccess' ) ) {
				$issues[] = __( 'Export directory not protected with .htaccess - files may be publicly accessible', 'wpshadow' );
			}

			// 3. Check for index.php protection.
			if ( ! file_exists( $export_dir . 'index.php' ) ) {
				$issues[] = __( 'Export directory lacks index.php - directory listing may be exposed', 'wpshadow' );
			}
		}

		// 4. Check cleanup cron job.
		$cron_hook = 'wp_privacy_delete_old_export_files';
		$cron_jobs = _get_cron_array();
		
		$has_cleanup_job = false;
		foreach ( $cron_jobs as $timestamp => $cron ) {
			if ( isset( $cron[ $cron_hook ] ) ) {
				$has_cleanup_job = true;
				break;
			}
		}

		if ( ! $has_cleanup_job ) {
			$issues[] = __( 'Automatic export file cleanup cron job is not scheduled', 'wpshadow' );
		}

		// 5. Check expiration filter.
		$expiration_days = apply_filters( 'wp_privacy_export_expiration_days', 3 );
		
		if ( $expiration_days > 7 ) {
			$issues[] = sprintf(
				/* translators: %d: number of days */
				__( 'Export files expire after %d days - too long for sensitive data', 'wpshadow' ),
				$expiration_days
			);
		}

		if ( $expiration_days < 1 ) {
			$issues[] = __( 'Export file expiration disabled - files never deleted', 'wpshadow' );
		}

		// 6. Check for plugin export files (WooCommerce, etc).
		$plugin_export_dirs = array(
			$upload_dir['basedir'] . '/woocommerce-exports/',
			$upload_dir['basedir'] . '/exports/',
			$upload_dir['basedir'] . '/backups/',
		);

		foreach ( $plugin_export_dirs as $dir ) {
			if ( file_exists( $dir ) && is_dir( $dir ) ) {
				$files = glob( $dir . '*.{zip,csv,xml,json}', GLOB_BRACE );
				
				if ( ! empty( $files ) ) {
					$old_plugin_files = 0;
					$now              = time();
					
					foreach ( $files as $file ) {
						$age = $now - filemtime( $file );
						
						if ( $age > ( 7 * DAY_IN_SECONDS ) ) {
							$old_plugin_files++;
						}
					}

					if ( $old_plugin_files > 0 ) {
						$issues[] = sprintf(
							/* translators: 1: directory name, 2: number of files */
							__( '%1$s contains %2$d old export file(s)', 'wpshadow' ),
							basename( $dir ),
							$old_plugin_files
						);
					}
				}
			}
		}

		// 7. Check download count tracking.
		global $wpdb;
		$request_table = $wpdb->prefix . 'posts';
		
		$completed_exports = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID FROM {$request_table} 
				WHERE post_type = %s 
				AND post_status = %s
				LIMIT 10",
				'user_request',
				'request-completed'
			)
		);

		if ( ! empty( $completed_exports ) ) {
			$missing_download_tracking = 0;
			
			foreach ( $completed_exports as $export ) {
				$download_count = get_post_meta( $export->ID, '_export_download_count', true );
				
				if ( empty( $download_count ) ) {
					$missing_download_tracking++;
				}
			}

			if ( $missing_download_tracking > 0 ) {
				$issues[] = __( 'Export download tracking not implemented - cannot verify files were accessed', 'wpshadow' );
			}
		}

		// 8. Check for public URL exposure.
		if ( file_exists( $export_dir ) ) {
			$export_url = $upload_dir['baseurl'] . '/wp-personal-data-exports/';
			
			// Try to access directory.
			$response = Diagnostic_Request_Helper::head_result( $export_url, array( 'sslverify' => false ) );
			
			if ( $response['success'] ) {
				$status_code = (int) $response['code'];
				
				if ( 200 === $status_code || 403 !== $status_code ) {
					$issues[] = __( 'Export directory may be publicly accessible - test protection', 'wpshadow' );
				}
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Export file cleanup issues: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'critical',
			'threat_level' => 90,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/export-file-cleanup',
			'details'      => array(
				'issues'          => $issues,
				'export_dir'      => $export_dir,
				'expiration_days' => $expiration_days,
			),
		);
	}
}
