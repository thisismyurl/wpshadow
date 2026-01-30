<?php
/**
 * Amelia Customer Panel Diagnostic
 *
 * Amelia customer panel permissions wrong.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.467.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Amelia Customer Panel Diagnostic Class
 *
 * @since 1.467.0000
 */
class Diagnostic_AmeliaCustomerPanel extends Diagnostic_Base {

	protected static $slug = 'amelia-customer-panel';
	protected static $title = 'Amelia Customer Panel';
	protected static $description = 'Amelia customer panel permissions wrong';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'AMELIA_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Customer role capabilities
		$customer_role = get_role( 'wpamelia-customer' );
		if ( $customer_role ) {
			$dangerous_caps = array(
				'amelia_read_others',
				'amelia_write_others',
				'amelia_delete_others',
				'manage_options',
				'edit_users',
			);
			
			$found_dangerous = array();
			foreach ( $dangerous_caps as $cap ) {
				if ( $customer_role->has_cap( $cap ) ) {
					$found_dangerous[] = $cap;
				}
			}
			
			if ( count( $found_dangerous ) > 0 ) {
				$issues[] = sprintf(
					/* translators: %s: list of capabilities */
					__( 'Customer role has elevated permissions: %s', 'wpshadow' ),
					implode( ', ', $found_dangerous )
				);
			}
		}
		
		// Check 2: Customer panel access restrictions
		$panel_restrictions = get_option( 'amelia_settings_panel_restrictions', array() );
		if ( ! isset( $panel_restrictions['restrict_panel_access'] ) || ! $panel_restrictions['restrict_panel_access'] ) {
			$issues[] = __( 'Customer panel access not restricted (data exposure)', 'wpshadow' );
		}
		
		// Check 3: Appointment data visibility
		$hide_others = get_option( 'amelia_hide_others_appointments', 'yes' );
		if ( 'no' === $hide_others ) {
			$issues[] = __( 'Customers can see others\' appointments (privacy leak)', 'wpshadow' );
		}
		
		// Check 4: Payment information access
		$hide_payments = get_option( 'amelia_hide_payment_details', 'yes' );
		if ( 'no' === $hide_payments ) {
			$issues[] = __( 'Payment details visible to customers (PCI violation)', 'wpshadow' );
		}
		
		// Check 5: Export data permissions
		$allow_export = get_option( 'amelia_customer_export', 'no' );
		if ( 'yes' === $allow_export ) {
			$issues[] = __( 'Customers can export data (unauthorized access)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 60;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 75;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 68;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of permission issues */
				__( 'Amelia customer panel has %d permission issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/amelia-customer-panel',
		);
	}
}
