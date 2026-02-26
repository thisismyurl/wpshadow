<?php
/**
 * Product Photography Standards Diagnostic
 *
 * Tests whether the site maintains professional, high-quality, and consistent product
 * photography standards. Quality images are the #1 factor in online purchase decisions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5003.1145
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Maintains_Photo_Standards Class
 *
 * Diagnostic #1: Product Photography Standards from Specialized & Emerging Success Habits.
 * Checks if the site maintains professional product photography standards.
 *
 * @since 1.5003.1145
 */
class Diagnostic_Maintains_Photo_Standards extends Diagnostic_Base {

	protected static $slug        = 'maintains-photo-standards';
	protected static $title       = 'Product Photography Standards';
	protected static $description = 'Tests whether the site maintains professional product photography standards';
	protected static $family      = 'ecommerce-optimization';

	public static function check() {
		$score           = 0;
		$max_score       = 5;
		$score_details   = array();
		$recommendations = array();

		// Check WooCommerce active.
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return null;
		}

		// Check product images exist.
		$products_with_images = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 50,
				'post_status'    => 'publish',
				'meta_query'     => array(
					array(
						'key'     => '_thumbnail_id',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		$total_products  = wp_count_posts( 'product' );
		$published_count = isset( $total_products->publish ) ? $total_products->publish : 0;

		if ( $published_count > 0 ) {
			$image_percentage = ( count( $products_with_images ) / $published_count ) * 100;

			if ( $image_percentage >= 90 ) {
				++$score;
				$score_details[] = __( '✓ 90%+ of products have featured images', 'wpshadow' );
			} elseif ( $image_percentage >= 50 ) {
				$score_details[]   = sprintf(
					/* translators: %d: percentage of products with featured images */
					__( '◐ %d%% of products have images', 'wpshadow' ),
					round( $image_percentage )
				);
				$recommendations[] = __( 'Add featured images to all products', 'wpshadow' );
			} else {
				$score_details[]   = __( '✗ Many products missing images', 'wpshadow' );
				$recommendations[] = __( 'Products without images convert 85% worse - prioritize adding images', 'wpshadow' );
			}
		}

		// Check product gallery images (multiple angles).
		$products_with_galleries = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 50,
				'post_status'    => 'publish',
				'meta_query'     => array(
					array(
						'key'     => '_product_image_gallery',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		if ( count( $products_with_galleries ) >= 10 ) {
			++$score;
			$score_details[] = sprintf(
				/* translators: %d: number of products */
				__( '✓ %d+ products with multiple images', 'wpshadow' ),
				count( $products_with_galleries )
			);
		} elseif ( ! empty( $products_with_galleries ) ) {
			$score_details[]   = sprintf(
				/* translators: %d: number of products with image galleries */
				__( '◐ %d product(s) with galleries', 'wpshadow' ),
				count( $products_with_galleries )
			);
			$recommendations[] = __( 'Add 3-5 images per product showing multiple angles', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ Single images only', 'wpshadow' );
			$recommendations[] = __( 'Multiple images increase conversions by 58% - show all angles', 'wpshadow' );
		}

		// Check recent high-res images.
		$recent_images = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => 20,
				'post_status'    => 'inherit',
				'date_query'     => array(
					array(
						'after' => '6 months ago',
					),
				),
			)
		);

		if ( ! empty( $recent_images ) ) {
			$large_images = 0;
			foreach ( $recent_images as $image ) {
				$metadata = wp_get_attachment_metadata( $image->ID );
				if ( isset( $metadata['width'] ) && $metadata['width'] >= 1000 ) {
					++$large_images;
				}
			}

			if ( $large_images >= 10 ) {
				++$score;
				$score_details[] = __( '✓ Recent high-resolution images (1000px+)', 'wpshadow' );
			} else {
				$score_details[]   = __( '◐ Some recent images are lower resolution', 'wpshadow' );
				$recommendations[] = __( 'Use minimum 1000px width for product images (zoom functionality)', 'wpshadow' );
			}
		} else {
			$score_details[]   = __( '✗ No recent high-res images uploaded', 'wpshadow' );
			$recommendations[] = __( 'Invest in professional photography with minimum 1000px dimensions', 'wpshadow' );
		}

		// Check lifestyle/context images.
		$lifestyle_content = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				's'              => 'lifestyle use action context',
			)
		);

		if ( ! empty( $lifestyle_content ) ) {
			++$score;
			$score_details[] = __( '✓ Lifestyle/context imagery detected', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No lifestyle photos', 'wpshadow' );
			$recommendations[] = __( 'Include lifestyle shots showing products in use (convert 40% better)', 'wpshadow' );
		}

		// Check consistent backgrounds/styling.
		$style_guide_content = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'photo style guide brand',
			)
		);

		if ( ! empty( $style_guide_content ) ) {
			++$score;
			$score_details[] = __( '✓ Photography style guide documentation', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No photo standards documented', 'wpshadow' );
			$recommendations[] = __( 'Create a photo style guide (background color, lighting, angles) for consistency', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 30;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 20;
		} else {
			return null;
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %d: score percentage */
				__( 'Product photography score: %d%%. Professional product images are the #1 factor influencing online purchase decisions (93%% of consumers). High-quality photos increase conversions by 30-40%%, multiple angles by 58%%. Consistent styling builds brand trust and perceived value.', 'wpshadow' ),
				$score_percentage
			),
			'severity'        => $severity,
			'threat_level'    => $threat_level,
			'auto_fixable'    => false,
			'kb_link'         => 'https://wpshadow.com/kb/product-photography',
			'details'         => $score_details,
			'recommendations' => $recommendations,
			'impact'          => __( 'Professional, consistent photography communicates quality, builds trust, and enables customers to make confident purchase decisions.', 'wpshadow' ),
		);
	}
}
