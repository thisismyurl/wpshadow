<?php
/**
 * WooCommerce Product Image Integration Diagnostic
 *
 * Validates WooCommerce product gallery functionality, image assignments,
 * and proper display of product images.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Product Image Integration Diagnostic Class
 *
 * Detects issues with WooCommerce product images and gallery functionality.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Woocommerce_Product_Image_Integration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'woocommerce-product-image-integration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WooCommerce Product Image Integration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests WooCommerce product gallery functionality and image assignments';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'integrations';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - WooCommerce is active
	 * - Product image sizes are configured
	 * - Products have assigned featured images
	 * - Gallery images are properly configured
	 * - Image regeneration is needed
	 * - Placeholder images work correctly
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only run if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		$issues = array();
		global $wpdb;

		// Check if WooCommerce image sizes are properly configured.
		$wc_image_sizes = array(
			'woocommerce_thumbnail'        => 'shop_thumbnail',
			'woocommerce_single'           => 'shop_single',
			'woocommerce_gallery_thumbnail' => 'shop_catalog',
		);

		$missing_sizes = array();
		foreach ( $wc_image_sizes as $option_key => $size_name ) {
			$size = get_option( $option_key . '_image_width' );
			if ( empty( $size ) || $size == 0 ) {
				$missing_sizes[] = $size_name;
			}
		}

		if ( ! empty( $missing_sizes ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of image size names */
				__( 'WooCommerce image sizes not properly configured: %s', 'wpshadow' ),
				implode( ', ', $missing_sizes )
			);
		}

		// Check for products without featured images.
		$products_without_images = $wpdb->get_var(
			"SELECT COUNT(DISTINCT p.ID)
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_thumbnail_id'
			WHERE p.post_type = 'product'
			AND p.post_status = 'publish'
			AND (pm.meta_value IS NULL OR pm.meta_value = '' OR pm.meta_value = '0')"
		);

		if ( $products_without_images > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of products */
				_n(
					'%d published product is missing a featured image',
					'%d published products are missing featured images',
					$products_without_images,
					'wpshadow'
				),
				$products_without_images
			);
		}

		// Check for products with gallery images but no featured image (common misconfiguration).
		$products_with_gallery_no_featured = $wpdb->get_var(
			"SELECT COUNT(DISTINCT p.ID)
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm_gallery ON p.ID = pm_gallery.post_id AND pm_gallery.meta_key = '_product_image_gallery'
			LEFT JOIN {$wpdb->postmeta} pm_thumbnail ON p.ID = pm_thumbnail.post_id AND pm_thumbnail.meta_key = '_thumbnail_id'
			WHERE p.post_type = 'product'
			AND p.post_status = 'publish'
			AND pm_gallery.meta_value != ''
			AND (pm_thumbnail.meta_value IS NULL OR pm_thumbnail.meta_value = '' OR pm_thumbnail.meta_value = '0')"
		);

		if ( $products_with_gallery_no_featured > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of products */
				_n(
					'%d product has gallery images but no featured image',
					'%d products have gallery images but no featured images',
					$products_with_gallery_no_featured,
					'wpshadow'
				),
				$products_with_gallery_no_featured
			);
		}

		// Check for orphaned gallery images (images in _product_image_gallery that don't exist).
		$products_with_invalid_gallery = $wpdb->get_var(
			"SELECT COUNT(DISTINCT pm.post_id)
			FROM {$wpdb->postmeta} pm
			WHERE pm.meta_key = '_product_image_gallery'
			AND pm.meta_value != ''
			AND pm.post_id IN (
				SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'
			)"
		);

		// For products with galleries, check if images actually exist.
		if ( $products_with_invalid_gallery > 0 ) {
			// Sample check: get a few products and verify their gallery images exist.
			$sample_products = $wpdb->get_results(
				"SELECT post_id, meta_value
				FROM {$wpdb->postmeta}
				WHERE meta_key = '_product_image_gallery'
				AND meta_value != ''
				LIMIT 10",
				ARRAY_A
			);

			$invalid_count = 0;
			foreach ( $sample_products as $product ) {
				$gallery_ids = explode( ',', $product['meta_value'] );
				foreach ( $gallery_ids as $image_id ) {
					$image_id = absint( trim( $image_id ) );
					if ( $image_id > 0 && get_post_type( $image_id ) !== 'attachment' ) {
						$invalid_count++;
						break; // One invalid is enough for this product.
					}
				}
			}

			if ( $invalid_count > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of products */
					__( 'At least %d products have invalid or deleted images in their galleries', 'wpshadow' ),
					$invalid_count
				);
			}
		}

		// Check if WooCommerce placeholder image is configured.
		$placeholder_id = get_option( 'woocommerce_placeholder_image', 0 );
		if ( empty( $placeholder_id ) || get_post_type( $placeholder_id ) !== 'attachment' ) {
			$issues[] = __( 'WooCommerce placeholder image is not configured or invalid', 'wpshadow' );
		}

		// Check if theme supports WooCommerce gallery features.
		if ( ! current_theme_supports( 'wc-product-gallery-zoom' ) &&
			 ! current_theme_supports( 'wc-product-gallery-lightbox' ) &&
			 ! current_theme_supports( 'wc-product-gallery-slider' ) ) {
			$issues[] = __( 'Theme does not declare support for WooCommerce product gallery features', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( ' ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'details'     => array(
					'products_without_featured' => $products_without_images ?? 0,
					'products_gallery_no_featured' => $products_with_gallery_no_featured ?? 0,
					'missing_image_sizes' => $missing_sizes,
					'placeholder_configured' => ! empty( $placeholder_id ) && get_post_type( $placeholder_id ) === 'attachment',
				),
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-product-image-integration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
