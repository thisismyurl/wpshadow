<?php
/**
 * Diagnostic: Backup Authentication Bypass
 *
 * Checks for emergency admin accounts, hardcoded authentication in plugins/themes,
 * and backdoor authentication mechanisms.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4010
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6034.1440
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backup Authentication Bypass Diagnostic
 *
 * Detects emergency admin accounts, hardcoded credentials, and authentication
 * backdoors in plugins/themes.
 *
 * @since 1.6034.1440
 */
class Diagnostic_Security_Backup_Authentication_Bypass extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'security-backup-auth-bypass';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Authentication Bypass';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for emergency accounts and hardcoded authentication backdoors';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Check for authentication bypass mechanisms.
	 *
	 * Scans for:
	 * - Suspicious admin accounts (emergency, backup, temp)
	 * - Hardcoded authentication in code
	 * - Authentication filter bypasses
	 *
	 * @since  1.6034.1440
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for suspicious admin usernames.
		$suspicious_names = array(
			'admin',
			'emergency',
			'backup',
			'temp',
			'test',
			'developer',
			'support',
			'emergency_admin',
		);

		foreach ( $suspicious_names as $username ) {
			$user = get_user_by( 'login', $username );
			if ( $user && user_can( $user, 'administrator' ) ) {
				$issues[] = sprintf(
					/* translators: %s: username */
					__( 'Suspicious admin account: "%s"', 'wpshadow' ),
					$username
				);
			}
		}

		// Check for hardcoded authentication in plugins.
		$plugins_dir = WP_PLUGIN_DIR;
		$plugins     = array_slice( scandir( $plugins_dir ), 2 ); // Skip . and ...
		
		$hardcoded_patterns = array(
			'/wp_set_current_user\s*\(\s*1\s*\)/i',  // Force login as user ID 1.
			'/wp_set_auth_cookie\s*\(\s*1\s*\)/i',   // Set auth cookie for user 1.
			'/if\s*\(\s*["\'].*secret.*["\']\s*===\s*\$_GET/i', // Secret URL parameters.
		);

		$files_checked = 0;
		
		foreach ( $plugins as $plugin ) {
			$plugin_path = $plugins_dir . '/' . $plugin;
			if ( ! is_dir( $plugin_path ) || $files_checked > 20 ) {
				continue; // Limit scanning to avoid timeout.
			}
			
			$php_files = glob( $plugin_path . '/*.php' );
			foreach ( $php_files as $file ) {
				if ( $files_checked > 20 ) {
					break;
				}
				
				$content = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				
				foreach ( $hardcoded_patterns as $pattern ) {
					if ( preg_match( $pattern, $content ) ) {
						$issues[] = sprintf(
							/* translators: %s: plugin directory */
							__( 'Hardcoded authentication found in plugin: %s', 'wpshadow' ),
							$plugin
						);
						break 2; // Found issue, move to next plugin.
					}
				}
				
				++$files_checked;
			}
		}

		// Check for authentication bypass filters.
		global $wp_filter;
		
		if ( isset( $wp_filter['authenticate'] ) ) {
			foreach ( $wp_filter['authenticate']->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback ) {
					if ( is_array( $callback['function'] ) && is_string( $callback['function'][0] ) ) {
						// Check for suspicious function names.
						$function_name = $callback['function'][1];
						if ( strpos( $function_name, 'bypass' ) !== false || strpos( $function_name, 'emergency' ) !== false ) {
							$issues[] = sprintf(
								/* translators: %s: function name */
								__( 'Suspicious authentication filter: %s', 'wpshadow' ),
								$function_name
							);
						}
					}
				}
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of issues */
				__(
					'Authentication bypass mechanisms detected: %s. Backup/emergency accounts and hardcoded authentication create severe security risks. Remove these accounts and use proper WordPress authentication flows.',
					'wpshadow'
				),
				implode( '; ', array_slice( $issues, 0, 3 ) )
			),
			'severity'     => 'critical',
			'threat_level' => 85,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/backup-authentication-bypass',
		);
	}
}
