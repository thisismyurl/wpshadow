<?php
/**
 * Cookie Notice Consent Logging Diagnostic
 *
 * Cookie Notice consent not logged.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.419.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cookie Notice Consent Logging Diagnostic Class
 *
 * @since 1.419.0000
 */
class Diagnostic_CookieNoticeConsentLogging extends Diagnostic_Base {

	protected static $slug = 'cookie-notice-consent-logging';
	protected static $title = 'Cookie Notice Consent Logging';
	protected static $description = 'Cookie Notice consent not logged';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'COOKIE_NOTICE_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Consent logging enabled
		$logging = get_option( 'cookie_notice_consent_logging_enabled', 0 );
		if ( ! $logging ) {
			$issues[] = 'Consent logging not enabled';
		}

		// Check 2: User IP logging
		$ip = get_option( 'cookie_notice_user_ip_logging_enabled', 0 );
		if ( ! $ip ) {
			$issues[] = 'User IP logging not enabled';
		}

		// Check 3: Timestamp recording
		$timestamp = get_option( 'cookie_notice_timestamp_recording_enabled', 0 );
		if ( ! $timestamp ) {
			$issues[] = 'Timestamp recording not enabled';
		}

		// Check 4: Consent choice logging
		$choices = get_option( 'cookie_notice_choice_logging_enabled', 0 );
		if ( ! $choices ) {
			$issues[] = 'Consent choice logging not enabled';
		}

		// Check 5: Log access restrictions
		$access = get_option( 'cookie_notice_log_access_restricted', 0 );
		if ( ! $access ) {
			$issues[] = 'Log access not properly restricted';
		}

		// Check 6: Log data retention
		$retention = get_option( 'cookie_notice_log_retention_configured', 0 );
		if ( ! $retention ) {
			$issues[] = 'Log retention policy not configured';
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
					'Found %d consent logging issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cookie-notice-consent-logging',
			);
		}

		return null;
	}
}
