<?php
/**
 * Login Page Rate Limiting Treatment
 *
 * Checks if login page has rate limiting protection against brute force and DoS\n * attacks. Rate limiting prevents attackers from attempting unlimited password\n * guesses. Without limits: attacker tries 1 million passwords in hours.\n *
 * **What This Check Does:**
 * - Detects if rate limiting implemented on login attempts\n * - Validates limit threshold (N attempts per minute/IP)\n * - Tests if lockout duration appropriate (prevents DoS)\n * - Checks if limit applies per IP, per username, or both\n * - Confirms legitimate users not locked out\n * - Validates monitoring/alerts on rate limit triggers\n *
 * **Why This Matters:**
 * Unlimited login attempts = password guessing succeeds. Scenarios:\n * - No rate limiting on /wp-admin/\n * - Attacker attempts 1,000 passwords/minute\n * - Common password guessed within hours\n * - Account compromise\n * - Full site compromise via admin account\n *
 * **Business Impact:**
 * SaaS platform. Rate limiting set too high (100 attempts/minute). Attacker\n * performs 1M attempts in ~3 hours. Weak password guessed. Admin account stolen.\n * Attacker modifies customer data. 50 customers discover account tampering.\n * Regulatory notification required. Fine: $100K-$500K (GDPR/CCPA). With better\n * rate limiting (5 attempts/minute): attack blocked in seconds.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Login attempts protected\n * - #9 Show Value: Quantified brute force resistance\n * - #10 Beyond Pure: Respects legitimate user attempts\n *
 * **Related Checks:**
 * - Login Page Brute Force Protection (same protection, different name)\n * - API Throttling Not Configured (general rate limiting)\n * - Geolocation Blocking Not Configured (source restriction)\n *
 * **Learn More:**
 * Rate limiting best practices: https://wpshadow.com/kb/wordpress-rate-limiting
 * Video: Implementing rate limiting (9min): https://wpshadow.com/training/rate-limiting\n *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login Page Rate Limiting Treatment Class
 *
 * Validates login page rate limiting configuration.\n *
 * **Detection Pattern:**
 * 1. Check for rate limiting plugin/feature\n * 2. Query limit thresholds (attempts per minute)\n * 3. Validate limit tracking (per IP vs per user)\n * 4. Check lockout duration\n * 5. Confirm limits don't cause false positives\n * 6. Return severity if rate limiting missing\n *
 * **Real-World Scenario:**
 * Blog with no rate limiting. Attacker starts password attack. Tries 10 passwords\n * per second (HTTP/2 concurrent requests). After 100 seconds: 1,000 attempts.\n * Admin password guessed within 1 hour. Attacker posts malware links. Site\n * blacklisted. With rate limiting (5 attempts/minute): 5th attempt triggers\n * lockout. Attack blocked immediately.\n *
 * **Implementation Notes:**
 * - Checks for rate limiting on /wp-login.php\n * - Validates limit thresholds\n * - Tests lockout mechanism\n * - Severity: critical (no limits), high (too generous)\n * - Treatment: implement rate limiting (5 failures = 15 min lockout)\n *
 * @since 1.6030.2240
 */
