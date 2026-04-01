<?php
/**
 * Multisite User Permission Isolation Not Configured Diagnostic
 *
 * Checks if multisite permissions are isolated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite User Permission Isolation Not Configured Diagnostic Class
 *
 * Detects unconfigured multisite isolation.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Multisite_User_Permission_Isolation_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-user-permission-isolation-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite User Permission Isolation Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if multisite permissions are isolated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}

		// Check if permission isolation is configured
		if ( ! has_filter( 'user_can_manage_sites', 'wp_check_site_permission' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Multisite user permission isolation is not configured. Set up granular permission controls for multisite user management.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/multisite-user-permission-isolation-not-configured?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
