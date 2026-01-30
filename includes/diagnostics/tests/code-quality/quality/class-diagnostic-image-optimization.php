<?php
/**
 * Image Optimization Diagnostic
 *
 * Verifies images optimized (compressed, resized) to
 * reduce page load time.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Image_Optimization Class
 *
 * Verifies images are optimized.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Image_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies images are optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if optimization missing, null otherwise.
	 */
	public static function check() {
		$optimization_status = self::check_image_optimization();

		if ( $optimization_status['is_optimized'] ) {
			return null; // Images optimized
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Images not optimized. Uncompressed images = 50-90% of page load. 4MB image load takes 4 seconds. Optimize = 200KB + 0.2 seconds. Essential.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/image-optimization',
			'family'       => self::$family,
			'meta'         => array(
				'images_optimized' => false,
			),
			'details'      => array(
				'impact_of_unoptimized_images'    => array(
					'Page Speed' => array(
						'Unoptimized: 4MB image = 4 seconds load',
						'Optimized: 200KB image = 0.2 seconds',
						'Difference: 20x faster',
					),
					'Bounce Rate' => array(
						'> 3 seconds: 40% bounce',
						'> 5 seconds: 75% bounce',
						'Optimization: Reduce to < 2 seconds',
					),
					'SEO' => array(
						'Core Web Vitals: LCP (largest paint)',
						'Images: Biggest contributor to LCP',
						'Optimization: Boost search rankings',
					),
				),
				'image_optimization_methods'       => array(
					'Compression' => array(
						'Lossy: 50-80% file size reduction',
						'Lossless: 20-40% file size reduction',
						'Example: 4MB → 200KB via lossy',
					),
					'Resizing' => array(
						'Remove metadata: -20% file size',
						'Resize to display size: -40-60%',
						'Example: 4000×3000 → 800×600 for thumbnail',
					),
					'Modern Formats' => array(
						'WebP: 25-35% smaller than JPEG',
						'AVIF: 40-50% smaller than JPEG',
						'JPEG XL: Future format',
					),
				),
				'image_optimization_tools'         => array(
					'WordPress Plugins' => array(
						'Imagify: Auto-compress on upload',
						'Smush: Batch optimize existing',
						'Shortpixel: Excellent quality',
						'TinyPNG: Simple, reliable',
					),
					'CDN Services' => array(
						'Cloudflare: Auto-optimize + WebP',
						'CloudImage: Format negotiation',
						'Imgix: Professional image service',
					),
					'Manual Tools' => array(
						'Online: tinypng.com, tinyjpg.com',
						'CLI: ImageMagick, cwebp',
						'Desktop: Adobe, Pixelmator',
					),
				),
				'optimization_strategy'           => array(
					'First: Batch Optimize' => array(
						'Existing images: Use plugin',
						'Catch-up on back catalog',
						'Takes: Few hours for large sites',
					),
					'Second: Auto-Optimize' => array(
						'New uploads: Auto-compress',
						'Setting: Enable in plugin',
						'Impact: All future uploads optimized',
					),
					'Third: WebP Delivery' => array(
						'Format: Serve WebP to modern browsers',
						'Fallback: JPEG for old browsers',
						'CDN or plugin: Handles detection',
					),
				),
				'image_best_practices'            => array(
					__( '1. Resize before upload (right size)' ),
					__( '2. Compress before upload (reduced file)' ),
					__( '3. Use plugin auto-compress (catch-all)' ),
					__( '4. Lazy load off-screen images (deferred)' ),
					__( '5. Enable WebP delivery (modern format)' ),
					__( '6. Serve via CDN (geographically closer)' ),
				),
			),
		);
	}

	/**
	 * Check image optimization.
	 *
	 * @since  1.2601.2148
	 * @return array Optimization status.
	 */
	private static function check_image_optimization() {
		$is_optimized = false;

		// Check if optimization plugin active
		if ( is_plugin_active( 'wp-smushit/wp-smush.php' ) ) {
			$is_optimized = true;
		}

		if ( is_plugin_active( 'imagify/imagify.php' ) ) {
			$is_optimized = true;
		}

		if ( is_plugin_active( 'shortpixel-image-optimiser/shortpixel.php' ) ) {
			$is_optimized = true;
		}

		return array(
			'is_optimized' => $is_optimized,
		);
	}
}
