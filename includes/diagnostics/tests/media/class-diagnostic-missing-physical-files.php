<?php
/**
 * Missing Physical Files Diagnostic
 *
 * Detects media database entries with missing physical files.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Missing_Physical_Files Class
 *
 * Finds attachments where the database references files that do not exist
 * on disk. These cause broken images and 404s.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Missing_Physical_Files extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'missing-physical-files';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Missing Physical Files';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects media database entries with missing physical files';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates:
	 * - File existence in uploads directory
	 * - Missing _wp_attached_file meta
	 * - Broken attachments
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$missing_files = 0;
		$missing_meta  = 0;

		global $wpdb;

		$attachments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, pm.meta_value as file_path
				FROM {$wpdb->posts} p
				LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attached_file'
				WHERE p.post_type = %s
				ORDER BY p.post_date DESC
				LIMIT 30",
				'attachment'
			)
		);

		if ( empty( $attachments ) ) {
			return null;
		}

		$upload_dir = wp_upload_dir();
		foreach ( $attachments as $attachment ) {
			if ( empty( $attachment->file_path ) ) {
				$missing_meta++;
				continue;
			}

			$file_path = $upload_dir['basedir'] . '/' . $attachment->file_path;
			if ( ! file_exists( $file_path ) ) {
				$missing_files++;
			}
		}

		if ( 0 < $missing_meta ) {
			$issues[] = sprintf(
				/* translators: %d: number of attachments */
				_n(
					'%d attachment is missing _wp_attached_file meta',
					'%d attachments are missing _wp_attached_file meta',
					$missing_meta,
					'wpshadow'
				),
				$missing_meta
			);
		}

		if ( 0 < $missing_files ) {
			$issues[] = sprintf(
				/* translators: %d: number of attachments */
				_n(
					'%d attachment file is missing on disk',
					'%d attachment files are missing on disk',
					$missing_files,
					'wpshadow'
				),
				$missing_files
			);
		}

		// Check for mismatched upload paths.
		$upload_path = get_option( 'upload_path' );
		if ( ! empty( $upload_path ) ) {
			$issues[] = __( 'Custom upload_path is set - missing files may be in a non-standard directory', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d missing file issue detected',
						'%d missing file issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/missing-physical-files',
				'details'      => array(
					'issues'        => $issues,
					'missing_files' => $missing_files,
					'missing_meta'  => $missing_meta,
				),
			);
		}

		return null;
	}
}
