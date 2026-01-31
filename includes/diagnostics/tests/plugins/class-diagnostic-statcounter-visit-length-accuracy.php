<?php
/**
 * Statcounter Visit Length Accuracy Diagnostic
 *
 * Statcounter Visit Length Accuracy misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1361.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Statcounter Visit Length Accuracy Diagnostic Class
 *
 * @since 1.1361.0000
 */
class Diagnostic_StatcounterVisitLengthAccuracy extends Diagnostic_Base {

	protected static $slug = 'statcounter-visit-length-accuracy';
	protected static $title = 'Statcounter Visit Length Accuracy';
	protected static $description = 'Statcounter Visit Length Accuracy misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'STATCOUNTER_VERSION' ) && ! get_option( 'statcounter_project', '' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify visit length tracking enabled
		$visit_length = get_option( 'statcounter_visit_length', 0 );
		if ( ! $visit_length ) {
			$issues[] = 'Visit length tracking not enabled';
		}

		// Check 2: Check for heartbeat ping interval
		$ping_interval = get_option( 'statcounter_ping_interval', 0 );
		if ( $ping_interval <= 0 ) {
			$issues[] = 'Ping interval not configured';
		}

		// Check 3: Verify async script loading
		$async = get_option( 'statcounter_async', 0 );
		if ( ! $async ) {
			$issues[] = 'StatCounter script not loaded asynchronously';
		}

		// Check 4: Check for bot filtering
		$bot_filter = get_option( 'statcounter_bot_filter', 0 );
		if ( ! $bot_filter ) {
			$issues[] = 'Bot filtering not enabled';
		}

		// Check 5: Verify consent integration
		$consent = get_option( 'statcounter_consent_mode', 0 );
		if ( ! $consent ) {
			$issues[] = 'Consent mode not enabled';
		}

		// Check 6: Check for cookie duration settings
		$cookie_duration = get_option( 'statcounter_cookie_duration', 0 );
		if ( $cookie_duration <= 0 ) {
			$issues[] = 'Cookie duration not configured';
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
					'Found %d StatCounter visit length issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/statcounter-visit-length-accuracy',
			);
		}

		return null;
	}
}
