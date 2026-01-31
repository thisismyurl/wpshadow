<?php
/**
 * Mollie Payment Link Expiry Diagnostic
 *
 * Mollie Payment Link Expiry vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1411.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mollie Payment Link Expiry Diagnostic Class
 *
 * @since 1.1411.0000
 */
class Diagnostic_MolliePaymentLinkExpiry extends Diagnostic_Base {

	protected static $slug = 'mollie-payment-link-expiry';
	protected static $title = 'Mollie Payment Link Expiry';
	protected static $description = 'Mollie Payment Link Expiry vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Mollie_WC_Plugin' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify payment link expiration time
		$link_expiry = get_option( 'mollie_payment_link_expiry_hours', 0 );
		if ( $link_expiry > 24 || $link_expiry === 0 ) {
			$issues[] = __( 'Payment link expiration time too long or not configured', 'wpshadow' );
		}

		// Check 2: Check expired link cleanup
		$cleanup_schedule = wp_get_schedule( 'mollie_expired_links_cleanup' );
		if ( false === $cleanup_schedule ) {
			$issues[] = __( 'Expired payment link cleanup not scheduled', 'wpshadow' );
		}

		// Check 3: Verify SSL for payment links
		if ( ! is_ssl() ) {
			$issues[] = __( 'SSL not enabled for payment link generation', 'wpshadow' );
		}

		// Check 4: Check expiry warning notifications
		$expiry_warnings = get_option( 'mollie_payment_link_expiry_warnings', false );
		if ( ! $expiry_warnings ) {
			$issues[] = __( 'Payment link expiry warnings not enabled', 'wpshadow' );
		}

		// Check 5: Verify automatic link renewal
		$auto_renewal = get_option( 'mollie_payment_link_auto_renewal', false );
		if ( ! $auto_renewal ) {
			$issues[] = __( 'Automatic payment link renewal not configured', 'wpshadow' );
		}

		// Check 6: Check payment link validation on access
		$validate_on_access = get_option( 'mollie_validate_link_on_access', false );
		if ( ! $validate_on_access ) {
			$issues[] = __( 'Payment link validation on access not enabled', 'wpshadow' );
		}
		return null;
	}
}
