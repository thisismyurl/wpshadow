<?php
/**
 * Default User Role Settings Problematic Treatment
 *
 * Tests for default user role configuration.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default User Role Settings Problematic Treatment Class
 *
 * Tests for default user role configuration security.
 *
 * @since 1.6093.1200
 */
class Treatment_Default_User_Role_Settings_Problematic extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'default-user-role-settings-problematic';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Default User Role Settings Problematic';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for default user role configuration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Default_User_Role_Settings_Problematic' );
	}
}
