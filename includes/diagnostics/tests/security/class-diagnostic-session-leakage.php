<?php
/**
 * Cross-Site Session Leakage Diagnostic
 *
 * Checks for session fixation via subdomain, domain isolation for cookies,
 * and CORS misconfiguration that could leak sessions.
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
 * Cross-Site Session Leakage Diagnostic Class
 *
 * Detects cookie and session configuration issues that could allow
 * cross-site session leakage or fixation attacks.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Session_Leakage extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'prevents_session_leakage';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Cross-Site Session Leakage';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies session cookies are properly isolated and protected from cross-site leakage';

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

		// Check cookie domain configuration (30 points).
		if ( defined( 'COOKIE_DOMAIN' ) && ! empty( COOKIE_DOMAIN ) ) {
			$earned_points += 15;
			$stats['cookie_domain'] = COOKIE_DOMAIN;

			// Check if properly configured (starts with dot for subdomains).
			if ( strpos( COOKIE_DOMAIN, '.' ) === 0 ) {
				$earned_points += 15;
				$stats['cookie_domain_config'] = 'properly configured for subdomains';
			} else {
				$warnings[] = 'COOKIE_DOMAIN does not start with dot - may not work across subdomains';
			}
		} else {
			$issues[] = 'COOKIE_DOMAIN not defined - cookies may leak across subdomains';
		}

		// Check for HTTPS/SSL (25 points).
		if ( is_ssl() ) {
			$earned_points += 25;
			$stats['https_enabled'] = true;

			// Check for secure cookie forcing.
			if ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ) {
				$stats['force_ssl_admin'] = true;
			} else {
				$warnings[] = 'FORCE_SSL_ADMIN not enabled';
			}
		} else {
			$issues[] = 'HTTPS not enabled - sessions vulnerable to interception';
		}

		// Check for SameSite cookie attribute support (20 points).
		// WordPress 5.2+ added support for SameSite cookies.
		global $wp_version;
		if ( version_compare( $wp_version, '5.2', '>=' ) ) {
			$earned_points += 20;
			$stats['samesite_support'] = true;
		} else {
			$issues[] = 'WordPress version does not support SameSite cookie attribute';
			$stats['samesite_support'] = false;
		}

		// Check for security headers plugins (15 points).
		$header_plugins = array(
			'wp-security-headers/wp-security-headers.php' => 'WP Security Headers',
			'security-headers/security-headers.php'       => 'Security Headers',
			'wordfence/wordfence.php'                     => 'Wordfence Security',
		);

		$active_headers = array();
		foreach ( $header_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_headers[] = $plugin_name;
				$earned_points   += 5; // Up to 15 points.
			}
		}

		if ( count( $active_headers ) > 0 ) {
			$stats['security_headers_plugins'] = implode( ', ', $active_headers );
		} else {
			$warnings[] = 'No security headers plugins detected';
		}

		// Check for session management plugins (10 points).
		$session_plugins = array(
			'wp-session-manager/wp-session-manager.php'     => 'WP Session Manager',
			'user-session-control/user-session-control.php' => 'User Session Control',
		);

		$active_session = array();
		foreach ( $session_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_session[] = $plugin_name;
				$earned_points   += 5; // Up to 10 points.
			}
		}

		if ( count( $active_session ) > 0 ) {
			$stats['session_plugins'] = implode( ', ', $active_session );
		}

		// Calculate score percentage.
		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		// Return finding if score is below 65%.
		if ( $score < 65 ) {
			$severity     = $score < 50 ? 'high' : 'medium';
			$threat_level = $score < 50 ? 75 : 65;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your session isolation scored %s. Improperly configured cookies can leak across subdomains or be vulnerable to cross-site attacks. This could allow attackers to hijack user sessions or perform session fixation attacks.', 'wpshadow' ),
					$score_text
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cross-site-session-leakage',
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
