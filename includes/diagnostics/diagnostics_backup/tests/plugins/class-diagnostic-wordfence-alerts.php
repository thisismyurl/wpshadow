<?php
/**
 * Wordfence Alert Configuration Diagnostic
 *
 * Validates Wordfence email alert settings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence Alert Configuration Class
 *
 * Checks if critical alerts are properly configured.
 *
 * @since 1.5029.1800
 */
class Diagnostic_Wordfence_Alerts extends Diagnostic_Base {

	protected static $slug        = 'wordfence-alerts';
	protected static $title       = 'Wordfence Alert Configuration';
	protected static $description = 'Validates Wordfence alert settings';
	protected static $family      = 'plugins';

	public static function check() {
		if ( ! class_exists( 'wordfence' ) ) {
			return null;
		}

		$cache_key = 'wpshadow_wordfence_alerts';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();

		// Check if alerts are enabled.
		$alerts_enabled = wfConfig::get( 'alertOn_block', 0 );
		if ( ! $alerts_enabled ) {
			$issues[] = 'Block alerts are disabled';
		}

		// Check if breach alerts are enabled.
		$breach_alerts = wfConfig::get( 'alertOn_breachLogin', 0 );
		if ( ! $breach_alerts ) {
			$issues[] = 'Breach login alerts are disabled';
		}

		// Check if scan completion alerts are enabled.
		$scan_alerts = wfConfig::get( 'alertOn_scanIssues', 0 );
		if ( ! $scan_alerts ) {
			$issues[] = 'Scan issue alerts are disabled';
		}

		// Check if critical alerts go to admin email.
		$alert_emails = wfConfig::get( 'alertEmails', '' );
		if ( empty( $alert_emails ) ) {
			$issues[] = 'No alert email addresses configured';
		} else {
			// Validate email format.
			$emails = explode( ',', $alert_emails );
			$invalid_emails = array();
			foreach ( $emails as $email ) {
				$email = trim( $email );
				if ( ! is_email( $email ) ) {
					$invalid_emails[] = $email;
				}
			}
			if ( ! empty( $invalid_emails ) ) {
				$issues[] = 'Invalid email addresses: ' . implode( ', ', $invalid_emails );
			}
		}

		// Check alert throttling.
		$throttle = wfConfig::get( 'alertFrequency', 0 );
		if ( $throttle > 1 ) {
			$issues[] = sprintf( 'Alerts throttled to %d hours - may miss critical events', $throttle );
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d alert configuration issues found. You may miss critical security events.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugins-wordfence-alerts',
				'data'         => array(
					'alert_issues' => $issues,
					'total_issues' => count( $issues ),
					'configured_emails' => wfConfig::get( 'alertEmails', 'None' ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
