<?php
/**
 * Session Timeout Settings Treatment
 *
 * Issue #4850: Session Timeout Too Long or Nonexistent
 * Pillar: 🛡️ Safe by Default
 *
 * Verifies session timeout settings follow security best practices.
 * Long or nonexistent session timeouts allow compromised sessions to remain valid indefinitely.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Session_Timeout_Settings Class
 *
 * Checks for:
 * - PHP session.gc_maxlifetime longer than 24 hours
 * - WordPress auth cookie expiration longer than 24 hours
 * - "Remember Me" duration longer than 14 days
 * - No idle timeout mechanism
 * - No absolute session timeout
 *
 * OWASP identifies session timeout as critical for security.
 * IBM reports average breach detection time is 207 days during which
 * stolen sessions can remain active.
 *
 * @since 1.6050.0000
 */
class Treatment_Session_Timeout_Settings extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $slug = 'session-timeout-settings';

	/**
	 * The treatment title
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $title = 'Session Timeout Too Long or Nonexistent';

	/**
	 * The treatment description
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $description = 'Verifies session timeout settings follow security best practices';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Analyzes session timeout configuration:
	 * 1. PHP session.gc_maxlifetime
	 * 2. WordPress auth cookie expiration
	 * 3. "Remember Me" duration
	 * 4. Idle timeout mechanisms
	 * 5. Absolute session timeout
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Session_Timeout_Settings' );
	}
}
