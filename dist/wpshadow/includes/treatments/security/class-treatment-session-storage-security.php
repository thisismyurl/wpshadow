<?php
/**
 * Session Storage Security Treatment
 *
 * Detects insecure session storage configurations that expose
 * session data to unauthorized access.
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
 * Session Storage Security Treatment Class
 *
 * Checks for:
 * - Session files in world-readable directories
 * - Session save path with weak permissions
 * - Sessions stored in /tmp on shared hosting
 * - Database session storage without encryption
 * - Session data in web-accessible locations
 * - Session file cleanup not configured
 *
 * Insecure session storage allows attackers to read session files
 * directly from the filesystem or database, bypassing authentication.
 *
 * @since 0.6093.1200
 */
class Treatment_Session_Storage_Security extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $slug = 'session-storage-security';

	/**
	 * The treatment title
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $title = 'Session Storage Security';

	/**
	 * The treatment description
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $description = 'Verifies secure session storage configuration';

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
	 * Validates session storage security.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Session_Storage_Security' );
	}
}
