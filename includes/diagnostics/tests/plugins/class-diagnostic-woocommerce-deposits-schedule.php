<?php
/**
 * Woocommerce Deposits Schedule Diagnostic
 *
 * Woocommerce Deposits Schedule issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.684.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Deposits Schedule Diagnostic Class
 *
 * @since 1.684.0000
 */
class Diagnostic_WoocommerceDepositsSchedule extends Diagnostic_Base {

	protected static $slug = 'woocommerce-deposits-schedule';
	protected static $title = 'Woocommerce Deposits Schedule';
	protected static $description = 'Woocommerce Deposits Schedule issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		// Check if Deposits plugin is active
		if ( ! class_exists( 'WC_Deposits' ) && ! defined( 'WC_DEPOSITS_VERSION' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check deposit plans
		$deposit_plans = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'wc_deposits_plan'"
		);
		if ( $deposit_plans === 0 ) {
			$issues[] = 'no_deposit_plans_configured';
			$threat_level += 25;
		}

		// Check deposit settings
		$deposits_enabled = get_option( 'wc_deposits_enabled', 'no' );
		if ( $deposits_enabled === 'no' ) {
			$issues[] = 'deposits_disabled';
			$threat_level += 20;
		}

		// Check payment schedule validation
		$schedule_validation = get_option( 'wc_deposits_validate_schedule', 'yes' );
		if ( $schedule_validation === 'no' ) {
			$issues[] = 'schedule_validation_disabled';
			$threat_level += 20;
		}

		// Check reminder notifications
		$reminders_enabled = get_option( 'wc_deposits_payment_reminders', 'yes' );
		if ( $reminders_enabled === 'no' ) {
			$issues[] = 'payment_reminders_disabled';
			$threat_level += 15;
		}

		// Check for overdue payments
		$overdue_orders = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} p
				 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				 WHERE p.post_type = %s
				 AND pm.meta_key = '_wc_deposits_payment_schedule'
				 AND pm.meta_value LIKE %s",
				'shop_order',
				'%overdue%'
			)
		);
		if ( $overdue_orders > 10 ) {
			$issues[] = 'excessive_overdue_payments';
			$threat_level += 20;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of deposit schedule issues */
				__( 'WooCommerce Deposits has scheduling issues: %s. This causes payment collection problems and revenue loss.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-deposits-schedule',
			);
		}
		
		return null;
	}
}
