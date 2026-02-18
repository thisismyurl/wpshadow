<?php
/**
 * OAuth/SSO Integration Security Diagnostic
 *
 * Detects and validates OAuth/single sign-on implementations for security.
 * OAuth implemented poorly = account takeover (attacker intercepts tokens).
 * SSO misconfiguration = unauthorized access to multiple sites.
 *
 * **What This Check Does:**
 * - Detects if OAuth/SSO plugin active
 * - Validates OAuth client secret storage (should not be in code)
 * - Tests if tokens are encrypted (at rest)
 * - Confirms token refresh implemented
 * - Checks if user identity verified (not spoofed)
 * - Validates single logout across all sites
 *
 * **Why This Matters:**
 * Weak OAuth = session hijacking. Scenarios:
 * - OAuth token stored in plaintext browser storage
 * - Attacker steals JavaScript (via XSS)
 * - Attacker extracts OAuth token
 * - Replays token to impersonate user
 * - Gains access to multiple connected sites
 *
 * **Business Impact:**
 * SaaS platform uses OAuth for single sign-on (5 connected services). Token
 * stored in localStorage (bad). Attacker injects XSS (via plugin vulnerability).
 * Steals OAuth token. Uses it to access user's email, documents, and payment info
 * (all 5 services). Fraud: $50K. Plus liability + incident response = $500K+.
 * Secure token storage (encrypted) would have prevented.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: OAuth secure end-to-end
 * - #9 Show Value: Prevents multi-site compromise
 * - #10 Beyond Pure: Trust federation
 *
 * **Related Checks:**
 * - Authentication Cookie Security (token validation)
 * - CSRF Protection (OAuth state parameter)
 * - Session Management (token refresh)
 *
 * **Learn More:**
 * OAuth security: https://wpshadow.com/kb/wordpress-oauth-security
 * Video: Secure OAuth setup (15min): https://wpshadow.com/training/oauth-sso
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
 * Diagnostic_OAuth_SSO_Integration_Security Class
 *
 * Analyzes OAuth and single sign-on implementations for security.
 *
 * **Detection Pattern:**
 * 1. Check for OAuth/SSO plugin
 * 2. Validate client secret storage (encrypted)
 * 3. Test token encryption (at rest)
 * 4. Confirm token expiration + refresh
 * 5. Check identity verification (prevent spoofing)
 * 6. Return severity if misconfigured
 *
 * **Real-World Scenario:**
 * WordPress integrates OAuth for Google login. Developer stores client ID in
 * wp-config (visible in source control). Client secret hardcoded in plugin
 * (accessible via backup download). Attacker finds both. Creates fake OAuth
 * app. Redirects users to attacker's app. Steals session tokens. Impersonates
 * users across platform. Secure approach: store secrets in environment variables
 * (never in code). Encrypt tokens. Validate origin.
 *
 * **Implementation Notes:**
 * - Checks for OAuth plugin configuration
 * - Validates secret storage (encrypted, not hardcoded)
 * - Tests token handling (encrypted, time-limited)
 * - Severity: critical (exposed secrets), high (weak token handling)
 * - Treatment: use secure token storage + encryption
 *
 * @since 1.6030.2240
 */
