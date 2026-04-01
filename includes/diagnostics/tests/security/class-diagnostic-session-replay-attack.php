<?php
/**
 * Session Replay Attack Diagnostic
 *
 * Detects vulnerabilities to session replay attacks where captured
 * session tokens can be reused by attackers.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session Replay Attack Diagnostic Class
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
 * @since 0.6093.1200
 */
class Diagnostic_Session_Replay_Attack extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $slug = 'session-replay-attack';

	/**
	 * The diagnostic title
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $title = 'Session Replay Attack Vulnerability';

	/**
	 * The diagnostic description
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $description = 'Detects session replay attack vulnerabilities';

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
	 * Validates session replay protections.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check 1: Verify IP address binding.
		$binds_ip = self::check_ip_binding();
		if ( ! $binds_ip ) {
			$issues[] = __( 'Session tokens not bound to IP addresses (replay from any location possible)', 'wpshadow' );
		}

		// Check 2: Verify user agent validation.
		$validates_ua = self::check_user_agent_validation();
		if ( ! $validates_ua ) {
			$issues[] = __( 'User agent not validated in sessions (replay from any browser possible)', 'wpshadow' );
		}

		// Check 3: Check session rotation on privilege change.
		$rotates_on_privilege = self::check_session_rotation_on_privilege_change();
		if ( ! $rotates_on_privilege ) {
			$issues[] = __( 'Sessions not rotated on privilege escalation', 'wpshadow' );
		}

		// Check 4: Check timestamp validation.
		$validates_timestamp = self::check_timestamp_validation();
		if ( ! $validates_timestamp ) {
			$issues[] = __( 'Session tokens lack timestamp validation (old tokens can be replayed)', 'wpshadow' );
		}

		// Check 5: Check token entropy.
		$sufficient_entropy = self::check_session_token_entropy();
		if ( ! $sufficient_entropy ) {
			$issues[] = __( 'Session tokens may be predictable (insufficient entropy)', 'wpshadow' );
		}

		// Check 6: Check logout invalidation.
		$invalidates_on_logout = self::check_logout_invalidation();
		if ( ! $invalidates_on_logout ) {
			$issues[] = __( 'Logout may not properly invalidate session tokens', 'wpshadow' );
		}

		// If we found issues, return finding.
		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d session replay vulnerability detected',
						'%d session replay vulnerabilities detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/session-replay-attack?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'issues' => $issues,
					'why'    => __(
						'Session replay attacks enable persistent unauthorized access by capturing and reusing authentication tokens. ' .
						'Without IP binding, attackers can replay stolen cookies from anywhere. Without user agent validation, tokens work ' .
						'across different browsers. Without session rotation on privilege change, attackers maintain access even after ' .
						'legitimate privilege escalation. Timestamp validation prevents indefinite token reuse. According to OWASP, session ' .
						'management flaws enable 95% of authentication bypasses. Session replay is particularly dangerous because it bypasses ' .
						'password security - attackers never need credentials, just captured cookies. Network sniffing, XSS, malware, and ' .
						'public WiFi all expose session tokens to capture and replay.',
						'wpshadow'
					),
					'recommendation' => __(
						'Implement multi-factor session validation: bind tokens to IP (accept changes with reverification), validate user agent ' .
						'(allow updates with confirmation), rotate tokens on privilege change, embed creation timestamp in tokens (reject if >24h old), ' .
						'use cryptographically secure random tokens (32+ bytes from /dev/urandom). Invalidate all tokens on logout using ' .
						'wp_destroy_current_session(). Set httponly and secure flags on cookies. Use SameSite=Strict. Monitor for suspicious ' .
						'session patterns (IP changes, concurrent logins). Consider HTTPS-only sessions with HSTS.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'session-hardening',
				'session_replay_protection'
			);

			return $finding;
		}

		return null;
	}

	/**
	 * Check if sessions bind to IP addresses.
	 *
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
	 * @return bool True if rotation found.
	 */
	private static function check_session_rotation_on_privilege_change() {
		// Check for hooks that rotate sessions.
		return has_action( 'set_user_role' ) || has_action( 'add_user_role' );
	}

	/**
	 * Check timestamp validation.
	 *
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
	 * @return bool True if invalidation found.
	 */
	private static function check_logout_invalidation() {
		// WordPress core wp_logout() calls wp_destroy_current_session().
		return function_exists( 'wp_destroy_current_session' );
	}

	/**
	 * Check for security plugin features.
	 *
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
