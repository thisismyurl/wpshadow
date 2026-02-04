<?php
/**
 * No Cross-Sell Strategy Between Product Categories Diagnostic
 *
 * Checks if cross-selling between product categories is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\BusinessPerformance
 * @since      1.6035.2100
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cross-Sell Strategy Diagnostic
 *
 * Detects when business isn't leveraging cross-selling opportunities.
 * Cross-selling (selling different products to existing customers) increases
 * revenue per customer by 20-30% with minimal CAC. Without strategy, you're
 * leaving 20-30% revenue on the table.
 *
 * @since 1.6035.2100
 */
class Diagnostic_No_Cross_Sell_Strategy_Between_Product_Categories extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-cross-sell-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cross-Sell Strategy Between Product Categories';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if cross-selling between product categories is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6035.2100
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$has_cross_sell = self::check_cross_sell_strategy();

		if ( ! $has_cross_sell ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No cross-selling strategy detected. Customers buy one product and you don\'t suggest related products. Cross-selling increases revenue per customer 20-30% with zero new customer acquisition cost. Implement: 1) Product recommendations (related items), 2) Bundle deals (discounted combos), 3) Complementary products (email after purchase), 4) Upgrade upsells. Map customer journey and suggest at each stage.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cross-sell-strategy',
				'details'     => array(
					'cross_sell_active'     => false,
					'cross_sell_types'      => self::get_cross_sell_types(),
					'business_impact'       => '20-30% revenue increase per customer, zero new CAC',
					'recommendation'        => __( 'Create cross-sell recommendations for each product category', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if cross-sell strategy exists
	 *
	 * @since  1.6035.2100
	 * @return bool True if cross-sell detected
	 */
	private static function check_cross_sell_strategy(): bool {
		// Check for WooCommerce cross-sell features
		if ( class_exists( 'WooCommerce' ) ) {
			$products = wc_get_products( array( 'limit' => 5 ) );

			if ( ! empty( $products ) ) {
				foreach ( $products as $product ) {
					$cross_sells = $product->get_cross_sell_ids();

					if ( ! empty( $cross_sells ) ) {
						return true;
					}
				}
			}
		}

		// Check for recommendations plugins
		$plugins = get_plugins();

		$recommendation_keywords = array( 'recommendation', 'cross-sell', 'upsell', 'bundle', 'related products' );

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $recommendation_keywords as $keyword ) {
				if ( strpos( $plugin_name, $keyword ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get cross-sell types
	 *
	 * @since  1.6035.2100
	 * @return array Array of cross-sell types
	 */
	private static function get_cross_sell_types(): array {
		return array(
			array(
				'type'        => 'Related Products',
				'placement'   => 'Product page / Post-purchase',
				'example'     => 'Customer buys hammer → see related nails, tool belt',
				'conversion'  => '5-15% of viewers click',
				'effort'      => 'Low (tag products)',
			),
			array(
				'type'        => 'Bundle Deals',
				'placement'   => 'Checkout / Email campaign',
				'example'     => '"Complete Toolkit Bundle: Save 20%" (hammer + nails + belt)',
				'conversion'  => '10-25% add bundle to cart',
				'effort'      => 'Medium (create bundle offers)',
			),
			array(
				'type'        => 'Post-Purchase Upsell',
				'placement'   => 'Thank you email / Post-purchase page',
				'example'     => 'After buying hammer: "Complete your toolkit - 20% off nail set"',
				'conversion'  => '5-10% of new customers',
				'effort'      => 'Low (email automation)',
			),
			array(
				'type'        => 'Tiered Upsells',
				'placement'   => 'Checkout / On product page',
				'example'     => 'Choose: Basic Hammer ($20) → Pro Hammer ($45) → Industrial ($120)',
				'conversion'  => '10-20% upgrade to higher tier',
				'effort'      => 'Medium (product setup)',
			),
			array(
				'type'        => 'Account/Membership Upgrade',
				'placement'   => 'Dashboard / Email',
				'example'     => 'Free user → "Upgrade to Pro for advanced features"',
				'conversion'  => '2-5% upgrade',
				'effort'      => 'Medium (feature gating)',
			),
		);
	}
}
