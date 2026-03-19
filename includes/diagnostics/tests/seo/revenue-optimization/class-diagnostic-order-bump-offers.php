<?php
/**
 * Order Bump Offers Diagnostic
 *
 * Checks whether order bump or complementary offers exist at checkout.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\RevenueOptimization
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order Bump Offers Diagnostic Class
 *
 * Verifies that checkout upsell tools are present.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Order_Bump_Offers extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'order-bump-offers';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Order Bump or Complementary Product Offers';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if order bump or complementary offers exist';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'revenue-optimization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$order_bump_plugins = array(
			'woocommerce-order-bump/woocommerce-order-bump.php' => 'WooCommerce Order Bump',
			'cartflows/cartflows.php' => 'CartFlows',
			'woocommerce-one-click-upsell/woocommerce-one-click-upsell.php' => 'One Click Upsell',
			'checkout-upsell-funnel/checkout-upsell-funnel.php' => 'Checkout Upsell Funnel',
		);

		$active_bumps = array();
		foreach ( $order_bump_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_bumps[] = $plugin_name;
			}
		}

		$stats['order_bump_tools'] = ! empty( $active_bumps ) ? implode( ', ', $active_bumps ) : 'none';

		if ( empty( $active_bumps ) ) {
			$issues[] = __( 'No order bump or checkout upsell tools detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Order bumps let customers add a helpful extra without extra steps. When done well, it improves convenience and increases order value.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/order-bump-offers',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