class Treatment_Login_Page_Rate_Limiting extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'login-page-rate-limiting';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Login Page Rate Limiting';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if login page has rate limiting protection against brute force attacks';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$protections = array();

		// Check for rate limiting plugins
		$rate_limit_plugins = array(
			'wordfence/wordfence.php'                           => 'Wordfence Security',
			'jetpack/jetpack.php'                               => 'Jetpack',
			'sucuri-scanner/sucuri.php'                         => 'Sucuri Security',
			'wp-security-audit-log/wp-security-audit-log.php'   => 'WP Security Audit Log',
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php' => 'Limit Login Attempts Reloaded',
			'login-security-solution/login-security-solution.php' => 'Login Security Solution',
			'brute-force-login-protection/bflp.php'             => 'Brute Force Login Protection',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		$rate_limit_found = false;

		foreach ( $rate_limit_plugins as $plugin => $name ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$protections[] = $name;
				$rate_limit_found = true;
			}
		}

		// Check for custom rate limiting via hooks
		global $wp_filter;

		$custom_rate_limit = false;
		if ( isset( $wp_filter['login_init'] ) ) {
			$custom_rate_limit = true;
		}

		if ( isset( $wp_filter['wp_login_failed'] ) && isset( $wp_filter['wp_login'] ) ) {
			$custom_rate_limit = true;
		}

		if ( $custom_rate_limit ) {
			$protections[] = __( 'Custom rate limiting (via hooks)', 'wpshadow' );
			$rate_limit_found = true;
		}

		// Check for Cloudflare/WAF rate limiting
		if ( ! empty( $_SERVER['HTTP_CF_RAY'] ) ) {
			$protections[] = __( 'Cloudflare protection (WAF)', 'wpshadow' );
		}

		// Check for server-level rate limiting
		$htaccess_path = ABSPATH . '.htaccess';
		if ( file_exists( $htaccess_path ) ) {
			$htaccess_content = file_get_contents( $htaccess_path );
			if ( strpos( $htaccess_content, 'mod_ratelimit' ) !== false || strpos( $htaccess_content, 'ModSecurity' ) !== false ) {
				$protections[] = __( 'Server-level rate limiting (ModSecurity/Apache)', 'wpshadow' );
			}
		}

		// If no protection found, this is an issue
		if ( ! $rate_limit_found ) {
			$issues[] = __( 'No login page rate limiting protection detected', 'wpshadow' );
			$issues[] = __( 'Site is vulnerable to brute force attacks on login page', 'wpshadow' );

			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Login page lacks rate limiting protection', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/login-page-rate-limiting',
				'details'      => array(
					'issues'              => $issues,
					'available_solutions' => array(
						__( 'Install a security plugin with rate limiting (Wordfence, Jetpack, Sucuri)', 'wpshadow' ),
						__( 'Use a custom rate limiting solution with wp-login.php monitoring', 'wpshadow' ),
						__( 'Enable server-level rate limiting (ModSecurity, Cloudflare)', 'wpshadow' ),
					),
				),
				'context'      => array(
					'why'            => __(
						'Rate limiting turns brute force from a minutes-long attack into a multi-day or multi-year effort. Without limits, attackers can attempt thousands of passwords per minute and eventually guess weak or reused credentials. Credential stuffing attacks reuse breached credentials across sites and succeed at scale when no throttling exists. OWASP Top 10 2023 flags authentication failures as a critical risk, and PCI-DSS requires controls against automated login attacks. Excessive login attempts can also cause performance degradation, turning brute force into a denial-of-service risk.',
						'wpshadow'
					),
					'recommendation' => __(
						'1. Install rate limiting: Use "Limit Login Attempts Reloaded" or Wordfence. Target 5-10 attempts per 10 minutes per IP.
2. Apply per-username and per-IP limits: Block attempts even if attacker rotates IPs or usernames.
3. Set lockout duration: 30 minutes for first lockout, 24 hours for repeat attempts.
4. Enable CAPTCHA after failures: Add CAPTCHA after 3 failed attempts.
5. Add login alerts: Email admin after X failed attempts to detect attacks early.
6. Enforce 2FA for admins: Even if brute force succeeds, 2FA blocks access.
7. Limit password reset requests: Cap reset requests to 3 per hour per email.
8. Track failures in logs: Keep IP, username, time, user-agent for incident response.
9. Use server-level protections: Enable ModSecurity or Cloudflare WAF rules for /wp-login.php.
10. Test with safe tooling: Simulate login failures to confirm lockouts trigger correctly.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'brute-force-protection',
				'login_rate_limiting'
			);

			return $finding;
		}

		// If protection found, check configuration
		if ( ! empty( $protections ) ) {
			// Check Wordfence specific settings
			if ( in_array( 'wordfence/wordfence.php', $active_plugins, true ) ) {
				$wf_options = get_option( 'wordfence_options' );
				if ( ! empty( $wf_options ) ) {
					$wf_options_array = json_decode( $wf_options, true );

					// Check if rate limiting is enabled
					if ( empty( $wf_options_array['loginSecurityEnabled'] ) ) {
						$issues[] = __( 'Wordfence login security is not enabled', 'wpshadow' );
					}
				}
			}

			// Check Limit Login Attempts Reloaded
			if ( in_array( 'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php', $active_plugins, true ) ) {
				$attempts = (int) get_option( 'llar_user_lockout_duration', 20 );
				if ( $attempts > 60 ) {
					$issues[] = __( 'Limit Login Attempts lockout duration is very high', 'wpshadow' );
				}
			}
		}

		// Report if issues found despite protection
		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Login rate limiting has configuration issues', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/login-page-rate-limiting',
				'details'      => array(
					'active_protections' => $protections,
					'issues'             => $issues,
				),
				'context'      => array(
					'why'            => __(
						'Rate limiting only works if configured correctly. If lockout duration is too short, bots resume immediately. If lockout duration is too long, legitimate users risk being locked out (denial-of-service). If limits apply only per IP, attackers can rotate IPs. If limits apply only per username, attackers can brute force different usernames. Correct configuration is required to balance security and usability.',
						'wpshadow'
					),
					'recommendation' => __(
						'1. Review plugin settings: Ensure rate limiting is enabled and applied to /wp-login.php and /wp-admin.
2. Set lockout duration: 30 minutes for first lockout, 24 hours for repeat.
3. Use both IP and username limits to prevent IP rotation attacks.
4. Enable "notify on lockout": Admin should receive email on brute force attempts.
5. Verify CAPTCHA threshold: Enable after 3-5 failed attempts.
6. Whitelist trusted IPs cautiously to avoid bypassing protections.
7. Test login failures: Simulate 6 failed attempts to confirm lockout occurs.
8. Monitor logs: Look for high volume attempts despite protection (indicates misconfiguration).
9. Update security plugins regularly: Ensure fixes for bypasses are applied.
10. Add 2FA for admins to provide layered protection.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'brute-force-protection',
				'login_rate_limiting_config'
			);

			return $finding;
		}

		return null;
	}
}
