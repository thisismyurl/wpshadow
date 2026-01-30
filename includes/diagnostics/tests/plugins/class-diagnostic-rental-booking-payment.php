<?php
/**
 * Rental Booking Payment Diagnostic
 *
 * Rental payment processing insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.612.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rental Booking Payment Diagnostic Class
 *
 * @since 1.612.0000
 */
class Diagnostic_RentalBookingPayment extends Diagnostic_Base {

	protected static $slug = 'rental-booking-payment';
	protected static $title = 'Rental Booking Payment';
	protected static $description = 'Rental payment processing insecure';
	protected static $family = 'security';

	public static function check() {
		global $wpdb;
		
		// Check for rental booking plugins (common post types)
		$rental_posts = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type IN (%s, %s, %s)",
				'rental',
				'booking',
				'rental_booking'
			)
		);
		
		if ( $rental_posts === 0 ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: SSL enabled for payment pages
		if ( ! is_ssl() ) {
			$issues[] = __( 'Site not using SSL (payment data insecure)', 'wpshadow' );
		}
		
		// Check 2: Payment gateway configured
		$gateway_options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				'%payment_gateway%',
				'%rental_payment%'
			)
		);
		
		if ( empty( $gateway_options ) ) {
			$issues[] = __( 'No payment gateway configured', 'wpshadow' );
		}
		
		// Check 3: PCI compliance mode
		$pci_mode = get_option( 'rental_pci_compliant_mode', false );
		if ( ! $pci_mode ) {
			$issues[] = __( 'PCI compliance mode not enabled', 'wpshadow' );
		}
		
		// Check 4: Payment data stored in postmeta
		$payment_meta = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
				'%payment_%'
			)
		);
		
		if ( $payment_meta > 100 ) {
			$issues[] = sprintf( __( '%d payment records in database (encryption status unknown)', 'wpshadow' ), $payment_meta );
		}
		
		// Check 5: Credit card tokenization
		$tokenization = get_option( 'rental_payment_tokenization', false );
		if ( ! $tokenization && count( $gateway_options ) > 0 ) {
			$issues[] = __( 'Payment tokenization not enabled (storing card data?)', 'wpshadow' );
		}
		
		// Check 6: Payment webhook verification
		$webhook_secret = get_option( 'rental_webhook_secret', '' );
		if ( empty( $webhook_secret ) ) {
			$issues[] = __( 'Payment webhook signature verification not configured', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 75;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 88;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 82;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of security issues */
				__( 'Rental booking payment has %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/rental-booking-payment',
		);
	}
}
