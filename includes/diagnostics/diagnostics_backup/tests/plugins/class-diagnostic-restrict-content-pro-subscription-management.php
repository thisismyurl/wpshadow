<?php
/**
 * Restrict Content Pro Subscription Management Diagnostic
 *
 * RCP subscription handling flawed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.331.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restrict Content Pro Subscription Management Diagnostic Class
 *
 * @since 1.331.0000
 */
class Diagnostic_RestrictContentProSubscriptionManagement extends Diagnostic_Base {

	protected static $slug = 'restrict-content-pro-subscription-management';
	protected static $title = 'Restrict Content Pro Subscription Management';
	protected static $description = 'RCP subscription handling flawed';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'RCP_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Memberships exist
		$membership_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}rcp_memberships" );
		
		if ( $membership_count === 0 ) {
			return null;
		}
		
		// Check 2: Expired subscriptions not cleaned
		$expired = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}rcp_memberships
				 WHERE status = %s AND expiration_date < %s",
				'active',
				date( 'Y-m-d H:i:s' )
			)
		);
		
		if ( $expired > 10 ) {
			$issues[] = sprintf( __( '%d expired subscriptions still marked active', 'wpshadow' ), $expired );
		}
		
		// Check 3: Renewal notifications enabled
		$renewal_notices = get_option( 'rcp_send_renewal_reminders', false );
		if ( ! $renewal_notices ) {
			$issues[] = __( 'Renewal reminder emails not enabled (churn risk)', 'wpshadow' );
		}
		
		// Check 4: Payment failure handling
		$failed_payments = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}rcp_payments WHERE status = %s",
				'failed'
			)
		);
		
		if ( $failed_payments > 20 ) {
			$issues[] = sprintf( __( '%d failed payments (review gateway configuration)', 'wpshadow' ), $failed_payments );
		}
		
		// Check 5: Subscription status cron
		$cron_scheduled = wp_next_scheduled( 'rcp_check_for_expired_memberships' );
		if ( ! $cron_scheduled ) {
			$issues[] = __( 'Expiration check cron not scheduled (status sync issues)', 'wpshadow' );
		}
		
		// Check 6: Member query optimization
		$has_index = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM information_schema.statistics
				 WHERE table_schema = %s AND table_name = %s AND index_name LIKE %s",
				DB_NAME,
				$wpdb->prefix . 'rcp_memberships',
				'%status%'
			)
		);
		
		if ( $has_index === 0 && $membership_count > 500 ) {
			$issues[] = __( 'Missing database index on status (slow member queries)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 68;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 62;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of subscription management issues */
				__( 'Restrict Content Pro subscriptions have %d management issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/restrict-content-pro-subscription-management',
		);
	}
}
