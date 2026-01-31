<?php
/**
 * Abandoned Cart Recovery Diagnostic
 *
 * Detects lack of abandoned cart recovery system,
 * leaving money on the table (28% of carts abandoned).
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
 * Diagnostic_Abandoned_Cart_Recovery Class
 *
 * Verifies abandoned cart recovery system.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Abandoned_Cart_Recovery extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'abandoned-cart-recovery';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Abandoned Cart Recovery';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies abandoned cart recovery';

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
	 * @return array|null Finding array if no recovery system, null otherwise.
	 */
	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null; // Not e-commerce site
		}

		$recovery_status = self::check_cart_recovery();

		if ( $recovery_status['is_configured'] ) {
			return null; // Cart recovery enabled
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Abandoned cart recovery not configured. $100 average cart × 28% abandonment = losing $28 per 100 visitors. Recovery emails recover 5-15% of abandoned carts = $1-4 per visitor.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/abandoned-cart',
			'family'       => self::$family,
			'meta'         => array(
				'woocommerce_detected' => true,
			),
			'details'      => array(
				'abandoned_cart_statistics'   => array(
					__( '28% of online carts abandoned (Statista)' ),
					__( '$260 billion in abandoned carts annually' ),
					__( '5-15% recovery rate with email (Klaviyo)' ),
					__( '$100 average cart value (industry avg)' ),
					__( 'ROI: $9 per $1 spent on recovery (SaleCycle)' ),
				),
				'why_carts_are_abandoned'     => array(
					'Unexpected Shipping Cost (48%)' => __( 'Show shipping early in cart' ),
					'Forced Account Creation (24%)' => __( 'Guest checkout option required' ),
					'Complex Checkout (21%)' => __( 'Multi-step simplified' ),
					'Limited Payment Methods (6%)' => __( 'Offer credit cards, PayPal, Apple Pay' ),
					'Site Performance Issues (25%)' => __( 'Slow site = abandoned' ),
				),
				'abandoned_cart_recovery_tools' => array(
					'Klaviyo' => array(
						'Free: Up to $300/month revenue',
						'Paid: $20+/month',
						'Features: Auto recovery, SMS, segmentation',
						'Best for: High-value stores',
					),
					'Baidu Hive' => array(
						'Free: Up to 1000 contacts',
						'Paid: $12+/month',
						'WooCommerce native',
					),
					'WooCommerce Recover Cart' => array(
						'Plugin: Abandoned Cart Recovery Pro',
						'Price: $99+',
						'Features: Email series, discount codes',
					),
					'Omnisend' => array(
						'Free: Up to 500 contacts',
						'Paid: $15+/month',
						'Features: Email, SMS, chat, popups',
					),
				),
				'recovery_email_sequence'      => array(
					'Email 1: 1 hour after abandon' => array(
						'Subject: "Your $99.99 order is waiting"',
						'Content: Show cart items, total price',
						'CTA: "Complete Your Order" button',
						'Rate: 30-40% open rate expected',
					),
					'Email 2: 24 hours after' => array(
						'Subject: Different angle: "Missing something?"',
						'Content: Customer testimonials, product reviews',
						'CTA: "Finish Shopping"',
						'Rate: 10-20% open rate',
					),
					'Email 3: 3 days after' => array(
						'Subject: "Quick question: What stopped you?"',
						'Content: "We noticed you left something"',
						'Offer: 10% discount code',
						'CTA: "Claim Your Discount"',
						'Rate: 5-15% recovery',
					),
				),
				'best_practices'               => array(
					'Show Shipping Early' => array(
						'Calculator: Let customer choose shipping',
						'Message: "Shipping to USA = $10"',
						'Before checkout: Prevents shock',
					),
					'Guest Checkout' => array(
						'NEVER force account creation',
						'Option: "Buy as guest" prominent',
						'Optional account: Email signup',
					),
					'Multiple Payment Methods' => array(
						'Credit cards (Visa, MC, Amex)',
						'Digital wallets (Apple Pay, Google Pay)',
						'PayPal',
						'Buy Now Pay Later (Klarna, Affirm)',
					),
					'Exit Intent' => array(
						'Popup: When mouse moves to close',
						'Offer: "Wait! 10% off"',
						'Captures: Last-minute browsers',
					),
				),
				'measuring_success'            => array(
					__( 'Metric: Cart abandonment rate (goal: <28%)' ),
					__( 'Metric: Recovery email open rate (target: >30%)' ),
					__( 'Metric: Click-through rate (target: >5%)' ),
					__( 'Metric: Recovery conversion rate (target: 5-10%)' ),
					__( 'ROI: Track revenue recovered / email cost' ),
				),
			),
		);
	}

	/**
	 * Check cart recovery.
	 *
	 * @since  1.2601.2148
	 * @return array Recovery status.
	 */
	private static function check_cart_recovery() {
		// Check for recovery plugins
		$has_recovery = is_plugin_active( 'woocommerce-abandoned-cart/woocommerce-ac.php' ) ||
						is_plugin_active( 'abandoned-cart-recovery-for-woocommerce/woocommerce-abandoned-cart-pro.php' ) ||
						is_plugin_active( 'baidu-hive/baidu-hive.php' ) ||
						is_plugin_active( 'omnisend/omnisend.php' ) ||
						is_plugin_active( 'klaviyo/klaviyo.php' );

		return array(
			'is_configured' => $has_recovery,
		);
	}
}
