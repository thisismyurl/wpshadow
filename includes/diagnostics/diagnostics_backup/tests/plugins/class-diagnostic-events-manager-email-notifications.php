<?php
/**
 * Events Manager Email Notifications Diagnostic
 *
 * Events Manager emails misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.579.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Events Manager Email Notifications Diagnostic Class
 *
 * @since 1.579.0000
 */
class Diagnostic_EventsManagerEmailNotifications extends Diagnostic_Base {

	protected static $slug = 'events-manager-email-notifications';
	protected static $title = 'Events Manager Email Notifications';
	protected static $description = 'Events Manager emails misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'EM_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Email notification enabled
		$notify_enabled = get_option( 'dbem_email_notifications', '0' );
		if ( '0' === $notify_enabled ) {
			return null; // Feature not in use
		}

		// Check 2: From email configured
		$from_email = get_option( 'dbem_mail_sender_address', '' );
		if ( empty( $from_email ) || $from_email === get_option( 'admin_email' ) ) {
			$issues[] = __( 'Using admin email as sender (spam risk)', 'wpshadow' );
		}

		// Check 3: Email template formatting
		$email_html = get_option( 'dbem_email_html', '0' );
		if ( '0' === $email_html ) {
			$issues[] = __( 'Plain text emails only (poor formatting)', 'wpshadow' );
		}

		// Check 4: BCC on all emails
		$bcc_enabled = get_option( 'dbem_email_bcc', '0' );
		if ( '1' === $bcc_enabled ) {
			$issues[] = __( 'BCC on all emails (privacy risk)', 'wpshadow' );
		}

		// Check 5: SMTP configuration
		$use_smtp = get_option( 'dbem_rsvp_mail_send_method', 'mail' );
		if ( 'mail' === $use_smtp ) {
			$issues[] = __( 'Using PHP mail() (unreliable delivery)', 'wpshadow' );
		}

		// Check 6: Email rate limiting
		$rate_limit = get_option( 'dbem_email_rate_limit', 0 );
		if ( $rate_limit === 0 ) {
			$issues[] = __( 'No rate limiting (mail server bans)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of Events Manager email issues */
				__( 'Events Manager emails have %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/events-manager-email-notifications',
		);
	}
}
