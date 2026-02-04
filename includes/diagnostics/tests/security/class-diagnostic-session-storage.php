<?php
/**
 * Session Storage Security Diagnostic
 *
 * Checks session storage location, file permissions, and potential
 * session data leakage vulnerabilities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6035.1600
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session Storage Security Diagnostic Class
 *
 * Verifies secure session storage configuration including location,
 * permissions, and protection against data leakage.
 *
 * @since 1.6035.1600
 */
class Diagnostic_Session_Storage extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'secures_session_storage';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Session Storage Security';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies session data is stored securely with proper permissions';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1600
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check PHP session configuration (30 points).
		$session_save_path = ini_get( 'session.save_path' );
		$session_handler   = ini_get( 'session.save_handler' );

		$stats['session_save_handler'] = $session_handler;
		$stats['session_save_path']    = $session_save_path;

		// Database storage is more secure than files (30 points).
		if ( 'user' === $session_handler || 'redis' === $session_handler || 'memcached' === $session_handler ) {
			$earned_points += 30;
			$stats['session_storage_type'] = 'secure (database/cache)';
		} elseif ( 'files' === $session_handler ) {
			$earned_points += 15;
			$stats['session_storage_type'] = 'files (less secure)';
			$warnings[] = 'Sessions stored in files - consider using database or Redis for better security';

			// Check file permissions if using files.
			if ( ! empty( $session_save_path ) && is_dir( $session_save_path ) && is_readable( $session_save_path ) ) {
				$perms = fileperms( $session_save_path );
				$octal = substr( sprintf( '%o', $perms ), -4 );

				$stats['session_dir_permissions'] = $octal;

				// Check if directory is world-readable (insecure).
				if ( $perms & 0x0004 ) {
					$issues[] = sprintf(
						/* translators: %s: Permissions in octal */
						__( 'Session directory is world-readable (%s) - potential data leakage', 'wpshadow' ),
						$octal
					);
				} else {
					$earned_points += 5; // Bonus for secure permissions.
				}
			}
		} else {
			$issues[] = 'Unknown session handler: ' . $session_handler;
		}

		// Check for session management plugins (25 points).
		$session_plugins = array(
			'wp-session-manager/wp-session-manager.php'       => 'WP Session Manager',
			'user-session-control/user-session-control.php'   => 'User Session Control',
			'wp-redis/wp-redis.php'                           => 'WP Redis',
			'redis-cache/redis-cache.php'                     => 'Redis Object Cache',
		);

		$active_session = array();
		foreach ( $session_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_session[] = $plugin_name;
				$earned_points   += 8; // Up to 25 points.
			}
		}

		if ( count( $active_session ) > 0 ) {
			$stats['session_management_plugins'] = implode( ', ', $active_session );
		} else {
			$warnings[] = 'No dedicated session management plugins detected';
		}

		// Check for object caching (20 points).
		if ( wp_using_ext_object_cache() ) {
			$earned_points += 20;
			$stats['object_cache_enabled'] = true;
		} else {
			$warnings[] = 'External object cache not enabled';
		}

		// Check for security plugins (15 points).
		$security_plugins = array(
			'wordfence/wordfence.php'                       => 'Wordfence Security',
			'better-wp-security/better-wp-security.php'     => 'iThemes Security',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
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

		// Check for HTTPS (10 points).
		if ( is_ssl() ) {
			$earned_points += 10;
			$stats['https_enabled'] = true;
		} else {
			$issues[] = 'HTTPS not enabled - session data transmitted without encryption';
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
			$threat_level = $score < 50 ? 70 : 60;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your session storage security scored %s. Insecure session storage can expose user data to unauthorized access. File-based sessions with incorrect permissions or storage locations may leak sensitive information.', 'wpshadow' ),
					$score_text
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/session-storage-security',
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
