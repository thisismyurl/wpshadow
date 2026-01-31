<?php
/**
 * Multisite Billing Integration Diagnostic
 *
 * Multisite Billing Integration misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.987.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Billing Integration Diagnostic Class
 *
 * @since 1.987.0000
 */
class Diagnostic_MultisiteBillingIntegration extends Diagnostic_Base {

	protected static $slug = 'multisite-billing-integration';
	protected static $title = 'Multisite Billing Integration';
	protected static $description = 'Multisite Billing Integration misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}

		$issues = array();

		// Check 1: Billing gateway configured.
		$gateway = get_site_option( 'multisite_billing_gateway', '' );
		if ( empty( $gateway ) ) {
			$issues[] = 'billing gateway not configured';
		}

		// Check 2: SSL for payments.
		if ( ! is_ssl() ) {
			$issues[] = 'billing without HTTPS';
		}

		// Check 3: Subscription tracking.
		$subscription_tracking = get_site_option( 'multisite_subscription_tracking', '1' );
		if ( '0' === $subscription_tracking ) {
			$issues[] = 'subscription tracking disabled';
		}

		// Check 4: Invoice generation.
		$invoices = get_site_option( 'multisite_generate_invoices', '1' );
		if ( '0' === $invoices ) {
			$issues[] = 'invoice generation disabled';
		}

		// Check 5: Payment logging.
		$payment_log = get_site_option( 'multisite_payment_logging', '1' );
		if ( '0' === $payment_log ) {
			$issues[] = 'payment logging disabled';
		}

		// Check 6: Failed payment handling.
		$failed_payments = get_site_option( 'multisite_handle_failed_payments', '1' );
		if ( '0' === $failed_payments ) {
			$issues[] = 'failed payments not handled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 50 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Multisite billing issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/multisite-billing-integration',
			);
		}

		return null;
	}
}
