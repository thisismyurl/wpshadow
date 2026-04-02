<?php
/**
 * Media EXIF Data Preservation Diagnostic
 *
 * Tests whether EXIF data is preserved during upload
 * and detects missing metadata handling.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_EXIF_Data_Preservation Class
 *
 * Checks for EXIF metadata availability on recent uploads.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Media_EXIF_Data_Preservation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-exif-data-preservation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'EXIF Data Preservation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if EXIF data is preserved during upload';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		if ( ! extension_loaded( 'exif' ) ) {
			$issues[] = __( 'PHP EXIF extension is not enabled; camera metadata cannot be preserved', 'wpshadow' );
		}

		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image/jpeg',
				'posts_per_page' => 5,
				'post_status'    => 'inherit',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$exif_found = 0;
		foreach ( $attachments as $attachment ) {
			$file = get_attached_file( $attachment->ID );
			if ( empty( $file ) || ! file_exists( $file ) ) {
				continue;
			}
			$metadata = wp_read_image_metadata( $file );
			if ( ! empty( $metadata['camera'] ) || ! empty( $metadata['created_timestamp'] ) ) {
				$exif_found++;
			}
		}

		if ( ! empty( $attachments ) && 0 === $exif_found ) {
			$issues[] = __( 'No EXIF metadata detected in recent JPEG uploads; EXIF data may be stripped during processing', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-exif-data-preservation',
			);
		}

		return null;
	}
}
