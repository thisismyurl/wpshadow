<?php
/**
 * MemberPress Email Notifications Diagnostic
 *
 * MemberPress email settings misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.325.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MemberPress Email Notifications Diagnostic Class
 *
 * @since 1.325.0000
 */
class Diagnostic_MemberpressEmailNotifications extends Diagnostic_Base {

	protected static $slug = 'memberpress-email-notifications';
	protected static $title = 'MemberPress Email Notifications';
	protected static $description = 'MemberPress email settings misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'MEPR_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		$threat_level = 0;

		// Check default from email
		$from_email = get_option( 'mepr_email_from_email', '' );
		if ( empty( $from_email ) ) {
			$issues[] = 'no_from_email';
			$threat_level += 15;
		}

		// Check email templates
		$emails_table = $wpdb->prefix . 'mepr_emails';
		$email_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$emails_table} WHERE enabled = 1" );
		if ( $email_count < 3 ) {
			$issues[] = 'insufficient_email_templates';
			$threat_level += 10;
		}

		// Check for emails without subject
		$emails_without_subject = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$emails_table} 
				 WHERE enabled = %d AND (subject IS NULL OR subject = '')",
				1
			)
		);
		if ( $emails_without_subject > 0 ) {
			$issues[] = 'emails_missing_subject';
			$threat_level += 15;
		}

		// Check signup email
		$signup_email = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$emails_table} 
				 WHERE context = %s AND enabled = %d",
				'user_signup',
				1
			)
		);
		if ( $signup_email === 0 ) {
			$issues[] = 'no_signup_email';
			$threat_level += 15;
		}

		// Check payment receipt email
		$receipt_email = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$emails_table} 
				 WHERE context = %s AND enabled = %d",
				'payment_receipt',
				1
			)
		);
		if ( $receipt_email === 0 ) {
			$issues[] = 'no_receipt_email';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of email notification issues */
				__( 'MemberPress email notifications are misconfigured: %s. This prevents users from receiving important membership updates and payment confirmations.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/memberpress-email-notifications',
			);
		}
		
		return null;
	}
}
