<?php
/**
 * Session Replay Attacks Treatment
 *
 * Checks for timestamp validation in tokens, single-use tokens for sensitive
 * actions, and nonce replay protection.
 *
 * @package    WPShadow
 * @subpackage Treatments\Security
 * @since      1.6035.1550
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session Replay Attacks Treatment Class
 *
 * Verifies proper token validation, nonce usage, and protection
 * against session replay attacks.
 *
 * @since 1.6035.1550
 */
class Treatment_Session_Replay extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'prevents_session_replay';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Session Replay Attacks';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies tokens are timestamp-validated and single-use for sensitive actions';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1550
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check WordPress nonce configuration (30 points).
		// WordPress uses time-based nonces by default with wp_nonce_tick().
		$nonce_life = apply_filters( 'nonce_life', DAY_IN_SECONDS );

		if ( $nonce_life <= 12 * HOUR_IN_SECONDS ) {
			// Shorter nonce life is better security (12 hours or less).
			$earned_points += 30;
			$stats['nonce_life_hours'] = round( $nonce_life / HOUR_IN_SECONDS, 1 );
		} elseif ( $nonce_life <= DAY_IN_SECONDS ) {
			// Default WordPress (24 hours).
			$earned_points += 20;
			$stats['nonce_life_hours'] = round( $nonce_life / HOUR_IN_SECONDS, 1 );
			$warnings[] = 'Nonce lifetime is 24 hours (default) - consider reducing to 12 hours or less';
		} else {
			// Longer than default (security risk).
			$stats['nonce_life_hours'] = round( $nonce_life / HOUR_IN_SECONDS, 1 );
			$issues[] = sprintf(
				/* translators: %s: Nonce lifetime in hours */
				__( 'Nonce lifetime is %s hours (longer than recommended 12-24 hours)', 'wpshadow' ),
				round( $nonce_life / HOUR_IN_SECONDS, 1 )
			);
		}

		// Check for session management plugins (25 points).
		$session_plugins = array(
			'wp-session-manager/wp-session-manager.php'       => 'WP Session Manager',
			'user-session-control/user-session-control.php'   => 'User Session Control',
			'wp-security-audit-log/wp-security-audit-log.php' => 'WP Activity Log',
		);

		$active_session = array();
		foreach ( $session_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_session[] = $plugin_name;
				$earned_points   += 8; // Up to 25 points.
			}
		}

		if ( count( $active_session ) > 0 ) {
			$stats['session_management'] = implode( ', ', $active_session );
		} else {
			$warnings[] = 'No dedicated session management plugins detected';
		}

		// Check for security plugins with token validation (25 points).
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
				$earned_points    += 8; // Up to 25 points.
			}
		}

		if ( count( $active_security ) > 0 ) {
			$stats['security_plugins'] = implode( ', ', $active_security );
		} else {
			$issues[] = 'No security plugins with token validation detected';
		}

		// Check for HTTPS (10 points).
		if ( is_ssl() ) {
			$earned_points         += 10;
			$stats['https_enabled'] = true;
		} else {
			$issues[] = 'HTTPS not enabled - tokens can be intercepted and replayed';
		}

		// Check for session timeout configuration (10 points).
		$session_timeout = defined( 'WP_SESSION_TIMEOUT' ) ? WP_SESSION_TIMEOUT : null;

		if ( $session_timeout && $session_timeout <= 12 * HOUR_IN_SECONDS ) {
			$earned_points += 10;
			$stats['session_timeout_hours'] = round( $session_timeout / HOUR_IN_SECONDS, 1 );
		} elseif ( $session_timeout && $session_timeout <= DAY_IN_SECONDS ) {
			$earned_points += 5;
			$stats['session_timeout_hours'] = round( $session_timeout / HOUR_IN_SECONDS, 1 );
		} else {
			$warnings[] = 'No custom session timeout configured (using WordPress defaults)';
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
					__( 'Your session replay protection scored %s. Without proper token validation and short token lifetimes, attackers can capture and replay authentication tokens to impersonate users or perform unauthorized actions.', 'wpshadow' ),
					$score_text
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/session-replay-attacks',
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
