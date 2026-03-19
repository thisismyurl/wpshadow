<?php
/**
 * Product Image Optimization Diagnostic
 *
 * Tests if product images are optimized for fast loading.
 *
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
 * Product Image Optimization Diagnostic Class
 *
 * Validates that product images are optimized with proper sizes,
 * compression, and lazy loading for fast catalog browsing.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Product_Image_Optimization extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'product-image-optimization';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Product Image Optimization';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if product images are optimized for fast loading';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests product image optimization including sizes, compression,
	 * format, and lazy loading configuration.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check if WooCommerce is active.
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return null;
		}

		// Get WooCommerce image sizes.
		$single_width = absint( get_option( 'woocommerce_single_image_width' ) );
		$thumbnail_width = absint( get_option( 'woocommerce_thumbnail_image_width' ) );

		// Check if image dimensions are reasonable.
		$single_size_ok = ( $single_width >= 600 && $single_width <= 2000 );
		$thumbnail_size_ok = ( $thumbnail_width >= 200 && $thumbnail_width <= 500 );

		// Check for image optimization plugins.
		$has_image_optimizer = is_plugin_active( 'ewww-image-optimizer/ewww-image-optimizer.php' ) ||
							  is_plugin_active( 'wp-smushit/wp-smush.php' ) ||
							  is_plugin_active( 'shortpixel-image-optimiser/wp-shortpixel.php' );

		// Check for WebP support.
		$has_webp_plugin = is_plugin_active( 'webp-converter-for-media/webp-converter-for-media.php' ) ||
						  is_plugin_active( 'ewww-image-optimizer/ewww-image-optimizer.php' );

		// Get product count.
		$product_count = wp_count_posts( 'product' )->publish ?? 0;

		// Sample product images for analysis.
		global $wpdb;
		$product_images = $wpdb->get_results(
			"SELECT p.ID, pm.meta_value as image_id
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			 WHERE p.post_type = 'product'
			 AND p.post_status = 'publish'
			 AND pm.meta_key = '_thumbnail_id'
			 ORDER BY p.post_date DESC
			 LIMIT 20",
			ARRAY_A
		);

		$oversized_images = 0;
		$uncompressed_images = 0;
		$total_image_size = 0;

		foreach ( $product_images as $product ) {
			$image_id = absint( $product['image_id'] );
			$image_path = get_attached_file( $image_id );

			if ( $image_path && file_exists( $image_path ) ) {
				$image_size = filesize( $image_path );
				$total_image_size += $image_size;

				// Check if image is oversized (> 500KB for product image).
				if ( $image_size > 512000 ) {
					$oversized_images++;
				}

				// Check if image appears uncompressed (very large for dimensions).
				$image_meta = wp_get_attachment_metadata( $image_id );
				if ( isset( $image_meta['width'], $image_meta['height'] ) ) {
					$pixels = $image_meta['width'] * $image_meta['height'];
					$bytes_per_pixel = $image_size / $pixels;
					if ( $bytes_per_pixel > 2 ) {
						$uncompressed_images++;
					}
				}
			}
		}

		$avg_image_size = count( $product_images ) > 0 ? $total_image_size / count( $product_images ) : 0;
		$avg_image_kb = round( $avg_image_size / 1024, 2 );

		// Check gallery image optimization.
		$gallery_lightbox = get_option( 'woocommerce_enable_lightbox' ) === 'yes';

		// Check for lazy loading.
		$has_lazy_loading = get_option( 'woocommerce_enable_lazy_loading' ) === 'yes';

		// Check thumbnail regeneration.
		$thumbnail_regen_needed = get_transient( 'woocommerce_regenerate_images_needed' );

		// Check for issues.
		$issues = array();

		// Issue 1: Single image size too large.
		if ( $single_width > 2000 ) {
			$issues[] = array(
				'type'        => 'single_image_too_large',
				'description' => sprintf(
					/* translators: %d: image width */
					__( 'Single product image width set to %d px; should be 800-1200px for performance', 'wpshadow' ),
					$single_width
				),
			);
		}

		// Issue 2: Many oversized product images.
		if ( $oversized_images > 5 ) {
			$issues[] = array(
				'type'        => 'oversized_images',
				'description' => sprintf(
					/* translators: %d: number of oversized images */
					__( '%d product images exceed 500KB; should be compressed', 'wpshadow' ),
					$oversized_images
				),
			);
		}

		// Issue 3: No image optimization plugin.
		if ( ! $has_image_optimizer && absint( $product_count ) > 50 ) {
			$issues[] = array(
				'type'        => 'no_image_optimizer',
				'description' => __( 'No image optimization plugin; product images not compressed', 'wpshadow' ),
			);
		}

		// Issue 4: High average image size.
		if ( $avg_image_kb > 300 ) {
			$issues[] = array(
				'type'        => 'high_avg_image_size',
				'description' => sprintf(
					/* translators: %s: average size in KB */
					__( 'Average product image size is %s KB; should be under 150KB', 'wpshadow' ),
					$avg_image_kb
				),
			);
		}

		// Issue 5: No WebP support.
		if ( ! $has_webp_plugin && absint( $product_count ) > 100 ) {
			$issues[] = array(
				'type'        => 'no_webp',
				'description' => __( 'No WebP format support; missing 30-50% file size savings', 'wpshadow' ),
			);
		}

		// Issue 6: Lazy loading not enabled.
		if ( ! $has_lazy_loading ) {
			$issues[] = array(
				'type'        => 'lazy_loading_disabled',
				'description' => __( 'Lazy loading disabled; all product images load immediately on catalog pages', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Product images are not optimized, which slows down catalog browsing and increases bandwidth costs', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/product-image-optimization',
				'details'      => array(
					'product_count'           => absint( $product_count ),
					'single_image_width'      => $single_width,
					'thumbnail_image_width'   => $thumbnail_width,
					'single_size_ok'          => $single_size_ok,
					'thumbnail_size_ok'       => $thumbnail_size_ok,
					'has_image_optimizer'     => $has_image_optimizer,
					'has_webp_support'        => $has_webp_plugin,
					'oversized_images'        => $oversized_images,
					'uncompressed_images'     => $uncompressed_images,
					'avg_image_size_kb'       => $avg_image_kb,
					'gallery_lightbox_enabled' => $gallery_lightbox,
					'lazy_loading_enabled'    => $has_lazy_loading,
					'thumbnail_regen_needed'  => (bool) $thumbnail_regen_needed,
					'issues_detected'         => $issues,
					'recommendation'          => __( 'Install ShortPixel/EWWW, enable WebP, reduce image dimensions, enable lazy loading', 'wpshadow' ),
					'image_size_guidelines'   => array(
						'Single product'   => '800-1200px width, <200KB',
						'Thumbnail'        => '300-400px width, <50KB',
						'Gallery'          => '1000-1500px width, <250KB',
						'Format'           => 'WebP (30-50% smaller than JPG)',
						'Compression'      => '80-85% quality (optimal balance)',
					),
					'performance_improvement' => '40-60% faster catalog page loading with optimization',
					'bandwidth_savings'       => 'Up to 70% with WebP + compression',
				),
			);
		}

		return null;
	}
}
