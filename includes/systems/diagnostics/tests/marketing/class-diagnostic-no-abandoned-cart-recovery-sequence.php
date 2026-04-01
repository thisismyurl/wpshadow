<?php
/**
 * Abandoned Cart Recovery Sequence Diagnostic
 *
 * Detects when abandoned cart recovery emails are not implemented
 * to recover lost sales and lost revenue.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Abandoned Cart Recovery Sequence
 *
 * Checks whether the site has implemented abandoned cart
 * recovery email campaigns.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Abandoned_Cart_Recovery_Sequence extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-abandoned-cart-recovery-sequence';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Abandoned Cart Recovery Sequence';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether abandoned cart recovery emails are configured';

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
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only applicable for WooCommerce
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		// Check for abandoned cart plugins
		$has_cart_recovery = is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ||
			is_plugin_active( 'cartflows/cartflows.php' ) ||
			is_plugin_active( 'abandoned-cart-lite-for-woocommerce/woocommerce-abandoned-cart.php' ) ||
			is_plugin_active( 'retriever-abandoned-cart-recovery/retriever.php' );

		// Check for custom implementation
		$has_custom_recovery = get_option( 'wpshadow_abandoned_cart_recovery' );

		if ( ! $has_cart_recovery && ! $has_custom_recovery ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Customers are adding items to their cart and leaving without completing the purchase. This is like having a store where 70% of people put items down and walk out. Abandoned cart recovery emails remind customers about their carts, often with a gentle nudge or discount code. Recovery emails achieve 40-45% open rates and 10-15% conversion rates—making it one of the highest-ROI email campaigns you can implement.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Recovered Revenue',
					'potential_gain' => '10-15% of abandoned cart value',
					'roi_explanation' => 'Abandoned cart recovery achieves 40-45% open rate and 10-15% conversion, recovering an average of 10-15% of lost revenue with minimal cost.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/abandoned-cart-recovery?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
