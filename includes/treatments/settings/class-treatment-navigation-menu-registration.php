<?php
/**
 * Navigation Menu Registration Treatment
 *
 * Validates that navigation menus are properly registered and
 * displayed in theme templates with accessibility support.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1335
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Navigation Menu Registration Treatment Class
 *
 * Checks navigation menu configuration and usage.
 *
 * @since 1.6032.1335
 */
class Treatment_Navigation_Menu_Registration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'navigation-menu-registration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Navigation Menu Registration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates navigation menu registration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1335
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Navigation_Menu_Registration' );
	}
}
