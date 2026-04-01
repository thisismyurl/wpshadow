<?php
/**
 * Session Timeout Configuration Treatment
 *
 * Detects insecure session timeout settings that could allow
 * session hijacking or unauthorized persistent access.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session Timeout Configuration Treatment Class
 *
 * Checks for:
 * - Session timeout longer than 24 hours
 * - Idle timeout not configured
 * - "Remember Me" duration longer than 14 days
 * - No absolute session timeout
 * - Session timeout not enforced for sensitive operations
 *
 * According to OWASP, improper session timeout is one of the
 * top 10 authentication vulnerabilities. The average time to
 * detect a breach is 207 days (IBM), during which stolen
 * sessions can remain active.
 *
 * @since 0.6093.1200
 */
class Treatment_Session_Timeout_Configuration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $slug = 'session-timeout-configuration';

	/**
	 * The treatment title
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $title = 'Session Timeout Configuration';

	/**
	 * The treatment description
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $description = 'Verifies session timeout settings follow security best practices';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Analyzes session timeout configuration:
	 * 1. Auth cookie expiration time
	 * 2. Remember Me duration
	 * 3. Idle timeout implementation
	 * 4. Session regeneration after privilege escalation
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Session_Timeout_Configuration' );
	}
}
