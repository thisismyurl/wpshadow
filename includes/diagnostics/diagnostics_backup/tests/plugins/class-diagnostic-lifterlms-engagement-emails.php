<?php
/**
 * LifterLMS Engagement Emails Diagnostic
 *
 * LifterLMS email triggers misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.368.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LifterLMS Engagement Emails Diagnostic Class
 *
 * @since 1.368.0000
 */
class Diagnostic_LifterlmsEngagementEmails extends Diagnostic_Base {

	protected static $slug = 'lifterlms-engagement-emails';
	protected static $title = 'LifterLMS Engagement Emails';
	protected static $description = 'LifterLMS email triggers misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'LLMS' ) ) {
			return null;
		}
		
		// Check if LifterLMS is active
		if ( ! function_exists( 'LLMS' ) && ! class_exists( 'LifterLMS' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check email notification settings
		$notifications_enabled = get_option( 'llms_engagement_emails_enabled', 'yes' );
		if ( $notifications_enabled === 'no' ) {
			$issues[] = 'engagement_emails_disabled';
			$threat_level += 25;
		}

		// Check email triggers
		$triggers = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'llms_email'"
		);
		if ( $triggers === 0 ) {
			$issues[] = 'no_email_triggers_configured';
			$threat_level += 20;
		}

		// Check FROM email
		$from_email = get_option( 'llms_email_from_address', '' );
		if ( empty( $from_email ) ) {
			$issues[] = 'from_email_not_configured';
			$threat_level += 20;
		}

		// Check email templates
		$header_text = get_option( 'llms_email_header_text', '' );
		$footer_text = get_option( 'llms_email_footer_text', '' );
		if ( empty( $header_text ) && empty( $footer_text ) ) {
			$issues[] = 'email_templates_not_customized';
			$threat_level += 10;
		}

		// Check notification delivery
		$engagement_triggers = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts}
				 WHERE post_type = %s
				 AND post_status = %s",
				'llms_engagement',
				'draft'
			)
		);
		if ( $engagement_triggers > 5 ) {
			$issues[] = 'inactive_engagement_triggers';
			$threat_level += 15;
		}

		// Check email personalization
		$personalization = get_option( 'llms_enable_email_personalization', 'yes' );
		if ( $personalization === 'no' ) {
			$issues[] = 'email_personalization_disabled';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of email configuration issues */
				__( 'LifterLMS engagement emails have configuration problems: %s. This reduces student engagement and completion rates.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/lifterlms-engagement-emails',
			);
		}
		
		return null;
	}
}
