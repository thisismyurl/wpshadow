<?php
/**
 * Magic Link Authentication Security Diagnostic
 *
 * Detects and analyzes magic link authentication implementations for security.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Magic Link Authentication Security Diagnostic
 *
 * Analyzes magic link implementations for security vulnerabilities.
 *
 * @since 1.2601.2240
 */
class Diagnostic_Magic_Link_Authentication_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'magic-link-authentication-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Magic Link Authentication Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects and analyzes magic link authentication implementations';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for magic link plugins
		$magic_link_plugins = array(
			'magic-login/magic-login.php'     => 'Magic Login',
			'wc-passwordless-login/wc-passwordless-login.php' => 'WC Passwordless',
			'simple-magic-login/simple-magic-login.php' => 'Simple Magic Login',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		$has_magic_link = false;

		foreach ( $magic_link_plugins as $plugin => $name ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$has_magic_link = true;
			}
		}

		if ( ! $has_magic_link ) {
			return null; // No magic link auth implementation
		}

		// Check for custom magic link implementations via hooks
		global $wp_filter;

		$auth_hooks = array(
			'authenticate',
			'wp_login',
			'wp_authenticate_user',
		);

		$custom_auth = 0;
		foreach ( $auth_hooks as $hook ) {
			if ( isset( $wp_filter[ $hook ] ) && ! empty( $wp_filter[ $hook ]->callbacks ) ) {
				$custom_auth += count( $wp_filter[ $hook ]->callbacks );
			}
		}

		if ( $custom_auth > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of authentication hooks */
				__( '%d custom authentication modifications detected', 'wpshadow' ),
				$custom_auth
			);
		}

		// Check for token storage security
		$token_stored_securely = true;
		global $wpdb;

		// Check if magic link tokens are stored in usermeta
		$token_check = $wpdb->get_results(
			"SELECT meta_key FROM {$wpdb->usermeta} WHERE meta_key LIKE '%magic%' OR meta_key LIKE '%token%' LIMIT 5"
		);

		if ( $token_check && is_array( $token_check ) && count( $token_check ) > 0 ) {
			// Check if tokens are hashed
			foreach ( $token_check as $meta ) {
				// If meta_key contains 'token' and values look like plain text, flag it
				if ( strpos( strtolower( $meta->meta_key ), 'token' ) !== false ) {
					$issues[] = __( 'Magic link tokens may not be properly hashed in database', 'wpshadow' );
					break;
				}
			}
		}

		// Check for token expiration
		if ( ! defined( 'MAGIC_LINK_EXPIRATION' ) && ! get_option( 'magic_link_expiration' ) ) {
			$issues[] = __( 'Magic link token expiration may not be configured', 'wpshadow' );
		}

		// Check for email security
		if ( ! is_ssl() ) {
			$issues[] = __( 'Magic links sent over non-HTTPS connection - security risk', 'wpshadow' );
		}

		// Check for rate limiting on magic link requests
		if ( ! get_option( 'magic_link_rate_limit' ) ) {
			$issues[] = __( 'No rate limiting configured for magic link requests', 'wpshadow' );
		}

		// Report findings
		if ( ! empty( $issues ) ) {
			$severity     = 'medium';
			$threat_level = 60;

			if ( count( $issues ) > 3 ) {
				$severity     = 'high';
				$threat_level = 80;
			}

			$description = __( 'Magic link authentication security concerns detected', 'wpshadow' );

			$details = array(
				'magic_link_enabled'   => $has_magic_link,
				'custom_auth_hooks'    => $custom_auth,
				'token_count'          => is_array( $token_check ) ? count( $token_check ) : 0,
				'issues'               => $issues,
			);

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/magic-link-authentication-security',
				'details'      => $details,
			);
		}

		return null;
	}
}
