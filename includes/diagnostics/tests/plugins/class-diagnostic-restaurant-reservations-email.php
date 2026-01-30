<?php
/**
 * Restaurant Reservations Email Diagnostic
 *
 * Restaurant notification emails misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.600.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restaurant Reservations Email Diagnostic Class
 *
 * @since 1.600.0000
 */
class Diagnostic_RestaurantReservationsEmail extends Diagnostic_Base {

	protected static $slug = 'restaurant-reservations-email';
	protected static $title = 'Restaurant Reservations Email';
	protected static $description = 'Restaurant notification emails misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'rtbInit' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Email notifications enabled
		$notifications = get_option( 'rtb_email_notifications_enabled', 0 );
		if ( ! $notifications ) {
			$issues[] = 'Email notifications not enabled';
		}
		
		// Check 2: Admin email configured
		$admin_email = get_option( 'rtb_admin_notification_email', '' );
		if ( empty( $admin_email ) ) {
			$issues[] = 'Admin notification email not configured';
		}
		
		// Check 3: Customer confirmation emails
		$customer_emails = get_option( 'rtb_customer_confirmation_emails', 0 );
		if ( ! $customer_emails ) {
			$issues[] = 'Customer confirmation emails not enabled';
		}
		
		// Check 4: Email templates
		$templates = get_option( 'rtb_email_templates_customized', 0 );
		if ( ! $templates ) {
			$issues[] = 'Email templates not customized';
		}
		
		// Check 5: Email scheduling
		$scheduling = get_option( 'rtb_email_scheduling_enabled', 0 );
		if ( ! $scheduling ) {
			$issues[] = 'Email scheduling not enabled';
		}
		
		// Check 6: Bounce handling
		$bounce = get_option( 'rtb_bounce_handling_enabled', 0 );
		if ( ! $bounce ) {
			$issues[] = 'Email bounce handling not enabled';
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
					'Found %d email configuration issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/restaurant-reservations-email',
			);
		}
		
		return null;
	}
}
