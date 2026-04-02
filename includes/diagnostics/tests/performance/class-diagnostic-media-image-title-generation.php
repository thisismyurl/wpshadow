<?php
/**
 * Media Image Title Generation Diagnostic
 *
 * Tests automatic title generation from filenames and
 * validates title sanitization.
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
 * Diagnostic_Media_Image_Title_Generation Class
 *
 * Checks attachment titles for proper generation and sanitization.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Media_Image_Title_Generation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-image-title-generation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Title Generation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests automatic title generation from filenames';

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

		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => 10,
				'post_status'    => 'inherit',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$empty_titles = 0;
		$unsanitized = 0;
		foreach ( $attachments as $attachment ) {
			$title = $attachment->post_title;
			if ( '' === trim( $title ) ) {
				$empty_titles++;
				continue;
			}

			$file = get_attached_file( $attachment->ID );
			if ( empty( $file ) ) {
				continue;
			}
			$filename = pathinfo( $file, PATHINFO_FILENAME );
			$expected = sanitize_text_field( str_replace( array( '-', '_' ), ' ', $filename ) );

			if ( false !== strpos( $title, '.' ) || $title === $filename ) {
				$unsanitized++;
				continue;
			}

			if ( ! empty( $expected ) && 0 !== strcasecmp( $title, $expected ) ) {
				$unsanitized++;
			}
		}

		if ( $empty_titles > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of images */
				_n(
					'%d recent image has an empty title; enable title generation from filenames',
					'%d recent images have empty titles; enable title generation from filenames',
					$empty_titles,
					'wpshadow'
				),
				$empty_titles
			);
		}

		if ( $unsanitized > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of images */
				_n(
					'%d recent image title appears unsanitized; clean up titles for better SEO',
					'%d recent image titles appear unsanitized; clean up titles for better SEO',
					$unsanitized,
					'wpshadow'
				),
				$unsanitized
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-image-title-generation',
			);
		}

		return null;
	}
}
