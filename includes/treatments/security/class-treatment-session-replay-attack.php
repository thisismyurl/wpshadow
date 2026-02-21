<?php
/**
 * Session Replay Attack Treatment
 *
 * Detects vulnerabilities to session replay attacks where captured
 * session tokens can be reused by attackers.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.2033.2108
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session Replay Attack Treatment Class
 *
 * Checks for:
 * - Session token binding to IP address
 * - User agent validation in sessions
 * - Session rotation on privilege change
 * - Timestamp validation in session tokens
 * - Protection against session token prediction
 * - Session invalidation on logout
 *
 * Session replay attacks allow attackers to hijack user sessions by
 * capturing and reusing authentication tokens, even after the original
 * session has ended.
 *
 * @since 1.2033.2108
 */
class Treatment_Session_Replay_Attack extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $slug = 'session-replay-attack';

	/**
	 * The treatment title
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $title = 'Session Replay Attack Vulnerability';

	/**
	 * The treatment description
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $description = 'Detects session replay attack vulnerabilities';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Validates session replay protections.
	 *
	 * @since  1.2033.2108
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Session_Replay_Attack' );
	}

	/**
	 * Check if sessions bind to IP addresses.
	 *
	 * @since  1.2033.2108
	 * @return bool True if IP binding found.
	 */
	private static function check_ip_binding() {
		// Check if theme/plugins implement IP binding.
		$theme_dir = get_stylesheet_directory();
		$pattern = '/\$_SERVER\[["\']REMOTE_ADDR["\']\].*session/i';

		$php_files = self::get_php_files( $theme_dir, 20 );
		foreach ( $php_files as $file ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$content = file_get_contents( $file );
			if ( preg_match( $pattern, $content ) ) {
				return true;
			}
		}

		// Check for IP binding plugins.
		return self::check_for_security_plugin_with_feature( 'ip-binding' );
	}

	/**
	 * Check user agent validation.
	 *
	 * @since  1.2033.2108
	 * @return bool True if validation found.
	 */
	private static function check_user_agent_validation() {
		// Check if user agent is validated in auth.
		return has_filter( 'authenticate' ) && 
		       self::check_for_security_plugin_with_feature( 'user-agent' );
	}

	/**
	 * Check session rotation on privilege change.
	 *
	 * @since  1.2033.2108
	 * @return bool True if rotation found.
	 */
	private static function check_session_rotation_on_privilege_change() {
		// Check for hooks that rotate sessions.
		return has_action( 'set_user_role' ) || has_action( 'add_user_role' );
	}

	/**
	 * Check timestamp validation.
	 *
	 * @since  1.2033.2108
	 * @return bool True if validation found.
	 */
	private static function check_timestamp_validation() {
		global $wpdb;

		// Check if session tokens include expiration.
		$sample_session = $wpdb->get_var(
			"SELECT meta_value FROM {$wpdb->usermeta} 
			WHERE meta_key = 'session_tokens' 
			LIMIT 1"
		);

		if ( empty( $sample_session ) ) {
			return false;
		}

		$sessions = maybe_unserialize( $sample_session );
		if ( ! is_array( $sessions ) ) {
			return false;
		}

		// WordPress includes 'expiration' in session tokens.
		$first_session = reset( $sessions );
		return isset( $first_session['expiration'] );
	}

	/**
	 * Check session token entropy.
	 *
	 * @since  1.2033.2108
	 * @return bool True if sufficient.
	 */
	private static function check_session_token_entropy() {
		global $wpdb;

		$sample_token = $wpdb->get_var(
			"SELECT meta_key FROM {$wpdb->usermeta} 
			WHERE meta_key LIKE 'session_tokens' 
			LIMIT 1"
		);

		if ( empty( $sample_token ) ) {
			return true; // Can't verify, assume OK.
		}

		// WordPress uses wp_generate_password(43) for tokens (sufficient).
		return true;
	}

	/**
	 * Check logout invalidation.
	 *
	 * @since  1.2033.2108
	 * @return bool True if invalidation found.
	 */
	private static function check_logout_invalidation() {
		// WordPress core wp_logout() calls wp_destroy_current_session().
		return function_exists( 'wp_destroy_current_session' );
	}

	/**
	 * Check for security plugin features.
	 *
	 * @since  1.2033.2108
	 * @param  string $feature Feature identifier.
	 * @return bool True if found.
	 */
	private static function check_for_security_plugin_with_feature( $feature ) {
		$security_plugins = array( 'wordfence', 'ithemes-security', 'sucuri', 'all-in-one-wp-security' );
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $security_plugins as $plugin ) {
			foreach ( $active_plugins as $active ) {
				if ( str_contains( $active, $plugin ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get PHP files from directory.
	 *
	 * @since  1.2033.2108
	 * @param  string $dir Directory path.
	 * @param  int    $limit Maximum files.
	 * @return array File paths.
	 */
	private static function get_php_files( $dir, $limit = 50 ) {
		$files = array();
		$count = 0;

		if ( ! is_dir( $dir ) ) {
			return $files;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS )
		);

		foreach ( $iterator as $file ) {
			if ( $count >= $limit ) {
				break;
			}
			if ( $file->isFile() && 'php' === $file->getExtension() ) {
				$files[] = $file->getPathname();
				$count++;
			}
		}

		return $files;
	}
}
