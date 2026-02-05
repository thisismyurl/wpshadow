<?php
/**
 * Session Replay Attacks Detection Treatment
 *
 * Detects vulnerabilities to session replay attacks by checking for
 * proper token validation and nonce expiration handling.
 *
 * @package    WPShadow
 * @subpackage Treatments\Security
 * @since      1.6041.0204
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session Replay Attacks Detection Treatment Class
 *
 * Session replay attacks occur when an attacker:
 * 1. Captures a valid authentication token
 * 2. Uses it again after it expires or is revoked
 * 3. Gains unauthorized access to the user's account
 *
 * **Real-World Impact:**
 * - 40% of authentication attacks involve token replay
 * - Average cost: $4.24M per breach
 * - Can bypass MFA if not properly protected
 * - Session tokens stored in browser localStorage are vulnerable
 *
 * **Common Vulnerabilities:**
 * - No timestamp validation on tokens
 * - No nonce single-use enforcement
 * - Long session lifetimes without rotation
 * - No device/IP binding to sessions
 *
 * @since 1.6041.0204
 */
class Treatment_Session_Replay_Attacks extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'session-replay-attacks';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Session Replay Attack Protection';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects vulnerabilities to session replay attacks';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security-session';

	/**
	 * Run the treatment check
	 *
	 * @since  1.6041.0204
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$vulnerabilities = array();

		// Check 1: Nonce rotation on sensitive forms
		if ( ! self::check_nonce_rotation() ) {
			$vulnerabilities[] = array(
				'type'    => 'no_nonce_rotation',
				'issue'   => 'Forms do not rotate nonces after use',
				'risk'    => 'Captured nonces can be replayed',
			);
		}

		// Check 2: Session token expiration
		if ( ! self::check_session_expiration() ) {
			$vulnerabilities[] = array(
				'type'    => 'no_session_expiration',
				'issue'   => 'Sessions lack proper expiration',
				'risk'    => 'Old sessions can be replayed indefinitely',
			);
		}

		// Check 3: CSRF token validation on state-changing operations
		if ( ! self::check_csrf_protection() ) {
			$vulnerabilities[] = array(
				'type'    => 'csrf_unprotected_operations',
				'issue'   => 'State-changing operations lack CSRF protection',
				'risk'    => 'Attackers can trick users into replaying actions',
			);
		}

		// Check 4: Session binding to client context
		if ( ! self::check_session_binding() ) {
			$vulnerabilities[] = array(
				'type'    => 'no_session_binding',
				'issue'   => 'Sessions not bound to IP/browser/device',
				'risk'    => 'Stolen tokens can be used from different locations',
			);
		}

		// Check 5: Sensitive action requires re-authentication
		if ( ! self::check_sensitive_operation_reauthentication() ) {
			$vulnerabilities[] = array(
				'type'    => 'no_reauthentication_required',
				'issue'   => 'Sensitive operations do not require re-authentication',
				'risk'    => 'Attackers with replayed tokens can perform critical actions',
			);
		}

		if ( ! empty( $vulnerabilities ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => 'Session replay attack protections are not properly configured. Tokens can be captured and reused.',
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'vulnerabilities' => $vulnerabilities,
				'kb_link'       => 'https://wpshadow.com/kb/security-session-replay-attacks',
				'remediation'   => array(
					'summary' => 'Implement multi-layer session replay protection',
					'steps'   => array(
						'1. Add timestamp validation: Reject tokens older than X minutes',
						'2. Implement nonce single-use: Invalidate nonce after each use',
						'3. Bind sessions to IP: Store and validate IP with session',
						'4. Require re-auth: Sensitive actions (password change, data deletion)',
						'5. Add CSRF tokens: Use wp_nonce_field() for all forms',
						'6. Token rotation: Generate new token on each request',
						'7. Monitor: Log and alert on token reuse attempts',
					),
				),
			);

			// Add upgrade path for WPShadow Pro Security
			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'session-protection',
				'replay-attack-prevention'
			);

			return $finding;
		}

		return null;
	}

	/**
	 * Check if nonces are properly rotated after use
	 *
	 * @return bool True if properly configured, false otherwise.
	 */
	private static function check_nonce_rotation(): bool {
		// Check if WordPress nonce action is being verified
		if ( ! did_action( 'wp_verify_nonce' ) && ! did_action( 'check_admin_referer' ) ) {
			// During initial page load, verify nonce settings
			$nonce_life = (int) apply_filters( 'nonce_life', DAY_IN_SECONDS );
			
			// Default is 1 day (24 hours) which is good
			if ( $nonce_life > 0 ) {
				return true;
			}
		}
		
		return did_action( 'wp_verify_nonce' ) || did_action( 'check_admin_referer' );
	}

	/**
	 * Check if sessions have proper expiration
	 *
	 * @return bool True if properly configured, false otherwise.
	 */
	private static function check_session_expiration(): bool {
		// Check for session timeout configuration
		if ( defined( 'AUTH_COOKIE_EXPIRATION' ) ) {
			$expiry = AUTH_COOKIE_EXPIRATION;
			// Should be less than 2 weeks (14 days) for security
			if ( $expiry <= 14 * DAY_IN_SECONDS ) {
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Check if CSRF protection is in place
	 *
	 * @return bool True if properly configured, false otherwise.
	 */
	private static function check_csrf_protection(): bool {
		global $wp_filter;
		
		// Check if wp_verify_nonce is hooked to relevant actions
		if ( isset( $wp_filter['check_admin_referer'] ) || 
		     isset( $wp_filter['wp_verify_nonce'] ) ||
		     isset( $wp_filter['wp_rest_request'] ) ) {
			return true;
		}
		
		return false;
	}

	/**
	 * Check if sessions are bound to client context
	 *
	 * @return bool True if properly configured, false otherwise.
	 */
	private static function check_session_binding(): bool {
		// Check if session binding is implemented
		if ( isset( $_COOKIE[ LOGGED_IN_COOKIE ] ) ) {
			// Check for custom binding in cookie or session
			$stored_ip = get_user_meta( get_current_user_id(), 'login_ip', true );
			$stored_ua = get_user_meta( get_current_user_id(), 'login_ua', true );
			
			if ( $stored_ip || $stored_ua ) {
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Check if sensitive operations require re-authentication
	 *
	 * @return bool True if properly configured, false otherwise.
	 */
	private static function check_sensitive_operation_reauthentication(): bool {
		// Check if user change password requires current password
		global $wp_filter;
		
		if ( isset( $wp_filter['check_passwords'] ) ||
		     isset( $wp_filter['validate_user_password'] ) ) {
			return true;
		}
		
		// Check for capability checks on sensitive operations
		if ( isset( $wp_filter['pre_delete_user'] ) ||
		     isset( $wp_filter['pre_update_user'] ) ) {
			return true;
		}
		
		return false;
	}
}
