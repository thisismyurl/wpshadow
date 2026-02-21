<?php
/**
 * Session Storage Security Treatment
 *
 * Checks session storage location, file permissions, and potential
 * session data leakage vulnerabilities.
 *
 * @package    WPShadow
 * @subpackage Treatments\Security
 * @since      1.6035.1600
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
 * Verifies secure session storage configuration including location,
 * permissions, and protection against data leakage.
 *
 * @since 1.6035.1600
 */
class Treatment_Session_Storage extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'secures_session_storage';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Session Storage Security';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies session data is stored securely with proper permissions';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1600
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Session_Storage' );
	}
}
