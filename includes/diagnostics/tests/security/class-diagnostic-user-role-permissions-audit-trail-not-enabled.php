<?php
/**
 * User Role Permissions Audit Trail Not Enabled Diagnostic
 *
 * Checks if permissions audit trail is enabled.
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
 * User Role Permissions Audit Trail Not Enabled Diagnostic Class
 *
 * Detects disabled permissions audit trail.
 *
 * @since 1.2601.2352
 */
class Diagnostic_User_Role_Permissions_Audit_Trail_Not_Enabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-role-permissions-audit-trail-not-enabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Role Permissions Audit Trail Not Enabled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if permissions audit trail is enabled';

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
		// Check if audit trail is enabled
		if ( ! get_option( 'enable_audit_trail' ) && ! is_plugin_active( 'stream/stream.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'User role permissions audit trail is not enabled. Enable audit logging to track changes to user roles and permissions.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/user-role-permissions-audit-trail-not-enabled',
			);
		}

		return null;
	}
}
