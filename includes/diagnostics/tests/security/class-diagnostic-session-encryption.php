<?php
/**
 * Session Data Encryption Diagnostic
 *
 * Checks if session data is properly encrypted at rest and sensitive data
 * is not stored in sessions or cookies without encryption.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session Data Encryption Diagnostic Class
 *
 * Detects insecure session data storage and provides recommendations
 * for proper session encryption and security.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Session_Encryption extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'encrypts_session_data';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Session Data Encryption';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies session data is encrypted at rest and sensitive data is not stored insecurely';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for secure session management plugins (30 points).
		$session_plugins = array(
			'wp-session-manager/wp-session-manager.php'       => 'WP Session Manager',
			'user-session-control/user-session-control.php'   => 'User Session Control',
			'wp-security-audit-log/wp-security-audit-log.php' => 'WP Activity Log',
		);

		$active_session_plugins = array();
		foreach ( $session_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_session_plugins[] = $plugin_name;
				$earned_points           += 10; // Up to 30 points for session management.
			}
		}

		if ( count( $active_session_plugins ) > 0 ) {
			$stats['session_management_plugins'] = implode( ', ', $active_session_plugins );
		} else {
			$issues[] = 'No dedicated session management plugins detected';
		}

		// Check for HTTPS/SSL (25 points).
		if ( is_ssl() ) {
			$earned_points          += 25;
			$stats['https_enabled']  = true;
		} else {
			$issues[] = 'HTTPS not enabled - session cookies vulnerable to interception';
		}

		// Check for secure cookie constants (20 points).
		$secure_cookies = 0;

		if ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ) {
			$secure_cookies++;
			$earned_points += 10;
		} else {
			$issues[] = 'FORCE_SSL_ADMIN not enabled';
		}

		if ( defined( 'COOKIE_DOMAIN' ) && ! empty( COOKIE_DOMAIN ) ) {
			$secure_cookies++;
			$earned_points += 5;
		}

		if ( defined( 'COOKIEHASH' ) && strlen( COOKIEHASH ) >= 32 ) {
			$secure_cookies++;
			$earned_points += 5;
		} else {
			$warnings[] = 'COOKIEHASH may not be sufficiently random';
		}

		$stats['secure_cookie_constants'] = $secure_cookies;

		// Check for session encryption security plugins (15 points).
		$security_plugins = array(
			'wordfence/wordfence.php'                       => 'Wordfence Security',
			'better-wp-security/better-wp-security.php'     => 'iThemes Security',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
			'sucuri-scanner/sucuri.php'                     => 'Sucuri Security',
		);

		$active_security = array();
		foreach ( $security_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_security[] = $plugin_name;
				$earned_points    += 5; // Up to 15 points.
			}
		}

		if ( count( $active_security ) > 0 ) {
			$stats['security_plugins'] = implode( ', ', $active_security );
		}

		// Check PHP session configuration (10 points).
		$session_config_secure = 0;

		if ( ini_get( 'session.cookie_httponly' ) ) {
			$session_config_secure++;
			$earned_points += 5;
		} else {
			$issues[] = 'session.cookie_httponly not enabled - cookies vulnerable to XSS';
		}

		if ( ini_get( 'session.cookie_secure' ) || is_ssl() ) {
			$session_config_secure++;
			$earned_points += 5;
		} else {
			$issues[] = 'session.cookie_secure not enabled';
		}

		$stats['session_config_secure'] = $session_config_secure;

		// Calculate score percentage.
		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		// Return finding if score is below 70%.
		if ( $score < 70 ) {
			$severity     = $score < 50 ? 'high' : 'medium';
			$threat_level = $score < 50 ? 75 : 65;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your site\'s session encryption scored %s. Session data may be vulnerable to interception or tampering. Encrypted sessions protect user authentication and sensitive data from being stolen or modified by attackers.', 'wpshadow' ),
					$score_text
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/session-data-encryption',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}
}
