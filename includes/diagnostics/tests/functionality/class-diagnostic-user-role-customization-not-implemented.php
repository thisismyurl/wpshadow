<?php
/**
 * User Role Customization Not Implemented Diagnostic
 *
 * Checks if custom user roles are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2346
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Role Customization Not Implemented Diagnostic Class
 *
 * Detects missing custom user roles.
 *
 * @since 1.2601.2346
 */
class Diagnostic_User_Role_Customization_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-role-customization-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Role Customization Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if custom user roles are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2346
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_roles;

		// Count custom roles (beyond default)
		$default_roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );
		$custom_roles   = array_diff( array_keys( $wp_roles->roles ), $default_roles );

		if ( count( $custom_roles ) === 0 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No custom user roles are configured. Create custom roles to manage user permissions more effectively.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/user-role-customization-not-implemented',
			);
		}

		return null;
	}
}
