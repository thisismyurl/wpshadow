<?php
/**
 * AR Product Visualization Diagnostic
 *
 * Tests whether the site provides augmented reality product previews that reduce returns.
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
 * AR Product Visualization Diagnostic Class
 *
 * Augmented Reality allows customers to visualize products in their own space,
 * improving purchase confidence and reducing returns, especially for furniture and home decor.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Ar_Product_Visualization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ar-product-visualization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'AR Product Visualization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site provides augmented reality product previews that reduce returns';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'e-commerce';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only relevant for e-commerce sites.
		if ( ! class_exists( 'WooCommerce' ) && ! class_exists( 'Easy_Digital_Downloads' ) ) {
			return null;
		}

		$issues = array();
		$ar_score = 0;
		$max_score = 6;

		// Check for AR plugins.
		$ar_plugins = array(
			'ar-for-wordpress/ar-for-wordpress.php' => 'AR for WordPress',
			'woocommerce-ar/woocommerce-ar.php' => 'WooCommerce AR',
			'model-viewer-for-woocommerce/model-viewer-for-woocommerce.php' => 'Model Viewer',
			'threedee/threedee.php' => '3D Product Viewer',
		);

		$has_ar_plugin = false;
		$active_plugin = '';
		foreach ( $ar_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_ar_plugin = true;
				$active_plugin = $plugin_name;
				$ar_score++;
				break;
			}
		}

		if ( ! $has_ar_plugin ) {
			$issues[] = __( 'No AR plugin detected for product visualization', 'wpshadow' );
		}

		// Check for 3D models in products.
		$has_3d_models = self::check_3d_models();
		if ( $has_3d_models ) {
			$ar_score++;
		} else {
			$issues[] = __( 'No 3D product models (GLB, GLTF, USDZ) found', 'wpshadow' );
		}

		// Check for AR Quick Look (Apple) support.
		$apple_ar_support = self::check_apple_ar_support();
		if ( $apple_ar_support ) {
			$ar_score++;
		} else {
			$issues[] = __( 'No Apple AR Quick Look support for iOS devices', 'wpshadow' );
		}

		// Check for Scene Viewer (Google) support.
		$google_ar_support = self::check_google_ar_support();
		if ( $google_ar_support ) {
			$ar_score++;
		} else {
			$issues[] = __( 'No Google Scene Viewer support for Android devices', 'wpshadow' );
		}

		// Check for AR indicators on product pages.
		$ar_indicators = self::check_ar_indicators();
		if ( $ar_indicators ) {
			$ar_score++;
		} else {
			$issues[] = __( 'No AR indicators or "View in Your Space" buttons on products', 'wpshadow' );
		}

		// Check for products suitable for AR.
		$suitable_products = self::check_suitable_products();
		if ( $suitable_products ) {
			$ar_score++;
		} else {
			$issues[] = __( 'Product catalog may not be suitable for AR visualization', 'wpshadow' );
		}

		// Determine severity based on AR implementation.
		$ar_percentage = ( $ar_score / $max_score ) * 100;

		if ( $ar_percentage < 30 ) {
			// Minimal or no AR implementation.
			$severity = 'medium';
			$threat_level = 40;
		} elseif ( $ar_percentage < 60 ) {
			// Basic AR implementation.
			$severity = 'low';
			$threat_level = 25;
		} else {
			// Good AR implementation - no issue.
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: AR implementation percentage */
				__( 'AR implementation at %d%%. ', 'wpshadow' ),
				(int) $ar_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'AR visualization can reduce returns by up to 40% for furniture and home goods', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ar-product-visualization',
			);
		}

		return null;
	}

	/**
	 * Check for 3D models in media library or products.
	 *
	 * @since 1.6093.1200
	 * @return bool True if 3D models exist, false otherwise.
	 */
	private static function check_3d_models() {
		// Check for 3D model file types in media library.
		$model_extensions = array( 'glb', 'gltf', 'usdz', 'obj', 'fbx' );

		$args = array(
			'post_type'      => 'attachment',
			'posts_per_page' => 100,
			'post_status'    => 'inherit',
		);

		$attachments = get_posts( $args );
		foreach ( $attachments as $attachment ) {
			$file = get_attached_file( $attachment->ID );
			$extension = pathinfo( $file, PATHINFO_EXTENSION );

			if ( in_array( strtolower( $extension ), $model_extensions, true ) ) {
				return true;
			}
		}

		// Check WooCommerce product metadata for 3D model links.
		if ( class_exists( 'WooCommerce' ) ) {
			$products = wc_get_products(
				array(
					'limit'  => 50,
					'status' => 'publish',
				)
			);

			foreach ( $products as $product ) {
				$ar_model = $product->get_meta( '_ar_model' );
				$model_url = $product->get_meta( '_3d_model_url' );

				if ( ! empty( $ar_model ) || ! empty( $model_url ) ) {
					return true;
				}
			}
		}

		return apply_filters( 'wpshadow_has_3d_models', false );
	}

	/**
	 * Check for Apple AR Quick Look support.
	 *
	 * @since 1.6093.1200
	 * @return bool True if Apple AR support exists, false otherwise.
	 */
	private static function check_apple_ar_support() {
		// Check for USDZ files (Apple's AR format).
		$args = array(
			'post_type'      => 'attachment',
			'posts_per_page' => 50,
			'post_status'    => 'inherit',
		);

		$attachments = get_posts( $args );
		foreach ( $attachments as $attachment ) {
			$file = get_attached_file( $attachment->ID );
			$extension = pathinfo( $file, PATHINFO_EXTENSION );

			if ( strtolower( $extension ) === 'usdz' ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_apple_ar_support', false );
	}

	/**
	 * Check for Google Scene Viewer support.
	 *
	 * @since 1.6093.1200
	 * @return bool True if Google AR support exists, false otherwise.
	 */
	private static function check_google_ar_support() {
		// Check for GLB/GLTF files (Google's AR format).
		$args = array(
			'post_type'      => 'attachment',
			'posts_per_page' => 50,
			'post_status'    => 'inherit',
		);

		$attachments = get_posts( $args );
		foreach ( $attachments as $attachment ) {
			$file = get_attached_file( $attachment->ID );
			$extension = pathinfo( $file, PATHINFO_EXTENSION );

			if ( in_array( strtolower( $extension ), array( 'glb', 'gltf' ), true ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_google_ar_support', false );
	}

	/**
	 * Check for AR indicators on the site.
	 *
	 * @since 1.6093.1200
	 * @return bool True if AR indicators exist, false otherwise.
	 */
	private static function check_ar_indicators() {
		// Check for content mentioning AR or "view in space".
		$ar_keywords = array( 'view in your space', 'ar preview', 'augmented reality', '3d view' );

		foreach ( $ar_keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page', 'product' ),
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_ar_indicators', false );
	}

	/**
	 * Check if products are suitable for AR visualization.
	 *
	 * @since 1.6093.1200
	 * @return bool True if suitable products exist, false otherwise.
	 */
	private static function check_suitable_products() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return false;
		}

		// Check for product categories that benefit from AR.
		$ar_categories = array(
			'furniture',
			'home-decor',
			'home-and-garden',
			'jewelry',
			'accessories',
			'appliances',
			'electronics',
		);

		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			)
		);

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$slug = $term->slug;
				foreach ( $ar_categories as $category ) {
					if ( strpos( $slug, $category ) !== false ) {
						return true;
					}
				}
			}
		}

		return apply_filters( 'wpshadow_has_ar_suitable_products', false );
	}
}
