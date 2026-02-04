<?php
/**
 * No Subscription or Membership Model Diagnostic
 *
 * Checks if recurring revenue model (subscription/membership) is established.
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
 * Subscription/Membership Model Diagnostic
 *
 * Detects when business relies on one-time purchases without recurring revenue.
 * Recurring revenue (subscriptions) is 5x more valuable than one-time sales.
 * One $100 customer buying once is worth $100. One $10/month customer is worth
 * $600+/year. Subscriptions improve business valuation dramatically.
 *
 * @since 1.6035.2100
 */
class Diagnostic_No_Subscription_Or_Membership_Model extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-subscription-membership-model';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Recurring Revenue Model Established';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if recurring revenue model (subscription/membership) is established';

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
		$has_subscriptions = self::check_subscriptions();

		if ( ! $has_subscriptions ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No subscription/membership model detected. You\'re leaving massive revenue on the table. Recurring revenue is 5x more valuable than one-time sales. A $100 one-time customer = $100 value. A $10/month customer = $600+/year value. Subscriptions also increase business valuation 3-5x. Options: 1) Membership access to content, 2) Monthly software subscription, 3) Subscription box, 4) VIP support tier, 5) Premium content access. Start with one tier.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/subscription-membership-model',
				'details'     => array(
					'subscriptions_active'      => false,
					'subscription_models'       => self::get_subscription_models(),
					'business_impact'           => '5x more valuable than one-time sales, 3-5x valuation increase',
					'recommendation'            => __( 'Introduce at least one subscription tier to your business model', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if subscriptions exist
	 *
	 * @since  1.6035.2100
	 * @return bool True if subscriptions detected
	 */
	private static function check_subscriptions(): bool {
		// Check for subscription/membership plugins
		$plugins = get_plugins();

		$subscription_keywords = array( 'subscription', 'membership', 'recurring', 'automate', 'paid memberships', 'memberpress' );

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $subscription_keywords as $keyword ) {
				if ( strpos( $plugin_name, $keyword ) !== false ) {
					return true;
				}
			}
		}

		// Check WooCommerce subscriptions
		if ( class_exists( 'WC_Subscription' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get subscription models
	 *
	 * @since  1.6035.2100
	 * @return array Array of subscription models
	 */
	private static function get_subscription_models(): array {
		return array(
			array(
				'model'       => 'Content Membership',
				'description' => 'Access to exclusive articles, videos, courses',
				'price'       => '$5-$50/month',
				'best_for'    => 'Content creators, educators, thought leaders',
				'effort'      => 'Medium (content production ongoing)',
			),
			array(
				'model'       => 'SaaS Subscription',
				'description' => 'Monthly access to software/tools',
				'price'       => '$10-$500+/month',
				'best_for'    => 'Developers, agencies, tools/platforms',
				'effort'      => 'High (ongoing development)',
			),
			array(
				'model'       => 'Subscription Box',
				'description' => 'Physical products shipped monthly',
				'price'       => '$20-$100/month',
				'best_for'    => 'E-commerce, unique products, niches',
				'effort'      => 'High (logistics, inventory)',
			),
			array(
				'model'       => 'VIP Support Tier',
				'description' => 'Priority support, faster response times',
				'price'       => '$20-$200/month',
				'best_for'    => 'Service providers, agencies, consultants',
				'effort'      => 'Low (repackage existing service)',
			),
			array(
				'model'       => 'Premium Membership',
				'description' => 'Tier-based access (Standard/Pro/Enterprise)',
				'price'       => 'Tiered $10-$500/month',
				'best_for'    => 'Most businesses (add premium features)',
				'effort'      => 'Medium (feature separation)',
			),
			array(
				'model'       => 'Coaching/Mentoring',
				'description' => 'Monthly 1-on-1 or group coaching access',
				'price'       => '$50-$500/month',
				'best_for'    => 'Consultants, coaches, mentors',
				'effort'      => 'Medium (time-intensive)',
			),
		);
	}
}
