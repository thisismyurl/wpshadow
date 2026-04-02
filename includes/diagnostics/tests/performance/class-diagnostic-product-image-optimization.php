<?php
/**
 * Product Image Optimization Diagnostic
 *
 * Checks if product images are compressed and cached.
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
 * Verifies that product images are properly optimized, compressed,
 * and cached for fast delivery.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Product_Image_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'product-image-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Product Image Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if product images are compressed and cached';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the product image optimization diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if image optimization issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for image optimization plugin.
		$optimization_plugins = array(
			'optimole-wp/optimole-wp.php',
			'imagify/imagify.php',
			'shortpixel-image-optimiser/wp-shortpixel.php',
			'tinypng-compress-images/tinypng.php',
		);

		$has_optimization = false;
		$active_plugin = null;

		foreach ( $optimization_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_optimization = true;
				$active_plugin = $plugin;
				break;
			}
		}

		$stats['image_optimizer'] = $active_plugin ?: 'None';

		if ( ! $has_optimization ) {
			$warnings[] = __( 'No image optimization plugin active', 'wpshadow' );
		}

		// Check for lazy loading.
		$lazy_loading = get_option( 'woocommerce_lazy_load_product_images' );
		$stats['lazy_loading_enabled'] = boolval( $lazy_loading );

		if ( ! $lazy_loading ) {
			$warnings[] = __( 'Lazy loading not enabled for product images', 'wpshadow' );
		}

		// Check for WebP format support.
		$webp_enabled = get_option( 'woocommerce_enable_webp_images' );
		$stats['webp_format'] = boolval( $webp_enabled );

		if ( ! $webp_enabled ) {
			$warnings[] = __( 'WebP format not enabled - use modern image format', 'wpshadow' );
		}

		// Check image dimensions.
		$thumbnail_width = get_option( 'woocommerce_thumbnail_image_width' );
		$thumbnail_height = get_option( 'woocommerce_thumbnail_image_height' );

		$stats['thumbnail_dimensions'] = $thumbnail_width . 'x' . $thumbnail_height;

		// Check if dimensions are reasonable.
		if ( intval( $thumbnail_width ) > 500 || intval( $thumbnail_height ) > 500 ) {
			$warnings[] = sprintf(
				/* translators: %s: dimensions */
				__( 'Thumbnail dimensions are large (%s) - resize for performance', 'wpshadow' ),
				$stats['thumbnail_dimensions']
			);
		}

		// Check for WooCommerce for WooCommerce image caching.
		$image_cache_enabled = get_option( 'woocommerce_image_cache_enabled' );
		$stats['image_caching'] = boolval( $image_cache_enabled );

		if ( ! $image_cache_enabled ) {
			$warnings[] = __( 'Image caching not enabled', 'wpshadow' );
		}

		// Check for responsive images.
		$responsive_images = get_option( 'woocommerce_responsive_images' );
		$stats['responsive_images'] = boolval( $responsive_images );

		if ( ! $responsive_images ) {
			$warnings[] = __( 'Responsive images not enabled - images won\'t scale properly', 'wpshadow' );
		}

		// Check for srcset configuration.
		$srcset_enabled = get_option( 'woocommerce_enable_srcset' );
		$stats['srcset_enabled'] = boolval( $srcset_enabled );

		if ( ! $srcset_enabled ) {
			$warnings[] = __( 'Srcset not enabled - missing responsive image sizes', 'wpshadow' );
		}

		// Check image quality setting.
		$image_quality = get_option( 'woocommerce_image_quality', 82 );
		$stats['image_quality'] = intval( $image_quality ) . '%';

		if ( $image_quality > 90 ) {
			$warnings[] = sprintf(
				/* translators: %d: percentage */
				__( 'Image quality set too high (%d%%) - reduce for smaller file sizes', 'wpshadow' ),
				$image_quality
			);
		}

		if ( $image_quality < 70 ) {
			$warnings[] = sprintf(
				/* translators: %d: percentage */
				__( 'Image quality set too low (%d%%) - may cause visible artifacts', 'wpshadow' ),
				$image_quality
			);
		}

		// Check uncompressed images count.
		$uncompressed_images = get_option( 'woocommerce_uncompressed_images_count', 0 );
		$stats['uncompressed_images_count'] = intval( $uncompressed_images );

		if ( intval( $uncompressed_images ) > 0 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( '%d product images not yet compressed', 'wpshadow' ),
				intval( $uncompressed_images )
			);
		}

		// Check total image size.
		$total_image_size = get_option( 'woocommerce_total_product_image_size_mb', 0 );
		$stats['total_image_size_mb'] = round( floatval( $total_image_size ), 2 );

		// Check average image file size.
		$avg_image_size = get_option( 'woocommerce_avg_product_image_size_kb', 0 );
		$stats['avg_image_size_kb'] = round( floatval( $avg_image_size ), 2 );

		if ( round( floatval( $avg_image_size ), 2 ) > 500 ) {
			$warnings[] = sprintf(
				/* translators: %d: KB */
				__( 'Average image size is %dKB - compress images further', 'wpshadow' ),
				intval( $avg_image_size )
			);
		}

		// Check for image CDN.
		$image_cdn = get_option( 'woocommerce_image_cdn_enabled' );
		$stats['image_cdn'] = boolval( $image_cdn );

		if ( ! $image_cdn ) {
			$warnings[] = __( 'Image CDN not enabled - images not distributed globally', 'wpshadow' );
		}

		// Check for AVIF format support.
		$avif_enabled = get_option( 'woocommerce_enable_avif_images' );
		$stats['avif_format'] = boolval( $avif_enabled );

		if ( ! $avif_enabled ) {
			$warnings[] = __( 'AVIF format not enabled - next-gen image format for modern browsers', 'wpshadow' );
		}

		// Check for alt text on images.
		$products_with_missing_alt = get_option( 'woocommerce_images_missing_alt_text', 0 );
		$stats['images_missing_alt_text'] = intval( $products_with_missing_alt );

		if ( intval( $products_with_missing_alt ) > 0 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( '%d images missing alt text - important for accessibility and SEO', 'wpshadow' ),
				intval( $products_with_missing_alt )
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Product image optimization has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/product-image-optimization',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Product image optimization has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/product-image-optimization',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Product images are optimized.
	}
}
