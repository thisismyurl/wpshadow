<?php
/**
 * Upload Organization Structure Diagnostic
 *
 * Tests year/month folder organization setting and validates
 * the integrity of the WordPress uploads folder structure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Upload Organization Structure Diagnostic Class
 *
 * Checks if uploads are organized in year/month folders and validates
 * folder structure integrity for optimal media management.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Upload_Organization_Structure extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'upload-organization-structure';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Upload Organization Structure';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests year/month folder organization setting and validates folder structure integrity';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates WordPress uploads folder organization:
	 * - Checks if year/month organization is enabled
	 * - Validates folder structure integrity
	 * - Detects missing or misconfigured folders
	 * - Checks permissions on upload directories
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$meta   = array();

		// Check if uploads_use_yearmonth_folders option is set.
		$use_yearmonth             = get_option( 'uploads_use_yearmonth_folders', 1 );
		$meta['yearmonth_enabled'] = (bool) $use_yearmonth;

		// Get upload directory information.
		$upload_dir = wp_upload_dir();

		if ( ! empty( $upload_dir['error'] ) ) {
			$issues[] = sprintf(
				/* translators: %s: error message */
				__( 'Upload directory error: %s', 'wpshadow' ),
				$upload_dir['error']
			);
		}

		$base_dir = $upload_dir['basedir'];

		// Check if base upload directory exists and is writable.
		if ( ! file_exists( $base_dir ) ) {
			$issues[] = __( 'Upload directory does not exist', 'wpshadow' );
		} elseif ( ! is_writable( $base_dir ) ) {
			$issues[] = __( 'Upload directory is not writable', 'wpshadow' );
		}

		// If year/month organization is not enabled, flag it.
		if ( ! $use_yearmonth ) {
			$issues[]               = __( 'Year/month folder organization is disabled (all uploads stored in root folder)', 'wpshadow' );
			$meta['recommendation'] = __( 'Enable year/month organization for better file management and scalability', 'wpshadow' );
		} else {
			// Year/month organization is enabled - validate structure.
			$structure_issues = self::validate_folder_structure( $base_dir );
			if ( ! empty( $structure_issues ) ) {
				$issues = array_merge( $issues, $structure_issues );
			}

			// Check for orphaned files in root.
			$orphaned_count = self::count_orphaned_files( $base_dir );
			if ( $orphaned_count > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of orphaned files */
					__( '%d files found in upload root instead of year/month folders', 'wpshadow' ),
					$orphaned_count
				);
				$meta['orphaned_files'] = $orphaned_count;
			}
		}

		// Check recent upload patterns.
		$recent_uploads_organized = self::check_recent_uploads_organization();
		if ( $use_yearmonth && ! $recent_uploads_organized ) {
			$issues[] = __( 'Recent uploads are not being organized into year/month folders', 'wpshadow' );
		}
		$meta['recent_uploads_organized'] = $recent_uploads_organized;

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					__( 'Found %d issues with upload folder organization.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/upload-organization-structure',
				'details'      => array(
					'issues'         => $issues,
					'recommendation' => __( 'Organize uploads in year/month folders for better scalability and management. Enable this in Settings → Media.', 'wpshadow' ),
				),
				'meta'         => $meta,
			);
		}

		return null;
	}

	/**
	 * Validate folder structure integrity.
	 *
	 * Checks for common issues with year/month folder organization:
	 * - Missing year folders for recent years
	 * - Permission issues
	 * - Malformed folder names
	 *
	 * @since 1.6093.1200
	 * @param  string $base_dir Base upload directory path.
	 * @return array Array of issues found.
	 */
	private static function validate_folder_structure( $base_dir ) {
		$issues       = array();
		$current_year = (int) gmdate( 'Y' );

		// Check if current year folder exists.
		$current_year_dir = $base_dir . '/' . $current_year;
		if ( ! file_exists( $current_year_dir ) ) {
			$issues[] = sprintf(
				/* translators: %d: current year */
				__( 'Missing folder for current year (%d)', 'wpshadow' ),
				$current_year
			);
		} elseif ( ! is_writable( $current_year_dir ) ) {
			$issues[] = sprintf(
				/* translators: %d: current year */
				__( 'Current year folder (%d) is not writable', 'wpshadow' ),
				$current_year
			);
		} else {
			// Check current month folder.
			$current_month     = gmdate( 'm' );
			$current_month_dir = $current_year_dir . '/' . $current_month;

			if ( ! file_exists( $current_month_dir ) && self::has_recent_uploads() ) {
				$issues[] = sprintf(
					/* translators: %s: current year and month */
					__( 'Missing folder for current month (%s)', 'wpshadow' ),
					$current_year . '/' . $current_month
				);
			} elseif ( file_exists( $current_month_dir ) && ! is_writable( $current_month_dir ) ) {
				$issues[] = sprintf(
					/* translators: %s: current year and month */
					__( 'Current month folder (%s) is not writable', 'wpshadow' ),
					$current_year . '/' . $current_month
				);
			}
		}

		// Check for malformed year folders.
		if ( file_exists( $base_dir ) && is_readable( $base_dir ) ) {
			$items = scandir( $base_dir );
			if ( false !== $items ) {
				foreach ( $items as $item ) {
					if ( '.' === $item || '..' === $item ) {
						continue;
					}

					$item_path = $base_dir . '/' . $item;
					if ( is_dir( $item_path ) && preg_match( '/^\d{4}$/', $item ) ) {
						// Valid year folder - check for month subfolders.
						$year_items = scandir( $item_path );
						if ( false !== $year_items ) {
							foreach ( $year_items as $month_item ) {
								if ( '.' === $month_item || '..' === $month_item ) {
									continue;
								}

								// Check if it's a valid month folder (01-12).
								if ( is_dir( $item_path . '/' . $month_item ) && ! preg_match( '/^(0[1-9]|1[0-2])$/', $month_item ) ) {
									$issues[] = sprintf(
										/* translators: 1: year folder, 2: month folder */
										__( 'Malformed month folder found: %1$s/%2$s', 'wpshadow' ),
										$item,
										$month_item
									);
								}
							}
						}
					}
				}
			}
		}

		return $issues;
	}

	/**
	 * Count orphaned files in upload root.
	 *
	 * Counts files that are directly in the uploads root directory
	 * instead of being organized in year/month folders.
	 *
	 * @since 1.6093.1200
	 * @param  string $base_dir Base upload directory path.
	 * @return int Number of orphaned files.
	 */
	private static function count_orphaned_files( $base_dir ) {
		if ( ! file_exists( $base_dir ) || ! is_readable( $base_dir ) ) {
			return 0;
		}

		$count = 0;
		$items = scandir( $base_dir );

		if ( false === $items ) {
			return 0;
		}

		foreach ( $items as $item ) {
			if ( '.' === $item || '..' === $item ) {
				continue;
			}

			$item_path = $base_dir . '/' . $item;

			// Count regular files (not directories) in root.
			if ( is_file( $item_path ) ) {
				// Exclude common WordPress files like .htaccess, index.php.
				$excluded_files = array( '.htaccess', 'index.php', 'index.html' );
				if ( ! in_array( $item, $excluded_files, true ) ) {
					++$count;
				}
			}
		}

		return $count;
	}

	/**
	 * Check if recent uploads are properly organized.
	 *
	 * Queries for recent attachments and verifies they are stored
	 * in year/month folders as expected.
	 *
	 * @since 1.6093.1200
	 * @return bool True if recent uploads are organized, false otherwise.
	 */
	private static function check_recent_uploads_organization() {
		global $wpdb;

		// Get the 5 most recent attachments.
		$recent_attachments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, guid FROM {$wpdb->posts}
				WHERE post_type = %s
				AND post_mime_type LIKE %s
				ORDER BY post_date DESC
				LIMIT %d",
				'attachment',
				'image%',
				5
			)
		);

		if ( empty( $recent_attachments ) ) {
			// No recent uploads to check.
			return true;
		}

		$organized_count = 0;

		foreach ( $recent_attachments as $attachment ) {
			$file_path = get_attached_file( $attachment->ID );

			if ( $file_path ) {
				// Check if file path contains year/month pattern.
				if ( preg_match( '/\/\d{4}\/\d{2}\//', $file_path ) ) {
					++$organized_count;
				}
			}
		}

		// Consider organized if at least 80% of recent uploads follow pattern.
		$total_attachments = count( $recent_attachments );
		if ( $total_attachments > 0 ) {
			return ( $organized_count / $total_attachments ) >= 0.8;
		}

		return true;
	}

	/**
	 * Check if site has recent uploads.
	 *
	 * Determines if the site has uploaded any files recently
	 * to avoid false positives for missing current month folder.
	 *
	 * @since 1.6093.1200
	 * @return bool True if site has uploads in the last 30 days, false otherwise.
	 */
	private static function has_recent_uploads() {
		global $wpdb;

		$recent_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts}
				WHERE post_type = %s
				AND post_date > DATE_SUB(NOW(), INTERVAL %d DAY)",
				'attachment',
				30
			)
		);

		return $recent_count > 0;
	}
}
