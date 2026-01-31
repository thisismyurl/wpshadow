<?php
/**
 * Business Directory Email Notifications Diagnostic
 *
 * Business Directory emails excessive.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.550.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Business Directory Email Notifications Diagnostic Class
 *
 * @since 1.550.0000
 */
class Diagnostic_BusinessDirectoryEmailNotifications extends Diagnostic_Base {

	protected static $slug = 'business-directory-email-notifications';
	protected static $title = 'Business Directory Email Notifications';
	protected static $description = 'Business Directory emails excessive';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Email notifications enabled
		$notifications_enabled = get_option( 'wpbdp_email_notifications', true );
		if ( ! $notifications_enabled ) {
			return null;
		}
		
		// Check 2: Notification frequency
		$notification_types = array(
			'wpbdp_notify_on_new_listing',
			'wpbdp_notify_on_listing_edit',
			'wpbdp_notify_on_listing_renewal',
			'wpbdp_notify_on_listing_expiration',
		);
		
		$enabled_count = 0;
		foreach ( $notification_types as $type ) {
			if ( get_option( $type, false ) ) {
				$enabled_count++;
			}
		}
		
		if ( $enabled_count > 3 ) {
			$issues[] = sprintf( __( '%d notification types enabled (excessive emails)', 'wpshadow' ), $enabled_count );
		}
		
		// Check 3: Email queuing
		$use_queue = get_option( 'wpbdp_email_use_queue', false );
		if ( ! $use_queue ) {
			$issues[] = __( 'Email queuing not enabled (server load spikes)', 'wpshadow' );
		}
		
		// Check 4: Rate limiting
		$rate_limit = get_option( 'wpbdp_email_rate_limit', 0 );
		if ( $rate_limit === 0 ) {
			$issues[] = __( 'No email rate limiting (spam complaints)', 'wpshadow' );
		}
		
		// Check 5: Unsubscribe links
		$unsubscribe_enabled = get_option( 'wpbdp_email_include_unsubscribe', false );
		if ( ! $unsubscribe_enabled ) {
			$issues[] = __( 'Unsubscribe links not included (CAN-SPAM violation)', 'wpshadow' );
		}
		
		// Check 6: SMTP configuration
		$smtp_configured = get_option( 'wpbdp_email_smtp_configured', false );
		if ( ! $smtp_configured ) {
			$issues[] = __( 'Using PHP mail() (low deliverability)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 45;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 58;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 52;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of email notification issues */
				__( 'Business Directory email notifications have %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/business-directory-email-notifications',
		);
	}
}
