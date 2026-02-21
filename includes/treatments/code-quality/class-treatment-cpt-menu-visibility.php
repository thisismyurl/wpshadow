<?php
/**
 * CPT Menu Visibility Treatment
 *
 * Verifies custom post types appear in admin menu. Tests show_in_menu and menu_position
 * settings to ensure CPTs are accessible to users in the WordPress admin.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT Menu Visibility Treatment Class
 *
 * Checks for custom post types that should be visible but aren't in the admin menu.
 *
 * @since 1.6030.2148
 */
class Treatment_CPT_Menu_Visibility extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-menu-visibility';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Menu Visibility';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies custom post types appear in admin menu with correct positioning';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'cpt';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_CPT_Menu_Visibility' );
	}
}
