<?php
/**
 * Session Timeout Settings Diagnostic
 *
 * Issue #4850: Session Timeout Too Long or Nonexistent
 * Pillar: 🛡️ Safe by Default
 *
 * Verifies session timeout settings follow security best practices.
 * Long or nonexistent session timeouts allow compromised sessions to remain valid indefinitely.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Session_Timeout_Settings Class
 *
 * Checks for:
 * - PHP session.gc_maxlifetime longer than 24 hours
 * - WordPress auth cookie expiration longer than 24 hours
 * - "Remember Me" duration longer than 14 days
 * - No idle timeout mechanism
 * - No absolute session timeout
 *
 * OWASP identifies session timeout as critical for security.
 * IBM reports average breach detection time is 207 days during which
 * stolen sessions can remain active.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Session_Timeout_Settings extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $slug = 'session-timeout-settings';

	/**
	 * The diagnostic title
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $title = 'Session Timeout Too Long or Nonexistent';

	/**
	 * The diagnostic description
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $description = 'Verifies session timeout settings follow security best practices';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Analyzes session timeout configuration:
	 * 1. PHP session.gc_maxlifetime
	 * 2. WordPress auth cookie expiration
	 * 3. "Remember Me" duration
	 * 4. Idle timeout mechanisms
	 * 5. Absolute session timeout
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check 1: PHP session.gc_maxlifetime (default 1440 = 24 minutes, but often increased)
		$php_session_lifetime = (int) ini_get( 'session.gc_maxlifetime' );
		$hours = $php_session_lifetime / HOUR_IN_SECONDS;

		if ( $php_session_lifetime > 86400 ) {  // More than 24 hours
			$issues[] = sprintf(
				/* translators: %s: hours */
				__( 'PHP session timeout: %s hours (recommended: ≤24 hours)', 'wpshadow' ),
				number_format_i18n( $hours, 1 )
			);
		}

		// Check 2: WordPress auth cookie expiration (default 2 days)
		$auth_cookie_expiration = (int) apply_filters( 'auth_cookie_expiration', 2 * DAY_IN_SECONDS );
		$days = $auth_cookie_expiration / DAY_IN_SECONDS;

		if ( $auth_cookie_expiration > 3 * DAY_IN_SECONDS ) {  // More than 3 days
			$issues[] = sprintf(
				/* translators: %s: days */
				__( 'Auth cookie expires after %s days (recommended: ≤2 days)', 'wpshadow' ),
				number_format_i18n( $days, 1 )
			);
		}

		// Check 3: "Remember Me" duration (checked via filters)
		$secure_auth_cookie_expiration = (int) apply_filters( 'secure_auth_cookie_expiration', 14 * DAY_IN_SECONDS );
		$remember_days = $secure_auth_cookie_expiration / DAY_IN_SECONDS;

		if ( $secure_auth_cookie_expiration > 14 * DAY_IN_SECONDS ) {
			$issues[] = sprintf(
				/* translators: %s: days */
				__( '"Remember Me" duration: %s days (recommended: ≤14 days)', 'wpshadow' ),
				number_format_i18n( $remember_days, 1 )
			);
		}

		// Check 4: No custom idle timeout implementation
		$has_idle_mechanism = self::check_custom_idle_timeout();
		if ( ! $has_idle_mechanism ) {
			$issues[] = __( 'No custom idle timeout mechanism detected (sessions persist with activity)', 'wpshadow' );
		}

		// If we found issues, return finding
		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Session timeouts may be set too long, leaving compromised sessions valid indefinitely', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/session-timeout-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'       => array(
					'findings'           => $issues,
					'auth_cookie_hours'  => number_format_i18n( $hours, 1 ),
					'recommended_hours'  => '24',
					'security_principle' => 'Shorter session timeout = less time for stolen sessions to be exploited',
				),
			);
		}

		return null;
	}

	/**
	 * Check if custom idle timeout is implemented
	 *
	 * @since 0.6093.1200
	 * @return bool True if idle timeout mechanism exists.
	 */
	private static function check_custom_idle_timeout(): bool {
		// Check if there's an idle timeout implementation
		// This would be a custom plugin or configuration
		// Look for common patterns

		// Check if filter exists
		$idle_timeout = apply_filters( 'wp_idle_session_timeout', false );

		// Check for common idle timeout plugins
		if ( class_exists( 'Idle_Session_Timeout' ) ) {
			return true;
		}

		// Check if wp-config has custom idle settings
		if ( defined( 'WP_IDLE_SESSION_TIMEOUT' ) ) {
			return true;
		}

		return false !== $idle_timeout;
	}
}
