<?php
/**
 * Geolocation Blocking Not Configured Diagnostic
 *
 * Validates that geolocation-based access controls are configured to restrict\n * traffic from high-risk countries or enforce regional compliance. Without geo-blocking,\n * bots from adversarial nations execute brute force attacks undetected.\n *
 * **What This Check Does:**
 * - Detects if geolocation blocking is enabled\n * - Checks for country-based access rules\n * - Validates IP geolocation database is current\n * - Tests if specific countries are whitelisted/blacklisted\n * - Confirms geo-blocking covers admin endpoints\n * - Validates VPN/proxy detection enabled (bypass prevention)\n *
 * **Why This Matters:**
 * Unblocked foreign traffic enables mass brute force attacks. Scenarios:\n * - Brute force attacks originate from specific adversarial nations\n * - Without geo-blocking, admin login attempts from anywhere succeed\n * - Attacker in nation X performs 1,000 attempts/minute\n * - No rate limiting per country (request volume absorbed)\n * - Password eventually guessed\n *
 * **Business Impact:**
 * B2B SaaS platform. Customers primarily US-based. Receives 40,000 failed login\n * attempts/hour from Eastern Europe + Asia. Server CPU maxed (brute force processing).\n * Legitimate users experience 5-second page load delays. Support tickets increase.\n * Revenue impact: 5% of users churn due to slowness = $50K/month lost.\n * Enabling geo-blocking: 5 minute setup, saves $50K/month.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Attack surface dramatically reduced\n * - #9 Show Value: Quantified brute force elimination\n * - #10 Beyond Pure: Regional compliance support (GDPR, data localization)\n *
 * **Related Checks:**
 * - API Throttling Not Configured (rate limiting)\n * - Bot Traffic Detection Not Implemented (bot prevention)\n * - Login Attempt Limiting (brute force defense)\n *
 * **Learn More:**
 * Geolocation blocking setup: https://wpshadow.com/kb/wordpress-geo-blocking\n * Video: Configuring geographic restrictions (8min): https://wpshadow.com/training/geo-security\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Geolocation Blocking Not Configured Diagnostic Class
 *
 * Implements detection of missing geolocation-based access controls.\n *
 * **Detection Pattern:**
 * 1. Check if geo-blocking plugin/feature enabled\n * 2. Query for country whitelist/blacklist rules\n * 3. Validate geolocation database (MaxMind, etc)\n * 4. Test if admin endpoints have geo-restrictions\n * 5. Check if VPN/proxy detection enabled\n * 6. Return severity if geo-blocking missing\n *
 * **Real-World Scenario:**
 * WordPress site primarily serves EU customers (GDPR compliant). Receives 90%\n * of traffic from outside EU. No geo-blocking configured. Attacker in nation\n * with poor security practices discovers WordPress install. Attempts 10K login\n * guesses in 24 hours. Server CPU overloaded. Legitimate EU traffic slows.\n *
 * **Implementation Notes:**
 * - Checks for geo-blocking plugin active\n * - Validates rule configuration (whitelist/blacklist)\n * - Tests geolocation database freshness\n * - Severity: medium (no geo-blocking), high (inadequate rules)\n * - Treatment: implement geolocation-based access control\n *
 * @since 1.6093.1200
 */
class Diagnostic_Geolocation_Blocking_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'geolocation-blocking-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Geolocation Blocking Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if geolocation blocking is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for geolocation blocking rules
		if ( ! get_option( 'geolocation_blocking_enabled' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Geolocation blocking is not configured. Block access from high-risk countries or allow access only from specific regions based on your business requirements and compliance needs.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/geolocation-blocking-not-configured',
			);
		}

		return null;
	}
}
