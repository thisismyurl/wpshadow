<?php
/**
 * User Session Management and Activity
 *
 * Validates user session management and activity monitoring.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_User_Session_Management Class
 *
 * Checks user session management and activity monitoring.
 *
 * @since 0.6093.1200
 */
class Treatment_User_Session_Management extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-session-management';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'User Session Management';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates user session management and activity monitoring';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-management';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_User_Session_Management' );
	}
}
