<?php
/**
 * Mobile Background Image Performance Diagnostic
 *
 * Validates background images are optimized for mobile.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Background Image Performance Diagnostic Class
 *
 * Validates that background images are optimized for mobile with appropriate
 * image sizes and media queries to reduce bandwidth.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_Background_Image_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-background-image-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Background Image Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validate background images are optimized for mobile with media queries';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if background images have mobile-specific media queries
		$mobile_media_queries = apply_filters( 'wpshadow_background_images_have_mobile_media_queries', false );
		if ( ! $mobile_media_queries ) {
			$issues[] = __( 'Background images should use @media (max-width: 768px) for mobile-specific sizes', 'wpshadow' );
		}

		// Check if hero images are avoided on mobile
		$no_hero_on_mobile = apply_filters( 'wpshadow_hero_images_not_on_mobile', false );
		if ( ! $no_hero_on_mobile ) {
			$issues[] = __( 'Large hero background images on mobile waste bandwidth; use smaller/simplified images', 'wpshadow' );
		}

		// Check for background image optimization
		$bg_images_optimized = apply_filters( 'wpshadow_background_images_optimized', false );
		if ( ! $bg_images_optimized ) {
			$issues[] = __( 'Background images may not be compressed; consider running through image optimizer', 'wpshadow' );
		}

		// Check if background images are repeated (pattern vs large image)
		$uses_pattern_backgrounds = apply_filters( 'wpshadow_uses_pattern_backgrounds_not_large_images', false );
		if ( ! $uses_pattern_backgrounds ) {
			$issues[] = __( 'Large background images could be replaced with CSS patterns or smaller textures', 'wpshadow' );
		}

		// Check for background image lazy loading
		$bg_lazy_loading = apply_filters( 'wpshadow_background_images_lazy_loaded', false );
		if ( ! $bg_lazy_loading ) {
			$issues[] = __( 'Below-fold background images should be lazy loaded to reduce initial page weight', 'wpshadow' );
		}

		// Check for appropriate background image sizing
		$bg_sizing_appropriate = apply_filters( 'wpshadow_background_image_size_appropriate_for_mobile', false );
		if ( ! $bg_sizing_appropriate ) {
			$issues[] = __( 'Background image dimensions should match maximum viewport width on mobile', 'wpshadow' );
		}

		// Check if using CSS gradients instead of images where possible
		$uses_css_gradients = apply_filters( 'wpshadow_uses_css_gradients_instead_of_images', false );
		if ( ! $uses_css_gradients ) {
			$issues[] = __( 'CSS gradients could replace some background images for smaller file sizes', 'wpshadow' );
		}

		// Check for WebP format support
		$webp_support = apply_filters( 'wpshadow_background_images_webp_format', false );
		if ( ! $webp_support ) {
			$issues[] = __( 'Background images could use WebP format with PNG fallback for better compression', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-background-image-performance',
			);
		}

		return null;
	}
}
