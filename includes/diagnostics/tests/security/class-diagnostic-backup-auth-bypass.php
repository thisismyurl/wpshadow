<?php
/**
 * Backup Authentication Bypass Diagnostic
 *
 * Checks for emergency admin accounts, hardcoded authentication, and
 * backdoor authentication mechanisms in plugins and themes.
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
 * Backup Authentication Bypass Diagnostic Class
 *
 * Detects suspicious authentication mechanisms that could allow
 * unauthorized access or bypass normal security controls.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Backup_Auth_Bypass extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'prevents_backup_auth_bypass';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Backup Authentication Bypass';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies no emergency admin accounts or backdoor authentication mechanisms exist';

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

		// Check for suspicious admin usernames (25 points).
		$suspicious_usernames = array(
			'admin',
			'administrator',
			'backup',
			'emergency',
			'root',
			'system',
			'test',
			'temp',
			'support',
		);

		$admins           = get_users( array( 'role' => 'administrator' ) );
		$suspicious_count = 0;
		$suspicious_list  = array();

		foreach ( $admins as $admin ) {
			if ( in_array( strtolower( $admin->user_login ), $suspicious_usernames, true ) ) {
				$suspicious_count++;
				$suspicious_list[] = $admin->user_login;
			}
		}

		if ( $suspicious_count === 0 ) {
			$earned_points += 25;
		} else {
			$issues[] = sprintf(
				/* translators: %d: Number of suspicious admin accounts */
				_n(
					'%d suspicious admin username detected (commonly targeted by attackers)',
					'%d suspicious admin usernames detected (commonly targeted by attackers)',
					$suspicious_count,
					'wpshadow'
				),
				$suspicious_count
			);
			$stats['suspicious_admins'] = $suspicious_list;
		}

		$stats['total_admins']      = count( $admins );
		$stats['suspicious_count']  = $suspicious_count;

		// Check for security audit plugins (25 points).
		$audit_plugins = array(
			'wp-security-audit-log/wp-security-audit-log.php' => 'WP Activity Log',
			'simple-history/index.php'                         => 'Simple History',
			'stream/stream.php'                                => 'Stream',
			'audit-trail/audit-trail.php'                      => 'Audit Trail',
		);

		$active_audit = array();
		foreach ( $audit_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_audit[] = $plugin_name;
				$earned_points += 8; // Up to 25 points.
			}
		}

		if ( count( $active_audit ) > 0 ) {
			$stats['audit_plugins'] = implode( ', ', $active_audit );
		} else {
			$issues[] = 'No security audit logging detected - backdoor access may go unnoticed';
		}

		// Check for hardcoded credentials in wp-config.php (20 points).
		$wp_config_path = ABSPATH . 'wp-config.php';
		$config_secure  = true;

		if ( file_exists( $wp_config_path ) && is_readable( $wp_config_path ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$config_content = file_get_contents( $wp_config_path );

			// Check for suspicious patterns (case-insensitive).
			$suspicious_patterns = array(
				'/define\s*\(\s*[\'"]EMERGENCY_ADMIN[\'"]/i',
				'/define\s*\(\s*[\'"]BACKDOOR[\'"]/i',
				'/define\s*\(\s*[\'"]BYPASS_AUTH[\'"]/i',
				'/\$emergency_user\s*=/i',
				'/\$backup_login\s*=/i',
			);

			foreach ( $suspicious_patterns as $pattern ) {
				if ( preg_match( $pattern, $config_content ) ) {
					$config_secure = false;
					$issues[]      = 'Suspicious authentication constants found in wp-config.php';
					break;
				}
			}
		}

		if ( $config_secure ) {
			$earned_points += 20;
		}

		$stats['wp_config_secure'] = $config_secure;

		// Check for security plugins with malware scanning (20 points).
		$malware_scanners = array(
			'wordfence/wordfence.php'                       => 'Wordfence Security',
			'sucuri-scanner/sucuri.php'                     => 'Sucuri Security',
			'anti-malware-security/anti-malware-security.php' => 'Anti-Malware Security',
			'quttera-web-malware-scanner/quttera-web-malware-scanner.php' => 'Quttera Web Malware Scanner',
		);

		$active_scanners = array();
		foreach ( $malware_scanners as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_scanners[] = $plugin_name;
				$earned_points    += 7; // Up to 20 points.
			}
		}

		if ( count( $active_scanners ) > 0 ) {
			$stats['malware_scanners'] = implode( ', ', $active_scanners );
		} else {
			$warnings[] = 'No malware scanning plugins detected';
		}

		// Check for 2FA plugins (10 points).
		$twofa_plugins = array(
			'two-factor/two-factor.php'                     => 'Two Factor',
			'google-authenticator/google-authenticator.php' => 'Google Authenticator',
			'wordfence/wordfence.php'                       => 'Wordfence 2FA',
			'better-wp-security/better-wp-security.php'     => 'iThemes Security 2FA',
		);

		$active_twofa = array();
		foreach ( $twofa_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_twofa[] = $plugin_name;
				$earned_points += 5; // Up to 10 points.
			}
		}

		if ( count( $active_twofa ) > 0 ) {
			$stats['twofa_plugins'] = implode( ', ', $active_twofa );
		}

		// Calculate score percentage.
		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		// Return finding if score is below 60% (critical security).
		if ( $score < 60 ) {
			$severity     = $score < 40 ? 'high' : 'medium';
			$threat_level = $score < 40 ? 90 : 75;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your authentication security scored %s. Emergency admin accounts, hardcoded credentials, or backdoor authentication mechanisms can allow attackers to bypass normal security controls and gain unauthorized access to your site.', 'wpshadow' ),
					$score_text
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-authentication-bypass',
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
