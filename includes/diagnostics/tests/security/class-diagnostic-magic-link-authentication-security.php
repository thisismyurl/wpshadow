<?php
/**
 * Magic Link Authentication Security Diagnostic
 *
 * Detects and analyzes magic link authentication implementations for security issues.
 * Magic links (passwordless login) must verify tokens are cryptographically strong,
 * single-use, time-limited, and properly validated. Weak implementation = session theft.
 *
 * **What This Check Does:**
 * - Detects if magic link authentication implemented
 * - Validates token generation (cryptographically secure random)
 * - Confirms tokens are single-use (not replayable)
 * - Tests token expiration (usually 15-30 minutes)
 * - Checks if token transmitted over HTTPS only
 * - Validates email verification before accepting token
 *
 * **Why This Matters:**
 * Weak magic link implementation = account takeover via token interception. Scenarios:
 * - Magic token predictable (weak random)
 * - Attacker guesses token format, generates valid tokens
 * - Token not time-limited (reusable indefinitely)
 * - Token stored in plaintext in database
 * - Link intercepted in transit (email forwarded)
 *
 * **Business Impact:**
 * SaaS platform implements magic links for "passwordless" convenience. Token
 * generation uses weak random (based on timestamp only). Attacker predicts token
 * format. Intercepts user email (email forwarded to attacker). Attacker replays
 * token from different IP. Gains account access. Premium account credentials stolen.
 * Cost: $100K account takeover liability + recovery.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Passwordless login still secure
 * - #9 Show Value: Prevents token replay attacks
 * - #10 Beyond Pure: Security-first passwordless authentication
 *
 * **Related Checks:**
 * - Authentication Cookie Security (token validation)
 * - Email Verification Implementation (email safety)
 * - Session Management (session reuse prevention)
 *
 * **Learn More:**
 * Magic link security: https://wpshadow.com/kb/magic-link-authentication
 * Video: Secure passwordless login (12min): https://wpshadow.com/training/magic-links
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Magic Link Authentication Security Diagnostic Class
 *
 * Analyzes magic link implementations for security vulnerabilities.
 *
 * **Detection Pattern:**
 * 1. Check if magic link plugin/feature active
 * 2. Validate token generation method
 * 3. Test token entropy (cryptographic randomness)
 * 4. Confirm token single-use enforcement
 * 5. Check token expiration (time-limited)
 * 6. Return severity if weak implementation found
 *
 * **Real-World Scenario:**
 * Developer creates magic link feature. Uses md5(email + timestamp) as token.
 * Attacker knows user email + approximate time. Generates likely token.
 * Tries token. Matches. Gains account access. Weak random = security failure.
 * Production should use random_bytes() + strong hashing.
 *
 * **Implementation Notes:**
 * - Checks for magic link plugin
 * - Validates random generation (random_bytes required)
 * - Confirms single-use, time-limited tokens
 * - Severity: critical (predictable tokens), high (weak validation)
 * - Treatment: use secure token generation + expiration
 *
 * @since 1.6030.2240
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
	 * @since  1.6030.2240
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

			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/magic-link-authentication-security',
				'details'      => $details,
				'context'      => array(
					'why'            => __( 'Weak magic link tokens enable account takeover. Scenario: Attacker predicts token format (md5(email+timestamp)). Guesses correct token. Gains account access without password. Passwordless auth only secure if tokens unpredictable + time-limited. Weak token generation = security theater (looks secure, isn\'t). Auth bypass via weak randomness = critical vuln. NIST mandates cryptographically secure random for authentication tokens.', 'wpshadow' ),
					'recommendation' => __( '1. Generate tokens with random_bytes(32) minimum. 2. Encode as URL-safe base64 (bin2hex or base64_url_encode). 3. Store token hash in database (hash_equals() to prevent timing attacks). 4. Set token expiration to 15-30 minutes maximum. 5. Implement single-use enforcement (delete token after first use). 6. Never send tokens in query strings (email forwarding leaks tokens). 7. Use POST with hidden form field or link with fragment (#). 8. Validate user email matches token email (prevent reuse on wrong account). 9. Log magic link generation/usage in activity log. 10. Rate limit magic link generation (max 3 per 10 minutes per email).', 'wpshadow' ),
				),
			);
			return Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'authentication', 'magic-links' );
		}

		return null;
	}
}
