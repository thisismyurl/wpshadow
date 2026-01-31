<?php
/**
 * Wordfence Rate Limiting Diagnostic
 *
 * Wordfence Rate Limiting misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.844.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence Rate Limiting Diagnostic Class
 *
 * @since 1.844.0000
 */
class Diagnostic_WordfenceRateLimiting extends Diagnostic_Base {

	protected static $slug = 'wordfence-rate-limiting';
	protected static $title = 'Wordfence Rate Limiting';
	protected static $description = 'Wordfence Rate Limiting misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WORDFENCE_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify login rate limiting
		$login_limit = get_option( 'wordfence_loginRateLimit', 0 );
		if ( ! $login_limit ) {
			$issues[] = 'Login rate limiting not enabled';
		}

		// Check 2: Check for password guessing protection
		$guesses = get_option( 'wordfence_loginSecurityEnabled', 0 );
		if ( ! $guesses ) {
			$issues[] = 'Password guessing protection not enabled';
		}

		// Check 3: Verify brute force protection
		$brute_force = get_option( 'wordfence_bruteForceProtection', 0 );
		if ( ! $brute_force ) {
			$issues[] = 'Brute force protection not enabled';
		}

		// Check 4: Check for IP block duration
		$block_duration = get_option( 'wordfence_bruteForceBlockDuration', 0 );
		if ( $block_duration <= 0 ) {
			$issues[] = 'IP block duration not configured';
		}

		// Check 5: Verify IP whitelist configuration
		$whitelist = get_option( 'wordfence_whitelistedIPs', '' );
		if ( empty( $whitelist ) ) {
			$issues[] = 'IP whitelist not configured';
		}

		// Check 6: Check for rate limiting on 404s
		$limit_404 = get_option( 'wordfence_404RateLimit', 0 );
		if ( ! $limit_404 ) {
			$issues[] = '404 rate limiting not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 70;
			$threat_multiplier = 5;
			$max_threat = 95;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Wordfence rate limiting issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wordfence-rate-limiting',
			);
		}

		return null;
	}
}
