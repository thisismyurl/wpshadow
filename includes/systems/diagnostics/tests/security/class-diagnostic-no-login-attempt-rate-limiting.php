<?php
/**
 * No Login Attempt Rate Limiting Diagnostic
 *
 * Detects when login rate limiting is not implemented,
 * allowing brute force password attacks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Login Attempt Rate Limiting
 *
 * Checks whether login rate limiting is implemented
 * to prevent brute force attacks.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Login_Attempt_Rate_Limiting extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-login-rate-limiting';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Login Attempt Rate Limiting';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether login rate limiting is enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for rate limiting plugins
		$has_rate_limiting = is_plugin_active( 'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php' ) ||
			is_plugin_active( 'wordfence-security/wordfence.php' ) ||
			is_plugin_active( 'jetpack/jetpack.php' );

		if ( ! $has_rate_limiting ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Login rate limiting isn\'t enabled, which means attackers can try unlimited passwords. Brute force attacks try thousands of password combinations per minute until they find one that works. Rate limiting blocks this by: limiting attempts (e.g., 5 tries per 15 minutes), locking out IPs after too many failures, requiring CAPTCHA after failures. Without rate limiting, attackers can compromise weak passwords in minutes.',
					'wpshadow'
				),
				'severity'      => 'critical',
				'threat_level'  => 85,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Brute Force Protection',
					'potential_gain' => 'Block 99%+ of brute force attacks',
					'roi_explanation' => 'Rate limiting prevents automated password attacks, blocking 99%+ of brute force attempts with minimal effort.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/login-rate-limiting',
			);
		}

		return null;
	}
}
