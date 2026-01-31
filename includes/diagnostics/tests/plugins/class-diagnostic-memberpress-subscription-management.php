<?php
/**
 * MemberPress Subscription Management Diagnostic
 *
 * MemberPress subscriptions not managed properly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.321.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MemberPress Subscription Management Diagnostic Class
 *
 * @since 1.321.0000
 */
class Diagnostic_MemberpressSubscriptionManagement extends Diagnostic_Base {

	protected static $slug = 'memberpress-subscription-management';
	protected static $title = 'MemberPress Subscription Management';
	protected static $description = 'MemberPress subscriptions not managed properly';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'MEPR_VERSION' ) ) {
			return null;
		}
		
		// Check if MemberPress is active
		if ( ! defined( 'MEPR_VERSION' ) && ! class_exists( 'MeprSubscription' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check subscriptions table
		$subscriptions_table = $wpdb->prefix . 'mepr_subscriptions';
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$subscriptions_table}'" );
		if ( ! $table_exists ) {
			$issues[] = 'subscriptions_table_missing';
			$threat_level += 40;
		}

		// Check for expired subscriptions
		$expired_subs = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$subscriptions_table} 
				 WHERE status = %s AND expires_at < %s",
				'active',
				current_time( 'mysql' )
			)
		);
		if ( $expired_subs > 10 ) {
			$issues[] = 'expired_subscriptions_not_processed';
			$threat_level += 30;
		}

		// Check failed payments
		$failed_payments = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}mepr_transactions 
				 WHERE status = %s AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)",
				'failed'
			)
		);
		if ( $failed_payments > 5 ) {
			$issues[] = 'excessive_payment_failures';
			$threat_level += 25;
		}

		// Check auto-renewal settings
		$auto_renew = get_option( 'mepr_auto_renew_subscriptions', '1' );
		if ( $auto_renew === '0' ) {
			$issues[] = 'auto_renewal_disabled';
			$threat_level += 20;
		}

		// Check payment retry logic
		$payment_retry = get_option( 'mepr_payment_retry_enabled', '1' );
		if ( $payment_retry === '0' ) {
			$issues[] = 'payment_retry_disabled';
			$threat_level += 15;
		}

		// Check grace period
		$grace_period = get_option( 'mepr_grace_init_days', '0' );
		if ( $grace_period === '0' ) {
			$issues[] = 'no_grace_period_configured';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of subscription issues */
				__( 'MemberPress subscription management has issues: %s. This causes revenue loss and member dissatisfaction.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/memberpress-subscription-management',
			);
		}
		
		return null;
	}
}
