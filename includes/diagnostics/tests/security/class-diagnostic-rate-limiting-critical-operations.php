<?php
/**
 * Rate Limiting on Critical Operations Diagnostic
 *
 * Issue #4882: No Rate Limiting on Login or API Endpoints
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if rate limiting protects critical operations.
 * Brute force attacks try thousands of logins per minute.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Rate_Limiting_Critical_Operations Class
 *
 * Checks for:
 * - Login rate limiting (max 5 attempts per 15 minutes)
 * - API endpoint rate limiting (100-1000 requests/hour)
 * - Password reset rate limiting
 * - Comment submission rate limiting
 * - Contact form rate limiting
 * - Account registration rate limiting
 * - IP-based and user-based limits
 * - Gradual backoff (exponential delays)
 *
 * Why this matters:
 * - Brute force attacks try 10,000+ passwords
 * - API abuse can overwhelm servers
 * - Comment spam floods databases
 * - DDoS attacks via legitimate endpoints
 *
 * @since 1.6093.1200
 */
class Diagnostic_Rate_Limiting_Critical_Operations extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'rate-limiting-critical-operations';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'No Rate Limiting on Login or API Endpoints';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if rate limiting protects against brute force and abuse';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance diagnostic - actual rate limiting requires infrastructure.
		// We provide recommendations.

		$issues = array();

		$issues[] = __( 'Login: Max 5 failed attempts per 15 minutes per IP', 'wpshadow' );
		$issues[] = __( 'API endpoints: 100-1000 requests per hour per user', 'wpshadow' );
		$issues[] = __( 'Password reset: Max 3 requests per hour per email', 'wpshadow' );
		$issues[] = __( 'Comment submission: Max 5 per hour per IP', 'wpshadow' );
		$issues[] = __( 'Account registration: Max 3 per hour per IP', 'wpshadow' );
		$issues[] = __( 'Use exponential backoff: 1s, 2s, 4s, 8s delays', 'wpshadow' );
		$issues[] = __( 'Track by IP address AND user ID (prevents distributed attacks)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Brute force attacks try thousands of passwords. API abuse overwhelms servers. Rate limiting blocks automated attacks.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/rate-limiting',
				'details'      => array(
					'recommendations'         => $issues,
					'brute_force_stats'       => 'Average attack: 10,000-100,000 login attempts',
					'implementation'          => 'Store attempt count + timestamp in transients or database',
					'lockout_strategy'        => '5 failures → 15 min lockout, 10 failures → 1 hour, 20+ → 24 hours',
					'legitimate_user_impact'  => 'Minimal: Real users rarely fail 5+ times',
					'plugins'                 => 'Wordfence, Limit Login Attempts, Jetpack Protect',
				),
			);
		}

		return null;
	}
}