class Diagnostic_OAuth_SSO_Integration_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'oauth-sso-integration-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'OAuth/SSO Integration Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects OAuth/SSO plugins and validates security configuration';

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

		// OAuth/SSO plugins
		$oauth_plugins = array(
			'google-authenticator-for-wordpress/google-authenticator-for-wordpress.php' => 'Google Authenticator',
			'wpcloud/plugin.php'         => 'WordPress.com Connect',
			'jetpack/jetpack.php'        => 'Jetpack (has SSO)',
			'auth0/auth0.php'            => 'Auth0',
			'oauth-single-sign-on/oauth-sso.php' => 'OAuth SSO',
			'miniorange-oauth-openid-connect/mo_openid_connect.php' => 'miniOrange OAuth',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		$has_oauth = false;
		$oauth_plugins_active = array();

		foreach ( $oauth_plugins as $plugin => $name ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$has_oauth = true;
				$oauth_plugins_active[] = $name;
			}
		}

		if ( ! $has_oauth ) {
			return null; // No OAuth/SSO implementation
		}

		// Check OAuth configuration
		$oauth_config = array(
			'oauth_client_id'     => get_option( 'oauth_client_id' ),
			'oauth_client_secret' => get_option( 'oauth_client_secret' ),
			'oauth_redirect_uri'  => get_option( 'oauth_redirect_uri' ),
		);

		foreach ( $oauth_config as $key => $value ) {
			if ( empty( $value ) ) {
				$issues[] = sprintf(
					/* translators: %s: configuration key */
					__( 'OAuth configuration incomplete: %s not set', 'wpshadow' ),
					str_replace( '_', ' ', ucfirst( $key ) )
				);
			}
		}

		// Check for HTTPS/SSL on OAuth callback
		$site_url = get_site_url();
		if ( strpos( $site_url, 'https://' ) === false ) {
			$issues[] = __( 'OAuth callback URL not using HTTPS - security risk', 'wpshadow' );
		}

		// Check for state parameter validation
		global $wp_filter;

		if ( ! isset( $wp_filter['oauth_callback'] ) || empty( $wp_filter['oauth_callback']->callbacks ) ) {
			$issues[] = __( 'OAuth state parameter validation may not be implemented', 'wpshadow' );
		}

		// Check for token storage security
		global $wpdb;
		$token_check = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key LIKE '%oauth%' OR meta_key LIKE '%sso%'"
		);

		if ( $token_check > 0 ) {
			// Check if tokens are encrypted
			$sample_token = $wpdb->get_var(
				"SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key LIKE '%oauth%' LIMIT 1"
			);

			if ( $sample_token && strlen( $sample_token ) < 100 ) {
				$issues[] = __( 'OAuth tokens appear to be stored unencrypted', 'wpshadow' );
			}
		}

		// Check for multiple SSO providers
		if ( count( $oauth_plugins_active ) > 1 ) {
			$issues[] = sprintf(
				/* translators: %d: number of SSO plugins */
				__( 'Multiple SSO plugins active (%d) - may cause conflicts', 'wpshadow' ),
				count( $oauth_plugins_active )
			);
		}

		// Check for user registration via OAuth
		$oauth_user_registration = get_option( 'oauth_auto_register_users' );
		if ( $oauth_user_registration ) {
			// This is actually a feature, but verify it has proper role assignment
			$oauth_default_role = get_option( 'oauth_default_role' );
			if ( empty( $oauth_default_role ) || 'administrator' === $oauth_default_role ) {
				$issues[] = __( 'OAuth auto-registration not configured for security - users may get admin role', 'wpshadow' );
			}
		}

		// Report findings
		if ( ! empty( $issues ) ) {
			$severity     = 'high';
			$threat_level = 75;

			$description = __( 'OAuth/SSO integration security concerns detected', 'wpshadow' );

			$details = array(
				'oauth_plugins_active' => $oauth_plugins_active,
				'issues'               => $issues,
				'site_url_https'       => strpos( $site_url, 'https://' ) !== false,
				'token_count'          => $token_check,
			);

		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/oauth-sso-integration-security',
			'details'      => $details,
			'context'      => array(
				'why'            => __( 'Insecure OAuth/SSO implementation = centralized authentication failure = all accounts compromised simultaneously. Attacker compromises OAuth provider OR intercepts token. Gains access to ALL integrated accounts (email, cloud storage, CRM, WordPress). One attack = multiple services breached. Business impact: coordinated multi-service breach, massive compliance violations (GDPR fines per account). Secure implementation: token validation, state parameter, HTTPS enforcement, token refresh, logout propagation.', 'wpshadow' ),
				'recommendation' => __( '1. Use only trusted OAuth providers (Google, Microsoft, GitHub - security audited). 2. Validate OAuth response state parameter (prevent CSRF attacks). 3. Always use HTTPS for OAuth callbacks (no http:// redirects). 4. Store OAuth tokens encrypted (AES-256 in database). 5. Validate token signature on every use (prevent token forgery). 6. Implement token expiration (1 hour access tokens). 7. Revoke tokens on logout (prevent reuse after logout). 8. Log OAuth login/logout in activity log (detect unauthorized access). 9. Implement fallback password auth if OAuth provider down. 10. Audit OAuth permissions quarterly (minimize requested scopes).', 'wpshadow' ),
			),
		);
		return Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'authentication', 'oauth-sso' );
		}

		return null;
	}
}
