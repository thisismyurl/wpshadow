<?php
/**
 * Product Bundle and Package Pricing Strategy Diagnostic
 *
 * Detects when product bundles or package pricing strategies
 * are not implemented to increase average order value.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Product Bundle or Package Pricing Strategy
 *
 * Checks whether the site implements product bundles or
 * tiered pricing packages to increase average order value.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Product_Bundle_Or_Package_Pricing_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-product-bundle-pricing-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Product Bundle & Package Pricing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether product bundles or tiered pricing packages are implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for WooCommerce
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null; // Not applicable
		}

		// Check for bundle plugins
		$has_bundle_plugin = is_plugin_active( 'woocommerce-product-bundles/woocommerce-product-bundles.php' ) ||
			is_plugin_active( 'composite-products-for-woocommerce/composite-products.php' ) ||
			is_plugin_active( 'product-bundles-for-woocommerce/product-bundles.php' );

		// Check for variable/tiered pricing
		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'   => '_product_attributes',
					'compare' => 'EXISTS',
				),
			),
		);
		$variable_products = get_posts( $args );

		if ( ! $has_bundle_plugin && empty( $variable_products ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not using product bundles or tiered pricing yet. This is like only selling individual ingredients when you could sell complete meal packages. Bundles increase average order value by 20-30%, make purchasing easier for customers, and help you clear inventory. Try offering a "Starter Bundle" plus "Professional Bundle" at different price points.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Average Order Value',
					'potential_gain' => '+20-30% AOV',
					'roi_explanation' => 'Product bundles increase AOV while making purchasing simpler for customers and improving inventory turnover.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/product-bundle-pricing-strategy',
			);
		}

		return null;
	}
}
