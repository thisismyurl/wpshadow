<?php
/**
 * Mobile Responsive Image Srcset Treatment
 *
 * Validates images use srcset for density variants.
 *
 * @since   1.6033.1645
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Responsive Image Srcset Treatment Class
 *
 * Validates that images use srcset with density variants (1x/2x/3x) and sizes
 * attribute for responsive image delivery, optimizing bandwidth.
 *
 * @since 1.6033.1645
 */
class Treatment_Mobile_Responsive_Image_Srcset extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-responsive-image-srcset';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Responsive Image Srcset';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validate images use srcset with density/size variants for responsive delivery';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if images use srcset attribute
		$has_srcset = apply_filters( 'wpshadow_images_have_srcset_attribute', false );
		if ( ! $has_srcset ) {
			$issues[] = __( 'Images should use srcset attribute with 1x/2x/3x density variants for different devices', 'wpshadow' );
		}

		// Check if images have sizes attribute
		$has_sizes = apply_filters( 'wpshadow_images_have_sizes_attribute', false );
		if ( ! $has_sizes ) {
			$issues[] = __( 'Images should have sizes attribute defining layout widths at different breakpoints', 'wpshadow' );
		}

		// Check for appropriate image density variants
		$density_variants = apply_filters( 'wpshadow_images_have_appropriate_density_variants', false );
		if ( ! $density_variants ) {
			$issues[] = __( 'Include at least 1x and 2x density variants; 3x optional for high-res displays', 'wpshadow' );
		}

		// Check for size variant optimization
		$size_variants_optimized = apply_filters( 'wpshadow_images_size_variants_optimized', false );
		if ( ! $size_variants_optimized ) {
			$issues[] = __( 'Image size variants should match actual layout widths to avoid oversized/undersized images', 'wpshadow' );
		}

		// Check if WordPress generates responsive images automatically
		$wp_responsive_images = apply_filters( 'wpshadow_wordpress_generates_responsive_images', false );
		if ( ! $wp_responsive_images ) {
			$issues[] = __( 'WordPress should automatically generate srcset for native media; verify in theme settings', 'wpshadow' );
		}

		// Check for picture element usage where needed
		$picture_element_used = apply_filters( 'wpshadow_picture_element_used_appropriately', false );
		if ( ! $picture_element_used ) {
			$issues[] = __( 'Complex responsive images may benefit from <picture> element for art direction', 'wpshadow' );
		}

		// Check for WebP format variants
		$webp_srcset_variants = apply_filters( 'wpshadow_images_include_webp_variants', false );
		if ( ! $webp_srcset_variants ) {
			$issues[] = __( 'Consider providing WebP variants in srcset for better compression on modern browsers', 'wpshadow' );
		}

		// Check for image-to-optimal-size ratio
		$images_appropriately_sized = apply_filters( 'wpshadow_images_appropriately_sized_for_layout', false );
		if ( ! $images_appropriately_sized ) {
			$issues[] = __( 'Some images may be delivering wrong size; measure actual vs optimal dimensions', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-responsive-image-srcset',
			);
		}

		return null;
	}
}
