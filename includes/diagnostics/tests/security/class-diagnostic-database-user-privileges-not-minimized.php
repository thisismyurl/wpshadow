<?php
/**
 * Database User Privileges Not Minimized Diagnostic
 *
 * Checks if database user privileges are minimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database User Privileges Not Minimized Diagnostic Class
 *
 * Detects excessive database privileges.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Database_User_Privileges_Not_Minimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-user-privileges-not-minimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database User Privileges Not Minimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database user privileges are minimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if database privilege audit exists
		if ( ! get_option( 'db_privilege_audit_date' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Database user privileges are not minimized. Grant only SELECT, INSERT, UPDATE, DELETE privileges - avoid GRANT or CREATE privileges for WordPress database users.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/database-user-privileges-not-minimized',
			);
		}

		return null;
	}
}
