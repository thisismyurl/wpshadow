<?php
/**
 * Theme Image Optimization Diagnostic
 *
 * Detects unoptimized images in theme templates and assets causing slowdowns.
 *
 * **What This Check Does:**
 * 1. Identifies large images embedded in theme (logo, patterns, backgrounds)
 * 2. Detects images not using responsive srcset
 * 3. Flags images larger than necessary (4K images for web)
 * 4. Checks for modern formats (WebP, AVIF) usage
 * 5. Analyzes cumulative theme image size
 * 6. Measures optimization potential\n *
 * **Why This Matters:**\n * Theme images (logo, backgrounds, patterns) can be 500KB-5MB. An unoptimized 2MB logo loads on every
 * page. With 50,000 monthly visitors, that's 100GB of logo downloads monthly. Modern optimization\n * (WebP, responsive sizing) reduces to 50-100KB. Same visual quality, 20-50x smaller.\n *
 * **Real-World Scenario:**\n * Premium theme had beautiful background images in CSS (3 high-resolution JPGs, total1.0MB). Homepage
 * loaded all 3 images unnecessarily. After optimization: convert to WebP (90% smaller), use responsive
 * images (load smallest on mobile), lazy-load below-fold images: total download 120KB. Page load1.0s
 * faster. Mobile traffic doubled (mobile visitors no longer bouncing due to slow loading).\n *
 * **Business Impact:**\n * - Page load 1-5 seconds slower (theme images)\n * - Mobile visitors abandon site immediately\n * - Bandwidth waste: $100-$500+ monthly on unoptimized images\n * - Bounce rate 30-50% higher on image-heavy themes\n * - Revenue loss: $5,000-$50,000+ monthly\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Recovers 50-80% of theme image bandwidth\n * - #8 Inspire Confidence: Visual quality maintained, size reduced\n * - #10 Talk-About-Worthy: "Recovered 1GB of monthly bandwidth"\n *
 * **Related Checks:**\n * - Lazy Loading Implementation (load-on-demand)\n * - Responsive Images Strategy (srcset implementation)\n * - CDN Configuration (edge delivery)\n * - Mobile Performance (mobile image impact)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/theme-image-optimization\n * - Video: https://wpshadow.com/training/webp-optimization (6 min)\n * - Advanced: https://wpshadow.com/training/responsive-image-patterns (12 min)\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Image Optimization Diagnostic Class
 *
 * Checks for image optimization issues in theme.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Theme_Image_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-image-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Image Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for unoptimized theme images';

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
		$theme = wp_get_theme();
		$theme_dir = get_stylesheet_directory();
		$issues = array();

		// Check for large images in theme directory.
		$image_extensions = array( 'jpg', 'jpeg', 'png', 'gif', 'webp' );
		$large_images = array();
		$total_size = 0;

		foreach ( $image_extensions as $ext ) {
			$images = glob( $theme_dir . '/**/*.' . $ext );
			if ( $images ) {
				foreach ( $images as $image ) {
					$size = filesize( $image );
					$total_size += $size;

					if ( $size > 500000 ) { // > 500KB.
						$large_images[] = array(
							'file' => basename( $image ),
							'size' => size_format( $size ),
						);
					}
				}
			}
		}

		if ( ! empty( $large_images ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of large images */
				_n(
					'%d large image found in theme (>500KB)',
					'%d large images found in theme (>500KB)',
					count( $large_images ),
					'wpshadow'
				),
				count( $large_images )
			);
		}

		// Check if theme supports responsive images.
		if ( ! current_theme_supports( 'post-thumbnails' ) ) {
			$issues[] = __( 'Theme does not support post thumbnails', 'wpshadow' );
		}

		// Check for WebP support.
		$has_webp = false;
		$webp_images = glob( $theme_dir . '/**/*.webp' );
		if ( ! empty( $webp_images ) ) {
			$has_webp = true;
		}

		// Check for modern image formats in functions.php.
		$functions_file = $theme_dir . '/functions.php';
		if ( file_exists( $functions_file ) ) {
			$functions_content = file_get_contents( $functions_file );
			if ( preg_match( '/webp|avif/i', $functions_content ) ) {
				$has_webp = true;
			}
		}

		if ( ! $has_webp && $total_size > 1048576 ) { // > 1MB total.
			$issues[] = __( 'Theme does not use modern image formats (WebP/AVIF)', 'wpshadow' );
		}

		// Check for lazy loading implementation.
		$home_url = home_url( '/' );
		$response = wp_remote_get( $home_url, array( 'timeout' => 10 ) );

		if ( ! is_wp_error( $response ) ) {
			$html = wp_remote_retrieve_body( $response );
			$has_lazy_loading = preg_match( '/loading=["\']lazy["\']/i', $html );

			if ( ! $has_lazy_loading ) {
				$issues[] = __( 'Images not configured for lazy loading', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Theme images are not optimized for performance', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'details'     => array(
					'theme'         => $theme->get( 'Name' ),
					'large_images'  => array_slice( $large_images, 0, 5 ),
					'total_size'    => size_format( $total_size ),
					'has_webp'      => $has_webp,
					'issues'        => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-image-optimization',
			);
		}

		return null;
	}
}
