<?php
/**
 * Responsive Image Srcset Generation Diagnostic
 *
 * Detects if responsive images with srcset are properly generated for all sizes.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.1635
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Responsive_Image_Srcset Class
 *
 * Tests if responsive image srcset generation is enabled and properly
 * generating multiple image sizes for responsive delivery.
 *
 * @since 1.6033.1635
 */
class Diagnostic_Media_Responsive_Image_Srcset extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-responsive-image-srcset';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Responsive Image Srcset Generation';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies responsive images with srcset are properly generated';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6033.1635
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$image_sizes = wp_get_registered_image_subsizes();

		if ( empty( $image_sizes ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No responsive image sizes are registered. Add image sizes to generate srcset attributes.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/responsive-image-srcset',
			);
		}

		// Check if images can generate srcset
		$test_image = wp_get_attachment_image_srcset( 0 );
		if ( empty( $test_image ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Srcset generation is not working properly. Verify image size configuration.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/responsive-image-srcset',
			);
		}

		return null;
	}
}
