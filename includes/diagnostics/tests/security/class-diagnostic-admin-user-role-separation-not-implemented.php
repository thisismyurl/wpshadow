<?php
/**
 * Admin User Role Separation Not Implemented Diagnostic
 *
 * Checks if admin roles are separated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin User Role Separation Not Implemented Diagnostic Class
 *
 * Detects missing role separation.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Admin_User_Role_Separation_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-user-role-separation-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin User Role Separation Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if admin roles are separated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$roles = wp_roles();
		$custom_roles = 0;

		// Count custom roles beyond default roles
		foreach ( $roles->get_names() as $role ) {
			if ( ! in_array( $role, array( 'administrator', 'editor', 'author', 'contributor', 'subscriber', 'super_admin' ), true ) ) {
				$custom_roles++;
			}
		}

		if ( $custom_roles === 0 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Admin role separation is not implemented. Create custom roles with limited capabilities to improve security and management.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/admin-user-role-separation-not-implemented',
			);
		}

		return null;
	}
}
