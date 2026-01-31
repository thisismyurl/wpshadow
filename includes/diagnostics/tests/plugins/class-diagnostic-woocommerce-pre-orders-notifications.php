<?php
/**
 * Woocommerce Pre Orders Notifications Diagnostic
 *
 * Woocommerce Pre Orders Notifications issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.670.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Pre Orders Notifications Diagnostic Class
 *
 * @since 1.670.0000
 */
class Diagnostic_WoocommercePreOrdersNotifications extends Diagnostic_Base {

	protected static $slug = 'woocommerce-pre-orders-notifications';
	protected static $title = 'Woocommerce Pre Orders Notifications';
	protected static $description = 'Woocommerce Pre Orders Notifications issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify customer notifications enabled
		$customer_email = get_option( 'woocommerce_pre_orders_customer_notification', 0 );
		if ( ! $customer_email ) {
			$issues[] = 'Customer pre-order notifications not enabled';
		}

		// Check 2: Check for admin notifications
		$admin_email = get_option( 'woocommerce_pre_orders_admin_notification', 0 );
		if ( ! $admin_email ) {
			$issues[] = 'Admin pre-order notifications not enabled';
		}

		// Check 3: Verify release date reminder
		$release_reminder = get_option( 'woocommerce_pre_orders_release_reminder', 0 );
		if ( ! $release_reminder ) {
			$issues[] = 'Release date reminders not enabled';
		}

		// Check 4: Check for payment processing notification
		$payment_notice = get_option( 'woocommerce_pre_orders_payment_notification', 0 );
		if ( ! $payment_notice ) {
			$issues[] = 'Payment processing notifications not enabled';
		}

		// Check 5: Verify reminder schedule
		$reminder_days = get_option( 'woocommerce_pre_orders_reminder_days', 0 );
		if ( $reminder_days <= 0 ) {
			$issues[] = 'Pre-order reminder schedule not configured';
		}

		// Check 6: Check for email sender configuration
		$from_email = get_option( 'woocommerce_email_from_address', '' );
		if ( empty( $from_email ) || ! is_email( $from_email ) ) {
			$issues[] = 'WooCommerce from email not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d WooCommerce pre-order notification issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-pre-orders-notifications',
			);
		}

		return null;
	}
}
