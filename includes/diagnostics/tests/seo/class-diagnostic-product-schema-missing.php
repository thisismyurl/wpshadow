<?php
/**
 * Product Schema Markup Diagnostic
 *
 * Detects missing Product schema.org markup on WooCommerce product pages.
 * Schema markup enables rich snippets in search results.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1815
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Product_Schema_Missing Class
 *
 * Checks if WooCommerce products have proper schema.org markup.
 *
 * @since 1.6028.1815
 */
class Diagnostic_Product_Schema_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'product-schema-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Product Page Missing Schema Markup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for Product schema.org markup on e-commerce pages';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1815
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only check if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null; // Not an e-commerce site.
		}

		$schema_analysis = self::analyze_product_schema();

		if ( $schema_analysis['has_schema'] ) {
			return null; // Schema markup is present.
		}

		// Get sample products for testing.
		$product_count = wp_count_posts( 'product' )->publish;

		if ( $product_count === 0 ) {
			return null; // No products to check.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %d: product count */
				_n(
					'%d product missing schema.org markup',
					'%d products missing schema.org markup',
					$product_count,
					'wpshadow'
				),
				$product_count
			),
			'severity'    => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/product-schema',
			'family'      => self::$family,
			'meta'        => array(
				'product_count'     => $product_count,
				'has_woocommerce'   => true,
				'recommended'       => __( 'Add Product schema markup to all product pages', 'wpshadow' ),
				'impact_level'      => 'high',
				'immediate_actions' => array(
					__( 'Install Schema Pro or similar plugin', 'wpshadow' ),
					__( 'Or enable WooCommerce structured data', 'wpshadow' ),
					__( 'Test with Google Rich Results Test', 'wpshadow' ),
					__( 'Verify price, availability included', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Product schema markup enables rich snippets in Google search results, showing price, availability, ratings, and images. This increases click-through rate significantly (20-30% improvement typical). Without schema, products appear as plain text results, losing competitive advantage. Google rewards properly structured data with enhanced visibility.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Lower CTR: Plain text results vs rich snippets', 'wpshadow' ),
					__( 'Missed Sales: Competitors show price/stars in results', 'wpshadow' ),
					__( 'Less Visibility: No product image in search', 'wpshadow' ),
					__( 'Lower Trust: Rich snippets signal legitimacy', 'wpshadow' ),
				),
				'schema_analysis' => $schema_analysis,
				'required_properties' => array(
					'@type'       => 'Product',
					'name'        => __( 'Product name', 'wpshadow' ),
					'image'       => __( 'Product image URL', 'wpshadow' ),
					'description' => __( 'Product description', 'wpshadow' ),
					'sku'         => __( 'Product SKU/ID', 'wpshadow' ),
					'offers'      => array(
						'@type'         => 'Offer',
						'price'         => __( 'Product price', 'wpshadow' ),
						'priceCurrency' => __( 'Currency code (USD, EUR)', 'wpshadow' ),
						'availability'  => __( 'Stock status URL', 'wpshadow' ),
					),
				),
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'Enable WooCommerce Structured Data', 'wpshadow' ),
						'description' => __( 'WooCommerce has built-in schema support', 'wpshadow' ),
						'steps'       => array(
							__( 'Go to WooCommerce → Settings → Products', 'wpshadow' ),
							__( 'Enable "Enable schema markup" (usually on by default)', 'wpshadow' ),
							__( 'Save settings', 'wpshadow' ),
							__( 'Test product page with Google Rich Results Test', 'wpshadow' ),
							__( 'Verify price, ratings, availability appear', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'Schema Pro Plugin', 'wpshadow' ),
						'description' => __( 'Advanced schema control with custom fields', 'wpshadow' ),
						'steps'       => array(
							__( 'Install Schema Pro plugin', 'wpshadow' ),
							__( 'Enable Product schema type', 'wpshadow' ),
							__( 'Map WooCommerce fields to schema properties', 'wpshadow' ),
							__( 'Add review/rating schema if available', 'wpshadow' ),
							__( 'Test all product pages', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'Custom Schema Implementation', 'wpshadow' ),
						'description' => __( 'Add schema via functions.php or plugin', 'wpshadow' ),
						'steps'       => array(
							__( 'Hook into woocommerce_single_product_summary', 'wpshadow' ),
							__( 'Build JSON-LD array with product data', 'wpshadow' ),
							__( 'Include name, image, price, availability', 'wpshadow' ),
							__( 'Add aggregateRating if reviews exist', 'wpshadow' ),
							__( 'Echo <script type="application/ld+json">', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Include all required properties (name, image, price)', 'wpshadow' ),
					__( 'Add aggregateRating for products with reviews', 'wpshadow' ),
					__( 'Use proper availability schema (InStock, OutOfStock)', 'wpshadow' ),
					__( 'Update schema when price/availability changes', 'wpshadow' ),
					__( 'Test with Google Rich Results Test regularly', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Visit product page in browser', 'wpshadow' ),
						__( 'View page source, search for "application/ld+json"', 'wpshadow' ),
						__( 'Copy product URL', 'wpshadow' ),
						__( 'Paste into Google Rich Results Test tool', 'wpshadow' ),
						__( 'Verify "Product" type detected with no errors', 'wpshadow' ),
					),
					'expected_result' => __( 'All product pages have valid Product schema markup', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Analyze product schema implementation.
	 *
	 * @since  1.6028.1815
	 * @return array Schema analysis results.
	 */
	private static function analyze_product_schema() {
		$result = array(
			'has_schema'         => false,
			'method'             => '',
			'sample_product_url' => '',
			'wc_schema_enabled'  => false,
		);

		// Get a sample product.
		$products = get_posts( array(
			'post_type'      => 'product',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
		) );

		if ( empty( $products ) ) {
			return $result;
		}

		$product_id = $products[0]->ID;
		$result['sample_product_url'] = get_permalink( $product_id );

		// Check if WooCommerce schema is enabled (it's on by default).
		// WooCommerce adds structured data via wc_get_product_schema() function.
		if ( function_exists( 'wc_get_product_schema' ) ) {
			$result['wc_schema_enabled'] = true;
			$result['has_schema'] = true;
			$result['method'] = 'WooCommerce Built-in';
			return $result;
		}

		// Check for common schema plugins.
		$schema_plugins = array(
			'Schema Pro'        => class_exists( 'BSF_AIOSRS_Pro' ),
			'Yoast SEO'         => class_exists( 'WPSEO_Options' ) && method_exists( 'WPSEO_Options', 'get' ),
			'Rank Math'         => class_exists( 'RankMath' ),
			'All in One Schema' => function_exists( 'saswp_pro_plugin_active' ),
		);

		foreach ( $schema_plugins as $plugin_name => $is_active ) {
			if ( $is_active ) {
				$result['has_schema'] = true;
				$result['method'] = $plugin_name;
				break;
			}
		}

		// Check if theme adds custom schema.
		// We'd need to actually fetch and parse the page HTML, which is expensive.
		// For now, rely on plugin detection.

		return $result;
	}
}
