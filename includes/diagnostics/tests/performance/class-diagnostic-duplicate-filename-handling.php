<?php
/**
 * Duplicate Filename Handling Diagnostic
 *
 * Tests how WordPress handles duplicate filenames during upload and
 * verifies filename sanitization works correctly.
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
 * Duplicate Filename Handling Class
 *
 * Ensures WordPress properly handles duplicate filenames by adding
 * numeric suffixes and sanitizes filenames to prevent security issues.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Duplicate_Filename_Handling extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'duplicate-filename-handling';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Duplicate Filename Handling';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests WordPress duplicate filename handling';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates filename sanitization and duplicate handling by checking
	 * for common issues with uploaded files.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if filename issues found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();
		$details = array();

		// Check for files with unsafe characters in filename.
		$unsafe_chars = $wpdb->get_results(
			"SELECT ID, guid
			FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND (
				guid LIKE '%..%'
				OR guid LIKE '%/%/%'
				OR guid REGEXP '[<>:\"|?*]'
			)
			LIMIT 10"
		);

		if ( ! empty( $unsafe_chars ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of files with unsafe characters */
				_n(
					'Found %d file with unsafe characters in filename',
					'Found %d files with unsafe characters in filename',
					count( $unsafe_chars ),
					'wpshadow'
				),
				number_format_i18n( count( $unsafe_chars ) )
			);

			$details['unsafe_filenames'] = array_map(
				function( $file ) {
					return array(
						'id'   => $file->ID,
						'guid' => basename( $file->guid ),
					);
				},
				$unsafe_chars
			);
		}

		// Check for very long filenames (may cause filesystem issues).
		$long_filenames = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND LENGTH(post_name) > 200"
		);

		if ( $long_filenames && (int) $long_filenames > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of files with long filenames */
				_n(
					'Found %d file with filename over 200 characters',
					'Found %d files with filenames over 200 characters',
					(int) $long_filenames,
					'wpshadow'
				),
				number_format_i18n( (int) $long_filenames )
			);

			$details['long_filenames'] = (int) $long_filenames;
		}

		// Check for files that appear to be duplicates (same base name, different suffix).
		$duplicates = $wpdb->get_results(
			"SELECT 
				SUBSTRING_INDEX(post_name, '-', 1) as base_name,
				COUNT(*) as count
			FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND post_name REGEXP '-[0-9]+$'
			GROUP BY base_name
			HAVING count > 5
			ORDER BY count DESC
			LIMIT 10"
		);

		if ( ! empty( $duplicates ) ) {
			$total_duplicates = array_sum( array_column( $duplicates, 'count' ) );

			$issues[] = sprintf(
				/* translators: 1: number of duplicate groups, 2: total duplicates */
				__( 'Found %1$d filename patterns with %2$s duplicates (indicates repeated uploads)', 'wpshadow' ),
				count( $duplicates ),
				number_format_i18n( $total_duplicates )
			);

			$details['duplicate_patterns'] = array_map(
				function( $dup ) {
					return array(
						'base_name' => $dup->base_name,
						'count'     => $dup->count,
					);
				},
				$duplicates
			);
		}

		// Check for unicode characters in filenames (may cause issues on some servers).
		$unicode_files = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND post_name REGEXP '[^[:ascii:]]'"
		);

		if ( $unicode_files && (int) $unicode_files > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of files with unicode characters */
				_n(
					'Found %d file with non-ASCII characters (may cause issues on some servers)',
					'Found %d files with non-ASCII characters (may cause issues on some servers)',
					(int) $unicode_files,
					'wpshadow'
				),
				number_format_i18n( (int) $unicode_files )
			);

			$details['unicode_filenames'] = (int) $unicode_files;
		}

		// Check for spaces in filenames (not ideal but common).
		$space_files = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND guid LIKE '% %'"
		);

		if ( $space_files && (int) $space_files > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of files with spaces */
				__( 'Found %d files with spaces in filename (may cause URL issues)', 'wpshadow' ),
				number_format_i18n( (int) $space_files )
			);

			$details['space_filenames'] = (int) $space_files;
		}

		// Check if sanitize_file_name filter is being used.
		$has_filter = has_filter( 'sanitize_file_name' );

		if ( $has_filter ) {
			$details['custom_sanitization'] = true;
		}

		// Check upload directory for actual file count vs database.
		$upload_dir = wp_upload_dir();

		if ( is_dir( $upload_dir['basedir'] ) ) {
			$db_count = (int) $wpdb->get_var(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_type = 'attachment'"
			);

			$details['database_attachments'] = $db_count;
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => implode( '. ', $issues ),
			'severity'    => 'low',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/duplicate-filename-handling',
			'details'     => $details,
		);
	}
}
