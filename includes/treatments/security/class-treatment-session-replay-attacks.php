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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Session_Replay_Attacks' );
	}
}
