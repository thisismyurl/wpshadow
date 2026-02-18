<?php
/**
 * Media Responsive Image Srcset Generation Diagnostic
 *
 * Checks if responsive image srcset attributes are properly generated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Responsive Image Srcset Generation Diagnostic Class
 *
 * Verifies that WordPress is generating srcset and sizes attributes
 * for responsive images to serve appropriate sizes to different devices.
 *
 * @since 1.6033.0000
 */
class Diagnostic_Media_Responsive_Image_Srcset_Generation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-responsive-image-srcset-generation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Responsive Image Srcset Generation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if responsive image srcset attributes are properly generated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if srcset generation is disabled.
		$max_srcset_image_width = apply_filters( 'max_srcset_image_width', 2048 );
		if ( $max_srcset_image_width < 1 ) {
			$issues[] = __( 'Srcset generation is disabled (max_srcset_image_width filter returns 0)', 'wpshadow' );
		}

		// Get sample images to test srcset generation.
		$sample_images = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => 10,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $sample_images ) ) {
			// No images to test.
			return null;
		}

		$images_without_srcset = 0;
		$total_tested          = 0;

		foreach ( $sample_images as $attachment ) {
			$image_meta = wp_get_attachment_metadata( $attachment->ID );

			// Only test images large enough to have multiple sizes.
			if ( empty( $image_meta['width'] ) || $image_meta['width'] < 800 ) {
				continue;
			}

			$total_tested++;

			// Get srcset for this image.
			$srcset = wp_get_attachment_image_srcset( $attachment->ID, 'large' );

			if ( empty( $srcset ) ) {
				$images_without_srcset++;
			}
		}

		if ( $total_tested > 0 ) {
			$percentage = ( $images_without_srcset / $total_tested ) * 100;

			if ( $percentage > 50 ) {
				$issues[] = sprintf(
					/* translators: %d: percentage of images without srcset */
					__( '%d%% of tested images do not have srcset attributes', 'wpshadow' ),
					round( $percentage )
				);
			}
		}

		// Check if intermediate image sizes are registered.
		$sizes = get_intermediate_image_sizes();
		if ( count( $sizes ) < 3 ) {
			$issues[] = __( 'Insufficient intermediate image sizes registered for responsive images', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-responsive-image-srcset-generation',
			);
		}

		return null;
	}
}
