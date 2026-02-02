<?php
/**
 * Media Missing Physical Files Diagnostic
 *
 * Detects media library entries with missing physical files
 * on disk and identifies broken attachments.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.26033.1605
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Missing_Physical_Files Class
 *
 * Checks attachment records and verifies their files exist.
 *
 * @since 1.26033.1605
 */
class Diagnostic_Media_Missing_Physical_Files extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-missing-physical-files';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Physical Files';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects media entries with missing files on disk';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.1605
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 50,
				'post_status'    => 'inherit',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $attachments ) ) {
			return null;
		}

		$missing_files = 0;
		$missing_meta = 0;
		foreach ( $attachments as $attachment ) {
			$file_path = get_attached_file( $attachment->ID );
			if ( empty( $file_path ) ) {
				$missing_meta++;
				continue;
			}

			if ( ! file_exists( $file_path ) ) {
				$missing_files++;
			}
		}

		if ( $missing_meta > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of attachments */
				_n(
					'%d recent attachment is missing file metadata; it may be broken',
					'%d recent attachments are missing file metadata; they may be broken',
					$missing_meta,
					'wpshadow'
				),
				$missing_meta
			);
		}

		if ( $missing_files > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of missing files */
				_n(
					'%d recent attachment file is missing on disk; cleanup or reupload needed',
					'%d recent attachment files are missing on disk; cleanup or reupload needed',
					$missing_files,
					'wpshadow'
				),
				$missing_files
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-missing-physical-files',
			);
		}

		return null;
	}
}
