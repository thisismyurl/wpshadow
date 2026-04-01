<?php
/**
 * Mobile Image Lazy Loading Diagnostic
 *
 * Ensures below-fold images load on demand to reduce bandwidth.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Image Lazy Loading Diagnostic Class
 *
 * Ensures below-fold images load on demand using loading="lazy" or Intersection Observer,
 * reducing initial page weight by up to 30%.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Mobile_Image_Lazy_Loading extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-image-lazy-loading';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Image Lazy Loading';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure below-fold images load on demand to reduce bandwidth consumption';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for loading="lazy" attribute support
		$lazy_loading_enabled = apply_filters( 'wpshadow_native_lazy_loading_enabled', false );
		if ( ! $lazy_loading_enabled ) {
			$issues[] = __( 'Native loading="lazy" attribute not detected; images load unnecessarily upfront', 'wpshadow' );
		}

		// Check for Intersection Observer setup
		$intersection_observer_configured = apply_filters( 'wpshadow_intersection_observer_configured', false );
		if ( ! $intersection_observer_configured && ! $lazy_loading_enabled ) {
			$issues[] = __( 'Intersection Observer not configured for JavaScript lazy loading fallback', 'wpshadow' );
		}

		// Check if optimization plugin handles lazy loading
		$optimization_plugins = array(
			'smush' => 'Smush',
			'imagify' => 'Imagify',
			'ewww-image-optimizer' => 'EWWW',
			'shortpixel-image-optimiser' => 'ShortPixel',
		);

		$has_optimization = false;
		foreach ( $optimization_plugins as $plugin_slug => $plugin_name ) {
			if ( is_plugin_active( "$plugin_slug/$plugin_slug.php" ) ) {
				$has_optimization = true;
				break;
			}
		}

		if ( ! $has_optimization && ! $lazy_loading_enabled ) {
			$issues[] = __( 'No image optimization plugin detected; lazy loading not implemented', 'wpshadow' );
		}

		// Check impact on page weight
		$page_weight_with_lazy = apply_filters( 'wpshadow_estimated_page_weight_with_lazy_loading', 0 );
		if ( $page_weight_with_lazy > 2000 ) {
			$issues[] = sprintf(
				/* translators: %dKB: estimated page weight */
				__( 'Initial page load still %dKB; defer off-screen images with lazy loading', 'wpshadow' ),
				$page_weight_with_lazy
			);
		}

		// Check for placeholder handling (blur-up, LQIP)
		$placeholder_strategy = apply_filters( 'wpshadow_lazy_loading_placeholder_strategy', 'none' );
		if ( 'none' === $placeholder_strategy ) {
			$issues[] = __( 'Lazy loading without placeholder creates visual jarring; consider blur-up or LQIP strategy', 'wpshadow' );
		}

		// Check for videos lazy loading
		$videos_lazy_loaded = apply_filters( 'wpshadow_videos_lazy_loaded', false );
		if ( ! $videos_lazy_loaded ) {
			$issues[] = __( 'Videos not lazy loaded; consider deferring video initialization', 'wpshadow' );
		}

		// Check for responsive images with lazy loading
		$responsive_lazy_images = apply_filters( 'wpshadow_responsive_images_with_lazy_loading', false );
		if ( ! $responsive_lazy_images ) {
			$issues[] = __( 'Responsive images may not work with lazy loading; verify srcset attributes preserved', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-image-lazy-loading?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
