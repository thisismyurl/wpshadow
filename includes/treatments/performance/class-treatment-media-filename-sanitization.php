<?php
/**
 * Media Filename Sanitization Treatment
 *
 * Validates filename sanitization and detects special
 * characters that could cause URL issues.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1625
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Filename_Sanitization Class
 *
 * Checks whether uploaded filenames are sanitized.
 *
 * @since 1.6033.1625
 */
class Treatment_Media_Filename_Sanitization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-filename-sanitization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Filename Sanitization';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates filename sanitization for media uploads';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1625
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 10,
				'post_status'    => 'inherit',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$unsanitized = 0;
		foreach ( $attachments as $attachment ) {
			$file = get_attached_file( $attachment->ID );
			if ( empty( $file ) ) {
				continue;
			}

			$filename = basename( $file );
			$sanitized = sanitize_file_name( $filename );
			if ( $filename !== $sanitized ) {
				$unsanitized++;
			}
		}

		if ( $unsanitized > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of files */
				_n(
					'%d recent file name contains special characters; sanitize uploads to ensure URL safety',
					'%d recent file names contain special characters; sanitize uploads to ensure URL safety',
					$unsanitized,
					'wpshadow'
				),
				$unsanitized
			);
		}

		if ( ! has_filter( 'sanitize_file_name' ) ) {
			$issues[] = __( 'No filename sanitization filter detected; consider enforcing stricter file naming rules', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-filename-sanitization',
			);
		}

		return null;
	}
}
