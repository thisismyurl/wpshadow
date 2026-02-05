<?php
/**
 * Cross-Sell Strategy Diagnostic
 *
 * Detects when sites aren't using cross-sell tactics to increase order value.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\CustomerRetention
 * @since      1.6035.2314
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\CustomerRetention;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cross-Sell Strategy Diagnostic Class
 *
 * Checks if the site uses cross-sell and upsell strategies effectively.
 *
 * @since 1.6035.2314
 */
class Diagnostic_Cross_Sell_Strategy extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cross-sell-strategy';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Cross-Sell or Upsell Strategy';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when sites miss opportunities to increase order value';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'customer-retention';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6035.2314
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		// Check for WooCommerce or EDD.
		$ecommerce_active = is_plugin_active( 'woocommerce/woocommerce.php' ) ||
			is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' );

		if ( ! $ecommerce_active ) {
			return null; // Not applicable without e-commerce.
		}

		// Check for upsell/cross-sell plugins.
		$upsell_plugins = array(
			'woocommerce-one-click-upsell-funnel/woocommerce-one-click-upsell-funnel.php' => 'One Click Upsell',
			'cartflows/cartflows.php'                => 'CartFlows (Sales Funnels)',
			'woocommerce-product-recommendations/woocommerce-product-recommendations.php' => 'Product Recommendations',
			'beeketing-for-woocommerce/beeketing-for-woocommerce.php' => 'Beeketing (Cross-sells)',
		);

		$active_upsell = array();
		foreach ( $upsell_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_upsell[] = $name;
			}
		}

		if ( ! empty( $active_upsell ) ) {
			return null; // Upsell/cross-sell strategy active.
		}

		// Check if WooCommerce cross-sells are configured.
		if ( function_exists( 'wc_get_products' ) ) {
			$products = wc_get_products( array( 'limit' => 10 ) );
			$has_cross_sells = false;

			foreach ( $products as $product ) {
				if ( ! empty( $product->get_cross_sell_ids() ) ) {
					$has_cross_sells = true;
					break;
				}
			}

			if ( $has_cross_sells ) {
				return null; // Cross-sells configured manually.
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'You\'re leaving money on the table. Cross-selling and upselling can increase your average order value by 10-30% with minimal effort', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/cross-sell-upsell',
			'context'      => array(
				'ecommerce_platform' => $ecommerce_active ? 'Active' : 'None',
				'has_strategy'       => false,
				'impact'             => __( 'Amazon generates 35% of revenue from cross-sells and upsells. Existing customers are 50% more likely to buy related products than new visitors.', 'wpshadow' ),
				'recommendation'     => array(
					__( 'Add "Frequently Bought Together" suggestions', 'wpshadow' ),
					__( 'Show related products on product pages', 'wpshadow' ),
					__( 'Offer bundle discounts (buy X get Y% off)', 'wpshadow' ),
					__( 'Use cart abandonment emails with cross-sell suggestions', 'wpshadow' ),
					__( 'Create product bundles and packages', 'wpshadow' ),
					__( 'Add "Customers Also Bought" sections', 'wpshadow' ),
					__( 'Offer one-click upsells at checkout', 'wpshadow' ),
					__( 'Suggest complementary products (camera + lens, etc.)', 'wpshadow' ),
				),
				'revenue_increase'   => __( 'Proper cross-selling increases average order value by 10-30%', 'wpshadow' ),
				'customer_value'     => __( 'Selling to existing customers costs 5x less than acquiring new ones', 'wpshadow' ),
				'timing'             => __( 'Best cross-sell moments: product page, cart, post-purchase email', 'wpshadow' ),
			),
		);
	}
}
