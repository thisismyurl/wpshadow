<?php
/**
 * Product Image Quality Diagnostic
 *
 * Measures product image dimensions. Low-resolution images reduce
 * perceived quality and increase return rates.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1830
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Product_Image_Quality Class
 *
 * Checks WooCommerce product image dimensions.
 *
 * @since 1.6028.1830
 */
class Diagnostic_Product_Image_Quality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'product-image-quality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Product Image Quality Below 1000px';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks product image dimensions for quality standards';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ux_engagement';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1830
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only check if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		$image_analysis = self::analyze_product_images();

		if ( $image_analysis['avg_width'] >= 1000 ) {
			return null; // Images meet quality standards.
		}

		// Determine severity.
		$avg_width = $image_analysis['avg_width'];
		if ( $avg_width < 500 ) {
			$severity     = 'high';
			$threat_level = 65;
		} elseif ( $avg_width < 750 ) {
			$severity     = 'medium';
			$threat_level = 55;
		} else {
			$severity     = 'low';
			$threat_level = 40;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %d: average image width */
				__( 'Average product image width: %dpx (recommended: ≥1000px)', 'wpshadow' ),
				$avg_width
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/product-image-quality',
			'family'      => self::$family,
			'meta'        => array(
				'avg_width'         => $avg_width,
				'products_checked'  => $image_analysis['products_checked'],
				'below_1000px'      => $image_analysis['below_1000px'],
				'recommended'       => __( 'Use high-resolution product images (≥1000px width)', 'wpshadow' ),
				'impact_level'      => 'high',
				'immediate_actions' => array(
					__( 'Replace low-res images with 1200px+ versions', 'wpshadow' ),
					__( 'Enable image zoom functionality', 'wpshadow' ),
					__( 'Use WebP format for better compression', 'wpshadow' ),
					__( 'Add multiple angles in gallery', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Product image quality directly impacts perceived value, trust, and return rates. Studies show 75% of shoppers prioritize image quality in purchase decisions. Low-resolution images (below 1000px) appear blurry on modern displays, reduce customer confidence, and increase return rates. High-quality images (1200px+) enable zoom, showcase details, and increase conversion by 30-40%.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Lower Perceived Value: Blurry images suggest cheap products', 'wpshadow' ),
					__( 'Higher Returns: Customers surprised by actual quality', 'wpshadow' ),
					__( 'Lower Conversion: Can\'t see product details clearly', 'wpshadow' ),
					__( 'Mobile Issues: Images too small on high-res screens', 'wpshadow' ),
				),
				'image_analysis' => $image_analysis,
				'quality_standards' => array(
					'≥1200px' => __( 'Excellent - Professional quality with zoom', 'wpshadow' ),
					'≥1000px' => __( 'Good - Clear on all devices', 'wpshadow' ),
					'500-1000px' => __( 'Warning - May appear blurry', 'wpshadow' ),
					'<500px'  => __( 'Critical - Unprofessional appearance', 'wpshadow' ),
				),
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'Re-photograph Products', 'wpshadow' ),
						'description' => __( 'Take new high-resolution product photos', 'wpshadow' ),
						'steps'       => array(
							__( 'Use camera/phone with ≥12MP resolution', 'wpshadow' ),
							__( 'Shoot on white background with good lighting', 'wpshadow' ),
							__( 'Export at full resolution (3000-4000px)', 'wpshadow' ),
							__( 'Compress to WebP format (maintains quality)', 'wpshadow' ),
							__( 'Upload to WooCommerce product gallery', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'Image Upscaling Service', 'wpshadow' ),
						'description' => __( 'AI upscaling to improve existing images', 'wpshadow' ),
						'steps'       => array(
							__( 'Export existing product images', 'wpshadow' ),
							__( 'Use AI upscaler: Topaz Gigapixel or Let\'s Enhance', 'wpshadow' ),
							__( 'Upscale to 2x or 4x resolution', 'wpshadow' ),
							__( 'Review quality improvements', 'wpshadow' ),
							__( 'Replace original images', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'Professional Product Photography', 'wpshadow' ),
						'description' => __( 'Hire photographer for catalog', 'wpshadow' ),
						'steps'       => array(
							__( 'Hire e-commerce product photographer', 'wpshadow' ),
							__( 'Request 1500-2000px images minimum', 'wpshadow' ),
							__( 'Get multiple angles per product', 'wpshadow' ),
							__( 'Include lifestyle/contextual shots', 'wpshadow' ),
							__( 'Budget ~$25-50 per product', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Use 1200px width minimum for primary images', 'wpshadow' ),
					__( 'Enable zoom functionality on product pages', 'wpshadow' ),
					__( 'Provide 3-5 gallery images per product', 'wpshadow' ),
					__( 'Use WebP format for 30% smaller file size', 'wpshadow' ),
					__( 'Show product from multiple angles', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Visit product page on desktop', 'wpshadow' ),
						__( 'Click to zoom on product image', 'wpshadow' ),
						__( 'Verify image remains sharp when zoomed', 'wpshadow' ),
						__( 'Check on high-DPI displays (Retina)', 'wpshadow' ),
					),
					'expected_result' => __( 'Product images ≥1000px width with zoom functionality', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Analyze product image dimensions.
	 *
	 * @since  1.6028.1830
	 * @return array Image analysis results.
	 */
	private static function analyze_product_images() {
		$result = array(
			'avg_width'        => 0,
			'products_checked' => 0,
			'below_1000px'     => 0,
			'sample_products'  => array(),
		);

		// Get sample products.
		$products = get_posts( array(
			'post_type'      => 'product',
			'posts_per_page' => 10,
			'post_status'    => 'publish',
			'orderby'        => 'rand',
		) );

		if ( empty( $products ) ) {
			return $result;
		}

		$total_width = 0;
		$count = 0;

		foreach ( $products as $product_post ) {
			$thumbnail_id = get_post_thumbnail_id( $product_post->ID );

			if ( ! $thumbnail_id ) {
				continue;
			}

			$image_data = wp_get_attachment_metadata( $thumbnail_id );

			if ( ! isset( $image_data['width'] ) ) {
				continue;
			}

			$width = (int) $image_data['width'];
			$total_width += $width;
			$count++;

			if ( $width < 1000 ) {
				$result['below_1000px']++;
			}

			// Track sample for reporting.
			if ( count( $result['sample_products'] ) < 3 ) {
				$result['sample_products'][] = array(
					'title' => get_the_title( $product_post->ID ),
					'width' => $width,
					'url'   => get_permalink( $product_post->ID ),
				);
			}
		}

		$result['products_checked'] = $count;
		$result['avg_width'] = $count > 0 ? round( $total_width / $count ) : 0;

		return $result;
	}
}
