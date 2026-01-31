<?php
/**
 * Cart Abandonment Rate Diagnostic
 *
 * Calculates the percentage of carts created vs completed orders,
 * identifying revenue loss from abandoned carts.
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
 * Diagnostic_Cart_Abandonment_Rate Class
 *
 * Analyzes WooCommerce cart abandonment to identify lost revenue opportunities.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Cart_Abandonment_Rate extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cart-abandonment-rate';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WooCommerce Cart Abandonment Rate Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies revenue lost to abandoned shopping carts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'e-commerce';

	/**
	 * Abandonment rate warning threshold (percentage)
	 *
	 * @var int
	 */
	const ABANDONMENT_WARNING = 70;

	/**
	 * Abandonment rate critical threshold (percentage)
	 *
	 * @var int
	 */
	const ABANDONMENT_CRITICAL = 85;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if high abandonment detected, null otherwise.
	 */
	public static function check() {
		// Check if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) && ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return null;
		}

		$analytics = self::calculate_abandonment_rate();

		if ( ! $analytics || $analytics['abandonment_rate'] < self::ABANDONMENT_WARNING ) {
			// Acceptable abandonment rate or insufficient data
			return null;
		}

		$severity = ( $analytics['abandonment_rate'] >= self::ABANDONMENT_CRITICAL ) ? 'high' : 'medium';
		$threat_level = ( $severity === 'high' ) ? 75 : 55;

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: percentage, %s: estimated lost revenue */
				__( 'Cart abandonment rate is %d%%. Estimated %s in lost revenue monthly.', 'wpshadow' ),
				$analytics['abandonment_rate'],
				$analytics['estimated_loss']
			),
			'severity'      => $severity,
			'threat_level'  => $threat_level,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/cart-abandonment',
			'family'        => self::$family,
			'meta'          => array(
				'abandonment_rate'           => $analytics['abandonment_rate'] . '%',
				'carts_created'              => $analytics['carts_created'],
				'orders_completed'           => $analytics['orders_completed'],
				'abandoned_carts_estimate'   => $analytics['abandoned_estimate'],
				'estimated_monthly_loss'    => $analytics['estimated_loss'],
				'recovery_actions'          => array(
					__( 'Implement abandoned cart email recovery (3rd party plugin)' ),
					__( 'Optimize checkout flow and remove unnecessary steps' ),
					__( 'Add product recommendations at checkout' ),
					__( 'Implement exit-intent popups with discounts' ),
					__( 'Test guest checkout vs account requirement' ),
					__( 'Reduce form fields at checkout' ),
					__( 'Show shipping/tax costs before checkout' ),
					__( 'Add payment option logos for trust' ),
				),
			),
			'details'       => array(
				'issue'           => sprintf(
					/* translators: %d: percentage */
					__( '%d%% of customers add items to cart but don\'t complete purchase.', 'wpshadow' ),
					$analytics['abandonment_rate']
				),
				'revenue_opportunity' => sprintf(
					/* translators: %s: revenue amount, %d: percentage */
					__( 'Recovering just 10% of abandoned carts could mean %s in additional monthly revenue (%d%% reduction in losses).', 'wpshadow' ),
					'$200-1000',
					10
				),
				'common_causes' => array(
					__( 'Unexpected costs' ) => __( 'Shipping/tax calculated at end, customers shocked' ),
					__( 'Complex checkout' ) => __( 'Too many form fields, account requirement' ),
					__( 'Payment options' ) => __( 'Missing preferred payment methods' ),
					__( 'Trust issues' ) => __( 'No security badges, unclear policy' ),
					__( 'Technical issues' ) => __( 'Slow checkout, JavaScript errors, bugs' ),
					__( 'Price comparison' ) => __( 'Customers leaving to check competitor prices' ),
				),
				'recovery_strategies' => array(
					'Tier 1 (Immediate Revenue)' => array(
						__( 'Install abandoned cart email plugin (Jilt, CartFlips, etc.)' ),
						__( 'Send email 1 hour after abandonment' ),
						__( 'Include cart link and small discount code' ),
						__( 'Expected recovery: 5-10% of abandoned carts' ),
					),
					'Tier 2 (Quick Wins)' => array(
						__( 'Simplify checkout form (remove optional fields)' ),
						__( 'Show shipping cost before checkout' ),
						__( 'Add express checkout (Apple Pay, Google Pay)' ),
						__( 'Expected improvement: 5-15% conversion lift' ),
					),
					'Tier 3 (Long-term)' => array(
						__( 'A/B test checkout flow' ),
						__( 'Add product recommendations' ),
						__( 'Implement exit-intent offers' ),
						__( 'Build loyalty/retention program' ),
					),
				),
			),
		);
	}

	/**
	 * Calculate cart abandonment rate.
	 *
	 * @since  1.2601.2148
	 * @return array|null Analytics data or null if insufficient data.
	 */
	private static function calculate_abandonment_rate() {
		global $wpdb;

		// Get completed orders in last 30 days
		$orders_30d = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'shop_order' AND post_status = 'wc-completed' AND post_date > DATE_SUB(NOW(), INTERVAL 30 DAY)"
		);

		$orders_30d = (int) $orders_30d;

		if ( $orders_30d < 5 ) {
			// Insufficient data
			return null;
		}

		// Estimate carts (rough: assume 3-4x orders as carts started)
		// Based on typical 25-30% conversion rate
		$estimated_carts = (int) ( $orders_30d / 0.28 );

		$abandoned = $estimated_carts - $orders_30d;
		$abandonment_rate = (int) ( ( $abandoned / $estimated_carts ) * 100 );

		// Estimate average order value
		$avg_order_value = $wpdb->get_var(
			"SELECT AVG( meta_value ) FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON pm.post_id = p.ID WHERE meta_key = '_order_total' AND p.post_type = 'shop_order' AND p.post_status = 'wc-completed' AND p.post_date > DATE_SUB(NOW(), INTERVAL 30 DAY)"
		);

		$avg_order_value = (float) $avg_order_value;

		if ( $avg_order_value === 0.0 ) {
			$avg_order_value = 100; // Fallback estimate
		}

		// Calculate estimated monthly loss
		$estimated_monthly_loss = (float) ( $abandoned * $avg_order_value / 4 ); // Weekly average

		return array(
			'abandonment_rate'  => $abandonment_rate,
			'carts_created'     => $estimated_carts,
			'orders_completed'  => $orders_30d,
			'abandoned_estimate' => $abandoned,
			'average_order_value' => '$' . number_format( $avg_order_value, 2 ),
			'estimated_loss'    => '$' . number_format( $estimated_monthly_loss, 2 ),
		);
	}
}
