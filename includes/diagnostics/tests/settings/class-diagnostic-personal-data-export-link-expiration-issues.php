<?php
/**
 * Personal Data Export Link Expiration Issues Diagnostic
 *
 * Detects whether export download links expire appropriately for security and usability.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Personal_Data_Export_Link_Expiration_Issues Class
 *
 * Checks if export link expiration is properly configured.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Personal_Data_Export_Link_Expiration_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'personal-data-export-link-expiration-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Export Link Expiration Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that personal data export links expire appropriately';

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

		// 1. Get the export file lifetime (default is 3 days).
		$expiration_days = apply_filters( 'wp_privacy_export_expiration_days', 3 );

		// 2. Check if expiration is too short (less than 1 day).
		if ( $expiration_days < 1 ) {
			$issues[] = sprintf(
				/* translators: %d: number of days */
				__( 'Export links expire in %d days - too short for users to download', 'wpshadow' ),
				$expiration_days
			);
		}

		// 3. Check if expiration is too long (more than 7 days).
		if ( $expiration_days > 7 ) {
			$issues[] = sprintf(
				/* translators: %d: number of days */
				__( 'Export links expire in %d days - security risk if links remain active too long', 'wpshadow' ),
				$expiration_days
			);
		}

		// 4. Check for old export files in directory.
		$upload_dir = wp_upload_dir();
		$export_dir = trailingslashit( $upload_dir['basedir'] ) . 'wp-personal-data-exports';

		if ( file_exists( $export_dir ) ) {
			$files = glob( $export_dir . '/*.zip' );
			if ( is_array( $files ) ) {
				$old_files = 0;
				$now       = time();
				$max_age   = $expiration_days * DAY_IN_SECONDS;

				foreach ( $files as $file ) {
					$file_age = $now - filemtime( $file );
					if ( $file_age > $max_age ) {
						$old_files++;
					}
				}

				if ( $old_files > 0 ) {
					$issues[] = sprintf(
						/* translators: %d: number of files */
						_n(
							'%d expired export file not automatically deleted',
							'%d expired export files not automatically deleted',
							$old_files,
							'wpshadow'
						),
						$old_files
					);
				}
			}
		}

		// 5. Check if cron is configured to clean up old exports.
		$scheduled = wp_next_scheduled( 'wp_privacy_delete_old_export_files' );
		if ( false === $scheduled ) {
			$issues[] = __( 'Automatic cleanup of old export files is not scheduled', 'wpshadow' );
		}

		// 6. Check if export directory is protected.
		if ( file_exists( $export_dir ) ) {
			$htaccess = $export_dir . '/.htaccess';
			$index    = $export_dir . '/index.php';

			if ( ! file_exists( $htaccess ) && ! file_exists( $index ) ) {
				$issues[] = __( 'Export directory is not protected - files may be accessible without download links', 'wpshadow' );
			}
		}

		// 7. Check for request key validation.
		global $wpdb;
		$table = $wpdb->prefix . 'usermeta';
		
		// Check if old export requests exist.
		$old_requests = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} 
				WHERE meta_key = %s 
				AND meta_value < %d",
				'_export_file_created',
				time() - ( $expiration_days * DAY_IN_SECONDS )
			)
		);

		if ( $old_requests > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of requests */
				__( '%d old export request records found - should be cleaned up', 'wpshadow' ),
				$old_requests
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Export link expiration configuration issues: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 75,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/export-link-expiration',
			'details'      => array(
				'issues'          => $issues,
				'expiration_days' => $expiration_days,
				'export_dir'      => $export_dir,
			),
		);
	}
}
