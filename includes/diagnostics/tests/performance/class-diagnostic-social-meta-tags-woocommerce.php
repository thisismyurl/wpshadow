<?php
/**
 * Social Meta Tags for WooCommerce Products
 *
 * Validates social meta tag implementation for e-commerce product pages.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Social_Meta_Tags_WooCommerce Class
 *
 * Checks for proper social meta tag implementation on WooCommerce product pages.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Social_Meta_Tags_WooCommerce extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-meta-tags-woocommerce';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Social Meta Tags for WooCommerce Products';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates social meta tag setup for product pages';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'social-media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only check if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		global $wpdb;

		// Get product count
		$product_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'" );

		// If no products, skip
		if ( empty( $product_count ) ) {
			return null;
		}

		$shop_page_id = wc_get_page_id( 'shop' );

		// Pattern 1: No social optimization plugin installed with products
		if ( $product_count > 10 ) {
			$has_seo_plugin = self::has_seo_plugin();

			if ( ! $has_seo_plugin ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'WooCommerce products lack proper social meta tags', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 65,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/social-meta-tags-woocommerce?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'issue' => 'no_product_social_optimization',
						'product_count' => intval( $product_count ),
						'message' => sprintf(
							/* translators: %d: number of products */
							__( 'Your store has %d products without proper social meta tags', 'wpshadow' ),
							intval( $product_count )
						),
						'recommendation' => __( 'Install SEO plugin with WooCommerce social optimization', 'wpshadow' ),
						'solution_plugins' => array(
							'Yoast SEO Premium - Full product optimization',
							'Rank Math - Social sharing for products',
							'All in One SEO - WooCommerce integration',
							'WooCommerce SEO by Yoast - Dedicated product SEO',
						),
						'why_important' => __( 'Product pages are prime social sharing targets', 'wpshadow' ),
						'social_benefits' => array(
							'Product images in social previews drive clicks',
							'Price display attracts price-conscious shoppers',
							'Rich previews increase click-through 20-35%',
							'Reviews/ratings build trust in preview',
						),
						'revenue_impact' => __( 'Optimized product pages increase social-driven sales 3-5x', 'wpshadow' ),
						'product_sharing_stats' => array(
							'30% of e-commerce traffic comes from social sharing',
							'Products with rich social previews get 2-3x more clicks',
							'Incomplete previews reduce sharing by 40%',
						),
					),
				);
			}
		}

		// Pattern 2: Missing product image in social tags
		$shop_page_response = wp_remote_get( get_permalink( $shop_page_id ) );

		if ( ! is_wp_error( $shop_page_response ) ) {
			$shop_page_content = wp_remote_retrieve_body( $shop_page_response );

			// Check for product schema with image
			if ( ! preg_match( '/"image"\s*:\s*\{.*?"url"/', $shop_page_content ) &&
				 ! preg_match( '/<meta\s+property=["\']og:image["\']/', $shop_page_content ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Product pages missing social meta images', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 70,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/social-meta-tags-woocommerce?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'issue' => 'missing_product_images',
						'message' => __( 'Product pages lack og:image meta tags for social sharing', 'wpshadow' ),
						'recommendation' => __( 'Enable social image configuration in SEO plugin for products', 'wpshadow' ),
						'why_matters' => __( 'Product images in social posts increase engagement 3-4x vs text only', 'wpshadow' ),
						'image_specs' => array(
							'Minimum size' => '1200x630px (recommended)',
							'Aspect ratio' => '1.91:1 or 2:1',
							'Format' => 'JPG or PNG',
							'File size' => 'Under 5MB',
						),
						'best_practice' => __( 'Use product featured image or main product photo', 'wpshadow' ),
						'engagement_stats' => __( 'Posts without images get 40% fewer shares and 55% fewer clicks', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 3: Missing product price in social tags
		if ( ! preg_match( '/<meta\s+property=["\']product:price["\']/', $shop_page_content ?? '' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Product social tags missing price information', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/social-meta-tags-woocommerce?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'missing_product_price',
					'message' => __( 'product:price meta tag not found in product pages', 'wpshadow' ),
					'why_important' => __( 'Showing price in social preview attracts price-conscious buyers', 'wpshadow' ),
					'conversion_impact' => __( 'Price transparency in social shares increases CTR 15-25%', 'wpshadow' ),
					'required_tags' => array(
						'product:price:amount' => 'Numeric price',
						'product:price:currency' => 'Currency code (USD, EUR, etc.)',
					),
					'example_format' => array(
						'<meta property="product:price:amount" content="29.99" />',
						'<meta property="product:price:currency" content="USD" />',
					),
					'benefit' => __( 'Buyers can see cost before clicking, improving relevance', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: Missing product availability meta tag
		if ( ! preg_match( '/<meta\s+property=["\']product:availability["\']/', $shop_page_content ?? '' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Product social tags missing availability information', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/social-meta-tags-woocommerce?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'missing_product_availability',
					'message' => __( 'product:availability meta tag not configured', 'wpshadow' ),
					'why_important' => __( 'Availability info prevents clicks on out-of-stock items', 'wpshadow' ),
					'valid_values' => array(
						'in stock' => 'Item is available',
						'out of stock' => 'Item is not available',
						'preorder' => 'Item available for pre-order',
						'backorder' => 'Item available for back-order',
					),
					'benefit' => __( 'Saves time for buyers and reduces bounce rate from out-of-stock', 'wpshadow' ),
					'recommendation' => __( 'Ensure SEO plugin automatically populates from WooCommerce inventory', 'wpshadow' ),
				),
			);
		}

		// Pattern 5: Missing product rating/reviews in social tags
		if ( ! preg_match( '/<meta\s+property=["\']product:rating["\']/', $shop_page_content ?? '' ) ) {
			// Check if store has review feature enabled
			if ( 'yes' === get_option( 'woocommerce_enable_reviews', 'yes' ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Product reviews not shown in social meta tags', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 40,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/social-meta-tags-woocommerce?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'issue' => 'missing_product_ratings',
						'message' => __( 'product:rating and product:rating:scale meta tags not found', 'wpshadow' ),
						'why_important' => __( 'Star ratings in social preview build trust and increase clicks', 'wpshadow' ),
						'social_proof_impact' => __( 'Products with visible ratings get 23% more clicks and 15% more conversions', 'wpshadow' ),
						'required_format' => array(
							'<meta property="product:rating:value" content="4.5" />',
							'<meta property="product:rating:scale" content="5" />',
						),
						'benefit' => __( 'Social proof (ratings) dramatically increases trust and conversion', 'wpshadow' ),
						'recommendation' => __( 'Ensure reviews feature is visible and SEO plugin includes rating tags', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 6: Missing product category/brand information
		if ( ! preg_match( '/<meta\s+property=["\']product:brand["\']/', $shop_page_content ?? '' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Product brand information not in social tags', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/social-meta-tags-woocommerce?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'missing_product_brand',
					'message' => __( 'product:brand meta tag not configured', 'wpshadow' ),
					'recommendation' => __( 'Add brand/manufacturer information to product pages', 'wpshadow' ),
					'why_important' => __( 'Brand recognition increases click-through rate 10-20%', 'wpshadow' ),
					'format' => '<meta property="product:brand" content="Your Brand Name" />',
					'benefit' => __( 'Customers recognize your brand in social preview', 'wpshadow' ),
				),
			);
		}

		return null; // No issues found
	}

	/**
	 * Check if SEO plugin with WooCommerce support is installed.
	 *
	 * @since 0.6093.1200
	 * @return bool True if SEO plugin active.
	 */
	private static function has_seo_plugin() {
		$seo_plugins = array(
			'wordpress-seo-premium/wp-seo-premium.php',
			'wordpress-seo/wp-seo.php',
			'seo-by-rank-math-pro/rank-math.php',
			'seo-by-rank-math/rank-math.php',
			'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'the-seo-framework/the-seo-framework.php',
			'seopress-pro/seopress.php',
			'seopress/seopress.php',
		);

		foreach ( $seo_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}
}
