<?php
/**
 * Concurrent Session Control Treatment
 *
 * Detects lack of concurrent session management that could allow
 * unauthorized shared access or session hijacking persistence.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.2033.2106
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Concurrent Session Control Treatment Class
 *
 * Checks for:
 * - Unlimited simultaneous sessions per user
 * - No session invalidation on password change
 * - Missing session token management
 * - No device/location tracking for sessions
 * - Inability to revoke specific sessions
 *
 * According to NIST guidelines, systems should limit concurrent
 * sessions and provide mechanisms to terminate active sessions,
 * especially after credential changes or suspicious activity.
 *
 * @since 1.2033.2106
 */
class Treatment_Concurrent_Session_Control extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.2033.2106
	 * @var   string
	 */
	protected static $slug = 'concurrent-session-control';

	/**
	 * The treatment title
	 *
	 * @since 1.2033.2106
	 * @var   string
	 */
	protected static $title = 'Concurrent Session Control';

	/**
	 * The treatment description
	 *
	 * @since 1.2033.2106
	 * @var   string
	 */
	protected static $description = 'Verifies proper concurrent session management and controls';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.2033.2106
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Analyzes concurrent session controls:
	 * 1. Session token management (WP_Session_Tokens)
	 * 2. Session invalidation on password change
	 * 3. Concurrent session limits
	 * 4. Session metadata tracking
	 *
	 * @since  1.2033.2106
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Concurrent_Session_Control' );
	}
}
