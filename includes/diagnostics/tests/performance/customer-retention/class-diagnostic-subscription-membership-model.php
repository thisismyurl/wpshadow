<?php
/**
 * Subscription Membership Model Diagnostic
 *
 * Detects when businesses could benefit from subscription/membership revenue.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\CustomerRetention
 * @since      1.6035.2313
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\CustomerRetention;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Subscription Membership Model Diagnostic Class
 *
 * Checks if the site uses subscription/membership revenue models.
 *
 * @since 1.6035.2313
 */
class Diagnostic_Subscription_Membership_Model extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'subscription-membership-model';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Subscription or Membership Offering';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when sites could benefit from subscription revenue';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'customer-retention';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6035.2313
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		// Check for subscription/membership plugins.
		$membership_plugins = array(
			'woocommerce-subscriptions/woocommerce-subscriptions.php' => 'WooCommerce Subscriptions',
			'memberpress/memberpress.php'            => 'MemberPress',
			'restrict-content-pro/restrict-content-pro.php' => 'Restrict Content Pro',
			'paid-memberships-pro/paid-memberships-pro.php' => 'Paid Memberships Pro',
			'wlm3-ssk/wlm3-ssk.php'                  => 'WishList Member',
		);

		$active_memberships = array();
		foreach ( $membership_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_memberships[] = $name;
			}
		}

		if ( ! empty( $active_memberships ) ) {
			return null; // Membership/subscription active.
		}

		// Check for business types that benefit from subscriptions.
		$suitable_plugins = array(
			'lifterlms/lifterlms.php'         => 'Courses (recurring access)',
			'learnpress/learnpress.php'       => 'Courses (recurring access)',
			'woocommerce/woocommerce.php'     => 'Products (subscription boxes)',
			'easy-digital-downloads/easy-digital-downloads.php' => 'Digital Products (software licenses)',
		);

		$business_opportunities = array();
		foreach ( $suitable_plugins as $plugin => $opportunity ) {
			if ( is_plugin_active( $plugin ) ) {
				$business_opportunities[] = $opportunity;
			}
		}

		// If no suitable business model detected, skip.
		if ( empty( $business_opportunities ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'You\'re only getting paid once. Subscription and membership models create predictable recurring revenue and increase customer lifetime value', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/subscription-models',
			'context'      => array(
				'opportunities'      => $business_opportunities,
				'has_memberships'    => false,
				'impact'             => __( 'Subscription businesses grow 5x faster than traditional businesses. Recurring revenue is more predictable and valued higher by investors (5-8x vs 1-2x for one-time sales).', 'wpshadow' ),
				'recommendation'     => array(
					__( 'Identify products/services that could be subscription-based', 'wpshadow' ),
					__( 'Create membership tiers with exclusive benefits', 'wpshadow' ),
					__( 'Offer "subscribe and save" discounts (10-15% off)', 'wpshadow' ),
					__( 'Bundle services into monthly/annual packages', 'wpshadow' ),
					__( 'Add exclusive content for members only', 'wpshadow' ),
					__( 'Implement a freemium model to convert free users', 'wpshadow' ),
					__( 'Use automatic renewal with easy cancellation', 'wpshadow' ),
				),
				'revenue_stability'  => __( 'Subscription revenue is 90% predictable vs. 10-20% for one-time sales', 'wpshadow' ),
				'customer_value'     => __( 'Subscription customers have 3-5x higher lifetime value', 'wpshadow' ),
				'churn_management'   => __( 'Focus on retention (5% churn reduction = 25-95% profit increase)', 'wpshadow' ),
			),
		);
	}
}
