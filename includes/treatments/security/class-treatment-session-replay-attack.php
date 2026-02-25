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
}
