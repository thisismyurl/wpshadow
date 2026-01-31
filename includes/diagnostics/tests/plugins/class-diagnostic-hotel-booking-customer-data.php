<?php
/**
 * Hotel Booking Customer Data Diagnostic
 *
 * Hotel customer data exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.610.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hotel Booking Customer Data Diagnostic Class
 *
 * @since 1.610.0000
 */
class Diagnostic_HotelBookingCustomerData extends Diagnostic_Base {

	protected static $slug = 'hotel-booking-customer-data';
	protected static $title = 'Hotel Booking Customer Data';
	protected static $description = 'Hotel customer data exposed';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'MPHB_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Data encryption enabled
		$encryption = get_option( 'mphb_customer_data_encryption', 0 );
		if ( ! $encryption ) {
			$issues[] = 'Customer data encryption not enabled';
		}

		// Check 2: PCI DSS compliance
		$pci_compliance = get_option( 'mphb_pci_dss_compliance', 0 );
		if ( ! $pci_compliance ) {
			$issues[] = 'PCI DSS compliance not enabled';
		}

		// Check 3: Data retention policy
		$retention = get_option( 'mphb_data_retention_policy', '' );
		if ( empty( $retention ) ) {
			$issues[] = 'Data retention policy not configured';
		}

		// Check 4: Privacy policy linked
		$privacy_link = get_option( 'mphb_privacy_policy_linked', 0 );
		if ( ! $privacy_link ) {
			$issues[] = 'Privacy policy not linked';
		}

		// Check 5: Payment data handling
		$payment_secure = get_option( 'mphb_secure_payment_handling', 0 );
		if ( ! $payment_secure ) {
			$issues[] = 'Secure payment handling not enabled';
		}

		// Check 6: Audit logging
		$audit = get_option( 'mphb_customer_data_audit_logging', 0 );
		if ( ! $audit ) {
			$issues[] = 'Data access audit logging not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 60;
			$threat_multiplier = 6;
			$max_threat = 90;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d customer data security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/hotel-booking-customer-data',
			);
		}

		return null;
	}
}
