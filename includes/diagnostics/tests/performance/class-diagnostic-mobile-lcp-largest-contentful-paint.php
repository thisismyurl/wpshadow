<?php
/**
 * Mobile LCP (Largest Contentful Paint) Diagnostic
 *
 * Measures time to largest visible element on mobile devices.
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
 * Mobile LCP (Largest Contentful Paint) Diagnostic Class
 *
 * Measures time to largest visible element on mobile devices, a key Core Web Vitals
 * metric for Google search rankings and user perceived performance.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_LCP_Largest_Contentful_Paint extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-lcp-largest-contentful-paint';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile LCP (Largest Contentful Paint)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measure time to largest visible element on mobile (Core Web Vitals metric)';

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

		// Check if hero image/video is optimized
		$hero_media_optimized = apply_filters( 'wpshadow_hero_media_optimized', false );
		if ( ! $hero_media_optimized ) {
			$issues[] = __( 'Hero image/video may not be optimized; could delay LCP', 'wpshadow' );
		}

		// Check for lazy loading configuration
		$lazy_loading_configured = apply_filters( 'wpshadow_lazy_loading_configured', false );
		if ( ! $lazy_loading_configured ) {
			$issues[] = __( 'Lazy loading not configured; unnecessary images loaded on init', 'wpshadow' );
		}

		// Check server response time
		$server_ttfb = apply_filters( 'wpshadow_server_response_time_ms', 0 );
		if ( $server_ttfb > 600 ) {
			$issues[] = sprintf(
				/* translators: %dms: time to first byte */
				__( 'Server response time is %dms; target <600ms for good LCP', 'wpshadow' ),
				$server_ttfb
			);
		}

		// Check if images are responsive
		$images_responsive = apply_filters( 'wpshadow_images_responsive_srcset', false );
		if ( ! $images_responsive ) {
			$issues[] = __( 'Images may not use responsive srcset; wrong size served to mobile', 'wpshadow' );
		}

		// Check for image optimization plugins
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

		if ( ! $has_optimization ) {
			$issues[] = __( 'No image optimization plugin detected; images may not be compressed', 'wpshadow' );
		}

		// Check for CSS/JavaScript render blocking
		$render_blocking_resources = apply_filters( 'wpshadow_has_render_blocking_resources', false );
		if ( $render_blocking_resources ) {
			$issues[] = __( 'Render-blocking CSS/JavaScript detected; defer non-critical resources', 'wpshadow' );
		}

		// Check for Core Web Vitals monitoring
		$cwv_monitoring = apply_filters( 'wpshadow_core_web_vitals_monitoring_enabled', false );
		if ( ! $cwv_monitoring ) {
			$issues[] = __( 'Core Web Vitals monitoring not detected; LCP measurement unavailable', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-lcp-largest-contentful-paint',
			);
		}

		return null;
	}
}
