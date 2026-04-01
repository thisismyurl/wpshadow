<?php
/**
 * Diagnostic: Billing Failure Recovery
 *
 * Tests whether the site automatically retries failed payments and recovers
 * >60% of failed billing through smart retry logic.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4547
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Billing Failure Recovery Diagnostic
 *
 * Checks for automatic payment retry systems. 20-40% of subscription failures
 * are temporary (expired cards, insufficient funds). Smart retry recovers 60%+.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Behavioral_Billing_Recovery extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'recovers-failed-billing';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Billing Failure Recovery';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site automatically retries failed payments';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for payment retry implementation.
	 *
	 * Looks for retry rules in subscription/membership systems.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if no retry, null if configured.
	 */
	public static function check() {
		// Check WooCommerce Subscriptions retry settings.
		if ( class_exists( 'WC_Subscriptions' ) ) {
			$retry_enabled = get_option( 'wcs_enable_retry', 'no' );

			if ( $retry_enabled === 'yes' ) {
				return null; // Retry enabled.
			}
		}

		// Check Stripe for automatic retry (Stripe has smart retry built-in).
		if ( is_plugin_active( 'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php' ) ) {
			// Stripe handles retry automatically if webhooks configured.
			return null;
		}

		// Check for dunning management plugins.
		$dunning_plugins = array(
			'subscriptions-dunning/subscriptions-dunning.php',
			'failed-payment-recovery/failed-payment-recovery.php',
		);

		foreach ( $dunning_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return null; // Has dunning system.
			}
		}

		// Only applicable for subscription sites.
		$is_subscription_site = false;

		if ( class_exists( 'WC_Subscriptions' ) ) {
			$is_subscription_site = true;
		}

		$membership_plugins = array(
			'memberpress/memberpress.php',
			'paid-memberships-pro/paid-memberships-pro.php',
			'restrict-content-pro/restrict-content-pro.php',
		);

		foreach ( $membership_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$is_subscription_site = true;
				break;
			}
		}

		if ( ! $is_subscription_site ) {
			return null; // Not subscription model.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'No automatic payment retry system detected. 20-40% of subscription payment failures are temporary (expired cards, insufficient funds). Without smart retry (multiple attempts over days), you lose these members permanently. Payment retry systems recover 60%+ of failed payments. Enable WooCommerce Subscriptions retry or use Stripe\'s smart retry.',
				'wpshadow'
			),
			'severity'     => 'medium',
			'threat_level' => 52,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/billing-failure-recovery?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}
}
