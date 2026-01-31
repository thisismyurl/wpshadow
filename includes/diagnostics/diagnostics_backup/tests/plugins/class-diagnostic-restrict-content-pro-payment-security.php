<?php
/**
 * Restrict Content Pro Payment Security Diagnostic
 *
 * RCP payment processing insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.326.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restrict Content Pro Payment Security Diagnostic Class
 *
 * @since 1.326.0000
 */
class Diagnostic_RestrictContentProPaymentSecurity extends Diagnostic_Base {

	protected static $slug = 'restrict-content-pro-payment-security';
	protected static $title = 'Restrict Content Pro Payment Security';
	protected static $description = 'RCP payment processing insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'RCP_PLUGIN_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify SSL is enabled for checkout
		if ( ! is_ssl() ) {
			$issues[] = 'SSL not enabled for payment processing';
		}

		// Check 2: Check payment gateway configuration
		$gateways = get_option( 'rcp_settings_gateways', array() );
		$enabled_gateways = isset( $gateways['enabled'] ) ? $gateways['enabled'] : array();
		if ( empty( $enabled_gateways ) ) {
			$issues[] = 'No payment gateways enabled';
		}

		// Check 3: Verify payment gateway keys are configured
		if ( isset( $enabled_gateways['stripe'] ) ) {
			$stripe_keys = get_option( 'rcp_settings_stripe', array() );
			if ( empty( $stripe_keys['live_secret'] ) || empty( $stripe_keys['live_publishable'] ) ) {
				$issues[] = 'Stripe API keys not configured';
			}
		}

		// Check 4: Check for webhook security
		$webhook_secret = get_option( 'rcp_stripe_webhook_secret', '' );
		if ( isset( $enabled_gateways['stripe'] ) && empty( $webhook_secret ) ) {
			$issues[] = 'Stripe webhook secret not configured';
		}

		// Check 5: Verify failed payment handling
		$failed_payment_action = get_option( 'rcp_failed_payment_action', '' );
		if ( empty( $failed_payment_action ) ) {
			$issues[] = 'Failed payment handling not configured';
		}

		// Check 6: Check for payment receipt security
		$disable_email_receipts = get_option( 'rcp_disable_email_receipts', 0 );
		if ( $disable_email_receipts ) {
			$issues[] = 'Payment receipts disabled (recommended for security audit trail)';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 70;
			$threat_multiplier = 5;
			$max_threat = 95;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Restrict Content Pro payment security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/restrict-content-pro-payment-security',
			);
		}

		return null;
	}
}
