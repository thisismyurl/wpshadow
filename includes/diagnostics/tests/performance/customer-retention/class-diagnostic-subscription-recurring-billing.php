<?php
/**
 * Subscription and Recurring Billing Diagnostic
 *
 * Checks if subscription/recurring billing functionality is available.
 *
 * @package WPShadow\Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Subscription and Recurring Billing
 *
 * Detects whether the site supports recurring revenue through subscriptions.
 */
class Diagnostic_Subscription_Recurring_Billing extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'subscription-recurring-billing';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Subscription and Recurring Billing';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for subscription and recurring billing capabilities';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'customer-retention';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		$issues  = array();
		$stats   = array();
		$plugins = array(
			'woocommerce-subscriptions/woocommerce-subscriptions.php'      => 'WooCommerce Subscriptions',
			'memberships/memberships.php'                                 => 'MemberPress',
			'paid-memberships-pro/paid-memberships-pro.php'               => 'Paid Memberships Pro',
			'pmpro-customizations/pmpro-customizations.php'               => 'PMPro',
			'restrict-content-pro/restrict-content-pro.php'               => 'Restrict Content Pro',
			'leaky-paywall/leaky-paywall.php'                             => 'Leaky Paywall',
		);

		$active = array();
		foreach ( $plugins as $file => $name ) {
			if ( is_plugin_active( $file ) ) {
				$active[] = $name;
			}
		}

		$stats['active_subscription_tools'] = count( $active );
		$stats['subscription_plugins_found'] = $active;

		if ( empty( $active ) ) {
			$issues[] = __( 'No subscription or recurring billing system detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Subscription and recurring billing models create predictable, recurring revenue and increase customer lifetime value. They also build long-term customer relationships and provide steady income streams for sustainable business growth.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/subscriptions?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'       => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
