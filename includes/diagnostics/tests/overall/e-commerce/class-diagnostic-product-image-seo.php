<?php
/**
 * Product Image SEO Diagnostic
 *
 * Analyzes product images for SEO optimization including alt text,
 * file naming, and image compression to improve search visibility.
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
 * Diagnostic_Product_Image_SEO Class
 *
 * Verifies product images are optimized for search engines.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Product_Image_SEO extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'product-image-seo';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Product Image SEO Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes product images for SEO optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if optimization issues found, null otherwise.
	 */
	public static function check() {
		// Check if WooCommerce is active
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return null; // Not an e-commerce site
		}

		$image_analysis = self::analyze_product_images();

		if ( $image_analysis['missing_alt_text'] === 0 && $image_analysis['unoptimized_count'] === 0 ) {
			return null; // All images are optimized
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Product images are not optimized for SEO. Missing alt text and uncompressed images reduce visibility in Google Images and impact page speed.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/product-image-seo',
			'family'       => self::$family,
			'meta'         => array(
				'missing_alt_text'     => $image_analysis['missing_alt_text'],
				'unoptimized_images'   => $image_analysis['unoptimized_count'],
				'total_product_images' => $image_analysis['total_count'],
				'seo_impact'           => __( 'Missing alt text = no Google Images traffic' ),
				'page_speed_impact'    => __( 'Uncompressed images slow page load by 30-50%' ),
			),
			'details'      => array(
				'alt_text_importance'  => array(
					__( 'Google Images uses alt text to understand image content' ),
					__( 'Alt text required for accessibility (screen readers)' ),
					__( 'Good alt text improves CTR from image search results' ),
					__( 'Example good alt: "Red wool winter coat with hood and pockets"' ),
					__( 'Example bad alt: "Image" or "Product photo"' ),
				),
				'image_naming'         => array(
					__( 'Avoid: product-image-123.jpg, IMG_0001.jpg' ),
					__( 'Good: red-wool-winter-coat-with-hood.jpg' ),
					__( 'Keyword-rich filename helps SEO' ),
					__( 'Use hyphens, not underscores' ),
				),
				'compression_targets'  => array(
					'JPG images' => 'Target 100-200KB per image (quality 80-85)',
					'PNG images' => 'Target 50-150KB (use for graphics only)',
					'WebP format' => 'Use for Chrome/modern browsers (-30% file size)',
				),
				'optimization_tools'   => array(
					'Free Tools' => array(
						'TinyPNG: Compress PNG/JPG (free 20/month)',
						'ImageOptim: Batch compression (Mac)',
						'FileOptimizer: Batch compression (Windows)',
					),
					'WordPress Plugins' => array(
						'Smush: Free image compression in WordPress (compress 50/month)',
						'Imagify: $10-50/month for unlimited compression',
						'ShortPixel: Compression + WebP conversion',
					),
				),
				'setup_steps'          => array(
					'Step 1' => __( 'Install Smush plugin for automatic compression' ),
					'Step 2' => __( 'Enable WebP format support' ),
					'Step 3' => __( 'Audit product images in Media Library' ),
					'Step 4' => __( 'Add descriptive alt text to all product images' ),
					'Step 5' => __( 'Rename image files to be keyword-rich' ),
					'Step 6' => __( 'Test Google Images to verify products appear' ),
				),
			),
		);
	}

	/**
	 * Analyze product images.
	 *
	 * @since  1.2601.2148
	 * @return array Image analysis results.
	 */
	private static function analyze_product_images() {
		global $wpdb;

		// Get product images with missing alt text
		$results = $wpdb->get_results(
			"SELECT pm.meta_value as image_id, p.ID as product_id
			FROM {$wpdb->posts} p
			JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE p.post_type = 'product'
			AND pm.meta_key IN ('_product_image_gallery', '_thumbnail_id')
			LIMIT 100"
		);

		$missing_alt     = 0;
		$unoptimized     = 0;
		$total           = 0;

		if ( $results ) {
			foreach ( $results as $result ) {
				$image_id = (int) $result->image_id;
				if ( $image_id > 0 ) {
					$total++;
					$alt_text = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
					if ( empty( $alt_text ) ) {
						$missing_alt++;
					}

					// Check if image is unoptimized
					$file = get_attached_file( $image_id );
					if ( $file ) {
						$filesize = filesize( $file );
						if ( $filesize > 300000 ) { // > 300KB
							$unoptimized++;
						}
					}
				}
			}
		}

		return array(
			'total_count'        => max( $total, 0 ),
			'missing_alt_text'   => max( $missing_alt, 0 ),
			'unoptimized_count'  => max( $unoptimized, 0 ),
		);
	}
}
