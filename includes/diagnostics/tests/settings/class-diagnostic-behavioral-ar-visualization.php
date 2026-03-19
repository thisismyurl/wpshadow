<?php
/**
 * Diagnostic: AR Product Visualization
 *
 * Tests whether the site provides augmented reality product previews that
 * reduce returns by 25% and increase purchase confidence.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4555
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AR Product Visualization Diagnostic
 *
 * Checks for augmented reality features. AR "try before you buy" reduces returns
 * by 25% for furniture, home decor, fashion - customers visualize products in context.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Behavioral_AR_Visualization extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'provides-ar-product-visualization';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'AR Product Visualization';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site provides AR product previews';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for AR implementation.
	 *
	 * Looks for AR/3D product visualization capabilities.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if missing, null if present.
	 */
	public static function check() {
		// Check for AR/3D plugins.
		$ar_plugins = array(
			'model-viewer/model-viewer.php'                  => 'Model Viewer',
		);

		foreach ( $ar_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return null; // Has AR capability.
			}
		}

		// Check product content for AR model files.
		if ( class_exists( 'WooCommerce' ) ) {
			$products = get_posts(
				array(
					'post_type'      => 'product',
					'posts_per_page' => 20,
					'post_status'    => 'publish',
				)
			);

			foreach ( $products as $product ) {
				// Check for 3D model file extensions in content/attachments.
				$attachments = get_attached_media( '', $product->ID );
				foreach ( $attachments as $attachment ) {
					$file_url = wp_get_attachment_url( $attachment->ID );
					if ( preg_match( '/\.(glb|gltf|usdz)$/i', $file_url ) ) {
						return null; // Has 3D models.
					}
				}
			}
		}

		// Only recommend for physical product e-commerce.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null; // Not e-commerce.
		}

		// Check product categories for AR-suitable items.
		$ar_suitable_categories = array( 'furniture', 'home', 'decor', 'fashion', 'jewelry', 'accessories' );
		$categories             = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
			)
		);

		$has_ar_suitable_products = false;
		foreach ( $categories as $category ) {
			foreach ( $ar_suitable_categories as $keyword ) {
				if ( stripos( $category->name, $keyword ) !== false ) {
					$has_ar_suitable_products = true;
					break 2;
				}
			}
		}

		if ( ! $has_ar_suitable_products ) {
			return null; // No AR-suitable products.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'No AR product visualization detected for suitable product categories. Augmented Reality "try before you buy" reduces returns by 25% for furniture, home decor, fashion - customers visualize products in their space before purchasing. AR increases purchase confidence and conversion rates. Modern browsers support AR with simple 3D model files (GLB/USDZ). Consider AR for physical products.',
				'wpshadow'
			),
			'severity'     => 'low',
			'threat_level' => 22,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/ar-product-visualization',
		);
	}
}
