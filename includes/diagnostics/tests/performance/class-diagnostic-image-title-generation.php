<?php
/**
 * Image Title Generation Diagnostic
 *
 * Tests automatic title generation from filenames.
 * Validates title sanitization and SEO implications.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7029.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Title Generation Diagnostic Class
 *
 * Checks if image titles are being properly generated from filenames
 * and whether they follow SEO best practices.
 *
 * @since 1.7029.1200
 */
class Diagnostic_Image_Title_Generation extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-title-generation';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Title Generation';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests automatic title generation from filenames';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Analyzes image titles to check if they're properly formatted,
	 * meaningful, and SEO-friendly vs. raw filenames.
	 *
	 * @since  1.7029.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Get recent image attachments.
		$args = array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'post_status'    => 'inherit',
			'posts_per_page' => 20,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$attachments = get_posts( $args );

		if ( empty( $attachments ) ) {
			return null; // No images to test.
		}

		$poor_titles       = array();
		$images_tested     = 0;
		$total_poor_titles = 0;

		foreach ( $attachments as $attachment ) {
			$images_tested++;

			$title         = get_the_title( $attachment->ID );
			$file_path     = get_attached_file( $attachment->ID );
			$filename      = $file_path ? basename( $file_path, '.' . pathinfo( $file_path, PATHINFO_EXTENSION ) ) : '';

			// Check for poor title patterns.
			$is_poor_title = false;
			$issue_type    = '';

			// Pattern 1: Title is identical to filename (with or without extension).
			if ( $title === $filename || $title === basename( $file_path ) ) {
				$is_poor_title = true;
				$issue_type    = 'filename_as_title';
			}

			// Pattern 2: Title contains common filename patterns (IMG_, DSC_, etc.).
			if ( preg_match( '/^(IMG|DSC|DCIM|IMG_|DSC_)[\d\-_]+$/i', $title ) ) {
				$is_poor_title = true;
				$issue_type    = 'camera_default';
			}

			// Pattern 3: Title is only numbers or very short.
			if ( preg_match( '/^\d+$/', $title ) || 3 > strlen( $title ) ) {
				$is_poor_title = true;
				$issue_type    = 'too_short';
			}

			// Pattern 4: Title contains excessive dashes/underscores without spaces.
			if ( preg_match( '/[\-_]{2,}/', $title ) || ( false === strpos( $title, ' ' ) && 15 < strlen( $title ) ) ) {
				$is_poor_title = true;
				$issue_type    = 'not_sanitized';
			}

			if ( $is_poor_title ) {
				$total_poor_titles++;

				$poor_titles[] = array(
					'attachment_id' => $attachment->ID,
					'title'         => $title,
					'filename'      => $filename,
					'issue_type'    => $issue_type,
					'upload_date'   => get_the_date( 'Y-m-d', $attachment->ID ),
				);

				// Limit samples.
				if ( 5 <= count( $poor_titles ) ) {
					break;
				}
			}
		}

		// Calculate percentage.
		$poor_title_percentage = 0 < $images_tested ? ( $total_poor_titles / $images_tested ) * 100 : 0;

		// Flag if more than 30% have poor titles.
		if ( 30 < $poor_title_percentage ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: percentage of images with poor titles */
					__( '%d%% of recent images have poorly formatted titles (raw filenames or camera defaults)', 'wpshadow' ),
					round( $poor_title_percentage )
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/image-title-generation',
				'details'      => array(
					'images_tested'         => $images_tested,
					'poor_titles_count'     => $total_poor_titles,
					'poor_title_percentage' => round( $poor_title_percentage, 2 ),
					'sample_poor_titles'    => $poor_titles,
					'seo_impact'            => __( 'Poor image titles negatively impact SEO and accessibility', 'wpshadow' ),
					'recommendation'        => __( 'Use a plugin to automatically generate meaningful titles from filenames, or manually edit titles after upload', 'wpshadow' ),
					'suggested_solution'    => __( 'Convert filenames like "IMG_1234.jpg" to "Event Name Photo 1234" or manually provide descriptive titles', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
