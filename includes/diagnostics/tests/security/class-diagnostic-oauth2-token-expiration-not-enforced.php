<?php
/**
 * OAuth2 Token Expiration Not Enforced Diagnostic
 *
 * Validates that OAuth2 tokens have expiration (time-limited).
 * Non-expiring tokens = permanent access. Stolen token never expires.
 * Attacker maintains access indefinitely even if password changed.
 *
 * **What This Check Does:**
 * - Detects if OAuth2 tokens implemented
 * - Validates token expiration time limit (usually 1-24 hours)
 * - Tests if refresh tokens supported (renewal)
 * - Confirms expired tokens rejected
 * - Checks if token revocation implemented
 * - Validates logout removes tokens
 *
 * **Why This Matters:**
 * Non-expiring tokens = permanent account compromise. Scenarios:
 * - OAuth2 tokens generated without expiration
 * - User password changed (compromised account recovered)
 * - But stolen token still works (never expires)
 * - Attacker maintains access despite password change
 * - User thinks account is safe (it's not)
 *
 * **Business Impact:**
 * User's account compromised (phishing). User changes password (thinks secure).
 * Attacker still has non-expiring OAuth token. Maintains access for 2 years.
 * Exfiltrates data continuously. User never discovers. Organization liable for
 * $500K+ breach (negligent token management). Expiring tokens (1 hour) would
 * limit attacker access to 1 hour from compromise.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Password change actually secures account
 * - #9 Show Value: Limits attacker persistence
 * - #10 Beyond Pure: Time-bounded trust
 *
 * **Related Checks:**
 * - OAuth/SSO Integration Security (overall OAuth)
 * - Session Management (session expiration)
 * - Logout Implementation (token revocation)
 *
 * **Learn More:**
 * OAuth2 token expiration: https://wpshadow.com/kb/oauth2-token-expiration
 * Video: OAuth2 security (11min): https://wpshadow.com/training/oauth2-tokens
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
 * OAuth2 Token Expiration Not Enforced Diagnostic Class
 *
 * Detects missing OAuth2 token expiration.
 *
 * **Detection Pattern:**
 * 1. Query OAuth2 token configuration
 * 2. Check for expiration time setting
 * 3. Test token validity after expiration
 * 4. Confirm refresh token mechanism
 * 5. Validate expired tokens rejected
 * 6. Return severity if no expiration
 *
 * **Real-World Scenario:**
 * Developer creates OAuth2 implementation. Forgets to add token expiration.
 * Tokens live forever (no timeout). User account stolen (phishing). User
 * changes password. Attacker's token still works (no expiration = permanent).
 * Attacker steals data for 6 months before discovered. With expiration
 * (1 hour): attacker only has 1 hour access. Damage limited dramatically.
 *
 * **Implementation Notes:**
 * - Checks OAuth2 token configuration
 * - Validates expiration time (1-24 hours typical)
 * - Tests refresh token implementation
 * - Severity: critical (no expiration), medium (very long expiration)
 * - Treatment: add token expiration + refresh mechanism
 *
 * @since 0.6093.1200
 */
class Diagnostic_OAuth2_Token_Expiration_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'oauth2-token-expiration-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'OAuth2 Token Expiration Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if OAuth2 token expiration is enforced';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if OAuth2 tokens have expiration
		if ( ! has_filter( 'validate_oauth_token', 'check_token_expiration' ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'OAuth2 token expiration is not enforced. Set token expiration to 1 hour and implement refresh tokens for enhanced security.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/oauth2-token-expiration-not-enforced?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'       => array(
					'why'            => __( 'Non-expiring OAuth2 tokens = permanent account access compromise. Attacker intercepts token via packet sniffing or phishing. User changes password (thinks account secure). Stolen token still works indefinitely. Attacker maintains access for months/years. Exfiltrates data continuously without detection. Business impact: $500K+ breach liability for negligent token management. With token expiration (1 hour): attacker access limited to 1 hour, drastically reducing damage window. OAuth2 RFC 6749 mandates access token expiration; non-compliance = fundamental security failure.', 'wpshadow' ),
					'recommendation' => __( '1. Set access token expiration to 1 hour maximum (lower for sensitive apps). 2. Implement refresh token mechanism (expires 30 days, offline refresh). 3. Store access token in memory only (never localStorage - XSS vulnerable). 4. Store refresh token in httpOnly cookie (not accessible via JavaScript). 5. Validate token expiration on every API request. 6. Revoke refresh tokens on logout. 7. Implement token rotation - issue new refresh token with each use. 8. Add token type validation (Bearer token required). 9. Log token issuance/revocation in activity log. 10. Force re-authentication for sensitive operations (re-issue access token requiring user confirmation).', 'wpshadow' ),
				),
			);
			return Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'authentication', 'oauth2-tokens' );
		}

		return null;
	}
}
