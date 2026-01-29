<?php
/**
 * Authentication Log Pattern Analysis Diagnostic
 *
 * Analyzes login attempts for brute force indicators and anomalies.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Authentication Log Pattern Analysis Class
 *
 * Tests login patterns.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Authentication_Log_Pattern_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'authentication-log-pattern-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Authentication Log Pattern Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes login attempts for brute force indicators and anomalies';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$log_check = self::check_authentication_logs();
		
		if ( $log_check['has_suspicious_activity'] ) {
			$issues = array();
			
			if ( $log_check['excessive_failures'] > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of failed attempts */
					__( '%d excessive failed login attempts detected', 'wpshadow' ),
					$log_check['excessive_failures']
				);
			}

			if ( $log_check['dictionary_attacks'] > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of dictionary attacks */
					__( '%d potential dictionary attack patterns found', 'wpshadow' ),
					$log_check['dictionary_attacks']
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/authentication-log-pattern-analysis',
				'meta'         => array(
					'excessive_failures'  => $log_check['excessive_failures'],
					'dictionary_attacks'  => $log_check['dictionary_attacks'],
					'common_usernames'    => $log_check['common_usernames'],
				),
			);
		}

		return null;
	}

	/**
	 * Check authentication logs.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_authentication_logs() {
		$check = array(
			'has_suspicious_activity' => false,
			'excessive_failures'      => 0,
			'dictionary_attacks'      => 0,
			'common_usernames'        => array(),
		);

		// Check for Limit Login Attempts plugin data.
		$lockout_data = get_option( 'limit_login_lockouts', array() );
		
		if ( ! empty( $lockout_data ) && is_array( $lockout_data ) ) {
			$check['excessive_failures'] = count( $lockout_data );
			
			if ( $check['excessive_failures'] > 0 ) {
				$check['has_suspicious_activity'] = true;
			}
		}

		// Check for common dictionary usernames.
		$dictionary_usernames = array( 'admin', 'administrator', 'test', 'user', 'root' );
		
		foreach ( $dictionary_usernames as $username ) {
			$user = get_user_by( 'login', $username );
			
			if ( $user ) {
				$check['common_usernames'][] = $username;
				++$check['dictionary_attacks'];
				$check['has_suspicious_activity'] = true;
			}
		}

		return $check;
	}
}
